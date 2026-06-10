<?php
session_start();
if(!isset($_SESSION['admin_opd'])){
    header('Location: ../dashboard/login.php'); 
    exit;
}
$user = $_SESSION['admin_opd'];
$opd = $user['opd'];
include 'koneksi.php';

// Fungsi sinkronisasi ke MongoDB via Node.js
function syncToMongo($action, $data) {
    $url = 'http://localhost:3000/api/sync';
    $payload = json_encode(['action' => $action, 'data' => $data]);
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 2);
    curl_exec($ch);
    curl_close($ch);
}

// Tambah data
if(isset($_POST['simpan'])){
    $nama_program = mysqli_real_escape_string($conn, $_POST['nama_program']);
    $target = (int)$_POST['target'];
    $realisasi = (int)$_POST['realisasi'];
    $tahun = (int)$_POST['tahun'];
    $bulan = !empty($_POST['bulan']) ? (int)$_POST['bulan'] : null;
    $triwulan = !empty($_POST['triwulan']) ? (int)$_POST['triwulan'] : null;
    $keterangan = mysqli_real_escape_string($conn, $_POST['keterangan']);
    $query = "INSERT INTO kinerja (nama_program, opd, target, realisasi, tahun, bulan, triwulan, keterangan) 
              VALUES ('$nama_program', '$opd', '$target', '$realisasi', '$tahun', '$bulan', '$triwulan', '$keterangan')";
    if(mysqli_query($conn, $query)){
        $id_baru = mysqli_insert_id($conn);
        syncToMongo('create', [
            'id' => $id_baru,
            'nama_program' => $nama_program,
            'opd' => $opd,
            'target' => $target,
            'realisasi' => $realisasi,
            'tahun' => $tahun,
            'bulan' => $bulan,
            'triwulan' => $triwulan,
            'keterangan' => $keterangan
        ]);
        echo "<script>alert('Data tersimpan'); window.location.href='dashboard.php';</script>";
    } else {
        echo "<script>alert('Gagal menyimpan');</script>";
    }
}

// Edit realisasi
if(isset($_POST['edit'])){
    $id = (int)$_POST['id'];
    $realisasi = (int)$_POST['realisasi'];
    mysqli_query($conn, "UPDATE kinerja SET realisasi='$realisasi' WHERE id=$id AND opd='$opd'");
    $result = mysqli_query($conn, "SELECT * FROM kinerja WHERE id=$id");
    $row = mysqli_fetch_assoc($result);
    syncToMongo('update', $row);
    echo "<script>alert('Data diupdate'); window.location.href='dashboard.php';</script>";
}

// Hapus data
if(isset($_GET['hapus'])){
    $id = (int)$_GET['hapus'];
    mysqli_query($conn, "DELETE FROM kinerja WHERE id=$id AND opd='$opd'");
    syncToMongo('delete', ['id' => $id]);
    echo "<script>alert('Data terhapus'); window.location.href='dashboard.php';</script>";
}

