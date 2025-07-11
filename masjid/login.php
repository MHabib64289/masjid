<?php
session_start();
include 'config/db.php';

// Jika user klik "Masuk sebagai Tamu"
if (isset($_GET['guest'])) {
    $_SESSION['role'] = 'guest';
    header("Location: user/dashboard.php");
    exit();
}

// Proses login admin
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Ambil user dari database
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    // Cek password dan role admin
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];
        header("Location: " . ($user['role'] === 'admin' ? "admin/dashboard.php" : "user/dashboard.php"));
        exit();
    } else {
        $user = false;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Masjid</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-blue-100 to-green-100 min-h-screen flex items-center justify-center">
    <div class="w-full max-w-md p-8 bg-white rounded-2xl shadow-xl">
        <div class="mb-8 text-center">
            <div class="flex justify-center mb-2">
                <span class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-blue-100">
                    <svg class="w-10 h-10 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 2a10 10 0 100 20 10 10 0 000-20zm0 4a2 2 0 110 4 2 2 0 010-4zm0 14a8 8 0 01-6.32-3.16c.03-2.5 5-3.88 6.32-3.88s6.29 1.38 6.32 3.88A8 8 0 0112 20z"/></svg>
                </span>
            </div>
            <h1 class="text-2xl font-bold text-slate-800">Masjid Management</h1>
            <p class="text-slate-500">Silakan login untuk melanjutkan</p>
        </div>
        <?php if (isset($user) && !$user): ?>
            <div class="mb-4 p-3 rounded bg-red-50 text-red-700 text-center">Login gagal: email atau password salah.</div>
        <?php endif; ?>
        <form method="post" class="space-y-4">
            <input type="email" name="email" required placeholder="Email" class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400">
            <input type="password" name="password" required placeholder="Password" class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400">
            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 rounded transition">Login sebagai Admin/User</button>
        </form>
        <div class="flex justify-between items-center mt-4">
            <a href="register.php" class="text-blue-600 hover:underline">Daftar</a>
            <form method="get" class="inline">
                <button type="submit" name="guest" value="1" class="ml-2 text-green-600 hover:underline">Masuk sebagai Tamu</button>
            </form>
        </div>
    </div>
</body>
</html>
