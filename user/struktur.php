<?php
include '../config/db.php';
session_start();
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['user', 'guest'])) {
    die('Akses ditolak');
}
$role = $_SESSION['role'];
$name = isset($_SESSION['name']) ? $_SESSION['name'] : 'User';
// Ambil data struktur
$result = $conn->query("SELECT * FROM struktur ORDER BY level ASC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struktur Masjid</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">
<div class="flex min-h-screen">
    <!-- Sidebar -->
    <aside class="w-64 bg-slate-800 text-white flex flex-col justify-between">
        <div>
            <div class="px-6 py-6 text-2xl font-bold tracking-wide bg-slate-900 mb-4">Masjid Management</div>
            <nav class="flex flex-col space-y-2 px-4">
                <a href="dashboard.php" class="py-2 px-4 rounded hover:bg-slate-700">Dashboard</a>
                <a href="struktur.php" class="py-2 px-4 rounded bg-slate-900 font-semibold">Struktur Masjid</a>
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
            <h1 class="text-2xl font-bold text-slate-800 mb-1">Struktur Masjid</h1>
            <p class="text-slate-600">Informasi pengurus dan struktur organisasi masjid.</p>
        </div>
        <div class="bg-white rounded-xl shadow p-6">
            <h2 class="text-lg font-semibold mb-4">Daftar Pengurus</h2>
            <div class="overflow-x-auto">
            <table class="min-w-full border text-sm">
                <thead>
                    <tr class="bg-slate-100">
                        <th class="py-2 px-3 border">Jabatan</th>
                        <th class="py-2 px-3 border">Nama</th>
                    </tr>
                </thead>
                <tbody>
                <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td class="py-2 px-3 border"><?= htmlspecialchars($row['jabatan']) ?></td>
                        <td class="py-2 px-3 border"><?= htmlspecialchars($row['nama']) ?></td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
            </div>
        </div>
    </main>
</div>
</body>
</html>
