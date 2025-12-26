<?php
/**
 * Footer Component
 * Toko Islami - Online Shop & Artikel
 */

$siteName = getSetting('site_name', 'Toko Islami');
$sitePhone = getSetting('site_phone', '08123456789');
$siteEmail = getSetting('site_email', 'info@tokoislami.com');
$siteAddress = getSetting('site_address', 'Jakarta, Indonesia');
?>
    </main>
    
    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-grid">
                <!-- About -->
                <div class="footer-col">
                    <h4>🕌 <?= $siteName ?></h4>
                    <p>Platform belanja online produk-produk Islami berkualitas. Kami menyediakan berbagai busana muslim, perlengkapan ibadah, dan produk halal terpercaya.</p>
                    <div class="footer-social">
                        <a href="#" title="Facebook">📘</a>
                        <a href="#" title="Instagram">📷</a>
                        <a href="#" title="WhatsApp">📱</a>
                        <a href="#" title="YouTube">📺</a>
                    </div>
                </div>
                
                <!-- Quick Links -->
                <div class="footer-col">
                    <h4>Link Cepat</h4>
                    <ul>
                        <li><a href="<?= BASE_URL ?>">→ Beranda</a></li>
                        <li><a href="<?= BASE_URL ?>/pages/shop/products.php">→ Produk</a></li>
                        <li><a href="<?= BASE_URL ?>/pages/articles/">→ Artikel</a></li>
                        <li><a href="<?= BASE_URL ?>/pages/aktivitas.php">→ Aktivitas</a></li>
                        <li><a href="<?= BASE_URL ?>/pages/about.php">→ Tentang Kami</a></li>
                        <li><a href="<?= BASE_URL ?>/pages/shop/cart.php">→ Keranjang</a></li>
                    </ul>
                </div>
                
                <!-- Categories -->
                <div class="footer-col">
                    <h4>Kategori</h4>
                    <ul>
                        <?php
                        $pdo = db();
                        $categories = $pdo->query("SELECT name, slug FROM categories LIMIT 6")->fetchAll();
                        foreach ($categories as $cat):
                        ?>
                        <li><a href="<?= BASE_URL ?>/pages/shop/products.php?category=<?= $cat['slug'] ?>">→ <?= $cat['name'] ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                
                <!-- Contact -->
                <div class="footer-col">
                    <h4>Hubungi Kami</h4>
                    <ul class="footer-contact">
                        <li>
                            <span>📍</span>
                            <span><?= $siteAddress ?></span>
                        </li>
                        <li>
                            <span>📞</span>
                            <span><?= $sitePhone ?></span>
                        </li>
                        <li>
                            <span>✉️</span>
                            <span><?= $siteEmail ?></span>
                        </li>
                        <li>
                            <span>🕐</span>
                            <span>Senin - Sabtu: 08.00 - 17.00 WIB</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        
        <!-- Footer Bottom -->
        <div class="footer-bottom">
            <div class="container">
                <div class="bismillah">بِسْمِ اللَّهِ الرَّحْمَٰنِ الرَّحِيمِ</div>
                <p>&copy; <?= date('Y') ?> <?= $siteName ?>. Semua Hak Dilindungi.</p>
                <p style="margin-top: 10px; font-size: 0.9rem;">Dengan Ridha Allah SWT, Kami Melayani Dengan Sepenuh Hati</p>
            </div>
        </div>
    </footer>
    
    
    <!-- Chat Widget -->
    <?php include_once __DIR__ . '/chat-widget.php'; ?>
    
    <!-- JavaScript -->
    <script src="<?= BASE_URL ?>/assets/js/main.js"></script>
</body>
</html>
