<?php
// Pastikan NIM ada
$nim = $_SESSION['nim'];

// 1. CEK DATA PROPOSAL (Tabel: Proposal)
// Kolom status di DB baru adalah 'status_pengajuan'
$q_prop = mysqli_query($conn, "SELECT * FROM Proposal WHERE NIM='$nim' AND status_pengajuan='Disetujui'");
$prop_setuju = mysqli_fetch_assoc($q_prop);

// 2. LOGIKA PENGAJUAN
if (isset($_POST['ajukan_extend'])) {
    if (!$prop_setuju) {
        echo "<script>alert('Gagal: Anda tidak memiliki proposal yang disetujui!');</script>";
    } else {
        $id_prop = $prop_setuju['idProposal'];
        $alasan  = mysqli_real_escape_string($conn, $_POST['alasan']);
        $tgl     = date('Y-m-d');
        $lama    = 6; // Default 6 bulan sesuai aturan di form
        
        // Cek apakah ada pengajuan yang masih pending (Tabel: Perpanjangan)
        $cek = mysqli_query($conn, "SELECT * FROM Perpanjangan WHERE id_proposal='$id_prop' AND status_perpanjangan='Diajukan'");
        
        if (mysqli_num_rows($cek) == 0) {
            // Insert ke Tabel Perpanjangan (Pastikan kolom lama_perpanjangan diisi)
            $query = "INSERT INTO Perpanjangan (id_proposal, nim, alasan, lama_perpanjangan, status_perpanjangan, tanggal_pengajuan) 
                      VALUES ('$id_prop', '$nim', '$alasan', '$lama', 'Diajukan', '$tgl')";
            
            if(mysqli_query($conn, $query)){ 
                echo "<script>alert('Berhasil diajukan!'); window.location='dashboard_mhs.php?page=extend';</script>"; 
            } else {
                echo "<script>alert('Gagal simpan: ".mysqli_error($conn)."');</script>";
            }
        } else {
            echo "<script>alert('Masih ada pengajuan perpanjangan yang sedang diproses (Pending)!');</script>";
        }
    }
}
?>

<div class="card shadow-sm border-0">
    <div class="card-header bg-danger text-white fw-bold">
        <i class="bi bi-clock-history me-2"></i> Form Perpanjangan (Extend)
    </div>
    <div class="card-body">
        <?php if ($prop_setuju): ?>
            <div class="alert alert-info small mb-3 border-0 bg-opacity-10 bg-info">
                <strong><i class="bi bi-info-circle-fill"></i> Judul TA Saat Ini:</strong><br>
                <?= htmlspecialchars($prop_setuju['Judul']) ?>
            </div>
            
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label fw-bold small">Alasan Perpanjangan</label>
                    <textarea name="alasan" class="form-control" rows="4" required placeholder="Jelaskan secara rinci alasan keterlambatan penyelesaian TA..."></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold small">Durasi Perpanjangan</label>
                    <input type="text" class="form-control bg-light" value="6 Bulan (Sesuai Aturan)" readonly>
                </div>
                <div class="d-grid">
                    <button type="submit" name="ajukan_extend" class="btn btn-danger fw-bold">
                        <i class="bi bi-send-fill me-2"></i> Kirim Pengajuan
                    </button>
                </div>
            </form>
        <?php else: ?>
            <div class="alert alert-warning d-flex align-items-center mb-0">
                <i class="bi bi-exclamation-triangle-fill fs-3 me-3"></i>
                <div>
                    <strong>Akses Dibatasi</strong><br>
                    Anda belum memiliki proposal yang statusnya <b>Disetujui</b>. Tidak dapat mengajukan perpanjangan.
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="card mt-4 border-0 shadow-sm">
    <div class="card-header bg-white fw-bold border-bottom">
        <i class="bi bi-clock me-2"></i> Riwayat Pengajuan
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-striped table-hover mb-0 small align-middle">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-3">Tanggal</th>
                        <th>Durasi</th>
                        <th>Alasan</th>
                        <th class="text-center">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Ambil riwayat (Tabel: Perpanjangan)
                    $hist = mysqli_query($conn, "SELECT * FROM Perpanjangan WHERE nim='$nim' ORDER BY tanggal_pengajuan DESC");
                    
                    if(mysqli_num_rows($hist) > 0):
                        while($h = mysqli_fetch_array($hist)):
                            $badge = 'bg-warning text-dark';
                            if($h['status_perpanjangan'] == 'Disetujui') $badge = 'bg-success';
                            if($h['status_perpanjangan'] == 'Ditolak') $badge = 'bg-danger';
                    ?>
                    <tr>
                        <td class="ps-3"><?= date('d M Y', strtotime($h['tanggal_pengajuan'])) ?></td>
                        <td><?= $h['lama_perpanjangan'] ?> Bulan</td>
                        <td><?= substr($h['alasan'], 0, 50) ?>...</td>
                        <td class="text-center">
                            <span class="badge <?= $badge ?> rounded-pill px-3">
                                <?= $h['status_perpanjangan'] ?>
                            </span>
                        </td>
                    </tr>
                    <?php 
                        endwhile; 
                    else:
                    ?>
                    <tr>
                        <td colspan="4" class="text-center py-4 text-muted">Belum ada riwayat pengajuan.</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>