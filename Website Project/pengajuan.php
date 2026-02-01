<?php
// Pastikan NIM terdefinisi
if (!isset($nim) || empty($nim)) {
    $nim = isset($_SESSION['username']) ? $_SESSION['username'] : ''; 
    if(empty($nim)) { die("Error: Sesi NIM habis. Silakan login ulang."); }
}

// 1. AMBIL DATA PROPOSAL
// Sesuaikan nama kolom dengan DB baru
$prop_lama = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM Proposal WHERE NIM='$nim'"));

// 2. AMBIL DATA PERPANJANGAN
$ext_lama = null;
if ($prop_lama) {
    $id_prop = $prop_lama['idProposal']; // idProposal (PascalCase)
    // Cek tabel Perpanjangan
    $ext_lama = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM Perpanjangan WHERE id_proposal='$id_prop'"));
}

// =================================================================================
// LOGIKA 1: SUBMIT / REVISI PROPOSAL (+ FILE UPLOAD)
// =================================================================================
if (isset($_POST['submit_proposal'])) {
    $judul = mysqli_real_escape_string($conn, $_POST['judul']);
    $jenis = $_POST['jenis'];
    $tgl   = date('Y-m-d');

    // --- PROSES UPLOAD FILE ---
    $file_upload = ""; 
    $upload_ok = true;
    
    // Cek apakah user mengupload file baru
    if (isset($_FILES['file_proposal']) && $_FILES['file_proposal']['error'] == 0) {
        $target_dir = "uploads/proposal/";
        if (!file_exists($target_dir)) { mkdir($target_dir, 0777, true); }

        $file_name = $_FILES['file_proposal']['name'];
        $file_tmp  = $_FILES['file_proposal']['tmp_name'];
        $file_ext  = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        // Validasi ekstensi
        if ($file_ext != "pdf") {
            echo "<script>alert('Gagal: Hanya file PDF yang diperbolehkan!');</script>";
            $upload_ok = false;
        } else {
            // Rename file: NIM_Timestamp.pdf
            $new_name = $nim . "_" . time() . ".pdf";
            $target_file = $target_dir . $new_name;

            if (move_uploaded_file($file_tmp, $target_file)) {
                $file_upload = $new_name;
            } else {
                echo "<script>alert('Gagal mengupload file ke server.');</script>";
                $upload_ok = false;
            }
        }
    }
    // ---------------------------

    if ($upload_ok) {
        if ($prop_lama) {
            // LOGIKA UPDATE (REVISI)
            // Kolom status_pengajuan (Bukan status)
            if ($prop_lama['status_pengajuan'] == 'Revisi' || $prop_lama['status_pengajuan'] == 'Ditolak') {
                // Jika ada file baru, update kolom file. Jika tidak, abaikan.
                // Kolom file_dokumen (Bukan file_proposal)
                $sql_file = ($file_upload != "") ? ", file_dokumen='$file_upload'" : "";

                $query = "UPDATE Proposal SET 
                          Judul='$judul', 
                          jenis_ta='$jenis', 
                          status_pengajuan='Diajukan', 
                          tanggal_pengajuan='$tgl', 
                          catatan_koor='' 
                          $sql_file 
                          WHERE NIM='$nim'";
                
                if (mysqli_query($conn, $query)) {
                    echo "<script>alert('Revisi proposal berhasil dikirim!'); window.location='dashboard_mhs.php?page=pengajuan';</script>";
                } else {
                    echo "<script>alert('Gagal Update: ".mysqli_error($conn)."');</script>";
                }
            }
        } else {
            // LOGIKA INSERT (BARU)
            if ($file_upload == "") {
                echo "<script>alert('Harap upload file proposal (PDF)!');</script>";
            } else {
                // GENERATE ID MANUAL (PROP-xxxx)
                $id_baru = "PROP-" . date("ymd") . rand(100, 999);

                $query = "INSERT INTO Proposal (idProposal, NIM, Judul, jenis_ta, file_dokumen, status_pengajuan, tanggal_pengajuan) 
                          VALUES ('$id_baru', '$nim', '$judul', '$jenis', '$file_upload', 'Diajukan', '$tgl')";
                
                if (mysqli_query($conn, $query)) {
                    echo "<script>alert('Proposal berhasil diajukan!'); window.location='dashboard_mhs.php?page=pengajuan';</script>";
                } else {
                    echo "<script>alert('Gagal Simpan: ".mysqli_error($conn)."');</script>";
                }
            }
        }
    }
}

