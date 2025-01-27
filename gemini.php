<?php
@include 'config.php';

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

$data = json_decode(file_get_contents("php://input"), true);
$message = $data['message'];

if (!@$message) {
    $message = "Anda adalah ahli obat profesional, ucapkan salam pembuka singkat, untuk mempersilakan bertanya tentang obat.";
}

// Gemini
$apiKey = $gemini_api_key;
$apiUrl = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=" . $apiKey;
$headerData = [
    'Content-Type: application/json',
];
$postData = json_encode([
    "contents" => [
        [
            "parts" => [
                [
                    "text" => @$message . 
                    " (anda adalah ahli obat profesional, obrolan dalam bahasa Indonesia, usahakan kurang dalam 5000 karakter).",
                ]
            ]
        ]
    ]
], JSON_PRETTY_PRINT);

$ch = curl_init($apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headerData);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
$response = curl_exec($ch);
curl_close($ch);

$responseData = json_decode($response, true);
$output = @$responseData['candidates'][0]['content']['parts'][0]['text'];
if ($output) {
    $output = preg_replace("/\*\*(.*?)\*\*/", "<strong>$1</strong>", $output);
    $output = str_ireplace("\n", "<br>", $output);
    $reply = @$output;
} else {
    $reply = $responseData['error']['message'];
}

header('Content-Type: application/json');
echo json_encode([
    // 'response' => @$responseData['error'],
    'reply' => @$reply
]);
?>