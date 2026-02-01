<?php
// Pastikan NIDN tersedia dari session
$nidn = $_SESSION['username']; // Login sebagai Dosen

// TANGKAP DATA MAHASISWA YANG DIPILIH
$selected_nim = isset($_GET['nim_mhs']) ? mysqli_real_escape_string($conn, $_GET['nim_mhs']) : '';
$selected_mhs = null;

// Jika ada NIM dipilih, ambil data mahasiswanya (Tabel: Mahasiswa, Kolom: NIM)
if ($selected_nim) {
    $q_mhs = mysqli_query($conn, "SELECT * FROM Mahasiswa WHERE NIM='$selected_nim'");
    if ($q_mhs && mysqli_num_rows($q_mhs) > 0) {
        $selected_mhs = mysqli_fetch_assoc($q_mhs);
    }
}

// LOGIKA KIRIM PESAN (DOSEN)
if (isset($_POST['kirim_pesan_dosen'])) {
    $isi = mysqli_real_escape_string($conn, $_POST['pesan']);
    $tgl = date('Y-m-d H:i:s');
    $nim_tujuan = mysqli_real_escape_string($conn, $_POST['nim_tujuan']);
    
    // Tabel: Pesan (Huruf Besar)
    $query = "INSERT INTO Pesan (pengirim, penerima, isi_pesan, waktu) VALUES ('$nidn', '$nim_tujuan', '$isi', '$tgl')";
    
    if(mysqli_query($conn, $query)){
        echo "<script>window.location='dashboard_dosen.php?page=chat&nim_mhs=$nim_tujuan';</script>";
    } else {
        echo "<script>alert('Gagal kirim: ".mysqli_error($conn)."');</script>";
    }
}
?>

