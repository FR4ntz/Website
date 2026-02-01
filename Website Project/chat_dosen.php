<?php
// Pastikan NIM tersedia
$nim = $_SESSION['nim'];

// 1. CEK DOSEN PEMBIMBING (Diperbaiki query-nya)
// Mengambil data dosen dari proposal yang sudah disetujui
// Tabel: Proposal, Dosen
// Kolom: Judul, Nama, NIDN, NIDN_Pembimbing, status_pengajuan
$cek_pembimbing = mysqli_query($conn, "SELECT p.Judul, d.Nama AS nama_dosen, d.NIDN as nidn 
                                       FROM Proposal p 
                                       JOIN Dosen d ON p.NIDN_Pembimbing = d.NIDN 
                                       WHERE p.NIM='$nim' AND p.status_pengajuan='Disetujui' LIMIT 1");
$data_dosen = mysqli_fetch_assoc($cek_pembimbing);

// 2. PROSES KIRIM PESAN
if (isset($_POST['kirim_pesan']) && $data_dosen) {
    $pesan = mysqli_real_escape_string($conn, $_POST['pesan']);
    $nidn_tujuan = $data_dosen['nidn'];
    $tgl = date('Y-m-d H:i:s');
    
    // Tabel: Pesan (Huruf Besar P)
    $query = "INSERT INTO Pesan (pengirim, penerima, isi_pesan, waktu) 
              VALUES ('$nim', '$nidn_tujuan', '$pesan', '$tgl')";
              
    if(mysqli_query($conn, $query)){
        // Redirect agar tidak resubmit saat refresh
        echo "<script>window.location='dashboard_mhs.php?page=chat';</script>";
    } else {
        echo "<script>alert('Gagal mengirim pesan: ".mysqli_error($conn)."');</script>";
    }
}
?>

<div class="card shadow-sm" style="height: 600px;">
    <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
        <div>
            <i class="bi bi-person-circle me-2"></i> 
            <?= $data_dosen ? $data_dosen['nama_dosen'] : 'Belum Ada Pembimbing' ?>
        </div>
        <span class="badge bg-light text-dark small">Online</span>
    </div>
    
    <div class="card-body bg-light chat-box" id="chatContainer" style="overflow-y: auto; flex: 1;">
        <?php if ($data_dosen): ?>
            <?php
            // 3. MENAMPILKAN RIWAYAT PESAN (REAL-TIME DARI DB)
            $nidn_dosen = $data_dosen['nidn'];
            
            // Ambil pesan antara Mahasiswa (Saya) dan Dosen Pembimbing
            // Tabel: Pesan
            $q_chat = mysqli_query($conn, "SELECT * FROM Pesan 
                                           WHERE (pengirim='$nim' AND penerima='$nidn_dosen') 
                                           OR (pengirim='$nidn_dosen' AND penerima='$nim') 
                                           ORDER BY waktu ASC");
            
            if(mysqli_num_rows($q_chat) > 0) {
                while($chat = mysqli_fetch_assoc($q_chat)) {
                    $jam = date('H:i', strtotime($chat['waktu']));
                    $isi = nl2br(htmlspecialchars($chat['isi_pesan']));

                    // Jika Pengirim == Saya (Mahasiswa) -> Tampil di Kanan (Biru/Hijau)
                    if($chat['pengirim'] == $nim) {
                        echo "
                        <div class='msg-container'>
                            <div class='msg-user'>$isi</div>
                            <small class='text-muted ms-auto me-2' style='font-size: 0.7rem;'>Anda • $jam</small>
                        </div>";
                    } 
                    // Jika Pengirim == Dosen -> Tampil di Kiri (Putih)
                    else {
                        echo "
                        <div class='msg-container'>
                            <div class='msg-ai'>$isi</div>
                            <small class='text-muted ms-2' style='font-size: 0.7rem;'>Dosen • $jam</small>
                        </div>";
                    }
                }
            } else {
                echo "<p class='text-center text-muted mt-5'>Belum ada riwayat percakapan. Mulai chat sekarang!</p>";
            }
            ?>
        <?php else: ?>
            <div class="text-center text-muted mt-5">
                <p>Anda belum memiliki dosen pembimbing.<br>Proposal harus disetujui terlebih dahulu.</p>
            </div>
        <?php endif; ?>
    </div>

    <div class="card-footer bg-white">
        <form method="POST" class="d-flex gap-2">
            <input type="text" name="pesan" class="form-control rounded-pill" placeholder="Tulis pesan..." autocomplete="off" <?= $data_dosen ? '' : 'disabled' ?> required>
            <button type="submit" name="kirim_pesan" class="btn btn-success rounded-circle" style="width: 40px; height: 40px;" <?= $data_dosen ? '' : 'disabled' ?>>
                <i class="bi bi-send-fill"></i>
            </button>
        </form>
    </div>
</div>

<script>
    var chatBox = document.getElementById("chatContainer");
    if(chatBox){
        chatBox.scrollTop = chatBox.scrollHeight;
    }
</script>