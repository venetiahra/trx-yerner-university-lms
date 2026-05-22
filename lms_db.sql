-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 22, 2026 at 02:24 PM
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
-- Database: `lms_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `account_email_logs`
--

CREATE TABLE `account_email_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `recipient` varchar(150) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `body` text NOT NULL,
  `type` varchar(100) DEFAULT NULL,
  `status` enum('pending','sent','failed') NOT NULL DEFAULT 'pending',
  `sent_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `account_email_logs`
--

INSERT INTO `account_email_logs` (`id`, `user_id`, `recipient`, `subject`, `body`, `type`, `status`, `sent_at`, `created_at`) VALUES
(1, 2, 'beatriciesr@gmail.com', 'Your account has been approved', '\n    <h2>Account Approved ✅</h2>\n    <p>Hello Zacahriah Feliz,</p>\n    <p>Your account for <b>TRX-Yerner University LMS</b> is now active.</p>\n    <p>You may now log in.</p>', 'account_approved', 'sent', '2026-05-17 09:49:38', '2026-05-17 15:49:38'),
(2, 3, 'ferrerbep@students.nu-laspinas.edu.ph', 'Registration received - awaiting approval', '\n    <h2>Welcome to TRX-Yerner University LMS</h2>\n    <p>Hello Max Verstappen,</p>\n    <p>Your account has been created.</p>\n    <p><b>Status:</b> Pending Approval</p>\n    <p>Please wait for the admin to approve your account.</p>', 'registration_pending', 'sent', '2026-05-19 14:41:37', '2026-05-19 20:41:37'),
(3, 3, 'ferrerbep@students.nu-laspinas.edu.ph', 'Your account has been approved', '\n    <h2>Account Approved ✅</h2>\n    <p>Hello Max Verstappen,</p>\n    <p>Your account for <b>TRX-Yerner University LMS</b> is now active.</p>\n    <p>You may now log in.</p>', 'account_approved', 'sent', '2026-05-19 14:42:01', '2026-05-19 20:42:01'),
(4, NULL, 'beatriciesr@gmail.com', '[TRX-Yerner University] Account Suspended', '<!DOCTYPE html>\n<html lang=\"en\">\n<head>\n  <meta charset=\"UTF-8\"/>\n  <meta name=\"viewport\" content=\"width=device-width,initial-scale=1.0\"/>\n  <title>Account Suspended</title>\n</head>\n<body style=\"margin:0;padding:0;background-color:#07121f;font-family:Arial,sans-serif;\">\n<table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\"\n       style=\"background-color:#07121f;padding:40px 16px;\">\n  <tr><td align=\"center\">\n  <table width=\"600\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\"\n         style=\"max-width:600px;width:100%;border-radius:16px;\n                border:1px solid #fbbf2444;background-color:#0c1d35;overflow:hidden;\">\n\n    <!-- Header -->\n    <tr>\n      <td style=\"background-color:#07121f;padding:28px 32px;\n                 border-bottom:2px solid #fbbf24;\">\n        <table cellpadding=\"0\" cellspacing=\"0\" border=\"0\">\n          <tr>\n            <td style=\"width:46px;height:46px;border-radius:12px;\n                       background-color:#fbbf24;text-align:center;\n                       vertical-align:middle;font-size:24px;line-height:46px;\">\n              &#9888;\n            </td>\n            <td style=\"padding-left:14px;vertical-align:middle;\">\n              <div style=\"font-size:10px;letter-spacing:0.14em;text-transform:uppercase;\n                          color:#fbbf24;font-weight:700;margin-bottom:3px;\">\n                TRX-Yerner University LMS\n              </div>\n              <div style=\"font-size:20px;font-weight:bold;color:#ffffff;\n                          font-family:Georgia,serif;\">\n                Account Suspended\n              </div>\n            </td>\n          </tr>\n        </table>\n      </td>\n    </tr>\n\n    <!-- Body -->\n    <tr>\n      <td style=\"padding:32px 32px 28px;background-color:#0c1d35;\">\n        <p style=\"margin:0 0 18px;font-size:14px;color:#ffffffaa;\">\n              Hello, <strong style=\"color:#ffffff;font-size:15px;\">Zacahriah Feliz</strong>\n            </p><p style=\"margin:0 0 14px;font-size:14px;line-height:1.75;color:#ffffffbb;\">Your account at <strong style=\"color:#c9a84c;\">TRX-Yerner University LMS</strong> has been temporarily <strong style=\"color:#fbbf24;\">suspended</strong>.</p><div style=\"display:inline-block;margin:14px 0 18px;padding:9px 22px;\n                         border-radius:99px;background-color:#f59e0b22;\n                         color:#fbbf24;font-size:13px;font-weight:700;\n                         letter-spacing:0.04em;\">&#9888;&nbsp; Account Suspended</div><br/><table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\"\n                    style=\"margin:18px 0 22px;border-radius:10px;\n                           border:1px solid #ffffff18;background-color:#ffffff08;\"><tr style=\"background-color:transparent;\">\n                    <td style=\"padding:11px 16px;font-size:11px;font-weight:700;\n                                text-transform:uppercase;letter-spacing:0.08em;\n                                color:#ffffff44;width:36%;\n                                border-right:1px solid #ffffff10;\">Status</td>\n                    <td style=\"padding:11px 16px;font-size:13px;\n                                color:#ffffff;font-weight:600;\">Suspended — temporary restriction</td>\n                  </tr><tr style=\"background-color:#ffffff05;\">\n                    <td style=\"padding:11px 16px;font-size:11px;font-weight:700;\n                                text-transform:uppercase;letter-spacing:0.08em;\n                                color:#ffffff44;width:36%;\n                                border-right:1px solid #ffffff10;\">Access</td>\n                    <td style=\"padding:11px 16px;font-size:13px;\n                                color:#ffffff;font-weight:600;\">Login and portal features are currently disabled</td>\n                  </tr><tr style=\"background-color:transparent;\">\n                    <td style=\"padding:11px 16px;font-size:11px;font-weight:700;\n                                text-transform:uppercase;letter-spacing:0.08em;\n                                color:#ffffff44;width:36%;\n                                border-right:1px solid #ffffff10;\">Contact</td>\n                    <td style=\"padding:11px 16px;font-size:13px;\n                                color:#ffffff;font-weight:600;\">Reach out to admin to resolve your account status</td>\n                  </tr></table><p style=\"margin:0 0 14px;font-size:14px;line-height:1.75;color:#ffffffbb;\">This suspension may be temporary. Please contact the university administrator for further information.</p>\n      </td>\n    </tr>\n\n    <!-- Divider -->\n    <tr>\n      <td style=\"padding:0 32px;\">\n        <div style=\"height:1px;background-color:#ffffff14;\"></div>\n      </td>\n    </tr>\n\n    <!-- Footer -->\n    <tr>\n      <td style=\"padding:20px 32px 28px;background-color:#07121f;text-align:center;\">\n        <p style=\"margin:0;font-size:11px;color:#ffffff44;line-height:1.7;\">\n          This is an automated message from\n          <strong style=\"color:#ffffff66;\">TRX-Yerner University LMS</strong>.<br/>\n          Please do not reply directly to this email.\n          &nbsp;&bull;&nbsp;\n          &copy; 2026 TRX-Yerner University. All rights reserved.\n        </p>\n      </td>\n    </tr>\n\n  </table>\n  </td></tr>\n</table>\n</body>\n</html>', 'suspended', 'sent', '2026-05-21 23:16:48', '2026-05-22 05:16:48'),
(5, 2, 'beatriciesr@gmail.com', 'Your account has been suspended', '<!DOCTYPE html>\n<html lang=\"en\">\n<head>\n  <meta charset=\"UTF-8\"/>\n  <meta name=\"viewport\" content=\"width=device-width,initial-scale=1.0\"/>\n  <title>Account Suspended</title>\n</head>\n<body style=\"margin:0;padding:0;background-color:#07121f;font-family:Arial,sans-serif;\">\n<table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\"\n       style=\"background-color:#07121f;padding:40px 16px;\">\n  <tr><td align=\"center\">\n  <table width=\"600\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\"\n         style=\"max-width:600px;width:100%;border-radius:16px;\n                border:1px solid #fbbf2444;background-color:#0c1d35;overflow:hidden;\">\n\n    <!-- Header -->\n    <tr>\n      <td style=\"background-color:#07121f;padding:28px 32px;\n                 border-bottom:2px solid #fbbf24;\">\n        <table cellpadding=\"0\" cellspacing=\"0\" border=\"0\">\n          <tr>\n            <td style=\"width:46px;height:46px;border-radius:12px;\n                       background-color:#fbbf24;text-align:center;\n                       vertical-align:middle;font-size:24px;line-height:46px;\">\n              &#9888;\n            </td>\n            <td style=\"padding-left:14px;vertical-align:middle;\">\n              <div style=\"font-size:10px;letter-spacing:0.14em;text-transform:uppercase;\n                          color:#fbbf24;font-weight:700;margin-bottom:3px;\">\n                TRX-Yerner University LMS\n              </div>\n              <div style=\"font-size:20px;font-weight:bold;color:#ffffff;\n                          font-family:Georgia,serif;\">\n                Account Suspended\n              </div>\n            </td>\n          </tr>\n        </table>\n      </td>\n    </tr>\n\n    <!-- Body -->\n    <tr>\n      <td style=\"padding:32px 32px 28px;background-color:#0c1d35;\">\n        <p style=\"margin:0 0 18px;font-size:14px;color:#ffffffaa;\">\n              Hello, <strong style=\"color:#ffffff;font-size:15px;\">Zacahriah Feliz</strong>\n            </p><p style=\"margin:0 0 14px;font-size:14px;line-height:1.75;color:#ffffffbb;\">Your account at <strong style=\"color:#c9a84c;\">TRX-Yerner University LMS</strong> has been temporarily <strong style=\"color:#fbbf24;\">suspended</strong>.</p><div style=\"display:inline-block;margin:14px 0 18px;padding:9px 22px;\n                         border-radius:99px;background-color:#f59e0b22;\n                         color:#fbbf24;font-size:13px;font-weight:700;\n                         letter-spacing:0.04em;\">&#9888;&nbsp; Account Suspended</div><br/><table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\"\n                    style=\"margin:18px 0 22px;border-radius:10px;\n                           border:1px solid #ffffff18;background-color:#ffffff08;\"><tr style=\"background-color:transparent;\">\n                    <td style=\"padding:11px 16px;font-size:11px;font-weight:700;\n                                text-transform:uppercase;letter-spacing:0.08em;\n                                color:#ffffff44;width:36%;\n                                border-right:1px solid #ffffff10;\">Status</td>\n                    <td style=\"padding:11px 16px;font-size:13px;\n                                color:#ffffff;font-weight:600;\">Suspended — temporary restriction</td>\n                  </tr><tr style=\"background-color:#ffffff05;\">\n                    <td style=\"padding:11px 16px;font-size:11px;font-weight:700;\n                                text-transform:uppercase;letter-spacing:0.08em;\n                                color:#ffffff44;width:36%;\n                                border-right:1px solid #ffffff10;\">Access</td>\n                    <td style=\"padding:11px 16px;font-size:13px;\n                                color:#ffffff;font-weight:600;\">Login and portal features are currently disabled</td>\n                  </tr><tr style=\"background-color:transparent;\">\n                    <td style=\"padding:11px 16px;font-size:11px;font-weight:700;\n                                text-transform:uppercase;letter-spacing:0.08em;\n                                color:#ffffff44;width:36%;\n                                border-right:1px solid #ffffff10;\">Contact</td>\n                    <td style=\"padding:11px 16px;font-size:13px;\n                                color:#ffffff;font-weight:600;\">Reach out to admin to resolve your account status</td>\n                  </tr></table><p style=\"margin:0 0 14px;font-size:14px;line-height:1.75;color:#ffffffbb;\">This suspension may be temporary. Please contact the university administrator for further information.</p>\n      </td>\n    </tr>\n\n    <!-- Divider -->\n    <tr>\n      <td style=\"padding:0 32px;\">\n        <div style=\"height:1px;background-color:#ffffff14;\"></div>\n      </td>\n    </tr>\n\n    <!-- Footer -->\n    <tr>\n      <td style=\"padding:20px 32px 28px;background-color:#07121f;text-align:center;\">\n        <p style=\"margin:0;font-size:11px;color:#ffffff44;line-height:1.7;\">\n          This is an automated message from\n          <strong style=\"color:#ffffff66;\">TRX-Yerner University LMS</strong>.<br/>\n          Please do not reply directly to this email.\n          &nbsp;&bull;&nbsp;\n          &copy; 2026 TRX-Yerner University. All rights reserved.\n        </p>\n      </td>\n    </tr>\n\n  </table>\n  </td></tr>\n</table>\n</body>\n</html>', 'account_suspended', 'sent', '2026-05-21 23:31:23', '2026-05-22 05:31:23'),
(6, 2, 'beatriciesr@gmail.com', 'Your account has been suspended', '<!DOCTYPE html>\n<html lang=\"en\">\n<head>\n  <meta charset=\"UTF-8\"/>\n  <meta name=\"viewport\" content=\"width=device-width,initial-scale=1.0\"/>\n  <title>Account Suspended</title>\n</head>\n<body style=\"margin:0;padding:0;background-color:#07121f;font-family:Arial,sans-serif;\">\n<table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\"\n       style=\"background-color:#07121f;padding:40px 16px;\">\n  <tr><td align=\"center\">\n  <table width=\"600\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\"\n         style=\"max-width:600px;width:100%;border-radius:16px;\n                border:1px solid #fbbf2444;background-color:#0c1d35;overflow:hidden;\">\n\n    <!-- Header -->\n    <tr>\n      <td style=\"background-color:#07121f;padding:28px 32px;\n                 border-bottom:2px solid #fbbf24;\">\n        <table cellpadding=\"0\" cellspacing=\"0\" border=\"0\">\n          <tr>\n            <td style=\"width:46px;height:46px;border-radius:12px;\n                       background-color:#fbbf24;text-align:center;\n                       vertical-align:middle;font-size:24px;line-height:46px;\">\n              &#9888;\n            </td>\n            <td style=\"padding-left:14px;vertical-align:middle;\">\n              <div style=\"font-size:10px;letter-spacing:0.14em;text-transform:uppercase;\n                          color:#fbbf24;font-weight:700;margin-bottom:3px;\">\n                TRX-Yerner University LMS\n              </div>\n              <div style=\"font-size:20px;font-weight:bold;color:#ffffff;\n                          font-family:Georgia,serif;\">\n                Account Suspended\n              </div>\n            </td>\n          </tr>\n        </table>\n      </td>\n    </tr>\n\n    <!-- Body -->\n    <tr>\n      <td style=\"padding:32px 32px 28px;background-color:#0c1d35;\">\n        <p style=\"margin:0 0 18px;font-size:14px;color:#ffffffaa;\">\n              Hello, <strong style=\"color:#ffffff;font-size:15px;\">Zacahriah Feliz</strong>\n            </p><p style=\"margin:0 0 14px;font-size:14px;line-height:1.75;color:#ffffffbb;\">Your account at <strong style=\"color:#c9a84c;\">TRX-Yerner University LMS</strong> has been temporarily <strong style=\"color:#fbbf24;\">suspended</strong>.</p><div style=\"display:inline-block;margin:14px 0 18px;padding:9px 22px;\n                         border-radius:99px;background-color:#f59e0b22;\n                         color:#fbbf24;font-size:13px;font-weight:700;\n                         letter-spacing:0.04em;\">&#9888;&nbsp; Account Suspended</div><br/><table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\"\n                    style=\"margin:18px 0 22px;border-radius:10px;\n                           border:1px solid #ffffff18;background-color:#ffffff08;\"><tr style=\"background-color:transparent;\">\n                    <td style=\"padding:11px 16px;font-size:11px;font-weight:700;\n                                text-transform:uppercase;letter-spacing:0.08em;\n                                color:#ffffff44;width:36%;\n                                border-right:1px solid #ffffff10;\">Status</td>\n                    <td style=\"padding:11px 16px;font-size:13px;\n                                color:#ffffff;font-weight:600;\">Suspended — temporary restriction</td>\n                  </tr><tr style=\"background-color:#ffffff05;\">\n                    <td style=\"padding:11px 16px;font-size:11px;font-weight:700;\n                                text-transform:uppercase;letter-spacing:0.08em;\n                                color:#ffffff44;width:36%;\n                                border-right:1px solid #ffffff10;\">Access</td>\n                    <td style=\"padding:11px 16px;font-size:13px;\n                                color:#ffffff;font-weight:600;\">Login and portal features are currently disabled</td>\n                  </tr><tr style=\"background-color:transparent;\">\n                    <td style=\"padding:11px 16px;font-size:11px;font-weight:700;\n                                text-transform:uppercase;letter-spacing:0.08em;\n                                color:#ffffff44;width:36%;\n                                border-right:1px solid #ffffff10;\">Contact</td>\n                    <td style=\"padding:11px 16px;font-size:13px;\n                                color:#ffffff;font-weight:600;\">Reach out to admin to resolve your account status</td>\n                  </tr></table><p style=\"margin:0 0 14px;font-size:14px;line-height:1.75;color:#ffffffbb;\">This suspension may be temporary. Please contact the university administrator for further information.</p>\n      </td>\n    </tr>\n\n    <!-- Divider -->\n    <tr>\n      <td style=\"padding:0 32px;\">\n        <div style=\"height:1px;background-color:#ffffff14;\"></div>\n      </td>\n    </tr>\n\n    <!-- Footer -->\n    <tr>\n      <td style=\"padding:20px 32px 28px;background-color:#07121f;text-align:center;\">\n        <p style=\"margin:0;font-size:11px;color:#ffffff44;line-height:1.7;\">\n          This is an automated message from\n          <strong style=\"color:#ffffff66;\">TRX-Yerner University LMS</strong>.<br/>\n          Please do not reply directly to this email.\n          &nbsp;&bull;&nbsp;\n          &copy; 2026 TRX-Yerner University. All rights reserved.\n        </p>\n      </td>\n    </tr>\n\n  </table>\n  </td></tr>\n</table>\n</body>\n</html>', 'account_suspended', 'sent', '2026-05-21 23:31:28', '2026-05-22 05:31:28'),
(7, 2, 'beatriciesr@gmail.com', 'Your account has been approved', '<!DOCTYPE html>\n<html lang=\"en\">\n<head>\n  <meta charset=\"UTF-8\"/>\n  <meta name=\"viewport\" content=\"width=device-width,initial-scale=1.0\"/>\n  <title>Account Approved</title>\n</head>\n<body style=\"margin:0;padding:0;background-color:#07121f;font-family:Arial,sans-serif;\">\n<table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\"\n       style=\"background-color:#07121f;padding:40px 16px;\">\n  <tr><td align=\"center\">\n  <table width=\"600\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\"\n         style=\"max-width:600px;width:100%;border-radius:16px;\n                border:1px solid #4ade8044;background-color:#0c1d35;overflow:hidden;\">\n\n    <!-- Header -->\n    <tr>\n      <td style=\"background-color:#07121f;padding:28px 32px;\n                 border-bottom:2px solid #4ade80;\">\n        <table cellpadding=\"0\" cellspacing=\"0\" border=\"0\">\n          <tr>\n            <td style=\"width:46px;height:46px;border-radius:12px;\n                       background-color:#4ade80;text-align:center;\n                       vertical-align:middle;font-size:24px;line-height:46px;\">\n              &#9989;\n            </td>\n            <td style=\"padding-left:14px;vertical-align:middle;\">\n              <div style=\"font-size:10px;letter-spacing:0.14em;text-transform:uppercase;\n                          color:#4ade80;font-weight:700;margin-bottom:3px;\">\n                TRX-Yerner University LMS\n              </div>\n              <div style=\"font-size:20px;font-weight:bold;color:#ffffff;\n                          font-family:Georgia,serif;\">\n                Account Approved\n              </div>\n            </td>\n          </tr>\n        </table>\n      </td>\n    </tr>\n\n    <!-- Body -->\n    <tr>\n      <td style=\"padding:32px 32px 28px;background-color:#0c1d35;\">\n        <p style=\"margin:0 0 18px;font-size:14px;color:#ffffffaa;\">\n              Hello, <strong style=\"color:#ffffff;font-size:15px;\">Zacahriah Feliz</strong>\n            </p><p style=\"margin:0 0 14px;font-size:14px;line-height:1.75;color:#ffffffbb;\">Great news! Your account for <strong style=\"color:#c9a84c;\">TRX-Yerner University LMS</strong> has been approved. You now have full access to the portal.</p><div style=\"display:inline-block;margin:14px 0 18px;padding:9px 22px;\n                         border-radius:99px;background-color:#22c55e22;\n                         color:#4ade80;font-size:13px;font-weight:700;\n                         letter-spacing:0.04em;\">&#10003;&nbsp; Account Active</div><br/><table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\"\n                    style=\"margin:18px 0 22px;border-radius:10px;\n                           border:1px solid #ffffff18;background-color:#ffffff08;\"><tr style=\"background-color:transparent;\">\n                    <td style=\"padding:11px 16px;font-size:11px;font-weight:700;\n                                text-transform:uppercase;letter-spacing:0.08em;\n                                color:#ffffff44;width:36%;\n                                border-right:1px solid #ffffff10;\">Status</td>\n                    <td style=\"padding:11px 16px;font-size:13px;\n                                color:#ffffff;font-weight:600;\">Active — full access granted</td>\n                  </tr><tr style=\"background-color:#ffffff05;\">\n                    <td style=\"padding:11px 16px;font-size:11px;font-weight:700;\n                                text-transform:uppercase;letter-spacing:0.08em;\n                                color:#ffffff44;width:36%;\n                                border-right:1px solid #ffffff10;\">Portal</td>\n                    <td style=\"padding:11px 16px;font-size:13px;\n                                color:#ffffff;font-weight:600;\">TRX-Yerner University LMS</td>\n                  </tr></table><p style=\"margin:0 0 14px;font-size:14px;line-height:1.75;color:#ffffffbb;\">You may now log in to your portal and start using the system. Welcome aboard!</p>\n      </td>\n    </tr>\n\n    <!-- Divider -->\n    <tr>\n      <td style=\"padding:0 32px;\">\n        <div style=\"height:1px;background-color:#ffffff14;\"></div>\n      </td>\n    </tr>\n\n    <!-- Footer -->\n    <tr>\n      <td style=\"padding:20px 32px 28px;background-color:#07121f;text-align:center;\">\n        <p style=\"margin:0;font-size:11px;color:#ffffff44;line-height:1.7;\">\n          This is an automated message from\n          <strong style=\"color:#ffffff66;\">TRX-Yerner University LMS</strong>.<br/>\n          Please do not reply directly to this email.\n          &nbsp;&bull;&nbsp;\n          &copy; 2026 TRX-Yerner University. All rights reserved.\n        </p>\n      </td>\n    </tr>\n\n  </table>\n  </td></tr>\n</table>\n</body>\n</html>', 'account_approved', 'sent', '2026-05-21 23:53:58', '2026-05-22 05:53:58');

-- --------------------------------------------------------

--
-- Table structure for table `activities`
--

CREATE TABLE `activities` (
  `id` int(11) NOT NULL,
  `code` varchar(50) DEFAULT NULL,
  `course_id` int(11) NOT NULL,
  `lesson_id` int(11) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `type` enum('quiz','assignment','exam','project') NOT NULL DEFAULT 'assignment',
  `max_score` decimal(5,2) DEFAULT 100.00,
  `due_date` datetime DEFAULT NULL,
  `is_published` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `pdf_path` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `activities`
--

INSERT INTO `activities` (`id`, `code`, `course_id`, `lesson_id`, `title`, `description`, `type`, `max_score`, `due_date`, `is_published`, `created_at`, `updated_at`, `pdf_path`) VALUES
(1, 'ACT-001', 1, NULL, 'Introduction Quiz', NULL, 'quiz', 25.00, '2024-06-15 23:59:00', 1, '2026-05-17 03:17:27', '2026-05-17 03:17:27', NULL),
(2, 'ACT-002', 1, NULL, 'Chapter 1 Assignment', NULL, 'assignment', 50.00, '2024-06-20 23:59:00', 1, '2026-05-17 03:17:27', '2026-05-17 03:17:27', NULL),
(3, 'ACT-003', 1, NULL, 'Midterm Exam', NULL, 'exam', 100.00, '2024-07-01 08:00:00', 1, '2026-05-17 03:17:27', '2026-05-17 03:17:27', NULL),
(4, 'ACT-004', 1, NULL, 'Final Project', NULL, 'project', 100.00, '2024-08-01 23:59:00', 0, '2026-05-17 03:17:27', '2026-05-17 03:17:27', NULL),
(5, 'ACT-005', 2, NULL, 'Computing Concepts Quiz', NULL, 'quiz', 25.00, '2024-06-18 23:59:00', 1, '2026-05-17 03:17:27', '2026-05-17 03:17:27', NULL),
(6, 'ACT-006', 2, NULL, 'Algorithm Assignment', NULL, 'assignment', 50.00, '2024-06-25 23:59:00', 1, '2026-05-17 03:17:27', '2026-05-17 03:17:27', NULL),
(7, 'ACT-007', 2, NULL, 'Prelim Exam', NULL, 'exam', 100.00, '2024-07-05 08:00:00', 1, '2026-05-17 03:17:27', '2026-05-17 03:17:27', NULL),
(8, 'ACT-008', 2, NULL, 'Capstone Project', NULL, 'project', 100.00, '2024-08-10 23:59:00', 0, '2026-05-17 03:17:27', '2026-05-17 03:17:27', NULL),
(9, 'ACT-009', 3, NULL, 'Engineering Math Quiz', NULL, 'quiz', 25.00, '2024-06-17 23:59:00', 1, '2026-05-17 03:17:27', '2026-05-17 03:17:27', NULL),
(10, 'ACT-010', 3, NULL, 'Problem Set 1', NULL, 'assignment', 50.00, '2024-06-22 23:59:00', 1, '2026-05-17 03:17:27', '2026-05-17 03:17:27', NULL),
(11, 'ACT-011', 4, NULL, 'Structural Analysis Quiz', NULL, 'quiz', 25.00, '2024-06-19 23:59:00', 1, '2026-05-17 03:17:27', '2026-05-17 03:17:27', NULL),
(12, 'ACT-012', 4, NULL, 'Design Assignment', NULL, 'assignment', 50.00, '2024-06-28 23:59:00', 1, '2026-05-17 03:17:27', '2026-05-17 03:17:27', NULL),
(13, 'ACT-013', 5, NULL, 'Architectural Concepts Quiz', NULL, 'quiz', 25.00, '2024-06-20 23:59:00', 1, '2026-05-17 03:17:27', '2026-05-17 03:17:27', NULL),
(14, 'ACT-014', 5, NULL, 'Design Portfolio', NULL, 'project', 100.00, '2024-08-05 23:59:00', 0, '2026-05-17 03:17:27', '2026-05-17 03:17:27', NULL),
(15, 'ACT-015', 6, NULL, 'Psychology Quiz 1', NULL, 'quiz', 25.00, '2024-06-16 23:59:00', 1, '2026-05-17 03:17:27', '2026-05-17 03:17:27', NULL),
(16, 'ACT-016', 6, NULL, 'Case Study Assignment', NULL, 'assignment', 50.00, '2024-06-23 23:59:00', 1, '2026-05-17 03:17:27', '2026-05-17 03:17:27', NULL),
(17, 'ACT-017', 7, NULL, 'Teaching Methods Quiz', NULL, 'quiz', 25.00, '2024-06-17 23:59:00', 1, '2026-05-17 03:17:27', '2026-05-17 03:17:27', NULL),
(18, 'ACT-018', 7, NULL, 'Lesson Plan Assignment', NULL, 'assignment', 50.00, '2024-06-24 23:59:00', 1, '2026-05-17 03:17:27', '2026-05-17 03:17:27', NULL),
(19, 'ACT-019', 8, NULL, 'Accounting Quiz 1', '', 'quiz', 100.00, '2024-06-18 23:59:00', 1, '2026-05-17 03:17:27', '2026-05-21 12:13:36', 'uploads/activities/act_1779365616_441123f5.pdf'),
(20, 'ACT-020', 8, NULL, 'Journal Entry Assignment', '', 'assignment', 50.00, '2024-06-25 23:59:00', 1, '2026-05-17 03:17:27', '2026-05-22 03:27:19', 'uploads/activities/act_1779420439_43cd1e0d.pdf'),
(21, 'ACT-021', 9, NULL, 'Tourism Overview Quiz', NULL, 'quiz', 25.00, '2024-06-19 23:59:00', 1, '2026-05-17 03:17:27', '2026-05-21 05:39:06', 'uploads/activities/act_1779341368_b572facb.pdf'),
(22, 'ACT-022', 9, NULL, 'Tour Package Project', '', 'project', 100.00, '2024-08-08 23:59:00', 0, '2026-05-17 03:17:27', '2026-05-21 05:29:28', 'uploads/activities/act_1779341368_b572facb.pdf'),
(23, 'ACT-023', 10, NULL, 'Business Math Quiz', NULL, 'quiz', 25.00, '2024-06-20 23:59:00', 1, '2026-05-17 03:17:27', '2026-05-17 03:17:27', NULL),
(24, 'ACT-024', 11, NULL, 'Biology Lab Report', NULL, 'assignment', 50.00, '2024-06-26 23:59:00', 1, '2026-05-17 03:17:27', '2026-05-17 03:17:27', NULL),
(25, 'ACT-025', 12, NULL, 'Philosophy Reflection Paper', NULL, 'assignment', 50.00, '2024-06-27 23:59:00', 1, '2026-05-17 03:17:27', '2026-05-17 03:17:27', NULL);

--
-- Triggers `activities`
--
DELIMITER $$
CREATE TRIGGER `trg_auto_grades_on_activity` AFTER INSERT ON `activities` FOR EACH ROW BEGIN
  IF NEW.`is_published` = 1 THEN
    INSERT IGNORE INTO `grades` (`student_id`, `activity_id`)
    SELECT e.`student_id`, NEW.`id`
    FROM `enrollments` e
    WHERE e.`course_id` = NEW.`course_id`
      AND e.`status` = 'enrolled';
  END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `announcements`
--

CREATE TABLE `announcements` (
  `id` int(11) NOT NULL,
  `course_id` int(11) DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `is_global` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `courses`
--

CREATE TABLE `courses` (
  `id` int(11) NOT NULL,
  `code` varchar(50) NOT NULL,
  `program` varchar(150) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `units` tinyint(4) DEFAULT NULL,
  `semester` varchar(50) DEFAULT NULL,
  `school_year` varchar(20) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `courses`
--

INSERT INTO `courses` (`id`, `code`, `program`, `title`, `name`, `description`, `units`, `semester`, `school_year`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'BSIT101', 'BSIT', 'Introduction to Computing', '', 'Basic computing concepts and fundamentals.', 3, '1st Semester', '2024-2025', 1, '2026-05-17 03:16:04', '2026-05-17 03:16:04'),
(2, 'BSCS101', 'BSCS', 'Introduction to Computer Science', '', 'Foundations of computer science.', 3, '1st Semester', '2024-2025', 1, '2026-05-17 03:16:04', '2026-05-17 03:16:04'),
(3, 'BCE101', 'BSCompEngr', 'Engineering Mathematics', '', 'Core mathematics for engineering students.', 3, '1st Semester', '2024-2025', 1, '2026-05-17 03:16:04', '2026-05-17 03:16:04'),
(4, 'BCE102', 'BSCivilEng', 'Structural Engineering Basics', '', 'Introduction to structural analysis.', 3, '1st Semester', '2024-2025', 1, '2026-05-17 03:16:04', '2026-05-17 03:16:04'),
(5, 'ARC101', 'BSArchi', 'Architectural Design 1', '', 'Fundamentals of architectural design.', 3, '1st Semester', '2024-2025', 1, '2026-05-17 03:16:04', '2026-05-17 03:16:04'),
(6, 'PSY101', 'Psychology', 'General Psychology', '', 'Overview of psychological theories.', 3, '1st Semester', '2024-2025', 1, '2026-05-17 03:16:04', '2026-05-17 03:16:04'),
(7, 'EDU101', 'Education', 'Principles of Teaching', '', 'Foundations of teaching and learning.', 3, '1st Semester', '2024-2025', 1, '2026-05-17 03:16:04', '2026-05-17 03:16:04'),
(8, 'ACC101', 'Accountancy', 'Basic Accounting', '', 'Introduction to accounting principles.', 3, '1st Semester', '2024-2025', 1, '2026-05-17 03:16:04', '2026-05-17 03:16:04'),
(9, 'TRM101', 'Tourism', 'Introduction to Tourism', '', 'Overview of the tourism and hospitality industry.', 3, '1st Semester', '2024-2025', 1, '2026-05-17 03:16:04', '2026-05-17 03:16:04'),
(10, 'ABM101', 'ABM', 'Business Mathematics', '', 'Mathematical concepts applied in business.', 3, '1st Semester', '2024-2025', 1, '2026-05-17 03:16:04', '2026-05-17 03:16:04'),
(11, 'STEM101', 'STEM', 'General Biology', '', 'Fundamentals of biological science.', 3, '1st Semester', '2024-2025', 1, '2026-05-17 03:16:04', '2026-05-17 03:16:04'),
(12, 'HUM101', 'HUMSS', 'Introduction to Philosophy', '', 'Basic philosophical concepts and ideas.', 3, '1st Semester', '2024-2025', 1, '2026-05-17 03:16:04', '2026-05-17 03:16:04');

-- --------------------------------------------------------

--
-- Table structure for table `course_professors`
--

CREATE TABLE `course_professors` (
  `id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `professor_id` int(11) NOT NULL,
  `assigned_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `course_professors`
--

INSERT INTO `course_professors` (`id`, `course_id`, `professor_id`, `assigned_at`) VALUES
(1, 1, 1, '2026-05-20 14:00:41'),
(2, 2, 2, '2026-05-20 14:00:41'),
(3, 3, 3, '2026-05-20 14:00:41'),
(4, 4, 4, '2026-05-20 14:00:41'),
(5, 5, 5, '2026-05-20 14:00:41'),
(6, 6, 6, '2026-05-20 14:00:41'),
(7, 7, 7, '2026-05-20 14:00:41'),
(8, 8, 8, '2026-05-20 14:00:41'),
(9, 9, 9, '2026-05-20 14:00:41'),
(10, 10, 10, '2026-05-20 14:00:41'),
(11, 11, 11, '2026-05-20 14:00:41'),
(12, 12, 12, '2026-05-20 14:00:41'),
(17, 9, 3, '2026-05-21 19:15:21'),
(18, 9, 11, '2026-05-21 19:17:54'),
(19, 8, 3, '2026-05-22 03:33:12'),
(20, 7, 3, '2026-05-22 03:52:43');

-- --------------------------------------------------------

--
-- Table structure for table `enrollments`
--

CREATE TABLE `enrollments` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `status` enum('enrolled','dropped','completed') NOT NULL DEFAULT 'enrolled',
  `enrolled_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `enrollments`
