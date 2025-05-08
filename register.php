<?php
session_start();
require_once 'db_connect.php';

// If user is already logged in, redirect to index page
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']); // Email bisa kosong
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validate input - email tidak diperiksa apakah kosong
    if (empty($username) || empty($password) || empty($confirm_password)) {
        $message = '<div class="alert alert-danger">Username dan password wajib diisi.</div>';
    } elseif ($password !== $confirm_password) {
        $message = '<div class="alert alert-danger">Passwords do not match.</div>';
    } else {
        // Check if username already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $message = '<div class="alert alert-danger">Username already taken.</div>';
        } else {
            // Check if email already exists (only if email is provided)
            if (!empty($email)) {
                $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
                $stmt->bind_param("s", $email);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows > 0) {
                    $message = '<div class="alert alert-danger">Email already registered.</div>';
                    $stmt->close();
                    goto end_registration;
                }
            }

            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Insert new user
            $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $username, $email, $hashed_password);

            if ($stmt->execute()) {
                $message = '<div class="alert alert-success">Registration successful! You can now <a href="login.php">login</a>.</div>';
            } else {
                $message = '<div class="alert alert-danger">Error creating account.</div>';
            }
        }
    }

    end_registration:
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - GKPI Griya Permata</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="styles.css">
</head>

<body>
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-6 offset-md-3">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white text-center">
                        <img src="public/images/church-logo.png" alt="Church Logo" height="60" class="mb-2">
                        <h3>GKPI Griya Permata</h3>
                        <p class="mb-0">Register New Account</p>
                    </div>
                    <div class="card-body">
                        <?php echo $message; ?>

                        <form method="POST" action="register.php">
                            <div class="form-group">
                                <label for="username"><i class="fas fa-user"></i> Username</label>
                                <input type="text" class="form-control" id="username" name="username" required>
                            </div>

                            <div class="form-group">
                                <label for="email"><i class="fas fa-envelope"></i> Email (Opsional)</label>
                                <input type="email" class="form-control" id="email" name="email">
                            </div>

                            <div class="form-group">
                                <label for="password"><i class="fas fa-lock"></i> Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>

                            <div class="form-group">
                                <label for="confirm_password"><i class="fas fa-check-circle"></i> Confirm Password</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                            </div>

                            <button type="submit" class="btn btn-primary btn-block"><i class="fas fa-user-plus"></i> Register</button>
                        </form>

                        <div class="text-center mt-3">
                            <p>Already have an account? <a href="login.php">Login</a></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>

</html>