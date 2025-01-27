<?php
@include 'config.php';

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

$data = json_decode(file_get_contents("php://input"), true);
$message = $data['message'];

if (!@$message) {
    $message = "Anda adalah ahli obat profesional, yang mengucapkan salam pembuka, ".
          "berdiskusi dalam bahasa Indonesia secara sopan dengan bahasa baku,".
          "hanya menjawab tentang obat dan farmasi.";
}

// Open AI
$apiKey = $openai_api_key;
$apiUrl = 'https://api.openai.com/v1/chat/completions';
$headerData = [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $apiKey,
];
$postData = json_encode([
    'model' => 'gpt-4o-mini',
    'store' => true,
    'messages' => [
        ['role' => 'user', 'content' => $message]
    ],
]);

$ch = curl_init($apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headerData);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
$response = curl_exec($ch);
curl_close($ch);

$responseData = json_decode($response, true);
if (@$responseData['choices'][0]['message']['content']) {
    $reply = $responseData['choices'][0]['message']['content'];
} else {
    $reply = $responseData['error']['message'];
}

header('Content-Type: application/json');
echo json_encode([
    // 'response' => @$responseData['error'],
    'reply' => @$reply
]);
?>