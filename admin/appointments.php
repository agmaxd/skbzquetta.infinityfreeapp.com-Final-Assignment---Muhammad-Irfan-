<?php
require_once __DIR__ . "/auth.php";

$message = "";
$messageType = "";

/* =========================================================
   SEARCH + SORT
========================================================= */

$search = trim($_GET["search"] ?? "");

$sortOrder = $_GET["sort_by"] ?? "newest_first";

$allowedSortOrders = [
    "newest_first",
    "oldest_first"
];

if (!in_array($sortOrder, $allowedSortOrders, true)) {
    $sortOrder = "newest_first";
}


/* =========================================================
   DEPARTMENT / DOCTOR OPTIONS
========================================================= */

$departmentOptions = $pdo->query(
    "SELECT department_id, department_name
     FROM departments
     ORDER BY department_name"
)->fetchAll();


$doctorOptions = $pdo->query(
    "SELECT
        d.doctor_id,
        d.department_id,
        d.full_name,
        d.specialization,
        dep.department_name
     FROM doctors d
     LEFT JOIN departments dep
        ON dep.department_id = d.department_id
     WHERE d.status = 'active'
     ORDER BY d.full_name"
)->fetchAll();


/* =========================================================
   UPDATE / DELETE APPOINTMENT
========================================================= */

