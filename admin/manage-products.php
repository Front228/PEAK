<?php

session_start(); // ← правильно!
require_once '../includes/config.php'; // или путь к твоему config

// Защита админки
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../src/php/auth/login.php');
    exit;
}



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
    <link rel="stylesheet" href="../src/css/style.css">
    <link rel="stylesheet" href="../src/css/media.css">
    <script src="../src/js/bugerMenu.js"></script>
</head>
<body>
    <header class="main-header" id="upPage">
            <div class="haeder_nav">
                <div class="nav_logo">
                    <a href="../index.php">
                        <svg width="85" height="60" viewBox="0 0 65 36" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M1.276 21.328C1.00933 21.328 0.876 21.1947 0.876 20.928V0.4C0.876 0.133333 1.00933 0 1.276 0H10.444C10.7 0 10.9133 0.0853335 11.084 0.256001L14.284 3.456C14.4653 3.63733 14.556 3.85067 14.556 4.096V10.336C14.556 10.592 14.4653 10.8053 14.284 10.976L11.084 14.176C10.9133 14.3573 10.7 14.448 10.444 14.448H6.62V20.928C6.62 21.1947 6.48667 21.328 6.22 21.328H1.276ZM7.564 9.408C8.31067 9.408 8.83867 9.248 9.148 8.928C9.45733 8.59733 9.612 8.02667 9.612 7.216C9.612 6.40533 9.45733 5.83467 9.148 5.504C8.83867 5.17333 8.31067 5.008 7.564 5.008C6.828 5.008 6.30533 5.17333 5.996 5.504C5.68667 5.83467 5.532 6.40533 5.532 7.216C5.532 8.02667 5.68667 8.59733 5.996 8.928C6.30533 9.248 6.828 9.408 7.564 9.408Z" fill="black"/>
                            <path d="M17.151 21.328C16.8843 21.328 16.751 21.1947 16.751 20.928V0.4C16.751 0.133333 16.8843 0 17.151 0H28.287C28.5537 0 28.687 0.133333 28.687 0.4V4.528C28.687 4.79467 28.5537 4.928 28.287 4.928H22.495V8.464H27.919C28.1857 8.464 28.319 8.59733 28.319 8.864V12.464C28.319 12.7307 28.1857 12.864 27.919 12.864H22.495V16.4H28.287C28.5537 16.4 28.687 16.5333 28.687 16.8V20.928C28.687 21.1947 28.5537 21.328 28.287 21.328H17.151Z" fill="black"/>
                            <path d="M32.071 21.328C31.8043 21.328 31.671 21.1947 31.671 20.928V6.096C31.671 5.94667 31.7243 5.78667 31.831 5.616L35.623 0.24C35.7403 0.0799999 35.911 0 36.135 0H41.559C41.7937 0 41.9643 0.0799999 42.071 0.24L45.879 5.616C45.9857 5.76533 46.039 5.92533 46.039 6.096V20.928C46.039 21.1947 45.9057 21.328 45.639 21.328H40.695C40.4283 21.328 40.295 21.1947 40.295 20.928V14.384H37.415V20.928C37.415 21.1947 37.2817 21.328 37.015 21.328H32.071ZM38.855 10.288C39.6763 10.288 40.247 10.096 40.567 9.712C40.8977 9.31733 41.063 8.62933 41.063 7.648C41.063 6.66667 40.8977 5.984 40.567 5.6C40.247 5.216 39.6763 5.024 38.855 5.024C38.0443 5.024 37.4737 5.216 37.143 5.6C36.8123 5.984 36.647 6.66667 36.647 7.648C36.647 8.62933 36.8123 9.31733 37.143 9.712C37.4737 10.096 38.0443 10.288 38.855 10.288Z" fill="black"/>
                            <path d="M49.4323 21.328C49.1656 21.328 49.0322 21.1947 49.0322 20.928V0.4C49.0322 0.133333 49.1656 0 49.4323 0H54.3763C54.6429 0 54.7762 0.133333 54.7762 0.4V8.192H56.2163L57.2562 4.208L58.1043 0.208C58.1256 0.133334 58.1789 0.0800006 58.2643 0.0480003C58.3603 0.0160001 58.4776 0 58.6162 0H63.4803C63.7469 0 63.8802 0.133333 63.8802 0.4V3.056C63.8802 3.17333 63.8589 3.28 63.8162 3.376C63.7842 3.472 63.7576 3.568 63.7363 3.664L61.6562 8.512L60.6483 10.416L61.6883 12.32L64.1523 17.664C64.1949 17.7387 64.2269 17.824 64.2483 17.92C64.2696 18.0053 64.2803 18.1227 64.2803 18.272V20.928C64.2803 21.1947 64.1469 21.328 63.8802 21.328H58.8882C58.8136 21.328 58.7176 21.3173 58.6003 21.296C58.4936 21.264 58.4296 21.1947 58.4082 21.088L57.2882 16.672L56.3923 13.808H54.7762V20.928C54.7762 21.1947 54.6429 21.328 54.3763 21.328H49.4323Z" fill="black"/>
                            <path d="M0.1375 35.328C0.0458333 35.328 0 35.2822 0 35.1905V28.134C0 28.0423 0.0458333 27.9965 0.1375 27.9965H3.179C3.267 27.9965 3.34033 28.0258 3.399 28.0845L4.499 29.1845C4.56133 29.2468 4.5925 29.3202 4.5925 29.4045V31.302C4.5925 31.39 4.56133 31.4633 4.499 31.522L3.399 32.622C3.34033 32.6843 3.267 32.7155 3.179 32.7155H1.8315V35.1905C1.8315 35.2822 1.78567 35.328 1.694 35.328H0.1375ZM2.244 31.2195C2.54833 31.2195 2.7555 31.159 2.8655 31.038C2.97917 30.917 3.036 30.6878 3.036 30.3505C3.036 30.0132 2.97917 29.784 2.8655 29.663C2.7555 29.542 2.54833 29.4815 2.244 29.4815C1.94333 29.4815 1.73617 29.542 1.6225 29.663C1.5125 29.784 1.4575 30.0132 1.4575 30.3505C1.4575 30.6878 1.5125 30.917 1.6225 31.038C1.73617 31.159 1.94333 31.2195 2.244 31.2195Z" fill="black"/>
                            <path d="M5.59453 35.328C5.50286 35.328 5.45703 35.2822 5.45703 35.1905V28.134C5.45703 28.0423 5.50286 27.9965 5.59453 27.9965H9.42803C9.5197 27.9965 9.56553 28.0423 9.56553 28.134V29.322C9.56553 29.4137 9.5197 29.4595 9.42803 29.4595H7.28853V31.0215H9.24103C9.3327 31.0215 9.37853 31.0673 9.37853 31.159V32.1655C9.37853 32.2572 9.3327 32.303 9.24103 32.303H7.28853V33.8595H9.42803C9.5197 33.8595 9.56553 33.9053 9.56553 33.997V35.1905C9.56553 35.2822 9.5197 35.328 9.42803 35.328H5.59453Z" fill="black"/>
                            <path d="M10.7508 35.328C10.6591 35.328 10.6133 35.2822 10.6133 35.1905V28.134C10.6133 28.0423 10.6591 27.9965 10.7508 27.9965H13.7318C13.8198 27.9965 13.8931 28.0203 13.9518 28.068L15.2168 29.322C15.2571 29.3587 15.2809 29.3898 15.2883 29.4155C15.2993 29.4412 15.3048 29.4833 15.3048 29.542V31.049C15.3048 31.0967 15.2864 31.1388 15.2498 31.1755L14.6448 31.764L14.1333 32.204L14.7383 33.007L15.3598 34.338C15.3744 34.371 15.3854 34.404 15.3928 34.437C15.4038 34.4663 15.4093 34.503 15.4093 34.547V35.1905C15.4093 35.2822 15.3634 35.328 15.2718 35.328H13.8198C13.7391 35.328 13.6841 35.2913 13.6548 35.218L12.7693 32.644H12.3568V35.1905C12.3568 35.2822 12.3109 35.328 12.2193 35.328H10.7508ZM12.9618 31.2965C13.2661 31.2965 13.4733 31.2342 13.5833 31.1095C13.6933 30.9812 13.7483 30.7428 13.7483 30.3945C13.7483 30.0425 13.6933 29.8042 13.5833 29.6795C13.4733 29.5512 13.2661 29.487 12.9618 29.487C12.6611 29.487 12.4539 29.5512 12.3403 29.6795C12.2266 29.8042 12.1698 30.0425 12.1698 30.3945C12.1698 30.7428 12.2266 30.9812 12.3403 31.1095C12.4539 31.2342 12.6611 31.2965 12.9618 31.2965Z" fill="black"/>
                            <path d="M16.4227 35.328C16.331 35.328 16.2852 35.2822 16.2852 35.1905V28.134C16.2852 28.0423 16.331 27.9965 16.4227 27.9965H20.1902C20.2818 27.9965 20.3277 28.0423 20.3277 28.134V29.322C20.3277 29.4137 20.2818 29.4595 20.1902 29.4595H18.1167V31.2965H19.9152C20.0068 31.2965 20.0527 31.3423 20.0527 31.434V32.622C20.0527 32.7137 20.0068 32.7595 19.9152 32.7595H18.1167V35.1905C18.1167 35.2822 18.0708 35.328 17.9792 35.328H16.4227Z" fill="black"/>
                            <path d="M22.6186 35.328C22.5342 35.328 22.4609 35.2968 22.3986 35.2345L21.2986 34.1345C21.2362 34.0758 21.2051 34.0025 21.2051 33.9145V29.4045C21.2051 29.3202 21.2362 29.2468 21.2986 29.1845L22.3986 28.0845C22.4609 28.0258 22.5342 27.9965 22.6186 27.9965H24.7801C24.8681 27.9965 24.9414 28.0258 25.0001 28.0845L26.1001 29.1845C26.1624 29.2468 26.1936 29.3202 26.1936 29.4045V33.9145C26.1936 34.0025 26.1624 34.0758 26.1001 34.1345L25.0001 35.2345C24.9414 35.2968 24.8681 35.328 24.7801 35.328H22.6186ZM23.2676 33.8595H24.1311C24.2851 33.8595 24.3621 33.7568 24.3621 33.5515V29.773C24.3621 29.564 24.2851 29.4595 24.1311 29.4595H23.2676C23.1172 29.4595 23.0421 29.564 23.0421 29.773V33.5515C23.0421 33.7568 23.1172 33.8595 23.2676 33.8595Z" fill="black"/>
                            <path d="M27.6482 35.328C27.5566 35.328 27.5107 35.2822 27.5107 35.1905V28.134C27.5107 28.0423 27.5566 27.9965 27.6482 27.9965H30.6292C30.7172 27.9965 30.7906 28.0203 30.8492 28.068L32.1142 29.322C32.1546 29.3587 32.1784 29.3898 32.1857 29.4155C32.1967 29.4412 32.2022 29.4833 32.2022 29.542V31.049C32.2022 31.0967 32.1839 31.1388 32.1472 31.1755L31.5422 31.764L31.0307 32.204L31.6357 33.007L32.2572 34.338C32.2719 34.371 32.2829 34.404 32.2902 34.437C32.3012 34.4663 32.3067 34.503 32.3067 34.547V35.1905C32.3067 35.2822 32.2609 35.328 32.1692 35.328H30.7172C30.6366 35.328 30.5816 35.2913 30.5522 35.218L29.6667 32.644H29.2542V35.1905C29.2542 35.2822 29.2084 35.328 29.1167 35.328H27.6482ZM29.8592 31.2965C30.1636 31.2965 30.3707 31.2342 30.4807 31.1095C30.5907 30.9812 30.6457 30.7428 30.6457 30.3945C30.6457 30.0425 30.5907 29.8042 30.4807 29.6795C30.3707 29.5512 30.1636 29.487 29.8592 29.487C29.5586 29.487 29.3514 29.5512 29.2377 29.6795C29.1241 29.8042 29.0672 30.0425 29.0672 30.3945C29.0672 30.7428 29.1241 30.9812 29.2377 31.1095C29.3514 31.2342 29.5586 31.2965 29.8592 31.2965Z" fill="black"/>
                            <path d="M33.3201 35.328C33.2285 35.328 33.1826 35.2822 33.1826 35.1905V29.223C33.1826 29.1387 33.2138 29.0653 33.2761 29.003L34.1891 28.0845C34.2515 28.0258 34.3248 27.9965 34.4091 27.9965H35.6686C35.753 27.9965 35.8263 28.0258 35.8886 28.0845L36.8126 29.014H37.0711L37.9951 28.0845C38.0575 28.0258 38.1308 27.9965 38.2151 27.9965H39.4691C39.5571 27.9965 39.6305 28.0258 39.6891 28.0845L40.6076 29.003C40.67 29.0653 40.7011 29.1387 40.7011 29.223V35.1905C40.7011 35.2822 40.6553 35.328 40.5636 35.328H39.0016C38.91 35.328 38.8641 35.2822 38.8641 35.1905V29.4595H37.8576V35.1905C37.8576 35.2822 37.8118 35.328 37.7201 35.328H36.1636C36.0719 35.328 36.0261 35.2822 36.0261 35.1905V29.4595H35.0141V35.1905C35.0141 35.2822 34.9683 35.328 34.8766 35.328H33.3201Z" fill="black"/>
                            <path d="M42.1224 35.328C42.0308 35.328 41.9849 35.2822 41.9849 35.1905V30.092C41.9849 30.0443 42.0033 29.9893 42.0399 29.927L43.3434 28.079C43.3838 28.024 43.4424 27.9965 43.5194 27.9965H45.3399C45.4206 27.9965 45.4793 28.024 45.5159 28.079L46.8249 29.927C46.8616 29.9783 46.8799 30.0333 46.8799 30.092V35.1905C46.8799 35.2822 46.8341 35.328 46.7424 35.328H45.1859C45.0943 35.328 45.0484 35.2822 45.0484 35.1905V32.853H43.8219V35.1905C43.8219 35.2822 43.7761 35.328 43.6844 35.328H42.1224ZM44.4324 31.6705C44.7698 31.6705 44.9971 31.5972 45.1144 31.4505C45.2354 31.3002 45.2959 31.016 45.2959 30.598C45.2959 30.18 45.2354 29.8977 45.1144 29.751C44.9971 29.6007 44.7698 29.5255 44.4324 29.5255C44.0988 29.5255 43.8714 29.6007 43.7504 29.751C43.6331 29.8977 43.5744 30.18 43.5744 30.598C43.5744 31.016 43.6331 31.3002 43.7504 31.4505C43.8714 31.5972 44.0988 31.6705 44.4324 31.6705Z" fill="black"/>
                            <path d="M48.1014 35.328C48.0097 35.328 47.9639 35.2822 47.9639 35.1905V28.134C47.9639 28.0423 48.0097 27.9965 48.1014 27.9965H51.2254C51.3134 27.9965 51.3867 28.0258 51.4454 28.0845L52.5454 29.1845C52.6077 29.2468 52.6389 29.3202 52.6389 29.4045V35.1905C52.6389 35.2822 52.593 35.328 52.5014 35.328H50.9449C50.8532 35.328 50.8074 35.2822 50.8074 35.1905V29.4595H49.7954V35.1905C49.7954 35.2822 49.7495 35.328 49.6579 35.328H48.1014Z" fill="black"/>
                            <path d="M56.037 35.328C55.9527 35.328 55.8793 35.3042 55.817 35.2565L54.9205 34.437L54.013 33.2545C53.9727 33.2032 53.947 33.172 53.936 33.161C53.925 33.1463 53.9195 33.1133 53.9195 33.062V30.257C53.9195 30.2057 53.925 30.1727 53.936 30.158C53.9507 30.1433 53.9763 30.1122 54.013 30.0645L54.8545 28.9755L55.817 28.068C55.8573 28.035 55.8885 28.0148 55.9105 28.0075C55.9362 28.0002 55.9783 27.9965 56.037 27.9965H57.3955C57.4872 27.9965 57.533 28.0423 57.533 28.134V29.014C57.533 29.0433 57.5293 29.0745 57.522 29.1075C57.5183 29.1368 57.5 29.1625 57.467 29.1845L56.433 29.894L55.7565 30.763V32.5615L56.444 33.4305L57.467 34.1345C57.4963 34.1528 57.5147 34.1748 57.522 34.2005C57.5293 34.2262 57.533 34.2537 57.533 34.283V35.1905C57.533 35.2822 57.4872 35.328 57.3955 35.328H56.037Z" fill="black"/>
                            <path d="M58.7361 35.328C58.6445 35.328 58.5986 35.2822 58.5986 35.1905V28.134C58.5986 28.0423 58.6445 27.9965 58.7361 27.9965H62.5696C62.6613 27.9965 62.7071 28.0423 62.7071 28.134V29.322C62.7071 29.4137 62.6613 29.4595 62.5696 29.4595H60.4301V31.0215H62.3826C62.4743 31.0215 62.5201 31.0673 62.5201 31.159V32.1655C62.5201 32.2572 62.4743 32.303 62.3826 32.303H60.4301V33.8595H62.5696C62.6613 33.8595 62.7071 33.9053 62.7071 33.997V35.1905C62.7071 35.2822 62.6613 35.328 62.5696 35.328H58.7361Z" fill="black"/>
                        </svg> 
                    </a>
                </div>
                <nav class="navbar">
                <ul class="navbar_list">
                    <li class="navbar_item"><a href="/block/men.php">акции</a></li>
                    <li class="navbar_item"><a href="/block/women.php">женское</a></li>
                    <li class="navbar_item"><a href="/block/men.php">мужское</a></li>
                    <li class="navbar_item"><a href="/block/kids.php">детское</a></li>
                    <li class="navbar_item"><a href="/block/equipment.php">аксессуары</a></li>
                    
                    <?php if (isset($_SESSION['user_id']) && isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                        <li class="navbar_item"><a href="../admin/manage-products.php">Товары</a></li>
                        <li class="navbar_item"><a href="../admin/add-product.php">Добавить товар</a></li>
                        <li class="navbar_item"><a href="../admin/orders.php">Трекер заказов</a></li>
                    <?php endif; ?>
                </ul>
                </nav>

                <div class="register">
                    <a href="../src/php/favorites.php" class="icon-wrapper">
                        <img src="../public/icon/favourite.svg" alt="Избраное" class="favicon">
                        <span class="badge" id="favorites-count"></span>
                    </a>
                    <a href="../src/php/cart.php" class="icon-wrapper">
                        <img src="../public/icon/shopping-cart.svg" alt="Корзина" class="shopping_cart">
                        <span class="badge" id="cart-count"></span>
                    </a>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <a href="../src/php/auth/logout.php" >
                        <img src="../public/icon/exit_profile.svg" alt="Выход" class="logout_image">
                        </a>
                    <?php else: ?>
                        <a href="../src/php/auth/login.php" >
                        <img src="../public/icon/profile.svg" alt="Вход" class="sign_image"></a>
                        </a>
                    <?php endif; ?>
                </div>
                <div class="mobile">
                    <div class="mobile-register">
                        <a href="../src/php/favorites.php"  class="icon-wrapper">
                            <img src="../public/icon/favourite.svg" alt="Избраное" class="favicon">
                            <span class="badge favorites-count-mobile" ></span>
                        </a>
                        <a href="../src/php/cart.php" class="icon-wrapper">
                            <img src="../public/icon/shopping-cart.svg" alt="Корзина" class="shopping_cart">
                            <span class="badge cart-count-mobile"></span>
                        </a>
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <a href="../src/php/auth/logout.php" >
                            <img src="../public/icon/exit_profile.svg" alt="Выход" class="logout_image">
                            </a>
                        <?php else: ?>
                            <a href="../src/php/auth/login.php" >
                            <img src="../public/icon/profile.svg" alt="Вход" class="sign_image"></a>
                            </a>
                        <?php endif; ?>
                    </div>
                <div class="burger_menu">
                    <img src="../public/icon/buger-menu.png" alt="menu" width="50" height="50">
                </div>
                <nav class="nav_mobile">
                    
                    <div class="closeMenu"><img src="../public/icon/closeMenu.png" alt="close" class="close-menu"></div>
                    <div class="mobileLogo">
                    <a href="index.php"><img src="../public/icon/PEAK WHITE.svg" alt="PEAK PERFORMANCE" width="85" height="60"></a>
                    </div>
                    <ul class="navbar_mibile-list">
                    <li class="navbar_mobile-item"><a href="/block/men.php">акции</a></li>
                    <li class="navbar_mobile-item"><a href="/block/women.php">женское</a></li>
                    <li class="navbar_mobile-item"><a href="/block/men.php">мужское</a></li>
                    <li class="navbar_mobile-item"><a href="/block/kids.php">детское</a></li>
                    <li class="navbar_mobile-item"><a href="/block/equipment.php">аксессуары</a></li>
                    
                    <?php if (isset($_SESSION['user_id']) && isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                        <li class="navbar_mobile-item"><a href="admin/manage-products.php">Товары</a></li>
                        <li class="navbar_mobile-item"><a href="admin/add-product.php">Добавить товар</a></li>
                        <li class="navbar_mobile-item"><a href="admin/orders.php">Трекер заказов</a></li>
                    <?php endif; ?>
                    </ul>
                </nav>
                </div>
            </div>
        </header>
    <h1 class="admin-title">Управление товарами</h1>

<?php if (isset($success)): ?>
    <div class="admin-message admin-message--success"><?= htmlspecialchars($success) ?></div>
<?php endif; ?>
<?php if (isset($error)): ?>
    <div class="admin-message admin-message--error"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<!-- Статистика по разделам -->
<div class="admin-stats" id="admin-stats">
    <?php foreach ($sectionCounts as $section => $count): ?>
        <div class="admin-stat-card" data-section="<?= htmlspecialchars($section) ?>">
            <div class="stat-number"><?= $count ?></div>
            <div class="stat-label"><?= htmlspecialchars($sectionLabels[$section]) ?></div>
        </div>
    <?php endforeach; ?>
    <div class="admin-stat-card admin-stat-card--total" data-section="all">
        <div class="stat-number"><?= array_sum($sectionCounts) ?></div>
        <div class="stat-label">Всего</div>
    </div>
</div>

<!-- Управление -->
<div class="admin-controls">
    <input type="text" id="search-input" class="admin-search" placeholder="Поиск по названию...">
    <a href="add-product.php" class="admin-btn admin-btn--add">Добавить товар</a>
</div>

<!-- Таблица товаров -->
<?php if (empty($allProducts)): ?>
    <p class="admin-empty">Нет товаров. <a href="add-product.php">Добавить товар</a></p>
<?php else: ?>
    <table class="admin-table" id="products-table">
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
            <tr 
                data-title="<?= htmlspecialchars(strtolower($product['title'])) ?>"
                data-section="<?= htmlspecialchars($product['section']) ?>"
                data-category="<?= htmlspecialchars($product['category']) ?>"
            >
                <td><?= htmlspecialchars($product['id']) ?></td>
                <td class="admin-images-preview">
                    <?php foreach (array_slice($product['images'], 0, 3) as $img): ?>
                        <img src="../<?= htmlspecialchars($img) ?>" alt="Изображение">
                    <?php endforeach; ?>
                </td>
                <td><?= htmlspecialchars($product['title']) ?></td>
                <td><?= htmlspecialchars($product['brand']) ?></td>
                <td><?= number_format($product['price'], 0, ',', ' ') ?> ₽</td>
                <td>
                    <span class="admin-section-badge admin-section-<?= htmlspecialchars($product['section']) ?>">
                        <?= htmlspecialchars($sectionLabels[$product['section']]) ?>
                    </span>
                </td>
                <td class="admin-actions">
                    <a href="edit-product.php?section=<?= urlencode($product['section']) ?>&id=<?= $product['id'] ?>" class="admin-btn admin-btn--edit">Редактировать</a>
                    <form method="POST" onsubmit="return confirm('Удалить товар?')" style="display:inline;">
                        <input type="hidden" name="section" value="<?= htmlspecialchars($product['section']) ?>">
                        <input type="hidden" name="id" value="<?= $product['id'] ?>">
                        <input type="hidden" name="delete" value="1">
                        <button type="submit" class="admin-btn admin-btn--delete">Удалить</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const statsCards = document.querySelectorAll('.admin-stat-card');
        const tableRows = document.querySelectorAll('#products-table tbody tr');
        const searchInput = document.getElementById('search-input');

        // Фильтрация по категории (клик по карточке)
        statsCards.forEach(card => {
            card.addEventListener('click', function() {
                const section = this.dataset.section;
                
                // Сброс активного состояния
                statsCards.forEach(c => c.classList.remove('active'));
                this.classList.add('active');
                
                // Фильтрация таблицы
                tableRows.forEach(row => {
                    if (section === 'all' || row.dataset.section === section) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            });
        });

        // Поиск по названию
        searchInput?.addEventListener('input', function() {
            const query = this.value.toLowerCase().trim();
            tableRows.forEach(row => {
                const title = row.dataset.title;
                if (query === '' || title.includes(query)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });

        // Активировать "Всего" по умолчанию
        document.querySelector('.admin-stat-card--total')?.classList.add('active');
});
</script>
<script src="../src/js/bugerMenu.js"></script>
</body>
</html>