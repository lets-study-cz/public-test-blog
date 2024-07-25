<?php
require_once('./vendor/erusev/parsedown/Parsedown.php');

$parsedown = new Parsedown();

// Get the filter from the URL
$filter = $_GET['filter'] ?? 'Všechny příspěvky';

// Get the start index from the URL
$startIndex = $_GET['start'] ?? 0;

// Connect to the database
require_once "{$_SERVER['DOCUMENT_ROOT']}/panel/db.php";

// Get the posts from the posts directory
$dir = './posts';
$files = scandir($dir);
$files = array_diff($files, ['.', '..']);

$posts = [];
$loaded_posts_index = $startIndex;
$index_stop = $startIndex + 12;

require_once "{$_SERVER['DOCUMENT_ROOT']}/components/head.php";

function get_post($files, &$loaded_posts_index, $index_stop, $dir, $filter, $conn, $parsedown) {
    foreach ($files as $file) {
        $loaded_posts_index++;
    
        /* if ($loaded_posts_index > $index_stop) {
            break;
        } */
    
        // Get the content of the file
        $content = file_get_contents("$dir/$file");
        $file_name = explode('.', $file)[0];
    
        // Get metadata
        preg_match('/---(.*?)---(.*)/s', $content, $matches);
        $metadata = array_map('trim', explode("\n", trim($matches[1])));
        $content = $parsedown->text(trim($matches[2]));
    
        // Extract metadata
        $post_data = [
            'title' => '',
            'date' => '',
            'author' => '',
            'categories' => [],
            'tags' => [],
            'description' => '',
            'head_image' => '',
        ];
    
        foreach ($metadata as $meta) {
            list($key, $value) = explode(':', $meta, 2);
            $key = trim($key);
            $value = trim($value, " \t\n\r\0\x0B\"");
            $post_data[$key] = $value;
        }
    
        // Split categories and tags
        $post_data['categories'] = explode(',', $post_data['categories']);
        $post_data['tags'] = explode(',', $post_data['tags']);
    
        // Query author details
        $stmt = $conn->prepare("SELECT display_name, avatar FROM users WHERE nickname = ?");
        $stmt->bind_param("s", $post_data['author']);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();
    
        // Add post to array
        $posts[] = array_merge($post_data, ['author' => $result]);
    
        // Sort posts by date
        usort($posts, function ($a, $b) {
            return strtotime($b['date']) - strtotime($a['date']);
        });
    
        // Filter and display posts
        if ($filter !== 'Všechny příspěvky' && !in_array($filter, $post_data['categories'])) {
            continue;
        }
    
        // Display posts
        ?>
        <div class="col-12 col-md-6 col-lg-4">
            <div class="blog-post">
                <a href="./view_article.php?post=<?= $file_name ?>">
                    <img src="<?= $post_data['head_image'] ?>" class="blog-post-image img-fluid" alt="...">
                </a>
                <div class="blog-post-body">
                    <h5 class="serif ls-2 mb-2"><?= implode(', ', $post_data['categories']) ?></h5>
                    <a href="./view_article.php?post=<?= $file_name ?>" class="blog-post-header fs-2"><?= $post_data['title'] ?></a>
                    <div class="d-flex align-items-center my-2">
                        <img src="<?= $result['avatar'] ?>" alt="Avatar" class="blog-avatar" loading="lazy">
                        <div class="blog-author ms-2">
                            <h6 class="mb-0 blog-author-name"><?= $result['display_name'] ?></h6>
                            <p class="mb-0 blog-date serif"><?= date('d.m.y H:i', strtotime($post_data['date'])) ?></p>
                        </div>
                    </div>
                    <p class="blog-post-description"><?= $post_data['description'] ?></p>
                </div>
            </div>
        </div>
        <?php
    }
}
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
        <h1 class="text-center display-1 ls-1 serif">Příspěvky - <?= $filter ?></h1>
        <?php require_once "{$_SERVER['DOCUMENT_ROOT']}/components/search_bar.php"; ?>
    </div>

    <div class="container-fluid col-11 col-lg-10 mt-3 mt-lg-5 min-vh-100">
        <div class="row g-3 m-0 w-100">
            <?php 
                // Return the posts
                get_post($files, $loaded_posts_index, $index_stop, $dir, $filter, $conn, $parsedown);

                // Make a button to load more posts
                /* if ($loaded_posts_index < count($files)) {
                    ?>
                    <div class="col-12 text-center">
                        <button class="btn btn-primary" id="load-more" onclick="<?php echo get_post($files, $loaded_posts_index, $index_stop, $dir, $filter, $conn, $parsedown) ?>">Načíst další</button>
                    </div>
                    <?php
                }*/
            ?>
        </div>
    </div>

    <?php
        require_once "{$_SERVER['DOCUMENT_ROOT']}/components/footer.php";
        require_once "{$_SERVER['DOCUMENT_ROOT']}/components/scripts.php";
    ?>
</body>
</html>