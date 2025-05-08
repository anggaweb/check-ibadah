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
$sql = "SELECT * FROM checklists WHERE id = '$checklist_id'";
$result = $conn->query($sql);

if ($result->num_rows == 0) {
    header("Location: history.php");
    exit;
}

$checklist = $result->fetch_assoc();

// Check if user is admin or the owner of the checklist
if ($_SESSION['user_id'] != 1 && $_SESSION['user_id'] != $checklist['user_id']) {
    header("Location: history.php");
    exit;
}

// Get checklist items
$sql = "SELECT * FROM checklist_items WHERE checklist_id = '$checklist_id' ORDER BY category, id";
$result = $conn->query($sql);
$all_items = [];
while ($row = $result->fetch_assoc()) {
    $all_items[] = $row;
}

// Group items by category
$items_by_category = [];
$parent_items = [];
$child_items = [];

foreach ($all_items as $item) {
    if ($item['parent_id']) {
        $child_items[$item['parent_id']][] = $item;
    } else {
        if (!isset($items_by_category[$item['category']])) {
            $items_by_category[$item['category']] = [];
        }
        $items_by_category[$item['category']][] = $item;

        if ($item['is_parent']) {
            $parent_items[$item['id']] = $item;
        }
    }
}

$success = '';
$error = '';

