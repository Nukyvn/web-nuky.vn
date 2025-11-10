<?php if (!empty($promotions)): ?>
<section class="py-20 relative overflow-hidden" style="background: linear-gradient(135deg, <?= COLOR_SECONDARY ?> 0%, #ffffff 100%);">
    <!-- Animated Background Elements -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute w-96 h-96 bg-gradient-to-br from-yellow-300/20 to-red-300/20 rounded-full blur-3xl -top-48 -left-32 animate-pulse"></div>
        <div class="absolute w-96 h-96 bg-gradient-to-br from-red-300/20 to-pink-300/20 rounded-full blur-3xl -bottom-48 -right-32 animate-pulse delay-1000"></div>
        <div class="absolute w-64 h-64 bg-gradient-to-br from-yellow-400/10 to-orange-300/10 rounded-full blur-2xl top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2"></div>
    </div>

    <div class="container mx-auto px-4 relative z-10">
        <!-- Section Header -->
        <div class="text-center mb-16">
            <div class="inline-block mb-4">
                <span class="bg-gradient-to-r from-red-500 to-yellow-500 text-white text-sm font-bold px-6 py-2 rounded-full shadow-lg">
                    🎁 SPECIAL OFFERS
                </span>
            </div>
            <h2 class="text-4xl md:text-5xl font-extrabold mb-4 bg-gradient-to-r from-red-600 to-yellow-600 bg-clip-text text-transparent">
                Khuyến Mãi Nổi Bật
            </h2>
            <p class="text-gray-600 text-lg max-w-2xl mx-auto">
                Đừng bỏ lỡ những ưu đãi hấp dẫn từ xưởng Nguyên Ký
            </p>
        </div>

        <!-- Promotions Grid -->
        <div class="grid md:grid-cols-2 lg:grid-cols-<?= count($promotions) > 2 ? '3' : '2' ?> gap-8 max-w-7xl mx-auto">
            <?php foreach ($promotions as $promotion):
                $banner = $promotion['banner_image'] ?: '/uploads/default-promotion.jpg';
                $countdown_end = $promotion['countdown_end'];
            ?>
            <div class="group relative">
                <!-- Main Card -->
                <div class="relative bg-white rounded-3xl overflow-hidden shadow-xl hover:shadow-2xl transition-all duration-500 transform hover:-translate-y-2">

                    <!-- Sale Badge -->
                    <?php if (!empty($promotion['description'])): ?>
                    <div class="absolute -top-4 -right-4 z-30">
                        <div class="relative">
                            <div class="bg-gradient-to-br from-red-600 via-red-500 to-yellow-500 text-white font-black text-lg px-8 py-3 rounded-full shadow-2xl transform rotate-12 group-hover:rotate-0 transition-transform duration-300">
                                <div class="flex items-center gap-2">
                                    <span class="text-2xl animate-bounce">🔥</span>
                                    <span><?= htmlspecialchars($promotion['description']) ?></span>
                                </div>
                            </div>
                            <!-- Glow effect -->
                            <div class="absolute inset-0 bg-gradient-to-br from-red-600 to-yellow-500 rounded-full blur-xl opacity-50 animate-pulse"></div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Image Container -->
                    <div class="relative h-72 overflow-hidden">
                        <img src="<?= $banner ?>"
                             alt="<?= htmlspecialchars($promotion['title']) ?>"
                             class="w-full h-full object-cover transform group-hover:scale-110 transition-transform duration-700">

                        <!-- Overlay Gradient -->
                        <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-black/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                    </div>

                    <!-- Content Container -->
                    <div class="p-8">
                        <!-- Title -->
                        <h3 class="text-2xl md:text-3xl font-bold mb-6 text-center" style="color: <?= COLOR_PRIMARY ?>">
                            <?= htmlspecialchars($promotion['title']) ?>
                        </h3>

                        <!-- Countdown Timer -->
                        <?php if ($countdown_end): ?>
                        <div class="mb-8 bg-gradient-to-r from-red-50 to-yellow-50 rounded-2xl p-6 border-2 border-red-200">
                            <div class="flex items-center justify-center gap-2 mb-3">
                                <span class="text-2xl">⏰</span>
                                <span class="text-sm font-bold text-gray-700 uppercase tracking-wider">
                                    Kết thúc trong
                                </span>
                            </div>
                            <div id="countdown-<?= $promotion['id'] ?>" class="flex justify-center gap-3"></div>
                        </div>
                        <?php endif; ?>

                        <!-- CTA Button -->
                        <div class="text-center">
                            <a href="<?= htmlspecialchars($promotion['button_link'] ?? '#') ?>"
                               class="group/btn relative inline-flex items-center justify-center gap-3 bg-gradient-to-r from-red-600 to-yellow-600 text-white px-10 py-4 rounded-xl font-bold text-lg overflow-hidden shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105">

                                <!-- Shine Effect -->
                                <span class="absolute inset-0 bg-gradient-to-r from-transparent via-white/30 to-transparent -translate-x-full group-hover/btn:translate-x-full transition-transform duration-700"></span>

                                <span class="relative"><?= htmlspecialchars($promotion['button_text'] ?? 'Xem Chi Tiết') ?></span>
                                <svg class="relative w-5 h-5 transform group-hover/btn:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                                </svg>
                            </a>
                        </div>
                    </div>

                    <!-- Bottom Accent Line -->
                    <div class="h-2 bg-gradient-to-r from-red-500 via-yellow-500 to-red-500"></div>
                </div>

                <!-- Decorative Corner Elements -->
                <div class="absolute -z-10 inset-0 bg-gradient-to-br from-red-500/20 to-yellow-500/20 rounded-3xl transform translate-x-4 translate-y-4 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Countdown Script -->
