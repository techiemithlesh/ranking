<div class="panel">
    <div class="panel-heading">
        <h4 class="panel-title">
            <i class="far fa-bell"></i> <?php echo translate('all_notifications'); ?>
            <?php if (count($notifications) > 0): ?>
                <button id="mark-all-read" class="btn btn-sm btn-success pull-right">
                    <i class="fas fa-check-double"></i> <?php echo translate('mark_all_as_read'); ?>
                </button>
            <?php endif; ?>
        </h4>
    </div>
    <div class="panel-body">
        <?php if (count($notifications) > 0): ?>
            <ul class="list-group notification-list">
                <?php foreach ($notifications as $note): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-start <?php echo ($note['is_read'] == 0) ? 'unread-notification' : ''; ?>" 
                        data-id="<?php echo $note['id']; ?>">
                        <div class="ms-2 me-auto notification-content">
                            <div class="notification-header">
                                <span class="fw-bold"><?php echo $note['title']; ?></span>
                                <?php if ($note['is_read'] == 0): ?>
                                    <span class="badge bg-primary new-badge">New</span>
                                <?php endif; ?>
                            </div>
                            <div class="notification-body">
                                <?php echo strip_tags($note['description']); ?>
                            </div>
                            <div class="notification-footer">
                                <small class="text-muted"><?php echo get_nicetime($note['created_at']); ?></small>
                            </div>
                        </div>
                        <div class="notification-actions">
                            <?php if (!empty($note['link'])): ?>
                                <a href="<?php echo base_url($note['link']); ?>" class="btn btn-sm btn-primary view-link">
                                    <i class="fas fa-eye"></i> <?php echo translate('view'); ?>
                                </a>
                            <?php endif; ?>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <div class="alert alert-info text-center"><?php echo translate('no_notifications'); ?></div>
        <?php endif; ?>
    </div>
</div>

<style>
/* Notification styling */
.notification-list .unread-notification {
    background-color: #f0f7ff;
    border-left: 4px solid #007bff;
}

.notification-header {
    margin-bottom: 8px;
    display: flex;
    align-items: center;
}

.notification-header .fw-bold {
    font-weight: bold;
    font-size: 16px;
    margin-right: 10px;
}

.notification-body {
    color: #333;
    margin-bottom: 8px;
}

.notification-footer {
    color: #6c757d;
    font-size: 12px;
}

.new-badge {
    padding: 3px 8px;
    border-radius: 10px;
    font-size: 10px;
    background-color: #007bff;
    color: white;
}

.notification-actions {
    display: flex;
    align-items: center;
}

#mark-all-read {
    margin-left: 10px;
}
</style>

<script>
$(document).ready(function() {
    // Mark notification as read when clicked
    $('.notification-list li').on('click', function(e) {
        // Don't trigger if clicking on the view link
        if($(e.target).closest('.view-link').length === 0) {
            var notification_id = $(this).data('id');
            var $notificationItem = $(this);
            
            // Send AJAX request to mark as read
            $.ajax({
                url: '<?php echo base_url("notification/mark_as_read"); ?>',
                type: 'POST',
                data: {
                    notification_id: notification_id
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        // Update UI to show as read
                        $notificationItem.removeClass('unread-notification');
                        $notificationItem.find('.new-badge').remove();
                    }
                }
            });
        }
    });
    
    // Mark all as read
    $('#mark-all-read').on('click', function() {
        $.ajax({
            url: '<?php echo base_url("notification/mark_all_read"); ?>',
            type: 'POST',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // Update UI to show all as read
                    $('.notification-list li').removeClass('unread-notification');
                    $('.new-badge').remove();
                }
            }
        });
    });
    
    // Also mark as read when view link is clicked
    $('.view-link').on('click', function() {
        var notification_id = $(this).closest('li').data('id');
        
        $.ajax({
            url: '<?php echo base_url("notification/mark_as_read"); ?>',
            type: 'POST',
            data: {
                notification_id: notification_id
            },
            dataType: 'json'
        });
    });
});
</script>