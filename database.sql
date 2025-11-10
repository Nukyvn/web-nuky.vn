-- Database: nuky.vn
-- Charset: utf8mb4_unicode_ci

-- Bảng cấu hình website
CREATE TABLE `settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text,
  `setting_type` enum('text','json','file') DEFAULT 'text',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng danh mục sản phẩm
CREATE TABLE `categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text,
  `image` varchar(500),
  `parent_id` int(11) DEFAULT NULL,
  `sort_order` int(11) DEFAULT 0,
  `status` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `parent_id` (`parent_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng sản phẩm
CREATE TABLE `products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_id` int(11),
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `short_description` text,
  `description` longtext,
  `price` decimal(15,2) NOT NULL DEFAULT 0,
  `sale_price` decimal(15,2) DEFAULT NULL,
  `stock_quantity` int(11) DEFAULT 0,
  `sku` varchar(100),
  `weight` varchar(50),
  `expiry_date` varchar(100),
  `images` text COMMENT 'JSON array of image URLs',
  `featured` tinyint(1) DEFAULT 0,
  `status` tinyint(1) DEFAULT 1,
  `view_count` int(11) DEFAULT 0,
  `meta_title` varchar(255),
  `meta_description` text,
  `meta_keywords` varchar(500),
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `category_id` (`category_id`),
  KEY `featured` (`featured`),
  KEY `status` (`status`),
  FULLTEXT KEY `search` (`name`,`short_description`,`description`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng bài viết
CREATE TABLE `articles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `excerpt` text,
  `content` longtext,
  `thumbnail` varchar(500),
  `author` varchar(100),
  `tags` varchar(500),
  `status` tinyint(1) DEFAULT 1,
  `view_count` int(11) DEFAULT 0,
  `meta_title` varchar(255),
  `meta_description` text,
  `published_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `status` (`status`),
  FULLTEXT KEY `search` (`title`,`excerpt`,`content`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng video
CREATE TABLE `videos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text,
  `video_url` varchar(500) NOT NULL,
  `video_type` enum('upload','youtube','vimeo') DEFAULT 'upload',
  `thumbnail` varchar(500),
  `duration` varchar(20),
  `view_count` int(11) DEFAULT 0,
  `status` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng khách hàng
CREATE TABLE `customers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `full_name` varchar(255) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `email` varchar(255),
  `address` text,
  `province` varchar(100),
  `district` varchar(100),
  `ward` varchar(100),
  `notes` text,
  `total_orders` int(11) DEFAULT 0,
  `total_spent` decimal(15,2) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `phone` (`phone`),
  KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng đơn hàng
CREATE TABLE `orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_code` varchar(50) NOT NULL,
  `customer_id` int(11),
  `customer_name` varchar(255) NOT NULL,
  `customer_phone` varchar(20) NOT NULL,
  `customer_email` varchar(255),
  `customer_address` text NOT NULL,
  `province` varchar(100),
  `district` varchar(100),
  `ward` varchar(100),
  `notes` text,
  `subtotal` decimal(15,2) NOT NULL DEFAULT 0,
  `shipping_fee` decimal(15,2) DEFAULT 0,
  `discount` decimal(15,2) DEFAULT 0,
  `total` decimal(15,2) NOT NULL DEFAULT 0,
  `payment_method` enum('COD','bank_transfer','momo') DEFAULT 'COD',
  `status` enum('pending','confirmed','processing','shipping','completed','cancelled') DEFAULT 'pending',
  `payment_status` enum('unpaid','paid','refunded') DEFAULT 'unpaid',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `order_code` (`order_code`),
  KEY `customer_id` (`customer_id`),
  KEY `status` (`status`),
  KEY `created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng chi tiết đơn hàng
CREATE TABLE `order_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `product_id` int(11),
  `product_name` varchar(255) NOT NULL,
  `product_image` varchar(500),
  `price` decimal(15,2) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `subtotal` decimal(15,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`),
  KEY `product_id` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng chat box messages
