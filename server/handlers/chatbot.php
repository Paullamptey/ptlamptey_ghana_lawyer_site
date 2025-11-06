<?php
// server/handlers/chatbot.php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$config = require '../config.php';
$apiKey = $config['ai']['openai_key'] ?? null;

if (!$apiKey || $apiKey === 'your-openai-api-key-here') {
    // Mock response for testing without API key
    $mockResponses = [
        "As a professional legal advocate, I must advise that for specific legal matters, you should consult with a qualified attorney. However, I can provide general information on legal principles.",
        "Legal advice should always be tailored to individual circumstances. While I can discuss general concepts, please seek personalized counsel for your situation.",
        "In Ghanaian law, matters are governed by the 1992 Constitution and relevant statutes. For detailed guidance, I recommend scheduling a consultation.",
        "International legal practices vary by jurisdiction. Common law principles often apply, but local laws take precedence.",
        "For any legal question, the key is to understand the applicable laws and seek professional advice. How can I assist you further?"
    ];
    echo json_encode(['response' => $mockResponses[array_rand($mockResponses)]]);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$message = $input['message'] ?? '';

if (empty($message)) {
    echo json_encode(['response' => 'Please provide a message.']);
    exit;
}

// System prompt for professional lawyer persona
$systemPrompt = "You are Akoto Assist, a professional legal AI assistant for Akoto Chambers, a law firm in Ghana. You are a qualified Advocate & Solicitor of the Supreme Court of Ghana with expertise in Ghanaian law (constitutional, customary, statutory) and international legal practices. Respond professionally, accurately, and ethically. Reference relevant laws, statutes, and principles. For Ghana-specific matters, cite acts like the 1992 Constitution, Children’s Act, Land Act, etc. For general legal advice, provide high-level guidance and recommend consultations. Do not give specific legal advice that could be construed as practicing law without a retainer. Keep responses concise but informative. If asked about non-legal topics, politely redirect to legal matters.";

$url = 'https://api.openai.com/v1/chat/completions';
$data = [
    'model' => 'gpt-3.5-turbo',
    'messages' => [
        ['role' => 'system', 'content' => $systemPrompt],
        ['role' => 'user', 'content' => $message]
    ],
    'max_tokens' => 500,
    'temperature' => 0.7
];

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $apiKey
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode !== 200) {
    echo json_encode(['response' => 'Sorry, I am unable to respond at the moment. Please try again later or contact us directly.']);
    exit;
}

$result = json_decode($response, true);
$aiResponse = $result['choices'][0]['message']['content'] ?? 'I apologize, but I could not generate a response.';

echo json_encode(['response' => $aiResponse]);
?>