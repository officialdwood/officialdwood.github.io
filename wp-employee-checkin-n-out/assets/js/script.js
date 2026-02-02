jQuery(document).ready(function ($) {
  let timerInterval;
  let clockInTime = null;
  let serverTimeOffset = 0;
  let weeklyTotalDecimal = null;
  let weeklyTotalFormatted = null;
  let dailyTotals = [];

  function parseUiDateTime(value) {
    if (!value) return null;
    const match = value.trim().match(/^(\d{1,2})\/(\d{1,2})\/(\d{4})\s+(\d{1,2}):(\d{2})\s*(AM|PM)$/i);
    if (!match) return null;
    let month = parseInt(match[1], 10) - 1;
    let day = parseInt(match[2], 10);
    const year = parseInt(match[3], 10);
    let hour = parseInt(match[4], 10);
    const minute = parseInt(match[5], 10);
    const meridiem = match[6].toUpperCase();
    if (hour === 12) hour = 0;
    if (meridiem === 'PM') hour += 12;
    return new Date(year, month, day, hour, minute);
  }

  function calculateServerTimeOffset(serverTimeString) {
    try {
      const serverTime = parseUiDateTime(serverTimeString) || new Date(serverTimeString);
      const clientTime = new Date();
      serverTimeOffset = serverTime.getTime() - clientTime.getTime();
      console.log('Server time offset calculated:', serverTimeOffset, 'ms');
    } catch (e) {
      console.warn('Could not calculate server time offset:', e);
      serverTimeOffset = 0;
    }
  }

  function getCurrentServerTime() {
    return new Date(Date.now() + serverTimeOffset);
  }

  function renderWeeklySummary() {
    const totalHtml = weeklyTotalDecimal !== null 
      ? weeklyTotalDecimal.toFixed(2) + " hours" 
      : "0.00 hours";
    $("#tcm-weekly-total .tcm-total-hours").html(totalHtml);
  }

  function renderDailyTotals() {
    const $container = $("#tcm-daily-breakdown");
    if (!$container.length) return;

    if (!Array.isArray(dailyTotals) || !dailyTotals.length) {
      $container.html('<div class="tcm-daily-empty">No hours recorded this week.</div>');
      return;
    }

    const items = dailyTotals
      .map(function (d) {
        const dec = Number.isFinite(d.decimal) ? d.decimal : 0;
        const dayParts = (d.label || '').split(' ');
        const dayName = dayParts[0] || '';
        return (
          '<div class="tcm-daily-item">' +
            '<div class="tcm-daily-day">' + dayName + '</div>' +
            '<div class="tcm-daily-hours">' + dec.toFixed(2) + 'h</div>' +
          '</div>'
        );
      })
      .join('');

    $container.html('<div class="tcm-daily-grid">' + items + '</div>');
  }

  function fetchWeeklyTotal() {
    $.post(
      tcm_ajax_object.ajaxurl,
      { action: "tcm_get_weekly_total" },
      function (resp) {
        if (resp && resp.success && resp.data) {
          weeklyTotalDecimal = parseFloat(resp.data.total_decimal || 0);
          weeklyTotalFormatted = resp.data.total_formatted || (weeklyTotalDecimal.toFixed(2) + " hours");
          dailyTotals = Array.isArray(resp.data.daily_totals) ? resp.data.daily_totals : [];
        } else {
          weeklyTotalDecimal = 0;
          weeklyTotalFormatted = "0.00 hours";
          dailyTotals = [];
        }
        renderWeeklySummary();
        renderDailyTotals();
      }
    ).fail(function(){
      weeklyTotalDecimal = 0;
      weeklyTotalFormatted = "0.00 hours";
      dailyTotals = [];
      renderWeeklySummary();
      renderDailyTotals();
    });
  }

  function startTimer(clockInTimeString = null) {
    clearInterval(timerInterval);
    
    if (clockInTimeString) {
      const parsed = parseUiDateTime(clockInTimeString) || new Date(clockInTimeString);
      if (parsed && !Number.isNaN(parsed.getTime())) {
        clockInTime = parsed;
        console.log('Timer started from server clock-in time:', clockInTime);
      } else {
        console.error('Error parsing clock-in time:', clockInTimeString);
        clockInTime = getCurrentServerTime();
      }
    } else {
      clockInTime = getCurrentServerTime();
      console.log('Timer started from current server time:', clockInTime);
    }
    
    $("#tcm-timer-card").show();
    updateTimer();
    timerInterval = setInterval(updateTimer, 1000);
  }

  function updateTimer() {
    if (!clockInTime) return;
    
    const now = getCurrentServerTime();
    const diffMs = now.getTime() - clockInTime.getTime();
    const totalSeconds = Math.max(0, Math.floor(diffMs / 1000));
    
    const hours = Math.floor(totalSeconds / 3600);
    const minutes = Math.floor((totalSeconds % 3600) / 60);
    const seconds = totalSeconds % 60;
    
    const hoursStr = String(hours).padStart(2, "0");
    const minutesStr = String(minutes).padStart(2, "0");
    const secondsStr = String(seconds).padStart(2, "0");

    const timeDisplay = `${hoursStr}:${minutesStr}:${secondsStr}`;
    const totalHours = (totalSeconds / 3600).toFixed(2);
    
    $("#tcm-timer").html(`
      <div class="timer-main">⏱️ ${timeDisplay}</div>
      <div class="timer-sub">Session: ${totalHours} hours</div>
    `);
    $("#tcm-timer-card").addClass('active');
  }

  function stopTimer() {
    clearInterval(timerInterval);
    clockInTime = null;
    $("#tcm-timer-card").hide().removeClass('active');
    console.log('Timer stopped');
  }

  $("#tcm-clock-in").click(function () {
    const button = $(this);
    button.prop('disabled', true).text('Clocking In...');
    
    $.post(
      tcm_ajax_object.ajaxurl,
      {
        action: "tcm_clock_action",
        clock_action: "clock_in",
      },
      function (response) {
        console.log('Clock in response:', response);
        
        if (response.success) {
          $("#tcm-message").html(`<span style="color: #00a32a;">✅ ${response.data.message}</span>`);
          button.text('Clock In').prop('disabled', true);
          $("#tcm-clock-out").prop('disabled', false);
          
          if (response.data.clock_in) {
            calculateServerTimeOffset(response.data.clock_in);
            startTimer(response.data.clock_in);
          } else {
            startTimer();
          }
          
          fetchWeeklyTotal();
        } else {
          $("#tcm-message").html(`<span style="color: #d63638;">❌ Error: ${response.data}</span>`);
          button.text('Clock In').prop('disabled', false);
        }
      }
    ).fail(function() {
      $("#tcm-message").html(`<span style="color: #d63638;">❌ Network error. Please try again.</span>`);
      button.text('Clock In').prop('disabled', false);
    });
  });

  $("#tcm-clock-out").click(function () {
    const button = $(this);
    button.prop('disabled', true).text('Clocking Out...');
    
    $.post(
      tcm_ajax_object.ajaxurl,
      {
        action: "tcm_clock_action",
        clock_action: "clock_out",
      },
      function (response) {
        console.log('Clock out response:', response);
        
        if (response.success) {
          $("#tcm-message").html(`<span style="color: #00a32a;">✅ ${response.data.message}</span>`);
          $("#tcm-clock-in").prop('disabled', false);
          button.text('Clock Out').prop('disabled', true);
          stopTimer();
          fetchWeeklyTotal();
        } else {
          $("#tcm-message").html(`<span style="color: #d63638;">❌ Error: ${response.data}</span>`);
          button.text('Clock Out').prop('disabled', false);
        }
      }
    ).fail(function() {
      $("#tcm-message").html(`<span style="color: #d63638;">❌ Network error. Please try again.</span>`);
      button.text('Clock Out').prop('disabled', false);
    });
  });

  // Time Adjustment Request Modal
  $("#tcm-request-adjustment-btn").click(function() {
    $("#tcm-adjustment-modal").fadeIn(200);
    // Set default date to today
    const today = new Date();
    const dateStr = today.toISOString().split('T')[0];
    $("#tcm-missed-date").val(dateStr);
  });

  $(".tcm-modal-close, #tcm-cancel-adjustment").click(function() {
    $("#tcm-adjustment-modal").fadeOut(200);
    $("#tcm-adjustment-form")[0].reset();
  });

  // Close modal when clicking outside
  $(window).click(function(event) {
    if (event.target.id === 'tcm-adjustment-modal') {
      $("#tcm-adjustment-modal").fadeOut(200);
      $("#tcm-adjustment-form")[0].reset();
    }
  });

  $("#tcm-adjustment-form").submit(function(e) {
    e.preventDefault();
    
    const missedDate = $("#tcm-missed-date").val();
    const missedTime = $("#tcm-missed-time").val();
    const notes = $("#tcm-adjustment-notes").val();
    
    if (!missedDate || !missedTime || !notes.trim()) {
      alert("Please fill in all fields.");
      return;
    }
    
    const submitBtn = $(this).find('button[type="submit"]');
    submitBtn.prop('disabled', true).text('Submitting...');
    
    $.post(
      tcm_ajax_object.ajaxurl,
      {
        action: "tcm_submit_adjustment_request",
        missed_date: missedDate,
        missed_time: missedTime,
        notes: notes
      },
      function(response) {
        if (response.success) {
          $("#tcm-adjustment-modal").fadeOut(200);
          $("#tcm-adjustment-form")[0].reset();
          $("#tcm-message").html(`<span style="color: #00a32a;">✅ Time adjustment request submitted successfully!</span>`);
          setTimeout(function() {
            $("#tcm-message").html('');
          }, 5000);
        } else {
          alert("Error: " + (response.data || "Could not submit request."));
        }
        submitBtn.prop('disabled', false).text('Submit Request');
      }
    ).fail(function() {
      alert("Network error. Please try again.");
      submitBtn.prop('disabled', false).text('Submit Request');
    });
  });

  function initializeTimer() {
    $.post(
      tcm_ajax_object.ajaxurl,
      {
        action: "tcm_get_server_time",
      },
      function (response) {
        if (response.success && response.data.server_time) {
          calculateServerTimeOffset(response.data.server_time);
        }
        checkActiveSession();
        fetchWeeklyTotal();
      }
    ).fail(function() {
      console.warn('Could not get server time, proceeding without offset');
      checkActiveSession();
      fetchWeeklyTotal();
    });
  }

  function checkActiveSession() {
    $.post(
      tcm_ajax_object.ajaxurl,
      {
        action: "tcm_check_session",
      },
      function (response) {
        console.log('Session check response:', response);
        
        if (response.success && response.data.clock_in) {
          $("#tcm-clock-in").prop('disabled', true);
          $("#tcm-clock-out").prop('disabled', false);
          
          console.log('Resuming timer from clock-in time:', response.data.clock_in);
          startTimer(response.data.clock_in);
          
          $("#tcm-message").html(`<span style="color: #00a32a;">✅ You are currently clocked in.</span>`);
        } else {
          $("#tcm-clock-in").prop('disabled', false);
          $("#tcm-clock-out").prop('disabled', true);
          $("#tcm-message").html(`<span style="color: #646970;">Ready to clock in.</span>`);
        }
      }
    ).fail(function() {
      console.error('Failed to check session status');
      $("#tcm-clock-in").prop('disabled', false);
      $("#tcm-clock-out").prop('disabled', true);
    });
  }

  // Initialize timer system
  initializeTimer();
});