--

INSERT INTO `enrollments` (`id`, `student_id`, `course_id`, `status`, `enrolled_at`, `updated_at`) VALUES
(1, 26, 1, 'enrolled', '2026-05-20 13:52:51', '2026-05-20 13:52:51'),
(2, 27, 2, 'enrolled', '2026-05-20 13:52:51', '2026-05-20 13:52:51'),
(3, 28, 3, 'enrolled', '2026-05-20 13:52:51', '2026-05-20 13:52:51'),
(4, 29, 4, 'enrolled', '2026-05-20 13:52:51', '2026-05-20 13:52:51'),
(5, 30, 5, 'enrolled', '2026-05-20 13:52:51', '2026-05-20 13:52:51'),
(6, 31, 6, 'enrolled', '2026-05-20 13:52:51', '2026-05-20 13:52:51'),
(7, 32, 6, 'enrolled', '2026-05-20 13:52:51', '2026-05-20 13:52:51'),
(8, 33, 6, 'enrolled', '2026-05-20 13:52:51', '2026-05-20 13:52:51'),
(9, 34, 7, 'enrolled', '2026-05-20 13:52:51', '2026-05-20 13:52:51'),
(10, 35, 7, 'enrolled', '2026-05-20 13:52:51', '2026-05-20 13:52:51'),
(11, 36, 8, 'enrolled', '2026-05-20 13:52:51', '2026-05-20 13:52:51'),
(12, 37, 8, 'enrolled', '2026-05-20 13:52:51', '2026-05-20 13:52:51'),
(13, 38, 8, 'enrolled', '2026-05-20 13:52:51', '2026-05-20 13:52:51'),
(14, 39, 9, 'enrolled', '2026-05-20 13:52:51', '2026-05-20 13:52:51'),
(15, 40, 9, 'enrolled', '2026-05-20 13:52:51', '2026-05-20 13:52:51'),
(16, 41, 10, 'enrolled', '2026-05-20 13:52:51', '2026-05-20 13:52:51'),
(17, 42, 10, 'enrolled', '2026-05-20 13:52:51', '2026-05-20 13:52:51'),
(18, 43, 11, 'enrolled', '2026-05-20 13:52:51', '2026-05-20 13:52:51'),
(19, 44, 12, 'enrolled', '2026-05-20 13:52:51', '2026-05-20 13:52:51'),
(20, 45, 12, 'enrolled', '2026-05-20 13:52:51', '2026-05-20 13:52:51'),
(21, 46, 2, 'enrolled', '2026-05-20 13:52:51', '2026-05-20 13:52:51'),
(22, 47, 9, 'enrolled', '2026-05-20 13:52:51', '2026-05-20 13:52:51'),
(23, 48, 11, 'enrolled', '2026-05-20 13:52:51', '2026-05-20 13:52:51'),
(24, 49, 7, 'enrolled', '2026-05-20 13:52:51', '2026-05-20 13:52:51'),
(25, 50, 5, 'enrolled', '2026-05-20 13:52:51', '2026-05-20 13:52:51'),
(38, 55, 8, 'enrolled', '2026-05-22 05:40:13', '2026-05-22 05:40:13'),
(39, 55, 9, 'enrolled', '2026-05-22 05:40:13', '2026-05-22 05:40:13'),
(40, 55, 12, 'enrolled', '2026-05-22 05:40:13', '2026-05-22 05:40:13');

