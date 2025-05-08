<?php
session_start();
require_once 'db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
$message = '';

// Handle new message submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message']) && !empty($_POST['message'])) {
    $chat_message = trim($_POST['message']);

    // Insert message into database
    $stmt = $conn->prepare("INSERT INTO global_chat (user_id, message) VALUES (?, ?)");
    $stmt->bind_param("is", $user_id, $chat_message);

    if ($stmt->execute()) {
        // Message sent successfully
        header("Location: chat.php");
        exit();
    } else {
        $message = '<div class="alert alert-danger">Error sending message: ' . $conn->error . '</div>';
    }
}

// Fetch chat messages
$chat_messages = [];
$stmt = $conn->prepare("
    SELECT gc.id, gc.message, gc.created_at, u.username 
    FROM global_chat gc
    JOIN users u ON gc.user_id = u.id
    ORDER BY gc.created_at ASC
    LIMIT 100
");
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $chat_messages[] = $row;
}

// Fetch all checklists for reviews section
$checklists = [];
$stmt = $conn->prepare("
    SELECT c.id, c.title, c.date, c.status, c.created_at, u.username 
    FROM checklists c
    JOIN users u ON c.user_id = u.id
    ORDER BY c.created_at DESC
    LIMIT 10
");
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $checklists[] = $row;
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diskusi - GKPI Griya Permata</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
    <link rel="icon" href="public/images/church-logo.png" type="image/png">
    <style>
        .chat-container {
            height: 400px;
            overflow-y: auto;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 15px;
            background-color: #f9f9f9;
        }

        .message {
            margin-bottom: 15px;
            padding: 10px;
            border-radius: 5px;
        }

        .message-self {
            background-color: #d1ecf1;
            margin-left: 20%;
            border-left: 4px solid #0dcaf0;
        }

        .message-other {
            background-color: #f8f9fa;
            margin-right: 20%;
            border-left: 4px solid #6c757d;
        }

        .message-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
            font-size: 0.85rem;
            color: #6c757d;
        }

        .message-content {
            word-break: break-word;
        }

        .checklist-card {
            margin-bottom: 20px;
            transition: transform 0.3s;
        }

        .checklist-card:hover {
            transform: translateY(-5px);
        }

        .status-badge {
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .status-completed {
            background-color: #d4edda;
            color: #155724;
        }

        .status-draft {
            background-color: #f8f9fa;
            color: #6c757d;
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
                        <a class="nav-link" href="checklist.php">
                            <i class="bi bi-clipboard-check"></i> Checklist Baru
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="history.php">
                            <i class="bi bi-clock-history"></i> Riwayat
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="chat.php">
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
        <?php echo $message; ?>

        <ul class="nav nav-tabs" id="communityTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="chat-tab" data-bs-toggle="tab" data-bs-target="#chat" type="button" role="tab" aria-controls="chat" aria-selected="true">
                    <i class="bi bi-chat-dots"></i> Diskusi Umum
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="reviews-tab" data-bs-toggle="tab" data-bs-target="#reviews" type="button" role="tab" aria-controls="reviews" aria-selected="false">
                    <i class="bi bi-clipboard-check"></i> Checklist Terbaru
                </button>
            </li>
        </ul>

        <div class="tab-content mt-3" id="communityTabsContent">
            <!-- Chat Tab -->
            <div class="tab-pane fade show active" id="chat" role="tabpanel" aria-labelledby="chat-tab">
                <div class="row">
                    <div class="col-md-12">
                        <div class="card shadow">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0"><i class="bi bi-chat-dots"></i> Diskusi Umum</h5>
                            </div>
                            <div class="card-body">
                                <div class="chat-container mb-3" id="chatContainer">
                                    <?php if (empty($chat_messages)): ?>
                                        <div class="text-center text-muted py-5">
                                            <i class="bi bi-chat-dots fs-1 mb-3"></i>
                                            <p>Belum ada pesan. Jadilah yang pertama memulai diskusi!</p>
                                        </div>
                                    <?php else: ?>
                                        <?php foreach ($chat_messages as $msg): ?>
                                            <div class="message <?php echo ($msg['username'] === $username) ? 'message-self' : 'message-other'; ?>">
                                                <div class="message-header">
                                                    <span><i class="bi bi-person-circle"></i> <?php echo htmlspecialchars($msg['username']); ?></span>
                                                    <span><i class="bi bi-clock"></i> <?php echo date('d M Y H:i', strtotime($msg['created_at'])); ?></span>
                                                </div>
                                                <div class="message-content">
                                                    <?php echo nl2br(htmlspecialchars($msg['message'])); ?>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>

                                <form method="post" action="chat.php">
                                    <div class="mb-3">
                                        <label for="message" class="form-label"><i class="bi bi-pencil-square"></i> Pesan Anda</label>
                                        <textarea class="form-control" id="message" name="message" rows="3" required placeholder="Tulis pesan Anda di sini..."></textarea>
                                    </div>
                                    <div class="d-grid">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="bi bi-send"></i> Kirim Pesan
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Reviews Tab -->
            <div class="tab-pane fade" id="reviews" role="tabpanel" aria-labelledby="reviews-tab">
                <div class="row">
                    <div class="col-md-12">
                        <div class="card shadow">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0"><i class="bi bi-clipboard-check"></i> Checklist Terbaru</h5>
                            </div>
                            <div class="card-body">
                                <?php if (empty($checklists)): ?>
                                    <div class="text-center text-muted py-5">
                                        <i class="bi bi-clipboard-x fs-1 mb-3"></i>
                                        <p>Belum ada checklist yang dibuat. <a href="checklist.php" class="alert-link">Buat checklist baru</a>.</p>
                                    </div>
                                <?php else: ?>
                                    <div class="row">
                                        <?php foreach ($checklists as $checklist): ?>
                                            <div class="col-md-6 mb-4">
                                                <div class="card checklist-card h-100">
                                                    <div class="card-header">
                                                        <div class="d-flex justify-content-between align-items-center">
                                                            <h6 class="mb-0"><?php echo htmlspecialchars($checklist['title']); ?></h6>
                                                            <span class="status-badge <?php echo $checklist['status'] == 'completed' ? 'status-completed' : 'status-draft'; ?>">
                                                                <?php echo $checklist['status'] == 'completed' ? 'Selesai' : 'Draft'; ?>
                                                            </span>
                                                        </div>
                                                    </div>
                                                    <div class="card-body">
                                                        <p><i class="bi bi-person-circle"></i> <strong>Dibuat oleh:</strong> <?php echo htmlspecialchars($checklist['username']); ?></p>
                                                        <p><i class="bi bi-calendar-event"></i> <strong>Tanggal Ibadah:</strong> <?php echo date('d M Y', strtotime($checklist['date'])); ?></p>
                                                        <p><i class="bi bi-clock"></i> <strong>Dibuat pada:</strong> <?php echo date('d M Y H:i', strtotime($checklist['created_at'])); ?></p>
                                                    </div>
                                                    <div class="card-footer">
                                                        <a href="view_checklist.php?id=<?php echo $checklist['id']; ?>" class="btn btn-primary btn-sm">
                                                            <i class="bi bi-eye"></i> Lihat Detail
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
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
    <script>
        // Scroll chat to bottom on load
        document.addEventListener('DOMContentLoaded', function() {
            const chatContainer = document.getElementById('chatContainer');
            if (chatContainer) {
                chatContainer.scrollTop = chatContainer.scrollHeight;
            }

            // Auto refresh chat every 30 seconds
            setInterval(function() {
                if (document.getElementById('chat-tab').classList.contains('active')) {
                    location.reload();
                }
            }, 30000);
        });
    </script>
</body>

</html>