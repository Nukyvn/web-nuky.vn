<?php
require_once dirname(__DIR__) . '/config.php';
require_login();

// Get statistics
$stats = [];

// Total products
$stats['products'] = $pdo->query("SELECT COUNT(*) FROM products WHERE status = 1")->fetchColumn();

// Total orders
$stats['orders'] = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();

// Total revenue
$stats['revenue'] = $pdo->query("SELECT SUM(total) FROM orders WHERE payment_status = 'paid'")->fetchColumn() ?? 0;

// Pending orders
$stats['pending_orders'] = $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'pending'")->fetchColumn();

// New messages
$stats['new_messages'] = $pdo->query("SELECT COUNT(*) FROM chat_messages WHERE status = 'new'")->fetchColumn();

// Recent orders
$recent_orders = $pdo->query("SELECT * FROM orders ORDER BY created_at DESC LIMIT 10")->fetchAll();

// Recent messages
$recent_messages = $pdo->query("SELECT * FROM chat_messages ORDER BY created_at DESC LIMIT 5")->fetchAll();

$page_title = 'Dashboard - Admin';

include ROOT_PATH . '/admin/includes/header.php';
?>

<div class="mb-6">
    <h1 class="text-3xl font-bold text-gray-900">Dashboard</h1>
    <p class="text-gray-600 mt-1">Chào mừng bạn trở lại, <?= $_SESSION['user_name'] ?>!</p>
</div>

<!-- Statistics Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600 mb-1">Tổng sản phẩm</p>
                <p class="text-3xl font-bold text-gray-900"><?= number_format($stats['products']) ?></p>
            </div>
            <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                </svg>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600 mb-1">Tổng đơn hàng</p>
                <p class="text-3xl font-bold text-gray-900"><?= number_format($stats['orders']) ?></p>
            </div>
            <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600 mb-1">Doanh thu</p>
                <p class="text-3xl font-bold text-gray-900"><?= number_format($stats['revenue']) ?></p>
            </div>
            <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600 mb-1">Đơn chờ xử lý</p>
                <p class="text-3xl font-bold text-gray-900"><?= number_format($stats['pending_orders']) ?></p>
            </div>
            <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
        </div>
    </div>
</div>

<div class="grid lg:grid-cols-2 gap-8">
    <!-- Recent Orders -->
    <div class="bg-white rounded-lg shadow-md">
        <div class="p-6 border-b">
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-bold">Đơn hàng mới nhất</h2>
                <a href="/admin/orders.php" class="text-green-600 hover:text-green-700 text-sm font-semibold">
                    Xem tất cả →
                </a>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Mã đơn</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Khách hàng</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-600 uppercase">Tổng tiền</th>
                        <th class="px-6 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Trạng thái</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    <?php if (empty($recent_orders)): ?>
                    <tr>
                        <td colspan="4" class="px-6 py-4 text-center text-gray-500">Chưa có đơn hàng nào</td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($recent_orders as $order): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <a href="/admin/orders.php?id=<?= $order['id'] ?>"
                                class="text-green-600 hover:underline font-semibold">
                                <?= $order['order_code'] ?>
                            </a>
                        </td>
                        <td class="px-6 py-4">
                            <div class="font-medium"><?= $order['customer_name'] ?></div>
                            <div class="text-sm text-gray-500"><?= $order['customer_phone'] ?></div>
                        </td>
                        <td class="px-6 py-4 text-right font-semibold">
                            <?= format_price($order['total']) ?>
                        </td>
                        <td class="px-6 py-4 text-center">
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
                                ?>
                            <span
                                class="px-3 py-1 rounded-full text-xs font-semibold <?= $status_colors[$order['status']] ?>">
                                <?= $status_names[$order['status']] ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Recent Messages -->
    <div class="bg-white rounded-lg shadow-md">
        <div class="p-6 border-b">
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-bold">Tin nhắn mới</h2>
                <a href="/admin/messages.php" class="text-green-600 hover:text-green-700 text-sm font-semibold">
                    Xem tất cả →
                </a>
            </div>
        </div>

        <div class="divide-y">
            <?php if (empty($recent_messages)): ?>
            <div class="px-6 py-4 text-center text-gray-500">Chưa có tin nhắn nào</div>
            <?php else: ?>
            <?php foreach ($recent_messages as $msg): ?>
            <div class="p-6 hover:bg-gray-50">
                <div class="flex items-start justify-between mb-2">
                    <div>
                        <div class="font-semibold text-gray-900"><?= $msg['customer_name'] ?></div>
                        <div class="text-sm text-gray-500"><?= $msg['customer_phone'] ?></div>
                    </div>
                    <span class="text-xs text-gray-400"><?= time_ago($msg['created_at']) ?></span>
                </div>
                <p class="text-gray-600 text-sm line-clamp-2"><?= $msg['message'] ?></p>
                <?php if ($msg['status'] === 'new'): ?>
                <span
                    class="inline-block mt-2 px-2 py-1 bg-red-100 text-red-800 rounded text-xs font-semibold">Mới</span>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include ROOT_PATH . '/admin/includes/footer.php'; ?>