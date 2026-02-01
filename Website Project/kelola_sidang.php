<?php
// Cek akses
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'Koordinator') { exit("Akses Ditolak."); }

// ==============================================================================
// 1. AMBIL DATA DOSEN (KHUSUS PENGUJI)
// ==============================================================================
$arr_dosen = [];

// PERBAIKAN: Filter berdasarkan Role='Penguji' dan gunakan Nama Tabel 'Dosen'
$q_dosen = @mysqli_query($conn, "SELECT NIDN, Nama FROM Dosen WHERE Role='Penguji'");
if (!$q_dosen) { 
    // Fallback jika nama kolom di database berbeda
    $q_dosen = mysqli_query($conn, "SELECT NIDN, Nama FROM Dosen WHERE Role='Penguji'"); 
}

if ($q_dosen) {
    while ($d = mysqli_fetch_assoc($q_dosen)) { $arr_dosen[] = $d; }
}

// ==============================================================================
// 2. LOGIKA TETAPKAN JADWAL
// ==============================================================================
if (isset($_POST['tetapkan_jadwal'])) {
    $id_sidang = $_POST['id_sidang']; // idSidang
    $penguji   = $_POST['penguji'];   // NIDN Penguji
    $tanggal   = $_POST['tanggal'] . ' ' . $_POST['jam']; 
    $ruang     = mysqli_real_escape_string($conn, $_POST['ruang']);
    
    // Update tabel Sidang (Kolom: NIDN (penguji), tanggal_sidang, ruangan, status_sidang)
    // Ingat, kolom NIDN di tabel Sidang adalah foreign key ke Dosen (Penguji)
    $q = "UPDATE Sidang SET NIDN='$penguji', tanggal_sidang='$tanggal', ruangan='$ruang', status_sidang='Dijadwalkan' WHERE idSidang='$id_sidang'";
    
    if(mysqli_query($conn, $q)){
        echo "<script>alert('Jadwal berhasil ditetapkan!'); window.location='dashboard_dosen.php?page=sidang';</script>";
    } else {
        echo "<script>alert('Gagal update: ".mysqli_error($conn)."');</script>";
    }
}

// ==============================================================================
// 3. LOGIKA SIMPAN NILAI
// ==============================================================================
if (isset($_POST['simpan_nilai'])) {
    $id_sidang = $_POST['id_sidang'];
    $nilai     = $_POST['nilai_akhir'];
    if ($nilai !== "") {
        $nilai = (float) $nilai; // Float karena nilai bisa koma
        
        $q = "UPDATE Sidang SET nilai_akhir='$nilai', status_sidang='Selesai' WHERE idSidang='$id_sidang'";
        mysqli_query($conn, $q);
        echo "<script>window.location='dashboard_dosen.php?page=sidang';</script>";
    }
}

