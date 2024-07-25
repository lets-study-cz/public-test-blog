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

    if ($user['group'] == "editor" || $user['group'] == "admin") {
        $can_access = true;

        // Get all users from database, so we can display them in the select input
        $stmt = $conn->prepare("SELECT * FROM users");
        $stmt->execute();
        $result = $stmt->get_result();
        $users = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        $categories_list = json_decode(file_get_contents("{$_SERVER['DOCUMENT_ROOT']}/panel/categories.json"), true);
        $tags_list = json_decode(file_get_contents("{$_SERVER['DOCUMENT_ROOT']}/panel/tags.json"), true);
    }
}


?>
<title>LC Blog | Vytvořit příspěvek</title>
<meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests">
<body>

    <?php require_once "{$_SERVER['DOCUMENT_ROOT']}/components/loader.php"; 
    if ($can_access) {
        date_default_timezone_set('Europe/Prague');
        $title = 'Nový článek';
        $description = '';
        $status = 'Draft';
        $seo_description = '';
        $seo_keywords = '';
        $head_image = '';
        $author = '';
        $categories = '';
        $tags = '';
        $date = date('Y-m-d H:i:s');
        $last_updated = date('Y-m-d H:i:s');
        $premiumContent = false;
        $content = '';

        if (isset($_GET['article'])) {
            $posts = "{$_SERVER['DOCUMENT_ROOT']}/posts/{$_GET['article']}.md";
            
            $markdownContent = file_get_contents($posts);
            $metadata = explode('---', $markdownContent)[1];
            $metadata = explode("\n", $metadata);
            
            foreach ($metadata as $meta) {
                if (strpos($meta, 'title:') !== false) {
                    $title = str_replace(['title: ', '"'], '', $meta);
                } elseif (strpos($meta, 'description:') !== false) {
                    $description = str_replace(['description: ', '"'], '', $meta);
                } elseif (strpos($meta, 'seo_desc:') !== false) {
                    $seo_description = str_replace(['seo_desc: ', '"'], '', $meta);
                } elseif (strpos($meta, 'seo_keywords:') !== false) {
                    $seo_keywords = str_replace(['seo_keywords: ', '"'], '', $meta);
                } elseif (strpos($meta, 'head_image:') !== false) {
                    $head_image = str_replace(['head_image: ', '"'], '', $meta);
                } elseif (strpos($meta, 'author:') !== false) {
                    $author = trim(str_replace(['author: ', '"'], '', $meta));
                } elseif (strpos($meta, 'categories:') !== false) {
                    $categories = str_replace(['categories: ', '"'], '', $meta);
                } elseif (strpos($meta, 'tags:') !== false) {
                    $tags = str_replace(['tags: ', '"'], '', $meta);
                } elseif (strpos($meta, 'date:') !== false) {
                    $date = str_replace(['date: ', '"'], '', $meta);
                } elseif (strpos($meta, 'last_updated:') !== false) {
                    $last_updated = str_replace(['last_updated: ', '"'], '', $meta);
                } elseif (strpos($meta, 'premium_content:') !== false) {
                    $premiumContent = str_replace(['premium_content: ', '"'], '', $meta) === 'true';
                } elseif (strpos($meta, 'status:') !== false) {
                    $status = str_replace(['status: ', '"'], '', $meta);
                }
            }

            $content = preg_split('/---/', $markdownContent, 3)[2];
        }
    ?>
    <div class="row g-0 m-0 w-100">
        <div class="col-lg-4 col-xl-2 col-12">
            <?php require_once "{$_SERVER['DOCUMENT_ROOT']}/components/sidebar_panel.php"; ?>
        </div>
        <div class="col-lg-8 col-xl-10 col-12">
            <form method="post" action="./generate_post.php" class="panel-content">
                <section class="panel-section panel-sidebar-menu d-block d-lg-none">
                    <button class="btn panel-sidebar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasResponsive" aria-controls="offcanvasResponsive"><i class="ph-bold ph-sidebar-simple"></i></button>
                </section>
                <section class="panel-section">
                    <div class="d-md-flex justify-content-between align-items-center mb-4 w-100 d-none">
                        <div class="d-flex align-items-center">
                            <a href="./" class="panel-path-name"><img src="../assets/content/logo.svg" alt="Logo" height="24px"></a>
                            <span class="panel-path-separator">/</span>
                            <a href="#" class="panel-path-name">Články</a>
                            <span class="panel-path-separator">/</span>
                            <p class="panel-path-name">Vytvořit článek</p>
                        </div>
                        <div class="d-flex align-items-center">
                            <a href="#" class="panel-path-name"><?= $user['display_name'] ?></a>
                            <img src="<?php echo $user['avatar'] ?>" alt="Avatar" class="panel-avatar">
                        </div>
                    </div>
                    <div class="row g-0 w-100 m-0 align-items-center">
                        <div class="col-12 col-lg">
                            <h1 class="panel-article-name" id="article-title"><?= $title ?></h1>
                        </div>
                        <p class="small panel-article-tip mb-0">Pokud se rozhodneš odejít a pak opět upravovat článek, otevři si ho rovnou ze seznamu článků. Nevytvářej si nový.</p>
                    </div>
                </section>
                <section class="panel-section">
                    <div class="d-flex justify-content-between align-items-center flex-wrap w-100">
                        <div class="row g-0 m-0 w-100">
                            <div class="col-12 col-lg d-flex align-items-center overflow-y-auto">
                                <div class="d-flex align-items-center me-4">
                                    <span class="panel-article-info text-gray text-nowrap">Status:</span>
                                    <span class="panel-article-info text-nowrap" id="status"><?= $status ?></span>
                                </div>
                                <div class="d-flex align-items-center me-4">
                                    <span class="panel-article-info text-gray text-nowrap">Naposledy upraveno:</span>
                                    <span class="panel-article-info text-nowrap" id="created"><?= $last_updated ?></span>
                                </div>
                                <div class="d-flex align-items-center">
                                    <span class="panel-article-info text-gray text-nowrap">Vytvořeno:</span>
                                    <span class="panel-article-info text-nowrap" id="last-updated"><?= $date ?></span>
                                </div>
                            </div>
                            <div class="col-12 col-lg mt-2 mt-lg-0">
                                <div class="row gx-2 m-0 w-100 justify-content-end">
                                    <div class="col col-lg-auto">
                                        <a href="#" class="btn panel-article-option w-100">Náhled</a>
                                    </div>
                                    <div method="post" act class="col col-lg-auto">
                                        <button type="submit" name="publish" class="btn panel-article-option btn-white w-100">Publikovat článek</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
                <section class="panel-section pb-0">
                    <div class="mb-4">
                        <label for="title" class="panel-form-label">Nadpis článku<span class="text-red">*</span></label>
                        <input type="text" class="panel-form-control" name="title" id="title" minlength="4" maxlength="48" value="<?= $title ?>">
                        <div class="panel-input-counter" id="title-counter">
                            <span id="title-length">0</span>
                            <span>/</span>
                            <span>48</span>
                            <span>Znaků</span>
                        </div>
                    </div>
                    <div class="nav" id="nav-tab" role="tablist">
                        <button class="panel-section-tab active" id="nav-info-tab" data-bs-toggle="tab" data-bs-target="#nav-info" type="button" role="tab" aria-controls="nav-info" aria-selected="true">Informace</button>
                        <button class="panel-section-tab" id="nav-seo-tab" data-bs-toggle="tab" data-bs-target="#nav-seo" type="button" role="tab" aria-controls="nav-seo" aria-selected="false">SEO</button>
                        <button class="panel-section-tab" id="nav-content-tab" data-bs-toggle="tab" data-bs-target="#nav-content" type="button" role="tab" aria-controls="nav-content" aria-selected="false">Obsah</button>
                    </div>
                </section>
                <section class="panel-section">
                    <div class="tab-content" id="nav-tabContent">
                        <div class="tab-pane fade show active" id="nav-info" role="tabpanel" aria-labelledby="nav-info-tab" tabindex="0">
                            <div class="mb-4">
                                <label for="description" class="panel-form-label">Krátký popisek článku<span class="text-red">*</span></label>
                                <input type="text" class="panel-form-control" name="description" id="description" minlength="4" maxlength="196" value="<?= $description ?>">
                                <div class="panel-input-counter" id="description-counter">
                                    <span id="description-length">0</span>
                                    <span>/</span>
                                    <span>196</span>
                                    <span>Znaků</span>
                                </div>
                            </div>
                            <div class="mb-4">
                                <label for="head_image" class="panel-form-label">Obrázek článku<span class="text-red">*</span></label>
                                <input type="text" class="panel-form-control" name="head_image" id="head_image" value="<?= $head_image ?>">
                            </div>
                            <div class="mb-4">
                                <label for="author" class="panel-form-label">Autor článku<span class="text-red">*</span></label>
                                <select class="panel-form-control panel-form-select" name="author" id="author">
                                    <?php foreach ($users as $user): ?>
                                        <option value="<?php echo $user['nickname']; ?>" <?php echo $user['nickname'] === $author ? 'selected' : ''; ?>><?php echo $user['nickname']; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-4">
                                <label for="categories" class="panel-form-label">Přidružený projekt<span class="text-red">*</span></label>
                                <select class="panel-form-control panel-form-select" name="categories" id="categories">
                                    <?php foreach ($categories_list as $category): ?>
                                        <option value="<?php echo $category; ?>" <?php echo $category === $categories ? 'selected' : ''; ?>><?php echo $category; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-4">
                                <label for="tags" class="panel-form-label">Štítek<span class="text-red">*</span></label>
                                <select class="panel-form-control panel-form-select" name="tags" id="tags">
                                    <?php foreach ($tags_list as $tag): ?>
                                        <option value="<?php echo $tag; ?>" <?php echo $tag === $tags ? 'selected' : ''; ?>><?php echo $tag; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="nav-seo" role="tabpanel" aria-labelledby="nav-seo-tab" tabindex="0">
                            <div class="mb-4">
                                <label for="seo-description" class="panel-form-label">Meta - description (Popisek)<span class="text-red">*</span></label>
                                <input type="text" class="panel-form-control" name="seo_description" id="seo_description" minlength="4" maxlength="160" value="<?= $seo_description ?>">
                                <div class="panel-input-counter" id="description-seo-counter">
                                    <span id="description-seo-length">0</span>
                                    <span>/</span>
                                    <span>160</span>
                                    <span>Znaků</span>
                                </div>
                            </div>
                            <div class="mb-4">
                                <label for="seo-keywords" class="panel-form-label">Meta - keywords (Klíčová slova)<span class="text-red">*</span></label>
                                <input type="text" class="panel-form-control" name="seo_keywords" id="seo_keywords" minlength="4" maxlength="160" value="<?= $seo_keywords ?>">
                                <div class="panel-input-counter" id="keywords-seo-counter">
                                    <span id="keywords-seo-length">0</span>
                                    <span>/</span>
                                    <span>160</span>
                                    <span>Znaků</span>
                                </div>
                            </div>
                            <hr class="my-5">
                            <div class="mb-4">
                                <h3>Přibližný náhled zobrazení stránky v Google vyhledavači</h3>
                                <div class="panel-seo-preview">
                                    <div class="d-flex align-items-center mb-3">
                                        <img src="../assets/content/logo.svg" alt="Logo" width="36px" class="me-2">
                                        <div>
                                            <h6 class="mb-0">Let's Create Blog</h6>
                                            <p class="small mb-0">https://blog.lets-create.cz/...</p>
                                        </div>
                                    </div>
                                    <h4 id="seo-preview-title"></h4>
                                    <p class="col-md-6 col-lg-5 col-xl-4 small mb-0" id="seo-preview-description"></p>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="nav-content" role="tabpanel" aria-labelledby="nav-content-tab" tabindex="0">
                            <div class="mb-4">
                                <label for="content" class="panel-form-label">Obsah článku<span class="text-red">*</span></label>
                                <textarea class="panel-form-control" name="content" id="content" rows="20" required><?= $content ?></textarea>
                            </div>
                            <div>
                                <input type="hidden" name="status" value="Draft">
                                <input type="hidden" name="last_updated" value="<?php echo date('Y-m-d H:i:s'); ?>">
                                <input type="hidden" name="date" value="<?= trim($date) ?>">
                            </div>
                        </div>
                    </div>
                </section>
            </form>
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

    if ($can_access) {
        ?>
        <script>
            const article_title = document.getElementById("article-title");
            const title_counter = document.getElementById("title-counter");
            const title_length = document.getElementById("title-length");

            const seo_preview_title = document.getElementById("seo-preview-title");
            const seo_preview_description = document.getElementById("seo-preview-description");

            const colors = {
                dangerous: "#FF1F47",
                warning: "#FFA900",
                safe: "#00C853",
                primary: "#007BFF"
            };

            const title = document.getElementById("title");
            title.addEventListener("input", function() {
                const value = this.value;
                const length = value.length;
                article_title.innerText = length > 16 ? value.substring(0, 16) + "..." : value || "Název článku";
                title_length.innerText = length;

                seo_preview_title.innerText = length > 40 ? value.substring(0, 40) + "..." : value || "Název článku";

                title_counter.style.backgroundColor = length > 40 ? colors.dangerous : length > 32 ? colors.warning : length > 0 ? colors.safe : colors.primary;
            });

            const description_counter = document.getElementById("description-counter");
            const description_length = document.getElementById("description-length");
            const description = document.getElementById("description");

            description.addEventListener("input", function() {
                const value = this.value;
                const length = value.length;
                description_length.innerText = length;

                description_counter.style.backgroundColor = length > 160 ? colors.dangerous : length > 128 ? colors.warning : length > 0 ? colors.safe : colors.primary;
            });

            const description_seo_counter = document.getElementById("description-seo-counter");
            const description_seo_length = document.getElementById("description-seo-length");
            const seo_description = document.getElementById("seo-description");

            seo_description.addEventListener("input", function() {
                const value = this.value;
                const length = value.length;
                description_seo_length.innerText = length;

                seo_preview_description.innerText = length > 160 ? value.substring(0, 160) + "..." : value || "Lorem ipsum dolor sit amet consectetur adipisicing elit. Laboriosam, atque odit nulla quia porro nostrum voluptatum obcaecati dolorem repellat, dolorum cum, a cupiditate quibusdam! Corporis eaque at laborum quod aspernatur?";

                description_seo_counter.style.backgroundColor = length > 160 ? colors.dangerous : length > 128 ? colors.warning : length > 0 ? colors.safe : colors.primary;
            });

            const keywords_seo_counter = document.getElementById("keywords-seo-counter");
            const keywords_seo_length = document.getElementById("keywords-seo-length");
            const seo_keywords = document.getElementById("seo-keywords");

            seo_keywords.addEventListener("input", function() {
                const value = this.value;
                const length = value.length;
                keywords_seo_length.innerText = length;

                keywords_seo_counter.style.backgroundColor = length > 160 ? colors.dangerous : length > 128 ? colors.warning : length > 0 ? colors.safe : colors.primary;
            });

            const content = document.getElementById("content");
            let last_title = "";

            function autosave() {

                const title = document.getElementById("title").value;
                const description = document.getElementById("description").value;
                const head_image = document.getElementById("head_image").value;
                const author = document.getElementById("author").value;
                const categories = document.getElementById("categories").value;
                const tags = document.getElementById("tags").value;
                const seo_description = document.getElementById("seo-description").value;
                const seo_keywords = document.getElementById("seo-keywords").value;
                const content = document.getElementById("content").value;
                const premium_content = false;
                const status = document.getElementsByName("status")[0].value;
                const date = document.getElementsByName("date")[0].value;

                const data = {
                    title: title,
                    description: description,
                    head_image: head_image,
                    author: author,
                    categories: categories,
                    tags: tags,
                    seo_description: seo_description,
                    seo_keywords: seo_keywords,
                    content: content,
                    premium_content: false,
                    last_title: last_title,
                    status: status,
                    date: date
                };

                // If title is set, create a new markdown file in ./posts/ directory
                if (title.length > 0) {
                    // Save the file using generate_post.php
                    fetch("./generate_post.php", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json"
                        },
                        body: JSON.stringify(data)
                    }).then(response => {
                        if (response.ok) {
                            // console.log("Post saved successfully!");
                            last_title = title;
                        } else {
                            console.error("Failed to save post!");
                        }
                    })
                }
            }

            // Autosave this document every 60 seconds
            setInterval(autosave, 60000);
        </script>
        <?php
    }
    
    ?>

    <?php require_once "{$_SERVER['DOCUMENT_ROOT']}/components/scripts.php"; ?>
</body>
</html>