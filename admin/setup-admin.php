<?php
// setup-admin.php
session_start();
require_once '../includes/config.php';

// Function to create default admin
function create_default_admin() {
    global $conn;
    
    $username = 'admin';
    $email = 'admin@vrtour.com';
    $password = 'password123'; // Change this in production!
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Check if admin already exists
    $check_query = "SELECT id FROM admins WHERE email = '$email'";
    $result = mysqli_query($conn, $check_query);
    
    if (mysqli_num_rows($result) == 0) {
        $query = "INSERT INTO admins (username, email, password, created_at) 
                  VALUES ('$username', '$email', '$hashed_password', NOW())";
        
        if (mysqli_query($conn, $query)) {
            return [
                'success' => true,
                'message' => 'Default admin user created successfully!<br>
                             Email: admin@vrtour.com<br>
                             Password: password123<br><br>
                             <strong>Please change this password immediately after login!</strong>'
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Error creating default admin: ' . mysqli_error($conn)
            ];
        }
    } else {
        return [
            'success' => true,
            'message' => 'Admin user already exists. No changes made.'
        ];
    }
}

// Handle form submission
$result = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = create_default_admin();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup Admin - VR Tour Application</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(to right, #4e54c8, #8f94fb);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .setup-container {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            max-width: 500px;
            width: 100%;
        }
    </style>
</head>
<body>
    <div class="setup-container">
        <div class="text-center mb-4">
            <i class="fas fa-vr-cardboard fa-3x text-primary mb-3"></i>
            <h2>Setup Admin Account</h2>
            <p class="text-muted">Create a default administrator account</p>
        </div>

        <?php if ($result): ?>
            <div class="alert alert-<?php echo $result['success'] ? 'success' : 'danger'; ?>">
                <?php echo $result['message']; ?>
            </div>
            <?php if ($result['success']): ?>
                <div class="text-center mt-3">
                    <a href="login.php" class="btn btn-primary">Go to Login</a>
                </div>
            <?php else: ?>
                <div class="text-center mt-3">
                    <a href="setup-admin.php" class="btn btn-primary">Try Again</a>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="alert alert-info">
                <p>This setup will create a default administrator account with the following credentials:</p>
                <ul>
                    <li>Email: <strong>admin@vrtour.com</strong></li>
                    <li>Password: <strong>password123</strong></li>
                </ul>
                <p class="mb-0"><strong>Warning:</strong> Change the password immediately after logging in!</p>
            </div>

            <form method="POST">
                <div class="text-center">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fas fa-user-plus"></i> Create Admin Account
                    </button>
                </div>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>