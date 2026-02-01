<?php
session_start();
include 'koneksi.php';

// 1. CEK LOGIN
if (isset($_SESSION['role'])) {
    $role = strtolower($_SESSION['role']);
    if ($role == 'mahasiswa') header("Location: dashboard_mhs.php");
    else header("Location: dashboard_dosen.php");
    exit;
}

// =========================================================
// 2. QUERY DATABASE (MENARIK DATA UNTUK TAMPILAN)
// =========================================================

// A. HEADER & FOOTER
$q_web = mysqli_query($conn, "SELECT * FROM konfig_web LIMIT 1");
$web = mysqli_fetch_assoc($q_web);

// B. MENU UTAMA (KIRI)
$q_menu = mysqli_query($conn, "SELECT * FROM menu_publik ORDER BY urutan ASC");

// C. KONTEN ARTIKEL (TENGAH - DARI TABEL BARU 'konten_publik')
$q_konten = mysqli_query($conn, "SELECT * FROM konten_publik ORDER BY tanggal DESC");

// D. DEADLINE PENTING (ASIDE KANAN)
$q_deadline = mysqli_query($conn, "SELECT * FROM deadline ORDER BY tanggal ASC");

// E. DOKUMEN PUBLIK (ASIDE KANAN)
$q_doc = mysqli_query($conn, "SELECT * FROM dokumen_publik");

// F. PERIODE SEMESTER (ASIDE KANAN)
$q_periode = mysqli_query($conn, "SELECT * FROM periode_aktif LIMIT 1");
$periode = mysqli_fetch_assoc($q_periode);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $web['nama_web'] ?> - Portal Akademik</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="style.css">
    <style>
        .card-img-top-placeholder {
            height: 140px;
            background: linear-gradient(135deg, #e9ecef 0%, #dee2e6 100%);
            display: flex; align-items: center; justify-content: center; color: #adb5bd; font-size: 3rem;
        }
        .author-badge { font-size: 0.75rem; padding: 5px 10px; border-radius: 20px; font-weight: 600; }
        .card-hover:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important; transition: all 0.3s ease; }
    </style>