$data = mysqli_query($conn, "SELECT * FROM kinerja WHERE opd='$opd' ORDER BY tahun DESC, bulan ASC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin OPD - <?= htmlspecialchars($opd) ?></title>
    <!-- Bootstrap 5, Font Awesome, Google Fonts -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,400;14..32,500;14..32,600;14..32,700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            background: linear-gradient(145deg, #eef2f7 0%, #d9e2ec 100%);
            font-family: 'Inter', 'Segoe UI', sans-serif;
        }
        /* Sidebar style (mirip dashboard real-time) */
        .sidebar {
            background: rgba(15, 35, 55, 0.92);
            backdrop-filter: blur(8px);
            min-height: 100vh;
            box-shadow: 4px 0 20px rgba(0, 0, 0, 0.08);
            border-right: 1px solid rgba(255, 255, 255, 0.2);
        }
        .sidebar .brand {
            font-size: 1.6rem;
            font-weight: 700;
            padding: 1.8rem 1rem;
            text-align: center;
            background: linear-gradient(135deg, #f6d5a5, #f4a261);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            border-bottom: 1px solid rgba(255,255,255,0.15);
            letter-spacing: -0.5px;
        }
        .sidebar .brand small {
            font-size: 0.7rem;
            color: #aac8e0;
            background: none;
            -webkit-background-clip: unset;
            background-clip: unset;
        }
        .sidebar .nav-link {
            color: #e2edf7;
            font-weight: 500;
            padding: 0.75rem 1.8rem;
            margin: 0.3rem 0.8rem;
            border-radius: 40px;
            transition: all 0.2s ease;
        }
        .sidebar .nav-link i {
            width: 28px;
            font-size: 1.1rem;
        }
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            background: rgba(244, 162, 97, 0.9);
            color: #0b2b3f;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            transform: translateX(4px);
        }
        /* Main content */
        .main-content {
            padding: 2rem 2rem;
        }
        .header-title h2 {
            font-weight: 800;
            background: linear-gradient(135deg, #1e4663, #0f2b3f);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }
        .btn-logout {
            background: linear-gradient(95deg, #2c6e9e, #1e4a6e);
            border: none;
            border-radius: 40px;
            padding: 0.5rem 1.4rem;
            font-weight: 600;
            color: white;
            transition: 0.2s;
        }
        .btn-logout:hover {
            transform: translateY(-2px);
            background: #1e5a74;
            color: white;
        }
        /* Card style */
        .card-modern {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 32px;
            border: 1px solid rgba(255,255,255,0.6);
            box-shadow: 0 12px 24px -12px rgba(0, 0, 0, 0.12);
            overflow: hidden;
            transition: 0.25s;
        }
        .card-header-modern {
            background: linear-gradient(135deg, #084094, #022257);
            color: white;
            font-weight: 600;
            padding: 1rem 1.5rem;
            border: none;
        }
        .card-header-info {
            background: linear-gradient(135deg, #084094, #022257);
            color: white;
            font-weight: 600;
            padding: 1rem 1.5rem;
        }
        .form-control, .form-select {
            border-radius: 16px;
            border: 1px solid #d7e4f3;
            padding: 0.6rem 1rem;
            transition: 0.2s;
        }
        .form-control:focus, .form-select:focus {
            border-color: #f4a261;
            box-shadow: 0 0 0 3px rgba(244,162,97,0.2);
        }
        .btn-save {
            background: linear-gradient(95deg, #084094, #022257);
            border: none;
            border-radius: 40px;
            padding: 0.6rem 1.8rem;
            font-weight: 600;
            color: white;
            transition: 0.2s;
        }
        .btn-save:hover {
            transform: translateY(-2px);
            filter: brightness(1.05);
        }
        .table thead {
            background: #0d6efd;
            color: white;
        }
        .table thead th {
            font-weight: 600;
            border: none;
            padding: 12px 8px;
        }
        .table tbody tr {
            transition: 0.2s;
        }
        .table tbody tr:hover {
            background: #eef5ff;
        }
        .persen {
            font-weight: 700;
            text-align: center;
            border-radius: 20px;
            padding: 4px 10px;
            display: inline-block;
            min-width: 70px;
        }
        .persen-success {
            background: #198754;
            color: white;
        }
        .persen-warning {
            background: #ffc107;
            color: black;
        }
        .persen-danger {
            background: #dc3545;
            color: white;
        }
        .btn-edit, .btn-delete {
            border-radius: 30px;
            padding: 5px 12px;
            margin: 0 3px;
        }
        .btn-edit {
            background: #011a6b;
            border: none;
            color: white;
        }
        .btn-edit:hover {
            background: #0343b9;
        }
        .btn-delete {
            background: #dc3545;
            border: none;
        }
        footer {
            font-size: 0.75rem;
            color: #5b7a99;
            text-align: center;
            padding-top: 1rem;
            margin-top: 2rem;
            border-top: 1px solid rgba(100, 130, 150, 0.3);
        }
        @media (max-width: 768px) {
            .sidebar {
                min-height: auto;
                margin-bottom: 1rem;
            }
            .main-content {
                padding: 1rem;
            }
        }
    </style>
</head>
<body>
<div class="container-fluid p-0">
    <div class="row g-0">
        <!-- SIDEBAR (sama dengan dashboard real-time) -->
        <div class="col-md-3 col-lg-2 sidebar">
            <div class="brand">
                <i class="fas fa-chart-line me-2"></i> SiMonik LA
                <br><small>ADMIN OPD</small>
            </div>
            <nav class="nav flex-column mt-3">
                <a class="nav-link" href="../dashboard/dashboard_pemimpin.html"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                <a class="nav-link active" href="dashboard.php"><i class="fas fa-building"></i> OPD</a>
                <a class="nav-link" href="../dashboard/pelaporan.html"><i class="fas fa-file-alt"></i> Pelaporan</a>
            </nav>
        </div>

        <!-- MAIN CONTENT -->
        <div class="col-md-9 col-lg-10 main-content">
            <div class="header-title mb-4 d-flex justify-content-between align-items-center flex-wrap">
                <div>
                    <h2><i class="fas fa-user-shield me-2"></i> Panel Admin OPD</h2>
                    <p class="text-muted">Selamat datang, <?= htmlspecialchars($opd) ?> • Kelola data capaian kinerja</p>
                </div>
            </div>

            <!-- Form Tambah Capaian -->
            <div class="card-modern mb-4">
                <div class="card-header-modern">
                    <i class="fas fa-plus-circle me-2"></i> Tambah Capaian Kinerja (Bulanan/Triwulan)
                </div>
                <div class="card-body p-4">
                    <form method="post">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Nama Program</label>
                                <input type="text" name="nama_program" class="form-control" placeholder="Contoh: Peningkatan Pelayanan Publik" required>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label fw-semibold">Target</label>
                                <input type="number" name="target" class="form-control" placeholder="Target" required>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label fw-semibold">Realisasi</label>
                                <input type="number" name="realisasi" class="form-control" placeholder="Realisasi" required>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label fw-semibold">Tahun</label>
                                <input type="number" name="tahun" class="form-control" placeholder="Tahun" required>
                            </div>
                            <div class="col-md-1">
                                <label class="form-label fw-semibold">Bulan</label>
                                <select name="bulan" class="form-select">
                                    <option value="">-</option>
                                    <?php for($i=1;$i<=12;$i++) echo "<option>$i</option>"; ?>
                                </select>
                            </div>
                            <div class="col-md-1">
                                <label class="form-label fw-semibold">Triwulan</label>
                                <select name="triwulan" class="form-select">
                                    <option value="">-</option>
                                    <option>1</option><option>2</option><option>3</option><option>4</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Keterangan</label>
                                <textarea name="keterangan" class="form-control" rows="2" placeholder="Opsional"></textarea>
                            </div>
                            <div class="col-12">
                                <button type="submit" name="simpan" class="btn btn-save"><i class="fas fa-save me-2"></i> Simpan Data</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Riwayat Capaian -->
            <div class="card-modern">
                <div class="card-header-info">
                    <i class="fas fa-history me-2"></i> Riwayat Capaian <?= htmlspecialchars($opd) ?>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Program</th>
                                    <th>Target</th>
                                    <th>Realisasi</th>
                                    <th>Capaian</th>
                                    <th>Tahun</th>
                                    <th>Bulan</th>
                                    <th>Triwulan</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($row = mysqli_fetch_assoc($data)): 
                                    $capaian = ($row['realisasi']/$row['target'])*100;
                                    $persenClass = $capaian>=80 ? 'persen-success' : ($capaian>=60 ? 'persen-warning' : 'persen-danger');
                                ?>
                                <tr>
                                    <td class="fw-semibold"><?= htmlspecialchars($row['nama_program']) ?></td>
                                    <td><?= $row['target'] ?></td>
                                    <td><?= $row['realisasi'] ?></td>
                                    <td><span class="persen <?= $persenClass ?>"><?= round($capaian,1) ?>%</span></td>
                                    <td><?= $row['tahun'] ?></td>
                                    <td><?= $row['bulan'] ?: '-' ?></td>
                                    <td><?= $row['triwulan'] ?: '-' ?></td>
                                    <td>
                                        <form method="post" style="display:inline-block" class="d-flex gap-1">
                                            <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                            <input type="number" name="realisasi" placeholder="Realisasi baru" required style="width:85px; border-radius:30px; border:1px solid #ccc; padding:4px 8px;">
                                            <button type="submit" name="edit" class="btn btn-edit btn-sm"><i class="fas fa-pen"></i></button>
                                        </form>
                                        <a href="?hapus=<?= $row['id'] ?>" class="btn btn-delete btn-sm" onclick="return confirm('Yakin hapus data ini?')"><i class="fas fa-trash"></i></a>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <footer>
                <i class="fas fa-database me-1"></i> Data tersimpan di MySQL dan tersinkronasi real-time ke MongoDB • Dashboard Eksekutif terupdate otomatis
            </footer>
        </div>
    </div>
</div>
</body>
</html>