// =================================================================================
// LOGIKA 2: SUBMIT PERPANJANGAN
// =================================================================================
if (isset($_POST['submit_extend'])) {
    $id_prop = $prop_lama['idProposal']; // PascalCase
    $lama    = $_POST['lama_perpanjangan'];
    $alasan  = mysqli_real_escape_string($conn, $_POST['alasan']);
    $tgl     = date('Y-m-d');

    // Tabel Perpanjangan (P Capital)
    $cek_ex = mysqli_query($conn, "SELECT id_perpanjangan FROM Perpanjangan WHERE id_proposal='$id_prop'");
    
    if(mysqli_num_rows($cek_ex) > 0){
         $q_ex = "UPDATE Perpanjangan SET lama_perpanjangan='$lama', alasan='$alasan', status_perpanjangan='Diajukan', tanggal_pengajuan='$tgl' WHERE id_proposal='$id_prop'";
    } else {
         $q_ex = "INSERT INTO Perpanjangan (nim, id_proposal, lama_perpanjangan, alasan, status_perpanjangan, tanggal_pengajuan) 
                  VALUES ('$nim', '$id_prop', '$lama', '$alasan', 'Diajukan', '$tgl')";
    }

    if (mysqli_query($conn, $q_ex)) {
        echo "<script>alert('Pengajuan Perpanjangan Berhasil Dikirim!'); window.location='dashboard_mhs.php?page=pengajuan';</script>";
    } else {
        echo "<script>alert('Gagal Simpan Extend: ".mysqli_error($conn)."');</script>";
    }
}
?>

