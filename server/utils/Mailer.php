<?php
// server/utils/Mailer.php
// Minimal PHPMailer integration. If PHPMailer isn't installed, we fallback to mail().
// Install PHPMailer with Composer in production:
// composer require phpmailer/phpmailer

// Check if PHPMailer is available via Composer
function isPHPMailerAvailable() {
    if (file_exists(__DIR__ . '/../../vendor/autoload.php')) {
        require_once __DIR__ . '/../../vendor/autoload.php';
        return class_exists('PHPMailer\PHPMailer\PHPMailer');
    } elseif (file_exists(__DIR__ . '/../../vendor/phpmailer/phpmailer/src/PHPMailer.php')) {
        require_once __DIR__ . '/../../vendor/phpmailer/phpmailer/src/PHPMailer.php';
        require_once __DIR__ . '/../../vendor/phpmailer/phpmailer/src/Exception.php';
        require_once __DIR__ . '/../../vendor/phpmailer/phpmailer/src/SMTP.php';
        return class_exists('PHPMailer\PHPMailer\PHPMailer');
    }
    return false;
}

class Mailer {
  private $cfg;
  public function __construct(array $cfg){
    $this->cfg = $cfg;
  }
  public function send($toEmail, $toName, $subject, $html){
    $fromEmail = $this->cfg['mail']['from_email'];
    $fromName  = $this->cfg['mail']['from_name'];
    $smtp      = $this->cfg['mail']['smtp'];

    // Use PHPMailer if available and SMTP is enabled
    if($smtp['enabled'] && isPHPMailerAvailable()){
      try {
        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = $smtp['host'];
        $mail->SMTPAuth = true;
        $mail->Username = $smtp['username'];
        $mail->Password = $smtp['password'];
        $mail->SMTPSecure = $smtp['secure'];
        $mail->Port = $smtp['port'];
        $mail->setFrom($fromEmail, $fromName);
        $mail->addAddress($toEmail, $toName);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $html;
        $mail->AltBody = strip_tags($html);
        $mail->send();
        return true;
      } catch (Exception $e) {
        // Log error and fall through to mail() fallback
        error_log("PHPMailer Error: " . $e->getMessage());
      }
    }
    
    // Fallback using native mail()
    $headers  = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8\r\n";
    $headers .= "From: {$fromName} <{$fromEmail}>\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion();
    
    return mail($toEmail, $subject, $html, $headers);
  }
}