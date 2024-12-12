<?php
session_start();
include 'config.php'; // Menghubungkan ke database to_do_app

// Handle Logout
if (isset($_POST['logout'])) {
    session_destroy(); // Menghancurkan session
    header("Location: login.php"); // Mengarahkan ke halaman login
    exit();
}

// Ambil email dari session
if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}
$user_email = $_SESSION['email'];

// Query untuk mengambil data pengguna dari tabel user
$sql_user = "SELECT * FROM user WHERE email = ?";
$stmt_user = $conn->prepare($sql_user);
$stmt_user->bind_param("s", $user_email);
$stmt_user->execute();
$user_result = $stmt_user->get_result();
$user_data = $user_result->fetch_assoc();

if (!$user_data) {
    echo "Data pengguna tidak ditemukan!";
    exit();
}

// Handle Create To-Do List
if (isset($_POST['submit'])) {
    $title = trim($_POST['title']);
    $date = $_POST['date'];
    $deadline = $_POST['deadline'];
    $day = date('l', strtotime($date));

    if (!empty($title) && !empty($day) && !empty($date) && !empty($deadline)) {
        $stmt = $conn->prepare("INSERT INTO to_do_lists (title, day, date, deadline, user_email) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $title, $day, $date, $deadline, $user_email);

        if ($stmt->execute()) {
            $_SESSION['message'] = "To-Do List berhasil ditambahkan!";
        } else {
            $_SESSION['message'] = "Error: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $_SESSION['message'] = "Semua kolom harus diisi!";
    }
    header("Location: index_coba.php");
    exit();
}

// Handle Update To-Do List (Edit)
if (isset($_POST['update'])) {
    $id = $_POST['id'];
    $title = trim($_POST['title']);
    $date = $_POST['date'];
    $deadline = $_POST['deadline'];
    $day = date('l', strtotime($date));

    if (!empty($title) && !empty($day) && !empty($date) && !empty($deadline)) {
        $stmt = $conn->prepare("UPDATE to_do_lists SET title = ?, day = ?, date = ?, deadline = ? WHERE id = ? AND user_email = ?");
        $stmt->bind_param("ssssis", $title, $day, $date, $deadline, $id, $user_email);

        if ($stmt->execute()) {
            $_SESSION['message'] = "To-Do List berhasil diperbarui!";
        } else {
            $_SESSION['message'] = "Error: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $_SESSION['message'] = "Semua kolom harus diisi!";
    }
    header("Location: index_coba.php");
    exit();
}

// Handle Delete To-Do List
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM to_do_lists WHERE id = ? AND user_email = ?");
    $stmt->bind_param("is", $id, $user_email);

    if ($stmt->execute()) {
        $_SESSION['message'] = "To-Do List berhasil dihapus!";
    } else {
        $_SESSION['message'] = "Error: " . $stmt->error;
    }
    $stmt->close();
    header("Location: index_coba.php");
    exit();
}

// Handle Search
$search_term = "";
if (isset($_POST['search'])) {
    $search_term = trim($_POST['search_term']);
}

// Ambil semua To-Do List terkait pengguna yang login
$sql = "SELECT * FROM to_do_lists WHERE user_email = ?";
if (!empty($search_term)) {
    $sql .= " AND title LIKE ?";
}
$stmt = $conn->prepare($sql);
if (!empty($search_term)) {
  
    $stmt->bind_param("ss", $user_email, $search_term);
} else {
    $stmt->bind_param("s", $user_email);
}
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>To-Do List</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #e3f2fd;
            color: #333;
            padding: 20px;
        }

        h1 {
            text-align: center;
            color: #0d47a1;
            font-size: 36px;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-bottom: 30px;
        }

        form {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-bottom: 20px;
        }

        input[type="text"],
        input[type="date"],
        input[type="time"] {
            padding: 12px;
            width: 300px;
            margin: 10px 0;
            border: 2px solid #90caf9;
            border-radius: 8px;
            font-size: 16px;
            transition: all 0.3s ease;
        }

        input[type="text"]:focus,
        input[type="date"]:focus,
        input[type="time"]:focus {
            border-color: #1e88e5;
            box-shadow: 0 0 5px rgba(30, 136, 229, 0.5);
        }

        input[type="submit"] {
            padding: 12px 25px;
            background-color: #1e88e5;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 18px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        input[type="submit"]:hover {
            background-color: #1565c0;
        }

        ul {
            list-style-type: none;
            padding: 0;
        }

        ul li {
            background-color: #ffffff;
            margin: 10px 0;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: box-shadow 0.3s ease;
        }

        ul li:hover {
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
        }

        ul li a {
            text-decoration: none;
            color: #e53935;
            font-weight: bold;
            transition: color 0.3s ease;
        }

        ul li a:hover {
            color: #b71c1c;
        }

        .message {
            text-align: center;
            color: #0d47a1;
            background-color: #bbdefb;
            border: 1px solid #0d47a1;
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 8px;
            font-size: 18px;
        }

        @media screen and (max-width: 768px) {
            input[type="text"],
            input[type="date"],
            input[type="time"] {
                width: 100%;
            }
        }

        .profile-menu {
            position: absolute;
            top: 20px;
            right: 20px;
            font-size: 16px;
        }

        .profile-button {
            background-color: #1e88e5;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .profile-button .arrow-down {
            margin-left: 10px;
            border: solid white;
            border-width: 0 2px 2px 0;
            padding: 3px;
            transform: rotate(45deg);
        }

        .profile-button:hover {
            background-color: #1565c0;
        }

        .dropdown-content {
            display: none;
            position: absolute;
            right: 0;
            background-color: white;
            min-width: 160px;
            box-shadow: 0px 8px 16px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            z-index 1;
            text-align: left;
        }

        .dropdown-content a,
        .dropdown-content form {
            color: #333;
            padding: 12px 16px;
            text-decoration: none;
            display: block;
        }

        .dropdown-content a:hover {
            background-color: #f1f1f1;
        }

        .profile-menu:hover .dropdown-content {
            display: block;
        }
    </style>
</head>

<body>
    <h1>To-Do List</h1>
    <div class="profile-menu">
        <button onclick="toggleDropdown()" class="profile-button">
            <?php echo htmlspecialchars($user_data['username']); ?>
            <i class="arrow-down"></i>
        </button>
        <div id="dropdown-menu" class="dropdown-content">
            <a href="view_profile.php">View Profile</a>
            <a href="profile.php">Edit Profile</a>
            <form action="index_coba.php" method="POST" style="display: inline;">
                <input type="submit" name="logout" value="Logout" class="logout-btn">
            </form>
        </div>
    </div>

    <?php if (isset($_SESSION['message'])): ?>
        <div class="message">
            <?php
            echo $_SESSION['message'];
            unset($_SESSION['message']);
            ?>
        </div>
    <?php endif; ?>

    <form action="index_coba.php" method="POST">
        <input type="text" name="title" placeholder="Judul Tugas" required>
        <input type="date" name="date" required>
        <input type="time" name="deadline" required>
        <input type="submit" name="submit" value="Tambah To-Do List">
    </form>

    <!-- Search Form -->
    <form action="index_coba.php" method="POST" style="text-align: center; margin: 20px 0;">
        <input type="text" name="search_term" placeholder="Cari To-Do List" value="<?php echo htmlspecialchars($search_term); ?>">
        <input type="submit" name="search" value="Cari">
    </form>

    <h2>Daftar To-Do Lists</h2>
    <ul>
        <?php
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                if (isset($_GET['edit']) && $_GET['edit'] == $row['id']) {
                    // Form Edit untuk To-Do List yang sedang diedit
                    echo "<form action='index_coba.php' method='POST'>
                            <input type='hidden' name='id' value='" . $row['id'] . "'>
                            <input type='text' name='title' value='" . htmlspecialchars($row['title']) . "' required>
                            <input type='date' name='date' value='" . htmlspecialchars($row['date']) . "' required>
                            <input type='time' name='deadline' value='" . htmlspecialchars($row['deadline']) . "' required>
                            <input type='submit' name='update' value='Update' style='background-color: blue;'>
                          </form>";
                } else {
                    // Tampilkan To-Do List biasa
                    echo "<li><strong>" . htmlspecialchars($row['title']) . "</strong> ("
                        . htmlspecialchars($row['day']) . ", "
                        . htmlspecialchars($row['date']) . " - "
                        . htmlspecialchars($row['deadline']) . ")
                         <a href='index_coba.php?edit=" . $row['id'] . "' style='color: #1e88e5;'>Edit</a>
                         <a href='index_coba.php?delete=" . $row['id'] . "' 
                         onclick=\"return confirm('Yakin ingin menghapus?');\" style='color: #e53935;'>Hapus</a></li>";
                }
            }
        } else {
            echo "<li>Belum ada To-Do List.</li>";
        }
        ?>
    </ul>
</body>

</html>

<?php
$conn->close();
?>