--
-- Triggers `enrollments`
--
DELIMITER $$
CREATE TRIGGER `trg_auto_grades_on_enroll` AFTER INSERT ON `enrollments` FOR EACH ROW BEGIN
  INSERT IGNORE INTO `grades` (`student_id`, `activity_id`)
  SELECT NEW.`student_id`, a.`id`
  FROM `activities` a
  WHERE a.`course_id` = NEW.`course_id`
    AND a.`is_published` = 1;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `grades`
--

CREATE TABLE `grades` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `activity_id` int(11) NOT NULL,
  `score` decimal(5,2) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `graded_by` int(11) DEFAULT NULL,
  `graded_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `grades`
--

INSERT INTO `grades` (`id`, `student_id`, `activity_id`, `score`, `remarks`, `graded_by`, `graded_at`, `created_at`, `updated_at`) VALUES
(1, 26, 1, NULL, NULL, NULL, NULL, '2026-05-20 14:00:41', '2026-05-20 14:00:41'),
(2, 26, 2, NULL, NULL, NULL, NULL, '2026-05-20 14:00:41', '2026-05-20 14:00:41'),
(3, 26, 3, NULL, NULL, NULL, NULL, '2026-05-20 14:00:41', '2026-05-20 14:00:41'),
(4, 27, 5, NULL, NULL, NULL, NULL, '2026-05-20 14:00:41', '2026-05-20 14:00:41'),
(5, 27, 6, NULL, NULL, NULL, NULL, '2026-05-20 14:00:41', '2026-05-20 14:00:41'),
(6, 27, 7, NULL, NULL, NULL, NULL, '2026-05-20 14:00:41', '2026-05-20 14:00:41'),
(7, 28, 9, NULL, NULL, NULL, NULL, '2026-05-20 14:00:41', '2026-05-20 14:00:41'),
(8, 28, 10, NULL, NULL, NULL, NULL, '2026-05-20 14:00:41', '2026-05-20 14:00:41'),
(9, 29, 11, NULL, NULL, NULL, NULL, '2026-05-20 14:00:41', '2026-05-20 14:00:41'),
(10, 29, 12, NULL, NULL, NULL, NULL, '2026-05-20 14:00:41', '2026-05-20 14:00:41'),
(11, 30, 13, NULL, NULL, NULL, NULL, '2026-05-20 14:00:41', '2026-05-20 14:00:41'),
(12, 31, 15, NULL, NULL, NULL, NULL, '2026-05-20 14:00:41', '2026-05-20 14:00:41'),
(13, 31, 16, NULL, NULL, NULL, NULL, '2026-05-20 14:00:41', '2026-05-20 14:00:41'),
(14, 32, 15, NULL, NULL, NULL, NULL, '2026-05-20 14:00:41', '2026-05-20 14:00:41'),
(15, 32, 16, NULL, NULL, NULL, NULL, '2026-05-20 14:00:41', '2026-05-20 14:00:41'),
(16, 33, 15, NULL, NULL, NULL, NULL, '2026-05-20 14:00:41', '2026-05-20 14:00:41'),
(17, 33, 16, NULL, NULL, NULL, NULL, '2026-05-20 14:00:41', '2026-05-20 14:00:41'),
(18, 34, 17, NULL, NULL, NULL, NULL, '2026-05-20 14:00:41', '2026-05-20 14:00:41'),
(19, 34, 18, NULL, NULL, NULL, NULL, '2026-05-20 14:00:41', '2026-05-20 14:00:41'),
(20, 35, 17, NULL, NULL, NULL, NULL, '2026-05-20 14:00:41', '2026-05-20 14:00:41'),
(21, 35, 18, NULL, NULL, NULL, NULL, '2026-05-20 14:00:41', '2026-05-20 14:00:41'),
(22, 36, 19, NULL, NULL, NULL, NULL, '2026-05-20 14:00:41', '2026-05-20 14:00:41'),
(23, 36, 20, NULL, NULL, NULL, NULL, '2026-05-20 14:00:41', '2026-05-20 14:00:41'),
(24, 37, 19, NULL, NULL, NULL, NULL, '2026-05-20 14:00:41', '2026-05-20 14:00:41'),
(25, 37, 20, NULL, NULL, NULL, NULL, '2026-05-20 14:00:41', '2026-05-20 14:00:41'),
(26, 38, 19, NULL, NULL, NULL, NULL, '2026-05-20 14:00:41', '2026-05-20 14:00:41'),
(27, 38, 20, NULL, NULL, NULL, NULL, '2026-05-20 14:00:41', '2026-05-20 14:00:41'),
(28, 39, 21, NULL, NULL, NULL, NULL, '2026-05-20 14:00:41', '2026-05-20 14:00:41'),
(29, 40, 21, NULL, NULL, NULL, NULL, '2026-05-20 14:00:41', '2026-05-20 14:00:41'),
(30, 41, 23, NULL, NULL, NULL, NULL, '2026-05-20 14:00:41', '2026-05-20 14:00:41'),
(31, 42, 23, NULL, NULL, NULL, NULL, '2026-05-20 14:00:41', '2026-05-20 14:00:41'),
(32, 43, 24, NULL, NULL, NULL, NULL, '2026-05-20 14:00:41', '2026-05-20 14:00:41'),
(33, 44, 25, NULL, NULL, NULL, NULL, '2026-05-20 14:00:41', '2026-05-20 14:00:41'),
(34, 45, 25, NULL, NULL, NULL, NULL, '2026-05-20 14:00:41', '2026-05-20 14:00:41'),
(35, 46, 5, NULL, NULL, NULL, NULL, '2026-05-20 14:00:41', '2026-05-20 14:00:41'),
(36, 46, 6, NULL, NULL, NULL, NULL, '2026-05-20 14:00:41', '2026-05-20 14:00:41'),
(37, 46, 7, NULL, NULL, NULL, NULL, '2026-05-20 14:00:41', '2026-05-20 14:00:41'),
(38, 47, 21, NULL, NULL, NULL, NULL, '2026-05-20 14:00:41', '2026-05-20 14:00:41'),
(39, 48, 24, NULL, NULL, NULL, NULL, '2026-05-20 14:00:41', '2026-05-20 14:00:41'),
(40, 49, 17, NULL, NULL, NULL, NULL, '2026-05-20 14:00:41', '2026-05-20 14:00:41'),
(41, 49, 18, NULL, NULL, NULL, NULL, '2026-05-20 14:00:41', '2026-05-20 14:00:41'),
(42, 50, 13, NULL, NULL, NULL, NULL, '2026-05-20 14:00:41', '2026-05-20 14:00:41'),
(43, 55, 21, 21.56, '', 1, '2026-05-22 03:18:25', '2026-05-20 14:00:41', '2026-05-22 03:18:25'),
(66, 55, 19, 92.13, '', 1, '2026-05-22 03:11:19', '2026-05-21 05:53:29', '2026-05-22 03:11:19'),
(67, 55, 20, 45.00, '', 3, '2026-05-22 05:37:41', '2026-05-21 05:53:29', '2026-05-22 05:37:41'),
(72, 55, 25, NULL, NULL, NULL, NULL, '2026-05-22 05:40:13', '2026-05-22 05:40:13');

-- --------------------------------------------------------

--
-- Table structure for table `lessons`
--

CREATE TABLE `lessons` (
  `id` int(11) NOT NULL,
  `code` varchar(50) DEFAULT NULL,
  `course_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` longtext DEFAULT NULL,
  `file_path` varchar(500) DEFAULT NULL,
  `order_no` int(11) NOT NULL DEFAULT 1,
  `is_published` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `lesson_order` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `lessons`
--

INSERT INTO `lessons` (`id`, `code`, `course_id`, `title`, `content`, `file_path`, `order_no`, `is_published`, `created_at`, `updated_at`, `lesson_order`) VALUES
(13, 'LES-001', 1, 'Introduction to the Course', NULL, NULL, 1, 1, '2026-05-17 03:16:12', '2026-05-17 03:16:12', 0),
(14, 'LES-002', 1, 'Basic Concepts and Fundamentals', NULL, NULL, 2, 1, '2026-05-17 03:16:12', '2026-05-17 03:16:12', 0),
(15, 'LES-003', 1, 'Core Principles', NULL, NULL, 3, 1, '2026-05-17 03:16:12', '2026-05-17 03:16:12', 0),
(16, 'LES-004', 1, 'Hands-on Practice', NULL, NULL, 4, 1, '2026-05-17 03:16:12', '2026-05-17 03:16:12', 0),
(17, 'LES-005', 1, 'Midterm Review', NULL, NULL, 5, 1, '2026-05-17 03:16:12', '2026-05-17 03:16:12', 0),
(18, 'LES-006', 1, 'Advanced Topics', NULL, NULL, 6, 0, '2026-05-17 03:16:12', '2026-05-17 03:16:12', 0),
(19, 'LES-007', 2, 'Introduction to Data Structures', NULL, NULL, 1, 1, '2026-05-17 03:16:12', '2026-05-17 03:16:12', 0),
(20, 'LES-008', 2, 'Arrays and Linked Lists', NULL, NULL, 2, 1, '2026-05-17 03:16:12', '2026-05-17 03:16:12', 0),
(21, 'LES-009', 2, 'Stacks and Queues', NULL, NULL, 3, 1, '2026-05-17 03:16:12', '2026-05-17 03:16:12', 0),
(22, 'LES-010', 2, 'Trees and Graphs', NULL, NULL, 4, 1, '2026-05-17 03:16:12', '2026-05-17 03:16:12', 0),
(23, 'LES-011', 2, 'Sorting Algorithms', NULL, NULL, 5, 1, '2026-05-17 03:16:12', '2026-05-17 03:16:12', 0),
(24, 'LES-012', 2, 'Final Review and Wrap-up', NULL, NULL, 6, 0, '2026-05-17 03:16:12', '2026-05-17 03:16:12', 0);

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `type` varchar(50) NOT NULL,
  `title` varchar(180) NOT NULL,
  `body` varchar(400) NOT NULL DEFAULT '',
  `link` varchar(255) NOT NULL DEFAULT '',
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `type`, `title`, `body`, `link`, `is_read`, `created_at`) VALUES
(1, 2, 'grade_posted', 'Grade posted: Tourism Overview Quiz', 'You scored 21.56 / 25.00 in TRM101', 'student_grades.php', 1, '2026-05-22 11:18:25'),
(2, 8, 'new_submission', 'New submission in ACC101', 'Zacahriah Feliz submitted \"Journal Entry Assignment\"', 'member_submissions.php?course_id=8', 0, '2026-05-22 11:31:17'),
(3, 1, 'new_submission', 'New submission: Journal Entry Assignment', 'Zacahriah Feliz submitted in ACC101', 'submissions.php', 1, '2026-05-22 11:31:17'),
(4, 6, 'new_submission', 'New submission in ACC101', 'Zacahriah Feliz submitted \"Journal Entry Assignment\"', 'member_submissions.php?course_id=8', 0, '2026-05-22 11:36:27'),
(5, 11, 'new_submission', 'New submission in ACC101', 'Zacahriah Feliz submitted \"Journal Entry Assignment\"', 'member_submissions.php?course_id=8', 0, '2026-05-22 11:36:27'),
(6, 1, 'new_submission', 'New submission: Journal Entry Assignment', 'Zacahriah Feliz submitted in ACC101', 'submissions.php', 1, '2026-05-22 11:36:27'),
(7, 6, 'new_submission', 'New submission in ACC101', 'Zacahriah Feliz submitted \"Journal Entry Assignment\"', 'member_submissions.php?course_id=8', 0, '2026-05-22 11:39:23'),
(8, 11, 'new_submission', 'New submission in ACC101', 'Zacahriah Feliz submitted \"Journal Entry Assignment\"', 'member_submissions.php?course_id=8', 0, '2026-05-22 11:39:23'),
(9, 1, 'new_submission', 'New submission: Journal Entry Assignment', 'Zacahriah Feliz submitted in ACC101', 'submissions.php', 1, '2026-05-22 11:39:23'),
(10, 6, 'new_submission', 'New submission in ACC101', 'Zacahriah Feliz submitted \"Journal Entry Assignment\"', 'member_submissions.php?course_id=8', 0, '2026-05-22 11:39:36'),
(11, 11, 'new_submission', 'New submission in ACC101', 'Zacahriah Feliz submitted \"Journal Entry Assignment\"', 'member_submissions.php?course_id=8', 0, '2026-05-22 11:39:36'),
(12, 1, 'new_submission', 'New submission: Journal Entry Assignment', 'Zacahriah Feliz submitted in ACC101', 'submissions.php', 1, '2026-05-22 11:39:36'),
(13, 2, 'grade_posted', 'Grade posted: Journal Entry Assignment', 'You scored 45 / 50.00 in ACC101', 'student_grades.php', 1, '2026-05-22 13:37:41'),
(14, 1, 'grade_posted', 'Grade posted: Journal Entry Assignment', 'Score: 45 / 50.00 (ACC101)', 'submissions.php', 0, '2026-05-22 13:37:41');

-- --------------------------------------------------------

--
-- Table structure for table `professors`
--

CREATE TABLE `professors` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `employee_no` varchar(50) DEFAULT NULL,
  `department` varchar(150) DEFAULT NULL,
  `specialization` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `professors`
--

INSERT INTO `professors` (`id`, `user_id`, `employee_no`, `department`, `specialization`, `created_at`, `updated_at`) VALUES
(1, 4, 'EMP-2026-001', 'BSIT', 'Information Technology', '2026-05-20 13:59:28', '2026-05-20 13:59:28'),
(2, 5, 'EMP-2026-002', 'BSCS', 'Computer Science', '2026-05-20 13:59:28', '2026-05-20 13:59:28'),
(3, 6, 'EMP-2026-003', 'BSCompEngr', 'Computer Engineering', '2026-05-20 13:59:28', '2026-05-20 13:59:28'),
(4, 7, 'EMP-2026-004', 'BSCivilEng', 'Civil Engineering', '2026-05-20 13:59:28', '2026-05-20 13:59:28'),
(5, 8, 'EMP-2026-005', 'BSArchi', 'Architecture', '2026-05-20 13:59:28', '2026-05-20 13:59:28'),
(6, 9, 'EMP-2026-006', 'Psychology', 'Clinical Psychology', '2026-05-20 13:59:28', '2026-05-20 13:59:28'),
(7, 10, 'EMP-2026-007', 'Education', 'Teaching & Learning', '2026-05-20 13:59:28', '2026-05-20 13:59:28'),
(8, 11, 'EMP-2026-008', 'Accountancy', 'Financial Accounting', '2026-05-20 13:59:28', '2026-05-20 13:59:28'),
(9, 12, 'EMP-2026-009', 'Tourism', 'Tourism & Hospitality', '2026-05-20 13:59:28', '2026-05-20 13:59:28'),
(10, 13, 'EMP-2026-010', 'ABM', 'Business Management', '2026-05-20 13:59:28', '2026-05-20 13:59:28'),
(11, 14, 'EMP-2026-011', 'STEM', 'Biology & Sciences', '2026-05-20 13:59:28', '2026-05-20 13:59:28'),
(12, 15, 'EMP-2026-012', 'HUMSS', 'Humanities & Social Sciences', '2026-05-20 13:59:28', '2026-05-20 13:59:28');

--
-- Triggers `professors`
--
DELIMITER $$
CREATE TRIGGER `trg_auto_assign_professor` AFTER INSERT ON `professors` FOR EACH ROW BEGIN
  INSERT IGNORE INTO `course_professors` (`course_id`, `professor_id`)
  SELECT c.`id`, NEW.`id`
  FROM `courses` c
  WHERE c.`program` = NEW.`department`
    AND c.`is_active` = 1;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `sent_activity_emails`
--

CREATE TABLE `sent_activity_emails` (
  `id` int(11) NOT NULL,
  `activity_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `recipient_email` varchar(120) NOT NULL,
  `subject` varchar(180) NOT NULL,
  `status` enum('preview','sent','failed') DEFAULT 'preview',
  `error_message` text DEFAULT NULL,
  `sent_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sent_activity_emails`
