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

// Handle add new item
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_item'])) {
    $category = $_POST['category'];
    if ($category == 'new' && !empty($_POST['new_category'])) {
        $category = $_POST['new_category'];
    }
    $item_name = $_POST['item_name'];
    $is_parent = isset($_POST['is_parent']) ? 1 : 0;
    $parent_id = isset($_POST['parent_id']) && !empty($_POST['parent_id']) ? $_POST['parent_id'] : null;
    
    // Validate input
    if (empty($category) || empty($item_name)) {
        $error = "Kategori dan nama item harus diisi.";
    } else {
        // Insert new item
        if ($parent_id) {
            $sql = "INSERT INTO checklist_template (category, item_name, parent_id) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssi", $category, $item_name, $parent_id);
        } else {
            $sql = "INSERT INTO checklist_template (category, item_name, is_parent) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssi", $category, $item_name, $is_parent);
        }
        
        if ($stmt->execute()) {
            $success = "Item checklist berhasil ditambahkan.";
        } else {
            $error = "Gagal menambahkan item: " . $conn->error;
        }
    }
}

// Handle edit item
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_item'])) {
    $item_id = $_POST['item_id'];
    $category = $_POST['category'];
    if ($category == 'new' && !empty($_POST['new_category'])) {
        $category = $_POST['new_category'];
    }
    $item_name = $_POST['item_name'];
    $is_parent = isset($_POST['is_parent']) ? 1 : 0;
    $parent_id = isset($_POST['parent_id']) && !empty($_POST['parent_id']) ? $_POST['parent_id'] : null;
    
    // Update item
    if ($parent_id) {
        $sql = "UPDATE checklist_template SET category = ?, item_name = ?, is_parent = 0, parent_id = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssii", $category, $item_name, $parent_id, $item_id);
    } else {
        $sql = "UPDATE checklist_template SET category = ?, item_name = ?, is_parent = ?, parent_id = NULL WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssii", $category, $item_name, $is_parent, $item_id);
    }
    
    if ($stmt->execute()) {
        $success = "Item checklist berhasil diperbarui.";
    } else {
        $error = "Gagal memperbarui item: " . $conn->error;
    }
}

// Handle delete item
if (isset($_GET['delete']) && !empty($_GET['delete'])) {
    $item_id = $_GET['delete'];
    
    // Check if this is a parent item with children
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM checklist_template WHERE parent_id = ?");
    $stmt->bind_param("i", $item_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $has_children = $result->fetch_assoc()['count'] > 0;
    
    if ($has_children) {
        $error = "Item ini memiliki sub-item. Hapus sub-item terlebih dahulu.";
    } else {
        // Delete item
        $stmt = $conn->prepare("DELETE FROM checklist_template WHERE id = ?");
        $stmt->bind_param("i", $item_id);
        
        if ($stmt->execute()) {
            $success = "Item checklist berhasil dihapus.";
        } else {
            $error = "Gagal menghapus item: " . $conn->error;
        }
    }
}

// Create checklist_template table if it doesn't exist
$sql = "CREATE TABLE IF NOT EXISTS checklist_template (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    category VARCHAR(100) NOT NULL,
    item_name VARCHAR(255) NOT NULL,
    is_parent TINYINT(1) DEFAULT 0,
    parent_id INT(11) NULL
)";
$conn->query($sql);

// Check if table is empty, populate with default items if needed
$result = $conn->query("SELECT COUNT(*) as count FROM checklist_template");
$count = $result->fetch_assoc()['count'];

if ($count == 0) {
    // Insert default items from checklist_items table
    $sql = "INSERT INTO checklist_template (category, item_name, is_parent, parent_id)
        SELECT category, item_name, ANY_VALUE(is_parent), ANY_VALUE(parent_id)
        FROM checklist_items
        GROUP BY category, item_name";
    $conn->query($sql);
}

// Get all parent items
$parent_items = [];
$result = $conn->query("SELECT * FROM checklist_template WHERE parent_id IS NULL AND is_parent = 1 ORDER BY category, id");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $parent_items[] = $row;
    }
}

// Get all items grouped by category
$items_by_category = [];
$result = $conn->query("SELECT * FROM checklist_template ORDER BY category, id");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        if (!isset($items_by_category[$row['category']])) {
            $items_by_category[$row['category']] = [];
        }
        $items_by_category[$row['category']][] = $row;
    }
}

