<div class="offcanvas-lg offcanvas-start panel-sidebar vh-100" tabindex="-1" id="offcanvasResponsive" aria-labelledby="offcanvasResponsiveLabel">
    <div class="offcanvas-header panel-sidebar-header">
        <div class="d-flex align-items-center w-100">
            <img src="<?php $_SERVER['DOCUMENT_ROOT'] ?>/assets/content/Logo.svg" alt="Logo" width="32px" height="32px">
            <h3 class="ms-3 panel-sidebar-name">LC Blog</h3>
        </div>
        <button type="button" class="btn panel-sidebar-close" data-bs-dismiss="offcanvas" data-bs-target="#offcanvasResponsive" aria-label="Close"><i class="ph-bold ph-x fs-3"></i></button>
    </div>
    <div class="offcanvas-body panel-sidebar-body h-100 d-flex flex-column w-100">
        <div class="d-flex flex-column w-100 mb-2">
            <h5 class="panel-sidebar-category">Hlavní nabídka</h5>
            <div class="d-flex flex-column w-100">
                <a class="panel-sidebar-link" href="./index.php"><i class="ph-bold ph-house"></i> Domů</a>
                <a class="panel-sidebar-link" href="./edit_profile.php"><i class="ph-bold ph-user-circle"></i> Můj profil</a>
            </div>
            <h5 class="panel-sidebar-category">Články</h5>
            <div class="d-flex flex-column w-100">
                <a class="panel-sidebar-link" href="./list_articles.php"><i class="ph-bold ph-files"></i> Seznam článků</a>
                <a class="panel-sidebar-link" href="./edit_article.php"><i class="ph-bold ph-file-plus"></i> Vytvořit článek</a>
            </div>
            <h5 class="panel-sidebar-category">Úložiště</h5>
            <div class="d-flex flex-column w-100">
                <a class="panel-sidebar-link" href="./list_files.php"><i class="ph-bold ph-images-square"></i> Soubory</a>
                <a class="panel-sidebar-link" href="./upload_file.php"><i class="ph-bold ph-box-arrow-up"></i> Nový soubor</a>
            </div>
            <h5 class="panel-sidebar-category">Uživatelé</h5>
            <div class="d-flex flex-column w-100">
                <a class="panel-sidebar-link" href="./list_users.php"><i class="ph-bold ph-users"></i> Spravovat uživatele</a>
                <a class="panel-sidebar-link" href="./create_user.php"><i class="ph-bold ph-user-circle-plus"></i> Přidat uživatele</a>
            </div>
        </div>
    </div>
</div>