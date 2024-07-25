<?php
    // Get the JSON data
    $json = file_get_contents('php://input');

    // Decode the JSON data
    $data = json_decode($json, true);

    // Check if the JSON data is not empty
    if (!empty($data) || !empty($_POST)) {
        // Get the data from the JSON data or from $_POST
        $title = !empty($data['title']) ? $data['title'] : $_POST['title'];
        $description = !empty($data['description']) ? $data['description'] : $_POST['description'];
        $author = !empty($data['author']) ? $data['author'] : $_POST['author'];
        $categories = !empty($data['categories']) ? $data['categories'] : $_POST['categories'];
        $tags = !empty($data['tags']) ? $data['tags'] : $_POST['tags'];
        $head_image = !empty($data['head_image']) ? $data['head_image'] : $_POST['head_image'];
        $content = !empty($data['content']) ? $data['content'] : $_POST['content'];
        $seo_description = !empty($data['seo_description']) ? $data['seo_description'] : $_POST['seo_description'];
        $seo_keywords = !empty($data['seo_keywords']) ? $data['seo_keywords'] : $_POST['seo_keywords'];
        $status = !empty($data['status']) ? $data['status'] : $_POST['status'];
        $premiumContent = false;
        date_default_timezone_set('Europe/Paris');
        $date = !empty($data['date']) ? $data['date'] : $_POST['date'];
        $last_updated = !empty($data['last_updated']) ? $data['last_updated'] : date('Y-m-d H:i:s');

        if (isset($_POST['publish'])) {
            $status = "Published";
        }

        if (!empty($data)) {
            $last_title = $data['last_title'] ?? "";
            $title3 = iconv('UTF-8', 'ASCII//TRANSLIT', $last_title);
            $filename = preg_replace('/[^a-z0-9]+/i', '', strtolower($title3));
    
            $title2 = iconv('UTF-8', 'ASCII//TRANSLIT', $title);
            $file_name = preg_replace('/[^a-z0-9]+/i', '', strtolower($title2));
        } else {
            $title2 = iconv('UTF-8', 'ASCII//TRANSLIT', $title);
            $file_name = preg_replace('/[^a-z0-9]+/i', '', strtolower($title2));
        }

        // If date is already set, skip this step
        if ($date == "") {
            $date = date('Y-m-d H:i:s');
        }

        $metadata = <<<EOT
        ---
        title: "$title"
        description: "$description"
        seo_desc: "$seo_description"
        seo_keywords: "$seo_keywords"
        head_image: "$head_image"
        author: "$author"
        categories: "$categories"
        tags: "$tags"
        date: "$date"
        last_updated: "$last_updated"
        status: "$status"
        premium_content: $premiumContent
        ---\n
        EOT;
        $post = $metadata . $content;
    }

    // Pokud se $file_name a $filename neshodují, smažte soubor s názvem $filename a vytvořte nový soubor s názvem $file_name
    if (!empty($data)) {
        if ($file_name != $filename) {
            // Sestavte cestu k souboru
            $oldFilePath = "../posts/" . $filename . ".md";
            $newFilePath = "../posts/" . $file_name . ".md";
    
            unlink($oldFilePath);
    
            // Vytvořte nový soubor s novým názvem
            file_put_contents($newFilePath, $post);
        } else {
            // Pokud se názvy shodují, jednoduše aktualizujte existující soubor
            $filePath = "../posts/" . $filename . ".md";
            file_put_contents($filePath, $post);
            header("./list_articles.php");
        }
    } else {
        // Pokud se $data rovná prázdnému poli, vytvořte nový soubor
        $filePath = "../posts/" . $file_name . ".md";
        file_put_contents($filePath, $post);
        header("./list_articles.php");
    }
?>