<div class="row">
    <div class="col-12 mb-4">
        <?php if ($prop_lama): ?>
            <div class="card shadow-sm border-0 border-start border-4 <?= ($prop_lama['status_pengajuan']=='Disetujui')?'border-success':(($prop_lama['status_pengajuan']=='Ditolak')?'border-danger':'border-warning') ?>">
                <div class="card-body">
                    <h5 class="fw-bold">Status Proposal TA</h5>
                    <table class="table table-borderless table-sm mb-0 small">
                        <tr><td width="130">Judul</td><td class="fw-bold">: <?= $prop_lama['Judul'] ?></td></tr>
                        <tr><td>Jenis</td><td>: <?= $prop_lama['jenis_ta'] ?></td></tr>
                        
                        <tr>
                            <td>File Proposal</td>
                            <td>: 
                                <?php if(!empty($prop_lama['file_dokumen'])): ?>
                                    <a href="uploads/proposal/<?= $prop_lama['file_dokumen'] ?>" target="_blank" class="text-decoration-none fw-bold text-primary">
                                        <i class="bi bi-file-earmark-pdf-fill text-danger"></i> Unduh PDF
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted fst-italic">File tidak tersedia</span>
                                <?php endif; ?>
                            </td>
                        </tr>

                        <tr>
                            <td>Status Validasi</td>
                            <td>: 
                                <?php 
                                if($prop_lama['status_pengajuan'] == 'Disetujui') echo '<span class="badge bg-success">DISETUJUI</span>';
                                elseif($prop_lama['status_pengajuan'] == 'Ditolak') echo '<span class="badge bg-danger">DITOLAK</span>';
                                else echo '<span class="badge bg-warning text-dark">MENUNGGU VERIFIKASI</span>';
                                ?>
                            </td>
                        </tr>
                    </table>
                    <?php if(!empty($prop_lama['catatan_koor'])): ?>
                        <div class="alert alert-danger mt-3 p-2 small">
                            <i class="bi bi-chat-quote-fill me-1"></i> <strong>Catatan Koordinator:</strong> "<?= $prop_lama['catatan_koor'] ?>"
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php else: ?>
            <div class="alert alert-info border-info shadow-sm">
                <i class="bi bi-info-circle-fill me-2"></i> Anda belum mengajukan proposal Tugas Akhir. Silakan isi form di bawah.
            </div>
        <?php endif; ?>
    </div>

    <div class="col-12">
        <?php if ($prop_lama && $prop_lama['status_pengajuan'] == 'Disetujui'): ?>
            <div class="card shadow-sm border-0">
                <div class="card-header bg-success text-white fw-bold">
                    <i class="bi bi-hourglass-split me-2"></i> Pengajuan Perpanjangan Waktu (Extend)
                </div>
                <div class="card-body">
                    <?php if ($ext_lama): ?>
                        <div class="text-center py-4">
                            <i class="bi bi-clock-history fs-1 text-muted"></i>
                            <h5 class="mt-3">Status Pengajuan Extend</h5>
                            <p class="mb-2">Durasi: <strong><?= $ext_lama['lama_perpanjangan'] ?> Bulan</strong></p>
                            <h4>
                                <?php 
                                if($ext_lama['status_perpanjangan'] == 'Disetujui') echo '<span class="badge bg-success">DISETUJUI</span>';
                                elseif($ext_lama['status_perpanjangan'] == 'Ditolak') echo '<span class="badge bg-danger">DITOLAK</span>';
                                else echo '<span class="badge bg-warning text-dark">SEDANG DIPROSES</span>';
                                ?>
                            </h4>
                        </div>
                    <?php else: ?>
                        <form method="POST">
                            <div class="mb-3">
                                <label class="fw-bold small">Durasi Perpanjangan</label>
                                <select name="lama_perpanjangan" class="form-select" required>
                                    <option value="1">1 Bulan</option>
                                    <option value="3">3 Bulan</option>
                                    <option value="6">6 Bulan</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="fw-bold small">Alasan Perpanjangan</label>
                                <textarea name="alasan" class="form-control" rows="3" required></textarea>
                            </div>
                            <div class="d-flex justify-content-end">
                                <button type="submit" name="submit_extend" class="btn btn-success fw-bold">
                                    <i class="bi bi-send-fill me-2"></i> Kirim Pengajuan
                                </button>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>
            </div>

        <?php elseif (!$prop_lama || $prop_lama['status_pengajuan'] == 'Revisi' || $prop_lama['status_pengajuan'] == 'Ditolak'): ?>
            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white fw-bold">
                    <i class="bi bi-pencil-square me-2"></i> Form Proposal Tugas Akhir
                </div>
                <div class="card-body">
                    <form method="POST" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Judul Proposal</label>
                            <textarea name="judul" class="form-control" rows="3" required placeholder="Masukkan judul lengkap..."><?= ($prop_lama)?$prop_lama['Judul']:'' ?></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Jenis Tugas Akhir</label>
                            <select name="jenis" class="form-select" required>
                                <option value="Rancang Bangun" <?= ($prop_lama && $prop_lama['jenis_ta']=='Rancang Bangun')?'selected':'' ?>>Rancang Bangun</option>
                                <option value="Skripsi" <?= ($prop_lama && $prop_lama['jenis_ta']=='Skripsi')?'selected':'' ?>>Skripsi</option>
                                <option value="Publikasi" <?= ($prop_lama && $prop_lama['jenis_ta']=='Publikasi')?'selected':'' ?>>Publikasi</option>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold">Upload File Proposal (PDF)</label>
                            <input type="file" name="file_proposal" class="form-control" accept=".pdf">
                            <div class="form-text small text-muted">
                                *Wajib format PDF. Maksimal 5MB. 
                                <?= ($prop_lama) ? 'Biarkan kosong jika tidak ingin mengubah file saat revisi.' : '' ?>
                            </div>
                        </div>

                        <div class="d-grid">
                            <button type="submit" name="submit_proposal" class="btn btn-primary fw-bold">
                                <i class="bi bi-send me-2"></i> Kirim Proposal
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        <?php else: ?>
            <div class="text-center py-5 bg-light rounded border">
                <div class="spinner-border text-primary mb-3" role="status"></div>
                <h4 class="fw-bold">Proposal Sedang Diverifikasi</h4>
                <p class="text-muted">Mohon menunggu Koordinator untuk memvalidasi proposal Anda.</p>
            </div>
        <?php endif; ?>
    </div>
</div>