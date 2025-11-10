<?php
require_once 'config.php';

$page_title = 'Về chúng tôi - ' . SITE_NAME;
$page_description = 'Tìm hiểu về xưởng trà Nguyên Ký - đơn vị hàng đầu trong lĩnh vực sản xuất và cung cấp nguyên liệu trà chất lượng cao';

include 'includes/header.php';
?>

<!-- Breadcrumb -->
<div class="bg-gray-100 py-4">
    <div class="container mx-auto px-4">
        <nav class="flex" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-3">
                <li class="inline-flex items-center">
                    <a href="/" class="text-gray-700 hover:text-green-600">Trang chủ</a>
                </li>
                <li>
                    <div class="flex items-center">
                        <svg class="w-3 h-3 text-gray-400 mx-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                                clip-rule="evenodd" />
                        </svg>
                        <span class="text-gray-500">Về chúng tôi</span>
                    </div>
                </li>
            </ol>
        </nav>
    </div>
</div>

<!-- Hero Section -->
<section class="py-16 bg-gradient-to-br from-green-50 to-blue-50">
    <div class="container mx-auto px-4">
        <div class="text-center max-w-3xl mx-auto">
            <h1 class="text-4xl md:text-5xl font-bold mb-6" style="color: <?= COLOR_PRIMARY ?>">
                Xưởng Trà Nguyên Ký
            </h1>
            <p class="text-xl text-gray-700 mb-8">
                Hơn 10 năm kinh nghiệm trong sản xuất và cung cấp nguyên liệu trà chất lượng cao cho ngành pha chế tại
                Việt Nam
            </p>
        </div>
    </div>
</section>

