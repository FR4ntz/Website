<?php
session_start();
include 'koneksi.php';

// 1. CEK KEAMANAN AKSES
if (!isset($_SESSION['role']) || strtolower($_SESSION['role']) != 'mahasiswa') { 
    header("Location: index.php"); 
    exit; 
}

// Ambil NIM & User
$nim = isset($_SESSION['username']) ? $_SESSION['username'] : (isset($_SESSION['nim']) ? $_SESSION['nim'] : '');
$nama_user = isset($_SESSION['user']) ? $_SESSION['user'] : 'Mahasiswa';

// =================================================================
// 2. QUERY DATABASE (DATA DINAMIS UNTUK LAYOUT)
// =================================================================

// A. IDENTITAS WEB (Header & Footer)
$q_web = mysqli_query($conn, "SELECT * FROM konfig_web LIMIT 1");
$web = mysqli_fetch_assoc($q_web);

// B. KONTEN ARTIKEL (Untuk Halaman Home)
$q_konten = mysqli_query($conn, "SELECT * FROM konten_publik ORDER BY tanggal DESC");

// C. WIDGET ASIDE (Kanan)
$q_deadline = mysqli_query($conn, "SELECT * FROM deadline ORDER BY tanggal ASC");
$q_doc = mysqli_query($conn, "SELECT * FROM dokumen_publik");
$q_periode = mysqli_query($conn, "SELECT * FROM periode_aktif LIMIT 1");
$periode = mysqli_fetch_assoc($q_periode);

// D. DATA MAHASISWA (SKS, JSDP, PROPOSAL)
$mhs = @mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM mahasiswa WHERE nim='$nim'"));
$prop = @mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM proposal WHERE nim='$nim'"));

// Logika SKS & JSDP
$sks = $mhs['total_sks'] ?? $mhs['Total_SKS'] ?? $mhs['SKS'] ?? $mhs['sks'] ?? 0;
$jsdp = $mhs['jsdp_poin'] ?? $mhs['Total_JSDP'] ?? $mhs['JSDP'] ?? $mhs['jsdp'] ?? 0;

// Logika Notifikasi
$notif_query = @mysqli_query($conn, "SELECT * FROM notifikasi WHERE nim='$nim' AND is_read=0 ORDER BY tanggal DESC");

