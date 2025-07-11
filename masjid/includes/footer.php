</main>
    </div>

    <!-- Footer -->
    <footer class="bg-white border-t lg:ml-64">
        <div class="max-w-7xl mx-auto px-4 py-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div>
                    <h3 class="text-lg font-semibold text-primary mb-4">Sistem Manajemen Masjid</h3>
                    <p class="text-sm text-gray-600">Memudahkan pengelolaan kegiatan dan keuangan masjid untuk kemaslahatan umat.</p>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-primary mb-4">Menu Utama</h3>
                    <ul class="space-y-2">
                        <li><a href="/masjid/user/pengajian.php" class="text-sm text-gray-600 hover:text-accent">Jadwal Pengajian</a></li>
                        <li><a href="/masjid/user/sedekah.php" class="text-sm text-gray-600 hover:text-accent">Infaq & Sedekah</a></li>
                        <li><a href="/masjid/user/tabungan.php" class="text-sm text-gray-600 hover:text-accent">Tabungan Masjid</a></li>
                        <li><a href="/masjid/user/laporan.php" class="text-sm text-gray-600 hover:text-accent">Laporan</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-primary mb-4">Kontak</h3>
                    <ul class="space-y-2">
                        <li class="flex items-center space-x-2 text-sm text-gray-600">
                            <svg class="w-5 h-5 text-accent" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd" />
                            </svg>
                            <span>HM9J+WH5, Jl. Sarirogo Raya, Sari Rogo, Kec. Sidoarjo, Kabupaten Sidoarjo, Jawa Timur 61234</span>
                        </li>
                        <li class="flex items-center space-x-2 text-sm text-gray-600">
                            <svg class="w-5 h-5 text-accent" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z" />
                                <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z" />
                            </svg>
                            <span>info@masjid.com</span>
                        </li>
                        <li class="flex items-center space-x-2 text-sm text-gray-600">
                            <svg class="w-5 h-5 text-accent" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z" />
                            </svg>
                            <span>(021) 1234-5678</span>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="mt-8 pt-8 border-t border-gray-200">
                <p class="text-center text-sm text-gray-600">&copy; <?php echo date('Y'); ?> Sistem Manajemen Masjid. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- Mobile Menu Toggle Script -->
    <script>
        document.getElementById('mobile-menu-button').addEventListener('click', function() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('-translate-x-full');
        });
    </script></body>
</html>
