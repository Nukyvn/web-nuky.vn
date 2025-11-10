<?php
require_once '../config.php';
require_login();

// Handle export to Excel
if (isset($_GET['export'])) {
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment;filename="khach_hang_' . date('Y-m-d') . '.xls"');
    header('Cache-Control: max-age=0');
    
    $customers = $pdo->query("SELECT * FROM customers ORDER BY created_at DESC")->fetchAll();
    
    echo '<table border="1">';
    echo '<thead><tr>';
    echo '<th>ID</th><th>Họ tên</th><th>Số điện thoại</th><th>Email</th><th>Địa chỉ</th>';
    echo '<th>Tổng đơn</th><th>Tổng chi tiêu</th><th>Ngày đăng ký</th>';
    echo '</tr></thead><tbody>';
    
    foreach ($customers as $c) {
        echo '<tr>';
        echo '<td>' . $c['id'] . '</td>';
        echo '<td>' . $c['full_name'] . '</td>';
        echo '<td>' . $c['phone'] . '</td>';
        echo '<td>' . $c['email'] . '</td>';
        echo '<td>' . $c['address'] . '</td>';
        echo '<td>' . $c['total_orders'] . '</td>';
        echo '<td>' . number_format($c['total_spent']) . '</td>';
        echo '<td>' . $c['created_at'] . '</td>';
        echo '</tr>';
    }
    
    echo '</tbody></table>';
    exit;
}

// Get filters
$search = $_GET['search'] ?? '';
$page = max(1, intval($_GET['page'] ?? 1));

// Build query
$where = ['1=1'];
$params = [];