</head>
<body>

    <header>
        <div class="container d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
                <i class="bi bi-mortarboard-fill fs-3 me-3"></i>
                <div>
                    <h5 class="m-0 fw-bold"><?= $web['nama_web'] ?></h5>
                    <small style="opacity: 0.8;"><?= $web['slogan'] ?></small>
                </div>
            </div>
            <div class="d-flex align-items-center">
                <div class="text-end me-3 d-none d-md-block">
                    <span class="d-block fw-bold">Tamu (Guest)</span>
                    <small style="opacity: 0.8;">Akses Publik</small>
                </div>
                <a href="login.php" class="btn btn-outline-light btn-sm ms-2">
                    <i class="bi bi-box-arrow-in-right"></i> Login
                </a>
            </div>
        </div>
    </header>

    <div class="container my-4">
        <div class="row">
            
            <div class="col-lg-3 mb-4">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white fw-bold py-3"><i class="bi bi-grid-fill me-2"></i> Menu Utama</div>
                    <div class="card-body p-2">
                        <nav class="nav flex-column gap-1">
                            <?php while($m = mysqli_fetch_assoc($q_menu)): ?>
                                <a class="nav-link <?= ($m['link']=='index.php') ? 'active' : '' ?>" href="<?= $m['link'] ?>">
                                    <i class="<?= $m['icon'] ?> me-2"></i> <?= $m['nama_menu'] ?>
                                </a>
                            <?php endwhile; ?>
                        </nav>
                    </div>
                </div>
            </div>

            <div class="col-lg-6 mb-4">
                
                <div class="mb-4">
                    <h4 class="fw-bold text-dark">Selamat Datang di Portal <?= $web['nama_web'] ?> 👋</h4>
                    <p class="text-secondary small">Berikut rangkuman informasi dan pengumuman terbaru.</p>
                </div>

                <div class="row g-3">
                    <?php if($q_konten && mysqli_num_rows($q_konten) > 0): ?>
                        <?php while($row = mysqli_fetch_assoc($q_konten)): ?>
                            <div class="col-md-6"> <div class="card h-100 border-0 shadow-sm card-hover">
                                    
                                    <?php 
                                        $path_gambar = 'assets/img/' . ($row['gambar'] ?? '');
                                        if (!empty($row['gambar']) && file_exists($path_gambar)) {
                                            echo "<img src='$path_gambar' class='card-img-top' style='height: 160px; object-fit: cover;' alt='News Image'>";
                                        } else {
                                            echo "<div class='card-img-top-placeholder rounded-top'><i class='bi bi-image'></i></div>";
                                        }
                                    ?>

                                    <div class="card-body d-flex flex-column">
                                        <h6 class="card-title fw-bold mb-3" style="min-height: 40px;"><?= htmlspecialchars($row['judul']) ?></h6>
                                        
                                        <div class="d-flex gap-2 mb-3">
                                            <span class="badge bg-light text-secondary border fw-normal">
                                                <i class="bi bi-calendar3"></i> <?= date('d M Y', strtotime($row['tanggal'])) ?>
                                            </span>
                                            <span class="badge bg-<?= $row['warna_badge'] ?> text-white author-badge">
                                                <i class="bi bi-person-fill"></i> <?= $row['penulis'] ?>
                                            </span>
                                        </div>

                                        <p class="card-text text-muted small flex-grow-1" style="display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden;">
                                            <?= htmlspecialchars($row['deskripsi']) ?>
                                        </p>
                                        
                                        <div class="mt-3 text-end">
                                            <a href="detail_artikel.php?id=<?= $row['id'] ?>" class="text-decoration-none small fw-bold text-primary">
                                                Detail Informasi <i class="bi bi-arrow-right"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="col-12">
                            <div class="alert alert-info text-center">Belum ada konten tersedia.</div>
                        </div>
                    <?php endif; ?>
                </div>
            </div> <div class="col-lg-3 mb-4">
                
                <div class="card shadow-sm border-0 mb-3 overflow-hidden">
                    <div class="card-header text-white fw-bold py-3" style="background-color: #dc3545;">
                        <i class="bi bi-alarm-fill me-2"></i> Deadline Penting
                    </div>
                    <ul class="list-group list-group-flush small">
                        <?php while($d = mysqli_fetch_assoc($q_deadline)): ?>
                            <li class="list-group-item py-2" style="background-color: <?= $d['warna_bg'] ?>;">
                                <?php 
                                    $icon_color = ($d['kategori'] == 'revisi') ? 'text-warning' : 'text-primary';
                                    $icon_type = ($d['kategori'] == 'revisi') ? 'bi-exclamation-circle-fill' : 'bi-calendar-event';
                                ?>
                                <strong><i class="bi <?= $icon_type ?> <?= $icon_color ?> me-1"></i> <?= $d['judul'] ?>:</strong> 
                                <br><span class="ms-4"><?= date('d M Y', strtotime($d['tanggal'])) ?></span>
                            </li>
                        <?php endwhile; ?>
                    </ul>
                </div>

                <div class="card shadow-sm border-0 mb-3">
                    <div class="card-header bg-white fw-bold py-3">
                        <i class="bi bi-folder2-open me-2 text-primary"></i> Dokumen Publik
                    </div>
                    <div class="list-group list-group-flush small">
                        <?php while($doc = mysqli_fetch_assoc($q_doc)): ?>
                            <a href="<?= $doc['link_file'] ?>" class="list-group-item list-group-item-action py-2">
                                <?= $doc['nama_doc'] ?> <i class="bi bi-download float-end text-muted"></i>
                            </a>
                        <?php endwhile; ?>
                    </div>
                </div>

                <div class="card shadow-sm border-0">
                    <div class="card-body text-center py-4">
                        <h6 class="fw-bold text-muted mb-1"><?= $periode['nama_periode'] ?? 'Periode' ?></h6>
                        <h1 class="display-3 fw-bold text-primary mb-0" style="letter-spacing: -2px;"><?= date('d') ?></h1>
                        <span class="text-uppercase fw-bold text-secondary ls-1"><?= date('F Y') ?></span>
                    </div>
                </div>

            </div>

        </div>
    </div>

    <footer class="text-center py-4 bg-dark text-white mt-auto">
        <div class="container">
            <h6 class="fw-bold mb-2"><?= $web['nama_web'] ?> - Universitas Pembangunan Jaya</h6>
            <small class="d-block text-white-50"><?= $web['alamat'] ?></small>
            
            <div class="social-links mt-3">
                <a href="<?= $web['fb_link'] ?>" class="text-white mx-2"><i class="bi bi-facebook"></i></a>
                <a href="<?= $web['ig_link'] ?>" class="text-white mx-2"><i class="bi bi-instagram"></i></a>
                <a href="<?= $web['tw_link'] ?>" class="text-white mx-2"><i class="bi bi-twitter-x"></i></a>
            </div>

            <hr class="border-light opacity-25 my-3">
            <small class="d-block text-white-50">&copy; <?= $web['copyright'] ?>. All Rights Reserved.</small>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>