if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    isset($_POST["appointment_id"])
) {

    if (
        !isset($_POST["csrf_token"]) ||
        !verifyCsrfToken($_POST["csrf_token"])
    ) {

        $message = "Security validation failed. Please try again.";
        $messageType = "error";

    } else {

        $appointmentId = (int) $_POST["appointment_id"];


        /* =====================================================
           DELETE
        ===================================================== */

        if (
            isset($_POST["action"]) &&
            $_POST["action"] === "delete"
        ) {

            $pdo->beginTransaction();

            try {

                $getAppointmentData = $pdo->prepare(
                    "SELECT patient_id, medical_record_path
                     FROM appointments
                     WHERE appointment_id = ?"
                );

                $getAppointmentData->execute([
                    $appointmentId
                ]);

                $appointmentRow = $getAppointmentData->fetch();


                if ($appointmentRow) {

                    $patientId =
                        (int) $appointmentRow["patient_id"];

                    $medicalRecordPath =
                        $appointmentRow["medical_record_path"];


                    /* Delete uploaded medical record */

                    if (
                        $medicalRecordPath !== null &&
                        $medicalRecordPath !== ""
                    ) {

                        $filePath =
                            __DIR__ . "/../" . $medicalRecordPath;

                        if (file_exists($filePath)) {
                            unlink($filePath);
                        }
                    }


                    /* Delete appointment */

                    $deleteAppointment = $pdo->prepare(
                        "DELETE FROM appointments
                         WHERE appointment_id = ?"
                    );

                    $deleteAppointment->execute([
                        $appointmentId
                    ]);


                    /* Delete patient */

                    $deletePatient = $pdo->prepare(
                        "DELETE FROM patients
                         WHERE patient_id = ?"
                    );

                    $deletePatient->execute([
                        $patientId
                    ]);


                    $pdo->commit();

                    $message =
                        "Appointment deleted successfully.";

                    $messageType = "success";

                } else {

                    $pdo->rollBack();

                    $message =
                        "Appointment not found.";

                    $messageType = "error";
                }

            } catch (Throwable $e) {

                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }

                $message =
                    "Unable to delete the appointment.";

                $messageType = "error";
            }


            /* =====================================================
               UPDATE
            ===================================================== */

        } else {

            $patientName =
                trim($_POST["patient_name"] ?? "");

            $patientEmail =
                trim($_POST["patient_email"] ?? "");

            $patientPhone =
                trim($_POST["patient_phone"] ?? "");

            $patientGender =
                trim($_POST["gender"] ?? "");

            $patientDob =
                trim($_POST["date_of_birth"] ?? "");

            $departmentId =
                filter_input(
                    INPUT_POST,
                    "department_id",
                    FILTER_VALIDATE_INT
                );

            $doctorId =
                filter_input(
                    INPUT_POST,
                    "doctor_id",
                    FILTER_VALIDATE_INT
                );

            $appointmentType =
                trim($_POST["appointment_type"] ?? "");

            $appointmentDate =
                trim($_POST["appointment_date"] ?? "");

            $appointmentTime =
                trim($_POST["appointment_time"] ?? "");

            $status =
                trim($_POST["status"] ?? "Pending");


            $allowedStatusesUpdate = [
                "Pending",
                "Confirmed",
                "Completed",
                "Cancelled"
            ];


            $allowedGenders = [
                "",
                "Male",
                "Female",
                "Other",
                "Prefer not to say"
            ];


            if (
                $patientName === "" ||
                $patientEmail === "" ||
                $patientPhone === "" ||
                !$departmentId ||
                !$doctorId ||
                $appointmentType === "" ||
                $appointmentDate === "" ||
                $appointmentTime === "" ||
                !in_array(
                    $status,
                    $allowedStatusesUpdate,
                    true
                ) ||
                !in_array(
                    $patientGender,
                    $allowedGenders,
                    true
                )
            ) {

                $message =
                    "Please complete all required values before saving.";

                $messageType = "error";

            } else {

                /* Verify doctor belongs to department */

                $doctorCheck = $pdo->prepare(
                    "SELECT 1
                     FROM doctors
                     WHERE doctor_id = ?
                     AND department_id = ?
                     AND status = 'active'"
                );

                $doctorCheck->execute([
                    $doctorId,
                    $departmentId
                ]);


                if (!$doctorCheck->fetch()) {

                    $message =
                        "The selected doctor does not belong to the selected department.";

                    $messageType = "error";

                } else {

                    $cleanPhone =
                        preg_replace(
                            "/[\s\-()]/",
                            "",
                            $patientPhone
                        );


                    if (
                        !preg_match(
                            "/^(03[0-9]{9}|\+923[0-9]{9})$/",
                            $cleanPhone
                        ) ||
                        !filter_var(
                            $patientEmail,
                            FILTER_VALIDATE_EMAIL
                        )
                    ) {

                        $message =
                            "Please use a valid email and phone number.";

                        $messageType = "error";

                    } else {

                        $patientRow = $pdo->prepare(
                            "SELECT patient_id
                             FROM appointments
                             WHERE appointment_id = ?"
                        );

                        $patientRow->execute([
                            $appointmentId
                        ]);

                        $patient =
                            $patientRow->fetch();


                        if (!$patient) {

                            $message =
                                "Appointment not found.";

                            $messageType = "error";

                        } else {

                            $patientId =
                                (int) $patient["patient_id"];

                            $dobValue =
                                $patientDob !== ""
                                ? $patientDob
                                : null;


                            $pdo->beginTransaction();

                            try {

                                /* Update patient */

                                $updatePatient = $pdo->prepare(
                                    "UPDATE patients
                                     SET
                                        full_name = ?,
                                        email = ?,
                                        phone = ?,
                                        gender = ?,
                                        date_of_birth = ?
                                     WHERE patient_id = ?"
                                );

                                $updatePatient->execute([
                                    $patientName,
                                    $patientEmail,
                                    $cleanPhone,
                                    $patientGender !== ""
                                    ? $patientGender
                                    : null,
                                    $dobValue,
                                    $patientId
                                ]);


                                /* Update appointment */

                                $updateAppointment = $pdo->prepare(
                                    "UPDATE appointments
                                     SET
                                        department_id = ?,
                                        doctor_id = ?,
                                        appointment_type = ?,
                                        appointment_date = ?,
                                        appointment_time = ?,
                                        status = ?
                                     WHERE appointment_id = ?"
                                );

                                $updateAppointment->execute([
                                    $departmentId,
                                    $doctorId,
                                    $appointmentType,
                                    $appointmentDate,
                                    $appointmentTime,
                                    $status,
                                    $appointmentId
                                ]);


                                $pdo->commit();

                                $message =
                                    "Appointment updated successfully.";

                                $messageType =
                                    "success";

                            } catch (Throwable $e) {

                                if ($pdo->inTransaction()) {
                                    $pdo->rollBack();
                                }

                                $message =
                                    "Unable to update the appointment.";

                                $messageType =
                                    "error";
                            }
                        }
                    }
                }
            }
        }
    }
}


/* =========================================================
   FETCH APPOINTMENTS
========================================================= */

