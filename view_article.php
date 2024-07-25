<?php
require_once('./vendor/erusev/parsedown/Parsedown.php');

$parsedown = new Parsedown();

$valid = true;

// Check if the post is set or post exists
if (!isset($_GET['post']) || !file_exists('./posts/' . $_GET['post'] . '.md')) {
    $valid = false;
} else {
    $posts = './posts/' . $_GET['post'] . '.md';

    $markdownContent = file_get_contents($posts);
    $htmlContent = $parsedown->text($markdownContent);
    
    $metadata = explode('---', $markdownContent)[1];
    $metadata = explode("\n", $metadata);
    
    $title = '';
    $date = '';
    $author = '';
    $categories = '';
    $tags = '';
    $head_image = '';
    $premiumContent = false;
    
    foreach ($metadata as $meta) {
        if (strpos($meta, 'title:') !== false) {
            $title = str_replace(['title: ', '"'], '', $meta);
        } elseif (strpos($meta, 'date:') !== false) {
            $date = str_replace(['date: ', '"'], '', $meta);
            $date = new DateTime($date);
            $date = $date->format('d.m.Y - H:i');
        } elseif (strpos($meta, 'author:') !== false) {
            $author = trim(str_replace(['author: ', '"'], '', $meta));
        } elseif (strpos($meta, 'categories:') !== false) {
            $categories = str_replace(['categories: ', '"'], '', $meta);
        } elseif (strpos($meta, 'tags:') !== false) {
            $tags = str_replace(['tags: ', '"'], '', $meta);
        } elseif (strpos($meta, 'premium_content:') !== false) {
            $premiumContent = str_replace(['premium_content: ', '"'], '', $meta) === 'true';
        } elseif (strpos($meta, 'head_image:') !== false) {
            $head_image = trim(str_replace(['head_image: ', '"'], '', $meta));
        }
    }
    
    // Split all tags and categories by comma
    $categories = implode(', ', explode(',', $categories));
    $tags = implode(', ', explode(',', $tags));
    
    $content = preg_split('/---/', $markdownContent, 3)[2];
    $content = $parsedown->text($content);

    require_once "{$_SERVER['DOCUMENT_ROOT']}/panel/db.php";

    $stmt = $conn->prepare("SELECT * FROM users WHERE nickname = ?");
    $stmt->bind_param("s", $author);
    $stmt->execute();
    $result = $stmt->get_result();
    $author = $result->fetch_assoc();
    $stmt->close();

    $author_avatar = $author['avatar'];
    $author_group = $author['group'];
    $author_nickname = $author['display_name'];

    // Rewrite group name based on the group id
    switch ($author_group) {
        case "admin":
            $author_group = 'Administrátor';
            break;
        case "editor":
            $author_group = 'Redaktor';
            break;
        default:
            $author_group = 'Neznámý';
            break;
    }

}
?>

<?php require_once "{$_SERVER['DOCUMENT_ROOT']}/components/head.php"; ?>
<body>
    <?php require_once "{$_SERVER['DOCUMENT_ROOT']}/components/loader.php"; ?>

    <?php require_once('./components/navbar.php'); ?>

    <?php if($valid) {
        ?>
        <div class="blog-image" style="background: linear-gradient(180deg, var(--background-20) 0%, var(--background) 100%), url('<?php echo $head_image ?>') center center / cover no-repeat;"></div>
        <div class="container-fluid col-11 col-lg-10 mt-5 pt-5">
            <h1 class="display-3 serif ls-1 mb-0"><?= $categories ?></h1>
            <h1 class="display-1 blog-header mb-0"><?= $title ?></h1>
            <h5 class="blog-date serif"><?= $date ?></h5>
            <div class="d-flex align-items-center mt-3">
                <img src="<?php echo $author_avatar ?>" alt="Avatar" class="blog-avatar">
                <div class="blog-author ms-2">
                    <h6 class="mb-0 blog-author-name"><?= $author_nickname ?></h6>
                    <p class="mb-0 blog-date serif"><?= $author_group ?></p>
                </div>
            </div>
        </div>
        <div class="container-fluid col-11 col-md-9 col-lg-7 col-xl-6 mt-3 min-content" id="content">
            <?= $content ?>
        </div>
        <?php
    } else {
        ?>
        <div class="container-fluid vh-100 d-flex flex-column justify-content-center align-items-center text-center">
            <h1 class="display-1 serif ls-1 fw-bold">404</h1>
            <h1 class="display-4">Nemohli jsme najít tvůj hledaný článek...</h1>
            <a href="/" class="btn btn-primary mt-3">Přejít zpět domů</a>
        </div>
        <?php
    }

    require_once "{$_SERVER['DOCUMENT_ROOT']}/components/footer.php";
    require_once "{$_SERVER['DOCUMENT_ROOT']}/components/scripts.php";
?> 
</body>
</html>