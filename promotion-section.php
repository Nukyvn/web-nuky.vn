<?php
/**
 * Section hiển thị 2 chương trình khuyến mãi gần nhất
 * - Chương trình 1: Đang diễn ra
 * - Chương trình 2: Sắp diễn ra
 */

// ==================== LOGIC LẤY 2 CHƯƠNG TRÌNH ====================
// Giả sử bạn đã có kết nối database $conn
// Thay đổi tên bảng và tên cột cho phù hợp với database của bạn

$current_date = date('Y-m-d H:i:s');

// Lấy chương trình đang diễn ra (start_date <= NOW <= end_date)
$sql_ongoing = "SELECT * FROM promotions
                WHERE start_date <= ?
                AND end_date >= ?
                AND status = 'active'
                ORDER BY start_date DESC
                LIMIT 1";
$stmt = $conn->prepare($sql_ongoing);
$stmt->bind_param("ss", $current_date, $current_date);
$stmt->execute();
$ongoing_promotion = $stmt->get_result()->fetch_assoc();

// Lấy chương trình sắp diễn ra (start_date > NOW)
$sql_upcoming = "SELECT * FROM promotions
                 WHERE start_date > ?
                 AND status = 'active'
                 ORDER BY start_date ASC
                 LIMIT 1";
$stmt = $conn->prepare($sql_upcoming);
$stmt->bind_param("s", $current_date);
$stmt->execute();
$upcoming_promotion = $stmt->get_result()->fetch_assoc();

// Function tính số ngày còn lại
function getDaysUntil($date) {
    $now = new DateTime();
    $target = new DateTime($date);
    $interval = $now->diff($target);
    return $interval->days;
}
?>

<!-- Alpine.js Countdown Component -->
<script>
function countdown(endDate) {
    return {
        days: '00',
        hours: '00',
        minutes: '00',
        seconds: '00',
        init() {
            this.updateCountdown();
            setInterval(() => {
                this.updateCountdown();
            }, 1000);
        },
        updateCountdown() {
            const end = new Date(endDate).getTime();
            const now = new Date().getTime();
            const distance = end - now;

            if (distance < 0) {
                this.days = '00';
                this.hours = '00';
                this.minutes = '00';
                this.seconds = '00';
                return;
            }

            this.days = String(Math.floor(distance / (1000 * 60 * 60 * 24))).padStart(2, '0');
            this.hours = String(Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60))).padStart(2, '0');
            this.minutes = String(Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60))).padStart(2, '0');
            this.seconds = String(Math.floor((distance % (1000 * 60)) / 1000)).padStart(2, '0');
        }
    }
}
</script>

