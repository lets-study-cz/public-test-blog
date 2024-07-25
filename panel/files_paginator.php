<?php
require_once "{$_SERVER['DOCUMENT_ROOT']}/panel/db.php";
// Get all the files in the posts directory
$pages = $_GET['page'] ?? 1;
$start = $_GET['start'] ?? 0;
$stop = $_GET['stop'] ?? 14;

$dir = "{$_SERVER['DOCUMENT_ROOT']}/images";
$files = scandir($dir);
$files = array_diff($files, ['.', '..']);

// Loop through all the files
foreach ($files as $file) {
    // Get file name
    $file_name = explode('.', $file)[0] . "." . explode('.', $file)[1];
    $file_extension = explode('.', $file)[2];
    $file_path = "/images/$file";
    $file_size = filesize($dir . '/' . $file);
    $file_size = round($file_size / 1024, 2);
    $file_size = round($file_size / 1024, 2);
    $file_size = $file_size . ' MB';
    $file_date = date('Y-m-d H:i:s', filemtime($dir . '/' . $file));

    // Add the file to the files array
    $files_array[] = [
        'name' => $file_name,
        'extension' => $file_extension,
        'path' => $file_path,
        'size' => $file_size,
        'date' => $file_date
    ];
}

// Sort the files array by date
usort($files_array, function ($a, $b) {
    return strtotime($b['date']) - strtotime($a['date']);
});

echo '[';
for ($i = $start; $i < $stop; $i++) {
    if (isset($files_array[$i])) {
        echo json_encode($files_array[$i]);
        if ($i < $stop - 1 && isset($files_array[$i + 1])) {
            echo ',';
        }
    }
}
echo ']';