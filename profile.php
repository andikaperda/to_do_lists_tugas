<?php
session_start();
include 'config.php'; // Menghubungkan ke database to_do_app

if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

// Ambil email pengguna dari session
$user_email = $_SESSION['email'];

// Query untuk mengambil data pengguna dari tabel user
$sql_user = "SELECT * FROM user WHERE email = ?";
$stmt_user = $conn->prepare($sql_user);
$stmt_user->bind_param("s", $user_email);
$stmt_user->execute();
$user_result = $stmt_user->get_result();
$user_data = $user_result->fetch_assoc();

// Handle Update Profil Pengguna
if (isset($_POST['update_profile'])) {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (!empty($username)) {
        if (!empty($password)) {
            // Validasi password yang baik: minimal 8 karakter
            if (strlen($password) < 8) {
                $_SESSION['message'] = "Password harus minimal 8 karakter!";
                header("Location: profile.php");
                exit();
            }
            // Jika password diisi, update username dan password
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $conn->prepare("UPDATE user SET username = ?, password = ? WHERE email = ?");
            $stmt->bind_param("sss", $username, $hashed_password, $user_email);
        } else {
            // Jika password kosong, hanya update username
            $stmt = $conn->prepare("UPDATE user SET username = ? WHERE email = ?");
            $stmt->bind_param("ss", $username, $user_email);
        }

        if ($stmt->execute()) {
            $_SESSION['message'] = "Profil berhasil diperbarui!";
        } else {
            $_SESSION['message'] = "Error: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $_SESSION['message'] = "Username tidak boleh kosong!";
    }
    header("Location: profile.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profil Pengguna</title>
    <style>
        /* Styling untuk seluruh halaman */
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f0f4f8;
            color: #333;
            padding: 20px;
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            box-sizing: border-box;
        }

        h1 {
            text-align: center;
            color: #1e88e5;
            margin-bottom: 20px;
            font-size: 28px;
            font-weight: bold;
        }

        form {
            background-color: #ffffff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
            align-items: stretch;
        }

        label {
            font-size: 16px;
            color: #1e88e5;
            margin-bottom: 8px;
        }

        input[type="text"], input[type="password"], input[type="email"] {
            padding: 14px;
            width: 100%;
            margin-bottom: 20px;
            border: 2px solid #90caf9;
            border-radius: 8px;
            font-size: 16px;
            background-color: #f0f4f8;
            box-sizing: border-box;
        }

        input[type="text"]:focus, input[type="password"]:focus, input[type="email"]:focus {
            border-color: #42a5f5;
            background-color: #e3f2fd;
        }

        input[type="submit"] {
            padding: 14px 30px;
            background-color: #1e88e5;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 18px;
            cursor: pointer;
            transition: background-color 0.3s ease, box-shadow 0.3s ease;
        }

        input[type="submit"]:hover {
            background-color: #1565c0;
            box-shadow: 0 6px 12px rgba(21, 101, 192, 0.3);
        }

        .message {
            text-align: center;
            color: #1e88e5;
            background-color: #e3f2fd;
            border: 1px solid #1e88e5;
            padding: 12px;
            margin-bottom: 20px;
            border-radius: 8px;
            font-size: 18px;
            width: 100%;
            box-sizing: border-box;
        }

        .back-btn {
            padding: 14px 30px;
            background-color: #ff6f61;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 18px;
            cursor: pointer;
            text-align: center;
            margin-top: 20px;
            text-decoration: none;
        }

        .back-btn:hover {
            background-color: #e53935;
            box-shadow: 0 6px 12px rgba(229, 57, 53, 0.3);
        }

        @media (max-width: 600px) {
            form {
                padding: 20px;
            }

            input[type="text"], input[type="password"], input[type="email"] {
                font-size: 14px;
            }

            input[type="submit"], .back-btn {
                font-size: 16px;
                padding: 12px 20px;
            }
        }
    </style>
</head>
<body>
   

    <?php if (isset($_SESSION['message'])): ?>
        <div class="message">
            <?php
            echo $_SESSION['message'];
            unset($_SESSION['message']);
            ?>
        </div>
    <?php endif; ?>

    <form action="profile.php" method="POST">
    <h1>Edit Profil</h1>
        <label>Username:</label>
        <input type="text" name="username" value="<?php echo isset($user_data['username']) ? htmlspecialchars($user_data['username']) : ''; ?>" required>
        
        <label>Email:</label>
        <input type="email" name="email" value="<?php echo isset($user_data['email']) ? htmlspecialchars($user_data['email']) : ''; ?>" readonly>
        
        <label>Password:</label>
        <input type="password" name="password" placeholder="Kosongkan jika tidak ingin mengubah">
        
        <input type="submit" name="update_profile" value="Update Profil">
        <a href="index.php" class="back-btn">Kembali ke Halaman Utama</a>
    </form>
    
    
</body>
</html>

<?php
$conn->close();
?>
