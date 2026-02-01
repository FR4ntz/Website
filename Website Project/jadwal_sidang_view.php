<div class="card shadow-sm border-0">
    <div class="card-header bg-primary text-white fw-bold">
        <i class="bi bi-calendar-week me-2"></i> Jadwal Sidang Anda
    </div>
    <div class="card-body text-center py-5">
        <?php
        // 1. QUERY DATABASE
        // Tabel: Sidang (s), Proposal (p), Dosen (d)
        // Join menggunakan idProposal dan NIDN
        $q_jadwal = mysqli_query($conn, "SELECT s.*, d.Nama as penguji, p.Judul 
                                         FROM Sidang s
                                         JOIN Proposal p ON s.idProposal = p.idProposal
                                         LEFT JOIN Dosen d ON s.NIDN = d.NIDN
                                         WHERE p.NIM = '$nim'");
        
        // 2. CEK APAKAH ADA DATA SIDANG
        if ($q_jadwal && mysqli_num_rows($q_jadwal) > 0) {
            $row = mysqli_fetch_assoc($q_jadwal);
            
            // --- LOGIKA STATUS ---
            
            // KONDISI 1: JIKA STATUS REVISI
            if ($row['status_sidang'] == 'Revisi') {
                echo "<div class='alert alert-warning border-warning shadow-sm d-inline-block text-start p-4'>";
                echo "<h4 class='fw-bold text-warning'><i class='bi bi-exclamation-triangle-fill'></i> Status: REVISI</h4>";
                echo "<p>Anda diminta melakukan revisi laporan. Silakan perbaiki dan hubungi dosen penguji.</p>";
                echo "<hr>";
                echo "<p class='mb-0 fw-bold'>Nilai Sementara: <span class='badge bg-warning text-dark fs-6'>" . ($row['nilai_akhir'] ?? '-') . "</span></p>";
                echo "</div>";
                
            } 
            
            // KONDISI 2: JIKA MENUNGGU JADWAL DARI KOORDINATOR
            elseif ($row['status_sidang'] == 'Menunggu Jadwal') {
                echo "<div class='py-4'>";
                echo "<i class='bi bi-hourglass-split fs-1 text-muted'></i>";
                echo "<h4 class='text-muted mt-3'>Menunggu Penjadwalan</h4>";
                echo "<p class='text-secondary'>Koordinator belum menetapkan jadwal sidang untuk Anda.<br>Mohon cek secara berkala.</p>";
                echo "</div>";
            } 
            
            // KONDISI 3: JIKA SUDAH LULUS (SELESAI dan Nilai Bagus bisa dianggap Lulus)
            // Di database baru ENUM('Menunggu Jadwal', 'Dijadwalkan', 'Selesai', 'Lulus', 'Tidak Lulus', 'Revisi')
            elseif ($row['status_sidang'] == 'Lulus' || ($row['status_sidang'] == 'Selesai' && $row['nilai_akhir'] >= 60)) {
                echo "<div class='alert alert-success d-inline-block px-5 py-4 shadow-sm'>";
                echo "<h1 class='display-1 mb-3'><i class='bi bi-trophy-fill text-success'></i></h1>";
                echo "<h3 class='fw-bold'>SELAMAT! ANDA LULUS</h3>";
                echo "<p class='lead mb-0'>Nilai Akhir: <strong class='fs-3'>{$row['nilai_akhir']}</strong></p>";
                echo "</div>";
            }
            
            // KONDISI 4: JIKA TIDAK LULUS
            elseif ($row['status_sidang'] == 'Tidak Lulus') {
                echo "<div class='alert alert-danger d-inline-block px-5 py-4 shadow-sm'>";
                echo "<h1 class='display-1 mb-3'><i class='bi bi-x-circle-fill text-danger'></i></h1>";
                echo "<h3 class='fw-bold'>TIDAK LULUS</h3>";
                echo "<p>Silakan hubungi Dosen Pembimbing untuk arahan selanjutnya.</p>";
                echo "<p class='lead mb-0'>Nilai Akhir: <strong>{$row['nilai_akhir']}</strong></p>";
                echo "</div>";
            }

            // KONDISI 5: JIKA DIJADWALKAN (NORMAL / AKAN SIDANG)
            // Bisa status 'Dijadwalkan' atau 'Selesai' tapi belum dinilai
            else {
                echo "<div class='border rounded p-4 d-inline-block shadow-sm bg-light'>";
                echo "<h3 class='text-primary fw-bold mb-3'>Sidang Dijadwalkan!</h3>";
                
                $tgl_sidang = isset($row['tanggal_sidang']) ? $row['tanggal_sidang'] : null;
                
                if ($tgl_sidang) {
                    echo "<h5 class='mb-3'><i class='bi bi-calendar-event'></i> " . date('d F Y', strtotime($tgl_sidang)) . "</h5>";
                    echo "<h2 class='display-6 fw-bold mb-3'>" . date('H:i', strtotime($tgl_sidang)) . " WIB</h2>";
                } else {
                    echo "<h5 class='text-muted'>Waktu belum ditentukan</h5>";
                }
                
                echo "<div class='d-flex justify-content-center gap-3 mt-4'>";
                echo "<div class='text-start border-end pe-3'>";
                echo "<small class='text-muted d-block'>Ruangan</small>";
                echo "<strong>" . ($row['ruangan'] ?? 'Online') . "</strong>";
                echo "</div>";
                echo "<div class='text-start'>";
                echo "<small class='text-muted d-block'>Dosen Penguji</small>";
                echo "<strong>" . ($row['penguji'] ?? '-') . "</strong>";
                echo "</div>";
                echo "</div>";
                echo "</div>";
            }

        } else {
            // 3. JIKA BELUM DAFTAR SAMA SEKALI
            echo "<img src='https://cdn-icons-png.flaticon.com/512/7486/7486744.png' width='100' class='mb-3 opacity-50'>";
            echo "<h5 class='text-muted'>Belum Ada Data Sidang</h5>";
            echo "<p class='small text-secondary'>Silakan ajukan pendaftaran sidang terlebih dahulu pada menu Daftar Sidang.</p>";
            echo "<a href='dashboard_mhs.php?page=daftar_sidang' class='btn btn-primary btn-sm px-4 rounded-pill'>Daftar Sidang Sekarang</a>";
        }
        ?>
    </div>
</div>