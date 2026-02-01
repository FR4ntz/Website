<?php
// Pastikan folder upload ada
$uploadDir = 'uploads/bukti_bimbingan/';
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// 1. AMBIL DATA PROPOSAL & PEMBIMBING
// Tabel: Proposal (P), Dosen (D)
// Kolom: status_pengajuan, NIDN_Pembimbing, Nama, NIM
$q_cek = "SELECT p.status_pengajuan, p.NIDN_Pembimbing, d.Nama as nama_dosen, p.idProposal 
          FROM Proposal p 
          LEFT JOIN Dosen d ON p.NIDN_Pembimbing = d.NIDN 
          WHERE p.NIM = '$nim'";
$cek_prop = mysqli_query($conn, $q_cek);
$data_prop = mysqli_fetch_assoc($cek_prop);

$status_proposal = $data_prop['status_pengajuan'] ?? 'Belum Mengajukan';
$nidn_pembimbing = $data_prop['NIDN_Pembimbing'] ?? '';
$nama_pembimbing = $data_prop['nama_dosen'] ?? 'Belum Ditentukan';
$id_proposal_aktif = $data_prop['idProposal'] ?? ''; // Penting untuk insert bimbingan

// Validasi: Harus disetujui DAN sudah ada pembimbingnya
$is_valid = ($status_proposal == 'Disetujui' && !empty($nidn_pembimbing));


// 2. LOGIKA SIMPAN LOGBOOK
if (isset($_POST['tambah_log']) && $is_valid) {
    // Ambil NIDN dari input hidden
    $nidn   = $_POST['nidn']; 
    $topik  = mysqli_real_escape_string($conn, $_POST['topik']);
    $tgl    = date('Y-m-d H:i:s'); // Format DATETIME
    
    // Proses Upload Foto
    $fotoName = null;
    if (isset($_FILES['bukti_foto']) && $_FILES['bukti_foto']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['bukti_foto']['tmp_name'];
        $fileName    = $_FILES['bukti_foto']['name'];
        $fileType    = $_FILES['bukti_foto']['type'];
        
        $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
        if (in_array($fileType, $allowedTypes)) {
            $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            $newFileName = $nim . '_' . time() . '.' . $fileExtension;
            $dest_path = $uploadDir . $newFileName;
            
            if(move_uploaded_file($fileTmpPath, $dest_path)) {
                $fotoName = $newFileName;
            }
        } else {
            echo "<script>alert('Format file tidak valid! Hanya JPG, JPEG, PNG.');</script>";
        }
    }

    // GENERATE ID BIMBINGAN (BIMB-TIMESTAMP)
    $id_bimb = "BIMB-" . time();

    // Query Insert (Sesuaikan Nama Kolom Baru)
    // Kolom: idBimbingan, NIM, NIDN, idProposal, Tanggal, Topik, Bukti_Foto, Status
    $query = "INSERT INTO Bimbingan (idBimbingan, NIM, NIDN, idProposal, Tanggal, Topik, Bukti_Foto, Status) 
              VALUES ('$id_bimb', '$nim', '$nidn', '$id_proposal_aktif', '$tgl', '$topik', '$fotoName', 'Menunggu')";
    
    if(mysqli_query($conn, $query)){
        echo "<script>alert('Logbook berhasil disimpan!'); window.location='dashboard_mhs.php?page=bimbingan';</script>";
    } else {
        echo "<script>alert('Gagal menyimpan: ".mysqli_error($conn)."');</script>";
    }
}
?>

