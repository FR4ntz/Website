<?php
// LOGIKA PROSES DATA
if(isset($_POST['aksi'])) {
    if($_POST['aksi'] == 'jadwal') {
        // Logic Simpan Jadwal (Khusus Koordinator)
        $pid = $_POST['proposal_id']; $pg = $_POST['penguji_id']; $tgl = $_POST['tanggal']; $rng = $_POST['ruangan'];
        mysqli_query($koneksi, "INSERT INTO sidang (proposal_id, tanggal, ruangan, penguji_id) VALUES ('$pid', '$tgl', '$rng', '$pg')");
        echo "<script>window.location='?page=sidang';</script>";
    }
    if($_POST['aksi'] == 'nilai') {
        // Logic Simpan Nilai (Khusus Penguji/Pembimbing)
        $sid = $_POST['sidang_id']; $val = $_POST['nilai'];
        mysqli_query($koneksi, "UPDATE sidang SET nilai='$val' WHERE id='$sid'");
        echo "<script>window.location='?page=sidang';</script>";
    }
}
?>

<h2>Info & Jadwal Sidang</h2>

<?php if($_SESSION['role'] == 'Koordinator TA') { ?>
<div class="card">
    <h3>Buat Jadwal Baru</h3>
    <form method="POST">
        <input type="hidden" name="aksi" value="jadwal">
        <div class="form-group">
            <select name="proposal_id" class="form-control">
                <option>Pilih Proposal Disetujui</option>
                <?php 
                $qp = mysqli_query($koneksi, "SELECT id, judul FROM proposal WHERE status='Disetujui'");
                while($rp = mysqli_fetch_assoc($qp)){ echo "<option value='".$rp['id']."'>".$rp['judul']."</option>"; }
                ?>
            </select>
        </div>
        <div class="form-group">
            <select name="penguji_id" class="form-control">
                <option>Pilih Penguji</option>
                <?php 
                $qd = mysqli_query($koneksi, "SELECT id, nama FROM users WHERE role='Dosen Penguji'");
                while($rd = mysqli_fetch_assoc($qd)){ echo "<option value='".$rd['id']."'>".$rd['nama']."</option>"; }
                ?>
            </select>
        </div>
        <input type="datetime-local" name="tanggal" class="form-control">
        <input type="text" name="ruangan" placeholder="Ruangan" class="form-control" style="margin-top:10px;">
        <button class="btn btn-primary" style="margin-top:10px;">Simpan</button>
    </form>
</div>
<?php } ?>

<div class="card">
    <table>
        <thead><tr><th>Waktu</th><th>Mahasiswa</th><th>Penguji</th><th>Nilai</th><th>Input</th></tr></thead>
        <tbody>
            <?php
            $qs = mysqli_query($koneksi, "SELECT s.*, p.judul, u.nama as mhs, d.nama as penguji FROM sidang s JOIN proposal p ON s.proposal_id=p.id JOIN users u ON p.mahasiswa_id=u.id LEFT JOIN users d ON s.penguji_id=d.id");
            while($r = mysqli_fetch_assoc($qs)) {
            ?>
            <tr>
                <td><?= $r['tanggal'] ?><br><small><?= $r['ruangan'] ?></small></td>
                <td><?= $r['mhs'] ?><br><b><?= $r['judul'] ?></b></td>
                <td><?= $r['penguji'] ?></td>
                <td><?= $r['nilai'] ? $r['nilai'] : '-' ?></td>
                <td>
                    <?php if(($_SESSION['role']=='Dosen Penguji' || $_SESSION['role']=='Dosen Pembimbing') && !$r['nilai']) { ?>
                        <form method="POST">
                            <input type="hidden" name="aksi" value="nilai">
                            <input type="hidden" name="sidang_id" value="<?= $r['id'] ?>">
                            <select name="nilai"><option>A</option><option>B</option><option>C</option></select>
                            <button class="btn btn-primary btn-sm">OK</button>
                        </form>
                    <?php } ?>
                </td>
            </tr>
            <?php } ?>
        </tbody>
    </table>
</div>