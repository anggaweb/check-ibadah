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

// Definisi subcategories untuk tampilan dan pemrosesan
$subcategories = [
    "Ruang Penyambutan Lantai Dasar" => [
        "Meja penyambutan bersih dan rapi.",
        "Amplop Persembahan sudah dipersiapkan (perpuluhan / diakon)",
        "Petugas ushers sudah stand-by."
    ],
    "Ruang Ibadah" => [
        "Apakah seluruh area sudah bersih (lantai 1 dan 2).",
        "Nyalakan AC sesuai kebutuhan (lantai 1 & 2).",
        "TV berfungsi dengan baik."
    ],
    "Streaming Setup" => [
        "Pastikan Kamera Streaming dengan baterai full.",
        "Pastikan Komputer Streaming menyala.",
        "Software streaming sudah siap dijalankan.",
        "Link streaming diuji 15 menit sebelum ibadah mulai.",
        "File Video Warta Jemaat sudah tersedia di Komputer Streaming."
    ],
    "Sound System" => [
        "Microfon hidup dan baterai cukup.",
        "Semua speaker monitor berfungsi.",
        "Semua alat musik siap digunakan."
    ],
    "Multimedia" => [
        "Pastikan Easy Worship script sudah ter-update.",
        "File Video Warta Jemaat sudah tersedia di Laptop Multimedia."
    ],
    "Petugas" => [
        "Pengkhotbah sudah hadir.",
        "WL sudah hadir.",
        "Singer sudah hadir.",
        "Pemusik sudah hadir.",
        "Multimedia sudah hadir.",
        "Streaming sudah hadir.",
        "Soundman sudah hadir."
    ]
];

// Define sub-items for special items
$special_items = [
    "Semua alat musik siap digunakan." => [
        "checksound Keyboard.",
        "checksound Gitar Listrik.",
        "checksound Bass.",
        "checksound Drum."
    ],
    "Pastikan Easy Worship script sudah ter-update." => [
        "sudah berisi lagu-lagu dari WL.",
        "sudah ada Bacaan Bertanggapan.",
        "sudah ada Doa Syafaat.",
        "konfirmasi urutan ibadah ke WL",
        "sudah ada Bahan Khotbah."
    ]
];

