<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Подключение к базе данных
    $db = new PDO('mysql:host=localhost;dbname=multi_role', 'user', 'password');

    // Получение данных из формы
    $broadcast_name = $_POST['broadcast_name'];
    $broadcast_cost = $_POST['broadcast_cost'];
    $duration = $_POST['duration'];
    $frequency = $_POST['frequency'];
    $total_cost = $broadcast_cost * $duration * $frequency;

    // Вставка нового заказа в базу данных
    $stmt = $db->prepare("INSERT INTO orders (broadcast_name, broadcast_cost, duration, frequency, total_cost, created_at) 
                          VALUES (:broadcast_name, :broadcast_cost, :duration, :frequency, :total_cost, NOW())");
    $stmt->bindParam(':broadcast_name', $broadcast_name);
    $stmt->bindParam(':broadcast_cost', $broadcast_cost);
    $stmt->bindParam(':duration', $duration);
    $stmt->bindParam(':frequency', $frequency);
    $stmt->bindParam(':total_cost', $total_cost);
    $stmt->execute();

    // Сохранение данных в документ
    
    require_once 'vendor/autoload.php';
    $outputFile = 'review.docx';
    $document = new \PhpOffice\PhpWord\TemplateProcessor('./review.docx');

    $document->setValue('broadcast_name', $broadcast_name);
    $document->setValue('broadcast_cost', $broadcast_cost);
    $document->setValue('duration', $duration);
    $document->setValue('frequency', $frequency);
    $document->setValue('total_cost', $total_cost);

    $document->saveAs($outputFile);

    // Устанавливаем заголовки для скачивания файла
    header("Content-Type: application/octet-stream");
    header("Accept-Ranges: bytes");
    header("Content-Disposition: attachment; filename=".$outputFile);

    readfile($outputFile);
    unlink($outputFile); // Удаляем временный файл документа
    exit; // Завершаем выполнение скрипта

} elseif ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // HTML код формы для ввода данных остаётся без изменений
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Order</title>
</head>
<body>
    <h1>Add Order</h1>
    <form method="post">
        <label for="broadcast_name">Название передачи:</label><br>
        <input type="text" id="broadcast_name" name="broadcast_name" required><br>

        <label for="broadcast_cost">Стоимость передачи (в рублях):</label><br>
        <input type="text" id="broadcast_cost" name="broadcast_cost" required><br>

        <label for="duration">Продолжительность (в минутах):</label><br>
        <input type="text" id="duration" name="duration" required><br>

        <label for="frequency">Частота показа:</label><br>
        <input type="text" id="frequency" name="frequency" required><br>

        <label for="total_cost">Общая стоимость:</label><br>
        <input type="text" id="total_cost" name="total_cost" value="" disabled><br>

        <button type="submit">Добавить заказ</button>
    </form>
</body>
</html>
<?php } ?>