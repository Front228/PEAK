<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $brand = trim($_POST['brand'] ?? '');
    $price = intval($_POST['price'] ?? 0);
    $category = trim($_POST['category'] ?? '');
    $color = trim($_POST['color'] ?? '');
    $isNew = isset($_POST['is_new']) ? 1 : 0;
    $discount = intval($_POST['discount'] ?? 0);
    $section = trim($_POST['section'] ?? '');
    $article = str_pad(rand(10000000,99999999), 8, '0', STR_PAD_LEFT);

    // Категории, где размер ОБЯЗАТЕЛЕН
    $sizeRequiredCategories = [
        'верхняя одежда',
        'штаны',
        'термо бельё',
        'ветровки',
        'флисовая одежда'
    ];

    $sizeRequired = in_array($category, $sizeRequiredCategories);
    $size = '';

    if ($sizeRequired) {
        $sizes = $_POST['sizes'] ?? [];
        if (empty($sizes)) {
            $error = 'Выберите хотя бы один размер';
        } else {
            $sizes = array_map('trim', $sizes);
            $size = implode(', ', $sizes);
        }
    } else {
        // Для очков, шапок и т.д. — размер не нужен
        $size = 'N/A';
    }

    // === ВАЛИДАЦИЯ ===
    $allowedSections = ['men', 'women', 'kids', 'gear', 'sales'];
    if (!$title || !$brand || !$price || !in_array($section, $allowedSections)) {
        $error = 'Заполните все обязательные поля';
    } elseif ($sizeRequired && empty($sizes)) {
        // Ошибка уже установлена выше
    } else {
        $images = [];

        // Загрузка изображений
        $uploadDir = __DIR__ . '/../public/image/product/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        for ($i = 1; $i <= 4; $i++) {
            if (isset($_FILES["image$i"]) && $_FILES["image$i"]['error'] === UPLOAD_ERR_OK) {
                $tmpName = $_FILES["image$i"]['tmp_name'];
                $fileName = basename($_FILES["image$i"]['name']);
                $filePath = $uploadDir . $fileName;

                if (move_uploaded_file($tmpName, $filePath)) {
                    $images[] = "/public/image/product/" . $fileName;
                }
            }
        }

        if (empty($images)) {
            $error = 'Добавьте хотя бы одно изображение';
        } else {
            // Выбор файла JSON
            $sectionFiles = [
                'men' => 'men.json',
                'women' => 'women.json',
                'kids' => 'kids.json',
                'gear' => 'gear.json',
                'sales' => 'sales.json'
            ];

            $dataFile = __DIR__ . '/../data/' . $sectionFiles[$section];
            
            if (!file_exists($dataFile)) {
                file_put_contents($dataFile, '[]');
            }

            $products = json_decode(file_get_contents($dataFile), true) ?: [];
            $newId = count($products) > 0 ? max(array_column($products, 'id')) + 1 : 1;

            $product = [
                'id' => $newId,
                'article' => $article,
                'title' => $title,
                'brand' => $brand,
                'price' => $price,
                'images' => $images,
                'category' => $category,
                'size' => $size,
                'color' => $color,
                'isNew' => (bool)$isNew,
                'discount' => $discount,
                'section' => $section
            ];

            $products[] = $product;
            file_put_contents($dataFile, json_encode($products, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

            $success = 'Товар успешно добавлен в раздел "' . htmlspecialchars($section) . '"!';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Добавить товар</title>
    <link rel="stylesheet" href="../src/css/admin.css">
    <style>
        h2{
            padding-bottom:20px;
        }
        body { font-family: Arial, sans-serif; padding: 20px; }
        form { max-width: 600px; margin: 0 auto; }
        label { display: block; margin: 10px 0 5px; }
        input, select, textarea { width: 100%; padding: 8px; margin-bottom: 10px; }
        button { padding: 10px 20px; background: #ff6b35; color: white; border: none; cursor: pointer; }
        .error { color: red; }
        .success { color: green; }
        .size-checkboxes { display: flex; flex-wrap: wrap; gap: 10px; margin-bottom: 10px; }
        .size-checkboxes label { display: flex; align-items: center; gap: 5px; cursor: pointer; }
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
    <h2>Добавить товар</h2>

    <?php if (isset($error)): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if (isset($success)): ?>
        <div class="success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">

        <label>Раздел *</label>
        <select name="section" required>
            <option value="">Выберите раздел</option>
            <option value="men">Мужское</option>
            <option value="women">Женское</option>
            <option value="kids">Детское</option>
            <option value="gear">Снаряжение</option>
            <option value="sales">Акции</option>
        </select>

        <label>Название *</label>
        <input type="text" name="title" required>

        <label>Бренд *</label>
        <input type="text" name="brand" required>

        <label>Цена *</label>
        <input type="number" name="price" min="0" required>

        <label>Категория *</label>
        <select name="category" required id="categorySelect">
            <option value="">Выберите</option>
            <option value="верхняя одежда">Верхняя одежда</option>
            <option value="штаны">Штаны</option>
            <option value="шапка">Шапка</option>
            <option value="очки">Очки</option>
            <option value="термо бельё">Термо бельё</option>
            <option value="ветровки">Ветровки</option>
            <option value="флисовая одежда">Флисовая одежда</option>
            <option value="обувь">Обувь</option>
            <option value="рюкзак">рюкзак</option>
            <option value="лыжи">Лыжи</option>
            <option value="сноуборд">Сноуборд</option>
            <option value="шлема">шлема</option>
            <option value="маски">Маски</option>
            <option value="Спальные мешки">Спальные мешки</option>
            <option value="Туристические коврики">Туристические коврики</option>
        </select>

        <!-- Блок размеров (появляется только если нужен) -->
        <div id="sizeBlock">
            <label>Размеры *</label>
            <div class="size-checkboxes">
                <label><input type="checkbox" name="sizes[]" value="XS"> XS</label>
                <label><input type="checkbox" name="sizes[]" value="S"> S</label>
                <label><input type="checkbox" name="sizes[]" value="M"> M</label>
                <label><input type="checkbox" name="sizes[]" value="L"> L</label>
                <label><input type="checkbox" name="sizes[]" value="XL"> XL</label>
                <label><input type="checkbox" name="sizes[]" value="2XL"> 2XL</label>
            </div>
        </div>

        <label>Цвет *</label>
        <select name="color" required>
            <option value="">Выберите</option>
            <option value="черный">Черный</option>
            <option value="белый">Белый</option>
            <option value="серый">Серый</option>
            <option value="синий">Синий</option>
            <option value="желтый">Желтый</option>
            <option value="оранжевая">Оранжевая</option>
            <option value="камуфляж">Камуфляж</option>
            <option value="красный">Красный</option>
            <option value="зеленый">Зеленый</option>
            <option value="фиолетовый">Фиолетовый</option>
            <option value="коричневый">Коричневый</option>
        </select>

        <label>Новинка</label>
        <input type="checkbox" name="is_new">

        <label>Скидка (%)</label>
        <input type="number" name="discount" min="0" max="100" value="0">

        <label>Изображение 1 *</label>
        <input type="file" name="image1" accept="image/*" required>

        <label>Изображение 2</label>
        <input type="file" name="image2" accept="image/*">

        <label>Изображение 3</label>
        <input type="file" name="image3" accept="image/*">

        <label>Изображение 4</label>
        <input type="file" name="image4" accept="image/*">

        <button type="submit">Добавить товар</button>
    </form>

    <br><a href="../index.php">← Назад к каталогу</a>

    <!-- Скрипт для скрытия/показа блока размеров -->
    <script>
        document.getElementById('categorySelect').addEventListener('change', function() {
            const sizeBlock = document.getElementById('sizeBlock');
            const category = this.value;
            const sizeRequired = [
                'верхняя одежда',
                'штаны',
                'термо бельё',
                'ветровки',
                'флисовая одежда'
            ].includes(category);
            
            sizeBlock.style.display = sizeRequired ? 'block' : 'none';
            
            // Если скрываем — снимаем выделение чекбоксов
            if (!sizeRequired) {
                document.querySelectorAll('#sizeBlock input[type="checkbox"]').forEach(cb => cb.checked = false);
            }
        });

        // Инициализация при загрузке
        document.getElementById('categorySelect').dispatchEvent(new Event('change'));
    </script>
</body>
</html>