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
$type  = trim($data['type'] ?? 'General');
$date  = trim($data['date'] ?? '');
$time  = trim($data['time'] ?? '');

if($name==='' || !filter_var($email, FILTER_VALIDATE_EMAIL) || $phone==='' || !preg_match('/^\d{4}-\d{2}-\d{2}$/',$date) || !preg_match('/^\d{2}:\d{2}$/',$time)){
  http_response_code(400);
  echo json_encode(['ok'=>false,'error'=>'Invalid appointment details.']); exit;
}

try{
  $stmt = $pdo->prepare('INSERT INTO appointments (name,email,phone,consult_type,date,time,status,created_at) VALUES (?,?,?,?,?,?,?,NOW())');
  $stmt->execute([$name,$email,$phone,$type,$date,$time,'pending']);
}catch(Throwable $e){
  http_response_code(500);
  echo json_encode(['ok'=>false,'error'=>'Failed to save appointment']); exit;
}

// Notify admin
$subject = "New Appointment Request – {$name}";
$html = "<h2>Appointment Request</h2>
<p><strong>Name:</strong> ".htmlspecialchars($name)."</p>
<p><strong>Email:</strong> ".htmlspecialchars($email)."</p>
<p><strong>Phone:</strong> ".htmlspecialchars($phone)."</p>
<p><strong>Type:</strong> ".htmlspecialchars($type)."</p>
<p><strong>Date:</strong> ".htmlspecialchars($date)."</p>
<p><strong>Time:</strong> ".htmlspecialchars($time)."</p>";
$mailer->send('paullamptey05@gmail.com','Chambers Admin',$subject,$html);

// Confirm to client
$mailer->send($email,$name,'Appointment request received',
  "Dear ".htmlspecialchars($name).",<br>We’ve received your appointment request for {$date} at {$time}. A coordinator will confirm availability.");

echo json_encode(['ok'=>true,'message'=>'Appointment requested successfully.']);
