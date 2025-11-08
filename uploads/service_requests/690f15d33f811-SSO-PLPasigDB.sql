-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 08, 2025 at 02:26 AM
-- Server version: 11.8.3-MariaDB-log
-- PHP Version: 7.2.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `u337253893_PLPasigSSO`
--

-- --------------------------------------------------------

--
-- Table structure for table `acc_locking`
--

CREATE TABLE `acc_locking` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `attempt_left` int(11) DEFAULT 5,
  `last_attempt_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `acc_locking`
--

INSERT INTO `acc_locking` (`id`, `user_id`, `attempt_left`, `last_attempt_at`) VALUES
(1, 1, 5, '2025-10-17 16:10:01'),
(2, 2, 5, '2025-11-03 04:31:28'),
(3, 3, 5, '2025-11-07 20:34:12');

-- --------------------------------------------------------

--
-- Table structure for table `admission_cycles`
--

CREATE TABLE `admission_cycles` (
  `id` int(11) NOT NULL,
  `cycle_name` varchar(255) NOT NULL,
  `is_archived` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admission_cycles`
--

INSERT INTO `admission_cycles` (`id`, `cycle_name`, `is_archived`) VALUES
(1, '2026-2027 Admissions', 0),
(2, '2027-2028', 0),
(3, '2028-2029', 0),
(4, '2025-2026 Admission', 0);

-- --------------------------------------------------------

--
-- Table structure for table `admission_submission`
--

CREATE TABLE `admission_submission` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `can_apply` tinyint(1) DEFAULT 1,
  `can_update` tinyint(1) DEFAULT 1,
  `submitted_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admission_submission`
--

INSERT INTO `admission_submission` (`id`, `user_id`, `can_apply`, `can_update`, `submitted_at`, `updated_at`) VALUES
(2, 1, 1, 1, '2025-11-03 23:16:29', '2025-11-07 07:36:13');

-- --------------------------------------------------------

--
-- Table structure for table `api_list`
--

CREATE TABLE `api_list` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `api_url` varchar(1000) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `api_list`
--

INSERT INTO `api_list` (`id`, `name`, `api_url`, `description`, `created_at`, `updated_at`) VALUES
(1, 'UPDATE_GENERAL_IMAGES', 'https://gold-lion-549609.hostingersite.com/update-general-upload.php', 'handles updating existing image assets in the general image system. It replaces or refreshes stored images with new versions, ensuring metadata consistency, optimized file storage, and proper linkage across dependent modules.', '2025-10-29 22:21:49', '2025-10-29 22:47:26'),
(2, 'SSO_EMAIL_SEND', 'https://gold-lion-549609.hostingersite.com/email-api.php', 'represents an API action that handles emails.', '2025-10-29 22:21:49', '2025-10-30 21:41:50'),
(3, 'EXAM_PERMIT_GENERATOR', 'https://gold-lion-549609.hostingersite.com/exam-permit.php', 'generator of an exam permit', '2025-10-29 22:21:49', '2025-10-30 21:41:50'),
(4, 'UPLOAD_REQUIREMENTS_IMAGES', 'https://gold-lion-549609.hostingersite.com/upload.php', 'handles updating existing image assets in the requirements image system.', '2025-10-29 22:21:49', '2025-11-02 00:08:28'),
(5, 'UPDATE_REQUIREMENTS_API', 'https://gold-lion-549609.hostingersite.com/update-requirement.php', 'handles image assets in the requirements image system.', '2025-10-29 22:21:49', '2025-11-02 20:25:30'),
(6, 'UPLOAD_REQUIREMENTS_BASE_URL', 'https://gold-lion-549609.hostingersite.com/', 'handles image assets in the requirements image system.', '2025-10-29 22:21:49', '2025-11-02 20:24:08'),
(7, 'PREVIEW_REQUIREMENTS_URL', 'https://gold-lion-549609.hostingersite.com/preview-requirement.php', 'handles image assets in the requirements image system.', '2025-10-29 22:21:49', '2025-11-02 20:24:08');

-- --------------------------------------------------------

--
-- Table structure for table `applicant_number_prefix`
--

