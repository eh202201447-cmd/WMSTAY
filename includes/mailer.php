<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

function getMailer() {
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host = 'smtp.example.com'; // Replace with your SMTP host
    $mail->SMTPAuth = true;
    $mail->Username = 'your-email@example.com'; // Replace
    $mail->Password = 'your-password'; // Replace
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;
    $mail->setFrom('noreply@wmstay.local', 'WMStay');
    return $mail;
}

function sendApplicationStatusEmail($studentId, $status, $details = '') {
    global $conn;
    $student = $conn->query("SELECT email, full_name FROM students WHERE id=$studentId")->fetch_assoc();
    if (!$student) return false;

    $subject = 'WMStay Application Status Update';
    $bodyHtml = getApplicationStatusTemplate($student['full_name'], $status, $details);
    $bodyPlain = strip_tags($bodyHtml);

    $mail = getMailer();
    try {
        $mail->addAddress($student['email'], $student['full_name']);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $bodyHtml;
        $mail->AltBody = $bodyPlain;
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Email failed: " . $mail->ErrorInfo);
        return false;
    }
}

function sendPaymentReminderEmail($studentId, $paymentId) {
    global $conn;
    $student = $conn->query("SELECT email, full_name FROM students WHERE id=$studentId")->fetch_assoc();
    $payment = $conn->query("SELECT amount, due_date FROM payments WHERE id=$paymentId")->fetch_assoc();
    if (!$student || !$payment) return false;

    $subject = 'WMStay Payment Reminder';
    $bodyHtml = getPaymentReminderTemplate($student['full_name'], $payment['amount'], $payment['due_date']);
    $bodyPlain = strip_tags($bodyHtml);

    $mail = getMailer();
    try {
        $mail->addAddress($student['email'], $student['full_name']);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $bodyHtml;
        $mail->AltBody = $bodyPlain;
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Email failed: " . $mail->ErrorInfo);
        return false;
    }
}

function sendAnnouncementEmailToAll($announcementId) {
    global $conn;
    $announcement = $conn->query("SELECT title, content FROM announcements WHERE id=$announcementId")->fetch_assoc();
    if (!$announcement) return false;

    $students = $conn->query("SELECT email, full_name FROM students");
    $subject = 'WMStay Announcement: ' . $announcement['title'];
    $bodyHtml = getAnnouncementTemplate($announcement['title'], $announcement['content']);
    $bodyPlain = strip_tags($bodyHtml);

    $mail = getMailer();
    $mail->isHTML(true);
    $mail->Subject = $subject;
    $mail->Body = $bodyHtml;
    $mail->AltBody = $bodyPlain;

    $sent = 0;
    while ($student = $students->fetch_assoc()) {
        try {
            $mail->clearAddresses();
            $mail->addAddress($student['email'], $student['full_name']);
            $mail->send();
            $sent++;
        } catch (Exception $e) {
            error_log("Email to {$student['email']} failed: " . $mail->ErrorInfo);
        }
    }
    return $sent;
}

function sendTestEmail($toEmail) {
    $mail = getMailer();
    try {
        $mail->addAddress($toEmail);
        $mail->isHTML(true);
        $mail->Subject = 'WMStay Test Email';
        $mail->Body = '<h1>Test Email</h1><p>This is a test email from WMStay.</p>';
        $mail->AltBody = 'Test Email: This is a test email from WMStay.';
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Test email failed: " . $mail->ErrorInfo);
        return false;
    }
}
?>