$sql = "
    SELECT
        a.appointment_id,
        a.patient_id,
        p.full_name AS patient_name,
        p.email,
        p.phone,
        p.gender,
        p.date_of_birth,
        d.doctor_id,
        dep.department_id,
        d.full_name AS doctor_name,
        dep.department_name,
        a.appointment_type,
        a.appointment_date,
        a.appointment_time,
        a.visit_reason,
        a.medical_record_path,
        a.status
    FROM appointments a
    LEFT JOIN patients p
        ON p.patient_id = a.patient_id
    LEFT JOIN doctors d
        ON d.doctor_id = a.doctor_id
    LEFT JOIN departments dep
        ON dep.department_id = a.department_id
";


/* =========================================================
   SEARCH BY EMAIL OR PHONE
========================================================= */

$params = [];

if ($search !== "") {

    $sql .= "
        WHERE
            p.email LIKE :search_email
            OR p.phone LIKE :search_phone
    ";

    $params[":search_email"] =
        "%" . $search . "%";

    $params[":search_phone"] =
        "%" . $search . "%";
}


/* =========================================================
   SORT
========================================================= */

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

$appointments = $stmt->fetchAll();

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>
        Appointments | Shaikh Khalifa Bin Zayed Hospital
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

                <a class="active" href="appointments.php">
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


            <div class="page-header">

                <div class="page-title-area">

                    <h1>
                        Appointments
                    </h1>

                    <p>
                        Manage and update patient appointment details
                    </p>

                    <div class="page-header-line"></div>

                </div>

            </div>



            <div class="card">


                <!-- =================================================
                 MESSAGE
            ================================================== -->

                <?php if ($message !== ""): ?>

                    <div class="msg <?php echo htmlspecialchars(
                        $messageType,
                        ENT_QUOTES,
                        'UTF-8'
                    ); ?>">

                        <?php echo htmlspecialchars(
                            $message,
                            ENT_QUOTES,
                            'UTF-8'
                        ); ?>

                    </div>

                <?php endif; ?>



                <!-- =================================================
                 SEARCH + SORT
            ================================================== -->

                <div class="dashboard-toolbar appointment-toolbar">



                    <form method="GET" action="appointments.php" class="filter-bar compact-filter-bar">


                        <!-- SEARCH -->

                        <div class="filter-field appointment-search-field">

                            <label for="appointment-search">
                                Search
                            </label>

                            <input type="text" id="appointment-search" name="search" value="<?php echo htmlspecialchars(
                                $search,
                                ENT_QUOTES,
                                'UTF-8'
                            ); ?>" placeholder="Email or phone number">

                        </div>



                        <!-- SORT -->

                        <div class="filter-field">

                            <label for="sort_by">
                                Sort by
                            </label>

                            <select id="sort_by" name="sort_by">

                                <option value="newest_first" <?php echo
                                    $sortOrder === "newest_first"
                                    ? "selected"
                                    : "";
                                ?>>
                                    Newest → Oldest
                                </option>

                                <option value="oldest_first" <?php echo
                                    $sortOrder === "oldest_first"
                                    ? "selected"
                                    : "";
                                ?>>
                                    Oldest → Newest
                                </option>

                            </select>

                        </div>



                        <!-- ACTIONS -->

                        <div class="filter-actions">

                            <button type="submit">
                                Apply
                            </button>


                            <a href="appointments.php">
                                Reset
                            </a>

                        </div>

                    </form>

                </div>



                <!-- =================================================
                 APPOINTMENT TABLE
            ================================================== -->

                <div class="appointment-table-window">

                    <table class="admin-table admin-edit-table">

                        <thead>

                            <tr>

                                <th>Patient</th>

                                <th>Doctor</th>

                                <th>Department</th>

                                <th>Appointment Type</th>

                                <th>Date</th>

                                <th>Time</th>

                                <th>Phone</th>

                                <th>Gender</th>

                                <th>Record</th>

                                <th>Status</th>

                                <th>Action</th>

                            </tr>

                        </thead>


                        <tbody>


                            <?php if (empty($appointments)): ?>

                                <tr>

                                    <td colspan="11">

                                        <?php if ($search !== ""): ?>

                                            No appointments found for
                                            "<?php echo htmlspecialchars(
                                                $search,
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ); ?>".

                                        <?php else: ?>

                                            No appointments found.

                                        <?php endif; ?>

                                    </td>

                                </tr>


                            <?php else: ?>


                                <?php foreach ($appointments as $appointment): ?>

                                    <tr>

                                        <form method="POST" action="appointments.php" class="admin-inline-edit-form">

                                            <input type="hidden" name="appointment_id" value="<?php echo
                                                (int) $appointment[
                                                    "appointment_id"
                                                ];
                                            ?>">

                                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(
                                                csrfToken(),
                                                ENT_QUOTES,
                                                "UTF-8"
                                            ); ?>">


                                            <!-- PATIENT -->

                                            <td>

                                                <div class="admin-inline-stack">

                                                    <input type="text" name="patient_name" value="<?php echo htmlspecialchars(
                                                        $appointment["patient_name"] ?? "",
                                                        ENT_QUOTES,
                                                        "UTF-8"
                                                    ); ?>">

                                                    <input type="email" name="patient_email" value="<?php echo htmlspecialchars(
                                                        $appointment["email"] ?? "",
                                                        ENT_QUOTES,
                                                        "UTF-8"
                                                    ); ?>">

                                                    <input type="date" name="date_of_birth" value="<?php echo htmlspecialchars(
                                                        $appointment["date_of_birth"] ?? "",
                                                        ENT_QUOTES,
                                                        "UTF-8"
                                                    ); ?>">

                                                </div>

                                            </td>


                                            <!-- DOCTOR -->

                                            <td>

                                                <select name="doctor_id">

                                                    <?php foreach ($doctorOptions as $doctor): ?>

                                                        <option value="<?php echo
                                                            (int) $doctor["doctor_id"];
                                                        ?>" <?php echo
                                                            (int) $doctor["doctor_id"] ===
                                                            (int) ($appointment["doctor_id"] ?? 0)
                                                            ? "selected"
                                                            : "";
                                                        ?>>

                                                            <?php echo htmlspecialchars(
                                                                $doctor["full_name"] .
                                                                " — " .
                                                                ($doctor["specialization"] ??
                                                                    "Qualification not set") .
                                                                " (" .
                                                                ($doctor["department_name"] ??
                                                                    "Department not set") .
                                                                ")",
                                                                ENT_QUOTES,
                                                                "UTF-8"
                                                            ); ?>

                                                        </option>

                                                    <?php endforeach; ?>

                                                </select>

                                            </td>


                                            <!-- DEPARTMENT -->

                                            <td>

                                                <select name="department_id">

                                                    <?php foreach ($departmentOptions as $department): ?>

                                                        <option value="<?php echo
                                                            (int) $department[
                                                                "department_id"
                                                            ];
                                                        ?>" <?php echo
                                                            (int) $department[
                                                                "department_id"
                                                            ] ===
                                                            (int) ($appointment[
                                                                "department_id"
                                                            ] ?? 0)
                                                            ? "selected"
                                                            : "";
                                                        ?>>

                                                            <?php echo htmlspecialchars(
                                                                $department[
                                                                    "department_name"
                                                                ],
                                                                ENT_QUOTES,
                                                                "UTF-8"
                                                            ); ?>

                                                        </option>

                                                    <?php endforeach; ?>

                                                </select>

                                            </td>


                                            <!-- APPOINTMENT TYPE -->

                                            <td>

                                                <select name="appointment_type">

                                                    <option value="In-Person Consultation" <?php echo
                                                        ($appointment[
                                                            "appointment_type"
                                                        ] ?? "") ===
                                                        "In-Person Consultation"
                                                        ? "selected"
                                                        : "";
                                                    ?>>
                                                        In-Person Consultation
                                                    </option>

                                                    <option value="Follow-up Visit" <?php echo
                                                        ($appointment[
                                                            "appointment_type"
                                                        ] ?? "") ===
                                                        "Follow-up Visit"
                                                        ? "selected"
                                                        : "";
                                                    ?>>
                                                        Follow-up Visit
                                                    </option>

                                                    <option value="General Check-up" <?php echo
                                                        ($appointment[
                                                            "appointment_type"
                                                        ] ?? "") ===
                                                        "General Check-up"
                                                        ? "selected"
                                                        : "";
                                                    ?>>
                                                        General Check-up
                                                    </option>

                                                </select>

                                            </td>


                                            <!-- DATE -->

                                            <td>

                                                <input type="date" name="appointment_date" value="<?php echo htmlspecialchars(
                                                    $appointment[
                                                        "appointment_date"
                                                    ] ?? "",
                                                    ENT_QUOTES,
                                                    "UTF-8"
                                                ); ?>">

                                            </td>


                                            <!-- TIME -->

                                            <td>

                                                <select name="appointment_time">

                                                    <?php
                                                    $times = [
                                                        "09:00",
                                                        "10:00",
                                                        "11:00",
                                                        "12:00",
                                                        "14:00",
                                                        "15:00",
                                                        "16:00"
                                                    ];
                                                    ?>

                                                    <?php foreach ($times as $time): ?>

                                                        <option value="<?php echo $time; ?>" <?php echo
                                                               ($appointment[
                                                                   "appointment_time"
                                                               ] ?? "") === $time
                                                               ? "selected"
                                                               : "";
                                                           ?>>

                                                            <?php echo $time; ?>

                                                        </option>

                                                    <?php endforeach; ?>

                                                </select>

                                            </td>


                                            <!-- PHONE -->

                                            <td>

                                                <input type="tel" name="patient_phone" value="<?php echo htmlspecialchars(
                                                    $appointment["phone"] ?? "",
                                                    ENT_QUOTES,
                                                    "UTF-8"
                                                ); ?>">

                                            </td>


                                            <!-- GENDER -->

                                            <td>

                                                <select name="gender">

                                                    <option value="" <?php echo
                                                        ($appointment["gender"] ?? "") === ""
                                                        ? "selected"
                                                        : "";
                                                    ?>>
                                                        Not set
                                                    </option>

                                                    <option value="Male" <?php echo
                                                        ($appointment["gender"] ?? "") === "Male"
                                                        ? "selected"
                                                        : "";
                                                    ?>>
                                                        Male
                                                    </option>

                                                    <option value="Female" <?php echo
                                                        ($appointment["gender"] ?? "") === "Female"
                                                        ? "selected"
                                                        : "";
                                                    ?>>
                                                        Female
                                                    </option>

                                                    <option value="Other" <?php echo
                                                        ($appointment["gender"] ?? "") === "Other"
                                                        ? "selected"
                                                        : "";
                                                    ?>>
                                                        Other
                                                    </option>

                                                    <option value="Prefer not to say" <?php echo
                                                        ($appointment["gender"] ?? "") ===
                                                        "Prefer not to say"
                                                        ? "selected"
                                                        : "";
                                                    ?>>
                                                        Prefer not to say
                                                    </option>

                                                </select>

                                            </td>


                                            <!-- MEDICAL RECORD -->

                                            <td>

                                                <?php if (
                                                    !empty(
                                                    $appointment[
                                                        "medical_record_path"
                                                    ]
                                                )
                                                ): ?>

                                                    <a href="../<?php echo htmlspecialchars(
                                                        $appointment[
                                                            "medical_record_path"
                                                        ],
                                                        ENT_QUOTES,
                                                        "UTF-8"
                                                    ); ?>" target="_blank" rel="noopener noreferrer">
                                                        View
                                                    </a>

                                                <?php else: ?>

                                                    —

                                                <?php endif; ?>

                                            </td>


                                            <!-- STATUS -->

                                            <td>

                                                <select name="status">

                                                    <option value="Pending" <?php echo
                                                        ($appointment["status"] ?? "") === "Pending"
                                                        ? "selected"
                                                        : "";
                                                    ?>>
                                                        Pending
                                                    </option>

                                                    <option value="Confirmed" <?php echo
                                                        ($appointment["status"] ?? "") === "Confirmed"
                                                        ? "selected"
                                                        : "";
                                                    ?>>
                                                        Confirmed
                                                    </option>

                                                    <option value="Completed" <?php echo
                                                        ($appointment["status"] ?? "") === "Completed"
                                                        ? "selected"
                                                        : "";
                                                    ?>>
                                                        Completed
                                                    </option>

                                                    <option value="Cancelled" <?php echo
                                                        ($appointment["status"] ?? "") === "Cancelled"
                                                        ? "selected"
                                                        : "";
                                                    ?>>
                                                        Cancelled
                                                    </option>

                                                </select>

                                            </td>


                                            <!-- ACTION -->

                                            <td>

                                                <div class="admin-action-stack">

                                                    <button type="submit">
                                                        Save
                                                    </button>


                                                    <button type="submit" class="danger-btn" name="action" value="delete"
                                                        onclick="return confirm('Delete this appointment row?');">
                                                        Delete
                                                    </button>

                                                </div>

                                            </td>

                                        </form>

                                    </tr>

                                <?php endforeach; ?>


                            <?php endif; ?>

                        </tbody>

                    </table>

                </div>

            </div>

        </main>

    </div>

</body>

</html>