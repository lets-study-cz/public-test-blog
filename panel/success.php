<?php
require_once "{$_SERVER['DOCUMENT_ROOT']}/components/head.php";
require_once "./db.php";
session_start();

$can_access = false;

// Check in database if user is redactor or admin
if (isset($_SESSION['name'])) {
    $stmt = $conn->prepare("SELECT * FROM users WHERE nickname = ?");
    $stmt->bind_param("s", $_SESSION['name']);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();

    if ($user['group'] == "editor" || $user['group'] == "admin") {
        $can_access = true;
    }
}


?>
<title>LC Blog | Vytvořen uživatel</title>
<body>
    <div class="header-gradient d-none d-lg-block"></div>

    <?php require_once "{$_SERVER['DOCUMENT_ROOT']}/components/loader.php"; 
    if ($can_access) {
        require_once "{$_SERVER['DOCUMENT_ROOT']}/components/sidebar.php";
        ?>
        <div class="vh-100 text-center d-flex flex-column justify-content-center align-items-center p-3">
            <h1 class="display-1 serif ls-2">Uživatel byl vytvořen.</h1>
            <a href="./list_users.php" class="btn btn-primary">Zobrazit všechny uživatele</a>
        </div>
        <?php
    } else {
        ?>
        <div class="vh-100 text-center d-flex flex-column justify-content-center align-items-center p-3">
            <h1 class="display-1 serif ls-2">Promiň, nemůžeme ti načíst stránku...</h1>
            <p class="lead">Pro zobrazení této stránky musíš být přihlášen jako redaktor nebo administrátor.</p>
            <a href="/" class="btn btn-primary">Hlavní stránka</a>
        </div>
        <?php
    }
    
    ?>

    <?php require_once "{$_SERVER['DOCUMENT_ROOT']}/components/scripts.php"; ?>
</body>
</html>