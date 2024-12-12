<?php
session_start();
include 'config.php'; // Menghubungkan ke database

// Jika pengguna sudah login, arahkan ke index.php
if (isset($_SESSION['email'])) {
    header("Location: index.php");
    exit();
}

// Fungsi untuk mengirim email reset password
function sendResetPasswordEmail($email) {
    global $conn;
    $token = bin2hex(random_bytes(16)); // Token 32 karakter
    $expire = date("Y-m-d H:i:s", strtotime('+1 hour')); // Token kadaluarsa 1 jam

    // Simpan token ke database
    $sql = "UPDATE user SET reset_token = ?, reset_token_expire = ? WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $token, $expire, $email);
    $stmt->execute();
    $stmt->close();

    // Siapkan dan kirim email
    $to = $email;
    $subject = "Reset Password";
    $message = "Klik link berikut untuk reset password: http://yourdomain.com/reset_password.php?token=" . $token;
    $headers = "From: no-reply@yourdomain.com\r\n";
    
    return mail($to, $subject, $message, $headers);
}

// Fungsi untuk menangani login
function handleLogin($email, $password) {
    global $conn;
    $sql_user = "SELECT * FROM user WHERE email = ?";
    $stmt = $conn->prepare($sql_user);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user_data = $result->fetch_assoc();

    // Verifikasi password
    if ($user_data && password_verify($password, $user_data['password'])) {
        $_SESSION['email'] = $user_data['email'];
        header("Location: index.php");
        exit();
    } else {
        $_SESSION['message'] = "Email atau password salah!";
        $_SESSION['message_type'] = "error";
    }
    $stmt->close();
}

// Fungsi untuk menangani forgot password
function handleForgotPassword($email) {
    global $conn;
    $sql_check_email = "SELECT * FROM user WHERE email = ?";
    $stmt = $conn->prepare($sql_check_email);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        if (sendResetPasswordEmail($email)) {
            $_SESSION['message'] = "Email reset password telah dikirim.";
            $_SESSION['message_type'] = "success";
        } else {
            $_SESSION['message'] = "Gagal mengirim email.";
            $_SESSION['message_type'] = "error";
        }
    } else {
        $_SESSION['message'] = "Email tidak terdaftar.";
        $_SESSION['message_type'] = "error";
    }
    $stmt->close();
}

// Proses login
if (isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    if (!empty($email) && !empty($password)) {
        handleLogin($email, $password);
    } else {
        $_SESSION['message'] = "Email dan password harus diisi!";
        $_SESSION['message_type'] = "warning";
    }
}

// Proses forgot password
if (isset($_POST['forgot_password'])) {
    $email = trim($_POST['email']);
    if (!empty($email)) {
        handleForgotPassword($email);
    } else {
        $_SESSION['message'] = "Email harus diisi!";
        $_SESSION['message_type'] = "warning";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.ripples/0.5.3/jquery.ripples.min.js"></script>
    <link rel="stylesheet" href="assets/css/login.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
</head>

<body>

    <!-- Jam digital -->
    <div id="clock"></div>

    <!-- Form Login -->
    <div class="login">
        <form action="login.php" method="POST">
            <h1 class="head">Login</h1>
            <label><b>Email</b></label>
            <input type="email" name="email" placeholder="Email" required>

            <label><b>Password</b></label>
            <input type="password" name="password" placeholder="Password" required>

            <input type="submit" name="login" value="Login">
            <div class="regisdulu">
                <p>Belum punya akun? <a href="register.php">Daftar di sini</a></p>
                <p><a href="#" id="forgotPasswordLink">Lupa Password?</a></p>
            </div>
        </form>
    </div>

    <!-- Form Reset Password -->
    <div id="forgotPasswordForm" style="display: none;">
        <h2 class="head">Reset Password</h2>
        <form action="login.php" method="POST">
            <label><b>Email</b></label>
            <input type="email" name="email" placeholder="Email" required>
            <input type="submit" name="forgot_password" value="Kirim Email Reset">
            <p class="back-login"><a href="#" id="backToLoginLink">Kembali ke Login</a></p>
        </form>
    </div>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.ripples/0.5.3/jquery.ripples.min.js"></script>
    <script src="js/jquery-3.2.1.min.js"></script>
    <script src="js/jquery.ripples.js"></script>
    <script>
        // Update jam setiap detik
        function updateClock() {
            const now = new Date();
            const options = {
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
                hour12: false
            };
            document.getElementById('clock').textContent = now.toLocaleTimeString([], options);
        }
        setInterval(updateClock, 1000);
        updateClock();

        // Toggle form reset password
        document.getElementById('forgotPasswordLink').onclick = function() {
            document.querySelector('.login').style.display = 'none';
            document.getElementById('forgotPasswordForm').style.display = 'block';
        };

        document.getElementById('backToLoginLink').onclick = function() {
            document.getElementById('forgotPas  swordForm').style.display = 'none';
            document.querySelector('.login').style.display = 'block';
        };

        // SweetAlert untuk pesan error atau sukses
        <?php if (isset($_SESSION['message'])): ?>
            Swal.fire({
                icon: "<?php echo $_SESSION['message_type']; ?>",
                title: "<?php echo $_SESSION['message_type'] == 'success' ? 'Berhasil' : 'Oops...'; ?>",
                text: "<?php echo $_SESSION['message']; ?>"
            });
            <?php unset($_SESSION['message']); unset($_SESSION['message_type']); ?>
        <?php endif; ?>

        $('body').ripples({
            resolution: 512,
            dropRadius: 20,
            perturbance: 0.05,
        });

    </script>

</body>
</html>