// ==============================================================================
// 4. AMBIL DATA SIDANG MENUNGGU (SIMPAN KE ARRAY DULU)
// ==============================================================================
$data_pending = [];
// JOIN: Sidang (s), Proposal (p), Mahasiswa (m)
$q_pending = mysqli_query($conn, "
    SELECT s.*, m.Nama, m.NIM, p.Judul, p.file_dokumen as file_laporan 
    FROM Sidang s
    JOIN Proposal p ON s.idProposal = p.idProposal
    JOIN Mahasiswa m ON p.NIM = m.NIM
    WHERE s.status_sidang = 'Menunggu Jadwal'
");

if($q_pending) {
    while($row = mysqli_fetch_assoc($q_pending)){
        $data_pending[] = $row;
    }
}
?>

<div class="row">
    <div class="col-12 mb-4">
        <div class="card shadow-sm border-start border-4 border-warning">
            <div class="card-header bg-white fw-bold py-3">
                <i class="bi bi-inbox-fill text-warning me-2"></i> Permintaan Sidang Baru
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light text-secondary small">
                            <tr>
                                <th class="ps-3">Mahasiswa</th>
                                <th>Judul Laporan</th>
                                <th>File</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(count($data_pending) > 0): ?>
                                <?php foreach($data_pending as $row): ?>
                                <tr>
                                    <td class="ps-3">
                                        <span class="fw-bold"><?= $row['Nama'] ?></span><br>
                                        <small class="text-muted"><?= $row['NIM'] ?></small>
                                    </td>
                                    <td><?= substr($row['Judul'], 0, 40) ?>...</td>
                                    <td>
                                        <?php if(!empty($row['file_laporan'])): ?>
                                            <a href="uploads/proposal/<?= $row['file_laporan'] ?>" target="_blank" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                                <i class="bi bi-link-45deg"></i> Link
                                            </a>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-warning btn-sm fw-bold text-dark" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#modalJadwal<?= str_replace(['-', ' '], '', $row['idSidang']) ?>">
                                            Atur Jadwal
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="4" class="text-center py-4 text-muted">Tidak ada permintaan sidang baru.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="card shadow-sm border-start border-4 border-success">
            <div class="card-header bg-white fw-bold py-3">
                <i class="bi bi-calendar-check text-success me-2"></i> Jadwal Aktif & Nilai
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-3">Mahasiswa</th>
                                <th>Jadwal & Penguji</th>
                                <th class="text-center">Nilai</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Query menampilkan sidang yang SUDAH dijadwalkan
                            // Kolom Penguji ada di NIDN tabel Sidang
                            $q_fixed = mysqli_query($conn, "
                                SELECT s.*, m.Nama, d.Nama as penguji 
                                FROM Sidang s 
                                JOIN Proposal p ON s.idProposal = p.idProposal
                                JOIN Mahasiswa m ON p.NIM = m.NIM
                                LEFT JOIN Dosen d ON s.NIDN = d.NIDN
                                WHERE s.status_sidang != 'Menunggu Jadwal'
                                ORDER BY s.tanggal_sidang DESC
                            ");
                            if($q_fixed && mysqli_num_rows($q_fixed) > 0):
                                while($row = mysqli_fetch_assoc($q_fixed)):
                            ?>
                            <tr>
                                <td class="ps-3 fw-bold"><?= $row['Nama'] ?></td>
                                <td>
                                    <?= date('d M Y, H:i', strtotime($row['tanggal_sidang'])) ?><br>
                                    <small class="text-muted">Penguji: <?= $row['penguji'] ?? '-' ?></small>
                                </td>
                                <td class="text-center">
                                    <h5 class="fw-bold m-0 text-success"><?= $row['nilai_akhir'] ?? '-' ?></h5>
                                </td>
                                <td class="p-2">
                                    <form method="POST" class="d-flex gap-1">
                                        <input type="hidden" name="id_sidang" value="<?= $row['idSidang'] ?>">
                                        <input type="number" step="0.01" name="nilai_akhir" class="form-control form-control-sm" style="width:70px" placeholder="0-100" value="<?= $row['nilai_akhir'] ?>">
                                        <button type="submit" name="simpan_nilai" class="btn btn-success btn-sm"><i class="bi bi-save"></i></button>
                                    </form>
                                </td>
                            </tr>
                            <?php endwhile; else: ?>
                                <tr><td colspan="4" class="text-center py-4 text-muted">Belum ada jadwal sidang aktif.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php foreach($data_pending as $row): 
    $modalID = "modalJadwal" . str_replace(['-', ' '], '', $row['idSidang']);
?>
<div class="modal fade" id="<?= $modalID ?>" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h6 class="modal-title fw-bold">Tetapkan Jadwal Sidang</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="id_sidang" value="<?= $row['idSidang'] ?>">
                    <div class="mb-3">
                        <label class="small fw-bold">Mahasiswa</label>
                        <input type="text" class="form-control bg-light" value="<?= $row['Nama'] ?>" readonly>
                    </div>
                    <div class="mb-2">
                        <label class="small fw-bold">Penguji</label>
                        <select name="penguji" class="form-select" required>
                            <option value="">-- Pilih Dosen Penguji --</option>
                            <?php if(empty($arr_dosen)): ?>
                                <option disabled>Tidak ada data penguji</option>
                            <?php else: ?>
                                <?php foreach($arr_dosen as $d): ?>
                                    <option value="<?= $d['NIDN'] ?>"><?= $d['Nama'] ?></option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="row mb-2">
                        <div class="col-6">
                            <label class="small fw-bold">Tanggal</label>
                            <input type="date" name="tanggal" class="form-control" required>
                        </div>
                        <div class="col-6">
                            <label class="small fw-bold">Jam</label>
                            <input type="time" name="jam" class="form-control" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="small fw-bold">Ruangan</label>
                        <input type="text" name="ruang" class="form-control" placeholder="Cth: B202" required>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="tetapkan_jadwal" class="btn btn-primary btn-sm fw-bold px-4">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endforeach; ?>