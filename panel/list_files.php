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
    }
}


?>
<title>LC Blog | Soubory</title>
<meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests">
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
                                <a href="#" class="panel-path-name">Soubory</a>
                            </div>
                            <div class="d-flex align-items-center">
                                <a href="#" class="panel-path-name"><?= $user['display_name'] ?></a>
                                <img src="<?php echo $user['avatar'] ?>" alt="Avatar" class="panel-avatar">
                            </div>
                        </div>
                        <div class="row g-0 w-100 m-0 align-items-center">
                            <div class="col-12 col-lg">
                                <h1 class="panel-article-name" id="article-title">Soubory</h1>
                            </div>
                            <p class="small panel-article-tip mb-0">Pokud nechceš vyhrát hostovací stránky pro obrázky, můžeš využít naše řešení pro ukládání fotek.</p>
                            <div class="row g-2 m-0 w-100" id="files">
                                <script>
                                    // Fetch the files from page 1
                                    fetch('./files_paginator.php?page=1&start=0&stop=14')
                                        .then(response => response.text())
                                        .then(data => {
                                            // Get the JSON response and loop through all the articles 
                                            for (let file of JSON.parse(data)) {
                                                let file_element = document.createElement('div');
                                                file_element.classList.add('col-12', 'col-lg-6', 'col-xl-3', 'h-100');
                                                let panel_article = document.createElement('div');
                                                panel_article.classList.add('panel-article');
                                                file_element.appendChild(panel_article);

                                                if (file.extension == 'png' || file.extension == 'jpg' || file.extension == 'jpeg' || file.extension == 'gif' || file.extension == 'webp' || file.extension == 'tiff') {
                                                    let image_div = document.createElement('div');
                                                    image_div.classList.add('panel-article-image');
                                                    image_div.style.backgroundImage = `url('${file.path}')`;
                                                    panel_article.appendChild(image_div);
                                                } else {
                                                    let video_element = document.createElement('video');
                                                    video_element.classList.add('panel-article-video');
                                                    video_element.controls = true;

                                                    let source_element = document.createElement('source');
                                                    source_element.src = `${file.path}`;
                                                    source_element.type = 'video/mp4';

                                                    video_element.appendChild(source_element);
                                                    panel_article.appendChild(video_element);
                                                }

                                                let content_div = document.createElement('div');
                                                content_div.classList.add('panel-article-content');
                                                panel_article.appendChild(content_div);

                                                let title_h2 = document.createElement('h5');
                                                title_h2.classList.add('panel-article-title');
                                                title_h2.textContent = file.name;
                                                content_div.appendChild(title_h2);

                                                let meta_div = document.createElement('div');
                                                meta_div.classList.add('panel-article-meta');
                                                content_div.appendChild(meta_div);

                                                let date_p = document.createElement('p');
                                                date_p.classList.add('panel-article-date');
                                                date_p.textContent = file.extension;
                                                meta_div.appendChild(date_p);

                                                let size_p = document.createElement('p');
                                                size_p.classList.add('panel-article-date');
                                                size_p.textContent = file.size;
                                                meta_div.appendChild(size_p);

                                                let row_div = document.createElement('div');
                                                row_div.classList.add('row', 'g-2', 'm-0', 'w-100', 'mb-1');
                                                content_div.appendChild(row_div);

                                                let col1_div = document.createElement('div');
                                                col1_div.classList.add('col');
                                                row_div.appendChild(col1_div);

                                                let view_a = document.createElement('a');
                                                view_a.href = file.path;
                                                view_a.classList.add('btn', 'btn-primary', 'w-100', 'rounded-2', 'py-2');
                                                view_a.textContent = 'Zobrazit';
                                                col1_div.appendChild(view_a);

                                                let col2_div = document.createElement('div');
                                                col2_div.classList.add('col');
                                                row_div.appendChild(col2_div);

                                                let delete_a = document.createElement('a');
                                                delete_a.href = `./delete_file.php?file=${file.name}.${file.extension}`;
                                                delete_a.classList.add('btn', 'btn-danger', 'w-100', 'rounded-2', 'py-2');
                                                delete_a.textContent = 'Smazat';
                                                col2_div.appendChild(delete_a);

                                                // Append the file element to the files container
                                                document.getElementById('files').appendChild(file_element);
                                            }
                                        });
                                </script>
                            </div>
                            <div class="mt-3">
                                <?php
                                    // Count the number of files in the images directory
                                    $dir = "{$_SERVER['DOCUMENT_ROOT']}/images";
                                    $files = scandir($dir);
                                    $files = array_diff($files, ['.', '..']);
                                    $file_count = count($files);

                                    // Count how many pagination pages are needed
                                    $pages = ceil($file_count / 15);
                                    $file_count = 0;
                                    // Get the current page
                                ?>
                                <ul class="panel-pagination">
                                    <li class="panel-pagination-page" onclick="paginator_get()" data-file-start="0" data-file-stop="14"><p>Reset</p></li>
                                    <?php
                                    for ($i = 1; $i <= $pages; $i++) {
                                        ?>
                                        <li class="panel-pagination-page" onclick="paginator_get()" data-file-start="<?= $file_count ?>" data-file-stop="<?= $file_count + 14 ?>"><p><?= $i ?></p></li>
                                        <?php
                                        $file_count += 14;
                                    }
                                    ?>
                                </ul>
                            </div>
                            <script>
                                function paginator_get() {
                                    let files = document.getElementById('files');
                                    let pages = document.getElementsByClassName('panel-pagination-page');
                                    let start = 0;
                                    let stop = 14;
                                    for (let i = 0; i < pages.length; i++) {
                                        pages[i].addEventListener('click', function() {
                                            start = this.getAttribute('data-file-start');
                                            stop = this.getAttribute('data-file-stop');
                                            files.innerHTML = '';
                                            fetch(`./files_paginator.php?page=${i + 1}&start=${start}&stop=${stop}`)
                                                .then(response => response.text())
                                                .then(data => {
                                                    // Get the JSON response and loop through all the files )
                                                    files.innerHTML = '';
                                                    for (let file of JSON.parse(data)) {
                                                        // Create the file element
                                                        let file_element = document.createElement('div');
                                                        file_element.classList.add('col-12', 'col-lg-6', 'col-xl-3', 'h-100');
                                                        let panel_article = document.createElement('div');
                                                        panel_article.classList.add('panel-article');
                                                        file_element.appendChild(panel_article);

                                                        if (file.extension == 'png' || file.extension == 'jpg' || file.extension == 'jpeg' || file.extension == 'gif' || file.extension == 'webp' || file.extension == 'tiff') {
                                                            let image_div = document.createElement('div');
                                                            image_div.classList.add('panel-article-image');
                                                            image_div.style.backgroundImage = `url('${file.path}')`;
                                                            panel_article.appendChild(image_div);
                                                        } else {
                                                            let video_element = document.createElement('video');
                                                            video_element.classList.add('panel-article-video');
                                                            video_element.controls = true;

                                                            let source_element = document.createElement('source');
                                                            source_element.src = `${file.path}`;
                                                            source_element.type = 'video/mp4';

                                                            video_element.appendChild(source_element);
                                                            panel_article.appendChild(video_element);
                                                        }

                                                        let content_div = document.createElement('div');
                                                        content_div.classList.add('panel-article-content');
                                                        panel_article.appendChild(content_div);

                                                        let title_h2 = document.createElement('h5');
                                                        title_h2.classList.add('panel-article-title');
                                                        title_h2.textContent = file.name;
                                                        content_div.appendChild(title_h2);

                                                        let meta_div = document.createElement('div');
                                                        meta_div.classList.add('panel-article-meta');
                                                        content_div.appendChild(meta_div);

                                                        let date_p = document.createElement('p');
                                                        date_p.classList.add('panel-article-date');
                                                        date_p.textContent = file.extension;
                                                        meta_div.appendChild(date_p);

                                                        let size_p = document.createElement('p');
                                                        size_p.classList.add('panel-article-date');
                                                        size_p.textContent = file.size;
                                                        meta_div.appendChild(size_p);

                                                        let row_div = document.createElement('div');
                                                        row_div.classList.add('row', 'g-2', 'm-0', 'w-100', 'mb-1');
                                                        content_div.appendChild(row_div);

                                                        let col1_div = document.createElement('div');
                                                        col1_div.classList.add('col');
                                                        row_div.appendChild(col1_div);

                                                        let view_a = document.createElement('a');
                                                        view_a.href = file.path;
                                                        view_a.classList.add('btn', 'btn-primary', 'w-100', 'rounded-2', 'py-2');
                                                        view_a.textContent = 'Zobrazit';
                                                        col1_div.appendChild(view_a);

                                                        let col2_div = document.createElement('div');
                                                        col2_div.classList.add('col');
                                                        row_div.appendChild(col2_div);

                                                        let delete_a = document.createElement('a');
                                                        delete_a.href = `./delete_file.php?file=${file.name}.${file.extension}`;
                                                        delete_a.classList.add('btn', 'btn-danger', 'w-100', 'rounded-2', 'py-2');
                                                        delete_a.textContent = 'Smazat';
                                                        col2_div.appendChild(delete_a);

                                                        // Append the file element to the files container
                                                        document.getElementById('files').appendChild(file_element);
                                                    }
                                                });
                                        });
                                    }
                                }
                                paginator_get();
                            </script>
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