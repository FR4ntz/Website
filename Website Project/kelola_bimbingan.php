<?php
// ==============================================================================
// 1. LOGIKA UPDATE RESPON DOSEN
// ==============================================================================
if (isset($_POST['update_respon'])) {
    $id_bim  = $_POST['id_bimbingan']; // Ini idBimbingan (CHAR)
    $catatan = mysqli_real_escape_string($conn, $_POST['catatan']);
    $status  = $_POST['status'];
    
    // Sesuaikan Nama Kolom & Tabel (PascalCase)
    $query = "UPDATE Bimbingan SET Catatan_Dosen='$catatan', Status='$status' WHERE idBimbingan='$id_bim'";
    
    if (mysqli_query($conn, $query)) {
        echo "<script>alert('Respon berhasil disimpan!'); window.location='dashboard_dosen.php?page=bimbingan';</script>";
    } else {
        echo "<script>alert('Gagal Update: ".mysqli_error($conn)."');</script>";
    }
}
?>

<div class="card shadow-sm mb-4">
    <div class="card-header bg-primary text-white fw-bold">
        <i class="bi bi-people-fill me-2"></i> Daftar Mahasiswa Bimbingan
    </div>
    <div class="card-body p-0">
        
        <?php
        // 1. AMBIL DATA MAHASISWA (DISTINCT / UNIK)
        // Join Tabel Bimbingan & Mahasiswa (Gunakan nama kolom baru)
        $q_mhs = mysqli_query($conn, "SELECT DISTINCT m.NIM, m.Nama 
                                      FROM Bimbingan b 
                                      JOIN Mahasiswa m ON b.NIM = m.NIM 
                                      WHERE b.NIDN = '$nidn'
                                      ORDER BY m.Nama ASC");

        if($q_mhs && mysqli_num_rows($q_mhs) > 0):
        ?>
            <div class="accordion accordion-flush" id="accordionBimbingan">
                
                <?php 
                $no = 1;
                while($mhs = mysqli_fetch_array($q_mhs)): 
                    $nim_mhs = $mhs['NIM'];
                    
                    // Hitung jumlah bimbingan pending (Status='Menunggu')
                    $q_count = mysqli_query($conn, "SELECT COUNT(*) as total FROM Bimbingan WHERE NIM='$nim_mhs' AND NIDN='$nidn' AND Status='Menunggu'");
                    $d_count = mysqli_fetch_assoc($q_count);
                    $pending = $d_count['total'];
                ?>
                
                <div class="accordion-item">
                    <h2 class="accordion-header" id="heading<?= $nim_mhs ?>">
                        <button class="accordion-button collapsed py-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?= $nim_mhs ?>">
                            <div class="d-flex w-100 justify-content-between align-items-center pe-3">
                                <div>
                                    <span class="fw-bold text-dark"><?= $mhs['Nama'] ?></span>
                                    <span class="text-muted small ms-2">(<?= $nim_mhs ?>)</span>
                                </div>
                                <?php if($pending > 0): ?>
                                    <span class="badge bg-danger rounded-pill"><?= $pending ?> Menunggu Respon</span>
                                <?php else: ?>
                                    <span class="badge bg-success rounded-pill opacity-75"><i class="bi bi-check"></i> Aman</span>
                                <?php endif; ?>
                            </div>
                        </button>
                    </h2>
                    
                    <div id="collapse<?= $nim_mhs ?>" class="accordion-collapse collapse" data-bs-parent="#accordionBimbingan">
                        <div class="accordion-body bg-light p-3">
                            
                            <div class="card border-0 shadow-sm">
                                <div class="table-responsive">
                                    <table class="table table-bordered mb-0 align-middle small bg-white">
                                        <thead class="table-secondary">
                                            <tr>
                                                <th width="20%">Tanggal</th>
                                                <th>Topik & Bukti</th>
                                                <th width="15%">Status</th>
                                                <th width="35%">Respon Dosen</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            // 2. AMBIL DETAIL BIMBINGAN PER MAHASISWA
                                            // Kolom: NIM, NIDN, Tanggal (PascalCase)
                                            $q_bim = mysqli_query($conn, "SELECT * FROM Bimbingan 
                                                                          WHERE NIM='$nim_mhs' AND NIDN='$nidn' 
                                                                          ORDER BY Tanggal DESC");
                                            while($row = mysqli_fetch_array($q_bim)):
                                            ?>
                                            <tr>
                                                <td>
                                                    <i class="bi bi-calendar-event me-1 text-muted"></i> 
                                                    <?= date('d M Y', strtotime($row['Tanggal'])) ?>
                                                </td>
                                                <td>
                                                    <div class="fw-bold mb-1">Topik:</div>
                                                    <p class="mb-2 text-muted fst-italic">"<?= $row['Topik'] ?>"</p>
                                                    
                                                    <?php if(!empty($row['Bukti_Foto'])): ?>
                                                        <a href="uploads/bukti_bimbingan/<?= $row['Bukti_Foto'] ?>" target="_blank" class="btn btn-sm btn-outline-info py-0 px-2" style="font-size: 0.75rem;">
                                                            <i class="bi bi-image me-1"></i> Lihat Bukti
                                                        </a>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="text-center">
                                                    <?php 
                                                        $bg = 'secondary';
                                                        if($row['Status']=='Menunggu') $bg = 'warning text-dark';
                                                        elseif($row['Status']=='ACC' || $row['Status']=='Disetujui') $bg = 'success';
                                                        elseif($row['Status']=='Revisi') $bg = 'danger';
                                                        echo "<span class='badge bg-$bg w-100'>{$row['Status']}</span>";
                                                    ?>
                                                </td>
                                                <td class="bg-white">
                                                    <form method="POST">
                                                        <input type="hidden" name="id_bimbingan" value="<?= $row['idBimbingan'] ?>">
                                                        
                                                        <textarea name="catatan" class="form-control form-control-sm mb-2" rows="2" placeholder="Catatan..."><?= $row['Catatan_Dosen'] ?></textarea>
                                                        
                                                        <div class="input-group input-group-sm">
                                                            <select name="status" class="form-select">
                                                                <option value="Menunggu" <?= $row['Status']=='Menunggu'?'selected':'' ?>>Menunggu</option>
                                                                <option value="Revisi" <?= $row['Status']=='Revisi'?'selected':'' ?>>Revisi</option>
                                                                <option value="ACC" <?= ($row['Status']=='ACC' || $row['Status']=='Disetujui')?'selected':'' ?>>ACC</option>
                                                            </select>
                                                            <button type="submit" name="update_respon" class="btn btn-primary">
                                                                <i class="bi bi-send"></i>
                                                            </button>
                                                        </div>
                                                    </form>
                                                </td>
                                            </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            </div>
                    </div>
                </div>
                <?php endwhile; ?>
                
            </div>
        <?php else: ?>
            <div class="text-center py-5 text-muted">
                <i class="bi bi-people fs-1 opacity-25"></i>
                <p class="mt-2">Belum ada mahasiswa yang melakukan bimbingan.</p>
            </div>
        <?php endif; ?>
        
    </div>
</div>