--

INSERT INTO `sent_activity_emails` (`id`, `activity_id`, `student_id`, `recipient_email`, `subject`, `status`, `error_message`, `sent_at`) VALUES
(1, 21, 39, 'isabel.navarro@trx.edu', 'New Activity: Tourism Overview Quiz', 'preview', NULL, '2026-05-19 20:36:15'),
(2, 21, 40, 'paolo.serrano@trx.edu', 'New Activity: Tourism Overview Quiz', 'preview', NULL, '2026-05-19 20:36:15'),
(3, 21, 47, 'francesca.ocampo@trx.edu', 'New Activity: Tourism Overview Quiz', 'preview', NULL, '2026-05-19 20:36:15'),
(4, 21, 39, 'isabel.navarro@trx.edu', 'New Activity: Tourism Overview Quiz', 'preview', NULL, '2026-05-19 20:36:29'),
(5, 21, 40, 'paolo.serrano@trx.edu', 'New Activity: Tourism Overview Quiz', 'preview', NULL, '2026-05-19 20:36:29'),
(6, 21, 47, 'francesca.ocampo@trx.edu', 'New Activity: Tourism Overview Quiz', 'preview', NULL, '2026-05-19 20:36:29'),
(7, 22, 47, 'francesca.ocampo@trx.edu', 'New Activity: Tour Package Project', 'preview', NULL, '2026-05-21 05:46:45'),
(8, 22, 39, 'isabel.navarro@trx.edu', 'New Activity: Tour Package Project', 'preview', NULL, '2026-05-21 05:46:45'),
(9, 22, 40, 'paolo.serrano@trx.edu', 'New Activity: Tour Package Project', 'preview', NULL, '2026-05-21 05:46:45');

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `full_name` varchar(150) NOT NULL,
  `email` varchar(150) DEFAULT NULL,
  `student_no` varchar(50) DEFAULT NULL,
  `course` varchar(150) DEFAULT NULL,
  `year_level` tinyint(1) DEFAULT NULL,
  `section` varchar(50) DEFAULT NULL,
  `program` varchar(150) DEFAULT NULL,
  `course_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`id`, `user_id`, `full_name`, `email`, `student_no`, `course`, `year_level`, `section`, `program`, `course_id`, `created_at`, `updated_at`) VALUES
(26, NULL, 'Juan dela Cruz', 'juan.delacruz@trx.edu', '2024-0001', NULL, 1, NULL, 'BSIT', NULL, '2026-05-17 03:14:17', '2026-05-17 03:14:17'),
(27, NULL, 'Maria Santos', 'maria.santos@trx.edu', '2024-0002', NULL, 2, NULL, 'BSCS', NULL, '2026-05-17 03:14:17', '2026-05-17 03:14:17'),
(28, NULL, 'Carlo Reyes', 'carlo.reyes@trx.edu', '2024-0003', NULL, 3, NULL, 'BSCompEngr', NULL, '2026-05-17 03:14:17', '2026-05-17 03:14:17'),
(29, NULL, 'Angela Flores', 'angela.flores@trx.edu', '2024-0004', NULL, 4, NULL, 'BSCivilEng', NULL, '2026-05-17 03:14:17', '2026-05-17 03:14:17'),
(30, NULL, 'Mark Villanueva', 'mark.villanueva@trx.edu', '2024-0005', NULL, 2, NULL, 'BSArchi', NULL, '2026-05-17 03:14:17', '2026-05-17 03:14:17'),
(31, NULL, 'Liza Gonzales', 'liza.gonzales@trx.edu', '2024-0006', NULL, 1, NULL, 'Psychology', NULL, '2026-05-17 03:14:17', '2026-05-17 03:14:17'),
(32, NULL, 'Ryan Aquino', 'ryan.aquino@trx.edu', '2024-0007', NULL, 2, NULL, 'Psychology', NULL, '2026-05-17 03:14:17', '2026-05-17 03:14:17'),
(33, NULL, 'Patricia Cruz', 'patricia.cruz@trx.edu', '2024-0008', NULL, 3, NULL, 'Psychology', NULL, '2026-05-17 03:14:17', '2026-05-17 03:14:17'),
(34, NULL, 'James Ramos', 'james.ramos@trx.edu', '2024-0009', NULL, 4, NULL, 'Education', NULL, '2026-05-17 03:14:17', '2026-05-17 03:14:17'),
(35, NULL, 'Sophia Mendoza', 'sophia.mendoza@trx.edu', '2024-0010', NULL, 1, NULL, 'Education', NULL, '2026-05-17 03:14:17', '2026-05-17 03:14:17'),
(36, NULL, 'Kevin Torres', 'kevin.torres@trx.edu', '2024-0011', NULL, 2, NULL, 'Accountancy', NULL, '2026-05-17 03:14:17', '2026-05-17 03:14:17'),
(37, NULL, 'Diana Castillo', 'diana.castillo@trx.edu', '2024-0012', NULL, 3, NULL, 'Accountancy', NULL, '2026-05-17 03:14:17', '2026-05-17 03:14:17'),
(38, NULL, 'Miguel Bautista', 'miguel.bautista@trx.edu', '2024-0013', NULL, 1, NULL, 'Accountancy', NULL, '2026-05-17 03:14:17', '2026-05-17 03:14:17'),
(39, NULL, 'Isabel Navarro', 'isabel.navarro@trx.edu', '2024-0014', NULL, 4, NULL, 'Tourism', NULL, '2026-05-17 03:14:17', '2026-05-17 03:14:17'),
(40, NULL, 'Paolo Serrano', 'paolo.serrano@trx.edu', '2024-0015', NULL, 2, NULL, 'Tourism', NULL, '2026-05-17 03:14:17', '2026-05-17 03:14:17'),
(41, NULL, 'Camille Hernandez', 'camille.hernandez@trx.edu', '2024-0016', NULL, 0, NULL, 'ABM', NULL, '2026-05-17 03:14:17', '2026-05-17 03:14:17'),
(42, NULL, 'Anton Dela Torre', 'anton.delatorre@trx.edu', '2024-0017', NULL, 0, NULL, 'ABM', NULL, '2026-05-17 03:14:17', '2026-05-17 03:14:17'),
(43, NULL, 'Trisha Pascual', 'trisha.pascual@trx.edu', '2024-0018', NULL, 0, NULL, 'STEM', NULL, '2026-05-17 03:14:17', '2026-05-17 03:14:17'),
(44, NULL, 'Rafael Domingo', 'rafael.domingo@trx.edu', '2024-0019', NULL, 0, NULL, 'HUMSS', NULL, '2026-05-17 03:14:17', '2026-05-17 03:14:17'),
(45, NULL, 'Bianca Lim', 'bianca.lim@trx.edu', '2024-0020', NULL, 0, NULL, 'HUMSS', NULL, '2026-05-17 03:14:17', '2026-05-17 03:14:17'),
(46, NULL, 'Christian Abad', 'christian.abad@trx.edu', '2024-0021', NULL, 1, NULL, 'BSCS', NULL, '2026-05-17 03:14:17', '2026-05-17 03:14:17'),
(47, NULL, 'Francesca Ocampo', 'francesca.ocampo@trx.edu', '2024-0022', NULL, 2, NULL, 'Tourism', NULL, '2026-05-17 03:14:17', '2026-05-17 03:14:17'),
(48, NULL, 'Daniel Espiritu', 'daniel.espiritu@trx.edu', '2024-0023', NULL, 0, NULL, 'STEM', NULL, '2026-05-17 03:14:17', '2026-05-17 03:14:17'),
(49, NULL, 'Monique Alvarado', 'monique.alvarado@trx.edu', '2024-0024', NULL, 3, NULL, 'Education', NULL, '2026-05-17 03:14:17', '2026-05-17 03:14:17'),
(50, NULL, 'Joshua Reyes', 'joshua.reyes@trx.edu', '2024-0025', NULL, 4, NULL, 'BSArchi', NULL, '2026-05-17 03:14:17', '2026-05-17 03:14:17'),
(55, 2, 'Zacahriah Feliz', 'beatriciesr@gmail.com', '2026-0026', NULL, 1, NULL, 'Tourism', 9, '2026-05-17 15:47:11', '2026-05-21 05:30:55');

--
-- Triggers `students`
--
DELIMITER $$
CREATE TRIGGER `trg_auto_enroll_on_student_insert` AFTER INSERT ON `students` FOR EACH ROW BEGIN
  INSERT IGNORE INTO `enrollments` (`student_id`, `course_id`, `status`)
  SELECT NEW.`id`, c.`id`, 'enrolled'
  FROM `courses` c
  WHERE c.`program` = NEW.`program`
    AND c.`is_active` = 1;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_students_student_no_bi` BEFORE INSERT ON `students` FOR EACH ROW BEGIN
  DECLARE next_student_no INT DEFAULT 1;

  IF NEW.`student_no` IS NULL OR NEW.`student_no` = '' THEN

    SELECT COALESCE(
      MAX(CAST(SUBSTRING_INDEX(`student_no`, '-', -1) AS UNSIGNED)),
      0
    ) + 1
    INTO next_student_no
    FROM `students`
    WHERE `student_no` REGEXP '^[0-9]{4}-[0-9]+$';

    SET NEW.`student_no` = CONCAT(
      YEAR(CURDATE()),
      '-',
      LPAD(next_student_no, 4, '0')
    );

  END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `submissions`
