
<?php
$token = '{{TELEGRAM_TOKEN}}';       // Placeholder para el token
$chat_id = '{{TELEGRAM_CHAT_ID}}';   // Placeholder para el chat ID
$mensaje = "🔔 ¡Alguien está en la puerta!";

$ch = curl_init();
// Configuración avanzada para resolver el DNS
curl_setopt($ch, CURLOPT_URL, "https://api.telegram.org/bot{$token}/sendMessage");
curl_setopt($ch, CURLOPT_RESOLVE, ["api.telegram.org:443:{{TELEGRAM_IP}}"]); // IP actual de Telegram
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    'chat_id' => $chat_id,
    'text' => $mensaje
]));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
if (curl_errno($ch)) {
    echo "Error: " . curl_error($ch);
} else {
    echo "¡Notificación enviada!";
}
curl_close($ch);
?>
