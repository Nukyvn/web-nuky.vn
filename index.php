<?php
require_once 'config.php';

// ==================== DATABASE QUERIES ====================
// Get hero banners for slider
$stmt = $pdo->prepare("SELECT * FROM banners WHERE position = 'hero' AND status = 1 ORDER BY sort_order ASC LIMIT 5");
$stmt->execute();
$hero_banners = $stmt->fetchAll();

// Get featured products
$stmt = $pdo->prepare("SELECT * FROM products WHERE featured = 1 AND status = 1 ORDER BY created_at DESC LIMIT 8");
$stmt->execute();
$featured_products = $stmt->fetchAll();

// Get latest articles
$stmt = $pdo->prepare("SELECT * FROM articles WHERE status = 1 ORDER BY created_at DESC LIMIT 6");
$stmt->execute();
$latest_articles = $stmt->fetchAll();

// Get testimonials
$stmt = $pdo->prepare("SELECT * FROM testimonials WHERE status = 1 ORDER BY sort_order ASC LIMIT 6");
$stmt->execute();
$testimonials = $stmt->fetchAll();

// Get promotions
$stmt = $pdo->prepare("
    SELECT * FROM promotions
    WHERE status = 1
      AND (countdown_end IS NULL OR countdown_end >= NOW())
    ORDER BY countdown_end ASC
    LIMIT 2
");
$stmt->execute();
$promotions = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get latest videos
$stmt = $pdo->prepare("SELECT * FROM videos WHERE status = 1 ORDER BY created_at DESC LIMIT 6");
$stmt->execute();
$latest_videos = $stmt->fetchAll();

// ==================== HELPER FUNCTIONS ====================

/**
 * Render service card icon
 */
function renderServiceIcon($type, $color) {
    $icons = [
        'oem' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>',
        'quality' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>',
        'delivery' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>'
    ];

    return sprintf(
        '<div class="w-16 h-16 %s rounded-full flex items-center justify-center mx-auto mb-4">
            <svg class="w-8 h-8 %s" fill="none" stroke="currentColor" viewBox="0 0 24 24">%s</svg>
        </div>',
        "bg-{$color}-100",
        "text-{$color}-600",
        $icons[$type] ?? ''
    );
}

/**
 * Render star rating
 */
function renderStars($rating, $maxStars = 5) {
    $output = '';
    for ($i = 1; $i <= $maxStars; $i++) {
        $class = $i <= $rating ? 'text-yellow-400' : 'text-gray-300';
        $output .= sprintf(
            '<svg class="w-5 h-5 %s" fill="currentColor" viewBox="0 0 20 20">
                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
            </svg>',
            $class
        );
    }
    return $output;
}

/**
 * Get YouTube thumbnail from URL
 */
function getYoutubeThumbnail($video) {
    if (!empty($video['thumbnail'])) {
        return $video['thumbnail'];
    }

    if ($video['video_type'] === 'youtube' && !empty($video['video_url'])) {
        preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/)([^\&\?]+)/', $video['video_url'], $matches);
        $youtube_id = $matches[1] ?? null;
        if ($youtube_id) {
            return "https://img.youtube.com/vi/$youtube_id/hqdefault.jpg";
        }
    }

    return null;
}

// Page metadata
$page_title = SITE_NAME . ' - Xưởng Trà Nguyên Ký';
$page_description = 'Chuyên sản xuất trà đen, bột kem béo, trà gạo rang, trà túi lọc và dịch vụ đóng gói OEM thương hiệu riêng.';

include 'includes/header.php';
?>

