-- =====================================================
-- Database: shop_islami
-- Online Shop & Artikel Islami
-- =====================================================

CREATE DATABASE IF NOT EXISTS shop_islami CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE shop_islami;

-- =====================================================
-- Tabel Kategori Produk
-- =====================================================
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) UNIQUE,
    description TEXT,
    image VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP  ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- Tabel Produk
-- =====================================================
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT,
    name VARCHAR(200) NOT NULL,
    slug VARCHAR(200) UNIQUE,
    description TEXT,
    price DECIMAL(12,2) NOT NULL,
    stock INT DEFAULT 0,
    image VARCHAR(255),
    is_featured TINYINT DEFAULT 0,
    is_active TINYINT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- Tabel Artikel
-- =====================================================
CREATE TABLE articles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE,
    excerpt TEXT,
    content TEXT,
    image VARCHAR(255),
    author VARCHAR(100),
    views INT DEFAULT 0,
    is_published TINYINT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- Tabel Users (Pelanggan)
-- =====================================================
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    name VARCHAR(100),
    email VARCHAR(100),
    is_active TINYINT DEFAULT 1,
    last_login TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- Tabel Admins (Administrator - Terpisah dari Users)
-- =====================================================
CREATE TABLE admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    name VARCHAR(100),
    email VARCHAR(100),
    is_active TINYINT DEFAULT 1,
    last_login TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- Tabel About Page (Halaman Tentang Kami)
-- =====================================================
CREATE TABLE about_page (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) DEFAULT 'Tentang Kami',
    subtitle VARCHAR(255) DEFAULT 'Mengenal Lebih Dekat',
    content TEXT,
    photo VARCHAR(255),
    vision TEXT,
    mission TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- Tabel Orders (Pesanan)
-- =====================================================
CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_number VARCHAR(50) UNIQUE,
    customer_name VARCHAR(100) NOT NULL,
    customer_email VARCHAR(100),
    customer_phone VARCHAR(20),
    customer_address TEXT,
    subtotal DECIMAL(12,2),
    shipping_cost DECIMAL(12,2) DEFAULT 0,
    total_amount DECIMAL(12,2),
    payment_method VARCHAR(50),
    notes TEXT,
    status ENUM('pending', 'processing', 'shipped', 'delivered', 'completed', 'cancelled') DEFAULT 'pending',
    courier_name VARCHAR(50),
    tracking_number VARCHAR(100),
    shipped_at TIMESTAMP NULL,
    delivered_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- Tabel Order Items (Detail Pesanan)
