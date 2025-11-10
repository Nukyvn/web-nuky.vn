<?php
require_once 'config.php';

$cart = get_cart();

// Redirect if cart is empty
if (empty($cart)) {
    header('Location: /gio-hang');
    exit;
}

$cart_total = get_cart_total();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'name' => clean_input($_POST['name'] ?? ''),
        'phone' => clean_input($_POST['phone'] ?? ''),
        'email' => clean_input($_POST['email'] ?? ''),
        'address' => clean_input($_POST['address'] ?? ''),
        'notes' => clean_input($_POST['notes'] ?? ''),
        'payment_method' => clean_input($_POST['payment_method'] ?? 'COD'),
        'shipping_fee' => 0
    ];
    
    $result = create_order($data);
    
    if ($result['success']) {
        // Send email
        send_order_email($result['order_id']);
        
        // Redirect to success page
        $_SESSION['order_success'] = $result['order_code'];
        header('Location: /thanh-toan/thanh-cong?order=' . $result['order_code']);
        exit;
    } else {
        $error_message = $result['message'] ?? 'Có lỗi xảy ra. Vui lòng thử lại.';
    }
}

$page_title = 'Thanh toán - ' . SITE_NAME;

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
                        <a href="/gio-hang" class="text-gray-700 hover:text-green-600">Giỏ hàng</a>
                    </div>
                </li>
                <li>
                    <div class="flex items-center">
                        <svg class="w-3 h-3 text-gray-400 mx-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                                clip-rule="evenodd" />
                        </svg>
                        <span class="text-gray-500">Thanh toán</span>
                    </div>
                </li>
            </ol>
        </nav>
    </div>
</div>

