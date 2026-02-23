<h2>Dashboard</h2>
<div class="card">
    <p>Halo, <b><?= $_SESSION['nama']; ?></b>.</p>
    <p>Anda saat ini login sebagai <b><?= $_SESSION['role']; ?></b>.</p>
    <p>Silakan gunakan menu di sebelah kiri untuk mengakses fitur sistem.</p>
</div>

<div class="card" style="border-left: 5px solid var(--primary);">
    <h3>Statistik Cepat</h3>
    <?php
    // Contoh query sederhana untuk statistik
    $jml_prop = mysqli_num_rows(mysqli_query($koneksi, "SELECT id FROM proposal"));
    $jml_sidang = mysqli_num_rows(mysqli_query($koneksi, "SELECT id FROM sidang"));
    ?>
    <p>Total Proposal Masuk: <b><?= $jml_prop; ?></b></p>
    <p>Jadwal Sidang Aktif: <b><?= $jml_sidang; ?></b></p>
</div>