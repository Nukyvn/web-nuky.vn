<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?? SITE_NAME ?></title>

    <!-- SEO Meta Tags -->
    <meta name="description" content="<?= $page_description ?? get_setting('site_description') ?>">
    <meta name="keywords" content="<?= $page_keywords ?? get_setting('meta_keywords') ?>">

    <!-- Open Graph -->
    <meta property="og:title" content="<?= $page_title ?? SITE_NAME ?>">
    <meta property="og:description" content="<?= $page_description ?? get_setting('site_description') ?>">
    <meta property="og:image" content="<?= $page_og_image ?? SITE_URL . get_setting('meta_og_image') ?>">
    <meta property="og:url" content="<?= SITE_URL . $_SERVER['REQUEST_URI'] ?>">
    <meta property="og:type" content="website">

    <!-- Tailwind CSS -->
    <link rel="stylesheet" href="/assets/css/tailwind.css">


    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">

    <style>
    body {
        font-family: 'Inter', sans-serif;
    }

    .line-clamp-2 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .line-clamp-3 {
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    </style>
    <script src="/assets/js/cart.js"></script>
</head>

<body class="flex flex-col min-h-screen bg-gray-50">

    <!-- Top Bar -->
    <div class="bg-green-700 text-white py-2">
        <div class="container mx-auto px-4">
            <div class="flex flex-col sm:flex-row justify-between items-center text-sm">
                <div class="flex items-center space-x-4 mb-2 sm:mb-0">
                    <a href="tel:<?= get_setting('contact_phone') ?>" class="hover:text-yellow-300 flex items-center">
                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path
                                d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z" />
                        </svg>
                        <?= get_setting('contact_phone') ?>
                    </a>
                    <a href="mailto:<?= get_setting('contact_email') ?>"
                        class="hover:text-yellow-300 flex items-center">
                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z" />
                            <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z" />
                        </svg>
                        <?= get_setting('contact_email') ?>
                    </a>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="<?= get_setting('facebook_url') ?>" target="_blank" class="hover:text-yellow-300">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                            <path
                                d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z" />
                        </svg>
                    </a>
                    <a href="<?= get_setting('zalo_url') ?>" target="_blank" class="hover:text-yellow-300">
                        <span class="font-bold">Zalo</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Navigation -->
    <nav class="bg-white shadow-md sticky top-0 z-50" x-data="{ mobileMenuOpen: false }">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center py-4">
                <!-- Logo -->
                <a href="/" class="flex items-center space-x-2">
                    <?php 
    // Lấy URL logo
    $logo_url = get_setting('site_logo'); 
    ?>

                    <?php if ($logo_url): ?>
                    <img src="<?= $logo_url ?>" alt="<?= SITE_NAME ?>" class="h-12">
                    <?php endif; ?>

                    <span class="text-2xl font-bold" style="color: <?= COLOR_PRIMARY ?>">
                        <?= SITE_NAME ?>
                    </span>
                </a>
                <!-- Desktop Menu -->
                <div class="hidden lg:flex items-center space-x-8">
                    <a href="/" class="text-gray-700 hover:text-green-600 font-medium transition">Trang chủ</a>
                    <a href="/ve-chung-toi" class="text-gray-700 hover:text-green-600 font-medium transition">Giới
                        thiệu</a>
                    <a href="/san-pham" class="text-gray-700 hover:text-green-600 font-medium transition">Sản phẩm</a>
                    <a href="/dich-vu" class="text-gray-700 hover:text-green-600 font-medium transition">Dịch vụ</a>
                    <a href="/bai-viet" class="text-gray-700 hover:text-green-600 font-medium transition">Bài viết</a>
                    <a href="/video" class="text-gray-700 hover:text-green-600 font-medium transition">Video</a>
                    <a href="/lien-he" class="text-gray-700 hover:text-green-600 font-medium transition">Liên hệ</a>
                </div>

                <!-- Cart & Mobile Menu Button -->
                <div class="flex items-center space-x-4">
                    <!-- Cart Icon -->
                    <a href="/gio-hang" id="cart-icon-target" class="relative text-gray-700 hover:text-green-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        <span data-cart-count
                            class="absolute -top-2 -right-2 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center font-bold">
                            <?= get_cart_count() ?>
                        </span>
                    </a>

                    <!-- Mobile Menu Toggle -->
                    <button @click="mobileMenuOpen = !mobileMenuOpen" class="lg:hidden text-gray-700">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Mobile Menu -->
            <div x-show="mobileMenuOpen" x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 transform scale-95"
                x-transition:enter-end="opacity-100 transform scale-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 transform scale-100"
                x-transition:leave-end="opacity-0 transform scale-95" class="lg:hidden pb-4">
                <a href="/" class="block py-2 text-gray-700 hover:text-green-600 font-medium">Trang chủ</a>
                <a href="/ve-chung-toi" class="block py-2 text-gray-700 hover:text-green-600 font-medium">Giới thiệu</a>
                <a href="/san-pham" class="block py-2 text-gray-700 hover:text-green-600 font-medium">Sản phẩm</a>
                <a href="/dich-vu" class="block py-2 text-gray-700 hover:text-green-600 font-medium">Dịch vụ</a>
                <a href="/bai-viet" class="block py-2 text-gray-700 hover:text-green-600 font-medium">Bài viết</a>
                <a href="/video" class="block py-2 text-gray-700 hover:text-green-600 font-medium">Video</a>
                <a href="/lien-he" class="block py-2 text-gray-700 hover:text-green-600 font-medium">Liên hệ</a>
            </div>
        </div>
    </nav>