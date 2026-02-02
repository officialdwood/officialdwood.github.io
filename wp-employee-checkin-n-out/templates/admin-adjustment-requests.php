<?php
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap">
    <h1>Time Adjustment Requests</h1>
    
    <?php if (empty($requests)): ?>
        <div class="notice notice-info">
            <p>No time adjustment requests found.</p>
        </div>
    <?php else: ?>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th style="width: 5%;">ID</th>
                    <th style="width: 15%;">Employee</th>
                    <th style="width: 15%;">Missed Punch Time</th>
                    <th style="width: 25%;">Notes</th>
                    <th style="width: 15%;">Requested On</th>
                    <th style="width: 10%;">Status</th>
                    <th style="width: 15%;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($requests as $request): ?>
                    <?php
                    $status_class = '';
                    switch ($request->status) {
                        case 'pending':
                            $status_class = 'tcm-status-pending';
                            $status_label = 'Pending';
                            break;
                        case 'approved':
                            $status_class = 'tcm-status-approved';
                            $status_label = 'Approved';
                            break;
                        case 'denied':
                            $status_class = 'tcm-status-denied';
                            $status_label = 'Denied';
                            break;
                        default:
                            $status_class = '';
                            $status_label = ucfirst($request->status);
                    }
                    ?>
                    <tr class="<?php echo $status_class; ?>">
                        <td><strong>#<?php echo esc_html($request->id); ?></strong></td>
                        <td><?php echo esc_html($request->display_name); ?></td>
                        <td><?php echo esc_html(date('m/d/Y g:i A', strtotime($request->missed_time))); ?></td>
                        <td>
                            <?php echo esc_html($request->notes); ?>
                            <?php if (!empty($request->admin_notes)): ?>
                                <br><small style="color: #666;"><strong>Admin Note:</strong> <?php echo esc_html($request->admin_notes); ?></small>
                            <?php endif; ?>
                        </td>
                        <td><?php echo esc_html(date('m/d/Y g:i A', strtotime($request->created_at))); ?></td>
                        <td>
                            <span class="tcm-status-badge tcm-status-<?php echo esc_attr($request->status); ?>">
                                <?php echo esc_html($status_label); ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($request->status === 'pending'): ?>
                                <button type="button" class="button button-small tcm-review-btn" data-request-id="<?php echo esc_attr($request->id); ?>">
                                    Review
                                </button>
                            <?php else: ?>
                                <span style="color: #999;">Reviewed</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    
                    <!-- Hidden review form for each request -->
                    <tr id="tcm-review-form-<?php echo esc_attr($request->id); ?>" class="tcm-review-form" style="display: none;">
                        <td colspan="7" style="background: #f9f9f9; padding: 20px;">
                            <form method="post" action="">
                                <?php wp_nonce_field('tcm_update_request_' . $request->id); ?>
                                <input type="hidden" name="request_id" value="<?php echo esc_attr($request->id); ?>">
                                <input type="hidden" name="tcm_update_request" value="1">
                                
                                <h3>Review Request #<?php echo esc_html($request->id); ?></h3>
                                
                                <table class="form-table">
                                    <tr>
                                        <th><label for="new_status_<?php echo esc_attr($request->id); ?>">Decision:</label></th>
                                        <td>
                                            <select name="new_status" id="new_status_<?php echo esc_attr($request->id); ?>" required>
                                                <option value="">-- Select --</option>
                                                <option value="approved">Approve</option>
                                                <option value="denied">Deny</option>
                                            </select>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th><label for="admin_notes_<?php echo esc_attr($request->id); ?>">Admin Notes (Optional):</label></th>
                                        <td>
                                            <textarea name="admin_notes" id="admin_notes_<?php echo esc_attr($request->id); ?>" rows="3" style="width: 100%;"></textarea>
                                        </td>
                                    </tr>
                                </table>
                                
                                <p>
                                    <button type="submit" class="button button-primary">Save Decision</button>
                                    <button type="button" class="button tcm-cancel-review" data-request-id="<?php echo esc_attr($request->id); ?>">Cancel</button>
                                </p>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<style>
    .tcm-status-badge {
        padding: 4px 10px;
        border-radius: 3px;
        font-weight: 600;
        font-size: 12px;
        text-transform: uppercase;
        display: inline-block;
    }
    
    .tcm-status-pending {
        background: #fff3cd;
        color: #856404;
        border: 1px solid #ffc107;
    }
    
    .tcm-status-approved {
        background: #d4edda;
        color: #155724;
        border: 1px solid #28a745;
    }
    
    .tcm-status-denied {
        background: #f8d7da;
        color: #721c24;
        border: 1px solid #dc3545;
    }
    
    .tcm-review-form {
        background: #f9f9f9;
    }
    
    .tcm-review-btn {
        background: #2271b1;
        color: #fff;
        border-color: #2271b1;
    }
    
    .tcm-review-btn:hover {
        background: #135e96;
        border-color: #135e96;
        color: #fff;
    }
</style>

<script>
jQuery(document).ready(function($) {
    $('.tcm-review-btn').on('click', function() {
        var requestId = $(this).data('request-id');
        $('#tcm-review-form-' + requestId).show();
        $(this).prop('disabled', true);
    });
    
    $('.tcm-cancel-review').on('click', function() {
        var requestId = $(this).data('request-id');
        $('#tcm-review-form-' + requestId).hide();
        $('.tcm-review-btn[data-request-id="' + requestId + '"]').prop('disabled', false);
    });
});
</script>
