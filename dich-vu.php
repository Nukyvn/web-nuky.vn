<?php
require_once 'config.php';

$page_title = 'Dịch vụ - ' . SITE_NAME;
$page_description = 'Dịch vụ sản xuất và đóng gói OEM thương hiệu riêng cho trà, nguyên liệu pha chế chất lượng cao';

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
                        <span class="text-gray-500">Dịch vụ</span>
                    </div>
                </li>
            </ol>
        </nav>
    </div>
</div>

<!-- Hero -->
<section class="py-16 bg-gradient-to-br from-green-50 to-blue-50">
    <div class="container mx-auto px-4 text-center">
        <h1 class="text-4xl md:text-5xl font-bold mb-6" style="color: <?= COLOR_PRIMARY ?>">
            Dịch vụ của chúng tôi
        </h1>
        <p class="text-xl text-gray-700 max-w-3xl mx-auto">
            Nguyên Ký cung cấp giải pháp toàn diện từ sản xuất, đóng gói đến phân phối nguyên liệu trà chất lượng cao
        </p>
    </div>
</section>

<!-- Main Services -->
<section class="py-16 bg-white">
    <div class="container mx-auto px-4">

        <!-- OEM Service -->
        <div class="grid md:grid-cols-2 gap-12 items-center mb-20">
            <div class="order-2 md:order-1">
                <div class="inline-block bg-green-100 text-green-800 px-4 py-2 rounded-full text-sm font-semibold mb-4">
                    Dịch vụ nổi bật
                </div>
                <h2 class="text-3xl md:text-4xl font-bold mb-6" style="color: <?= COLOR_PRIMARY ?>">
                    Sản xuất OEM Thương hiệu riêng
                </h2>
                <p class="text-gray-700 text-lg mb-6">
                    Chúng tôi hỗ trợ doanh nghiệp xây dựng thương hiệu riêng với dịch vụ sản xuất và đóng gói OEM chuyên
                    nghiệp.
                    Từ thiết kế bao bì đến sản xuất sản phẩm, chúng tôi đồng hành cùng bạn trong mọi bước.
                </p>

                <ul class="space-y-4 mb-8">
                    <li class="flex items-start">
                        <svg class="w-6 h-6 text-green-600 mt-1 mr-3 flex-shrink-0" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <span class="text-gray-700"><strong>Thiết kế bao bì:</strong> Đội ngũ thiết kế sáng tạo, tư vấn
                            miễn phí</span>
                    </li>
                    <li class="flex items-start">
                        <svg class="w-6 h-6 text-green-600 mt-1 mr-3 flex-shrink-0" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <span class="text-gray-700"><strong>Sản xuất theo yêu cầu:</strong> Linh hoạt về công thức, khối
                            lượng</span>
                    </li>
                    <li class="flex items-start">
                        <svg class="w-6 h-6 text-green-600 mt-1 mr-3 flex-shrink-0" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <span class="text-gray-700"><strong>Đóng gói chuyên nghiệp:</strong> Nhiều loại bao bì, nhãn mác
                            đầy đủ</span>
                    </li>
                    <li class="flex items-start">
                        <svg class="w-6 h-6 text-green-600 mt-1 mr-3 flex-shrink-0" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <span class="text-gray-700"><strong>Đăng ký giấy phép:</strong> Hỗ trợ hoàn tất thủ tục pháp
                            lý</span>
                    </li>
                </ul>

                <a href="/lien-he"
                    class="inline-block bg-green-600 text-white px-8 py-3 rounded-lg font-semibold hover:bg-green-700 transition">
                    Tư vấn ngay
                </a>
            </div>
            <div class="order-1 md:order-2">
                <img src="/uploads/oem-service.jpg" alt="Dịch vụ OEM" class="rounded-lg shadow-xl w-full">
            </div>
        </div>

        <!-- Wholesale -->
        <div class="grid md:grid-cols-2 gap-12 items-center mb-20">
            <div>
                <img src="/uploads/wholesale.jpg" alt="Bán buôn nguyên liệu" class="rounded-lg shadow-xl w-full">
            </div>
            <div>
                <h2 class="text-3xl md:text-4xl font-bold mb-6" style="color: <?= COLOR_PRIMARY ?>">
                    Cung cấp nguyên liệu sỉ
                </h2>
                <p class="text-gray-700 text-lg mb-6">
                    Với kho hàng luôn dồi dào, chúng tôi cung cấp đầy đủ các loại nguyên liệu trà, bột kem, topping
                    với giá sỉ cạnh tranh cho các quán trà sữa, cà phê và nhà phân phối.
                </p>

                <div class="grid grid-cols-2 gap-4 mb-8">
                    <div class="bg-green-50 rounded-lg p-4">
                        <div class="text-3xl font-bold text-green-600 mb-2">50+</div>
                        <div class="text-gray-700">Loại sản phẩm</div>
                    </div>
                    <div class="bg-blue-50 rounded-lg p-4">
                        <div class="text-3xl font-bold text-blue-600 mb-2">24h</div>
                        <div class="text-gray-700">Giao hàng nhanh</div>
                    </div>
                    <div class="bg-yellow-50 rounded-lg p-4">
                        <div class="text-3xl font-bold text-yellow-600 mb-2">100%</div>
                        <div class="text-gray-700">Chất lượng đảm bảo</div>
                    </div>
                    <div class="bg-purple-50 rounded-lg p-4">
                        <div class="text-3xl font-bold text-purple-600 mb-2">VIP</div>
                        <div class="text-gray-700">Giá ưu đãi</div>
                    </div>
                </div>

                <a href="/san-pham"
                    class="inline-block bg-green-600 text-white px-8 py-3 rounded-lg font-semibold hover:bg-green-700 transition">
                    Xem bảng giá
                </a>
            </div>
        </div>

        <!-- Consulting -->
        <div class="grid md:grid-cols-2 gap-12 items-center">
            <div class="order-2 md:order-1">
                <h2 class="text-3xl md:text-4xl font-bold mb-6" style="color: <?= COLOR_PRIMARY ?>">
                    Tư vấn mở quán trà sữa
                </h2>
                <p class="text-gray-700 text-lg mb-6">
                    Với kinh nghiệm nhiều năm trong ngành, chúng tôi cung cấp dịch vụ tư vấn toàn diện
                    giúp bạn khởi nghiệp kinh doanh trà sữa thành công.
                </p>

                <div class="space-y-4 mb-8">
                    <div class="flex items-start">
                        <div
                            class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center mr-4 flex-shrink-0">
                            <span class="text-green-600 font-bold text-lg">1</span>
                        </div>
                        <div>
                            <h3 class="font-bold text-lg mb-1">Tư vấn menu</h3>
                            <p class="text-gray-600">Xây dựng thực đơn phù hợp với thị trường địa phương</p>
                        </div>
                    </div>

                    <div class="flex items-start">
                        <div
                            class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mr-4 flex-shrink-0">
                            <span class="text-blue-600 font-bold text-lg">2</span>
                        </div>
                        <div>
                            <h3 class="font-bold text-lg mb-1">Đào tạo pha chế</h3>
                            <p class="text-gray-600">Hướng dẫn kỹ thuật pha chế chuyên nghiệp</p>
                        </div>
                    </div>

                    <div class="flex items-start">
                        <div
                            class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center mr-4 flex-shrink-0">
                            <span class="text-yellow-600 font-bold text-lg">3</span>
                        </div>
                        <div>
                            <h3 class="font-bold text-lg mb-1">Tư vấn thiết bị</h3>
                            <p class="text-gray-600">Gợi ý thiết bị phù hợp với quy mô quán</p>
                        </div>
                    </div>

                    <div class="flex items-start">
                        <div
                            class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center mr-4 flex-shrink-0">
                            <span class="text-purple-600 font-bold text-lg">4</span>
                        </div>
                        <div>
                            <h3 class="font-bold text-lg mb-1">Hỗ trợ marketing</h3>
                            <p class="text-gray-600">Chia sẻ kinh nghiệm quảng bá thương hiệu</p>
                        </div>
                    </div>
                </div>

                <a href="/lien-he"
                    class="inline-block bg-green-600 text-white px-8 py-3 rounded-lg font-semibold hover:bg-green-700 transition">
                    Đăng ký tư vấn
                </a>
            </div>
            <div class="order-1 md:order-2">
                <img src="/uploads/consulting.jpg" alt="Tư vấn mở quán" class="rounded-lg shadow-xl w-full">
            </div>
        </div>
    </div>
