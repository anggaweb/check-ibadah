<?php
session_start();
include 'db_connect.php';

// Redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$success = '';
$error = '';

// Create finance_collection table if it doesn't exist
$sql = "CREATE TABLE IF NOT EXISTS finance_collection (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    date DATE NOT NULL,
    sunday_school INT DEFAULT 0,
    general_service INT DEFAULT 0,
    tithe INT DEFAULT 0,
    diaconia INT DEFAULT 0,
    other_offering INT DEFAULT 0,
    other_offering_description VARCHAR(255) NULL,
    total_amount INT DEFAULT 0,
    total_attendance INT DEFAULT 0,
    counter1_name VARCHAR(100) NULL,
    counter1_signature LONGTEXT NULL,
    counter2_name VARCHAR(100) NULL,
    counter2_signature LONGTEXT NULL,
    counter3_name VARCHAR(100) NULL,
    counter3_signature LONGTEXT NULL,
    created_by INT(11) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id)
)";
$conn->query($sql);

// Process form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $date = mysqli_real_escape_string($conn, $_POST['date']);

    // Konversi nilai uang dari format Rupiah ke integer
    $sunday_school = (int)str_replace('.', '', $_POST['sunday_school']);
    $general_service = (int)str_replace('.', '', $_POST['general_service']);
    $tithe = (int)str_replace('.', '', $_POST['tithe']);
    $diaconia = (int)str_replace('.', '', $_POST['diaconia']);
    $other_offering = (int)str_replace('.', '', $_POST['other_offering']);

    $other_offering_description = mysqli_real_escape_string($conn, $_POST['other_offering_description']);
    $total_attendance = intval($_POST['total_attendance']);

    // Calculate total
    $total_amount = $sunday_school + $general_service + $tithe + $diaconia + $other_offering;

    // Get counter information
    $counter1_name = mysqli_real_escape_string($conn, $_POST['counter1_name'] ?? '');
    $counter1_signature = mysqli_real_escape_string($conn, $_POST['counter1_signature'] ?? '');
    $counter2_name = mysqli_real_escape_string($conn, $_POST['counter2_name'] ?? '');
    $counter2_signature = mysqli_real_escape_string($conn, $_POST['counter2_signature'] ?? '');
    $counter3_name = mysqli_real_escape_string($conn, $_POST['counter3_name'] ?? '');
    $counter3_signature = mysqli_real_escape_string($conn, $_POST['counter3_signature'] ?? '');
    $created_by = $_SESSION['user_id'];

    // Insert into database using direct query
    $sql = "INSERT INTO finance_collection (
        date, sunday_school, general_service, tithe, diaconia, 
        other_offering, other_offering_description, total_amount, total_attendance,
        counter1_name, counter1_signature, counter2_name, counter2_signature, 
        counter3_name, counter3_signature, created_by
    ) VALUES (
        '$date', $sunday_school, $general_service, $tithe, $diaconia, 
        $other_offering, '$other_offering_description', $total_amount, $total_attendance,
        '$counter1_name', '$counter1_signature', '$counter2_name', '$counter2_signature',
        '$counter3_name', '$counter3_signature', $created_by
    )";

    if ($conn->query($sql)) {
        $success = "Form penerimaan kolekte berhasil disimpan!";
    } else {
        $error = "Terjadi kesalahan: " . $conn->error;
    }
}