-- =====================================================
CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT,
    product_id INT,
    product_name VARCHAR(200),
    quantity INT,
    price DECIMAL(12,2),
    subtotal DECIMAL(12,2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- Tabel Settings (Pengaturan Website)
-- =====================================================
CREATE TABLE settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- Tabel Chats (Conversations)
-- =====================================================
CREATE TABLE chats (
    id INT AUTO_INCREMENT PRIMARY KEY,
    session_id VARCHAR(100) UNIQUE NOT NULL,
    user_name VARCHAR(100) DEFAULT 'Pengunjung',
    user_email VARCHAR(100),
    status ENUM('active', 'closed') DEFAULT 'active',
    last_message TEXT,
    unread_admin INT DEFAULT 0,
    unread_user INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- Tabel Chat Messages
-- =====================================================
CREATE TABLE chat_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    chat_id INT NOT NULL,
    sender_type ENUM('user', 'admin') NOT NULL,
    message TEXT NOT NULL,
    is_read TINYINT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (chat_id) REFERENCES chats(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- Tabel Dakwah Videos (Video YouTube Dakwah)
-- =====================================================
CREATE TABLE dakwah_videos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    youtube_url VARCHAR(500) NOT NULL,
    youtube_id VARCHAR(20),
    description TEXT,
    is_featured TINYINT DEFAULT 0,
    is_active TINYINT DEFAULT 1,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- Tabel Social Activities (Kegiatan Sosial dan Agama)
-- =====================================================
CREATE TABLE social_activities (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE,
    description TEXT,
    content TEXT,
    images TEXT,
    event_date DATE,
    location VARCHAR(255),
    is_active TINYINT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- Tabel Quizzes (Kuis Islami)
-- =====================================================
CREATE TABLE quizzes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE,
    description TEXT,
    category VARCHAR(100),
    difficulty ENUM('mudah', 'sedang', 'sulit') DEFAULT 'sedang',
    time_limit INT DEFAULT 0,
    passing_score INT DEFAULT 70,
    image VARCHAR(255),
    is_active TINYINT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- Tabel Quiz Questions (Pertanyaan Kuis)
-- =====================================================
CREATE TABLE quiz_questions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    quiz_id INT NOT NULL,
    question TEXT NOT NULL,
    option_a VARCHAR(500) NOT NULL,
    option_b VARCHAR(500) NOT NULL,
    option_c VARCHAR(500) NOT NULL,
    option_d VARCHAR(500) NOT NULL,
    correct_answer ENUM('a', 'b', 'c', 'd') NOT NULL,
    explanation TEXT,
    sort_order INT DEFAULT 0,
    FOREIGN KEY (quiz_id) REFERENCES quizzes(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- Tabel Quiz Attempts (Percobaan/Skor User)
-- =====================================================
CREATE TABLE quiz_attempts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    quiz_id INT NOT NULL,
    user_name VARCHAR(100),
    user_email VARCHAR(100),
    score INT DEFAULT 0,
    total_questions INT DEFAULT 0,
    correct_answers INT DEFAULT 0,
    time_taken INT DEFAULT 0,
    answers TEXT,
    completed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (quiz_id) REFERENCES quizzes(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- Tabel Payment Tokens (Token Pembayaran QR)
-- =====================================================
CREATE TABLE payment_tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_number VARCHAR(50) NOT NULL,
    token VARCHAR(64) NOT NULL UNIQUE,
    amount DECIMAL(15,2) DEFAULT 0,
    is_used TINYINT DEFAULT 0,
    expires_at TIMESTAMP NULL,
    used_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- Tabel Site Settings (Pengaturan Website)
-- =====================================================
CREATE TABLE site_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT,
    setting_type VARCHAR(50) DEFAULT 'text',
    description VARCHAR(255),
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- Insert Data Awal
-- =====================================================

-- Admin default (password: admin123)
INSERT INTO admins (username, password, name, email) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator', 'admin@shopislami.com');

-- About Page default content
INSERT INTO about_page (title, subtitle, content, vision, mission) VALUES (
    'Tentang Toko Islami',
    'Belanja Berkah, Hidup Barokah',
    '<p>Toko Islami adalah platform belanja online yang menyediakan berbagai produk Islami berkualitas. Kami hadir untuk memudahkan umat muslim dalam mendapatkan kebutuhan ibadah dan produk halal terpercaya.</p><p>Dengan pengalaman bertahun-tahun dalam melayani pelanggan, kami berkomitmen untuk memberikan pelayanan terbaik dengan prinsip amanah dan kejujuran sesuai syariat Islam.</p><p>Setiap produk yang kami jual telah melalui seleksi ketat untuk memastikan kualitas dan kehalalannya. Kami percaya bahwa berbelanja yang berkah akan membawa keberkahan dalam hidup.</p>',
    'Menjadi platform belanja online Islami terpercaya yang membawa keberkahan bagi seluruh umat.',
    'Menyediakan produk-produk Islami berkualitas dengan harga terjangkau. Melayani dengan amanah dan kejujuran. Menyebarkan manfaat dan keberkahan melalui setiap transaksi.'
);

-- Kategori Produk
INSERT INTO categories (name, slug, description) VALUES 
('Busana Muslim Pria', 'busana-muslim-pria', 'Koleksi busana muslim untuk pria'),
('Busana Muslim Wanita', 'busana-muslim-wanita', 'Koleksi busana muslim untuk wanita'),
('Perlengkapan Sholat', 'perlengkapan-sholat', 'Sajadah, mukena, sarung, dan perlengkapan sholat lainnya'),
('Al-Quran & Buku', 'al-quran-buku', 'Al-Quran, buku islami, dan literatur keagamaan'),
('Aksesoris Islami', 'aksesoris-islami', 'Tasbih, parfum, peci, dan aksesoris islami lainnya'),
('Makanan Halal', 'makanan-halal', 'Makanan dan minuman halal berkualitas');

-- Produk Sample
INSERT INTO products (category_id, name, slug, description, price, stock, is_featured) VALUES 
(1, 'Jubah Premium Al-Haramain', 'jubah-premium-al-haramain', 'Jubah premium dengan bahan berkualitas tinggi, nyaman dipakai untuk ibadah dan sehari-hari. Tersedia dalam berbagai ukuran.', 450000, 50, 1),
(1, 'Gamis Pria Modern', 'gamis-pria-modern', 'Gamis pria dengan desain modern dan elegan, cocok untuk berbagai acara.', 350000, 30, 1),
(2, 'Gamis Syar''i Elegant', 'gamis-syari-elegant', 'Gamis syar''i dengan desain elegan dan bahan premium yang nyaman.', 550000, 40, 1),
(2, 'Hijab Pashmina Premium', 'hijab-pashmina-premium', 'Hijab pashmina dengan bahan lembut dan tidak mudah kusut.', 85000, 100, 1),
(3, 'Sajadah Turki Premium', 'sajadah-turki-premium', 'Sajadah import Turki dengan motif indah dan bahan berkualitas.', 175000, 60, 1),
(3, 'Mukena Katun Rayon', 'mukena-katun-rayon', 'Mukena dengan bahan katun rayon yang adem dan nyaman.', 250000, 45, 0),
(4, 'Al-Quran Mushaf Madinah', 'al-quran-mushaf-madinah', 'Al-Quran dengan rasm Utsmani, cetakan Madinah.', 125000, 80, 1),
(4, 'Buku Riyadhus Shalihin', 'buku-riyadhus-shalihin', 'Terjemahan lengkap kitab Riyadhus Shalihin.', 185000, 35, 0),
(5, 'Tasbih Kayu Kokka', 'tasbih-kayu-kokka', 'Tasbih dari kayu kokka asli dengan 99 butir.', 95000, 70, 1),
(5, 'Parfum Non Alkohol Al-Rehab', 'parfum-non-alkohol-al-rehab', 'Parfum roll-on non alkohol dengan wangi tahan lama.', 45000, 120, 0),
(6, 'Kurma Ajwa Premium 500gr', 'kurma-ajwa-premium', 'Kurma Ajwa asli Madinah grade premium.', 275000, 50, 1),
(6, 'Madu Yaman Al-Doani', 'madu-yaman-al-doani', 'Madu murni dari Yaman berkualitas tinggi.', 385000, 25, 1);

-- Artikel Sample
INSERT INTO articles (title, slug, excerpt, content, author, is_published) VALUES 
('Keutamaan Sholat Berjamaah', 'keutamaan-sholat-berjamaah', 'Sholat berjamaah memiliki banyak keutamaan yang luar biasa dalam Islam...', '<p>Sholat berjamaah merupakan ibadah yang sangat dianjurkan dalam Islam. Rasulullah SAW bersabda bahwa sholat berjamaah lebih utama 27 derajat dibandingkan sholat sendirian.</p><p>Beberapa keutamaan sholat berjamaah antara lain:</p><ul><li>Pahala yang berlipat ganda</li><li>Mempererat ukhuwah islamiyah</li><li>Melatih kedisiplinan</li><li>Mendapatkan keberkahan</li></ul><p>Mari kita senantiasa menjaga sholat berjamaah di masjid.</p>', 'Admin', 1),
('Tips Memilih Busana Muslim yang Syar''i', 'tips-memilih-busana-muslim-syari', 'Berbusana muslim yang syar''i adalah kewajiban setiap muslim...', '<p>Memilih busana muslim yang syar''i merupakan hal penting bagi setiap muslim. Berikut tips memilih busana muslim yang sesuai syariat:</p><ol><li><strong>Menutup aurat</strong> - Pastikan busana menutupi seluruh aurat</li><li><strong>Tidak ketat</strong> - Pilih busana yang longgar dan tidak membentuk lekuk tubuh</li><li><strong>Tidak transparan</strong> - Pilih bahan yang tebal dan tidak tembus pandang</li><li><strong>Tidak menyerupai lawan jenis</strong> - Pilih model yang sesuai dengan jenis kelamin</li></ol><p>Dengan berbusana syar''i, kita telah menjalankan perintah Allah SWT.</p>', 'Admin', 1),
('Adab Berbelanja dalam Islam', 'adab-berbelanja-dalam-islam', 'Islam mengajarkan adab dalam setiap aspek kehidupan termasuk berbelanja...', '<p>Dalam Islam, setiap aktivitas termasuk berbelanja memiliki adab yang harus diperhatikan:</p><ul><li><strong>Niat yang baik</strong> - Niatkan berbelanja untuk memenuhi kebutuhan halal</li><li><strong>Jujur dan amanah</strong> - Baik penjual maupun pembeli harus jujur</li><li><strong>Tidak berlebihan</strong> - Hindari sikap boros dan israf</li><li><strong>Memilih yang halal</strong> - Pastikan produk yang dibeli halal dan baik</li><li><strong>Membayar dengan adil</strong> - Bayar sesuai harga yang disepakati</li></ul><p>Dengan menjaga adab berbelanja, transaksi kita akan penuh berkah.</p>', 'Admin', 1);

-- Settings
INSERT INTO settings (setting_key, setting_value) VALUES 
('site_name', 'Toko Islami'),
('site_tagline', 'Belanja Berkah, Hidup Barokah'),
('site_email', 'info@tokoislami.com'),
('site_phone', '08123456789'),
('site_address', 'Jl. Masjid Agung No. 123, Jakarta'),
('shipping_cost', '15000');

-- Site Settings (Logo, Navbar, Hero)
INSERT INTO site_settings (setting_key, setting_value, setting_type, description) VALUES 
('navbar_logo', '', 'image', 'Logo di navbar'),
('navbar_title_large', 'Toko', 'text', 'Teks besar pada logo navbar'),
('navbar_title_small', 'Islami', 'text', 'Teks kecil pada logo navbar'),
('header_banner', '', 'image', 'Banner di header'),
('footer_text', 'Toko Islami - Belanja Berkah, Hidup Barokah', 'text', 'Teks di footer'),
('hero_title', 'Selamat Datang di Toko Islami', 'text', 'Judul di hero section beranda'),
('hero_subtitle', 'Temukan berbagai produk Islami berkualitas untuk kebutuhan ibadah dan sehari-hari Anda. Belanja mudah, berkah berlimpah.', 'text', 'Deskripsi di hero section beranda');
