<?php
require 'config.php';

// простая пагинация, чтобы не грузить всё сразу
$perPage = 200;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) {
    $page = 1;
}
$offset = ($page - 1) * $perPage;

// сколько всего строк в таблице
$total = (int)$pdo->query("SELECT COUNT(*) FROM prices")->fetchColumn();
$totalPages = (int)ceil($total / $perPage);

// основной запрос с подстановкой лимита и смещения
$sql = "
SELECT
    p.pharmacy_id,
    ph.name as pharmacy_name,
    ph.address,
    p.product_id,
    pr.name as product_name,
    p.quantity,
    p.price,
    p.discount_price
FROM prices p
LEFT JOIN pharmacies ph ON ph.id = p.pharmacy_id
LEFT JOIN products pr ON pr.id = p.product_id
LIMIT :limit OFFSET :offset
";

$stmt = $pdo->prepare($sql);
$stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$rows = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Просмотр данных</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="wrap">
        <div class="header">
            <h1>Данные (страница <?= $page ?> из <?= max($totalPages, 1) ?>)</h1>
            <a href="index.php">Загрузить новый CSV</a>
        </div>
        <div class="card table-card">
            <?php if (count($rows) === 0): ?>
                <div class="empty">Пока нет данных. Загрузите CSV на странице загрузки.</div>
            <?php else: ?>
                <!-- таблица с данными -->
                <table>
                    <thead>
                        <tr>
                            <th>Аптека</th>
                            <th>Адрес</th>
                            <th>Товар</th>
                            <th>Количество</th>
                            <th>Цена</th>
                            <th>Цена со скидкой</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rows as $row): ?>
                            <tr>
                                <!-- если названия нет, показываем ID -->
                                <td><?= htmlspecialchars($row['pharmacy_name'] ?? ('Аптека #' . $row['pharmacy_id'])) ?></td>
                                <td class="muted"><?= htmlspecialchars($row['address'] ?? '—') ?></td>
                                <td><?= htmlspecialchars($row['product_name'] ?? ('Товар #' . $row['product_id'])) ?></td>
                                <td><?= htmlspecialchars($row['quantity']) ?></td>
                                <td><?= htmlspecialchars($row['price']) ?></td>
                                <td><?= htmlspecialchars($row['discount_price']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
        <?php if ($totalPages > 1): ?>
            <!-- блок навигации по страницам -->
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a class="page-link" href="?page=<?= $page - 1 ?>">Назад</a>
                <?php endif; ?>
                <span class="page-info">Страница <?= $page ?> из <?= $totalPages ?></span>
                <?php if ($page < $totalPages): ?>
                    <a class="page-link" href="?page=<?= $page + 1 ?>">Вперёд</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
