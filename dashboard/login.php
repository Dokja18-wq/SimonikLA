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
    } else $error = "Kombinasi username atau password salah.";
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk | SiMonik LA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            background-color: #f8fafc;
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #0f172a;
        }
        .login-card {
            background: #ffffff;
            border-radius: 12px;
            padding: 2.5rem 2rem;
            width: 100%;
            max-width: 420px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05), 0 1px 2px -1px rgba(0, 0, 0, 0.05);
        }
        .brand-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            margin-bottom: 2rem;
        }
        .brand-badge {
            width: 48px;
            height: 48px;
            background-color: #eff6ff;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1rem;
            border: 1px solid #dbeafe;
        }
        .brand-badge i {
            font-size: 1.5rem;
            color: #2563eb;
        }
        .login-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 0.25rem;
            letter-spacing: -0.025em;
        }
        .login-sub {
            color: #64748b;
            font-size: 0.875rem;
            line-height: 1.25rem;
        }
        .form-label {
            font-weight: 500;
            font-size: 0.875rem;
            color: #334155;
            margin-bottom: 0.5rem;
        }
        .form-control {
            border-radius: 8px;
            border: 1px solid #cbd5e1;
            padding: 0.625rem 0.875rem;
            background-color: #ffffff;
            font-size: 0.875rem;
            color: #0f172a;
            transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
        }
        .form-control:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 1px #2563eb;
            outline: 0;
            background-color: #ffffff;
        }
        .btn-login {
            background-color: #2563eb;
            border: 1px solid #2563eb;
            border-radius: 8px;
            padding: 0.625rem;
            font-weight: 600;
            font-size: 0.875rem;
            transition: all 0.15s ease;
            width: 100%;
            color: #ffffff;
            margin-top: 0.5rem;
        }
        .btn-login:hover {
            background-color: #1d4ed8;
            border-color: #1d4ed8;
        }
        .alert {
            border-radius: 8px;
            font-size: 0.8125rem;
            padding: 0.75rem 1rem;
            border: 1px solid #fecaca;
            background-color: #fef2f2;
            color: #991b1b;
            margin-bottom: 1.5rem;
        }
        .alert-dismissible .btn-close {
            padding: 0.9rem 1rem;
        }
        footer {
            text-align: center;
            margin-top: 2rem;
            font-size: 0.75rem;
            color: #94a3b8;
            border-top: 1px solid #f1f5f9;
            padding-top: 1.25rem;
        }
        footer i {
            color: #64748b;
        }
    </style>
</head>
<body>
<div class="login-card">
    <div class="brand-container">
        <div class="brand-badge">
            <i class="fas fa-chart-line"></i>
        </div>
        <h3 class="login-title">SiMonik LA</h3>
        <p class="login-sub">Sistem Monitoring Kinerja OPD<br>Kabupaten Lamongan</p>
    </div>

    <?php if(isset($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-circle-exclamation me-1.5"></i> <?= $error ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <form method="post">
        <div class="mb-3">
            <label class="form-label">Username</label>
            <input type="text" name="username" class="form-control" placeholder="Masukkan username" required autofocus>
        </div>
        <div class="mb-4">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control" placeholder="Masukkan password" required>
        </div>
        <button type="submit" name="login" class="btn-login">
            Masuk ke Dashboard
        </button>
    </form>
    <footer>
        <i class="fas fa-circle-info me-1"></i> Data real-time sinkron dengan MySQL & MongoDB
    </footer>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>