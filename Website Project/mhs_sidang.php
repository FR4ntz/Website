<?php
// Pastikan NIM sudah ada dari session (di-include dari dashboard)
$nim = $_SESSION['nim'];

// ==============================================================================
// 1. CEK SYARAT BIMBINGAN (Tabel: Bimbingan)
// ==============================================================================
// Kolom 'Status' di DB baru pakai huruf besar 'S'
$q_cek_bim = mysqli_query($conn, "SELECT COUNT(*) as total FROM Bimbingan WHERE NIM='$nim' AND Status='ACC'");
$data_bim  = mysqli_fetch_assoc($q_cek_bim);
$total_bimbingan = $data_bim['total'];
$syarat_minimal  = 8;
$lolos_syarat    = ($total_bimbingan >= $syarat_minimal);

// ==============================================================================
// 2. CEK DATA PROPOSAL (Tabel: Proposal)
// ==============================================================================
// Kolom status di proposal adalah 'status_pengajuan'
$q_prop = mysqli_query($conn, "SELECT idProposal FROM Proposal WHERE NIM='$nim' AND status_pengajuan='Disetujui'");
$d_prop = mysqli_fetch_assoc($q_prop);
$id_proposal = $d_prop['idProposal'] ?? null;

// ==============================================================================
// 3. CEK STATUS PENDAFTARAN SIDANG (Tabel: Sidang)
// ==============================================================================
$sudah_daftar = false;
$status_saat_ini = '';

if ($id_proposal) {
    // Cek apakah sudah ada data di tabel Sidang berdasarkan idProposal
    $q_cek_sidang = mysqli_query($conn, "SELECT * FROM Sidang WHERE idProposal='$id_proposal'");
    if (mysqli_num_rows($q_cek_sidang) > 0) {
        $sudah_daftar = true;
        $d_sidang = mysqli_fetch_assoc($q_cek_sidang);
        $status_saat_ini = $d_sidang['status_sidang'];
    }
}

// ==============================================================================
// 4. LOGIKA PENDAFTARAN (INSERT DATA)
// ==============================================================================
if (isset($_POST['daftar_sidang'])) {
    $link_laporan = mysqli_real_escape_string($conn, $_POST['link_laporan']);
    
    // Validasi
    if(!$id_proposal) {
        echo "<script>alert('Gagal: Proposal belum disetujui atau tidak ditemukan!');</script>";
    } else {
        // --- GENERATE ID SIDANG MANUAL (Karena bukan Auto Increment) ---
        // Format: SID-YYYYMM-XXX (Contoh: SID-202512-059)
        $id_sidang_baru = "SID-" . date("ymd") . rand(100, 999);

        // Insert ke Tabel Sidang (Sertakan NIM sesuai diagram)
        $query = "INSERT INTO Sidang (idSidang, idProposal, NIM, file_laporan, status_sidang) 
                  VALUES ('$id_sidang_baru', '$id_proposal', '$nim', '$link_laporan', 'Menunggu Jadwal')";
        
        if (mysqli_query($conn, $query)) {
            echo "<script>alert('Pendaftaran Berhasil!'); window.location='dashboard_mhs.php?page=daftar_sidang';</script>";
        } else {
            echo "<script>alert('Error Database: ".mysqli_error($conn)."');</script>";
        }
    }
}
?>

<div class="card shadow-sm border-0">
    <div class="card-header bg-primary text-white fw-bold">
        <i class="bi bi-mortarboard-fill me-2"></i> Pendaftaran Sidang Akhir
    </div>
    <div class="card-body">
        
        <div class="alert <?= $lolos_syarat ? 'alert-success' : 'alert-warning' ?> d-flex align-items-center shadow-sm">
            <i class="bi <?= $lolos_syarat ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill' ?> fs-1 me-3"></i>
            <div>
                <h6 class="fw-bold mb-1">Status Syarat Bimbingan</h6>
                <span>Total Disetujui: <strong><?= $total_bimbingan ?></strong> / <?= $syarat_minimal ?> Sesi.</span>
            </div>
        </div>

        <?php if ($sudah_daftar): ?>
            <div class="text-center py-5 bg-light rounded border">
                <i class="bi bi-send-check-fill fs-1 text-success"></i>
                <h4 class="mt-3 fw-bold">Pendaftaran Telah Diterima</h4>
                <p class="text-muted mb-3">Status saat ini: <span class="badge bg-warning text-dark fs-6"><?= $status_saat_ini ?></span></p>
                <div class="d-flex justify-content-center gap-2">
                    <button class="btn btn-secondary btn-sm" disabled>Menunggu Jadwal</button>
                    <?php if($status_saat_ini != 'Menunggu Jadwal'): ?>
                        <a href="dashboard_mhs.php?page=jadwal" class="btn btn-outline-primary btn-sm">Lihat Jadwal</a>
                    <?php endif; ?>
                </div>
            </div>

        <?php elseif ($lolos_syarat && $id_proposal): ?>
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label fw-bold">Link Laporan Akhir (GDrive/PDF)</label>
                    <input type="text" name="link_laporan" class="form-control" placeholder="https://drive.google.com/..." required>
                    <div class="form-text">Pastikan link dapat diakses oleh publik/dosen (Open Access).</div>
                </div>
                <button type="submit" name="daftar_sidang" class="btn btn-primary w-100 fw-bold">
                    <i class="bi bi-send me-2"></i> Daftar Sidang Sekarang
                </button>
            </form>

        <?php else: ?>
            <div class="text-center py-5">
                <i class="bi bi-lock-fill fs-1 text-secondary opacity-25"></i>
                <h5 class="text-muted mt-2">Pendaftaran Terkunci</h5>
                <p class="small text-muted">
                    <?php if(!$id_proposal): ?>
                        Anda belum memiliki Proposal yang disetujui.
                    <?php else: ?>
                        Anda belum memenuhi jumlah minimal bimbingan (Min: 8x ACC).
                    <?php endif; ?>
                </p>
            </div>
        <?php endif; ?>

    </div>
</div>