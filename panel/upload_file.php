<?php
require_once "{$_SERVER['DOCUMENT_ROOT']}/components/panel_head.php";
require_once "./db.php";
require_once "{$_SERVER['DOCUMENT_ROOT']}/vendor/erusev/parsedown/Parsedown.php";
$parsedown = new Parsedown();

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
        $uploaded = false;
        $error = false;
        $error_msg = "";

        // if the form was submitted, process the file upload
        if (isset($_FILES['file'])) {
            $file = $_FILES['file'];
            $file_name = $file['name'];
            $file_tmp = $file['tmp_name'];
            $file_size = $file['size'];
            $file_error = $file['error'];
    
            $file_ext = explode('.', $file_name);
            $file_ext = strtolower(end($file_ext));
    
            $allowed = ['jpg', 'png', 'jpeg', 'gif', 'webp', 'tiff', 'mp4', 'mov', 'avi'];
    
            if (in_array($file_ext, $allowed)) {
                if ($file_error === 0) {
                    if ($file_size <= 999999999) {
    
                        // Check if the file is an image
                        if (in_array($file_ext, ['jpg', 'png', 'jpeg', 'gif', 'webp', 'tiff'])) {
                            // Make the file 75% of the original size, and save it as a new file, save it as png
                            $image = imagecreatefromstring(file_get_contents($file_tmp));
                            $new_image = imagescale($image, imagesx($image) * 0.75, imagesy($image) * 0.75);
                            imagepng($new_image, $file_tmp);
    
                            $file_name = explode('.', $file_name);
                            $file_name = $file_name[0] . '.png';
                        } else {
                            $file_name = explode('.', $file_name);
                            $file_name = $file_name[0] . '.' . $file_ext;
                        }
    
                        // Generate a unique name for the file
                        $file_ext = explode('.', $file_name);
                        $file_ext = strtolower(end($file_ext));
                    
                        $file_name_new = uniqid('', true) . '.' . $file_ext;
                        $file_destination = "{$_SERVER['DOCUMENT_ROOT']}/images/" . $file_name_new;
    
                        if (move_uploaded_file($file_tmp, $file_destination)) {
                            $uploaded = true;
                            $link = "/images/" . $file_name_new;
                        } else {
                            $error = true;
                            $error_msg = "Nemohl jsem nahrát soubor... Zkuste to prosím znovu.";
                        }
                    } else {
                        $error = true;
                        $error_msg = "Soubor je příliš velký. Maximální velikost souboru je " . round(999999999 / 1024 / 1024, 2) . " MB.";
                    }
                } else {
                    $error = true;
                    $error_msg = "Nemohl jsem nahrát soubor... Zkuste to prosím znovu.";
                }
            } else {
                $error = true;
                $error_msg = "Tento typ souboru není povolen.";
            }
        }
    }
}


?>
<title>LC Blog | Nahrát soubor</title>
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
                                <a href="#" class="panel-path-name">Nahrát soubor</a>
                            </div>
                            <div class="d-flex align-items-center">
                                <a href="#" class="panel-path-name"><?= $user['display_name'] ?></a>
                                <img src="<?php echo $user['avatar'] ?>" alt="Avatar" class="panel-avatar">
                            </div>
                        </div>
                        <div class="row g-0 w-100 m-0 align-items-center">
                            <div class="col-12 col-lg">
                                <h1 class="panel-article-name" id="article-title">Nahrát soubor</h1>
                            </div>
                            <p class="small panel-article-tip mb-0">Nahraj soubor, který bys chtěl hostovat.</p>
                        </div>
                    </section>
                    <?php if ($uploaded) : ?>
                        <div class="panel-section panel-alert-success d-flex align-items-center" role="alert">
                            <div class="me-3 d-flex align-align-items-center">
                                <i class="ph-bold ph-check-circle fs-3"></i>
                            </div>
                            <p class="mb-0">Soubor byl úspěšně nahrán</p>
                            <a class="ms-auto text-white" href="<?php echo $link; ?>">Odkaz na soubor</a>
                        </div>
                    <?php endif; ?>
                    <?php if ($error) : ?>
                        <div class="panel-section panel-alert-danger d-flex align-items-center" role="alert">
                            <div class="me-3 d-flex align-align-items-center">
                                <i class="ph-bold ph-x-circle fs-3"></i>
                            </div>
                            <p class="mb-0"><?php echo $error_msg; ?></p>
                        </div>
                    <?php endif; ?>
                    <section class="panel-section">
                    <form method="post" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="file" class="panel-form-label">Vyber soubor</label>
                            <input class="panel-form-control" type="file" name="file" id="file" accept="image/png, image/jpeg, image/gif, image/webp, image/tiff, video/mp4, video/mov, video/avi">
                        </div>
                        <button type="submit" class="btn btn-primary">Nahrát soubor</button>
                    </form>
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