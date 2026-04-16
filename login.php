<?php
// login.php
session_start();
require 'config.php';
$conn = db_connect();

$errors = [];
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    if($username===''||$password==='') $errors[]='Semua field wajib diisi';
    if(empty($errors)){
        $stmt = $conn->prepare("SELECT id,password,role FROM users WHERE username = ? LIMIT 1");
        $stmt->bind_param("s",$username);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        if($res && password_verify($password, $res['password'])){
            if($res['role'] === 'admin'){
                // original behavior was admin login; but per request we remove admin login link.
                // Still allow login if admin account exists.
                $_SESSION['admin_id'] = $res['id'];
                $_SESSION['role'] = 'admin';
            } else {
                $_SESSION['user_id'] = $res['id'];
                $_SESSION['role'] = 'user';
            }
            header('Location: index.php'); exit;
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
