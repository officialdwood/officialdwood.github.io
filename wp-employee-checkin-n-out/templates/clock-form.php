<?php
$user = wp_get_current_user();
?>

<div class="tcm-clock-form">
    <p>Hello, <strong><?php echo esc_html($user->display_name); ?></strong></p>
    <button id="tcm-clock-in" class="tcm-button">Clock In</button>
    <button id="tcm-clock-out" class="tcm-button">Clock Out</button>
    <p id="tcm-message"></p>
    <div class="tcm-stats-card">
        <div id="tcm-timer" class="tcm-timer"></div>
        <div id="tcm-daily-breakdown" class="tcm-daily">
            <div class="tcm-daily-header">Daily Breakdown</div>
        </div>
    </div>
    <div class="tcm-logout-wrap" style="text-align:center;">
        <a class="tcm-request-link" id="tcm-request-link" href="#">Request Time Change</a>
        <a class="tcm-button tcm-logout-btn" href="<?php echo esc_url( wp_logout_url( home_url('/timeclock') ) ); ?>">Logout</a>
    </div>

    <style>
        .tcm-logout-wrap{ margin-top:12px; }
        .tcm-stats-card{margin-top:14px;background:#f7f7f8;border:1px solid #dedede;border-radius:10px;padding:14px;text-align:center;}
        .tcm-timer .timer-main{font-size:20px;font-weight:700;letter-spacing:0.5px;}
        .tcm-timer .timer-sub{color:#6b7280;font-size:13px;margin-top:3px;}
        .tcm-daily{margin-top:12px;}
        .tcm-daily-header{font-size:14px;font-weight:700;color:#374151;margin-bottom:10px;text-align:center;}
        .tcm-daily-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(110px,1fr));gap:8px;}
        .tcm-daily-item{background:#ffffff;border:1px solid #e5e7eb;border-radius:8px;padding:6px 4px;box-shadow:0 1px 2px rgba(0,0,0,0.04);}
        .tcm-daily-day{font-weight:600;color:#4b5563;font-size:11px;}
        .tcm-daily-hours{color:#6b7280;font-weight:600;margin-top:2px;font-size:11px;}
        .tcm-daily-empty{color:#6b7280;font-size:13px;}
        .tcm-request-link{display:inline-block;margin:8px auto 8px auto;font-size:12px;color:#6b7280;text-decoration:underline;cursor:pointer;user-select:none;}
        .tcm-request-link:hover{color:#374151;text-decoration:underline;}
        
        /* Modal Styles */
        .tcm-modal{display:none;position:fixed;z-index:9999;left:0;top:0;width:100%;height:100%;background-color:rgba(0,0,0,0.5);}
        .tcm-modal-content{background:#fff;margin:5% auto;padding:20px;border-radius:8px;max-width:400px;position:relative;box-shadow:0 4px 6px rgba(0,0,0,0.1);}
        .tcm-modal-close{position:absolute;right:15px;top:10px;font-size:28px;font-weight:bold;color:#9ca3af;cursor:pointer;}
        .tcm-modal-close:hover{color:#374151;}
        .tcm-modal-content h3{margin:0 0 20px 0;font-size:18px;color:#111827;}
        .tcm-form-group{margin-bottom:15px;}
        .tcm-form-group label{display:block;margin-bottom:5px;font-size:13px;font-weight:600;color:#374151;}
        .tcm-form-group input,.tcm-form-group select,.tcm-form-group textarea{width:100%;padding:8px;border:1px solid #d1d5db;border-radius:6px;font-size:13px;box-sizing:border-box;}
        .tcm-form-group textarea{resize:vertical;}
        .tcm-form-actions{display:flex;gap:10px;margin-top:20px;}
        .tcm-btn-primary,.tcm-btn-secondary{flex:1;padding:10px;border:none;border-radius:6px;font-size:14px;font-weight:600;cursor:pointer;}
        .tcm-btn-primary{background:#2271b1;color:#fff;}
        .tcm-btn-primary:hover{background:#135e96;}
        .tcm-btn-secondary{background:#f0f0f1;color:#2c3338;}
        .tcm-btn-secondary:hover{background:#dcdcde;}
        #tcm-request-message{margin-top:15px;padding:10px;border-radius:6px;font-size:13px;display:none;}
        #tcm-request-message.success{background:#e8f5e8;border:1px solid #00a32a;color:#00a32a;}
        #tcm-request-message.error{background:#fef2f2;border:1px solid #d63638;color:#d63638;}
    </style>

</div>

<!-- Time Change Request Modal (outside main container) -->
<div id="tcm-request-modal" class="tcm-modal">
    <div class="tcm-modal-content">
        <span class="tcm-modal-close">&times;</span>
        <h3>Request Time Change</h3>
        <form id="tcm-request-form">
            <div class="tcm-form-group">
                <label for="tcm-request-type">Request Type</label>
                <select id="tcm-request-type" name="request_type" required>
                    <option value="">Select type...</option>
                    <option value="missed_punch">Missed Punch</option>
                    <option value="time_change">Time Change</option>
                    <option value="other">Other</option>
                </select>
            </div>
            <div class="tcm-form-group">
                <label for="tcm-request-date">Date</label>
                <input type="date" id="tcm-request-date" name="request_date" required>
            </div>
            <div class="tcm-form-group">
                <label for="tcm-request-time">Time</label>
                <input type="time" id="tcm-request-time" name="request_time" required>
            </div>
            <div class="tcm-form-group">
                <label for="tcm-request-description">Description</label>
                <textarea id="tcm-request-description" name="description" rows="3" placeholder="Brief description of the request..." required></textarea>
            </div>
            <div class="tcm-form-actions">
                <button type="button" class="tcm-btn-secondary" id="tcm-request-cancel">Cancel</button>
                <button type="submit" class="tcm-btn-primary">Submit Request</button>
            </div>
            <div id="tcm-request-message"></div>
        </form>
    </div>
</div>