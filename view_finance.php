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

$finance_id = $_GET['id'];

// Get finance record
$sql = "SELECT f.*, u.username FROM finance_collection f 
        JOIN users u ON f.created_by = u.id 
        WHERE f.id = $finance_id";
$result = $conn->query($sql);

if ($result->num_rows == 0) {
    header("Location: finance.php");
    exit;
}

$finance = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Penerimaan Kolekte - GKPI Griya Permata</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
    <link rel="icon" href="public/images/church-logo.png" type="image/png">
    <style>
        .signature-container {
            max-width: 200px;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 10px;
            background-color: #fff;
            margin-bottom: 15px;
        }

        .signature-image {
            max-width: 100%;
            height: auto;
        }

        .finance-detail-label {
            font-weight: 500;
        }

        .finance-detail-value {
            font-weight: 400;
        }

        .finance-total {
            font-weight: 600;
            font-size: 1.1rem;
        }

        @media print {

            .navbar,
            .footer,
            .btn-print,
            .btn-back,
            .btn-edit,
            .btn-delete {
                display: none !important;
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
            }

            .print-title span {
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
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="bi bi-cash-coin"></i> Detail Penerimaan Kolekte</h2>
            <div>
                <a href="finance.php" class="btn btn-secondary btn-back">
                    <i class="bi bi-arrow-left"></i> Kembali
                </a>
                <button type="button" class="btn btn-info btn-print" onclick="printFinanceDetail()">
                    <i class="bi bi-printer"></i> Cetak
                </button>
                <a href="edit_finance.php?id=<?php echo $finance_id; ?>" class="btn btn-warning btn-edit">
                    <i class="bi bi-pencil"></i> Edit
                </a>
                <button type="button" class="btn btn-danger btn-delete" data-bs-toggle="modal" data-bs-target="#deleteModal">
                    <i class="bi bi-trash"></i> Hapus
                </button>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="print-header" style="display: none;">
                    <img src="public/images/church-logo.png" alt="GKPI Logo" style="height: 80px; margin-bottom: 10px;">
                    <h2>GKPI GRIYA PERMATA</h2>
                    <p>Laporan Penerimaan Kolekte</p>
                </div>

                <div class="print-title" style="display: none;"><span>LAPORAN PENERIMAAN KOLEKTE</span></div>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td class="finance-detail-label">Tanggal</td>
                                <td>:</td>
                                <td class="finance-detail-value"><?php echo date('d M Y', strtotime($finance['date'])); ?></td>
                            </tr>
                            <tr>
                                <td class="finance-detail-label">Dibuat Oleh</td>
                                <td>:</td>
                                <td class="finance-detail-value"><?php echo htmlspecialchars($finance['username']); ?></td>
                            </tr>
                            <tr>
                                <td class="finance-detail-label">Waktu Dibuat</td>
                                <td>:</td>
                                <td class="finance-detail-value"><?php echo date('d M Y H:i', strtotime($finance['created_at'])); ?></td>
                            </tr>
                            <tr>
                                <td class="finance-detail-label">Total Kehadiran</td>
                                <td>:</td>
                                <td class="finance-detail-value"><?php echo $finance['total_attendance']; ?> orang</td>
                            </tr>
                        </table>
                    </div>
                </div>

                <div class="table-responsive mb-4">
                    <table class="table table-bordered">
                        <thead class="table-light">
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
                                <td>Rp <?php echo number_format($finance['sunday_school'], 0, ',', '.'); ?></td>
                            </tr>
                            <tr>
                                <td>2</td>
                                <td>Persembahan Kebaktian Umum</td>
                                <td>Rp <?php echo number_format($finance['general_service'], 0, ',', '.'); ?></td>
                            </tr>
                            <tr>
                                <td>3</td>
                                <td>Persembahan Persepuluhan</td>
                                <td>Rp <?php echo number_format($finance['tithe'], 0, ',', '.'); ?></td>
                            </tr>
                            <tr>
                                <td>4</td>
                                <td>Persembahan Diakonia</td>
                                <td>Rp <?php echo number_format($finance['diaconia'], 0, ',', '.'); ?></td>
                            </tr>
                            <tr>
                                <td>5</td>
                                <td>
                                    Persembahan Lain-lain
                                    <?php if (!empty($finance['other_offering_description'])): ?>
                                        (<?php echo htmlspecialchars($finance['other_offering_description']); ?>)
                                    <?php endif; ?>
                                </td>
                                <td>Rp <?php echo number_format($finance['other_offering'], 0, ',', '.'); ?></td>
                            </tr>
                            <tr>
                                <td colspan="2" class="text-end finance-total">TOTAL</td>
                                <td class="finance-total">Rp <?php echo number_format($finance['total_amount'], 0, ',', '.'); ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="row mb-4">
                    <div class="col-md-12">
                        <h5 class="mb-3">Tanda Tangan Penghitung</h5>
                        <div class="row">
                            <?php if (!empty($finance['counter1_name']) || !empty($finance['counter1_signature'])): ?>
                                <div class="col-md-4 mb-3">
                                    <div class="card">
                                        <div class="card-header">Penghitung 1</div>
                                        <div class="card-body text-center">
                                            <?php if (!empty($finance['counter1_signature'])): ?>
                                                <div class="signature-container mx-auto">
                                                    <img src="<?php echo $finance['counter1_signature']; ?>" alt="Tanda Tangan" class="signature-image">
                                                </div>
                                            <?php endif; ?>
                                            <p><?php echo htmlspecialchars($finance['counter1_name']); ?></p>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($finance['counter2_name']) || !empty($finance['counter2_signature'])): ?>
                                <div class="col-md-4 mb-3">
                                    <div class="card">
                                        <div class="card-header">Penghitung 2</div>
                                        <div class="card-body text-center">
                                            <?php if (!empty($finance['counter2_signature'])): ?>
                                                <div class="signature-container mx-auto">
                                                    <img src="<?php echo $finance['counter2_signature']; ?>" alt="Tanda Tangan" class="signature-image">
                                                </div>
                                            <?php endif; ?>
                                            <p><?php echo htmlspecialchars($finance['counter2_name']); ?></p>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($finance['counter3_name']) || !empty($finance['counter3_signature'])): ?>
                                <div class="col-md-4 mb-3">
                                    <div class="card">
                                        <div class="card-header">Penghitung 3</div>
                                        <div class="card-body text-center">
                                            <?php if (!empty($finance['counter3_signature'])): ?>
                                                <div class="signature-container mx-auto">
                                                    <img src="<?php echo $finance['counter3_signature']; ?>" alt="Tanda Tangan" class="signature-image">
                                                </div>
                                            <?php endif; ?>
                                            <p><?php echo htmlspecialchars($finance['counter3_name']); ?></p>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">Konfirmasi Hapus</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Apakah Anda yakin ingin menghapus data penerimaan kolekte tanggal <strong><?php echo date('d M Y', strtotime($finance['date'])); ?></strong>?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <a href="delete_finance.php?id=<?php echo $finance_id; ?>" class="btn btn-danger">Hapus</a>
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
                <p>Laporan Penerimaan Kolekte</p>
            </div>

            <div class="print-title"><span>LAPORAN PENERIMAAN KOLEKTE</span></div>

            <div class="print-info">
                <div class="print-info-row">
                    <div class="print-info-label">Tanggal:</div>
                    <div id="print-date"><?php echo date('d M Y', strtotime($finance['date'])); ?></div>
                </div>
                <div class="print-info-row">
                    <div class="print-info-label">Total Kehadiran:</div>
                    <div id="print-attendance"><?php echo $finance['total_attendance']; ?> orang</div>
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
                        <td id="print-sunday-school">Rp <?php echo number_format($finance['sunday_school'], 0, ',', '.'); ?></td>
                    </tr>
                    <tr>
                        <td>2</td>
                        <td>Persembahan Kebaktian Umum</td>
                        <td id="print-general-service">Rp <?php echo number_format($finance['general_service'], 0, ',', '.'); ?></td>
                    </tr>
                    <tr>
                        <td>3</td>
                        <td>Persembahan Persepuluhan</td>
                        <td id="print-tithe">Rp <?php echo number_format($finance['tithe'], 0, ',', '.'); ?></td>
                    </tr>
                    <tr>
                        <td>4</td>
                        <td>Persembahan Diakonia</td>
                        <td id="print-diaconia">Rp <?php echo number_format($finance['diaconia'], 0, ',', '.'); ?></td>
                    </tr>
                    <tr>
                        <td>5</td>
                        <td>Persembahan Lain-lain <span id="print-other-desc">
                                <?php if (!empty($finance['other_offering_description'])): ?>
                                    (<?php echo htmlspecialchars($finance['other_offering_description']); ?>)
                                <?php endif; ?>
                            </span></td>
                        <td id="print-other-offering">Rp <?php echo number_format($finance['other_offering'], 0, ',', '.'); ?></td>
                    </tr>
                    <tr>
                        <td colspan="2" class="text-end"><strong>TOTAL</strong></td>
                        <td id="print-total">Rp <?php echo number_format($finance['total_amount'], 0, ',', '.'); ?></td>
                    </tr>
                </tbody>
            </table>

            <div class="print-signatures">
                <?php if (!empty($finance['counter1_name']) || !empty($finance['counter1_signature'])): ?>
                    <div class="print-signature">
                        <div class="print-signature-line" id="print-sig1">
                            <?php if (!empty($finance['counter1_signature'])): ?>
                                <img src="<?php echo $finance['counter1_signature']; ?>" alt="Tanda Tangan" style="max-width: 100%; max-height: 60px;">
                            <?php endif; ?>
                        </div>
                        <div id="print-counter1"><?php echo htmlspecialchars($finance['counter1_name']); ?></div>
                        <div>Penghitung 1</div>
                    </div>
                <?php else: ?>
                    <div class="print-signature">
                        <div class="print-signature-line"></div>
                        <div>&nbsp;</div>
                        <div>Penghitung 1</div>
                    </div>
                <?php endif; ?>

                <?php if (!empty($finance['counter2_name']) || !empty($finance['counter2_signature'])): ?>
                    <div class="print-signature">
                        <div class="print-signature-line" id="print-sig2">
                            <?php if (!empty($finance['counter2_signature'])): ?>
                                <img src="<?php echo $finance['counter2_signature']; ?>" alt="Tanda Tangan" style="max-width: 100%; max-height: 60px;">
                            <?php endif; ?>
                        </div>
                        <div id="print-counter2"><?php echo htmlspecialchars($finance['counter2_name']); ?></div>
                        <div>Penghitung 2</div>
                    </div>
                <?php else: ?>
                    <div class="print-signature">
                        <div class="print-signature-line"></div>
                        <div>&nbsp;</div>
                        <div>Penghitung 2</div>
                    </div>
                <?php endif; ?>

                <?php if (!empty($finance['counter3_name']) || !empty($finance['counter3_signature'])): ?>
                    <div class="print-signature">
                        <div class="print-signature-line" id="print-sig3">
                            <?php if (!empty($finance['counter3_signature'])): ?>
                                <img src="<?php echo $finance['counter3_signature']; ?>" alt="Tanda Tangan" style="max-width: 100%; max-height: 60px;">
                            <?php endif; ?>
                        </div>
                        <div id="print-counter3"><?php echo htmlspecialchars($finance['counter3_name']); ?></div>
                        <div>Penghitung 3</div>
                    </div>
                <?php else: ?>
                    <div class="print-signature">
                        <div class="print-signature-line"></div>
                        <div>&nbsp;</div>
                        <div>Penghitung 3</div>
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
    <script>
        // Debug function to check signature data
        function checkSignatures() {
            console.log("Signature 1:", <?php echo json_encode($finance['counter1_signature']); ?>);
            console.log("Signature 2:", <?php echo json_encode($finance['counter2_signature']); ?>);
            console.log("Signature 3:", <?php echo json_encode($finance['counter3_signature']); ?>);
        }

        // Run debug on page load
        window.onload = function() {
            checkSignatures();
        };

        function printFinanceDetail() {
            // Create a new window for printing
            const printWindow = window.open('', '_blank');
            printWindow.document.write('<html><head><title>Laporan Penerimaan Kolekte</title>');
            printWindow.document.write('<style>');
            printWindow.document.write(`
                body { font-family: Arial, sans-serif; margin: 0; padding: 20px; }
                .print-container { max-width: 800px; margin: 0 auto; }
                .print-header { text-align: center; margin-bottom: 20px; }
                .print-header img { height: 80px; margin-bottom: 10px; }
                .print-title { text-align: center; font-size: 18px; font-weight: bold; margin-bottom: 20px; }
                .print-title span { text-decoration: underline; }
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

            printWindow.document.close();
            printWindow.focus();

            // Print after a short delay to ensure content is loaded
            setTimeout(function() {
                printWindow.print();
            }, 500);
        }
    </script>
</body>

</html>