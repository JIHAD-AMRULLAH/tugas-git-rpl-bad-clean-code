<?php
session_start();
require 'config.php';

$conn = db_connect();

// ================== CONSTANT ==================
define('ROLE_ADMIN', 'admin');
define('ROLE_USER', 'user');

// ================== FUNCTIONS ==================

/**
 * Validasi input login
 */
function validateLoginInput($username, $password) {
    $errors = [];

    if (empty($username) || empty($password)) {
        $errors[] = 'Semua field wajib diisi';
    }

    return $errors;
}

/**
 * Ambil user dari database berdasarkan username
 */
function fetchUserByUsername($conn, $username) {
    $query = "SELECT id, password, role FROM users WHERE username = ? LIMIT 1";

    $statement = $conn->prepare($query);
    $statement->bind_param("s", $username);
    $statement->execute();

    return $statement->get_result()->fetch_assoc();
}

/**
 * Verifikasi password user
 */
function verifyUserPassword($inputPassword, $hashedPassword) {
    return password_verify($inputPassword, $hashedPassword);
}

/**
 * Set session sesuai role user
 */
function createUserSession($user) {
    if ($user['role'] === ROLE_ADMIN) {
        $_SESSION['admin_id'] = $user['id'];
        $_SESSION['role'] = ROLE_ADMIN;
    } else {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = ROLE_USER;
    }
}

/**
 * Redirect ke halaman utama
 */
function redirectToHome() {
    header('Location: index.php');
    exit;
}


// ================== MAIN PROCESS ==================

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    // 1. Validasi input
    $errors = validateLoginInput($username, $password);

    if (empty($errors)) {

        // 2. Ambil data user
        $user = fetchUserByUsername($conn, $username);

        // 3. Cek user & password
        if ($user && verifyUserPassword($password, $user['password'])) {

            // 4. Set session
            createUserSession($user);

            // 5. Redirect
            redirectToHome();

        } else {
            $errors[] = 'Username atau password salah';
        }
    }
}
?>
<!doctype html>
<html lang="id">
<head><meta charset="utf-8"><title>Login</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"></head>
<body>
<div class="container mt-5">
  <h3>Login</h3>
  <?php if($errors): ?><div class="alert alert-danger"><?php echo implode('<br>',$errors); ?></div><?php endif; ?>
  <form method="post">
    <div class="mb-2"><label>Username</label><input autofocus required class="form-control" name="username"></div>
    <div class="mb-2"><label>Password</label><input required type="password" class="form-control" name="password"></div>
    <button class="btn btn-primary">Login</button>
  </form>
  <p class="mt-2">Belum punya akun? <a href="register.php">Register</a></p>
</div>
</body></html>
