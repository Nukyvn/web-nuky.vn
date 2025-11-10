<?php
require_once '../../config.php';
require_login();

$id = intval($_GET['id'] ?? 0);

$stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
$stmt->execute([$id]);
$order = $stmt->fetch();

if (!$order) {
    echo '<p class="text-center text-red-600 p-6">Không tìm thấy đơn hàng</p>';
    exit;
}

// Get order items
$stmt = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ?");
$stmt->execute([$id]);
$items = $stmt->fetchAll();

$status_colors = [
    'pending' => 'bg-yellow-100 text-yellow-800',
    'confirmed' => 'bg-blue-100 text-blue-800',
    'processing' => 'bg-purple-100 text-purple-800',
    'shipping' => 'bg-indigo-100 text-indigo-800',
    'completed' => 'bg-green-100 text-green-800',
    'cancelled' => 'bg-red-100 text-red-800'
];
$status_names = [
    'pending' => 'Chờ xử lý',
    'confirmed' => 'Đã xác nhận',
    'processing' => 'Đang xử lý',
    'shipping' => 'Đang giao',
    'completed' => 'Hoàn thành',
    'cancelled' => 'Đã hủy'
];
?>

<div class="flex items-center justify-between mb-6">
    <h2 class="text-2xl font-bold">Chi tiết đơn hàng #<?= $order['order_code'] ?></h2>
    <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>
    </button>
</div>

<div class="grid md:grid-cols-2 gap-6 mb-6">
    <!-- Customer Info -->
    <div class="bg-gray-50 rounded-lg p-4">
        <h3 class="font-bold text-lg mb-3">Thông tin khách hàng</h3>
        <div class="space-y-2 text-sm">
            <div class="flex">
                <span class="text-gray-600 w-32">Họ tên:</span>
                <span class="font-semibold"><?= $order['customer_name'] ?></span>
            </div>
            <div class="flex">
                <span class="text-gray-600 w-32">Số điện thoại:</span>
                <span class="font-semibold">
                    <a href="tel:<?= $order['customer_phone'] ?>" class="text-green-600 hover:underline">
                        <?= $order['customer_phone'] ?>
                    </a>
                </span>
            </div>
            <?php if ($order['customer_email']): ?>
            <div class="flex">
                <span class="text-gray-600 w-32">Email:</span>
                <span class="font-semibold">
                    <a href="mailto:<?= $order['customer_email'] ?>" class="text-blue-600 hover:underline">
                        <?= $order['customer_email'] ?>
                    </a>
                </span>
            </div>
            <?php endif; ?>
            <div class="flex items-start">
                <span class="text-gray-600 w-32">Địa chỉ:</span>
                <span class="font-semibold"><?= $order['customer_address'] ?></span>
            </div>
            <?php if ($order['notes']): ?>
            <div class="flex items-start">
                <span class="text-gray-600 w-32">Ghi chú:</span>
                <span class="text-gray-700"><?= $order['notes'] ?></span>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Order Info -->
    <div class="bg-gray-50 rounded-lg p-4">
        <h3 class="font-bold text-lg mb-3">Thông tin đơn hàng</h3>
        <div class="space-y-2 text-sm">
            <div class="flex">
                <span class="text-gray-600 w-32">Mã đơn:</span>
                <span class="font-bold text-green-600"><?= $order['order_code'] ?></span>
            </div>
            <div class="flex">
                <span class="text-gray-600 w-32">Ngày đặt:</span>
                <span class="font-semibold"><?= format_date($order['created_at']) ?></span>
            </div>
            <div class="flex">
                <span class="text-gray-600 w-32">Trạng thái:</span>
                <span class="px-3 py-1 rounded-full text-xs font-semibold <?= $status_colors[$order['status']] ?>">
                    <?= $status_names[$order['status']] ?>
                </span>
            </div>
            <div class="flex">
                <span class="text-gray-600 w-32">Thanh toán:</span>
                <span class="font-semibold"><?= strtoupper($order['payment_method']) ?></span>
            </div>
            <div class="flex">
                <span class="text-gray-600 w-32">TT thanh toán:</span>
                <?php
                $payment_colors = [
                    'unpaid' => 'bg-gray-100 text-gray-800',
                    'paid' => 'bg-green-100 text-green-800',
                    'refunded' => 'bg-red-100 text-red-800'
                ];
                $payment_names = [
                    'unpaid' => 'Chưa thanh toán',
                    'paid' => 'Đã thanh toán',
                    'refunded' => 'Đã hoàn tiền'
                ];
                ?>
                <span
                    class="px-3 py-1 rounded-full text-xs font-semibold <?= $payment_colors[$order['payment_status']] ?>">
                    <?= $payment_names[$order['payment_status']] ?>
                </span>
            </div>
        </div>
    </div>
</div>

<!-- Order Items -->
<div class="mb-6">
    <h3 class="font-bold text-lg mb-3">Sản phẩm đã đặt</h3>
    <div class="bg-white border rounded-lg overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Sản phẩm</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Đơn giá</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">SL</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase">Thành tiền</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                <?php foreach ($items as $item): ?>
                <tr>
                    <td class="px-4 py-3">
                        <div class="flex items-center">
                            <?php if ($item['product_image']): ?>
                            <img src="<?= $item['product_image'] ?>" alt="<?= $item['product_name'] ?>"
                                class="w-12 h-12 object-cover rounded mr-3">
                            <?php endif; ?>
                            <span class="font-semibold"><?= $item['product_name'] ?></span>
                        </div>
                    </td>
                    <td class="px-4 py-3 text-center"><?= format_price($item['price']) ?></td>
                    <td class="px-4 py-3 text-center font-semibold"><?= $item['quantity'] ?></td>
                    <td class="px-4 py-3 text-right font-bold"><?= format_price($item['subtotal']) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Order Summary -->
<div class="bg-gray-50 rounded-lg p-4">
    <div class="space-y-2 max-w-md ml-auto">
        <div class="flex justify-between text-sm">
            <span class="text-gray-600">Tạm tính:</span>
            <span class="font-semibold"><?= format_price($order['subtotal']) ?></span>
        </div>
        <div class="flex justify-between text-sm">
            <span class="text-gray-600">Phí vận chuyển:</span>
            <span class="font-semibold"><?= format_price($order['shipping_fee']) ?></span>
        </div>
        <?php if ($order['discount'] > 0): ?>
        <div class="flex justify-between text-sm">
            <span class="text-gray-600">Giảm giá:</span>
            <span class="font-semibold text-red-600">-<?= format_price($order['discount']) ?></span>
        </div>
        <?php endif; ?>
        <div class="flex justify-between text-lg font-bold pt-2 border-t">
            <span>Tổng cộng:</span>
            <span class="text-green-600"><?= format_price($order['total']) ?></span>
        </div>
    </div>
</div>

<div class="mt-6 flex justify-end gap-3">
    <button onclick="closeModal()" class="px-6 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition">
        Đóng
    </button>
    <button onclick="editOrder(<?= $id ?>)"
        class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
        Cập nhật trạng thái
    </button>
</div>