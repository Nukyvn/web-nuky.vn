<?php
// Đảm bảo include các file cần thiết
require_once 'config.php';
require_once 'functions.php'; 

// --- Logic xử lý đơn hàng ---

// Lấy mã đơn hàng từ query string (URL)
$order_code = clean_input($_GET['order'] ?? '');
$order = null;

if ($order_code) {
    // Lấy chi tiết đơn hàng từ database
    $order = get_order_by_code($order_code);
    
    // Tùy chọn: Xóa session sau khi đã hiển thị để tránh tải lại trang thành công nhiều lần
    if (isset($_SESSION['order_success']) && $_SESSION['order_success'] === $order_code) {
        unset($_SESSION['order_success']);
    }
}

// Nếu không tìm thấy đơn hàng hoặc không có mã, chuyển hướng hoặc hiển thị lỗi
if (!$order) {
    header('Location: /'); 
    exit;
}

$page_title = 'Đặt hàng thành công - ' . SITE_NAME;

include 'includes/header.php';
?>

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
                        <span class="text-gray-500">Đặt hàng thành công</span>
                    </div>
                </li>
            </ol>
        </nav>
    </div>
</div>

<section class="py-16 bg-gray-50">
    <div class="container mx-auto px-4">
        <div class="max-w-3xl mx-auto bg-white rounded-xl shadow-xl p-8 md:p-12 text-center">
            <svg class="w-20 h-20 text-green-500 mx-auto mb-4 animate-bounce" fill="none" stroke="currentColor"
                viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <h1 class="text-3xl font-extrabold text-gray-800 mb-3">ĐẶT HÀNG THÀNH CÔNG!</h1>
            <p class="text-xl text-gray-600 mb-6">Cảm ơn bạn đã tin tưởng và đặt hàng tại <?= SITE_NAME ?>.</p>

            <div class="p-6 border-2 border-green-300 bg-green-50 rounded-lg inline-block mb-8">
                <p class="text-2xl font-bold text-green-700">Mã đơn hàng của bạn:</p>
                <p class="text-4xl font-extrabold text-green-800 mt-1"><?= htmlspecialchars($order['order_code']) ?></p>
            </div>

            <div class="text-left border-t pt-6 mt-6">
                <h2 class="text-2xl font-bold mb-4">Thông tin chi tiết đơn hàng</h2>
                <div class="space-y-3 text-gray-700">
                    <p><strong>Ngày đặt hàng:</strong> <?= date('H:i:s d/m/Y', strtotime($order['created_at'])) ?></p>
                    <p><strong>Người nhận:</strong> <?= htmlspecialchars($order['customer_name']) ?></p>
                    <p><strong>Điện thoại:</strong> <?= htmlspecialchars($order['customer_phone']) ?></p>
                    <p><strong>Email:</strong> <?= htmlspecialchars($order['customer_email']) ?></p>
                    <p><strong>Địa chỉ giao hàng:</strong> <?= htmlspecialchars($order['customer_address']) ?></p>
                    <p><strong>Phương thức thanh toán:</strong> <?= get_payment_method_name($order['payment_method']) ?>
                    </p>
                </div>

                <?php if (!empty($order['items'])): ?>
                <h3 class="text-xl font-semibold mt-6 mb-3">Sản phẩm đã đặt:</h3>
                <ul class="space-y-2 border p-4 rounded-lg bg-gray-50">
                    <?php foreach ($order['items'] as $item): ?>
                    <li class="flex justify-between text-sm">
                        <span><?= htmlspecialchars($item['product_name']) ?> x <?= $item['quantity'] ?></span>
                        <span class="font-medium"><?= format_price($item['subtotal']) ?></span>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <?php endif; ?>

                <div class="mt-5 pt-5 border-t">
                    <div class="flex justify-between font-bold text-lg text-gray-800">
                        <span>Tổng thanh toán:</span>
                        <span class="text-red-600"><?= format_price($order['total']) ?></span>
                    </div>
                </div>

                <p class="mt-6 text-sm text-gray-500">
                    Một email xác nhận đơn hàng đã được gửi tới địa chỉ email của bạn
                    (<?= htmlspecialchars($order['customer_email']) ?>). Vui lòng kiểm tra hộp thư.
                </p>
            </div>

            <a href="/"
                class="mt-8 inline-block bg-green-600 text-white py-3 px-8 rounded-full font-semibold hover:bg-green-700 transition">
                Tiếp tục mua sắm
            </a>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>