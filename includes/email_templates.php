<?php
function getApplicationStatusTemplate($name, $status, $details) {
    return "
    <html>
    <head><style>body{font-family:Arial,sans-serif;}</style></head>
    <body>
        <h2>WMStay Application Status Update</h2>
        <p>Dear $name,</p>
        <p>Your dormitory application status has been updated to: <strong>$status</strong>.</p>
        <p>Details: $details</p>
        <p>If you have any questions, please contact the administration.</p>
        <p>Best regards,<br>WMStay Team</p>
    </body>
    </html>
    ";
}

function getPaymentReminderTemplate($name, $amount, $dueDate) {
    return "
    <html>
    <head><style>body{font-family:Arial,sans-serif;}</style></head>
    <body>
        <h2>WMStay Payment Reminder</h2>
        <p>Dear $name,</p>
        <p>This is a reminder that your payment of $$amount is due on $dueDate.</p>
        <p>Please ensure timely payment to avoid any issues.</p>
        <p>Best regards,<br>WMStay Team</p>
    </body>
    </html>
    ";
}

function getAnnouncementTemplate($title, $content) {
    return "
    <html>
    <head><style>body{font-family:Arial,sans-serif;}</style></head>
    <body>
        <h2>$title</h2>
        <p>$content</p>
        <p>Best regards,<br>WMStay Team</p>
    </body>
    </html>
    ";
}
?>