CREATE TABLE `chat_messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_name` varchar(255) NOT NULL,
  `customer_phone` varchar(20) NOT NULL,
  `customer_email` varchar(255),
  `message` text NOT NULL,
  `ip_address` varchar(50),
  `user_agent` varchar(500),
  `status` enum('new','read','replied') DEFAULT 'new',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `status` (`status`),
  KEY `created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng banners/sliders
CREATE TABLE `banners` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `subtitle` varchar(500),
  `image` varchar(500),
  `video` varchar(500),
  `link` varchar(500),
  `button_text` varchar(100),
  `position` enum('hero','sidebar','popup') DEFAULT 'hero',
  `sort_order` int(11) DEFAULT 0,
  `status` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `position` (`position`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng testimonials/đánh giá
CREATE TABLE `testimonials` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_name` varchar(255) NOT NULL,
  `customer_avatar` varchar(500),
  `customer_title` varchar(255),
  `rating` tinyint(1) DEFAULT 5,
  `content` text NOT NULL,
  `sort_order` int(11) DEFAULT 0,
  `status` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng users/admin
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `email` varchar(255),
  `role` enum('admin','editor','viewer') DEFAULT 'editor',
  `avatar` varchar(500),
  `last_login` timestamp NULL DEFAULT NULL,
  `status` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng coupons/mã giảm giá
CREATE TABLE `coupons` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(50) NOT NULL,
  `description` varchar(255),
  `discount_type` enum('percent','fixed') DEFAULT 'percent',
  `discount_value` decimal(15,2) NOT NULL,
  `min_order_value` decimal(15,2) DEFAULT 0,
  `max_discount` decimal(15,2) DEFAULT NULL,
  `usage_limit` int(11) DEFAULT NULL,
  `used_count` int(11) DEFAULT 0,
  `start_date` timestamp NULL DEFAULT NULL,
  `end_date` timestamp NULL DEFAULT NULL,
  `status` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default admin user (password: admin123)
INSERT INTO `users` (`username`, `password`, `full_name`, `email`, `role`, `status`) 
VALUES ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator', 'admin@nuky.vn', 'admin', 1);

-- Insert default settings
INSERT INTO `settings` (`setting_key`, `setting_value`, `setting_type`) VALUES
('site_name', 'NUKY - Xưởng Trà Nguyên Ký', 'text'),
('site_description', 'Chuyên sản xuất trà đen, bột kem béo, trà gạo rang, trà túi lọc và dịch vụ đóng gói OEM thương hiệu riêng.', 'text'),
('color_primary', '#2E7D32', 'text'),
('color_secondary', '#F9F7F2', 'text'),
('color_accent', '#FFD54F', 'text'),
('site_logo', '/uploads/logo.png', 'file'),
('contact_phone', '0947009933', 'text'),
('contact_email', 'orders@nuky.vn', 'text'),
('contact_address', 'Việt Nam', 'text'),
('facebook_url', 'https://facebook.com/nuky.vn', 'text'),
('zalo_url', 'https://zalo.me/0947009933', 'text'),
('meta_keywords', 'trà sữa, nguyên liệu trà, OEM trà, đóng gói thương hiệu', 'text'),
('meta_og_image', '/uploads/og-image.jpg', 'file');

-- ============================================
-- CẬP NHẬT DATABASE - THÊM PROMOTIONS & SETTINGS
-- ============================================

-- Bảng khuyến mãi/promotions
CREATE TABLE IF NOT EXISTS `promotions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text,
  `banner_image` varchar(500),
  `discount_text` varchar(100) COMMENT 'VD: GIẢM 50%, MUA 2 TẶNG 1',
  `button_text` varchar(100) DEFAULT 'Mua ngay',
  `button_link` varchar(500),
  `countdown_end` datetime DEFAULT NULL COMMENT 'Thời gian kết thúc đếm ngược',
  `status` tinyint(1) DEFAULT 1,
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE promotions
ADD COLUMN start_date DATETIME DEFAULT NULL AFTER discount_text;

-- Thêm settings mới
INSERT INTO `settings` (`setting_key`, `setting_value`, `setting_type`) VALUES
('google_maps_embed', '', 'text'),
('testimonials_title', 'Khách hàng nói gì về chúng tôi', 'text'),
('testimonials_description', 'Hàng ngàn khách hàng tin tưởng và hài lòng với sản phẩm của Nguyên Ký', 'text')
ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value);

-- Insert dữ liệu mẫu promotion
INSERT INTO `promotions` (`title`, `description`, `discount_text`, `button_text`, `button_link`, `countdown_end`, `status`, `sort_order`) VALUES
('🎉 FLASH SALE CUỐI TUẦN', 'Giảm giá sốc cho tất cả sản phẩm trà và nguyên liệu pha chế', 'GIẢM TỚI 50%', 'Mua ngay', '/san-pham', DATE_ADD(NOW(), INTERVAL 3 DAY), 1, 1);