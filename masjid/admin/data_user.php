<?php
include '../config/db.php';
include '../includes/header.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../logout.php');
    exit;
}

// Initialize success and error messages
$success_message = $_SESSION['success_message'] ?? null;
$error_message = $_SESSION['error_message'] ?? null;
unset($_SESSION['success_message'], $_SESSION['error_message']);

// Get all users with basic stats
$result = $conn->query("SELECT * FROM users ORDER BY name");
$stats = $conn->query("SELECT 
    COUNT(*) as total,
    COUNT(CASE WHEN role = 'admin' THEN 1 END) as admin_count,
    COUNT(*) as active
    FROM users")->fetch_assoc();

// Set default stats values
$stats['inactive'] = 0;

// Handle activation/deactivation
if (isset($_GET['activate']) || isset($_GET['deactivate'])) {
    $id = isset($_GET['activate']) ? $_GET['activate'] : $_GET['deactivate'];
    $new_status = isset($_GET['activate']) ? 'active' : 'inactive';
    
    try {
        $stmt = $conn->prepare("UPDATE users SET status = ? WHERE id = ? AND role != 'admin'");
        $stmt->bind_param("si", $new_status, $id);
        $stmt->execute();
        if ($stmt->affected_rows > 0) {
            $_SESSION['success_message'] = "Status pengguna berhasil diperbarui.";
        }
        $stmt->close();
    } catch (Exception $e) {
        $_SESSION['error_message'] = "Gagal mengubah status pengguna.";
    }
    
    header("Location: data_user.php");
    exit;
}

// Handle user edit
if (isset($_POST['update_user'])) {
    $id = $_POST['user_id'];
    $name = $_POST['name'];
    $email = $_POST['email'];
    
    try {
        $stmt = $conn->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
        $stmt->bind_param("ssi", $name, $email, $id);
        $stmt->execute();
        if ($stmt->affected_rows > 0) {
            $_SESSION['success_message'] = "Data pengguna berhasil diperbarui.";
        }
        $stmt->close();
    } catch (Exception $e) {
        $_SESSION['error_message'] = "Gagal memperbarui data pengguna.";
    }
    header("Location: data_user.php");
    exit;
}

// Get user data for editing
$edit_data = null;
if (isset($_GET['edit'])) {
    $edit_id = $_GET['edit'];
    $edit_query = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $edit_query->bind_param("i", $edit_id);
    $edit_query->execute();
    $edit_data = $edit_query->get_result()->fetch_assoc();
    $edit_query->close();
}
?>
    <!-- Main Content -->
    <div class="flex-1 bg-gray-50 p-8">
        <div class="max-w-7xl mx-auto">
            <div class="mb-8">
                <div class="flex items-center justify-between mb-6">
                    <h1 class="text-3xl font-bold text-gray-900">Data User</h1>
                    <nav class="flex space-x-4" aria-label="Tabs">
                        <a href="#" class="bg-white text-sm font-medium text-gray-900 px-4 py-2 rounded-md shadow-sm hover:bg-gray-50 active">
                            Semua User
                        </a>
                        <a href="#" class="text-sm font-medium text-gray-500 px-4 py-2 rounded-md hover:text-gray-700 hover:bg-white">
                            Admin
                        </a>
                        <a href="#" class="text-sm font-medium text-gray-500 px-4 py-2 rounded-md hover:text-gray-700 hover:bg-white">
                            Regular User
                        </a>
                    </nav>
                </div>
                
                <?php if ($success_message): ?>
                    <div class="mb-4 flex p-4 bg-emerald-50 border-l-4 border-emerald-400 items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-emerald-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-emerald-700"><?= $success_message ?></p>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($error_message): ?>
                    <div class="mb-4 flex p-4 bg-rose-50 border-l-4 border-rose-400 items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-rose-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-rose-700"><?= $error_message ?></p>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Stats Grid -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                    <!-- Total Users -->
                    <div class="bg-white overflow-hidden rounded-2xl shadow-sm hover:shadow-md transition-shadow">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 rounded-xl bg-indigo-50 p-3">
                                    <svg class="h-6 w-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt class="text-sm font-medium text-gray-500 truncate">Total User</dt>
                                        <dd class="flex items-baseline">
                                            <div class="text-2xl font-semibold text-gray-900"><?= $stats['total'] ?></div>
                                            <div class="ml-2 flex items-baseline text-sm font-semibold text-indigo-600">
                                                <svg class="self-center flex-shrink-0 h-5 w-5 text-indigo-500" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M5.293 9.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L11 7.414V15a1 1 0 11-2 0V7.414L6.707 9.707a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                                </svg>
                                                <span class="sr-only">Meningkat</span>
                                            </div>
                                        </dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Active Users -->
                    <div class="bg-white overflow-hidden rounded-2xl shadow-sm hover:shadow-md transition-shadow">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 rounded-xl bg-green-50 p-3">
                                    <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt class="text-sm font-medium text-gray-500 truncate">User Aktif</dt>
                                        <dd class="flex items-baseline">
                                            <div class="text-2xl font-semibold text-gray-900"><?= $stats['active'] ?></div>
                                            <div class="ml-2 flex items-baseline text-sm font-semibold text-green-600">
                                                <svg class="self-center flex-shrink-0 h-5 w-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M5.293 9.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L11 7.414V15a1 1 0 11-2 0V7.414L6.707 9.707a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                                </svg>
                                                <span class="sr-only">Meningkat</span>
                                            </div>
                                        </dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Inactive Users -->
                    <div class="bg-white overflow-hidden rounded-2xl shadow-sm hover:shadow-md transition-shadow">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 rounded-xl bg-rose-50 p-3">
                                    <svg class="h-6 w-6 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt class="text-sm font-medium text-gray-500 truncate">User Nonaktif</dt>
                                        <dd class="flex items-baseline">
                                            <div class="text-2xl font-semibold text-gray-900"><?= $stats['inactive'] ?></div>
                                        </dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Admin Users -->
                    <div class="bg-white overflow-hidden rounded-2xl shadow-sm hover:shadow-md transition-shadow">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 rounded-xl bg-amber-50 p-3">
                                    <svg class="h-6 w-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                    </svg>
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt class="text-sm font-medium text-gray-500 truncate">Admin</dt>
                                        <dd class="flex items-baseline">
                                            <div class="text-2xl font-semibold text-gray-900"><?= $stats['admin_count'] ?></div>
                                        </dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Edit Form -->
                <?php if ($edit_data): ?>
                    <div class="bg-white rounded-lg shadow-lg p-8 mb-8 transform transition-all duration-300">
                        <div class="flex items-center justify-between mb-6">
                            <h3 class="text-xl font-semibold text-gray-800">Edit User</h3>
                            <a href="data_user.php" class="text-gray-400 hover:text-gray-500 transition-colors">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </a>
                        </div>
                        
                        <form method="post" class="space-y-6">
                            <input type="hidden" name="user_id" value="<?= $edit_data['id'] ?>">
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="space-y-2">
                                    <label class="block text-sm font-medium text-gray-700">Nama</label>
                                    <div class="relative rounded-md shadow-sm">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                            </svg>
                                        </div>
                                        <input type="text" name="name" 
                                            class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                                            value="<?= htmlspecialchars($edit_data['name']) ?>" 
                                            required
                                        >
                                    </div>
                                </div>
                                
                                <div class="space-y-2">
                                    <label class="block text-sm font-medium text-gray-700">Email</label>
                                    <div class="relative rounded-md shadow-sm">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" />
                                            </svg>
                                        </div>
                                        <input type="email" name="email" 
                                            class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                                            value="<?= htmlspecialchars($edit_data['email']) ?>" 
                                            required
                                        >
                                    </div>
                                </div>
                            </div>

                            <div class="flex justify-end space-x-4 pt-4">
                                <a href="data_user.php" 
                                   class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors">
                                    Batal
                                </a>
                                <button type="submit" name="update_user" 
                                    class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors">
                                    Simpan Perubahan
                                </button>
                            </div>
                        </form>
                    </div>
                <?php endif; ?>

                <!-- Table Section -->
                <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                    <div class="p-6 border-b border-gray-200">
                        <div class="flex flex-col md:flex-row md:items-center md:justify-between space-y-4 md:space-y-0">
                            <!-- Search Box -->
                            <div class="relative flex-1 max-w-md">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                    </svg>
                                </div>
                                <input type="text" 
                                    id="searchInput"
                                    placeholder="Cari user..." 
                                    class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                                >
                            </div>
                            
                            <!-- Export Button -->
                            <button onclick="exportToExcel()" 
                                class="inline-flex items-center px-4 py-2 bg-[#16A085] text-white rounded-md hover:bg-opacity-90 focus:outline-none focus:ring-2 focus:ring-[#16A085] focus:ring-offset-2 transition-colors">
                                <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                Export Excel
                            </button>
                        </div>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200" id="usersTable">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                                    <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama</th>
                                    <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                    <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                                    <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200" id="tableBody">
                                <?php if ($result->num_rows === 0): ?>
                                    <tr>
                                        <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                                            <div class="flex flex-col items-center justify-center space-y-2">
                                                <svg class="h-8 w-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                                                </svg>
                                                <span>Belum ada data pengguna</span>
                                            </div>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php 
                                    $no = 1;
                                    while($row = $result->fetch_assoc()): 
                                    ?>
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?= $no++ ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="h-8 w-8 bg-blue-100 rounded-full flex items-center justify-center">
                                                    <span class="text-sm font-medium text-blue-800">
                                                        <?= strtoupper(substr($row['name'], 0, 1)) ?>
                                                    </span>
                                                </div>
                                                <div class="ml-4">
                                                    <div class="text-sm font-medium text-gray-900">
                                                        <?= htmlspecialchars($row['name']) ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900"><?= htmlspecialchars($row['email']) ?></div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                <?= $row['role'] === 'admin' ? 'bg-purple-100 text-purple-800' : 'bg-green-100 text-green-800' ?>">
                                                <?= ucfirst($row['role']) ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <div class="flex items-center space-x-4">
                                                <a href="?edit=<?= $row['id'] ?>" 
                                                   class="text-blue-600 hover:text-blue-900 flex items-center transition-colors">
                                                    <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                    </svg>
                                                    Edit
                                                </a>
                                                <?php if ($row['role'] !== 'admin'): ?>
                                                    <a href="?deactivate=<?= $row['id'] ?>" 
                                                       onclick="return confirm('Apakah Anda yakin ingin menonaktifkan user ini?')"
                                                       class="text-red-600 hover:text-red-900 flex items-center transition-colors">
                                                        <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                                                        </svg>
                                                        Nonaktifkan
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://unpkg.com/xlsx/dist/xlsx.full.min.js"></script>
<script>
// Search functionality
document.getElementById('searchInput').addEventListener('input', function(e) {
    const searchTerm = e.target.value.toLowerCase();
    const tableBody = document.getElementById('tableBody');
    const rows = tableBody.getElementsByTagName('tr');
    
    Array.from(rows).forEach(row => {
        const name = row.querySelector('td:nth-child(2)')?.textContent.toLowerCase() || '';
        const email = row.querySelector('td:nth-child(3)')?.textContent.toLowerCase() || '';
        const role = row.querySelector('td:nth-child(4)')?.textContent.toLowerCase() || '';
        
        if (name.includes(searchTerm) || email.includes(searchTerm) || role.includes(searchTerm)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
});

// Enhanced export functionality
function exportToExcel() {
    // Show loading state
    const exportBtn = event.target;
    const originalText = exportBtn.innerHTML;
    exportBtn.innerHTML = `
        <svg class="animate-spin h-5 w-5 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        Exporting...
    `;
    
    try {
        // Get the table
        const table = document.getElementById('usersTable');
        
        // Prepare the worksheet data
        const ws_data = [];
        
        // Add headers
        const headers = [];
        table.querySelectorAll('thead th').forEach(th => {
            headers.push(th.textContent.trim());
        });
        ws_data.push(headers);
        
        // Add visible rows only
        table.querySelectorAll('tbody tr').forEach(tr => {
            if (tr.style.display !== 'none') {
                const row_data = [];
                tr.querySelectorAll('td').forEach(td => {
                    // Get only the text content, not the HTML
                    row_data.push(td.textContent.trim());
                });
                ws_data.push(row_data);
            }
        });
        
        // Create worksheet
        const ws = XLSX.utils.aoa_to_sheet(ws_data);
        
        // Create workbook and add worksheet
        const wb = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(wb, ws, 'Users');
        
        // Generate Excel file with current date
        const date = new Date().toISOString().slice(0,10);
        XLSX.writeFile(wb, `data_users_${date}.xlsx`);
        
        // Show success message
        exportBtn.classList.remove('bg-[#16A085]');
        exportBtn.classList.add('bg-green-500');
        exportBtn.innerHTML = `
            <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
            Exported!
        `;
        
        // Reset button after 2 seconds
        setTimeout(() => {
            exportBtn.classList.remove('bg-green-500');
            exportBtn.classList.add('bg-[#16A085]');
            exportBtn.innerHTML = originalText;
        }, 2000);
        
    } catch (error) {
        console.error('Export failed:', error);
        
        // Show error state
        exportBtn.classList.remove('bg-[#16A085]');
        exportBtn.classList.add('bg-red-500');
        exportBtn.innerHTML = `
            <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
            Export Failed!
        `;
        
        // Reset button after 2 seconds
        setTimeout(() => {
            exportBtn.classList.remove('bg-red-500');
            exportBtn.classList.add('bg-[#16A085]');
            exportBtn.innerHTML = originalText;
        }, 2000);
    }
}

// Add transition effects when rows are added/removed
document.querySelectorAll('#tableBody tr').forEach(row => {
    row.classList.add('transition-all', 'duration-300', 'ease-in-out');
});
</script>

<?php include '../includes/footer.php'; ?>
