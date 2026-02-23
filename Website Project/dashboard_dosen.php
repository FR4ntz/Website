<?php
session_start();
include 'koneksi.php';

// 1. CEK KEAMANAN AKSES
if (!isset($_SESSION['role']) || (!in_array($_SESSION['role'], ['Dosen', 'Koordinator', 'Penguji']))) {
    header("Location: index.php"); 
    exit; 
}

$nidn = $_SESSION['username'];
$role = $_SESSION['role']; 
$nama = $_SESSION['user'];

// ==============================================================================
// 2. QUERY DATA TAMPILAN
// ==============================================================================

// A. IDENTITAS WEB
$q_web = mysqli_query($conn, "SELECT * FROM konfig_web LIMIT 1");
$web = mysqli_fetch_assoc($q_web);

// B. KONTEN BERITA
$q_konten = mysqli_query($conn, "SELECT * FROM konten_publik ORDER BY tanggal DESC");

// C. WIDGET ASIDE (KANAN)
$q_deadline = mysqli_query($conn, "SELECT * FROM deadline ORDER BY tanggal ASC");
$q_doc = mysqli_query($conn, "SELECT * FROM dokumen_publik");
$q_periode = mysqli_query($conn, "SELECT * FROM periode_aktif LIMIT 1");
$periode = mysqli_fetch_assoc($q_periode);

// ==============================================================================
// 3. FUNGSI QUERY AMAN
// ==============================================================================
function query_aman($conn, $query_utama, $query_cadangan) {
    $hasil = @mysqli_query($conn, $query_utama);
    if (!$hasil) {
        $hasil = @mysqli_query($conn, $query_cadangan);
    }
    return ($hasil) ? mysqli_num_rows($hasil) : 0;
}

// ==============================================================================
// 4. HITUNG STATISTIK (BADGE)
// ==============================================================================
$jml_bim_pending    = 0;
$jml_prop_baru      = 0;
$jml_sidang_baru    = 0;
$jml_pesan_baru     = 0;
$jml_ujian_pending  = 0;
$jml_extend_pending = 0;

if ($role == 'Dosen') {
    $jml_bim_pending = query_aman($conn, 
        "SELECT * FROM Bimbingan WHERE NIDN='$nidn' AND Status='Menunggu'", 
        "SELECT * FROM bimbingan WHERE nidn_pembimbing='$nidn' AND status='Menunggu'"
    );
    $jml_pesan_baru = query_aman($conn, 
        "SELECT * FROM Pesan WHERE penerima='$nidn' AND is_read=0",
        "SELECT * FROM pesan WHERE penerima='$nidn' AND is_read=0"
    );

} elseif ($role == 'Koordinator') {
    $jml_prop_baru = query_aman($conn, 
        "SELECT * FROM Proposal WHERE status_pengajuan='Diajukan'",
        "SELECT * FROM proposal WHERE status_pengajuan='Diajukan'"
    );
    $jml_sidang_baru = query_aman($conn, 
        "SELECT * FROM Sidang WHERE status_sidang='Menunggu Jadwal'",
        "SELECT * FROM sidang WHERE status_sidang='Menunggu Jadwal'"
    );
    $jml_extend_pending = query_aman($conn,
        "SELECT * FROM Perpanjangan WHERE status_perpanjangan='Diajukan'",
        "SELECT * FROM perpanjangan WHERE status_perpanjangan='Diajukan'"
    );

} elseif ($role == 'Penguji') {
    $jml_ujian_pending = query_aman($conn, 
        "SELECT * FROM Sidang WHERE NIDN='$nidn' AND status_sidang='Dijadwalkan'",
        "SELECT * FROM sidang WHERE nidn_penguji='$nidn' AND status_sidang='Dijadwalkan'"
    );
}

