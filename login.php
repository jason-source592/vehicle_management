<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';

if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username && $password) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? LIMIT 1");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id']   = $user['id'];
            $_SESSION['username']  = $user['username'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['role']      = $user['role'];
            header('Location: index.php');
            exit;
        } else {
            $error = 'Sai tên đăng nhập hoặc mật khẩu!';
        }
    } else {
        $error = 'Vui lòng nhập đầy đủ thông tin.';
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Đăng Nhập — <?= SITE_NAME ?></title>
<link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
:root {
    --navy: #0f1c2e;
    --navy2: #162032;
    --blue: #1a56db;
    --blue-light: #3b82f6;
    --accent: #f59e0b;
    --white: #ffffff;
    --gray: #94a3b8;
    --border: rgba(255,255,255,0.08);
}
body {
    font-family: 'Be Vietnam Pro', sans-serif;
    background: var(--navy);
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    overflow: hidden;
}
body::before {
    content: '';
    position: absolute;
    top: -200px; left: -200px;
    width: 600px; height: 600px;
    background: radial-gradient(circle, rgba(26,86,219,0.15) 0%, transparent 70%);
    border-radius: 50%;
}
body::after {
    content: '';
    position: absolute;
    bottom: -150px; right: -150px;
    width: 500px; height: 500px;
    background: radial-gradient(circle, rgba(245,158,11,0.08) 0%, transparent 70%);
    border-radius: 50%;
}
.login-wrap {
    width: 420px;
    position: relative;
    z-index: 1;
    animation: fadeUp 0.5s ease;
}
@keyframes fadeUp {
    from { opacity: 0; transform: translateY(20px); }
    to   { opacity: 1; transform: translateY(0); }
}
.logo-area {
    text-align: center;
    margin-bottom: 32px;
}
.logo-icon { display: inline-flex; align-items: center; justify-content: center; margin-bottom: 16px; padding: 8px; border-radius: 16px; box-shadow: 0 8px 32px rgba(0,0,0,0.25); background: transparent; } .logo-icon img { width: 72px; height: 72px; object-fit: contain; display: block; }
.logo-area h1 {
    font-size: 22px;
    font-weight: 700;
    color: var(--white);
    letter-spacing: -0.3px;
}
.logo-area p {
    font-size: 13px;
    color: var(--gray);
    margin-top: 4px;
}
.card {
    background: var(--navy2);
    border: 1px solid var(--border);
    border-radius: 20px;
    padding: 36px;
    box-shadow: 0 24px 64px rgba(0,0,0,0.4);
}
.form-group { margin-bottom: 20px; }
label {
    display: block;
    font-size: 13px;
    font-weight: 500;
    color: var(--gray);
    margin-bottom: 8px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
input[type=text], input[type=password] {
    width: 100%;
    padding: 13px 16px;
    background: rgba(255,255,255,0.05);
    border: 1px solid var(--border);
    border-radius: 10px;
    color: var(--white);
    font-family: 'Be Vietnam Pro', sans-serif;
    font-size: 15px;
    outline: none;
    transition: border-color 0.2s, background 0.2s;
}
input:focus {
    border-color: var(--blue-light);
    background: rgba(59,130,246,0.07);
}
.btn-login {
    width: 100%;
    padding: 14px;
    background: linear-gradient(135deg, var(--blue), var(--blue-light));
    border: none;
    border-radius: 10px;
    color: white;
    font-family: 'Be Vietnam Pro', sans-serif;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    transition: transform 0.15s, box-shadow 0.15s;
    margin-top: 8px;
    box-shadow: 0 4px 20px rgba(26,86,219,0.4);
}
.btn-login:hover {
    transform: translateY(-1px);
    box-shadow: 0 6px 28px rgba(26,86,219,0.5);
}
.alert-error {
    background: rgba(239,68,68,0.12);
    border: 1px solid rgba(239,68,68,0.3);
    color: #fca5a5;
    padding: 12px 16px;
    border-radius: 10px;
    font-size: 14px;
    margin-bottom: 20px;
}
.hint {
    text-align: center;
    margin-top: 24px;
    font-size: 12px;
    color: rgba(148,163,184,0.5);
}
.guard-link {
    text-align: center;
    margin-top: 16px;
}
.guard-link a {
    color: var(--accent);
    text-decoration: none;
    font-size: 13px;
    font-weight: 500;
}
.guard-link a:hover { text-decoration: underline; }
</style>
</head>
<body>
<div class="login-wrap">
    <div class="logo-area">
       <div class="logo-icon"> <img src="icon/logo_login.ico" alt="Logo Hệ Thống Quản Lý Xe"> </div>
        <h1><?= SITE_NAME ?></h1>
        <p>Quản lý điều phối xe nội bộ</p>
    </div>
    <div class="card">
        <?php if ($error): ?>
        <div class="alert-error">⚠️ <?= sanitize($error) ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label>Tên đăng nhập</label>
                <input type="text" name="username" placeholder="Nhập username" value="<?= sanitize($_POST['username'] ?? '') ?>" autofocus>
            </div>
            <div class="form-group">
                <label>Mật khẩu</label>
                <input type="password" name="password" placeholder="••••••••">
            </div>
            <button type="submit" class="btn-login">Đăng Nhập</button>
        </form>
        <div class="hint"></div>
    </div>
    <div class="guard-link">
        <a href="dashboard.php" target="_blank">🖥️ Màn hình Phòng Bảo Vệ (không cần đăng nhập)</a>
    </div>
</div>
</body>
</html>
