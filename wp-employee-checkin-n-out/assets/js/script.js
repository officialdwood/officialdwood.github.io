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
    return new Date(year, month, day, hour, minute); // local tz assumption
  }

  // Calculate server time offset when page loads
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

  
  function renderIdleSummary() {
    const weekly = weeklyTotalDecimal !== null ? weeklyTotalDecimal.toFixed(2) + " hours" : "0.00 hours";
    $("#tcm-timer").html(
      '<div class="timer-main">📊 Your Hours This Week</div>' +
      '<div class="timer-sub">Total: ' + weekly + '</div>'
    ).addClass('active');
  }

  function renderDailyTotals() {
    const $container = $("#tcm-daily-breakdown");
    if (!$container.length) return;

    // Preserve the header if it exists
    const header = '<div class="tcm-daily-header">Daily Breakdown</div>';

    if (!Array.isArray(dailyTotals) || !dailyTotals.length) {
      $container.html(header + '<div class="tcm-daily-empty">Daily totals unavailable.</div>');
      return;
    }

    const items = dailyTotals
      .map(function (d) {
        const dec = Number.isFinite(d.decimal) ? d.decimal : 0;
        return (
          '<div class="tcm-daily-item">' +
            '<div class="tcm-daily-day">' + (d.label || d.date || '') + '</div>' +
            '<div class="tcm-daily-hours">' + dec.toFixed(2) + ' h</div>' +
          '</div>'
        );
      })
      .join('');

    $container.html(header + '<div class="tcm-daily-grid">' + items + '</div>');
  }

  function fetchWeeklyTotal() {
    $.post(
      tcm_ajax_object.ajaxurl,
      { action: "tcm_get_weekly_total" },
      function (resp) {
        if (resp && resp.success && resp.data) {
          weeklyTotalDecimal = parseFloat(resp.data.total_decimal || 0);
          weeklyTotalFormatted = resp.data.total_formatted || (weeklyTotalDecimal.toFixed ? (weeklyTotalDecimal.toFixed(2) + " hours") : "0.00 hours");
          dailyTotals = Array.isArray(resp.data.daily_totals) ? resp.data.daily_totals : [];
        } else {
          weeklyTotalDecimal = null;
          weeklyTotalFormatted = null;
          dailyTotals = [];
        }
        renderDailyTotals();
        if (!clockInTime) {
          renderIdleSummary();
        }
      }
    ).fail(function(){
      dailyTotals = [];
      renderDailyTotals();
      if (!clockInTime) {
        renderIdleSummary();
      }
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
      // Start timer from current server time
      clockInTime = getCurrentServerTime();
      console.log('Timer started from current server time:', clockInTime);
    }
    
    // Update timer immediately, then every second
    updateTimer();
    timerInterval = setInterval(updateTimer, 1000);
  }

  function updateTimer() {
    if (!clockInTime) return;
    
    const now = getCurrentServerTime();
    const diffMs = now.getTime() - clockInTime.getTime();
    
    // Ensure we don't show negative time
    const totalSeconds = Math.max(0, Math.floor(diffMs / 1000));
    
    // Calculate hours, minutes, seconds
    const hours = Math.floor(totalSeconds / 3600);
    const minutes = Math.floor((totalSeconds % 3600) / 60);
    const seconds = totalSeconds % 60;
    
    // Format with leading zeros
    const hoursStr = String(hours).padStart(2, "0");
    const minutesStr = String(minutes).padStart(2, "0");
    const secondsStr = String(seconds).padStart(2, "0");

    const timeDisplay = `${hoursStr}:${minutesStr}:${secondsStr}`;
    const totalHours = (totalSeconds / 3600).toFixed(2);
    
    $("#tcm-timer").html(`
      <div class="timer-main">⏱️ ${timeDisplay}</div>
      <div class="timer-sub">Total: ${totalHours} hours</div>\n      <div class="timer-sub">Weekly Total: ${weeklyTotalDecimal !== null ? weeklyTotalDecimal.toFixed(2) : '--'} hours</div>
    `).addClass('active');
    
    console.log('Timer update:', timeDisplay, `(${totalHours}h)`);
  }

  function stopTimer() {
    clearInterval(timerInterval);
    clockInTime = null;
    renderIdleSummary();
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
        fetchWeeklyTotal();
        
        if (response.success) {
          $("#tcm-message").html(`<span class="tcm-msg-success">✅ ${response.data.message}</span>`);
          button.text('Clock In').prop('disabled', true);
          $("#tcm-clock-out").prop('disabled', false);
          
          // Calculate server time offset if we have clock_in time
          if (response.data.clock_in) {
            calculateServerTimeOffset(response.data.clock_in);
            // Start the timer from the exact server clock-in time
            startTimer(response.data.clock_in);
          } else {
            // Fallback: start timer from current time
            startTimer();
          }
        } else {
          $("#tcm-message").html(`<span class="tcm-msg-error">❌ Error: ${response.data}</span>`);
          button.text('Clock In').prop('disabled', false);
        }
      }
    ).fail(function() {
      $("#tcm-message").html(`<span class="tcm-msg-error">❌ Network error. Please try again.</span>`);
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
        fetchWeeklyTotal();
        
        if (response.success) {
          $("#tcm-message").html(`<span class="tcm-msg-success">✅ ${response.data.message}</span>`);
          $("#tcm-clock-in").prop('disabled', false);
          button.text('Clock Out').prop('disabled', true);
          stopTimer();
        } else {
          $("#tcm-message").html(`<span class="tcm-msg-error">❌ Error: ${response.data}</span>`);
          button.text('Clock Out').prop('disabled', false);
        }
      }
    ).fail(function() {
      $("#tcm-message").html(`<span class="tcm-msg-error">❌ Network error. Please try again.</span>`);
      button.text('Clock Out').prop('disabled', false);
    });
  });

  // Helper function to fetch and display weekly totals
  function loadWeeklyTotalsAndCheckSession() {
    $.post(
      tcm_ajax_object.ajaxurl,
      { action: "tcm_get_weekly_total" },
      function (resp) {
        if (resp && resp.success && resp.data) {
          weeklyTotalDecimal = parseFloat(resp.data.total_decimal || 0);
          weeklyTotalFormatted = resp.data.total_formatted || (weeklyTotalDecimal.toFixed ? (weeklyTotalDecimal.toFixed(2) + " hours") : "0.00 hours");
          dailyTotals = Array.isArray(resp.data.daily_totals) ? resp.data.daily_totals : [];
        } else {
          weeklyTotalDecimal = null;
          weeklyTotalFormatted = null;
          dailyTotals = [];
        }
        renderDailyTotals();
        // Now check for existing active session
        checkActiveSession();
      }
    ).fail(function(){
      dailyTotals = [];
      renderDailyTotals();
      // Even if weekly fetch fails, still check session
      checkActiveSession();
    });
  }

  // Get current server time first to calculate offset
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
        
        // Fetch weekly totals first, then check active session
        // This ensures weekly data is available before rendering idle state
        loadWeeklyTotalsAndCheckSession();
      }
    ).fail(function() {
      console.warn('Could not get server time, proceeding without offset');
      // Still try to fetch weekly totals and check session even if server time fails
      loadWeeklyTotalsAndCheckSession();
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
          
          // Resume timer from the exact clock-in time
          console.log('Resuming timer from clock-in time:', response.data.clock_in);
          startTimer(response.data.clock_in);
          
          $("#tcm-message").html(`<span class="tcm-msg-success">✅ You are currently clocked in.</span>`);
        } else {
          $("#tcm-clock-in").prop('disabled', false);
          $("#tcm-clock-out").prop('disabled', true);
          $("#tcm-message").html(`<span class="tcm-msg-info">👋 Welcome! View your hours below, then clock in when ready.</span>`);
          renderIdleSummary();
        }
      }
    ).fail(function() {
      console.error('Failed to check session status');
      $("#tcm-clock-in").prop('disabled', false);
      $("#tcm-clock-out").prop('disabled', true);
      renderIdleSummary();
    });
  }

  // Initialize timer system
  initializeTimer();

  // Time Change Request Modal Functionality
  const $modal = $("#tcm-request-modal");
  const $requestLink = $("#tcm-request-link");
  const $closeBtn = $(".tcm-modal-close");
  const $cancelBtn = $("#tcm-request-cancel");
  const $form = $("#tcm-request-form");
  const $message = $("#tcm-request-message");

  // Open modal
  $requestLink.click(function(e) {
    e.preventDefault();
    $modal.fadeIn(200);
    // Set today's date as default
    const today = new Date().toISOString().split('T')[0];
    $("#tcm-request-date").val(today);
  });

  // Close modal functions
  function closeModal() {
    $modal.fadeOut(200);
    $form[0].reset();
    $message.hide().removeClass('success error');
  }

  $closeBtn.click(closeModal);
  $cancelBtn.click(closeModal);

  // Close when clicking outside modal
  $modal.click(function(e) {
    if (e.target === $modal[0]) {
      closeModal();
    }
  });

  // Handle form submission
  $form.submit(function(e) {
    e.preventDefault();
    
    const formData = {
      action: 'tcm_submit_time_request',
      nonce: tcm_ajax_object.time_request_nonce,
      request_type: $("#tcm-request-type").val(),
      request_date: $("#tcm-request-date").val(),
      request_time: $("#tcm-request-time").val(),
      description: $("#tcm-request-description").val()
    };

    // Disable submit button
    const $submitBtn = $form.find('button[type="submit"]');
    $submitBtn.prop('disabled', true).text('Submitting...');

    $.post(tcm_ajax_object.ajaxurl, formData, function(response) {
      $submitBtn.prop('disabled', false).text('Submit Request');
      
      if (response.success) {
        $message.removeClass('error').addClass('success')
          .text('✓ Your request has been submitted successfully.')
          .show();
        
        // Clear form after short delay
        setTimeout(function() {
          closeModal();
        }, 2000);
      } else {
        $message.removeClass('success').addClass('error')
          .text('✗ ' + (response.data || 'Failed to submit request. Please try again.'))
          .show();
      }
    }).fail(function() {
      $submitBtn.prop('disabled', false).text('Submit Request');
      $message.removeClass('success').addClass('error')
        .text('✗ Network error. Please try again.')
        .show();
    });
  });
});
