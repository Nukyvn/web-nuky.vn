<?php
require_once '../../config.php';
require_login();

$id = intval($_GET['id'] ?? 0);

$stmt = $pdo->prepare("SELECT * FROM customers WHERE id = ?");
$stmt->execute([$id]);
$customer = $stmt->fetch();

if (!$customer) {
    echo '<p class="text-center text-red-600 p-6">Không tìm thấy khách hàng</p>';
    exit;
}

// Get customer orders
$stmt = $pdo->prepare("SELECT * FROM orders WHERE customer_id = ? ORDER BY created_at DESC LIMIT 10");
$stmt->execute([$id]);
$orders = $stmt->fetchAll();
?>

<div class="flex items-center justify-between mb-6">
    <h2 class="text-2xl font-bold">Thông tin khách hàng</h2>
    <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>
    </button>
</div>

<!-- Customer Info -->
<div class="bg-gradient-to-r from-green-50 to-blue-50 rounded-lg p-6 mb-6">
    <div class="flex items-start">
        <div class="w-20 h-20 bg-green-600 rounded-full flex items-center justify-center mr-6 flex-shrink-0">
            <span class="text-white font-bold text-3xl">
                <?= mb_substr($customer['full_name'], 0, 1) ?>
            </span>
        </div>

        <div class="flex-1">
            <h3 class="text-2xl font-bold text-gray-900 mb-2"><?= $customer['full_name'] ?></h3>

            <div class="grid md:grid-cols-2 gap-4 text-sm">
                <div class="flex items-center">
                    <svg class="w-5 h-5 text-gray-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                    </svg>
                    <a href="tel:<?= $customer['phone'] ?>" class="text-blue-600 hover:underline font-semibold">
                        <?= $customer['phone'] ?>
                    </a>
                </div>

                <?php if ($customer['email']): ?>
                <div class="flex items-center">
                    <svg class="w-5 h-5 text-gray-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                    <a href="mailto:<?= $customer['email'] ?>" class="text-blue-600 hover:underline">
                        <?= $customer['email'] ?>
                    </a>
                </div>
                <?php endif; ?>

                <div class="flex items-start md:col-span-2">
                    <svg class="w-5 h-5 text-gray-500 mr-2 mt-0.5" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <span class="text-gray-700"><?= $customer['address'] ?></span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Stats -->
<div class="grid grid-cols-3 gap-4 mb-6">
    <div class="bg-blue-50 rounded-lg p-4 text-center">
        <p class="text-sm text-gray-600 mb-1">Tổng đơn hàng</p>
        <p class="text-3xl font-bold text-blue-600"><?= $customer['total_orders'] ?></p>
    </div>

    <div class="bg-green-50 rounded-lg p-4 text-center">
        <p class="text-sm text-gray-600 mb-1">Tổng chi tiêu</p>
        <p class="text-2xl font-bold text-green-600"><?= format_price($customer['total_spent']) ?></p>
    </div>

    <div class="bg-purple-50 rounded-lg p-4 text-center">
        <p class="text-sm text-gray-600 mb-1">Khách hàng từ</p>
        <p class="text-lg font-bold text-purple-600"><?= date('d/m/Y', strtotime($customer['created_at'])) ?></p>
    </div>
</div>

<!-- Order History -->
<div>
    <h3 class="font-bold text-lg mb-3">Lịch sử đơn hàng (10 đơn gần nhất)</h3>

    <?php if (empty($orders)): ?>
    <div class="bg-gray-50 rounded-lg p-8 text-center text-gray-500">
        <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
        </svg>
        <p>Chưa có đơn hàng nào</p>
    </div>
    <?php else: ?>
    <div class="bg-white border rounded-lg overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Mã đơn</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Ngày đặt</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase">Tổng tiền</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Trạng thái</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                <?php
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
                foreach ($orders as $order):
                ?>
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3">
                        <a href="/admin/orders.php?id=<?= $order['id'] ?>"
                            class="text-green-600 hover:underline font-semibold">
                            <?= $order['order_code'] ?>
                        </a>
                    </td>
                    <td class="px-4 py-3 text-gray-600">
                        <?= format_date($order['created_at']) ?>
                    </td>
                    <td class="px-4 py-3 text-right font-bold">
                        <?= format_price($order['total']) ?>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <span
                            class="px-2 py-1 rounded-full text-xs font-semibold <?= $status_colors[$order['status']] ?>">
                            <?= $status_names[$order['status']] ?>
                        </span>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<div class="mt-6 flex justify-end gap-3">
    <button onclick="closeModal()" class="px-6 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition">
        Đóng
    </button>
    <a href="/admin/orders.php?customer_id=<?= $customer['id'] ?>"
        class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
        Xem tất cả đơn hàng
    </a>
</div>