// Get recent finance records
$recent_records = [];
$sql = "SELECT f.*, u.username FROM finance_collection f 
        JOIN users u ON f.created_by = u.id 
        ORDER BY f.date DESC LIMIT 10";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $recent_records[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Keuangan - GKPI Griya Permata</title>
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
        }

        .signature-pad {
            width: 100%;
            height: 150px;
            background-color: #fff;
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

        .print-header {
            display: none;
        }

        @media print {

            .navbar,
            .footer,
            .btn-print,
            .btn-back,
            .signature-pad-actions,
            .nav-tabs,
            .tab-content>.tab-pane {
                display: none !important;
            }

            .tab-content>.active {
                display: block !important;
            }

            .container {
                width: 100%;
                max-width: 100%;
            }

            .card {
                border: none !important;
                box-shadow: none !important;
            }

            .print-header {
                display: block;
                text-align: center;
                margin-bottom: 20px;
            }

            .print-header h2 {
                font-size: 18px;
                margin-bottom: 5px;
            }

            .print-header p {
                font-size: 14px;
                margin-bottom: 0;
            }

            .print-container {
                padding: 20px;
                max-width: 800px;
                margin: 0 auto;
            }

            .print-title {
                text-align: center;
                font-size: 18px;
                font-weight: bold;
                margin-bottom: 20px;
                text-decoration: underline;
            }

            .print-info {
                margin-bottom: 20px;
            }

            .print-info-row {
                display: flex;
                margin-bottom: 5px;
            }

            .print-info-label {
                width: 150px;
                font-weight: bold;
            }

            .print-table {
                width: 100%;
                border-collapse: collapse;
                margin-bottom: 20px;
            }

            .print-table th,
            .print-table td {
                border: 1px solid #000;
                padding: 8px;
            }

            .print-table th {
                background-color: #f0f0f0;
            }

            .print-signatures {
                display: flex;
                justify-content: space-between;
                margin-top: 50px;
            }

            .print-signature {
                text-align: center;
                width: 30%;
            }

            .print-signature-line {
                border-bottom: 1px solid #000;
                margin-bottom: 5px;
                height: 60px;
            }
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

        <ul class="nav nav-tabs" id="financeTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="form-tab" data-bs-toggle="tab" data-bs-target="#form-tab-pane" type="button" role="tab" aria-controls="form-tab-pane" aria-selected="true">
                    <i class="bi bi-file-earmark-text"></i> Form Penerimaan Kolekte
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="history-tab" data-bs-toggle="tab" data-bs-target="#history-tab-pane" type="button" role="tab" aria-controls="history-tab-pane" aria-selected="false">
                    <i class="bi bi-clock-history"></i> Riwayat Penerimaan
                </button>
            </li>
        </ul>

        <div class="tab-content" id="financeTabContent">
            <!-- Form Tab -->
            <div class="tab-pane fade show active" id="form-tab-pane" role="tabpanel" aria-labelledby="form-tab" tabindex="0">
                <div class="card mt-3">
                    <div class="card-body">
                        <div class="print-header">
                            <h2>GKPI GRIYA PERMATA</h2>
                            <p>Form Penerimaan Kolekte</p>
                        </div>

                        <form method="post" action="" id="finance-form" class="finance-form">
                            <div class="row mb-4">
                                <div class="col-md-6 offset-md-6 text-end">
                                    <div class="mb-3">
                                        <label for="date" class="form-label">Tanggal:</label>
                                        <input type="date" class="form-control" id="date" name="date" required value="<?php echo date('Y-m-d'); ?>">
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
                                        <input type="text" class="form-control offering-amount" id="sunday_school" name="sunday_school" value="0" required>
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
                                        <input type="text" class="form-control offering-amount" id="general_service" name="general_service" value="0" required>
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
                                        <input type="text" class="form-control offering-amount" id="tithe" name="tithe" value="0" required>
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
                                        <input type="text" class="form-control offering-amount" id="diaconia" name="diaconia" value="0" required>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-8">
                                    <div class="input-group">
                                        <label for="other_offering" class="form-label">Persembahan lain-lain</label>
                                        <div class="ms-2">
                                            <input type="text" class="form-control" id="other_offering_description" name="other_offering_description" placeholder="Keterangan">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="input-group">
                                        <span class="input-group-text">Rp</span>
                                        <input type="text" class="form-control offering-amount" id="other_offering" name="other_offering" value="0" required>
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
                                        <input type="text" class="form-control finance-total" id="total_amount" name="total_amount" value="0" readonly>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-4">
                                <div class="col-md-8">
                                    <label for="total_attendance" class="form-label">Total Kehadiran</label>
                                </div>
                                <div class="col-md-4">
                                    <div class="input-group">
                                        <input type="number" class="form-control" id="total_attendance" name="total_attendance" value="0" min="0" required>
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
                                                    <input type="text" class="form-control" id="counter1_name" name="counter1_name" placeholder="Nama Penghitung 1">
                                                </div>
                                                <label class="form-label">Paraf:</label>
                                                <div class="signature-pad-container">
                                                    <canvas id="signature-pad-1" class="signature-pad"></canvas>
                                                    <div class="signature-pad-actions">
                                                        <button type="button" class="btn btn-sm btn-secondary clear-signature" data-pad="1">Hapus</button>
                                                    </div>
                                                </div>
                                                <input type="hidden" name="counter1_signature" id="counter1_signature">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-4 mb-4">
                                        <div class="card h-100">
                                            <div class="card-header">Dihitung Oleh:</div>
                                            <div class="card-body">
                                                <div class="mb-3">
                                                    <input type="text" class="form-control" id="counter2_name" name="counter2_name" placeholder="Nama Penghitung 2">
                                                </div>
                                                <label class="form-label">Paraf:</label>
                                                <div class="signature-pad-container">
                                                    <canvas id="signature-pad-2" class="signature-pad"></canvas>
                                                    <div class="signature-pad-actions">
                                                        <button type="button" class="btn btn-sm btn-secondary clear-signature" data-pad="2">Hapus</button>
                                                    </div>
                                                </div>
                                                <input type="hidden" name="counter2_signature" id="counter2_signature">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-4 mb-4">
                                        <div class="card h-100">
                                            <div class="card-header">Dihitung Oleh:</div>
                                            <div class="card-body">
                                                <div class="mb-3">
                                                    <input type="text" class="form-control" id="counter3_name" name="counter3_name" placeholder="Nama Penghitung 3">
                                                </div>
                                                <label class="form-label">Paraf:</label>
                                                <div class="signature-pad-container">
                                                    <canvas id="signature-pad-3" class="signature-pad"></canvas>
                                                    <div class="signature-pad-actions">
                                                        <button type="button" class="btn btn-sm btn-secondary clear-signature" data-pad="3">Hapus</button>
                                                    </div>
                                                </div>
                                                <input type="hidden" name="counter3_signature" id="counter3_signature">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between mt-4">
                                <button type="button" class="btn btn-secondary btn-back" onclick="window.location.href='index.php'">
                                    <i class="bi bi-arrow-left"></i> Kembali
                                </button>
                                <div>
                                    <button type="button" class="btn btn-info me-2 btn-print" onclick="printFinanceForm()">
                                        <i class="bi bi-printer"></i> Cetak
                                    </button>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-save"></i> Simpan
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- History Tab -->
            <div class="tab-pane fade" id="history-tab-pane" role="tabpanel" aria-labelledby="history-tab" tabindex="0">
                <div class="card mt-3">
                    <div class="card-header bg-primary text-white">
                        <i class="bi bi-clock-history"></i> Riwayat Penerimaan Kolekte
                    </div>
                    <div class="card-body">
                        <?php if (count($recent_records) > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Tanggal</th>
                                            <th>Total</th>
                                            <th>Kehadiran</th>
                                            <th>Dibuat Oleh</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recent_records as $record): ?>
                                            <tr>
                                                <td><?php echo date('d M Y', strtotime($record['date'])); ?></td>
                                                <td>Rp <?php echo number_format($record['total_amount'], 0, ',', '.'); ?></td>
                                                <td><?php echo $record['total_attendance']; ?> orang</td>
                                                <td><?php echo htmlspecialchars($record['username']); ?></td>
                                                <td>
                                                    <div class="btn-group">
                                                        <a href="view_finance.php?id=<?php echo $record['id']; ?>" class="btn btn-sm btn-primary">
                                                            <i class="bi bi-eye"></i> Lihat
                                                        </a>
                                                        <a href="edit_finance.php?id=<?php echo $record['id']; ?>" class="btn btn-sm btn-warning">
                                                            <i class="bi bi-pencil"></i> Edit
                                                        </a>
                                                        <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal<?php echo $record['id']; ?>">
                                                            <i class="bi bi-trash"></i> Hapus
                                                        </button>
                                                    </div>

                                                    <!-- Delete Modal -->
                                                    <div class="modal fade" id="deleteModal<?php echo $record['id']; ?>" tabindex="-1" aria-labelledby="deleteModalLabel<?php echo $record['id']; ?>" aria-hidden="true">
                                                        <div class="modal-dialog">
                                                            <div class="modal-content">
                                                                <div class="modal-header">
                                                                    <h5 class="modal-title" id="deleteModalLabel<?php echo $record['id']; ?>">Konfirmasi Hapus</h5>
                                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                </div>
                                                                <div class="modal-body">
                                                                    Apakah Anda yakin ingin menghapus data penerimaan kolekte tanggal <strong><?php echo date('d M Y', strtotime($record['date'])); ?></strong>?
                                                                </div>
                                                                <div class="modal-footer">
                                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                                    <a href="delete_finance.php?id=<?php echo $record['id']; ?>" class="btn btn-danger">Hapus</a>
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
                                <i class="bi bi-info-circle"></i> Belum ada data penerimaan kolekte.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Print Template (Hidden) -->
        <div id="print-template" style="display: none;">
            <div class="print-container">
                <div class="print-header">
                    <img src="public/images/church-logo.png" alt="GKPI Logo" style="height: 80px; margin-bottom: 10px;">
                    <h2>GKPI GRIYA PERMATA</h2>
                    <p>Form Penerimaan Kolekte</p>
                </div>

                <div class="print-title">LAPORAN PENERIMAAN KOLEKTE</div>

                <div class="print-info">
                    <div class="print-info-row">
                        <div class="print-info-label">Tanggal:</div>
                        <div id="print-date"></div>
                    </div>
                </div>

                <table class="print-table">
                    <thead>
                        <tr>
                            <th width="5%">No</th>
                            <th width="60%">Jenis Persembahan</th>
                            <th width="35%">Jumlah</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>1</td>
                            <td>Persembahan Sekolah Minggu</td>
                            <td id="print-sunday-school"></td>
                        </tr>
                        <tr>
                            <td>2</td>
                            <td>Persembahan Kebaktian Umum</td>
                            <td id="print-general-service"></td>
                        </tr>
                        <tr>
                            <td>3</td>
                            <td>Persembahan Persepuluhan</td>
                            <td id="print-tithe"></td>
                        </tr>
                        <tr>
                            <td>4</td>
                            <td>Persembahan Diakonia</td>
                            <td id="print-diaconia"></td>
                        </tr>
                        <tr>
                            <td>5</td>
                            <td>Persembahan Lain-lain <span id="print-other-desc"></span></td>
                            <td id="print-other-offering"></td>
                        </tr>
                        <tr>
                            <td colspan="2" class="text-end"><strong>TOTAL</strong></td>
                            <td id="print-total"></td>
                        </tr>
                    </tbody>
                </table>

                <div class="print-info">
                    <div class="print-info-row">
                        <div class="print-info-label">Total Kehadiran:</div>
                        <div id="print-attendance"></div>
                    </div>
                </div>

                <div class="print-signatures">
                    <div class="print-signature">
                        <div class="print-signature-line" id="print-sig1"></div>
                        <div id="print-counter1"></div>
                        <div>Penghitung 1</div>
                    </div>
                    <div class="print-signature">
                        <div class="print-signature-line" id="print-sig2"></div>
                        <div id="print-counter2"></div>
                        <div>Penghitung 2</div>
                    </div>
                    <div class="print-signature">
                        <div class="print-signature-line" id="print-sig3"></div>
                        <div id="print-counter3"></div>
                        <div>Penghitung 3</div>
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
    <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize signature pads
            const signaturePad1 = new SignaturePad(document.getElementById('signature-pad-1'), {
                backgroundColor: 'rgba(255, 255, 255, 0)',
                penColor: 'black'
            });

            const signaturePad2 = new SignaturePad(document.getElementById('signature-pad-2'), {
                backgroundColor: 'rgba(255, 255, 255, 0)',
                penColor: 'black'
            });

            const signaturePad3 = new SignaturePad(document.getElementById('signature-pad-3'), {
                backgroundColor: 'rgba(255, 255, 255, 0)',
                penColor: 'black'
            });

            // Clear signature buttons
            document.querySelectorAll('.clear-signature').forEach(button => {
                button.addEventListener('click', function() {
                    const padNumber = this.getAttribute('data-pad');
                    if (padNumber === '1') {
                        signaturePad1.clear();
                        document.getElementById('counter1_signature').value = '';
                    } else if (padNumber === '2') {
                        signaturePad2.clear();
                        document.getElementById('counter2_signature').value = '';
                    } else if (padNumber === '3') {
                        signaturePad3.clear();
                        document.getElementById('counter3_signature').value = '';
                    }
                });
            });

            // Resize canvas
            function resizeCanvas() {
                const ratio = Math.max(window.devicePixelRatio || 1, 1);

                const canvas1 = document.getElementById('signature-pad-1');
                canvas1.width = canvas1.offsetWidth * ratio;
                canvas1.height = canvas1.offsetHeight * ratio;
                canvas1.getContext("2d").scale(ratio, ratio);
                signaturePad1.clear();

                const canvas2 = document.getElementById('signature-pad-2');
                canvas2.width = canvas2.offsetWidth * ratio;
                canvas2.height = canvas2.offsetHeight * ratio;
                canvas2.getContext("2d").scale(ratio, ratio);
                signaturePad2.clear();

                const canvas3 = document.getElementById('signature-pad-3');
                canvas3.width = canvas3.offsetWidth * ratio;
                canvas3.height = canvas3.offsetHeight * ratio;
                canvas3.getContext("2d").scale(ratio, ratio);
                signaturePad3.clear();
            }

            window.addEventListener("resize", resizeCanvas);
            resizeCanvas();

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
            document.getElementById('finance-form').addEventListener('submit', function(e) {
                // Save signature data to hidden inputs
                if (!signaturePad1.isEmpty()) {
                    document.getElementById('counter1_signature').value = signaturePad1.toDataURL();
                }

                if (!signaturePad2.isEmpty()) {
                    document.getElementById('counter2_signature').value = signaturePad2.toDataURL();
                }

                if (!signaturePad3.isEmpty()) {
                    document.getElementById('counter3_signature').value = signaturePad3.toDataURL();
                }

                return true;
            });

            // Make print function available globally
            window.printFinanceForm = function() {
                // Populate print template with form data
                document.getElementById('print-date').textContent = document.getElementById('date').value;
                document.getElementById('print-sunday-school').textContent = 'Rp ' + document.getElementById('sunday_school').value;
                document.getElementById('print-general-service').textContent = 'Rp ' + document.getElementById('general_service').value;
                document.getElementById('print-tithe').textContent = 'Rp ' + document.getElementById('tithe').value;
                document.getElementById('print-diaconia').textContent = 'Rp ' + document.getElementById('diaconia').value;

                const otherDesc = document.getElementById('other_offering_description').value;
                if (otherDesc) {
                    document.getElementById('print-other-desc').textContent = '(' + otherDesc + ')';
                } else {
                    document.getElementById('print-other-desc').textContent = '';
                }

                document.getElementById('print-other-offering').textContent = 'Rp ' + document.getElementById('other_offering').value;
                document.getElementById('print-total').textContent = 'Rp ' + document.getElementById('total_amount').value;
                document.getElementById('print-attendance').textContent = document.getElementById('total_attendance').value + ' orang';

                document.getElementById('print-counter1').textContent = document.getElementById('counter1_name').value;
                document.getElementById('print-counter2').textContent = document.getElementById('counter2_name').value;
                document.getElementById('print-counter3').textContent = document.getElementById('counter3_name').value;

                // Create a new window for printing
                const printWindow = window.open('', '_blank');
                printWindow.document.write('<html><head><title>Laporan Penerimaan Kolekte</title>');
                printWindow.document.write('<style>');
                printWindow.document.write(`
                    body { font-family: Arial, sans-serif; margin: 0; padding: 20px; }
                    .print-container { max-width: 800px; margin: 0 auto; }
                    .print-header { text-align: center; margin-bottom: 20px; }
                    .print-header img { height: 80px; margin-bottom: 10px; }
                    .print-title { text-align: center; font-size: 18px; font-weight: bold; margin-bottom: 20px; text-decoration: underline; }
                    .print-info { margin-bottom: 20px; }
                    .print-info-row { display: flex; margin-bottom: 5px; }
                    .print-info-label { width: 150px; font-weight: bold; }
                    .print-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
                    .print-table th, .print-table td { border: 1px solid #000; padding: 8px; }
                    .print-table th { background-color: #f0f0f0; }
                    .print-signatures { display: flex; justify-content: space-between; margin-top: 50px; }
                    .print-signature { text-align: center; width: 30%; }
                    .print-signature-line { border-bottom: 1px solid #000; margin-bottom: 5px; height: 60px; }
                    .text-end { text-align: right; }
                `);
                printWindow.document.write('</style></head><body>');
                printWindow.document.write(document.getElementById('print-template').innerHTML);
                printWindow.document.write('</body></html>');

                // Add signatures if available
                if (!signaturePad1.isEmpty()) {
                    const sig1Img = document.createElement('img');
                    sig1Img.src = signaturePad1.toDataURL();
                    sig1Img.style.maxWidth = '100%';
                    sig1Img.style.maxHeight = '60px';
                    printWindow.document.getElementById('print-sig1').innerHTML = '';
                    printWindow.document.getElementById('print-sig1').appendChild(sig1Img);
                }

                if (!signaturePad2.isEmpty()) {
                    const sig2Img = document.createElement('img');
                    sig2Img.src = signaturePad2.toDataURL();
                    sig2Img.style.maxWidth = '100%';
                    sig2Img.style.maxHeight = '60px';
                    printWindow.document.getElementById('print-sig2').innerHTML = '';
                    printWindow.document.getElementById('print-sig2').appendChild(sig2Img);
                }

                if (!signaturePad3.isEmpty()) {
                    const sig3Img = document.createElement('img');
                    sig3Img.src = signaturePad3.toDataURL();
                    sig3Img.style.maxWidth = '100%';
                    sig3Img.style.maxHeight = '60px';
                    printWindow.document.getElementById('print-sig3').innerHTML = '';
                    printWindow.document.getElementById('print-sig3').appendChild(sig3Img);
                }

                printWindow.document.close();
                printWindow.focus();

                // Print after a short delay to ensure content is loaded
                setTimeout(function() {
                    printWindow.print();
                }, 500);
            };
        });
    </script>
</body>

</html>