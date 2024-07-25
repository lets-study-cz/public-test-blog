<?php
    require_once('db.php');

    // Wait until user submits form

    if (isset($_POST['name']) && isset($_POST['password'])) {
        $name = $_POST['name'];
        $password = $_POST['password'];

        // Prepare SQL query
        $stmt = $conn->prepare('SELECT password FROM users WHERE nickname = ?');
        $stmt->bind_param('s', $name);
        $stmt->execute();
        $stmt->bind_result($hash);
        $stmt->fetch();

        // Check if the username exists
        if (empty($hash)) {
            $name_error = true;
        }

        // Decrypt password
        if (!empty($hash)){
            if (password_verify($password, $hash)) {
                echo 'You have been logged in';
                session_start();
                $_SESSION['name'] = $name;
                header('Location: index.php');
            } else {
                $pass_error = true;
            }
        }

        $stmt->close();
        $conn->close();
    }

    // Check if user is logged in
    session_start();
    if (isset($_SESSION['name'])) {
        header('Location: index.php');
    }

    require_once('../components/panel_head.php');
?>
<body>
    <?php require_once "{$_SERVER['DOCUMENT_ROOT']}/components/loader.php"; ?>
    <section class="vh-100 container d-flex justify-content-center align-items-center" id="login-5">
        <div class="p-2 col-xl-5 col-lg-7 col-md-10 col-12">
            <div class="mb-5 text-center">
                <img class="mb-4" alt="Logo" width="48" height="48" src="<?php $_SERVER['DOCUMENT_ROOT'] ?>/assets/content/logo.svg">
                <h2>Přihlaš se do <span class="serif ls-2 text-white">blog panelu</span></h2>
            </div>
            <form method="post">
                <div class="form-floating">
                    <input type="text" class="form-control rounded-top rounded-0" id="floatingInput" name="name" placeholder="name@example.com">
                    <label for="floatingInput">Přezdívka</label>
                </div>
                <div class="form-floating mb-3">
                    <input type="password" class="form-control rounded-bottom rounded-0 border-top-0" name="password" id="floatingPassword" placeholder="Password">
                    <label for="floatingPassword">Heslo</label>
                </div>
                <button type="submit" class="btn btn-primary w-100">Přihlásit se</button>

                <?php
                    // Check if the username exists
                    if (isset($name_error)) {
                        echo '<div class="alert alert-warning mt-2">
                            <i class="ph ph-warning"></i> Nepovedlo se mi najít tento účet.
                        </div>';
                    }

                    // Check if the password is correct (use password_verify)
                    if (!isset($name_error) && (isset($pass_error))) {
                        echo '<div class="alert alert-warning mt-2">
                            <i class="ph ph-warning"></i> Zadal/a jsi nesprávné heslo.
                        </div>';
                    }
                ?>
            </form>
        </div>
    </section>
    <?php
    require_once "{$_SERVER['DOCUMENT_ROOT']}/components/scripts.php";
?>
</body>
</html>