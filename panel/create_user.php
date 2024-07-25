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
    
        if ($user['group'] == "editor" || $user['group'] == "admin") {
            $can_access = true;

            if (isset($_POST['name']) && isset($_POST['email']) && isset($_POST['password'])) {
                $name = $_POST['name'];
                $email = $_POST['email'];
                $password = $_POST['password'];

                require_once('db.php');

                // Check if user already exists
                $stmt = $conn->prepare('SELECT nickname FROM users WHERE nickname = ?');
                $stmt->bind_param('s', $name);
                $stmt->execute();
                $result = $stmt->get_result();
                $stmt->close();

                $stmt2 = $conn->prepare('SELECT email FROM users WHERE email = ?');
                $stmt2->bind_param('s', $email);
                $stmt2->execute();
                $result2 = $stmt2->get_result();
                $stmt2->close();

                // Predefine variables
                $name_error = false;
                $special_chars_error = false;
                $pass_error = false;
                $checkbox_error = false;
                
                // Check if the username exists or if the email is already registered
                if($result->num_rows > 0 || !filter_var($email, FILTER_VALIDATE_EMAIL) || $result2->num_rows > 0) {
                    $name_error = true;
                }

                // Check if the username has special characters
                if (!preg_match('/^[a-zA-Z0-9_.]+$/', $name)) {
                    $special_chars_error = true;
                }

                // Check if the password is corresponding with the confirmation password
                if ($password != $_POST['password']) {
                    $pass_error = true;
                }

                // Check if the checkbox was checked
                if (!isset($_POST['tos'])) {
                    $checkbox_error = true;
                }

                // Get user's IP address
                // $ip = $_SERVER['REMOTE_ADDR'];
                // echo $ip;

                // Encrypt password with SHA-256
                $password = password_hash($password, PASSWORD_DEFAULT);

                if ($name_error || $special_chars_error || $pass_error || $checkbox_error) {
                    $conn->close();
                } else {
                    $stmt = $conn->prepare('INSERT INTO users (nickname, email, password) VALUES (?, ?, ?)');
                    $stmt->bind_param('sss', $name, $email, $password);
                    $stmt->execute();
            
                    $conn->close();
            
                    echo 'You have been registered';
                    header('Location: success.php');
                    exit;
            
                    // Encrypt password with SHA-256
                    $password = password_hash($password, PASSWORD_DEFAULT);
            
                    $stmt = $conn->prepare('INSERT INTO users (nickname, email, password) VALUES (?, ?, ?, ?)');
                    $stmt->bind_param('sss', $name, $email, $password);
                    $stmt->execute();
            
                    $conn->close();
            
                    echo 'You have been registered';
                    header('Location: login.php');
                    exit;
                }
            }
        }
    }
?>
<title>LC Blog | Vytvořit uživatele</title>
<body>
<section class="vh-100 p-2 container-fluid" id="login-1">
        <div class="row w-100 m-0 g-0 h-100">
            <div class="col-12 col-lg-6 d-flex flex-column justify-content-center align-items-center">
                <div class="p-2 col-xl-6 col-lg-8 col-md-10 col-12">
                    <div class="mb-4">
                        <h1>Nový uživatel</h1>
                    </div>
                    <?php
                    if ($can_access) {
                        ?>
                        <form method="post">
                            <div class="mb-3">
                                <label for="name" class="form-label">Přezdívka</label>
                                <input type="text" class="form-control" id="name" name="name" placeholder="např. super_mnamka" maxlength="16" minlength="3" required>
                                <p class="small mt-1 text-white-50">Přezdívka musí mít minimálně 3 znaky a maximálně až 16.</p>
                            </div>
                            <div class="mb-3">
                                <label for="surname" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" placeholder="tvuj@email.cz" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Heslo</label>
                                <input type="password" class="form-control" id="password" name="password" placeholder="********" minlength="7" maxlength="48" required>
                                <p class="small mt-1 text-white-50">Vytvoř si silné heslo, které má alespoň 7 až 48 znaků!</p>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Potvrzení hesla</label>
                                <input type="password" class="form-control" id="password" name="password" placeholder="********" minlength="7" maxlength="48" required>
                                <p class="small mt-1 text-white-50">Zadej své heslo znovu pro kontrolu.</p>
                            </div>
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" name="tos" type="checkbox" value="" id="flexCheckDefault">
                                    <label class="form-check-label" for="flexCheckDefault">
                                        Chci vytvořit tohoto uživatele a souhlasím s podmínkami použití a ukládáním osobních údajů.
                                    </label>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-lg btn-primary w-100">Zaregistrovat</button>
                        </form>
                        <?php
                            // Check if the username exists
                            if (isset($name_error)) {
                                echo '<div class="alert alert-warning mt-2">
                                    <i class="ph ph-warning"></i> Tento účet již existuje nebo email byl již použit. Zvol si jiné jméno/email.
                                </div>';
                            }

                            // Check if the username has special characters
                            if (isset($special_chars_error)) {
                                echo '<div class="alert alert-warning mt-2">
                                    <i class="ph ph-warning"></i> Tvé jméno obsahuje nepovolené znaky. Používej pouze písmena, čísla, tečky a podtržítka.
                                </div>';
                            }

                            // Check if the password is corresponding with the confirmation password
                            if (isset($pass_error)) {
                                echo '<div class="alert alert-warning mt-2">
                                    <i class="ph ph-warning"></i> Tvé heslo se neshoduje s potvrzením hesla. Zadej své heslo znovu.
                                </div>';
                            }

                            // Check if the checkbox was checked
                            if (isset($checkbox_error)) {
                                echo '<div class="alert alert-warning mt-2">
                                    <i class="ph ph-warning"></i> Musíš souhlasit s podmínkami použití a ukládáním osobních údajů. Bez tohoto souhlasu se nemůžeš registrovat.
                                </div>';
                            }
                        ?>
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
                </div>
            </div>
        </div>
    </section>
    <?php require_once "{$_SERVER['DOCUMENT_ROOT']}/components/scripts.php"; ?>
</body>
</html>