<!-- Promotion Section -->
<section class="py-12 bg-gray-50">
    <div class="container mx-auto px-4">
        <h2 class="text-3xl md:text-4xl font-bold text-center mb-12 text-gray-800">
            🔥 CHƯƠNG TRÌNH KHUYẾN MÃI
        </h2>

        <div class="grid md:grid-cols-2 gap-8">

            <!-- CHƯƠNG TRÌNH 1: ĐANG DIỄN RA -->
            <?php if ($ongoing_promotion): ?>
            <div class="relative overflow-hidden rounded-2xl shadow-2xl transform hover:scale-105 transition duration-300">
                <div class="absolute inset-0 bg-gradient-to-r from-red-500 to-orange-500 opacity-90"></div>

                <div class="relative z-10 p-8 text-white">
                    <!-- Badge trạng thái -->
                    <div class="inline-flex items-center bg-yellow-400 text-red-900 px-4 py-2 rounded-full text-sm font-bold mb-4 animate-pulse">
                        <span class="w-2 h-2 bg-red-900 rounded-full mr-2 animate-ping"></span>
                        ⚡ ĐANG DIỄN RA
                    </div>

                    <!-- Tiêu đề -->
                    <h3 class="text-2xl md:text-3xl font-bold mb-4">
                        <?= htmlspecialchars($ongoing_promotion['title']) ?>
                    </h3>

                    <!-- Mô tả -->
                    <?php if (!empty($ongoing_promotion['description'])): ?>
                    <p class="text-lg mb-6 text-white/90">
                        <?= htmlspecialchars($ongoing_promotion['description']) ?>
                    </p>
                    <?php endif; ?>

                    <!-- Giảm giá -->
                    <div class="flex items-center gap-4 mb-6">
                        <div class="bg-white text-red-600 px-6 py-3 rounded-lg font-bold text-3xl shadow-lg">
                            <?= htmlspecialchars($ongoing_promotion['discount_text']) ?>
                        </div>
                    </div>

                    <!-- Countdown Timer -->
                    <div class="mb-6" x-data="countdown('<?= date('Y-m-d H:i:s', strtotime($ongoing_promotion['end_date'])) ?>')">
                        <div class="text-sm font-semibold mb-2">⏰ Kết thúc trong:</div>
                        <div class="flex gap-3">
                            <div class="bg-white/20 backdrop-blur-sm rounded-lg px-3 py-2 min-w-[70px] text-center">
                                <div class="text-2xl font-bold" x-text="days">00</div>
                                <div class="text-xs">Ngày</div>
                            </div>
                            <div class="bg-white/20 backdrop-blur-sm rounded-lg px-3 py-2 min-w-[70px] text-center">
                                <div class="text-2xl font-bold" x-text="hours">00</div>
                                <div class="text-xs">Giờ</div>
                            </div>
                            <div class="bg-white/20 backdrop-blur-sm rounded-lg px-3 py-2 min-w-[70px] text-center">
                                <div class="text-2xl font-bold" x-text="minutes">00</div>
                                <div class="text-xs">Phút</div>
                            </div>
                            <div class="bg-white/20 backdrop-blur-sm rounded-lg px-3 py-2 min-w-[70px] text-center">
                                <div class="text-2xl font-bold" x-text="seconds">00</div>
                                <div class="text-xs">Giây</div>
                            </div>
                        </div>
                    </div>

                    <!-- Button -->
                    <a href="<?= $ongoing_promotion['button_link'] ?? '/san-pham' ?>"
                       class="inline-block bg-yellow-400 hover:bg-yellow-500 text-red-700 px-8 py-4 rounded-lg font-bold text-lg transition transform hover:scale-105 shadow-lg">
                        <?= htmlspecialchars($ongoing_promotion['button_text'] ?? 'Mua ngay') ?> →
                    </a>

                    <!-- Ảnh banner (nếu có) -->
                    <?php if (!empty($ongoing_promotion['banner_image'])): ?>
                    <div class="mt-6">
                        <img src="<?= htmlspecialchars($ongoing_promotion['banner_image']) ?>"
                             alt="<?= htmlspecialchars($ongoing_promotion['title']) ?>"
                             class="w-full rounded-xl shadow-lg">
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Decorative elements -->
                <div class="absolute top-0 right-0 w-32 h-32 bg-white/10 rounded-full -mr-16 -mt-16"></div>
                <div class="absolute bottom-0 left-0 w-24 h-24 bg-white/10 rounded-full -ml-12 -mb-12"></div>
            </div>
            <?php else: ?>
            <!-- Không có chương trình đang diễn ra -->
            <div class="relative overflow-hidden rounded-2xl shadow-lg bg-gray-200 flex items-center justify-center min-h-[400px]">
                <div class="text-center text-gray-500">
                    <svg class="w-20 h-20 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <p class="text-xl font-semibold">Hiện không có chương trình nào đang diễn ra</p>
                </div>
            </div>
            <?php endif; ?>

            <!-- CHƯƠNG TRÌNH 2: SẮP DIỄN RA -->
            <?php if ($upcoming_promotion):
                $days_until = getDaysUntil($upcoming_promotion['start_date']);
            ?>
            <div class="relative overflow-hidden rounded-2xl shadow-2xl transform hover:scale-105 transition duration-300">
                <div class="absolute inset-0 bg-gradient-to-r from-blue-500 to-purple-600 opacity-90"></div>

                <div class="relative z-10 p-8 text-white">
                    <!-- Badge trạng thái -->
                    <div class="inline-flex items-center bg-green-400 text-blue-900 px-4 py-2 rounded-full text-sm font-bold mb-4">
                        <span class="w-2 h-2 bg-blue-900 rounded-full mr-2"></span>
                        🎯 SẮP DIỄN RA
                    </div>

                    <!-- Số ngày còn lại -->
                    <div class="mb-4">
                        <div class="inline-block bg-white/20 backdrop-blur-sm px-6 py-3 rounded-lg">
                            <span class="text-sm">Còn</span>
                            <span class="text-4xl font-bold mx-2"><?= $days_until ?></span>
                            <span class="text-sm">ngày nữa</span>
                        </div>
                    </div>

                    <!-- Tiêu đề -->
                    <h3 class="text-2xl md:text-3xl font-bold mb-4">
                        <?= htmlspecialchars($upcoming_promotion['title']) ?>
                    </h3>

                    <!-- Mô tả -->
                    <?php if (!empty($upcoming_promotion['description'])): ?>
                    <p class="text-lg mb-6 text-white/90">
                        <?= htmlspecialchars($upcoming_promotion['description']) ?>
                    </p>
                    <?php endif; ?>

                    <!-- Giảm giá -->
                    <div class="flex items-center gap-4 mb-6">
                        <div class="bg-white text-blue-600 px-6 py-3 rounded-lg font-bold text-3xl shadow-lg">
                            <?= htmlspecialchars($upcoming_promotion['discount_text']) ?>
                        </div>
                    </div>

                    <!-- Thời gian bắt đầu -->
                    <div class="mb-6 bg-white/10 backdrop-blur-sm rounded-lg p-4">
                        <div class="text-sm font-semibold mb-1">📅 Bắt đầu:</div>
                        <div class="text-xl font-bold">
                            <?= date('d/m/Y H:i', strtotime($upcoming_promotion['start_date'])) ?>
                        </div>
                    </div>

                    <!-- Countdown đến khi bắt đầu -->
                    <div class="mb-6" x-data="countdown('<?= date('Y-m-d H:i:s', strtotime($upcoming_promotion['start_date'])) ?>')">
                        <div class="text-sm font-semibold mb-2">⏰ Bắt đầu trong:</div>
                        <div class="flex gap-3">
                            <div class="bg-white/20 backdrop-blur-sm rounded-lg px-3 py-2 min-w-[70px] text-center">
                                <div class="text-2xl font-bold" x-text="days">00</div>
                                <div class="text-xs">Ngày</div>
                            </div>
                            <div class="bg-white/20 backdrop-blur-sm rounded-lg px-3 py-2 min-w-[70px] text-center">
                                <div class="text-2xl font-bold" x-text="hours">00</div>
                                <div class="text-xs">Giờ</div>
                            </div>
                            <div class="bg-white/20 backdrop-blur-sm rounded-lg px-3 py-2 min-w-[70px] text-center">
                                <div class="text-2xl font-bold" x-text="minutes">00</div>
                                <div class="text-xs">Phút</div>
                            </div>
                            <div class="bg-white/20 backdrop-blur-sm rounded-lg px-3 py-2 min-w-[70px] text-center">
                                <div class="text-2xl font-bold" x-text="seconds">00</div>
                                <div class="text-xs">Giây</div>
                            </div>
                        </div>
                    </div>

                    <!-- Button -->
                    <a href="<?= $upcoming_promotion['button_link'] ?? '/san-pham' ?>"
                       class="inline-block bg-green-400 hover:bg-green-500 text-blue-900 px-8 py-4 rounded-lg font-bold text-lg transition transform hover:scale-105 shadow-lg">
                        Xem chi tiết →
                    </a>

                    <!-- Ảnh banner (nếu có) -->
                    <?php if (!empty($upcoming_promotion['banner_image'])): ?>
                    <div class="mt-6">
                        <img src="<?= htmlspecialchars($upcoming_promotion['banner_image']) ?>"
                             alt="<?= htmlspecialchars($upcoming_promotion['title']) ?>"
                             class="w-full rounded-xl shadow-lg">
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Decorative elements -->
                <div class="absolute top-0 left-0 w-32 h-32 bg-white/10 rounded-full -ml-16 -mt-16"></div>
                <div class="absolute bottom-0 right-0 w-24 h-24 bg-white/10 rounded-full -mr-12 -mb-12"></div>
            </div>
            <?php else: ?>
            <!-- Không có chương trình sắp diễn ra -->
            <div class="relative overflow-hidden rounded-2xl shadow-lg bg-gray-200 flex items-center justify-center min-h-[400px]">
                <div class="text-center text-gray-500">
                    <svg class="w-20 h-20 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    <p class="text-xl font-semibold">Chưa có chương trình sắp diễn ra</p>
                </div>
            </div>
            <?php endif; ?>

        </div>
    </div>
