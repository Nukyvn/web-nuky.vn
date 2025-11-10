<?php
require_once 'config.php';

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

// Get promotion section
$current_date = date('Y-m-d H:i:s');
$stmt = $pdo->prepare("
    SELECT * FROM promotions 
    WHERE status = 1 AND countdown_end >= :current_date
    ORDER BY start_date ASC 
    LIMIT 2
");
$stmt->execute([':current_date' => $current_date]);
$promotions = $stmt->fetchAll();

// Page metadata
$page_title = SITE_NAME . ' - Xưởng Trà Nguyên Ký';
$page_description = 'Chuyên sản xuất trà đen, bột kem béo, trà gạo rang, trà túi lọc và dịch vụ đóng gói OEM thương hiệu riêng.';

include 'includes/header.php';
?>

<!-- Hero Slider Section -->
<?php if (!empty($hero_banners)): ?>
<section class="hero-slider relative overflow-hidden" style="background-color: <?= COLOR_SECONDARY ?>">
    <div class="relative h-[500px] md:h-[600px]">
        <!-- Slides Container -->
        <div class="slides-container relative h-full">
            <?php foreach ($hero_banners as $index => $banner): ?>
            <div class="hero-slide absolute inset-0 transition-all duration-700 ease-in-out <?= $index === 0 ? 'opacity-100 translate-x-0' : 'opacity-0 translate-x-full' ?>"
                data-slide="<?= $index ?>">
                <!-- Background Image/Video -->
                <?php if ($banner['video']): ?>
                <video autoplay muted loop playsinline class="w-full h-full object-cover">
                    <source src="<?= $banner['video'] ?>" type="video/mp4">
                </video>
                <?php elseif ($banner['image']): ?>
                <img src="<?= $banner['image'] ?>" alt="<?= $banner['title'] ?>"
                    class="w-full h-full object-cover">
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
                            <a href="<?= $banner['link'] ?>"
                                class="inline-block bg-white text-gray-900 px-8 py-3 rounded-lg font-semibold hover:bg-opacity-90 transition animate-fade-in-delay-2">
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
        <button onclick="heroSlider.prev()"
            class="absolute left-4 top-1/2 -translate-y-1/2 bg-white/80 hover:bg-white text-gray-800 p-3 rounded-full shadow-lg transition z-10 group">
            <svg class="w-6 h-6 transform group-hover:-translate-x-1 transition" fill="none" stroke="currentColor"
                viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
        </button>
        <button onclick="heroSlider.next()"
            class="absolute right-4 top-1/2 -translate-y-1/2 bg-white/80 hover:bg-white text-gray-800 p-3 rounded-full shadow-lg transition z-10 group">
            <svg class="w-6 h-6 transform group-hover:translate-x-1 transition" fill="none" stroke="currentColor"
                viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
        </button>

        <!-- Indicators/Dots -->
        <div class="absolute bottom-6 left-1/2 -translate-x-1/2 flex space-x-2 z-10">
            <?php foreach ($hero_banners as $index => $banner): ?>
            <button onclick="heroSlider.goTo(<?= $index ?>)"
                class="hero-indicator w-3 h-3 rounded-full transition-all <?= $index === 0 ? 'bg-white w-8' : 'bg-white/50 hover:bg-white/75' ?>"
                data-indicator="<?= $index ?>">
            </button>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</section>

<style>
    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(20px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .animate-fade-in {
        animation: fadeIn 0.8s ease-out forwards;
    }

    .animate-fade-in-delay {
        opacity: 0;
        animation: fadeIn 0.8s ease-out 0.2s forwards;
    }

    .animate-fade-in-delay-2 {
        opacity: 0;
        animation: fadeIn 0.8s ease-out 0.4s forwards;
    }

    .hero-slide {
        transition: opacity 0.7s ease-in-out, transform 0.7s ease-in-out;
    }
</style>

<script>
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

            // Update indicators
            indicators[this.currentSlide].classList.remove('bg-white', 'w-8');
            indicators[this.currentSlide].classList.add('bg-white/50');

            // Update current slide
            this.currentSlide = index;

            // Show new slide
            slides[this.currentSlide].classList.remove('opacity-0', 'translate-x-full', '-translate-x-full');
            slides[this.currentSlide].classList.add('opacity-100', 'translate-x-0');

            // Update indicators
            indicators[this.currentSlide].classList.remove('bg-white/50');
            indicators[this.currentSlide].classList.add('bg-white', 'w-8');

            // Reset autoplay
            this.resetAutoPlay();
        },

        next() {
            const nextSlide = (this.currentSlide + 1) % this.totalSlides;
            this.goTo(nextSlide);
        },

        prev() {
            const prevSlide = (this.currentSlide - 1 + this.totalSlides) % this.totalSlides;
            this.goTo(prevSlide);
        },

        startAutoPlay() {
            this.autoPlayInterval = setInterval(() => {
                this.next();
            }, 5000); // Change slide every 5 seconds
        },

        resetAutoPlay() {
            if (this.autoPlayInterval) {
                clearInterval(this.autoPlayInterval);
                this.startAutoPlay();
            }
        }
    };

    // Initialize slider when DOM is loaded
    document.addEventListener('DOMContentLoaded', () => {
        heroSlider.init();
    });
