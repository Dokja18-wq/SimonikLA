<?php session_start();
include 'admin-odp/koneksi.php';
if(isset($_POST['login'])){
    $username = $_POST['username'];
    $password = md5($_POST['password']);
    $query = mysqli_query($conn, "SELECT * FROM users WHERE username='$username' AND password='$password'");
    if(mysqli_num_rows($query)>0){
        $_SESSION['admin_opd'] = mysqli_fetch_assoc($query);
        header('Location: admin-odp/dashboard.php');
    } else $error = "Login gagal";
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>body{background:linear-gradient(135deg,#1e3c72,#2a5298);height:100vh}.login-box{width:400px;margin:100px auto;background:white;padding:30px;border-radius:15px}</style>
</head>
<body>

<div class="container-fluid vh-100">
    <div class="row h-100">

        <div class="col-lg-7 d-none d-lg-flex align-items-center justify-content-center bg-primary text-white">

            <div class="text-center">

                <h1 class="display-4 fw-bold">
                    Sistem Monitoring Kinerja
                </h1>

                <h3>Pemerintah Daerah</h3>

                <img src="https://cdn-icons-png.flaticon.com/512/3135/3135715.png"
                     width="250"
                     class="mt-4">

            </div>

        </div>

        <div class="col-lg-5 d-flex align-items-center justify-content-center">

            <div class="card shadow-lg border-0 p-4" style="width:420px;border-radius:25px">

                <div class="text-center mb-4">

                    <h2 class="fw-bold text-primary">
                        Login Admin OPD
                    </h2>

                    <p class="text-muted">
                        Masuk ke sistem monitoring
                    </p>

                </div>

                <?php if(isset($error)): ?>
                <div class="alert alert-danger">
                    <?= $error ?>
                </div>
                <?php endif; ?>

                <form method="post">

                    <div class="mb-3">
                        <label>Username</label>
                        <input type="text"
                               name="username"
                               class="form-control form-control-lg"
                               required>
                    </div>

                    <div class="mb-3">
                        <label>Password</label>
                        <input type="password"
                               name="password"
                               class="form-control form-control-lg"
                               required>
                    </div>

                    <button class="btn btn-primary w-100 btn-lg"
                            name="login">

                        Login

                    </button>

                </form>

            </div>

        </div>

    </div>
</div>
</body>
</html>