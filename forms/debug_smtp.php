<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>PHPMailer SMTP Test</h1>";

// Import PHPMailer classes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Load PHPMailer
require '../PHPMailer-master/src/Exception.php';
require '../PHPMailer-master/src/PHPMailer.php';
require '../PHPMailer-master/src/SMTP.php';

// Baca konfigurasi SMTP
if (file_exists('smtp_config.php')) {
    include 'smtp_config.php';
    
    echo "<h2>Konfigurasi SMTP</h2>";
    echo "<pre>";
    echo "SMTP Enabled: " . ($use_smtp ? "Yes" : "No") . "\n";
    echo "SMTP Host: " . $smtp_settings['host'] . "\n";
    echo "SMTP Port: " . $smtp_settings['port'] . "\n";
    echo "SMTP Username: " . $smtp_settings['username'] . "\n";
    echo "SMTP Password: " . (empty($smtp_settings['password']) ? "MISSING!" : "CONFIGURED") . "\n";
    echo "SMTP Encryption: " . $smtp_settings['encryption'] . "\n";
    echo "</pre>";
    
    // Coba koneksi SMTP
    echo "<h2>Testing SMTP Connection</h2>";
    try {
        // Buat instance PHPMailer
        $mail = new PHPMailer(true);
        
        // Set mode debug
        $mail->SMTPDebug = SMTP::DEBUG_SERVER;
        $mail->Debugoutput = function($str, $level) {
            echo "<pre>$str</pre>";
        };
        
        // Set SMTP
        $mail->isSMTP();
        $mail->Host = $smtp_settings['host'];
        $mail->SMTPAuth = true;
        $mail->Username = $smtp_settings['username'];
        $mail->Password = $smtp_settings['password'];
        $mail->SMTPSecure = $smtp_settings['encryption'];
        $mail->Port = $smtp_settings['port'];
        
        // SSL options untuk membantu menangani masalah sertifikat
        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );
        
        // Koneksi ke server
        echo "<p>Mencoba menghubungi server SMTP...</p>";
        if ($mail->smtpConnect()) {
            echo "<p style='color:green; font-weight:bold;'>SUKSES! Koneksi SMTP berhasil.</p>";
            $mail->smtpClose();
        } else {
            echo "<p style='color:red; font-weight:bold;'>GAGAL! Tidak dapat terhubung ke server SMTP.</p>";
        }
        
    } catch (Exception $e) {
        echo "<p style='color:red; font-weight:bold;'>ERROR! " . $mail->ErrorInfo . "</p>";
        echo "<p>" . $e->getMessage() . "</p>";
    }
    
    // Coba kirim test email
    echo "<h2>Kirim Email Test</h2>";
    try {
        // Reset mailer
        $mail = new PHPMailer(true);
        
        // Set SMTP lagi
        $mail->isSMTP();
        $mail->SMTPDebug = SMTP::DEBUG_SERVER;
        $mail->Debugoutput = function($str, $level) {
            echo "<pre>$str</pre>";
        };
        $mail->Host = $smtp_settings['host'];
        $mail->SMTPAuth = true;
        $mail->Username = $smtp_settings['username'];
        $mail->Password = $smtp_settings['password'];
        $mail->SMTPSecure = $smtp_settings['encryption'];
        $mail->Port = $smtp_settings['port'];
        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );
        
        // Set konten email
        $mail->setFrom($smtp_settings['username'], 'ARTHAPURA GLOBAL KONSTRUKSI');
        $mail->addAddress($smtp_settings['username']);
        $mail->Subject = 'Test Email dari AGK Website';
        $mail->Body = 'Ini adalah email test dari AGK Website. Jika Anda menerima email ini, berarti konfigurasi SMTP sudah benar.';
        
        // Kirim email
        echo "<p>Mencoba kirim email test ke " . $smtp_settings['username'] . "...</p>";
        if ($mail->send()) {
            echo "<p style='color:green; font-weight:bold;'>SUKSES! Email berhasil dikirim.</p>";
        } else {
            echo "<p style='color:red; font-weight:bold;'>GAGAL! Email tidak dapat dikirim.</p>";
        }
        
    } catch (Exception $e) {
        echo "<p style='color:red; font-weight:bold;'>ERROR! " . $mail->ErrorInfo . "</p>";
        echo "<p>" . $e->getMessage() . "</p>";
    }
    
    // Info tambahan
    echo "<h2>Info Sistem</h2>";
    echo "<pre>";
    echo "PHP Version: " . phpversion() . "\n";
    echo "Extensions: \n";
    echo " - OpenSSL: " . (extension_loaded('openssl') ? "Loaded" : "Not loaded!") . "\n";
    echo " - cURL: " . (extension_loaded('curl') ? "Loaded" : "Not loaded!") . "\n";
    echo " - SMTP Port Test (587): " . (fsockopen('smtp.gmail.com', 587) ? "Open" : "Blocked/Closed!") . "\n";
    echo " - SMTP Port Test (465): " . (fsockopen('smtp.gmail.com', 465) ? "Open" : "Blocked/Closed!") . "\n";
    echo "</pre>";
    
} else {
    echo "<p style='color:red; font-weight:bold;'>Error: File smtp_config.php tidak ditemukan!</p>";
}
?>