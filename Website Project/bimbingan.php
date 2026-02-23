<?php
// --- 1. SETTING FOLDER UPLOAD ---
$uploadDir = 'uploads/bukti_bimbingan/';
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// --- 2. AMBIL DATA PROPOSAL & STATUS SIAP SIDANG ---
$q_cek = "SELECT p.status_pengajuan, p.NIDN_Pembimbing, d.Nama as nama_dosen, p.idProposal, p.siap_sidang
          FROM Proposal p 
          LEFT JOIN Dosen d ON p.NIDN_Pembimbing = d.NIDN 
          WHERE p.NIM = '$nim'";
$cek_prop = mysqli_query($conn, $q_cek);
$data_prop = mysqli_fetch_assoc($cek_prop);

$status_proposal    = $data_prop['status_pengajuan'] ?? 'Belum Mengajukan';
$nidn_pembimbing    = $data_prop['NIDN_Pembimbing'] ?? '';
$nama_pembimbing    = $data_prop['nama_dosen'] ?? 'Belum Ditentukan';
$id_proposal_aktif  = $data_prop['idProposal'] ?? '';
$status_siap_sidang = $data_prop['siap_sidang'] ?? 'Belum';

// --- 3. HITUNG JUMLAH BIMBINGAN ACC ---
$q_count = "SELECT COUNT(*) as total_acc FROM Bimbingan 
            WHERE NIM = '$nim' AND (Status = 'ACC' OR Status = 'Disetujui')";
$res_count = mysqli_query($conn, $q_count);
$data_acc = mysqli_fetch_assoc($res_count);
$jumlah_acc = $data_acc['total_acc'] ?? 0;

// Logika Validasi
$is_valid = ($status_proposal == 'Disetujui' && !empty($nidn_pembimbing));
$boleh_sidang = ($jumlah_acc >= 8 && $status_siap_sidang == 'Siap');

// --- 4. LOGIKA SIMPAN LOGBOOK ---
if (isset($_POST['tambah_log']) && $is_valid) {
    $nidn   = $_POST['nidn']; 
    $topik  = mysqli_real_escape_string($conn, $_POST['topik']);
    $tgl    = date('Y-m-d H:i:s');
    
    $fotoName = null;
    if (isset($_FILES['bukti_foto']) && $_FILES['bukti_foto']['error'] === UPLOAD_ERR_OK) {
        $fileExtension = strtolower(pathinfo($_FILES['bukti_foto']['name'], PATHINFO_EXTENSION));
        $newFileName = $nim . '_' . time() . '.' . $fileExtension;
        if(move_uploaded_file($_FILES['bukti_foto']['tmp_name'], $uploadDir . $newFileName)) {
            $fotoName = $newFileName;
        }
    }

    $id_bimb = "BIMB" . date('ym') . mt_rand(1000, 9999);
    $query = "INSERT INTO Bimbingan (idBimbingan, NIM, NIDN, idProposal, Tanggal, Topik, Bukti_Foto, Status) 
              VALUES ('$id_bimb', '$nim', '$nidn', '$id_proposal_aktif', '$tgl', '$topik', '$fotoName', 'Menunggu')";
    
    if(mysqli_query($conn, $query)){
        echo "<script>alert('Logbook berhasil disimpan!'); window.location='dashboard_mhs.php?page=bimbingan';</script>";
        exit; 
    }
}
?>