// Process form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $date = $_POST['date'];
    $signature = isset($_POST['signature_data']) ? $_POST['signature_data'] : '';
    $responsible_person = isset($_POST['responsible_person']) ? $_POST['responsible_person'] : '';

    // Insert checklist
    $user_id = $_SESSION['user_id'];
    $status = 'active'; // Default status is now 'active'

    // Escape strings to prevent SQL injection
    $title = $conn->real_escape_string($title);
    $date = $conn->real_escape_string($date);
    $status = $conn->real_escape_string($status);
    $signature = $conn->real_escape_string($signature);
    $responsible_person = $conn->real_escape_string($responsible_person);

    $sql = "INSERT INTO checklists (user_id, title, date, status, signature, responsible_person) 
            VALUES ('$user_id', '$title', '$date', '$status', '$signature', '$responsible_person')";

    if ($conn->query($sql)) {
        $checklist_id = $conn->insert_id;

        // Process H-2 s/d H-1 items
        $category1 = "H-2 s/d H-1 Sebelum Ibadah";
        $items1 = [
            "Anggota Tim Pelayan sudah fixed ?",
            "Check Alat musik saat latihan.",
            "Konsumsi Tersedia ?",
            "Ada Perjamuan Kudus ?",
            "Konfirmasi tema ibadah dan materi presentasi ke tim multimedia.",
            "Pembuatan Thumbnail untuk Streaming.",
            "Microfon hidup dan baterai cukup.",
            "Pembuatan Video Warta Jemaat."
        ];

        // Sub-items for "Check Alat musik saat latihan."
        $alat_musik_subitems = [
            "checksound Keyboard.",
            "checksound Gitar Listrik.",
            "checksound Bass.",
            "checksound Drum."
        ];

        // Sub-items for "Ada Perjamuan Kudus?"
        $perjamuan_kudus_subitems = [
            "Anggur dan roti sudah disiapkan.",
            "Petugas Perjamuan Kudus Sudah Fix ?"
        ];

        // Process main items
        foreach ($items1 as $item) {
            $item_id = md5($item);
            $status_val = isset($_POST['item_' . $item_id]) ? 1 : 0;
            $notes = isset($_POST['notes_' . $item_id]) ? $conn->real_escape_string($_POST['notes_' . $item_id]) : '';

            $is_parent = ($item == "Ada Perjamuan Kudus ?" || $item == "Check Alat musik saat latihan.") ? 1 : 0;

            $sql = "INSERT INTO checklist_items (checklist_id, category, item_name, status, notes, is_parent) 
                    VALUES ('$checklist_id', '$category1', '$item', '$status_val', '$notes', '$is_parent')";
            $conn->query($sql);

            // If this is "Ada Perjamuan Kudus?" and it's checked, add sub-items
            if ($item == "Ada Perjamuan Kudus ?" && $status_val == 1) {
                $parent_id = $conn->insert_id;

                foreach ($perjamuan_kudus_subitems as $subitem) {
                    $subitem_id = md5($item . $subitem);
                    $subitem_status = isset($_POST['subitem_' . $subitem_id]) ? 1 : 0;
                    $subitem_notes = isset($_POST['notes_subitem_' . $subitem_id]) ? $conn->real_escape_string($_POST['notes_subitem_' . $subitem_id]) : '';

                    $sql = "INSERT INTO checklist_items (checklist_id, category, item_name, status, notes, parent_id) 
                            VALUES ('$checklist_id', '$category1', '$subitem', '$subitem_status', '$subitem_notes', '$parent_id')";
                    $conn->query($sql);
                }
            }

            // If this is "Check Alat musik saat latihan." and it's checked, add sub-items
            if ($item == "Check Alat musik saat latihan." && $status_val == 1) {
                $parent_id = $conn->insert_id;

                foreach ($alat_musik_subitems as $subitem) {
                    $subitem_id = md5($item . $subitem);
                    $subitem_status = isset($_POST['subitem_' . $subitem_id]) ? 1 : 0;
                    $subitem_notes = isset($_POST['notes_subitem_' . $subitem_id]) ? $conn->real_escape_string($_POST['notes_subitem_' . $subitem_id]) : '';

                    $sql = "INSERT INTO checklist_items (checklist_id, category, item_name, status, notes, parent_id) 
                            VALUES ('$checklist_id', '$category1', '$subitem', '$subitem_status', '$subitem_notes', '$parent_id')";
                    $conn->query($sql);
                }
            }
        }

        // Process Hari-H items with subcategories
        foreach ($subcategories as $subcategory => $subcategory_items) {
            $full_category = "Hari-H Sebelum Ibadah - " . $subcategory;

            foreach ($subcategory_items as $item) {
                $item_id = md5($item);
                $status_val = isset($_POST['item_' . $item_id]) ? 1 : 0;
                $notes = isset($_POST['notes_' . $item_id]) ? $conn->real_escape_string($_POST['notes_' . $item_id]) : '';

                // Check if this item has sub-items
                $is_parent = isset($special_items[$item]) ? 1 : 0;

                $sql = "INSERT INTO checklist_items (checklist_id, category, item_name, status, notes, is_parent) 
                        VALUES ('$checklist_id', '$full_category', '$item', '$status_val', '$notes', '$is_parent')";
                $conn->query($sql);

                // If this item has sub-items and is checked, add them
                if (isset($special_items[$item]) && $status_val == 1) {
                    $parent_id = $conn->insert_id;

                    foreach ($special_items[$item] as $subitem) {
                        $subitem_id = md5($item . $subitem);
                        $subitem_status = isset($_POST['subitem_' . $subitem_id]) ? 1 : 0;
                        $subitem_notes = isset($_POST['notes_subitem_' . $subitem_id]) ? $conn->real_escape_string($_POST['notes_subitem_' . $subitem_id]) : '';

                        $sql = "INSERT INTO checklist_items (checklist_id, category, item_name, status, notes, parent_id) 
                                VALUES ('$checklist_id', '$full_category', '$subitem', '$subitem_status', '$subitem_notes', '$parent_id')";
                        $conn->query($sql);
                    }
                }
            }
        }

        // Add remark if provided
        if (!empty($_POST['remark'])) {
            $remark = $conn->real_escape_string($_POST['remark']);
            $sql = "UPDATE checklists SET remark = '$remark' WHERE id = '$checklist_id'";
            $conn->query($sql);
        }

        $success = "Checklist berhasil disimpan!";
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
    <title>Checklist Baru - GKPI Griya Permata</title>
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
            display: none;
        }

        .signature-pad-container {
            border: 1px solid #dee2e6;
            border-radius: 10px;
            overflow: hidden;
            margin-bottom: 15px;
        }

        #signature-pad {
            width: 100%;
            height: 200px;
            background-color: #fff;
        }

        .signature-pad-actions {
            display: flex;
            justify-content: flex-end;
            padding: 10px;
            background-color: #f8f9fa;
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

        .notes-toggle {
            color: #3a5a78;
            cursor: pointer;
        }

        .notes-container {
            display: none;
            margin-top: 8px;
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

        /* Section styling */
        .section-title {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 1rem;
            color: #3a5a78;
            border-bottom: 2px solid #e9b872;
            padding-bottom: 0.5rem;
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

        <div class="card">
            <div class="card-header">
                <i class="bi bi-clipboard-plus"></i> Checklist Persiapan Ibadah Baru
            </div>
            <div class="card-body">
                <form method="post" action="" id="checklist-form">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="title" class="form-label">Judul Checklist</label>
                                <input type="text" class="form-control" id="title" name="title" required placeholder="Contoh: Persiapan Ibadah Minggu 24 April 2023">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="date" class="form-label">Tanggal Ibadah</label>
                                <input type="date" class="form-control" id="date" name="date" required>
                            </div>
                        </div>
                    </div>

                    <!-- H-2 s/d H-1 Checklist -->
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
                                        $items1 = [
                                            "Anggota Tim Pelayan sudah fixed ?",
                                            "Check Alat musik saat latihan.",
                                            "Konsumsi Tersedia ?",
                                            "Ada Perjamuan Kudus ?",
                                            "Konfirmasi tema ibadah dan materi presentasi ke tim multimedia.",
                                            "Pembuatan Thumbnail untuk Streaming.",
                                            "Microfon hidup dan baterai cukup.",
                                            "Pembuatan Video Warta Jemaat."
                                        ];

                                        // Sub-items for "Check Alat musik saat latihan."
                                        $alat_musik_subitems = [
                                            "checksound Keyboard.",
                                            "checksound Gitar Listrik.",
                                            "checksound Bass.",
                                            "checksound Drum."
                                        ];

                                        // Sub-items for "Ada Perjamuan Kudus?"
                                        $perjamuan_kudus_subitems = [
                                            "Anggur dan roti sudah disiapkan.",
                                            "Petugas Perjamuan Kudus Sudah Fix ?"
                                        ];

                                        $i = 1;
                                        foreach ($items1 as $item) {
                                            $item_id = md5($item);
                                            echo '<tr>';
                                            echo '<td>' . $i++ . '</td>';
                                            echo '<td>' . htmlspecialchars($item) . '</td>';
                                            echo '<td>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="item_' . $item_id . '" id="item_' . $item_id . '"' . ($item == "Ada Perjamuan Kudus ?" || $item == "Check Alat musik saat latihan." ? ' onclick="toggleSubitems(\'' . $item_id . '\')"' : '') . '>
                                                    <label class="form-check-label" for="item_' . $item_id . '">Selesai</label>
                                                </div>
                                            </td>';
                                            echo '<td>
                                                <span class="notes-toggle" onclick="toggleNotes(\'' . $item_id . '\')"><i class="bi bi-pencil-square"></i> Catatan</span>
                                                <div id="notes_container_' . $item_id . '" class="notes-container">
                                                    <textarea class="form-control" name="notes_' . $item_id . '" rows="2" placeholder="Tambahkan catatan..."></textarea>
                                                </div>
                                            </td>';
                                            echo '</tr>';

                                            // Add sub-items for "Check Alat musik saat latihan."
                                            if ($item == "Check Alat musik saat latihan.") {
                                                echo '<tr id="subitems_' . $item_id . '" style="display: none;">';
                                                echo '<td colspan="4">';
                                                echo '<div class="subitem-container">';
                                                echo '<table class="table table-sm">';
                                                echo '<thead><tr><th width="5%">#</th><th width="55%">Sub Item</th><th width="20%">Status</th><th width="20%">Catatan</th></tr></thead>';
                                                echo '<tbody>';

                                                $j = 1;
                                                foreach ($alat_musik_subitems as $subitem) {
                                                    $subitem_id = md5($item . $subitem);
                                                    echo '<tr>';
                                                    echo '<td>' . $j++ . '</td>';
                                                    echo '<td>' . htmlspecialchars($subitem) . '</td>';
                                                    echo '<td>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" name="subitem_' . $subitem_id . '" id="subitem_' . $subitem_id . '">
                                                            <label class="form-check-label" for="subitem_' . $subitem_id . '">Selesai</label>
                                                        </div>
                                                    </td>';
                                                    echo '<td>
                                                        <span class="notes-toggle" onclick="toggleNotes(\'subitem_' . $subitem_id . '\')"><i class="bi bi-pencil-square"></i> Catatan</span>
                                                        <div id="notes_container_subitem_' . $subitem_id . '" class="notes-container">
                                                            <textarea class="form-control" name="notes_subitem_' . $subitem_id . '" rows="2" placeholder="Tambahkan catatan..."></textarea>
                                                        </div>
                                                    </td>';
                                                    echo '</tr>';
                                                }

                                                echo '</tbody>';
                                                echo '</table>';
                                                echo '</div>';
                                                echo '</td>';
                                                echo '</tr>';
                                            }

                                            // Add sub-items for "Ada Perjamuan Kudus?"
                                            if ($item == "Ada Perjamuan Kudus ?") {
                                                echo '<tr id="subitems_' . $item_id . '" style="display: none;">';
                                                echo '<td colspan="4">';
                                                echo '<div class="subitem-container">';
                                                echo '<table class="table table-sm">';
                                                echo '<thead><tr><th width="5%">#</th><th width="55%">Sub Item</th><th width="20%">Status</th><th width="20%">Catatan</th></tr></thead>';
                                                echo '<tbody>';

                                                $j = 1;
                                                foreach ($perjamuan_kudus_subitems as $subitem) {
                                                    $subitem_id = md5($item . $subitem);
                                                    echo '<tr>';
                                                    echo '<td>' . $j++ . '</td>';
                                                    echo '<td>' . htmlspecialchars($subitem) . '</td>';
                                                    echo '<td>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" name="subitem_' . $subitem_id . '" id="subitem_' . $subitem_id . '">
                                                            <label class="form-check-label" for="subitem_' . $subitem_id . '">Selesai</label>
                                                        </div>
                                                    </td>';
                                                    echo '<td>
                                                        <span class="notes-toggle" onclick="toggleNotes(\'subitem_' . $subitem_id . '\')"><i class="bi bi-pencil-square"></i> Catatan</span>
                                                        <div id="notes_container_subitem_' . $subitem_id . '" class="notes-container">
                                                            <textarea class="form-control" name="notes_subitem_' . $subitem_id . '" rows="2" placeholder="Tambahkan catatan..."></textarea>
                                                        </div>
                                                    </td>';
                                                    echo '</tr>';
                                                }

                                                echo '</tbody>';
                                                echo '</table>';
                                                echo '</div>';
                                                echo '</td>';
                                                echo '</tr>';
                                            }
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Hari-H Checklist -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <i class="bi bi-calendar2-event"></i> Hari-H Sebelum Ibadah
                        </div>
                        <div class="card-body">
                            <?php foreach ($subcategories as $subcategory => $subcategory_items): ?>
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
                                            foreach ($subcategory_items as $item) {
                                                $item_id = md5($item);
                                                $has_subitems = isset($special_items[$item]);

                                                echo '<tr>';
                                                echo '<td>' . $i++ . '</td>';
                                                echo '<td>' . htmlspecialchars($item) . '</td>';
                                                echo '<td>
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" name="item_' . $item_id . '" id="item_' . $item_id . '"' . ($has_subitems ? ' onclick="toggleSubitems(\'' . $item_id . '\')"' : '') . '>
                                                        <label class="form-check-label" for="item_' . $item_id . '">Selesai</label>
                                                    </div>
                                                </td>';
                                                echo '<td>
                                                    <span class="notes-toggle" onclick="toggleNotes(\'' . $item_id . '\')"><i class="bi bi-pencil-square"></i> Catatan</span>
                                                    <div id="notes_container_' . $item_id . '" class="notes-container">
                                                        <textarea class="form-control" name="notes_' . $item_id . '" rows="2" placeholder="Tambahkan catatan..."></textarea>
                                                    </div>
                                                </td>';
                                                echo '</tr>';

                                                // Add sub-items if this item has them
                                                if ($has_subitems) {
                                                    echo '<tr id="subitems_' . $item_id . '" style="display: none;">';
                                                    echo '<td colspan="4">';
                                                    echo '<div class="subitem-container">';
                                                    echo '<table class="table table-sm">';
                                                    echo '<thead><tr><th width="5%">#</th><th width="55%">Sub Item</th><th width="20%">Status</th><th width="20%">Catatan</th></tr></thead>';
                                                    echo '<tbody>';

                                                    $j = 1;
                                                    foreach ($special_items[$item] as $subitem) {
                                                        $subitem_id = md5($item . $subitem);
                                                        echo '<tr>';
                                                        echo '<td>' . $j++ . '</td>';
                                                        echo '<td>' . htmlspecialchars($subitem) . '</td>';
                                                        echo '<td>
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="checkbox" name="subitem_' . $subitem_id . '" id="subitem_' . $subitem_id . '">
                                                                <label class="form-check-label" for="subitem_' . $subitem_id . '">Selesai</label>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <span class="notes-toggle" onclick="toggleNotes(\'subitem_' . $subitem_id . '\')"><i class="bi bi-pencil-square"></i> Catatan</span>
                                                            <div id="notes_container_subitem_' . $subitem_id . '" class="notes-container">
                                                                <textarea class="form-control" name="notes_subitem_' . $subitem_id . '" rows="2" placeholder="Tambahkan catatan..."></textarea>
                                                            </div>
                                                        </td>
                                                        </tr>
                                                        '; ?>
                                            <?php
                                                    }

                                                    echo '</tbody>';
                                                    echo '</table>';
                                                    echo '</div>';
                                                    echo '</td>';
                                                    echo '</tr>';
                                                }
                                            }
                                            ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Remark -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <i class="bi bi-chat-left-text"></i> Catatan Tambahan
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="remark" class="form-label">Catatan Tambahan</label>
                                <textarea class="form-control" id="remark" name="remark" rows="3" placeholder="Tambahkan catatan tambahan di sini..."></textarea>
                                <small class="text-muted">Catatan ini akan disimpan terpisah dari komentar diskusi.</small>
                            </div>
                        </div>
                    </div>

                    <!-- Signature Section -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <i class="bi bi-pen"></i> Tanda Tangan Penanggung Jawab
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="responsible_person" class="form-label">Nama Penanggung Jawab</label>
                                <input type="text" class="form-control" id="responsible_person" name="responsible_person" placeholder="Masukkan nama penanggung jawab">
                            </div>

                            <label class="form-label">Tanda Tangan</label>
                            <div class="signature-pad-container">
                                <canvas id="signature-pad" class="signature-pad"></canvas>
                                <div class="signature-pad-actions">
                                    <button type="button" class="btn btn-sm btn-secondary" id="clear-signature">Hapus</button>
                                </div>
                            </div>
                            <input type="hidden" name="signature_data" id="signature_data">

                            <div class="alert alert-info">
                                <i class="bi bi-info-circle"></i> Tanda tangan diperlukan sebagai bukti bahwa checklist ini diisi oleh penanggung jawab yang berwenang.
                            </div>
                        </div>
                    </div>

                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <a href="index.php" class="btn btn-secondary me-2"><i class="bi bi-x-circle"></i> Batal</a>
                        <button type="submit" class="btn btn-primary" id="submit-form"><i class="bi bi-save"></i> Simpan Checklist</button>
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
        // Toggle notes container
        function toggleNotes(itemId) {
            const notesContainer = document.getElementById('notes_container_' + itemId);
            if (notesContainer.style.display === 'block') {
                notesContainer.style.display = 'none';
            } else {
                notesContainer.style.display = 'block';
            }
        }

        // Toggle sub-items
        function toggleSubitems(itemId) {
            const checkbox = document.getElementById('item_' + itemId);
            const subitemsContainer = document.getElementById('subitems_' + itemId);

            if (checkbox.checked) {
                subitemsContainer.style.display = '';
                document.querySelector('#subitems_' + itemId + ' .subitem-container').style.display = 'block';
            } else {
                subitemsContainer.style.display = 'none';
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            // Initialize signature pad
            const canvas = document.getElementById('signature-pad');
            const signaturePad = new SignaturePad(canvas, {
                backgroundColor: 'rgba(255, 255, 255, 0)',
                penColor: 'black'
            });

            // Clear signature button
            document.getElementById('clear-signature').addEventListener('click', function() {
                signaturePad.clear();
                document.getElementById('signature_data').value = '';
            });

            // Resize canvas
            function resizeCanvas() {
                const ratio = Math.max(window.devicePixelRatio || 1, 1);
                canvas.width = canvas.offsetWidth * ratio;
                canvas.height = canvas.offsetHeight * ratio;
                canvas.getContext("2d").scale(ratio, ratio);
                signaturePad.clear(); // Otherwise isEmpty() might return incorrect value
            }

            window.addEventListener("resize", resizeCanvas);
            resizeCanvas();

            // Form submission
            document.getElementById('checklist-form').addEventListener('submit', function(e) {
                // Save signature data to hidden input if not empty
                if (!signaturePad.isEmpty()) {
                    const signatureData = signaturePad.toDataURL();
                    document.getElementById('signature_data').value = signatureData;
                }

                return true;
            });
        });
    </script>
</body>

</html>