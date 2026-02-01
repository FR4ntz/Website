<?php
include 'koneksi.php';

// Ambil ID dari URL
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Query Data Artikel
$query = mysqli_query($conn, "SELECT * FROM notifikasi WHERE id = $id");
$data = mysqli_fetch_assoc($query);

// Jika data tidak ditemukan
if (!$data) {
    echo "<script>alert('Artikel tidak ditemukan!'); window.location='index.php';</script>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($data['judul']) ?> - SITA UPJ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <div class="container my-4">
        <a href="index.php" class="btn btn-outline-secondary mb-3">
            <i class="bi bi-arrow-left"></i> Kembali ke Dashboard
        </a>

        <div class="card shadow-sm border-0">
            <?php 
                $img_path = 'uploads/' . $data['gambar'];
                // Gunakan placeholder jika gambar tidak ada/kosong
                if (empty($data['gambar']) || !file_exists($img_path)) {
                    $img_src = "https://via.placeholder.com/800x400.png?text=No+Image"; 
                } else {
                    $img_src = $img_path;
                }
            ?>
            <img src="<?= $img_src ?>" class="card-img-top" alt="Gambar Artikel" style="max-height: 400px; object-fit: cover;">

            <div class="card-body p-4">
                <h1 class="fw-bold mb-3"><?= htmlspecialchars($data['judul']) ?></h1>
                
                <div class="text-muted small mb-4">
                    <i class="bi bi-calendar3 me-2"></i> <?= date('d F Y', strtotime($data['tanggal'])) ?>
                    <span class="mx-2">|</span>
                    <i class="bi bi-person-fill me-2"></i> Admin
                </div>

                <div class="article-content" style="line-height: 1.8; text-align: justify;">
                    <?= nl2br(htmlspecialchars($data['pesan'])) ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>