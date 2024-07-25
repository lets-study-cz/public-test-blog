<button class="btn text-white" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasResponsive" aria-controls="offcanvasResponsive"><i class="ph-bold ph-list"></i></button>

<div class="offcanvas offcanvas-start vh-100 p-2" tabindex="-1" id="offcanvasResponsive" aria-labelledby="offcanvasResponsiveLabel">
    <div class="offcanvas-header">
        <div class="d-flex align-items-center w-100">
            <img src="<?php $_SERVER['DOCUMENT_ROOT'] ?>/assets/content/Logo.svg" alt="Logo" width="32px" height="32px">
            <h3 class="ms-3 mb-0 serif ls-2">LC Blog</h3>
        </div>
        <button type="button" class="btn text-white d-flex justify-content-center align-items-center" data-bs-dismiss="offcanvas" data-bs-target="#offcanvasResponsive" aria-label="Close"><i class="ph-bold ph-x fs-3"></i></button>
    </div>
    <div class="offcanvas-body h-100 d-flex flex-column w-100 p-2">
        <div class="d-flex flex-column w-100 mb-2">
            <h5 class="mt-3 mb-0">Články</h5>
            <a class="nav-sidebar" href="./index.php"><i class="ph-bold ph-house"></i> Domů</a>
            <a class="nav-sidebar" href="./list_posts.php"><i class="ph-bold ph-article-medium"></i> Články</a>
            <h5 class="mt-3 mb-0">Ukládání</h5>
            <a class="nav-sidebar" href="./list_files.php"><i class="ph-bold ph-cloud"></i> Úložiště</a>
            <a class="nav-sidebar" href="./upload_file.php"><i class="ph-bold ph-cloud-arrow-up"></i> Nahrát obrázek</a>
            <h5 class="mt-3 mb-0">Uživatelé</h5>
            <a class="nav-sidebar" href="./list_users.php"><i class="ph-bold ph-folder-user"></i> Správa uživatelů</a>
            <a class="nav-sidebar" href="./create_user.php"><i class="ph-bold ph-user-plus"></i> Přidat uživatele</a>
            <a class="nav-sidebar" href="./edit_myself.php"><i class="ph-bold ph-highlighter"></i> Upravit svůj účet</a>
        </div>
    </div>
</div>