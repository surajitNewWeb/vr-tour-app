<?php
require_once __DIR__ . '/includes/db.php';
session_start();

$message = "";

if ($_POST) {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

    $stmt = $conn->prepare("INSERT INTO vr_users (username, email, password) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $username, $email, $password);

    if ($stmt->execute()) {
        $message = "Account created! Please <a href='login.php'>Login</a>";
    } else {
        $message = "Error: Username or Email already exists.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Register - VR Tours</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center h-screen">
  <div class="bg-white p-8 rounded-2xl shadow-md w-full max-w-md">
    <h1 class="text-2xl font-bold mb-6 text-center">Create Account</h1>
    <?php if ($message): ?>
      <p class="text-center text-sm text-red-500 mb-4"><?= $message ?></p>
    <?php endif; ?>
    <form method="POST" class="space-y-4">
      <input type="text" name="username" placeholder="Username" required class="w-full border px-4 py-2 rounded-lg">
      <input type="email" name="email" placeholder="Email" required class="w-full border px-4 py-2 rounded-lg">
      <input type="password" name="password" placeholder="Password" required class="w-full border px-4 py-2 rounded-lg">
      <button type="submit" class="w-full bg-indigo-600 text-white py-2 rounded-lg hover:bg-indigo-700">Register</button>
    </form>
    <p class="mt-4 text-center text-sm">Already have an account? <a href="login.php" class="text-indigo-600">Login</a></p>
  </div>
</body>
</html>