$page = isset($_GET['page']) ? $_GET['page'] : 'home';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Staff - <?= $web['nama_web'] ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="style.css">
    
    <style>
        /* PERBAIKAN 1: Tambahkan padding-top pada body agar konten turun */
        body { 
            background-color: #f4f6f9; 
            padding-top: 80px; 
        }

        /* PERBAIKAN 2: Pastikan Header Fixed memiliki Z-Index paling tinggi */
        header.dosen-mode {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            z-index: 1050; /* Lebih tinggi dari elemen lain */
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .card-img-top-placeholder { height: 180px; background: #e9ecef; display: flex; align-items: center; justify-content: center; color: #adb5bd; }
        
        /* === STYLE MENU === */
        .nav-link { 
            color: #555; 
            font-weight: 500; 
            transition: all 0.2s; 
            border-radius: 8px; 
            margin-bottom: 4px; 
            padding: 10px 15px; 
        }

        .nav-link:hover { 
            color: #0d6efd; 
            background-color: #f0f0f0; 
        }

        .nav-link.active { 
            background-color: #0d6efd !important; 
            color: #ffffff !important;           
            font-weight: bold; 
            box-shadow: 0 4px 6px rgba(13, 110, 253, 0.3); 
        }

        .nav-link.active i {
            color: #ffffff !important;
        }

        .nav-link[data-bs-toggle="collapse"] .bi-chevron-down { transition: transform 0.3s ease; }
        .nav-link[data-bs-toggle="collapse"]:not(.collapsed) .bi-chevron-down { transform: rotate(180deg); }
        .collapse .nav-link { font-size: 0.9rem; padding-left: 2rem; }
    </style>
</head>
<body>

    <header class="dosen-mode">
        <div class="container d-flex justify-content-between align-items-center" style="min-height: 60px;"> <div class="d-flex align-items-center">
                <i class="bi bi-building-fill fs-3 me-3"></i>
                <div>
                    <h5 class="m-0 fw-bold"><?= $web['nama_web'] ?> - STAFF</h5>
                    <small style="opacity: 0.9;"><?= $web['slogan'] ?></small>
                </div>
            </div>
            <div class="d-flex align-items-center">
                <div class="text-end me-3 d-none d-md-block">
                    <span class="d-block fw-bold"><?= $nama ?></span>
                    <?php
                        $bg_badge = 'bg-warning text-dark';
                        if($role=='Penguji') $bg_badge = 'bg-danger text-white';
                        if($role=='Dosen') $bg_badge = 'bg-light text-primary';
                    ?>
                    <span class="badge <?= $bg_badge ?>"><?= $role ?></span>
                </div>
                <a href="logout.php" class="btn btn-outline-light btn-sm ms-2">
                    <i class="bi bi-box-arrow-right"></i> Logout
                </a>
            </div>
        </div>
    </header>

    <div class="container my-4">
        <div class="row">
            
            <div class="col-lg-3 mb-4">
                <div class="card shadow-sm border-0 sticky-top" style="top: 90px; z-index: 1000;">
                    <div class="card-header bg-white fw-bold py-3">
                        <i class="bi bi-grid-fill me-2"></i> Menu Utama
                    </div>
                    <div class="card-body p-2">
                        <nav class="nav flex-column gap-1">
                            
                            <a class="nav-link <?= ($page=='home')?'active':'' ?>" href="dashboard_dosen.php?page=home">
                                <i class="bi bi-speedometer2 me-2"></i> Dashboard
                            </a>
                            
                            <?php if($role == 'Dosen'): ?>
                                <div class="text-muted small fw-bold mt-3 ms-2 mb-1">PEMBIMBING</div>
                                <a class="nav-link <?= ($page=='bimbingan')?'active':'' ?>" href="dashboard_dosen.php?page=bimbingan">
                                    <i class="bi bi-people me-2"></i> Kelola Bimbingan
                                    <?php if($jml_bim_pending > 0): ?>
                                        <span class="badge bg-danger ms-auto rounded-pill"><?= $jml_bim_pending ?></span>
                                    <?php endif; ?>
                                </a>
                                <a class="nav-link <?= ($page=='chat')?'active':'' ?>" href="dashboard_dosen.php?page=chat">
                                    <i class="bi bi-chat-dots me-2"></i> Pesan Masuk
                                    <?php if($jml_pesan_baru > 0): ?>
                                        <span class="badge bg-danger ms-auto rounded-pill"><?= $jml_pesan_baru ?></span>
                                    <?php endif; ?>
                                </a>
                            <?php endif; ?>

                            <?php if($role == 'Penguji'): ?>
                                <div class="text-muted small fw-bold mt-3 ms-2 mb-1">PENGUJI SIDANG</div>
                                <a class="nav-link <?= ($page=='ujian')?'active':'' ?>" href="dashboard_dosen.php?page=ujian">
                                    <i class="bi bi-clipboard-check me-2"></i> Penilaian Sidang
                                    <?php if($jml_ujian_pending > 0): ?>
                                        <span class="badge bg-danger ms-auto rounded-pill"><?= $jml_ujian_pending ?></span>
                                    <?php endif; ?>
                                </a>
                            <?php endif; ?>

                            <?php if($role == 'Koordinator'): ?>
                                <hr class="my-2 border-secondary opacity-25">
                                
                                <?php $master_active = in_array($page, ['master_dosen', 'mahasiswa', 'konten']); ?>
                                <a class="nav-link d-flex justify-content-between align-items-center <?= $master_active ? '' : 'collapsed' ?>" 
                                   data-bs-toggle="collapse" href="#collapseMasterData" role="button" 
                                   aria-expanded="<?= $master_active ? 'true' : 'false' ?>">
                                    <span><i class="bi bi-database me-2"></i> Master Data</span>
                                    <i class="bi bi-chevron-down small"></i>
                                </a>
                                <div class="collapse <?= $master_active ? 'show' : '' ?>" id="collapseMasterData">
                                    <div class="nav flex-column ms-3 mt-1 border-start border-2 ps-2">
                                        <a class="nav-link <?= ($page=='master_dosen')?'active':'' ?>" href="dashboard_dosen.php?page=master_dosen">Kelola Akun Dosen</a>
                                        <a class="nav-link <?= ($page=='mahasiswa')?'active':'' ?>" href="dashboard_dosen.php?page=mahasiswa">Master Mahasiswa</a>
                                        <a class="nav-link <?= ($page=='konten')?'active':'' ?>" href="dashboard_dosen.php?page=konten">Kelola Berita (CMS)</a>
                                    </div>
                                </div>

                                <?php $coord_active = in_array($page, ['proposal', 'sidang', 'extend']); ?>
                                <a class="nav-link d-flex justify-content-between align-items-center mt-1 <?= $coord_active ? '' : 'collapsed' ?>" 
                                   data-bs-toggle="collapse" href="#collapseKoordinatorTA" role="button" 
                                   aria-expanded="<?= $coord_active ? 'true' : 'false' ?>">
                                    <span><i class="bi bi-journal-bookmark-fill me-2"></i> Koordinator TA</span>
                                    <i class="bi bi-chevron-down small"></i>
                                </a>
                                <div class="collapse <?= $coord_active ? 'show' : '' ?>" id="collapseKoordinatorTA">
                                    <div class="nav flex-column ms-3 mt-1 border-start border-2 ps-2">
                                        <a class="nav-link d-flex justify-content-between align-items-center <?= ($page=='proposal')?'active':'' ?>" href="dashboard_dosen.php?page=proposal">
                                            Validasi Proposal
                                            <?php if($jml_prop_baru > 0): ?><span class="badge bg-warning text-dark rounded-pill"><?= $jml_prop_baru ?></span><?php endif; ?>
                                        </a>
                                        <a class="nav-link d-flex justify-content-between align-items-center <?= ($page=='sidang')?'active':'' ?>" href="dashboard_dosen.php?page=sidang">
                                            Kelola Sidang
                                            <?php if($jml_sidang_baru > 0): ?><span class="badge bg-danger rounded-pill"><?= $jml_sidang_baru ?></span><?php endif; ?>
                                        </a>
                                        <a class="nav-link d-flex justify-content-between align-items-center <?= ($page=='extend')?'active':'' ?>" href="dashboard_dosen.php?page=extend">
                                            Validasi Extend
                                            <?php if($jml_extend_pending > 0): ?><span class="badge bg-danger rounded-pill"><?= $jml_extend_pending ?></span><?php endif; ?>
                                        </a>
                                    </div>
                                </div>
                            <?php endif; ?>

                        </nav>
                    </div>
                </div>
            </div>

            <div class="col-lg-6 mb-4">
                <?php 
                switch ($page) {
                    case 'home':
                        ?>
                        <div class="alert alert-success shadow-sm border-0 d-flex align-items-center mb-4" role="alert" style="background: linear-gradient(135deg, #198754, #146c43); color: white;">
                            <i class="bi bi-person-badge-fill fs-1 me-3 opacity-50"></i>
                            <div>
                                <h4 class="alert-heading fw-bold mb-1">Selamat Datang!</h4>
                                <p class="mb-0 small opacity-90">Anda login sebagai <strong><?= $role ?></strong>. Selamat bekerja.</p>
                            </div>
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
                                                    <a href="dashboard_dosen.php?page=detail_berita&id=<?= $berita['id'] ?>" class="text-primary text-decoration-none fw-bold small">
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
                                    <a href="dashboard_dosen.php" class="btn btn-outline-secondary rounded-pill"><i class="bi bi-arrow-left"></i> Kembali</a>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-warning">Berita tidak ditemukan.</div>
                        <?php endif;
                        break;

                    case 'bimbingan': include 'kelola_bimbingan.php'; break;
                    case 'chat':      include 'chat_dosen_view.php'; break;
                    case 'proposal':  include 'admin_proposal.php'; break;
                    case 'sidang':    include 'kelola_sidang.php'; break;
                    case 'extend':    include 'admin_perpanjangan.php'; break;
                    case 'mahasiswa': include 'admin_mahasiswa.php'; break;
                    case 'master_dosen': include 'admin_dosen.php'; break;
                    case 'ujian':     include 'dosen_ujian.php'; break;
                    case 'konten':    include 'admin_konten.php'; break;

                    default: echo "<div class='alert alert-danger'>Halaman tidak ditemukan!</div>"; break;
                }
                ?>
            </div>

            <div class="col-lg-3 mb-4">
                
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
                        <h6 class="fw-bold text-muted mb-1"><?= $periode['nama_periode'] ?? 'Periode Akademik' ?></h6>
                        <h1 class="display-3 fw-bold text-primary mb-0" style="letter-spacing: -2px;"><?= date('d') ?></h1>
                        <span class="text-uppercase fw-bold text-secondary ls-1"><?= date('F Y') ?></span>
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