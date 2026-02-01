<?php
// Cek akses
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'Koordinator') { 
    echo "<div class='alert alert-danger'>Akses Ditolak.</div>"; 
    exit; 
}

// ==============================================================================
// 1. LOGIKA TAMBAH DOSEN (CREATE)
// ==============================================================================
if (isset($_POST['simpan_dosen'])) {
    $nidn  = mysqli_real_escape_string($conn, $_POST['nidn']);
    $nama  = mysqli_real_escape_string($conn, $_POST['nama']);
    $peran = mysqli_real_escape_string($conn, $_POST['peran']);
    $pass  = md5($_POST['password']); 
    
    // Cek NIDN Kembar
    $cek = mysqli_query($conn, "SELECT NIDN FROM Dosen WHERE NIDN='$nidn'");
    if (mysqli_num_rows($cek) > 0) {
        echo "<script>alert('Gagal: NIDN sudah terdaftar!');</script>";
    } else {
        $q_insert = "INSERT INTO Dosen (NIDN, Nama, Password, Role) VALUES ('$nidn', '$nama', '$pass', '$peran')";
        if (mysqli_query($conn, $q_insert)) {
            echo "<script>alert('Berhasil menambah akun!'); window.location='dashboard_dosen.php?page=master_dosen';</script>";
        } else {
            echo "<script>alert('Error Database: ".mysqli_error($conn)."');</script>";
        }
    }
}

// ==============================================================================
// 2. LOGIKA UPDATE DOSEN (UPDATE)
// ==============================================================================
if (isset($_POST['update_dosen'])) {
    $nidn_lama = mysqli_real_escape_string($conn, $_POST['nidn_asli']); 
    $nama_baru = mysqli_real_escape_string($conn, $_POST['nama']);
    $peran_baru = mysqli_real_escape_string($conn, $_POST['peran']);
    
    // Cek apakah password diubah?
    $sql_pass = "";
    if (!empty($_POST['password_baru'])) {
        $pass_baru = md5($_POST['password_baru']);
        $sql_pass = ", Password='$pass_baru'";
    }

    $q_update = "UPDATE Dosen SET Nama='$nama_baru', Role='$peran_baru' $sql_pass WHERE NIDN='$nidn_lama'";

    if (mysqli_query($conn, $q_update)) {
        echo "<script>alert('Data berhasil diperbarui!'); window.location='dashboard_dosen.php?page=master_dosen';</script>";
    } else {
        echo "<script>alert('Gagal update: ".mysqli_error($conn)."');</script>";
    }
}

// ==============================================================================
// 3. LOGIKA HAPUS DOSEN (DELETE - CLEANUP #1451)
// ==============================================================================
if (isset($_POST['hapus_dosen'])) {
    $nidn = mysqli_real_escape_string($conn, $_POST['nidn_hapus']);
    
    if ($nidn == $_SESSION['username']) {
        echo "<script>alert('Tidak bisa menghapus akun sendiri!');</script>";
    } else {
        // 1. Hapus Bimbingan
        mysqli_query($conn, "DELETE FROM Bimbingan WHERE NIDN='$nidn'");
        
        // 2. Lepas Proposal (Set NULL)
        mysqli_query($conn, "UPDATE Proposal SET NIDN_Pembimbing=NULL WHERE NIDN_Pembimbing='$nidn'");
        
        // 3. Lepas Sidang (Set NULL)
        mysqli_query($conn, "UPDATE Sidang SET NIDN=NULL WHERE NIDN='$nidn'");
        
        // 4. Hapus Chat
        mysqli_query($conn, "DELETE FROM Pesan WHERE pengirim='$nidn' OR penerima='$nidn'");

        // 5. Hapus Akun Dosen
        $q_del = mysqli_query($conn, "DELETE FROM Dosen WHERE NIDN='$nidn'");
        
        if($q_del) {
            echo "<script>alert('Data dosen berhasil dihapus.'); window.location='dashboard_dosen.php?page=master_dosen';</script>";
        } else {
            echo "<script>alert('Gagal hapus: ".mysqli_error($conn)."');</script>";
        }
    }
}

// ==============================================================================
// 4. AMBIL SEMUA DATA (SIMPAN DI ARRAY UNTUK MEMISAHKAN TABEL & MODAL)
// ==============================================================================
$data_dosen = [];
$q_dosen = mysqli_query($conn, "SELECT * FROM Dosen ORDER BY NIDN ASC");
while($r = mysqli_fetch_assoc($q_dosen)){
    $data_dosen[] = $r;
}
?>

