<?php
require_once __DIR__ . '/includes/db.php';
session_start();

$message = "";

if ($_POST) {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    $stmt = $conn->prepare("SELECT id, password FROM vr_users WHERE username=?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->bind_result($user_id, $hashed_password);

    if ($stmt->fetch() && password_verify($password, $hashed_password)) {
        $_SESSION['user_id'] = $user_id;
        $_SESSION['username'] = $username;
        header("Location: index.php");
        exit;
    } else {
        $message = "Invalid username or password.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Login - VR Tours</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center h-screen">
  <div class="bg-white p-8 rounded-2xl shadow-md w-full max-w-md">
    <h1 class="text-2xl font-bold mb-6 text-center">Login</h1>
    <?php if ($message): ?>
      <p class="text-center text-sm text-red-500 mb-4"><?= $message ?></p>
    <?php endif; ?>
    <form method="POST" class="space-y-4">
      <input type="text" name="username" placeholder="Username" required class="w-full border px-4 py-2 rounded-lg">
      <input type="password" name="password" placeholder="Password" required class="w-full border px-4 py-2 rounded-lg">
      <button type="submit" class="w-full bg-indigo-600 text-white py-2 rounded-lg hover:bg-indigo-700">Login</button>
    </form>
    <p class="mt-4 text-center text-sm">No account? <a href="register.php" class="text-indigo-600">Register</a></p>
  </div>
</body>
</html>
