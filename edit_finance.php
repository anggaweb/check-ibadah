<?php
session_start();
include 'db_connect.php';

// Redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Check if ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: finance.php");
    exit;
}

$finance_id = intval($_GET['id']);

// Get finance record
$sql = "SELECT * FROM finance_collection WHERE id = $finance_id";
$result = $conn->query($sql);

if ($result->num_rows == 0) {
    header("Location: finance.php");
    exit;
}

$finance = $result->fetch_assoc();

// Check if user is admin or the creator
if ($_SESSION['user_id'] != 1 && $_SESSION['user_id'] != $finance['created_by']) {
    header("Location: finance.php");
    exit;
}

$success = '';
$error = '';

// Process form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $date = $conn->real_escape_string($_POST['date']);

    // Konversi nilai uang dari format Rupiah ke integer
    $sunday_school = (int)str_replace('.', '', $_POST['sunday_school']);
    $general_service = (int)str_replace('.', '', $_POST['general_service']);
    $tithe = (int)str_replace('.', '', $_POST['tithe']);
    $diaconia = (int)str_replace('.', '', $_POST['diaconia']);
    $other_offering = (int)str_replace('.', '', $_POST['other_offering']);

    $other_offering_description = $conn->real_escape_string($_POST['other_offering_description']);
    $total_attendance = intval($_POST['total_attendance']);

    // Calculate total
    $total_amount = $sunday_school + $general_service + $tithe + $diaconia + $other_offering;

    // Get counter information
    $counter1_name = $conn->real_escape_string($_POST['counter1_name'] ?? '');
    $counter1_signature = !empty($_POST['counter1_signature']) ? $conn->real_escape_string($_POST['counter1_signature']) : $finance['counter1_signature'];
    $counter2_name = $conn->real_escape_string($_POST['counter2_name'] ?? '');
    $counter2_signature = !empty($_POST['counter2_signature']) ? $conn->real_escape_string($_POST['counter2_signature']) : $finance['counter2_signature'];
    $counter3_name = $conn->real_escape_string($_POST['counter3_name'] ?? '');
    $counter3_signature = !empty($_POST['counter3_signature']) ? $conn->real_escape_string($_POST['counter3_signature']) : $finance['counter3_signature'];

    // Update database
    $sql = "UPDATE finance_collection SET 
            date = '$date', 
            sunday_school = $sunday_school, 
            general_service = $general_service, 
            tithe = $tithe, 
            diaconia = $diaconia, 
            other_offering = $other_offering, 
            other_offering_description = '$other_offering_description', 
            total_amount = $total_amount, 
            total_attendance = $total_attendance,
            counter1_name = '$counter1_name', 
            counter1_signature = '$counter1_signature', 
            counter2_name = '$counter2_name', 
            counter2_signature = '$counter2_signature', 
            counter3_name = '$counter3_name', 
            counter3_signature = '$counter3_signature'
            WHERE id = $finance_id";

    if ($conn->query($sql)) {
        $success = "Data penerimaan kolekte berhasil diperbarui!";

        // Refresh finance data
        $result = $conn->query("SELECT * FROM finance_collection WHERE id = $finance_id");
        $finance = $result->fetch_assoc();
    } else {
        $error = "Terjadi kesalahan: " . $conn->error;
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Penerimaan Kolekte - GKPI Griya Permata</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
    <link rel="icon" href="public/images/church-logo.png" type="image/png">
    <style>
        .signature-pad-container {
            border: 1px solid #dee2e6;
            border-radius: 10px;
            overflow: hidden;
            margin-bottom: 15px;
            position: relative;
            /* Penting untuk positioning */
        }

        .signature-pad {
            width: 100%;
            height: 150px;
            background-color: #fff;
            touch-action: none;
            /* Penting untuk touch devices */
        }

        .signature-pad-actions {
            display: flex;
            justify-content: flex-end;
            padding: 10px;
            background-color: #f8f9fa;
        }

        .finance-form label {
            font-weight: 500;
        }

        .finance-form .form-control {
            border-radius: 8px;
        }

        .finance-form .input-group-text {
            background-color: var(--primary-light);
            color: white;
            border-color: var(--primary-light);
        }

        .finance-total {
            font-size: 1.2rem;
            font-weight: 600;
        }

        .counter-section {
            border-top: 1px solid #dee2e6;
            padding-top: 20px;
            margin-top: 20px;
        }

        .current-signature {
            max-width: 100%;
            height: auto;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 10px;
            margin-bottom: 10px;
        }

        /* Penting: Pastikan container memiliki tinggi yang cukup */
        .signature-pad-wrapper {
            position: relative;
            height: 150px;
            /* Harus sama dengan tinggi canvas */
            margin-bottom: 50px;
            /* Untuk tombol actions */
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
                        <a class="nav-link" href="chat.php">
                            <i class="bi bi-chat-dots"></i> Diskusi
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="finance.php">
                            <i class="bi bi-cash-coin"></i> Keuangan
                        </a>
                    </li>
                    <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == 1): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="admin.php">
                                <i class="bi bi-speedometer2"></i> Admin
                            </a>
                        </li>
                    <?php endif; ?>
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
            <h2><i class="bi bi-pencil-square"></i> Edit Penerimaan Kolekte</h2>
            <a href="finance.php" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
        </div>

        <div class="card">
            <div class="card-body">
                <form method="post" action="" id="finance-form" class="finance-form">
                    <div class="row mb-4">
                        <div class="col-md-6 offset-md-6 text-end">
                            <div class="mb-3">
                                <label for="date" class="form-label">Tanggal:</label>
                                <input type="date" class="form-control" id="date" name="date" required value="<?php echo $finance['date']; ?>">
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-8">
                            <label for="sunday_school" class="form-label">Persembahan Sekolah Minggu</label>
                        </div>
                        <div class="col-md-4">
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="text" class="form-control offering-amount" id="sunday_school" name="sunday_school" value="<?php echo number_format($finance['sunday_school'], 0, ',', '.'); ?>" required>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-8">
                            <label for="general_service" class="form-label">Persembahan Kebaktian Umum</label>
                        </div>
                        <div class="col-md-4">
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="text" class="form-control offering-amount" id="general_service" name="general_service" value="<?php echo number_format($finance['general_service'], 0, ',', '.'); ?>" required>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-8">
                            <label for="tithe" class="form-label">Persembahan Persepuluhan</label>
                        </div>
                        <div class="col-md-4">
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="text" class="form-control offering-amount" id="tithe" name="tithe" value="<?php echo number_format($finance['tithe'], 0, ',', '.'); ?>" required>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-8">
                            <label for="diaconia" class="form-label">Persembahan Diakonia</label>
                        </div>
                        <div class="col-md-4">
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="text" class="form-control offering-amount" id="diaconia" name="diaconia" value="<?php echo number_format($finance['diaconia'], 0, ',', '.'); ?>" required>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-8">
                            <div class="input-group">
                                <label for="other_offering" class="form-label">Persembahan lain-lain</label>
                                <div class="ms-2">
                                    <input type="text" class="form-control" id="other_offering_description" name="other_offering_description" placeholder="Keterangan" value="<?php echo htmlspecialchars($finance['other_offering_description']); ?>">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="text" class="form-control offering-amount" id="other_offering" name="other_offering" value="<?php echo number_format($finance['other_offering'], 0, ',', '.'); ?>" required>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-8">
                            <label class="form-label finance-total">Total</label>
                        </div>
                        <div class="col-md-4">
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="text" class="form-control finance-total" id="total_amount" name="total_amount" value="<?php echo number_format($finance['total_amount'], 0, ',', '.'); ?>" readonly>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-8">
                            <label for="total_attendance" class="form-label">Total Kehadiran</label>
                        </div>
                        <div class="col-md-4">
                            <div class="input-group">
                                <input type="number" class="form-control" id="total_attendance" name="total_attendance" value="<?php echo $finance['total_attendance']; ?>" min="0" required>
                                <span class="input-group-text">orang</span>
                            </div>
                        </div>
                    </div>

                    <div class="counter-section">
                        <div class="row">
                            <div class="col-md-4 mb-4">
                                <div class="card h-100">
                                    <div class="card-header">Dihitung Oleh:</div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <input type="text" class="form-control" id="counter1_name" name="counter1_name" placeholder="Nama Penghitung 1" value="<?php echo htmlspecialchars($finance['counter1_name']); ?>">
                                        </div>
                                        <?php if (!empty($finance['counter1_signature'])): ?>
                                            <div class="mb-3">
                                                <label class="form-label">Tanda Tangan Saat Ini:</label>
                                                <div style="border: 1px solid #dee2e6; padding: 10px; border-radius: 5px;">
                                                    <img src="<?php echo $finance['counter1_signature']; ?>" alt="Tanda Tangan" class="current-signature" style="max-width: 100%; height: auto;">
                                                </div>
                                                <div class="form-check mt-2">
                                                    <input class="form-check-input" type="checkbox" id="change_signature1" name="change_signature1">
                                                    <label class="form-check-label" for="change_signature1">
                                                        Ganti tanda tangan
                                                    </label>
                                                </div>
                                            </div>
                                            <div id="new_signature_container1" style="display: none;">
                                            <?php endif; ?>
                                            <div class="signature-pad-wrapper">
                                                <div class="signature-pad-container">
                                                    <canvas id="signature-pad-1" class="signature-pad"></canvas>
                                                    <div class="signature-pad-actions">
                                                        <button type="button" class="btn btn-sm btn-secondary clear-signature" data-pad="1">Hapus</button>
                                                    </div>
                                                </div>
                                            </div>
                                            <input type="hidden" name="counter1_signature" id="counter1_signature">
                                            <?php if (!empty($finance['counter1_signature'])): ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4 mb-4">
                                <div class="card h-100">
                                    <div class="card-header">Dihitung Oleh:</div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <input type="text" class="form-control" id="counter2_name" name="counter2_name" placeholder="Nama Penghitung 2" value="<?php echo htmlspecialchars($finance['counter2_name']); ?>">
                                        </div>
                                        <?php if (!empty($finance['counter2_signature'])): ?>
                                            <div class="mb-3">
                                                <label class="form-label">Tanda Tangan Saat Ini:</label>
                                                <div style="border: 1px solid #dee2e6; padding: 10px; border-radius: 5px;">
                                                    <img src="<?php echo $finance['counter2_signature']; ?>" alt="Tanda Tangan" class="current-signature" style="max-width: 100%; height: auto;">
                                                </div>
                                                <div class="form-check mt-2">
                                                    <input class="form-check-input" type="checkbox" id="change_signature2" name="change_signature2">
                                                    <label class="form-check-label" for="change_signature2">
                                                        Ganti tanda tangan
                                                    </label>
                                                </div>
                                            </div>
                                            <div id="new_signature_container2" style="display: none;">
                                            <?php endif; ?>
                                            <div class="signature-pad-wrapper">
                                                <div class="signature-pad-container">
                                                    <canvas id="signature-pad-2" class="signature-pad"></canvas>
                                                    <div class="signature-pad-actions">
                                                        <button type="button" class="btn btn-sm btn-secondary clear-signature" data-pad="2">Hapus</button>
                                                    </div>
                                                </div>
                                            </div>
                                            <input type="hidden" name="counter2_signature" id="counter2_signature">
                                            <?php if (!empty($finance['counter2_signature'])): ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4 mb-4">
                                <div class="card h-100">
                                    <div class="card-header">Dihitung Oleh:</div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <input type="text" class="form-control" id="counter3_name" name="counter3_name" placeholder="Nama Penghitung 3" value="<?php echo htmlspecialchars($finance['counter3_name']); ?>">
                                        </div>
                                        <?php if (!empty($finance['counter3_signature'])): ?>
                                            <div class="mb-3">
                                                <label class="form-label">Tanda Tangan Saat Ini:</label>
                                                <div style="border: 1px solid #dee2e6; padding: 10px; border-radius: 5px;">
                                                    <img src="<?php echo $finance['counter3_signature']; ?>" alt="Tanda Tangan" class="current-signature" style="max-width: 100%; height: auto;">
                                                </div>
                                                <div class="form-check mt-2">
                                                    <input class="form-check-input" type="checkbox" id="change_signature3" name="change_signature3">
                                                    <label class="form-check-label" for="change_signature3">
                                                        Ganti tanda tangan
                                                    </label>
                                                </div>
                                            </div>
                                            <div id="new_signature_container3" style="display: none;">
                                            <?php endif; ?>
                                            <div class="signature-pad-wrapper">
                                                <div class="signature-pad-container">
                                                    <canvas id="signature-pad-3" class="signature-pad"></canvas>
                                                    <div class="signature-pad-actions">
                                                        <button type="button" class="btn btn-sm btn-secondary clear-signature" data-pad="3">Hapus</button>
                                                    </div>
                                                </div>
                                            </div>
                                            <input type="hidden" name="counter3_signature" id="counter3_signature">
                                            <?php if (!empty($finance['counter3_signature'])): ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between mt-4">
                        <a href="finance.php" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Kembali
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Simpan Perubahan
                        </button>
                    </div>
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
    <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize signature pads with delay to ensure DOM is ready
            setTimeout(function() {
                initSignaturePads();
            }, 300);

            function initSignaturePads() {
                // Initialize signature pads
                const canvas1 = document.getElementById('signature-pad-1');
                const canvas2 = document.getElementById('signature-pad-2');
                const canvas3 = document.getElementById('signature-pad-3');

                let signaturePad1 = null;
                let signaturePad2 = null;
                let signaturePad3 = null;

                if (canvas1) {
                    // Pastikan canvas memiliki dimensi yang benar
                    canvas1.width = canvas1.offsetWidth;
                    canvas1.height = canvas1.offsetHeight;

                    signaturePad1 = new SignaturePad(canvas1, {
                        backgroundColor: 'rgba(255, 255, 255, 0)',
                        penColor: 'black'
                    });
                }

                if (canvas2) {
                    // Pastikan canvas memiliki dimensi yang benar
                    canvas2.width = canvas2.offsetWidth;
                    canvas2.height = canvas2.offsetHeight;

                    signaturePad2 = new SignaturePad(canvas2, {
                        backgroundColor: 'rgba(255, 255, 255, 0)',
                        penColor: 'black'
                    });
                }

                if (canvas3) {
                    // Pastikan canvas memiliki dimensi yang benar
                    canvas3.width = canvas3.offsetWidth;
                    canvas3.height = canvas3.offsetHeight;

                    signaturePad3 = new SignaturePad(canvas3, {
                        backgroundColor: 'rgba(255, 255, 255, 0)',
                        penColor: 'black'
                    });
                }

                // Clear signature buttons
                document.querySelectorAll('.clear-signature').forEach(button => {
                    button.addEventListener('click', function() {
                        const padNumber = this.getAttribute('data-pad');
                        if (padNumber === '1' && signaturePad1) {
                            signaturePad1.clear();
                            document.getElementById('counter1_signature').value = '';
                        } else if (padNumber === '2' && signaturePad2) {
                            signaturePad2.clear();
                            document.getElementById('counter2_signature').value = '';
                        } else if (padNumber === '3' && signaturePad3) {
                            signaturePad3.clear();
                            document.getElementById('counter3_signature').value = '';
                        }
                    });
                });

                // Resize canvas
                function resizeCanvas() {
                    const ratio = Math.max(window.devicePixelRatio || 1, 1);

                    if (canvas1 && signaturePad1) {
                        // Simpan data tanda tangan sebelum resize
                        const data1 = signaturePad1.toData();

                        canvas1.width = canvas1.offsetWidth * ratio;
                        canvas1.height = canvas1.offsetHeight * ratio;
                        canvas1.getContext("2d").scale(ratio, ratio);

                        // Kembalikan data tanda tangan setelah resize
                        if (data1 && data1.length) signaturePad1.fromData(data1);
                    }

                    if (canvas2 && signaturePad2) {
                        // Simpan data tanda tangan sebelum resize
                        const data2 = signaturePad2.toData();

                        canvas2.width = canvas2.offsetWidth * ratio;
                        canvas2.height = canvas2.offsetHeight * ratio;
                        canvas2.getContext("2d").scale(ratio, ratio);

                        // Kembalikan data tanda tangan setelah resize
                        if (data2 && data2.length) signaturePad2.fromData(data2);
                    }

                    if (canvas3 && signaturePad3) {
                        // Simpan data tanda tangan sebelum resize
                        const data3 = signaturePad3.toData();

                        canvas3.width = canvas3.offsetWidth * ratio;
                        canvas3.height = canvas3.offsetHeight * ratio;
                        canvas3.getContext("2d").scale(ratio, ratio);

                        // Kembalikan data tanda tangan setelah resize
                        if (data3 && data3.length) signaturePad3.fromData(data3);
                    }
                }

                window.addEventListener("resize", resizeCanvas);
                resizeCanvas();

                // Toggle signature change
                const changeSignature1 = document.getElementById('change_signature1');
                if (changeSignature1) {
                    changeSignature1.addEventListener('change', function() {
                        const newSignatureContainer = document.getElementById('new_signature_container1');
                        if (this.checked) {
                            newSignatureContainer.style.display = 'block';
                            // Resize canvas setelah container ditampilkan
                            setTimeout(resizeCanvas, 100);
                        } else {
                            newSignatureContainer.style.display = 'none';
                            document.getElementById('counter1_signature').value = '';
                        }
                    });
                }

                const changeSignature2 = document.getElementById('change_signature2');
                if (changeSignature2) {
                    changeSignature2.addEventListener('change', function() {
                        const newSignatureContainer = document.getElementById('new_signature_container2');
                        if (this.checked) {
                            newSignatureContainer.style.display = 'block';
                            // Resize canvas setelah container ditampilkan
                            setTimeout(resizeCanvas, 100);
                        } else {
                            newSignatureContainer.style.display = 'none';
                            document.getElementById('counter2_signature').value = '';
                        }
                    });
                }

                const changeSignature3 = document.getElementById('change_signature3');
                if (changeSignature3) {
                    changeSignature3.addEventListener('change', function() {
                        const newSignatureContainer = document.getElementById('new_signature_container3');
                        if (this.checked) {
                            newSignatureContainer.style.display = 'block';
                            // Resize canvas setelah container ditampilkan
                            setTimeout(resizeCanvas, 100);
                        } else {
                            newSignatureContainer.style.display = 'none';
                            document.getElementById('counter3_signature').value = '';
                        }
                    });
                }

                // Format currency input
                function formatRupiah(angka) {
                    let number_string = angka.toString().replace(/[^,\d]/g, '').toString(),
                        split = number_string.split(','),
                        sisa = split[0].length % 3,
                        rupiah = split[0].substr(0, sisa),
                        ribuan = split[0].substr(sisa).match(/\d{3}/gi);

                    if (ribuan) {
                        separator = sisa ? '.' : '';
                        rupiah += separator + ribuan.join('.');
                    }

                    return rupiah;
                }

                // Calculate total amount
                const offeringInputs = document.querySelectorAll('.offering-amount');
                offeringInputs.forEach(input => {
                    input.addEventListener('input', function(e) {
                        // Hanya memperbolehkan angka
                        this.value = this.value.replace(/[^\d]/g, '');
                        // Format sebagai Rupiah
                        this.value = formatRupiah(this.value);
                        calculateTotal();
                    });
                });

                function calculateTotal() {
                    let total = 0;
                    offeringInputs.forEach(input => {
                        // Hapus semua titik sebagai pemisah ribuan
                        const value = input.value.replace(/\./g, '');
                        total += parseInt(value) || 0;
                    });

                    document.getElementById('total_amount').value = formatRupiah(total);
                }

                // Form submission
                const form = document.getElementById('finance-form');
                if (form) {
                    form.addEventListener('submit', function(e) {
                        // Save signature data to hidden inputs if changed
                        if (changeSignature1 && changeSignature1.checked && signaturePad1 && !signaturePad1.isEmpty()) {
                            document.getElementById('counter1_signature').value = signaturePad1.toDataURL();
                            console.log("New signature 1 saved, length:", document.getElementById('counter1_signature').value.length);
                        }

                        if (changeSignature2 && changeSignature2.checked && signaturePad2 && !signaturePad2.isEmpty()) {
                            document.getElementById('counter2_signature').value = signaturePad2.toDataURL();
                            console.log("New signature 2 saved, length:", document.getElementById('counter2_signature').value.length);
                        }

                        if (changeSignature3 && changeSignature3.checked && signaturePad3 && !signaturePad3.isEmpty()) {
                            document.getElementById('counter3_signature').value = signaturePad3.toDataURL();
                            console.log("New signature 3 saved, length:", document.getElementById('counter3_signature').value.length);
                        }

                        return true;
                    });
                }
            }
        });
    </script>
</body>

</html>