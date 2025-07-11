<?php
include '../config/db.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../logout.php');
    exit;
}

// Auto hapus data jika tanggal + waktu sudah lewat
$conn->query("DELETE FROM pengajian WHERE CONCAT(tanggal, ' ', waktu) < NOW()");

// Tambah pengajian
if (isset($_POST['submit'])) {
    $tema = $_POST['tema'];
    $penceramah = $_POST['penceramah'];
    $tanggal = $_POST['tanggal'];
    $waktu = $_POST['waktu'];
    $stmt = $conn->prepare("INSERT INTO pengajian (tema, penceramah, tanggal, waktu) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $tema, $penceramah, $tanggal, $waktu);
    $stmt->execute();
    $stmt->close();
    header("Location: pengajian.php");
    exit;
}

// Hapus pengajian
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    $conn->query("DELETE FROM pengajian WHERE id = $id");
    header("Location: pengajian.php");
    exit;
}

// Untuk edit
$edit_data = null;
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $edit_result = $conn->query("SELECT * FROM pengajian WHERE id = $id");
    $edit_data = $edit_result->fetch_assoc();
}

// Ambil data pengajian
$result = $conn->query("SELECT * FROM pengajian ORDER BY tanggal, waktu ASC");

include '../includes/header.php';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jadwal Pengajian - Admin Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
    <div class="min-h-screen flex">
        
        <!-- Main Content -->
        <div class="flex-1 p-8">
            <div class="max-w-7xl mx-auto">
                <h1 class="text-2xl font-semibold text-gray-800 mb-6">Jadwal Pengajian</h1>

                <!-- Simple Form Section -->
                <div class="bg-whhite rounded-lg shadow-sm p-6 mb-6">
                    <form method="post" class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm text-gray-700 mb-2">Tema</label>
                                <input 
                                    type="text" 
                                    name="tema" 
                                    class="w-full p-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500" 
                                    placeholder="Masukkan tema pengajian"
                                    value="<?= $edit_data['tema'] ?? '' ?>"
                                    required
                                >
                            </div>
                            <div>
                                <label class="block text-sm text-gray-700 mb-2">Penceramah</label>
                                <input 
                                    type="text" 
                                    name="penceramah" 
                                    class="w-full p-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500" 
                                    placeholder="Nama penceramah"
                                    value="<?= $edit_data['penceramah'] ?? '' ?>"
                                    required
                                >
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
                            <div>
                                <label class="block text-sm text-gray-700 mb-2">Waktu</label>
                                <input 
                                    type="time" 
                                    name="waktu" 
                                    class="w-full p-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500" 
                                    value="<?= $edit_data['waktu'] ?? '' ?>"
                                    required
                                >
                            </div>
                        </div>
                        <div class="flex justify-end">
                            <?php if ($edit_data): ?>
                                <input type="hidden" name="id" value="<?= $edit_data['id'] ?>">
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
                    <table class="min-w-full">
                        <thead class="bg-gray-50 text-gray-600 uppercase text-xs">
                            <tr>
                                <th class="py-3 px-4 text-left">Tema</th>
                                <th class="py-3 px-4 text-left">Penceramah</th>
                                <th class="py-3 px-4 text-left">Tanggal</th>
                                <th class="py-3 px-4 text-left">Waktu</th>
                                <th class="py-3 px-4 text-left">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php while($row = $result->fetch_assoc()): 
                                $jadwal = new DateTime($row['tanggal'] . ' ' . $row['waktu']);
                                $sekarang = new DateTime();
                                $beda = $sekarang->diff($jadwal)->days;
                                $warna = '';

                                if ($jadwal > $sekarang && $beda === 1) {
                                    $warna = 'bg-yellow-50';
                                }
                            ?>
                                <tr class="<?= $warna ?> hover:bg-gray-50">
                                    <td class="py-3 px-4"><?= htmlspecialchars($row['tema']) ?></td>
                                    <td class="py-3 px-4"><?= htmlspecialchars($row['penceramah']) ?></td>
                                    <td class="py-3 px-4"><?= $row['tanggal'] ?></td>
                                    <td class="py-3 px-4"><?= $row['waktu'] ?></td>
                                    <td class="py-3 px-4">
                                        <a href="?edit=<?= $row['id'] ?>" class="text-blue-600 hover:underline mr-3">Edit</a>
                                        <a href="?hapus=<?= $row['id'] ?>" onclick="return confirm('Apakah Anda yakin ingin menghapus data ini?')" class="text-red-600 hover:underline">Hapus</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
    document.querySelector('form').addEventListener('submit', function(e) {
        const tanggal = document.querySelector('input[name="tanggal"]').value;
        const waktu = document.querySelector('input[name="waktu"]').value;
        
        const jadwal = new Date(tanggal + 'T' + waktu);
        const sekarang = new Date();
        
        if (jadwal < sekarang) {
            e.preventDefault();
            alert('Tidak dapat menambahkan jadwal untuk waktu yang telah lewat');
        }
    });
    </script>
</body>
</html>

<?php include '../includes/footer.php'; ?>