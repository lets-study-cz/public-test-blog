<?php
require_once "{$_SERVER['DOCUMENT_ROOT']}/panel/db.php";
// Get all the files in the posts directory
$pages = $_GET['page'] ?? 1;
$start = $_GET['start'] ?? 0;
$stop = $_GET['stop'] ?? 14;

$dir = "{$_SERVER['DOCUMENT_ROOT']}/posts";
$files = scandir($dir);
$files = array_diff($files, ['.', '..']);

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
        } elseif (strpos($meta, 'description:') !== false) {
            $description = str_replace(['description: ', '"'], '', $meta);
        } elseif (strpos($meta, 'head_image:') !== false) {
            $head_image = str_replace(['head_image: ', '"'], '', $meta);
        } elseif (strpos($meta, 'status:') !== false) {
            $status = str_replace(['status: ', '"'], '', $meta);
        }
    }

    // Split all tags and categories by comma
    $categories = explode(',', $categories);
    $tags = explode(',', $tags);

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
        'author' => $author['display_name'],
        'description' => $description,
        'head_image' => $head_image,
        'status' => $status,
        'file_name' => $file_name,
    ];

    // Sort the posts by date
    usort($posts, function ($a, $b) {
        return strtotime($b['date']) - strtotime($a['date']);
    });
}

echo '[';
for ($i = $start; $i < $stop; $i++) {
    if (isset($posts[$i])) {
        echo json_encode($posts[$i]);
        if ($i < $stop - 1 && isset($posts[$i + 1])) {
            echo ',';
        }
    }
}
echo ']';