</section>

<!--
====================
CẤU TRÚC DATABASE MẪU
====================

CREATE TABLE promotions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    discount_text VARCHAR(100) NOT NULL,
    start_date DATETIME NOT NULL,
    end_date DATETIME NOT NULL,
    banner_image VARCHAR(500),
    button_text VARCHAR(100) DEFAULT 'Mua ngay',
    button_link VARCHAR(500),
    status ENUM('active', 'inactive', 'draft') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_dates (start_date, end_date),
    INDEX idx_status (status)
);

====================
DỮ LIỆU MẪU
====================

INSERT INTO promotions (title, description, discount_text, start_date, end_date, button_text, button_link, status) VALUES
('BLACK FRIDAY SALE', 'Giảm giá cực sốc toàn bộ sản phẩm', 'GIẢM 50%', '2025-11-01 00:00:00', '2025-11-15 23:59:59', 'Mua ngay', '/san-pham', 'active'),
('TẾT SALE 2026', 'Đón tết với ưu đãi khủng', 'GIẢM 40%', '2026-01-20 00:00:00', '2026-02-05 23:59:59', 'Xem ngay', '/san-pham', 'active'),
('SUMMER SALE', 'Mùa hè sôi động với giảm giá', 'GIẢM 30%', '2026-06-01 00:00:00', '2026-06-30 23:59:59', 'Khám phá', '/san-pham', 'active');

-->
