<?php

session_start();
require_once __DIR__ . "/init-app.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $username = trim($_POST["username"] ?? "");
    $password = $_POST["password"] ?? "";

    if ($username === "" || $password === "") {

        $error = "Please enter both username and password.";

    } else {

        $stmt = $pdo->prepare(
            "SELECT * FROM admins WHERE username = ? LIMIT 1"
        );

        $stmt->execute([$username]);

        $admin = $stmt->fetch();

        if (
            $admin &&
            password_verify(
                $password,
                $admin["password_hash"]
            )
        ) {

            $_SESSION["admin_logged_in"] = true;
            $_SESSION["admin_id"] = $admin["admin_id"];
            $_SESSION["admin_username"] = $admin["username"];
            $_SESSION["admin_name"] = $admin["full_name"];
            $_SESSION["admin_role"] = $admin["role"];

            header("Location: dashboard.php");
            exit;
        }

        $error = "Invalid username or password.";
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Admin Login | Shaikh Khalifa Bin Zayed Hospital
    </title>

    <link
        rel="stylesheet"
        href="../Style.css"
    >

    <link
        rel="icon"
        type="image/png"
        href="../images/logo.png"
    >

    <script
        src="../Function.js"
        defer
    ></script>

</head>

<body class="admin-login-body">

    <div class="login-shell">

        <div class="brand-panel">

            <div class="brand-row">

                <div class="logo-wrap">

                    <img
                        src="../images/logo.png"
                        alt="Shaikh Khalifa Bin Zayed Hospital logo"
                    >

                </div>

                <a class="homelink-login" href="../index.html">
                
                     <h2>
                    Shaikh Khalifa Bin Zayed Hospital, Quetta
                	</h2>
                    
                </a>
                
               

            </div>

            <p>
                Secure administrative access for managing appointments,
                patient requests, and hospital information.
            </p>

        </div>


        <div class="login-box">

            <h1>Admin Login</h1>

            <?php if ($error !== ""): ?>

                <div class="error">

                    <?php
                    echo htmlspecialchars(
                        $error,
                        ENT_QUOTES,
                        "UTF-8"
                    );
                    ?>

                </div>

            <?php endif; ?>


            <form
                method="POST"
                action="login.php"
            >

                <div>

                    <label for="username">
                        Username
                    </label>

                    <input
                        type="text"
                        id="username"
                        name="username"
                        autocomplete="username"
                        required
                    >

                </div>


                <div>

                    <label for="admin-password">
                        Password
                    </label>

                    <div class="admin-pass-container">

                        <input
                            type="password"
                            id="admin-password"
                            name="password"
                            placeholder="Enter password"
                            autocomplete="current-password"
                            required
                        >


                    </div>

                </div>


                <button type="submit">
                    Login
                </button>

            </form>

        </div>

    </div>
    
    <script src="../Function.js" defer></script>

</body>

</html>