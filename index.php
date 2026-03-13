<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Загрузка CSV</title>
    <link rel="stylesheet" href="styles.css">
</head>

<body class="centered">
    <main class="card">
        <h1>Загрузка CSV</h1>
        <!-- форма отправляет файл на upload.php -->
        <form class="upload" action="upload.php" method="POST" enctype="multipart/form-data">
            <div class="row">
                <!-- поле выбора CSV -->
                <input type="file" name="csv_file" accept=".csv" required>
                <button type="submit">Загрузить</button>
            </div>
            <div class="meta">Формат: Аптека, Товар, Количество, Цена, Скидка</div>
        </form>
        <div class="links">
            <!-- ссылка на страницу просмотра -->
            <a href="view.php">Посмотреть данные</a>
        </div>
    </main>
</body>

</html>