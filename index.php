<?php
session_start();
include 'config.php';

if (isset($_POST['logout'])) {
    session_destroy();
    header("Location: login.php");
    exit();
}

if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}
$user_email = $_SESSION['email'];

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

// Handle search
$search_term = "";
$sql = "SELECT * FROM to_do_lists WHERE user_email = ?";
$params = [$user_email];

if (isset($_POST['search'])) {
    $search_term = trim($_POST['search_term']);
    $sql .= " AND title LIKE ?"; 
    $params[] = "%" . $search_term . "%"; 
}

$stmt = $conn->prepare($sql);
$stmt->bind_param(str_repeat("s", count($params)), ...$params);
$stmt->execute();
$result = $stmt->get_result();

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
    header("Location: index.php");
    exit();
}

$sorting = isset($_POST['sorting']) ? $_POST['sorting'] : 'all'; 

if ($sorting == 'completed') {
    $sql .= " AND is_completed = 1"; 
} elseif ($sorting == 'not_completed') {
    $sql .= " AND is_completed = 0"; 
}

if (isset($_POST['update'])) {
    $id = $_POST['id'];
    $title = trim($_POST['title']);
    $date = $_POST['date'];
    $deadline = $_POST['deadline'];
    $is_completed = isset($_POST['is_completed']) ? 1 : 0;

    if (!empty($title) && !empty($date) && !empty($deadline)) {
        $stmt = $conn->prepare("UPDATE to_do_lists SET title = ?, date = ?, deadline = ?, is_completed = ? WHERE id = ? AND user_email = ?");
        $stmt->bind_param("sssisi", $title, $date, $deadline, $is_completed, $id, $user_email);

        if ($stmt->execute()) {
            $_SESSION['message'] = "To-Do List berhasil diperbarui!";
        } else {
            $_SESSION['message'] = "Error: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $_SESSION['message'] = "Semua kolom harus diisi!";
    }
    header("Location: index.php");
    exit();
}

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
    header("Location: index.php");
    exit();
}

    if (isset($_POST['updateProfile'])) {
        $new_username = trim($_POST['username']);
        $new_password = $_POST['password'];
        $profile_image = $_FILES['profileImage'];

    if (!empty($new_username)) {
        $stmt = $conn->prepare("UPDATE user SET username = ? WHERE email = ?");
        $stmt->bind_param("ss", $new_username, $user_email);
        $stmt->execute();
        $_SESSION['message'] = "Username berhasil diperbarui!";
    }

    if (!empty($new_password)) {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE user SET password = ? WHERE email = ?");
        $stmt->bind_param("ss", $hashed_password, $user_email);
        $stmt->execute();
        $_SESSION['message'] = "Password berhasil diperbarui!";
    }

    if ($profile_image['size'] > 0) {
        $target_dir = "uploads/";
        $target_file = $target_dir . basename($profile_image['name']);
        move_uploaded_file($profile_image['tmp_name'], $target_file);

        $stmt = $conn->prepare("UPDATE user SET profile_image = ? WHERE email = ?");
        $stmt->bind_param("ss", $target_file, $user_email);
        $stmt->execute();
        $_SESSION['message'] = "Foto profil berhasil diperbarui!";
    }

    header("Location: index.php");
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>To-Do List Overview</title>
    <link rel="stylesheet" href="assets/css/index.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
</head>

<body>
    <div class="navbar">
        <div class="navbar-brand">
            <a href="#">Penjadwalan Tugas</a>
        </div>
        <div class="dropdown">
            <a href="#" class="username-dropdown">
                <?php echo htmlspecialchars($user_data['username']); ?>
            </a>
            <div class="dropdown-content">
                <a href="#" id="viewProfile">View Profile</a>
                <form method="POST">
                    <button type="submit" name="logout">Logout</button>
                </form>
            </div>
        </div>
    </div>

    <div class="container">
         <div class="sidebar">  
            
                <img src="<?php echo !empty($user_data['profile_image']) ? htmlspecialchars($user_data['profile_image']) : 'default-profile.jpg'; ?>" alt="Profile Image">
            
            <div class="user-name">
                <h3><?php echo htmlspecialchars($user_data['username']); ?></h3>
                <p><?php echo htmlspecialchars($user_data['email']); ?></p>
            </div>
        </div>

        <div class="content">
            <div class="tabs">
                <button onclick="showTab('list')">List Tugas</button>
                <button onclick="showTab('add')">Tambah Tugas</button>
            </div>

            
            <div id="list" class="tab-content active">
                <div id="search" class="container-fluid">
                    <form class="auto" method="POST" action="index.php">
                        <input class="form-control me-2" type="search" name="search_term" aria-label="Search" placeholder="Cari" value="<?php echo htmlspecialchars($search_term); ?>">
                        <button class="btn btn-outline-success" type="submit" name="search">Cari</button>
                    </form>


                    <div class="select-wrapper">
                        <i class="fa-regular fa-clock select-icon" onclick="toggleDropdown()"></i>
                        
                        <ul class="custom-dropdown" id="customDropdown">
                            <li onclick="selectOption('all')">All</li>
                            <li onclick="selectOption('overdue')">Overdue</li>
                            <li onclick="selectOption('due_date')">Due date</li>
                            <li onclick="selectOption('next_7_weeks')">Next 7 weeks</li>
                            <li onclick="selectOption('completed')">Complate</li>
                            <li onclick="selectOption('not_completed')">Not Complated</li>
                        </ul>
                        
                        <select name="" id="sorting" onchange="sortTasks(this.value)">
                            <option value="all">All</option>
                            <option value="overdue">Overdue</option>
                            <option value="due_date">Due date</option>
                            <option value="next_7_weeks">Next 7 weeks</option>
                            <option value="completed">Completed</option> 
                            <option value="not_completed">Not Completed</option>
                        </select>
                    </div>
                </div>

                <h2>Daftar Tugas</h2>
                <hr>
                    <table class="todo-table">
                        <thead>
                            <tr>
                                <th>Judul Tugas</th>
                                <th>Deadline</th>
                                <th>Aksi</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result->num_rows > 0): ?>
                                <?php while ($row = $result->fetch_assoc()):
                                    $dueDate = strtotime($row['date']);
                                    $now = time();
                                    $overdue = ($dueDate < $now);
                                    $isCompleted = $row['is_completed'];
            
                                    $bgColor = '';
                                    if ($isCompleted) {
                                        $bgColor = 'style="background-color: #cfefcf;"'; 
                                    } elseif ($overdue) {
                                        $bgColor = 'style="background-color: #efcfcf;"';
                                    }
                                ?>
                                    <tr class="task-item" data-date="<?php echo htmlspecialchars($row['date']); ?>" data-overdue="<?php echo $overdue ? '1' : '0'; ?>" <?php echo $bgColor; ?>>
                                        <?php if (isset($_GET['edit']) && $_GET['edit'] == $row['id']): ?>
                                            <!-- Form Edit untuk To-Do List yang sedang diedit -->
                                            <form action="index.php" method="POST">
                                            
                                                <td>
                                                    <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                                    <input type="text" name="title" value="<?php echo htmlspecialchars($row['title']); ?>" required>
                                                </td>
                                                
                                                <td>
                                                    <input type="date" name="date" value="<?php echo htmlspecialchars($row['date']); ?>" required>
                                                    <input type="time" name="deadline" value="<?php echo htmlspecialchars($row['deadline']); ?>" required>
                                                </td>
                                                <td>
                                                    <input type="submit" name="update" value="Update" style="background-color: blue;">
                                                </td>
                                                <td>
                                                    <label>
                                                        <input type="checkbox" name="is_completed" <?php echo $row['is_completed'] ? 'checked' : ''; ?>> Selesai
                                                    </label>
                                                </td>
                                                
                                            </form>
                                        <?php else: ?>
                                            <td><strong><?php echo htmlspecialchars($row['title']); ?></strong></td>
                                            <td><?php echo htmlspecialchars($row['day']) . ", " . htmlspecialchars($row['date']) . " - " . htmlspecialchars($row['deadline']); ?></td>
                                            <td>
                                                <button id="edit" type="button" onclick="openEditForm('<?php echo $row['id']; ?>', '<?php echo htmlspecialchars($row['title']); ?>', '<?php echo htmlspecialchars($row['date']); ?>', '<?php echo htmlspecialchars($row['deadline']); ?>', '<?php echo $row['is_completed']; ?>')">✎ Edit</button>
                                                <button id="delete" type="button" onclick="confirmDelete('<?php echo $row['id']; ?>')">🗑 Delete</button>
                                            </td>
                                            <td>
                                                <label>
                                                    <input type="checkbox" <?php echo $row['is_completed'] ? 'checked' : ''; ?> disabled> 
                                                </label>
                                            </td>
                                        <?php endif; ?>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4">Tidak ada hasil pencarian yang ditemukan.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
            </div>

            <div id="add" class="tab-content">
                <h2>Tambah Tugas</h2>
                <form method="POST" action="index.php">
                    <input type="text" name="title" placeholder="Judul Tugas" required>
                    <input type="date" name="date" required>
                    <input type="time" name="deadline" required>
                    <button type="submit" name="submit">Tambah</button>
                </form>
            </div>
        </div>

        <div id="editModal" class="modal">
            <div class="modal-content">
                <span class="close">&times;</span>
                <h2>Edit Tugas</h2>
                <form id="editForm" method="POST" action="index.php">
                    <input type="hidden" name="id" id="editId">
                    <label for="title">Judul Tugas</label>
                    <input type="text" name="title" id="editTitle" required>
                    <label for="date">Tanggal</label>
                    <input type="date" name="date" id="editDate" required>
                    <label for="deadline">Waktu Deadline</label>
                    <input type="time" name="deadline" id="editDeadline" required>
                    <label for="status" name="status">Status </br>
                        <input type="checkbox" name="is_completed" id="editIsCompleted"> Selesai
                    </label>
                    <input type="submit" name="update" value="Update" style="background-color: blue;">
                </form>
            </div>
        </div>
    </div>

