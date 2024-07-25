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

        $article = $_GET['article'] ?? null;
        $deleted = false;
        $error = false;
        $error_msg = "";
    }
}


?>
<title>LC Blog | Smazat článek</title>

<body>
    <?php require_once "{$_SERVER['DOCUMENT_ROOT']}/components/loader.php";
    if ($can_access) {
        // Check if the file exists in the /images directory
        if (file_exists("{$_SERVER['DOCUMENT_ROOT']}/posts/$article.md")) {
            // Move the file to the archive directory
            rename("{$_SERVER['DOCUMENT_ROOT']}/posts/$article.md", "{$_SERVER['DOCUMENT_ROOT']}/archive/$article.md");
            $deleted = true;
        } else {
            $error = true;
            $error_msg = "Článek neexistuje nebo se nedokázal dohledat.";
        }
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
                                <a href="#" class="panel-path-name">Smazat článek</a>
                            </div>
                            <div class="d-flex align-items-center">
                                <a href="#" class="panel-path-name"><?= $user['display_name'] ?></a>
                                <img src="<?php echo $user['avatar'] ?>" alt="Avatar" class="panel-avatar">
                            </div>
                        </div>
                        <div class="row g-0 w-100 m-0 align-items-center">
                            <div class="col-12 col-lg">
                                <h1 class="panel-article-name" id="article-title">Smazání článku</h1>
                            </div>
                            <?php
                            if ($deleted) {
                            ?>
                                <div class="panel-alert-success p-4" role="alert">
                                    Článek byl přehozen do archivu, aby ho bylo možné obnovit.
                                </div>
                                <a href="./" class="btn btn-primary col-auto mt-3">Zpět na domovskou stránku</a>
                            <?php
                            } else if ($error) {
                            ?>
                                <div class="panel-alert-danger p-4" role="alert">
                                    Problém - <?php echo $error_msg; ?>
                                </div>
                                <a href="./" class="btn btn-primary col-auto mt-3">Zpět na domovskou stránku</a>
                            <?php
                            }
                            ?>
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