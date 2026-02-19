<?php
// Безопасность: проверка авторизации (раскомментируйте, если используется)
// session_start();
// if (!isset($_SESSION['user_id'])) {
//     header('Location: login.php');
//     exit;
// }

// УДАЛЕНИЕ ТОВАРА
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete'])) {
    $section = trim($_POST['section'] ?? '');
    $id = intval($_POST['id'] ?? 0);

    $allowedSections = ['men', 'women', 'kids', 'gear', 'sales'];
    if (!in_array($section, $allowedSections) || $id <= 0) {
        $error = 'Неверные данные для удаления';
    } else {
        $file = __DIR__ . "/../data/{$section}.json";
        if (file_exists($file)) {
            $products = json_decode(file_get_contents($file), true) ?: [];
            $products = array_filter($products, fn($p) => $p['id'] !== $id);
            file_put_contents($file, json_encode(array_values($products), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
            $success = 'Товар удалён!';
        } else {
            $error = 'Файл раздела не найден';
        }
    }
}

// ЗАГРУЗКА ВСЕХ ТОВАРОВ И ПОДСЧЁТ КОЛИЧЕСТВА
$sections = ['men', 'women', 'kids', 'gear', 'sales'];
$sectionLabels = [
    'men' => 'Мужское',
    'women' => 'Женское',
    'kids' => 'Детское',
    'gear' => 'Снаряжение',
    'sales' => 'Акции'
];

$allProducts = [];
$sectionCounts = [];

foreach ($sections as $section) {
    $file = __DIR__ . "/../data/{$section}.json";
    if (file_exists($file)) {
        $products = json_decode(file_get_contents($file), true) ?: [];
        $sectionCounts[$section] = count($products);
        foreach ($products as $p) {
            $p['section'] = $section;
            $allProducts[] = $p;
        }
    } else {
        $sectionCounts[$section] = 0;
    }
}
usort($allProducts, fn($a, $b) => $a['id'] <=> $b['id']);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Управление товарами</title>
    <link rel="stylesheet" href="../src/css/admin.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
            background: #f5f5f5;
            margin: 0;
        }
        h1 {
            margin-bottom: 20px;
            color: #333;
        }
        .stats {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        .stat-card {
            background: white;
            padding: 12px 20px;
            border-radius: 8px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
            min-width: 120px;
            text-align: center;
        }
        .stat-number {
            font-size: 24px;
            font-weight: bold;
            color: #ff6b35;
        }
        .stat-label {
            font-size: 14px;
            color: #666;
        }
        .controls {
            margin-bottom: 20px;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            align-items: center;
        }
        .search-input {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
            width: 300px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            border-radius: 8px;
            overflow: hidden;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        th {
            background: #f9f9f9;
            font-weight: 600;
            position: sticky;
            top: 0;
        }
        .section-badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: bold;
            color: white;
        }
        .section-men { background: #1e88e5; }
        .section-women { background: #e91e63; }
        .section-kids { background: #43a047; }
        .section-gear { background: #fb8c00; }
        .section-sales { background: #ff6b35; }
        .actions form {
            display: inline;
        }
        .btn {
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            margin-right: 5px;
        }
        .btn-add { background: #4caf50; color: white; text-decoration: none; display: inline-block; }
        .btn-add:hover { background: #388e3c; }
        .btn-edit { background: #2196f3; color: white; text-decoration: none; }
        .btn-edit:hover { background: #0b7dda; }
        .btn-delete { background: #f44336; color: white; }
        .btn-delete:hover { background: #d32f2f; }
        .message {
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .success { background: #d4edda; color: #155724; }
        .error { background: #f8d7da; color: #721c24; }
        .images-preview {
            display: flex;
            gap: 5px;
            max-width: 150px;
        }
        .images-preview img {
            width: 40px;
            height: 40px;
            object-fit: cover;
            border-radius: 4px;
        }
        .hidden { display: none; }
    </style>
</head>
<header class="header-admin">
        <ul>
            <li><a href="manage-products.php">Просмотр товара</a></li>
            <li><a href="edit-product.php">Редактировать товар</a></li>
            <li><a href="add-product.php">Добавлять товар</a></li>
        </ul>
        <div>
            <a href="../index.php" class="admin-loguot">Выйти</a>
        </div>
    </header>
<body>
    <h1>Управление товарами</h1>

    <?php if (isset($success)): ?>
        <div class="message success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    <?php if (isset($error)): ?>
        <div class="message error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <!-- Статистика по разделам -->
    <div class="stats">
        <?php foreach ($sectionCounts as $section => $count): ?>
            <div class="stat-card">
                <div class="stat-number"><?= $count ?></div>
                <div class="stat-label"><?= htmlspecialchars($sectionLabels[$section]) ?></div>
            </div>
        <?php endforeach; ?>
        <div class="stat-card">
            <div class="stat-number"><?= array_sum($sectionCounts) ?></div>
            <div class="stat-label">Всего</div>
        </div>
    </div>

    <!-- Управление -->
    <div class="controls">
        <input type="text" id="search-input" class="search-input" placeholder="Поиск по названию...">
        <a href="add-product.php" class="btn btn-add">Добавить товар</a>
    </div>

    <!-- Таблица товаров -->
    <?php if (empty($allProducts)): ?>
        <p>Нет товаров. <a href="add-product.php">Добавить товар</a></p>
    <?php else: ?>
        <table id="products-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Изображения</th>
                    <th>Название</th>
                    <th>Бренд</th>
                    <th>Цена</th>
                    <th>Раздел</th>
                    <th>Действия</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($allProducts as $product): ?>
                <tr data-title="<?= htmlspecialchars(strtolower($product['title'])) ?>">
                    <td><?= htmlspecialchars($product['id']) ?></td>
                    <td class="images-preview">
                        <?php foreach (array_slice($product['images'], 0, 3) as $img): ?>
                            <img src="../<?= htmlspecialchars($img) ?>" alt="Изображение">
                        <?php endforeach; ?>
                    </td>
                    <td><?= htmlspecialchars($product['title']) ?></td>
                    <td><?= htmlspecialchars($product['brand']) ?></td>
                    <td><?= number_format($product['price'], 0, ',', ' ') ?> ₽</td>
                    <td>
                        <span class="section-badge section-<?= htmlspecialchars($product['section']) ?>">
                            <?= htmlspecialchars($sectionLabels[$product['section']]) ?>
                        </span>
                    </td>
                    <td class="actions">
                        <a href="edit-product.php?section=<?= urlencode($product['section']) ?>&id=<?= $product['id'] ?>" class="btn btn-edit">Редактировать</a>
                        <form method="POST" onsubmit="return confirm('Удалить товар?')" style="display:inline;">
                            <input type="hidden" name="section" value="<?= htmlspecialchars($product['section']) ?>">
                            <input type="hidden" name="id" value="<?= $product['id'] ?>">
                            <input type="hidden" name="delete" value="1">
                            <button type="submit" class="btn btn-delete">Удалить</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <script>
        // Поиск по названию
        document.getElementById('search-input').addEventListener('input', function() {
            const query = this.value.toLowerCase().trim();
            const rows = document.querySelectorAll('#products-table tbody tr');
            
            rows.forEach(row => {
                const title = row.dataset.title;
                if (query === '' || title.includes(query)) {
                    row.classList.remove('hidden');
                } else {
                    row.classList.add('hidden');
                }
            });
        });
    </script>
</body>
</html>