// Process form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $date = $_POST['date'];
    $signature = isset($_POST['signature_data']) && !empty($_POST['signature_data']) ?
        $_POST['signature_data'] : $checklist['signature'];
    $responsible_person = isset($_POST['responsible_person']) ? $_POST['responsible_person'] : '';

    $status = 'active'; // Default status is now 'active'

    // Escape strings to prevent SQL injection
    $title = $conn->real_escape_string($title);
    $date = $conn->real_escape_string($date);
    $status = $conn->real_escape_string($status);
    $signature = $conn->real_escape_string($signature);
    $responsible_person = $conn->real_escape_string($responsible_person);

    // Update checklist
    $sql = "UPDATE checklists SET title = '$title', date = '$date', status = '$status', 
            signature = '$signature', responsible_person = '$responsible_person' WHERE id = '$checklist_id'";

    if ($conn->query($sql)) {
        // Update checklist items
        foreach ($all_items as $item) {
            if (!$item['parent_id']) { // Only process parent items here
                $item_id = $item['id'];
                $status_val = isset($_POST['item_' . $item_id]) ? 1 : 0;
                $notes = isset($_POST['notes_' . $item_id]) ? $conn->real_escape_string($_POST['notes_' . $item_id]) : '';

                // Update item
                $sql = "UPDATE checklist_items SET status = '$status_val', notes = '$notes' WHERE id = '$item_id'";
                $conn->query($sql);

                // If this item has sub-items, update them too
                if ($item['is_parent']) {
                    $sql = "SELECT * FROM checklist_items WHERE parent_id = '$item_id'";
                    $subresult = $conn->query($sql);

                    while ($subitem = $subresult->fetch_assoc()) {
                        $subitem_id = $subitem['id'];
                        $subitem_status = isset($_POST['subitem_' . $subitem_id]) ? 1 : 0;
                        $subitem_notes = isset($_POST['notes_subitem_' . $subitem_id]) ? $conn->real_escape_string($_POST['notes_subitem_' . $subitem_id]) : '';

                        $sql = "UPDATE checklist_items SET status = '$subitem_status', notes = '$subitem_notes' WHERE id = '$subitem_id'";
                        $conn->query($sql);
                    }
                }
            }
        }

        // Update remark if provided
        if (isset($_POST['remark'])) {
            $remark = $conn->real_escape_string($_POST['remark']);
            $sql = "UPDATE checklists SET remark = '$remark' WHERE id = '$checklist_id'";
            $conn->query($sql);
        }

        $success = "Checklist berhasil diperbarui!";

        // Refresh checklist data
        $sql = "SELECT * FROM checklists WHERE id = '$checklist_id'";
        $result = $conn->query($sql);
        $checklist = $result->fetch_assoc();
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
    <title>Edit Checklist - GKPI Griya Permata</title>
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
            display: block;
        }

        .signature-pad-container {
            border: 1px solid #dee2e6;
            border-radius: 10px;
            overflow: hidden;
            margin-bottom: 15px;
            position: relative;
            /* Penting untuk positioning */
        }

        #signature-pad {
            width: 100%;
            height: 200px;
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

        .current-signature {
            max-width: 100%;
            height: auto;
            margin-bottom: 15px;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 10px;
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

        /* Penting: Pastikan container memiliki tinggi yang cukup */
        .signature-pad-wrapper {
            position: relative;
            height: 200px;
            /* Harus sama dengan tinggi canvas */
            margin-bottom: 50px;
            /* Untuk tombol actions */
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

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2><i class="bi bi-pencil-square"></i> Edit Checklist</h2>
            <a href="history.php" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Kembali</a>
        </div>

        <div class="card">
            <div class="card-header">
                <i class="bi bi-clipboard-data"></i> Edit Checklist Persiapan Ibadah
            </div>
            <div class="card-body">
                <form method="post" action="" id="checklist-form">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="title" class="form-label">Judul Checklist</label>
                                <input type="text" class="form-control" id="title" name="title" required value="<?php echo htmlspecialchars($checklist['title']); ?>">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="date" class="form-label">Tanggal Ibadah</label>
                                <input type="date" class="form-control" id="date" name="date" required value="<?php echo $checklist['date']; ?>">
                            </div>
                        </div>
                    </div>

                    <!-- H-2 s/d H-1 Checklist -->
                    <?php if (isset($items_by_category['H-2 s/d H-1 Sebelum Ibadah'])): ?>
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
                                            foreach ($items_by_category['H-2 s/d H-1 Sebelum Ibadah'] as $item):
                                            ?>
                                                <tr>
                                                    <td><?php echo $i++; ?></td>
                                                    <td><?php echo htmlspecialchars($item['item_name']); ?></td>
                                                    <td>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" name="item_<?php echo $item['id']; ?>" id="item_<?php echo $item['id']; ?>" <?php echo $item['status'] ? 'checked' : ''; ?> <?php echo $item['is_parent'] ? 'onclick="toggleSubitems(' . $item['id'] . ')"' : ''; ?>>
                                                            <label class="form-check-label" for="item_<?php echo $item['id']; ?>">Selesai</label>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <span class="notes-toggle" onclick="toggleNotes('<?php echo $item['id']; ?>')"><i class="bi bi-pencil-square"></i> Catatan</span>
                                                        <div id="notes_container_<?php echo $item['id']; ?>" class="notes-container" style="<?php echo !empty($item['notes']) ? 'display: block;' : ''; ?>">
                                                            <textarea class="form-control" name="notes_<?php echo $item['id']; ?>" rows="2"><?php echo htmlspecialchars($item['notes']); ?></textarea>
                                                        </div>
                                                    </td>
                                                </tr>

                                                <?php if ($item['is_parent'] && isset($child_items[$item['id']])): ?>
                                                    <tr id="subitems_<?php echo $item['id']; ?>" style="<?php echo $item['status'] ? '' : 'display: none;'; ?>">
                                                        <td colspan="4">
                                                            <div class="subitem-container">
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
                                                                                        <input class="form-check-input" type="checkbox" name="subitem_<?php echo $subitem['id']; ?>" id="subitem_<?php echo $subitem['id']; ?>" <?php echo $subitem['status'] ? 'checked' : ''; ?>>
                                                                                        <label class="form-check-label" for="subitem_<?php echo $subitem['id']; ?>">Selesai</label>
                                                                                    </div>
                                                                                </td>
                                                                                <td>
                                                                                    <span class="notes-toggle" onclick="toggleNotes('subitem_<?php echo $subitem['id']; ?>')"><i class="bi bi-pencil-square"></i> Catatan</span>
                                                                                    <div id="notes_container_subitem_<?php echo $subitem['id']; ?>" class="notes-container" style="<?php echo !empty($subitem['notes']) ? 'display: block;' : ''; ?>">
                                                                                        <textarea class="form-control" name="notes_subitem_<?php echo $subitem['id']; ?>" rows="2"><?php echo htmlspecialchars($subitem['notes']); ?></textarea>
                                                                                    </div>
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
                    <?php
                    // Group items by subcategory for Hari-H
                    $hari_h_subcategories = [];
                    foreach ($items_by_category as $category => $items) {
                        if (strpos($category, 'Hari-H Sebelum Ibadah - ') === 0) {
                            $subcategory = substr($category, strlen('Hari-H Sebelum Ibadah - '));
                            if (!isset($hari_h_subcategories[$subcategory])) {
                                $hari_h_subcategories[$subcategory] = [];
                            }
                            $hari_h_subcategories[$subcategory] = array_merge($hari_h_subcategories[$subcategory], $items);
                        }
                    }

                    if (!empty($hari_h_subcategories)):
                    ?>
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
                                                                <input class="form-check-input" type="checkbox" name="item_<?php echo $item['id']; ?>" id="item_<?php echo $item['id']; ?>" <?php echo $item['status'] ? 'checked' : ''; ?> <?php echo $item['is_parent'] ? 'onclick="toggleSubitems(' . $item['id'] . ')"' : ''; ?>>
                                                                <label class="form-check-label" for="item_<?php echo $item['id']; ?>">Selesai</label>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <span class="notes-toggle" onclick="toggleNotes('<?php echo $item['id']; ?>')"><i class="bi bi-pencil-square"></i> Catatan</span>
                                                            <div id="notes_container_<?php echo $item['id']; ?>" class="notes-container" style="<?php echo !empty($item['notes']) ? 'display: block;' : ''; ?>">
                                                                <textarea class="form-control" name="notes_<?php echo $item['id']; ?>" rows="2"><?php echo htmlspecialchars($item['notes']); ?></textarea>
                                                            </div>
                                                        </td>
                                                    </tr>

                                                    <?php if ($item['is_parent'] && isset($child_items[$item['id']])): ?>
                                                        <tr id="subitems_<?php echo $item['id']; ?>" style="<?php echo $item['status'] ? '' : 'display: none;'; ?>">
                                                            <td colspan="4">
                                                                <div class="subitem-container">
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
                                                                                            <input class="form-check-input" type="checkbox" name="subitem_<?php echo $subitem['id']; ?>" id="subitem_<?php echo $subitem['id']; ?>" <?php echo $subitem['status'] ? 'checked' : ''; ?>>
                                                                                            <label class="form-check-label" for="subitem_<?php echo $subitem['id']; ?>">Selesai</label>
                                                                                        </div>
                                                                                    </td>
                                                                                    <td>
                                                                                        <span class="notes-toggle" onclick="toggleNotes('subitem_<?php echo $subitem['id']; ?>')"><i class="bi bi-pencil-square"></i> Catatan</span>
                                                                                        <div id="notes_container_subitem_<?php echo $subitem['id']; ?>" class="notes-container" style="<?php echo !empty($subitem['notes']) ? 'display: block;' : ''; ?>">
                                                                                            <textarea class="form-control" name="notes_subitem_<?php echo $subitem['id']; ?>" rows="2"><?php echo htmlspecialchars($subitem['notes']); ?></textarea>
                                                                                        </div>
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

                    <!-- Remark -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <i class="bi bi-chat-left-text"></i> Catatan Tambahan
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="remark" class="form-label">Catatan Tambahan</label>
                                <textarea class="form-control" id="remark" name="remark" rows="3" placeholder="Tambahkan catatan tambahan di sini..."><?php echo htmlspecialchars($checklist['remark'] ?? ''); ?></textarea>
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
                                <input type="text" class="form-control" id="responsible_person" name="responsible_person" value="<?php echo htmlspecialchars($checklist['responsible_person']); ?>">
                            </div>

                            <?php if (!empty($checklist['signature'])): ?>
                                <div class="mb-3">
                                    <label class="form-label">Tanda Tangan Saat Ini</label>
                                    <div style="border: 1px solid #dee2e6; padding: 10px; border-radius: 5px;">
                                        <img src="<?php echo $checklist['signature']; ?>" alt="Tanda Tangan" class="current-signature" style="max-width: 100%; height: auto;">
                                    </div>
                                    <div class="form-check mb-3">
                                        <input class="form-check-input" type="checkbox" id="change_signature" name="change_signature">
                                        <label class="form-check-label" for="change_signature">
                                            Ganti tanda tangan
                                        </label>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <div id="new_signature_container" style="display: <?php echo empty($checklist['signature']) ? 'block' : 'none'; ?>;">
                                <label class="form-label">Tanda Tangan</label>
                                <div class="signature-pad-wrapper">
                                    <div class="signature-pad-container">
                                        <canvas id="signature-pad" class="signature-pad"></canvas>
                                        <div class="signature-pad-actions">
                                            <button type="button" class="btn btn-sm btn-secondary" id="clear-signature">Hapus</button>
                                        </div>
                                    </div>
                                </div>
                                <input type="hidden" name="signature_data" id="signature_data">
                            </div>

                            <div class="alert alert-info">
                                <i class="bi bi-info-circle"></i> Tanda tangan diperlukan sebagai bukti bahwa checklist ini diisi oleh penanggung jawab yang berwenang.
                            </div>
                        </div>
                    </div>

                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <a href="history.php" class="btn btn-secondary me-2"><i class="bi bi-x-circle"></i> Batal</a>
                        <button type="submit" class="btn btn-primary" id="submit-form"><i class="bi bi-save"></i> Simpan Perubahan</button>
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
            } else {
                subitemsContainer.style.display = 'none';
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            // Initialize signature pad with delay to ensure DOM is ready
            setTimeout(function() {
                initSignaturePad();
            }, 300);

            function initSignaturePad() {
                // Initialize signature pad
                const canvas = document.getElementById('signature-pad');

                if (!canvas) {
                    console.error("Canvas element not found!");
                    return;
                }

                // Pastikan canvas memiliki dimensi yang benar
                canvas.width = canvas.offsetWidth;
                canvas.height = canvas.offsetHeight;

                const signaturePad = new SignaturePad(canvas, {
                    backgroundColor: 'rgba(255, 255, 255, 0)',
                    penColor: 'black'
                });

                // Clear signature button
                const clearButton = document.getElementById('clear-signature');
                if (clearButton) {
                    clearButton.addEventListener('click', function() {
                        signaturePad.clear();
                        document.getElementById('signature_data').value = '';
                    });
                }

                // Resize canvas
                function resizeCanvas() {
                    const ratio = Math.max(window.devicePixelRatio || 1, 1);

                    // Simpan data tanda tangan sebelum resize
                    const data = signaturePad.toData();

                    canvas.width = canvas.offsetWidth * ratio;
                    canvas.height = canvas.offsetHeight * ratio;
                    canvas.getContext("2d").scale(ratio, ratio);

                    // Kembalikan data tanda tangan setelah resize
                    if (data && data.length) signaturePad.fromData(data);
                }

                window.addEventListener("resize", resizeCanvas);
                resizeCanvas();

                // Toggle signature change
                const changeSignatureCheckbox = document.getElementById('change_signature');
                if (changeSignatureCheckbox) {
                    changeSignatureCheckbox.addEventListener('change', function() {
                        const newSignatureContainer = document.getElementById('new_signature_container');
                        if (this.checked) {
                            newSignatureContainer.style.display = 'block';
                            // Resize canvas setelah container ditampilkan
                            setTimeout(resizeCanvas, 100);
                        } else {
                            newSignatureContainer.style.display = 'none';
                            document.getElementById('signature_data').value = '';
                        }
                    });
                }

                // Form submission
                const form = document.getElementById('checklist-form');
                if (form) {
                    form.addEventListener('submit', function(e) {
                        const changeSignatureCheckbox = document.getElementById('change_signature');
                        const newSignatureContainer = document.getElementById('new_signature_container');

                        // Jika container tanda tangan baru ditampilkan (baik karena checkbox atau tidak ada tanda tangan sebelumnya)
                        if (newSignatureContainer.style.display === 'block' || newSignatureContainer.style.display === '') {
                            // Jika tanda tangan kosong
                            if (signaturePad.isEmpty()) {
                                // Jika ini adalah tanda tangan baru (tidak ada tanda tangan sebelumnya)
                                if (!document.querySelector('.current-signature')) {
                                    e.preventDefault();
                                    alert('Mohon berikan tanda tangan penanggung jawab!');
                                    return false;
                                }
                                // Jika ini adalah perubahan tanda tangan, tapi user tidak menggambar apa-apa
                                else if (changeSignatureCheckbox && changeSignatureCheckbox.checked) {
                                    e.preventDefault();
                                    alert('Mohon berikan tanda tangan penanggung jawab baru!');
                                    return false;
                                }
                            } else {
                                // Tanda tangan tidak kosong, simpan data
                                const signatureData = signaturePad.toDataURL();
                                document.getElementById('signature_data').value = signatureData;
                                console.log("New signature data saved, length:", signatureData.length);
                            }
                        }

                        return true;
                    });
                }
            }
        });
    </script>
</body>

</html>