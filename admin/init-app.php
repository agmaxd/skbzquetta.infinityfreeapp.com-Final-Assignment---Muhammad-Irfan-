<?php

require_once __DIR__ . "/../config/database.php";

try {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS admins (
            admin_id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(100) NOT NULL UNIQUE,
            password_hash VARCHAR(255) NOT NULL,
            full_name VARCHAR(150) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");

    $columnCheck = $pdo->query("SHOW COLUMNS FROM admins LIKE 'role'")->fetch();
    if (!$columnCheck) {
        $pdo->exec("ALTER TABLE admins ADD COLUMN role ENUM('super_admin', 'editor') DEFAULT 'editor' AFTER full_name");
    }

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS departments (
            department_id INT AUTO_INCREMENT PRIMARY KEY,
            department_name VARCHAR(150) NOT NULL,
            description TEXT,
            status ENUM('active', 'inactive') DEFAULT 'active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS doctors (
            doctor_id INT AUTO_INCREMENT PRIMARY KEY,
            department_id INT NOT NULL,
            full_name VARCHAR(150) NOT NULL,
            specialization VARCHAR(150) NOT NULL,
            available_days VARCHAR(255) DEFAULT NULL,
            image VARCHAR(255) DEFAULT NULL,
            status ENUM('active', 'inactive') DEFAULT 'active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (department_id) REFERENCES departments(department_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS patients (
            patient_id INT AUTO_INCREMENT PRIMARY KEY,
            full_name VARCHAR(150) NOT NULL,
            email VARCHAR(150) NOT NULL,
            phone VARCHAR(30) NOT NULL,
            date_of_birth DATE DEFAULT NULL,
            gender VARCHAR(30) DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS appointments (
            appointment_id INT AUTO_INCREMENT PRIMARY KEY,
            patient_id INT NOT NULL,
            doctor_id INT NOT NULL,
            department_id INT NOT NULL,
            appointment_type VARCHAR(100) NOT NULL,
            appointment_date DATE NOT NULL,
            appointment_time TIME NOT NULL,
            visit_reason TEXT,
            additional_message TEXT,
            medical_record_path VARCHAR(255) DEFAULT NULL,
            status ENUM('pending', 'confirmed', 'rejected', 'completed') DEFAULT 'pending',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (patient_id) REFERENCES patients(patient_id),
            FOREIGN KEY (doctor_id) REFERENCES doctors(doctor_id),
            FOREIGN KEY (department_id) REFERENCES departments(department_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");

    $columnCheck = $pdo->query("SHOW COLUMNS FROM contact_messages LIKE 'full_name'")->fetch();
    if (!$columnCheck) {
        $pdo->exec("ALTER TABLE contact_messages ADD COLUMN full_name VARCHAR(150) NOT NULL AFTER message_id");
    }

    $phoneCheck = $pdo->query("SHOW COLUMNS FROM contact_messages LIKE 'phone'")->fetch();
    if ($phoneCheck && $phoneCheck['Null'] === 'NO') {
        $pdo->exec("ALTER TABLE contact_messages MODIFY phone VARCHAR(30) NULL DEFAULT NULL");
    }

    $statusCheck = $pdo->query("SHOW COLUMNS FROM contact_messages LIKE 'status'")->fetch();
    if (!$statusCheck) {
        $pdo->exec("ALTER TABLE contact_messages ADD COLUMN status ENUM('new', 'read') NOT NULL DEFAULT 'new' AFTER message");
    }

    $recordColumnCheck = $pdo->query("SHOW COLUMNS FROM appointments LIKE 'medical_record_path'")->fetch();
    if (!$recordColumnCheck) {
        $pdo->exec("ALTER TABLE appointments ADD COLUMN medical_record_path VARCHAR(255) DEFAULT NULL AFTER additional_message");
    }

    $adminCount = $pdo->query("SELECT COUNT(*) FROM admins")->fetchColumn();
    if ((int) $adminCount === 0) {
        $defaultPassword = password_hash("admin123", PASSWORD_DEFAULT);
        $stmt = $pdo->prepare(
            "INSERT INTO admins (username, password_hash, full_name, role) VALUES (?, ?, ?, ?)"
        );
        $stmt->execute(["admin", $defaultPassword, "Administrator", "super_admin"]);
    } else {
        $stmt = $pdo->prepare("UPDATE admins SET role = 'super_admin' WHERE role IS NULL OR role = ''");
        $stmt->execute();
    }

    $departmentCount = $pdo->query("SELECT COUNT(*) FROM departments")->fetchColumn();
    if ((int) $departmentCount === 0) {
        $defaultDepartments = [
            "Cardiology",
            "Neurology",
            "Pediatrics",
            "General Medicine",
            "Orthopedics"
        ];

        $stmt = $pdo->prepare(
            "INSERT INTO departments (department_name, description, status) VALUES (?, ?, 'active')"
        );

        foreach ($defaultDepartments as $name) {
            $stmt->execute([$name, "Hospital department for " . $name]);
        }
    }

} catch (PDOException $e) {
    die("Database initialization failed: " . $e->getMessage());
}
