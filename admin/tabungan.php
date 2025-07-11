<?php
session_start();
include '../config/db.php';

// Cek autentikasi
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../logout.php');
    exit;
}

// Inisialisasi pesan
$success_message = $_SESSION['success_message'] ?? null;
$error_message = $_SESSION['error_message'] ?? null;
unset($_SESSION['success_message'], $_SESSION['error_message']);

// Tambah tabungan
if (isset($_POST['submit'])) {
    $keterangan = $_POST['keterangan'];
    $jumlah = $_POST['jumlah'];
    $jenis = $_POST['jenis'];
    $tanggal = $_POST['tanggal'];
    
    try {
        $stmt = $conn->prepare("INSERT INTO tabungan (keterangan, jumlah, jenis, tanggal) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("siss", $keterangan, $jumlah, $jenis, $tanggal);
        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Data tabungan berhasil ditambahkan.";
        } else {
            $_SESSION['error_message'] = "Gagal menambahkan data tabungan.";
        }
        $stmt->close();
        header("Location: tabungan.php");
        exit;
    } catch (Exception $e) {
        $_SESSION['error_message'] = "Error: " . $e->getMessage();
        header("Location: tabungan.php");
        exit;
    }
}

// Hapus tabungan
if (isset($_GET['hapus'])) {
    $id = intval($_GET['hapus']);
    try {
        $stmt = $conn->prepare("DELETE FROM tabungan WHERE id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Data tabungan berhasil dihapus.";
        } else {
            $_SESSION['error_message'] = "Gagal menghapus data tabungan.";
        }
        $stmt->close();
    } catch (Exception $e) {
        $_SESSION['error_message'] = "Error: " . $e->getMessage();
    }
    header("Location: tabungan.php");
    exit;
}

// Ambil data untuk edit
$edit_data = null;
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $edit_result = $conn->query("SELECT * FROM tabungan WHERE id = $id");
    $edit_data = $edit_result->fetch_assoc();
}

// Update tabungan
if (isset($_POST['update'])) {
    $id = $_POST['id'];
    $keterangan = $_POST['keterangan'];
    $jumlah = $_POST['jumlah'];
    $jenis = $_POST['jenis'];
    $tanggal = $_POST['tanggal'];
    $stmt = $conn->prepare("UPDATE tabungan SET keterangan=?, jumlah=?, jenis=?, tanggal=? WHERE id=?");
    $stmt->bind_param("sissi", $keterangan, $jumlah, $jenis, $tanggal, $id);
    $stmt->execute();
    header("Location: tabungan.php");
    exit;
}

// Ambil semua data tabungan
$result = $conn->query("SELECT * FROM tabungan ORDER BY tanggal DESC");

// Hitung total
$saldo = 0;
$sum_result = $conn->query("SELECT jenis, SUM(jumlah) as total FROM tabungan GROUP BY jenis");
while ($sum = $sum_result->fetch_assoc()) {
    if ($sum['jenis'] == 'pemasukan') {
        $saldo += $sum['total'];
    } else {
        $saldo -= $sum['total'];
    }
}

// Include header setelah semua logika PHP
include '../includes/header.php';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tabungan Masjid - Admin Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
    <div class="min-h-screen flex">
        <!-- Main Content -->
        <div class="flex-1 p-8">
            <div class="max-w-7xl mx-auto">
                <h1 class="text-2xl font-semibold text-gray-900 mb-6">Tabungan Masjid</h1>

                <?php if ($success_message): ?>
                    <div class="mb-4 p-4 bg-green-100 border-l-4 border-green-500 text-green-700">
                        <?= $success_message ?>
                    </div>
                <?php endif; ?>

                <?php if ($error_message): ?>
                    <div class="mb-4 p-4 bg-red-100 border-l-4 border-red-500 text-red-700">
                        <?= $error_message ?>
                    </div>
                <?php endif; ?>

                <div class="mb-8">
                    <div class="bg-white rounded-lg shadow-sm p-4 mb-6">
                        <div class="text-xl font-semibold text-gray-700">
                            Total Saldo: 
                            <span class="<?= $saldo >= 0 ? 'text-green-600' : 'text-red-600' ?>">
                                Rp <?= number_format($saldo, 0, ',', '.') ?>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Form Section -->
                <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                    <form method="post" class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm text-gray-700 mb-2">Keterangan</label>
                                <input 
                                    type="text" 
                                    name="keterangan" 
                                    class="w-full p-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500" 
                                    placeholder="Masukkan keterangan"
                                    value="<?= $edit_data['keterangan'] ?? '' ?>" 
                                    required
                                >
                            </div>
                            <div>
                                <label class="block text-sm text-gray-700 mb-2">Jumlah</label>
                                <input 
                                    type="number" 
                                    name="jumlah" 
                                    class="w-full p-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500" 
                                    placeholder="Masukkan jumlah"
                                    value="<?= $edit_data['jumlah'] ?? '' ?>" 
                                    required
                                >
                            </div>
                            <div>
                                <label class="block text-sm text-gray-700 mb-2">Jenis</label>
                                <select 
                                    name="jenis" 
                                    class="w-full p-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500"
                                    required
                                >
                                    <option value="pemasukan" <?= (isset($edit_data) && $edit_data['jenis'] == 'pemasukan') ? 'selected' : '' ?>>Pemasukan</option>
                                    <option value="pengeluaran" <?= (isset($edit_data) && $edit_data['jenis'] == 'pengeluaran') ? 'selected' : '' ?>>Pengeluaran</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm text-gray-700 mb-2">Tanggal</label>
                                <input 
                                    type="date" 
                                    name="tanggal" 
                                    class="w-full p-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500" 
                                    value="<?= $edit_data['tanggal'] ?? '' ?>" 
                                    required
                                >
                            </div>
                        </div>
                        <div class="flex justify-end space-x-3">
                            <?php if ($edit_data): ?>
                                <input type="hidden" name="id" value="<?= $edit_data['id'] ?>">
                                <a href="tabungan.php" class="px-4 py-2 text-gray-700 bg-gray-100 rounded hover:bg-gray-200">
                                    Batal
                                </a>
                                <button type="submit" name="update" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                                    Update
                                </button>
                            <?php else: ?>
                                <button type="submit" name="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                                    Tambah
                                </button>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>

                <!-- Table Section -->
                <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Keterangan</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jenis</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jumlah</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php while($row = $result->fetch_assoc()): ?>
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <?= $row['tanggal'] ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <?= htmlspecialchars($row['keterangan']) ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                <?= $row['jenis'] == 'pemasukan' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                                                <?= ucfirst($row['jenis']) ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            <span class="<?= $row['jenis'] == 'pemasukan' ? 'text-green-600' : 'text-red-600' ?>">
                                                Rp <?= number_format($row['jumlah'], 0, ',', '.') ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                            <a href="?edit=<?= $row['id'] ?>" class="text-blue-600 hover:text-blue-900">Edit</a>
                                            <a href="?hapus=<?= $row['id'] ?>" onclick="return confirm('Apakah Anda yakin ingin menghapus data ini?')" class="text-red-600 hover:text-red-900">Hapus</a>
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