<script>
(function() {
    <?php foreach ($promotions as $promotion):
        if (!$promotion['countdown_end']) continue;
        $end_timestamp = (new DateTime($promotion['countdown_end']))->getTimestamp() * 1000;
    ?>
    const el_<?= $promotion['id'] ?> = document.getElementById('countdown-<?= $promotion['id'] ?>');
    const endTime_<?= $promotion['id'] ?> = <?= $end_timestamp ?>;

    function updateCountdown_<?= $promotion['id'] ?>() {
        const now = new Date().getTime();
        const distance = endTime_<?= $promotion['id'] ?> - now;

        if (distance < 0) {
            el_<?= $promotion['id'] ?>.innerHTML = '<div class="text-gray-400 font-bold text-lg">Đã kết thúc</div>';
            return;
        }

        const days = Math.floor(distance / (1000 * 60 * 60 * 24));
        const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((distance % (1000 * 60)) / 1000);

        el_<?= $promotion['id'] ?>.innerHTML = `
            <div class="flex flex-col items-center">
                <div class="bg-gradient-to-br from-red-500 to-red-600 text-white w-16 h-16 rounded-xl shadow-lg flex flex-col items-center justify-center transform hover:scale-110 transition-transform">
                    <div class="text-2xl font-black">${days}</div>
                    <div class="text-xs font-semibold opacity-90">Ngày</div>
                </div>
            </div>
            <div class="text-red-500 text-2xl font-bold">:</div>
            <div class="flex flex-col items-center">
                <div class="bg-gradient-to-br from-red-500 to-yellow-500 text-white w-16 h-16 rounded-xl shadow-lg flex flex-col items-center justify-center transform hover:scale-110 transition-transform">
                    <div class="text-2xl font-black">${hours}</div>
                    <div class="text-xs font-semibold opacity-90">Giờ</div>
                </div>
            </div>
            <div class="text-red-500 text-2xl font-bold">:</div>
            <div class="flex flex-col items-center">
                <div class="bg-gradient-to-br from-yellow-500 to-yellow-600 text-white w-16 h-16 rounded-xl shadow-lg flex flex-col items-center justify-center transform hover:scale-110 transition-transform">
                    <div class="text-2xl font-black">${minutes}</div>
                    <div class="text-xs font-semibold opacity-90">Phút</div>
                </div>
            </div>
            <div class="text-red-500 text-2xl font-bold">:</div>
            <div class="flex flex-col items-center">
                <div class="bg-gradient-to-br from-yellow-600 to-orange-500 text-white w-16 h-16 rounded-xl shadow-lg flex flex-col items-center justify-center transform hover:scale-110 transition-transform">
                    <div class="text-2xl font-black">${seconds}</div>
                    <div class="text-xs font-semibold opacity-90">Giây</div>
                </div>
            </div>
        `;
    }

    updateCountdown_<?= $promotion['id'] ?>();
    setInterval(updateCountdown_<?= $promotion['id'] ?>, 1000);
    <?php endforeach; ?>
})();
</script>

<style>
@keyframes pulse {
    0%, 100% { opacity: 0.5; transform: scale(1); }
    50% { opacity: 0.8; transform: scale(1.05); }
}

.delay-1000 {
    animation-delay: 1s;
}

.animate-pulse {
    animation: pulse 4s ease-in-out infinite;
}

@media (max-width: 768px) {
    #countdown-<?= $promotion['id'] ?> > div > div {
        width: 3.5rem !important;
        height: 3.5rem !important;
    }

    #countdown-<?= $promotion['id'] ?> > div > div > .text-2xl {
        font-size: 1.25rem !important;
    }
}
</style>
<?php endif; ?>
