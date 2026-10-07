<?php
session_start();

/* =====================================================
   DATABASE
===================================================== */

$host = "localhost";
$user = "root";
$pass = "";
$db   = "sportzone";

$conn = new mysqli($host, $user, $pass);

if ($conn->connect_error) {
    die("MySQL қосылмаған! XAMPP-та MySQL -> Start бас.");
}

$conn->query("
    CREATE DATABASE IF NOT EXISTS sportzone
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci
");

$conn->select_db($db);


/* =====================================================
   TABLES
===================================================== */

$conn->query("
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL
)
");

$conn->query("
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    category VARCHAR(100) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    emoji VARCHAR(20) NOT NULL,
    image VARCHAR(500) DEFAULT NULL
)
");


/* =====================================================
   ADD IMAGE COLUMN
===================================================== */

$checkImageColumn = $conn->query("
    SHOW COLUMNS FROM products LIKE 'image'
");

if ($checkImageColumn->num_rows == 0) {

    $conn->query("
        ALTER TABLE products
        ADD COLUMN image VARCHAR(500) DEFAULT NULL
    ");

}


/* =====================================================
   DEFAULT PRODUCTS
===================================================== */

$productsData = [

    [
        "name" => "Футбол добы",
        "category" => "Футбол",
        "description" => "Кәсіби жаттығуларға арналған футбол добы",
        "price" => 18900,
        "emoji" => "⚽",
        "image" => "https://images.unsplash.com/photo-1579952363873-27f3bade9f55?auto=format&fit=crop&w=900&q=80"
    ],

    [
        "name" => "Баскетбол добы",
        "category" => "Баскетбол",
        "description" => "Жоғары сапалы баскетбол добы",
        "price" => 21900,
        "emoji" => "🏀",
        "image" => "https://images.unsplash.com/photo-1546519638-68e109498ffc?auto=format&fit=crop&w=900&q=80"
    ],

    [
        "name" => "Спорттық аяқ киім",
        "category" => "Аяқ киім",
        "description" => "Жүгіруге арналған жеңіл спорттық аяқ киім",
        "price" => 35900,
        "emoji" => "👟",
        "image" => "https://images.unsplash.com/photo-1542291026-7eec264c27ff?auto=format&fit=crop&w=900&q=80"
    ],

    [
        "name" => "Футбол формасы",
        "category" => "Киім",
        "description" => "Жаттығу және ойынға арналған спорттық форма",
        "price" => 24900,
        "emoji" => "👕",
        "image" => "https://avatars.mds.yandex.net/get-mpic/19658723/2a0000019dce3253284ef552b995fc28f10d/orig"
    ],

    [
        "name" => "Қақпашы қолғаптары",
        "category" => "Футбол",
        "description" => "Кәсіби қақпашыларға арналған қолғап",
        "price" => 19900,
        "emoji" => "🧤",
        "image" => "https://images.unsplash.com/photo-1553778263-73a83bab9b0c?auto=format&fit=crop&w=900&q=80"
    ],

    [
        "name" => "Фитнес жабдығы",
        "category" => "Фитнес",
        "description" => "Үй жағдайында жаттығуға арналған жабдық",
        "price" => 29900,
        "emoji" => "🏋️",
        "image" => "https://images.unsplash.com/photo-1581009146145-b5ef050c2e1e?auto=format&fit=crop&w=900&q=80"
    ],

    [
        "name" => "Спорт сөмкесі",
        "category" => "Аксессуар",
        "description" => "Спорттық киім мен жабдықтарға арналған сөмке",
        "price" => 17900,
        "emoji" => "🎒",
        "image" => "https://images.unsplash.com/photo-1553062407-98eeb64c6a62?auto=format&fit=crop&w=900&q=80"
    ],

    [
        "name" => "Жүгіру жейдесі",
        "category" => "Киім",
        "description" => "Жеңіл және ыңғайлы спорттық жейде",
        "price" => 14900,
        "emoji" => "🏃",
        "image" => "https://images.unsplash.com/photo-1552674605-db6ffd4facb5?auto=format&fit=crop&w=900&q=80"
    ]

];


/* =====================================================
   INSERT / UPDATE PRODUCTS
===================================================== */

foreach ($productsData as $p) {

    $checkProduct = $conn->prepare("
        SELECT id
        FROM products
        WHERE name = ?
        LIMIT 1
    ");

    $checkProduct->bind_param(
        "s",
        $p["name"]
    );

    $checkProduct->execute();

    $productResult = $checkProduct->get_result();

    if ($productResult->num_rows > 0) {

        $update = $conn->prepare("
            UPDATE products
            SET
                category = ?,
                description = ?,
                price = ?,
                emoji = ?,
                image = ?
            WHERE name = ?
        ");

        $update->bind_param(
            "ssdsss",
            $p["category"],
            $p["description"],
            $p["price"],
            $p["emoji"],
            $p["image"],
            $p["name"]
        );

        $update->execute();

    } else {

        $insert = $conn->prepare("
            INSERT INTO products
            (name, category, description, price, emoji, image)
            VALUES (?, ?, ?, ?, ?, ?)
        ");

        $insert->bind_param(
            "sssdss",
            $p["name"],
            $p["category"],
            $p["description"],
            $p["price"],
            $p["emoji"],
            $p["image"]
        );

        $insert->execute();
    }
}


/* =====================================================
   LOGOUT
===================================================== */

if (isset($_GET["logout"])) {

    session_destroy();

    header("Location: index.php");

    exit;
}


/* =====================================================
   REGISTER
===================================================== */

$message = "";
$messageType = "";

if (isset($_POST["register"])) {

    $name = trim($_POST["name"]);
    $email = trim($_POST["email"]);
    $password = $_POST["password"];

    if (strlen($password) < 6) {

        $message = "Пароль кемінде 6 таңба болуы керек!";
        $messageType = "error";

    } else {

        $check = $conn->prepare("
            SELECT id
            FROM users
            WHERE email=?
        ");

        $check->bind_param(
            "s",
            $email
        );

        $check->execute();

        $result = $check->get_result();

        if ($result->num_rows > 0) {

            $message = "Бұл email бұрын тіркелген!";
            $messageType = "error";

        } else {

            $hash = password_hash(
                $password,
                PASSWORD_DEFAULT
            );

            $stmt = $conn->prepare("
                INSERT INTO users
                (name,email,password)
                VALUES (?,?,?)
            ");

            $stmt->bind_param(
                "sss",
                $name,
                $email,
                $hash
            );

            if ($stmt->execute()) {

                $message = "Тіркелу сәтті өтті! Енді кіріңіз.";
                $messageType = "success";

            } else {

                $message = "Қате пайда болды!";
                $messageType = "error";
            }
        }
    }
}


/* =====================================================
   LOGIN
===================================================== */

if (isset($_POST["login"])) {

    $email = trim($_POST["email"]);
    $password = $_POST["password"];

    $stmt = $conn->prepare("
        SELECT id,name,password
        FROM users
        WHERE email=?
    ");

    $stmt->bind_param(
        "s",
        $email
    );

    $stmt->execute();

    $result = $stmt->get_result();

    if ($result->num_rows == 1) {

        $userData = $result->fetch_assoc();

        if (
            password_verify(
                $password,
                $userData["password"]
            )
        ) {

            $_SESSION["user_id"] =
                $userData["id"];

            $_SESSION["user_name"] =
                $userData["name"];

            header("Location: index.php");

            exit;

        } else {

            $message = "Пароль дұрыс емес!";
            $messageType = "error";
        }

    } else {

        $message = "Мұндай қолданушы жоқ!";
        $messageType = "error";
    }
}


/* =====================================================
   PAGE
===================================================== */

$page = $_GET["page"] ?? "home";


/* =====================================================
   PRODUCTS
===================================================== */

$products = $conn->query("
    SELECT *
    FROM products
    ORDER BY id DESC
");

?>

<!DOCTYPE html>

<html lang="kk">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>SPORTZONE — Спорт дүкені</title>


<style>

/* =====================================================
   RESET
===================================================== */

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

html {
    scroll-behavior: smooth;
}

body {

    font-family:
    Arial,
    Helvetica,
    sans-serif;

    background: #07111f;

    color: white;
}


/* =====================================================
   HEADER
===================================================== */

header {

    position: sticky;

    top: 0;

    z-index: 1000;

    min-height: 75px;

    display: flex;

    align-items: center;

    justify-content: space-between;

    padding: 15px 7%;

    background:
    rgba(3,10,20,.95);

    backdrop-filter:
    blur(15px);

    border-bottom:
    1px solid
    rgba(255,255,255,.1);
}

.logo {

    font-size: 25px;

    font-weight: 900;

    color: #39ff72;
}

nav {

    display: flex;

    gap: 22px;

    align-items: center;
}

nav a {

    color: white;

    text-decoration: none;

    font-weight: bold;

    transition: .3s;
}

nav a:hover {

    color: #39ff72;
}


/* =====================================================
   HERO — VIDEO BACKGROUND
===================================================== */

.hero {

    min-height: 700px;

    display: flex;

    align-items: center;

    padding: 80px 8%;

    position: relative;

    overflow: hidden;

    background: #07111f;
}


/* 8 СЕКУНДТЫҚ ВИДЕО */

.hero-video {

    position: absolute;

    top: 0;
    left: 0;

    width: 100%;
    height: 100%;

    object-fit: cover;

    z-index: 0;

    pointer-events: none;
}


/* ВИДЕО ҮСТІНДЕГІ ҚАРАҢҒЫ ҚАБАТ */

.hero-overlay {

    position: absolute;

    top: 0;
    left: 0;

    width: 100%;
    height: 100%;

    background:

        linear-gradient(
            90deg,
            rgba(0,0,0,.88),
            rgba(0,0,0,.45)
        );

    z-index: 1;
}


/* HERO МӘТІНІ */

.hero-content {

    max-width: 750px;

    position: relative;

    z-index: 2;
}

.small-title {

    color: #39ff72;

    font-weight: bold;

    letter-spacing: 5px;

    margin-bottom: 20px;
}

.hero h1 {

    font-size:
    clamp(45px,7vw,90px);

    line-height: .95;

    margin-bottom: 30px;
}

.hero h1 span {

    color: #39ff72;
}

.hero p {

    color: #d7dce3;

    font-size: 19px;

    line-height: 1.7;

    margin-bottom: 30px;
}

.button {

    display: inline-block;

    border: none;

    padding: 15px 30px;

    background: #39ff72;

    color: #03100a;

    border-radius: 9px;

    text-decoration: none;

    font-weight: 900;

    cursor: pointer;

    transition: .3s;
}

.button:hover {

    transform:
    translateY(-4px);

    box-shadow:
    0 10px 30px
    rgba(57,255,114,.3);
}


/* =====================================================
   SECTIONS
===================================================== */

section {

    padding: 90px 7%;
}

.section-title {

    text-align: center;

    font-size: 42px;

    margin-bottom: 50px;
}


/* =====================================================
   CATEGORIES
===================================================== */

.categories {

    display: grid;

    grid-template-columns:
    repeat(4,1fr);

    gap: 20px;
}

.category {

    background:
    #0d1b2d;

    border:
    1px solid #20334b;

    border-radius: 16px;

    padding: 35px 20px;

    text-align: center;

    transition: .3s;
}

.category:hover {

    transform:
    translateY(-8px);

    border-color:
    #39ff72;
}

.category-icon {

    font-size: 60px;

    margin-bottom: 15px;
}

.category h3 {

    margin-bottom: 10px;
}

.category p {

    color: #9caabd;
}


/* =====================================================
   PRODUCTS
===================================================== */

.products {

    display: grid;

    grid-template-columns:
    repeat(4,1fr);

    gap: 25px;
}

.product {

    background:
    #0d1b2d;

    border:
    1px solid #20334b;

    border-radius: 16px;

    overflow: hidden;

    transition: .3s;
}

.product:hover {

    transform:
    translateY(-8px);

    border-color:
    #39ff72;
}


/* =====================================================
   PRODUCT IMAGE
===================================================== */

.product-picture {

    height: 230px;

    display: flex;

    align-items: center;

    justify-content: center;

    background:

    linear-gradient(
        135deg,
        #102944,
        #092018
    );

    overflow: hidden;
}

.product-picture img {

    width: 100%;

    height: 100%;

    object-fit: cover;

    display: block;

    transition: .3s;
}

.product:hover
.product-picture img {

    transform:
    scale(1.08);
}

.product-picture span {

    font-size: 110px;

    transition: .3s;
}

.product:hover
.product-picture span {

    transform:
    scale(1.15)
    rotate(-5deg);
}


/* =====================================================
   PRODUCT INFO
===================================================== */

.product-info {

    padding: 20px;
}

.product-category {

    color: #39ff72;

    font-size: 13px;

    font-weight: bold;
}

.product-info h3 {

    margin:
    10px 0;
}

.product-info p {

    color: #a5b1c2;

    line-height: 1.5;

    min-height: 48px;
}

.product-bottom {

    margin-top: 20px;

    display: flex;

    align-items: center;

    justify-content: space-between;
}

.price {

    color: #39ff72;

    font-size: 20px;

    font-weight: bold;
}

.cart-button {

    border: none;

    background: #39ff72;

    width: 45px;

    height: 45px;

    border-radius: 8px;

    cursor: pointer;

    font-size: 20px;
}


/* =====================================================
   AUTH
===================================================== */

.auth-container {

    min-height:
    calc(100vh - 75px);

    display: flex;

    align-items: center;

    justify-content: center;

    padding: 30px;

    background:

    radial-gradient(
        circle at top,
        #164f2c,
        transparent 40%
    ),

    #06101c;
}

.auth-box {

    width: 100%;

    max-width: 450px;

    background:
    #0d1b2d;

    border:
    1px solid #29405a;

    border-radius: 20px;

    padding: 40px;

    box-shadow:
    0 30px 80px
    rgba(0,0,0,.5);
}

.auth-box h2 {

    text-align: center;

    margin-bottom: 25px;
}

.auth-box label {

    display: block;

    margin:
    15px 0 7px;

    color: #ddd;
}

.auth-box input {

    width: 100%;

    padding: 14px;

    border-radius: 8px;

    border:
    1px solid #30445c;

    background:
    #07111f;

    color: white;

    outline: none;

    font-size: 16px;
}

.auth-box input:focus {

    border-color:
    #39ff72;
}

.auth-box button {

    width: 100%;

    margin-top: 25px;
}

.auth-link {

    display: block;

    text-align: center;

    margin-top: 20px;

    color: #39ff72;

    text-decoration: none;
}


/* =====================================================
   ALERT
===================================================== */

.alert {

    padding: 14px;

    border-radius: 8px;

    margin-bottom: 20px;

    text-align: center;
}

.error {

    background:
    rgba(255,50,50,.15);

    color:
    #ff7777;
}

.success {

    background:
    rgba(57,255,114,.12);

    color:
    #39ff72;
}


/* =====================================================
   ABOUT
===================================================== */

.about {

    background:

    linear-gradient(
        135deg,
        #07111f,
        #0b2819
    );
}

.about-content {

    max-width: 850px;

    margin: auto;

    text-align: center;
}

.about h2 {

    font-size: 45px;

    margin-bottom: 25px;
}

.about p {

    color: #b7c1ce;

    line-height: 1.8;

    margin-bottom: 15px;
}


/* =====================================================
   FOOTER
===================================================== */

footer {

    background: #030912;

    padding: 40px 7%;

    display: flex;

    justify-content: space-between;

    color: #8997a9;
}

footer strong {

    color: #39ff72;
}


/* =====================================================
   SEARCH
===================================================== */

.search-box {

    max-width: 600px;

    margin:
    0 auto 40px;
}

.search-box input {

    width: 100%;

    padding: 17px;

    border-radius: 10px;

    background: #0d1b2d;

    border:
    1px solid #30445c;

    color: white;

    outline: none;

    font-size: 16px;
}


/* =====================================================
   CART
===================================================== */

.cart-page {

    min-height: 70vh;
}

.cart-list {

    max-width: 900px;

    margin: auto;
}

.cart-item {

    display: flex;

    align-items: center;

    justify-content: space-between;

    padding: 20px;

    margin-bottom: 12px;

    background:
    #0d1b2d;

    border-radius: 10px;
}

.cart-total {

    max-width: 900px;

    margin: 30px auto;

    text-align: right;

    font-size: 25px;
}

.cart-total span {

    color: #39ff72;
}


/* =====================================================
   MOBILE
===================================================== */

@media(max-width:1000px) {

    .products {

        grid-template-columns:
        repeat(2,1fr);

    }

    .categories {

        grid-template-columns:
        repeat(2,1fr);

    }

}


@media(max-width:700px) {

    header {

        flex-direction: column;

        gap: 15px;
    }

    nav {

        justify-content:
        center;

        flex-wrap: wrap;

        gap: 12px;
    }

    nav a {

        font-size: 14px;
    }

    .hero {

        min-height: 600px;

        padding:
        60px 7%;
    }

    .hero h1 {

        font-size: 48px;
    }

    .hero p {

        font-size: 16px;
    }

    .products {

        grid-template-columns:
        1fr;
    }

    .categories {

        grid-template-columns:
        1fr;
    }

    .section-title {

        font-size: 32px;
    }

    section {

        padding:
        65px 6%;
    }

    .auth-box {

        padding: 25px;
    }

    footer {

        flex-direction:
        column;

        gap: 15px;

        text-align: center;
    }

}


@media(max-width:400px) {

    .hero h1 {

        font-size: 40px;
    }

}

</style>

</head>


<body>


<!-- =====================================================
     HEADER
===================================================== -->

<header>

    <div class="logo">
        🏆 SPORTZONE
    </div>


    <nav>

        <a href="index.php">
            Басты бет
        </a>

        <a href="?page=products">
            Тауарлар
        </a>

        <a href="?page=cart">
            🛒 Себет
            <span id="cartCount">0</span>
        </a>


        <?php if(isset($_SESSION["user_id"])): ?>

            <a href="?page=account">

                👤

                <?= htmlspecialchars(
                    $_SESSION["user_name"]
                ) ?>

            </a>

            <a href="?logout=1">
                Шығу
            </a>


        <?php else: ?>

            <a href="?page=login">
                Кіру
            </a>

        <?php endif; ?>

    </nav>

</header>



<?php if($page == "home"): ?>


<!-- =====================================================
     HOME — VIDEO HERO
===================================================== -->

<section class="hero">


    <!-- =================================================
         8 СЕКУНДТЫҚ ВИДЕО ФОН
         Файл: sport-video.mp4
    ================================================== -->

    <video
        class="hero-video"
        autoplay
        muted
        loop
        playsinline
        preload="auto"
    >

        <source
            src="sport-video.mp4"
            type="video/mp4"
        >

        Сіздің браузеріңіз видео элементін қолдамайды.

    </video>


    <!-- ҚАРАҢҒЫ OVERLAY -->

    <div class="hero-overlay"></div>


    <!-- =================================================
         HERO TEXT
    ================================================== -->

    <div class="hero-content">

        <div class="small-title">

            SPORT • POWER • VICTORY

        </div>


        <h1>

            Спортпен бірге

            <span>

                биіктерге жет!

            </span>

        </h1>


        <p>

            SPORTZONE — спорттық киімдер,
            аяқ киімдер және жабдықтар дүкені.
            Жаттығуыңды жаңа деңгейге көтер!

        </p>


        <a
            href="?page=products"
            class="button"
        >

            🛍 Тауарларды көру

        </a>

    </div>

</section>



<section>

    <h2 class="section-title">

        🏆 Спорт бағыттары

    </h2>


    <div class="categories">


        <div class="category">

            <div class="category-icon">
                ⚽
            </div>

            <h3>
                Футбол
            </h3>

            <p>
                Доптар, форма,
                қолғаптар
            </p>

        </div>



        <div class="category">

            <div class="category-icon">
                🏀
            </div>

            <h3>
                Баскетбол
            </h3>

            <p>
                Доптар және
                спорттық киімдер
            </p>

        </div>



        <div class="category">

            <div class="category-icon">
                🏃
            </div>

            <h3>
                Фитнес
            </h3>

            <p>
                Жаттығу жабдықтары
            </p>

        </div>



        <div class="category">

            <div class="category-icon">
                👟
            </div>

            <h3>
                Жүгіру
            </h3>

            <p>
                Жеңіл спорттық
                аяқ киімдер
            </p>

        </div>


    </div>

</section>



<section class="about">

    <div class="about-content">

        <h2>
            SPORTZONE
        </h2>


        <p>

            Біз спортпен айналысатын
            адамдарға арналған заманауи
            интернет дүкенбіз.

        </p>


        <p>

            Футбол, баскетбол, фитнес,
            жүгіру және басқа спорт
            түрлеріне арналған тауарлар.

        </p>


        <br>


        <a
            href="?page=products"
            class="button"
        >

            Дүкенге өту

        </a>

    </div>

</section>



<?php elseif($page == "products"): ?>


<!-- =====================================================
     PRODUCTS
===================================================== -->

<section>

    <h1 class="section-title">

        🛍 Спорттық тауарлар

    </h1>


    <div class="search-box">

        <input
            type="text"
            id="search"
            placeholder="🔎 Тауар іздеу..."
        >

    </div>


    <div
        class="products"
        id="products"
    >


        <?php while($p = $products->fetch_assoc()): ?>


        <div
            class="product"
            data-name="<?= strtolower(
                $p["name"]
            ) ?>"
        >


            <div class="product-picture">


                <?php if(!empty($p["image"])): ?>

                    <img
                        src="<?= htmlspecialchars(
                            $p["image"]
                        ) ?>"
                        alt="<?= htmlspecialchars(
                            $p["name"]
                        ) ?>"
                        loading="lazy"
                    >

                <?php else: ?>

                    <span>

                        <?= htmlspecialchars(
                            $p["emoji"]
                        ) ?>

                    </span>

                <?php endif; ?>


            </div>


            <div class="product-info">


                <div class="product-category">

                    <?= htmlspecialchars(
                        $p["category"]
                    ) ?>

                </div>


                <h3>

                    <?= htmlspecialchars(
                        $p["name"]
                    ) ?>

                </h3>


                <p>

                    <?= htmlspecialchars(
                        $p["description"]
                    ) ?>

                </p>


                <div class="product-bottom">


                    <div class="price">

                        <?= number_format(
                            $p["price"],
                            0,
                            ".",
                            " "
                        ) ?>

                        ₸

                    </div>


                    <button
                        class="cart-button"

                        onclick="addToCart(
                            <?= $p["id"] ?>,
                            '<?= htmlspecialchars(
                                $p["name"],
                                ENT_QUOTES
                            ) ?>',
                            <?= $p["price"] ?>
                        )"
                    >

                        🛒

                    </button>


                </div>


            </div>


        </div>


        <?php endwhile; ?>


    </div>

</section>



<?php elseif($page == "login"): ?>


<!-- =====================================================
     LOGIN
===================================================== -->

<div class="auth-container">

    <div class="auth-box">


        <h2>
            🏆 SPORTZONE
        </h2>


        <h2>
            Жүйеге кіру
        </h2>


        <?php if($message): ?>

            <div class="alert <?= $messageType ?>">

                <?= htmlspecialchars(
                    $message
                ) ?>

            </div>

        <?php endif; ?>


        <form method="POST">


            <label>
                Email
            </label>


            <input
                type="email"
                name="email"
                placeholder="example@gmail.com"
                required
            >


            <label>
                Пароль
            </label>


            <input
                type="password"
                name="password"
                placeholder="Пароль"
                required
            >


            <button
                class="button"
                name="login"
                type="submit"
            >

                Кіру

            </button>


        </form>


        <a
            href="?page=register"
            class="auth-link"
        >

            Аккаунтың жоқ па? Тіркелу

        </a>


    </div>

</div>



<?php elseif($page == "register"): ?>


<!-- =====================================================
     REGISTER
===================================================== -->

<div class="auth-container">

    <div class="auth-box">


        <h2>
            🏆 SPORTZONE
        </h2>


        <h2>
            Тіркелу
        </h2>


        <?php if($message): ?>

            <div class="alert <?= $messageType ?>">

                <?= htmlspecialchars(
                    $message
                ) ?>

            </div>

        <?php endif; ?>


        <form method="POST">


            <label>
                Аты-жөніңіз
            </label>


            <input
                type="text"
                name="name"
                placeholder="Аты-жөніңіз"
                required
            >


            <label>
                Email
            </label>


            <input
                type="email"
                name="email"
                placeholder="example@gmail.com"
                required
            >


            <label>
                Пароль
            </label>


            <input
                type="password"
                name="password"
                placeholder="Кемінде 6 таңба"
                minlength="6"
                required
            >


            <button
                class="button"
                name="register"
                type="submit"
            >

                Тіркелу

            </button>


        </form>


        <a
            href="?page=login"
            class="auth-link"
        >

            Аккаунтың бар ма? Кіру

        </a>


    </div>

</div>



<?php elseif($page == "cart"): ?>


<!-- =====================================================
     CART
===================================================== -->

<section class="cart-page">


    <h1 class="section-title">

        🛒 Менің себетім

    </h1>


    <div
        class="cart-list"
        id="cartList"
    ></div>


    <div class="cart-total">


        Жалпы:


        <span id="cartTotal">

            0 ₸

        </span>


        <br><br>


        <button
            class="button"
            onclick="clearCart()"
        >

            Себетті тазалау

        </button>


    </div>


</section>



<?php elseif($page == "account"): ?>


<!-- =====================================================
     ACCOUNT
===================================================== -->

<section>


    <div
        class="auth-box"
        style="margin:auto;"
    >


        <h2>

            👤 Менің аккаунтым

        </h2>


        <p
            style="
                text-align:center;
                color:#aaa;
                margin:20px;
            "
        >

            Қош келдіңіз,


            <strong
                style="color:#39ff72;"
            >

                <?= htmlspecialchars(
                    $_SESSION["user_name"]
                ) ?>

            </strong>


        </p>


        <a
            href="?logout=1"
            class="button"
            style="
                width:100%;
                text-align:center;
            "
        >

            Шығу

        </a>


    </div>

</section>



<?php endif; ?>



<!-- =====================================================
     FOOTER
===================================================== -->

<footer>


    <div>

        <strong>
            🏆 SPORTZONE
        </strong>


        <br><br>


        Спорт — сенің жаңа күшің!

    </div>


    <div>

        © 2026 SPORTZONE

    </div>


</footer>



<script>

/* =====================================================
   CART SYSTEM
===================================================== */

let cart =
JSON.parse(
    localStorage.getItem(
        "sportzone_cart"
    )
) || [];


/* =====================================================
   ADD TO CART
===================================================== */

function addToCart(
    id,
    name,
    price
) {

    cart.push({

        id: id,

        name: name,

        price: price

    });


    localStorage.setItem(

        "sportzone_cart",

        JSON.stringify(cart)

    );


    updateCartCount();


    alert(
        "✅ " +
        name +
        " себетке қосылды!"
    );

}


/* =====================================================
   CART COUNT
===================================================== */

function updateCartCount() {

    let counter =
        document.getElementById(
            "cartCount"
        );


    if(counter) {

        counter.innerText =
            cart.length;

    }

}


/* =====================================================
   SHOW CART
===================================================== */

function showCart() {

    let list =
        document.getElementById(
            "cartList"
        );


    let total =
        document.getElementById(
            "cartTotal"
        );


    if(!list) return;


    if(cart.length === 0) {

        list.innerHTML = `

            <div class="cart-item">

                🛒 Себет бос.

            </div>

        `;


        if(total) {

            total.innerText =
                "0 ₸";

        }

        return;

    }


    list.innerHTML = "";

    let sum = 0;


    cart.forEach(
        function(item,index) {


            sum +=
                Number(item.price);


            let div =
                document.createElement(
                    "div"
                );


            div.className =
                "cart-item";


            div.innerHTML = `

                <div>

                    <strong>

                        ${item.name}

                    </strong>

                    <br>

                    <span
                    style="color:#39ff72;">

                        ${Number(
                            item.price
                        ).toLocaleString()}

                        ₸

                    </span>

                </div>


                <button
                    class="cart-button"

                    onclick="
                    removeCart(${index})
                    "
                >

                    ❌

                </button>

            `;


            list.appendChild(div);

        }
    );


    if(total) {

        total.innerText =
            sum.toLocaleString() +
            " ₸";

    }

}


/* =====================================================
   REMOVE CART
===================================================== */

function removeCart(index) {

    cart.splice(
        index,
        1
    );


    localStorage.setItem(

        "sportzone_cart",

        JSON.stringify(cart)

    );


    showCart();

    updateCartCount();

}


/* =====================================================
   CLEAR CART
===================================================== */

function clearCart() {

    cart = [];


    localStorage.removeItem(
        "sportzone_cart"
    );


    showCart();

    updateCartCount();

}


/* =====================================================
   SEARCH
===================================================== */

let search =
    document.getElementById(
        "search"
    );


if(search) {


    search.addEventListener(

        "input",

        function() {


            let value =
                this.value
                .toLowerCase();


            document
            .querySelectorAll(
                ".product"
            )
            .forEach(

                function(product) {


                    let name =
                        product
                        .dataset
                        .name;


                    if(
                        name.includes(
                            value
                        )
                    ) {

                        product.style.display =
                            "block";

                    } else {

                        product.style.display =
                            "none";

                    }

                }

            );

        }

    );

}


/* =====================================================
   START
===================================================== */

updateCartCount();

showCart();

</script>


</body>

</html>