<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-success text-white fw-bold py-3">
        <i class="bi bi-person-plus-fill me-2"></i> Tambah Akun Dosen / Staff
    </div>
    <div class="card-body">
        <form method="POST">
            <div class="row g-2">
                <div class="col-md-3">
                    <label class="small fw-bold mb-1">NIDN</label>
                    <input type="text" name="nidn" class="form-control" placeholder="Cth: 041002" required>
                </div>
                <div class="col-md-4">
                    <label class="small fw-bold mb-1">Nama Lengkap & Gelar</label>
                    <input type="text" name="nama" class="form-control" placeholder="Nama Dosen" required>
                </div>
                <div class="col-md-3">
                    <label class="small fw-bold mb-1">Peran</label>
                    <select name="peran" class="form-select" required>
                        <option value="Dosen">Dosen Pembimbing</option>
                        <option value="Penguji">Dosen Penguji</option>
                        <option value="Koordinator">Koordinator TA</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="small fw-bold mb-1">Password</label>
                    <input type="text" name="password" class="form-control" value="123456" readonly>
                </div>
                <div class="col-12 mt-3">
                    <button type="submit" name="simpan_dosen" class="btn btn-success w-100 fw-bold">
                        <i class="bi bi-save me-2"></i> Simpan Data
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-header bg-white fw-bold d-flex justify-content-between align-items-center py-3">
        <span><i class="bi bi-people-fill text-success me-2"></i> Daftar Dosen Terdaftar</span>
        <span class="badge bg-secondary">Total: <?= count($data_dosen) ?></span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive" style="max-height: 600px; overflow-y: auto;">
            <table class="table table-striped align-middle mb-0">
                <thead class="bg-light text-secondary small text-uppercase sticky-top">
                    <tr>
                        <th class="ps-4">NIDN</th>
                        <th>Nama Lengkap</th>
                        <th class="text-center">Role</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(count($data_dosen) > 0): ?>
                        <?php foreach($data_dosen as $r): 
                            $role_user = $r['Role']; 
                            $modalEditID = "editDosen" . $r['NIDN'];
                        ?>
                        <tr>
                            <td class="ps-4 fw-bold text-dark"><?= $r['NIDN'] ?></td>
                            <td class="text-secondary"><?= $r['Nama'] ?></td>
                            <td class="text-center">
                                <?php if($role_user == 'Koordinator'): ?>
                                    <span class="badge bg-warning text-dark rounded-pill px-3">Koordinator</span>
                                <?php elseif($role_user == 'Penguji'): ?>
                                    <span class="badge bg-danger rounded-pill px-3">Penguji</span>
                                <?php else: ?>
                                    <span class="badge bg-primary rounded-pill px-3">Pembimbing</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <?php if($r['NIDN'] != $_SESSION['username']): ?>
                                    <div class="d-flex justify-content-center gap-1">
                                        
                                        <button type="button" class="btn btn-warning btn-sm p-1 px-2 text-dark" data-bs-toggle="modal" data-bs-target="#<?= $modalEditID ?>" title="Edit">
                                            <i class="bi bi-pencil-square"></i>
                                        </button>

                                        <form method="POST" onsubmit="return confirm('PERINGATAN: Menghapus dosen ini akan MENGHAPUS seluruh riwayat bimbingan & chat mereka. Lanjutkan?');">
                                            <input type="hidden" name="nidn_hapus" value="<?= $r['NIDN'] ?>">
                                            <button type="submit" name="hapus_dosen" class="btn btn-danger btn-sm p-1 px-2" title="Hapus">
                                                <i class="bi bi-trash-fill"></i>
                                            </button>
                                        </form>
                                    </div>
                                <?php else: ?>
                                    <span class="badge bg-light text-muted border">Akun Anda</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="4" class="text-center py-4 text-muted">Belum ada data dosen.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php foreach($data_dosen as $r): 
    $modalEditID = "editDosen" . $r['NIDN'];
    $role_user = $r['Role'];
?>
<div class="modal fade" id="<?= $modalEditID ?>" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h6 class="modal-title fw-bold text-dark">Edit Data Dosen: <?= $r['NIDN'] ?></h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body text-start">
                    <div class="mb-3">
                        <label class="small fw-bold">NIDN</label>
                        <input type="text" name="nidn_asli" class="form-control bg-light" value="<?= $r['NIDN'] ?>" readonly>
                    </div>
                    
                    <div class="mb-3">
                        <label class="small fw-bold">Nama Lengkap</label>
                        <input type="text" name="nama" class="form-control" value="<?= $r['Nama'] ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="small fw-bold">Peran</label>
                        <select name="peran" class="form-select" required>
                            <option value="Dosen" <?= ($role_user == 'Dosen') ? 'selected' : '' ?>>Dosen Pembimbing</option>
                            <option value="Penguji" <?= ($role_user == 'Penguji') ? 'selected' : '' ?>>Dosen Penguji</option>
                            <option value="Koordinator" <?= ($role_user == 'Koordinator') ? 'selected' : '' ?>>Koordinator TA</option>
                        </select>
                    </div>

                    <hr>
                    <div class="mb-3">
                        <label class="small fw-bold text-danger">Reset Password (Opsional)</label>
                        <input type="text" name="password_baru" class="form-control" placeholder="Isi jika ingin mengganti password...">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="update_dosen" class="btn btn-warning btn-sm fw-bold px-4">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endforeach; ?>