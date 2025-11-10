<?php
// Import PHPMailer classes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Load PHPMailer autoloader
require '../PHPMailer-master/src/Exception.php';
require '../PHPMailer-master/src/PHPMailer.php';
require '../PHPMailer-master/src/SMTP.php';

// Set target email
$receiving_email_address = 'ajisilmawan@gmail.com';

// Check if request is POST
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    echo 'Invalid request method';
    exit;
}

// Get form data
$name = isset($_POST['name']) ? strip_tags(trim($_POST['name'])) : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$subject = isset($_POST['subject']) ? strip_tags(trim($_POST['subject'])) : '';
$message = isset($_POST['message']) ? strip_tags(trim($_POST['message'])) : '';

// Validate data
if (empty($name) || empty($email) || empty($subject) || empty($message)) {
    echo 'Please fill all required fields.';
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo 'Please enter a valid email address.';
    exit;
}

// Create log file as backup
$log_file = '../contact_messages.log';
$log_entry = "=== " . date('Y-m-d H:i:s') . " ===\n";
$log_entry .= "Name: " . $name . "\n";
$log_entry .= "Email: " . $email . "\n";
$log_entry .= "Subject: " . $subject . "\n";
$log_entry .= "Message: " . $message . "\n";
$log_entry .= "========================\n\n";
file_put_contents($log_file, $log_entry, FILE_APPEND | LOCK_EX);

try {
    // Create a new PHPMailer instance
    $mail = new PHPMailer(true);
    
    // Enable debugging
    $mail->SMTPDebug = 0;  // 0=off, 1=client messages, 2=client and server messages
    
    // Check if we should use SMTP configuration
    if (file_exists('smtp_config.php')) {
        include 'smtp_config.php';
        
        if (isset($use_smtp) && $use_smtp === true) {
            // Use SMTP settings
            $mail->isSMTP();
            $mail->Host = $smtp_settings['host'];
            $mail->SMTPAuth = true;
            $mail->Username = $smtp_settings['username'];
            $mail->Password = $smtp_settings['password'];
            $mail->SMTPSecure = $smtp_settings['encryption'];
            $mail->Port = $smtp_settings['port'];
            // Additional SMTP settings for Gmail
            $mail->SMTPOptions = array(
                'ssl' => array(
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                )
            );
        } else {
            // Use PHP's mail function as fallback
            $mail->isMail();
        }
    } else {
        // Use PHP's mail function as default
        $mail->isMail();
    }

    // Set email content
    $mail->setFrom('ajisilmawan@gmail.com', 'ARTHAPURA GLOBAL KONSTRUKSI');
    $mail->addAddress($receiving_email_address);
    $mail->addReplyTo($email, $name);
    $mail->isHTML(true);
    $mail->Subject = 'AGK Website Contact: ' . $subject;
    
    // Build email body with basic HTML
    $mail->Body = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; }
            .container { max-width: 600px; margin: 0 auto; }
            .header { background-color: #f8f9fa; padding: 20px; border-bottom: 3px solid #0d6efd; }
            .content { padding: 20px; }
            .field { margin-bottom: 15px; }
            .label { font-weight: bold; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h2>Pesan Baru dari Website AGK</h2>
            </div>
            <div class='content'>
                <div class='field'>
                    <span class='label'>Nama:</span><br>
                    " . htmlspecialchars($name) . "
                </div>
                <div class='field'>
                    <span class='label'>Email:</span><br>
                    " . htmlspecialchars($email) . "
                </div>
                <div class='field'>
                    <span class='label'>Subjek:</span><br>
                    " . htmlspecialchars($subject) . "
                </div>
                <div class='field'>
                    <span class='label'>Pesan:</span><br>
                    " . nl2br(htmlspecialchars($message)) . "
                </div>
            </div>
        </div>
    </body>
    </html>
    ";

    // Plain text alternative
    $mail->AltBody = "Nama: $name\nEmail: $email\nSubjek: $subject\nPesan: $message";

    // Send email
    $mail->send();
    echo 'OK'; // Success response for the form processing
} catch (Exception $e) {
    // Log the detailed error
    $error_message = "Mailer Error: " . $mail->ErrorInfo;
    error_log($error_message);
    
    // Append error to log file for debugging
    $error_log = "=== ERROR " . date('Y-m-d H:i:s') . " ===\n";
    $error_log .= $error_message . "\n";
    $error_log .= "========================\n\n";
    file_put_contents($log_file, $error_log, FILE_APPEND | LOCK_EX);
    
    // For debugging purposes - comment this out in production
    echo "Error: " . $error_message; 
    // Uncomment this for production:
    // echo "Your message has been received. We will get back to you soon!";
}
?>
