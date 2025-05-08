<?php
session_start();
// Redirect to login if not logged in
if (!isset($_SESSION['user_id']) && basename($_SERVER['PHP_SELF']) != 'login.php') {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checklist Persiapan Ibadah GKPI Griya Permata</title>
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
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>" href="index.php">
                            <i class="bi bi-house-door"></i> Beranda
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'checklist.php' ? 'active' : ''; ?>" href="checklist.php">
                            <i class="bi bi-clipboard-check"></i> Checklist Baru
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'history.php' ? 'active' : ''; ?>" href="history.php">
                            <i class="bi bi-clock-history"></i> Riwayat
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'chat.php' ? 'active' : ''; ?>" href="chat.php">
                            <i class="bi bi-chat-dots"></i> Diskusi
                        </a>
                    </li>
                </ul>
                <?php if (isset($_SESSION['user_id'])): ?>
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
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <?php if (basename($_SERVER['PHP_SELF']) == 'index.php'): ?>
            <div class="hero-section">
                <div class="container">
                    <h1>Sistem Checklist Persiapan Ibadah</h1>
                    <p>Selamat datang di sistem pengelolaan persiapan ibadah GKPI Griya Permata. Sistem ini membantu Anda mengelola dan memantau persiapan ibadah dengan checklist yang terstruktur.</p>
                    <div class="hero-buttons">
                        <a href="checklist.php" class="btn btn-primary">
                            <i class="bi bi-plus-circle"></i> Buat Checklist Baru
                        </a>
                        <a href="history.php" class="btn btn-secondary">
                            <i class="bi bi-clock-history"></i> Lihat Riwayat
                        </a>
                        <a href="finance.php" class="btn btn-success">
                            <i class="bi bi-cash-coin"></i> Form Keuangan
                        </a>
                        <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == 1): ?>
                            <a href="admin.php" class="btn btn-danger">
                                <i class="bi bi-speedometer2"></i> Admin Panel
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-3">
                    <div class="card feature-card">
                        <div class="card-body">
                            <div class="feature-icon">
                                <i class="bi bi-check2-circle"></i>
                            </div>
                            <h4 class="feature-title">Checklist Terstruktur</h4>
                            <p>Kelola persiapan ibadah dengan checklist yang terorganisir dan terstruktur dengan baik.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card feature-card">
                        <div class="card-body">
                            <div class="feature-icon">
                                <i class="bi bi-chat-dots"></i>
                            </div>
                            <h4 class="feature-title">Diskusi Tim</h4>
                            <p>Komunikasikan dan diskusikan persiapan ibadah dengan tim melalui fitur komentar.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card feature-card">
                        <div class="card-body">
                            <div class="feature-icon">
                                <i class="bi bi-graph-up"></i>
                            </div>
                            <h4 class="feature-title">Pantau Progres</h4>
                            <p>Pantau progres persiapan ibadah dengan mudah melalui indikator kemajuan yang jelas.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card feature-card">
                        <div class="card-body">
                            <div class="feature-icon">
                                <i class="bi bi-cash-coin"></i>
                            </div>
                            <h4 class="feature-title">Kelola Keuangan</h4>
                            <p>Catat dan kelola penerimaan kolekte dengan form keuangan yang terstruktur.</p>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
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