<?php if ($is_valid): ?>
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <i class="bi bi-pencil-fill"></i> Isi Logbook Baru
        </div>
        <div class="card-body">
            <form method="POST" enctype="multipart/form-data">
                
                <div class="mb-3">
                    <label class="fw-bold small mb-1">Dosen Pembimbing</label>
                    <input type="text" class="form-control bg-light" value="<?= $nama_pembimbing ?>" readonly>
                    <input type="hidden" name="nidn" value="<?= $nidn_pembimbing ?>">
                </div>
                
                <div class="mb-3">
                    <label class="fw-bold small mb-1">Topik Bimbingan</label>
                    <textarea name="topik" class="form-control" rows="3" required placeholder="Misal: Revisi Bab 1 tentang Latar Belakang..."></textarea>
                </div>

                <div class="mb-3">
                    <label class="fw-bold small mb-1">Bukti Foto Bimbingan (Opsional)</label>
                    <input type="file" name="bukti_foto" class="form-control" accept="image/jpeg, image/png, image/jpg">
                    <div class="form-text text-muted">Format: JPG, PNG. Maks 2MB.</div>
                </div>

                <button type="submit" name="tambah_log" class="btn btn-primary w-100">
                    <i class="bi bi-save"></i> Simpan Logbook
                </button>
            </form>
        </div>
    </div>

<?php else: ?>
    <div class="alert alert-warning border-warning shadow-sm d-flex align-items-center" role="alert">
        <i class="bi bi-exclamation-triangle-fill fs-1 me-3 text-warning"></i>
        <div>
            <h5 class="alert-heading fw-bold">Akses Dibatasi</h5>
            <p class="mb-0">
                Anda belum dapat mengisi logbook bimbingan. <br>
                Status Proposal: <strong><?= strtoupper($status_proposal) ?></strong>. <br>
                Pastikan proposal sudah <strong>Disetujui</strong> dan <strong>Dosen Pembimbing</strong> telah ditentukan oleh Koordinator.
            </p>
        </div>
    </div>
<?php endif; ?>

<div class="card shadow-sm mt-4">
    <div class="card-header bg-white fw-bold border-bottom">
        <i class="bi bi-clock-history"></i> Riwayat Bimbingan Anda
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-striped table-hover mb-0 small align-middle">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">No</th>
                        <th>Tanggal</th>
                        <th>Topik</th>
                        <th>Bukti</th> <th>Catatan</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Ambil Data Bimbingan (Join Dosen)
                    // Kolom: NIDN (bukan nidn_pembimbing), Tanggal (PascalCase), Topik, Bukti_Foto
                    $log = mysqli_query($conn, "SELECT b.*, d.Nama as nama_dosen 
                                                FROM Bimbingan b 
                                                JOIN Dosen d ON b.NIDN = d.NIDN 
                                                WHERE b.NIM='$nim' 
                                                ORDER BY b.Tanggal DESC");
                    $no = 1;
                    if ($log && mysqli_num_rows($log) > 0) {
                        while($row = mysqli_fetch_array($log)):
                    ?>
                    <tr>
                        <td class="ps-3"><?= $no++ ?></td>
                        <td><?= date('d M Y', strtotime($row['Tanggal'])) ?></td>
                        <td><?= htmlspecialchars($row['Topik']) ?></td>
                        
                        <td>
                            <?php if(!empty($row['Bukti_Foto'])): ?>
                                <a href="uploads/bukti_bimbingan/<?= $row['Bukti_Foto'] ?>" target="_blank" class="btn btn-sm btn-outline-info">
                                    <i class="bi bi-image"></i> Lihat
                                </a>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>

                        <td class="text-danger fst-italic"><?= $row['Catatan_Dosen'] ?? '-' ?></td>
                        <td>
                            <?php if($row['Status'] == 'ACC' || $row['Status'] == 'Disetujui'): ?>
                                <span class="badge bg-success">ACC</span>
                            <?php elseif($row['Status'] == 'Revisi'): ?>
                                <span class="badge bg-danger">Revisi</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">Menunggu</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; 
                    } ?>
                </tbody>
            </table>
            <?php if(!$log || mysqli_num_rows($log) == 0): ?>
                <div class="p-4 text-center text-muted">
                    <i class="bi bi-inbox fs-4 d-block mb-2"></i> Belum ada data bimbingan.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>