</script>
<?php endif; ?>

<!-- Featured Products -->
<?php if (!empty($featured_products)): ?>
<section class="py-16 bg-white">
    <div class="container mx-auto px-4">
        <div class="text-center mb-12">
            <h2 class="text-3xl md:text-4xl font-bold mb-4" style="color: <?= COLOR_PRIMARY ?>">Sản phẩm nổi bật</h2>
            <p class="text-gray-600 max-w-2xl mx-auto">Chọn lọc những sản phẩm chất lượng cao nhất từ xưởng Nguyên Ký
            </p>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            <?php foreach ($featured_products as $product): 
        $images = json_decode($product['images'], true);
        $image = $images[0] ?? '/uploads/no-image.png';
        $price = $product['sale_price'] ?? $product['price'];
    ?>
            <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-xl transition group product-item">
                <a href="/san-pham/<?= $product['slug'] ?>" class="block relative overflow-hidden">
                    <img src="<?= $image ?>" alt="<?= $product['name'] ?>"
                        class="w-full h-64 object-cover group-hover:scale-110 transition duration-300 product-image">
                    <?php if ($product['sale_price']): ?>
                    <span class="absolute top-2 right-2 bg-red-500 text-white px-2 py-1 rounded text-sm font-semibold">
                        -<?= round((1 - $product['sale_price'] / $product['price']) * 100) ?>%
                    </span>
                    <?php endif; ?>
                </a>

                <div class="p-4">
                    <h3 class="font-semibold text-lg mb-2 line-clamp-2">
                        <a href="/san-pham/<?= $product['slug'] ?>"
                            class="hover:text-green-600"><?= $product['name'] ?></a>
                    </h3>

                    <button onclick="
                var container = this.closest('.product-item'); 
                var imgElement = container ? container.querySelector('.product-image') : null;
                
                addToCart(<?= $product['id'] ?>, imgElement);
            " class="w-full mt-4 text-white py-2 rounded-lg transition font-semibold"
                        style="background-color: <?= COLOR_PRIMARY ?>;"
                        onmouseover="this.style.backgroundColor='#1a5e20';"
                        onmouseout="this.style.backgroundColor='<?= COLOR_PRIMARY ?>';">
                        Thêm vào giỏ
                    </button>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="text-center mt-8">
            <a href="/san-pham"
                class="inline-block border-2 border-green-600 text-green-600 px-8 py-3 rounded-lg font-semibold hover:bg-green-600 hover:text-white transition">
                Xem tất cả sản phẩm
            </a>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Promotion Section -->