<!-- About Content -->
<section class="py-16 bg-white">
    <div class="container mx-auto px-4">
        <div class="grid md:grid-cols-2 gap-12 items-center mb-16">
            <div>
                <img src="/uploads/about-factory.jpg" alt="Xưởng sản xuất" class="rounded-lg shadow-xl w-full">
            </div>
            <div>
                <h2 class="text-3xl font-bold mb-6" style="color: <?= COLOR_PRIMARY ?>">Câu chuyện của chúng tôi</h2>
                <p class="text-gray-700 mb-4 text-lg">
                    Nguyên Ký được thành lập với sứ mệnh mang đến những sản phẩm trà chất lượng cao nhất cho các quán
                    trà sữa,
                    cà phê và nhà hàng trên toàn quốc. Chúng tôi tự hào là đối tác tin cậy của hơn 1,000 cửa hàng trên
                    cả nước.
                </p>
                <p class="text-gray-700 mb-4 text-lg">
                    Với đội ngũ chuyên gia giàu kinh nghiệm và dây chuyền sản xuất hiện đại, chúng tôi cam kết mang đến
                    những sản phẩm đạt tiêu chuẩn vệ sinh an toàn thực phẩm, giúp khách hàng yên tâm phát triển kinh
                    doanh.
                </p>
            </div>
        </div>

        <!-- Vision & Mission -->
        <div class="grid md:grid-cols-3 gap-8 mb-16">
            <div class="bg-green-50 rounded-lg p-8 text-center">
                <div class="w-16 h-16 bg-green-600 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                </div>
                <h3 class="text-xl font-bold mb-3">Tầm nhìn</h3>
                <p class="text-gray-700">
                    Trở thành nhà cung cấp nguyên liệu trà hàng đầu Việt Nam, đồng hành cùng sự phát triển của ngành F&B
                </p>
            </div>

            <div class="bg-blue-50 rounded-lg p-8 text-center">
                <div class="w-16 h-16 bg-blue-600 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                </div>
                <h3 class="text-xl font-bold mb-3">Sứ mệnh</h3>
                <p class="text-gray-700">
                    Cung cấp nguyên liệu trà chất lượng cao với giá cả hợp lý, hỗ trợ doanh nghiệp phát triển bền vững
                </p>
            </div>

            <div class="bg-yellow-50 rounded-lg p-8 text-center">
                <div class="w-16 h-16 bg-yellow-600 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                    </svg>
                </div>
                <h3 class="text-xl font-bold mb-3">Giá trị cốt lõi</h3>
                <p class="text-gray-700">
                    Chất lượng - Uy tín - Tận tâm - Đổi mới sáng tạo - Phát triển bền vững
                </p>
            </div>
        </div>

        <!-- Numbers -->
        <div class="bg-gradient-to-r from-green-600 to-blue-600 rounded-2xl p-12 text-white mb-16">
            <h2 class="text-3xl font-bold text-center mb-12">Nguyên Ký trong con số</h2>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-8">
                <div class="text-center">
                    <div class="text-5xl font-bold mb-2">10+</div>
                    <div class="text-lg opacity-90">Năm kinh nghiệm</div>
                </div>
                <div class="text-center">
                    <div class="text-5xl font-bold mb-2">1000+</div>
                    <div class="text-lg opacity-90">Khách hàng</div>
                </div>
                <div class="text-center">
                    <div class="text-5xl font-bold mb-2">50+</div>
                    <div class="text-lg opacity-90">Sản phẩm</div>
                </div>
                <div class="text-center">
                    <div class="text-5xl font-bold mb-2">100%</div>
                    <div class="text-lg opacity-90">Đạt chuẩn VSATTP</div>
                </div>
            </div>
        </div>

        <!-- Why Choose Us -->
        <div>
            <h2 class="text-3xl font-bold text-center mb-12" style="color: <?= COLOR_PRIMARY ?>">
                Tại sao chọn Nguyên Ký?
            </h2>

            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                <div class="flex items-start">
                    <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center mr-4 flex-shrink-0">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold mb-2">Chất lượng đảm bảo</h3>
                        <p class="text-gray-600">Sản phẩm đạt tiêu chuẩn VSATTP, được kiểm định nghiêm ngặt</p>
                    </div>
                </div>

                <div class="flex items-start">
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mr-4 flex-shrink-0">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold mb-2">Giá cả cạnh tranh</h3>
                        <p class="text-gray-600">Cam kết giá tốt nhất thị trường cho khách hàng thân thiết</p>
                    </div>
                </div>

                <div class="flex items-start">
                    <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center mr-4 flex-shrink-0">
                        <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold mb-2">Giao hàng nhanh</h3>
                        <p class="text-gray-600">Vận chuyển toàn quốc, cam kết giao hàng đúng hạn</p>
                    </div>
                </div>

                <div class="flex items-start">
                    <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center mr-4 flex-shrink-0">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold mb-2">Tùy chỉnh linh hoạt</h3>
                        <p class="text-gray-600">Dịch vụ OEM theo yêu cầu, thiết kế bao bì riêng</p>
                    </div>
                </div>

                <div class="flex items-start">
                    <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center mr-4 flex-shrink-0">
                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold mb-2">Hỗ trợ tận tình</h3>
                        <p class="text-gray-600">Đội ngũ tư vấn chuyên nghiệp, hỗ trợ 24/7</p>
                    </div>
                </div>

                <div class="flex items-start">
                    <div class="w-12 h-12 bg-indigo-100 rounded-lg flex items-center justify-center mr-4 flex-shrink-0">
                        <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold mb-2">Chứng nhận uy tín</h3>
                        <p class="text-gray-600">Giấy phép kinh doanh đầy đủ, chứng nhận an toàn thực phẩm</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA -->
<section class="py-16 bg-gradient-to-r from-green-600 to-blue-600 text-white">
    <div class="container mx-auto px-4 text-center">
        <h2 class="text-3xl md:text-4xl font-bold mb-4">Sẵn sàng hợp tác cùng Nguyên Ký?</h2>
        <p class="text-xl mb-8 max-w-2xl mx-auto">
            Liên hệ ngay với chúng tôi để được tư vấn và báo giá tốt nhất
        </p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="/lien-he"
                class="inline-block bg-white text-green-600 px-8 py-3 rounded-lg font-semibold hover:bg-gray-100 transition">
                Liên hệ ngay
            </a>
            <a href="/san-pham"
                class="inline-block border-2 border-white text-white px-8 py-3 rounded-lg font-semibold hover:bg-white hover:text-green-600 transition">
                Xem sản phẩm
            </a>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>