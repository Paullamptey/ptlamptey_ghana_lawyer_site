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
$category = trim($data['category'] ?? '');
$urgency  = strtolower(trim($data['urgency'] ?? 'low'));
$details  = trim($data['details'] ?? '');
if($name==='' || !filter_var($email, FILTER_VALIDATE_EMAIL) || $phone==='' || $category==='' || $details===''){
  http_response_code(400);
  echo json_encode(['ok'=>false,'error'=>'Please complete all fields.']); exit;
}
if(!in_array($urgency, ['low','medium','high'])) $urgency = 'low';

try{
  $stmt = $pdo->prepare('INSERT INTO complaints (name,email,phone,category,urgency,details,status,created_at) VALUES (?,?,?,?,?,?,?,NOW())');
  $stmt->execute([$name,$email,$phone,$category,$urgency,$details,'new']);
}catch(Throwable $e){
  http_response_code(500);
  echo json_encode(['ok'=>false,'error'=>'Failed to save complaint']); exit;
}

// Notify admin
$subject = "New Complaint – {$category} ({$urgency})";
$html = "<h2>Complaint Submitted</h2>
<p><strong>Name:</strong> ".htmlspecialchars($name)."</p>
<p><strong>Email:</strong> ".htmlspecialchars($email)."</p>
<p><strong>Phone:</strong> ".htmlspecialchars($phone)."</p>
<p><strong>Category:</strong> ".htmlspecialchars($category)."</p>
<p><strong>Urgency:</strong> ".htmlspecialchars($urgency)."</p>
<p><strong>Details:</strong><br>".nl2br(htmlspecialchars($details))."</p>";
$mailer->send('owner@akotochambers.com','Chambers Admin',$subject,$html);

// Confirm to client
$mailer->send($email,$name,'We received your complaint',
  "Dear ".htmlspecialchars($name).",<br>We’ve received your complaint and will respond in line with our professional conduct policy.");

echo json_encode(['ok'=>true,'message'=>'Complaint submitted successfully.']);
