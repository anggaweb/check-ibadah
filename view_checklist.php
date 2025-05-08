<?php
session_start();
include 'db_connect.php';

// Redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Check if checklist ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: history.php");
    exit;
}

$checklist_id = $_GET['id'];
$checklist_id = $conn->real_escape_string($checklist_id);

// Get checklist details
$sql = "SELECT c.*, u.username FROM checklists c JOIN users u ON c.user_id = u.id WHERE c.id = '$checklist_id'";
$result = $conn->query($sql);

if ($result->num_rows == 0) {
    header("Location: history.php");
    exit;
}

$checklist = $result->fetch_assoc();

// Get checklist items
$sql = "SELECT * FROM checklist_items WHERE checklist_id = '$checklist_id' ORDER BY category, id";
$result = $conn->query($sql);
$items = [];
while ($row = $result->fetch_assoc()) {
    $items[] = $row;
}

// Group items by category
$grouped_items = [];
$parent_items = [];
$child_items = [];

foreach ($items as $item) {
    if ($item['parent_id']) {
        $child_items[$item['parent_id']][] = $item;
    } else {
        if (!isset($grouped_items[$item['category']])) {
            $grouped_items[$item['category']] = [];
        }
        $grouped_items[$item['category']][] = $item;

        if ($item['is_parent']) {
            $parent_items[$item['id']] = $item;
        }
    }
}

// Get comments
$sql = "SELECT c.*, u.username FROM comments c JOIN users u ON c.user_id = u.id WHERE c.checklist_id = '$checklist_id' ORDER BY c.created_at";
$result = $conn->query($sql);
$comments = [];
while ($row = $result->fetch_assoc()) {
    $comments[] = $row;
}

// Process new comment
$comment_success = '';
$comment_error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['comment'])) {
    $comment = $_POST['comment'];

    if (!empty($comment)) {
        $comment = $conn->real_escape_string($comment);
        $user_id = $_SESSION['user_id'];

        $sql = "INSERT INTO comments (checklist_id, user_id, comment) VALUES ('$checklist_id', '$user_id', '$comment')";

        if ($conn->query($sql)) {
            $comment_success = "Komentar berhasil ditambahkan!";

            // Refresh comments
            $sql = "SELECT c.*, u.username FROM comments c JOIN users u ON c.user_id = u.id WHERE c.checklist_id = '$checklist_id' ORDER BY c.created_at";
            $result = $conn->query($sql);
            $comments = [];
            while ($row = $result->fetch_assoc()) {
                $comments[] = $row;
            }
        } else {
            $comment_error = "Gagal menambahkan komentar: " . $conn->error;
        }
    } else {
        $comment_error = "Komentar tidak boleh kosong!";
    }
}

