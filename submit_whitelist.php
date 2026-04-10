<?php
// submit_whitelist.php

// ========== НАСТРОЙКИ - ЗАМЕНИТЕ НА СВОИ ==========
$bot_token = '8595437741:AAER1QVD0HwZAod5w-aP2VY1AnVf5mdX4As';  // ТОКЕН вашего Telegram бота
$admin_chat_id = '7402154704';  // ВАШ Telegram ID (НЕ username, а цифровой ID!)
// ===================================================

// Получаем данные из формы
$data = json_decode(file_get_contents('php://input'), true);
$nickname = htmlspecialchars(trim($data['nickname'] ?? ''));
$info = htmlspecialchars(trim($data['info'] ?? ''));

if (empty($nickname)) {
    echo json_encode(['success' => false, 'message' => 'Никнейм не может быть пустым']);
    exit;
}

// Проверка на допустимые символы в нике (только латиница, цифры, _)
if (!preg_match('/^[a-zA-Z0-9_]{3,16}$/', $nickname)) {
    echo json_encode(['success' => false, 'message' => 'Никнейм должен содержать 3-16 латинских букв, цифр или _']);
    exit;
}

// Формируем сообщение для админа
$message = "📋 **НОВАЯ ЗАЯВКА НА ВАЙТЛИСТ!**\n\n";
$message .= "👤 **Никнейм:** `$nickname`\n";
if (!empty($info)) {
    $message .= "📝 **О себе:** $info\n";
}
$message .= "\n⏰ Время: " . date('d.m.Y H:i:s');
$message .= "\n\n✅ **Чтобы добавить игрока**, выполните в консоли сервера:\n";
$message .= "`whitelist add $nickname`";
$message .= "\n\n❌ **Чтобы отклонить** — просто проигнорируйте это сообщение.";

// Отправляем админу через Telegram бота
$url = "https://api.telegram.org/bot$bot_token/sendMessage";
$postData = [
    'chat_id' => $admin_chat_id,
    'text' => $message,
    'parse_mode' => 'Markdown'
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$result = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// Отправляем ответ игроку на сайт
if ($httpCode == 200) {
    echo json_encode([
        'success' => true, 
        'message' => 'Заявка отправлена! Администратор рассмотрит её в ближайшее время.'
    ]);
} else {
    echo json_encode([
        'success' => false, 
        'message' => 'Техническая ошибка. Попробуйте позже или напишите администратору в Discord.'
    ]);
}
?>