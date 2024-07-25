<?php
require_once "{$_SERVER['DOCUMENT_ROOT']}/components/head.php";
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

    if ($user['group'] == "admin") {
        $can_access = true;
        $found = false;
        $edited = false;

        if (isset($_POST['edit_user'])) {
            $nickname = $_POST['nickname'];
            $display_name = $_POST['display_name'];
            $group = $_POST['group'];
            $email = $_POST['email'];

            // Check if password is set
            if (empty($_POST['password'])) {
                $stmt = $conn->prepare("UPDATE users SET nickname = ?, display_name = ?, `group` = ?, email = ? WHERE nickname = ?");
                $stmt->bind_param("sssss", $nickname, $display_name, $group, $email, $_GET['user']);
                $stmt->execute();
                $stmt->close();

                $found = true;
                $edited = true;
            } else {
                $password = $_POST['password'];
                $password = password_hash($password, PASSWORD_DEFAULT);

                $stmt = $conn->prepare("UPDATE users SET nickname = ?, display_name = ?, `group` = ?, email = ?, password = ? WHERE nickname = ?");
                $stmt->bind_param("ssssss", $nickname, $display_name, $group, $email, $password, $_GET['user']);
                $stmt->execute();
                $stmt->close();

                $found = true;
                $edited = true;
            }
        }
    }
}


?>
<title>LC Blog | Upravit uživatele</title>
<body>
    <div class="header-gradient d-none d-lg-block"></div>

    <?php require_once "{$_SERVER['DOCUMENT_ROOT']}/components/loader.php"; 
    if ($can_access) {
        require_once "{$_SERVER['DOCUMENT_ROOT']}/components/sidebar.php";

        $user = $_GET['user'];

        // Open database and get the user
        $stmt = $conn->prepare("SELECT * FROM users WHERE nickname = ?");
        $stmt->bind_param("s", $user);
        $stmt->execute();
        $result = $stmt->get_result();
        $user_db = $result->fetch_assoc();
        $stmt->close();
        if (isset($user) || empty($user_db)) {
            $found = true;
        } else {
            $found = false;
        }

        $nickname = $user_db['nickname'];
        $display_name = $user_db['display_name'];
        $group = $user_db['group'];
        $avatar = $user_db['avatar'];
        $email = $user_db['email'];

        // Get permissions from permissions.json
        $permissions = json_decode(file_get_contents("{$_SERVER['DOCUMENT_ROOT']}/panel/permissions.json"), true);
        ?>

        <div class="container-fluid col-11 col-lg-10 mt-3 mt-lg-5">
            <h1 class="display-1">Upravit uživatele</h1>
            <?php
            if ($found) {
                ?>
            <p>Právě upravuješ <span class="fw-bold"><?php echo $display_name ?></span></p>
            <form method="post">
                <div class="row g-3 m-0 w-100">
                    <div class="col-12 col-lg-4">
                        <div class="mb-3">
                            <label for="title" class="form-label">Přezdívka</label>
                            <input type="text" class="form-control" id="nickname" name="nickname" value="<?php echo $nickname ?>" required>
                        </div>
                    </div>
                    <div class="col-12 col-lg-4">
                        <div class="mb-3">
                            <label for="categories" class="form-label">Zobrazované jméno</label>
                            <input type="text" class="form-control" id="display_name" name="display_name" value="<?php echo $display_name ?>" required>
                        </div>
                    </div>
                    <div class="col-12 col-lg-4">
                        <div class="mb-3">
                            <label for="author" class="form-label">Práva</label>
                            <select class="form-select form-control" id="group" name="group" required>
                                <?php
                                foreach ($permissions as $permission) {
                                    if ($permission == $group) {
                                        echo "<option value='$permission' selected>$permission</option>";
                                    } else {
                                        echo "<option value='$permission'>$permission</option>";
                                    }
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-12 col-lg-4">
                        <div class="mb-3">
                            <label for="categories" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" value="<?php echo $email ?>" required>
                        </div>
                    </div>
                    <div class="col-12 col-lg-4">
                        <div class="mb-3">
                            <label for="categories" class="form-label">Změna hesla</label>
                            <input type="password" class="form-control" id="password" name="password">
                        </div>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary" name="edit_user">Upravit uživatele</button>
            </form>
            <?php
            } else if (!$found) {
                ?>
                <p>Uživatel nebyl nalezen</p>
                <a href="./list_users.php" class="btn btn-primary">Zpět na uživatele</a>
                <?php
            } else if ($edited) {
                ?>
                <p>Uživatel byl úspěšně upraven!</p>
                <a href="./list_users.php" class="btn btn-primary">Zpět na uživatele</a>
                <?php
            }
            ?>
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
    
    ?>

    <?php require_once "{$_SERVER['DOCUMENT_ROOT']}/components/scripts.php"; ?>
</body>
</html>