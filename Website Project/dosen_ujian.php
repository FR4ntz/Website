<?php
// Cek akses
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'Penguji') { exit("Akses Ditolak."); }

$nidn = $_SESSION['username'];

// ==============================================================================
// 1. LOGIKA SIMPAN NILAI
// ==============================================================================
if (isset($_POST['simpan_nilai'])) {
    $id_sidang = $_POST['id_sidang'];
    $nilai     = $_POST['nilai_akhir'];
    
    // Update nilai dan ubah status jadi Selesai
    // Pastikan nama kolom ID sesuai (idSidang atau id_sidang)
    $q_nilai = "UPDATE Sidang SET nilai_akhir='$nilai', status_sidang='Selesai' WHERE idSidang='$id_sidang'";
    
    if(mysqli_query($conn, $q_nilai)){
        echo "<script>alert('Nilai berhasil disimpan!'); window.location='dashboard_dosen.php?page=ujian';</script>";
    } else {
        echo "<script>alert('Gagal simpan: ".mysqli_error($conn)."');</script>";
    }
}

// ==============================================================================
// 2. AMBIL JADWAL PENGUJI INI
// ==============================================================================
// Query Join untuk mengambil data lengkap
$query = "SELECT s.*, m.Nama, m.NIM, p.Judul, p.file_dokumen 
          FROM Sidang s
          JOIN Proposal p ON s.idProposal = p.idProposal
          JOIN Mahasiswa m ON p.NIM = m.NIM
          WHERE s.NIDN = '$nidn' AND s.status_sidang != 'Menunggu Jadwal'
          ORDER BY s.tanggal_sidang ASC";

$result = mysqli_query($conn, $query);
?>

<div class="card shadow-sm border-0">
    <div class="card-header bg-danger text-white fw-bold py-3">
        <i class="bi bi-clipboard-check me-2"></i> Jadwal & Penilaian Sidang
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th width="25%">Jadwal & Lokasi</th>
                        <th>Mahasiswa</th>
                        <th>Judul & Laporan</th>
                        <th width="15%" class="text-center">Nilai (0-100)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && mysqli_num_rows($result) > 0): ?>
                        <?php while ($row = mysqli_fetch_assoc($result)): 
                            // -----------------------------------------------------------
                            // PERBAIKAN: DETEKSI KOLOM RUANGAN (Huruf Besar/Kecil)
                            // -----------------------------------------------------------
                            // Coba ambil 'ruangan', jika tidak ada ambil 'Ruangan', jika tidak ada set '-'
                            $ruangan = $row['ruangan'] ?? $row['Ruangan'] ?? 'Online / TBD';
                            
                            // Format Tanggal
                            $tgl_sidang = date('d M Y', strtotime($row['tanggal_sidang']));
                            $jam_sidang = date('H:i', strtotime($row['tanggal_sidang']));
                        ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center mb-2">
                                        <div class="bg-danger text-white rounded p-2 text-center me-3" style="width: 50px;">
                                            <span class="d-block fw-bold small"><?= date('M', strtotime($row['tanggal_sidang'])) ?></span>
                                            <span class="d-block h5 mb-0 fw-bold"><?= date('d', strtotime($row['tanggal_sidang'])) ?></span>
                                        </div>
                                        <div>
                                            <span class="fw-bold text-dark d-block">Jam: <?= $jam_sidang ?> WIB</span>
                                            <small class="text-muted">
                                                <i class="bi bi-geo-alt-fill text-danger"></i> <?= $ruangan ?>
                                            </small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="fw-bold text-dark"><?= $row['Nama'] ?></span><br>
                                    <span class="text-muted small"><?= $row['NIM'] ?></span>
                                </td>
                                <td>
                                    <div class="mb-2 small fst-italic">"<?= substr($row['Judul'], 0, 50) ?>..."</div>
                                    
                                    <?php 
                                    $file_dokumen = isset($row['file_dokumen']) ? $row['file_dokumen'] : '';
                                    if(!empty($file_dokumen)): 
                                        $link_file = "uploads/proposal/" . $file_dokumen; 
                                    ?> 
                                    <a href="<?= $link_file ?>" target="_blank" class="btn btn-outline-primary btn-sm py-0 rounded-pill small">
                                        <i class="bi bi-file-earmark-pdf"></i> Lihat Laporan
                                    </a>
                                    <?php else: ?>
                                        <span class="badge bg-light text-muted border">Belum ada file</span>
                                    <?php endif; ?>
                                </td>
                                <td class="bg-light">
                                    <form method="POST">
                                        <input type="hidden" name="id_sidang" value="<?= $row['idSidang'] ?? $row['id_sidang'] ?>">
                                        
                                        <div class="input-group input-group-sm mb-2">
                                            <input type="number" step="0.01" name="nilai_akhir" class="form-control fw-bold text-center" 
                                                   value="<?= $row['nilai_akhir'] ?>" placeholder="0" min="0" max="100" required>
                                            <button type="submit" name="simpan_nilai" class="btn btn-success" title="Simpan Nilai">
                                                <i class="bi bi-check-lg"></i>
                                            </button>
                                        </div>
                                        
                                        <div class="text-center">
                                            <?php 
                                                $st = $row['status_sidang'];
                                                $bg = ($st == 'Selesai') ? 'bg-success' : 'bg-warning text-dark';
                                            ?>
                                            <span class="badge <?= $bg ?>"><?= $st ?></span>
                                        </div>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="4" class="text-center py-5 text-muted">Belum ada jadwal sidang untuk Anda.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>