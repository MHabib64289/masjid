<?php
session_start();
if (!isset($_SESSION['role'])) {
    die('Akses ditolak');
}
$role = $_SESSION['role'];
$name = isset($_SESSION['name']) ? $_SESSION['name'] : 'User';
$user_id = $_SESSION['user_id'] ?? null;

// --- Ambil data ringkasan ---
include '../config/db.php';
// Total laporan user
$total_laporan = 0;
if ($user_id) {
    $res = $conn->query("SELECT COUNT(*) as total FROM laporan WHERE user_id = $user_id");
    $total_laporan = $res ? $res->fetch_assoc()['total'] : 0;
}
// Total sedekah user
$total_sedekah = 0;
$sum_sedekah = 0;
if ($user_id) {
    $res = $conn->query("SELECT COUNT(*) as total, COALESCE(SUM(jumlah),0) as sum FROM sedekah WHERE user_id = $user_id");
    if ($res) {
        $row = $res->fetch_assoc();
        $total_sedekah = $row['total'];
        $sum_sedekah = $row['sum'];
    }
}
// Saldo tabungan masjid
$saldo = 0;
$sum_result = $conn->query("SELECT jenis, SUM(jumlah) as total FROM tabungan GROUP BY jenis");
while ($sum = $sum_result->fetch_assoc()) {
    if ($sum['jenis'] == 'pemasukan') {
        $saldo += $sum['total'];
    } else {
        $saldo -= $sum['total'];
    }
}
// Pengajian mendatang (1 terdekat)
$pengajian = $conn->query("SELECT * FROM pengajian WHERE tanggal >= CURDATE() ORDER BY tanggal, waktu ASC LIMIT 1");
$next_pengajian = $pengajian && $pengajian->num_rows > 0 ? $pengajian->fetch_assoc() : null;
// Aktivitas terakhir user (laporan & sedekah)
if ($user_id) {
    $activities = $conn->query("
        SELECT type, tanggal, jumlah, isi FROM (
            SELECT 'sedekah' as type, tanggal, jumlah, NULL as isi FROM sedekah WHERE user_id = $user_id
            UNION ALL
            SELECT 'laporan' as type, tanggal, NULL as jumlah, isi FROM laporan WHERE user_id = $user_id
        ) as all_activities
        ORDER BY tanggal DESC LIMIT 5
    ");
} else {
    $activities = false;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">
    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <aside class="w-64 bg-slate-800 text-white flex flex-col justify-between">
            <div>
                <div class="px-6 py-6 text-2xl font-bold tracking-wide bg-slate-900 mb-4">Masjid Management</div>
                <nav class="flex flex-col space-y-2 px-4">
                    <a href="#" class="py-2 px-4 rounded hover:bg-slate-700 bg-slate-900 font-semibold">Dashboard</a>
                    <a href="struktur.php" class="py-2 px-4 rounded hover:bg-slate-700">Struktur Masjid</a>
                    <a href="pengajian.php" class="py-2 px-4 rounded hover:bg-slate-700">Jadwal Pengajian</a>
                    <a href="sedekah.php" class="py-2 px-4 rounded hover:bg-slate-700">Data Sedekah</a>
                    <a href="tabungan.php" class="py-2 px-4 rounded hover:bg-slate-700">Tabungan Masjid</a>
                    <?php if ($role != 'guest'): ?>
                        <a href="laporan.php" class="py-2 px-4 rounded hover:bg-slate-700">Laporan</a>
                    <?php endif; ?>
                </nav>
            </div>
            <div class="flex items-center space-x-3 px-6 py-4 border-t border-slate-700">
                <div class="w-10 h-10 rounded-full bg-slate-200 flex items-center justify-center text-slate-700 font-bold text-lg">
                    <?php echo strtolower(substr($name,0,1)); ?>
                </div>
                <div>
                    <div class="font-semibold text-white leading-tight"><?php echo htmlspecialchars($name); ?></div>
                    <div class="text-xs text-slate-300 capitalize"><?php echo $role; ?></div>
                </div>
                <a href="../logout.php" class="ml-auto text-slate-400 hover:text-white" title="Logout">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a2 2 0 01-2 2H7a2 2 0 01-2-2V7a2 2 0 012-2h4a2 2 0 012 2v1" /></svg>
                </a>
            </div>
        </aside>
        <!-- Main Content -->
        <main class="flex-1 p-10">
            <div class="mb-8">
                <h1 class="text-2xl font-bold text-slate-800 mb-1">Dashboard User</h1>
                <p class="text-slate-600 mb-2">Pantau dan kelola aktivitas Anda di masjid.</p>
                <div class="bg-blue-100 border border-blue-200 text-blue-800 rounded-lg px-4 py-3 mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M12 20a8 8 0 100-16 8 8 0 000 16z"/></svg>
                    <span>Selamat datang, <span class="font-semibold"><?php echo htmlspecialchars($name); ?></span>! Semoga hari Anda penuh berkah dan semangat berkontribusi untuk masjid.</span>
                </div>
            </div>
            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="bg-white rounded-xl shadow p-6 flex flex-col items-start">
                    <span class="text-slate-500 mb-2 flex items-center gap-2"><svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>Status Akun</span>
                    <span class="text-2xl font-bold text-slate-800 capitalize"><?php echo $role; ?></span>
                    <span class="text-xs text-slate-400 mt-1">Akses <?php echo $role == 'guest' ? 'Tamu' : 'User'; ?></span>
                </div>
                <div class="bg-white rounded-xl shadow p-6 flex flex-col items-start">
                    <span class="text-slate-500 mb-2 flex items-center gap-2"><svg class="w-5 h-5 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M3 13h2v-2H3v2zm0 4h2v-2H3v2zm0-8h2V7H3v2zm4 8h2v-2H7v2zm0-4h2v-2H7v2zm0-4h2V7H7v2zm4 8h2v-2h-2v2zm0-4h2v-2h-2v2zm0-4h2V7h-2v2zm4 8h2v-2h-2v2zm0-4h2v-2h-2v2zm0-4h2V7h-2v2z"/></svg>Total Laporan</span>
                    <span class="text-2xl font-bold text-slate-800"><?php echo $total_laporan; ?></span>
                    <span class="text-xs text-slate-400 mt-1">Laporan terkirim</span>
                </div>
                <div class="bg-white rounded-xl shadow p-6 flex flex-col items-start">
                    <span class="text-slate-500 mb-2 flex items-center gap-2"><svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 8c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm0 10c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>Total Sedekah</span>
                    <span class="text-2xl font-bold text-slate-800">Rp <?php echo number_format($sum_sedekah, 0, ',', '.'); ?></span>
                    <span class="text-xs text-slate-400 mt-1"><?php echo $total_sedekah; ?> transaksi</span>
                </div>
                <div class="bg-white rounded-xl shadow p-6 flex flex-col items-start">
                    <span class="text-slate-500 mb-2 flex items-center gap-2"><svg class="w-5 h-5 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 8c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm0 10c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>Saldo Tabungan</span>
                    <span class="text-2xl font-bold text-slate-800">Rp <?php echo number_format($saldo, 0, ',', '.'); ?></span>
                    <span class="text-xs text-slate-400 mt-1">Tabungan masjid</span>
                </div>
            </div>
            <!-- Content Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Upcoming Event -->
                <div class="bg-white rounded-xl shadow p-6">
                    <h2 class="text-lg font-semibold mb-4">Jadwal Pengajian Mendatang</h2>
                    <?php if ($next_pengajian): ?>
                        <div class="flex items-center space-x-4 p-4 bg-blue-50 rounded-lg">
                            <div class="flex-shrink-0 w-12 h-12 flex items-center justify-center bg-blue-100 rounded-lg">
                                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            </div>
                            <div>
                                <h3 class="font-medium text-slate-900"><?php echo htmlspecialchars($next_pengajian['tema']); ?></h3>
                                <p class="text-sm text-slate-600"><?php echo htmlspecialchars($next_pengajian['penceramah']); ?></p>
                                <p class="text-sm text-slate-500 mt-1">
                                    <?php echo date('d F Y', strtotime($next_pengajian['tanggal'])); ?> •
                                    <?php echo date('H:i', strtotime($next_pengajian['waktu'])); ?> WIB
                                </p>
                            </div>
                        </div>
                    <?php else: ?>
                        <p class="text-slate-500 text-center py-4">Belum ada jadwal pengajian mendatang.</p>
                    <?php endif; ?>
                </div>
                <!-- Recent Activities -->
                <div class="bg-white rounded-xl shadow p-6">
                    <h2 class="text-lg font-semibold mb-4">Aktivitas Terakhir</h2>
                    <?php if ($activities && $activities->num_rows > 0): ?>
                        <div class="divide-y">
                            <?php while($activity = $activities->fetch_assoc()): ?>
                                <div class="flex items-start space-x-4 py-4">
                                    <div class="flex-shrink-0">
                                        <?php if ($activity['type'] === 'sedekah'): ?>
                                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-green-100">
                                                <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 8c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm0 10c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
                                            </span>
                                        <?php else: ?>
                                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-blue-100">
                                                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M3 13h2v-2H3v2zm0 4h2v-2H3v2zm0-8h2V7H3v2zm4 8h2v-2H7v2zm0-4h2v-2H7v2zm0-4h2V7H7v2zm4 8h2v-2h-2v2zm0-4h2v-2h-2v2zm0-4h2V7h-2v2zm4 8h2v-2h-2v2zm0-4h2v-2h-2v2zm0-4h2V7h-2v2z"/></svg>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-slate-900">
                                            <?php
                                            if ($activity['type'] === 'sedekah') {
                                                echo "Sedekah: Rp " . number_format($activity['jumlah'], 0, ',', '.');
                                            } else {
                                                echo "Laporan: " . htmlspecialchars($activity['isi']);
                                            }
                                            ?>
                                        </p>
                                        <p class="text-sm text-slate-500"><?php echo date('d F Y', strtotime($activity['tanggal'])); ?></p>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-slate-500 text-center py-4">Belum ada aktivitas.</p>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</body>
</html>