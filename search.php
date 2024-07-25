<?php
require_once('./vendor/erusev/parsedown/Parsedown.php');

$parsedown = new Parsedown();

$search = $_GET['search'] ?? '';

// Connect to the database
require_once "{$_SERVER['DOCUMENT_ROOT']}/panel/db.php";

// Get the posts from the posts directory
$dir = './posts';
$files = scandir($dir);
$files = array_diff($files, ['.', '..']);

$posts = [];
$found_posts = 0;

require_once "{$_SERVER['DOCUMENT_ROOT']}/components/head.php";
?>
<title>LC Blog | Příspěvky</title>
<body>
    <?php require_once "{$_SERVER['DOCUMENT_ROOT']}/components/loader.php"; ?>

    <div class="header-gradient d-none d-lg-block"></div>

    <nav class="navbar navbar-main navbar-expand-lg container-fluid col-11 col-lg-10">
        <div class="container-fluid">
            <img src="./assets/content/logo.svg" alt="Logo" width="28px" class="me-2">
            <a class="navbar-brand serif fw-bold" href="/">LC Blog</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                <i class="ph-bold ph-list"></i>
            </button>
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link" href="/">Domů</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/about">O nás</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/articles">Příspěvky</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/search">Vyhledávání</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/panel/login">Administrace</a>
                    </li>
                </ul>

                <div class="d-flex align-items-center ms-auto mb-2 mb-lg-0">
                    <a href="/panel/login" class="btn btn-primary me-3">Přihlásit se</a>
                    <a href="/panel/login" class="nav-link px-2 py-2">Registrovat se</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="container-fluid col-11 col-lg-10 mt-3 mt-lg-5">
        <div class="d-none d-lg-block">
        </div>
        <h1 class="text-center display-1 ls-1 serif">Vyhledávat...</h1>
        <?php require_once "{$_SERVER['DOCUMENT_ROOT']}/components/search_bar.php"; ?>
    </div>

    <div class="container-fluid col-11 col-lg-10 mt-3 mt-lg-5">
        <form class="mb-5 row m-0 g-3 w-100" method="get">
            <div class="col">
                <input type="text" class="form-control py-2" id="search" name="search" placeholder="Hledat..." value="<?= $search ?>">
            </div>
            <div class="col-auto">
                <button class="btn btn-primary py-2 d-flex align-items-center" id="search-button" role="button" type="submit">Vyhledat <i class="ph-bold ph-magnifying-glass ms-2"></i></button>
            </div>
        </form>
        <h4>Výsledky vyhledávání</h4>
        <div class="row g-3 m-0 w-100" id="posts">
            <?php 
            if (!isset($search) || $search === '') {
                ?>
                <h1 class="text-center">Aktuálně nic nevyhledáváš.</h1>
                <p class="text-center lead">Začni hledat pomocí hledáčku výše. Výsledky se objeví zde.</p>
                <?php
                return;
            }

            // Loop through all the files
            foreach ($files as $file) {
                // Get the content of the file
                $content = file_get_contents($dir . '/' . $file);
                $file_name = explode('.', $file)[0];

                // Get the metadata of the file
                $metadata = explode('---', $content)[1];
                $metadata = explode("\n", $metadata);

                // Initialize the variables
                $title = '';
                $date = '';
                $author = '';
                $categories = '';
                $tags = '';
                $head_image = '';

                // Loop through all the metadata
                foreach ($metadata as $meta) {
                    if (strpos($meta, 'title:') !== false) {
                        $title = str_replace(['title: ', '"'], '', $meta);
                    } elseif (strpos($meta, 'date:') !== false) {
                        $date = str_replace(['date: ', '"'], '', $meta);
                    } elseif (strpos($meta, 'author:') !== false) {
                        $author = trim(str_replace(['author: ', '"'], '', $meta));
                    } elseif (strpos($meta, 'categories:') !== false) {
                        $categories = trim(str_replace(['categories: ', '"'], '', $meta));
                    } elseif (strpos($meta, 'tags:') !== false) {
                        $tags = str_replace(['tags: ', '"'], '', $meta);
                    } elseif (strpos($meta, 'description:') !== false) {
                        $description = str_replace(['description: ', '"'], '', $meta);
                    } elseif (strpos($meta, 'head_image:') !== false) {
                        $head_image = str_replace(['head_image: ', '"'], '', $meta);
                    } elseif (strpos($meta, 'premium_content:') !== false) {
                        $premiumContent = str_replace(['premium_content: ', '"'], '', $meta) === 'true';
                    }
                }

                // Split all tags and categories by comma
                $categories = explode(',', $categories);
                $tags = explode(',', $tags);

                // Get the content of the post
                $content = preg_split('/---/', $content, 3)[2];
                $content = $parsedown->text($content);

                $stmt = $conn->prepare("SELECT * FROM users WHERE nickname = ?");
                $stmt->bind_param("s", $author);
                $stmt->execute();
                $result = $stmt->get_result();
                $author = $result->fetch_assoc();
                $stmt->close();
        
                // Add the post to the posts array
                $posts[] = [
                    'title' => $title,
                    'date' => $date,
                    'author' => $author,
                    'categories' => $categories,
                    'tags' => $tags,
                    'description' => $description,
                    'head_image' => $head_image,
                    'content' => $content,
                ];

                $nickname = $author['display_name'];
                $avatar = $author['avatar'];

                // Sort the posts by date
                usort($posts, function ($a, $b) {
                    return strtotime($b['date']) - strtotime($a['date']);
                });

                // Check if the search query is somewhere in the post title, description or content
                if (strpos(strtolower($title), strtolower($search)) === false && strpos(strtolower($description), strtolower($search)) === false && strpos(strtolower($content), strtolower($search)) === false) {
                    continue;
                } else {
                    $found_posts++;
                }

                // Display the posts
                ?>
                <div class="col-12 col-md-6 col-lg-4">
                    <div class="blog-post">
                        <a href="./view_article.php?post=<?php echo $file ?>">
                            <img src="<?php echo $head_image ?>" class="blog-post-image img-fluid" alt="...">
                        </a>
                        <div class="blog-post-body">
                            <h5 class="serif ls-2 mb-2"><?= implode(', ', $categories) ?></h5>
                            <a href="./view_article.php?post=<?php echo $file_name ?>" class="blog-post-header fs-2"><?= $title ?></a>
                            <div class="d-flex align-items-center my-2">
                                <img src="<?php echo $avatar ?>" alt="Avatar" class="blog-avatar" loading="lazy">
                                <div class="blog-author ms-2">
                                    <h6 class="mb-0 blog-author-name"><?= $nickname ?></h6>
                                    <p class="mb-0 blog-date serif"><?= date('d.m.y H:i', strtotime($date)) ?></p>
                                </div>
                            </div>
                            <p class="blog-post-description"><?= $description ?></p>
                        </div>
                    </div>
                </div>
                <?php
            }

            // Check if there is anything in div id posts
            if ($found_posts === 0) {
                ?>
                <div>
                    <h1 class="serif display-3 ls-2">Nic nebylo nalezeno.</h1>
                    <p class="lead">Zkuste hledat jiný výraz.</p>
                </div>
                <?php
            }
            ?>
        </div>
    </div>

    <?php
        require_once "{$_SERVER['DOCUMENT_ROOT']}/components/footer.php";
        require_once "{$_SERVER['DOCUMENT_ROOT']}/components/scripts.php";
    ?>
</body>
</html>