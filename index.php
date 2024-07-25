<?php
require_once('./vendor/erusev/parsedown/Parsedown.php');

$parsedown = new Parsedown();

require_once "{$_SERVER['DOCUMENT_ROOT']}/components/head.php";
?>
<title>Let's Create Blog</title>
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
        <h1 class="text-center display-1 ls-1 serif">Let's Create Blog</h1>
        <?php require_once "{$_SERVER['DOCUMENT_ROOT']}/components/search_bar.php"; ?>
    </div>

    <?php
    // Get all posts from /posts directory

    $dir = './posts';
    $files = scandir($dir);
    $files = array_diff($files, ['.', '..']);

    $posts = [];
    require_once "{$_SERVER['DOCUMENT_ROOT']}/panel/db.php";

    // Loop through all the files
    foreach ($files as $file) {
        // Get the content of the file

        // Get file name for the post
        $file_name = explode('.', $file)[0];

        $content = file_get_contents($dir . '/' . $file);

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
        $premiumContent = false;

        // Loop through all the metadata
        foreach ($metadata as $meta) {
            if (strpos($meta, 'title:') !== false) {
                $title = str_replace(['title: ', '"'], '', $meta);
            } elseif (strpos($meta, 'date:') !== false) {
                $date = str_replace(['date: ', '"'], '', $meta);
            } elseif (strpos($meta, 'author:') !== false) {
                $author = trim(str_replace(['author: ', '"'], '', $meta));
            } elseif (strpos($meta, 'categories:') !== false) {
                $categories = str_replace(['categories: ', '"'], '', $meta);
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
            'file_name' => $file_name,
            'title' => $title,
            'description' => $description,
            'head_image' => $head_image,
            'date' => $date,
            'author' => $author,
            'categories' => $categories,
            'tags' => $tags,
            'premium_content' => $premiumContent,
            'avatar' => $author['avatar'],
            'group' => $author['group'],
            'nickname' => $author['display_name'],
        ];
    }

    // Sort the posts by date
    usort($posts, function ($a, $b) {
        return strtotime($b['date']) - strtotime($a['date']);
    });
    ?>

    <div class="container-fluid col-11 col-lg-10 mt-3 mt-lg-5">
        <div class="row g-3 m-0 w-100">
            <div class="col-12 col-lg-5">
                <div class="blog-post">
                    <a href="./view_article.php?post=<?php echo $posts[0]["file_name"] ?>">
                        <img src="<?php echo $posts[0]["head_image"] ?>" class="blog-post-image img-fluid" alt="..." loading="lazy">
                    </a>
                    <div class="blog-post-body">
                        <h5 class="serif ls-2 mb-2"><?= implode(', ', $posts[0]["categories"]) ?></h5>
                        <a href="./view_article.php?post=<?php echo $posts[0]["file_name"] ?>" class="blog-post-header fs-2"><?= $posts[0]["title"] ?></a>
                        <div class="d-flex align-items-center my-2">
                            <img src="<?php echo $posts[0]["avatar"] ?>" alt="Avatar" class="blog-avatar">
                            <div class="blog-author ms-2">
                                <h6 class="mb-0 blog-author-name"><?= $posts[0]["nickname"] ?></h6>
                                <p class="mb-0 blog-date serif"><?= $posts[0]["group"] ?></p>
                            </div>
                            <div class="ms-auto text-end">
                                <h6 class="mb-0 blog-author-name">Zveřejněno</h6>
                                <p class="mb-0 blog-date serif"><?= date('d.m.y H:i', strtotime($posts[0]["date"])) ?></p>
                            </div>
                        </div>
                        <p class="blog-post-description"><?= $posts[0]["description"] ?></p>
                    </div>
                </div>
            </div>
            <div class="col-12 col-lg-7">
                <div class="row g-1 g-lg-5 m-0 w-100">
                    <div class="col-12 col-lg-6 mt-0">
                        <div class="blog-post">
                            <a href="./view_article.php?post=<?php echo $posts[1]["file_name"] ?>">
                                <img src="<?php echo $posts[1]["head_image"] ?>" class="blog-post-image img-fluid" alt="..." loading="lazy">
                            </a>
                            <div class="blog-post-body">
                                <h5 class="serif ls-2 mb-2"><?= implode(', ', $posts[1]["categories"]) ?></h5>
                                <a href="./view_article.php?post=<?php echo $posts[1]["file_name"] ?>" class="blog-post-header fs-2"><?= $posts[1]["title"] ?></a>
                                <div class="d-flex align-items-center my-2">
                                    <img src="<?php echo $posts[1]["avatar"] ?>" alt="Avatar" class="blog-avatar">
                                    <div class="blog-author ms-2">
                                        <h6 class="mb-0 blog-author-name"><?= $posts[1]["nickname"] ?></h6>
                                        <p class="mb-0 blog-date serif"><?= $posts[1]["group"] ?></p>
                                    </div>
                                    <div class="ms-auto text-end">
                                        <h6 class="mb-0 blog-author-name">Zveřejněno</h6>
                                        <p class="mb-0 blog-date serif"><?= date('d.m.y H:i', strtotime($posts[1]["date"])) ?></p>
                                    </div>
                                </div>
                                <p class="blog-post-description"><?= $posts[1]["description"] ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-lg-6 mt-0">
                        <div class="blog-post">
                            <a href="./view_article.php?post=<?php echo $posts[2]["file_name"] ?>">
                                <img src="<?php echo $posts[2]["head_image"] ?>" class="blog-post-image img-fluid" alt="..." loading="lazy">
                            </a>
                            <div class="blog-post-body">
                                <h5 class="serif ls-2 mb-2"><?= implode(', ', $posts[2]["categories"]) ?></h5>
                                <a href="./view_article.php?post=<?php echo $posts[2]["file_name"] ?>" class="blog-post-header fs-2"><?= $posts[2]["title"] ?></a>
                                <div class="d-flex align-items-center my-2">
                                    <img src="<?php echo $posts[2]["avatar"] ?>" alt="Avatar" class="blog-avatar">
                                    <div class="blog-author ms-2">
                                        <h6 class="mb-0 blog-author-name"><?= $posts[2]["nickname"] ?></h6>
                                        <p class="mb-0 blog-date serif"><?= $posts[2]["group"] ?></p>
                                    </div>
                                    <div class="ms-auto text-end">
                                        <h6 class="mb-0 blog-author-name">Zveřejněno</h6>
                                        <p class="mb-0 blog-date serif"><?= date('d.m.y H:i', strtotime($posts[2]["date"])) ?></p>
                                    </div>
                                </div>
                                <p class="blog-post-description"><?= $posts[2]["description"] ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-lg-6 mt-0">
                        <div class="blog-post">
                            <a href="./view_article.php?post=<?php echo $posts[3]["file_name"] ?>">
                                <img src="<?php echo $posts[3]["head_image"] ?>" class="blog-post-image img-fluid" alt="..." loading="lazy">
                            </a>
                            <div class="blog-post-body">
                                <h5 class="serif ls-2 mb-2"><?= implode(', ', $posts[3]["categories"]) ?></h5>
                                <a href="./view_article.php?post=<?php echo $posts[3]["file_name"] ?>" class="blog-post-header fs-2"><?= $posts[3]["title"] ?></a>
                                <div class="d-flex align-items-center my-2">
                                    <img src="<?php echo $posts[3]["avatar"] ?>" alt="Avatar" class="blog-avatar">
                                    <div class="blog-author ms-2">
                                        <h6 class="mb-0 blog-author-name"><?= $posts[3]["nickname"] ?></h6>
                                        <p class="mb-0 blog-date serif"><?= $posts[3]["group"] ?></p>
                                    </div>
                                    <div class="ms-auto text-end">
                                        <h6 class="mb-0 blog-author-name">Zveřejněno</h6>
                                        <p class="mb-0 blog-date serif"><?= date('d.m.y H:i', strtotime($posts[3]["date"])) ?></p>
                                    </div>
                                </div>
                                <p class="blog-post-description"><?= $posts[3]["description"] ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-lg-6 mt-0">
                        <div class="blog-post">
                            <a href="./view_article.php?post=<?php echo $posts[4]["file_name"] ?>">
                                <img src="<?php echo $posts[4]["head_image"] ?>" class="blog-post-image img-fluid" alt="..." loading="lazy">
                            </a>
                            <div class="blog-post-body">
                                <h5 class="serif ls-2 mb-2"><?= implode(', ', $posts[4]["categories"]) ?></h5>
                                <a href="./view_article.php?post=<?php echo $posts[4]["file_name"] ?>" class="blog-post-header fs-2"><?= $posts[4]["title"] ?></a>
                                <div class="d-flex align-items-center my-2">
                                    <img src="<?php echo $posts[4]["avatar"] ?>" alt="Avatar" class="blog-avatar">
                                    <div class="blog-author ms-2">
                                        <h6 class="mb-0 blog-author-name"><?= $posts[4]["nickname"] ?></h6>
                                        <p class="mb-0 blog-date serif"><?= $posts[4]["group"] ?></p>
                                    </div>
                                    <div class="ms-auto text-end">
                                        <h6 class="mb-0 blog-author-name">Zveřejněno</h6>
                                        <p class="mb-0 blog-date serif"><?= date('d.m.y H:i', strtotime($posts[4]["date"])) ?></p>
                                    </div>
                                </div>
                                <p class="blog-post-description"><?= $posts[4]["description"] ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php require_once "{$_SERVER['DOCUMENT_ROOT']}/components/footer.php"; ?>

    <?php require_once "{$_SERVER['DOCUMENT_ROOT']}/components/scripts.php"; ?>
</body>
</html>