--

CREATE TABLE `submissions` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `activity_id` int(11) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `original_filename` varchar(255) DEFAULT NULL,
  `submitted_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `submissions`
--

INSERT INTO `submissions` (`id`, `student_id`, `activity_id`, `file_path`, `original_filename`, `submitted_at`, `updated_at`) VALUES
(1, 55, 21, 'uploads/submissions/sub_55_21_1779364974.pdf', 'FelizZ_TourismOverviewQuiz .pdf', '2026-05-21 12:02:54', '2026-05-21 12:02:54'),
(2, 55, 19, 'uploads/submissions/sub_55_19_1779365822.pdf', 'FelizZ_AccountingQuiz1.pdf', '2026-05-21 12:17:02', '2026-05-21 12:17:02'),
(4, 55, 20, 'uploads/submissions/sub_55_20_1779421176.pdf', 'Journal Entry Assignment (ACC101).pdf', '2026-05-22 03:39:36', '2026-05-22 03:39:36');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `company` varchar(150) DEFAULT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `profile_pic` varchar(255) DEFAULT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','professor','student','member') NOT NULL DEFAULT 'student',
  `status` enum('pending','active','inactive') NOT NULL DEFAULT 'pending',
  `student_id` int(11) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `company`, `phone`, `profile_pic`, `email`, `password`, `role`, `status`, `student_id`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Admin', 'SECA', '', 'profile_1_1779419364.png', 'trxmcln@gmail.com', '$2b$12$6HZReNdrjhGLnMXm0sMxbutaUEsG5UkmgSEXeVKxV7jwW5eQbQji.', 'admin', 'active', NULL, 1, '2026-05-17 03:02:51', '2026-05-22 03:09:24'),
(2, 'Zacahriah Feliz', 'SBMA', '', 'profile_2_1779033088.jpg', 'beatriciesr@gmail.com', '$2y$10$KO0wvYKIPwUx5zLe4jNax.9f3HV5WaHVC2H7j.xwF7AWViZBanLgi', 'student', 'active', 55, 1, '2026-05-17 15:47:11', '2026-05-22 05:53:52'),
(3, 'Max Verstappen', 'SBMA', '', 'profile_3_1779223386.png', 'ferrerbep@students.nu-laspinas.edu.ph', '$2y$10$0BeQoEOn72K2QC2tLsmGM.lssYAeXJUROS3/7CzSyFNT7k5ghK78q', 'member', 'active', NULL, 1, '2026-05-19 20:41:32', '2026-05-22 05:18:14'),
(4, 'Prof. Rico Delos Santos', NULL, NULL, NULL, 'rico.delossantos@trx.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uYutTBI', 'professor', 'active', NULL, 1, '2026-05-20 13:59:28', '2026-05-20 13:59:28'),
(5, 'Prof. Maricel Ramos', NULL, NULL, NULL, 'maricel.ramos@trx.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uYutTBI', 'professor', 'active', NULL, 1, '2026-05-20 13:59:28', '2026-05-20 13:59:28'),
(6, 'Prof. Eduardo Villanueva', NULL, NULL, NULL, 'eduardo.villanueva@trx.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uYutTBI', 'professor', 'active', NULL, 1, '2026-05-20 13:59:28', '2026-05-20 13:59:28'),
(7, 'Prof. Arturo Magno', NULL, NULL, NULL, 'arturo.magno@trx.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uYutTBI', 'professor', 'active', NULL, 1, '2026-05-20 13:59:28', '2026-05-20 13:59:28'),
(8, 'Prof. Carmela Tan', NULL, NULL, NULL, 'carmela.tan@trx.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uYutTBI', 'professor', 'active', NULL, 1, '2026-05-20 13:59:28', '2026-05-20 13:59:28'),
(9, 'Prof. Josefina Cruz', NULL, NULL, NULL, 'josefina.cruz@trx.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uYutTBI', 'professor', 'active', NULL, 1, '2026-05-20 13:59:28', '2026-05-20 13:59:28'),
(10, 'Prof. Remedios Aquino', 'SBMA', '', NULL, 'remedios.aquino@trx.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uYutTBI', 'member', 'pending', NULL, 1, '2026-05-20 13:59:28', '2026-05-22 05:39:07'),
(11, 'Prof. Roberto Lim', 'SBMA', '', NULL, 'roberto.lim@trx.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uYutTBI', 'member', 'pending', NULL, 1, '2026-05-20 13:59:28', '2026-05-22 05:39:01'),
(12, 'Prof. Anita Reyes', 'SBMA', '', NULL, 'anita.reyes@trx.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uYutTBI', 'member', 'pending', NULL, 1, '2026-05-20 13:59:28', '2026-05-22 05:17:23'),
(13, 'Prof. Fernando Garcia', NULL, NULL, NULL, 'fernando.garcia@trx.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uYutTBI', 'professor', 'active', NULL, 1, '2026-05-20 13:59:28', '2026-05-20 13:59:28'),
(14, 'Prof. Natividad Santos', NULL, NULL, NULL, 'natividad.santos@trx.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uYutTBI', 'professor', 'active', NULL, 1, '2026-05-20 13:59:28', '2026-05-20 13:59:28'),
(15, 'Prof. Benedicto dela Cruz', NULL, NULL, NULL, 'benedicto.delacruz@trx.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uYutTBI', 'professor', 'active', NULL, 1, '2026-05-20 13:59:28', '2026-05-20 13:59:28');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `account_email_logs`
--
ALTER TABLE `account_email_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `activities`
--
ALTER TABLE `activities`
  ADD PRIMARY KEY (`id`),
  ADD KEY `course_id` (`course_id`),
  ADD KEY `lesson_id` (`lesson_id`);

