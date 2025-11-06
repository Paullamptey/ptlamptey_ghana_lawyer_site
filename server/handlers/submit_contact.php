<?php
header('Content-Type: application/json');
if($_SERVER['REQUEST_METHOD'] !== 'POST'){
  http_response_code(405);
  echo json_encode(['ok'=>false,'error'=>'Method not allowed']); exit;
}
$raw = file_get_contents('php://input');
$data = json_decode($raw, true);
if(!$data){ $data = $_POST; }

require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../utils/Mailer.php';
$config = require __DIR__ . '/../config.php';
$mailer = new Mailer($config);

// basic rate limit by IP
$ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
$now = time();
$bucket = sys_get_temp_dir() . '/lawyer_rate_' . md5($ip);
$last = is_file($bucket) ? (int)file_get_contents($bucket) : 0;
if($now - $last < 5){
  http_response_code(429);
  echo json_encode(['ok'=>false,'error'=>'Too many requests, try again.']); exit;
}
file_put_contents($bucket, (string)$now);

// Validate
$name  = trim($data['name'] ?? '');
$email = trim($data['email'] ?? '');
$phone = trim($data['phone'] ?? '');
$msg   = trim(($data['message'] ?? $data['summary'] ?? ''));
$case  = trim($data['case_type'] ?? 'General');

if($name==='' || !filter_var($email, FILTER_VALIDATE_EMAIL) || $phone==='' || $msg===''){
  http_response_code(400);
  echo json_encode(['ok'=>false,'error'=>'Please complete all fields with valid information.']); exit;
}

try{
  $stmt = $pdo->prepare('INSERT INTO clients (name,email,phone,case_type,notes,created_at) VALUES (?,?,?,?,?,NOW())');
  $stmt->execute([$name,$email,$phone,$case,$msg]);
}catch(Throwable $e){
  http_response_code(500);
  echo json_encode(['ok'=>false,'error'=>'Failed to save data']); exit;
}

// Notify admin
$subject = "New Contact/Intake – {$name} ({$case})";
$html = "<h2>New Contact/Intake</h2>
<p><strong>Name:</strong> ".htmlspecialchars($name)."</p>
<p><strong>Email:</strong> ".htmlspecialchars($email)."</p>
<p><strong>Phone:</strong> ".htmlspecialchars($phone)."</p>
<p><strong>Case Type:</strong> ".htmlspecialchars($case)."</p>
<p><strong>Message:</strong><br>".nl2br(htmlspecialchars($msg))."</p>";
$mailer->send('owner@akotochambers.com','Chambers Admin',$subject,$html);

// Confirm to client
$mailer->send($email,$name,'We received your message',"Thank you, ".htmlspecialchars($name).". We will respond shortly.");

echo json_encode(['ok'=>true,'message'=>'Your message has been received.']);
