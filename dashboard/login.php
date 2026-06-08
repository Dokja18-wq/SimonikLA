<?php session_start();
include '../admin-odp/koneksi.php';
if(isset($_POST['login'])){
    $username = $_POST['username'];
    $password = md5($_POST['password']);
    $query = mysqli_query($conn, "SELECT * FROM users WHERE username='$username' AND password='$password'");
    if(mysqli_num_rows($query)>0){
        $_SESSION['admin_opd'] = mysqli_fetch_assoc($query);
        header('Location: ../admin-odp/dashboard.php');
        exit;
    } else $error = "Login gagal";
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin OPD | SiMonik LA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            background: linear-gradient(145deg, #0b2b3f 0%, #123e54 100%);
            font-family: 'Segoe UI', Roboto, 'Helvetica Neue', sans-serif;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            background: rgba(255, 255, 255, 0.96);
            backdrop-filter: blur(2px);
            border-radius: 36px;
            padding: 2rem;
            width: 100%;
            max-width: 440px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(255,255,255,0.4);
            transition: all 0.3s;
        }
        .login-card:hover {
            transform: translateY(-5px);
        }
        .brand-icon {
            text-align: center;
            margin-bottom: 1.5rem;
        }
        .brand-icon i {
            font-size: 3.5rem;
            background: linear-gradient(135deg, #f6d5a5, #f4a261);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }
        .login-title {
            font-weight: 800;
            color: #0f2b3f;
            text-align: center;
            letter-spacing: -0.3px;
        }
        .login-sub {
            text-align: center;
            color: #5f7f9c;
            font-size: 0.9rem;
            margin-bottom: 1.8rem;
        }
        .form-label {
            font-weight: 600;
            color: #1e4a62;
            margin-bottom: 0.4rem;
        }
        .form-control {
            border-radius: 40px;
            border: 1px solid #d7e4f3;
            padding: 0.7rem 1.2rem;
            background-color: #fefefe;
        }
        .form-control:focus {
            border-color: #f4a261;
            box-shadow: 0 0 0 3px rgba(244,162,97,0.2);
        }
        .btn-login {
            background: linear-gradient(95deg, #f4a261, #e76f51);
            border: none;
            border-radius: 40px;
            padding: 0.7rem;
            font-weight: 700;
            font-size: 1rem;
            transition: all 0.2s;
            width: 100%;
            color: white;
        }
        .btn-login:hover {
            transform: translateY(-2px);
            filter: brightness(1.05);
        }
        .alert {
            border-radius: 40px;
            font-size: 0.9rem;
        }
        footer {
            text-align: center;
            margin-top: 1.8rem;
            font-size: 0.7rem;
            color: #8aaec0;
        }
        footer i {
            color: #f4a261;
        }
    </style>
</head>
<body>
<div class="login-card">
    <div class="brand-icon">
        <i class="fas fa-chart-line"></i>
    </div>
    <h3 class="login-title">SiMonik LA</h3>
    <p class="login-sub">Sistem Monitoring Kinerja OPD<br>Kabupaten Lamongan</p>

    <?php if(isset($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i> <?= $error ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <form method="post">
        <div class="mb-3">
            <label class="form-label"><i class="fas fa-user me-1"></i> Username</label>
            <input type="text" name="username" class="form-control" placeholder="Masukkan username" required autofocus>
        </div>
        <div class="mb-4">
            <label class="form-label"><i class="fas fa-lock me-1"></i> Password</label>
            <input type="password" name="password" class="form-control" placeholder="Masukkan password" required>
        </div>
        <button type="submit" name="login" class="btn-login">
            <i class="fas fa-sign-in-alt me-2"></i> Masuk ke Dashboard
        </button>
    </form>
    <footer>
        <i class="fas fa-database"></i> Data real-time • Terintegrasi dengan MongoDB
    </footer>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>