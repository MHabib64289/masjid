<?php
require_once '../config/db.php';
require_once '../includes/header.php';

// Secure admin check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../logout.php");
    exit;
}

try {
    // Get total users with error handling
    $users_query = $conn->query("SELECT COUNT(*) as total FROM users WHERE role = 'user'");
    if (!$users_query) throw new Exception($conn->error);
    $users_count = $users_query->fetch_assoc()['total'];

    // Get financial stats with error handling
    $sedekah_query = $conn->query("SELECT COUNT(*) as total_transactions, COALESCE(SUM(jumlah), 0) as total_amount FROM sedekah");
    if (!$sedekah_query) throw new Exception($conn->error);
    $sedekah_stats = $sedekah_query->fetch_assoc();

    $tabungan_query = $conn->query("
        SELECT 
            COALESCE(SUM(CASE WHEN jenis = 'pemasukan' THEN jumlah ELSE 0 END), 0) as total_income,
            COALESCE(SUM(CASE WHEN jenis = 'pengeluaran' THEN jumlah ELSE 0 END), 0) as total_expense
        FROM tabungan
    ");
    if (!$tabungan_query) throw new Exception($conn->error);
    $tabungan_stats = $tabungan_query->fetch_assoc();
    $saldo = $tabungan_stats['total_income'] - $tabungan_stats['total_expense'];

    // Get report stats with error handling
    $laporan_query = $conn->query("
        SELECT 
            COUNT(*) as total,
            COUNT(CASE WHEN status = 'belum_ditanggapi' THEN 1 END) as pending,
            COUNT(CASE WHEN status = 'ditanggapi' THEN 1 END) as handled
        FROM laporan
    ");
    if (!$laporan_query) throw new Exception($conn->error);
    $laporan_stats = $laporan_query->fetch_assoc();

    // Get upcoming events with error handling
    $pengajian_query = $conn->query("
        SELECT * FROM pengajian 
        WHERE tanggal >= CURDATE() 
        ORDER BY tanggal, waktu ASC 
        LIMIT 3
    ");
    if (!$pengajian_query) throw new Exception($conn->error);

    // Get recent activities with error handling
    $activities_query = $conn->query("
        (SELECT 
            'sedekah' as type, 
            s.tanggal as date, 
            s.jumlah as amount, 
            u.name as user_name,
            s.id as id
        FROM sedekah s 
        JOIN users u ON s.user_id = u.id)
        UNION ALL
        (SELECT 
            'laporan' as type, 
            l.tanggal as date, 
            NULL as amount, 
            u.name as user_name,
            l.id as id
        FROM laporan l 
        JOIN users u ON l.user_id = u.id)
        UNION ALL
        (SELECT 
            'tabungan' as type, 
            t.tanggal as date, 
            t.jumlah as amount, 
            t.keterangan as user_name,
            t.id as id
        FROM tabungan t)
        ORDER BY date DESC 
        LIMIT 5
    ");
    if (!$activities_query) throw new Exception($conn->error);

} catch (Exception $e) {
    // Log error and show user-friendly message
    error_log("Dashboard Error: " . $e->getMessage());
    $error_message = "Terjadi kesalahan saat memuat data. Silakan coba lagi nanti.";
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Admin Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
    <div class="min-h-screen flex">
       
        <!-- Main Content -->
        <div class="flex-1 p-8">
            <!-- Welcome Section -->
            <div class="mb-8">
                <h1 class="text-2xl font-bold text-gray-800 mb-2">Dashboard Admin</h1>
                <p class="text-gray-600">Pantau dan kelola aktivitas masjid.</p>
                <div class="mt-2 mb-4 p-4 bg-emerald-50 border-l-4 border-emerald-500 text-emerald-800 rounded">
                    Selamat datang, <span class="font-semibold">
                        <?= isset($_SESSION['name']) && $_SESSION['name'] ? htmlspecialchars($_SESSION['name']) : 'Admin' ?>
                    </span>! Semoga harimu menyenangkan dan penuh berkah.
                </div>
                <?php if (isset($error_message)): ?>
                    <div class="mt-4 p-4 bg-red-50 border-l-4 border-red-500 text-red-700">
                        <?php echo $error_message; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Stats Section -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <!-- Total Users -->
                <div class="stats-card hover:shadow-md transition-shadow duration-300">
                    <div class="flex items-center justify-between mb-4">
                        <span class="stats-label">Total Jamaah</span>
                        <span class="p-2 bg-blue-50 rounded-full">
                            <svg class="w-5 h-5 text-blue-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3zM6 8a2 2 0 11-4 0 2 2 0 014 0zM16 18v-3a5.972 5.972 0 00-.75-2.906A3.005 3.005 0 0119 15v3h-3zM4.75 12.094A5.973 5.973 0 004 15v3H1v-3a3 3 0 013.75-2.906z" />
                            </svg>
                        </span>
                    </div>
                    <div class="flex flex-col">
                        <span class="text-2xl font-bold text-gray-900"><?php echo number_format($users_count); ?></span>
                        <span class="text-sm text-gray-500">User terdaftar</span>
                    </div>
                </div>

                <!-- Total Sedekah -->
                <div class="stats-card hover:shadow-md transition-shadow duration-300">
                    <div class="flex items-center justify-between mb-4">
                        <span class="stats-label">Total Sedekah</span>
                        <span class="p-2 bg-green-50 rounded-full">
                            <svg class="w-5 h-5 text-green-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M4 4a2 2 0 00-2 2v1h16V6a2 2 0 00-2-2H4z" />
                                <path fill-rule="evenodd" d="M18 9H2v5a2 2 0 002 2h12a2 2 0 002-2V9zM4 13a1 1 0 011-1h1a1 1 0 110 2H5a1 1 0 01-1-1z" clip-rule="evenodd" />
                            </svg>
                        </span>
                    </div>
                    <div class="flex flex-col">
                        <span class="text-2xl font-bold text-gray-900">Rp <?php echo number_format($sedekah_stats['total_amount'], 0, ',', '.'); ?></span>
                        <span class="text-sm text-gray-500"><?php echo number_format($sedekah_stats['total_transactions']); ?> transaksi</span>
                    </div>
                </div>

                <!-- Saldo Tabungan -->
                <div class="stats-card hover:shadow-md transition-shadow duration-300">
                    <div class="flex items-center justify-between mb-4">
                        <span class="stats-label">Saldo Tabungan</span>
                        <span class="p-2 bg-purple-50 rounded-full">
                            <svg class="w-5 h-5 text-purple-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M8.433 7.418c.155-.103.346-.196.567-.267v1.698a2.305 2.305 0 01-.567-.267C8.07 8.34 8 8.114 8 8c0-.114.07-.34.433-.582zM11 12.849v-1.698c.22.071.412.164.567.267.364.243.433.468.433.582 0 .114-.07.34-.433.582a2.305 2.305 0 01-.567.267z" />
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-13a1 1 0 10-2 0v.092a4.535 4.535 0 00-1.676.662C6.602 6.234 6 7.009 6 8c0 .99.602 1.765 1.324 2.246.48.32 1.054.545 1.676.662v1.941c-.391-.127-.68-.317-.843-.504a1 1 0 10-1.51 1.31c.562.649 1.413 1.076 2.353 1.253V15a1 1 0 102 0v-.092a4.535 4.535 0 001.676-.662C13.398 13.766 14 12.991 14 12c0-.99-.602-1.765-1.324-2.246A4.535 4.535 0 0011 9.092V7.151c.391.127.68.317.843.504a1 1 0 101.511-1.31c-.563-.649-1.413-1.076-2.354-1.253V5z" clip-rule="evenodd" />
                            </svg>
                        </span>
                    </div>
                    <div class="flex flex-col">
                        <span class="text-2xl font-bold text-gray-900">Rp <?php echo number_format($saldo, 0, ',', '.'); ?></span>
                        <div class="flex items-center space-x-2 text-sm text-gray-500">
                            <span class="text-green-600">+<?php echo number_format($tabungan_stats['total_income'], 0, ',', '.'); ?></span>
                            <span>/</span>
                            <span class="text-red-600">-<?php echo number_format($tabungan_stats['total_expense'], 0, ',', '.'); ?></span>
                        </div>
                    </div>
                </div>

                <!-- Laporan Stats -->
                <div class="stats-card hover:shadow-md transition-shadow duration-300">
                    <div class="flex items-center justify-between mb-4">
                        <span class="stats-label">Laporan</span>
                        <span class="p-2 bg-yellow-50 rounded-full">
                            <svg class="w-5 h-5 text-yellow-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd" />
                            </svg>
                        </span>
                    </div>
                    <div class="flex flex-col">
                        <span class="text-2xl font-bold text-gray-900"><?php echo number_format($laporan_stats['total']); ?></span>
                        <div class="flex items-center space-x-2 text-sm">
                            <span class="inline-flex items-center text-yellow-600">
                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
                                </svg>
                                <?php echo $laporan_stats['pending']; ?> pending
                            </span>
                            <span>/</span>
                            <span class="inline-flex items-center text-green-600">
                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                                <?php echo $laporan_stats['handled']; ?> selesai
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Content Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Upcoming Events -->
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">Jadwal Pengajian Mendatang</h2>
                        <a href="pengajian.php" class="text-sm text-accent hover:text-accent/80">Kelola</a>
                    </div>
                    <?php if ($pengajian_query->num_rows > 0): ?>
                        <div class="space-y-4">
                            <?php while($pengajian = $pengajian_query->fetch_assoc()): ?>
                                <div class="flex items-start space-x-4 p-4 bg-gray-50 rounded-lg">
                                    <div class="flex-shrink-0 w-12 h-12 flex items-center justify-center bg-accent/10 rounded-lg">
                                        <svg class="w-6 h-6 text-accent" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 class="font-medium text-gray-900"><?php echo htmlspecialchars($pengajian['tema']); ?></h3>
                                        <p class="text-sm text-gray-600"><?php echo htmlspecialchars($pengajian['penceramah']); ?></p>
                                        <p class="text-sm text-gray-500 mt-1">
                                            <?php echo date('d F Y', strtotime($pengajian['tanggal'])); ?> • 
                                            <?php echo date('H:i', strtotime($pengajian['waktu'])); ?> WIB
                                        </p>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-gray-500 text-center py-4">Belum ada jadwal pengajian mendatang.</p>
                    <?php endif; ?>
                </div>

                <!-- Recent Activities -->
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">Aktivitas Terakhir</h2>
                    </div>
                    <?php if ($activities_query->num_rows > 0): ?>
                        <div class="divide-y">
                            <?php while($activity = $activities_query->fetch_assoc()): ?>
                                <div class="flex items-start space-x-4 py-4">
                                    <div class="flex-shrink-0">
                                        <?php if ($activity['type'] === 'sedekah'): ?>
                                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-green-100">
                                                <svg class="w-4 h-4 text-green-600" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                    <path d="M4 4a2 2 0 00-2 2v1h16V6a2 2 0 00-2-2H4z" />
                                                    <path fill-rule="evenodd" d="M18 9H2v5a2 2 0 002 2h12a2 2 0 002-2V9zM4 13a1 1 0 011-1h1a1 1 0 110 2H5a1 1 0 01-1-1z" clip-rule="evenodd" />
                                                </svg>
                                            </span>
                                        <?php elseif ($activity['type'] === 'laporan'): ?>
                                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-blue-100">
                                                <svg class="w-4 h-4 text-blue-600" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd" />
                                                </svg>
                                            </span>
                                        <?php else: ?>
                                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-purple-100">
                                                <svg class="w-4 h-4 text-purple-600" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                    <path d="M8.433 7.418c.155-.103.346-.196.567-.267v1.698a2.305 2.305 0 01-.567-.267C8.07 8.34 8 8.114 8 8c0-.114.07-.34.433-.582zM11 12.849v-1.698c.22.071.412.164.567.267.364.243.433.468.433.582 0 .114-.07.34-.433.582a2.305 2.305 0 01-.567.267z" />
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-13a1 1 0 10-2 0v.092a4.535 4.535 0 00-1.676.662C6.602 6.234 6 7.009 6 8c0 .99.602 1.765 1.324 2.246.48.32 1.054.545 1.676.662v1.941c-.391-.127-.68-.317-.843-.504a1 1 0 10-1.51 1.31c.562.649 1.413 1.076 2.353 1.253V15a1 1 0 102 0v-.092a4.535 4.535 0 001.676-.662C13.398 13.766 14 12.991 14 12c0-.99-.602-1.765-1.324-2.246A4.535 4.535 0 0011 9.092V7.151c.391.127.68.317.843.504a1 1 0 101.511-1.31c-.563-.649-1.413-1.076-2.354-1.253V5z" clip-rule="evenodd" />
                                                </svg>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">
                                            <?php
                                            switch ($activity['type']) {
                                                case 'sedekah':
                                                    echo htmlspecialchars($activity['user_name']) . " mengirim sedekah Rp " . number_format($activity['amount'], 0, ',', '.');
                                                    break;
                                                case 'laporan':
                                                    echo htmlspecialchars($activity['user_name']) . " mengirim laporan baru";
                                                    break;
                                                case 'tabungan':
                                                    $jenis = strpos(strtolower($activity['user_name']), 'masuk') !== false ? 'Pemasukan' : 'Pengeluaran';
                                                    echo "$jenis: " . htmlspecialchars($activity['user_name']) . " (Rp " . number_format($activity['amount'], 0, ',', '.') . ")";
                                                    break;
                                            }
                                            ?>
                                        </p>
                                        <p class="text-sm text-gray-500"><?php echo date('d F Y', strtotime($activity['date'])); ?></p>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-gray-500 text-center py-4">Belum ada aktivitas.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>
</body>
</html>
