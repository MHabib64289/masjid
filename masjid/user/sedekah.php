<?php
include '../config/db.php';
session_start();
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['user', 'guest'])) {
    die('Akses ditolak');
}
$role = $_SESSION['role'];
$name = isset($_SESSION['name']) ? $_SESSION['name'] : 'User';
$user_id = $_SESSION['user_id'] ?? null;
// Proses kirim sedekah: hanya untuk user login
if ($role === 'user' && isset($_POST['submit'])) {
    $jumlah = intval($_POST['jumlah']);
    $bukti_name = null;
    if (isset($_FILES['bukti']) && $_FILES['bukti']['error'] === 0) {
        $target_dir = "../uploads/";
        if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
        $ext = pathinfo($_FILES['bukti']['name'], PATHINFO_EXTENSION);
        $bukti_name = 'bukti_user_' . time() . '_' . $user_id . '.' . $ext;
        $target_file = $target_dir . $bukti_name;
        if (!move_uploaded_file($_FILES['bukti']['tmp_name'], $target_file)) {
            $bukti_name = null;
            $msg = [false, 'Upload bukti gagal.'];
        }
    }
    $stmt = $conn->prepare("INSERT INTO sedekah (user_id, jumlah, tanggal, bukti) VALUES (?, ?, CURDATE(), ?)");
    $stmt->bind_param("iis", $user_id, $jumlah, $bukti_name);
    $stmt->execute();
    // Catat juga ke tabungan masjid
    $keterangan = "Sedekah dari User ID $user_id";
    $jenis = "pemasukan";
    $tanggal = date('Y-m-d');
    $conn->query("INSERT INTO tabungan (tanggal, jumlah, jenis, keterangan) VALUES ('$tanggal', $jumlah, '$jenis', '$keterangan')");
    $msg = [true, 'Sedekah berhasil dikirim!'];
}
// Ambil data sedekah user
$sedekah = $user_id ? $conn->query("SELECT * FROM sedekah WHERE user_id = $user_id ORDER BY tanggal DESC") : false;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sedekah Masjid</title>
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
                <a href="struktur.php" class="py-2 px-4 rounded hover:bg-slate-700">Struktur Masjid</a>
                <a href="pengajian.php" class="py-2 px-4 rounded hover:bg-slate-700">Jadwal Pengajian</a>
                <a href="sedekah.php" class="py-2 px-4 rounded bg-slate-900 font-semibold">Data Sedekah</a>
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
            <h1 class="text-2xl font-bold text-slate-800 mb-1">Data Sedekah</h1>
            <p class="text-slate-600">Kirim dan pantau sedekah Anda ke masjid.</p>
        </div>
        <?php if ($role === 'user'): ?>
        <div class="bg-white rounded-xl shadow p-6 mb-8 max-w-xl">
            <h2 class="text-lg font-semibold mb-4">Kirim Sedekah</h2>
            <?php if (isset($msg)): ?>
                <div class="mb-4 p-3 rounded <?php echo $msg[0] ? 'bg-green-50 text-green-700' : 'bg-red-50 text-red-700'; ?>"><?php echo $msg[1]; ?></div>
            <?php endif; ?>
            <form method="post" enctype="multipart/form-data" class="space-y-4">
                <div>
                    <label class="block text-slate-700 mb-1">Jumlah Sedekah (Rp)</label>
                    <input type="number" name="jumlah" class="w-full border rounded px-3 py-2" required min="1000">
                </div>
                <div>
                    <label class="block text-slate-700 mb-1">Bukti Transfer (opsional)</label>
                    <input type="file" name="bukti" class="block w-full text-slate-700" accept="image/*">
                </div>
                <button type="submit" name="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded">Kirim</button>
            </form>
        </div>
        <?php endif; ?>
        <div class="bg-white rounded-xl shadow p-6">
            <h2 class="text-lg font-semibold mb-4">Riwayat Sedekah Anda</h2>
            <div class="overflow-x-auto">
            <table class="min-w-full border text-sm">
                <thead>
                    <tr class="bg-slate-100">
                        <th class="py-2 px-3 border">Tanggal</th>
                        <th class="py-2 px-3 border">Jumlah</th>
                        <th class="py-2 px-3 border">Bukti</th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($sedekah && $sedekah->num_rows > 0): ?>
                    <?php while($row = $sedekah->fetch_assoc()): ?>
                        <tr>
                            <td class="py-2 px-3 border"><?php echo htmlspecialchars($row['tanggal']); ?></td>
                            <td class="py-2 px-3 border">Rp <?php echo number_format($row['jumlah'], 0, ',', '.'); ?></td>
                            <td class="py-2 px-3 border">
                                <?php if ($row['bukti']): ?>
                                    <a href="../uploads/<?php echo $row['bukti']; ?>" target="_blank" class="text-blue-600 underline">Lihat</a>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="3" class="text-center py-4 text-slate-400">Belum ada sedekah.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
            </div>
        </div>
    </main>
</div>
</body>
</html>
