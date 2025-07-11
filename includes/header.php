<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: /masjid/login.php");
    exit;
}

$isAdmin = $_SESSION['role'] === 'admin';
$baseUrl = $isAdmin ? '/masjid/admin' : '/masjid/user';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Manajemen Masjid</title>
    <link href="<?php echo isset($is_admin) ? '../css/style.css' : '/masjid/css/style.css'; ?>" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="bg-white min-h-screen flex flex-col">    <!-- Mobile Menu Button -->
    <div class="lg:hidden fixed top-4 left-4 z-50">
        <button id="mobile-menu-button" class="p-2 rounded-lg bg-white shadow-sm hover:bg-gray-50">
            <svg class="w-6 h-6 text-gray-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
        </button>
    </div>

    <!-- Sidebar -->
    <aside id="sidebar" class="fixed inset-y-0 left-0 z-40 w-64 bg-white shadow-lg transform -translate-x-full lg:translate-x-0 transition-transform duration-300 ease-in-out">
        <!-- Logo -->
        <div class="flex items-center justify-center h-16 bg-primary">
            <span class="text-xl font-bold text-white">Masjid Management</span>
        </div>

        <!-- Navigation -->
        <nav class="mt-6 px-4">
            <div class="space-y-2">
                <?php if ($_SESSION['role'] === 'admin'): ?>
                    <a href="/masjid/admin/dashboard.php" class="nav-link block w-full text-gray-900 font-semibold">Dashboard</a>
                    <a href="/masjid/admin/struktur.php" class="nav-link block w-full text-gray-900 font-semibold">Struktur Masjid</a>
                    <a href="/masjid/admin/pengajian.php" class="nav-link block w-full text-gray-900 font-semibold">Jadwal Pengajian</a>
                    <a href="/masjid/admin/sedekah.php" class="nav-link block w-full text-gray-900 font-semibold">Data Sedekah</a>
                    <a href="/masjid/admin/tabungan.php" class="nav-link block w-full text-gray-900 font-semibold">Tabungan Masjid</a>
                    <a href="/masjid/admin/laporan.php" class="nav-link block w-full text-gray-900 font-semibold">Laporan</a>
                    <a href="/masjid/admin/data_user.php" class="nav-link block w-full text-gray-900 font-semibold">Data User</a>
                <?php else: ?>
                    <a href="/masjid/user/dashboard.php" class="nav-link block w-full text-gray-900 font-semibold">Dashboard</a>
                    <a href="/masjid/user/struktur.php" class="nav-link block w-full text-gray-900 font-semibold">Struktur Masjid</a>
                    <a href="/masjid/user/pengajian.php" class="nav-link block w-full text-gray-900 font-semibold">Jadwal Pengajian</a>
                    <a href="/masjid/user/sedekah.php" class="nav-link block w-full text-gray-900 font-semibold">Kirim Sedekah</a>
                    <a href="/masjid/user/tabungan.php" class="nav-link block w-full text-gray-900 font-semibold">Tabungan</a>
                    <a href="/masjid/user/laporan.php" class="nav-link block w-full text-gray-900 font-semibold">Kirim Laporan</a>
                <?php endif; ?>
            </div>

            <!-- User Info -->
            <div class="absolute bottom-0 left-0 right-0 p-4 border-t border-gray-200">
                <div class="flex items-center space-x-4">
                    <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center">
                        <span class="text-white font-bold">
                            <?= isset($_SESSION['name']) && $_SESSION['name'] ? strtoupper(substr($_SESSION['name'], 0, 1)) : 'A' ?>
                        </span>
                    </div>
                    <div>
                        <p class="text-sm font-medium">
                            <?= isset($_SESSION['name']) && $_SESSION['name'] ? htmlspecialchars($_SESSION['name']) : 'Admin' ?>
                        </p>
                        <p class="text-xs text-gray-400">Admin</p>
                    </div>
                    <a href="/masjid/logout.php" class="p-2 text-gray-500 hover:text-red-500">
                        <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M3 3a1 1 0 011 1v12a1 1 0 11-2 0V4a1 1 0 011-1zm7.707 3.293a1 1 0 010 1.414L9.414 9H17a1 1 0 110 2H9.414l1.293 1.293a1 1 0 01-1.414 1.414l-3-3a1 1 0 010-1.414l3-3a1 1 0 011.414 0z" clip-rule="evenodd" />
                        </svg>
                    </a>
                </div>
            </div>
        </nav>
    </aside>

    <!-- Main Content -->
    <div class="lg:ml-64 flex-1">
        <!-- Top Bar -->
        <header class="bg-white shadow-sm">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-16">
                    <div class="flex-1 min-w-0">
                        <h2 class="text-lg font-medium leading-7 text-gray-900 sm:truncate">
                            <?php
                            $current_page = basename($_SERVER['PHP_SELF'], '.php');
                            echo ucwords(str_replace('_', ' ', $current_page));
                            ?>
                        </h2>
                    </div>
                </div>
            </div>
        </header>

        <!-- Page Content -->
        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Content will be inserted here -->
