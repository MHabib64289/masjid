<?php
session_start();
include 'config/db.php';

if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}

$error = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = mysqli_real_escape_string($conn, trim($_POST['name']));
    $email = mysqli_real_escape_string($conn, trim($_POST['email']));
    $password = $_POST['password'];
    $password_confirm = $_POST['password_confirm'];

    // Validation
    if (strlen($name) < 3) {
        $error[] = "Nama harus minimal 3 karakter";
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error[] = "Format email tidak valid";
    }

    if (strlen($password) < 6) {
        $error[] = "Password harus minimal 6 karakter";
    }

    if ($password !== $password_confirm) {
        $error[] = "Password konfirmasi tidak sesuai";
    }

    // Check if email already exists
    $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $check->bind_param("s", $email);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows > 0) {
        $error[] = "Email sudah terdaftar. Silakan gunakan email lain.";
    }

    // If no errors, proceed with registration
    if (empty($error)) {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO users (name, email, password, role, created_at) VALUES (?, ?, ?, 'user', NOW())");
        $stmt->bind_param("sss", $name, $email, $password_hash);
        
        if ($stmt->execute()) {
            header("Location: login.php?registered=1");
            exit();
        } else {
            $error[] = "Gagal mendaftar. Silakan coba lagi.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrasi - Sistem Manajemen Masjid</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; }
    </style>
</head>
<body class="bg-[#F0F2F5] min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="w-full max-w-md">
        <div class="bg-white rounded-xl shadow-lg p-8">
            <div class="text-center mb-8">
                <h1 class="text-2xl font-bold text-[#2C3E50]">Daftar Akun Baru</h1>
                <p class="text-gray-600 mt-2">Silakan lengkapi data diri Anda</p>
            </div>
            
            <?php if (!empty($error)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-4">
                    <ul class="list-disc list-inside">
                        <?php foreach ($error as $err): ?>
                            <li><?php echo $err; ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="POST" class="space-y-6" id="registerForm">
                <div>
                    <label class="block text-gray-700 text-sm font-semibold mb-2" for="name">
                        Nama Lengkap
                    </label>
                    <input class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-[#16A085] focus:border-transparent transition-all"
                           type="text" name="name" id="name" required minlength="3"
                           value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>"
                           pattern="[A-Za-z\s]+"
                           title="Nama hanya boleh mengandung huruf dan spasi">
                </div>

                <div>
                    <label class="block text-gray-700 text-sm font-semibold mb-2" for="email">
                        Email
                    </label>
                    <input class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-[#16A085] focus:border-transparent transition-all"
                           type="email" name="email" id="email" required
                           value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                </div>

                <div>
                    <label class="block text-gray-700 text-sm font-semibold mb-2" for="password">
                        Password
                    </label>
                    <input class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-[#16A085] focus:border-transparent transition-all"
                           type="password" name="password" id="password" required
                           minlength="6"
                           pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{6,}"
                           title="Password harus mengandung minimal 6 karakter, termasuk huruf besar, huruf kecil, dan angka">
                    <div class="mt-1 text-sm text-gray-500">
                        Password harus mengandung:
                        <ul class="list-disc list-inside">
                            <li>Minimal 6 karakter</li>
                            <li>Huruf besar dan kecil</li>
                            <li>Angka</li>
                        </ul>
                    </div>
                </div>

                <div>
                    <label class="block text-gray-700 text-sm font-semibold mb-2" for="password_confirm">
                        Konfirmasi Password
                    </label>
                    <input class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-[#16A085] focus:border-transparent transition-all"
                           type="password" name="password_confirm" id="password_confirm" required>
                </div>

                <button type="submit" 
                        class="w-full bg-[#16A085] text-white font-semibold py-2 px-4 rounded-lg hover:bg-opacity-90 transition-all">
                    Daftar Sekarang
                </button>
            </form>

            <div class="mt-6 text-center">
                <p class="text-gray-600">
                    Sudah punya akun? 
                    <a href="login.php" class="text-[#16A085] hover:underline">Login disini</a>
                </p>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const passwordConfirm = document.getElementById('password_confirm').value;

            if (password !== passwordConfirm) {
                e.preventDefault();
                alert('Password konfirmasi tidak sesuai!');
            }
        });

        // Real-time password validation
        document.getElementById('password').addEventListener('input', function() {
            const password = this.value;
            const hasUpperCase = /[A-Z]/.test(password);
            const hasLowerCase = /[a-z]/.test(password);
            const hasNumbers = /\d/.test(password);
            const isLongEnough = password.length >= 6;

            if (hasUpperCase && hasLowerCase && hasNumbers && isLongEnough) {
                this.style.borderColor = '#16A085';
            } else {
                this.style.borderColor = '#EF4444';
            }
        });
    </script>
</body>
</html>
