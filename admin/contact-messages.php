<?php
require_once __DIR__ . "/auth.php";

$search = trim($_GET['search'] ?? "");
$sortOrder = $_GET['sort_by'] ?? "newest_first";


/* =========================================================
   ALLOWED SORT OPTIONS
========================================================= */

$allowedSortOrders = [
    "newest_first",
    "oldest_first"
];

if (!in_array($sortOrder, $allowedSortOrders, true)) {
    $sortOrder = "newest_first";
}


/* =========================================================
   DELETE MESSAGE
========================================================= */

if (
    $_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_POST['delete_message_id'])
) {

    if (
        !isset($_POST['csrf_token']) ||
        !verifyCsrfToken($_POST['csrf_token'])
    ) {

        header("Location: contact-messages.php");
        exit;
    }


    $messageId = filter_input(
        INPUT_POST,
        'delete_message_id',
        FILTER_VALIDATE_INT
    );


    if ($messageId) {

        $deleteStmt = $pdo->prepare(
            "DELETE FROM contact_messages
             WHERE message_id = ?"
        );

        $deleteStmt->execute([
            $messageId
        ]);
    }


    header("Location: contact-messages.php");
    exit;
}


/* =========================================================
   FETCH CONTACT MESSAGES
========================================================= */

$sql = "
    SELECT
        message_id,
        full_name AS name,
        email,
        phone,
        subject,
        message,
        status,
        created_at
    FROM contact_messages
";


$params = [];


/* =========================================================
   SEARCH BY EMAIL OR PHONE
========================================================= */

if ($search !== "") {

    $sql .= "
        WHERE
            email LIKE :search_email
            OR phone LIKE :search_phone
    ";

    $searchValue = "%" . $search . "%";

    $params[':search_email'] = $searchValue;
    $params[':search_phone'] = $searchValue;
}


/* =========================================================
   SORT
========================================================= */

if ($sortOrder === "oldest_first") {

    $sql .= "
        ORDER BY created_at ASC
    ";

} else {

    $sql .= "
        ORDER BY created_at DESC
    ";
}


$stmt = $pdo->prepare($sql);

$stmt->execute($params);

$messages = $stmt->fetchAll();

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>
        Contact Messages | Shaikh Khalifa Bin Zayed Hospital
    </title>

    <link rel="stylesheet" href="../Style.css">

    <link rel="icon" type="image/png" href="../images/logo.png">

</head>


