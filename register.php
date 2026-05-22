<?php
require 'config.php';
require 'academic_options.php';
require 'mail_helper.php';

if (!empty($_SESSION['user_id'])) redirect(role_home());

// Redirect direct visits to index modal
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    redirect('index.php?modal=register');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type = $_POST['account_type'] ?? 'member';
    $name = trim($_POST['name'] ?? '');
    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    $company = $_POST['company'] ?? '';
    $program = $_POST['program'] ?? '';
    $year = trim($_POST['year_level'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $p = $_POST['password'] ?? '';
    $c = $_POST['confirm_password'] ?? '';

    // Convert year input to number only.
    // Example: "1st Year" becomes 1, "2" becomes 2.
    $year_level = (int) preg_replace('/[^0-9]/', '', $year);
    if ($year_level <= 0) {
        $year_level = 1;
    }

    if (!$name || !$email || strlen($p) < 6) {
        $error = 'Complete all required fields (password min 6 chars).';
    } elseif (!in_array($company, allowed_schools(), true)) {
        $error = 'Choose a valid school.';
    } elseif ($type === 'student' && (!in_array($program, allowed_programs(), true) || school_for_program($program) !== $company)) {
        $error = 'Choose a valid program under your school.';
    } elseif ($p !== $c) {
        $error = 'Passwords do not match.';
    } else {
        try {
            $conn->beginTransaction();

            $role = $type === 'student' ? 'student' : 'member';
            $sid = null;
            $generated_student_no = null;

            if ($role === 'student') {
                /*
                 * IMPORTANT:
                 * Do NOT include student_no here.
                 * Your MySQL trigger will auto-generate student_no
                 * for new students where student_no is NULL/blank.
                 */
                $stmt = $conn->prepare('
                    INSERT INTO students(full_name, email, program, year_level)
                    VALUES (?, ?, ?, ?)
                ');
                $stmt->execute([$name, $email, $program, $year_level]);

                $sid = (int) $conn->lastInsertId();

                // Get the generated student_no from the students table.
                $stmt = $conn->prepare('SELECT student_no FROM students WHERE id = ?');
                $stmt->execute([$sid]);
                $generated_student_no = $stmt->fetchColumn();
            }

            $stmt = $conn->prepare('
                INSERT INTO users(name, email, password, role, company, phone, status, student_id)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ');
            $stmt->execute([
                $name,
                $email,
                password_hash($p, PASSWORD_DEFAULT),
                $role,
                $company,
                $phone,
                'pending',
                $sid
            ]);

            $uid = (int) $conn->lastInsertId();

            // Optional but helpful: connect students.user_id to users.id if student.
            if ($role === 'student' && $sid) {
                $stmt = $conn->prepare('UPDATE students SET user_id = ? WHERE id = ?');
                $stmt->execute([$uid, $sid]);
            }

            $conn->commit();

            send_system_email(
                $conn,
                $uid,
                $email,
                $name,
                'Registration received - awaiting approval',
                registration_pending_email($APP_NAME, $name),
                'registration_pending'
            );

            if ($role === 'student' && $generated_student_no) {
                flash('success', 'Registration successful. Your Student Number is ' . $generated_student_no . '. Please wait for admin approval before logging in.');
            } else {
                flash('success', 'Registration successful. Please wait for admin approval before logging in.');
            }

            redirect('login.php');

        } catch (PDOException $e) {
            if ($conn->inTransaction()) $conn->rollBack();

            // Use this clean message for users.
            $error = 'Email already exists or registration failed.';

            // If you still get an error, temporarily use this line instead:
            // $error = 'Registration failed: ' . $e->getMessage();
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Register | <?= e($APP_NAME) ?></title>
    <link rel="stylesheet" href="assets/css/style.css">

    <style>
        .register-card {
            width: min(1160px, 96%);
            grid-template-columns: .85fr 1.15fr;
        }

        .student-only {
            display: none;
        }

        .auto-student-no {
            background: rgba(0,0,0,.04);
            color: var(--ink-40);
            cursor: not-allowed;
        }

        .field-note {
            display: block;
            margin-top: 6px;
            font-size: .75rem;
            color: var(--ink-40);
            text-transform: none;
            letter-spacing: normal;
            font-weight: 500;
        }
    </style>
</head>

<body>
<section class="login">
    <div class="login-card register-card">

        <!-- Art -->
        <div class="login-art">
            <div class="brand">
                <div class="logo">TRX</div>
                <span><?= e($APP_NAME) ?></span>
            </div>

            <div class="login-art-body">
                <h1>Join the university portal.</h1>
                <p>After registering, your account will be reviewed by the admin before you can sign in.</p>

                <div style="margin-top:24px;display:grid;gap:10px;">
                    <?php
                    $s = [
                        ['SECA', ['BSIT','BSCS','BSCompEngr','BSCivilEng','BSArchi']],
                        ['SASE', ['Psychology','Education']],
                        ['SBMA', ['Accountancy','Tourism']],
                        ['SHS', ['ABM','STEM','HUMSS']],
                    ];

                    foreach ($s as [$code, $progs]):
                    ?>
                        <div style="background:rgba(255,255,255,.07);border-radius:12px;padding:10px 14px;">
                            <strong style="color:var(--gold-lt);font-size:.85rem;"><?= $code ?></strong>

                            <div style="margin-top:6px;display:flex;flex-wrap:wrap;gap:4px;">
                                <?php foreach ($progs as $pr): ?>
                                    <span style="background:rgba(255,255,255,.1);color:rgba(255,255,255,.7);border-radius:999px;padding:3px 9px;font-size:.72rem;">
                                        <?= $pr ?>
                                    </span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="login-art-footer">
                Already registered?
                <a href="login.php" style="color:var(--gold-lt);">Sign in here</a>
            </div>
        </div>

        <!-- Form -->
        <form class="login-form" method="post" style="gap:18px;overflow-y:auto;max-height:100vh;">
            <div>
                <h2>Create Account</h2>
                <p style="font-size:.82rem;color:var(--ink-40);margin-top:4px;">
                    Fill in all required fields below
                </p>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-error"><?= e($error) ?></div>
            <?php endif; ?>

            <label style="text-transform:uppercase;font-size:.75rem;font-weight:700;letter-spacing:.06em;">
                Account Type
                <select name="account_type" id="accountType">
                    <option value="member">Member / Client</option>
                    <option value="student">Student</option>
                </select>
            </label>

            <div class="form-grid" style="grid-template-columns:1fr 1fr;gap:14px;">
                <label>
                    Full Name
                    <input name="name" required placeholder="Juan dela Cruz">
                </label>

                <label>
                    Email
                    <input type="email" name="email" required placeholder="you@email.com">
                </label>
            </div>

            <label>
                School / Organization
                <select name="company" id="schoolSelect">
                    <?= school_options_html($_POST['company'] ?? '') ?>
                </select>
            </label>

            <div class="student-only" style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
                <label>
                    Program
                    <select name="program" id="programSelect">
                        <option value="">Select Program</option>
                    </select>
                </label>

                <label>
                    Year Level
                    <input name="year_level" placeholder="1">
                </label>
            </div>

            <div class="student-only">
                <label>
                    Student Number
                    <input class="auto-student-no" value="Auto-generated after registration" readonly disabled>
                    <span class="field-note">
                        This will be created automatically by the system after you register.
                    </span>
                </label>
            </div>

            <div class="form-grid" style="grid-template-columns:1fr 1fr;gap:14px;">
                <label>
                    Password
                    <input type="password" name="password" required placeholder="Min 6 characters">
                </label>

                <label>
                    Confirm Password
                    <input type="password" name="confirm_password" required placeholder="Repeat password">
                </label>
            </div>

            <label>
                Phone (optional)
                <input name="phone" placeholder="+63 9XX XXX XXXX">
            </label>

            <button class="btn" type="submit" style="width:100%;padding:13px;font-size:.9rem;background:var(--navy);">
                Register & Wait for Approval →
            </button>
        </form>
    </div>
</section>

<script>
const programs = {
    SECA: ['BSIT','BSCS','BSCompEngr','BSCivilEng','BSArchi'],
    SASE: ['Psychology','Education'],
    SBMA: ['Accountancy','Tourism'],
    SHS: ['ABM','STEM','HUMSS']
};

const accountType = document.getElementById('accountType');
const schoolSel = document.getElementById('schoolSelect');
const progSel = document.getElementById('programSelect');

function toggleStudentFields() {
    const isStudent = accountType.value === 'student';

    document.query