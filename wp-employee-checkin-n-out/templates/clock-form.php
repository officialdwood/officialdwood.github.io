<?php
$user = wp_get_current_user();
?>

<div class="tcm-clock-form">
    <p class="tcm-greeting">Hello, <strong><?php echo esc_html($user->display_name); ?></strong></p>
    
    <!-- Weekly Hours Summary Box (Shows immediately on login) -->
    <div class="tcm-weekly-summary">
        <h3 class="tcm-summary-title">Weekly Hours Summary</h3>
        <div id="tcm-weekly-total" class="tcm-weekly-total">
            <span class="tcm-total-label">Total:</span>
            <span class="tcm-total-hours">Loading...</span>
        </div>
        <div id="tcm-daily-breakdown" class="tcm-daily-breakdown"></div>
    </div>

    <!-- Clock In/Out Buttons -->
    <div class="tcm-action-buttons">
        <button id="tcm-clock-in" class="tcm-button tcm-clock-in-btn">Clock In</button>
        <button id="tcm-clock-out" class="tcm-button tcm-clock-out-btn">Clock Out</button>
    </div>
    
    <p id="tcm-message" class="tcm-message"></p>
    
    <!-- Active Timer Display -->
    <div class="tcm-timer-card" id="tcm-timer-card" style="display:none;">
        <div id="tcm-timer" class="tcm-timer"></div>
    </div>

    <!-- Request Time Adjustment Button -->
    <div class="tcm-adjustment-wrap">
        <button id="tcm-request-adjustment-btn" class="tcm-button tcm-adjustment-btn">Request Time Adjustment</button>
    </div>

    <!-- Time Adjustment Request Modal -->
    <div id="tcm-adjustment-modal" class="tcm-modal" style="display:none;">
        <div class="tcm-modal-content">
            <span class="tcm-modal-close">&times;</span>
            <h3>Request Time Adjustment</h3>
            <p class="tcm-modal-desc">If you missed a punch or need to adjust your time, please provide the details below.</p>
            <form id="tcm-adjustment-form">
                <div class="tcm-form-group">
                    <label for="tcm-missed-date">Date of Missed Punch:</label>
                    <input type="date" id="tcm-missed-date" name="missed_date" required />
                </div>
                <div class="tcm-form-group">
                    <label for="tcm-missed-time">Time of Missed Punch:</label>
                    <input type="time" id="tcm-missed-time" name="missed_time" required />
                </div>
                <div class="tcm-form-group">
                    <label for="tcm-adjustment-notes">Notes/Explanation:</label>
                    <textarea id="tcm-adjustment-notes" name="notes" rows="4" placeholder="Please explain the reason for this adjustment request..." required></textarea>
                </div>
                <div class="tcm-form-actions">
                    <button type="submit" class="tcm-button tcm-submit-btn">Submit Request</button>
                    <button type="button" class="tcm-button tcm-cancel-btn" id="tcm-cancel-adjustment">Cancel</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Logout Button -->
    <div class="tcm-logout-wrap">
        <a class="tcm-button tcm-logout-btn" href="<?php echo esc_url( wp_logout_url( home_url('/timeclock') ) ); ?>">Logout</a>
    </div>
</div>