<?php

require_once __DIR__ . "/auth.php";

$message = "";
$messageType = "";


/* =========================================================
   LOAD DEPARTMENTS
========================================================= */

$departmentOptions = $pdo->query("
    SELECT department_id, department_name
    FROM departments
    ORDER BY department_name
")->fetchAll();


/* =========================================================
   ADD / EDIT / DELETE / STATUS
========================================================= */

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
        $message = "Security validation failed. Please try again.";
        $messageType = "error";
    } else {
        $action = $_POST["action"] ?? "";


        /* =====================================================
           DELETE DOCTOR
        ===================================================== */

        if ($action === "delete") {

            $doctorId = filter_input(
                INPUT_POST,
                "doctor_id",
                FILTER_VALIDATE_INT
            );

            if (!$doctorId) {

                $message = "Invalid doctor.";
                $messageType = "error";

            } else {

                try {

                    /*
                     * Check whether this doctor has appointments.
                     */

                    $check = $pdo->prepare("
                    SELECT COUNT(*)
                    FROM appointments
                    WHERE doctor_id = ?
                ");

                    $check->execute([$doctorId]);

                    $appointmentCount = (int) $check->fetchColumn();


                    if ($appointmentCount > 0) {

                        $message =
                            "This doctor cannot be deleted because appointments are associated with this doctor. Deactivate the doctor instead.";

                        $messageType = "error";

                    } else {

                        $delete = $pdo->prepare("
                        DELETE FROM doctors
                        WHERE doctor_id = ?
                    ");

                        $delete->execute([$doctorId]);

                        $message = "Doctor deleted successfully.";
                        $messageType = "success";
                    }

                } catch (PDOException $e) {

                    $message = "Unable to delete doctor.";
                    $messageType = "error";
                }
            }
        }


        /* =====================================================
           ACTIVATE / DEACTIVATE
        ===================================================== */ elseif ($action === "toggle_status") {

            $doctorId = filter_input(
                INPUT_POST,
                "doctor_id",
                FILTER_VALIDATE_INT
            );

            $newStatus = $_POST["new_status"] ?? "";

            if (
                !$doctorId ||
                !in_array($newStatus, ["active", "inactive"], true)
            ) {

                $message = "Invalid doctor status.";
                $messageType = "error";

            } else {

                $stmt = $pdo->prepare("
                UPDATE doctors
                SET status = ?
                WHERE doctor_id = ?
            ");

                $stmt->execute([
                    $newStatus,
                    $doctorId
                ]);

                $message =
                    $newStatus === "active"
                    ? "Doctor is now available."
                    : "Doctor has been made unavailable.";

                $messageType = "success";
            }
        }


        /* =====================================================
           ADD / EDIT DOCTOR
        ===================================================== */ elseif ($action === "save") {

            $doctorId = filter_input(
                INPUT_POST,
                "doctor_id",
                FILTER_VALIDATE_INT
            );

            $fullName = trim(
                $_POST["full_name"] ?? ""
            );

            $specialization = trim(
                $_POST["specialization"] ?? ""
            );

            $departmentId = filter_input(
                INPUT_POST,
                "department_id",
                FILTER_VALIDATE_INT
            );

            $status = $_POST["status"] ?? "active";


            /* =================================================
               VALIDATION
            ================================================= */

            if (
                $fullName === "" ||
                $specialization === "" ||
                !$departmentId
            ) {

                $message =
                    "Please complete all required doctor information.";

                $messageType = "error";

            } elseif (
                !in_array($status, ["active", "inactive"], true)
            ) {

                $message = "Invalid doctor status.";
                $messageType = "error";

            } else {

                /* =============================================
                   CHECK DEPARTMENT
                ============================================= */

                $departmentCheck = $pdo->prepare("
                SELECT department_id
                FROM departments
                WHERE department_id = ?
            ");

                $departmentCheck->execute([
                    $departmentId
                ]);


                if (!$departmentCheck->fetch()) {

                    $message = "Invalid department.";
                    $messageType = "error";

                } else {

                    /* =========================================
                       UPDATE
                    ========================================= */

                    if ($doctorId) {

                        $stmt = $pdo->prepare("
                        UPDATE doctors
                        SET
                            full_name = ?,
                            specialization = ?,
                            department_id = ?,
                            status = ?
                        WHERE doctor_id = ?
                    ");

                        $stmt->execute([
                            $fullName,
                            $specialization,
                            $departmentId,
                            $status,
                            $doctorId
                        ]);

                        $message =
                            "Doctor updated successfully.";

                        $messageType = "success";

                    }


                    /* =========================================
                       INSERT
                    ========================================= */ else {

                        $stmt = $pdo->prepare("
                        INSERT INTO doctors
                        (
                            full_name,
                            specialization,
                            department_id,
                            status
                        )
                        VALUES (?, ?, ?, ?)
                    ");

                        $stmt->execute([
                            $fullName,
                            $specialization,
                            $departmentId,
                            $status
                        ]);

                        $message =
                            "Doctor added successfully.";

                        $messageType = "success";
                    }
                }
            }
        }
    }
}

/* =========================================================
   DEPARTMENT FILTER
========================================================= */

$filterDepartment = filter_input(
    INPUT_GET,
    "department_id",
    FILTER_VALIDATE_INT
);
/* =========================================================
   LOAD DOCTORS
========================================================= */

if ($filterDepartment) {

    $stmt = $pdo->prepare("
        SELECT
            d.doctor_id,
            d.full_name,
            d.specialization,
            d.status,
            d.department_id,
            dep.department_name
        FROM doctors d
        LEFT JOIN departments dep
            ON dep.department_id = d.department_id
        WHERE d.department_id = ?
        ORDER BY d.full_name ASC
    ");

    $stmt->execute([$filterDepartment]);

} else {

    $stmt = $pdo->query("
        SELECT
            d.doctor_id,
            d.full_name,
            d.specialization,
            d.status,
            d.department_id,
            dep.department_name
        FROM doctors d
        LEFT JOIN departments dep
            ON dep.department_id = d.department_id
        ORDER BY d.full_name ASC
    ");

}

$doctors = $stmt->fetchAll();

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>
        Doctors | Shaikh Khalifa Bin Zayed Hospital
    </title>

    <link rel="stylesheet" href="../Style.css">
    <link rel="icon" type="image/png" href="../images/logo.png">
</head>


<body class="admin-page-body">


    <div class="admin-shell">


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
                <a href="contact-messages.php">Contact Messages</a>

                <a href="doctors.php" class="active">
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
                        Doctors
                    </h1>

                    <p>
                        Manage hospital doctors and their availability.
                    </p>

                    <div class="page-header-line"></div>

                </div>

            </div>



            <!-- =================================================
             MESSAGE
        ================================================== -->

            <?php if ($message !== ""): ?>

                <div class="msg <?php echo $messageType; ?>">

                    <?php
                    echo htmlspecialchars(
                        $message,
                        ENT_QUOTES,
                        "UTF-8"
                    );
                    ?>

                </div>

            <?php endif; ?>



            <!-- =================================================
             ADD DOCTOR
        ================================================== -->

            <div class="card">

                <h2>
                    Add New Doctor
                </h2>

                <form method="POST" action="doctors.php">

                    <input type="hidden" name="action" value="save">

                    <input type="hidden" name="csrf_token"
                        value="<?php echo htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8'); ?>">

                    <div class="form-grid">


                        <div class="form-group">

                            <label>
                                Full Name
                            </label>

                            <input type="text" name="full_name" placeholder="Dr. John Doe" required>

                        </div>


                        <div class="form-group">

                            <label>
                                Specialization
                            </label>

                            <input type="text" name="specialization" placeholder="Cardiology" required>

                        </div>


                        <div class="form-group">

                            <label>
                                Department
                            </label>

                            <select name="department_id" required>

                                <option value="">
                                    Select Department
                                </option>

                                <?php foreach ($departmentOptions as $department): ?>

                                    <option value="<?php echo (int) $department["department_id"]; ?>">

                                        <?php
                                        echo htmlspecialchars(
                                            $department["department_name"],
                                            ENT_QUOTES,
                                            "UTF-8"
                                        );
                                        ?>

                                    </option>

                                <?php endforeach; ?>

                            </select>

                        </div>


                        <div class="form-group">

                            <label>
                                Status
                            </label>

                            <select name="status">

                                <option value="active">
                                    Available
                                </option>

                                <option value="inactive">
                                    Unavailable
                                </option>

                            </select>

                        </div>

                    </div>


                    <button type="submit">
                        Add Doctor
                    </button>

                </form>

            </div>



            <!-- =================================================
             DOCTOR LIST
        ================================================== -->

            <div class="card">

                <h2>
                    Manage Doctors
                </h2>

<form method="GET" action="doctors.php" class="doctor-filter">

    <label for="department-filter">
        Filter by Department
    </label>

    <select
        id="department-filter"
        name="department_id"
        onchange="this.form.submit()"
    >

        <option value="">
            All Departments
        </option>

        <?php foreach ($departmentOptions as $department): ?>

            <option
                value="<?php echo (int) $department["department_id"]; ?>"
                <?php
                echo $filterDepartment === (int) $department["department_id"]
                    ? "selected"
                    : "";
                ?>
            >

                <?php
                echo htmlspecialchars(
                    $department["department_name"],
                    ENT_QUOTES,
                    "UTF-8"
                );
                ?>

            </option>

        <?php endforeach; ?>

    </select>

</form>
                <table class="admin-table">

                    <thead>

                        <tr>

                            <th>
                                Doctor
                            </th>

                            <th>
                                Specialization
                            </th>

                            <th>
                                Department
                            </th>

                            <th>
                                Status
                            </th>

                            <th>
                                Actions
                            </th>

                        </tr>

                    </thead>


                    <tbody>

                        <?php if (empty($doctors)): ?>

                            <tr>

                                <td colspan="6">
                                    No doctors found.
                                </td>

                            </tr>

                        <?php else: ?>


                            <?php foreach ($doctors as $doctor): ?>

                                <tr>


                                    <!-- DOCTOR -->

                                    <td>

                                        <form method="POST" action="doctors.php">

                                            <input type="hidden" name="action" value="save">

                                            <input type="hidden" name="csrf_token"
                                                value="<?php echo htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8'); ?>">

                                            <input type="hidden" name="doctor_id"
                                                value="<?php echo (int) $doctor["doctor_id"]; ?>">

                                            <input type="text" name="full_name" value="<?php
                                            echo htmlspecialchars(
                                                $doctor["full_name"],
                                                ENT_QUOTES,
                                                "UTF-8"
                                            );
                                            ?>" required>

                                    </td>


                                    <!-- SPECIALIZATION -->

                                    <td>

                                        <input type="text" name="specialization" value="<?php
                                        echo htmlspecialchars(
                                            $doctor["specialization"],
                                            ENT_QUOTES,
                                            "UTF-8"
                                        );
                                        ?>" required>

                                    </td>


                                    <!-- DEPARTMENT -->

                                    <td>

                                        <select name="department_id" required>

                                            <?php foreach ($departmentOptions as $department): ?>

                                                <option value="<?php echo (int) $department["department_id"]; ?>" <?php
                                                    echo
                                                        (int) $department["department_id"]
                                                        ===
                                                        (int) $doctor["department_id"]
                                                        ? "selected"
                                                        : "";
                                                    ?>>

                                                    <?php
                                                    echo htmlspecialchars(
                                                        $department["department_name"],
                                                        ENT_QUOTES,
                                                        "UTF-8"
                                                    );
                                                    ?>

                                                </option>

                                            <?php endforeach; ?>

                                        </select>

                                    </td>


                                    <!-- STATUS -->

                                    <td>

                                        <select name="status">

                                            <option value="active" <?php
                                            echo $doctor["status"] === "active"
                                                ? "selected"
                                                : "";
                                            ?>>
                                                Available
                                            </option>

                                            <option value="inactive" <?php
                                            echo $doctor["status"] === "inactive"
                                                ? "selected"
                                                : "";
                                            ?>>
                                                Unavailable
                                            </option>

                                        </select>

                                    </td>


                                    <!-- ACTIONS -->

                                    <td>

                                        <div class="admin-action-stack">

                                            <button type="submit">
                                                Save
                                            </button>

                                            </form>


                                            <!-- DELETE -->

                                            <form method="POST" action="doctors.php"
                                                onsubmit="return confirm('Delete this doctor?');">

                                                <input type="hidden" name="action" value="delete">

                                                <input type="hidden" name="csrf_token"
                                                    value="<?php echo htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8'); ?>">

                                                <input type="hidden" name="doctor_id"
                                                    value="<?php echo (int) $doctor["doctor_id"]; ?>">

                                                <button type="submit" class="danger-btn">
                                                    Delete
                                                </button>

                                            </form>

                                        </div>

                                    </td>

                                </tr>

                            <?php endforeach; ?>

                        <?php endif; ?>

                    </tbody>

                </table>

            </div>

        </main>

    </div>


</body>

</html>