</section>

<!-- Process -->
<section class="py-16" style="background-color: <?= COLOR_SECONDARY ?>">
    <div class="container mx-auto px-4">
        <div class="text-center mb-12">
            <h2 class="text-3xl md:text-4xl font-bold mb-4" style="color: <?= COLOR_PRIMARY ?>">
                Quy trình làm việc
            </h2>
            <p class="text-xl text-gray-600">4 bước đơn giản để bắt đầu hợp tác</p>
        </div>

        <div class="grid md:grid-cols-4 gap-8">
            <div class="text-center">
                <div
                    class="w-20 h-20 bg-green-600 text-white rounded-full flex items-center justify-center mx-auto mb-4 text-2xl font-bold">
                    1
                </div>
                <h3 class="text-xl font-bold mb-2">Liên hệ</h3>
                <p class="text-gray-600">Gọi điện hoặc gửi yêu cầu qua website</p>
            </div>

            <div class="text-center">
                <div
                    class="w-20 h-20 bg-blue-600 text-white rounded-full flex items-center justify-center mx-auto mb-4 text-2xl font-bold">
                    2
                </div>
                <h3 class="text-xl font-bold mb-2">Tư vấn</h3>
                <p class="text-gray-600">Trao đổi nhu cầu và báo giá chi tiết</p>
            </div>

            <div class="text-center">
                <div
                    class="w-20 h-20 bg-yellow-600 text-white rounded-full flex items-center justify-center mx-auto mb-4 text-2xl font-bold">
                    3
                </div>
                <h3 class="text-xl font-bold mb-2">Thực hiện</h3>
                <p class="text-gray-600">Sản xuất theo yêu cầu và tiêu chuẩn</p>
            </div>

            <div class="text-center">
                <div
                    class="w-20 h-20 bg-purple-600 text-white rounded-full flex items-center justify-center mx-auto mb-4 text-2xl font-bold">
                    4
                </div>
                <h3 class="text-xl font-bold mb-2">Giao hàng</h3>
                <p class="text-gray-600">Vận chuyển nhanh chóng và bảo quản tốt</p>
            </div>
        </div>
    </div>
</section>

<!-- CTA -->
<section class="py-16 bg-gradient-to-r from-green-600 to-blue-600 text-white">
    <div class="container mx-auto px-4 text-center">
        <h2 class="text-3xl md:text-4xl font-bold mb-4">Bắt đầu hợp tác ngay hôm nay</h2>
        <p class="text-xl mb-8 max-w-2xl mx-auto">
            Liên hệ với chúng tôi để được tư vấn miễn phí và nhận ưu đãi đặc biệt
        </p>
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

<?php include 'includes/footer.php'; ?>