CREATE TABLE `applicant_number_prefix` (
  `id` int(11) NOT NULL,
  `prefix` varchar(255) NOT NULL,
  `date_added` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `applicant_number_prefix`
--

INSERT INTO `applicant_number_prefix` (`id`, `prefix`, `date_added`) VALUES
(1, 'PLPPasig', '2025-10-31 21:04:45'),
(2, 'Admission2025', '2025-10-31 21:04:45');

-- --------------------------------------------------------

--
-- Table structure for table `applicant_types`
--

CREATE TABLE `applicant_types` (
  `id` int(11) NOT NULL,
  `admission_cycle_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `is_archived` tinyint(1) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `applicant_types`
--

INSERT INTO `applicant_types` (`id`, `admission_cycle_id`, `name`, `is_archived`, `is_active`) VALUES
(1, 1, 'SHS Graduate', 0, 0),
(2, 1, 'ALS Graduate', 0, 0),
(3, 1, 'On Going SHS', 0, 0),
(4, 4, 'ALS Graduate', 0, 1),
(5, 4, 'SHS Graduate', 0, 1),
(6, 4, 'Transferee', 0, 1),
(7, 4, 'On-Going Grade 12', 0, 1),
(8, 4, 'Transferee', 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `application_permit`
--

CREATE TABLE `application_permit` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `applicant_number` varchar(250) NOT NULL,
  `status` enum('pending','used') NOT NULL DEFAULT 'pending',
  `date_sent` timestamp NOT NULL DEFAULT current_timestamp(),
  `admission_officer` varchar(128) DEFAULT NULL,
  `applicant_name` varchar(128) DEFAULT NULL,
  `exam_date` date DEFAULT NULL,
  `exam_time` time DEFAULT NULL,
  `room_no` varchar(64) DEFAULT NULL,
  `exam_venue` varchar(128) DEFAULT NULL,
  `application_period_start` date DEFAULT NULL,
  `application_period_end` date DEFAULT NULL,
  `application_period_text` varchar(128) DEFAULT NULL,
  `accent_color` char(7) NOT NULL DEFAULT '#18a558',
  `qr_text` varchar(255) DEFAULT NULL,
  `download_url` text DEFAULT NULL,
  `email_subject` varchar(255) DEFAULT NULL,
  `email_body` mediumtext DEFAULT NULL,
  `email_status` enum('queued','sent','failed') DEFAULT 'queued',
  `email_sent_at` datetime DEFAULT NULL,
  `used_at` datetime DEFAULT NULL,
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `contact_support`
--

CREATE TABLE `contact_support` (
  `id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `contact_support`
--

INSERT INTO `contact_support` (`id`, `name`, `email`, `subject`, `message`, `created_at`, `updated_at`) VALUES
(1, 'Romeo John Ador', 'adorromeojohn0105@gmail.com', 'test sub', 'test issue', '2025-11-02 18:26:18', '2025-11-02 18:26:18'),
(2, 'Romeo John Ador', 'adorromeojohn0105@gmail.com', 'test sub', 'test issue', '2025-11-02 18:27:43', '2025-11-02 18:27:43'),
(3, 'Romeo John Ador', 'adorromeojohn0105@gmail.com', 'test', 'test', '2025-11-02 18:28:00', '2025-11-02 18:28:00'),
(4, 'Romeo John Ador', 'adorromeojohn0105@gmail.com', 'test sub', 'test email', '2025-11-02 18:28:14', '2025-11-02 18:28:14');

-- --------------------------------------------------------

--
-- Table structure for table `email_template`
--

CREATE TABLE `email_template` (
  `id` int(50) NOT NULL,
  `title` varchar(250) NOT NULL,
  `subject` varchar(250) NOT NULL,
  `html_code` longtext NOT NULL,
  `date_added` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_active` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `email_template`
--

INSERT INTO `email_template` (`id`, `title`, `subject`, `html_code`, `date_added`, `is_active`) VALUES
(1, 'Account Registration', 'PLP - Student Success Office [Account Registration]', '<!DOCTYPE html>\n<html>\n\n<head>\n    <meta charset=\"UTF-8\">\n    <title>{{subject}}</title>\n    <link href=\"https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap\" rel=\"stylesheet\">\n    <style>\n        body {\n            font-family: \'Poppins\', Arial, sans-serif;\n            background-color: #f4f4f4;\n            padding: 20px;\n            margin: 0;\n        }\n\n        .email-container {\n            max-width: 600px;\n            margin: 0 auto;\n            background: #ffffff;\n            border-radius: 8px;\n            overflow: hidden;\n            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);\n        }\n\n        /* Header */\n        .header-table {\n            width: 100%;\n            background: #ffffff;\n            padding: 10px 15px;\n        }\n\n        .header-table img {\n            max-height: 70px;\n            vertical-align: middle;\n            margin: 0 5px;\n        }\n\n        .header-text {\n            text-align: center;\n            padding-top: 5px;\n            padding-bottom: 10px;\n        }\n\n        .header-text .school-name {\n            background: #0c326f;\n            color: white;\n            font-weight: 600;\n            font-size: 12px;\n            padding: 10px;\n            display: inline-block;\n            border-radius: 15px 0 0 15px;\n            margin-bottom: 4px;\n        }\n\n        .header-text .college-name {\n            font-size: 14px;\n            font-weight: 600;\n            color: #000;\n        }\n\n        .header-text .address {\n            font-size: 12px;\n            color: #333;\n        }\n\n        /* Content */\n        .content {\n            padding: 20px;\n            font-size: 14px;\n            line-height: 1.6;\n            color: #333;\n        }\n\n        .content p {\n            margin: 0 0 15px;\n        }\n\n        .button {\n            display: inline-block;\n            background: #004aad;\n            color: #fff !important;\n            padding: 10px 15px;\n            text-decoration: none;\n            border-radius: 4px;\n            margin-top: 10px;\n        }\n\n        .notice {\n            font-size: 13px;\n            color: #555;\n            background: #f8f8f8;\n            padding: 10px;\n            border-radius: 6px;\n            margin-top: 20px;\n            border-left: 4px solid #004aad;\n        }\n\n        .official-email {\n            text-align: center;\n            font-size: 14px;\n            color: #004aad;\n            font-weight: 600;\n            margin-top: 5px;\n        }\n\n        .footer {\n            font-size: 12px;\n            color: #888;\n            text-align: center;\n            padding: 15px;\n            background: #f9f9f9;\n        }\n\n        /* ✅ Responsive Header for Mobile */\n        @media only screen and (max-width: 480px) {\n            .header-table td {\n                display: block !important;\n                width: 100% !important;\n                text-align: center !important;\n            }\n\n            .header-table img {\n                display: inline-block !important;\n                max-height: 60px !important;\n                margin: 5px 3px !important;\n            }\n\n            .header-table td[align=\"right\"] {\n                text-align: center !important;\n                padding-top: 10px !important;\n            }\n        }\n    </style>\n</head>\n\n<body>\n    <div class=\"email-container\">\n        <!-- HEADER -->\n        <table class=\"header-table\" cellpadding=\"0\" cellspacing=\"0\" role=\"presentation\">\n            <tr>\n                <!-- LOGOS -->\n                <td align=\"left\" valign=\"middle\" style=\"white-space:nowrap;\">\n                    <img src=\"https://gcdnb.pbrd.co/images/Pni5FEz4UOEJ.png?o=1\" alt=\"PLP Logo\">\n                    <img src=\"https://gcdnb.pbrd.co/images/EFxDeFIopVQN.png?o=1\" alt=\"SSO Logo\">\n                </td>\n\n                <!-- TEXT -->\n                <td align=\"right\" valign=\"middle\" style=\"text-align:right;\">\n                    <div style=\"background:#0c326f; color:#fff; font-weight:600; font-size:12px; padding:5px 10px; display:inline-block; border-radius:15px 0 0 15px; margin-bottom:4px;\">\n                        PAMANTASAN NG LUNGSOD NG PASIG\n                    </div><br>\n                    <div style=\"font-size:14px; font-weight:600; color:#000;\">\n                        Student Success Office\n                    </div>\n                    <div style=\"font-size:12px; color:#333;\">\n                        Alkalde Jose St. Kapasigan Pasig City, Philippines 1600\n                    </div>\n                </td>\n            </tr>\n        </table>\n\n        <!-- EMAIL CONTENT -->\n        <div class=\"content\">\n            <p>{{greetings}},</p>\n            <p>Thank you for registering with the <strong>Pamantasan ng Lungsod ng Pasig - Student Success Office (SSO)</strong>. To complete your verification process, please click the button below:</p>\n\n            <p style=\"text-align: center; margin: 25px 0;\">\n                <a href=\"{{verification_link}}\"\n                    style=\"background-color: #004aad; color: #ffffff; text-decoration: none; padding: 12px 20px; border-radius: 5px; display: inline-block; font-weight: bold;\">\n                    Verify Account\n                </a>\n            </p>\n\n            <p>This link will remain valid until <strong>{{expire_at}}</strong>. If the link expires, you will need to request a new verification email through our system.</p>\n\n            <div class=\"notice\">\n                If you did not initiate this password reset request, you can safely ignore this email. For your security, please note that the <strong>only legitimate sender email address</strong> from our office is:\n                <div class=\"official-email\">plpasig.sso@gmail.com</div>\n                Any other email addresses claiming to represent the SSO should be considered unauthorized.\n            </div>\n\n            <p style=\"margin-top: 20px;\">Best regards,<br>\n                <strong>Pamantasan ng Lungsod ng Pasig<br>\n                    Student Success Office</strong>\n            </p>\n        </div>\n\n        <!-- FOOTER -->\n        <div class=\"footer\">\n            <p>Pamantasan ng Lungsod ng Pasig - Student Success Office</p>\n        </div>\n    </div>\n</body>\n\n</html>', '2025-10-03 10:29:28', 1),
(2, 'Login Account With OTP', 'PLP - Student Success Office [Login Account With OTP]', '<!DOCTYPE html>\n<html>\n\n<head>\n    <meta charset=\"UTF-8\">\n    <title>{{subject}}</title>\n    <link href=\"https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap\" rel=\"stylesheet\">\n    <style>\n        body {\n            font-family: \'Poppins\', Arial, sans-serif;\n            background-color: #f4f4f4;\n            padding: 20px;\n            margin: 0;\n        }\n\n        .email-container {\n            max-width: 600px;\n            margin: 0 auto;\n            background: #ffffff;\n            border-radius: 8px;\n            overflow: hidden;\n            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);\n        }\n\n        /* Header */\n        .header-table {\n            width: 100%;\n            background: #ffffff;\n            padding: 10px 15px;\n        }\n\n        .header-table img {\n            max-height: 70px;\n            vertical-align: middle;\n            margin: 0 5px;\n        }\n\n        .header-text {\n            text-align: center;\n            padding-top: 5px;\n            padding-bottom: 10px;\n        }\n\n        .header-text .school-name {\n            background: #0c326f;\n            color: white;\n            font-weight: 600;\n            font-size: 12px;\n            padding: 10px;\n            display: inline-block;\n            border-radius: 15px 0 0 15px;\n            margin-bottom: 4px;\n        }\n\n        .header-text .college-name {\n            font-size: 14px;\n            font-weight: 600;\n            color: #000;\n        }\n\n        .header-text .address {\n            font-size: 12px;\n            color: #333;\n        }\n\n        /* Content */\n        .content {\n            padding: 20px;\n            font-size: 14px;\n            line-height: 1.6;\n            color: #333;\n        }\n\n        .content p {\n            margin: 0 0 15px;\n        }\n\n        .button {\n            display: inline-block;\n            background: #004aad;\n            color: #fff !important;\n            padding: 10px 15px;\n            text-decoration: none;\n            border-radius: 4px;\n            margin-top: 10px;\n        }\n\n        .notice {\n            font-size: 13px;\n            color: #555;\n            background: #f8f8f8;\n            padding: 10px;\n            border-radius: 6px;\n            margin-top: 20px;\n            border-left: 4px solid #004aad;\n        }\n\n        .official-email {\n            text-align: center;\n            font-size: 14px;\n            color: #004aad;\n            font-weight: 600;\n            margin-top: 5px;\n        }\n\n        .footer {\n            font-size: 12px;\n            color: #888;\n            text-align: center;\n            padding: 15px;\n            background: #f9f9f9;\n        }\n\n        /* ✅ Responsive Header for Mobile */\n        @media only screen and (max-width: 480px) {\n            .header-table td {\n                display: block !important;\n                width: 100% !important;\n                text-align: center !important;\n            }\n\n            .header-table img {\n                display: inline-block !important;\n                max-height: 60px !important;\n                margin: 5px 3px !important;\n            }\n\n            .header-table td[align=\"right\"] {\n                text-align: center !important;\n                padding-top: 10px !important;\n            }\n        }\n    </style>\n</head>\n\n<body>\n    <div class=\"email-container\">\n        <!-- HEADER -->\n        <table class=\"header-table\" cellpadding=\"0\" cellspacing=\"0\" role=\"presentation\">\n            <tr>\n                <!-- LOGOS -->\n                <td align=\"left\" valign=\"middle\" style=\"white-space:nowrap;\">\n                    <img src=\"https://gcdnb.pbrd.co/images/Pni5FEz4UOEJ.png?o=1\" alt=\"PLP Logo\">\n                    <img src=\"https://gcdnb.pbrd.co/images/EFxDeFIopVQN.png?o=1\" alt=\"SSO Logo\">\n                </td>\n\n                <!-- TEXT -->\n                <td align=\"right\" valign=\"middle\" style=\"text-align:right;\">\n                    <div style=\"background:#0c326f; color:#fff; font-weight:600; font-size:12px; padding:5px 10px; display:inline-block; border-radius:15px 0 0 15px; margin-bottom:4px;\">\n                        PAMANTASAN NG LUNGSOD NG PASIG\n                    </div><br>\n                    <div style=\"font-size:14px; font-weight:600; color:#000;\">\n                        Student Success Office\n                    </div>\n                    <div style=\"font-size:12px; color:#333;\">\n                        Alkalde Jose St. Kapasigan Pasig City, Philippines 1600\n                    </div>\n                </td>\n            </tr>\n        </table>\n\n        <!-- EMAIL CONTENT -->\n        <div class=\"content\">\n            <p>{{greetings}},</p>\n            <p>Thank you for logging in to the <strong>Pamantasan ng Lungsod ng Pasig - Student Success Office (SSO)</strong>. To continue, please use the One-Time Password (OTP) provided below:</p>\n\n            <p style=\"text-align: center; margin: 25px 0;\">\n                <a href=\"\"\n                    style=\"background-color: #004aad; color: #ffffff; text-decoration: none; padding: 12px 20px; border-radius: 5px; display: inline-block; font-weight: bold;\">\n                    {{otp_code}}\n                </a>\n            </p>\n\n            <p>This code will remain valid until <strong>{{expire_at}}</strong>. If the link expires, you will need to request a new verification email through our system.</p>\n\n            <div class=\"notice\">\n                If you did not initiate this password reset request, you can safely ignore this email. For your security, please note that the <strong>only legitimate sender email address</strong> from our office is:\n                <div class=\"official-email\">plpasig.sso@gmail.com</div>\n                Any other email addresses claiming to represent the SSO should be considered unauthorized.\n            </div>\n\n            <p style=\"margin-top: 20px;\">Best regards,<br>\n                <strong>Pamantasan ng Lungsod ng Pasig<br>\n                    Student Success Office</strong>\n            </p>\n        </div>\n\n        <!-- FOOTER -->\n        <div class=\"footer\">\n            <p>Pamantasan ng Lungsod ng Pasig - Student Success Office</p>\n        </div>\n    </div>\n</body>\n\n</html>', '2025-10-06 19:52:52', 1),
(3, 'Reset Password With Link', 'PLP - Student Success Office [Reset Password]', '<!DOCTYPE html>\n<html>\n\n<head>\n    <meta charset=\"UTF-8\">\n    <title>{{subject}}</title>\n    <link href=\"https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap\" rel=\"stylesheet\">\n    <style>\n        body {\n            font-family: \'Poppins\', Arial, sans-serif;\n            background-color: #f4f4f4;\n            padding: 20px;\n            margin: 0;\n        }\n\n        .email-container {\n            max-width: 600px;\n            margin: 0 auto;\n            background: #ffffff;\n            border-radius: 8px;\n            overflow: hidden;\n            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);\n        }\n\n        /* Header */\n        .header-table {\n            width: 100%;\n            background: #ffffff;\n            padding: 10px 15px;\n        }\n\n        .header-table img {\n            max-height: 70px;\n            vertical-align: middle;\n            margin: 0 5px;\n        }\n\n        .header-text {\n            text-align: center;\n            padding-top: 5px;\n            padding-bottom: 10px;\n        }\n\n        .header-text .school-name {\n            background: #0c326f;\n            color: white;\n            font-weight: 600;\n            font-size: 12px;\n            padding: 10px;\n            display: inline-block;\n            border-radius: 15px 0 0 15px;\n            margin-bottom: 4px;\n        }\n\n        .header-text .college-name {\n            font-size: 14px;\n            font-weight: 600;\n            color: #000;\n        }\n\n        .header-text .address {\n            font-size: 12px;\n            color: #333;\n        }\n\n        /* Content */\n        .content {\n            padding: 20px;\n            font-size: 14px;\n            line-height: 1.6;\n            color: #333;\n        }\n\n        .content p {\n            margin: 0 0 15px;\n        }\n\n        .button {\n            display: inline-block;\n            background: #004aad;\n            color: #fff !important;\n            padding: 10px 15px;\n            text-decoration: none;\n            border-radius: 4px;\n            margin-top: 10px;\n        }\n\n        .notice {\n            font-size: 13px;\n            color: #555;\n            background: #f8f8f8;\n            padding: 10px;\n            border-radius: 6px;\n            margin-top: 20px;\n            border-left: 4px solid #004aad;\n        }\n\n        .official-email {\n            text-align: center;\n            font-size: 14px;\n            color: #004aad;\n            font-weight: 600;\n            margin-top: 5px;\n        }\n\n        .footer {\n            font-size: 12px;\n            color: #888;\n            text-align: center;\n            padding: 15px;\n            background: #f9f9f9;\n        }\n\n        /* ✅ Responsive Header for Mobile */\n        @media only screen and (max-width: 480px) {\n            .header-table td {\n                display: block !important;\n                width: 100% !important;\n                text-align: center !important;\n            }\n\n            .header-table img {\n                display: inline-block !important;\n                max-height: 60px !important;\n                margin: 5px 3px !important;\n            }\n\n            .header-table td[align=\"right\"] {\n                text-align: center !important;\n                padding-top: 10px !important;\n            }\n        }\n    </style>\n</head>\n\n<body>\n    <div class=\"email-container\">\n        <!-- HEADER -->\n        <table class=\"header-table\" cellpadding=\"0\" cellspacing=\"0\" role=\"presentation\">\n            <tr>\n                <!-- LOGOS -->\n                <td align=\"left\" valign=\"middle\" style=\"white-space:nowrap;\">\n                    <img src=\"https://gcdnb.pbrd.co/images/Pni5FEz4UOEJ.png?o=1\" alt=\"PLP Logo\">\n                    <img src=\"https://gcdnb.pbrd.co/images/EFxDeFIopVQN.png?o=1\" alt=\"SSO Logo\">\n                </td>\n\n                <!-- TEXT -->\n                <td align=\"right\" valign=\"middle\" style=\"text-align:right;\">\n                    <div style=\"background:#0c326f; color:#fff; font-weight:600; font-size:12px; padding:5px 10px; display:inline-block; border-radius:15px 0 0 15px; margin-bottom:4px;\">\n                        PAMANTASAN NG LUNGSOD NG PASIG\n                    </div><br>\n                    <div style=\"font-size:14px; font-weight:600; color:#000;\">\n                        Student Success Office\n                    </div>\n                    <div style=\"font-size:12px; color:#333;\">\n                        Alkalde Jose St. Kapasigan Pasig City, Philippines 1600\n                    </div>\n                </td>\n            </tr>\n        </table>\n\n        <!-- EMAIL CONTENT -->\n        <div class=\"content\">\n            <p>{{greetings}},</p>\n            <p>We received a request to reset the password for your <strong>Pamantasan ng Lungsod ng Pasig - Student Success Office (SSO)</strong> account. To proceed, please click the button below to reset your password:</p>\n\n            <p style=\"text-align: center; margin: 25px 0;\">\n                <a href=\"{{reset_link}}\"\n                    style=\"background-color: #004aad; color: #ffffff; text-decoration: none; padding: 12px 20px; border-radius: 5px; display: inline-block; font-weight: bold;\">\n                    Reset Password\n                </a>\n            </p>\n\n            <p>This link will remain valid until <strong>{{expire_at}}</strong>. If it expires, simply request a new password reset link through the <strong>Forgot Password</strong> page.</p>\n\n            <div class=\"notice\">\n                If you did not initiate this password reset request, you can safely ignore this email. For your security, please note that the <strong>only legitimate sender email address</strong> from our office is:\n                <div class=\"official-email\">plpasig.sso@gmail.com</div>\n                Any other email addresses claiming to represent the SSO should be considered unauthorized.\n            </div>\n\n            <p style=\"margin-top: 20px;\">Best regards,<br>\n                <strong>Pamantasan ng Lungsod ng Pasig<br>\n                    Student Success Office</strong>\n            </p>\n        </div>\n\n        <!-- FOOTER -->\n        <div class=\"footer\">\n            <p>Pamantasan ng Lungsod ng Pasig - Student Success Office</p>\n        </div>\n    </div>\n</body>\n\n</html>', '2025-10-08 20:54:58', 1),
(4, 'Account Reactivation', 'PLP - Student Success Office [Reactivate Account]', '<!DOCTYPE html>\n<html>\n\n<head>\n    <meta charset=\"UTF-8\">\n    <title>{{subject}}</title>\n    <link href=\"https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap\" rel=\"stylesheet\">\n    <style>\n        body {\n            font-family: \'Poppins\', Arial, sans-serif;\n            background-color: #f4f4f4;\n            padding: 20px;\n            margin: 0;\n        }\n\n        .email-container {\n            max-width: 600px;\n            margin: 0 auto;\n            background: #ffffff;\n            border-radius: 8px;\n            overflow: hidden;\n            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);\n        }\n\n        /* Header */\n        .header-table {\n            width: 100%;\n            background: #ffffff;\n            padding: 10px 15px;\n        }\n\n        .header-table img {\n            max-height: 70px;\n            vertical-align: middle;\n            margin: 0 5px;\n        }\n\n        .header-text {\n            text-align: center;\n            padding-top: 5px;\n            padding-bottom: 10px;\n        }\n\n        .header-text .school-name {\n            background: #0c326f;\n            color: white;\n            font-weight: 600;\n            font-size: 12px;\n            padding: 10px;\n            display: inline-block;\n            border-radius: 15px 0 0 15px;\n            margin-bottom: 4px;\n        }\n\n        .header-text .college-name {\n            font-size: 14px;\n            font-weight: 600;\n            color: #000;\n        }\n\n        .header-text .address {\n            font-size: 12px;\n            color: #333;\n        }\n\n        /* Content */\n        .content {\n            padding: 20px;\n            font-size: 14px;\n            line-height: 1.6;\n            color: #333;\n        }\n\n        .content p {\n            margin: 0 0 15px;\n        }\n\n        .button {\n            display: inline-block;\n            background: #004aad;\n            color: #fff !important;\n            padding: 10px 15px;\n            text-decoration: none;\n            border-radius: 4px;\n            margin-top: 10px;\n        }\n\n        .notice {\n            font-size: 13px;\n            color: #555;\n            background: #f8f8f8;\n            padding: 10px;\n            border-radius: 6px;\n            margin-top: 20px;\n            border-left: 4px solid #004aad;\n        }\n\n        .official-email {\n            text-align: center;\n            font-size: 14px;\n            color: #004aad;\n            font-weight: 600;\n            margin-top: 5px;\n        }\n\n        .footer {\n            font-size: 12px;\n            color: #888;\n            text-align: center;\n            padding: 15px;\n            background: #f9f9f9;\n        }\n\n        /* ✅ Responsive Header for Mobile */\n        @media only screen and (max-width: 480px) {\n            .header-table td {\n                display: block !important;\n                width: 100% !important;\n                text-align: center !important;\n            }\n\n            .header-table img {\n                display: inline-block !important;\n                max-height: 60px !important;\n                margin: 5px 3px !important;\n            }\n\n            .header-table td[align=\"right\"] {\n                text-align: center !important;\n                padding-top: 10px !important;\n            }\n        }\n    </style>\n</head>\n\n<body>\n    <div class=\"email-container\">\n        <!-- HEADER -->\n        <table class=\"header-table\" cellpadding=\"0\" cellspacing=\"0\" role=\"presentation\">\n            <tr>\n                <!-- LOGOS -->\n                <td align=\"left\" valign=\"middle\" style=\"white-space:nowrap;\">\n                    <img src=\"https://gcdnb.pbrd.co/images/Pni5FEz4UOEJ.png?o=1\" alt=\"PLP Logo\">\n                    <img src=\"https://gcdnb.pbrd.co/images/EFxDeFIopVQN.png?o=1\" alt=\"SSO Logo\">\n                </td>\n\n                <!-- TEXT -->\n                <td align=\"right\" valign=\"middle\" style=\"text-align:right;\">\n                    <div style=\"background:#0c326f; color:#fff; font-weight:600; font-size:12px; padding:5px 10px; display:inline-block; border-radius:15px 0 0 15px; margin-bottom:4px;\">\n                        PAMANTASAN NG LUNGSOD NG PASIG\n                    </div><br>\n                    <div style=\"font-size:14px; font-weight:600; color:#000;\">\n                        Student Success Office\n                    </div>\n                    <div style=\"font-size:12px; color:#333;\">\n                        Alkalde Jose St. Kapasigan Pasig City, Philippines 1600\n                    </div>\n                </td>\n            </tr>\n        </table>\n\n        <!-- EMAIL CONTENT -->\n        <div class=\"content\">\n            <p>{{greetings}},</p>\n            <p>We noticed that your account with the <strong>Pamantasan ng Lungsod ng Pasig - Student Success Office (SSO)</strong> has been deactivated. To reactivate your account and regain access, please click the button below:</p>\n\n            <p style=\"text-align: center; margin: 25px 0;\">\n                <a href=\"{{activation_link}}\"\n                    style=\"background-color: #004aad; color: #ffffff; text-decoration: none; padding: 12px 20px; border-radius: 5px; display: inline-block; font-weight: bold;\">\n                    Reactivate Account\n                </a>\n            </p>\n\n            <p>This link will remain valid until <strong>{{expire_at}}</strong></p>\n\n            <div class=\"notice\">\n                If you did not request this reactivation, please ignore this email, you can safely ignore this email. For your security, please note that the <strong>only legitimate sender email address</strong> from our office is:\n                <div class=\"official-email\">plpasig.sso@gmail.com</div>\n                Any other email addresses claiming to represent the SSO should be considered unauthorized.\n            </div>\n\n            <p style=\"margin-top: 20px;\">Best regards,<br>\n                <strong>Pamantasan ng Lungsod ng Pasig<br>\n                    Student Success Office</strong>\n            </p>\n        </div>\n\n        <!-- FOOTER -->\n        <div class=\"footer\">\n            <p>Pamantasan ng Lungsod ng Pasig - Student Success Office</p>\n        </div>\n    </div>\n</body>\n\n</html>', '2025-10-15 10:42:26', 1),
(5, 'Admission Update', 'PLP - Student Success Office [Admission Update]', '<!DOCTYPE html>\n<html>\n\n<head>\n    <meta charset=\"UTF-8\">\n    <title>{{subject}}</title>\n    <link href=\"https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap\" rel=\"stylesheet\">\n    <style>\n        body {\n            font-family: \'Poppins\', Arial, sans-serif;\n            background-color: #f4f4f4;\n            padding: 20px;\n            margin: 0;\n        }\n\n        .email-container {\n            max-width: 600px;\n            margin: 0 auto;\n            background: #ffffff;\n            border-radius: 8px;\n            overflow: hidden;\n            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);\n        }\n\n        /* Header */\n        .header-table {\n            width: 100%;\n            background: #ffffff;\n            padding: 10px 15px;\n        }\n\n        .header-table img {\n            max-height: 70px;\n            vertical-align: middle;\n            margin: 0 5px;\n        }\n\n        .header-text {\n            text-align: center;\n            padding-top: 5px;\n            padding-bottom: 10px;\n        }\n\n        .header-text .school-name {\n            background: #0c326f;\n            color: white;\n            font-weight: 600;\n            font-size: 12px;\n            padding: 10px;\n            display: inline-block;\n            border-radius: 15px 0 0 15px;\n            margin-bottom: 4px;\n        }\n\n        .header-text .college-name {\n            font-size: 14px;\n            font-weight: 600;\n            color: #000;\n        }\n\n        .header-text .address {\n            font-size: 12px;\n            color: #333;\n        }\n\n        /* Content */\n        .content {\n            padding: 20px;\n            font-size: 14px;\n            line-height: 1.6;\n            color: #333;\n        }\n\n        .content p {\n            margin: 0 0 15px;\n        }\n\n        .button {\n            display: inline-block;\n            background: #004aad;\n            color: #fff !important;\n            padding: 10px 15px;\n            text-decoration: none;\n            border-radius: 4px;\n            margin-top: 10px;\n        }\n\n        .notice {\n            font-size: 13px;\n            color: #555;\n            background: #f8f8f8;\n            padding: 10px;\n            border-radius: 6px;\n            margin-top: 20px;\n            border-left: 4px solid #004aad;\n        }\n\n        .official-email {\n            text-align: center;\n            font-size: 14px;\n            color: #004aad;\n            font-weight: 600;\n            margin-top: 5px;\n        }\n\n        .footer {\n            font-size: 12px;\n            color: #888;\n            text-align: center;\n            padding: 15px;\n            background: #f9f9f9;\n        }\n\n        /* ✅ Responsive Header for Mobile */\n        @media only screen and (max-width: 480px) {\n            .header-table td {\n                display: block !important;\n                width: 100% !important;\n                text-align: center !important;\n            }\n\n            .header-table img {\n                display: inline-block !important;\n                max-height: 60px !important;\n                margin: 5px 3px !important;\n            }\n\n            .header-table td[align=\"right\"] {\n                text-align: center !important;\n                padding-top: 10px !important;\n            }\n        }\n    </style>\n</head>\n\n<body>\n    <div class=\"email-container\">\n        <!-- HEADER -->\n        <table class=\"header-table\" cellpadding=\"0\" cellspacing=\"0\" role=\"presentation\">\n            <tr>\n                <!-- LOGOS -->\n                <td align=\"left\" valign=\"middle\" style=\"white-space:nowrap;\">\n                    <img src=\"https://gcdnb.pbrd.co/images/Pni5FEz4UOEJ.png?o=1\" alt=\"PLP Logo\">\n                    <img src=\"https://gcdnb.pbrd.co/images/EFxDeFIopVQN.png?o=1\" alt=\"SSO Logo\">\n                </td>\n\n                <!-- TEXT -->\n                <td align=\"right\" valign=\"middle\" style=\"text-align:right;\">\n                    <div style=\"background:#0c326f; color:#fff; font-weight:600; font-size:12px; padding:5px 10px; display:inline-block; border-radius:15px 0 0 15px; margin-bottom:4px;\">\n                        PAMANTASAN NG LUNGSOD NG PASIG\n                    </div><br>\n                    <div style=\"font-size:14px; font-weight:600; color:#000;\">\n                        Student Success Office\n                    </div>\n                    <div style=\"font-size:12px; color:#333;\">\n                        Alkalde Jose St. Kapasigan Pasig City, Philippines 1600\n                    </div>\n                </td>\n            </tr>\n        </table>\n\n        <!-- EMAIL CONTENT -->\n        <!-- EMAIL CONTENT -->\n        <div class=\"content\">\n            <p>Good day!</p>\n\n            <p>\n                We hope you are doing well. We are writing to inform you that your admission status at\n                <strong>Pamantasan ng Lungsod ng Pasig</strong> has been updated.\n            </p>\n\n            <div style=\"text-align:center; margin: 25px 0;\">\n                <div style=\"background-color: #004aad; color: #ffffff; padding: 14px 20px; border-radius: 6px; display: inline-block; font-weight: 600; font-size: 15px; letter-spacing: 0.5px;\">\n                    APPLICATION STATUS: {{status}}\n                </div>\n            </div>\n\n            <div style=\"text-align:center; margin: 20px 0;\">\n                <div style=\"background-color: #f1f5ff; color: #004aad; border: 1px solid #004aad; padding: 12px 18px; border-radius: 6px; display: inline-block; font-weight: 500; font-size: 14px;\">\n                    REMARKS: {{remarks}}\n                </div>\n            </div>\n\n            <p>\n                To view more details or take the next steps, please visit your applicant portal.\n            </p>\n\n            <div class=\"notice\">\n                <strong>Important Reminder:</strong><br>\n                For your security, please note that the <strong>only legitimate sender email address</strong> from our office is:\n                <div class=\"official-email\">plpasig.sso@gmail.com</div>\n                Any other email addresses claiming to represent the SSO should be considered unauthorized.\n            </div>\n\n            <p style=\"margin-top: 25px;\">\n                Best regards,<br>\n                <strong>Pamantasan ng Lungsod ng Pasig<br>\n                    Student Success Office</strong>\n            </p>\n        </div>\n\n\n        <!-- FOOTER -->\n        <div class=\"footer\">\n            <p>Pamantasan ng Lungsod ng Pasig - Student Success Office</p>\n        </div>\n    </div>\n</body>\n\n</html>', '2025-10-15 10:42:26', 1),
(6, 'Exam Schedule', 'PLP - Student Success Office [Exam Schedule]', '<!DOCTYPE html>\n<html>\n\n<head>\n    <meta charset=\"UTF-8\">\n    <title>{{subject}}</title>\n    <link href=\"https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap\" rel=\"stylesheet\">\n    <style>\n        body {\n            font-family: \'Poppins\', Arial, sans-serif;\n            background-color: #f4f4f4;\n            padding: 20px;\n            margin: 0;\n        }\n\n        .email-container {\n            max-width: 600px;\n            margin: 0 auto;\n            background: #ffffff;\n            border-radius: 8px;\n            overflow: hidden;\n            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);\n        }\n\n        /* Header */\n        .header-table {\n            width: 100%;\n            background: #ffffff;\n            padding: 10px 15px;\n        }\n\n        .header-table img {\n            max-height: 70px;\n            vertical-align: middle;\n            margin: 0 5px;\n        }\n\n        .header-text {\n            text-align: center;\n            padding-top: 5px;\n            padding-bottom: 10px;\n        }\n\n        .header-text .school-name {\n            background: #0c326f;\n            color: white;\n            font-weight: 600;\n            font-size: 12px;\n            padding: 10px;\n            display: inline-block;\n            border-radius: 15px 0 0 15px;\n            margin-bottom: 4px;\n        }\n\n        .header-text .college-name {\n            font-size: 14px;\n            font-weight: 600;\n            color: #000;\n        }\n\n        .header-text .address {\n            font-size: 12px;\n            color: #333;\n        }\n\n        /* Content */\n        .content {\n            padding: 20px;\n            font-size: 14px;\n            line-height: 1.6;\n            color: #333;\n        }\n\n        .content p {\n            margin: 0 0 15px;\n        }\n\n        .button {\n            display: inline-block;\n            background: #004aad;\n            color: #fff !important;\n            padding: 10px 15px;\n            text-decoration: none;\n            border-radius: 4px;\n            margin-top: 10px;\n        }\n\n        .notice {\n            font-size: 13px;\n            color: #555;\n            background: #f8f8f8;\n            padding: 10px;\n            border-radius: 6px;\n            margin-top: 20px;\n            border-left: 4px solid #004aad;\n        }\n\n        .official-email {\n            text-align: center;\n            font-size: 14px;\n            color: #004aad;\n            font-weight: 600;\n            margin-top: 5px;\n        }\n\n        .footer {\n            font-size: 12px;\n            color: #888;\n            text-align: center;\n            padding: 15px;\n            background: #f9f9f9;\n        }\n\n        /* ✅ Responsive Header for Mobile */\n        @media only screen and (max-width: 480px) {\n            .header-table td {\n                display: block !important;\n                width: 100% !important;\n                text-align: center !important;\n            }\n\n            .header-table img {\n                display: inline-block !important;\n                max-height: 60px !important;\n                margin: 5px 3px !important;\n            }\n\n            .header-table td[align=\"right\"] {\n                text-align: center !important;\n                padding-top: 10px !important;\n            }\n        }\n    </style>\n</head>\n\n<body>\n    <div class=\"email-container\">\n        <!-- HEADER -->\n        <table class=\"header-table\" cellpadding=\"0\" cellspacing=\"0\" role=\"presentation\">\n            <tr>\n                <!-- LOGOS -->\n                <td align=\"left\" valign=\"middle\" style=\"white-space:nowrap;\">\n                    <img src=\"https://gcdnb.pbrd.co/images/Pni5FEz4UOEJ.png?o=1\" alt=\"PLP Logo\">\n                    <img src=\"https://gcdnb.pbrd.co/images/EFxDeFIopVQN.png?o=1\" alt=\"SSO Logo\">\n                </td>\n\n                <!-- TEXT -->\n                <td align=\"right\" valign=\"middle\" style=\"text-align:right;\">\n                    <div style=\"background:#0c326f; color:#fff; font-weight:600; font-size:12px; padding:5px 10px; display:inline-block; border-radius:15px 0 0 15px; margin-bottom:4px;\">\n                        PAMANTASAN NG LUNGSOD NG PASIG\n                    </div><br>\n                    <div style=\"font-size:14px; font-weight:600; color:#000;\">\n                        Student Success Office\n                    </div>\n                    <div style=\"font-size:12px; color:#333;\">\n                        Alkalde Jose St. Kapasigan Pasig City, Philippines 1600\n                    </div>\n                </td>\n            </tr>\n        </table>\n\n        <!-- EMAIL CONTENT -->\n        <div class=\"content\">\n            <p>Good day!</p>\n\n            <p>\n                We are pleased to inform you that your <strong>entrance examination schedule</strong> has been set.\n                Please review the details of your exam below carefully.\n            </p>\n\n            <div\n                style=\"background-color: #f1f5ff; border-left: 4px solid #004aad; padding: 20px; border-radius: 6px; margin: 25px 0;\">\n                <table\n                    width=\"100%\"\n                    cellpadding=\"5\"\n                    cellspacing=\"0\"\n                    style=\"font-size: 14px; color: #333;\">\n                    <tr>\n                        <td style=\"font-weight: 600; width: 35%; vertical-align: top;\">\n                            <svg\n                                xmlns=\"http://www.w3.org/2000/svg\"\n                                width=\"14\"\n                                height=\"14\"\n                                fill=\"#004aad\"\n                                viewBox=\"0 0 24 24\"\n                                style=\"vertical-align: middle; margin-right: 6px;\">\n                                <path\n                                    d=\"M19 4h-1V2h-2v2H8V2H6v2H5C3.9 4 3 4.9 3 6v14c0 \n              1.1.9 2 2 2h14c1.1 0 2-.9 \n              2-2V6c0-1.1-.9-2-2-2zm0 \n              16H5V10h14v10zm0-12H5V6h14v2z\" />\n                            </svg>\n                            Exam Date:\n                        </td>\n                        <td>{{exam_date}}</td>\n                    </tr>\n\n                    <tr>\n                        <td style=\"font-weight: 600; vertical-align: top;\">\n                            <svg\n                                xmlns=\"http://www.w3.org/2000/svg\"\n                                width=\"14\"\n                                height=\"14\"\n                                fill=\"#004aad\"\n                                viewBox=\"0 0 24 24\"\n                                style=\"vertical-align: middle; margin-right: 6px;\">\n                                <path\n                                    d=\"M12 1a11 11 0 1 0 11 11A11 11 0 0 0 12 \n              1zm0 20a9 9 0 1 1 9-9a9 9 0 0 1-9 \n              9zm.5-9.79V6h-1v6h6v-1h-5z\" />\n                            </svg>\n                            Time:\n                        </td>\n                        <td>{{exam_time}}</td>\n                    </tr>\n\n                    <tr>\n                        <td style=\"font-weight: 600; vertical-align: top;\">\n                            <svg\n                                xmlns=\"http://www.w3.org/2000/svg\"\n                                width=\"14\"\n                                height=\"14\"\n                                fill=\"#004aad\"\n                                viewBox=\"0 0 24 24\"\n                                style=\"vertical-align: middle; margin-right: 6px;\">\n                                <path\n                                    d=\"M12 2a7 7 0 0 0-7 \n              7c0 5.25 7 13 7 13s7-7.75 \n              7-13a7 7 0 0 0-7-7zm0 9.5a2.5 \n              2.5 0 1 1 2.5-2.5A2.5 2.5 0 0 1 12 \n              11.5z\" />\n                            </svg>\n                            Venue:\n                        </td>\n                        <td>{{exam_venue}}</td>\n                    </tr>\n                </table>\n            </div>\n\n            <div class=\"notice\">\n                <p style=\"margin-top: 10px;\">\n                    For your security, please note that the\n                    <strong>only legitimate sender email address</strong> from our office is:\n                </p>\n                <div class=\"official-email\">plpasig.sso@gmail.com</div>\n                Any other email addresses claiming to represent the SSO should be considered unauthorized.\n            </div>\n\n            <p style=\"margin-top: 25px;\">\n                Best regards,<br />\n                <strong>Pamantasan ng Lungsod ng Pasig<br />Student Success Office</strong>\n            </p>\n        </div>\n\n        <!-- FOOTER -->\n        <div class=\"footer\">\n            <p>Pamantasan ng Lungsod ng Pasig - Student Success Office</p>\n        </div>\n    </div>\n</body>\n\n</html>', '2025-10-15 10:42:26', 1),
(7, 'Exam Permit', 'PLP - Student Success Office [Exam Permit]', '<!DOCTYPE html>\n<html>\n\n<head>\n    <meta charset=\"UTF-8\">\n    <title>{{subject}}</title>\n    <link href=\"https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap\" rel=\"stylesheet\">\n    <style>\n        body {\n            font-family: \'Poppins\', Arial, sans-serif;\n            background-color: #f4f4f4;\n            padding: 20px;\n            margin: 0;\n        }\n\n        .email-container {\n            max-width: 600px;\n            margin: 0 auto;\n            background: #ffffff;\n            border-radius: 8px;\n            overflow: hidden;\n            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);\n        }\n\n        /* Header */\n        .header-table {\n            width: 100%;\n            background: #ffffff;\n            padding: 10px 15px;\n        }\n\n        .header-table img {\n            max-height: 70px;\n            vertical-align: middle;\n            margin: 0 5px;\n        }\n\n        .header-text {\n            text-align: center;\n            padding-top: 5px;\n            padding-bottom: 10px;\n        }\n\n        .header-text .school-name {\n            background: #0c326f;\n            color: white;\n            font-weight: 600;\n            font-size: 12px;\n            padding: 10px;\n            display: inline-block;\n            border-radius: 15px 0 0 15px;\n            margin-bottom: 4px;\n        }\n\n        .header-text .college-name {\n            font-size: 14px;\n            font-weight: 600;\n            color: #000;\n        }\n\n        .header-text .address {\n            font-size: 12px;\n            color: #333;\n        }\n\n        /* Content */\n        .content {\n            padding: 20px;\n            font-size: 14px;\n            line-height: 1.6;\n            color: #333;\n        }\n\n        .content p {\n            margin: 0 0 15px;\n        }\n\n        .button {\n            display: inline-block;\n            background: #004aad;\n            color: #fff !important;\n            padding: 10px 15px;\n            text-decoration: none;\n            border-radius: 4px;\n            margin-top: 10px;\n        }\n\n        .notice {\n            font-size: 13px;\n            color: #555;\n            background: #f8f8f8;\n            padding: 10px;\n            border-radius: 6px;\n            margin-top: 20px;\n            border-left: 4px solid #004aad;\n        }\n\n        .official-email {\n            text-align: center;\n            font-size: 14px;\n            color: #004aad;\n            font-weight: 600;\n            margin-top: 5px;\n        }\n\n        .footer {\n            font-size: 12px;\n            color: #888;\n            text-align: center;\n            padding: 15px;\n            background: #f9f9f9;\n        }\n\n        /* ✅ Responsive Header for Mobile */\n        @media only screen and (max-width: 480px) {\n            .header-table td {\n                display: block !important;\n                width: 100% !important;\n                text-align: center !important;\n            }\n\n            .header-table img {\n                display: inline-block !important;\n                max-height: 60px !important;\n                margin: 5px 3px !important;\n            }\n\n            .header-table td[align=\"right\"] {\n                text-align: center !important;\n                padding-top: 10px !important;\n            }\n        }\n    </style>\n</head>\n\n<body>\n    <div class=\"email-container\">\n        <!-- HEADER -->\n        <table class=\"header-table\" cellpadding=\"0\" cellspacing=\"0\" role=\"presentation\">\n            <tr>\n                <!-- LOGOS -->\n                <td align=\"left\" valign=\"middle\" style=\"white-space:nowrap;\">\n                    <img src=\"https://gcdnb.pbrd.co/images/Pni5FEz4UOEJ.png?o=1\" alt=\"PLP Logo\">\n                    <img src=\"https://gcdnb.pbrd.co/images/EFxDeFIopVQN.png?o=1\" alt=\"SSO Logo\">\n                </td>\n\n                <!-- TEXT -->\n                <td align=\"right\" valign=\"middle\" style=\"text-align:right;\">\n                    <div style=\"background:#0c326f; color:#fff; font-weight:600; font-size:12px; padding:5px 10px; display:inline-block; border-radius:15px 0 0 15px; margin-bottom:4px;\">\n                        PAMANTASAN NG LUNGSOD NG PASIG\n                    </div><br>\n                    <div style=\"font-size:14px; font-weight:600; color:#000;\">\n                        Student Success Office\n                    </div>\n                    <div style=\"font-size:12px; color:#333;\">\n                        Alkalde Jose St. Kapasigan Pasig City, Philippines 1600\n                    </div>\n                </td>\n            </tr>\n        </table>\n\n        <!-- EMAIL CONTENT -->\n        <div class=\"content\">\n            <p>Good day!</p>\n\n            <p>\n                This is to inform you that your <strong>Entrance Examination Permit</strong> has been issued.\n                Please review your exam details and ensure you bring a printed or digital copy of your permit\n                on the day of your examination.\n            </p>\n\n            <p style=\"text-align: center; margin: 25px 0;\">\n                <a href=\"{{exam_permit_download_link}}\"\n                    style=\"background-color: #004aad; color: #ffffff; text-decoration: none; padding: 12px 20px; border-radius: 5px; display: inline-block; font-weight: bold;\">\n                    Download Exam Permit\n                </a>\n            </p>\n\n            <div class=\"notice\">\n                <p style=\"margin-top: 10px;\">\n                    For your security, please note that the\n                    <strong>only legitimate sender email address</strong> from our office is:\n                </p>\n                <div class=\"official-email\">plpasig.sso@gmail.com</div>\n                Any other email addresses claiming to represent the SSO should be considered unauthorized.\n            </div>\n\n            <p style=\"margin-top: 25px;\">\n                Best regards,<br />\n                <strong>Pamantasan ng Lungsod ng Pasig<br />Student Success Office</strong>\n            </p>\n        </div>\n\n        <!-- FOOTER -->\n        <div class=\"footer\">\n            <p>Pamantasan ng Lungsod ng Pasig - Student Success Office</p>\n        </div>\n    </div>\n</body>\n\n</html>', '2025-10-15 10:42:26', 1);

-- --------------------------------------------------------

--
-- Table structure for table `ExamRegistrations`
--

CREATE TABLE `ExamRegistrations` (
  `registration_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `schedule_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `ExamRegistrations`
--

INSERT INTO `ExamRegistrations` (`registration_id`, `user_id`, `schedule_id`) VALUES
(1, 1, 34);

-- --------------------------------------------------------

--
-- Table structure for table `ExamSchedules`
--

CREATE TABLE `ExamSchedules` (
  `schedule_id` int(11) NOT NULL,
  `floor` varchar(50) DEFAULT NULL,
  `room` varchar(50) DEFAULT NULL,
  `capacity` int(11) DEFAULT NULL,
  `start_date_and_time` timestamp NOT NULL,
  `status` enum('Open','Full') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `ExamSchedules`
--

INSERT INTO `ExamSchedules` (`schedule_id`, `floor`, `room`, `capacity`, `start_date_and_time`, `status`) VALUES
(1, '1st Floor', 'Room 101', 50, '2025-11-10 08:00:00', 'Open'),
(2, '1st Floor', 'Room 102', 40, '2025-11-10 08:00:00', 'Open'),
(3, '1st Floor', 'Room 103', 45, '2025-11-10 08:30:00', 'Open'),
(4, '2nd Floor', 'Room 201', 60, '2025-11-10 08:30:00', 'Open'),
(5, '2nd Floor', 'Room 202', 50, '2025-11-10 09:00:00', 'Open'),
(6, '2nd Floor', 'Room 203', 55, '2025-11-10 09:00:00', 'Open'),
(7, '3rd Floor', 'Room 301', 30, '2025-11-10 09:30:00', 'Open'),
(8, '3rd Floor', 'Room 302', 35, '2025-11-10 09:30:00', 'Open'),
(9, '3rd Floor', 'Room 303', 40, '2025-11-10 10:00:00', 'Open'),
(10, '4th Floor', 'Room 401', 70, '2025-11-10 10:00:00', 'Open'),
(11, '4th Floor', 'Room 402', 65, '2025-11-10 10:30:00', 'Open'),
(12, '1st Floor', 'Room 101', 50, '2025-11-10 10:30:00', 'Open'),
(13, '1st Floor', 'Room 102', 40, '2025-11-10 11:00:00', 'Open'),
(14, '1st Floor', 'Room 103', 45, '2025-11-10 11:00:00', 'Open'),
(15, '2nd Floor', 'Room 201', 60, '2025-11-10 11:30:00', 'Open'),
(16, '2nd Floor', 'Room 202', 50, '2025-11-10 11:30:00', 'Open'),
(17, '2nd Floor', 'Room 203', 55, '2025-11-10 13:00:00', 'Open'),
(18, '3rd Floor', 'Room 301', 30, '2025-11-10 13:00:00', 'Open'),
(19, '3rd Floor', 'Room 302', 35, '2025-11-10 13:30:00', 'Open'),
(20, '3rd Floor', 'Room 303', 40, '2025-11-10 13:30:00', 'Open'),
(21, '4th Floor', 'Room 401', 70, '2025-11-10 14:00:00', 'Open'),
(22, '4th Floor', 'Room 402', 65, '2025-11-10 14:00:00', 'Open'),
(23, '1st Floor', 'Room 101', 50, '2025-11-10 14:30:00', 'Open'),
(24, '1st Floor', 'Room 102', 40, '2025-11-10 14:30:00', 'Open'),
(25, '1st Floor', 'Room 103', 45, '2025-11-10 15:00:00', 'Open'),
(26, '2nd Floor', 'Room 201', 60, '2025-11-10 15:00:00', 'Open'),
(27, '2nd Floor', 'Room 202', 50, '2025-11-10 15:30:00', 'Open'),
(28, '2nd Floor', 'Room 203', 55, '2025-11-10 15:30:00', 'Open'),
(29, '3rd Floor', 'Room 301', 30, '2025-11-10 16:00:00', 'Open'),
(30, '3rd Floor', 'Room 302', 35, '2025-11-10 16:00:00', 'Open'),
(31, '1st Floor', 'Room 104', 25, '2025-11-11 08:00:00', 'Open'),
(32, '1st Floor', 'Room 105', 30, '2025-11-11 08:00:00', 'Open'),
(33, '2nd Floor', 'Room 204', 40, '2025-11-11 08:30:00', 'Open'),
(34, '2nd Floor', 'Room 205', 35, '2025-11-11 08:30:00', 'Open'),
(35, '3rd Floor', 'Room 304', 50, '2025-11-11 09:00:00', 'Open'),
(36, '3rd Floor', 'Room 305', 45, '2025-11-11 09:00:00', 'Open'),
(37, '4th Floor', 'Room 403', 60, '2025-11-11 09:30:00', 'Open'),
(38, '4th Floor', 'Room 404', 55, '2025-11-11 09:30:00', 'Open'),
(39, 'Gymnasium', 'Section A', 150, '2025-11-11 10:00:00', 'Open'),
(40, 'Gymnasium', 'Section B', 150, '2025-11-11 10:00:00', 'Open'),
(41, 'Auditorium', 'Main Hall', 200, '2025-11-11 10:30:00', 'Open'),
(42, '1st Floor', 'Room 104', 25, '2025-11-11 10:30:00', 'Open'),
(43, '1st Floor', 'Room 105', 30, '2025-11-11 11:00:00', 'Open'),
(44, '2nd Floor', 'Room 204', 40, '2025-11-11 11:00:00', 'Open'),
(45, '2nd Floor', 'Room 205', 35, '2025-11-11 13:00:00', 'Open'),
(46, '3rd Floor', 'Room 304', 50, '2025-11-11 13:00:00', 'Open'),
(47, '3rd Floor', 'Room 305', 45, '2025-11-11 13:30:00', 'Open'),
(48, '4th Floor', 'Room 403', 60, '2025-11-11 13:30:00', 'Open'),
(49, '4th Floor', 'Room 404', 55, '2025-11-11 14:00:00', 'Open'),
(50, 'Gymnasium', 'Section A', 150, '2025-11-11 14:00:00', 'Open'),
(51, '3rd Floor', 'Room 301', 123, '2025-10-28 20:07:00', 'Open'),
(52, 'sdada', 'sdasdsadas', 12312, '2025-10-15 13:45:00', 'Open'),
(53, 'asdas', 'dasdas', 2312312, '2025-10-10 03:47:00', 'Open'),
(54, 'asdas', 'asdasd', 1231212, '0000-00-00 00:00:00', 'Open'),
(55, '1', '3rd', 2, '2025-10-22 01:45:00', 'Open');

-- --------------------------------------------------------

--
-- Table structure for table `expiration_config`
--

CREATE TABLE `expiration_config` (
  `id` int(11) NOT NULL,
  `type` varchar(50) DEFAULT NULL,
  `interval_value` int(11) DEFAULT NULL,
  `interval_unit` enum('MINUTE','HOUR','DAY','MONTH') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `expiration_config`
--

INSERT INTO `expiration_config` (`id`, `type`, `interval_value`, `interval_unit`) VALUES
(1, 'activation_account', 1, 'DAY'),
(2, 'password_reset', 1, 'DAY'),
(3, 'login_otp', 10, 'MINUTE'),
(4, 'session', 7, 'DAY');

-- --------------------------------------------------------

--
-- Table structure for table `faqs`
--

CREATE TABLE `faqs` (
  `id` int(11) NOT NULL,
  `question` text NOT NULL,
  `answer` text NOT NULL,
  `read_count` int(11) DEFAULT 0,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `faqs`
--

INSERT INTO `faqs` (`id`, `question`, `answer`, `read_count`, `status`, `created_at`, `updated_at`) VALUES
(1, 'What are the eligibility criteria for undergraduate admission?', 'Applicants must have completed high school or equivalent with a minimum required percentage as specified by the college.', 3, 'active', '2025-11-02 16:02:23', '2025-11-02 16:44:47'),
(2, 'How can I apply for admission?', 'You can apply online through our official admission portal by filling out the application form and submitting the required documents.', 8, 'active', '2025-11-02 16:02:23', '2025-11-03 00:46:41'),
(3, 'What documents are required during the admission process?', 'You will need your high school transcripts, passport-size photographs, ID proof, transfer certificate, and entrance exam scorecard (if applicable).', 5, 'active', '2025-11-02 16:02:23', '2025-11-05 09:07:31'),
(4, 'Is there an entrance exam for admission?', 'Yes, certain courses require applicants to appear for an entrance exam. Please check the course details for more information.', 5, 'active', '2025-11-02 16:02:23', '2025-11-02 22:11:42'),
(5, 'What is the application fee?', 'The application fee is specified on the admission portal and varies based on the course. Payments can be made online via credit/debit card or net banking.', 1, 'active', '2025-11-02 16:02:23', '2025-11-02 16:43:33'),
(6, 'Can I apply for multiple courses?', 'Yes, you can apply for multiple courses, but separate application forms and fees are required for each course.', 1, 'active', '2025-11-02 16:02:23', '2025-11-02 16:43:33'),
(7, 'Do you offer scholarships or financial aid?', 'Yes, scholarships and financial aid are available based on academic performance, entrance exam scores, and economic background.', 1, 'active', '2025-11-02 16:02:23', '2025-11-02 16:43:34'),
(8, 'How can I check my application status?', 'You can check your application status by logging into your account on the admission portal using your registered email and password.', 1, 'active', '2025-11-02 16:02:23', '2025-11-02 16:43:34'),
(9, 'What is the last date to submit the application form?', 'The last date for application submission is mentioned on the admission notification and official website. Late submissions may not be accepted.', 0, 'inactive', '2025-11-02 16:02:23', '2025-11-02 16:02:23'),
(10, 'Can international students apply for admission?', 'Yes, international students are welcome to apply. They must submit equivalent academic documents and a valid student visa.', 2, 'active', '2025-11-02 16:02:23', '2025-11-02 16:44:45');

-- --------------------------------------------------------

--
-- Table structure for table `form_fields`
--

CREATE TABLE `form_fields` (
  `id` int(11) NOT NULL,
  `step_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `label` varchar(255) NOT NULL,
  `input_type` varchar(50) NOT NULL,
  `placeholder_text` varchar(255) DEFAULT NULL,
  `is_archived` tinyint(1) NOT NULL DEFAULT 0,
  `is_required` tinyint(1) NOT NULL DEFAULT 0,
  `field_order` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `form_fields`
--

INSERT INTO `form_fields` (`id`, `step_id`, `name`, `label`, `input_type`, `placeholder_text`, `is_archived`, `is_required`, `field_order`) VALUES
(1, 1, 'first_name', 'First Name', 'text', '', 0, 1, 1),
(2, 1, 'middle_name', 'Middle Name', 'text', '', 0, 0, 2),
(3, 1, 'last_name', 'Last Name', 'text', '', 0, 1, 3),
(4, 1, 'suffix', 'Suffix', 'text', '', 0, 0, 4),
(5, 2, 'student_valid_id', 'Government Issued Valid ID/Student ID', 'file', '', 0, 1, 1),
(6, 3, 'first_name', 'First Name', 'text', 'eg. Juan', 0, 1, 1),
(7, 5, 'first_name', 'First Name', 'text', 'eg. Juan', 0, 1, 1),
(8, 3, 'LRN', 'LRN', 'text', 'LRN', 0, 0, 2),
(9, 3, 'Surname', 'Surname', 'text', 'Surname', 0, 1, 3),
(10, 3, 'given_name', 'Given Name', 'text', 'Given Name', 0, 1, 4),
(11, 3, 'middle_name', 'Middle Name', 'text', 'Middle Name', 0, 1, 5),
(12, 3, 'person_disability', 'Are you a person with disability?', 'select', 'eg. Yes/No', 0, 1, 6);

-- --------------------------------------------------------

--
-- Table structure for table `form_field_options`
--

CREATE TABLE `form_field_options` (
  `id` int(11) NOT NULL,
  `field_id` int(11) NOT NULL,
  `option_label` varchar(255) NOT NULL,
  `option_value` varchar(255) NOT NULL,
  `option_order` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `form_field_options`
--

INSERT INTO `form_field_options` (`id`, `field_id`, `option_label`, `option_value`, `option_order`) VALUES
(1, 12, 'Yes', 'Yes', 1),
(2, 12, 'No', 'No', 2);

-- --------------------------------------------------------

--
-- Table structure for table `form_steps`
--

CREATE TABLE `form_steps` (
  `id` int(11) NOT NULL,
  `applicant_type_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `is_archived` tinyint(1) NOT NULL DEFAULT 0,
  `step_order` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `form_steps`
--

INSERT INTO `form_steps` (`id`, `applicant_type_id`, `title`, `is_archived`, `step_order`) VALUES
(1, 1, 'Step 1: Personal Information', 0, 1),
(2, 1, 'Step 2: Required Documents', 0, 2),
(3, 4, 'Step 1: Personal Information', 0, 1),
(4, 4, 'Step 2: Document Requirements', 0, 2),
(5, 8, 'Step 1: Personal Information', 0, 1),
(6, 8, 'Step 2: Document Requirements', 0, 2);

-- --------------------------------------------------------

--
-- Table structure for table `general_uploads`
--

CREATE TABLE `general_uploads` (
  `id` int(11) NOT NULL,
  `title` varchar(250) NOT NULL,
  `file_url` varchar(1024) NOT NULL COMMENT 'Full URL to the uploaded file',
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `date_upload` timestamp NOT NULL DEFAULT current_timestamp(),
  `date_modified` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `general_uploads`
--

INSERT INTO `general_uploads` (`id`, `title`, `file_url`, `status`, `date_upload`, `date_modified`) VALUES
(1, 'Footer Image', 'https://gold-lion-549609.hostingersite.com/uploads/general/690153f9a724_3d_footer1.png', 'active', '2025-10-28 23:38:33', '2025-10-28 23:38:33'),
(2, 'PLP Logo', 'https://gold-lion-549609.hostingersite.com/uploads/general/69015463d5fa_Inang_pamantasan_logo.png', 'active', '2025-10-28 23:40:19', '2025-10-28 23:40:19'),
(3, 'SSO Logo', 'https://gold-lion-549609.hostingersite.com/uploads/general/69015492e8be_SSO_LOGO.png', 'active', '2025-10-28 23:41:06', '2025-10-28 23:41:06'),
(4, 'Officer Cover', 'https://gold-lion-549609.hostingersite.com/uploads/general/690154e5afc8_wall_of_officer.png', 'active', '2025-10-28 23:42:29', '2025-10-28 23:42:29'),
(5, 'Transferee Cover', 'https://gold-lion-549609.hostingersite.com/uploads/general/690155036bc2_transferee_cover2.png', 'active', '2025-10-28 23:42:59', '2025-10-28 23:42:59'),
(6, 'Pasig Logo white', 'https://gold-lion-549609.hostingersite.com/uploads/general/6901560b0dc8_Pasig_word_White_color.png', 'active', '2025-10-28 23:47:23', '2025-10-28 23:47:23'),
(7, 'Pasig Logo Original', 'https://gold-lion-549609.hostingersite.com/uploads/general/69015642684e_Pasig_word_blue_color.png', 'active', '2025-10-28 23:48:18', '2025-10-28 23:48:18'),
(8, 'ID Replacement Cover', 'https://gold-lion-549609.hostingersite.com/uploads/general/69015673f2fe_ID_replacement.png', 'active', '2025-10-28 23:49:07', '2025-10-28 23:49:07'),
(9, 'Banner Images', 'https://gold-lion-549609.hostingersite.com/uploads/general/690156e6b4d1_banner_images.png', 'active', '2025-10-28 23:51:02', '2025-10-28 23:51:02'),
(10, 'Document Example Images', 'https://gold-lion-549609.hostingersite.com/uploads/general/69015706c966_Example_document_form.png', 'active', '2025-10-28 23:51:34', '2025-10-28 23:51:34'),
(11, 'Admission Cover', 'https://gold-lion-549609.hostingersite.com/uploads/general/690157c735a9_12.png', 'active', '2025-10-28 23:54:47', '2025-10-28 23:54:47'),
(12, 'ID Replacement Cover', 'https://gold-lion-549609.hostingersite.com/uploads/general/690157f3d94c_10.png', 'active', '2025-10-28 23:55:31', '2025-10-28 23:55:31'),
(13, 'Type Of Applicant Cover', 'https://gold-lion-549609.hostingersite.com/uploads/general/690158675e99_type_of_applicant.png', 'active', '2025-10-28 23:57:27', '2025-10-28 23:57:27'),
(14, 'Ador Images', 'https://gold-lion-549609.hostingersite.com/uploads/general/690158a3dcca_dev_ador.png', 'active', '2025-10-28 23:58:27', '2025-10-28 23:58:27'),
(15, 'Abo Images', 'https://gold-lion-549609.hostingersite.com/uploads/general/690158b2cd24_dev_abo.png', 'active', '2025-10-28 23:58:42', '2025-10-28 23:58:42'),
(16, 'Datus Images', 'https://gold-lion-549609.hostingersite.com/uploads/general/690158c546b6_dev_datus.png', 'active', '2025-10-28 23:59:01', '2025-10-28 23:59:01'),
(17, 'Goodmoral Cover', 'https://gold-lion-549609.hostingersite.com/uploads/general/6901ade3e087_Good_moral_card_cover1.png', 'active', '2025-10-29 06:02:11', '2025-10-29 06:02:11'),
(18, 'System Logo', 'https://gold-lion-549609.hostingersite.com/uploads/general/6901f146879a_Student_Servoice-removebg-preview.png', 'active', '2025-10-29 10:49:42', '2025-10-29 10:49:42'),
(20, 'Contact Us Banner', 'https://gold-lion-549609.hostingersite.com/uploads/general/69021a10cfcb_things_i_wanted_to_say_but_never_did.png', 'active', '2025-10-29 13:43:44', '2025-10-29 13:43:44'),
(21, 'Admission Applicant Background', 'https://gold-lion-549609.hostingersite.com/uploads/general/69021ca59dd8_layered-waves-haikei.png', 'active', '2025-10-29 13:54:45', '2025-10-29 13:54:45'),
(22, 'Banner Header', 'https://gold-lion-549609.hostingersite.com/uploads/general/690222154c1c_banner_images.png', 'active', '2025-10-29 14:17:57', '2025-10-29 14:19:45'),
(23, 'Testing123', 'http://localhost/STUDENT SUCCESS OFFICE - api/uploads/general/6902860a7301_things_i_wanted_to_say_but_never_did.png', 'active', '2025-10-29 14:36:01', '2025-10-29 21:24:29'),
(24, 'test', 'http://localhost/STUDENT SUCCESS OFFICE - api/uploads/general/69028839a261_things_i_wanted_to_say_but_never_did.png', 'active', '2025-10-29 14:41:17', '2025-10-29 21:33:48'),
(25, 'Tstng', 'http://localhost/STUDENT SUCCESS OFFICE - api/uploads/general/690285e88727_things_i_wanted_to_say_but_never_did.png', 'active', '2025-10-29 20:50:04', '2025-10-29 21:23:55'),
(26, 'things i wanted to say but never did', 'https://gold-lion-549609.hostingersite.com/uploads/general/69029a8d0cc9_Untitled_Diagram.drawio.png', 'active', '2025-10-29 21:17:40', '2025-10-29 22:51:57'),
(27, 'testing', 'https://gold-lion-549609.hostingersite.com/uploads/general/69066ef537c6_exam_permit_2025001__6_.pdf', 'active', '2025-11-01 20:35:04', '2025-11-01 20:35:04'),
(28, 'Testing', 'https://gold-lion-549609.hostingersite.com/uploads/general/6907bd2b2631_applicants_export_cycle_1_2025-10-30_21-40-58.xlsx', 'active', '2025-11-02 20:20:59', '2025-11-02 20:20:59'),
(29, 'exam_permit_2025001 (8)', 'http://localhost/STUDENT SUCCESS OFFICE - api/uploads/general/6908badb77d2_exam_permit_2025001__8_.pdf', 'active', '2025-11-03 14:23:25', '2025-11-03 14:23:25');

-- --------------------------------------------------------

--
-- Table structure for table `msg_config`
--

CREATE TABLE `msg_config` (
  `id` int(11) NOT NULL,
  `name` varchar(250) NOT NULL,
  `value` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_active` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `msg_config`
--

INSERT INTO `msg_config` (`id`, `name`, `value`, `created_at`, `is_active`) VALUES
(1, 'REQUEST_METHOD_POST', 'To interact with this endpoint, be sure to send a POST request — other methods aren’t supported.', '2025-10-14 17:33:41', 1),
(2, 'EMPTY_EMAIL_PASS', 'To continue, please make sure you’ve entered both your email address and password.', '2025-10-14 18:05:54', 1),
(3, 'INVALID_EMAIL', 'Hmm... that doesn\'t look like a valid email address.', '2025-10-14 18:09:54', 1),
(4, 'ALREADY_REGISTERED', 'Looks like this email is already registered. Try logging in instead.', '2025-10-14 18:11:42', 1),
(5, 'REGISTER_SUCCESS', 'Your account has been created successfully! Please check your email for a verification link.', '2025-10-14 18:28:55', 1),
(6, 'INVALID_VERIFY_LINK', 'Oops — it looks like your verification link isn’t valid anymore. Try requesting a new link to verify your account.', '2025-10-14 20:12:58', 1),
(7, 'EXPIRED_VERIFY_LINK', 'This verification link has expired for security reasons. Please request a new link to verify your account.', '2025-10-14 20:19:53', 1),
(8, 'ALREADY_VERIFIED', 'It looks like your account has already been verified. If you’re having trouble signing in, try resetting your password or contacting support.', '2025-10-14 20:21:38', 1),
(9, 'USER_NOT_FOUND', 'We couldn’t find an account with this verification link. Please check your information and try again.', '2025-10-14 20:23:00', 1),
(10, 'NOT_REGISTER', 'We couldn’t find an account with that email address. Please check and try again.', '2025-10-14 20:31:15', 1),
(11, 'NOT_VERIFIED_ACCOUNT', 'Your account hasn’t been verified yet. Please check your inbox (and spam folder) for the verification link.', '2025-10-14 20:35:36', 1),
(12, 'ACCOUNT_BANNED_DELETED', 'It looks like your account has been deactivated or suspended. If this is unexpected, please contact our support team to restore access.', '2025-10-14 21:00:37', 1),
(13, 'INVALID_PASSWORD', 'Login unsuccessful — the password entered is incorrect. You can try again or reset your password to regain access.', '2025-10-15 07:50:24', 1),
(14, 'LOGIN_SUCCESS', 'Login successful! Your account is now active and ready to use.', '2025-10-15 07:52:58', 1),
(15, 'VERIFICATION_ACCOUNT_LINK_SUCCESS', 'A new verification link has been sent to your email address. Please check your inbox to continue.', '2025-10-15 09:20:56', 1),
(16, 'VERIFICATION_ACCOUNT_LINK_FAILED', 'We couldn’t send the verification link due to a system issue or invalid email address. Please double-check your information and try again later. If the issue persists, contact support.', '2025-10-15 09:21:33', 1),
(17, 'REACTIVATION_ACCOUNT_SUCCESS', 'A reactivation link has been sent to your email. Please check your inbox to reactivate your account.', '2025-10-15 10:44:25', 1),
(18, 'REACTIVATION_ACCOUNT_FAILED', 'Something went wrong while activation your account. Please try again.', '2025-10-15 10:45:31', 1),
(19, 'RESET_PASSWORD_SEND_SUCCESS', 'A password reset link has been sent to your email. Please check your inbox and reset your password within 24 hours to complete the process.', '2025-10-15 13:20:45', 1),
(20, 'RESET_PASSWORD_SEND_FAILED', 'We couldn’t send the password reset link due to a system issue or invalid email address. Please double-check your information and try again later. If the issue persists, contact support.', '2025-10-15 13:21:03', 1),
(21, 'RESET_PASSWORD_LINK_INVALID', 'We couldn’t find an account with this reset password link. Please check your reset password link and try again.', '2025-10-15 14:23:48', 1),
(22, 'RESET_PASSWORD_LINK_EXPIRED', 'This reset password link has expired for security reasons. Please request a new link to verify your account.', '2025-10-15 14:24:32', 1),
(23, 'CHANGE_PASSWORD_SUCCESS', 'Your password has been updated successfully! You can now log in with your new password.', '2025-10-15 14:59:00', 1),
(24, 'CHANGE_PASSWORD_FAILED', 'Hmm, we couldn\'t update your password. Please try again in a few moments.', '2025-10-15 14:59:49', 1),
(25, 'OTP_SEND_FAILED', 'We couldn’t send the OTP CODE due to a system issue or invalid email address. Please double-check your information and try again later. If the issue persists, contact support.', '2025-10-15 20:59:11', 1),
(26, 'OTP_SEND_SUCCESS', 'We’ve sent a 6-digit code to your registered email. Please check your inbox and enter the code to continue.', '2025-10-15 21:00:05', 1),
(27, 'INVALID_OTP', 'Hmm... that doesn\'t look like a valid OTP.', '2025-10-17 10:09:11', 1),
(28, 'OTP_ALREADY_USED', 'This verification code has already been used. Please request a new code by clicking <b>Resend OTP Code</b>.', '2025-10-17 11:11:56', 1),
(29, 'OTP_ALREADY_USED', 'This verification code has already been used. Please request a new code by clicking <b>Resend OTP Code</b>.', '2025-10-17 11:12:58', 1),
(30, 'OTP_EXPIRED', 'Your verification code has expired. Please request a new code by clicking <b>Resend OTP Code</b> and use the updated code sent to your email to proceed.', '2025-10-17 11:12:58', 1),
(31, 'OTP_VERIFICATION_SUCCESS', 'Verification successful! You can now proceed with your login.', '2025-10-17 11:12:58', 1),
(32, 'OTP_UPDATE_ERROR', 'An error occurred while processing your request. Please try again.', '2025-10-17 11:12:58', 1),
(33, 'OTP_INVALID_CODE', 'It looks like the code you entered is incorrect. Please double-check the code sent to your email and try again.<br><br>Click <b>Try Again</b> to re-enter your code, or click <b>Resend OTP Code</b> to request a new one.', '2025-10-17 11:12:58', 1),
(34, 'OTP_NOT_FOUND', 'No verification code found for this account. Please request a new code by clicking <b>Resend OTP Code</b>.', '2025-10-17 11:12:58', 1),
(35, 'LOGIN_ERROR', 'We couldn’t login your account due to a system issue. Please double-check your information and try again later. If the issue persists, contact support.', '2025-10-15 07:52:58', 1),
(36, 'SESSION_EXPIRED', 'Your session has expired or you’re not logged in. Please sign in to regain access.', '2025-10-17 20:15:16', 1),
(37, 'UNAUTHENTICATED', 'Authentication required. Please log in to access this resource.', '2025-10-17 20:20:29', 1),
(38, 'INVALID_FIRST_NAME', 'To continue, please make sure you’ve entered a valid first name.', '2025-10-19 20:51:46', 1),
(39, 'INVALID_LAST_NAME', 'To continue, please make sure you’ve entered a valid last name.', '2025-10-19 20:52:03', 1),
(40, 'SET_PROFILE_FAILED', 'We couldn’t update your profile at this time. Please check your input and try again. If the issue persists, contact support for help.', '2025-10-19 21:17:55', 1),
(41, 'SET_PROFILE_SUCCESS', 'Your profile has been set up successfully.', '2025-10-19 21:18:04', 1),
(42, 'INVALID_APPLICATION_TYPE', 'The provided application type is invalid or not supported.', '2025-10-21 11:36:08', 1);

-- --------------------------------------------------------

--
-- Table structure for table `otp_user`
--

CREATE TABLE `otp_user` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `value` text NOT NULL,
  `is_used` tinyint(1) DEFAULT 0,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `otp_user`
--

INSERT INTO `otp_user` (`id`, `user_id`, `value`, `is_used`, `expires_at`, `created_at`) VALUES
(1, 1, '450430', 0, '2025-11-03 07:13:47', '2025-10-17 18:49:05');

-- --------------------------------------------------------

--
-- Table structure for table `remark_templates`
--

CREATE TABLE `remark_templates` (
  `id` int(11) NOT NULL,
  `remark_text` text NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `remark_templates`
--

INSERT INTO `remark_templates` (`id`, `remark_text`, `is_active`) VALUES
(1, 'Missing required document: [Document Name]', 1),
(2, 'Information provided is unclear. Please clarify: [Specific Detail]', 1),
(3, 'Application approved pending final review.', 1),
(4, 'Application does not meet minimum requirements.', 1),
(5, 'Duplicate submission detected.', 1);

-- --------------------------------------------------------

--
-- Table structure for table `requirements_uploads`
--

CREATE TABLE `requirements_uploads` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `file_url` varchar(1024) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `date_upload` datetime NOT NULL DEFAULT current_timestamp(),
  `date_modified` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `requirements_uploads`
--

INSERT INTO `requirements_uploads` (`id`, `user_id`, `file_url`, `title`, `status`, `date_upload`, `date_modified`) VALUES
(1, 1, 'https://gold-lion-549609.hostingersite.com/uploads/requirements/6907e3c2267d_ENDORSEMENT_LETTER-1.pdf', 'ENDORSEMENT_LETTER-1.pdf', 'active', '2025-11-02 23:05:38', '2025-11-02 23:05:38'),
(2, 1, 'https://gold-lion-549609.hostingersite.com/uploads/requirements/6907e3fb5ad5_applicants_export_cycle_1_2025-10-30_21-40-58.xlsx', 'helloo', 'active', '2025-11-02 23:06:35', '2025-11-02 23:06:35'),
(3, 1, 'https://gold-lion-549609.hostingersite.com/uploads/requirements/6907e41a890e_ENDORSEMENT_LETTER-1.pdf', 'ENDORSEMENT_LETTER-1.pdf', 'active', '2025-11-02 23:07:06', '2025-11-02 23:07:06'),
(4, 1, 'https://gold-lion-549609.hostingersite.com/uploads/requirements/u337253893_PLPasigSSO (1).sql', 'u337253893_PLPasigSSO (1).sql', 'active', '2025-11-02 23:07:55', '2025-11-03 00:44:08'),
(5, 1, 'http://localhost/STUDENT SUCCESS OFFICE - api/uploads/requirements/6907f5d4bfc4_things_i_wanted_to_say_but_never_did.png', 'testtt', 'active', '2025-11-03 00:22:47', '2025-11-03 00:22:47'),
(6, 1, 'https://google.com/uploads/requirements/applicants_export_cycle_1_2025-10-30_21-40-58.xls', 'applicants_export_cycle_1_2025-10-30_21-40-58.xls', 'active', '2025-11-03 00:25:08', '2025-11-03 00:25:25'),
(7, 1, 'https://gold-lion-549609.hostingersite.com/uploads/requirements/690937ca8b74_Letter-of-Intent.pdf', 'Letter-of-Intent.pdf', 'active', '2025-11-03 23:16:26', '2025-11-03 23:16:26');

-- --------------------------------------------------------

--
-- Table structure for table `services_answers`
--

CREATE TABLE `services_answers` (
  `answer_id` int(11) NOT NULL,
  `request_id` int(11) NOT NULL,
  `field_id` int(11) NOT NULL,
  `answer_value` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `services_answers`
--

INSERT INTO `services_answers` (`answer_id`, `request_id`, `field_id`, `answer_value`) VALUES
(1, 1, 2, 'http://localhost/STUDENT SUCCESS OFFICE - SERVICES/api/../uploads/service_requests/690931b6521ff-thingsiwantedtosaybutneverdid.png'),
(2, 1, 1, 'Male'),
(3, 2, 2, 'http://localhost/STUDENT SUCCESS OFFICE - SERVICES/uploads/service_requests/690932b567469-AWS_Practice_1.png'),
(4, 2, 1, 'Male'),
(5, 3, 2, 'http://localhost/STUDENT SUCCESS OFFICE - SERVICES/uploads/service_requests/690933c1b4c92-thingsiwantedtosaybutneverdid.png'),
(6, 3, 1, 'Female'),
(7, 4, 2, 'http://localhost/STUDENT SUCCESS OFFICE - SERVICES/uploads/service_requests/req_20251105_021841_2_91dafe7b.pdf'),
(8, 4, 3, 'Romeo John'),
(9, 4, 4, 'Romeo John'),
(10, 4, 5, 'Romeo John'),
(11, 4, 1, 'Female'),
(38, 3, 3, 'Romeo John'),
(39, 3, 4, 'Romeo John'),
(40, 3, 5, 'Romeo John'),
(41, 5, 2, 'http://localhost/STUDENT SUCCESS OFFICE - SERVICES/uploads/service_requests/690db3efc41f5-thingsiwantedtosaybutneverdid.png'),
(42, 5, 3, 'ewq'),
(43, 5, 4, 'wqqw'),
(44, 5, 5, 'wqeqw'),
(45, 5, 1, 'Male'),
(46, 6, 9, 'No'),
(47, 7, 9, 'Yes'),
(48, 7, 10, 'Pinagbuhatan'),
(49, 8, 9, 'Yes'),
(50, 8, 10, 'Pinagbuhatan'),
(51, 8, 11, '21'),
(52, 8, 12, 'adorromeojohn0105@gmail.com'),
(53, 9, 13, 'http://localhost/STUDENT SUCCESS OFFICE - SERVICES/uploads/service_requests/690e8de521628-expiration_config.sql'),
(54, 9, 9, 'Yes'),
(55, 9, 10, 'Pinagbuhatan'),
(56, 9, 11, '21'),
(57, 9, 12, 'adorromeojohn0105@gmail.com');

-- --------------------------------------------------------

--
-- Table structure for table `services_email_otp_codes`
--

CREATE TABLE `services_email_otp_codes` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `six_digit` varchar(50) NOT NULL,
  `purpose` enum('login','register') NOT NULL DEFAULT 'login',
  `sent_to` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL,
  `consumed_at` datetime DEFAULT NULL,
  `attempts` int(11) NOT NULL DEFAULT 0,
  `max_attempts` int(11) NOT NULL DEFAULT 5,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `services_email_otp_codes`
--

INSERT INTO `services_email_otp_codes` (`id`, `user_id`, `six_digit`, `purpose`, `sent_to`, `expires_at`, `consumed_at`, `attempts`, `max_attempts`, `created_at`) VALUES
(13, 14, '367239', 'register', 'adorromeojohn0105@gmail.com', '2025-11-05 04:39:19', '2025-11-03 20:39:54', 0, 5, '2025-11-03 20:18:12');

-- --------------------------------------------------------

--
-- Table structure for table `services_fields`
--

CREATE TABLE `services_fields` (
  `field_id` int(11) NOT NULL,
  `service_id` int(11) NOT NULL,
  `label` varchar(255) NOT NULL,
  `field_type` varchar(250) NOT NULL,
  `is_required` tinyint(1) NOT NULL DEFAULT 1,
  `display_order` int(11) NOT NULL,
  `allowed_file_types` varchar(255) DEFAULT NULL,
  `visible_when_value` varchar(255) DEFAULT NULL,
  `visible_when_option_id` int(11) DEFAULT NULL,
  `max_file_size_mb` int(11) DEFAULT NULL
) ;

--
-- Dumping data for table `services_fields`
--

INSERT INTO `services_fields` (`field_id`, `service_id`, `label`, `field_type`, `is_required`, `display_order`, `allowed_file_types`, `visible_when_value`, `visible_when_option_id`, `max_file_size_mb`) VALUES
(1, 1, 'Gender', 'select', 1, 4, NULL, NULL, NULL, NULL),
(2, 1, 'Student ID', 'file', 1, 2, '.png', NULL, NULL, NULL),
(3, 1, 'First Name', 'text', 1, 1, NULL, NULL, NULL, NULL),
(4, 1, 'Middle Name', 'text', 0, 2, NULL, NULL, NULL, NULL),
(5, 1, 'Last Name', 'text', 1, 3, NULL, NULL, NULL, NULL),
(6, 1, 'Are you a person with disability?', 'textarea', 1, 3, NULL, NULL, NULL, NULL),
(7, 1, 'Are you a person with disability?', 'radio', 1, 4, NULL, NULL, NULL, NULL),
(8, 1, 'WORD', 'file', 1, 5, '.docs', NULL, NULL, NULL),
(9, 2, 'I am pasig Resident', 'radio', 1, 1, NULL, NULL, NULL, NULL),
(10, 2, 'Barangay', 'select', 1, 1, NULL, NULL, 19, NULL),
(11, 2, 'Age', 'number', 1, 2, NULL, NULL, NULL, NULL),
(12, 2, 'Email Address', 'email', 1, 3, NULL, NULL, NULL, NULL),
(13, 2, 'Valid ID', 'file', 1, 4, NULL, NULL, NULL, 3);

-- --------------------------------------------------------

--
-- Table structure for table `services_field_options`
--

CREATE TABLE `services_field_options` (
  `option_id` int(11) NOT NULL,
  `field_id` int(11) NOT NULL,
  `option_label` varchar(255) NOT NULL,
  `option_value` varchar(255) NOT NULL,
  `display_order` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `services_field_options`
--

INSERT INTO `services_field_options` (`option_id`, `field_id`, `option_label`, `option_value`, `display_order`) VALUES
(1, 1, 'Male', 'Male', 1),
(2, 1, 'Female', 'Female', 2),
(11, 1, 'LGBTQ+', 'LGBTQ+', 3),
(12, 1, 'Other', 'Other', 4),
(13, 7, 'Yes', 'Yes', 1),
(16, 7, 'No', 'No', 2),
(18, 10, 'Pinagbuhatan', 'Pinagbuhatan', 1),
(19, 9, 'Yes', 'Yes', 1),
(20, 9, 'No', 'No', 2);

-- --------------------------------------------------------

--
-- Table structure for table `services_list`
--

CREATE TABLE `services_list` (
  `service_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `icon` text DEFAULT NULL,
  `button_text` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `services_list`
--

INSERT INTO `services_list` (`service_id`, `name`, `description`, `icon`, `button_text`, `is_active`) VALUES
(1, 'Good Moral Request', 'A formal request to obtain a certification confirming an individual\'s good moral character and ethical conduct.', '<svg xmlns=\"http://www.w3.org/2000/svg\" width=\"24\" height=\"24\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2\" stroke-linecap=\"round\" stroke-linejoin=\"round\" class=\"lucide lucide-file-text-icon lucide-file-text\"><path d=\"M6 22a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h8a2.4 2.4 0 0 1 1.704.706l3.588 3.588A2.4 2.4 0 0 1 20 8v12a2 2 0 0 1-2 2z\"/><path d=\"M14 2v5a1 1 0 0 0 1 1h5\"/><path d=\"M10 9H8\"/><path d=\"M16 13H8\"/><path d=\"M16 17H8\"/></svg>', 'Request', 1),
(2, 'ID Replacement', 'ID Replacement is the process of issuing a new identification card to replace one that has been lost, stolen, damaged, or contains outdated information.', '<svg xmlns=\"http://www.w3.org/2000/svg\" width=\"24\" height=\"24\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2\" stroke-linecap=\"round\" stroke-linejoin=\"round\" class=\"lucide lucide-id-card-lanyard-icon lucide-id-card-lanyard\"><path d=\"M13.5 8h-3\"/><path d=\"m15 2-1 2h3a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h3\"/><path d=\"M16.899 22A5 5 0 0 0 7.1 22\"/><path d=\"m9 2 3 6\"/><circle cx=\"12\" cy=\"15\" r=\"3\"/></svg>', 'Request', 1),
(3, 'Library Card Replacement', 'If your library card is lost, stolen, or damaged, you can request a replacement at the library’s front desk. A small replacement fee may apply, and valid identification is required to issue a new card.', '<svg xmlns=\"http://www.w3.org/2000/svg\" width=\"24\" height=\"24\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2\" stroke-linecap=\"round\" stroke-linejoin=\"round\" class=\"lucide lucide-id-card-icon lucide-id-card\"><path d=\"M16 10h2\"/><path d=\"M16 14h2\"/><path d=\"M6.17 15a3 3 0 0 1 5.66 0\"/><circle cx=\"9\" cy=\"11\" r=\"2\"/><rect x=\"2\" y=\"5\" width=\"20\" height=\"14\" rx=\"2\"/></svg>', 'Request', 1);

-- --------------------------------------------------------

--
-- Table structure for table `services_requests`
--

CREATE TABLE `services_requests` (
  `request_id` int(11) NOT NULL,
  `service_id` int(11) NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `status_id` int(11) NOT NULL DEFAULT 1,
  `admin_remarks` text DEFAULT NULL,
  `can_update` tinyint(1) NOT NULL DEFAULT 0,
  `requested_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `services_requests`
--

INSERT INTO `services_requests` (`request_id`, `service_id`, `user_id`, `status_id`, `admin_remarks`, `can_update`, `requested_at`) VALUES
(1, 1, 14, 1, NULL, 0, '2025-11-03 22:50:30'),
(2, 1, 14, 1, NULL, 0, '2025-11-03 22:54:46'),
(3, 1, 14, 1, NULL, 0, '2025-11-03 22:59:14'),
(4, 1, 14, 1, NULL, 0, '2025-11-04 17:40:22'),
(5, 1, 14, 1, NULL, 0, '2025-11-07 08:55:12'),
(6, 2, 14, 1, NULL, 0, '2025-11-07 23:11:23'),
(7, 2, 14, 1, NULL, 0, '2025-11-07 23:11:40'),
(8, 2, 14, 1, NULL, 0, '2025-11-07 23:54:43'),
(9, 2, 14, 1, NULL, 0, '2025-11-08 00:25:08');

-- --------------------------------------------------------

--
-- Table structure for table `services_request_statuses`
--

CREATE TABLE `services_request_statuses` (
  `status_id` int(11) NOT NULL,
  `status_name` varchar(100) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `color_hex` varchar(7) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `services_request_statuses`
--

INSERT INTO `services_request_statuses` (`status_id`, `status_name`, `description`, `color_hex`) VALUES
(1, 'Pending', 'Waiting for admin review.', '#FFC107'),
(2, 'In Progress', 'Your request is being processed.', '#007BFF'),
(3, 'Completed', 'Your request is complete.', '#28A745'),
(4, 'Rejected', 'Your request was rejected.', '#DC3545'),
(5, 'Needs Resubmission', 'Missing information or files.', '#FD7E14');

-- --------------------------------------------------------

--
-- Table structure for table `services_users`
--

CREATE TABLE `services_users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `email` varchar(255) NOT NULL,
  `first_name` varchar(100) DEFAULT NULL,
  `middle_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `suffix` varchar(20) DEFAULT NULL,
  `password_hash` varchar(255) DEFAULT NULL,
  `email_verified` tinyint(1) NOT NULL DEFAULT 0,
  `email_verified_at` datetime DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `last_login_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `services_users`
--

INSERT INTO `services_users` (`id`, `email`, `first_name`, `middle_name`, `last_name`, `suffix`, `password_hash`, `email_verified`, `email_verified_at`, `is_active`, `last_login_at`, `created_at`, `updated_at`) VALUES
(14, 'adorromeojohn0105@gmail.com', NULL, NULL, NULL, NULL, '$2y$10$jxIp7IwC12X524QDOuGu8.5zELOqNynBOOC9qXbFA0b66RtDcq92u', 1, '2025-11-03 20:39:54', 1, '2025-11-07 20:59:56', '2025-11-03 20:18:11', '2025-11-07 20:59:56');

-- --------------------------------------------------------

--
-- Table structure for table `smtp_config`
--

CREATE TABLE `smtp_config` (
  `id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `address` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'inactive'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `smtp_config`
--

INSERT INTO `smtp_config` (`id`, `username`, `password`, `address`, `name`, `status`) VALUES
(1, 'plpasig.sso@gmail.com', 'npla ugmc iafq mvaf', 'smtp.gmail.com', 'Pamantasan ng Lungsod ng Pasig - Student Success Office', 'active');

-- --------------------------------------------------------

--
-- Table structure for table `sso_officers`
--

CREATE TABLE `sso_officers` (
  `id` int(11) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `middle_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) NOT NULL,
  `suffix` varchar(50) DEFAULT NULL,
  `title` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `profile_image_url` varchar(255) DEFAULT NULL,
  `status` enum('active','archived') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sso_officers`
--

INSERT INTO `sso_officers` (`id`, `first_name`, `middle_name`, `last_name`, `suffix`, `title`, `email`, `profile_image_url`, `status`) VALUES
(1, 'Arlene', NULL, 'Daniel', NULL, 'Student Success Office Director', 'sample@plpasig.edu.ph', NULL, 'active');

-- --------------------------------------------------------

--
-- Table structure for table `sso_user`
--

CREATE TABLE `sso_user` (
  `id` int(11) NOT NULL,
  `first_name` varchar(250) NOT NULL,
  `middle_name` varchar(250) DEFAULT NULL,
  `last_name` varchar(250) NOT NULL,
  `suffix` varchar(250) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `status` enum('active','archived') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sso_user`
--

INSERT INTO `sso_user` (`id`, `first_name`, `middle_name`, `last_name`, `suffix`, `email`, `password`, `status`, `created_at`) VALUES
(1, 'ROMEO JOHN', NULL, 'ADOR', NULL, 'testuser@example.com', 'some_password_hash', 'active', '2025-10-23 19:57:01'),
(2, 'ROMEO JOHN', NULL, 'ADOR', NULL, 'admin@sso.edu', '$2y$10$0RtgqYinB4p5bHMB7dKgVOipANdaYevQbrX.OUDqIN6IPsvtVIJe2', 'active', '2025-10-26 21:33:22'),
(3, '', NULL, '', NULL, 'admin@sso.edu.ph', '$2y$10$gsh6vf8hlqBdU4T.Ao.Mpuv1xWcMDV/jGobuXwpSgpafqn5L8WL1u', 'active', '2025-10-27 07:43:37'),
(4, 'John', 'Michael', 'Doe', 'Jr.', 'admin@test.com', '$2y$10$BuOzHpV3B8Ran5YlHL7O4.PA23HydDsJSiMN1HUawyX2UCt.i2EwO', 'active', '2025-10-27 13:43:56');

-- --------------------------------------------------------

--
-- Table structure for table `statuses`
--

CREATE TABLE `statuses` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL COMMENT 'e.g., Pending, Accepted',
  `hex_color` varchar(250) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `statuses`
--

INSERT INTO `statuses` (`id`, `name`, `hex_color`) VALUES
(1, 'Pending', '#FACC15'),
(2, 'In Review', '#3B82F6'),
(3, 'Waitlisted', '#FB923C'),
(4, 'Examination', '#FACC15'),
(5, 'Rejected', '#EF4444');

-- --------------------------------------------------------

--
-- Table structure for table `submissions`
--

CREATE TABLE `submissions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `applicant_type_id` int(11) NOT NULL,
  `submitted_at` datetime NOT NULL DEFAULT current_timestamp(),
  `status` varchar(50) NOT NULL DEFAULT 'Pending',
  `remarks` text DEFAULT 'Thank you for your submission. Your application is still being processed, and additional time is required to complete the review.'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `submissions`
--

INSERT INTO `submissions` (`id`, `user_id`, `applicant_type_id`, `submitted_at`, `status`, `remarks`) VALUES
(1, 1, 1, '2025-11-02 23:03:45', 'Waitlisted', 'Information provided is unclear. Please clarify: [Specific Detail]'),
(2, 1, 1, '2025-11-02 23:05:40', 'Waitlisted', 'Information provided is unclear. Please clarify: [Specific Detail]'),
(3, 1, 1, '2025-11-02 23:07:52', 'Rejected', ''),
(4, 1, 1, '2025-11-03 23:16:19', 'Pending', '');

-- --------------------------------------------------------

--
-- Table structure for table `submission_data`
--

CREATE TABLE `submission_data` (
  `id` int(11) NOT NULL,
  `submission_id` int(11) NOT NULL,
  `field_name` varchar(255) NOT NULL,
  `field_value` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `submission_data`
--

INSERT INTO `submission_data` (`id`, `submission_id`, `field_name`, `field_value`) VALUES
(1, 3, 'first_name', 'Mark Andrie'),
(2, 3, 'middle_name', ''),
(3, 3, 'last_name', 'Datus'),
(4, 3, 'suffix', ''),
(5, 4, 'first_name', 'hhh'),
(6, 4, 'middle_name', 'DALISAY'),
(7, 4, 'last_name', 'DATUS'),
(8, 4, 'suffix', '');

-- --------------------------------------------------------

--
-- Table structure for table `submission_files`
--

CREATE TABLE `submission_files` (
  `id` int(11) NOT NULL,
  `submission_id` int(11) NOT NULL,
  `field_name` varchar(255) NOT NULL,
  `original_filename` varchar(255) NOT NULL,
  `file_path` varchar(512) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `submission_files`
--

INSERT INTO `submission_files` (`id`, `submission_id`, `field_name`, `original_filename`, `file_path`) VALUES
(1, 3, 'student_valid_id', 'u337253893_PLPasigSSO (1).sql', 'https://gold-lion-549609.hostingersite.com/uploads/requirements/u337253893_PLPasigSSO (1).sql'),
(2, 4, 'student_valid_id', 'Letter-of-Intent.pdf', 'https://gold-lion-549609.hostingersite.com/uploads/requirements/690937ca8b74_Letter-of-Intent.pdf');

-- --------------------------------------------------------

--
-- Table structure for table `tokenization`
--

CREATE TABLE `tokenization` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `name` varchar(250) NOT NULL,
  `value` text NOT NULL,
  `is_used` tinyint(1) DEFAULT 0,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tokenization`
--

INSERT INTO `tokenization` (`id`, `user_id`, `name`, `value`, `is_used`, `expires_at`, `created_at`) VALUES
(1, 1, 'VERIFY_ACCOUNT', 'c995ffe3064f9defbaebceb941aafe4239318fc1ef26e90b866b1cfcfeac3803', 1, '2025-10-18 16:11:24', '2025-10-17 16:10:01'),
(2, 1, 'RESET_PASSWORD', '2070763c96b1b1a7ac478c0e47df97724163f3690f8785a41408b633d264b8f1', 0, '2025-11-17 18:48:00', '2025-10-17 18:40:04'),
(3, 1, 'SESSION', 'dfd9f3aa0b1a26d9ed53b8617c6c37de5410b9c175bbb819f62dc4837ffe740c', 0, '2025-11-14 07:31:52', '2025-10-17 20:26:02'),
(4, 2, 'VERIFY_ACCOUNT', 'ad31fffc6cff947fc9b46aebdf9e5fe137fa1832bcb11f56e81e827f9e316fd5', 0, '2025-11-04 07:08:19', '2025-11-03 04:31:29'),
(5, 3, 'VERIFY_ACCOUNT', 'de90fc3d8ae607da87b973e833ff05dd04ea99d134733f8458afbd443ade9cc3', 0, '2025-11-08 20:35:27', '2025-11-07 20:34:12');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) DEFAULT NULL,
  `role` enum('student','applicant','sso','admin') NOT NULL,
  `acc_type` enum('admission','main') NOT NULL,
  `acc_status` enum('not_verified','banned','deleted','active','deactivated','locked') DEFAULT 'not_verified',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `email`, `password_hash`, `role`, `acc_type`, `acc_status`, `created_at`, `updated_at`) VALUES
(1, 'Mc+Mcxig2rdxnixLZkgbFUZib24yTStwUThXRHNGcklrd0w1MklLUFJCL2IrVENzc0h6a2hodVVyM2M9', '$2y$10$qqkbJr8jYNemgO.L1poJAudVVatRG1J7AU7lFmGN/49VenmXC6EPm', 'applicant', 'admission', 'active', '2025-10-17 16:10:01', '2025-10-17 16:11:37'),
(2, 'zmsCoQ2CE3SGf2BbhhvtW3k4ZUczbTg0WHI1bXA1TUdmSzZGbDB1L1ptZExPaEF6TFo4cFF0OWs1S0E9', '$2y$10$vs7pye8l7AWgAXQjBBLegOTJBrFFaXjS9nMoBSu8XtigsektxtSKy', 'applicant', 'admission', 'not_verified', '2025-11-03 04:31:27', '2025-11-03 04:31:27'),
(3, 'BwkLzX0Of7DS7MNTUKo06nhkOHprcy9aTm5RQzV6UzNGV1lzdEY5U2FweFE3NW9BaXZaNEtzb3FwdmM9', '$2y$10$SPl5OAHoBxu916gu/alTbeHQn8GuOgGWrvYSKd/EyqrQlu4JXTo2m', 'applicant', 'admission', 'not_verified', '2025-11-07 20:34:11', '2025-11-07 20:34:11');

-- --------------------------------------------------------

--
-- Table structure for table `user_fullname`
--

CREATE TABLE `user_fullname` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `first_name` varchar(250) NOT NULL,
  `middle_name` varchar(250) DEFAULT NULL,
  `last_name` varchar(250) NOT NULL,
  `suffix` varchar(250) DEFAULT NULL,
  `last_updated` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_fullname`
--

INSERT INTO `user_fullname` (`id`, `user_id`, `first_name`, `middle_name`, `last_name`, `suffix`, `last_updated`) VALUES
(1, 1, 'Romeo John', '', 'Ador', '', '2025-10-19 21:58:51');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `acc_locking`
--
ALTER TABLE `acc_locking`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `admission_cycles`
--
ALTER TABLE `admission_cycles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_archived` (`is_archived`);

--
-- Indexes for table `admission_submission`
--
ALTER TABLE `admission_submission`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `api_list`
--
ALTER TABLE `api_list`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `applicant_number_prefix`
--
ALTER TABLE `applicant_number_prefix`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `applicant_types`
--
ALTER TABLE `applicant_types`
  ADD PRIMARY KEY (`id`),
  ADD KEY `admission_cycle_id` (`admission_cycle_id`),
  ADD KEY `idx_archived` (`is_archived`);

--
-- Indexes for table `application_permit`
--
ALTER TABLE `application_permit`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_application_user` (`user_id`);

--
-- Indexes for table `contact_support`
--
ALTER TABLE `contact_support`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `email_template`
--
ALTER TABLE `email_template`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ExamRegistrations`
--
ALTER TABLE `ExamRegistrations`
  ADD PRIMARY KEY (`registration_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `schedule_id` (`schedule_id`);

--
-- Indexes for table `ExamSchedules`
--
ALTER TABLE `ExamSchedules`
  ADD PRIMARY KEY (`schedule_id`);

--
-- Indexes for table `expiration_config`
--
ALTER TABLE `expiration_config`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `type` (`type`);

--
-- Indexes for table `faqs`
--
ALTER TABLE `faqs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `form_fields`
--
ALTER TABLE `form_fields`
  ADD PRIMARY KEY (`id`),
  ADD KEY `step_id` (`step_id`),
  ADD KEY `idx_archived` (`is_archived`);

--
-- Indexes for table `form_field_options`
--
ALTER TABLE `form_field_options`
  ADD PRIMARY KEY (`id`),
  ADD KEY `field_id` (`field_id`);

--
-- Indexes for table `form_steps`
--
ALTER TABLE `form_steps`
  ADD PRIMARY KEY (`id`),
  ADD KEY `applicant_type_id` (`applicant_type_id`),
  ADD KEY `idx_archived` (`is_archived`);

--
-- Indexes for table `general_uploads`
--
ALTER TABLE `general_uploads`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `msg_config`
--
ALTER TABLE `msg_config`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `otp_user`
--
ALTER TABLE `otp_user`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `remark_templates`
--
ALTER TABLE `remark_templates`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `requirements_uploads`
--
ALTER TABLE `requirements_uploads`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `services_answers`
--
ALTER TABLE `services_answers`
  ADD PRIMARY KEY (`answer_id`),
  ADD KEY `request_id` (`request_id`),
  ADD KEY `field_id` (`field_id`);

--
-- Indexes for table `services_email_otp_codes`
--
ALTER TABLE `services_email_otp_codes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_email_otp_code` (`six_digit`),
  ADD KEY `idx_email_otp_user` (`user_id`);

--
-- Indexes for table `services_fields`
--
ALTER TABLE `services_fields`
  ADD PRIMARY KEY (`field_id`),
  ADD KEY `service_id` (`service_id`),
  ADD KEY `fk_visible_option` (`visible_when_option_id`);

--
-- Indexes for table `services_field_options`
--
ALTER TABLE `services_field_options`
  ADD PRIMARY KEY (`option_id`),
  ADD KEY `field_id` (`field_id`);

--
-- Indexes for table `services_list`
--
ALTER TABLE `services_list`
  ADD PRIMARY KEY (`service_id`);

--
-- Indexes for table `services_requests`
--
ALTER TABLE `services_requests`
  ADD PRIMARY KEY (`request_id`),
  ADD KEY `service_id` (`service_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `status_id` (`status_id`);

--
-- Indexes for table `services_request_statuses`
--
ALTER TABLE `services_request_statuses`
  ADD PRIMARY KEY (`status_id`),
  ADD UNIQUE KEY `status_name` (`status_name`);

--
-- Indexes for table `services_users`
--
ALTER TABLE `services_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_users_email` (`email`);

--
-- Indexes for table `smtp_config`
--
ALTER TABLE `smtp_config`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sso_officers`
--
ALTER TABLE `sso_officers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sso_user`
--
ALTER TABLE `sso_user`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `statuses`
--
ALTER TABLE `statuses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `submissions`
--
ALTER TABLE `submissions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `applicant_type_id` (`applicant_type_id`);

--
-- Indexes for table `submission_data`
--
ALTER TABLE `submission_data`
  ADD PRIMARY KEY (`id`),
  ADD KEY `submission_id` (`submission_id`);

--
-- Indexes for table `submission_files`
--
ALTER TABLE `submission_files`
  ADD PRIMARY KEY (`id`),
  ADD KEY `submission_id` (`submission_id`);

--
-- Indexes for table `tokenization`
--
ALTER TABLE `tokenization`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `user_fullname`
--
ALTER TABLE `user_fullname`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `acc_locking`
--
ALTER TABLE `acc_locking`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `admission_cycles`
--
ALTER TABLE `admission_cycles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `admission_submission`
--
ALTER TABLE `admission_submission`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `api_list`
--
ALTER TABLE `api_list`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `applicant_number_prefix`
--
ALTER TABLE `applicant_number_prefix`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `applicant_types`
--
ALTER TABLE `applicant_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `application_permit`
--
ALTER TABLE `application_permit`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `contact_support`
--
ALTER TABLE `contact_support`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `email_template`
--
ALTER TABLE `email_template`
  MODIFY `id` int(50) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `expiration_config`
--
ALTER TABLE `expiration_config`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `faqs`
--
ALTER TABLE `faqs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `form_fields`
--
ALTER TABLE `form_fields`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `form_field_options`
--
ALTER TABLE `form_field_options`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `form_steps`
--
ALTER TABLE `form_steps`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `general_uploads`
--
ALTER TABLE `general_uploads`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `msg_config`
--
ALTER TABLE `msg_config`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT for table `otp_user`
--
ALTER TABLE `otp_user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `remark_templates`
--
ALTER TABLE `remark_templates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `requirements_uploads`
--
ALTER TABLE `requirements_uploads`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `services_answers`
--
ALTER TABLE `services_answers`
  MODIFY `answer_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=58;

--
-- AUTO_INCREMENT for table `services_email_otp_codes`
--
ALTER TABLE `services_email_otp_codes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `services_fields`
--
ALTER TABLE `services_fields`
  MODIFY `field_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `services_field_options`
--
ALTER TABLE `services_field_options`
  MODIFY `option_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `services_list`
--
ALTER TABLE `services_list`
  MODIFY `service_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `services_requests`
--
ALTER TABLE `services_requests`
  MODIFY `request_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `services_request_statuses`
--
ALTER TABLE `services_request_statuses`
  MODIFY `status_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `services_users`
--
ALTER TABLE `services_users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `smtp_config`
--
ALTER TABLE `smtp_config`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `sso_officers`
--
ALTER TABLE `sso_officers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `sso_user`
--
ALTER TABLE `sso_user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `statuses`
--
ALTER TABLE `statuses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `submissions`
--
ALTER TABLE `submissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `submission_data`
--
ALTER TABLE `submission_data`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `submission_files`
--
ALTER TABLE `submission_files`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `tokenization`
--
ALTER TABLE `tokenization`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `user_fullname`
--
ALTER TABLE `user_fullname`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `acc_locking`
--
ALTER TABLE `acc_locking`
  ADD CONSTRAINT `acc_locking_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `admission_submission`
--
ALTER TABLE `admission_submission`
  ADD CONSTRAINT `admission_submission_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `applicant_types`
--
ALTER TABLE `applicant_types`
  ADD CONSTRAINT `applicant_types_ibfk_1` FOREIGN KEY (`admission_cycle_id`) REFERENCES `admission_cycles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `application_permit`
--
ALTER TABLE `application_permit`
  ADD CONSTRAINT `fk_application_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `ExamRegistrations`
--
ALTER TABLE `ExamRegistrations`
  ADD CONSTRAINT `ExamRegistrations_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `ExamRegistrations_ibfk_2` FOREIGN KEY (`schedule_id`) REFERENCES `ExamSchedules` (`schedule_id`);

--
-- Constraints for table `form_fields`
--
ALTER TABLE `form_fields`
  ADD CONSTRAINT `form_fields_ibfk_1` FOREIGN KEY (`step_id`) REFERENCES `form_steps` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `form_field_options`
--
ALTER TABLE `form_field_options`
  ADD CONSTRAINT `form_field_options_ibfk_1` FOREIGN KEY (`field_id`) REFERENCES `form_fields` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `form_steps`
--
ALTER TABLE `form_steps`
  ADD CONSTRAINT `form_steps_ibfk_1` FOREIGN KEY (`applicant_type_id`) REFERENCES `applicant_types` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `otp_user`
--
ALTER TABLE `otp_user`
  ADD CONSTRAINT `otp_user_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `requirements_uploads`
--
ALTER TABLE `requirements_uploads`
  ADD CONSTRAINT `fk_requirements_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `services_answers`
--
ALTER TABLE `services_answers`
  ADD CONSTRAINT `services_answers_ibfk_1` FOREIGN KEY (`request_id`) REFERENCES `services_requests` (`request_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `services_answers_ibfk_2` FOREIGN KEY (`field_id`) REFERENCES `services_fields` (`field_id`) ON DELETE CASCADE;

--
-- Constraints for table `services_email_otp_codes`
--
ALTER TABLE `services_email_otp_codes`
  ADD CONSTRAINT `fk_services_email_otp_user` FOREIGN KEY (`user_id`) REFERENCES `services_users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `services_fields`
--
ALTER TABLE `services_fields`
  ADD CONSTRAINT `fk_visible_option` FOREIGN KEY (`visible_when_option_id`) REFERENCES `services_field_options` (`option_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `services_fields_ibfk_1` FOREIGN KEY (`service_id`) REFERENCES `services_list` (`service_id`) ON DELETE CASCADE;

--
-- Constraints for table `services_field_options`
--
ALTER TABLE `services_field_options`
  ADD CONSTRAINT `services_field_options_ibfk_1` FOREIGN KEY (`field_id`) REFERENCES `services_fields` (`field_id`) ON DELETE CASCADE;

--
-- Constraints for table `services_requests`
--
ALTER TABLE `services_requests`
  ADD CONSTRAINT `services_requests_ibfk_1` FOREIGN KEY (`service_id`) REFERENCES `services_list` (`service_id`),
  ADD CONSTRAINT `services_requests_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `services_users` (`id`),
  ADD CONSTRAINT `services_requests_ibfk_3` FOREIGN KEY (`status_id`) REFERENCES `services_request_statuses` (`status_id`);

--
-- Constraints for table `submissions`
--
ALTER TABLE `submissions`
  ADD CONSTRAINT `submissions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `sso_user` (`id`),
  ADD CONSTRAINT `submissions_ibfk_2` FOREIGN KEY (`applicant_type_id`) REFERENCES `applicant_types` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `submission_data`
--
ALTER TABLE `submission_data`
  ADD CONSTRAINT `submission_data_ibfk_1` FOREIGN KEY (`submission_id`) REFERENCES `submissions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `submission_files`
--
ALTER TABLE `submission_files`
  ADD CONSTRAINT `submission_files_ibfk_1` FOREIGN KEY (`submission_id`) REFERENCES `submissions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tokenization`
--
ALTER TABLE `tokenization`
  ADD CONSTRAINT `tokenization_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
