<?php
require_once '../config.php';
require_login();

// Handle status update
if (isset($_POST['update_status'])) {
    $order_id = intval($_POST['order_id']);
    $status = clean_input($_POST['status']);
    $payment_status = clean_input($_POST['payment_status']);
    
    $pdo->prepare("UPDATE orders SET status = ?, payment_status = ? WHERE id = ?")
        ->execute([$status, $payment_status, $order_id]);
    
    header('Location: /admin/orders.php?msg=updated');
    exit;
}

// Get filters
$search = $_GET['search'] ?? '';
$status_filter = $_GET['status'] ?? '';
$page = max(1, intval($_GET['page'] ?? 1));

// Build query
$where = ['1=1'];
$params = [];

if ($search) {
    $where[] = "(order_code LIKE ? OR customer_name LIKE ? OR customer_phone LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($status_filter) {
    $where[] = "status = ?";
    $params[] = $status_filter;
}

$where_sql = implode(' AND ', $where);

// Get total
$stmt = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE $where_sql");
$stmt->execute($params);
$total = $stmt->fetchColumn();

// Pagination
$pagination = paginate($total, $page);

// Get orders
$sql = "SELECT * FROM orders WHERE $where_sql ORDER BY created_at DESC LIMIT {$pagination['per_page']} OFFSET {$pagination['offset']}";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$orders = $stmt->fetchAll();

$page_title = 'Quản lý đơn hàng';

include 'includes/header.php';
?>

<div class="mb-6">
    <h1 class="text-3xl font-bold text-gray-900">Đơn hàng</h1>
</div>

<?php if (isset($_GET['msg'])): ?>
<div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
    Đã cập nhật đơn hàng thành công!
</div>
<?php endif; ?>

<!-- Filters -->
<div class="bg-white rounded-lg shadow-md p-6 mb-6">
    <form method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
            <input type="text" name="search" value="<?= htmlspecialchars($search) ?>"
                placeholder="Tìm mã đơn, tên KH, SĐT..."
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
        </div>

        <div>
            <select name="status"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                <option value="">Tất cả trạng thái</option>
                <option value="pending" <?= $status_filter === 'pending' ? 'selected' : '' ?>>Chờ xử lý</option>
                <option value="confirmed" <?= $status_filter === 'confirmed' ? 'selected' : '' ?>>Đã xác nhận</option>
                <option value="processing" <?= $status_filter === 'processing' ? 'selected' : '' ?>>Đang xử lý</option>
                <option value="shipping" <?= $status_filter === 'shipping' ? 'selected' : '' ?>>Đang giao</option>
                <option value="completed" <?= $status_filter === 'completed' ? 'selected' : '' ?>>Hoàn thành</option>
                <option value="cancelled" <?= $status_filter === 'cancelled' ? 'selected' : '' ?>>Đã hủy</option>
            </select>
        </div>

        <div class="flex gap-2">
            <button type="submit"
                class="flex-1 bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition">
                Lọc
            </button>
            <a href="/admin/orders.php" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                Reset
            </a>
        </div>
    </form>
</div>