<div class="grid md:grid-cols-2 gap-8">
    <div class="absolute inset-0 bg-gradient-to-r from-green-400 to-blue-500 opacity-10"></div>
    <div class="relative z-10">
        <h2 class="text-3xl md:text-4xl font-bold text-center mb-8 text-white" style="color: <?= COLOR_PRIMARY ?>">
            Khuyến mãi đặc biệt
        </h2>
    </div>
    <?php foreach ($promotions as $promotion): 
        $image = $promotion['banner_image'] ?: '/uploads/no-image.png';
    ?>
    <section class="p-6 rounded-lg shadow-xl"
        style="background: linear-gradient(135deg, #E25848FF 0%, #EBC804FF 100%);">
        <div class="p-6">
            <div class="flex flex-col md:flex-row items-center md:items-start md:space-x-6">
                <img src="<?= $image ?>" alt="<?= $promotion['title'] ?>"
                    class="w-full md:w-1/2 h-auto rounded-lg mb-4 md:mb-0 shadow-lg">
                <div class="mb-6">
                    <h2 class="text-2xl md:text-3xl font-bold mb-4 text-white"><?= $promotion['title'] ?></h2>
                    <?php if (!empty($promotion['countdown_end'])): 
                        $end_datetime = new DateTime($promotion['countdown_end']);
                    ?>
                    <h3 class="text-xl font-semibold mb-2">Thời gian còn lại:</h3>
                    <div id="countdown-<?= $promotion['id'] ?>" class="text-2xl font-bold text-red-600"></div>
                </div>

                <script>
                // Countdown timer
                function startCountdown<?= $promotion['id'] ?>() {
                    const countdownElement = document.getElementById('countdown-<?= $promotion['id'] ?>');
                    const endTime = new Date('<?= $end_datetime->format('Y-m-d H:i:s') ?>').getTime();

                    const interval = setInterval(() => {
                        const now = new Date().getTime();
                        const distance = endTime - now;

                        if (distance < 0) {
                            clearInterval(interval);
                            countdownElement.innerHTML = 'Đã kết thúc';
                            return;
                        }

                        const days = Math.floor(distance / (1000 * 60 * 60 * 24));
                        const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                        const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                        const seconds = Math.floor((distance % (1000 * 60)) / 1000);

                        countdownElement.innerHTML =
                            `${days}ngày ${hours}giờ ${minutes}phút ${seconds}giây`;
                    }, 1000);
                }
                startCountdown<?= $promotion['id'] ?>();
                </script>
                <?php endif; ?>
                <a href="<?= $promotion['link'] ?>"
                    class="inline-block bg-green-600 text-white px-8 py-3 rounded-lg font-semibold hover:bg-green-700 transition">
                    <?= $promotion['button_text'] ?? 'Xem chi tiết' ?>
                </a>
            </div>
        </div>
    </section>
    <?php endforeach; ?>
</div>

<!-- About Section -->
<section class="py-16" style="background-color: <?= COLOR_SECONDARY ?>">
    <div class="container mx-auto px-4">
        <div class="grid md:grid-cols-2 gap-12 items-center">
            <div>
                <h2 class="text-3xl md:text-4xl font-bold mb-6" style="color: <?= COLOR_PRIMARY ?>">Về Nguyên Ký
                </h2>
                <p class="text-gray-700 mb-4 text-lg">
                    Xưởng trà Nguyên Ký tự hào là đơn vị hàng đầu trong lĩnh vực sản xuất và cung cấp nguyên
                    liệu trà
                    chất lượng cao cho ngành pha chế tại Việt Nam.
                </p>
                <p class="text-gray-700 mb-6 text-lg">
                    Với hơn 10 năm kinh nghiệm, chúng tôi cam kết mang đến những sản phẩm đạt tiêu chuẩn vệ sinh
                    an toàn
                    thực phẩm, đáp ứng nhu cầu của hàng ngàn quán trà sữa và cà phê trên toàn quốc.
                </p>
                <a href="/ve-chung-toi"
                    class="inline-block bg-green-600 text-white px-8 py-3 rounded-lg font-semibold hover:bg-green-700 transition">
                    Tìm hiểu thêm
                </a>
            </div>
            <div>
                <img src="/uploads/about-image.jpg" alt="Xưởng Nguyên Ký" class="rounded-lg shadow-lg w-full">
            </div>
        </div>
    </div>
</section>

