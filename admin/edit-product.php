<?php
// Безопасность: проверка авторизации (раскомментируйте, если используется)
// session_start();
// if (!isset($_SESSION['user_id'])) {
//     header('Location: login.php');
//     exit;
// }

$section = $_GET['section'] ?? '';
$id = intval($_GET['id'] ?? 0);

// Валидация раздела
$allowedSections = ['men', 'women', 'kids', 'gear', 'sales'];
if (!in_array($section, $allowedSections) || $id <= 0) {
    die('Ошибка: неверные параметры раздела или ID.');
}

// Путь к файлу
$file = __DIR__ . "/../data/{$section}.json";
if (!file_exists($file)) {
    die('Ошибка: файл раздела не найден.');
}

// Загрузка товаров
$products = json_decode(file_get_contents($file), true);
if (json_last_error() !== JSON_ERROR_NONE || !is_array($products)) {
    die('Ошибка: повреждённый JSON-файл.');
}

// Поиск товара
$product = null;
foreach ($products as $p) {
    if (isset($p['id']) && $p['id'] === $id) {
        $product = $p;
        break;
    }
}
if (!$product) {
    die('Ошибка: товар не найден.');
}

// Обработка формы при сохранении
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $brand = trim($_POST['brand'] ?? '');
    $price = intval($_POST['price'] ?? 0);
    $category = trim($_POST['category'] ?? '');
    $size = trim($_POST['size'] ?? '');
    $color = trim($_POST['color'] ?? '');
    $isNew = isset($_POST['is_new']) ? 1 : 0;
    $discount = intval($_POST['discount'] ?? 0);

    if (!$title || !$brand || !$price) {
        $error = 'Заполните все обязательные поля: Название, Бренд, Цена.';
    } else {
        // Копируем текущие изображения
        $images = $product['images'];

        // УДАЛЕНИЕ изображений (галочки)
        $deleteIndices = [];
        for ($i = 0; $i < count($images); $i++) {
            if (isset($_POST['delete_image_' . $i]) && $_POST['delete_image_' . $i] === '1') {
                $deleteIndices[] = $i;
            }
        }

        // Удаляем в обратном порядке, чтобы индексы не сбивались
        rsort($deleteIndices);
        foreach ($deleteIndices as $index) {
            unset($images[$index]);
        }

        // Преобразуем в индексированный массив
        $images = array_values($images);

        // Защита: минимум 1 изображение
        if (empty($images)) {
            $error = 'Товар должен иметь хотя бы одно изображение.';
        } else {
            // ЗАГРУЗКА новых изображений
            $uploadDir = __DIR__ . '/../public/image/product/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            for ($i = 1; $i <= 4; $i++) {
                if (isset($_FILES["image$i"]) && $_FILES["image$i"]['error'] === UPLOAD_ERR_OK) {
                    $tmpName = $_FILES["image$i"]['tmp_name'];
                    $fileName = uniqid() . '_' . basename($_FILES["image$i"]['name']);
                    $filePath = $uploadDir . $fileName;
                    if (move_uploaded_file($tmpName, $filePath)) {
                        // Добавляем новое изображение в конец массива
                        $images[] = "public/image/product/" . $fileName;
                    }
                }
            }

            // Обрезаем до максимум 5 изображений (или 4)
            $images = array_slice($images, 0, 5);

            // Обновляем товар
            $updatedProduct = [
                'id' => $id,
                'title' => $title,
                'brand' => $brand,
                'price' => $price,
                'images' => $images,
                'category' => $category,
                'size' => $size,
                'color' => $color,
                'isNew' => (bool)$isNew,
                'discount' => $discount
            ];

            // Сохраняем
            $found = false;
            foreach ($products as $index => $p) {
                if ($p['id'] === $id) {
                    $products[$index] = $updatedProduct;
                    $found = true;
                    break;
                }
            }

            if ($found) {
                file_put_contents($file, json_encode($products, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
                header("Location: manage-products.php?success=Товар+обновлён!");
                exit;
            } else {
                $error = 'Ошибка: не удалось обновить товар.';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Редактировать товар</title>
    <link rel="stylesheet" href="../src/css/admin.css">
    <style>
        /* ... предыдущие стили ... */
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
            background: #f9f9f9;
            max-width: 800px;
            margin: 0 auto;
        }
        h2 {
            color: #333;
            margin-bottom: 20px;
        }
        .error {
            color: #d32f2f;
            background: #ffebee;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 15px;
        }
        form {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        label {
            display: block;
            margin: 12px 0 6px;
            font-weight: bold;
            color: #555;
        }
        input[type="text"],
        input[type="number"],
        select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }
        .images-current {
            margin: 20px 0;
        }
        .image-item {
            display: inline-flex;
            flex-direction: column;
            align-items: center;
            margin-right: 15px;
            margin-bottom: 15px;
        }
        .image-item img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border: 1px solid #eee;
            border-radius: 4px;
        }
        .image-item label {
            margin-top: 5px;
            font-weight: normal;
            font-size: 12px;
            color: #666;
        }
        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        button {
            background: #ff6b35;
            color: white;
            border: none;
            padding: 12px 24px;
            font-size: 16px;
            border-radius: 6px;
            cursor: pointer;
            margin-top: 20px;
        }
        button:hover {
            background: #e55a2b;
        }
        .back-link {
            display: inline-block;
            margin-top: 15px;
            color: #1e88e5;
            text-decoration: none;
        }
    </style>
</head>
<body>
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
    <h2>Редактировать товар</h2>

    <?php if (isset($error)): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="section" value="<?= htmlspecialchars($section) ?>">

        <!-- ... остальные поля (название, бренд и т.д.) без изменений ... -->
        <label for="title">Название *</label>
        <input type="text" id="title" name="title" value="<?= htmlspecialchars($product['title']) ?>" required>

        <label for="brand">Бренд *</label>
        <input type="text" id="brand" name="brand" value="<?= htmlspecialchars($product['brand']) ?>" required>

        <label for="price">Цена *</label>
        <input type="number" id="price" name="price" value="<?= $product['price'] ?>" min="0" required>

        <label for="category">Категория *</label>
        <select id="category" name="category" required>
            <option value="">Выберите</option>
            <option value="верхняя одежда" <?= $product['category'] === 'верхняя одежда' ? 'selected' : '' ?>>Верхняя одежда</option>
            <option value="штаны" <?= $product['category'] === 'штаны' ? 'selected' : '' ?>>Штаны</option>
            <option value="шапка" <?= $product['category'] === 'шапка' ? 'selected' : '' ?>>Шапка</option>
            <option value="очки" <?= $product['category'] === 'очки' ? 'selected' : '' ?>>Очки</option>
            <option value="термо бельё" <?= $product['category'] === 'термо бельё' ? 'selected' : '' ?>>Термо бельё</option>
            <option value="ветровки" <?= $product['category'] === 'ветровки' ? 'selected' : '' ?>>Ветровки</option>
        </select>

        <!-- <label for="size">Размер *</label>
        <select id="size" name="size" required>
            <option value="">Выберите</option>
            <option value="XS" <?= $product['size'] === 'XS' ? 'selected' : '' ?>>XS</option>
            <option value="S" <?= $product['size'] === 'S' ? 'selected' : '' ?>>S</option>
            <option value="M" <?= $product['size'] === 'M' ? 'selected' : '' ?>>M</option>
            <option value="L" <?= $product['size'] === 'L' ? 'selected' : '' ?>>L</option>
            <option value="XL" <?= $product['size'] === 'XL' ? 'selected' : '' ?>>XL</option>
            <option value="2XL" <?= $product['size'] === '2XL' ? 'selected' : '' ?>>2XL</option>
        </select> -->

        <label for="color">Цвет *</label>
        <select id="color" name="color" required>
            <option value="">Выберите</option>
            <option value="черный" <?= $product['color'] === 'черный' ? 'selected' : '' ?>>Черный</option>
            <option value="белый" <?= $product['color'] === 'белый' ? 'selected' : '' ?>>Белый</option>
            <option value="серый" <?= $product['color'] === 'серый' ? 'selected' : '' ?>>Серый</option>
            <option value="синий" <?= $product['color'] === 'синий' ? 'selected' : '' ?>>Синий</option>
            <option value="красный" <?= $product['color'] === 'красный' ? 'selected' : '' ?>>Красный</option>
        </select>

        <label>
            <div class="checkbox-group">
                <input type="checkbox" name="is_new" <?= $product['isNew'] ? 'checked' : '' ?>>
                <span>Новинка</span>
            </div>
        </label>

        <label for="discount">Скидка (%)</label>
        <input type="number" id="discount" name="discount" min="0" max="100" value="<?= $product['discount'] ?>">

        <!-- Текущие изображения с возможностью удаления -->
        <div class="images-current">
            <label>Текущие изображения (отметьте для удаления):</label>
            <?php foreach ($product['images'] as $index => $img): ?>
                <div class="image-item">
                    <img src="../<?= htmlspecialchars($img) ?>" alt="Изображение <?= $index + 1 ?>">
                    <label>
                        <input type="checkbox" name="delete_image_<?= $index ?>" value="1">
                        Удалить
                    </label>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Новые изображения -->
        <label>Добавить новое изображение 1</label>
        <input type="file" name="image1" accept="image/*">

        <label>Добавить новое изображение 2</label>
        <input type="file" name="image2" accept="image/*">

        <label>Добавить новое изображение 3</label>
        <input type="file" name="image3" accept="image/*">

        <label>Добавить новое изображение 4</label>
        <input type="file" name="image4" accept="image/*">

        <button type="submit">Сохранить изменения</button>
        <a href="manage-products.php" class="back-link">← Отмена</a>
    </form>
</body>
</html>