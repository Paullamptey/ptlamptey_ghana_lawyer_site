<?php
// server/config.php
// 1) Rename this file to config.local.php in production and gitignore it.
// 2) Update DB and mail settings.

return [
  'db' => [
    'host' => 'localhost',
    'port' => 3306,
    'name' => 'schema',
    'user' => 'root',
    'pass' => '',
    'charset' => 'utf8mb4'
  ],
  'mail' => [
    'from_email' => 'paullamptey05@gmail.com',
    'from_name'  => 'Akoto Chambers',
    // If using SMTP with PHPMailer:
    'smtp' => [
      'enabled' => false,
      'host' => 'smtp.example.com',
      'port' => 587,
      'username' => 'smtp-user',
      'password' => 'smtp-pass',
      'secure' => 'tls'
    ]
  ],
  'ai' => [
    'openai_key' => 'your-openai-api-key-here' // Replace with actual key
  ]
];

