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
        <div id="tcm-daily-breakdown" class="tcm-daily"></div>
    </div>
    <div class="tcm-logout-wrap" style="text-align:center;">
        <a class="tcm-button tcm-logout-btn" href="<?php echo esc_url( wp_logout_url( home_url('/timeclock') ) ); ?>">Logout</a>
    </div>
    <style>
        .tcm-logout-wrap{ margin-top:12px; }
        .tcm-stats-card{margin-top:14px;background:#f7f7f8;border:1px solid #dedede;border-radius:10px;padding:14px;text-align:center;}
        .tcm-timer .timer-main{font-size:20px;font-weight:700;letter-spacing:0.5px;}
        .tcm-timer .timer-sub{color:#6b7280;font-size:13px;margin-top:3px;}
        .tcm-daily{margin-top:12px;}
        .tcm-daily-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(110px,1fr));gap:8px;}
        .tcm-daily-item{background:#ffffff;border:1px solid #e5e7eb;border-radius:8px;padding:8px 6px;box-shadow:0 1px 2px rgba(0,0,0,0.04);}
        .tcm-daily-day{font-weight:700;color:#111827;font-size:13px;}
        .tcm-daily-hours{color:#047857;font-weight:700;margin-top:2px;}
        .tcm-daily-empty{color:#6b7280;font-size:13px;}
    </style>

</div>