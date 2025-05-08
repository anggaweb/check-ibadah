<?php
session_start();
include 'db_connect.php';

// Check if user is admin (assuming admin has user_id = 1)
if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] != 1) {
    header("Location: index.php");
    exit;
}

$success = '';
$error = '';

// Get statistics
$stats = [
    'total_checklists' => 0,
    'completed_checklists' => 0,
    'total_users' => 0,
    'total_comments' => 0
];

// Total checklists
$result = $conn->query("SELECT COUNT(*) as count FROM checklists");
if ($result) {
    $stats['total_checklists'] = $result->fetch_assoc()['count'];
}

// Completed checklists
$result = $conn->query("SELECT COUNT(*) as count FROM checklists WHERE status = 'completed'");
if ($result) {
    $stats['completed_checklists'] = $result->fetch_assoc()['count'];
}

// Total users
$result = $conn->query("SELECT COUNT(*) as count FROM users");
if ($result) {
    $stats['total_users'] = $result->fetch_assoc()['count'];
}

// Total comments
$result = $conn->query("SELECT COUNT(*) as count FROM comments");
if ($result) {
    $stats['total_comments'] = $result->fetch_assoc()['count'];
}

// Get recent checklists
$recent_checklists = [];
$result = $conn->query("SELECT c.*, u.username FROM checklists c JOIN users u ON c.user_id = u.id ORDER BY c.created_at DESC LIMIT 5");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $recent_checklists[] = $row;
    }
}

// Get recent users
$recent_users = [];
$result = $conn->query("SELECT * FROM users ORDER BY created_at DESC LIMIT 5");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $recent_users[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - GKPI Griya Permata</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
    <link rel="icon" href="public/images/church-logo.png" type="image/png">
    <style>
        .stat-card {
            transition: transform 0.3s;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-icon {
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
        }

        .admin-menu-card {
            transition: transform 0.3s;
            cursor: pointer;
        }

        .admin-menu-card:hover {
            transform: translateY(-5px);
        }
    </style>
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <img src="public/images/church-logo.png" alt="GKPI Logo">
                GKPI GP
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">
                            <i class="bi bi-house-door"></i> Beranda
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="admin.php">
                            <i class="bi bi-speedometer2"></i> Admin
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="admin_checklist_items.php">
                            <i class="bi bi-list-check"></i> Kelola Item Checklist
                        </a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="profile.php">
                            <i class="bi bi-person-circle"></i> <?php echo $_SESSION['username']; ?> (Admin)
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">
                            <i class="bi bi-box-arrow-right"></i> Keluar
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <?php if ($success): ?>
            <div class="alert alert-success">
                <i class="bi bi-check-circle-fill"></i> <?php echo $success; ?>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-danger">
                <i class="bi bi-exclamation-triangle-fill"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="bi bi-speedometer2"></i> Admin Dashboard</h2>
            <span class="badge bg-primary">Admin Area</span>
        </div>

        <!-- Statistics -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card stat-card text-center h-100">
                    <div class="card-body">
                        <div class="stat-icon text-primary">
                            <i class="bi bi-clipboard-check"></i>
                        </div>
                        <h3><?php echo $stats['total_checklists']; ?></h3>
                        <p class="text-muted mb-0">Total Checklist</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card text-center h-100">
                    <div class="card-body">
                        <div class="stat-icon text-success">
                            <i class="bi bi-check-circle"></i>
                        </div>
                        <h3><?php echo $stats['completed_checklists']; ?></h3>
                        <p class="text-muted mb-0">Checklist Selesai</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card text-center h-100">
                    <div class="card-body">
                        <div class="stat-icon text-info">
                            <i class="bi bi-people"></i>
                        </div>
                        <h3><?php echo $stats['total_users']; ?></h3>
                        <p class="text-muted mb-0">Total Pengguna</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card text-center h-100">
                    <div class="card-body">
                        <div class="stat-icon text-warning">
                            <i class="bi bi-chat-dots"></i>
                        </div>
                        <h3><?php echo $stats['total_comments']; ?></h3>
                        <p class="text-muted mb-0">Total Komentar</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Admin Menu -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <i class="bi bi-grid"></i> Menu Admin
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <a href="admin_checklist_items.php" class="text-decoration-none">
                                    <div class="card admin-menu-card h-100">
                                        <div class="card-body text-center">
                                            <i class="bi bi-list-check fs-1 text-primary mb-3"></i>
                                            <h5>Kelola Item Checklist</h5>
                                            <p class="text-muted">Tambah, edit, atau hapus item checklist</p>
                                        </div>
                                    </div>
                                </a>
                            </div>
                            <div class="col-md-4 mb-3">
                                <a href="history.php" class="text-decoration-none">
                                    <div class="card admin-menu-card h-100">
                                        <div class="card-body text-center">
                                            <i class="bi bi-clock-history fs-1 text-success mb-3"></i>
                                            <h5>Riwayat Checklist</h5>
                                            <p class="text-muted">Lihat dan kelola semua checklist</p>
                                        </div>
                                    </div>
                                </a>
                            </div>
                            <div class="col-md-4 mb-3">
                                <a href="chat.php" class="text-decoration-none">
                                    <div class="card admin-menu-card h-100">
                                        <div class="card-body text-center">
                                            <i class="bi bi-chat-dots fs-1 text-info mb-3"></i>
                                            <h5>Diskusi</h5>
                                            <p class="text-muted">Lihat dan kelola diskusi</p>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Checklists -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <i class="bi bi-clipboard-data"></i> Checklist Terbaru
                    </div>
                    <div class="card-body">
                        <?php if (count($recent_checklists) > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Judul</th>
                                            <th>Dibuat Oleh</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recent_checklists as $checklist): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($checklist['title']); ?></td>
                                                <td><?php echo htmlspecialchars($checklist['username']); ?></td>
                                                <td>
                                                    <a href="view_checklist.php?id=<?php echo $checklist['id']; ?>" class="btn btn-sm btn-primary">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <div class="text-end">
                                <a href="history.php" class="btn btn-sm btn-outline-primary">Lihat Semua</a>
                            </div>
                        <?php else: ?>
                            <div class="text-center text-muted py-3">
                                <i class="bi bi-clipboard-x fs-1 mb-3"></i>
                                <p>Belum ada checklist yang dibuat.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Recent Users -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <i class="bi bi-people"></i> Pengguna Terbaru
                    </div>
                    <div class="card-body">
                        <?php if (count($recent_users) > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Username</th>
                                            <th>Email</th>
                                            <th>Terdaftar Pada</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recent_users as $user): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($user['username']); ?></td>
                                                <td><?php echo htmlspecialchars($user['email'] ?? 'N/A'); ?></td>
                                                <td><?php echo date('d M Y', strtotime($user['created_at'])); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="text-center text-muted py-3">
                                <i class="bi bi-person-x fs-1 mb-3"></i>
                                <p>Belum ada pengguna yang terdaftar.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer class="footer mt-5">
        <div class="container">
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> GKPI Griya Permata. All Rights Reserved.</p>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>