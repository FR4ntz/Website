<?php
// 1. CEK AKSES (HINDARI DOUBLE SESSION START)
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['Koordinator', 'Dosen'])) { 
    echo "<div class='alert alert-danger'>Akses Ditolak.</div>"; 
    exit; 
}

// Konfigurasi Folder Upload (DIUBAH KE ASSETS/IMG/)
$uploadDir = 'assets/img/';
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true); 
}

$error   = "";
$sukses  = "";
$edit_mode = false;

// Variabel Default Form
$v_judul = "";
$v_penulis = "";
$v_deskripsi = "";
$v_badge = "primary";
$v_gambar_lama = "";
$v_id = "";

// ==========================================================
// 2. LOGIKA HAPUS DATA
// ==========================================================
if (isset($_GET['op']) && $_GET['op'] == 'hapus') {
    $id = $_GET['id'];
    
    $q1 = mysqli_query($conn, "SELECT gambar FROM konten_publik WHERE id = '$id'");
    $r1 = mysqli_fetch_assoc($q1);
    
    if ($r1['gambar'] != '' && file_exists($uploadDir . $r1['gambar'])) {
        unlink($uploadDir . $r1['gambar']);
    }

    $q2 = mysqli_query($conn, "DELETE FROM konten_publik WHERE id = '$id'");
    if ($q2) {
        echo "<script>alert('Data berhasil dihapus'); window.location='dashboard_dosen.php?page=konten';</script>";
    } else {
        $error = "Gagal menghapus data";
    }
}

// ==========================================================
// 3. LOGIKA EDIT (AMBIL DATA)
// ==========================================================
if (isset($_GET['op']) && $_GET['op'] == 'edit') {
    $id = $_GET['id'];
    $q1 = mysqli_query($conn, "SELECT * FROM konten_publik WHERE id = '$id'");
    $r1 = mysqli_fetch_assoc($q1);
    if ($r1) {
        $edit_mode   = true;
        $v_id        = $r1['id'];
        $v_judul     = $r1['judul'];
        $v_penulis   = $r1['penulis'];
        $v_deskripsi = $r1['deskripsi'];
        $v_badge     = $r1['warna_badge'];
        $v_gambar_lama = $r1['gambar'];
    }
}

// ==========================================================
// 4. LOGIKA SIMPAN (CREATE / UPDATE)
// ==========================================================
if (isset($_POST['simpan'])) {
    $judul      = mysqli_real_escape_string($conn, $_POST['judul']);
    $penulis    = mysqli_real_escape_string($conn, $_POST['penulis']);
    $deskripsi  = mysqli_real_escape_string($conn, $_POST['deskripsi']);
    $badge      = $_POST['warna_badge'];
    $tanggal    = date('Y-m-d');
    
    $nama_gambar = $_POST['gambar_lama'];
    
    if ($_FILES['gambar']['name']) {
        $nama_file   = $_FILES['gambar']['name'];
        $tmp_file    = $_FILES['gambar']['tmp_name'];
        $ext         = strtolower(pathinfo($nama_file, PATHINFO_EXTENSION));
        $ext_boleh   = ['jpg', 'jpeg', 'png'];

        if (in_array($ext, $ext_boleh)) {
            $nama_gambar_baru = time() . '_' . $nama_file;
            $path = $uploadDir . $nama_gambar_baru;
            
            if (move_uploaded_file($tmp_file, $path)) {
                $nama_gambar = $nama_gambar_baru;
                if ($edit_mode && $_POST['gambar_lama'] != '' && file_exists($uploadDir . $_POST['gambar_lama'])) {
                    unlink($uploadDir . $_POST['gambar_lama']);
                }
            }
        }
    }

    if ($_GET['op'] == 'edit') { // UPDATE
        $id = $_GET['id'];
        $sql = "UPDATE konten_publik SET 
                judul='$judul', penulis='$penulis', warna_badge='$badge', 
                deskripsi='$deskripsi', gambar='$nama_gambar' 
                WHERE id='$id'";
        if (mysqli_query($conn, $sql)) {
            echo "<script>alert('Data diperbarui'); window.location='dashboard_dosen.php?page=konten';</script>";
        }
    } else { // INSERT
        $sql = "INSERT INTO konten_publik (judul, tanggal, penulis, warna_badge, deskripsi, gambar) 
                VALUES ('$judul', '$tanggal', '$penulis', '$badge', '$deskripsi', '$nama_gambar')";
        if (mysqli_query($conn, $sql)) {
            echo "<script>alert('Berita diterbitkan'); window.location='dashboard_dosen.php?page=konten';</script>";
        }
    }
}
?>

