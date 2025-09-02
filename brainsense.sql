-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 15, 2025 at 03:09 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `brainsense`
--

-- --------------------------------------------------------

--
-- Table structure for table `activities`
--

CREATE TABLE `activities` (
  `id` int(11) NOT NULL,
  `patient_id` int(11) DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `appointments`
--

CREATE TABLE `appointments` (
  `id` int(11) NOT NULL,
  `patient_id` int(11) DEFAULT NULL,
  `appointment_date` datetime DEFAULT NULL,
  `appointment_type` varchar(100) NOT NULL,
  `id_doctor` int(255) NOT NULL,
  `doctor_name` varchar(100) DEFAULT NULL,
  `specialty` varchar(100) DEFAULT NULL,
  `type` varchar(50) DEFAULT NULL,
  `purpose` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `feedback` text DEFAULT NULL,
  `patient_questions` text DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `join_link` varchar(255) DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `reminder_sent` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `appointments`
--

INSERT INTO `appointments` (`id`, `patient_id`, `appointment_date`, `appointment_type`, `id_doctor`, `doctor_name`, `specialty`, `type`, `purpose`, `notes`, `feedback`, `patient_questions`, `location`, `join_link`, `status`, `created_by`, `created_at`, `updated_at`, `reminder_sent`) VALUES
(20, 1, '2025-05-27 11:00:00', 'Consultation', 1, 'Dr. Mehdi Benslimane', 'Neurologie', 'En personne', 'Suivi de l\'hypertension', 'Patient nécessite un suivi régulier', NULL, 'Quels sont les effets secondaires des nouveaux médicaments?', 'Clinique Maroc, Rabat', NULL, 'Confirmé', 23, '2025-05-26 09:35:00', '2025-05-27 08:44:03', 0),
(21, 1, '2025-05-27 14:30:00', 'Suivi', 1, 'Dr. Malak Regragui', 'Médecine générale', 'En personne', 'Examen annuel', 'Vérifier les résultats des analyses sanguines', NULL, NULL, 'Clinique Maroc, Rabat', NULL, 'Confirmé', 23, '2025-05-26 09:40:00', '2025-05-27 08:44:06', 0),
(22, 1, '2025-05-28 11:17:00', 'Téléconsultation', 1, 'Dr. Mehdi Benslimane', 'Neurologie', 'Virtuel', 'Discussion des résultats IRM', 'Envoyer les résultats avant la consultation\n[Rescheduled] mm', NULL, NULL, 'En ligne', 'https://meet.cliniquemaroc.ma/omar-cherkaoui', 'Confirmé', 23, '2025-05-26 09:45:00', '2025-05-27 08:57:16', 0),
(119, 1, '2025-05-23 19:14:00', '', 1, 'Dr. Mehdi Benslimane', 'Neurology', 'In-person', 'Suivi des migraines chroniques', '\n[Rescheduled] ', NULL, NULL, 'Centre médical Hassan II, Rabat', NULL, 'Scheduled', 1, '2025-05-21 17:14:02', '2025-05-25 17:50:29', 0),
(120, 1, '2025-06-05 18:14:02', '', 1, 'Dr. Mehdi Benslimane', 'Neurology', 'In-person', 'Suivi des migraines chroniques', NULL, NULL, NULL, 'Centre médical Hassan II, Rabat', NULL, 'Scheduled', 1, '2025-05-21 17:14:02', '2025-05-27 08:43:25', 0),
(122, 7, '2025-05-24 01:09:00', 'Follow-up', 0, NULL, NULL, NULL, NULL, 'adfsf', NULL, NULL, 'brainsens', NULL, 'confirmed', 1, '2025-05-23 00:09:04', '2025-05-25 17:50:29', 0),
(123, 4, '2025-05-23 04:14:00', 'Follow-up', 0, NULL, NULL, NULL, NULL, 'gbd', NULL, NULL, 'fgfgbgb', NULL, 'confirmed', 1, '2025-05-23 00:11:12', '2025-05-25 17:50:29', 0),
(124, 6, '2025-05-24 00:16:00', 'Scan Review', 0, NULL, NULL, NULL, NULL, 'gfgjj', NULL, NULL, 'fg', NULL, 'confirmed', 1, '2025-05-23 00:13:16', '2025-05-25 17:50:29', 0),
(125, 4, '2025-05-24 17:32:00', 'Consultation', 0, NULL, NULL, NULL, NULL, 'dvdv', NULL, NULL, 'v', NULL, 'confirmed', 1, '2025-05-23 16:30:46', '2025-05-25 17:50:29', 0),
(126, 27, '2025-06-26 09:55:00', '', 0, '24', NULL, 'Follow-up', NULL, 'cc', NULL, NULL, NULL, NULL, 'Scheduled', NULL, '2025-06-15 00:55:34', NULL, 0);

-- --------------------------------------------------------

--
-- Table structure for table `doctors`
--

CREATE TABLE `doctors` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `specialization` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `doctors`
--

INSERT INTO `doctors` (`id`, `user_id`, `name`, `specialization`, `created_at`) VALUES
(1, NULL, 'Dr. Malak Regragui', 'generalist ', '2023-11-01 08:15:00'),
(2, NULL, 'Dr. Mehdi Benslimane', 'Neurology', '2023-12-10 13:30:00'),
(3, NULL, 'Dr. Yasmine Khattabi', 'Pediatrics', '2024-01-05 09:45:00'),
(4, NULL, 'Dr. Omar El Idrissi', 'Orthopedics', '2024-02-17 07:20:00'),
(5, NULL, 'Dr. Salma Bouziane', 'Dermatology', '2024-03-22 12:10:00'),
(6, NULL, 'Dr. Rachid Laamrani', 'Oncology', '2024-04-15 14:00:00'),
(7, NULL, 'Dr. Imane Zaki', 'Ophthalmology', '2024-05-10 10:25:00'),
(20, 21, 'Dr. Mehdi Benslimane', 'Neurologie', '2025-05-26 09:05:00'),
(21, 22, 'Dr. Malak Regragui', 'Médecine générale', '2025-05-26 09:10:00'),
(24, 22, 'Dr. Malak Regragui', 'Médecine générale', '2025-05-26 09:10:00');

-- --------------------------------------------------------

--
-- Table structure for table `documents`
--

CREATE TABLE `documents` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `file_path` varchar(255) NOT NULL,
  `file_type` varchar(50) NOT NULL,
  `file_size` int(11) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `patient_id` int(11) DEFAULT NULL,
  `uploaded_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `is_public` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `documents`
--

INSERT INTO `documents` (`id`, `name`, `description`, `file_path`, `file_type`, `file_size`, `category_id`, `patient_id`, `uploaded_by`, `created_at`, `updated_at`, `is_public`) VALUES
(3, 'patient records ', 'ew', 'uploads/documents/682ddabc77690_1747835580.png', 'image/png', 1345517, 1, NULL, 1, '2025-05-21 13:53:00', NULL, 0),
(7, 'test', 'yoo', '../uploads/documents/682ddcbf4ea61_1747836095.pdf', 'application/pdf', 3001, 6, 7, 1, '2025-05-21 14:01:35', NULL, 0),
(8, 'yo', 'dv', 'C:\\xampp\\htdocs\\BranSense\\includes/../uploads/documents/68344c11eca26_test_pdf.pdf', 'pdf', 3001, 5, 6, 1, '2025-05-26 11:10:09', NULL, 0),
(9, 'yo', 'dv', 'C:\\xampp\\htdocs\\BranSense\\includes/../uploads/documents/68344c12ab386_test_pdf.pdf', 'pdf', 3001, 5, 6, 1, '2025-05-26 11:10:10', NULL, 0),
(20, 'IRM cérébrale', 'IRM effectuée le 15/05/2025', 'uploads/documents/IRM_Karim_El_Fassi_20.pdf', 'application/pdf', 2500, 20, 20, 21, '2025-05-26 10:25:00', '2025-05-26 10:25:00', 0),
(21, 'Analyses sanguines', 'Bilan complet du 18/05/2025', 'uploads/documents/Analyses_Leila_Benjelloun_21.pdf', 'application/pdf', 1800, 21, 21, 22, '2025-05-26 10:30:00', '2025-05-26 10:30:00', 0),
(22, 'patient records ', 'Just pour le test ', '../uploads/documents/68358122c1429_1748336930.pdf', 'application/pdf', 3001, 22, NULL, 1, '2025-05-27 09:08:50', NULL, 0);

-- --------------------------------------------------------

--
-- Table structure for table `document_categories`
--

CREATE TABLE `document_categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `document_categories`
--

INSERT INTO `document_categories` (`id`, `name`, `description`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'Medical Reports', 'Patient medical reports and diagnoses', 1, '2025-05-21 13:13:59', '2025-05-25 17:50:29'),
(2, 'Lab Results', 'Laboratory test results and analysis', 1, '2025-05-21 13:13:59', '2025-05-25 17:50:29'),
(3, 'Prescriptions', 'Medical prescriptions and medication records', 1, '2025-05-21 13:13:59', '2025-05-25 17:50:29'),
(4, 'Imaging', 'X-rays, MRIs, and other imaging results', 1, '2025-05-21 13:13:59', '2025-05-25 17:50:29'),
(5, 'Insurance', 'Insurance documents and claims', 1, '2025-05-21 13:13:59', '2025-05-25 17:50:29'),
(6, 'Managmenet', 'lalala', 1, '2025-05-21 13:18:21', '2025-05-25 17:50:29'),
(7, 'olol', 'fdf', 1, '2025-05-24 03:28:15', '2025-05-25 17:50:29'),
(8, 'patinet1 ', 'fd', NULL, '2025-05-26 11:12:48', NULL),
(20, 'Rapports médicaux', 'Rapports médicaux et diagnostics', 20, '2025-05-26 10:20:00', '2025-05-26 10:20:00'),
(21, 'Résultats de laboratoire', 'Résultats d\'analyses médicales', 20, '2025-05-26 10:20:00', '2025-05-26 10:20:00'),
(22, 'Categorie test ', 'Pour le test ', NULL, '2025-05-27 09:08:19', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `medical_reports`
--

CREATE TABLE `medical_reports` (
  `id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `doctor_id` int(11) NOT NULL,
  `report_type` enum('scan','lab','diagnosis','treatment') NOT NULL,
  `report_date` date NOT NULL,
  `findings` text NOT NULL,
  `status` enum('pending','completed') NOT NULL DEFAULT 'pending',
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `medical_reports`
--

INSERT INTO `medical_reports` (`id`, `patient_id`, `doctor_id`, `report_type`, `report_date`, `findings`, `status`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'scan', '2024-02-15', 'Normal brain structure, no abnormalities detected', 'pending', 1, '2025-05-23 15:05:13', '2025-05-25 17:50:29'),
(2, 2, 1, 'lab', '2024-02-14', 'All parameters within normal range', 'pending', 1, '2025-05-23 15:05:13', '2025-05-25 17:50:29'),
(3, 3, 1, 'diagnosis', '2024-02-13', 'Requires further analysis', 'pending', 1, '2025-05-23 15:05:13', '2025-05-25 17:50:29'),
(4, 4, 1, 'scan', '2025-05-22', 'Loorlgrj', 'completed', 1, '2025-05-23 15:18:50', '2025-05-28 11:09:02'),
(20, 20, 20, 'scan', '2025-05-15', 'IRM cérébrale normale, pas d\'anomalies détectées', 'completed', 21, '2025-05-26 10:00:00', '2025-05-26 10:00:00'),
(21, 21, 21, 'lab', '2025-05-18', 'Glycémie à jeun: 1.10 g/L (normal), Cholestérol total: 1.80 g/L', 'completed', 22, '2025-05-26 10:05:00', '2025-05-26 10:05:00'),
(22, 22, 20, 'diagnosis', '2025-05-20', 'Suspicion de migraine avec aura, nécessite examens complémentaires', 'completed', 21, '2025-05-26 10:10:00', '2025-05-28 11:01:48'),
(23, 7, 1, 'lab', '2001-11-11', 'soeaspace', 'completed', NULL, '2025-05-28 10:58:42', '2025-05-28 11:05:13'),
(24, 29, 1, 'lab', '2025-11-11', 'hellp', 'pending', NULL, '2025-06-02 20:49:30', '2025-06-02 20:49:30');

--
-- Triggers `medical_reports`
--
DELIMITER $$
CREATE TRIGGER `after_report_insert` AFTER INSERT ON `medical_reports` FOR EACH ROW BEGIN
                    DECLARE current_month DATE;
                    SET current_month = DATE_FORMAT(NEW.report_date, '%Y-%m-01');
                    
                    INSERT INTO report_statistics 
                    (total_reports, brain_scans, lab_results, completed_reports, month_year)
                    VALUES (1, 
                            IF(NEW.report_type = 'scan', 1, 0),
                            IF(NEW.report_type = 'lab', 1, 0),
                            IF(NEW.status = 'completed', 1, 0),
                            current_month)
                    ON DUPLICATE KEY UPDATE
                        total_reports = total_reports + 1,
                        brain_scans = brain_scans + IF(NEW.report_type = 'scan', 1, 0),
                        lab_results = lab_results + IF(NEW.report_type = 'lab', 1, 0),
                        completed_reports = completed_reports + IF(NEW.status = 'completed', 1, 0);
                END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `medications`
--

CREATE TABLE `medications` (
  `id` int(11) NOT NULL,
  `patient_id` int(11) DEFAULT NULL,
  `medication_name` varchar(100) DEFAULT NULL,
  `dosage` varchar(50) DEFAULT NULL,
  `intake_date` date DEFAULT NULL,
  `intake_time` time DEFAULT NULL,
  `status` enum('Pending','Taken','Missed') DEFAULT 'Pending',
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `medications`
--

INSERT INTO `medications` (`id`, `patient_id`, `medication_name`, `dosage`, `intake_date`, `intake_time`, `status`, `notes`) VALUES
(12, 2, 'Ibuprofen', '200mg', '2025-05-15', '13:00:00', 'Missed', 'Avoid if stomach pain'),
(13, 3, 'Lisinopril', '10mg', '2025-05-15', '09:00:00', 'Taken', 'Daily for blood pressure'),
(14, 4, 'Vitamin D', '1000 IU', '2025-05-15', '11:00:00', 'Taken', 'Every morning'),
(16, 2, 'Cetirizine', '10mg', '2025-05-15', '21:00:00', 'Pending', 'For allergies'),
(18, 3, 'Simvastatin', '20mg', '2025-05-15', '22:00:00', 'Pending', 'Before bed'),
(19, 4, 'Omeprazole', '40mg', '2025-05-15', '07:30:00', 'Taken', 'Before eating');

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `sender_id` int(11) NOT NULL,
  `sender_type` enum('patient','doctor','secretary') NOT NULL,
  `recipient_id` int(11) NOT NULL,
  `recipient_type` enum('patient','doctor','secretary') NOT NULL,
  `status` enum('unread','read') NOT NULL DEFAULT 'unread',
  `priority` enum('low','medium','high') NOT NULL DEFAULT 'medium',
  `category` varchar(50) DEFAULT NULL,
  `attachments` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`id`, `subject`, `message`, `sender_id`, `sender_type`, `recipient_id`, `recipient_type`, `status`, `priority`, `category`, `attachments`, `created_by`, `created_at`, `updated_at`) VALUES
(7, 'zzzzzzz', 'zz', 1, 'secretary', 2, 'patient', 'read', 'medium', NULL, NULL, 1, '2025-05-21 15:32:02', '2025-05-25 17:50:29'),
(8, 'lolo', 'cc', 0, 'secretary', 1, 'doctor', 'read', 'medium', NULL, NULL, 1, '2025-05-21 16:47:39', '2025-05-25 17:50:29'),
(9, 'lolo', 'cc', 0, 'secretary', 2, 'doctor', 'read', 'medium', NULL, NULL, 1, '2025-05-21 16:47:39', '2025-05-25 17:50:29'),
(10, 'lolo', 'cc', 0, 'secretary', 3, 'doctor', 'read', 'medium', NULL, NULL, 1, '2025-05-21 16:47:39', '2025-05-25 17:50:29'),
(11, 'lolo', 'cc', 0, 'secretary', 4, 'doctor', 'unread', 'medium', NULL, NULL, 1, '2025-05-21 16:47:39', '2025-05-25 17:50:29'),
(12, 'lolo', 'cc', 0, 'secretary', 5, 'doctor', 'unread', 'medium', NULL, NULL, 1, '2025-05-21 16:47:39', '2025-05-25 17:50:29'),
(13, 'lolo', 'cc', 1, 'doctor', 1, 'doctor', 'unread', 'medium', NULL, NULL, 1, '2025-05-21 16:47:39', '2025-05-25 17:50:29'),
(14, 'lolo', 'cc', 0, 'secretary', 7, 'doctor', 'unread', 'medium', NULL, NULL, 1, '2025-05-21 16:47:39', '2025-05-25 17:50:29'),
(15, 'lolo', 'cc', 0, 'secretary', 8, 'doctor', 'unread', 'medium', NULL, NULL, 1, '2025-05-21 16:47:39', '2025-05-25 17:50:29'),
(16, 'lolo', 'cc', 0, 'secretary', 9, 'doctor', 'unread', 'medium', NULL, NULL, 1, '2025-05-21 16:47:39', '2025-05-25 17:50:29'),
(17, 'lolo', 'cc', 0, 'secretary', 22, 'doctor', 'read', 'medium', NULL, NULL, 1, '2025-05-21 16:47:39', '2025-05-29 09:53:26'),
(18, 'lolo', 'vvv', 1, 'doctor', 1, 'doctor', 'unread', 'medium', NULL, NULL, 1, '2025-05-21 23:25:28', '2025-05-25 17:50:29'),
(19, 'Emission de la gregation ', 'cbcb', 1, 'doctor', 6, 'patient', 'unread', 'medium', NULL, NULL, 1, '2025-05-22 00:18:54', '2025-05-25 17:50:29'),
(20, 'lolo', 'piw piw piw pwi', 1, 'doctor', 1, 'patient', 'read', 'high', NULL, NULL, 1, '2025-05-23 23:44:09', '2025-05-25 17:50:29'),
(21, 'jfdfj', 'lolofdfd', 1, 'secretary', 4, 'patient', 'read', 'medium', NULL, NULL, 1, '2025-05-23 23:45:08', '2025-05-25 17:50:29'),
(22, 'ffjj', 'doododod', 1, 'secretary', 1, 'doctor', 'read', 'medium', NULL, NULL, 1, '2025-05-23 23:45:26', '2025-05-25 17:50:29'),
(23, 'vv', 'fdf', 1, 'doctor', 7, 'patient', 'unread', 'medium', NULL, NULL, 1, '2025-05-24 00:33:42', '2025-05-25 17:50:29'),
(24, 'vd', 'dfcd', 0, 'secretary', 1, 'doctor', 'unread', 'medium', NULL, NULL, 1, '2025-05-24 03:33:44', '2025-05-25 17:50:29'),
(25, 'vd', 'dfcd', 0, 'secretary', 2, 'doctor', 'unread', 'medium', NULL, NULL, 1, '2025-05-24 03:33:44', '2025-05-25 17:50:29'),
(26, 'vd', 'dfcd', 0, 'secretary', 3, 'doctor', 'unread', 'medium', NULL, NULL, 1, '2025-05-24 03:33:44', '2025-05-25 17:50:29'),
(27, 'vd', 'dfcd', 0, 'secretary', 4, 'doctor', 'unread', 'medium', NULL, NULL, 1, '2025-05-24 03:33:44', '2025-05-25 17:50:29'),
(28, 'vd', 'dfcd', 0, 'secretary', 5, 'doctor', 'unread', 'medium', NULL, NULL, 1, '2025-05-24 03:33:44', '2025-05-25 17:50:29'),
(29, 'vd', 'dfcd', 0, 'secretary', 6, 'doctor', 'unread', 'medium', NULL, NULL, 1, '2025-05-24 03:33:44', '2025-05-25 17:50:29'),
(30, 'vd', 'dfcd', 0, 'secretary', 7, 'doctor', 'unread', 'medium', NULL, NULL, 1, '2025-05-24 03:33:44', '2025-05-25 17:50:29'),
(31, 'vd', 'dfcd', 0, 'secretary', 8, 'doctor', 'unread', 'medium', NULL, NULL, 1, '2025-05-24 03:33:44', '2025-05-25 17:50:29'),
(32, 'vd', 'dfcd', 0, 'secretary', 9, 'doctor', 'unread', 'medium', NULL, NULL, 1, '2025-05-24 03:33:44', '2025-05-25 17:50:29'),
(33, 'vd', 'dfcd', 0, 'secretary', 22, 'doctor', 'unread', 'medium', NULL, NULL, 1, '2025-05-24 03:33:44', '2025-05-29 09:53:29'),
(34, 'vd', 'dfcd', 0, 'secretary', 6, 'patient', 'unread', 'medium', NULL, NULL, 1, '2025-05-24 03:33:44', '2025-05-25 17:50:29'),
(35, 'vd', 'dfcd', 0, 'secretary', 7, 'patient', 'unread', 'medium', NULL, NULL, 1, '2025-05-24 03:33:44', '2025-05-25 17:50:29'),
(36, 'vd', 'dfcd', 0, 'secretary', 4, 'patient', 'unread', 'medium', NULL, NULL, 1, '2025-05-24 03:33:44', '2025-05-25 17:50:29'),
(37, 'vd', 'dfcd', 0, 'secretary', 1, 'patient', 'unread', 'medium', NULL, NULL, 1, '2025-05-24 03:33:44', '2025-05-25 17:50:29'),
(38, 'vd', 'dfcd', 0, 'secretary', 2, 'patient', 'unread', 'medium', NULL, NULL, 1, '2025-05-24 03:33:44', '2025-05-25 17:50:29'),
(39, 'vd', 'dfcd', 0, 'secretary', 3, 'patient', 'unread', 'medium', NULL, NULL, 1, '2025-05-24 03:33:44', '2025-05-25 17:50:29'),
(201, 'Confirmation de rendez-vous', 'Bonjour M. El Fassi, votre rendez-vous du 01/06 est confirmé. Merci de vous présenter 15 minutes avant.', 23, 'secretary', 24, 'patient', 'unread', 'medium', 'Rendez-vous', NULL, 23, '2025-05-26 10:35:00', '2025-05-30 15:34:23'),
(211, 'Question sur prescription', 'Dr. Benslimane, puis-je prendre le Lisinopril avec mon nouveau médicament pour le cholestérol?', 20, 'patient', 22, 'doctor', 'unread', 'high', 'Médicaments', NULL, 20, '2025-05-26 10:40:00', '2025-05-29 09:53:17'),
(221, 'Résultats d\'analyses', 'Mme Benjelloun, vos résultats sont disponibles dans votre espace patient. Cordialement, la secrétaire.', 23, 'secretary', 21, 'patient', 'read', 'medium', 'Résultats', NULL, 23, '2025-05-26 10:45:00', '2025-05-27 09:12:06');

-- --------------------------------------------------------

--
-- Table structure for table `messages_backup`
--

CREATE TABLE `messages_backup` (
  `id` int(11) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `sender_id` int(11) DEFAULT NULL,
  `sender_type` enum('patient','doctor','secretary','admin') NOT NULL,
  `recipient_id` int(11) DEFAULT NULL,
  `recipient_type` enum('patient','doctor','secretary','admin') NOT NULL,
  `status` enum('unread','read','archived') NOT NULL DEFAULT 'unread',
  `priority` enum('low','medium','high') NOT NULL DEFAULT 'medium',
  `category` varchar(50) DEFAULT NULL,
  `attachments` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Stand-in structure for view `message_details`
-- (See below for the actual view)
--
CREATE TABLE `message_details` (
`id` int(11)
,`subject` varchar(255)
,`message` text
,`sender_id` int(11)
,`sender_type` enum('patient','doctor','secretary')
,`recipient_id` int(11)
,`recipient_type` enum('patient','doctor','secretary')
,`status` enum('unread','read')
,`priority` enum('low','medium','high')
,`category` varchar(50)
,`attachments` text
,`created_at` timestamp
,`updated_at` timestamp
,`sender_name` varchar(255)
,`recipient_name` varchar(255)
);

-- --------------------------------------------------------

--
-- Table structure for table `patient`
--

CREATE TABLE `patient` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `medical_history` text DEFAULT NULL,
  `allergies` text DEFAULT NULL,
  `blood_type` varchar(5) DEFAULT NULL,
  `emergency_contact` varchar(255) DEFAULT NULL,
  `insurance_info` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `patient`
--

INSERT INTO `patient` (`id`, `user_id`, `medical_history`, `allergies`, `blood_type`, `emergency_contact`, `insurance_info`, `created_at`) VALUES
(1, 2, NULL, NULL, NULL, NULL, NULL, '2025-05-31 13:45:29');

-- --------------------------------------------------------

--
-- Table structure for table `patients`
--

CREATE TABLE `patients` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `date_of_birth` date NOT NULL,
  `gender` enum('male','female','other') NOT NULL,
  `national_id` varchar(50) DEFAULT NULL,
  `blood_type` varchar(5) DEFAULT NULL,
  `medical_conditions` text DEFAULT NULL,
  `allergies` text DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `address` text DEFAULT NULL,
  `emergency_contact` varchar(255) DEFAULT NULL,
  `emergency_phone` varchar(20) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `profile_photo` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `last_password_change` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `patients`
--

INSERT INTO `patients` (`id`, `user_id`, `full_name`, `date_of_birth`, `gender`, `national_id`, `blood_type`, `medical_conditions`, `allergies`, `email`, `phone`, `address`, `emergency_contact`, `emergency_phone`, `created_by`, `password`, `profile_photo`, `created_at`, `updated_at`, `last_password_change`) VALUES
(1, 24, 'Rachid El Malkiccc', '1975-06-10', 'male', 'RM19750610', 'O+', 'high', 'Pollen', 'rachid.malki@gmail.com', '+212612345678', 'Rabat, Morocco', 'Fatima El Malki', '+212611112233', 1, '8d969eef6ecad3c29a3a629280e686cf0c3f5d5a86aff3ca12020c923adc6c92', '../uploads/profiles/profile11121.png', '2025-05-21 17:14:02', '2025-05-29 11:28:58', '2025-05-21'),
(2, 0, 'Sara El Amrani', '1998-09-21', 'male', 'SA987654', 'A-', 'high', '', 'sara.amrani@example.com', '+212600987654', 'Not specified', NULL, NULL, 1, 'e05f79651d465214e7558a382ed0f0e5a77380a649f4573f3a1036dc4ee10c0b', '../uploads/profiles/profile11121.png', '2025-05-15 20:38:11', '2025-06-02 20:48:27', '2025-05-20'),
(3, 0, 'Youssef Mansouri', '1985-03-30', 'other', 'YM345678', 'B+', '', '', 'y.mansouri@example.com', '+212611223344', 'Not specified', NULL, NULL, 1, '5312f12a54966726410450a6f72430f338be559f4e8ec32745a911ec28c29e74', '../uploads/profiles/profile11121.png', '2025-05-15 20:38:11', '2025-06-02 20:48:24', '2025-05-20'),
(4, 0, 'Meryem Idrissi', '1990-11-12', 'other', 'MI112233', 'AB+', NULL, NULL, 'meryem.idrissi@example.com', '+212677889900', 'Not specified', NULL, NULL, 1, 'de66699dc77e327a428c7cad8adc14e60bfbb0bedaae003ddbdaccf8303b8de4', '../uploads/profiles/profile11121.png', '2025-05-15 20:38:11', '2025-06-02 20:48:22', '2025-05-15'),
(6, 0, 'dcc', '2001-11-11', 'male', 'BA120002', 'A+', '', '', 'ana@gmial.com', '1212112121', 'vdvd', NULL, NULL, 1, '', 'uploads/patients/patient_6_1747832263.png', '2025-05-21 12:31:34', '2025-05-25 17:50:29', '2025-05-21'),
(7, 0, 'anweeer', '2001-11-11', 'male', 'ba33', 'A+', '', '', 'anxa@gmial.com', '06797979791', 'aenf', NULL, NULL, 1, '', 'uploads/patients/patient_682dc851d336f.png', '2025-05-21 12:34:25', '2025-05-25 17:50:29', '2025-05-21'),
(20, 0, 'Karim El Fassi', '1980-05-15', 'male', 'K123456789', 'A+', 'Hypertension artérielle, Diabète de type 2', 'Pénicilline', 'karim.elfassi@gmail.com', '+212612345678', '45 Avenue Hassan II, Rabat', 'Amina El Fassi', '+212678901234', 20, '8d969eef6ecad3c29a3a629280e686cf0c3f5d5a86aff3ca12020c923adc6c92', 'patient_20.jpg', '2025-05-26 09:20:00', '2025-05-26 09:20:00', '2025-05-26'),
(21, 0, 'Leila Benjelloun', '1992-11-22', 'female', 'L987654321', 'O-', 'Migraines chroniques', 'Aucune', 'leila.benjelloun@hotmail.com', '+212678901234', 'Rue des Orangers, Marrakech', 'Youssef Benjelloun', '+212612345678', 20, '8d969eef6ecad3c29a3a629280e686cf0c3f5d5a86aff3ca12020c923adc6c92', 'patient_21.jpg', '2025-05-26 09:25:00', '2025-05-26 09:25:00', '2025-05-26'),
(22, 0, 'Omar Cherkaoui', '1975-03-08', 'male', 'O456789123', 'B+', 'Problèmes cardiaques', 'Iode', 'omar.cherkaouhhi@gmail.com', '', 'Avenue Mohammed VI, Tanger', 'Fatima Cherkaoui', '+212644556677', 20, '8d969eef6ecad3c29a3a629280e686cf0c3f5d5a86aff3ca12020c923adc6c92', 'patient_22.jpg', '2025-05-26 09:30:00', '2025-05-26 13:00:30', '2025-05-26'),
(23, 0, 'fwef', '2009-11-11', 'male', NULL, 'A+', NULL, '', 'sjg@gma.c', '31212', '3dvd', '', '', NULL, '', NULL, '2025-05-30 21:54:09', '2025-05-30 21:54:09', NULL),
(24, 0, 'g', '2009-11-11', 'male', NULL, 'A+', NULL, '', 'r120051012@taalim.ma', '3213212', 'GRGR', '', '', NULL, '', '', '2025-05-30 22:06:33', '2025-05-30 22:06:33', NULL),
(25, 0, '3E3', '2009-11-11', 'male', NULL, 'A+', NULL, '', 'E23@GMAIL.COM', '11121', 'DFKS', '', '', NULL, '', '', '2025-05-30 22:08:14', '2025-05-30 22:08:14', NULL),
(26, 0, 'GJ', '2009-11-11', 'male', NULL, 'A+', NULL, '', '32@F.C', '23332', 'DJS', '', '', NULL, '', '', '2025-05-30 22:11:30', '2025-05-30 22:11:30', NULL),
(27, 0, 'RGR', '2009-11-11', 'male', NULL, 'A+', NULL, '', 'FJ22@GMIAL.CO', '33232', 'GDJSG', '', '', NULL, '', '', '2025-05-30 22:12:59', '2025-05-30 22:12:59', NULL),
(28, 3, 'Anweeer1231', '2009-11-11', 'male', '212121', 'A+', NULL, NULL, 'hgesf@gmail.con', '067944594', NULL, NULL, NULL, NULL, '123', 'uploads/profiles/profile_3_1748896482.png', '2025-05-31 13:49:11', '2025-06-02 20:34:42', NULL),
(29, 4, 'Aneeer2134', '2009-11-11', 'other', '122121', 'A+', NULL, NULL, 'Anweer@fial.com', '067939332', NULL, NULL, NULL, NULL, '12345', 'uploads/profiles/profile_4_1749940667.png', '2025-06-02 20:43:32', '2025-06-14 22:37:47', '2025-06-02');

-- --------------------------------------------------------

--
-- Table structure for table `patients_backup`
--

CREATE TABLE `patients_backup` (
  `id` int(11) NOT NULL,
  `full_name` varchar(100) DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `gender` enum('Male','Female','Other') DEFAULT NULL,
  `national_id` varchar(50) DEFAULT NULL,
  `blood_type` varchar(3) DEFAULT NULL,
  `medical_conditions` text DEFAULT NULL,
  `allergies` text DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `profile_photo` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_password_change` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `patients_backup`
--

INSERT INTO `patients_backup` (`id`, `full_name`, `date_of_birth`, `gender`, `national_id`, `blood_type`, `medical_conditions`, `allergies`, `email`, `phone`, `address`, `password`, `profile_photo`, `created_at`, `last_password_change`) VALUES
(2, 'Sara El Amrani', '1998-09-21', 'Male', 'SA987654', 'A-', '', '', 'sara.amrani@example.com', '+212600987654', 'Not specified', 'e05f79651d465214e7558a382ed0f0e5a77380a649f4573f3a1036dc4ee10c0b', 'uploads/patients/patient_2_1747708524.jpg', '2025-05-15 20:38:11', '2025-05-20'),
(3, 'Youssef Mansouri', '1985-03-30', 'Other', 'YM345678', 'B+', '', '', 'y.mansouri@example.com', '+212611223344', 'Not specified', '5312f12a54966726410450a6f72430f338be559f4e8ec32745a911ec28c29e74', 'photo', '2025-05-15 20:38:11', '2025-05-20'),
(4, 'Meryem Idrissi', '1990-11-12', 'Other', 'MI112233', 'AB+', NULL, NULL, 'meryem.idrissi@example.com', '+212677889900', 'Not specified', 'de66699dc77e327a428c7cad8adc14e60bfbb0bedaae003ddbdaccf8303b8de4', 'meryem.jpg', '2025-05-15 20:38:11', '2025-05-15'),
(6, 'dcc', '2001-11-11', 'Male', 'BA120002', 'A+', '', '', 'ana@gmial.com', '1212112121', 'vdvd', '', 'uploads/patients/patient_6_1747832263.png', '2025-05-21 12:31:34', '2025-05-21'),
(7, 'anweeer', '2001-11-11', 'Male', 'ba33', 'A+', '', '', 'anxa@gmial.com', '06797979791', 'aenf', '', 'uploads/patients/patient_682dc851d336f.png', '2025-05-21 12:34:25', '2025-05-21');

-- --------------------------------------------------------

--
-- Table structure for table `patient_notes`
--

CREATE TABLE `patient_notes` (
  `id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `doctor_id` int(11) NOT NULL,
  `note_type` enum('consultation','observation','follow_up','prescription') NOT NULL,
  `content` text NOT NULL,
  `priority` enum('low','medium','high') NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `patient_notes`
--

INSERT INTO `patient_notes` (`id`, `patient_id`, `doctor_id`, `note_type`, `content`, `priority`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'observation', 'vvvvv', 'low', '2025-05-22 00:24:12', '2025-05-22 00:24:12'),
(20, 20, 20, 'consultation', 'Patient présente une tension artérielle bien contrôlée avec le traitement actuel. A continuer.', 'medium', '2025-05-26 12:00:00', '2025-05-26 12:00:00'),
(21, 21, 21, 'observation', 'Migraines fréquentes mais sans signes d\'alerte. Essayer le traitement prescrit pendant 1 mois.', 'high', '2025-05-26 12:05:00', '2025-05-26 12:05:00');

-- --------------------------------------------------------

--
-- Table structure for table `quick_notes`
--

CREATE TABLE `quick_notes` (
  `id` int(11) NOT NULL,
  `content` text NOT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `quick_notes`
--

INSERT INTO `quick_notes` (`id`, `content`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'lsls', 1, '2025-05-21 15:34:42', '2025-05-21 15:34:42');

-- --------------------------------------------------------

--
-- Stand-in structure for view `recent_reports_view`
-- (See below for the actual view)
--
CREATE TABLE `recent_reports_view` (
`id` int(11)
,`patient_id` int(11)
,`doctor_id` int(11)
,`report_type` enum('scan','lab','diagnosis','treatment')
,`report_date` date
,`findings` text
,`status` enum('pending','completed')
,`created_at` timestamp
,`updated_at` timestamp
,`patient_name` varchar(255)
,`doctor_name` varchar(255)
,`report_type_display` varchar(14)
);

-- --------------------------------------------------------

--
-- Table structure for table `reminders_log`
--

CREATE TABLE `reminders_log` (
  `id` int(11) NOT NULL,
  `appointment_id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `sent_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `reminder_type` enum('email','sms') DEFAULT 'email',
  `status` enum('sent','failed') DEFAULT 'sent',
  `error_message` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `report_attachments`
--

CREATE TABLE `report_attachments` (
  `id` int(11) NOT NULL,
  `report_id` int(11) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `file_type` varchar(50) NOT NULL,
  `file_size` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `report_attachments`
--

INSERT INTO `report_attachments` (`id`, `report_id`, `file_name`, `file_path`, `file_type`, `file_size`, `created_at`) VALUES
(1, 4, 'test_pdf.pdf', 'uploads/reports/683091da72702_test_pdf.pdf', 'application/pdf', 3001, '2025-05-23 15:18:50'),
(20, 20, 'IRM_Karim_El_Fassi.pdf', 'uploads/reports/IRM_Karim_El_Fassi_20.pdf', 'application/pdf', 2500, '2025-05-26 10:00:00'),
(21, 21, 'Analyses_Leila_Benjelloun.pdf', 'uploads/reports/Analyses_Leila_Benjelloun_21.pdf', 'application/pdf', 1800, '2025-05-26 10:05:00'),
(22, 23, 'Tableau_Produits_Final.pdf', '../uploads/medical_records/6836ec62ecd29_Tableau_Produits_Final.pdf', 'application/pdf', 2007, '2025-05-28 10:58:42'),
(23, 24, 'Tableau_Produits_Ameliore.pdf', '../uploads/medical_records/683e0e5a26c29_Tableau_Produits_Ameliore.pdf', 'application/pdf', 2010, '2025-06-02 20:49:30');

-- --------------------------------------------------------

--
-- Table structure for table `report_statistics`
--

CREATE TABLE `report_statistics` (
  `id` int(11) NOT NULL,
  `total_reports` int(11) NOT NULL DEFAULT 0,
  `brain_scans` int(11) NOT NULL DEFAULT 0,
  `lab_results` int(11) NOT NULL DEFAULT 0,
  `completed_reports` int(11) NOT NULL DEFAULT 0,
  `month_year` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `report_statistics`
--

INSERT INTO `report_statistics` (`id`, `total_reports`, `brain_scans`, `lab_results`, `completed_reports`, `month_year`, `created_at`, `updated_at`) VALUES
(1, 3, 1, 1, 2, '2024-02-01', '2025-05-23 15:05:13', '2025-05-23 15:05:13'),
(5, 4, 2, 1, 2, '2025-05-01', '2025-05-23 15:18:50', '2025-05-26 12:43:50'),
(9, 1, 0, 1, 0, '2001-11-01', '2025-05-28 10:58:42', '2025-05-28 10:58:42'),
(10, 1, 0, 1, 0, '2025-11-01', '2025-06-02 20:49:30', '2025-06-02 20:49:30');

-- --------------------------------------------------------

--
-- Table structure for table `secretaries`
--

CREATE TABLE `secretaries` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `full_name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `password` varchar(255) NOT NULL,
  `national_id` varchar(50) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `profile_photo` varchar(255) DEFAULT NULL,
  `role` enum('receptionist','administrative','medical_records') NOT NULL DEFAULT 'receptionist',
  `department` varchar(100) DEFAULT NULL,
  `working_hours` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`working_hours`)),
  `status` enum('active','inactive','on_leave') NOT NULL DEFAULT 'active',
  `last_login` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `last_password_change` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `secretaries`
--

INSERT INTO `secretaries` (`id`, `user_id`, `full_name`, `email`, `phone`, `password`, `national_id`, `address`, `profile_photo`, `role`, `department`, `working_hours`, `status`, `last_login`, `created_at`, `updated_at`, `last_password_change`) VALUES
(20, 3, 'Fatima Zahra El Amrani', 'secretary@cliniquemaroc.ma', '+212612345678', '8d969eef6ecad3c29a3a629280e686cf0c3f5d5a86aff3ca12020c923adc6c92', 'AB123456', '12 Rue Mohamed V, Casablanca', '../uploads/profiles/profile11121.png', 'receptionist', 'Réception', '{\"monday\": {\"start\": \"08:00\", \"end\": \"17:00\"}, \"tuesday\": {\"start\": \"08:00\", \"end\": \"17:00\"}, \"wednesday\": {\"start\": \"08:00\", \"end\": \"17:00\"}, \"thursday\": {\"start\": \"08:00\", \"end\": \"17:00\"}, \"friday\": {\"start\": \"08:00\", \"end\": \"12:00\"}}', 'active', NULL, '2025-05-26 09:15:00', '2025-06-15 00:57:37', '2025-05-26');

-- --------------------------------------------------------

--
-- Table structure for table `secretary_activities`
--

CREATE TABLE `secretary_activities` (
  `id` int(11) NOT NULL,
  `secretary_id` int(11) NOT NULL,
  `activity_type` enum('login','logout','appointment_created','appointment_modified','patient_registered','document_uploaded','message_sent') NOT NULL,
  `description` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `secretary_activities`
--

INSERT INTO `secretary_activities` (`id`, `secretary_id`, `activity_type`, `description`, `ip_address`, `created_at`) VALUES
(20, 20, 'appointment_created', 'Création de rendez-vous pour M. El Fassi', '192.168.1.100', '2025-05-26 10:35:00'),
(21, 20, 'message_sent', 'Envoi de message à M. El Fassi', '192.168.1.100', '2025-05-26 10:35:00');

-- --------------------------------------------------------

--
-- Table structure for table `secretary_assignments`
--

CREATE TABLE `secretary_assignments` (
  `id` int(11) NOT NULL,
  `secretary_id` int(11) NOT NULL,
  `doctor_id` int(11) NOT NULL,
  `assignment_type` enum('primary','backup') NOT NULL DEFAULT 'primary',
  `start_date` date NOT NULL,
  `end_date` date DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `secretary_assignments`
--

INSERT INTO `secretary_assignments` (`id`, `secretary_id`, `doctor_id`, `assignment_type`, `start_date`, `end_date`, `status`, `created_at`, `updated_at`) VALUES
(20, 20, 20, 'primary', '2025-05-26', NULL, 'active', '2025-05-26 10:50:00', '2025-05-26 10:50:00'),
(21, 20, 21, 'backup', '2025-05-26', NULL, 'active', '2025-05-26 10:50:00', '2025-05-26 10:50:00');

-- --------------------------------------------------------

--
-- Table structure for table `secretary_permissions`
--

CREATE TABLE `secretary_permissions` (
  `id` int(11) NOT NULL,
  `secretary_id` int(11) NOT NULL,
  `permission_name` varchar(50) NOT NULL,
  `permission_value` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `secretary_schedules`
--

CREATE TABLE `secretary_schedules` (
  `id` int(11) NOT NULL,
  `secretary_id` int(11) NOT NULL,
  `day_of_week` enum('monday','tuesday','wednesday','thursday','friday','saturday','sunday') NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `is_working_day` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `Profile` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `role` enum('admin','secretary','doctor','patient') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_login` timestamp NULL DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `full_name`, `Profile`, `email`, `role`, `created_at`, `last_login`, `status`) VALUES
(1, 'admin', '123', 'System Administrator', '', 'admin@brainsense.com', 'admin', '2025-05-25 16:48:10', '2025-06-02 21:37:42', 'active'),
(2, 'patient1', '123', 'user id slam', '', 'anef@gmila.co', 'patient', '2025-05-31 13:45:29', '2025-06-15 00:30:09', 'active'),
(3, 'cvqgjdf', '123', 'Anweeer1231', '../uploads/profiles/profile11121.png', 'hgesf@gmail.con', 'secretary', '2025-05-31 13:49:11', '2025-06-15 00:54:28', 'active'),
(4, 'Anweer', '12345', 'Aneeer2134', 'uploads/profiles/profile_4_1749940667.png', 'Anweer@fial.com', 'patient', '2025-06-02 20:43:32', '2025-06-14 22:31:22', 'active');

-- --------------------------------------------------------

--
-- Structure for view `message_details`
--
DROP TABLE IF EXISTS `message_details`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `message_details`  AS SELECT `m`.`id` AS `id`, `m`.`subject` AS `subject`, `m`.`message` AS `message`, `m`.`sender_id` AS `sender_id`, `m`.`sender_type` AS `sender_type`, `m`.`recipient_id` AS `recipient_id`, `m`.`recipient_type` AS `recipient_type`, `m`.`status` AS `status`, `m`.`priority` AS `priority`, `m`.`category` AS `category`, `m`.`attachments` AS `attachments`, `m`.`created_at` AS `created_at`, `m`.`updated_at` AS `updated_at`, CASE WHEN `m`.`sender_type` = 'patient' THEN `p`.`full_name` WHEN `m`.`sender_type` = 'doctor' THEN `d`.`name` WHEN `m`.`sender_type` = 'secretary' THEN 'Secretary' END AS `sender_name`, CASE WHEN `m`.`recipient_type` = 'patient' THEN `p2`.`full_name` WHEN `m`.`recipient_type` = 'doctor' THEN `d2`.`name` WHEN `m`.`recipient_type` = 'secretary' THEN 'Secretary' END AS `recipient_name` FROM ((((`messages` `m` left join `patients` `p` on(`m`.`sender_type` = 'patient' and `m`.`sender_id` = `p`.`id`)) left join `doctors` `d` on(`m`.`sender_type` = 'doctor' and `m`.`sender_id` = `d`.`id`)) left join `patients` `p2` on(`m`.`recipient_type` = 'patient' and `m`.`recipient_id` = `p2`.`id`)) left join `doctors` `d2` on(`m`.`recipient_type` = 'doctor' and `m`.`recipient_id` = `d2`.`id`)) ;

-- --------------------------------------------------------

--
-- Structure for view `recent_reports_view`
--
DROP TABLE IF EXISTS `recent_reports_view`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `recent_reports_view`  AS SELECT `mr`.`id` AS `id`, `mr`.`patient_id` AS `patient_id`, `mr`.`doctor_id` AS `doctor_id`, `mr`.`report_type` AS `report_type`, `mr`.`report_date` AS `report_date`, `mr`.`findings` AS `findings`, `mr`.`status` AS `status`, `mr`.`created_at` AS `created_at`, `mr`.`updated_at` AS `updated_at`, `p`.`full_name` AS `patient_name`, `d`.`name` AS `doctor_name`, CASE WHEN `mr`.`report_type` = 'scan' THEN 'Brain Scan' WHEN `mr`.`report_type` = 'lab' THEN 'Lab Results' WHEN `mr`.`report_type` = 'diagnosis' THEN 'Diagnosis' WHEN `mr`.`report_type` = 'treatment' THEN 'Treatment Plan' END AS `report_type_display` FROM ((`medical_reports` `mr` join `patients` `p` on(`mr`.`patient_id` = `p`.`id`)) join `doctors` `d` on(`mr`.`doctor_id` = `d`.`id`)) ORDER BY `mr`.`report_date` DESC ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activities`
--
ALTER TABLE `activities`
  ADD PRIMARY KEY (`id`),
  ADD KEY `patient_id` (`patient_id`);

--
-- Indexes for table `appointments`
--
ALTER TABLE `appointments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_appointments_patient` (`patient_id`),
  ADD KEY `idx_creator` (`created_by`);

--
-- Indexes for table `doctors`
--
ALTER TABLE `doctors`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `documents`
--
ALTER TABLE `documents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `patient_id` (`patient_id`),
  ADD KEY `idx_uploader` (`uploaded_by`);

--
-- Indexes for table `document_categories`
--
ALTER TABLE `document_categories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_creator` (`created_by`);

--
-- Indexes for table `medical_reports`
--
ALTER TABLE `medical_reports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `patient_id` (`patient_id`),
  ADD KEY `doctor_id` (`doctor_id`),
  ADD KEY `idx_report_date` (`report_date`),
  ADD KEY `idx_report_type` (`report_type`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_creator` (`created_by`);

--
-- Indexes for table `medications`
--
ALTER TABLE `medications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_medications_patient` (`patient_id`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_sender` (`sender_id`,`sender_type`),
  ADD KEY `idx_recipient` (`recipient_id`,`recipient_type`),
  ADD KEY `idx_creator` (`created_by`);

--
-- Indexes for table `messages_backup`
--
ALTER TABLE `messages_backup`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sender_id` (`sender_id`),
  ADD KEY `recipient_id` (`recipient_id`);

--
-- Indexes for table `patient`
--
ALTER TABLE `patient`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `patients`
--
ALTER TABLE `patients`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `full_name` (`full_name`),
  ADD KEY `idx_creator` (`created_by`);

--
-- Indexes for table `patients_backup`
--
ALTER TABLE `patients_backup`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `patient_notes`
--
ALTER TABLE `patient_notes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `patient_id` (`patient_id`),
  ADD KEY `doctor_id` (`doctor_id`);

--
-- Indexes for table `quick_notes`
--
ALTER TABLE `quick_notes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `reminders_log`
--
ALTER TABLE `reminders_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_reminders_appointment` (`appointment_id`),
  ADD KEY `fk_reminders_patient` (`patient_id`);

--
-- Indexes for table `report_attachments`
--
ALTER TABLE `report_attachments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `report_id` (`report_id`),
  ADD KEY `idx_file_type` (`file_type`);

--
-- Indexes for table `report_statistics`
--
ALTER TABLE `report_statistics`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `month_year` (`month_year`);

--
-- Indexes for table `secretaries`
--
ALTER TABLE `secretaries`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `national_id` (`national_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `secretary_activities`
--
ALTER TABLE `secretary_activities`
  ADD PRIMARY KEY (`id`),
  ADD KEY `secretary_id` (`secretary_id`);

--
-- Indexes for table `secretary_assignments`
--
ALTER TABLE `secretary_assignments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `secretary_id` (`secretary_id`),
  ADD KEY `doctor_id` (`doctor_id`);

--
-- Indexes for table `secretary_permissions`
--
ALTER TABLE `secretary_permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `secretary_permission` (`secretary_id`,`permission_name`);

--
-- Indexes for table `secretary_schedules`
--
ALTER TABLE `secretary_schedules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `secretary_id` (`secretary_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activities`
--
ALTER TABLE `activities`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `appointments`
--
ALTER TABLE `appointments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=127;

--
-- AUTO_INCREMENT for table `doctors`
--
ALTER TABLE `doctors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `documents`
--
ALTER TABLE `documents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `document_categories`
--
ALTER TABLE `document_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `medical_reports`
--
ALTER TABLE `medical_reports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `medications`
--
ALTER TABLE `medications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=222;

--
-- AUTO_INCREMENT for table `messages_backup`
--
ALTER TABLE `messages_backup`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `patient`
--
ALTER TABLE `patient`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `patients`
--
ALTER TABLE `patients`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `patients_backup`
--
ALTER TABLE `patients_backup`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `patient_notes`
--
ALTER TABLE `patient_notes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `quick_notes`
--
ALTER TABLE `quick_notes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `reminders_log`
--
ALTER TABLE `reminders_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `report_attachments`
--
ALTER TABLE `report_attachments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `report_statistics`
--
ALTER TABLE `report_statistics`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `secretaries`
--
ALTER TABLE `secretaries`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `secretary_activities`
--
ALTER TABLE `secretary_activities`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `secretary_assignments`
--
ALTER TABLE `secretary_assignments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `secretary_permissions`
--
ALTER TABLE `secretary_permissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `secretary_schedules`
--
ALTER TABLE `secretary_schedules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activities`
--
ALTER TABLE `activities`
  ADD CONSTRAINT `activities_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`);

--
-- Constraints for table `appointments`
--
ALTER TABLE `appointments`
  ADD CONSTRAINT `appointments_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `fk_appointments_patient` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `doctors`
--
ALTER TABLE `doctors`
  ADD CONSTRAINT `doctors_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `documents`
--
ALTER TABLE `documents`
  ADD CONSTRAINT `documents_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `document_categories` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `documents_ibfk_2` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `documents_ibfk_3` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `document_categories`
--
ALTER TABLE `document_categories`
  ADD CONSTRAINT `document_categories_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `medical_reports`
--
ALTER TABLE `medical_reports`
  ADD CONSTRAINT `fk_reports_doctor` FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_reports_patient` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `medical_reports_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `medications`
--
ALTER TABLE `medications`
  ADD CONSTRAINT `fk_medications_patient` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `patient`
--
ALTER TABLE `patient`
  ADD CONSTRAINT `patient_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `patients`
--
ALTER TABLE `patients`
  ADD CONSTRAINT `patients_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `patient_notes`
--
ALTER TABLE `patient_notes`
  ADD CONSTRAINT `patient_notes_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `patient_notes_ibfk_2` FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `reminders_log`
--
ALTER TABLE `reminders_log`
  ADD CONSTRAINT `fk_reminders_appointment` FOREIGN KEY (`appointment_id`) REFERENCES `appointments` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_reminders_patient` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `report_attachments`
--
ALTER TABLE `report_attachments`
  ADD CONSTRAINT `fk_attachments_report` FOREIGN KEY (`report_id`) REFERENCES `medical_reports` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `secretaries`
--
ALTER TABLE `secretaries`
  ADD CONSTRAINT `secretaries_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `secretary_activities`
--
ALTER TABLE `secretary_activities`
  ADD CONSTRAINT `fk_secretary_activities` FOREIGN KEY (`secretary_id`) REFERENCES `secretaries` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `secretary_assignments`
--
ALTER TABLE `secretary_assignments`
  ADD CONSTRAINT `fk_secretary_assignments_doctor` FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_secretary_assignments_secretary` FOREIGN KEY (`secretary_id`) REFERENCES `secretaries` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `secretary_permissions`
--
ALTER TABLE `secretary_permissions`
  ADD CONSTRAINT `fk_secretary_permissions` FOREIGN KEY (`secretary_id`) REFERENCES `secretaries` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `secretary_schedules`
--
ALTER TABLE `secretary_schedules`
  ADD CONSTRAINT `fk_secretary_schedules` FOREIGN KEY (`secretary_id`) REFERENCES `secretaries` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
