<?php
header('Content-Type: application/json');

// Hide raw errors and handle them securely via JSON
error_reporting(E_ALL);
ini_set('display_errors', 0);

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method.');
    }

    $name = $_POST['name'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $package = $_POST['package'] ?? '';

    // Check if the receipt image uploaded correctly
    if (!isset($_FILES['receipt']) || $_FILES['receipt']['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('File upload failed. Please try a smaller image.');
    }

    // Securely stored on the server (Hidden from user inspection)
    $botToken = "8871795481:AAGACvu4m81xsSrvVbQ4IKvUwXYi2ogOVzc";
    $chatId = "7345560366";

    $caption = "🔥 *New Package Order!* 🔥\n\n👤 *Name:* $name\n📞 *Phone:* $phone\n📦 *Package:* $package";

    $filePath = $_FILES['receipt']['tmp_name'];
    $fileName = $_FILES['receipt']['name'];
    $fileType = $_FILES['receipt']['type'];

    $url = "https://api.telegram.org/bot{$botToken}/sendPhoto";

    $postFields = [
        'chat_id' => $chatId,
        'caption' => $caption,
        'parse_mode' => 'Markdown',
        'photo' => new CURLFile($filePath, $fileType, $fileName)
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

    $result = curl_exec($ch);

    if (curl_errno($ch)) {
        $curlError = curl_error($ch);
        curl_close($ch);
        throw new Exception('cURL Connection Error: ' . $curlError);
    }

    curl_close($ch);

    $responseData = json_decode($result, true);
    if (!$responseData || empty($responseData['ok'])) {
        throw new Exception('Telegram API Error: ' . ($responseData['description'] ?? $result));
    }

    echo json_encode(['ok' => true]);

} catch (Exception $e) {
    echo json_encode(['ok' => false, 'description' => $e->getMessage()]);
}
?>
