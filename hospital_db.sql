-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 23, 2026 at 01:27 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `hospital_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `admin_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `role` enum('super_admin','editor') DEFAULT 'editor',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`admin_id`, `username`, `password_hash`, `full_name`, `role`, `created_at`) VALUES
(1, 'admin', '$2y$10$jZXVGgwgZueIdYFgGrHZOe9rFFkASzPDVVb3IX76yhpkjrLmTdnxi', 'Administrator', 'super_admin', '2026-08-13 22:38:08');

-- --------------------------------------------------------

--
-- Table structure for table `appointments`
--

CREATE TABLE `appointments` (
  `appointment_id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `doctor_id` int(11) NOT NULL,
  `department_id` int(11) NOT NULL,
  `appointment_type` enum('In-Person Consultation','Follow-up Visit','General Check-up') NOT NULL,
  `appointment_date` date NOT NULL,
  `appointment_time` time NOT NULL,
  `visit_reason` varchar(255) DEFAULT NULL,
  `additional_message` text DEFAULT NULL,
  `medical_record_path` varchar(255) DEFAULT NULL,
  `status` enum('Pending','Confirmed','Completed','Cancelled') DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `appointments`
--

INSERT INTO `appointments` (`appointment_id`, `patient_id`, `doctor_id`, `department_id`, `appointment_type`, `appointment_date`, `appointment_time`, `visit_reason`, `additional_message`, `medical_record_path`, `status`, `created_at`) VALUES
(25, 28, 19, 1, 'In-Person Consultation', '2027-11-11', '14:00:00', 'wdwecwe', '', 'uploads/medical-records/1787045327_40b0f770_Medical-Reports-of-Patients.png', 'Pending', '2026-08-18 09:28:47'),
(26, 29, 13, 5, 'In-Person Consultation', '2026-08-18', '09:00:00', 'asdad', '', 'uploads/medical-records/1787052549_cfb0b6db_Medical-Reports-of-Patients.png', 'Pending', '2026-08-18 11:29:09'),
(27, 30, 4, 2, 'In-Person Consultation', '2026-08-19', '09:00:00', 'chest pain', '', 'uploads/medical-records/1787064181_312c945a_images.jfif', 'Pending', '2026-08-18 14:43:01');

-- --------------------------------------------------------

--
-- Table structure for table `contact_messages`
--

CREATE TABLE `contact_messages` (
  `message_id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `subject` varchar(150) NOT NULL,
  `message` text NOT NULL,
  `status` enum('new','read') NOT NULL DEFAULT 'new',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `contact_messages`
--

INSERT INTO `contact_messages` (`message_id`, `full_name`, `email`, `phone`, `subject`, `message`, `status`, `created_at`) VALUES
(15, 'Ghazala Yasmin', 'directorspe1@gmail.com', '03337871296', 'General Inquiry', '03320566755033205667550332056675503320566755033205667550332056675503320566755033205667550332056675503320566755033205667550332056675503320566755033205667550332056675503320566755033205667550332056675503320566755033205667550332056675503320566755033205667550332056675503320566755033205667550332056675503320566755033205667550332056675503320566755033205667550332056675503320566755033205667550332056675503320566755033205667550332056675503320566755', 'new', '2026-08-18 09:06:56'),
(16, 'Irfan Qumbrani', 'mirfan.qumbrani@gmail.com', '03320566755', 'Facilities', '033205667550332056675503320566755033205667550332056675503320566755033205667550332056675503320566755033205667550332056675503320566755033205667550332056675503320566755033205667550332056675503320566755033205667550332056675503320566755033205667550332056675503320566755033205667550332056675503320566755033205667550332056675503320566755033205667550332056675503320566755', 'new', '2026-08-18 09:07:05'),
(18, 'Ghazala Yasmin', 'directorspe1@gmail.com', '03337871296', 'Hospital Services', 'Lorem ipsum dolor sit amet consectetur adipisicing elit. Sit possimus nemo quaerat? Totam in numquam corrupti, debitis, dicta incidunt iure aspernatur natus tempore nostrum enim! Nostrum, obcaecati vel. Voluptate sit obcaecati fuga tenetur aspernatur officiis, voluptatem laudantium necessitatibus pariatur culpa error, repellat iusto totam nesciunt dignissimos. Maiores sint vitae cum quo voluptate blanditiis nihil rerum ad nostrum, et assumenda dignissimos eligendi tempore explicabo ex neque dolor repudiandae optio voluptas architecto aliquid consectetur exercitationem accusantium! Voluptatum earum maiores beatae laborum sit porro neque nemo iure accusantium voluptas dolore aperiam, quo dolorem, voluptate consequatur voluptatem veniam eius nisi nostrum deserunt laudantium a provident! Distinctio delectus voluptate fugit adipisci eveniet at magni. Ea accusantium adipisci pariatur ratione eos, recusandae iusto. Qui odio facere aspernatur, sapiente quos recusandae quae magni quo, consequatur, amet veritatis. Non maiores magni temporibus reiciendis excepturi cumque debitis eaque, rerum quisquam officiis repellendus unde repellat hic perferendis dignissimos libero corporis dolores iure! Beatae dolore odit doloremque qui maiores debitis cum cupiditate, voluptates atque repellat quam? Necessitatibus commodi voluptatibus soluta aut labore blanditiis cum possimus ducimus officia fugiat repellat porro nam aspernatur, deleniti libero pariatur unde deserunt, suscipit optio distinctio! Ullam laborum doloribus molestiae officia labore nisi consequatur sunt laboriosam, ducimus eius aspernatur quis magnam omnis rem, adipisci reiciendis impedit consectetur repellat soluta. Sapiente cum veniam quaerat unde qui velit non corrupti magnam ducimus consectetur consequuntur aliquam omnis placeat molestias nam aspernatur harum incidunt culpa, laboriosam eum animi adipisci! Molestiae accusantium cupiditate, placeat, temporibus non amet pariatur quae quas earum assumenda ea, totam aut dolorum', 'new', '2026-08-18 09:24:21'),
(19, 'Muhammad Irfan', 'mirfan.qumbrani@gmail.com', '03320566755', 'Departments', 'Lorem ipsum dolor sit amet consectetur adipisicing elit. Sit possimus nemo quaerat? Totam in numquam corrupti, debitis, dicta incidunt iure aspernatur natus tempore nostrum enim! Nostrum, obcaecati vel. Voluptate sit obcaecati fuga tenetur aspernatur officiis, voluptatem laudantium necessitatibus pariatur culpa error, repellat iusto totam nesciunt dignissimos. Maiores sint vitae cum quo voluptate blanditiis nihil rerum ad nostrum, et assumenda dignissimos eligendi tempore explicabo ex neque dolor repudiandae optio voluptas architecto aliquid consectetur exercitationem accusantium! Voluptatum earum maiores beatae laborum sit porro neque nemo iure accusantium voluptas dolore aperiam, quo dolorem, voluptate consequatur voluptatem veniam eius nisi nostrum deserunt laudantium a provident! Distinctio delectus voluptate fugit adipisci eveniet at magni. Ea accusantium adipisci pariatur ratione eos, recusandae iusto. Qui odio facere aspernatur, sapiente quos recusandae quae magni quo, consequatur, amet veritatis. Non maiores magni temporibus reiciendis excepturi cumque debitis eaque, rerum quisquam officiis repellendus unde repellat hic perferendis dignissimos libero corporis dolores iure! Beatae dolore odit doloremque qui maiores debitis cum cupiditate, voluptates atque repellat quam? Necessitatibus commodi voluptatibus soluta aut labore blanditiis cum possimus ducimus officia fugiat repellat porro nam aspernatur, deleniti libero pariatur unde deserunt, suscipit optio distinctio! Ullam laborum doloribus molestiae officia labore nisi consequatur sunt laboriosam, ducimus eius aspernatur quis magnam omnis rem, adipisci reiciendis impedit consectetur repellat soluta. Sapiente cum veniam quaerat unde qui velit non corrupti magnam ducimus consectetur consequuntur aliquam omnis placeat molestias nam aspernatur harum incidunt culpa, laboriosam eum animi adipisci! Molestiae accusantium cupiditate, placeat, temporibus non amet pariatur quae quas earum assumenda ea, totam aut dolorum', 'new', '2026-08-18 09:24:31'),
(22, 'Irfan Qumbrani', 'mirfan.qumbrani@gmail.com', '03320566755', 'Hospital Services', 'hellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohellohello', 'new', '2026-08-18 14:45:09');

-- --------------------------------------------------------

--
-- Table structure for table `departments`
--

CREATE TABLE `departments` (
  `department_id` int(11) NOT NULL,
  `department_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `departments`
--

INSERT INTO `departments` (`department_id`, `department_name`, `description`, `created_at`) VALUES
(1, 'Cardiology', 'Specialized medical care for conditions affecting the heart and cardiovascular system.', '2026-08-13 14:37:00'),
(2, 'Neurology', 'Diagnosis and treatment of disorders affecting the brain, spinal cord and nervous system.', '2026-08-13 14:37:00'),
(3, 'Pediatrics', 'Comprehensive healthcare services for infants, children and adolescents.', '2026-08-13 14:37:00'),
(4, 'General Medicine', 'General medical consultation, diagnosis, prevention and treatment for adults.', '2026-08-13 14:37:00'),
(5, 'Orthopedics', 'Medical and surgical care for bones, joints, muscles and the musculoskeletal system.', '2026-08-13 14:37:00');

-- --------------------------------------------------------

--
-- Table structure for table `doctors`
--

CREATE TABLE `doctors` (
  `doctor_id` int(11) NOT NULL,
  `department_id` int(11) NOT NULL,
  `full_name` varchar(120) NOT NULL,
  `specialization` varchar(150) NOT NULL,
  `qualification` varchar(255) DEFAULT NULL,
  `experience_years` int(11) DEFAULT 0,
  `gender` enum('Male','Female') NOT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `available_days` varchar(255) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `doctors`
--

INSERT INTO `doctors` (`doctor_id`, `department_id`, `full_name`, `specialization`, `qualification`, `experience_years`, `gender`, `phone`, `email`, `bio`, `image`, `available_days`, `status`, `created_at`) VALUES
(1, 1, 'Dr. Muhammad Hamza Khan', 'Interventional Cardiology', 'MBBS, FCPS Cardiology', 12, 'Male', '+92 300 1111111', 'hamza.khan@hospital.com', 'Specialist in interventional cardiology, coronary artery disease and cardiac procedures.', 'doctor-hamza.jpg', 'Monday, Wednesday, Friday', 'active', '2026-08-13 14:37:00'),
(2, 1, 'Dr. Ayesha Fatima', 'Clinical Cardiology', 'MBBS, FCPS Cardiology', 9, 'Female', '+92 301 2222222', 'ayesha.fatima@hospital.com', 'Experienced cardiologist specializing in hypertension, heart failure and preventive cardiac care.', 'doctor-ayesha.jpg', 'Tuesday, Thursday, Saturday', 'active', '2026-08-13 14:37:00'),
(3, 1, 'Dr. Usman Ahmed Siddiqui', 'Cardiac Electrophysiology', 'MBBS, MRCP, FCPS Cardiology', 15, 'Male', '+92 302 3333333', 'usman.siddiqui@hospital.com', 'Specialist in cardiac rhythm disorders, arrhythmias and electrophysiology.', 'doctor-usman.jpg', 'Monday, Tuesday, Thursday', 'active', '2026-08-13 14:37:00'),
(4, 2, 'Dr. Bilal Ahmed', 'Clinical Neurology', 'MBBS, FCPS Neurology', 11, 'Male', '+92 303 4444444', 'bilal.ahmed@hospital.com', 'Neurologist specializing in headaches, epilepsy and neurological disorders.', 'doctor-bilal.jpg', 'Monday, Wednesday, Saturday', 'active', '2026-08-13 14:37:00'),
(5, 2, 'Dr. Maryam Noor', 'Neurophysiology', 'MBBS, FCPS Neurology', 8, 'Female', '+92 304 5555555', 'maryam.noor@hospital.com', 'Specialist in neurophysiology, epilepsy and peripheral nervous system disorders.', 'doctor-maryam.jpg', 'Tuesday, Thursday, Friday', 'active', '2026-08-13 14:37:00'),
(6, 2, 'Dr. Hassan Raza', 'Stroke & Vascular Neurology', 'MBBS, FCPS, Fellowship in Stroke Medicine', 14, 'Male', '+92 305 6666666', 'hassan.raza@hospital.com', 'Experienced neurologist focusing on stroke management and cerebrovascular disorders.', 'doctor-hassan.jpg', 'Monday, Wednesday, Thursday', 'active', '2026-08-13 14:37:00'),
(7, 3, 'Dr. Zainab Ahmed', 'General Pediatrics', 'MBBS, FCPS Pediatrics', 10, 'Female', '+92 306 7777777', 'zainab.ahmed@hospital.com', 'Pediatrician providing comprehensive healthcare for infants, children and adolescents.', 'doctor-zainab.jpg', 'Monday, Tuesday, Friday', 'active', '2026-08-13 14:37:00'),
(8, 3, 'Dr. Abdullah Farooq', 'Pediatric Medicine', 'MBBS, FCPS Pediatrics', 13, 'Male', '+92 307 8888888', 'abdullah.farooq@hospital.com', 'Specialist in childhood illnesses, preventive healthcare and developmental medicine.', 'doctor-abdullah.jpg', 'Tuesday, Thursday, Saturday', 'active', '2026-08-13 14:37:00'),
(9, 3, 'Dr. Hafsa Malik', 'Pediatric Infectious Diseases', 'MBBS, FCPS Pediatrics', 7, 'Female', '+92 308 9999999', 'hafsa.malik@hospital.com', 'Pediatric specialist with a focus on childhood infections and preventive pediatric care.', 'doctor-hafsa.jpg', 'Monday, Wednesday, Thursday', 'active', '2026-08-13 14:37:00'),
(10, 4, 'Dr. Omar Abdullah', 'Internal Medicine', 'MBBS, FCPS Medicine', 16, 'Male', '+92 309 1010101', 'omar.abdullah@hospital.com', 'Experienced physician specializing in adult medicine, chronic disease management and diagnosis.', 'doctor-omar.jpg', 'Monday, Wednesday, Friday', 'active', '2026-08-13 14:37:00'),
(11, 4, 'Dr. Sara Mahmood', 'Internal Medicine', 'MBBS, MRCP', 9, 'Female', '+92 310 2020202', 'sara.mahmood@hospital.com', 'Physician specializing in preventive medicine, diabetes and hypertension management.', 'doctor-sara.jpg', 'Tuesday, Thursday, Saturday', 'active', '2026-08-13 14:37:00'),
(12, 4, 'Dr. Ibrahim Qureshi', 'General Medicine', 'MBBS, FCPS Medicine', 12, 'Male', '+92 311 3030303', 'ibrahim.qureshi@hospital.com', 'General physician experienced in complex adult medical conditions and comprehensive patient care.', 'doctor-ibrahim.jpg', 'Monday, Tuesday, Thursday', 'active', '2026-08-13 14:37:00'),
(13, 5, 'Dr. Khalid Mahmood', 'Orthopedic Surgery', 'MBBS, FCPS Orthopedic Surgery', 17, 'Male', '+92 312 4040404', 'khalid.mahmood@hospital.com', 'Orthopedic surgeon specializing in joint disorders, fractures and reconstructive procedures.', 'doctor-khalid.jpg', 'Monday, Wednesday, Friday', 'active', '2026-08-13 14:37:00'),
(14, 5, 'Dr. Noor Fatima', 'Sports Medicine', 'MBBS, FCPS Orthopedics', 8, 'Female', '+92 313 5050505', 'noor.fatima@hospital.com', 'Orthopedic specialist focusing on sports injuries, rehabilitation and musculoskeletal conditions.', 'doctor-noor.jpg', 'Tuesday, Thursday, Saturday', 'active', '2026-08-13 14:37:00'),
(15, 5, 'Dr. Saad Hussain', 'Joint Replacement Surgery', 'MBBS, FCPS Orthopedic Surgery', 14, 'Male', '+92 314 6060606', 'saad.hussain@hospital.com', 'Orthopedic surgeon specializing in joint replacement and degenerative joint conditions.', 'doctor-saad.jpg', 'Monday, Tuesday, Thursday', 'active', '2026-08-13 14:37:00'),
(16, 1, 'Dr. John Doe', 'Cardio Surgeon', NULL, 0, 'Male', NULL, NULL, NULL, NULL, NULL, 'active', '2026-08-14 07:57:45'),
(17, 1, 'Dr. Furqan', 'cardivascular surgeon', NULL, 0, 'Male', NULL, NULL, NULL, NULL, NULL, 'active', '2026-08-14 08:52:44'),
(18, 3, 'Dr Zahoor Qambrani', 'Pediatric Medicine', NULL, 0, 'Male', NULL, NULL, NULL, NULL, NULL, 'active', '2026-08-15 17:03:28'),
(19, 1, 'Dr Nida Yasmin', 'Clinical Cardiology', NULL, 0, 'Male', NULL, NULL, NULL, NULL, NULL, 'active', '2026-08-15 17:03:56'),
(20, 1, 'Dr Aurangzaib', 'Clinical Cardiology', NULL, 0, 'Male', NULL, NULL, NULL, NULL, NULL, 'active', '2026-08-15 19:40:58'),
(21, 2, 'Dr Irfan', 'Clinical Neurology', NULL, 0, 'Male', NULL, NULL, NULL, NULL, NULL, 'inactive', '2026-08-18 13:56:10');

-- --------------------------------------------------------

--
-- Table structure for table `gallery`
--

CREATE TABLE `gallery` (
  `gallery_id` int(11) NOT NULL,
  `title` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `image` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `patients`
--

CREATE TABLE `patients` (
  `patient_id` int(11) NOT NULL,
  `full_name` varchar(120) NOT NULL,
  `email` varchar(150) NOT NULL,
  `phone` varchar(30) NOT NULL,
  `date_of_birth` date DEFAULT NULL,
  `gender` enum('Male','Female','Other','Prefer not to say') DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `patients`
--

INSERT INTO `patients` (`patient_id`, `full_name`, `email`, `phone`, `date_of_birth`, `gender`, `created_at`) VALUES
(28, 'Irfan Qumbrani', 'mirfan.qumbrani@gmail.com', '+923320566755', '2026-08-06', 'Female', '2026-08-18 09:28:47'),
(29, 'Irfan Qumbrani', 'mirfan.qumbrani@gmail.com', '+923320566755', '1973-10-25', 'Male', '2026-08-18 11:29:09'),
(30, 'Irfan Qumbrani', 'mirfan.qumbrani@gmail.com', '+923320566755', '2000-11-11', 'Female', '2026-08-18 14:43:01');

-- --------------------------------------------------------

--
-- Table structure for table `services`
--

CREATE TABLE `services` (
  `service_id` int(11) NOT NULL,
  `service_name` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `services`
--

INSERT INTO `services` (`service_id`, `service_name`, `description`, `image`, `status`, `created_at`) VALUES
(1, 'Emergency Care', '24-hour emergency medical services for urgent and critical conditions.', NULL, 'active', '2026-08-13 14:37:00'),
(2, 'Cardiology', 'Comprehensive diagnosis and treatment of cardiovascular conditions.', NULL, 'active', '2026-08-13 14:37:00'),
(3, 'Neurology', 'Specialized diagnosis and treatment of neurological disorders.', NULL, 'active', '2026-08-13 14:37:00'),
(4, 'Pediatrics', 'Healthcare services for infants, children and adolescents.', NULL, 'active', '2026-08-13 14:37:00'),
(5, 'Orthopedics', 'Treatment and management of bone, joint and musculoskeletal conditions.', NULL, 'active', '2026-08-13 14:37:00'),
(6, 'General Medicine', 'Comprehensive medical consultation and treatment for adults.', NULL, 'active', '2026-08-13 14:37:00');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`admin_id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `appointments`
--
ALTER TABLE `appointments`
  ADD PRIMARY KEY (`appointment_id`),
  ADD KEY `patient_id` (`patient_id`),
  ADD KEY `doctor_id` (`doctor_id`),
  ADD KEY `department_id` (`department_id`);

--
-- Indexes for table `contact_messages`
--
ALTER TABLE `contact_messages`
  ADD PRIMARY KEY (`message_id`);

--
-- Indexes for table `departments`
--
ALTER TABLE `departments`
  ADD PRIMARY KEY (`department_id`),
  ADD UNIQUE KEY `department_name` (`department_name`);

--
-- Indexes for table `doctors`
--
ALTER TABLE `doctors`
  ADD PRIMARY KEY (`doctor_id`),
  ADD KEY `department_id` (`department_id`);

--
-- Indexes for table `gallery`
--
ALTER TABLE `gallery`
  ADD PRIMARY KEY (`gallery_id`);

--
-- Indexes for table `patients`
--
ALTER TABLE `patients`
  ADD PRIMARY KEY (`patient_id`);

--
-- Indexes for table `services`
--
ALTER TABLE `services`
  ADD PRIMARY KEY (`service_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `admin_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `appointments`
--
ALTER TABLE `appointments`
  MODIFY `appointment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `contact_messages`
--
ALTER TABLE `contact_messages`
  MODIFY `message_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `departments`
--
ALTER TABLE `departments`
  MODIFY `department_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `doctors`
--
ALTER TABLE `doctors`
  MODIFY `doctor_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `gallery`
--
ALTER TABLE `gallery`
  MODIFY `gallery_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `patients`
--
ALTER TABLE `patients`
  MODIFY `patient_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `services`
--
ALTER TABLE `services`
  MODIFY `service_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `appointments`
--
ALTER TABLE `appointments`
  ADD CONSTRAINT `appointments_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`patient_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `appointments_ibfk_2` FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`doctor_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `appointments_ibfk_3` FOREIGN KEY (`department_id`) REFERENCES `departments` (`department_id`) ON UPDATE CASCADE;

--
-- Constraints for table `doctors`
--
ALTER TABLE `doctors`
  ADD CONSTRAINT `doctors_ibfk_1` FOREIGN KEY (`department_id`) REFERENCES `departments` (`department_id`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
