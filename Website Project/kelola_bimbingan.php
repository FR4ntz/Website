<?php
// ==============================================================================
// 1. LOGIKA UPDATE (Respon, Izin, & Jadwal)
// ==============================================================================

// A. Update Respon Bimbingan
if (isset($_POST['update_respon'])) {
    $id_bim  = $_POST['id_bimbingan']; 
    $catatan = mysqli_real_escape_string($conn, $_POST['catatan']);
    $status  = $_POST['status'];
    
    $query = "UPDATE Bimbingan SET Catatan_Dosen='$catatan', Status='$status' WHERE idBimbingan='$id_bim'";
    if (mysqli_query($conn, $query)) {
        echo "<script>alert('Respon berhasil disimpan!'); window.location='dashboard_dosen.php?page=bimbingan';</script>";
    }
}

// B. Update Izin Sidang
if (isset($_POST['berikan_izin'])) {
    $nim_target = $_POST['nim_mhs'];
    $query_izin = "UPDATE Proposal SET siap_sidang = 'Siap' WHERE NIM = '$nim_target'";
    if (mysqli_query($conn, $query_izin)) {
        echo "<script>alert('Izin sidang berhasil diberikan!'); window.location='dashboard_dosen.php?page=bimbingan';</script>";
    }
}

// C. Update Jadwal Sidang (Sesuai Struktur Tabel: tanggal_sidang DATETIME)
if (isset($_POST['atur_jadwal'])) {
    $id_sidang = $_POST['id_sidang'];
    $tgl       = $_POST['tgl_sidang']; // YYYY-MM-DD
    $jam       = $_POST['jam_sidang']; // HH:MM
    $ruang     = mysqli_real_escape_string($conn, $_POST['ruangan']);

    // Gabungkan Tanggal & Jam untuk kolom tanggal_sidang (datetime)
    $datetime_gabung = $tgl . ' ' . $jam . ':00';

    $query_jadwal = "UPDATE Sidang SET 
                     Ruangan = '$ruang', 
                     tanggal_sidang = '$datetime_gabung', 
                     status_sidang = 'Dijadwalkan' 
                     WHERE idSidang = '$id_sidang'";

    if (mysqli_query($conn, $query_jadwal)) {
        echo "<script>alert('Jadwal sidang berhasil disimpan!'); window.location='dashboard_dosen.php?page=bimbingan';</script>";
    }
}
?>