// Get all categories
$categories = [];
$result = $conn->query("SELECT DISTINCT category FROM checklist_template ORDER BY category");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $categories[] = $row['category'];
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Item Checklist - GKPI Griya Permata</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
    <link rel="icon" href="public/images/church-logo.png" type="image/png">
    <style>
        .subitem {
            margin-left: 2rem;
            border-left: 2px solid #e9b872;
            padding-left: 1rem;
        }
        .category-badge {
            background-color: var(--primary-light);
            color: white;
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
            font-size: 0.8rem;
            margin-left: 0.5rem;
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
                        <a class="nav-link" href="admin.php">
                            <i class="bi bi-speedometer2"></i> Admin
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="admin_checklist_items.php">
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
        <?php if($success): ?>
        <div class="alert alert-success">
            <i class="bi bi-check-circle-fill"></i> <?php echo $success; ?>
        </div>
        <?php endif; ?>
        
        <?php if($error): ?>
        <div class="alert alert-danger">
            <i class="bi bi-exclamation-triangle-fill"></i> <?php echo $error; ?>
        </div>
        <?php endif; ?>
        
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="bi bi-list-check"></i> Kelola Item Checklist</h2>
            <div>
                <a href="admin.php" class="btn btn-secondary me-2">
                    <i class="bi bi-arrow-left"></i> Kembali ke Admin
                </a>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addItemModal">
                    <i class="bi bi-plus-circle"></i> Tambah Item Baru
                </button>
            </div>
        </div>
        
        <!-- Checklist Items by Category -->
        <?php foreach ($items_by_category as $category => $items): ?>
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <i class="bi bi-tag"></i> <?php echo htmlspecialchars($category); ?>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th width="5%">#</th>
                                <th width="60%">Item</th>
                                <th width="15%">Kategori</th>
                                <th width="10%">Tipe</th>
                                <th width="10%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $i = 1;
                            foreach ($items as $item): 
                                // Skip sub-items, we'll show them under their parent
                                if ($item['parent_id']) continue;
                            ?>
                            <tr>
                                <td><?php echo $i++; ?></td>
                                <td><?php echo htmlspecialchars($item['item_name']); ?></td>
                                <td><span class="category-badge"><?php echo htmlspecialchars($category); ?></span></td>
                                <td>
                                    <?php if ($item['is_parent']): ?>
                                    <span class="badge bg-info">Parent</span>
                                    <?php else: ?>
                                    <span class="badge bg-secondary">Item</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editItemModal<?php echo $item['id']; ?>">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteItemModal<?php echo $item['id']; ?>">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                    
                                    <!-- Edit Item Modal -->
                                    <div class="modal fade" id="editItemModal<?php echo $item['id']; ?>" tabindex="-1" aria-labelledby="editItemModalLabel<?php echo $item['id']; ?>" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="editItemModalLabel<?php echo $item['id']; ?>">Edit Item</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <form method="post" action="">
                                                    <div class="modal-body">
                                                        <input type="hidden" name="item_id" value="<?php echo $item['id']; ?>">
                                                        
                                                        <div class="mb-3">
                                                            <label for="edit_category<?php echo $item['id']; ?>" class="form-label">Kategori</label>
                                                            <select class="form-select" id="edit_category<?php echo $item['id']; ?>" name="category">
                                                                <?php foreach ($categories as $cat): ?>
                                                                <option value="<?php echo htmlspecialchars($cat); ?>" <?php echo $cat == $item['category'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($cat); ?></option>
                                                                <?php endforeach; ?>
                                                                <option value="new">Kategori Baru...</option>
                                                            </select>
                                                        </div>
                                                        
                                                        <div class="mb-3" id="new_category_container<?php echo $item['id']; ?>" style="display: none;">
                                                            <label for="new_category<?php echo $item['id']; ?>" class="form-label">Kategori Baru</label>
                                                            <input type="text" class="form-control" id="new_category<?php echo $item['id']; ?>" name="new_category">
                                                        </div>
                                                        
                                                        <div class="mb-3">
                                                            <label for="edit_item_name<?php echo $item['id']; ?>" class="form-label">Nama Item</label>
                                                            <input type="text" class="form-control" id="edit_item_name<?php echo $item['id']; ?>" name="item_name" value="<?php echo htmlspecialchars($item['item_name']); ?>" required>
                                                        </div>
                                                        
                                                        <div class="form-check mb-3">
                                                            <input class="form-check-input" type="checkbox" id="edit_is_parent<?php echo $item['id']; ?>" name="is_parent" <?php echo $item['is_parent'] ? 'checked' : ''; ?>>
                                                            <label class="form-check-label" for="edit_is_parent<?php echo $item['id']; ?>">
                                                                Item ini memiliki sub-item
                                                            </label>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                        <button type="submit" class="btn btn-primary" name="edit_item">Simpan</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Delete Item Modal -->
                                    <div class="modal fade" id="deleteItemModal<?php echo $item['id']; ?>" tabindex="-1" aria-labelledby="deleteItemModalLabel<?php echo $item['id']; ?>" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="deleteItemModalLabel<?php echo $item['id']; ?>">Konfirmasi Hapus</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    Apakah Anda yakin ingin menghapus item "<strong><?php echo htmlspecialchars($item['item_name']); ?></strong>"?
                                                    <br><br>
                                                    <div class="alert alert-warning">
                                                        <i class="bi bi-exclamation-triangle-fill"></i> Tindakan ini tidak dapat dibatalkan.
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                    <a href="admin_checklist_items.php?delete=<?php echo $item['id']; ?>" class="btn btn-danger">Hapus</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            
                            <?php
                            // Show sub-items if this is a parent
                            if ($item['is_parent']):
                                // Find sub-items
                                $sub_items = [];
                                foreach ($items_by_category[$category] as $sub_item) {
                                    if ($sub_item['parent_id'] == $item['id']) {
                                        $sub_items[] = $sub_item;
                                    }
                                }
                                
                                foreach ($sub_items as $sub_item):
                            ?>
                            <tr class="table-light">
                                <td></td>
                                <td>
                                    <div class="subitem">
                                        <?php echo htmlspecialchars($sub_item['item_name']); ?>
                                    </div>
                                </td>
                                <td><span class="category-badge"><?php echo htmlspecialchars($category); ?></span></td>
                                <td>
                                    <span class="badge bg-secondary">Sub-item</span>
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editItemModal<?php echo $sub_item['id']; ?>">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteItemModal<?php echo $sub_item['id']; ?>">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                    
                                    <!-- Edit Sub-Item Modal -->
                                    <div class="modal fade" id="editItemModal<?php echo $sub_item['id']; ?>" tabindex="-1" aria-labelledby="editItemModalLabel<?php echo $sub_item['id']; ?>" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="editItemModalLabel<?php echo $sub_item['id']; ?>">Edit Sub-Item</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <form method="post" action="">
                                                    <div class="modal-body">
                                                        <input type="hidden" name="item_id" value="<?php echo $sub_item['id']; ?>">
                                                        
                                                        <div class="mb-3">
                                                            <label for="edit_category<?php echo $sub_item['id']; ?>" class="form-label">Kategori</label>
                                                            <select class="form-select" id="edit_category<?php echo $sub_item['id']; ?>" name="category">
                                                                <?php foreach ($categories as $cat): ?>
                                                                <option value="<?php echo htmlspecialchars($cat); ?>" <?php echo $cat == $sub_item['category'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($cat); ?></option>
                                                                <?php endforeach; ?>
                                                                <option value="new">Kategori Baru...</option>
                                                            </select>
                                                        </div>
                                                        
                                                        <div class="mb-3" id="new_category_container<?php echo $sub_item['id']; ?>" style="display: none;">
                                                            <label for="new_category<?php echo $sub_item['id']; ?>" class="form-label">Kategori Baru</label>
                                                            <input type="text" class="form-control" id="new_category<?php echo $sub_item['id']; ?>" name="new_category">
                                                        </div>
                                                        
                                                        <div class="mb-3">
                                                            <label for="edit_parent_id<?php echo $sub_item['id']; ?>" class="form-label">Parent Item</label>
                                                            <select class="form-select" id="edit_parent_id<?php echo $sub_item['id']; ?>" name="parent_id">
                                                                <?php foreach ($parent_items as $parent): ?>
                                                                <option value="<?php echo $parent['id']; ?>" <?php echo $parent['id'] == $sub_item['parent_id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($parent['item_name']); ?></option>
                                                                <?php endforeach; ?>
                                                            </select>
                                                        </div>
                                                        
                                                        <div class="mb-3">
                                                            <label for="edit_item_name<?php echo $sub_item['id']; ?>" class="form-label">Nama Item</label>
                                                            <input type="text" class="form-control" id="edit_item_name<?php echo $sub_item['id']; ?>" name="item_name" value="<?php echo htmlspecialchars($sub_item['item_name']); ?>" required>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                        <button type="submit" class="btn btn-primary" name="edit_item">Simpan</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Delete Sub-Item Modal -->
                                    <div class="modal fade" id="deleteItemModal<?php echo $sub_item['id']; ?>" tabindex="-1" aria-labelledby="deleteItemModalLabel<?php echo $sub_item['id']; ?>" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="deleteItemModalLabel<?php echo $sub_item['id']; ?>">Konfirmasi Hapus</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    Apakah Anda yakin ingin menghapus sub-item "<strong><?php echo htmlspecialchars($sub_item['item_name']); ?></strong>"?
                                                    <br><br>
                                                    <div class="alert alert-warning">
                                                        <i class="bi bi-exclamation-triangle-fill"></i> Tindakan ini tidak dapat dibatalkan.
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                    <a href="admin_checklist_items.php?delete=<?php echo $sub_item['id']; ?>" class="btn btn-danger">Hapus</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <?php 
                                endforeach;
                            endif;
                            ?>
                            
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
        
        <!-- Add Item Modal -->
        <div class="modal fade" id="addItemModal" tabindex="-1" aria-labelledby="addItemModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addItemModalLabel">Tambah Item Baru</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form method="post" action="">
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="category" class="form-label">Kategori</label>
                                <select class="form-select" id="category" name="category">
                                    <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo htmlspecialchars($cat); ?>"><?php echo htmlspecialchars($cat); ?></option>
                                    <?php endforeach; ?>
                                    <option value="new">Kategori Baru...</option>
                                </select>
                            </div>
                            
                            <div class="mb-3" id="new_category_container" style="display: none;">
                                <label for="new_category" class="form-label">Kategori Baru</label>
                                <input type="text" class="form-control" id="new_category" name="new_category">
                            </div>
                            
                            <div class="mb-3">
                                <label for="item_name" class="form-label">Nama Item</label>
                                <input type="text" class="form-control" id="item_name" name="item_name" required>
                            </div>
                            
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="is_parent" name="is_parent">
                                <label class="form-check-label" for="is_parent">
                                    Item ini memiliki sub-item
                                </label>
                            </div>
                            
                            <div class="mb-3" id="parent_container" style="display: none;">
                                <label for="parent_id" class="form-label">Parent Item</label>
                                <select class="form-select" id="parent_id" name="parent_id">
                                    <option value="">-- Pilih Parent Item --</option>
                                    <?php foreach ($parent_items as $parent): ?>
                                    <option value="<?php echo $parent['id']; ?>"><?php echo htmlspecialchars($parent['item_name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-primary" name="add_item">Tambah</button>
                        </div>
                    </form>
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
        document.addEventListener('DOMContentLoaded', function() {
            // Toggle new category input
            const categorySelect = document.getElementById('category');
            const newCategoryContainer = document.getElementById('new_category_container');
            
            categorySelect.addEventListener('change', function() {
                if (this.value === 'new') {
                    newCategoryContainer.style.display = 'block';
                } else {
                    newCategoryContainer.style.display = 'none';
                }
            });
            
            // Toggle parent item select
            const isParentCheckbox = document.getElementById('is_parent');
            const parentContainer = document.getElementById('parent_container');
            
            isParentCheckbox.addEventListener('change', function() {
                if (this.checked) {
                    parentContainer.style.display = 'none';
                } else {
                    parentContainer.style.display = 'block';
                }
            });
            
            // Initialize edit modals
            const editCategorySelects = document.querySelectorAll('[id^="edit_category"]');
            editCategorySelects.forEach(function(select) {
                const id = select.id.replace('edit_category', '');
                const newCategoryContainer = document.getElementById('new_category_container' + id);
                
                select.addEventListener('change', function() {
                    if (this.value === 'new') {
                        newCategoryContainer.style.display = 'block';
                    } else {
                        newCategoryContainer.style.display = 'none';
                    }
                });
            });
        });
    </script>
</body>
</html>
