<?php
session_start();

// ===== خروج از سیستم =====
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: login.php?logout=1');
    exit;
}

// ===== اتصال به وردپرس =====
require_once('../../../wp-load.php');
global $wpdb;

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize_text_field($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = 'لطفاً نام کاربری و رمز عبور را وارد کنید.';
    } else {
        $table_name = $wpdb->prefix . 'lp_users';
        $user = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$table_name} WHERE username = %s",
            $username
        ));
        
        if ($user && password_verify($password, $user->password)) {
            $_SESSION['user_id'] = $user->id;
            $_SESSION['username'] = $user->username;
            $_SESSION['display_name'] = $user->display_name;
            header('Location: admin.php?page=lp-panel');
            exit;
        } else {
            $error = 'نام کاربری یا رمز عبور اشتباه است.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fa">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ورود به پنل مدیریت</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #0f0f1a;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            color: #fff;
        }
        .login-container {
            background: #1a1a2e;
            padding: 40px;
            border-radius: 12px;
            width: 100%;
            max-width: 400px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.7);
        }
        .login-container h1 {
            text-align: center;
            font-size: 28px;
            margin-bottom: 8px;
            color: #e1ec00;
            font-weight: 700;
            letter-spacing: 2px;
        }
        .login-container .subtitle {
            text-align: center;
            color: #888;
            margin-bottom: 30px;
            font-size: 14px;
        }
        .login-container input {
            width: 100%;
            padding: 12px 16px;
            margin-bottom: 16px;
            background: #2a2a42;
            border: 1px solid #333;
            border-radius: 6px;
            color: #fff;
            font-size: 16px;
            transition: border 0.3s;
        }
        .login-container input:focus {
            border-color: #e1ec00;
            outline: none;
        }
        .login-container button {
            width: 100%;
            padding: 12px;
            background: #e1ec00;
            color: #000;
            border: none;
            border-radius: 6px;
            font-size: 18px;
            font-weight: 700;
            cursor: pointer;
            transition: background 0.3s;
        }
        .login-container button:hover {
            background: #c5cf00;
        }
        .login-container .error {
            background: rgba(255, 50, 50, 0.15);
            color: #ff6b6b;
            padding: 10px 14px;
            border-radius: 6px;
            margin-bottom: 16px;
            border: 1px solid rgba(255, 50, 50, 0.2);
            font-size: 14px;
            text-align: center;
        }
        .login-container .success {
            background: rgba(50, 255, 50, 0.1);
            color: #6bff6b;
            padding: 10px 14px;
            border-radius: 6px;
            margin-bottom: 16px;
            border: 1px solid rgba(50, 255, 50, 0.2);
            font-size: 14px;
            text-align: center;
        }
        .login-container .footer {
            text-align: center;
            margin-top: 20px;
            font-size: 12px;
            color: #555;
        }
        @media (max-width: 480px) {
            .login-container { padding: 24px; }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h1>🏆 LIVE POINT</h1>
        <div class="subtitle">ورود به پنل مدیریت</div>
        
        <?php if (isset($_GET['logout'])): ?>
            <div class="success">✅ با موفقیت خارج شدید.</div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="error">❌ <?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="post">
            <input type="text" name="username" placeholder="نام کاربری" required autofocus>
            <input type="password" name="password" placeholder="رمز عبور" required>
            <button type="submit">ورود به پنل</button>
        </form>
        <div class="footer">تمامی حقوق محفوظ است</div>
    </div>
</body>
</html>