// 3. TENTUKAN HALAMAN AKTIF
$page = isset($_GET['page']) ? $_GET['page'] : 'home';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - <?= $web['nama_web'] ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="style.css">
    <style>
        .author-badge { font-size: 0.75rem; padding: 5px 10px; border-radius: 20px; font-weight: 600; }
        .card-img-top-placeholder { height: 180px; background: #e9ecef; display: flex; align-items: center; justify-content: center; color: #adb5bd; }
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
                    <span class="d-block fw-bold"><?= htmlspecialchars($nama_user) ?></span>
                    <small style="opacity: 0.8;"><?= htmlspecialchars($nim) ?></small>
                </div>
                <a href="logout.php" class="btn btn-outline-light btn-sm ms-2">
                    <i class="bi bi-box-arrow-right"></i> Logout
                </a>
            </div>
        </div>
    </header>

    <div class="container my-4">
        <div class="row">
            
            <div class="col-lg-3">
                <div class="card shadow-sm border-0 mb-3">
                    <div class="card-header bg-white fw-bold"><i class="bi bi-menu-button-wide"></i> Menu Mahasiswa</div>
                    <div class="card-body p-2">
                        <nav class="nav flex-column gap-1">
                            <a class="nav-link <?= ($page=='home')?'active':'' ?>" href="dashboard_mhs.php?page=home"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a>
                            <a class="nav-link <?= ($page=='pengajuan')?'active':'' ?>" href="dashboard_mhs.php?page=pengajuan"><i class="bi bi-file-earmark-plus me-2"></i> Pengajuan Proposal</a>
                            <a class="nav-link <?= ($page=='bimbingan')?'active':'' ?>" href="dashboard_mhs.php?page=bimbingan"><i class="bi bi-journal-text me-2"></i> Bimbingan (Logbook)</a>
                            <a class="nav-link <?= ($page=='daftar_sidang')?'active':'' ?>" href="dashboard_mhs.php?page=daftar_sidang"><i class="bi bi-pencil-square me-2"></i> Daftar Sidang Akhir</a>
                            <a class="nav-link <?= ($page=='jadwal')?'active':'' ?>" href="dashboard_mhs.php?page=jadwal"><i class="bi bi-calendar-event me-2"></i> Jadwal Sidang</a>
                            <hr class="my-2 border-secondary opacity-25">
                            <a class="nav-link <?= ($page=='chat')?'active':'' ?>" href="dashboard_mhs.php?page=chat"><i class="bi bi-chat-dots me-2"></i> Chat Pembimbing</a>
                            <a class="nav-link <?= ($page=='ai')?'active':'' ?>" href="dashboard_mhs.php?page=ai"><i class="bi bi-stars me-2"></i> Konsultasi AI</a>
                            <a class="nav-link <?= ($page=='bantuan')?'active':'' ?>" href="dashboard_mhs.php?page=bantuan"><i class="bi bi-question-circle me-2"></i> Bantuan</a>
                        </nav>
                    </div>
                </div>
                
                <div class="card mt-3 shadow-sm border-0">
                    <div class="card-header bg-white fw-bold"><i class="bi bi-person-vcard"></i> Info Akademik</div>
                    <div class="card-body">
                        <ul class="list-group list-group-flush small">
                            <li class="list-group-item d-flex justify-content-between px-0">
                                Total SKS
                                <span class="badge <?= ($sks >= 120) ? 'text-bg-success' : 'text-bg-danger' ?> rounded-pill"><?= $sks ?> / 120</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between px-0">
                                Poin JSDP
                                <span class="badge <?= ($jsdp >= 600) ? 'text-bg-success' : 'text-bg-danger' ?> rounded-pill"><?= $jsdp ?> / 600</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <?php 
                switch ($page) {
                    case 'home':
                        ?>
                        <div class="mb-4">
                            <h5 class="fw-bold mb-1">Selamat Datang, <?= htmlspecialchars($nama_user) ?> 👋</h5>
                            <p class="text-muted small">Berikut rangkuman informasi terbaru terkait Tugas Akhir.</p>
                        </div>

                        <div class="row g-4">
                            <?php if($q_konten && mysqli_num_rows($q_konten) > 0): ?>
                                <?php while($berita = mysqli_fetch_assoc($q_konten)): ?>
                                    <div class="col-md-6">
                                        <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden">
                                            <?php 
                                                $path_gambar = 'assets/img/' . ($berita['gambar'] ?? '');
                                                if (!empty($berita['gambar']) && file_exists($path_gambar)) {
                                                    echo "<img src='$path_gambar' class='card-img-top' style='height: 180px; object-fit: cover;' alt='Gambar'>";
                                                } else {
                                                    echo "<div class='card-img-top-placeholder'><i class='bi bi-image fs-1'></i></div>";
                                                }
                                            ?>
                                            
                                            <div class="card-body d-flex flex-column p-4">
                                                <h6 class="fw-bold mb-3"><?= htmlspecialchars($berita['judul']) ?></h6>
                                                
                                                <div class="mb-3">
                                                    <span class="badge bg-light text-secondary border rounded-pill px-2 me-1">
                                                        <i class="bi bi-calendar-event"></i> <?= date('d M Y', strtotime($berita['tanggal'])) ?>
                                                    </span>
                                                    <span class="badge bg-<?= $berita['warna_badge'] ?> rounded-pill px-2">
                                                        <i class="bi bi-person"></i> <?= $berita['penulis'] ?>
                                                    </span>
                                                </div>

                                                <p class="text-secondary small mb-4 flex-grow-1" style="display: -webkit-box; -webkit-line-clamp: 3; line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden;">
                                                    <?= strip_tags($berita['deskripsi']) ?>
                                                </p>

                                                <div class="mt-auto text-end">
                                                    <a href="dashboard_mhs.php?page=detail_berita&id=<?= $berita['id'] ?>" class="text-primary text-decoration-none fw-bold small">
                                                        Detail Informasi <i class="bi bi-arrow-right ms-1"></i>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <div class="col-12"><div class="alert alert-info">Belum ada berita terbaru.</div></div>
                            <?php endif; ?>
                        </div>
                        <?php
                        break;

                    // HALAMAN DETAIL BERITA (Database)
                    case 'detail_berita':
                        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
                        $q_detail = mysqli_query($conn, "SELECT * FROM konten_publik WHERE id='$id'");
                        
                        if ($berita = mysqli_fetch_assoc($q_detail)): ?>
                            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                                <?php 
                                    $path_gambar = 'assets/img/' . ($berita['gambar'] ?? '');
                                    if (!empty($berita['gambar']) && file_exists($path_gambar)): ?>
                                    <div class="position-relative" style="height: 300px;">
                                        <img src="<?= $path_gambar ?>" class="w-100 h-100" style="object-fit: cover;">
                                        <div class="position-absolute bottom-0 start-0 w-100 p-4" style="background: linear-gradient(to top, rgba(0,0,0,0.8), transparent);">
                                            <h2 class="text-white fw-bold"><?= $berita['judul'] ?></h2>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <div class="card-body p-4 p-md-5">
                                    <div class="mb-4 text-muted small">
                                        <i class="bi bi-calendar"></i> <?= $berita['tanggal'] ?> | <i class="bi bi-person"></i> <?= $berita['penulis'] ?>
                                    </div>
                                    <article class="lh-lg text-secondary">
                                        <?= nl2br(htmlspecialchars($berita['deskripsi'])) ?>
                                    </article>
                                    <hr class="my-5">
                                    <a href="dashboard_mhs.php" class="btn btn-outline-secondary rounded-pill"><i class="bi bi-arrow-left"></i> Kembali</a>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-warning">Berita tidak ditemukan.</div>
                        <?php endif;
                        break;

                    // ROUTING LAINNYA (TETAP SAMA)
                    case 'pengajuan': include 'pengajuan.php'; break;
                    case 'bimbingan': include 'bimbingan.php'; break;
                    case 'daftar_sidang': include 'mhs_sidang.php'; break;
                    case 'jadwal': include 'jadwal_sidang_view.php'; break;
                    case 'chat': include 'chat_dosen.php'; break;
                    case 'ai': include 'ai_assistant.php'; break;
                    case 'bantuan': include 'panduan.php'; break;
                    default: echo "<div class='alert alert-danger'>Halaman tidak ditemukan!</div>"; break;
                }
                ?>
            </div>

            <div class="col-lg-3">
                
                <div class="card border-0 shadow-sm mb-3 overflow-hidden">
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

                <div class="card mt-3 shadow-sm border-0">
                    <div class="card-header fw-bold bg-light"><i class="bi bi-folder2-open"></i> Dokumen</div>
                    <div class="list-group list-group-flush small">
                        <?php while($doc = mysqli_fetch_assoc($q_doc)): ?>
                            <a href="<?= $doc['link_file'] ?>" class="list-group-item list-group-item-action">
                                <?= $doc['nama_doc'] ?> <i class="bi bi-download float-end"></i>
                            </a>
                        <?php endwhile; ?>
                    </div>
                </div>

                <div class="card mt-3 shadow-sm border-0">
                    <div class="card-body text-center">
                        <h6 class="fw-bold text-muted"><?= $periode['nama_periode'] ?? 'Periode' ?></h6>
                        <h2 class="display-4 fw-bold text-primary"><?= date('d') ?></h2>
                        <span class="text-uppercase ls-1"><?= date('F Y') ?></span>
                    </div>
                </div>

            </div>

        </div>
    </div>

    <footer class="text-center py-4 bg-dark text-white mt-auto">
        <div class="container">
            <h6 class="fw-bold mb-2"><?= $web['nama_web'] ?> - Universitas Perguruan Tinggi</h6>
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