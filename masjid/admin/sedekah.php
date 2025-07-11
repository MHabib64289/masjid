<?php
include '../config/db.php';
include '../includes/header.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../logout.php');
    exit;
}

// Ambil data sedekah lengkap
$result = $conn->query("SELECT s.*, u.name FROM sedekah s JOIN users u ON s.user_id = u.id ORDER BY s.tanggal DESC");

// Hitung total sedekah
$total_query = $conn->query("SELECT SUM(jumlah) as total FROM sedekah");
$total_sedekah = $total_query->fetch_assoc()['total'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Sedekah - Admin Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
    <div class="min-h-screen flex">

        <!-- Main Content -->
        <div class="flex-1 p-8">
            <div class="max-w-7xl mx-auto">
                <div class="mb-8">
                    <h1 class="text-2xl font-semibold text-gray-800 mb-2">Data Sedekah</h1>
                    <div class="bg-white rounded-lg shadow-sm p-4 mb-6">
                        <div class="text-xl font-semibold text-gray-700">
                            Total Sedekah: 
                            <span class="text-green-600">
                                Rp <?= number_format($total_sedekah, 0, ',', '.') ?>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Table Section -->
                <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama User</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jumlah</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bukti</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php while($row = $result->fetch_assoc()): ?>
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">
                                                <?= htmlspecialchars($row['name']) ?>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-green-600">
                                                Rp <?= number_format($row['jumlah'], 0, ',', '.') ?>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">
                                                <?= $row['tanggal'] ?>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <?php if (!empty($row['bukti'])): ?>
                                                <a href="../uploads/<?= $row['bukti'] ?>" target="_blank" class="text-blue-600 hover:text-blue-900">
                                                    Lihat Bukti
                                                </a>
                                            <?php else: ?>
                                                <span class="text-gray-500">Tidak Ada</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
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