<div class="row">
    
    <div class="col-12 mb-4">
        <h4 class="fw-bold text-primary"><i class="bi bi-newspaper me-2"></i> Kelola Berita (CMS)</h4>
        <p class="text-muted small">Manajemen artikel yang tampil di halaman depan website.</p>
    </div>

    <div class="col-12 mb-4">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-primary text-white fw-bold d-flex justify-content-between align-items-center">
                <span><i class="bi bi-pencil-square me-2"></i> <?= ($edit_mode) ? "Edit Konten" : "Tambah Konten Baru" ?></span>
                <?php if ($edit_mode): ?>
                    <a href="dashboard_dosen.php?page=konten" class="btn btn-sm btn-light text-primary fw-bold">Batal Edit</a>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <form action="" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="gambar_lama" value="<?= $v_gambar_lama ?>">

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label small fw-bold">Judul Artikel</label>
                                <input type="text" name="judul" class="form-control" value="<?= $v_judul ?>" required placeholder="Masukkan Judul Berita...">
                            </div>
                            
                            <div class="row g-2">
                                <div class="col-6 mb-3">
                                    <label class="form-label small fw-bold">Penulis</label>
                                    <input type="text" name="penulis" class="form-control" value="<?= $v_penulis ?>" required placeholder="Misal: Admin">
                                </div>
                                <div class="col-6 mb-3">
                                    <label class="form-label small fw-bold">Warna Label</label>
                                    <select name="warna_badge" class="form-select">
                                        <option value="primary" <?= ($v_badge=='primary')?'selected':'' ?>>Biru</option>
                                        <option value="success" <?= ($v_badge=='success')?'selected':'' ?>>Hijau</option>
                                        <option value="danger"  <?= ($v_badge=='danger')?'selected':'' ?>>Merah</option>
                                        <option value="warning" <?= ($v_badge=='warning')?'selected':'' ?>>Kuning</option>
                                    </select>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-bold">Gambar Utama</label>
                                <input type="file" name="gambar" class="form-control form-control-sm">
                                <?php if ($edit_mode && $v_gambar_lama): ?>
                                    <div class="mt-2 p-2 bg-light border rounded d-inline-block">
                                        <img src="assets/img/<?= $v_gambar_lama ?>" style="height: 60px;" class="rounded me-2">
                                        <span class="small text-muted">Gambar saat ini</span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3 h-100 d-flex flex-column">
                                <label class="form-label small fw-bold">Isi Berita / Deskripsi</label>
                                <textarea name="deskripsi" class="form-control flex-grow-1" rows="5" required placeholder="Tulis isi berita di sini..."><?= $v_deskripsi ?></textarea>
                            </div>
                        </div>
                    </div>

                    <hr class="my-3">
                    
                    <div class="text-end">
                        <button type="submit" name="simpan" class="btn btn-success fw-bold px-4">
                            <i class="bi bi-save me-2"></i> Simpan Data
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white fw-bold py-3 border-bottom">
                <i class="bi bi-list-task text-primary me-2"></i> Daftar Berita Tayang
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light small text-uppercase">
                            <tr>
                                <th class="ps-4" width="5%">No</th>
                                <th width="10%">Img</th>
                                <th width="25%">Judul & Info</th>
                                <th>Deskripsi Singkat</th>
                                <th class="text-center" width="15%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $q2 = mysqli_query($conn, "SELECT * FROM konten_publik ORDER BY tanggal DESC");
                            $no = 1;
                            if(mysqli_num_rows($q2) > 0) {
                                while ($r2 = mysqli_fetch_assoc($q2)) {
                                    // Perhatikan path gambar: assets/img/
                                    $img = ($r2['gambar'] && file_exists('assets/img/'.$r2['gambar'])) ? 'assets/img/'.$r2['gambar'] : 'https://via.placeholder.com/50';
                                    ?>
                                    <tr>
                                        <td class="ps-4 fw-bold"><?= $no++ ?></td>
                                        <td>
                                            <img src="<?= $img ?>" class="rounded border" style="width: 60px; height: 40px; object-fit: cover;">
                                        </td>
                                        <td>
                                            <div class="fw-bold text-dark text-truncate" style="max-width: 200px;"><?= $r2['judul'] ?></div>
                                            <div class="small text-muted mt-1">
                                                <i class="bi bi-calendar"></i> <?= $r2['tanggal'] ?> 
                                                <span class="badge bg-<?= $r2['warna_badge'] ?> ms-1 py-0 px-2"><?= $r2['penulis'] ?></span>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="text-secondary small text-truncate" style="max-width: 350px;">
                                                <?= substr($r2['deskripsi'], 0, 100) ?>...
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <a href="dashboard_dosen.php?page=konten&op=edit&id=<?= $r2['id'] ?>" class="btn btn-warning btn-sm py-1 px-2 mb-1 text-white" title="Edit">
                                                <i class="bi bi-pencil-square"></i>
                                            </a>
                                            <a href="dashboard_dosen.php?page=konten&op=hapus&id=<?= $r2['id'] ?>" class="btn btn-danger btn-sm py-1 px-2 mb-1" onclick="return confirm('Hapus?')" title="Hapus">
                                                <i class="bi bi-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php
                                }
                            } else {
                                echo "<tr><td colspan='5' class='text-center py-5 text-muted'>Belum ada berita yang diterbitkan.</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</div>