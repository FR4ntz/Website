<?php
if($_SESSION['role'] != 'Dosen Pembimbing') {
    echo "<h3>Akses Ditolak</h3>";
    exit();
}
?>

<h2>Bimbingan Mahasiswa</h2>
<div class="card">
    <p>Halaman ini khusus untuk Dosen Pembimbing memantau progress mahasiswa.</p>
</div>

<div class="card">
    <h3>Daftar Mahasiswa yang Mengajukan Proposal</h3>
    <!-- Logic sederhana: Menampilkan semua proposal yang disetujui -->
    <table>
        <thead>
            <tr>
                <th>Nama Mahasiswa</th>
                <th>Judul TA</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // Query contoh: menampilkan proposal yang statusnya disetujui (diasumsikan pembimbing ikut memantau)
            $qBimb = mysqli_query($koneksi, "SELECT p.judul, p.status, u.nama FROM proposal p JOIN users u ON p.mahasiswa_id = u.id WHERE p.status = 'Disetujui'");
            
            if(mysqli_num_rows($qBimb) > 0) {
                while($row = mysqli_fetch_assoc($qBimb)) {
                    echo "<tr>";
                    echo "<td>".$row['nama']."</td>";
                    echo "<td>".$row['judul']."</td>";
                    echo "<td><span class='badge bg-success'>".$row['status']."</span></td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='3'>Belum ada mahasiswa bimbingan yang disetujui proposalnya.</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>