// Group items by subcategory for Hari-H
$hari_h_subcategories = [];
foreach ($grouped_items as $category => $items) {
    if (strpos($category, 'Hari-H Sebelum Ibadah - ') === 0) {
        $subcategory = substr($category, strlen('Hari-H Sebelum Ibadah - '));
        if (!isset($hari_h_subcategories[$subcategory])) {
            $hari_h_subcategories[$subcategory] = [];
        }
        $hari_h_subcategories[$subcategory] = array_merge($hari_h_subcategories[$subcategory], $items);
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lihat Checklist - GKPI Griya Permata</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
    <link rel="icon" href="public/images/church-logo.png" type="image/png">
    <style>
        .subitem-container {
            margin-left: 30px;
            padding-left: 10px;
            border-left: 2px solid #e9b872;
        }

        .signature-container {
            max-width: 300px;
            margin-top: 10px;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 10px;
            background-color: #fff;
        }

        .signature-image {
            max-width: 100%;
            height: auto;
        }

        /* Custom navbar styling */
        .custom-navbar {
            background-color: #3a5a78;
            padding: 10px 0;
        }

        .navbar-brand {
            display: flex;
            align-items: center;
            color: white;
            font-weight: 600;
            font-size: 1.5rem;
        }

        .navbar-brand img {
            height: 40px;
            margin-right: 10px;
        }

        .nav-link {
            color: white !important;
            padding: 8px 16px;
            border-radius: 5px;
            transition: background-color 0.3s;
        }

        .nav-link:hover,
        .nav-link.active {
            background-color: rgba(255, 255, 255, 0.1);
        }

        .nav-link i {
            margin-right: 5px;
        }

        .btn-logout {
            background-color: #d32f2f;
            color: white;
            border: none;
            padding: 8px 20px;
            border-radius: 5px;
            font-weight: 500;
        }

        .btn-logout:hover {
            background-color: #b71c1c;
        }

        .card-header {
            background-color: #3a5a78;
            color: white;
            font-weight: 600;
        }

        /* Table styling */
        .table th {
            background-color: #f8f9fa;
            font-weight: 600;
        }

        .table td,
        .table th {
            padding: 12px 15px;
            vertical-align: middle;
        }

        /* Chat styling */
        .chat-container {
            max-height: 400px;
            overflow-y: auto;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 15px;
            background-color: #f8f9fa;
        }

        .chat-message {
            margin-bottom: 15px;
            padding: 10px;
            border-radius: 5px;
        }

        .user-message {
            background-color: #e3f2fd;
            margin-left: 20%;
            border-left: 4px solid #2196f3;
        }

        .other-message {
            background-color: #fff;
            margin-right: 20%;
            border-left: 4px solid #4caf50;
        }

        .message-header {
            font-size: 0.85rem;
            color: #666;
            margin-bottom: 5px;
        }

        /* Section styling */
        .section-title {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 1rem;
            color: #3a5a78;
            border-bottom: 2px solid #e9b872;
            padding-bottom: 0.5rem;
        }

        /* Status badge */
        .status-badge {
            display: inline-block;
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
            font-size: 0.875rem;
            font-weight: 500;
        }

        .status-active {
            background-color: #4caf50;
            color: white;
        }

        /* Item notes */
        .item-notes {
            font-size: 0.9rem;
            color: #666;
            background-color: #f8f9fa;
            padding: 5px 10px;
            border-radius: 4px;
            border-left: 3px solid #e9b872;
        }
    </style>
</head>

<body>
    <!-- Modern navbar -->
    <nav class="custom-navbar">
        <div class="container d-flex justify-content-between align-items-center">
            <a class="navbar-brand" href="index.php">
                <img src="public/images/church-logo.png" alt="GKPI Logo">
                GKPI GP
            </a>
            <div class="d-flex">
                <a class="nav-link" href="index.php">
                    <i class="bi bi-house-door"></i> Beranda
                </a>
                <a class="nav-link" href="checklist.php">
                    <i class="bi bi-clipboard-check"></i> Checklist Baru
                </a>
                <a class="nav-link active" href="history.php">
                    <i class="bi bi-clock-history"></i> Riwayat
                </a>
                <a class="nav-link" href="chat.php">
                    <i class="bi bi-chat-dots"></i> Diskusi
                </a>
            </div>
            <div class="d-flex align-items-center">
                <a class="nav-link" href="profile.php">
                    <i class="bi bi-person-circle"></i> <?php echo $_SESSION['username']; ?>
                </a>
                <a class="btn-logout" href="logout.php">
                    <i class="bi bi-box-arrow-right"></i> Keluar
                </a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2><i class="bi bi-clipboard-data"></i> <?php echo htmlspecialchars($checklist['title']); ?></h2>
            <a href="history.php" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Kembali</a>
        </div>

        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <i class="bi bi-info-circle"></i> Detail Checklist
                    </div>
                    <div class="card-body">
                        <p><strong><i class="bi bi-person"></i> Dibuat Oleh:</strong> <?php echo htmlspecialchars($checklist['username']); ?></p>
                        <p><strong><i class="bi bi-calendar-event"></i> Tanggal Ibadah:</strong> <?php echo date('d M Y', strtotime($checklist['date'])); ?></p>
                        <p><strong><i class="bi bi-flag"></i> Status:</strong>
                            <span class="status-badge status-active">Aktif</span>
                        </p>
                        <p><strong><i class="bi bi-clock"></i> Dibuat Pada:</strong> <?php echo date('d M Y H:i', strtotime($checklist['created_at'])); ?></p>

                        <?php if (!empty($checklist['responsible_person'])): ?>
                            <p><strong><i class="bi bi-person"></i> Penanggung Jawab:</strong> <?php echo htmlspecialchars($checklist['responsible_person']); ?></p>
                        <?php endif; ?>

                        <?php if (!empty($checklist['signature'])): ?>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Tanda Tangan Penanggung Jawab:</label>
                                <div style="border: 1px solid #dee2e6; padding: 10px; border-radius: 5px; background-color: white;">
                                    <img src="<?php echo $checklist['signature']; ?>" alt="Tanda Tangan" style="max-width: 100%; height: auto;">
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <i class="bi bi-graph-up"></i> Progres Checklist
                    </div>
                    <div class="card-body">
                        <?php
                        $total_items = count($items);
                        $completed_items = 0;

                        foreach ($items as $item) {
                            if ($item['status'] == 1) {
                                $completed_items++;
                            }
                        }

                        $progress_percentage = $total_items > 0 ? round(($completed_items / $total_items) * 100) : 0;
                        ?>
                        <h4 class="text-center"><?php echo $progress_percentage; ?>% Selesai</h4>
                        <div class="progress">
                            <div class="progress-bar bg-success" role="progressbar" style="width: <?php echo $progress_percentage; ?>%" aria-valuenow="<?php echo $progress_percentage; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                        <p class="text-center mt-2"><?php echo $completed_items; ?> dari <?php echo $total_items; ?> item selesai</p>
                    </div>
                </div>
            </div>
        </div>

        <?php if (!empty($checklist['remark'])): ?>
            <div class="card mb-4">
                <div class="card-header">
                    <i class="bi bi-chat-left-text"></i> Catatan Tambahan
                </div>
                <div class="card-body">
                    <div class="p-3 bg-light rounded">
                        <?php echo nl2br(htmlspecialchars($checklist['remark'])); ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- H-2 s/d H-1 Checklist -->
        <?php if (isset($grouped_items['H-2 s/d H-1 Sebelum Ibadah'])): ?>
            <div class="card mb-4">
                <div class="card-header">
                    <i class="bi bi-calendar2-minus"></i> H-2 s/d H-1 Sebelum Ibadah
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th width="5%">#</th>
                                    <th width="55%">Item</th>
                                    <th width="20%">Status</th>
                                    <th width="20%">Catatan</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $i = 1;
                                foreach ($grouped_items['H-2 s/d H-1 Sebelum Ibadah'] as $item):
                                ?>
                                    <tr>
                                        <td><?php echo $i++; ?></td>
                                        <td><?php echo htmlspecialchars($item['item_name']); ?></td>
                                        <td>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" <?php echo $item['status'] ? 'checked' : ''; ?> disabled>
                                                <label class="form-check-label">Selesai</label>
                                            </div>
                                        </td>
                                        <td>
                                            <?php if (!empty($item['notes'])): ?>
                                                <div class="item-notes"><i class="bi bi-pencil"></i> <?php echo htmlspecialchars($item['notes']); ?></div>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>

                                    <?php if ($item['is_parent'] && isset($child_items[$item['id']])): ?>
                                        <tr>
                                            <td colspan="4">
                                                <div class="subitem-container mt-2">
                                                    <table class="table table-sm">
                                                        <thead>
                                                            <tr>
                                                                <th width="5%">#</th>
                                                                <th width="55%">Sub Item</th>
                                                                <th width="20%">Status</th>
                                                                <th width="20%">Catatan</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php
                                                            $j = 1;
                                                            foreach ($child_items[$item['id']] as $subitem):
                                                            ?>
                                                                <tr>
                                                                    <td><?php echo $j++; ?></td>
                                                                    <td><?php echo htmlspecialchars($subitem['item_name']); ?></td>
                                                                    <td>
                                                                        <div class="form-check">
                                                                            <input class="form-check-input" type="checkbox" <?php echo $subitem['status'] ? 'checked' : ''; ?> disabled>
                                                                            <label class="form-check-label">Selesai</label>
                                                                        </div>
                                                                    </td>
                                                                    <td>
                                                                        <?php if (!empty($subitem['notes'])): ?>
                                                                            <div class="item-notes"><i class="bi bi-pencil"></i> <?php echo htmlspecialchars($subitem['notes']); ?></div>
                                                                        <?php else: ?>
                                                                            <span class="text-muted">-</span>
                                                                        <?php endif; ?>
                                                                    </td>
                                                                </tr>
                                                            <?php endforeach; ?>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endif; ?>

                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Hari-H Checklist -->
        <?php if (!empty($hari_h_subcategories)): ?>
            <div class="card mb-4">
                <div class="card-header">
                    <i class="bi bi-calendar2-event"></i> Hari-H Sebelum Ibadah
                </div>
                <div class="card-body">
                    <?php foreach ($hari_h_subcategories as $subcategory => $subcategory_items): ?>
                        <div class="section-title">
                            <i class="bi bi-tag"></i> <?php echo htmlspecialchars($subcategory); ?>
                        </div>
                        <div class="table-responsive mb-4">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th width="5%">#</th>
                                        <th width="55%">Item</th>
                                        <th width="20%">Status</th>
                                        <th width="20%">Catatan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $i = 1;
                                    foreach ($subcategory_items as $item):
                                    ?>
                                        <tr>
                                            <td><?php echo $i++; ?></td>
                                            <td><?php echo htmlspecialchars($item['item_name']); ?></td>
                                            <td>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" <?php echo $item['status'] ? 'checked' : ''; ?> disabled>
                                                    <label class="form-check-label">Selesai</label>
                                                </div>
                                            </td>
                                            <td>
                                                <?php if (!empty($item['notes'])): ?>
                                                    <div class="item-notes"><i class="bi bi-pencil"></i> <?php echo htmlspecialchars($item['notes']); ?></div>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>

                                        <?php if ($item['is_parent'] && isset($child_items[$item['id']])): ?>
                                            <tr>
                                                <td colspan="4">
                                                    <div class="subitem-container mt-2">
                                                        <table class="table table-sm">
                                                            <thead>
                                                                <tr>
                                                                    <th width="5%">#</th>
                                                                    <th width="55%">Sub Item</th>
                                                                    <th width="20%">Status</th>
                                                                    <th width="20%">Catatan</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <?php
                                                                $j = 1;
                                                                foreach ($child_items[$item['id']] as $subitem):
                                                                ?>
                                                                    <tr>
                                                                        <td><?php echo $j++; ?></td>
                                                                        <td><?php echo htmlspecialchars($subitem['item_name']); ?></td>
                                                                        <td>
                                                                            <div class="form-check">
                                                                                <input class="form-check-input" type="checkbox" <?php echo $subitem['status'] ? 'checked' : ''; ?> disabled>
                                                                                <label class="form-check-label">Selesai</label>
                                                                            </div>
                                                                        </td>
                                                                        <td>
                                                                            <?php if (!empty($subitem['notes'])): ?>
                                                                                <div class="item-notes"><i class="bi bi-pencil"></i> <?php echo htmlspecialchars($subitem['notes']); ?></div>
                                                                            <?php else: ?>
                                                                                <span class="text-muted">-</span>
                                                                            <?php endif; ?>
                                                                        </td>
                                                                    </tr>
                                                                <?php endforeach; ?>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endif; ?>

                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Comments Section -->
        <div class="card mb-4">
            <div class="card-header">
                <i class="bi bi-chat-dots"></i> Diskusi
            </div>
            <div class="card-body">
                <?php if ($comment_success): ?>
                    <div class="alert alert-success">
                        <i class="bi bi-check-circle-fill"></i> <?php echo $comment_success; ?>
                    </div>
                <?php endif; ?>

                <?php if ($comment_error): ?>
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle-fill"></i> <?php echo $comment_error; ?>
                    </div>
                <?php endif; ?>

                <div class="chat-container mb-3">
                    <?php if (count($comments) > 0): ?>
                        <?php foreach ($comments as $comment): ?>
                            <div class="chat-message <?php echo $comment['user_id'] == $_SESSION['user_id'] ? 'user-message' : 'other-message'; ?>">
                                <div class="message-header">
                                    <strong><?php echo $comment['user_id'] == $_SESSION['user_id'] ? 'Anda' : htmlspecialchars($comment['username']); ?></strong> -
                                    <?php echo date('d M Y H:i', strtotime($comment['created_at'])); ?>
                                </div>
                                <div><?php echo nl2br(htmlspecialchars($comment['comment'])); ?></div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-center text-muted">Belum ada komentar.</div>
                    <?php endif; ?>
                </div>

                <form method="post" action="">
                    <div class="mb-3">
                        <label for="comment" class="form-label"><i class="bi bi-chat-square-text"></i> Tambahkan Komentar</label>
                        <textarea class="form-control" id="comment" name="comment" rows="3" required placeholder="Tulis komentar Anda di sini..."></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-send"></i> Kirim</button>
                </form>
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
    <script>
        // Scroll chat to bottom on load
        document.addEventListener('DOMContentLoaded', function() {
            const chatContainer = document.querySelector('.chat-container');
            chatContainer.scrollTop = chatContainer.scrollHeight;

            // Auto refresh comments every 30 seconds
            setInterval(function() {
                location.reload();
            }, 30000);
        });

        // Debug signature data
        function checkSignature() {
            console.log("Signature data:", <?php echo json_encode($checklist['signature']); ?>);
            console.log("Signature element:", document.querySelector('img[alt="Tanda Tangan"]'));
        }

        setTimeout(checkSignature, 1000);
    </script>
</body>

</html>