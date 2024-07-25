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
<title>LC Blog | Články</title>
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
                                <a href="#" class="panel-path-name">Články</a>
                            </div>
                            <div class="d-flex align-items-center">
                                <a href="#" class="panel-path-name"><?= $user['display_name'] ?></a>
                                <img src="<?php echo $user['avatar'] ?>" alt="Avatar" class="panel-avatar">
                            </div>
                        </div>
                        <div class="row g-0 w-100 m-0 align-items-center">
                            <div class="col-12 col-lg">
                                <h1 class="panel-article-name" id="article-title">Články</h1>
                            </div>
                            <p class="small panel-article-tip mb-0">Zde najdeš všechny články, které jsou k dispozici na blogu.</p>
                            <div class="row g-2 m-0 w-100" id="articles">
                                <script>
                                    // Fetch the articles from page 1
                                    fetch('./article_paginator.php?page=1&start=0&stop=14')
                                        .then(response => response.text())
                                        .then(data => {
                                            // Get the JSON response and loop through all the articles 
                                            for (let article of JSON.parse(data)) {
                                                // Create the article element
                                                let article_element = document.createElement('div');
                                                article_element.classList.add('col-12', 'col-lg-6', 'col-xl-3', 'h-100');
                                                article_element.innerHTML = `
                                                <div class="panel-article h-100">
                                                    <div class="panel-article-content">
                                                        <h2 class="panel-article-title">${article.title}</h2>
                                                        <p class="panel-article-description">${article.description}</p>
                                                        <div class="panel-article-meta">
                                                            <p class="panel-article-author">${article.author}</p>
                                                            <p class="panel-article-date">${article.date}</p>
                                                        </div>
                                                        <div class="row g-2 m-0 w-100 mb-1">
                                                            <div class="col">
                                                                <a href="./edit_article.php?article=${article.file_name}" class="btn btn-primary w-100 rounded-2 py-2">Upravit</a>
                                                            </div>
                                                            <div class="col">
                                                                <a href="./delete_article.php?article=${article.file_name}" class="btn btn-danger w-100 rounded-2 py-2">Smazat</a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                `;
                                                // Append the article element to the articles container
                                                document.getElementById('articles').appendChild(article_element);
                                            }
                                        });
                                </script>
                            </div>
                            <div class="mt-3">
                                <?php
                                    // Count the number of article files in the posts directory
                                    $dir = "{$_SERVER['DOCUMENT_ROOT']}/posts";
                                    $files = scandir($dir);
                                    $article_count = count($files);

                                    // Count how many pagination pages are needed
                                    $pages = ceil($article_count / 15);
                                    $articles_count = 0;
                                    // Get the current page
                                ?>
                                <ul class="panel-pagination">
                                    <li class="panel-pagination-page" onclick="paginator_get()" data-article-start="0" data-article-stop="14"><p>Reset</p></li>
                                    <?php
                                    for ($i = 1; $i <= $pages; $i++) {
                                        ?>
                                        <li class="panel-pagination-page" onclick="paginator_get()" data-article-start="<?= $articles_count ?>" data-article-stop="<?= $articles_count + 14 ?>"><p><?= $i ?></p></li>
                                        <?php
                                        $articles_count += 14;
                                    }
                                    ?>
                                </ul>
                            </div>
                            <script>
                                function paginator_get() {
                                    let articles = document.getElementById('articles');
                                    let pages = document.getElementsByClassName('panel-pagination-page');
                                    let title = document.getElementById('article-title');
                                    let start = 0;
                                    let stop = 14;
                                    for (let i = 0; i < pages.length; i++) {
                                        pages[i].addEventListener('click', function() {
                                            start = this.getAttribute('data-article-start');
                                            stop = this.getAttribute('data-article-stop');
                                            articles.innerHTML = '';
                                            fetch(`./article_paginator.php?page=${i + 1}&start=${start}&stop=${stop}`)
                                                .then(response => response.text())
                                                .then(data => {
                                                    // Get the JSON response and loop through all the articles )
                                                    articles.innerHTML = '';
                                                    for (let article of JSON.parse(data)) {
                                                        // Create the article element
                                                        let article_element = document.createElement('div');
                                                        article_element.classList.add('col-12', 'col-lg-6', 'col-xl-3', 'h-100');
                                                        article_element.innerHTML = `
                                                            <div class="panel-article">
                                                                <div class="panel-article-content">
                                                                    <h2 class="panel-article-title">${article.title}</h2>
                                                                    <p class="panel-article-description">${article.description}</p>
                                                                    <div class="panel-article-meta">
                                                                        <p class="panel-article-author">${article.author}</p>
                                                                        <p class="panel-article-date">${article.date}</p>
                                                                    </div>
                                                                    <div class="row g-2 m-0 w-100 mb-1">
                                                                        <div class="col">
                                                                            <a href="./edit_article.php?article=${article.file_name}" class="btn btn-primary w-100 rounded-2 py-2">Upravit</a>
                                                                        </div>
                                                                        <div class="col">
                                                                            <a href="./delete_article.php?article=${article.file_name}" class="btn btn-danger w-100 rounded-2 py-2">Smazat</a>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        `;
                                                        // Append the article element to the articles container
                                                        document.getElementById('articles').appendChild(article_element);
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