<div class="row g-3">
    <div class="col-md-4">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white fw-bold d-flex align-items-center">
                <i class="bi bi-chat-square-text-fill text-primary me-2"></i> Daftar Pesan
            </div>
            <div class="list-group list-group-flush" style="max-height: 550px; overflow-y: auto;">
                <?php
                // QUERY LIST MAHASISWA 
                // 1. Ambil Mahasiswa dari Riwayat Chat (Tabel: Pesan)
                // 2. UNION
                // 3. Ambil Mahasiswa Bimbingan yang sudah Disetujui (Tabel: Proposal)
                
                $sql_list = "
                    SELECT DISTINCT m.NIM, m.Nama 
                    FROM Pesan ps
                    JOIN Mahasiswa m ON (ps.pengirim = m.NIM OR ps.penerima = m.NIM)
                    WHERE (ps.penerima = '$nidn' OR ps.pengirim = '$nidn') AND m.NIM != '$nidn'
                    
                    UNION
                    
                    SELECT DISTINCT m.NIM, m.Nama 
                    FROM Proposal p 
                    JOIN Mahasiswa m ON p.NIM = m.NIM 
                    WHERE p.NIDN_Pembimbing = '$nidn' AND p.status_pengajuan = 'Disetujui'
                ";

                $q_list = mysqli_query($conn, $sql_list);
                
                if (!$q_list) {
                    echo '<div class="alert alert-danger m-2 small">Error SQL: '.mysqli_error($conn).'</div>';
                } elseif (mysqli_num_rows($q_list) > 0) {
                    while ($m = mysqli_fetch_assoc($q_list)) {
                        $isActive = ($m['NIM'] == $selected_nim) ? 'active' : '';
                        $bgClass  = ($m['NIM'] == $selected_nim) ? 'bg-primary text-white border-primary' : 'list-group-item-action';
                        
                        echo "<a href='dashboard_dosen.php?page=chat&nim_mhs={$m['NIM']}' class='list-group-item $bgClass d-flex justify-content-between align-items-center'>";
                        echo "<div>";
                        echo "<div class='fw-bold mb-0'>{$m['Nama']}</div>";
                        echo "<small class='" . ($isActive ? 'text-white-50' : 'text-muted') . "'>{$m['NIM']}</small>";
                        echo "</div>";
                        echo "<i class='bi bi-chevron-right " . ($isActive ? 'text-white' : 'text-muted') . "'></i>";
                        echo "</a>";
                    }
                } else {
                    echo '<div class="p-4 text-center text-muted small">Belum ada riwayat pesan.</div>';
                }
                ?>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card shadow-sm" style="height: 600px;">
            <?php if ($selected_mhs): ?>
                <div class="card-header bg-success text-white d-flex align-items-center shadow-sm" style="background: linear-gradient(135deg, #198754, #146c43);">
                    <div class="bg-white text-success rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                        <i class="bi bi-person-fill fs-5"></i>
                    </div>
                    <div>
                        <h6 class="m-0 fw-bold"><?= $selected_mhs['Nama'] ?></h6>
                        <small style="opacity: 0.9;">Mahasiswa Bimbingan • NIM: <?= $selected_mhs['NIM'] ?></small>
                    </div>
                </div>

                <div class="card-body bg-light chat-box" id="chatContainer" style="overflow-y: auto; flex: 1;">
                    <?php
                    // Ambil Chat (Tabel: Pesan)
                    $q_chat = mysqli_query($conn, "SELECT * FROM Pesan 
                                                   WHERE (pengirim='$nidn' AND penerima='$selected_nim') 
                                                   OR (pengirim='$selected_nim' AND penerima='$nidn') 
                                                   ORDER BY waktu ASC");
                    
                    if ($q_chat && mysqli_num_rows($q_chat) > 0) {
                        while ($chat = mysqli_fetch_assoc($q_chat)) {
                            $jam = date('H:i', strtotime($chat['waktu']));
                            $isi = nl2br(htmlspecialchars($chat['isi_pesan']));
                            
                            if ($chat['pengirim'] == $nidn) {
                                // Chat Dosen (Kanan)
                                echo "
                                <div class='msg-container'>
                                    <div class='msg-user bg-success text-white'>$isi</div>
                                    <small class='text-muted ms-auto me-2' style='font-size: 0.7rem;'>$jam <i class='bi bi-check2-all text-primary'></i></small>
                                </div>";
                            } else {
                                // Chat Mahasiswa (Kiri)
                                echo "
                                <div class='msg-container'>
                                    <div class='msg-ai'>$isi</div>
                                    <small class='text-muted ms-2' style='font-size: 0.7rem;'>$jam</small>
                                </div>";
                            }
                        }
                    } else {
                        echo "
                        <div class='h-100 d-flex flex-column align-items-center justify-content-center text-muted opacity-50'>
                            <i class='bi bi-chat-dots fs-1 mb-2'></i>
                            <p>Belum ada percakapan. Mulai chat sekarang!</p>
                        </div>";
                    }
                    ?>
                </div>

                <div class="card-footer bg-white p-3">
                    <form method="POST" class="d-flex gap-2 align-items-center">
                        <input type="hidden" name="nim_tujuan" value="<?= $selected_nim ?>">
                        <input type="text" name="pesan" class="form-control rounded-pill bg-light border-0 px-3" placeholder="Ketik pesan..." autocomplete="off" required>
                        <button type="submit" name="kirim_pesan_dosen" class="btn btn-success rounded-circle shadow-sm" style="width: 45px; height: 45px;">
                            <i class="bi bi-send-fill"></i>
                        </button>
                    </form>
                </div>

            <?php else: ?>
                <div class="card-body d-flex flex-column align-items-center justify-content-center text-center h-100 text-muted">
                    <i class="bi bi-chat-left-text fs-1 text-success opacity-50 mb-3"></i>
                    <h5 class="fw-bold">Pesan Masuk</h5>
                    <p class="small w-75">Pilih mahasiswa di sebelah kiri untuk melihat chat.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    var chatBox = document.getElementById("chatContainer");
    if(chatBox){
        chatBox.scrollTop = chatBox.scrollHeight;
    }
</script>