<?php
session_start();
include 'config.php';

$registration_error = null;
$registration_success = false;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Validasi dasar
    if (strlen($username) < 3) {
        $registration_error = "Username harus minimal 3 karakter.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $registration_error = "Email tidak valid.";
    } elseif (strlen($password) < 8) {
        $registration_error = "Password harus minimal 8 karakter.";
    } else {
        // Check if the email already exists in the database
        $stmt = $conn->prepare("SELECT * FROM user WHERE email = ?");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $registration_error = "Email sudah terdaftar.";
        } else {
            // Hash the password before storing it
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Insert new user into the 'user' table
            $stmt = $conn->prepare("INSERT INTO user (username, email, password) VALUES (?, ?, ?)");
            $stmt->bind_param('sss', $username, $email, $hashed_password);

            if ($stmt->execute()) {
                $registration_success = true;
                $_SESSION['success_message'] = "Akun Anda sudah terdaftar, silakan login.";
            } else {
                $registration_error = "Terjadi kesalahan, silakan coba lagi.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrasi</title>
    <link rel="stylesheet" href="assets/css/registrasi.css">
    <link rel="stylesheet" href="assets/css/login.css">



</head>
<body>

    <div class="register-container">
        <h2>Registrasi</h2>

        <!-- Error message
        <?php if ($registration_error): ?>
            <div class="error">
                <?php echo htmlspecialchars($registration_error); ?>
            </div>
        <?php endif; ?> -->

        <!-- Registration form -->
        <form class="register" method="POST" action="register.php">
            <input type="text" name="username" placeholder="Username" required><br>
            <input type="email" name="email" placeholder="Email" required><br>
            <input type="password" name="password" placeholder="Password" required><br>
            <button type="submit">Sign Up</button>
        </form>

        <p class="have-akun">Sudah punya akun? <a href="login.php">Login di sini</a></p>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        <?php if ($registration_success): ?>
            Swal.fire({
                position: "center",
                icon: "success",
                title: "Registrasi berhasil!",
                text: "Akun Anda telah terdaftar. Silakan login.",
                showConfirmButton: false,
                timer: 2000
            }).then(() => {
                window.location.href = "login.php"; // Redirect setelah sukses
            });
        <?php elseif ($registration_error): ?>
            Swal.fire({
                position: "center",
                icon: "error",
                title: "Oops...",
                text: "<?php echo $registration_error; ?>"
            });
        <?php endif; ?>
    </script>


</body>
</html>
