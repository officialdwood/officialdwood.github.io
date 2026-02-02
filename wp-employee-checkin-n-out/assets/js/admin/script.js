jQuery(function ($) {
  "use strict";

  const ajaxUrl = tcm_ajax_object && tcm_ajax_object.ajaxurl ? tcm_ajax_object.ajaxurl : window.ajaxurl;
  const updateNonce = tcm_ajax_object && tcm_ajax_object.nonce ? tcm_ajax_object.nonce : "";
  const reportsNonce = tcm_ajax_object && tcm_ajax_object.reports_nonce ? tcm_ajax_object.reports_nonce : "";
  const addRecordNonce = tcm_ajax_object && tcm_ajax_object.add_record_nonce ? tcm_ajax_object.add_record_nonce : "";

  function currentFilters() {
    const $form = $(".tcm-filter-form").first();
    if (!$form.length) {
      return { tcm_user: "", tcm_week: "", tcm_location: "" };
    }
    return {
      tcm_user: ($form.find("[name='tcm_user']").val() || "").toString(),
      tcm_week: ($form.find("[name='tcm_week']").val() || "").toString(),
      tcm_location: ($form.find("[name='tcm_location']").val() || "").toString(),
    };
  }

  function getExpandedUserIds() {
    return $(".tcm-summary-row.open")
      .map(function () {
        const id = $(this).data("user-id");
        return id ? id.toString() : null;
      })
      .get()
      .filter(Boolean);
  }

  let toastStylesInjected = false;

  function ensureToastStyles() {
    if (toastStylesInjected) {
      return;
    }
    const css = "" +
      ".tcm-toast{position:fixed;right:24px;bottom:24px;z-index:100050;padding:12px 16px;border-radius:6px;font-weight:600;" +
      "box-shadow:0 10px 25px rgba(0,0,0,0.15);opacity:0;transform:translateY(16px);transition:opacity .18s ease,transform .18s ease;" +
      "pointer-events:none;display:flex;align-items:center;gap:8px;}" +
      ".tcm-toast.is-visible{opacity:1;transform:translateY(0);}" +
      ".tcm-toast.success{background:#047857;color:#fff;}" +
      ".tcm-toast.success::before{content:'\\2713';font-weight:700;}" +
      ".tcm-toast.error{background:#b91c1c;color:#fff;}";
    $("<style>").attr("id", "tcm-toast-styles").text(css).appendTo("head");
    toastStylesInjected = true;
  }

  function showToast(message, type) {
    if (!message) {
      return;
    }
    ensureToastStyles();
    const kind = type === "error" ? "error" : "success";
    const $toast = $("<div class='tcm-toast'></div>").addClass(kind).text(message);
    $("body").append($toast);
    const raf = typeof window.requestAnimationFrame === "function"
      ? window.requestAnimationFrame
      : function (cb) { return window.setTimeout(cb, 16); };
    raf(function () {
      $toast.addClass("is-visible");
    });
    window.setTimeout(function () {
      $toast.removeClass("is-visible");
    }, 2600);
    window.setTimeout(function () {
      $toast.remove();
    }, 3000);
  }

  function replaceReportsView(html, opts) {
    const options = opts || {};
    const $fragment = $("<div>").html(html || "");
    const $newWrap = $fragment.find(".wrap.tcm-admin-reports").first();
    const $newModal = $fragment.find("#tcm-add-record-modal").first();
    const $currentWrap = $(".wrap.tcm-admin-reports").first();

    if ($newWrap.length && $currentWrap.length) {
      $currentWrap.replaceWith($newWrap);
    } else {
      window.location.reload();
      return;
    }

    if ($newModal.length) {
      const $existingModal = $("#tcm-add-record-modal");
      if ($existingModal.length) {
        $existingModal.replaceWith($newModal);
      } else {
        $newWrap.after($newModal);
      }
    }

    const expandList = Array.isArray(options.expandedUsers) ? options.expandedUsers.slice() : [];
    if (options.focusUser) {
      const focusId = options.focusUser.toString();
      if (expandList.indexOf(focusId) === -1) {
        expandList.push(focusId);
      }
    }
    const uniqueExpand = Array.from(new Set(expandList.filter(Boolean).map(function (id) { return id.toString(); })));
    if (uniqueExpand.length) {
      window.setTimeout(function () {
        uniqueExpand.forEach(function (userId) {
          const $target = $(".tcm-summary-row[data-user-id='" + userId + "']").first();
          if ($target.length) {
            const $toggle = $target.find(".tcm-summary-toggle").first();
            if ($toggle.length) {
              $toggle.trigger("click");
            }
          }
        });
      }, 120);
    }

    if (options.notice && options.notice.message) {
      showToast(options.notice.message, options.notice.type || "success");
    }
  }

  function refreshReports(options) {
    const opts = options || {};
    const filters = currentFilters();
    const expandedUsers = Array.isArray(opts.expandedUsers)
      ? opts.expandedUsers.filter(Boolean).map(function (id) { return id.toString(); })
      : [];
    const payload = {
      action: "tcm_render_reports",
      nonce: reportsNonce,
      tcm_user: filters.tcm_user,
      tcm_week: filters.tcm_week,
      tcm_location: filters.tcm_location,
    };

    const $wrap = $(".wrap.tcm-admin-reports").first();
    const $overlay = $("<div class='tcm-refresh-overlay'>Refreshing...</div>");
    if ($wrap.length) {
      $wrap.addClass("tcm-is-loading");
      $overlay.appendTo($wrap).css({
        padding: "12px 16px",
        background: "#fff",
        border: "1px solid #d1d5db",
        borderRadius: "6px",
        position: "relative",
        margin: "12px auto",
        maxWidth: "220px",
        textAlign: "center",
        color: "#4b5563",
        fontWeight: "600",
      });
    }

    return $.post(ajaxUrl, payload)
      .done(function (resp) {
        if (resp && resp.success && resp.data && resp.data.html) {
          replaceReportsView(resp.data.html, {
            focusUser: opts.focusUser,
            expandedUsers: expandedUsers,
            notice: opts.notice || null,
          });
        } else {
          window.location.reload();
        }
      })
      .fail(function () {
        window.location.reload();
      })
      .always(function () {
        if ($wrap.length) {
          $wrap.removeClass("tcm-is-loading");
        }
        $overlay.remove();
      });
  }

  function fmtLocal(dt) {
    const pad = function (n) { return (n < 10 ? "0" : "") + n; };
    const hours = dt.getHours();
    const suffix = hours >= 12 ? "PM" : "AM";
    let hour12 = hours % 12;
    if (hour12 === 0) {
      hour12 = 12;
    }
    return (
      pad(dt.getMonth() + 1) +
      "/" +
      pad(dt.getDate()) +
      "/" +
      dt.getFullYear() +
      " " +
      pad(hour12) +
      ":" +
      pad(dt.getMinutes()) +
      " " +
      suffix
    );
  }

  function openAddRecordModal() {
    const $modal = $("#tcm-add-record-modal");
    if (!$modal.length) {
      return;
    }
    const now = new Date();
    const defaultOut = new Date(now.getTime() + 15 * 60000);
    const $clockIn = $modal.find("input[name='tcm_clock_in']");
    const $clockOut = $modal.find("input[name='tcm_clock_out']");
    if ($clockIn.length && !$clockIn.val()) {
      $clockIn.val(fmtLocal(now));
    }
    if ($clockOut.length && !$clockOut.val()) {
      $clockOut.val(fmtLocal(defaultOut));
    }
    $modal.show();
  }

  function closeAddRecordModal() {
    $("#tcm-add-record-modal").hide();
  }

  function loadUserPunches($row, weekOverride) {
    const userId = $row.data("user-id");
    if (!userId) {
      return $.Deferred().reject().promise();
    }
    const filters = currentFilters();
    let weekValue = (weekOverride || $row.data("week") || filters.tcm_week || "").toString();
    if (!weekValue) {
      weekValue = "";
    }
    const $details = $row.find(".tcm-summary-details").first();
    const $loader = $details.find(".tcm-detail-loading");

    $row.attr("aria-busy", "true");
    $details.removeAttr("hidden");
    if (!$loader.length) {
      $details.html('<div class="tcm-detail-loading" style="padding:24px;text-align:center;color:#6b7280;">Loading punches...</div>');
    }

    return $.post(ajaxUrl, {
      action: "tcm_get_user_punches",
      nonce: reportsNonce,
      user_id: userId,
      tcm_week: weekValue,
      tcm_location: filters.tcm_location,
    })
      .done(function (resp) {
        if (resp && resp.success && resp.data) {
          $details.html(resp.data.html || "");
          if (resp.data.weekly_total_formatted) {
            $row
              .find(".tcm-summary-weekly")
              .text(resp.data.weekly_total_formatted)
              .attr("data-weekly-decimal", resp.data.weekly_total_decimal || 0);
          }
          if (typeof resp.data.punch_count !== "undefined") {
            $row.find(".tcm-summary-punches").text(resp.data.punch_count);
          }
          if (resp.data.week_value) {
            $row.attr("data-week", resp.data.week_value);
          } else if (weekValue) {
            $row.attr("data-week", weekValue);
          }
          $row.data("loaded", true);
        } else {
          $details.html('<div style="padding:24px;text-align:center;color:#dc2626;">Unable to load punches. Please try again.</div>');
        }
      })
      .fail(function () {
        $details.html('<div style="padding:24px;text-align:center;color:#dc2626;">Network error while loading punches.</div>');
      })
      .always(function () {
        $row.removeAttr("aria-busy");
      });
  }

  $(document).on("click", ".tcm-summary-toggle", function (e) {
    e.preventDefault();
    const $btn = $(this);
    const expanded = $btn.attr("aria-expanded") === "true";
    const $row = $btn.closest(".tcm-summary-row");
    const $details = $row.find(".tcm-summary-details").first();

    if (expanded) {
      $btn.attr("aria-expanded", "false");
      $row.removeClass("open");
      $details.attr("hidden", true);
      return;
    }

    $btn.attr("aria-expanded", "true");
    $row.addClass("open");
    $details.removeAttr("hidden");

    if (!$row.data("loaded")) {
      loadUserPunches($row);
    }
  });

  $(document).on("change", ".tcm-week-select", function () {
    const $select = $(this);
    const userId = $select.data("user-id");
    const weekValue = ($select.val() || "").toString();
    const $row = $(".tcm-summary-row[data-user-id='" + userId + "']").first();
    if (!$row.length) {
      return;
    }
    $row.data("loaded", false);
    loadUserPunches($row, weekValue);
  });

  $(document).on("submit", ".tcm-filter-form", function (e) {
    e.preventDefault();
    const serialized = $(this).serialize();
    const baseUrl = window.location.origin + window.location.pathname;
    window.location.href = serialized ? baseUrl + "?" + serialized : baseUrl;
  });

  $(document).on("click", "#tcm-open-add-record", function (e) {
    e.preventDefault();
    openAddRecordModal();
  });

  $(document).on("click", "#tcm-close-add-record, #tcm-cancel-add-record", function (e) {
    e.preventDefault();
    closeAddRecordModal();
  });

  $(document).on("click", "#tcm-add-record-modal", function (e) {
    if (e.target === e.currentTarget) {
      closeAddRecordModal();
    }
  });

  $(document).on("submit", "form.tcm-add-form", function (e) {
    e.preventDefault();
    const $form = $(this);
    const payload = {
      action: "tcm_add_manual_record",
      nonce: addRecordNonce,
      user_id: $form.find("[name='tcm_user_id']").val(),
      location: $form.find("[name='tcm_location']").val(),
      clock_in: ($form.find("[name='tcm_clock_in']").val() || "").trim(),
      clock_out: ($form.find("[name='tcm_clock_out']").val() || "").trim(),
      duration_hours: ($form.find("[name='tcm_duration_hours']").val() || "").trim(),
      note: ($form.find("[name='tcm_note']").val() || "").trim(),
    };

    const $submit = $form.find("button[type='submit'], input[type='submit']");
    const originalText = $submit.first().is("button") ? $submit.first().text() : $submit.first().val();

    $submit.prop("disabled", true);
    if ($submit.first().is("button")) {
      $submit.first().text("Saving...");
    } else {
      $submit.first().val("Saving...");
    }

    $.post(ajaxUrl, payload)
      .done(function (resp) {
        if (resp && resp.success && resp.data && resp.data.record) {
          closeAddRecordModal();
          refreshReports({ focusUser: resp.data.record.user_id });
        } else {
          const msg = resp && resp.data && resp.data.message ? resp.data.message : "Unknown error";
          window.alert("Save failed: " + msg);
        }
      })
      .fail(function () {
        window.alert("Save failed: network/server error");
      })
      .always(function () {
        $submit.prop("disabled", false);
        if ($submit.first().is("button")) {
          $submit.first().text(originalText || "Save");
        } else {
          $submit.first().val(originalText || "Save");
        }
      });
  });

  $(document).on("click", ".tcm-summary-details .tcm-delete-btn", function (e) {
    e.preventDefault();
    const $btn = $(this);
    const id = $btn.data("id");
    const userId = $btn.data("user-id");
    if (!id) {
      return;
    }
    if (!window.confirm("Delete this time record? This cannot be undone.")) {
      return;
    }

    const original = $btn.text();
    $btn.prop("disabled", true).text("Deleting...");

    $.post(ajaxUrl, {
      action: "tcm_delete_record",
      nonce: updateNonce,
      id: id,
    })
      .done(function (resp) {
        if (resp && resp.success) {
          refreshReports({ focusUser: userId || null });
        } else {
          const msg = resp && resp.data ? resp.data : "Unknown error";
          window.alert("Delete failed: " + msg);
          $btn.prop("disabled", false).text(original);
        }
      })
      .fail(function () {
        window.alert("Delete failed: network/server error");
        $btn.prop("disabled", false).text(original);
      });
  });

  function postUpdate(payload, $button, focusUser, options) {
    const original = $button.text();
    const opts = options || {};
    const expandedUsers = Array.isArray(opts.expandedUsers)
      ? opts.expandedUsers.filter(Boolean).map(function (id) { return id.toString(); })
      : getExpandedUserIds();
    const successMessage = opts.successMessage || "Punch saved";

    $button.prop("disabled", true).text("Saving...");
    return $.post(ajaxUrl, payload)
      .done(function (resp) {
        if (resp && resp.success) {
          refreshReports({
            focusUser: focusUser || null,
            expandedUsers: expandedUsers,
            notice: { message: successMessage, type: "success" },
          });
        } else {
          const msg = resp && resp.data ? resp.data : "Unknown error";
          window.alert("Update failed: " + msg);
          $button.prop("disabled", false).text(original);
        }
      })
      .fail(function () {
        window.alert("Update failed: network/server error");
        $button.prop("disabled", false).text(original);
      })
      .always(function () {
        if ($button.closest("body").length) {
          $button.prop("disabled", false).text(original);
        }
      });
  }

  $(document).on("click", ".tcm-summary-details .tcm-update-btn", function (e) {
    e.preventDefault();
    const $btn = $(this);
    const rowId = $btn.data("id");
    const userId = $btn.data("user-id");
    if (!rowId) {
      return;
    }

    const hoursInput = $("input.tcm-hours-part[data-id='" + rowId + "']");
    const minutesInput = $("input.tcm-minutes-part[data-id='" + rowId + "']");
    const hours = parseInt(hoursInput.val(), 10) || 0;
    const minutes = parseInt(minutesInput.val(), 10) || 0;
    const totalMinutes = hours * 60 + minutes;
    const totalHours = totalMinutes / 60;

    const inVal = $("input.tcm-edit-clock-in[data-id='" + rowId + "']").val();
    const outVal = $("input.tcm-edit-clock-out[data-id='" + rowId + "']").val();

    const payload = {
      action: "tcm_update_hours",
      nonce: updateNonce,
      id: rowId,
      total_hours: Number.isFinite(totalHours) ? Number(totalHours.toFixed(4)) : 0,
      total_minutes: totalMinutes,
      hours_part: hours,
      minutes_part: minutes,
    };
    if (inVal) {
      payload.time_in = inVal;
    }
    if (outVal) {
      payload.time_out = outVal;
    }

    const expandedUsers = Array.from(new Set(getExpandedUserIds()));
    postUpdate(payload, $btn, userId, {
      expandedUsers: expandedUsers,
      successMessage: "Punch updated",
    });
  });

  $(document).on("click", ".tcm-summary-details .tcm-save-time", function (e) {
    e.preventDefault();
    const $btn = $(this);
    const rowId = $btn.data("id");
    const kind = $btn.data("kind");
    const userId = $btn.data("user-id");
    if (!rowId) {
      return;
    }

    const inVal = $("input.tcm-edit-clock-in[data-id='" + rowId + "']").val();
    const outVal = $("input.tcm-edit-clock-out[data-id='" + rowId + "']").val();

    const payload = {
      action: "tcm_update_hours",
      nonce: updateNonce,
      id: rowId,
    };

    if (kind === "in" && inVal) {
      payload.time_in = inVal;
    }
    if (kind === "out" && outVal) {
      payload.time_out = outVal;
    }
    if (inVal && outVal) {
      payload.time_in = inVal;
      payload.time_out = outVal;
    }

    const expandedUsers = Array.from(new Set(getExpandedUserIds()));
    postUpdate(payload, $btn, userId, {
      expandedUsers: expandedUsers,
      successMessage: "Time saved",
    });
  });
});
