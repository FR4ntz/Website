<?php
// Cek akses
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'Koordinator') { 
    echo "<div class='alert alert-danger'>Akses Ditolak.</div>"; 
    exit; 
}

// ==============================================================================
// 1. LOGIKA TAMBAH MAHASISWA (CREATE)
// ==============================================================================
if (isset($_POST['simpan_mhs'])) {
    $nim  = mysqli_real_escape_string($conn, $_POST['nim']);
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $pass = md5($_POST['password']); 
    $sks  = (int) $_POST['sks'];
    $jsdp = (int) $_POST['jsdp'];
    
    $cek = mysqli_query($conn, "SELECT NIM FROM Mahasiswa WHERE NIM='$nim'");
    if (mysqli_num_rows($cek) > 0) {
        echo "<script>alert('Gagal: NIM sudah terdaftar!');</script>";
    } else {
        $query = "INSERT INTO Mahasiswa (NIM, Nama, Password, Total_SKS, Total_JSDP) VALUES ('$nim', '$nama', '$pass', '$sks', '$jsdp')";
        if(mysqli_query($conn, $query)){
            echo "<script>alert('Mahasiswa berhasil ditambahkan!'); window.location='dashboard_dosen.php?page=mahasiswa';</script>";
        } else {
            echo "<script>alert('Error: ".mysqli_error($conn)."');</script>";
        }
    }
}

// ==============================================================================
// 2. LOGIKA UPDATE MAHASISWA (UPDATE)
// ==============================================================================
if (isset($_POST['update_mhs'])) {
    $nim_asli = mysqli_real_escape_string($conn, $_POST['nim_asli']);
    $nama_baru = mysqli_real_escape_string($conn, $_POST['nama']);
    $sks_baru  = (int) $_POST['sks'];
    $jsdp_baru = (int) $_POST['jsdp'];

    $sql_pass = "";
    if (!empty($_POST['password_baru'])) {
        $pass_hash = md5($_POST['password_baru']);
        $sql_pass = ", Password='$pass_hash'";
    }

    $q_update = "UPDATE Mahasiswa SET Nama='$nama_baru', Total_SKS='$sks_baru', Total_JSDP='$jsdp_baru' $sql_pass WHERE NIM='$nim_asli'";

    if (mysqli_query($conn, $q_update)) {
        echo "<script>alert('Data mahasiswa berhasil diperbarui!'); window.location='dashboard_dosen.php?page=mahasiswa';</script>";
    } else {
        echo "<script>alert('Gagal Update: ".mysqli_error($conn)."');</script>";
    }
}

// ==============================================================================
// 3. LOGIKA HAPUS MAHASISWA (DELETE)
// ==============================================================================
if (isset($_POST['hapus_mhs'])) {
    $nim_hapus = mysqli_real_escape_string($conn, $_POST['nim_hapus']);
    
    mysqli_query($conn, "DELETE FROM Bimbingan WHERE NIM='$nim_hapus'");
    mysqli_query($conn, "DELETE FROM Pesan WHERE pengirim='$nim_hapus' OR penerima='$nim_hapus'");

    $q_prop = mysqli_query($conn, "SELECT idProposal FROM Proposal WHERE NIM='$nim_hapus'");
    while($p = mysqli_fetch_assoc($q_prop)){
        $id_p = $p['idProposal'];
        mysqli_query($conn, "DELETE FROM Sidang WHERE idProposal='$id_p'");
        mysqli_query($conn, "DELETE FROM Perpanjangan WHERE id_proposal='$id_p'");
    }

    mysqli_query($conn, "DELETE FROM Proposal WHERE NIM='$nim_hapus'");

    if(mysqli_query($conn, "DELETE FROM Mahasiswa WHERE NIM='$nim_hapus'")){
        echo "<script>alert('Data mahasiswa BERHASIL dihapus.'); window.location='dashboard_dosen.php?page=mahasiswa';</script>";
    } else {
        echo "<script>alert('Gagal Hapus: ".mysqli_error($conn)."');</script>";
    }
}

// ==============================================================================
// 4. AMBIL SEMUA DATA DULU (SIMPAN DI ARRAY)
// ==============================================================================
// Ini teknik agar kita bisa memisahkan Tabel dan Modal
$data_mahasiswa = [];
$q_mhs = mysqli_query($conn, "SELECT * FROM Mahasiswa ORDER BY NIM ASC");
while($r = mysqli_fetch_assoc($q_mhs)){
    $data_mahasiswa[] = $r;
}
?>