<div class="card shadow-sm mb-4">
    <div class="card-header bg-primary text-white fw-bold py-3">
        <i class="bi bi-people-fill me-2"></i> Pengelolaan Bimbingan & Sidang
    </div>
    <div class="card-body p-0">
        <?php
        $q_mhs = mysqli_query($conn, "SELECT DISTINCT m.NIM, m.Nama, p.siap_sidang, p.idProposal, s.idSidang, s.tanggal_sidang, s.status_sidang, s.Ruangan
                                      FROM Bimbingan b 
                                      JOIN Mahasiswa m ON b.NIM = m.NIM 
                                      LEFT JOIN Proposal p ON m.NIM = p.NIM
                                      LEFT JOIN Sidang s ON p.idProposal = s.idProposal
                                      WHERE b.NIDN = '$nidn'
                                      ORDER BY m.Nama ASC");

        if($q_mhs && mysqli_num_rows($q_mhs) > 0):
            $modal_list = []; 
        ?>
            <div class="accordion accordion-flush" id="accordionBimbingan">
                <?php 
                while($mhs = mysqli_fetch_array($q_mhs)): 
                    $nim_mhs = $mhs['NIM'];
                    $is_siap = $mhs['siap_sidang'];
                    
                    $q_acc = mysqli_query($conn, "SELECT COUNT(*) as total FROM Bimbingan WHERE NIM='$nim_mhs' AND (Status='ACC' OR Status='Disetujui')");
                    $jml_acc = mysqli_fetch_assoc($q_acc)['total'];

                    if(!empty($mhs['idSidang'])) { $modal_list[] = $mhs; }
                ?>
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed py-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?= $nim_mhs ?>">
                            <div class="d-flex w-100 justify-content-between align-items-center pe-3">
                                <div>
                                    <span class="fw-bold text-dark"><?= $mhs['Nama'] ?></span>
                                    <span class="badge bg-light text-primary border ms-2">ACC: <?= $jml_acc ?></span>
                                </div>
                                <div>
                                    <?php if($mhs['status_sidang'] == 'Dijadwalkan'): ?>
                                        <span class="badge bg-info text-dark me-2">Dijadwalkan: <?= date('d/m/y', strtotime($mhs['tanggal_sidang'])) ?></span>
                                    <?php endif; ?>
                                    <?php if($is_siap == 'Siap'): ?>
                                        <span class="badge bg-success me-2">Siap Sidang</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </button>
                    </h2>
                    
                    <div id="collapse<?= $nim_mhs ?>" class="accordion-collapse collapse" data-bs-parent="#accordionBimbingan">
                        <div class="accordion-body bg-light">
                            <div class="row mb-3 g-2">
                                <div class="col-md-6">
                                    <div class="card card-body border-0 shadow-sm">
                                        <h6 class="fw-bold small">Izin Sidang</h6>
                                        <?php if($is_siap != 'Siap' && $jml_acc >= 8): ?>
                                            <form method="POST"><input type="hidden" name="nim_mhs" value="<?= $nim_mhs ?>"><button type="submit" name="berikan_izin" class="btn btn-primary btn-sm w-100">Berikan Izin</button></form>
                                        <?php else: ?>
                                            <span class="badge bg-<?= $is_siap=='Siap'?'success':'secondary' ?> w-100 py-2"><?= $is_siap=='Siap'?'Sudah Diizinkan':'ACC Belum Cukup' ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card card-body border-0 shadow-sm">
                                        <h6 class="fw-bold small">Jadwal Sidang</h6>
                                        <?php if(!empty($mhs['idSidang'])): ?>
                                            <button class="btn btn-info btn-sm w-100" data-bs-toggle="modal" data-bs-target="#modalJadwal<?= $mhs['idSidang'] ?>">Atur/Ubah Jadwal</button>
                                        <?php else: ?>
                                            <span class="badge bg-secondary w-100 py-2">Belum Daftar</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <table class="table table-sm table-bordered bg-white small shadow-sm">
                                <thead class="table-dark"><tr><th>Tgl</th><th>Topik</th><th width="15%">Status</th><th width="35%">Respon</th></tr></thead>
                                <tbody>
                                    <?php
                                    $q_bim = mysqli_query($conn, "SELECT * FROM Bimbingan WHERE NIM='$nim_mhs' ORDER BY Tanggal DESC");
                                    while($row = mysqli_fetch_array($q_bim)):
                                        $bg = ($row['Status']=='ACC' || $row['Status']=='Disetujui') ? 'success' : (($row['Status']=='Revisi') ? 'danger' : 'warning text-dark');
                                    ?>
                                    <tr>
                                        <td><?= date('d/m/y', strtotime($row['Tanggal'])) ?></td>
                                        <td><?= htmlspecialchars($row['Topik']) ?></td>
                                        <td><span class="badge bg-<?= $bg ?> w-100"><?= $row['Status'] ?></span></td>
                                        <td>
                                            <form method="POST" class="d-flex gap-1">
                                                <input type="hidden" name="id_bimbingan" value="<?= $row['idBimbingan'] ?>">
                                                <input type="text" name="catatan" class="form-control form-control-sm" value="<?= $row['Catatan_Dosen'] ?>" placeholder="Catatan...">
                                                <select name="status" class="form-select form-select-sm" style="width: auto;">
                                                    <option value="Menunggu" <?= $row['Status']=='Menunggu'?'selected':'' ?>>Wait</option>
                                                    <option value="Revisi" <?= $row['Status']=='Revisi'?'selected':'' ?>>Revisi</option>
                                                    <option value="ACC" <?= ($row['Status']=='ACC' || $row['Status']=='Disetujui')?'selected':'' ?>>ACC</option>
                                                </select>
                                                <button type="submit" name="update_respon" class="btn btn-primary btn-sm"><i class="bi bi-send"></i></button>
                                            </form>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php foreach($modal_list as $m): 
    $val_tgl = !empty($m['tanggal_sidang']) ? date('Y-m-d', strtotime($m['tanggal_sidang'])) : '';
    $val_jam = !empty($m['tanggal_sidang']) ? date('H:i', strtotime($m['tanggal_sidang'])) : '';
?>
<div class="modal fade" id="modalJadwal<?= $m['idSidang'] ?>" tabindex="-1" aria-hidden="true" style="z-index: 9999;">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-info text-dark">
                <h5 class="modal-title fw-bold">Penjadwalan: <?= $m['Nama'] ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id_sidang" value="<?= $m['idSidang'] ?>">
                <div class="mb-3">
                    <label class="form-label small fw-bold">Tanggal Sidang</label>
                    <input type="date" name="tgl_sidang" class="form-control" value="<?= $val_tgl ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold">Waktu/Jam</label>
                    <input type="time" name="jam_sidang" class="form-control" value="<?= $val_jam ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold">Ruangan / Lokasi</label>
                    <input type="text" name="ruangan" class="form-control" value="<?= $m['Ruangan'] ?>" placeholder="Contoh: R. Rapat 1" required>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" name="atur_jadwal" class="btn btn-primary px-4">Simpan Jadwal Sidang</button>
            </div>
        </form>
    </div>
</div>
<?php endforeach; ?>