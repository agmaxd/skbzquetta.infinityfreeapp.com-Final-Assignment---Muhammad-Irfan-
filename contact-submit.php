<?php
session_start();

require_once __DIR__ . "/config/database.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: html/contact.html');
    exit;
}

$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$subject = trim($_POST['subject'] ?? '');
$message = trim($_POST['message'] ?? '');

$errors = [];

if ($name === '') {
    $errors[] = 'Please enter your full name.';
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Please enter a valid email address.';
}

if ($subject === '') {
    $errors[] = 'Please select a subject.';
}

if ($message === '') {
    $errors[] = 'Please enter your message.';
}

if (!empty($errors)) {
    $errorMessage = urlencode($errors[0]);
    header('Location: html/contact.html?status=error&message=' . $errorMessage);
    exit;
}

try {
    $stmt = $pdo->prepare(
        "INSERT INTO contact_messages (full_name, email, phone, subject, message, status) VALUES (?, ?, ?, ?, ?, 'new')"
    );

    $stmt->execute([
        $name,
        $email,
        $phone !== '' ? $phone : '',
        $subject,
        $message,
    ]);

    $successMessage = urlencode('Your message has been sent successfully. We will get back to you soon.');
    header('Location: html/contact.html?status=success&message=' . $successMessage);
    exit;
} catch (PDOException $e) {
    $errorMessage = urlencode('Something went wrong while saving your message. Please try again later.');
    header('Location: html/contact.html?status=error&message=' . $errorMessage);
    exit;
}