<!-- Checkout Section -->
<section class="py-12 bg-gray-50">
    <div class="container mx-auto px-4">
        <h1 class="text-3xl font-bold mb-8">Thanh toán</h1>

        <?php if (isset($error_message)): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
            <?= $error_message ?>
        </div>
        <?php endif; ?>

        <form method="POST" class="grid lg:grid-cols-3 gap-8" x-data="checkoutForm()">
            <!-- Customer Information -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Contact Information -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-bold mb-6">Thông tin liên hệ</h2>

                    <div class="grid md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold mb-2">Họ và tên <span
                                    class="text-red-500">*</span></label>
                            <input type="text" name="name" required
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                placeholder="Nguyễn Văn A">
                        </div>

                        <div>
                            <label class="block text-sm font-semibold mb-2">Số điện thoại <span
                                    class="text-red-500">*</span></label>
                            <input type="tel" name="phone" required pattern="[0-9]{10,11}"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                placeholder="0987654321">
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-semibold mb-2">Email</label>
                            <input type="email" name="email"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                placeholder="email@example.com">
                        </div>
                    </div>
                </div>

                <!-- Shipping Address -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-bold mb-6">Địa chỉ giao hàng</h2>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-semibold mb-2">Địa chỉ <span
                                    class="text-red-500">*</span></label>
                            <textarea name="address" required rows="3"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent resize-none"
                                placeholder="Số nhà, tên đường, phường/xã, quận/huyện, tỉnh/thành phố"></textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold mb-2">Ghi chú đơn hàng (tùy chọn)</label>
                            <textarea name="notes" rows="3"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent resize-none"
                                placeholder="Ghi chú thêm về đơn hàng, ví dụ: giao hàng giờ hành chính..."></textarea>
                        </div>
                    </div>
                </div>

                <!-- Payment Method -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-bold mb-6">Phương thức thanh toán</h2>

                    <div class="space-y-3">
                        <label
                            class="flex items-center p-4 border-2 border-gray-300 rounded-lg cursor-pointer hover:border-green-500 transition">
                            <input type="radio" name="payment_method" value="COD" checked
                                class="w-5 h-5 text-green-600">
                            <div class="ml-3 flex-1">
                                <div class="font-semibold">Thanh toán khi nhận hàng (COD)</div>
                                <div class="text-sm text-gray-500">Thanh toán bằng tiền mặt khi nhận hàng</div>
                            </div>
                        </label>

                        <label
                            class="flex items-center p-4 border-2 border-gray-300 rounded-lg cursor-pointer hover:border-green-500 transition">
                            <input type="radio" name="payment_method" value="bank_transfer"
                                class="w-5 h-5 text-green-600">
                            <div class="ml-3 flex-1">
                                <div class="font-semibold">Chuyển khoản ngân hàng</div>
                                <div class="text-sm text-gray-500">Chuyển khoản qua tài khoản ngân hàng</div>
                            </div>
                        </label>

                        <label
                            class="flex items-center p-4 border-2 border-gray-300 rounded-lg cursor-pointer hover:border-green-500 transition">
                            <input type="radio" name="payment_method" value="momo" class="w-5 h-5 text-green-600">
                            <div class="ml-3 flex-1">
                                <div class="font-semibold">Ví Momo</div>
                                <div class="text-sm text-gray-500">Thanh toán qua ví điện tử Momo</div>
                            </div>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Order Summary -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-lg shadow-md p-6 sticky top-24">
                    <h2 class="text-xl font-bold mb-6">Đơn hàng của bạn</h2>

                    <!-- Cart Items -->
                    <div class="space-y-4 mb-6 max-h-64 overflow-y-auto">
                        <?php foreach ($cart as $item): ?>
                        <div class="flex gap-3">
                            <img src="<?= $item['image'] ?>" alt="<?= $item['name'] ?>"
                                class="w-16 h-16 object-cover rounded-lg">
                            <div class="flex-1">
                                <h3 class="font-semibold text-sm line-clamp-2"><?= $item['name'] ?></h3>
                                <div class="flex justify-between items-center mt-1">
                                    <span class="text-sm text-gray-500">SL: <?= $item['quantity'] ?></span>
                                    <span class="font-semibold" style="color: <?= COLOR_PRIMARY ?>">
                                        <?= format_price($item['price'] * $item['quantity']) ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Totals -->
                    <div class="border-t pt-4 space-y-3">
                        <div class="flex justify-between text-gray-600">
                            <span>Tạm tính:</span>
                            <span class="font-semibold"><?= format_price($cart_total) ?></span>
                        </div>
                        <div class="flex justify-between text-gray-600">
                            <span>Phí vận chuyển:</span>
                            <span class="font-semibold">Miễn phí</span>
                        </div>
                        <div class="border-t pt-3 flex justify-between">
                            <span class="text-lg font-bold">Tổng cộng:</span>
                            <span class="text-2xl font-bold" style="color: <?= COLOR_PRIMARY ?>">
                                <?= format_price($cart_total) ?>
                            </span>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit"
                        class="w-full mt-6 bg-green-600 text-white py-4 rounded-lg font-bold text-lg hover:bg-green-700 transition">
                        Đặt hàng
                    </button>

                    <div class="mt-4 text-center">
                        <a href="/gio-hang" class="text-green-600 hover:underline text-sm">
                            ← Quay lại giỏ hàng
                        </a>
                    </div>

                    <!-- Security Info -->
                    <div class="mt-6 pt-6 border-t">
                        <div class="flex items-center text-sm text-gray-600 mb-2">
                            <svg class="w-5 h-5 text-green-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z"
                                    clip-rule="evenodd" />
                            </svg>
                            <span>Thanh toán an toàn & bảo mật</span>
                        </div>
                        <div class="flex items-center text-sm text-gray-600">
                            <svg class="w-5 h-5 text-green-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z" />
                                <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z" />
                            </svg>
                            <span>Xác nhận đơn hàng qua email</span>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</section>

<script>
function checkoutForm() {
    return {
        // Add any checkout-specific logic here
    }
}
</script>

<?php include 'includes/footer.php'; ?>