<!-- ==================== STYLES ==================== -->
<style>
@keyframes soft-pulse {
    0%, 100% { transform: scale(1); box-shadow: 0 0 0 rgba(255, 59, 59, 0); }
    50% { transform: scale(1.06); box-shadow: 0 0 14px rgba(255, 59, 59, 0.5); }
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

.animate-pulse-soft { animation: soft-pulse 1.4s ease-in-out infinite; }
.animate-fade-in { animation: fadeIn 0.8s ease-out forwards; }
.animate-fade-in-delay { opacity: 0; animation: fadeIn 0.8s ease-out 0.2s forwards; }
.animate-fade-in-delay-2 { opacity: 0; animation: fadeIn 0.8s ease-out 0.4s forwards; }
.hero-slide { transition: opacity 0.7s ease-in-out, transform 0.7s ease-in-out; }
</style>

<!-- ==================== HERO SLIDER ==================== -->
<?php if (!empty($hero_banners)): ?>
<section class="hero-slider relative overflow-hidden" style="background-color: <?= COLOR_SECONDARY ?>">
    <div class="relative h-[500px] md:h-[600px]">
        <!-- Slides Container -->
        <div class="slides-container relative h-full">
            <?php foreach ($hero_banners as $index => $banner): ?>
            <div class="hero-slide absolute inset-0 transition-all duration-700 ease-in-out <?= $index === 0 ? 'opacity-100 translate-x-0' : 'opacity-0 translate-x-full' ?>" data-slide="<?= $index ?>">
                <!-- Background Image/Video -->
                <?php if ($banner['video']): ?>
                <video autoplay muted loop playsinline class="w-full h-full object-cover">
                    <source src="<?= $banner['video'] ?>" type="video/mp4">
                </video>
                <?php elseif ($banner['image']): ?>
                <img src="<?= $banner['image'] ?>" alt="<?= $banner['title'] ?>" class="w-full h-full object-cover">
                <?php endif; ?>

                <!-- Content Overlay -->
                <div class="absolute inset-0 flex items-center" style="background-color: rgba(0,0,0,0.3)">
                    <div class="container mx-auto px-4">
                        <div class="max-w-2xl text-white">
                            <h1 class="text-4xl md:text-6xl font-bold mb-4 animate-fade-in"><?= $banner['title'] ?></h1>
                            <?php if ($banner['subtitle']): ?>
                            <p class="text-xl md:text-2xl mb-6 animate-fade-in-delay"><?= $banner['subtitle'] ?></p>
                            <?php endif; ?>
                            <?php if ($banner['link']): ?>
                            <a href="<?= $banner['link'] ?>" class="inline-block bg-white text-gray-900 px-8 py-3 rounded-lg font-semibold hover:bg-opacity-90 transition animate-fade-in-delay-2">
                                <?= $banner['button_text'] ?? 'Xem thêm' ?>
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <?php if (count($hero_banners) > 1): ?>
        <!-- Navigation Arrows -->
        <button onclick="heroSlider.prev()" class="absolute left-4 top-1/2 -translate-y-1/2 bg-white/80 hover:bg-white text-gray-800 p-3 rounded-full shadow-lg transition z-10 group">
            <svg class="w-6 h-6 transform group-hover:-translate-x-1 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
        </button>
        <button onclick="heroSlider.next()" class="absolute right-4 top-1/2 -translate-y-1/2 bg-white/80 hover:bg-white text-gray-800 p-3 rounded-full shadow-lg transition z-10 group">
            <svg class="w-6 h-6 transform group-hover:translate-x-1 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
        </button>

        <!-- Indicators/Dots -->
        <div class="absolute bottom-6 left-1/2 -translate-x-1/2 flex space-x-2 z-10">
            <?php foreach ($hero_banners as $index => $banner): ?>
            <button onclick="heroSlider.goTo(<?= $index ?>)" class="hero-indicator w-3 h-3 rounded-full transition-all <?= $index === 0 ? 'bg-white w-8' : 'bg-white/50 hover:bg-white/75' ?>" data-indicator="<?= $index ?>"></button>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</section>
<?php endif; ?>

<!-- ==================== FEATURED PRODUCTS ==================== -->
<?php if (!empty($featured_products)): ?>
<section class="py-16 bg-white">
    <div class="container mx-auto px-4">
        <div class="text-center mb-12">
            <h2 class="text-3xl md:text-4xl font-bold mb-4" style="color: <?= COLOR_PRIMARY ?>">Sản phẩm nổi bật</h2>
            <p class="text-gray-600 max-w-2xl mx-auto">Chọn lọc những sản phẩm chất lượng cao nhất từ xưởng Nguyên Ký</p>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            <?php foreach ($featured_products as $product):
                $images = json_decode($product['images'], true);
                $image = $images[0] ?? '/uploads/no-image.png';
                $discount = $product['sale_price'] ? round((1 - $product['sale_price'] / $product['price']) * 100) : 0;
            ?>
            <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-xl transition group product-item">
                <a href="/san-pham/<?= $product['slug'] ?>" class="block relative overflow-hidden">
                    <img src="<?= $image ?>" alt="<?= $product['name'] ?>" class="w-full h-64 object-cover group-hover:scale-110 transition duration-300 product-image">
                    <?php if ($discount > 0): ?>
                    <span class="absolute top-2 right-2 bg-red-500 text-white px-2 py-1 rounded text-sm font-semibold">
                        -<?= $discount ?>%
                    </span>
                    <?php endif; ?>
                </a>

                <div class="p-4">
                    <h3 class="font-semibold text-lg mb-2 line-clamp-2">
                        <a href="/san-pham/<?= $product['slug'] ?>" class="hover:text-green-600"><?= $product['name'] ?></a>
                    </h3>

                    <button onclick="addToCart(<?= $product['id'] ?>, this.closest('.product-item')?.querySelector('.product-image'));"
                            class="w-full mt-4 text-white py-2 rounded-lg transition font-semibold hover:brightness-90"
                            style="background-color: <?= COLOR_PRIMARY ?>;">
                        Thêm vào giỏ
                    </button>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="text-center mt-8">
            <a href="/san-pham" class="inline-block border-2 border-green-600 text-green-600 px-8 py-3 rounded-lg font-semibold hover:bg-green-600 hover:text-white transition">
                Xem tất cả sản phẩm
            </a>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ==================== PROMOTION SECTION ==================== -->
<?php if (!empty($promotions)): ?>
<section class="py-16" style="background-color: <?= COLOR_SECONDARY ?>">
    <div class="container mx-auto px-4">
        <div class="text-center mb-12">
            <h2 class="text-3xl md:text-4xl font-bold mb-4" style="color: <?= COLOR_PRIMARY ?>">Khuyến mãi nổi bật</h2>
            <p class="text-gray-700 max-w-2xl mx-auto">Đừng bỏ lỡ những ưu đãi hấp dẫn từ xưởng Nguyên Ký</p>
        </div>

        <div class="grid md:grid-cols-<?= count($promotions) ?> gap-8 max-w-6xl mx-auto">
            <?php foreach ($promotions as $promotion):
                $banner = $promotion['banner_image'] ?: '/uploads/default-promotion.jpg';
                $has_countdown = !empty($promotion['countdown_end']);
            ?>
            <div class="rounded-2xl overflow-hidden shadow-2xl bg-gradient-to-br from-yellow-200 to-red-300 border border-red-300 relative">
                <!-- Description Badge -->
                <?php if (!empty($promotion['description'])): ?>
                <div class="text-center bg-yellow-600 text-white text-2xl md:text-4xl font-bold px-6 py-3 shadow-xl">
                    🔥 <?= htmlspecialchars($promotion['description']) ?>
                </div>
                <?php endif; ?>

                <!-- Promotion Image -->
                <div class="w-full">
                    <img src="<?= $banner ?>" alt="<?= htmlspecialchars($promotion['title']) ?>" class="w-full h-64 object-cover">
                </div>

                <!-- Content -->
                <div class="p-6 text-center">
                    <h3 class="text-3xl font-bold mb-4" style="color: <?= COLOR_PRIMARY ?>">
                        <?= htmlspecialchars($promotion['title']) ?>
                    </h3>

                    <!-- Countdown Timer -->
                    <?php if ($has_countdown): ?>
                    <div class="mb-6">
                        <div class="text-sm font-semibold text-gray-700 uppercase tracking-wide mb-3">
                            ⏰ Kết thúc trong
                        </div>
                        <div id="countdown-<?= $promotion['id'] ?>" class="flex justify-center gap-3 mb-2"></div>
                        <div class="text-gray-600 text-sm">
                            (Kết thúc vào <?= (new DateTime($promotion['countdown_end']))->format('d/m/Y H:i') ?>)
                        </div>
                        <div class="text-sm font-semibold text-red-600 uppercase tracking-wide mt-2">
                            Mua ngay kẻo lỡ!
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Call to Action Button -->
                    <a href="<?= htmlspecialchars($promotion['button_link'] ?? '#') ?>"
                       class="inline-block bg-green-600 text-white px-8 py-3 rounded-xl font-semibold hover:bg-yellow-700 transition-transform hover:scale-105 shadow-lg">
                        <?= htmlspecialchars($promotion['button_text'] ?? 'Xem chi tiết') ?>
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ==================== SERVICES ==================== -->
<section class="py-16 bg-white">
    <div class="container mx-auto px-4">
        <div class="text-center mb-12">
            <h2 class="text-3xl md:text-4xl font-bold mb-4" style="color: <?= COLOR_PRIMARY ?>">Dịch vụ của chúng tôi</h2>
        </div>

        <div class="grid md:grid-cols-3 gap-8">
            <!-- OEM Service -->
            <div class="text-center p-6 bg-gray-50 rounded-lg hover:shadow-lg transition">
                <?= renderServiceIcon('oem', 'green') ?>
                <h3 class="text-xl font-bold mb-3">Sản xuất OEM</h3>
                <p class="text-gray-600">Đóng gói theo thương hiệu riêng của bạn với thiết kế bao bì chuyên nghiệp</p>
            </div>

            <!-- Quality Service -->
            <div class="text-center p-6 bg-gray-50 rounded-lg hover:shadow-lg transition">
                <?= renderServiceIcon('quality', 'yellow') ?>
                <h3 class="text-xl font-bold mb-3">Chất lượng đảm bảo</h3>
                <p class="text-gray-600">Đạt tiêu chuẩn VSATTP, kiểm định chặt chẽ từng khâu sản xuất</p>
            </div>

            <!-- Delivery Service -->
            <div class="text-center p-6 bg-gray-50 rounded-lg hover:shadow-lg transition">
                <?= renderServiceIcon('delivery', 'blue') ?>
                <h3 class="text-xl font-bold mb-3">Giao hàng nhanh</h3>
                <p class="text-gray-600">Vận chuyển toàn quốc, cam kết giao hàng đúng hạn</p>
            </div>
        </div>
    </div>
</section>

<!-- ==================== TESTIMONIALS ==================== -->
<?php if (!empty($testimonials)): ?>
<section class="py-16" style="background-color: <?= COLOR_SECONDARY ?>">
    <div class="container mx-auto px-4">
        <div class="text-center mb-12">
            <h2 class="text-3xl md:text-4xl font-bold mb-4" style="color: <?= COLOR_PRIMARY ?>">
                <?= get_setting('testimonials_title', 'Khách hàng nói gì về chúng tôi') ?>
            </h2>
            <p class="text-gray-600 max-w-2xl mx-auto">
                <?= get_setting('testimonials_description', 'Hàng ngàn khách hàng tin tưởng và hài lòng với sản phẩm của Nguyên Ký') ?>
            </p>
        </div>

        <div class="grid md:grid-cols-3 gap-8">
            <?php foreach ($testimonials as $testimonial): ?>
            <div class="bg-white p-6 rounded-lg shadow-md hover:shadow-xl transition">
                <!-- Star Rating -->
                <div class="flex items-center mb-4">
                    <?= renderStars($testimonial['rating']) ?>
                </div>

                <!-- Testimonial Content -->
                <p class="text-gray-700 mb-4 italic line-clamp-4">"<?= $testimonial['content'] ?>"</p>

                <!-- Customer Info -->
                <div class="flex items-center">
                    <?php if ($testimonial['customer_avatar']): ?>
                    <img src="<?= $testimonial['customer_avatar'] ?>" alt="<?= $testimonial['customer_name'] ?>" class="w-12 h-12 rounded-full mr-4 object-cover">
                    <?php else: ?>
                    <div class="w-12 h-12 rounded-full flex items-center justify-center mr-4" style="background-color: <?= COLOR_PRIMARY ?>20;">
                        <span class="font-bold text-lg" style="color: <?= COLOR_PRIMARY ?>">
                            <?= mb_substr($testimonial['customer_name'], 0, 1) ?>
                        </span>
                    </div>
                    <?php endif; ?>
                    <div>
                        <p class="font-semibold"><?= $testimonial['customer_name'] ?></p>
                        <?php if ($testimonial['customer_title']): ?>
                        <p class="text-sm text-gray-500"><?= $testimonial['customer_title'] ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ==================== LATEST ARTICLES ==================== -->
<?php if (!empty($latest_articles)): ?>
<section class="py-16 bg-white">
    <div class="container mx-auto px-4">
        <div class="text-center mb-12">
            <h2 class="text-3xl md:text-4xl font-bold mb-4" style="color: <?= COLOR_PRIMARY ?>">Tin tức & Bài viết</h2>
        </div>

        <div class="grid md:grid-cols-3 gap-8">
            <?php foreach ($latest_articles as $article): ?>
            <article class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-xl transition">
                <a href="/bai-viet/<?= $article['slug'] ?>">
                    <img src="<?= $article['thumbnail'] ?>" alt="<?= $article['title'] ?>" class="w-full h-48 object-cover">
                </a>
                <div class="p-6">
                    <div class="text-sm text-gray-500 mb-2"><?= format_date($article['created_at']) ?></div>
                    <h3 class="text-xl font-bold mb-3 line-clamp-2">
                        <a href="/bai-viet/<?= $article['slug'] ?>" class="hover:text-green-600"><?= $article['title'] ?></a>
                    </h3>
                    <p class="text-gray-600 mb-4 line-clamp-3"><?= $article['excerpt'] ?></p>
                    <a href="/bai-viet/<?= $article['slug'] ?>" class="text-green-600 font-semibold hover:underline">Đọc tiếp →</a>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ==================== VIDEO SECTION ==================== -->
<section class="py-16" style="background-color: <?= COLOR_SECONDARY ?>">
    <div class="container mx-auto px-4">
        <div class="text-center mb-12">
            <h2 class="text-3xl md:text-4xl font-bold mb-4" style="color: <?= COLOR_PRIMARY ?>">Thư viện Video</h2>
            <p class="text-gray-700 max-w-2xl mx-auto">Khám phá quy trình sản xuất, hướng dẫn pha chế và nhiều hơn nữa</p>
        </div>

        <div class="grid md:grid-cols-3 gap-8">
            <?php foreach ($latest_videos as $video):
                $thumb = getYoutubeThumbnail($video);
            ?>
            <article class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-xl transition">
                <a href="/video/<?= $video['slug'] ?>" class="block relative">
                    <div class="relative h-48 bg-gray-900">
                        <?php if ($thumb): ?>
                        <img src="<?= $thumb ?>" alt="<?= htmlspecialchars($video['title']) ?>" class="w-full h-full object-cover">
                        <?php else: ?>
                        <div class="flex items-center justify-center h-full">
                            <svg class="w-16 h-16 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <?php endif; ?>

                        <!-- Play Button Overlay -->
                        <div class="absolute inset-0 flex items-center justify-center bg-black bg-opacity-10 opacity-0 hover:opacity-100 transition">
                            <svg class="w-12 h-12 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M6.5 5.5v9l7-4.5-7-4.5zM10 0C4.477 0 0 4.477 0 10s4.477 10 10 10 10-4.477 10-10S15.523 0 10 0zM10 18.182A8.182 8.182 0 1110 1.818a8.182 8.182 0 010 16.364z"></path>
                            </svg>
                        </div>
                    </div>
                </a>
                <div class="p-6">
                    <h3 class="text-xl font-bold mb-2 line-clamp-2">
                        <a href="/video/<?= $video['slug'] ?>" class="hover:text-green-600"><?= $video['title'] ?></a>
                    </h3>
                    <p class="text-gray-600 text-sm"><?= format_date($video['created_at']) ?></p>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ==================== CTA SECTION ==================== -->
<section class="py-16 bg-green-600 text-white">
    <div class="container mx-auto px-4 text-center">
        <h2 class="text-3xl md:text-4xl font-bold mb-4">Bạn cần tư vấn về sản phẩm?</h2>
        <p class="text-xl mb-8 max-w-2xl mx-auto">Liên hệ ngay với chúng tôi để được hỗ trợ tốt nhất</p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="/lien-he" class="inline-block bg-white text-green-600 px-8 py-3 rounded-lg font-semibold hover:bg-gray-100 transition">
                Liên hệ ngay
            </a>
            <a href="tel:<?= get_setting('contact_phone') ?>" class="inline-block border-2 border-white text-white px-8 py-3 rounded-lg font-semibold hover:bg-white hover:text-green-600 transition">
                Gọi: <?= get_setting('contact_phone') ?>
            </a>
        </div>
    </div>
</section>

<!-- ==================== SCRIPTS ==================== -->
<script>
// Hero Slider
const heroSlider = {
    currentSlide: 0,
    totalSlides: <?= count($hero_banners) ?>,
    autoPlayInterval: null,

    init() {
        if (this.totalSlides > 1) {
            this.startAutoPlay();
        }
    },

    goTo(index) {
        const slides = document.querySelectorAll('.hero-slide');
        const indicators = document.querySelectorAll('.hero-indicator');

        // Hide current slide
        slides[this.currentSlide].classList.remove('opacity-100', 'translate-x-0');
        slides[this.currentSlide].classList.add('opacity-0', 'translate-x-full');
        indicators[this.currentSlide].classList.remove('bg-white', 'w-8');
        indicators[this.currentSlide].classList.add('bg-white/50');

        // Update current slide
        this.currentSlide = index;

        // Show new slide
        slides[this.currentSlide].classList.remove('opacity-0', 'translate-x-full', '-translate-x-full');
        slides[this.currentSlide].classList.add('opacity-100', 'translate-x-0');
        indicators[this.currentSlide].classList.remove('bg-white/50');
        indicators[this.currentSlide].classList.add('bg-white', 'w-8');

        this.resetAutoPlay();
    },

    next() {
        this.goTo((this.currentSlide + 1) % this.totalSlides);
    },

    prev() {
        this.goTo((this.currentSlide - 1 + this.totalSlides) % this.totalSlides);
    },

    startAutoPlay() {
        this.autoPlayInterval = setInterval(() => this.next(), 5000);
    },

    resetAutoPlay() {
        if (this.autoPlayInterval) {
            clearInterval(this.autoPlayInterval);
            this.startAutoPlay();
        }
    }
};

// Countdown Timer Factory
function createCountdown(promotionId, endTimestamp) {
    const el = document.getElementById(`countdown-${promotionId}`);
    if (!el) return;

    function createBox(val, label) {
        return `
            <div class="animate-pulse-soft bg-red-600 text-white rounded-xl px-4 py-2 text-center shadow-lg min-w-[70px]">
                <div class="text-2xl font-bold">${val}</div>
                <div class="text-xs tracking-wide">${label}</div>
            </div>
        `;
    }

    function update() {
        const now = Date.now();
        const diff = endTimestamp - now;

        if (diff <= 0) {
            el.innerHTML = '<span class="text-gray-400 italic">Đã kết thúc</span>';
            return;
        }

        const days = Math.floor(diff / (1000 * 60 * 60 * 24));
        const hours = Math.floor((diff / (1000 * 60 * 60)) % 24);
        const minutes = Math.floor((diff / (1000 * 60)) % 60);
        const seconds = Math.floor((diff / 1000) % 60);

        el.innerHTML = createBox(days, 'Ngày') + createBox(hours, 'Giờ') + createBox(minutes, 'Phút') + createBox(seconds, 'Giây');
    }

    update();
    setInterval(update, 1000);
}

// Initialize on DOM ready
document.addEventListener('DOMContentLoaded', () => {
    heroSlider.init();

    // Initialize countdown timers
    <?php foreach ($promotions as $promotion):
        if (!empty($promotion['countdown_end'])):
            $end_timestamp = (new DateTime($promotion['countdown_end']))->getTimestamp() * 1000;
    ?>
    createCountdown(<?= $promotion['id'] ?>, <?= $end_timestamp ?>);
    <?php endif; endforeach; ?>
});
</script>

<?php include ROOT_PATH . '/includes/footer.php'; ?>
