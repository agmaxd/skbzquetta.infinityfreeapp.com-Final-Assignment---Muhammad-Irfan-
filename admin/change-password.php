<?php
require_once __DIR__ . "/auth.php";

$message = "";
$messageType = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    /* =========================
       CSRF VALIDATION
    ========================= */
    if (!isset($_POST["csrf_token"]) || !verifyCsrfToken($_POST["csrf_token"])) {

        $message = "Security validation failed. Please try again.";
        $messageType = "error";

    } else {

        /* =========================
           GET FORM VALUES
        ========================= */
        $currentUsername = trim($_POST["current_username"] ?? "");
        $newUsername = trim($_POST["new_username"] ?? "");
        $confirmUsername = trim($_POST["confirm_username"] ?? "");

        $currentPassword = $_POST["current_password"] ?? "";
        $newPassword = $_POST["new_password"] ?? "";
        $confirmPassword = $_POST["confirm_password"] ?? "";


        /* =========================
           GET CURRENT ADMIN
        ========================= */
        $stmt = $pdo->prepare("
            SELECT admin_id, username, password_hash
            FROM admins
            WHERE admin_id = ?
            LIMIT 1
        ");

        $stmt->execute([$_SESSION["admin_id"]]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);


        if (!$admin) {

            $message = "Admin account not found.";
            $messageType = "error";

        } elseif ($currentUsername === "") {

            $message = "Please enter your current username.";
            $messageType = "error";

        } elseif ($currentUsername !== $admin["username"]) {

            $message = "Current username is incorrect.";
            $messageType = "error";

        } elseif ($currentPassword === "") {

            $message = "Please enter your current password.";
            $messageType = "error";

        } elseif (!password_verify($currentPassword, $admin["password_hash"])) {

            $message = "Current password is incorrect.";
            $messageType = "error";

        } else {

            /* =========================
               DETERMINE WHAT IS CHANGING
            ========================= */

            $changingUsername =
                $newUsername !== "" ||
                $confirmUsername !== "";

            $changingPassword =
                $newPassword !== "" ||
                $confirmPassword !== "";


            /* =========================
               NOTHING TO CHANGE
            ========================= */

            if (!$changingUsername && !$changingPassword) {

                $message = "Please enter a new username or a new password.";
                $messageType = "error";

            }

            /* =========================
               USERNAME VALIDATION
            ========================= */ elseif ($changingUsername) {

                if ($newUsername === "") {

                    $message = "Please enter a new username.";
                    $messageType = "error";

                } elseif ($confirmUsername === "") {

                    $message = "Please confirm your new username.";
                    $messageType = "error";

                } elseif ($newUsername !== $confirmUsername) {

                    $message = "New username and confirm username do not match.";
                    $messageType = "error";

                } else {

                    /* Check whether username already exists */

                    $existingUser = $pdo->prepare("
                        SELECT admin_id
                        FROM admins
                        WHERE username = ?
                        AND admin_id != ?
                        LIMIT 1
                    ");

                    $existingUser->execute([
                        $newUsername,
                        $_SESSION["admin_id"]
                    ]);


                    if ($existingUser->fetch()) {

                        $message = "That username is already taken. Please choose another one.";
                        $messageType = "error";

                    }
                }
            }


            /* =========================
               PASSWORD VALIDATION
            ========================= */

            if (
                $message === "" &&
                $changingPassword
            ) {

                if ($newPassword === "") {

                    $message = "Please enter a new password.";
                    $messageType = "error";

                } elseif ($confirmPassword === "") {

                    $message = "Please confirm your new password.";
                    $messageType = "error";

                } elseif (
                    !preg_match(
                        '/^(?=.*\d)(?=.*[^A-Za-z0-9]).{8,}$/',
                        $newPassword
                    )
                ) {

                    $message = "New password must be at least 8 characters long and include at least one number and one special character.";
                    $messageType = "error";

                } elseif ($newPassword !== $confirmPassword) {

                    $message = "New password and confirm password do not match.";
                    $messageType = "error";
                }
            }


            /* =========================
               UPDATE DATABASE
            ========================= */

            if ($message === "") {

                $finalUsername = $changingUsername
                    ? $newUsername
                    : $admin["username"];


                if ($changingPassword) {

                    $newPasswordHash = password_hash(
                        $newPassword,
                        PASSWORD_DEFAULT
                    );

                    $updateStmt = $pdo->prepare("
                        UPDATE admins
                        SET username = ?, password_hash = ?
                        WHERE admin_id = ?
                    ");

                    $updateStmt->execute([
                        $finalUsername,
                        $newPasswordHash,
                        $_SESSION["admin_id"]
                    ]);

                } else {

                    $updateStmt = $pdo->prepare("
                        UPDATE admins
                        SET username = ?
                        WHERE admin_id = ?
                    ");

                    $updateStmt->execute([
                        $finalUsername,
                        $_SESSION["admin_id"]
                    ]);
                }


                /* =========================
                   UPDATE SESSION
                ========================= */

                $_SESSION["admin_username"] = $finalUsername;


                /* =========================
                   SUCCESS MESSAGE
                ========================= */

                if ($changingUsername && $changingPassword) {

                    $message = "Username and password updated successfully.";

                } elseif ($changingUsername) {

                    $message = "Username updated successfully.";

                } else {

                    $message = "Password updated successfully.";
                }

                $messageType = "success";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>
        Change Password | Shaikh Khalifa Bin Zayed Hospital
    </title>

    <link rel="stylesheet" href="../Style.css">

    <link rel="icon" type="image/png" href="../images/logo.png">

</head>


<body class="admin-page-body">

    <div class="admin-shell">

        <aside class="sidebar">

            <div class="brand">
                <a href="../index.html">
                    <div class="brand-badge">
                        <img src="../images/logo.png" alt="Shaikh Khalifa Bin Zayed Hospital logo">
                    </div>
                    <div class="brand-text">
                        <div class="brand-title">Shaikh Khalifa Bin Zayed Hospital</div>
                        <div class="brand-location">Quetta</div>
                    </div>
                </a>
            </div>


            <nav class="nav">

                <div class="nav-label">
                    Administration
                </div>

                <a href="dashboard.php">
                    Dashboard
                </a>

                <a href="appointments.php">
                    Appointments
                </a>

                <a href="contact-messages.php">
                    Contact Messages
                </a>

                <a href="doctors.php">
                    Doctors
                </a>

                <a class="active" href="change-password.php">
                    Change Password
                </a>

                <a href="logout.php">
                    Logout
                </a>

            </nav>

        </aside>


        <main class="content">

            <div class="card">

                <h1>
                    Change Password
                </h1>


                <?php if ($message !== ""): ?>

                    <div class="msg <?php echo htmlspecialchars(
                        $messageType,
                        ENT_QUOTES,
                        "UTF-8"
                    ); ?>">

                        <?php echo htmlspecialchars(
                            $message,
                            ENT_QUOTES,
                            "UTF-8"
                        ); ?>

                    </div>

                <?php endif; ?>


                <form method="POST" action="change-password.php" class="password-form" autocomplete="off" novalidate>

                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(
                        csrfToken(),
                        ENT_QUOTES,
                        "UTF-8"
                    ); ?>">


                    <!-- CURRENT USERNAME -->

                    <div class="password-field">

                        <label for="current_username">
                            Current Username
                        </label>

                        <input type="text" id="current_username" name="current_username" value="" autocomplete="off"
                            autocapitalize="none" spellcheck="false" readonly required>

                    </div>


                    <!-- NEW USERNAME -->

                    <div class="password-field">

                        <label for="new_username">
                            New Username
                        </label>

                        <input type="text" id="new_username" name="new_username" value="" autocomplete="off"
                            autocapitalize="none" spellcheck="false">

                    </div>


                    <!-- CONFIRM USERNAME -->

                    <div class="password-field">

                        <label for="confirm_username">
                            Confirm New Username
                        </label>

                        <input type="text" id="confirm_username" name="confirm_username" value="" autocomplete="off"
                            autocapitalize="none" spellcheck="false">

                    </div>


                    <!-- CURRENT PASSWORD -->

                    <div class="password-field">

                        <label for="current_password">
                            Current Password
                        </label>

                        <div class="password-input-wrap">

                            <input type="password" id="current_password" name="current_password" value=""
                                autocomplete="new-password" readonly required>

                            <button type="button" class="password-toggle" data-target="current_password"
                                aria-label="Show current password">
                                Show
                            </button>

                        </div>

                    </div>


                    <!-- NEW PASSWORD -->

                    <div class="password-field">

                        <label for="new_password">
                            New Password
                        </label>

                        <div class="password-input-wrap">

                            <input type="password" id="new_password" name="new_password" value=""
                                autocomplete="new-password" placeholder="Leave blank to keep current password">

                            <button type="button" class="password-toggle" data-target="new_password"
                                aria-label="Show new password">
                                Show
                            </button>

                        </div>


                        <div class="password-strength-wrap" aria-live="polite">

                            <div class="password-strength-bar" id="passwordStrengthBar">

                                <span id="passwordStrengthFill"></span>

                            </div>


                            <small id="passwordStrengthText" class="password-strength-text">
                                Start typing to check strength
                            </small>

                        </div>

                    </div>


                    <!-- CONFIRM NEW PASSWORD -->

                    <div class="password-field">

                        <label for="confirm_password">
                            Confirm New Password
                        </label>

                        <div class="password-input-wrap">

                            <input type="password" id="confirm_password" name="confirm_password" value=""
                                autocomplete="new-password" placeholder="Leave blank to keep current password">

                            <button type="button" class="password-toggle" data-target="confirm_password"
                                aria-label="Show password">
                                Show
                            </button>

                        </div>

                    </div>


                    <button type="submit" class="submit-button" id="updateAccountButton" disabled>
                        Update Account
                    </button>

                </form>

            </div>

        </main>

    </div>


    <script>

        /* =========================================
           INPUT REFERENCES
        ========================================= */

        const currentUsernameInput =
            document.getElementById("current_username");

        const newUsernameInput =
            document.getElementById("new_username");

        const confirmUsernameInput =
            document.getElementById("confirm_username");

        const currentPasswordInput =
            document.getElementById("current_password");

        const newPasswordInput =
            document.getElementById("new_password");

        const confirmPasswordInput =
            document.getElementById("confirm_password");

        const updateAccountButton =
            document.getElementById("updateAccountButton");

        const strengthBar =
            document.getElementById("passwordStrengthBar");

        const strengthFill =
            document.getElementById("passwordStrengthFill");

        const strengthText =
            document.getElementById("passwordStrengthText");


        /* =========================================
           STOP BROWSER AUTOFILL
        ========================================= */

        /*
           readonly inputs prevent Chrome/password managers
           from automatically inserting saved credentials.
        
           Once the user actually clicks into the field,
           readonly is removed so they can type normally.
        */

        [currentUsernameInput, currentPasswordInput].forEach(function (input) {

            input.addEventListener("focus", function () {

                this.removeAttribute("readonly");

            }, { once: true });

        });


        /* =========================================
           PASSWORD STRENGTH
        ========================================= */

        function isStrongPassword(value) {

            return /^(?=.*\d)(?=.*[^A-Za-z0-9]).{8,}$/.test(value);

        }


        function updatePasswordStrength() {

            const currentUsernameFilled =
                currentUsernameInput.value.trim() !== "";

            const currentPasswordFilled =
                currentPasswordInput.value.length > 0;


            const newUsername =
                newUsernameInput.value.trim();

            const confirmUsername =
                confirmUsernameInput.value.trim();


            const newPassword =
                newPasswordInput.value;

            const confirmPassword =
                confirmPasswordInput.value;


            const changingUsername =
                newUsername.length > 0 ||
                confirmUsername.length > 0;


            const changingPassword =
                newPassword.length > 0 ||
                confirmPassword.length > 0;


            /* =====================================
               USERNAME VALIDATION
            ===================================== */

            let usernameValid = true;


            if (changingUsername) {

                usernameValid =
                    newUsername.length > 0 &&
                    confirmUsername.length > 0 &&
                    newUsername === confirmUsername;

            }


            /* =====================================
               PASSWORD VALIDATION
            ===================================== */

            let passwordValid = true;


            if (changingPassword) {

                passwordValid =
                    newPassword.length > 0 &&
                    confirmPassword.length > 0 &&
                    isStrongPassword(newPassword) &&
                    newPassword === confirmPassword;

            }


            /* =====================================
               PASSWORD STRENGTH DISPLAY
            ===================================== */

            if (
                newPassword.length === 0 &&
                confirmPassword.length === 0
            ) {

                strengthFill.style.width = "0%";

                strengthFill.style.background = "#e5e7eb";

                strengthText.textContent =
                    "Start typing to check strength";

                strengthBar.classList.remove("active");

            } else {

                let score = 0;


                if (newPassword.length >= 8) {
                    score++;
                }

                if (/\d/.test(newPassword)) {
                    score++;
                }

                if (/[^A-Za-z0-9]/.test(newPassword)) {
                    score++;
                }


                const width =
                    Math.min(100, (score / 3) * 100);


                const color =
                    score === 3
                        ? "#22c55e"
                        : score === 2
                            ? "#f59e0b"
                            : "#ef4444";


                strengthFill.style.width =
                    width + "%";

                strengthFill.style.background =
                    color;

                strengthBar.classList.add("active");


                if (
                    score === 3 &&
                    newPassword.length >= 8
                ) {

                    strengthText.textContent =
                        "Strong password — ready to update";

                } else {

                    strengthText.textContent =
                        "Use 8+ chars, 1 number, and 1 special character";

                }

            }


            /* =====================================
               FINAL SUBMIT CHECK
            ===================================== */

            const somethingChanging =
                changingUsername ||
                changingPassword;


            const canSubmit =
                currentUsernameFilled &&
                currentPasswordFilled &&
                somethingChanging &&
                usernameValid &&
                passwordValid;


            updateAccountButton.disabled =
                !canSubmit;


            updateAccountButton.style.opacity =
                canSubmit ? "1" : "0.6";

        }


        /* =========================================
           LISTEN FOR INPUT
        ========================================= */

        [
            currentUsernameInput,
            newUsernameInput,
            confirmUsernameInput,
            currentPasswordInput,
            newPasswordInput,
            confirmPasswordInput
        ].forEach(function (input) {

            input.addEventListener(
                "input",
                updatePasswordStrength
            );

        });


        /* =========================================
           SHOW / HIDE PASSWORD
        ========================================= */

        document
            .querySelectorAll(".password-toggle")
            .forEach(function (button) {

                button.addEventListener(
                    "click",
                    function () {

                        const targetId =
                            this.getAttribute("data-target");

                        const input =
                            document.getElementById(targetId);

                        if (!input) return;


                        const isPassword =
                            input.type === "password";


                        input.type =
                            isPassword
                                ? "text"
                                : "password";


                        this.textContent =
                            isPassword
                                ? "Hide"
                                : "Show";


                        this.setAttribute(
                            "aria-label",
                            isPassword
                                ? "Hide password"
                                : "Show password"
                        );

                    }
                );

            });


        /* =========================================
           INITIAL STATE
        ========================================= */

        updatePasswordStrength();

    </script>

</body>

</html>
```