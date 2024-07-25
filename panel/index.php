<?php
require_once "{$_SERVER['DOCUMENT_ROOT']}/components/panel_head.php";
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

    if ($user['group'] == "redactor" || $user['group'] == "admin") {
        $can_access = true;
    }
}


?>
<title>LC Blog | Domů</title>

<body>
    <?php require_once "{$_SERVER['DOCUMENT_ROOT']}/components/loader.php";
    if ($can_access) {
    ?>
        <div class="row g-0 m-0 w-100">
            <div class="col-lg-4 col-xl-2 col-12">
                <?php require_once "{$_SERVER['DOCUMENT_ROOT']}/components/sidebar_panel.php"; ?>
            </div>
            <div class="col-lg-8 col-xl-10 col-12">
                <div class="panel-content">
                    <section class="panel-section panel-sidebar-menu d-block d-lg-none">
                        <button class="btn panel-sidebar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasResponsive" aria-controls="offcanvasResponsive"><i class="ph-bold ph-sidebar-simple"></i></button>
                    </section>
                    <section class="panel-section">
                        <div class="d-md-flex justify-content-between align-items-center mb-4 w-100 d-none">
                            <div class="d-flex align-items-center">
                                <a href="./" class="panel-path-name"><img src="../assets/content/logo.svg" alt="Logo" height="24px"></a>
                                <span class="panel-path-separator">/</span>
                                <a href="#" class="panel-path-name">Domů</a>
                            </div>
                            <div class="d-flex align-items-center">
                                <a href="#" class="panel-path-name"><?= $user['display_name'] ?></a>
                                <img src="<?php echo $user['avatar'] ?>" alt="Avatar" class="panel-avatar">
                            </div>
                        </div>
                        <div class="row g-0 w-100 m-0 align-items-center">
                            <div class="col-12 col-lg">
                                <h1 class="panel-article-name" id="article-title">Domovská stránka</h1>
                            </div>
                            <!--<div class="row g-2 m-0 w-100">
                                <div class="col-12 col-md-6 col-lg-4 col-xl-3">
                                    <div class="panel-article h-100">
                                        <div class="panel-article-body">
                                            <h5 class="panel-article-title">Domů</h5>
                                        </div>
                                        <div class="row g-2 m-0 w-100 mb-1">
                                            <div class="col">
                                                <a href="./edit_user.php?user=<?php echo $nickname ?>" class="btn btn-primary w-100 rounded-2 py-2">Upravit</a>
                                            </div>
                                            <div class="col">
                                                <a href="./delete_user.php?user=<?php echo $nickname ?>" class="btn btn-danger w-100 rounded-2 py-2">Smazat</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>-->
                        </div>
                    </section>
                </div>
            </div>
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

    require_once "{$_SERVER['DOCUMENT_ROOT']}/components/scripts.php";
    ?>
</body>

</html>