<?php
include '../includes/header.php';
include '../config/db.php';

if ($_SESSION['role'] !== 'admin') {
    header('Location: /masjid/login.php');
    exit;
}

// Tambah data jika form disubmit
if (isset($_POST['submit'])) {
    $jabatan = $_POST['jabatan'];
    $nama = $_POST['nama'];
    $level = $_POST['level']; // ambil level dari form

    // Gunakan prepared statement untuk keamanan
    $stmt = $conn->prepare("INSERT INTO struktur (jabatan, nama, level) VALUES (?, ?, ?)");
    $stmt->bind_param("ssi", $jabatan, $nama, $level);
    $stmt->execute();
    $stmt->close();
}

// Ambil data dengan urutan level ASC (jabatan tertinggi di atas)
$result = $conn->query("SELECT * FROM struktur ORDER BY level ASC");
?>

<div class="container mx-auto px-4 py-8">
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Form Input Struktur -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-2xl font-bold text-gray-800 mb-6">Tambah Struktur Organisasi</h2>
            
            <form method="post" class="space-y-6">
                <div>
                    <label for="jabatan" class="block text-sm font-medium text-gray-700 mb-2">
                        Jabatan
                    </label>
                    <input type="text" name="jabatan" id="jabatan" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-[#16A085] focus:border-transparent">
                </div>

                <div>
                    <label for="nama" class="block text-sm font-medium text-gray-700 mb-2">
                        Nama
                    </label>
                    <input type="text" name="nama" id="nama" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-[#16A085] focus:border-transparent">
                </div>

                <div>
                    <label for="level" class="block text-sm font-medium text-gray-700 mb-2">
                        Level (Urutan)
                    </label>
                    <input type="number" name="level" id="level" required min="1"
                        class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-[#16A085] focus:border-transparent">
                    <p class="mt-1 text-sm text-gray-500">Semakin kecil angka, semakin tinggi posisinya</p>
                </div>

                <button type="submit" name="submit"
                    class="w-full bg-[#16A085] text-white py-2 px-4 rounded-md hover:bg-opacity-90 focus:outline-none focus:ring-2 focus:ring-[#16A085] focus:ring-offset-2 transition-colors">
                    Simpan Data
                </button>
            </form>
        </div>

        <!-- Preview Struktur -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold text-gray-800">Data Struktur Organisasi</h2>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Level</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jabatan</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if ($result->num_rows === 0): ?>
                        <tr>
                            <td colspan="4" class="px-6 py-4 text-center text-gray-500">
                                Belum ada data struktur organisasi
                            </td>
                        </tr>
                        <?php else: ?>
                            <?php while($row = $result->fetch_assoc()): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?= htmlspecialchars($row['level']) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?= htmlspecialchars($row['jabatan']) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?= htmlspecialchars($row['nama']) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <a href="?edit=<?= $row['id'] ?>" class="text-[#16A085] hover:text-[#16A085]/80">Edit</a>
                                    <a href="?delete=<?= $row['id'] ?>" class="ml-3 text-red-600 hover:text-red-900" 
                                       onclick="return confirm('Yakin ingin menghapus data ini?')">Hapus</a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Preview Image Struktur -->
    <div class="mt-8 bg-white rounded-lg shadow-md p-6">
        <h2 class="text-2xl font-bold text-gray-800 mb-6">Preview Struktur Organisasi</h2>
        <div class="flex justify-center">
            <img src="../image2.png" 
                 alt="Struktur Organisasi"
                 class="max-w-full h-auto rounded-lg shadow-md"
            >
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
