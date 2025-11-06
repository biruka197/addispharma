<?php

declare(strict_types=1);

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Only POST requests are supported.']);
    exit;
}

if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode(['error' => 'Upload a valid image before submitting.']);
    exit;
}

$apiKey = getenv('GEMINI_API_KEY');
if (!$apiKey) {
    http_response_code(500);
    echo json_encode(['error' => 'GEMINI_API_KEY environment variable is not set on the server.']);
    exit;
}

$tmpName = $_FILES['image']['tmp_name'];
$mimeType = mime_content_type($tmpName) ?: 'application/octet-stream';
$allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];

if (!in_array($mimeType, $allowed, true)) {
    http_response_code(400);
    echo json_encode(['error' => 'Unsupported image type. Use JPG, PNG, WEBP, or GIF.']);
    exit;
}

try {
    $imageData = base64_encode(file_get_contents($tmpName));
} catch (Throwable $exception) {
    http_response_code(500);
    echo json_encode(['error' => 'Unable to read the uploaded file.']);
    exit;
}

$endpoint = sprintf(
    'https://generativelanguage.googleapis.com/v1beta/models/%s:generateContent?key=%s',
    rawurlencode('gemini-2.5-pro-latest'),
    urlencode($apiKey)
);

$prompt = <<<PROMPT
You are an assistant that performs OCR and assessment design.
1. Read the text embedded in the supplied image.
2. Return JSON with three fields: "extracted_text", "question", and "answer".
   - "extracted_text" must contain the literal text you detected.
   - "question" should be a challenging exam-style question derived from the extracted material.
   - "answer" must be the correct, concise answer to that question.
3. If you cannot read the image, set the question and answer to null and explain the issue in "extracted_text".
PROMPT;

$payload = [
    'contents' => [[
        'parts' => [
            [
                'inline_data' => [
                    'mime_type' => $mimeType,
                    'data' => $imageData,
                ],
            ],
            [
                'text' => $prompt,
            ],
        ],
    ]],
    'generationConfig' => [
        'temperature' => 0.2,
        'topK' => 32,
        'topP' => 0.95,
        'maxOutputTokens' => 1024,
    ],
    'responseMimeType' => 'application/json',
];

$ch = curl_init($endpoint);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
    ],
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($payload, JSON_THROW_ON_ERROR),
]);

$responseBody = curl_exec($ch);

if ($responseBody === false) {
    http_response_code(502);
    echo json_encode(['error' => 'Unable to contact Gemini API: ' . curl_error($ch)]);
    curl_close($ch);
    exit;
}

$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode >= 400) {
    http_response_code(502);
    $decoded = json_decode($responseBody, true);
    $message = $decoded['error']['message'] ?? 'Gemini API returned an error.';
    echo json_encode(['error' => $message]);
    exit;
}

$decoded = json_decode($responseBody, true);
if (!isset($decoded['candidates'][0]['content']['parts'][0]['text'])) {
    http_response_code(502);
    echo json_encode(['error' => 'Unexpected response from Gemini API.']);
    exit;
}

$rawText = $decoded['candidates'][0]['content']['parts'][0]['text'];

$structured = json_decode($rawText, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(502);
    echo json_encode([
        'error' => 'Gemini response was not valid JSON.',
        'details' => $rawText,
    ]);
    exit;
}

$result = [
    'extractedText' => $structured['extracted_text'] ?? '',
    'question' => $structured['question'] ?? '',
    'answer' => $structured['answer'] ?? '',
];

echo json_encode($result);
