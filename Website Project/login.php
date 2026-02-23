<?php
session_start();
include 'koneksi.php';

// Jika user sudah login, langsung arahkan ke dashboard
if (isset($_SESSION['role'])) {
    if ($_SESSION['role'] == 'mahasiswa') {
        header("Location: dashboard_mhs.php");
    } else {
        // Untuk Dosen, Koordinator, Penguji
        header("Location: dashboard_dosen.php");
    }
    exit;
}

// LOGIKA LOGIN
if (isset($_POST['login'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = md5($_POST['password']); // Hashing MD5

    // 1. Cek Tabel MAHASISWA (Nama Tabel & Kolom Sesuai DB Baru)
    $cek_mhs = mysqli_query($conn, "SELECT * FROM Mahasiswa WHERE NIM='$username' AND Password='$password'");
    
    if (mysqli_num_rows($cek_mhs) > 0) {
        $data = mysqli_fetch_assoc($cek_mhs);
        
        // Simpan sesi (Perhatikan huruf besar kecil key array sesuai kolom DB)
        $_SESSION['user'] = $data['Nama']; // Kolom 'Nama'
        $_SESSION['nim']  = $data['NIM'];  // Kolom 'NIM'
        $_SESSION['role'] = 'mahasiswa';   // Role manual untuk mahasiswa
        
        echo "<script>window.location='dashboard_mhs.php';</script>";
        exit;
    }

    // 2. Cek Tabel DOSEN (Nama Tabel & Kolom Sesuai DB Baru)
    $cek_dosen = mysqli_query($conn, "SELECT * FROM Dosen WHERE NIDN='$username' AND Password='$password'");
    
    if (mysqli_num_rows($cek_dosen) > 0) {
        $data = mysqli_fetch_assoc($cek_dosen);
        
        // Simpan sesi
        $_SESSION['user']     = $data['Nama']; // Kolom 'Nama'
        $_SESSION['username'] = $data['NIDN']; // Kolom 'NIDN'
        $_SESSION['role']     = $data['Role']; // Kolom 'Role' (Isinya: Dosen/Koordinator/Penguji)
        
        echo "<script>window.location='dashboard_dosen.php';</script>";
        exit;
    }

    // 3. Jika Gagal
    $error = "NIM/NIDN atau Password salah!";
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SITA Perguruan Tinggi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #003366 0%, #0056b3 100%); /* Biru */
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .login-card {
            width: 100%;
            max-width: 400px;
            background: rgba(255, 255, 255, 0.95);
            padding: 40px 30px;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.2);
            animation: slideUp 0.8s ease-out;
            position: relative;
        }

        .brand-logo {
            width: 70px;
            height: 70px;
            background: #e7f1ff;
            color: #003366;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px auto;
            font-size: 2rem;
            box-shadow: 0 5px 15px rgba(0, 51, 102, 0.1);
        }

        .form-control {
            border-radius: 10px;
            padding: 12px 15px;
            border: 1px solid #dee2e6;
            background-color: #f8f9fa;
            font-size: 0.95rem;
        }
        
        .form-control:focus {
            box-shadow: 0 0 0 3px rgba(0, 51, 102, 0.1);
            border-color: #003366;
            background-color: white;
        }

        .input-group-text {
            border-radius: 10px 0 0 10px;
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-right: none;
            color: #003366;
        }
        
        .form-control-icon {
            border-left: none;
            border-radius: 0 10px 10px 0;
        }

        .btn-login {
            background: linear-gradient(135deg, #003366 0%, #0056b3 100%);
            border: none;
            border-radius: 10px;
            padding: 12px;
            font-weight: 600;
            letter-spacing: 1px;
            box-shadow: 0 5px 15px rgba(0, 51, 102, 0.2);
            transition: all 0.3s;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 51, 102, 0.3);
            background: linear-gradient(135deg, #004080 0%, #0066cc 100%);
        }

        .footer-text {
            text-align: center;
            margin-top: 25px;
            font-size: 0.8rem;
            color: #6c757d;
        }

        /* Animasi Masuk */
        @keyframes slideUp {
            from { opacity: 0; transform: translateY(50px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>

    <div class="container px-4">
        <div class="login-card mx-auto">
            
            <div class="brand-logo">
                <i class="bi bi-mortarboard-fill"></i>
            </div>
            
            <div class="text-center mb-4">
                <h4 class="fw-bold m-0" style="color: #003366;">SITA Perguruan Tinggi</h4>
                <p class="text-muted small">Portal Sistem Informasi Tugas Akhir</p>
            </div>

            <?php if (isset($error)): ?>
                <div class="alert alert-danger py-2 small d-flex align-items-center mb-3" role="alert">
                    <i class="bi bi-exclamation-circle-fill me-2"></i> <?= $error ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="mb-3">
                    <label class="form-label small fw-bold text-secondary ms-1">NIM / NIDN</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-person-fill"></i></span>
                        <input type="text" name="username" class="form-control form-control-icon" placeholder="Masukkan ID Pengguna" required autofocus>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label small fw-bold text-secondary ms-1">Password</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                        <input type="password" name="password" id="passInput" class="form-control" placeholder="Masukkan Kata Sandi" style="border-radius: 0;" required>
                        <button class="btn btn-outline-secondary" type="button" onclick="togglePass()" style="border-radius: 0 10px 10px 0; border-left: none; background: #f8f9fa;">
                            <i class="bi bi-eye-slash" id="toggleIcon"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" name="login" class="btn btn-primary w-100 btn-login text-white">
                    MASUK SISTEM <i class="bi bi-arrow-right-short"></i>
                </button>
            </form>

            <div class="footer-text">
                &copy; 2025 Universitas Perguruan Tinggi<br>
                Prodi Sistem Informasi
            </div>
        </div>
    </div>

    <script>
        function togglePass() {
            var input = document.getElementById("passInput");
            var icon = document.getElementById("toggleIcon");
            if (input.type === "password") {
                input.type = "text";
                icon.classList.remove("bi-eye-slash");
                icon.classList.add("bi-eye");
            } else {
                input.type = "password";
                icon.classList.remove("bi-eye");
                icon.classList.add("bi-eye-slash");
            }
        }
    </script>

</body>
</html>