<body class="admin-page-body">


    <div class="admin-shell admin-app">


        <!-- =====================================================
         SIDEBAR
    ====================================================== -->

        <aside class="sidebar">

            <div class="brand">

                <a href="../index.html">

                    <div class="brand-badge">

                        <img src="../images/logo.png" alt="Shaikh Khalifa Bin Zayed Hospital logo">

                    </div>


                    <div class="brand-text">

                        <div class="brand-title">
                            Shaikh Khalifa Bin Zayed Hospital
                        </div>

                        <div class="brand-location">
                            Quetta
                        </div>

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

                <a class="active" href="contact-messages.php">
                    Contact Messages
                </a>

                <a href="doctors.php">
                    Doctors
                </a>

                <a href="change-password.php">
                    Change Password
                </a>

                <a href="logout.php">
                    Logout
                </a>

            </nav>

        </aside>



        <!-- =====================================================
         MAIN CONTENT
    ====================================================== -->

        <main class="content">


            <div class="page-header">

                <div class="page-title-area">

                    <h1>
                        Contact Messages
                    </h1>

                    <p>
                        Manage and review messages submitted through the contact form
                    </p>

                    <div class="page-header-line"></div>

                </div>

            </div>



            <div class="card">


                <!-- =================================================
                 SEARCH + SORT
            ================================================== -->

                <div class="dashboard-toolbar appointment-toolbar">

                    <form method="GET" action="contact-messages.php" class="filter-bar compact-filter-bar">


                        <!-- SEARCH -->

                        <div class="filter-field appointment-search-field">

                            <label for="appointment-search">
                                Search Contact Messages
                            </label>

                            <input type="text" id="appointment-search" name="search" value="<?php echo htmlspecialchars(
                                $search,
                                ENT_QUOTES,
                                'UTF-8'
                            ); ?>" placeholder="Enter email or phone number..." autocomplete="off">

                        </div>



                        <!-- SORT -->

                        <div class="filter-field">

                            <label class="sort_by">
                                Sort by
                            </label>

                            <select name="sort_by" id="sort_by">

                                <option value="newest_first" <?php echo $sortOrder === "newest_first" ? "selected" : ""; ?>>
                                    Newest → Oldest
                                </option>

                                <option value="oldest_first" <?php echo $sortOrder === "oldest_first" ? "selected" : ""; ?>>
                                    Oldest → Newest
                                </option>

                            </select>

                        </div>



                        <!-- APPLY -->

                        <button type="submit" class="appointment-search-btn">
                            Apply
                        </button>



                        <!-- CLEAR -->

                        <?php if (
                            $search !== ""
                            || $sortOrder !== "newest_first"
                        ): ?>

                            <a href="contact-messages.php" class="appointment-clear-btn">
                                Reset
                            </a>

                        <?php endif; ?>


                    </form>

                </div>



                <!-- =================================================
                 CONTACT MESSAGE TABLE
            ================================================== -->

                <div class="contact-table-window">

                    <table class="admin-table">

                        <thead>

                            <tr>

                                <th>ID</th>

                                <th>Name</th>

                                <th>Email</th>

                                <th>Phone</th>

                                <th>Subject</th>

                                <th>Message</th>

                                <th>Submitted</th>

                                <th>Actions</th>

                            </tr>

                        </thead>


                        <tbody>


                            <?php if (empty($messages)): ?>


                                <tr>

                                    <td colspan="8">

                                        <?php if ($search !== ""): ?>

                                            No contact messages found for
                                            "<?php echo htmlspecialchars(
                                                $search,
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ); ?>".

                                        <?php else: ?>

                                            No contact messages found.

                                        <?php endif; ?>

                                    </td>

                                </tr>


                            <?php else: ?>


                                <?php foreach ($messages as $message): ?>


                                    <tr>


                                        <!-- ID -->

                                        <td>

                                            <?php
                                            echo htmlspecialchars(
                                                $message['message_id'],
                                                ENT_QUOTES,
                                                'UTF-8'
                                            );
                                            ?>

                                        </td>



                                        <!-- NAME -->

                                        <td>

                                            <?php
                                            echo htmlspecialchars(
                                                $message['name'],
                                                ENT_QUOTES,
                                                'UTF-8'
                                            );
                                            ?>

                                        </td>



                                        <!-- EMAIL -->

                                        <td>

                                            <?php
                                            echo htmlspecialchars(
                                                $message['email'],
                                                ENT_QUOTES,
                                                'UTF-8'
                                            );
                                            ?>

                                        </td>



                                        <!-- PHONE -->

                                        <td>

                                            <?php
                                            echo htmlspecialchars(
                                                $message['phone'] ?? 'N/A',
                                                ENT_QUOTES,
                                                'UTF-8'
                                            );
                                            ?>

                                        </td>



                                        <!-- SUBJECT -->

                                        <td>

                                            <?php
                                            echo htmlspecialchars(
                                                $message['subject'],
                                                ENT_QUOTES,
                                                'UTF-8'
                                            );
                                            ?>

                                        </td>



                                        <!-- MESSAGE -->

                                        <td class="message-cell">

                                            <?php

                                            echo nl2br(
                                                htmlspecialchars(
                                                    $message['message'],
                                                    ENT_QUOTES,
                                                    'UTF-8'
                                                )
                                            );

                                            ?>

                                        </td>



                                        <!-- SUBMITTED -->

                                        <td>

                                            <?php
                                            echo htmlspecialchars(
                                                $message['created_at'],
                                                ENT_QUOTES,
                                                'UTF-8'
                                            );
                                            ?>

                                        </td>



                                        <!-- ACTIONS -->

                                        <td class="contact-actions">

                                            <form method="POST" action="contact-messages.php"
                                                onsubmit="return confirm('Delete this message permanently?');">

                                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(
                                                    csrfToken(),
                                                    ENT_QUOTES,
                                                    'UTF-8'
                                                ); ?>">


                                                <input type="hidden" name="delete_message_id"
                                                    value="<?php echo (int) $message['message_id']; ?>">


                                                <button type="submit" class="btn danger-btn">
                                                    Delete
                                                </button>

                                            </form>

                                        </td>


                                    </tr>


                                <?php endforeach; ?>


                            <?php endif; ?>


                        </tbody>

                    </table>

                </div>


            </div>


        </main>

    </div>



    <!-- =========================================================
     REPLY MODAL