<!-- Services -->
<section class="py-16 bg-white">
    <div class="container mx-auto px-4">
        <div class="text-center mb-12">
            <h2 class="text-3xl md:text-4xl font-bold mb-4" style="color: <?= COLOR_PRIMARY ?>">Dịch vụ của
                chúng tôi
            </h2>
        </div>

        <div class="grid md:grid-cols-3 gap-8">
            <div class="text-center p-6 bg-gray-50 rounded-lg hover:shadow-lg transition">
                <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-bold mb-3">Sản xuất OEM</h3>
                <p class="text-gray-600">Đóng gói theo thương hiệu riêng của bạn với thiết kế bao bì chuyên
                    nghiệp</p>
            </div>

            <div class="text-center p-6 bg-gray-50 rounded-lg hover:shadow-lg transition">
                <div class="w-16 h-16 bg-yellow-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-bold mb-3">Chất lượng đảm bảo</h3>
                <p class="text-gray-600">Đạt tiêu chuẩn VSATTP, kiểm định chặt chẽ từng khâu sản xuất</p>
            </div>

            <div class="text-center p-6 bg-gray-50 rounded-lg hover:shadow-lg transition">
                <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-bold mb-3">Giao hàng nhanh</h3>
                <p class="text-gray-600">Vận chuyển toàn quốc, cam kết giao hàng đúng hạn</p>
            </div>
        </div>
    </div>
</section>

<!-- Testimonials -->
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
                <div class="flex items-center mb-4">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                    <svg class="w-5 h-5 <?= $i <= $testimonial['rating'] ? 'text-yellow-400' : 'text-gray-300' ?>"
                        fill="currentColor" viewBox="0 0 20 20">
                        <path
                            d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z">
                        </path>
                    </svg>
                    <?php endfor; ?>
                </div>
                <p class="text-gray-700 mb-4 italic line-clamp-4">"<?= $testimonial['content'] ?>"</p>
                <div class="flex items-center">
                    <?php if ($testimonial['customer_avatar']): ?>
                    <img src="<?= $testimonial['customer_avatar'] ?>" alt="<?= $testimonial['customer_name'] ?>"
                        class="w-12 h-12 rounded-full mr-4 object-cover">
                    <?php else: ?>
                    <div class="w-12 h-12 rounded-full flex items-center justify-center mr-4"
                        style="background-color: <?= COLOR_PRIMARY ?>20;">
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

<!-- Latest Articles -->
<?php if (!empty($latest_articles)): ?>
<section class="py-16 bg-white">
    <div class="container mx-auto px-4">
        <div class="text-center mb-12">
            <h2 class="text-3xl md:text-4xl font-bold mb-4" style="color: <?= COLOR_PRIMARY ?>">Tin tức & Bài
                viết</h2>
        </div>

        <div class="grid md:grid-cols-3 gap-8">
            <?php foreach ($latest_articles as $article): ?>
            <article class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-xl transition">
                <a href="/bai-viet/<?= $article['slug'] ?>">
                    <img src="<?= $article['thumbnail'] ?>" alt="<?= $article['title'] ?>"
                        class="w-full h-48 object-cover">
                </a>
                <div class="p-6">
                    <div class="text-sm text-gray-500 mb-2"><?= format_date($article['created_at']) ?></div>
                    <h3 class="text-xl font-bold mb-3 line-clamp-2">
                        <a href="/bai-viet/<?= $article['slug'] ?>"
                            class="hover:text-green-600"><?= $article['title'] ?></a>
                    </h3>
                    <p class="text-gray-600 mb-4 line-clamp-3"><?= $article['excerpt'] ?></p>
                    <a href="/bai-viet/<?= $article['slug'] ?>" class="text-green-600 font-semibold hover:underline">Đọc
                        tiếp →</a>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Video Section -->