<div class="card shadow-sm border-0 mb-4 bg-light">
    <div class="card-body">
        <div class="row align-items-center text-center text-md-start">
            <div class="col-md-8">
                <h5 class="fw-bold text-primary mb-1"><i class="bi bi-mortarboard-fill me-2"></i>Status Kelayakan Sidang</h5>
                <p class="small text-muted mb-0">
                    Syarat: 
                    <span class="badge <?= $jumlah_acc >= 8 ? 'bg-success' : 'bg-secondary' ?> p-2 px-3"><?= $jumlah_acc ?>/8 ACC</span> 
                    <span class="ms-1">|</span>
                    <span class="badge <?= $status_siap_sidang == 'Siap' ? 'bg-success' : 'bg-secondary' ?> p-2 px-3">Izin Dosen: <?= $status_siap_sidang ?></span>
                </p>
            </div>
            <div class="col-md-4 text-md-end mt-3 mt-md-0">
                <?php if($boleh_sidang): ?>
                    <a href="?page=daftar_sidang" class="btn btn-success btn-lg shadow-sm w-100">
                        <i class="bi bi-check-all me-1"></i> Daftar Sidang Akhir
                    </a>
                <?php else: ?>
                    <button class="btn btn-secondary btn-lg shadow-sm w-100" disabled style="background-color: #adb5bd; border:none;">
                        <i class="bi bi-lock-fill me-1"></i> Belum Memenuhi Syarat
                    </button>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12 mb-4">
        <?php if ($is_valid): ?>
            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white fw-bold py-3">
                    <i class="bi bi-pencil-square me-2"></i>Isi Logbook Baru
                </div>
                <div class="card-body p-4">
                    <form method="POST" enctype="multipart/form-data">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="small fw-bold mb-2">Dosen Pembimbing</label>
                                <input type="text" class="form-control bg-light py-2" value="<?= $nama_pembimbing ?>" readonly>
                                <input type="hidden" name="nidn" value="<?= $nidn_pembimbing ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="small fw-bold mb-2">Bukti Foto (Opsional)</label>
                                <input type="file" name="bukti_foto" class="form-control py-2" accept="image/*">
                            </div>
                            <div class="col-12 mb-3">
                                <label class="small fw-bold mb-2">Topik Bimbingan</label>
                                <textarea name="topik" class="form-control" rows="3" required placeholder="Apa yang Anda konsultasikan hari ini?"></textarea>
                            </div>
                        </div>
                        <div class="text-end">
                            <button type="submit" name="tambah_log" class="btn btn-primary px-5 py-2 shadow-sm">
                                <i class="bi bi-send me-2"></i>Kirim Logbook
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        <?php else: ?>
            <div class="alert alert-warning shadow-sm py-4">
                <h5 class="fw-bold"><i class="bi bi-exclamation-triangle me-2"></i>Akses Terbatas</h5>
                Status Proposal: <b><?= $status_proposal ?></b>. Anda baru bisa mengisi logbook setelah proposal disetujui.
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white fw-bold border-bottom py-3">
                <i class="bi bi-clock-history me-2 text-primary"></i>Riwayat Bimbingan
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr class="small text-uppercase">
                            <th class="ps-4" width="15%">Tanggal</th>
                            <th width="50%">Topik & Catatan Dosen</th>
                            <th width="15%" class="text-center">Bukti</th>
                            <th width="20%" class="text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $log = mysqli_query($conn, "SELECT * FROM Bimbingan WHERE NIM='$nim' ORDER BY Tanggal DESC");
                        if(mysqli_num_rows($log) > 0):
                            while($row = mysqli_fetch_array($log)):
                                $color = ($row['Status'] == 'ACC' || $row['Status'] == 'Disetujui') ? 'success' : ($row['Status'] == 'Revisi' ? 'danger' : 'warning text-dark');
                        ?>
                        <tr>
                            <td class="ps-4 fw-bold text-muted"><?= date('d M Y', strtotime($row['Tanggal'])) ?></td>
                            <td>
                                <div class="fw-bold text-dark mb-1"><?= htmlspecialchars($row['Topik']) ?></div>
                                <?php if($row['Catatan_Dosen']): ?>
                                    <div class="bg-light p-2 rounded border-start border-danger border-3 small">
                                        <span class="text-danger fw-bold small">Catatan Dosen:</span><br>
                                        <span class="text-muted fst-italic"><?= $row['Catatan_Dosen'] ?></span>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <?php if($row['Bukti_Foto']): ?>
                                    <a href="uploads/bukti_bimbingan/<?= $row['Bukti_Foto'] ?>" target="_blank" class="btn btn-sm btn-outline-info rounded-pill px-3">
                                        <i class="bi bi-image"></i> Lihat
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-<?= $color ?> rounded-pill px-4 py-2" style="font-size: 0.75rem;">
                                    <?= strtoupper($row['Status']) ?>
                                </span>
                            </td>
                        </tr>
                        <?php endwhile; 
                        else: ?>
                        <tr>
                            <td colspan="4" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-2 d-block mb-2 opacity-50"></i>
                                Belum ada riwayat bimbingan.
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>