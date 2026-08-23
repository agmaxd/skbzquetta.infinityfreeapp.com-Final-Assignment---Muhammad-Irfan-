<?php

require_once "../config/database.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    die("Invalid request.");
}

try {

    /* =========================================================
       GET FORM DATA
    ========================================================= */

    $fullName = trim($_POST["patient_name"] ?? "");
    $email = trim($_POST["patient_email"] ?? "");
    $phone = trim($_POST["patient_phone"] ?? "");
    $dateOfBirth = trim($_POST["date_of_birth"] ?? "");
    $gender = trim($_POST["gender"] ?? "");

    $doctorId = filter_input(
        INPUT_POST,
        "doctor_id",
        FILTER_VALIDATE_INT
    );

    $departmentId = filter_input(
        INPUT_POST,
        "department_id",
        FILTER_VALIDATE_INT
    );

    $appointmentType = trim(
        $_POST["appointment_type"] ?? ""
    );

    $appointmentDate = trim(
        $_POST["appointment_date"] ?? ""
    );

    $appointmentTime = trim(
        $_POST["appointment_time"] ?? ""
    );

    $visitReason = trim(
        $_POST["visit_reason"] ?? ""
    );

    $additionalMessage = trim(
        $_POST["additional_message"] ?? ""
    );

    $medicalRecordPath = null;
    $medicalRecordName = "";
    $consent = isset($_POST["consent"]);

    if (isset($_FILES["medical_record"]) && $_FILES["medical_record"]["error"] !== UPLOAD_ERR_NO_FILE) {
        $file = $_FILES["medical_record"];

        if ($file["error"] !== UPLOAD_ERR_OK) {
            die("There was a problem uploading the medical record.");
        }

        if ($file["size"] > 50 * 1024 * 1024) {
            die("Medical record must be 50MB or smaller.");
        }

        $allowedMimeTypes = [
            "image/jpeg",
            "image/png",
            "image/webp"
        ];

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($file["tmp_name"]);

        if (!in_array($mimeType, $allowedMimeTypes, true)) {
            die("Please upload a valid image file (JPG, PNG, or WEBP). ");
        }

        $safeName = preg_replace("/[^A-Za-z0-9._-]/", "_", pathinfo($file["name"], PATHINFO_FILENAME));
        $extension = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
        $uniqueName = time() . "_" . bin2hex(random_bytes(4)) . "_" . ($safeName !== "" ? $safeName : "record") . "." . $extension;

        $targetDir = __DIR__ . "/../uploads/medical-records";
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }

        $targetPath = $targetDir . "/" . $uniqueName;

        if (!move_uploaded_file($file["tmp_name"], $targetPath)) {
            die("Medical record could not be saved.");
        }

        $medicalRecordPath = "uploads/medical-records/" . $uniqueName;
        $medicalRecordName = basename($file["name"]);
    }


    /* =========================================================
       REQUIRED FIELDS
    ========================================================= */

    if (
        $fullName === "" ||
        $email === "" ||
        $phone === "" ||
        !$doctorId ||
        !$departmentId ||
        $appointmentType === "" ||
        $appointmentDate === "" ||
        $appointmentTime === "" ||
        !$consent
    ) {
        die("Please fill in all required fields.");
    }


    /* =========================================================
       NAME VALIDATION
       Letters, spaces, apostrophes, periods and hyphens only
    ========================================================= */

    if (
        mb_strlen($fullName) < 2 ||
        mb_strlen($fullName) > 100 ||
        !preg_match(
            "/^[\p{L}][\p{L} .'-]{1,99}$/u",
            $fullName
        )
    ) {
        die("Please enter a valid name.");
    }


    /* =========================================================
       EMAIL VALIDATION
    ========================================================= */

    if (
        mb_strlen($email) > 254 ||
        !filter_var($email, FILTER_VALIDATE_EMAIL)
    ) {
        die("Please enter a valid email address.");
    }


    /* =========================================================
       PHONE VALIDATION
       Accepts:
       03001234567
       +923001234567
    ========================================================= */

    $cleanPhone = preg_replace(
        "/[\s\-()]/",
        "",
        $phone
    );

    if (
        !preg_match(
            "/^(03[0-9]{9}|\+923[0-9]{9})$/",
            $cleanPhone
        )
    ) {
        die("Please enter a valid Pakistani mobile number.");
    }


    /* =========================================================
       GENDER VALIDATION
    ========================================================= */

    $allowedGenders = [
        "",
        "Male",
        "Female",
        "Other",
        "Prefer not to say"
    ];

    if (!in_array($gender, $allowedGenders, true)) {
        die("Invalid gender selection.");
    }


    /* =========================================================
       DATE OF BIRTH VALIDATION
       Age must be between 0 and 120 years.
    ========================================================= */

    $dobObject = null;

    if ($dateOfBirth !== "") {

        $dobObject = DateTime::createFromFormat(
            "Y-m-d",
            $dateOfBirth
        );

        $dobErrors =
            DateTime::getLastErrors();

        if (
            $dobObject === false ||
            (
                $dobErrors !== false &&
                (
                    $dobErrors["warning_count"] > 0 ||
                    $dobErrors["error_count"] > 0
                )
            ) ||
            $dobObject->format("Y-m-d") !== $dateOfBirth
        ) {
            die("Invalid date of birth.");
        }


        $today = new DateTime("today");


        /* Cannot be born in the future */

        if ($dobObject > $today) {
            die("Date of birth cannot be in the future.");
        }


        /* Maximum age = 120 years */

        $minimumDOB =
            (clone $today)->modify("-120 years");


        if ($dobObject < $minimumDOB) {
            die("Date of birth cannot be more than 120 years ago.");
        }
    }


    /* =========================================================
       APPOINTMENT DATE VALIDATION
       Cannot be in the past.
    ========================================================= */

    $appointmentDateObject =
        DateTime::createFromFormat(
            "Y-m-d",
            $appointmentDate
        );

    $appointmentDateErrors =
        DateTime::getLastErrors();


    if (
        $appointmentDateObject === false ||
        (
            $appointmentDateErrors !== false &&
            (
                $appointmentDateErrors["warning_count"] > 0 ||
                $appointmentDateErrors["error_count"] > 0
            )
        ) ||
        $appointmentDateObject->format("Y-m-d") !== $appointmentDate
    ) {
        die("Invalid appointment date.");
    }


    $today = new DateTime("today");


    if ($appointmentDateObject < $today) {
        die("Appointment date cannot be in the past.");
    }


    /* =========================================================
       APPOINTMENT TIME VALIDATION
    ========================================================= */

    $allowedTimes = [
        "09:00",
        "10:00",
        "11:00",
        "12:00",
        "14:00",
        "15:00",
        "16:00"
    ];

    if (!in_array($appointmentTime, $allowedTimes, true)) {
        die("Invalid appointment time.");
    }


    /* =========================================================
       APPOINTMENT TYPE VALIDATION
    ========================================================= */

    $allowedAppointmentTypes = [
        "In-Person Consultation",
        "Follow-up Visit",
        "General Check-up"
    ];

    if (
        !in_array(
            $appointmentType,
            $allowedAppointmentTypes,
            true
        )
    ) {
        die("Invalid appointment type.");
    }


    /* =========================================================
       TEXT LENGTH LIMITS
    ========================================================= */

    if (mb_strlen($visitReason) > 500) {
        die("Reason for visit is too long.");
    }

    if (mb_strlen($additionalMessage) > 2000) {
        die("Additional information is too long.");
    }


    /* =========================================================
       CHECK DOCTOR + DEPARTMENT
    ========================================================= */

    $doctorStmt = $pdo->prepare("
        SELECT doctor_id
        FROM doctors
        WHERE doctor_id = ?
        AND department_id = ?
        AND status = 'active'
    ");

    $doctorStmt->execute([
        $doctorId,
        $departmentId
    ]);

    if (!$doctorStmt->fetch()) {
        die("Invalid doctor selection.");
    }


    /* =========================================================
       CREATE PATIENT + APPOINTMENT TOGETHER
    ========================================================= */

    $pdo->beginTransaction();


    /* =========================================================
       CREATE PATIENT
    ========================================================= */

    $patientStmt = $pdo->prepare("
        INSERT INTO patients
        (
            full_name,
            email,
            phone,
            date_of_birth,
            gender
        )
        VALUES (?, ?, ?, ?, ?)
    ");

    $patientStmt->execute([
        $fullName,
        $email,
        $cleanPhone,
        $dateOfBirth !== "" ? $dateOfBirth : null,
        $gender !== "" ? $gender : null
    ]);


    $patientId =
        $pdo->lastInsertId();


    /* =========================================================
       CREATE APPOINTMENT
    ========================================================= */

    $appointmentStmt = $pdo->prepare("
        INSERT INTO appointments
        (
            patient_id,
            doctor_id,
            department_id,
            appointment_type,
            appointment_date,
            appointment_time,
            visit_reason,
            additional_message,
            medical_record_path,
            status
        )
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $appointmentStmt->execute([
        $patientId,
        $doctorId,
        $departmentId,
        $appointmentType,
        $appointmentDate,
        $appointmentTime,
        $visitReason,
        $additionalMessage,
        $medicalRecordPath,
        "pending"
    ]);


    /* =========================================================
       EVERYTHING SUCCESSFUL
    ========================================================= */

    $pdo->commit();


    /* =========================================================
       SAFE DISPLAY NAME
    ========================================================= */

    $safeName =
        htmlspecialchars(
            $fullName,
            ENT_QUOTES,
            "UTF-8"
        );


    /* =========================================================
       SUCCESS PAGE
    ========================================================= */

    echo "
    <!DOCTYPE html>

    <html lang='en'>

    <head>

        <meta charset='UTF-8'>

        <meta name='viewport'
              content='width=device-width, initial-scale=1.0'>

        <title>Appointment Submitted</title>

        <link rel='stylesheet'
              href='../Style.css'>

    </head>

    <body>

        <div style='
            max-width:700px;
            margin:120px auto;
            padding:50px;
            text-align:center;
            background:white;
            border-radius:20px;
            box-shadow:0 10px 40px rgba(0,0,0,.1);
        '>

            <div style='
                font-size:60px;
                margin-bottom:20px;
            '>
                ✓
            </div>

            <h1>
                Appointment Request Submitted
            </h1>

            <p>
                Thank you, {$safeName}.
                Your appointment request has been
                successfully received.
            </p>

            <p>
                Our hospital team will review your request
                and contact you to confirm the appointment.
            </p>

            <a href='../html/appointment.html'
               style='
                    display:inline-block;
                    margin-top:25px;
                    padding:14px 28px;
                    background:#8f1025;
                    color:white;
                    text-decoration:none;
                    border-radius:8px;
               '>
                Back to Appointment Page
            </a>

        </div>

    </body>

    </html>
    ";

} catch (PDOException $e) {

    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    die(
        "Something went wrong while booking the appointment."
    );
}
?>