<div class="row">
    <div class="col-12 mb-4">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-primary text-white fw-bold py-3">
                <i class="bi bi-person-plus-fill me-2"></i> Tambah Mahasiswa Baru
            </div>
            <div class="card-body">
                <form method="POST" class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label small fw-bold">NIM</label>
                        <input type="text" name="nim" class="form-control" placeholder="Cth: 2021001" required>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label small fw-bold">Nama Lengkap</label>
                        <input type="text" name="nama" class="form-control" placeholder="Nama Mahasiswa" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Password Default</label>
                        <input type="text" name="password" class="form-control" value="123456" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Total SKS Awal</label>
                        <input type="number" name="sks" class="form-control" value="0" min="0" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Poin JSDP Awal</label>
                        <input type="number" name="jsdp" class="form-control" value="0" min="0" required>
                    </div>
                    <div class="col-12 mt-4">
                        <button type="submit" name="simpan_mhs" class="btn btn-success fw-bold px-4 w-100">
                            <i class="bi bi-save me-2"></i> Simpan Data Mahasiswa
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white fw-bold d-flex justify-content-between align-items-center py-3">
                <span><i class="bi bi-people-fill text-primary me-2"></i> Data Mahasiswa Terdaftar</span>
                <span class="badge bg-secondary">Total: <?= count($data_mahasiswa) ?></span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive" style="max-height: 600px; overflow-y: auto;">
                    <table class="table table-striped table-hover mb-0 align-middle">
                        <thead class="table-light sticky-top">
                            <tr>
                                <th class="ps-4">NIM</th>
                                <th>Nama Lengkap</th>
                                <th class="text-center">SKS</th>
                                <th class="text-center">JSDP</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(count($data_mahasiswa) > 0): ?>
                                <?php foreach($data_mahasiswa as $row): 
                                    $modalEditID = "editMhs" . $row['NIM'];
                                ?>
                                <tr>
                                    <td class="ps-4 fw-bold"><?= $row['NIM'] ?></td>
                                    <td><?= $row['Nama'] ?></td>
                                    <td class="text-center">
                                        <span class="badge <?= ($row['Total_SKS']>=120)?'bg-success':'bg-warning text-dark' ?>">
                                            <?= $row['Total_SKS'] ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge <?= ($row['Total_JSDP']>=600)?'bg-success':'bg-secondary' ?>">
                                            <?= $row['Total_JSDP'] ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex justify-content-center gap-1">
                                            <button type="button" class="btn btn-warning btn-sm py-1 px-2" data-bs-toggle="modal" data-bs-target="#<?= $modalEditID ?>" title="Edit Data">
                                                <i class="bi bi-pencil-square"></i>
                                            </button>

                                            <form method="POST" onsubmit="return confirm('PERINGATAN: Menghapus mahasiswa ini akan MENGHAPUS SEMUA DATA riwayatnya. Lanjutkan?');">
                                                <input type="hidden" name="nim_hapus" value="<?= $row['NIM'] ?>">
                                                <button type="submit" name="hapus_mhs" class="btn btn-danger btn-sm py-1 px-2" title="Hapus Permanen">
                                                    <i class="bi bi-trash-fill"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="5" class="text-center py-5 text-muted">Belum ada data mahasiswa.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php foreach($data_mahasiswa as $row): 
    $modalEditID = "editMhs" . $row['NIM'];
?>
<div class="modal fade" id="<?= $modalEditID ?>" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h6 class="modal-title fw-bold">Edit Mahasiswa: <?= $row['NIM'] ?></h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body text-start">
                    <input type="hidden" name="nim_asli" value="<?= $row['NIM'] ?>">
                    
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Nama Lengkap</label>
                        <input type="text" name="nama" class="form-control" value="<?= $row['Nama'] ?>" required>
                    </div>
                    
                    <div class="row g-2">
                        <div class="col-6">
                            <label class="form-label small fw-bold">Total SKS</label>
                            <input type="number" name="sks" class="form-control" value="<?= $row['Total_SKS'] ?>" min="0" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold">Poin JSDP</label>
                            <input type="number" name="jsdp" class="form-control" value="<?= $row['Total_JSDP'] ?>" min="0" required>
                        </div>
                    </div>

                    <hr>
                    <div class="mb-2">
                        <label class="form-label small fw-bold text-danger">Reset Password (Opsional)</label>
                        <input type="text" name="password_baru" class="form-control form-control-sm" placeholder="Isi hanya jika ingin mereset password">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="update_mhs" class="btn btn-primary btn-sm fw-bold px-4">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endforeach; ?>