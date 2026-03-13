<?php

require 'config.php';

// если загруженного файла нет - дальше смысла нет
if (!isset($_FILES['csv_file'])) {
    die("Файл не получен");
}

// проверяем стандартную ошибку загрузки
if ($_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
    die("Ошибка загрузки файла: " . $_FILES['csv_file']['error']);
}

// берём временный файл, который создал PHP
$file = $_FILES['csv_file']['tmp_name'];
if (!is_file($file)) {
    die("Временный файл не найден");
}

$handle = fopen($file, "r");
if ($handle === false) {
    die("Ошибка открытия файла");
}

// небольшой хелпер, чтобы из строки получить число
function parseNumber($value) {
    $v = trim((string)$value);
    if ($v === '') {
        return null;
    }
    // убираем пробелы и заменяем запятую на точку
    $v = str_replace([" ", "\xC2\xA0"], "", $v);
    $v = str_replace(",", ".", $v);
    return is_numeric($v) ? (float)$v : null;
}

// определяем разделитель по первой строке
$firstLine = fgets($handle);
if ($firstLine === false) {
    fclose($handle);
    die("Пустой файл");
}
$commaCount = substr_count($firstLine, ",");
$semiCount = substr_count($firstLine, ";");
$delimiter = $semiCount > $commaCount ? ";" : ",";

// заголовок нам не нужен, но всё равно его читаем
$header = str_getcsv($firstLine, $delimiter);

// готовим запросы один раз, чтобы было быстрее
$pharmacyStmt = $pdo->prepare("
    INSERT INTO pharmacies (id, name, address)
    VALUES (?, ?, ?)
    ON DUPLICATE KEY UPDATE id = id
");

$productStmt = $pdo->prepare("
    INSERT INTO products (id, name)
    VALUES (?, ?)
    ON DUPLICATE KEY UPDATE id = id
");

$stmt = $pdo->prepare("
    INSERT INTO prices
    (pharmacy_id, product_id, quantity, price, discount_price)
    VALUES (?, ?, ?, ?, ?)
");

$inserted = 0;
$skipped = 0;

// читаем файл построчно, чтобы не забивать память
while (($data = fgetcsv($handle, 0, $delimiter)) !== false) {
    if (count($data) < 5) {
        $skipped++;
        continue;
    }

    $pharmacy = (int)$data[0];
    $product = (int)$data[1];
    $quantity = (int)parseNumber($data[2]);
    $price = parseNumber($data[3]);
    $discount = parseNumber($data[4]);

    if ($pharmacy <= 0 || $product <= 0 || $price === null || $discount === null) {
        $skipped++;
        continue;
    }

    // формула цены со скидкой
    $discount_price = $price - ($price * $discount / 100.0);

    // добавляем справочники, если их ещё нет
    $pharmacyStmt->execute([$pharmacy, "Аптека #{$pharmacy}", "Адрес неизвестен"]);
    $productStmt->execute([$product, "Товар #{$product}"]);

    $stmt->execute([
        $pharmacy,
        $product,
        $quantity,
        $price,
        $discount_price
    ]);

    $inserted++;
}

fclose($handle);

echo "Импорт завершён. Добавлено: {$inserted}. Пропущено: {$skipped}.";
