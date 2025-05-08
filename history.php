<?php
session_start();
include 'db_connect.php';

// Redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Handle delete request
if (isset($_GET['delete']) && !empty($_GET['delete'])) {
    $checklist_id = $_GET['delete'];

    // Check if user is admin or the owner of the checklist
    $stmt = $conn->prepare("SELECT user_id FROM checklists WHERE id = ?");
    $stmt->bind_param("i", $checklist_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $checklist = $result->fetch_assoc();

        // Allow delete if user is admin (id=1) or the owner
        if ($_SESSION['user_id'] == 1 || $_SESSION['user_id'] == $checklist['user_id']) {
            // Delete checklist
            $stmt = $conn->prepare("DELETE FROM checklists WHERE id = ?");
            $stmt->bind_param("i", $checklist_id);

            if ($stmt->execute()) {
                $success_message = "Checklist berhasil dihapus.";
            } else {
                $error_message = "Gagal menghapus checklist: " . $conn->error;
            }
        } else {
            $error_message = "Anda tidak memiliki izin untuk menghapus checklist ini.";
        }
    } else {
        $error_message = "Checklist tidak ditemukan.";
    }
}

// Get all checklists (not just for the current user)
$sql = "SELECT c.*, u.username FROM checklists c JOIN users u ON c.user_id = u.id ORDER BY c.date DESC";
$result = $conn->query($sql);
$checklists = $result->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Checklist - GKPI Griya Permata</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
    <link rel="icon" href="public/images/church-logo.png" type="image/png">
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
                        <a class="nav-link" href="checklist.php">
                            <i class="bi bi-clipboard-check"></i> Checklist Baru
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="history.php">
                            <i class="bi bi-clock-history"></i> Riwayat
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="chat.php">
                            <i class="bi bi-chat-dots"></i> Diskusi
                        </a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="profile.php">
                            <i class="bi bi-person-circle"></i> <?php echo $_SESSION['username']; ?>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link bg-danger rounded" href="logout.php">
                            <i class="bi bi-box-arrow-right"></i> Keluar
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <?php if (isset($success_message)): ?>
            <div class="alert alert-success">
                <i class="bi bi-check-circle-fill"></i> <?php echo $success_message; ?>
            </div>
        <?php endif; ?>

        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger">
                <i class="bi bi-exclamation-triangle-fill"></i> <?php echo $error_message; ?>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header">
                <i class="bi bi-clock-history"></i> Riwayat Checklist Persiapan Ibadah
            </div>
            <div class="card-body">
                <?php if (count($checklists) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Judul</th>
                                    <th>Dibuat Oleh</th>
                                    <th>Tanggal Ibadah</th>
                                    <th>Dibuat Pada</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $i = 1;
                                foreach ($checklists as $checklist):
                                    $status_class = $checklist['status'] == 'completed' ? 'status-completed' : 'status-draft';
                                    $status_text = $checklist['status'] == 'completed' ? 'Selesai' : 'Draft';
                                    $can_edit_delete = ($_SESSION['user_id'] == 1 || $_SESSION['user_id'] == $checklist['user_id']);
                                ?>
                                    <tr>
                                        <td><?php echo $i++; ?></td>
                                        <td><?php echo htmlspecialchars($checklist['title']); ?></td>
                                        <td><?php echo htmlspecialchars($checklist['username']); ?></td>
                                        <td><?php echo date('d M Y', strtotime($checklist['date'])); ?></td>
                                        <td><?php echo date('d M Y H:i', strtotime($checklist['created_at'])); ?></td>
                                        <td>
                                            <div class="btn-group">
                                                <a href="view_checklist.php?id=<?php echo $checklist['id']; ?>" class="btn btn-sm btn-primary">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                <?php if ($can_edit_delete): ?>
                                                    <a href="edit_checklist.php?id=<?php echo $checklist['id']; ?>" class="btn btn-sm btn-warning">
                                                        <i class="bi bi-pencil"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal<?php echo $checklist['id']; ?>">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                <?php endif; ?>
                                            </div>

                                            <!-- Delete Confirmation Modal -->
                                            <div class="modal fade" id="deleteModal<?php echo $checklist['id']; ?>" tabindex="-1" aria-labelledby="deleteModalLabel<?php echo $checklist['id']; ?>" aria-hidden="true">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title" id="deleteModalLabel<?php echo $checklist['id']; ?>">Konfirmasi Hapus</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            Apakah Anda yakin ingin menghapus checklist "<strong><?php echo htmlspecialchars($checklist['title']); ?></strong>"?
                                                            <br><br>
                                                            <div class="alert alert-warning">
                                                                <i class="bi bi-exclamation-triangle-fill"></i> Tindakan ini tidak dapat dibatalkan.
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                            <a href="history.php?delete=<?php echo $checklist['id']; ?>" class="btn btn-danger">Hapus</a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i> Belum ada checklist yang dibuat. <a href="checklist.php" class="alert-link">Buat checklist baru</a>.
                    </div>
                <?php endif; ?>
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