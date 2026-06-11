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
    <!-- Bootstrap 5, Font Awesome, Google Fonts Inter -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* Reset & Global */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            background-color: #f8fafc;
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            color: #0f172a;
        }

        /* Layout */
        .app-layout {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar */
        .sidebar {
            width: 260px;
            background-color: #0f172a;
            border-right: 1px solid #1e293b;
            display: flex;
            flex-direction: column;
            flex-shrink: 0;
        }
        .sidebar .brand {
            padding: 2rem 1.5rem;
            border-bottom: 1px solid #1e293b;
            color: #ffffff;
        }
        .sidebar .brand-title {
            font-size: 1.25rem;
            font-weight: 700;
            letter-spacing: -0.025em;
            line-height: 1.2;
        }
        .sidebar .brand-subtitle {
            font-size: 0.75rem;
            font-weight: 600;
            color: #94a3b8;
            letter-spacing: 0.05em;
            margin-top: 0.25rem;
        }
        .sidebar .nav-menu {
            padding: 1.5rem 0.75rem;
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }
        .sidebar .nav-link {
            display: flex;
            align-items: center;
            color: #94a3b8;
            font-weight: 500;
            font-size: 0.875rem;
            padding: 0.75rem 1rem;
            border-radius: 8px;
            text-decoration: none;
            transition: all 0.15s ease;
        }
        .sidebar .nav-link i {
            width: 20px;
            margin-right: 10px;
            font-size: 1rem;
            text-align: center;
        }
        .sidebar .nav-link:hover {
            background-color: #1e293b;
            color: #f1f5f9;
        }
        .sidebar .nav-link.active {
            background-color: #1e293b;
            color: #ffffff;
            border-left: 3px solid #2563eb;
            border-top-left-radius: 0;
            border-bottom-left-radius: 0;
            padding-left: 13px; /* compensate border width */
        }

        /* Main Content */
        .main-content {
            flex-grow: 1;
            padding: 2rem 2.5rem;
            overflow-y: auto;
        }
        .header-title h2 {
            font-size: 1.75rem;
            font-weight: 700;
            color: #0f172a;
            letter-spacing: -0.02em;
            margin-bottom: 0.35rem;
        }
        .header-title p {
            color: #64748b;
            font-size: 0.875rem;
            margin-bottom: 0;
        }

        /* Card styles */
        .card-modern {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            overflow: hidden;
            margin-bottom: 1.75rem;
        }
        .card-header-modern {
            background-color: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            color: #0f172a;
            font-weight: 600;
            font-size: 0.9375rem;
            padding: 1rem 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .card-header-modern i {
            color: #2563eb;
        }
        .card-body-modern {
            padding: 1.5rem;
        }

        /* Form elements */
        .form-label {
            font-size: 0.8125rem;
            font-weight: 600;
            color: #475569;
            margin-bottom: 0.375rem;
        }
        .form-control, .form-select {
            border-radius: 8px;
            border: 1px solid #cbd5e1;
            padding: 0.5rem 0.75rem;
            font-size: 0.875rem;
            color: #0f172a;
            transition: all 0.15s ease;
        }
        .form-control:focus, .form-select:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 1px #2563eb;
            outline: 0;
        }
        .btn-save {
            background-color: #2563eb;
            border: 1px solid #2563eb;
            color: #ffffff;
            border-radius: 8px;
            padding: 0.5rem 1.25rem;
            font-weight: 600;
            font-size: 0.875rem;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.15s ease;
        }
        .btn-save:hover {
            background-color: #1d4ed8;
            border-color: #1d4ed8;
        }

        /* Tables */
        .table-container {
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            overflow: hidden;
        }
        .table {
            margin-bottom: 0;
        }
        .table thead {
            background-color: #f8fafc;
        }
        .table th {
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #64748b;
            border-bottom: 1px solid #e2e8f0;
            padding: 0.75rem 1rem;
        }
        .table td {
            font-size: 0.875rem;
            color: #334155;
            border-bottom: 1px solid #e2e8f0;
            padding: 0.875rem 1rem;
            vertical-align: middle;
        }
        .table tbody tr:last-child td {
            border-bottom: none;
        }
        .table tbody tr:hover {
            background-color: #f8fafc;
        }

        /* Status badges */
        .status-badge {
            font-size: 0.75rem;
            font-weight: 600;
            padding: 0.25rem 0.5rem;
            border-radius: 6px;
            display: inline-block;
        }
        .status-badge.status-success {
            background-color: #ecfdf5;
            color: #047857;
            border: 1px solid #a7f3d0;
        }
        .status-badge.status-warning {
            background-color: #fffbeb;
            color: #b45309;
            border: 1px solid #fde68a;
        }
        .status-badge.status-danger {
            background-color: #fef2f2;
            color: #b91c1c;
            border: 1px solid #fca5a5;
        }

        /* Inline edit field */
        .edit-input {
            width: 80px;
            border-radius: 6px;
            border: 1px solid #cbd5e1;
            padding: 0.25rem 0.5rem;
            font-size: 0.8125rem;
            text-align: center;
        }
        .edit-input:focus {
            border-color: #2563eb;
            outline: 0;
        }
        .btn-action-edit {
            background-color: #2563eb;
            border: 1px solid #2563eb;
            color: #ffffff;
            width: 28px;
            height: 28px;
            border-radius: 6px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 0.75rem;
            transition: all 0.15s ease;
        }
        .btn-action-edit:hover {
            background-color: #1d4ed8;
            border-color: #1d4ed8;
        }
        .btn-action-delete {
            background-color: #fef2f2;
            border: 1px solid #fecaca;
            color: #b91c1c;
            width: 28px;
            height: 28px;
            border-radius: 6px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 0.75rem;
            transition: all 0.15s ease;
            text-decoration: none;
        }
        .btn-action-delete:hover {
            background-color: #fee2e2;
            color: #991b1b;
        }

        /* Footer */
        footer {
            font-size: 0.75rem;
            color: #94a3b8;
            border-top: 1px solid #e2e8f0;
            margin-top: 2.5rem;
            padding-top: 1.25rem;
            text-align: center;
        }

        /* Responsive */
        @media (max-width: 992px) {
            .app-layout {
                flex-direction: column;
            }
            .sidebar {
                width: 100%;
                min-height: auto;
            }
            .sidebar .brand {
                padding: 1.25rem 1.5rem;
            }
            .sidebar .nav-menu {
                flex-direction: row;
                flex-wrap: wrap;
                padding: 0.75rem;
                gap: 0.5rem;
            }
            .sidebar .nav-link {
                padding: 0.5rem 0.75rem;
            }
            .sidebar .nav-link.active {
                border-left: none;
                border-bottom: 3px solid #2563eb;
                border-bottom-left-radius: 0;
                border-bottom-right-radius: 0;
                padding-left: 0.75rem;
            }
            .main-content {
                padding: 1.5rem;
            }
        }
    </style>
</head>
<body>
<div class="app-layout">
    <!-- SIDEBAR -->
    <div class="sidebar">
        <div class="brand">
            <div class="brand-title"><i class="fas fa-chart-line text-blue-500 me-1"></i> SiMonik LA</div>
            <div class="brand-subtitle">ADMIN OPD</div>
        </div>
        <nav class="nav-menu">
            <a class="nav-link" href="../dashboard/dashboard_pemimpin.html">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>
            <a class="nav-link active" href="dashboard.php">
                <i class="fas fa-building"></i> OPD
            </a>
            <a class="nav-link" href="../dashboard/pelaporan.html">
                <i class="fas fa-file-alt"></i> Pelaporan
            </a>
        </nav>
    </div>

    <!-- MAIN CONTENT -->
    <div class="main-content">
        <div class="header-title mb-4">
            <h2>Panel Admin OPD</h2>
            <p>Selamat datang, <span class="fw-semibold text-dark"><?= htmlspecialchars($opd) ?></span> • Silakan kelola data capaian kinerja instansi Anda</p>
        </div>

        <!-- Form Tambah Capaian -->
        <div class="card-modern">
            <div class="card-header-modern">
                <i class="fas fa-plus-circle"></i> Tambah Capaian Kinerja Baru
            </div>
            <div class="card-body-modern">
                <form method="post">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Nama Program Kerja</label>
                            <input type="text" name="nama_program" class="form-control" placeholder="Contoh: Peningkatan Pelayanan Publik" required>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Target Nilai</label>
                            <input type="number" name="target" class="form-control" placeholder="Target" required>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Realisasi Nilai</label>
                            <input type="number" name="realisasi" class="form-control" placeholder="Realisasi" required>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Tahun Anggaran</label>
                            <input type="number" name="tahun" class="form-control" placeholder="Tahun" value="2024" required>
                        </div>
                        <div class="col-md-1">
                            <label class="form-label">Bulan</label>
                            <select name="bulan" class="form-select">
                                <option value="">-</option>
                                <?php for($i=1;$i<=12;$i++) echo "<option>$i</option>"; ?>
                            </select>
                        </div>
                        <div class="col-md-1">
                            <label class="form-label">Triwulan</label>
                            <select name="triwulan" class="form-select">
                                <option value="">-</option>
                                <option>1</option><option>2</option><option>3</option><option>4</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Keterangan Tambahan</label>
                            <textarea name="keterangan" class="form-control" rows="2" placeholder="Opsional (contoh: hambatan atau detail tambahan)"></textarea>
                        </div>
                        <div class="col-12">
                            <button type="submit" name="simpan" class="btn btn-save">
                                <i class="fas fa-save"></i> Simpan Data Capaian
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Riwayat Capaian -->
        <div class="card-modern">
            <div class="card-header-modern">
                <i class="fas fa-history"></i> Riwayat Capaian <?= htmlspecialchars($opd) ?>
            </div>
            <div class="card-body-modern p-0">
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th>Nama Program</th>
                                <th style="width: 100px; text-align: right;">Target</th>
                                <th style="width: 100px; text-align: right;">Realisasi</th>
                                <th style="width: 100px; text-align: center;">Capaian</th>
                                <th style="width: 80px; text-align: center;">Tahun</th>
                                <th style="width: 70px; text-align: center;">Bulan</th>
                                <th style="width: 90px; text-align: center;">Triwulan</th>
                                <th style="width: 180px; text-align: center;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (mysqli_num_rows($data) == 0): ?>
                                <tr>
                                    <td colspan="8" class="text-center py-4 text-muted">Belum ada data capaian yang tersimpan.</td>
                                </tr>
                            <?php endif; ?>
                            <?php while($row = mysqli_fetch_assoc($data)): 
                                $capaian = ($row['target'] > 0) ? ($row['realisasi']/$row['target'])*100 : 0;
                                $statusClass = $capaian>=80 ? 'status-success' : ($capaian>=60 ? 'status-warning' : 'status-danger');
                            ?>
                            <tr>
                                <td class="fw-semibold text-dark"><?= htmlspecialchars($row['nama_program']) ?></td>
                                <td class="text-right font-monospace" style="text-align: right;"><?= number_format($row['target']) ?></td>
                                <td class="text-right font-monospace" style="text-align: right;"><?= number_format($row['realisasi']) ?></td>
                                <td class="text-center"><span class="status-badge <?= $statusClass ?>"><?= round($capaian,1) ?>%</span></td>
                                <td class="text-center fw-medium text-secondary"><?= $row['tahun'] ?></td>
                                <td class="text-center fw-medium text-secondary"><?= $row['bulan'] ?: '-' ?></td>
                                <td class="text-center fw-medium text-secondary"><?= $row['triwulan'] ? 'TW ' . $row['triwulan'] : '-' ?></td>
                                <td class="text-center">
                                    <form method="post" style="display:inline-block" class="d-inline-flex gap-1 align-items-center">
                                        <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                        <input type="number" name="realisasi" placeholder="Realisasi baru" required class="edit-input">
                                        <button type="submit" name="edit" class="btn btn-action-edit" title="Perbarui Realisasi">
                                            <i class="fas fa-check"></i>
                                        </button>
                                    </form>
                                    <a href="?hapus=<?= $row['id'] ?>" class="btn btn-action-delete ms-1" onclick="return confirm('Apakah Anda yakin ingin menghapus data capaian program ini?')" title="Hapus Data">
                                        <i class="fas fa-trash-can"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <footer>
            <div class="text-muted">
                <i class="fas fa-database me-1"></i> Data tersimpan di MySQL dan disinkronkan ke MongoDB • SiMonik LA
            </div>
        </footer>
    </div>
</div>
</body>
</html>