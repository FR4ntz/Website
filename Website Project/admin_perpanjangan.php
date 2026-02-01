<?php
// Cek akses
if ($_SESSION['role'] != 'Koordinator') { exit("Akses Ditolak."); }

// ==============================================================================
// 1. LOGIKA VALIDASI (ACC/TOLAK)
// ==============================================================================
if (isset($_POST['aksi'])) {
    $id_ext = $_POST['id_perpanjangan'];
    $status = $_POST['status_perpanjangan']; // Disetujui / Ditolak
    
    // Update tabel Perpanjangan
    $q = "UPDATE Perpanjangan SET status_perpanjangan='$status' WHERE id_perpanjangan='$id_ext'";
    
    if (mysqli_query($conn, $q)) {
        echo "<script>alert('Status berhasil diubah menjadi: $status'); window.location='dashboard_dosen.php?page=extend';</script>";
    } else {
        echo "<script>alert('Gagal update: ".mysqli_error($conn)."');</script>";
    }
}

// ==============================================================================
// 2. AMBIL DATA PENGAJUAN
// ==============================================================================
// Join: Perpanjangan, Mahasiswa, Proposal
$query = "SELECT ex.*, m.Nama, m.NIM, p.Judul 
          FROM Perpanjangan ex
          JOIN Mahasiswa m ON ex.nim = m.NIM
          JOIN Proposal p ON ex.id_proposal = p.idProposal
          ORDER BY ex.tanggal_pengajuan DESC";

$result = mysqli_query($conn, $query);
?>

<div class="card shadow-sm border-0">
    <div class="card-header bg-white py-3">
        <h5 class="m-0 fw-bold text-dark"><i class="bi bi-hourglass-split me-2"></i> Validasi Perpanjangan Waktu (Extend)</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle mb-0 small">
                <thead class="bg-light text-secondary text-uppercase">
                    <tr>
                        <th>Tgl Pengajuan</th>
                        <th>Mahasiswa</th>
                        <th>Judul TA</th>
                        <th>Durasi & Alasan</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && mysqli_num_rows($result) > 0): ?>
                        <?php while ($row = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td><?= date('d M Y', strtotime($row['tanggal_pengajuan'])) ?></td>
                                <td>
                                    <span class="fw-bold"><?= $row['Nama'] ?></span><br>
                                    <span class="text-muted"><?= $row['NIM'] ?></span>
                                </td>
                                <td><?= substr($row['Judul'], 0, 50) ?>...</td>
                                <td>
                                    <span class="badge bg-info text-dark mb-1"><?= $row['lama_perpanjangan'] ?> Bulan</span><br>
                                    <span class="text-muted fst-italic">"<?= $row['alasan'] ?>"</span>
                                </td>
                                <td class="text-center">
                                    <?php 
                                    $st = $row['status_perpanjangan'];
                                    $bg = ($st=='Disetujui')?'success':(($st=='Ditolak')?'danger':'warning text-dark');
                                    echo "<span class='badge bg-$bg rounded-pill'>$st</span>";
                                    ?>
                                </td>
                                <td class="text-center">
                                    <?php if($st == 'Diajukan'): ?>
                                        <form method="POST" class="d-flex gap-1 justify-content-center">
                                            <input type="hidden" name="id_perpanjangan" value="<?= $row['id_perpanjangan'] ?>">
                                            
                                            <button type="submit" name="aksi" value="acc" class="btn btn-success btn-sm" title="Setujui" onclick="this.form.status_perpanjangan.value='Disetujui'">
                                                <i class="bi bi-check-lg"></i>
                                            </button>
                                            
                                            <button type="submit" name="aksi" value="tolak" class="btn btn-danger btn-sm" title="Tolak" onclick="this.form.status_perpanjangan.value='Ditolak'">
                                                <i class="bi bi-x-lg"></i>
                                            </button>
                                            
                                            <input type="hidden" name="status_perpanjangan" id="status_input">
                                        </form>
                                    <?php else: ?>
                                        <i class="bi bi-lock-fill text-muted"></i>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="6" class="text-center py-4 text-muted">Belum ada pengajuan perpanjangan.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>