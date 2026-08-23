<?php
require_once __DIR__ . "/auth.php";

$filterStatus = $_GET["status_filter"] ?? "all";
$sortOrder = $_GET["sort_by"] ?? "most_recent";

$appointmentSearch = trim($_GET["appointment_search"] ?? "");
$contactSearch = trim($_GET["contact_search"] ?? "");

/* =========================================================
   CONTACT MESSAGE SORT
========================================================= */

$contactSortOrder = $_GET["contact_sort_by"] ?? "most_recent";


/* =========================================================
   VALIDATE APPOINTMENT FILTERS
========================================================= */

$allowedStatuses = [
    "all",
    "pending",
    "confirmed",
    "rejected",
    "completed"
];

if (!in_array($filterStatus, $allowedStatuses, true)) {
    $filterStatus = "all";
}


$allowedSortOrders = [
    "most_recent",
    "oldest_first"
];

if (!in_array($sortOrder, $allowedSortOrders, true)) {
    $sortOrder = "most_recent";
}


/* =========================================================
   VALIDATE CONTACT MESSAGE SORT
========================================================= */

$allowedContactSortOrders = [
    "most_recent",
    "oldest_first"
];

if (!in_array($contactSortOrder, $allowedContactSortOrders, true)) {
    $contactSortOrder = "most_recent";
}


/* =========================================================
   DASHBOARD SUMMARY
========================================================= */

$summary = [
    "appointments" => $pdo->query(
        "SELECT COUNT(*) FROM appointments"
    )->fetchColumn(),

    "patients" => $pdo->query(
        "SELECT COUNT(*) FROM patients"
    )->fetchColumn(),

    "doctors" => $pdo->query(
        "SELECT COUNT(*) FROM doctors"
    )->fetchColumn(),

    "admins" => $pdo->query(
        "SELECT COUNT(*) FROM admins"
    )->fetchColumn(),

    "messages" => $pdo->query(
        "SELECT COUNT(*) FROM contact_messages"
    )->fetchColumn(),
];


/* =========================================================
   APPOINTMENTS
========================================================= */

$sql = "
    SELECT
        a.appointment_id,
        p.full_name AS patient_name,
        p.gender,
        p.date_of_birth,
        p.email,
        p.phone,
        dep.department_name,
        d.full_name AS doctor_name,
        a.appointment_type,
        a.appointment_date,
        a.appointment_time,
        a.medical_record_path,
        a.status,
        a.created_at
    FROM appointments a

    LEFT JOIN patients p
        ON p.patient_id = a.patient_id

    LEFT JOIN doctors d
        ON d.doctor_id = a.doctor_id

    LEFT JOIN departments dep
        ON dep.department_id = a.department_id

    WHERE 1 = 1
";

$params = [];


/* STATUS FILTER */

if ($filterStatus !== "all") {

    $sql .= "
        AND a.status = :status_filter
    ";

    $params[":status_filter"] = $filterStatus;
}


/* EMAIL / PHONE SEARCH */

if ($appointmentSearch !== "") {

    $sql .= "
        AND (
            p.email LIKE :appointment_search_email
            OR p.phone LIKE :appointment_search_phone
        )
    ";

    $searchValue = "%" . $appointmentSearch . "%";

    $params[":appointment_search_email"] = $searchValue;
    $params[":appointment_search_phone"] = $searchValue;
}


/* SORT */

if ($sortOrder === "oldest_first") {

    $sql .= "
        ORDER BY
            a.appointment_date ASC,
            a.appointment_time ASC
    ";

} else {

    $sql .= "
        ORDER BY
            a.appointment_date DESC,
            a.appointment_time DESC
    ";
}


$stmt = $pdo->prepare($sql);
$stmt->execute($params);

$recentAppointments = $stmt->fetchAll();


/* =========================================================
   CONTACT MESSAGES
========================================================= */

$contactSql = "
    SELECT
        message_id,
        full_name AS name,
        email,
        phone,
        subject,
        message,
        created_at

    FROM contact_messages

    WHERE 1 = 1
";

$contactParams = [];


if ($contactSearch !== "") {

    $contactSql .= "
        AND (
            email LIKE :contact_search_email
            OR phone LIKE :contact_search_phone
        )
    ";

    $contactSearchValue = "%" . $contactSearch . "%";

    $contactParams[":contact_search_email"] =
        $contactSearchValue;

    $contactParams[":contact_search_phone"] =
        $contactSearchValue;
}