if ($search) {
    $where[] = "(full_name LIKE ? OR phone LIKE ? OR email LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$where_sql = implode(' AND ', $where);

// Get total
$stmt = $pdo->prepare("SELECT COUNT(*) FROM customers WHERE $where_sql");
$stmt->execute($params);
$total = $stmt->fetchColumn();

// Pagination
$pagination = paginate($total, $page, 20);

// Get customers
$sql = "SELECT * FROM customers WHERE $where_sql ORDER BY created_at DESC LIMIT {$pagination['per_page']} OFFSET {$pagination['offset']}";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$customers = $stmt->fetchAll();

// Get stats
$stats = [
    'total' => $pdo->query("SELECT COUNT(*) FROM customers")->fetchColumn(),
    'total_orders' => $pdo->query("SELECT SUM(total_orders) FROM customers")->fetchColumn(),
    'total_revenue' => $pdo->query("SELECT SUM(total_spent) FROM customers")->fetchColumn()
];

$page_title = 'Quản lý khách hàng';

include 'includes/header.php';
?>

<div class="flex justify-between items-center mb-6">
    <h1 class="text-3xl font-bold text-gray-900">Khách hàng</h1>
    <a href="?export=1"
        class="bg-green-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-green-700 transition flex items-center">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
        </svg>
        Xuất Excel
    </a>
</div>

<!-- Stats -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600 mb-1">Tổng khách hàng</p>
                <p class="text-3xl font-bold text-gray-900"><?= number_format($stats['total']) ?></p>
            </div>
            <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600 mb-1">Tổng đơn hàng</p>
                <p class="text-3xl font-bold text-gray-900"><?= number_format($stats['total_orders'] ?? 0) ?></p>
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
                <p class="text-sm text-gray-600 mb-1">Tổng doanh thu</p>
                <p class="text-3xl font-bold text-gray-900"><?= number_format($stats['total_revenue'] ?? 0) ?></p>
            </div>
            <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
        </div>
    </div>
</div>

<!-- Search -->
<div class="bg-white rounded-lg shadow-md p-6 mb-6">
    <form method="GET" class="flex gap-4">
        <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Tìm kiếm khách hàng..."
            class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
        <button type="submit" class="bg-green-600 text-white px-6 py-2 rounded-lg hover:bg-green-700 transition">
            Tìm kiếm
        </button>
        <?php if ($search): ?>
        <a href="/admin/customers.php" class="px-6 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition">
            Reset
        </a>
        <?php endif; ?>
    </form>
</div>

<!-- Customers Table -->
<div class="bg-white rounded-lg shadow-md overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Khách hàng</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Liên hệ</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Địa chỉ</th>
                    <th class="px-6 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Đơn hàng</th>
                    <th class="px-6 py-3 text-right text-xs font-semibold text-gray-600 uppercase">Tổng chi tiêu</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Ngày đăng ký</th>
                    <th class="px-6 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Thao tác</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                <?php if (empty($customers)): ?>
                <tr>
                    <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                        Không tìm thấy khách hàng nào
                    </td>
                </tr>
                <?php else: ?>
                <?php foreach ($customers as $customer): ?>
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4">
                        <div class="flex items-center">
                            <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center mr-3">
                                <span class="text-green-600 font-bold">
                                    <?= mb_substr($customer['full_name'], 0, 1) ?>
                                </span>
                            </div>
                            <div>
                                <div class="font-semibold text-gray-900"><?= $customer['full_name'] ?></div>
                                <div class="text-sm text-gray-500">ID: #<?= $customer['id'] ?></div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-sm">
                            <div class="text-gray-900"><?= $customer['phone'] ?></div>
                            <?php if ($customer['email']): ?>
                            <div class="text-gray-500"><?= $customer['email'] ?></div>
                            <?php endif; ?>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-sm text-gray-600 max-w-xs truncate">
                            <?= $customer['address'] ?>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <span class="font-semibold text-gray-900"><?= $customer['total_orders'] ?></span>
                    </td>
                    <td class="px-6 py-4 text-right">
                        <span class="font-bold text-gray-900"><?= format_price($customer['total_spent']) ?></span>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-600">
                        <?= format_date($customer['created_at']) ?>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center justify-center space-x-2">
                            <button onclick="viewCustomer(<?= $customer['id'] ?>)"
                                class="text-blue-600 hover:text-blue-800 transition" title="Xem chi tiết">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </button>

                            <a href="tel:<?= $customer['phone'] ?>"
                                class="text-green-600 hover:text-green-800 transition" title="Gọi điện">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                </svg>
                            </a>

                            <?php if ($customer['email']): ?>
                            <a href="mailto:<?= $customer['email'] ?>"
                                class="text-purple-600 hover:text-purple-800 transition" title="Gửi email">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                            </a>
                            <?php endif; ?>
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
                <?= min($pagination['offset'] + $pagination['per_page'], $total) ?> trong tổng số <?= $total ?> khách
                hàng
            </div>

            <nav class="flex space-x-2">
                <?php if ($pagination['current_page'] > 1): ?>
                <a href="?page=<?= $pagination['current_page'] - 1 ?><?= $search ? '&search='.urlencode($search) : '' ?>"
                    class="px-3 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">← Trước</a>
                <?php endif; ?>

                <?php for ($i = max(1, $pagination['current_page'] - 2); $i <= min($pagination['total_pages'], $pagination['current_page'] + 2); $i++): ?>
                <?php if ($i === $pagination['current_page']): ?>
                <span class="px-3 py-2 bg-green-600 text-white rounded-lg"><?= $i ?></span>
                <?php else: ?>
                <a href="?page=<?= $i ?><?= $search ? '&search='.urlencode($search) : '' ?>"
                    class="px-3 py-2 border border-gray-300 rounded-lg hover:bg-gray-50"><?= $i ?></a>
                <?php endif; ?>
                <?php endfor; ?>

                <?php if ($pagination['current_page'] < $pagination['total_pages']): ?>
                <a href="?page=<?= $pagination['current_page'] + 1 ?><?= $search ? '&search='.urlencode($search) : '' ?>"
                    class="px-3 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">Sau →</a>
                <?php endif; ?>
            </nav>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Customer Detail Modal -->
<div id="customerModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-lg max-w-4xl w-full max-h-[90vh] overflow-y-auto">
        <div id="customerModalContent" class="p-6">
            <div class="flex justify-center py-12">
                <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-green-600"></div>
            </div>
        </div>
    </div>
</div>

<script>
function viewCustomer(customerId) {
    document.getElementById('customerModal').classList.remove('hidden');

    fetch(`/admin/ajax/customer-detail.php?id=${customerId}`)
        .then(res => res.text())
        .then(html => {
            document.getElementById('customerModalContent').innerHTML = html;
        })
        .catch(err => {
            document.getElementById('customerModalContent').innerHTML =
                '<p class="text-center text-red-600">Có lỗi xảy ra</p>';
        });
}

function closeModal() {
    document.getElementById('customerModal').classList.add('hidden');
}

document.getElementById('customerModal')?.addEventListener('click', function(e) {
    if (e.target === this) closeModal();
});
</script>

<?php include 'includes/footer.php'; ?>