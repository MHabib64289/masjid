<?php
include '../config/db.php';
include '../includes/header.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../logout.php');
    exit;
}

// Proses kirim atau update tanggapan
if (isset($_POST['tanggapi'])) {
    $id = intval($_POST['id']);
    $tanggapan = $conn->real_escape_string($_POST['tanggapan']);

    // Proses upload foto bukti tanggapan
    $bukti_name = null;
    if (isset($_FILES['bukti']) && $_FILES['bukti']['error'] === 0) {
        $target_dir = "../uploads/";
        if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
        $ext = pathinfo($_FILES['bukti']['name'], PATHINFO_EXTENSION);
        $bukti_name = 'bukti_' . time() . '_' . $id . '.' . $ext;
        $target_file = $target_dir . $bukti_name;

        if (!move_uploaded_file($_FILES['bukti']['tmp_name'], $target_file)) {
            echo "<p style='color:red'>Gagal mengupload bukti gambar.</p>";
            $bukti_name = null;
        }
    }

    // Update database
    $sql = "UPDATE laporan SET tanggapan='$tanggapan', status='ditanggapi'";
    if ($bukti_name) {
        $sql .= ", bukti_tanggapan='$bukti_name'";
    }
    $sql .= " WHERE id=$id";

    if ($conn->query($sql)) {
        echo "<p style='color:green'>Tanggapan berhasil dikirim untuk laporan ID $id.</p>";
    } else {
        echo "<p style='color:red'>Error: " . $conn->error . "</p>";
    }
}

// Tangkap ID jika sedang mengedit tanggapan
$edit_id = isset($_GET['edit']) ? intval($_GET['edit']) : null;

// Ambil semua laporan
$result = $conn->query("SELECT l.*, u.name FROM laporan l JOIN users u ON l.user_id = u.id ORDER BY l.tanggal DESC");

// Hitung statistik laporan
$stats_query = $conn->query("
    SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN status = 'belum_ditanggapi' THEN 1 ELSE 0 END) as pending,
        SUM(CASE WHEN status = 'ditanggapi' THEN 1 ELSE 0 END) as handled
    FROM laporan
");
$stats = $stats_query->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan - Admin Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
    <div class="min-h-screen flex">
        

        <!-- Main Content -->
        <div class="flex-1 p-8">
            <div class="max-w-7xl mx-auto">
                <div class="mb-8">
                    <h1 class="text-2xl font-semibold text-gray-800 mb-2">Data Laporan</h1>
                    
                    <!-- Stats Cards -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                        <div class="bg-white rounded-lg shadow-sm p-4">
                            <div class="text-sm font-medium text-gray-500 mb-1">Total Laporan</div>
                            <div class="text-2xl font-semibold text-gray-800"><?= $stats['total'] ?></div>
                        </div>
                        <div class="bg-white rounded-lg shadow-sm p-4">
                            <div class="text-sm font-medium text-yellow-600 mb-1">Menunggu Tanggapan</div>
                            <div class="text-2xl font-semibold text-yellow-600"><?= $stats['pending'] ?></div>
                        </div>
                        <div class="bg-white rounded-lg shadow-sm p-4">
                            <div class="text-sm font-medium text-green-600 mb-1">Sudah Ditanggapi</div>
                            <div class="text-2xl font-semibold text-green-600"><?= $stats['handled'] ?></div>
                        </div>
                    </div>
                </div>

                <!-- Table Section -->
                <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jenis</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Foto</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php while($row = $result->fetch_assoc()): ?>
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900"><?= htmlspecialchars($row['name']) ?></div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900"><?= htmlspecialchars($row['jenis']) ?></div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900"><?= $row['tanggal'] ?></div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                <?= $row['status'] == 'ditanggapi' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' ?>">
                                                <?= $row['status'] == 'ditanggapi' ? 'Sudah Ditanggapi' : 'Menunggu Tanggapan' ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <?php if (!empty($row['foto'])): ?>
                                                <a href="../uploads/<?= $row['foto'] ?>" target="_blank" class="text-blue-600 hover:text-blue-900">
                                                    Lihat Foto
                                                </a>
                                            <?php else: ?>
                                                <span class="text-gray-500">Tidak Ada</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <?php if ($row['status'] == 'belum_ditanggapi'): ?>
                                                <a href="?edit=<?= $row['id'] ?>" class="text-blue-600 hover:text-blue-900">Tanggapi</a>
                                            <?php else: ?>
                                                <a href="?edit=<?= $row['id'] ?>" class="text-gray-600 hover:text-gray-900">Lihat Tanggapan</a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>

                                    <?php if ($edit_id == $row['id']): ?>
                                        <tr class="bg-gray-50">
                                            <td colspan="6" class="px-6 py-4">
                                                <form method="post" enctype="multipart/form-data" class="space-y-4">
                                                    <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                                    
                                                    <div>
                                                        <label class="block text-sm font-medium text-gray-700 mb-2">
                                                            Deskripsi Laporan
                                                        </label>
                                                        <p class="text-sm text-gray-600 bg-gray-100 p-3 rounded">
                                                            <?= isset($row['keterangan']) ? nl2br(htmlspecialchars($row['keterangan'])) : '<span class="italic text-gray-400">(Tidak ada deskripsi)</span>' ?>
                                                        </p>
                                                    </div>

                                                    <div>
                                                        <label class="block text-sm font-medium text-gray-700 mb-2">
                                                            Tanggapan
                                                        </label>
                                                        <textarea 
                                                            name="tanggapan" 
                                                            rows="3" 
                                                            class="w-full p-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500"
                                                            placeholder="Tulis tanggapan Anda di sini..."
                                                            required
                                                        ><?= $row['tanggapan'] ?? '' ?></textarea>
                                                    </div>

                                                    <div>
                                                        <label class="block text-sm font-medium text-gray-700 mb-2">
                                                            Bukti Tanggapan (Opsional)
                                                        </label>
                                                        <input 
                                                            type="file" 
                                                            name="bukti" 
                                                            accept="image/*"
                                                            class="w-full"
                                                        >
                                                    </div>

                                                    <div class="flex justify-end space-x-3">
                                                        <a href="laporan.php" class="px-4 py-2 text-gray-700 bg-gray-100 rounded hover:bg-gray-200">
                                                            Batal
                                                        </a>
                                                        <button type="submit" name="tanggapi" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                                                            <?= empty($row['tanggapan']) ? 'Kirim Tanggapan' : 'Update Tanggapan' ?>
                                                        </button>
                                                    </div>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

<?php include '../includes/footer.php'; ?>