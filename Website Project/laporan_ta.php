<?php
session_start();
include 'koneksi.php';

// Cek Login
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'mahasiswa') { 
    header("Location: index.php"); exit; 
}

$nim = $_SESSION['nim'];

// Ambil Data Mahasiswa (Tabel Mahasiswa)
$mhs = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM Mahasiswa WHERE NIM='$nim'"));

// Ambil Data Proposal (Tabel Proposal)
$prop = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM Proposal WHERE NIM='$nim'"));
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <title>Kartu Bimbingan - <?= $nim ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Times New Roman', serif; padding: 40px; }
        .kop { border-bottom: 3px double black; margin-bottom: 20px; padding-bottom: 10px; }
        .ttd-area { margin-top: 50px; float: right; width: 250px; text-align: center; }
        
        /* CSS Print: Hilangkan tombol saat diprint */
        @media print {
            .no-print { display: none; }
        }
    </style>
</head>
<body onload="window.print()">

    <div class="no-print mb-4">
        <button onclick="window.print()" class="btn btn-primary">Print Laporan</button>
        <button onclick="window.close()" class="btn btn-secondary">Tutup</button>
        <div class="alert alert-info mt-2">
            Tip: Gunakan pengaturan "Save as PDF" jika ingin menyimpan file.
        </div>
    </div>

    <div class="text-center kop">
        <h4 class="fw-bold m-0">UNIVERSITAS PEMBANGUNAN JAYA</h4>
        <h5 class="m-0">FAKULTAS TEKNOLOGI DAN DESAIN</h5>
        <small>Jln. Cendrawasih Raya Blok B7/P, Bintaro Jaya, Tangerang Selatan</small>
    </div>

    <h5 class="text-center fw-bold mb-4 text-uppercase">Kartu Kendali Bimbingan Tugas Akhir</h5>

    <table class="table table-borderless table-sm w-75 mx-auto mb-4">
        <tr>
            <td width="150">Nama Mahasiswa</td>
            <td width="10">:</td>
            <td class="fw-bold"><?= $mhs['Nama'] ?></td>
        </tr>
        <tr>
            <td>NIM</td>
            <td>:</td>
            <td><?= $mhs['NIM'] ?></td>
        </tr>
        <tr>
            <td>Judul TA</td>
            <td>:</td>
            <td><?= $prop['Judul'] ?? '-' ?></td>
        </tr>
    </table>

    <table class="table table-bordered border-dark text-center">
        <thead>
            <tr class="bg-light">
                <th width="5%">No</th>
                <th width="20%">Tanggal</th>
                <th>Topik / Materi Bimbingan</th>
                <th width="20%">Paraf Dosen</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // Ambil Riwayat Bimbingan (Tabel Bimbingan)
            // Kolom: NIM, Tanggal, Topik, Status
            $log = mysqli_query($conn, "SELECT * FROM Bimbingan WHERE NIM='$nim' ORDER BY Tanggal ASC");
            $no = 1;
            while($r = mysqli_fetch_array($log)):
            ?>
            <tr>
                <td><?= $no++ ?></td>
                <td><?= date('d/m/Y', strtotime($r['Tanggal'])) ?></td>
                <td class="text-start ps-3">
                    <?= $r['Topik'] ?><br>
                    <small class="text-muted fst-italic">Status: <?= $r['Status'] ?></small>
                </td>
                <td>
                    <?php if($r['Status'] == 'ACC' || $r['Status'] == 'Disetujui'): ?>
                        <span style="font-family: 'Brush Script MT', cursive; font-size: 1.2rem;">Acc Dosen</span><br>
                        <small style="font-size: 0.7rem;">(Digital Sign)</small>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endwhile; ?>

            <?php 
            // Tambahkan baris kosong jika data sedikit agar tabel terlihat penuh
            for($i=0; $i<(10-mysqli_num_rows($log)); $i++): 
            ?>
            <tr>
                <td style="height: 40px;"></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            <?php endfor; ?>
        </tbody>
    </table>

    <div class="ttd-area">
        <p>Tangerang Selatan, <?= date('d F Y') ?></p>
        <p class="mb-5">Mengetahui,<br>Dosen Pembimbing</p>
        <br>
        <p class="fw-bold text-decoration-underline">
            (...........................................)
        </p>
    </div>

</body>
</html>