========================================================= -->

    <div id="replyModal" class="reply-modal" style="display: none;">


        <div class="reply-modal-box">


            <button type="button" class="reply-modal-close" onclick="closeReplyModal()">
                &times;
            </button>


            <h2>
                Reply to Contact
            </h2>


            <div class="reply-recipient">


                <p>

                    <strong>Name:</strong>

                    <span id="replyName"></span>

                </p>


                <p>

                    <strong>Email:</strong>

                    <span id="replyEmail"></span>

                </p>


                <p>

                    <strong>Phone:</strong>

                    <span id="replyPhone"></span>

                </p>


            </div>



            <form method="POST" action="reply-contact.php" id="replyForm">


                <input type="hidden" name="message_id" id="replyMessageId">


                <label for="replyMessage">
                    Your Reply
                </label>


                <textarea id="replyMessage" name="reply" rows="7" maxlength="2000"
                    placeholder="Write your reply here..." required></textarea>


                <div class="reply-character-count">

                    <span id="replyCounter">
                        0 / 2000
                    </span>

                </div>



                <div class="reply-modal-actions">


                    <button type="button" class="btn secondary" onclick="closeReplyModal()">
                        Cancel
                    </button>


                    <button type="submit" class="btn">
                        Send Reply
                    </button>


                </div>


            </form>


        </div>

    </div>



    <script>

        /* =========================================================
           REPLY MODAL
        ========================================================= */

        function openReplyModal(
            messageId,
            name,
            email,
            phone
        ) {

            document.getElementById("replyMessageId").value =
                messageId;


            document.getElementById("replyName").textContent =
                name;


            document.getElementById("replyEmail").textContent =
                email;


            document.getElementById("replyPhone").textContent =
                phone || "N/A";


            document.getElementById("replyMessage").value =
                "";


            document.getElementById("replyCounter").textContent =
                "0 / 2000";


            document.getElementById("replyModal").style.display =
                "flex";

        }



        function closeReplyModal() {

            document.getElementById("replyModal").style.display =
                "none";

        }



        /* =========================================================
           CLOSE MODAL WHEN CLICKING OUTSIDE
        ========================================================= */

        document.getElementById("replyModal").addEventListener(
            "click",
            function (event) {

                if (event.target === this) {

                    closeReplyModal();

                }

            }
        );



        /* =========================================================
           REPLY CHARACTER COUNTER
        ========================================================= */

        const replyMessage =
            document.getElementById("replyMessage");


        const replyCounter =
            document.getElementById("replyCounter");


        replyMessage.addEventListener(
            "input",
            function () {

                const length =
                    this.value.length;

                replyCounter.textContent =
                    length + " / 2000";

            }
        );



        /* =========================================================
           ESC CLOSES MODAL
        ========================================================= */

        document.addEventListener(
            "keydown",
            function (event) {

                if (event.key === "Escape") {

                    closeReplyModal();

                }

            }
        );

    </script>


</body>

</html>