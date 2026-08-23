<?php

require_once "../config/database.php";

header("Content-Type: application/json");


/* =========================================================
   CHECK DEPARTMENT ID
========================================================= */

if (!isset($_GET["department_id"])) {

    echo json_encode([
        "success" => false,
        "message" => "Department ID is required."
    ]);

    exit;
}


/* =========================================================
   VALIDATE DEPARTMENT ID
========================================================= */

$departmentId = filter_input(
    INPUT_GET,
    "department_id",
    FILTER_VALIDATE_INT
);


if (!$departmentId) {

    echo json_encode([
        "success" => false,
        "message" => "Invalid department."
    ]);

    exit;
}


/* =========================================================
   GET DOCTORS
========================================================= */

try {

    $stmt = $pdo->prepare("
        SELECT
            doctor_id,
            full_name,
            specialization,
            available_days
        FROM doctors
        WHERE department_id = ?
        AND status = 'active'
        ORDER BY full_name ASC
    ");


    $stmt->execute([
        $departmentId
    ]);


    $doctors = $stmt->fetchAll();


    /* =====================================================
       RETURN JSON
    ===================================================== */

    echo json_encode([
        "success" => true,
        "doctors" => $doctors
    ]);

} catch (PDOException $e) {

    http_response_code(500);

    echo json_encode([
        "success" => false,
        "message" => "Unable to load doctors."
    ]);

}