<section class="py-16" style="background-color: <?= COLOR_SECONDARY ?>">
    <div class="container mx-auto px-4">
        <div class="text-center mb-12">
            <h2 class="text-3xl md:text-4xl font-bold mb-4" style="color: <?= COLOR_PRIMARY ?>">Thư viện Video
            </h2>
            <p class="text-gray-700 max-w-2xl mx-auto">Khám phá quy trình sản xuất, hướng dẫn pha chế và nhiều
                hơn
                nữa</p>
        </div>

        <div class="grid md:grid-cols-3 gap-8">
            <?php
        // Fetch 3 latest videos
        $stmt = $pdo->prepare("SELECT * FROM videos WHERE status = 1 ORDER BY created_at DESC LIMIT 6");
        $stmt->execute();
        $latest_videos = $stmt->fetchAll();

        foreach ($latest_videos as $video):
    ?>
            <article class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-xl transition">
                <?php
        // Xử lý thumbnail
        $thumb = $video['thumbnail'];
        if (empty($thumb) && $video['video_type'] === 'youtube' && !empty($video['video_url'])) {
            preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/)([^\&\?]+)/', $video['video_url'], $matches);
            $youtube_id = $matches[1] ?? null;
            if ($youtube_id) {
                $thumb = "https://img.youtube.com/vi/$youtube_id/hqdefault.jpg";
            }
        }
    ?>
                <a href="/video/<?= $video['slug'] ?>" class="block relative">
                    <div class="relative h-48 bg-gray-900">
                        <?php if ($thumb): ?>
                        <img src="<?= $thumb ?>" alt="<?= htmlspecialchars($video['title']) ?>"
                            class="w-full h-full object-cover">
                        <?php else: ?>
                        <div class="flex items-center justify-center h-full">
                            <svg class="w-16 h-16 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <?php endif; ?>
                        <div
                            class="absolute inset-0 flex items-center justify-center bg-yellow bg-opacity-10 opacity-10 hover:opacity-50 transition">
                            <svg class="w-12 h-12 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path
                                    d="M6.5 5.5v9l7-4.5-7-4.5zM10 0C4.477 0 0 4.477 0 10s4.477 10 10 10 10-4.477 10-10S15.523 0 10 0zM10 18.182A8.182 8.182 0 1110 1.818a8.182 8.182 0 010 16.364z">
                                </path>
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

<!-- CTA Section -->
<section class="py-16 bg-green-600 text-white">
    <div class="container mx-auto px-4 text-center">
        <h2 class="text-3xl md:text-4xl font-bold mb-4">Bạn cần tư vấn về sản phẩm?</h2>
        <p class="text-xl mb-8 max-w-2xl mx-auto">Liên hệ ngay với chúng tôi để được hỗ trợ tốt nhất</p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="/lien-he"
                class="inline-block bg-white text-green-600 px-8 py-3 rounded-lg font-semibold hover:bg-gray-100 transition">
                Liên hệ ngay
            </a>
            <a href="tel:<?= get_setting('contact_phone') ?>"
                class="inline-block border-2 border-white text-white px-8 py-3 rounded-lg font-semibold hover:bg-white hover:text-green-600 transition">
                Gọi: <?= get_setting('contact_phone') ?>
            </a>
        </div>
    </div>
</section>

<script>
// Countdown Timer
<?php foreach ($promotions as $promotion):
    $end_datetime = new DateTime($promotion['countdown_end']);
    $end_timestamp = $end_datetime->getTimestamp() * 1000; // Convert to milliseconds
    ?>
var countdownElement<?= $promotion['id'] ?> = document.getElementById('countdown-<?= $promotion['id'] ?>');
var countdownInterval<?= $promotion['id'] ?> = setInterval(function() {
    var now = new Date().getTime();
    var distance = <?= $end_timestamp ?> - now;
    if (distance < 0) {
        clearInterval(countdownInterval<?= $promotion['id'] ?>);
        countdownElement<?= $promotion['id'] ?>.innerHTML = "Ưu đãi đã kết thúc";
        return;
    }
    var days = Math.floor(distance / (1000 * 60 * 60 * 24));
    var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
    var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
    var seconds = Math.floor((distance % (1000 * 60)) / 1000);
    countdownElement<?= $promotion['id'] ?>.innerHTML = days + "ngày " + hours + "giờ " +
        minutes + "phút " + seconds + "giây ";
}, 1000);
<?php endforeach; ?>
</script>

<?php include ROOT_PATH . '/includes/footer.php'; ?>