--
-- Indexes for table `announcements`
--
ALTER TABLE `announcements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `course_id` (`course_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `courses`
--
ALTER TABLE `courses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Indexes for table `course_professors`
--
ALTER TABLE `course_professors`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_course_prof` (`course_id`,`professor_id`),
  ADD KEY `professor_id` (`professor_id`);

--
-- Indexes for table `enrollments`
--
ALTER TABLE `enrollments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_enrollment` (`student_id`,`course_id`),
  ADD KEY `course_id` (`course_id`);

--
-- Indexes for table `grades`
--
ALTER TABLE `grades`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_grade` (`student_id`,`activity_id`),
  ADD KEY `activity_id` (`activity_id`);

--
-- Indexes for table `lessons`
--
ALTER TABLE `lessons`
  ADD PRIMARY KEY (`id`),
  ADD KEY `course_id` (`course_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_read` (`user_id`,`is_read`),
  ADD KEY `idx_created` (`created_at`);

--
-- Indexes for table `professors`
--
ALTER TABLE `professors`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- Indexes for table `sent_activity_emails`
--
ALTER TABLE `sent_activity_emails`
  ADD PRIMARY KEY (`id`),
  ADD KEY `activity_id` (`activity_id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`),
  ADD KEY `course_id` (`course_id`);

--
-- Indexes for table `submissions`
--
ALTER TABLE `submissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_sub` (`student_id`,`activity_id`),
  ADD KEY `activity_id` (`activity_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `users_student_fk` (`student_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `account_email_logs`
--
ALTER TABLE `account_email_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `activities`
--
ALTER TABLE `activities`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `announcements`
--
ALTER TABLE `announcements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `courses`
--
ALTER TABLE `courses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `course_professors`
--
ALTER TABLE `course_professors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `enrollments`
--
ALTER TABLE `enrollments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `grades`
--
ALTER TABLE `grades`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=73;

--
-- AUTO_INCREMENT for table `lessons`
--
ALTER TABLE `lessons`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `professors`
--
ALTER TABLE `professors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `sent_activity_emails`
--
ALTER TABLE `sent_activity_emails`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=56;

--
-- AUTO_INCREMENT for table `submissions`
--
ALTER TABLE `submissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activities`
--
ALTER TABLE `activities`
  ADD CONSTRAINT `activity_course_fk` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `activity_lesson_fk` FOREIGN KEY (`lesson_id`) REFERENCES `lessons` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `announcements`
--
ALTER TABLE `announcements`
  ADD CONSTRAINT `ann_course_fk` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `ann_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `course_professors`
--
ALTER TABLE `course_professors`
  ADD CONSTRAINT `cp_course_fk` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cp_prof_fk` FOREIGN KEY (`professor_id`) REFERENCES `professors` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `enrollments`
--
ALTER TABLE `enrollments`
  ADD CONSTRAINT `enroll_course_fk` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `enroll_student_fk` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `grades`
--
ALTER TABLE `grades`
  ADD CONSTRAINT `grade_activity_fk` FOREIGN KEY (`activity_id`) REFERENCES `activities` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `grade_student_fk` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `lessons`
--
ALTER TABLE `lessons`
  ADD CONSTRAINT `lesson_course_fk` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `professors`
--
ALTER TABLE `professors`
  ADD CONSTRAINT `prof_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `sent_activity_emails`
--
ALTER TABLE `sent_activity_emails`
  ADD CONSTRAINT `sent_activity_emails_ibfk_1` FOREIGN KEY (`activity_id`) REFERENCES `activities` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `sent_activity_emails_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `students`
--
ALTER TABLE `students`
  ADD CONSTRAINT `student_course_fk` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `submissions`
--
ALTER TABLE `submissions`
  ADD CONSTRAINT `submissions_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `submissions_ibfk_2` FOREIGN KEY (`activity_id`) REFERENCES `activities` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_student_fk` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
