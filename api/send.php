<?php
header('Content-Type: application/json; charset=utf-8');
require __DIR__ . '/config.php';
if (BOT_TOKEN === 'PASTE_TELEGRAM_BOT_TOKEN_HERE' || CHAT_ID === 'PASTE_CHAT_ID_HERE') {
  echo json_encode(['ok'=>false,'error'=>'Telegram config is empty']); exit;
}
$raw = file_get_contents('php://input');
$data = json_decode($raw, true);
if (!$data) { $data = $_POST; }
$phone = trim($data['phone'] ?? '');
if ($phone === '') { echo json_encode(['ok'=>false,'error'=>'Phone is required']); exit; }
$name = trim($data['name'] ?? '');
$message = trim($data['message'] ?? '');
$text = "🟢 Новая заявка с сайта\n";
$text .= "Имя: " . ($name ?: 'не указано') . "\n";
$text .= "Телефон: " . $phone . "\n";
if ($message) $text .= "Сообщение: " . $message . "\n";
foreach ($data as $k=>$v) {
  if (in_array($k, ['name','phone','message'])) continue;
  if (is_string($v) && trim($v) !== '') $text .= $k . ': ' . trim($v) . "\n";
}
$url = 'https://api.telegram.org/bot'.BOT_TOKEN.'/sendMessage';
$payload = ['chat_id'=>CHAT_ID,'text'=>$text,'parse_mode'=>'HTML'];
$ch = curl_init($url);
curl_setopt_array($ch, [CURLOPT_POST=>true, CURLOPT_POSTFIELDS=>$payload, CURLOPT_RETURNTRANSFER=>true, CURLOPT_TIMEOUT=>10]);
$response = curl_exec($ch);
$error = curl_error($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);
if ($error || $code >= 400) { echo json_encode(['ok'=>false,'error'=>$error ?: $response]); exit; }
echo json_encode(['ok'=>true]);
?>