<!-- Orders Table -->
<div class="bg-white rounded-lg shadow-md overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Mã đơn</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Khách hàng</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Ngày đặt</th>
                    <th class="px-6 py-3 text-right text-xs font-semibold text-gray-600 uppercase">Tổng tiền</th>
                    <th class="px-6 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Thanh toán</th>
                    <th class="px-6 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Trạng thái</th>
                    <th class="px-6 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Thao tác</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                <?php if (empty($orders)): ?>
                <tr>
                    <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                        Không tìm thấy đơn hàng nào
                    </td>
                </tr>
                <?php else: ?>
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

                <?php foreach ($orders as $order): ?>
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4">
                        <div class="font-bold text-green-600"><?= $order['order_code'] ?></div>
                        <div class="text-xs text-gray-500"><?= $order['payment_method'] ?></div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="font-semibold"><?= $order['customer_name'] ?></div>
                        <div class="text-sm text-gray-500"><?= $order['customer_phone'] ?></div>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-600">
                        <?= format_date($order['created_at']) ?>
                    </td>
                    <td class="px-6 py-4 text-right font-bold text-gray-900">
                        <?= format_price($order['total']) ?>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <span
                            class="px-3 py-1 rounded-full text-xs font-semibold <?= $payment_colors[$order['payment_status']] ?>">
                            <?= $payment_names[$order['payment_status']] ?>
                        </span>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <span
                            class="px-3 py-1 rounded-full text-xs font-semibold <?= $status_colors[$order['status']] ?>">
                            <?= $status_names[$order['status']] ?>
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center justify-center space-x-2">
                            <button onclick="viewOrder(<?= $order['id'] ?>)"
                                class="text-blue-600 hover:text-blue-800 transition" title="Xem chi tiết">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </button>

                            <button onclick="editOrder(<?= $order['id'] ?>)"
                                class="text-green-600 hover:text-green-800 transition" title="Cập nhật trạng thái">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                            </button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php if ($pagination['total_pages'] > 1): ?>
    <div class="bg-gray-50 px-6 py-4 border-t">
        <div class="flex items-center justify-between">
            <div class="text-sm text-gray-600">
                Hiển thị <?= $pagination['offset'] + 1 ?> -
                <?= min($pagination['offset'] + $pagination['per_page'], $total) ?> trong tổng số <?= $total ?> đơn hàng
            </div>

            <nav class="flex space-x-2">
                <?php if ($pagination['current_page'] > 1): ?>
                <a href="?page=<?= $pagination['current_page'] - 1 ?><?= $search ? '&search='.urlencode($search) : '' ?><?= $status_filter ? '&status='.$status_filter : '' ?>"
                    class="px-3 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">← Trước</a>
                <?php endif; ?>

                <?php for ($i = max(1, $pagination['current_page'] - 2); $i <= min($pagination['total_pages'], $pagination['current_page'] + 2); $i++): ?>
                <?php if ($i === $pagination['current_page']): ?>
                <span class="px-3 py-2 bg-green-600 text-white rounded-lg"><?= $i ?></span>
                <?php else: ?>
                <a href="?page=<?= $i ?><?= $search ? '&search='.urlencode($search) : '' ?><?= $status_filter ? '&status='.$status_filter : '' ?>"
                    class="px-3 py-2 border border-gray-300 rounded-lg hover:bg-gray-50"><?= $i ?></a>
                <?php endif; ?>
                <?php endfor; ?>

                <?php if ($pagination['current_page'] < $pagination['total_pages']): ?>
                <a href="?page=<?= $pagination['current_page'] + 1 ?><?= $search ? '&search='.urlencode($search) : '' ?><?= $status_filter ? '&status='.$status_filter : '' ?>"
                    class="px-3 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">Sau →</a>
                <?php endif; ?>
            </nav>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Order Detail Modal -->
<div id="orderModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-lg max-w-4xl w-full max-h-[90vh] overflow-y-auto">
        <div id="orderModalContent"></div>
    </div>
</div>

<script>
function viewOrder(orderId) {
    fetch(`/admin/ajax/order-detail.php?id=${orderId}`)
        .then(res => res.text())
        .then(html => {
            document.getElementById('orderModalContent').innerHTML = html;
            document.getElementById('orderModal').classList.remove('hidden');
        });
}

function editOrder(orderId) {
    fetch(`/admin/ajax/order-edit.php?id=${orderId}`)
        .then(res => res.text())
        .then(html => {
            document.getElementById('orderModalContent').innerHTML = html;
            document.getElementById('orderModal').classList.remove('hidden');
        });
}

function closeModal() {
    document.getElementById('orderModal').classList.add('hidden');
}

// Close modal on click outside
document.getElementById('orderModal')?.addEventListener('click', function(e) {
    if (e.target === this) closeModal();
});
</script>

<?php include 'includes/footer.php'; ?>