/* CONTACT MESSAGE SORT */

if ($contactSortOrder === "oldest_first") {

    $contactSql .= "
        ORDER BY created_at ASC
    ";

} else {

    $contactSql .= "
        ORDER BY created_at DESC
    ";
}


$contactStmt = $pdo->prepare($contactSql);
$contactStmt->execute($contactParams);

$contactMessages = $contactStmt->fetchAll();

?>
<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>
        Admin Dashboard | Shaikh Khalifa Bin Zayed Hospital
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

                <a class="active" href="dashboard.php">
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


            <!-- =================================================
             TOP BAR
        ================================================== -->

            <div class="topbar">

                <h1>
                    Welcome,
                    <?php
                    echo htmlspecialchars(
                        $_SESSION['admin_name'],
                        ENT_QUOTES,
                        'UTF-8'
                    );
                    ?>
                </h1>

                <div class="topbar-actions">

                    <a class="btn" href="logout.php">
                        Logout
                    </a>

                </div>

            </div>


            <!-- =================================================
             STATISTICS
        ================================================== -->

            <section class="stats">

                <div class="stat-card">

                    <h3>
                        Total Doctors
                    </h3>

                    <div class="count">
                        <?php echo $summary['doctors']; ?>
                    </div>

                </div>


                <div class="stat-card">

                    <h3>
                        Total Appointments
                    </h3>

                    <div class="count">
                        <?php echo $summary['appointments']; ?>
                    </div>

                </div>


                <div class="stat-card">

                    <h3>
                        Contact Messages
                    </h3>

                    <div class="count">
                        <?php echo $summary['messages']; ?>
                    </div>

                </div>

            </section>


            <!-- =================================================
             APPOINTMENT OVERVIEW
        ================================================== -->

            <section class="dashboard-data-section">


                <div class="dashboard-toolbar">

                    <h2>
                        Appointment Overview
                    </h2>


                    <form method="GET" action="dashboard.php" class="filter-bar compact-filter-bar">

                        <div class="filter-field">

                            <label for="status_filter">
                                Status
                            </label>

                            <select id="status_filter" name="status_filter">

                                <option value="all" <?php echo
                                    $filterStatus === 'all'
                                    ? 'selected'
                                    : '';
                                ?>>
                                    All statuses
                                </option>

                                <option value="pending" <?php echo
                                    $filterStatus === 'pending'
                                    ? 'selected'
                                    : '';
                                ?>>
                                    Pending
                                </option>

                                <option value="confirmed" <?php echo
                                    $filterStatus === 'confirmed'
                                    ? 'selected'
                                    : '';
                                ?>>
                                    Confirmed
                                </option>

                                <option value="rejected" <?php echo
                                    $filterStatus === 'rejected'
                                    ? 'selected'
                                    : '';
                                ?>>
                                    Rejected
                                </option>

                                <option value="completed" <?php echo
                                    $filterStatus === 'completed'
                                    ? 'selected'
                                    : '';
                                ?>>
                                    Completed
                                </option>

                            </select>

                        </div>


                        <div class="filter-field">

                            <label for="sort_by">
                                Sort by
                            </label>

                            <select id="sort_by" name="sort_by">

                                <option value="most_recent" <?php echo
                                    $sortOrder === 'most_recent'
                                    ? 'selected'
                                    : '';
                                ?>>
                                    Most recent
                                </option>

                                <option value="oldest_first" <?php echo
                                    $sortOrder === 'oldest_first'
                                    ? 'selected'
                                    : '';
                                ?>>
                                    Oldest first
                                </option>

                            </select>

                        </div>


                        <div class="filter-actions">

                            <button type="submit">
                                Apply
                            </button>

                            <a href="dashboard.php">
                                Reset
                            </a>

                        </div>

                    </form>

                </div>


                <!-- APPOINTMENT SEARCH -->

                <form method="GET" action="dashboard.php" class="dashboard-search-form">

                    <input type="text" name="appointment_search" value="<?php echo htmlspecialchars(
                        $appointmentSearch,
                        ENT_QUOTES,
                        'UTF-8'
                    ); ?>" placeholder="Search appointment by email or phone..." autocomplete="off">

                    <input type="hidden" name="status_filter" value="<?php echo htmlspecialchars(
                        $filterStatus,
                        ENT_QUOTES,
                        'UTF-8'
                    ); ?>">

                    <input type="hidden" name="sort_by" value="<?php echo htmlspecialchars(
                        $sortOrder,
                        ENT_QUOTES,
                        'UTF-8'
                    ); ?>">

                    <button type="submit">
                        Search
                    </button>

                    <?php if ($appointmentSearch !== ""): ?>

                        <a
                            href="dashboard.php?status_filter=<?php echo urlencode($filterStatus); ?>&sort_by=<?php echo urlencode($sortOrder); ?>">
                            Clear
                        </a>

                    <?php endif; ?>

                </form>


                <!-- APPOINTMENT WINDOW -->

                <div class="dashboard-table-window">

                    <table class="admin-table">

                        <thead>

                            <tr>

                                <th>Patient</th>
                                <th>Gender</th>
                                <th>DOB</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Department</th>
                                <th>Doctor</th>
                                <th>Type</th>
                                <th>Date / Time</th>
                                <th>Record</th>
                                <th>Status</th>

                            </tr>

                        </thead>


                        <tbody>

                            <?php if (empty($recentAppointments)): ?>

                                <tr>

                                    <td colspan="11">

                                        <?php if ($appointmentSearch !== ""): ?>

                                            No appointment found for that
                                            email or phone number.

                                        <?php else: ?>

                                            No appointments found.

                                        <?php endif; ?>

                                    </td>

                                </tr>

                            <?php else: ?>

                                <?php foreach (
                                    $recentAppointments
                                    as $appointment
                                ): ?>

                                    <tr>

                                        <td>
                                            <?php echo htmlspecialchars(
                                                $appointment['patient_name'],
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ); ?>
                                        </td>


                                        <td>
                                            <?php echo htmlspecialchars(
                                                $appointment['gender']
                                                ?? 'Not set',
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ); ?>
                                        </td>


                                        <td>
                                            <?php echo htmlspecialchars(
                                                $appointment['date_of_birth']
                                                ?? 'Not set',
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ); ?>
                                        </td>


                                        <td>
                                            <?php echo htmlspecialchars(
                                                $appointment['email']
                                                ?? '',
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ); ?>
                                        </td>


                                        <td>
                                            <?php echo htmlspecialchars(
                                                $appointment['phone'],
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ); ?>
                                        </td>


                                        <td>
                                            <?php echo htmlspecialchars(
                                                $appointment['department_name']
                                                ?? 'N/A',
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ); ?>
                                        </td>


                                        <td>
                                            <?php echo htmlspecialchars(
                                                $appointment['doctor_name']
                                                ?? 'N/A',
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ); ?>
                                        </td>


                                        <td>
                                            <?php echo htmlspecialchars(
                                                $appointment['appointment_type']
                                                ?? 'N/A',
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ); ?>
                                        </td>


                                        <td>

                                            <?php echo htmlspecialchars(
                                                $appointment['appointment_date'],
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ); ?>

                                            <br>

                                            <?php echo htmlspecialchars(
                                                $appointment['appointment_time'],
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ); ?>

                                        </td>


                                        <td>

                                            <?php if (
                                                !empty(
                                                $appointment[
                                                    'medical_record_path'
                                                ]
                                            )
                                            ): ?>

                                                <a href="../<?php echo htmlspecialchars(
                                                    $appointment[
                                                        'medical_record_path'
                                                    ],
                                                    ENT_QUOTES,
                                                    'UTF-8'
                                                ); ?>" target="_blank" rel="noopener noreferrer">
                                                    View
                                                </a>

                                            <?php else: ?>

                                                —

                                            <?php endif; ?>

                                        </td>


                                        <td>

                                            <span class="badge status-<?php echo htmlspecialchars(
                                                $appointment['status'],
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ); ?>">

                                                <?php echo ucfirst(
                                                    htmlspecialchars(
                                                        $appointment['status'],
                                                        ENT_QUOTES,
                                                        'UTF-8'
                                                    )
                                                ); ?>

                                            </span>

                                        </td>

                                    </tr>

                                <?php endforeach; ?>

                            <?php endif; ?>

                        </tbody>

                    </table>

                </div>


            </section>


            <!-- =================================================
             CONTACT MESSAGES
        ================================================== -->

            <section class="dashboard-data-section dashboard-contact-section">


                <div class="dashboard-toolbar">

                    <h2>
                        Contact Messages
                    </h2>


                    <!-- CONTACT SORT FILTER -->

                    <form method="GET" action="dashboard.php" class="filter-bar compact-filter-bar">

                        <div class="filter-field">

                            <label for="contact_sort_by">
                                Sort by
                            </label>

                            <select id="contact_sort_by" name="contact_sort_by">

                                <option value="most_recent" <?php echo
                                    $contactSortOrder === 'most_recent'
                                    ? 'selected'
                                    : '';
                                ?>>
                                    Most recent
                                </option>

                                <option value="oldest_first" <?php echo
                                    $contactSortOrder === 'oldest_first'
                                    ? 'selected'
                                    : '';
                                ?>>
                                    Oldest first
                                </option>

                            </select>

                        </div>


                        <div class="filter-actions">

                            <button type="submit">
                                Apply
                            </button>

                            <a href="dashboard.php">
                                Reset
                            </a>

                        </div>

                    </form>

                </div>


                <!-- CONTACT SEARCH -->

                <form method="GET" action="dashboard.php" class="dashboard-search-form">

                    <input type="text" name="contact_search" value="<?php echo htmlspecialchars(
                        $contactSearch,
                        ENT_QUOTES,
                        'UTF-8'
                    ); ?>" placeholder="Search contact by email or phone..." autocomplete="off">

                    <input type="hidden" name="appointment_search" value="<?php echo htmlspecialchars(
                        $appointmentSearch,
                        ENT_QUOTES,
                        'UTF-8'
                    ); ?>">

                    <input type="hidden" name="status_filter" value="<?php echo htmlspecialchars(
                        $filterStatus,
                        ENT_QUOTES,
                        'UTF-8'
                    ); ?>">

                    <input type="hidden" name="sort_by" value="<?php echo htmlspecialchars(
                        $sortOrder,
                        ENT_QUOTES,
                        'UTF-8'
                    ); ?>">

                    <input type="hidden" name="contact_sort_by" value="<?php echo htmlspecialchars(
                        $contactSortOrder,
                        ENT_QUOTES,
                        'UTF-8'
                    ); ?>">

                    <button type="submit">
                        Search
                    </button>


                    <?php if ($contactSearch !== ""): ?>

                        <a
                            href="dashboard.php?status_filter=<?php echo urlencode($filterStatus); ?>&sort_by=<?php echo urlencode($sortOrder); ?>&appointment_search=<?php echo urlencode($appointmentSearch); ?>&contact_sort_by=<?php echo urlencode($contactSortOrder); ?>">
                            Clear
                        </a>

                    <?php endif; ?>

                </form>


                <!-- CONTACT WINDOW -->

                <div class="dashboard-table-window">

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

                            </tr>

                        </thead>


                        <tbody>

                            <?php if (empty($contactMessages)): ?>

                                <tr>

                                    <td colspan="7">

                                        <?php if ($contactSearch !== ""): ?>

                                            No contact message found for that
                                            email or phone number.

                                        <?php else: ?>

                                            No contact messages found.

                                        <?php endif; ?>

                                    </td>

                                </tr>

                            <?php else: ?>

                                <?php foreach (
                                    $contactMessages
                                    as $message
                                ): ?>

                                    <tr>

                                        <td>
                                            <?php echo htmlspecialchars(
                                                $message['message_id'],
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ); ?>
                                        </td>


                                        <td>
                                            <?php echo htmlspecialchars(
                                                $message['name'],
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ); ?>
                                        </td>


                                        <td>
                                            <?php echo htmlspecialchars(
                                                $message['email'],
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ); ?>
                                        </td>


                                        <td>
                                            <?php echo htmlspecialchars(
                                                $message['phone']
                                                ?? 'N/A',
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ); ?>
                                        </td>


                                        <td>
                                            <?php echo htmlspecialchars(
                                                $message['subject'],
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ); ?>
                                        </td>


                                        <td class="message-cell">

                                            <?php echo nl2br(
                                                htmlspecialchars(
                                                    $message['message'],
                                                    ENT_QUOTES,
                                                    'UTF-8'
                                                )
                                            ); ?>

                                        </td>


                                        <td>

                                            <?php echo htmlspecialchars(
                                                $message['created_at'],
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ); ?>

                                        </td>

                                    </tr>

                                <?php endforeach; ?>

                            <?php endif; ?>

                        </tbody>

                    </table>

                </div>


            </section>


        </main>

    </div>

</body>

</html>