<div id="profileModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2>View Profile</h2>
        <form id="profileForm" method="POST" enctype="multipart/form-data">
            <label for="username">Username:</label>
            <input type="text" name="username" value="<?php echo htmlspecialchars($user_data['username']); ?>" required>
            
            <label for="email">Email:</label>
            <input type="email" name="email" value="<?php echo htmlspecialchars($user_data['email']); ?>" readonly>
            
            <label for="password">Password Baru:</label>
            <input type="password" name="password" placeholder="Password Baru">
        
            <label for="profileImage">Upload Profile Image:</label>
            <input type="file" name="profileImage" accept="image/*">
            
            <input type="submit" name="updateProfile" value="Update Profile" class="btn-update">
        </form>
    </div>
</div>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.ripples/0.5.3/jquery.ripples.min.js"></script>
    <script src="js/jquery-3.2.1.min.js"></script>
    <script src="js/jquery.ripples.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // function 1
        function showTab(tabName) {
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.style.display = 'none';
            });
            document.getElementById(tabName).style.display = 'block';
        }

        function openEditForm(id) {
            window.location.href = `index.php?edit=${id}`;
        }

    
        // function 2
        function sortTasks(criteria) {
            const tasks = document.querySelectorAll('.task-item');
            tasks.forEach(task => {
                const overdue = task.dataset.overdue === '1';
                const isCompleted = task.querySelector('input[type="checkbox"]').checked;

                if (criteria === 'all') {
                    task.style.display = 'table-row';
                } else if (criteria === 'overdue' && overdue) {
                    task.style.display = 'table-row';
                } else if (criteria === 'completed' && isCompleted) {
                    task.style.display = 'table-row';
                } else if (criteria === 'not_completed' && !isCompleted) {
                    task.style.display = 'table-row';
                } else {
                    task.style.display = 'none';
                }
            });
        }


        const modal = document.getElementById("editModal");
        const span = document.getElementsByClassName("close")[0];
        const editForm = document.getElementById("editForm");

        span.onclick = function() {
            modal.style.display = "none";
        }

        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }

        function openEditForm(id, title, date, deadline, isCompleted) {
            document.getElementById('editId').value = id;
            document.getElementById('editTitle').value = title;
            document.getElementById('editDate').value = date;
            document.getElementById('editDeadline').value = deadline;
            document.getElementById('editIsCompleted').checked = isCompleted === '1';

            modal.style.display = "block";
        }

        function confirmDelete(taskId) {
            Swal.fire({
                title: "Are you sure?",
                text: "You won't be able to revert this!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Yes, delete it!"
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = `index.php?delete=${taskId}`;
                }
            });
        }

        function toggleDropdown() {
            const dropdown = document.getElementById('customDropdown');
            dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
        }
        function selectOption(value) {
            const select = document.getElementById('sorting');
            select.value = value;
            sortTasks(value);
            toggleDropdown();
        }

        window.onclick = function(event) {
            if (!event.target.matches('.select-icon')) {
                const dropdowns = document.getElementsByClassName("custom-dropdown");
                for (let i = 0; i < dropdowns.length; i++) {
                    const openDropdown = dropdowns[i];
                    if (openDropdown.style.display === "block") {
                        openDropdown.style.display = "none";
                    }
                }
            }
        }

        const profileModal = document.getElementById("profileModal");
        const profileClose = profileModal.getElementsByClassName("close")[0];

        document.getElementById("viewProfile").onclick = function() {
            profileModal.style.display = "block";
        }

        profileClose.onclick = function() {
            profileModal.style.display = "none";
        }

        window.onclick = function(event) {
            if (event.target == profileModal) {
                profileModal.style.display = "none";
            }
        }

        $('body').ripples({
            resolution: 512,
            dropRadius: 20,
            perturbance: 0.05,
        });
    </script>
</body>

</html>
