<?php
require_once '../config.php';
require_login();

// Handle delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $pdo->prepare("DELETE FROM products WHERE id = ?")->execute([$id]);
    header('Location: /admin/products.php?msg=deleted');
    exit;
}

// Get filters
$search = $_GET['search'] ?? '';
$category = $_GET['category'] ?? '';
$status = $_GET['status'] ?? '';
$page = max(1, intval($_GET['page'] ?? 1));

// Build query
$where = ['1=1'];
$params = [];

if ($search) {
    $where[] = "(name LIKE ? OR sku LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($category) {
    $where[] = "category_id = ?";
    $params[] = $category;
}

if ($status !== '') {
    $where[] = "status = ?";
    $params[] = $status;
}

$where_sql = implode(' AND ', $where);

// Get total
$count_sql = "SELECT COUNT(*) FROM products WHERE $where_sql";
$stmt = $pdo->prepare($count_sql);
$stmt->execute($params);
$total = $stmt->fetchColumn();

// Pagination
$pagination = paginate($total, $page);

// Get products
$sql = "SELECT p.*, c.name as category_name 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        WHERE $where_sql 
        ORDER BY p.created_at DESC 
        LIMIT {$pagination['per_page']} OFFSET {$pagination['offset']}";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

// Get categories for filter
$categories = $pdo->query("SELECT * FROM categories WHERE status = 1 ORDER BY name")->fetchAll();

$page_title = 'Quản lý sản phẩm';

include 'includes/header.php';
?>

<div class="flex justify-between items-center mb-6">
    <h1 class="text-3xl font-bold text-gray-900">Sản phẩm</h1>
    <a href="/admin/product-edit.php"
        class="bg-green-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-green-700 transition flex items-center">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        Thêm sản phẩm
    </a>
</div>

<?php if (isset($_GET['msg'])): ?>
<div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
    <?php
    $messages = [
        'saved' => 'Đã lưu sản phẩm thành công!',
        'deleted' => 'Đã xóa sản phẩm thành công!'
    ];
    echo $messages[$_GET['msg']] ?? '';
    ?>
</div>
<?php endif; ?>

<!-- Filters -->
<div class="bg-white rounded-lg shadow-md p-6 mb-6">
    <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
            <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Tìm kiếm sản phẩm..."
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
        </div>

        <div>
            <select name="category"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                <option value="">Tất cả danh mục</option>
                <?php foreach ($categories as $cat): ?>
                <option value="<?= $cat['id'] ?>" <?= $category == $cat['id'] ? 'selected' : '' ?>>
                    <?= $cat['name'] ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <select name="status"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                <option value="">Tất cả trạng thái</option>
                <option value="1" <?= $status === '1' ? 'selected' : '' ?>>Đang bán</option>
                <option value="0" <?= $status === '0' ? 'selected' : '' ?>>Tạm ẩn</option>
            </select>
        </div>

        <div class="flex gap-2">
            <button type="submit"
                class="flex-1 bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition">
                Lọc
            </button>
            <a href="/admin/products.php"
                class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                Reset
            </a>
        </div>
    </form>
</div>

<!-- Products Table -->
<div class="bg-white rounded-lg shadow-md overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Hình ảnh</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Tên sản phẩm</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Danh mục</th>
                    <th class="px-6 py-3 text-right text-xs font-semibold text-gray-600 uppercase">Giá</th>
                    <th class="px-6 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Kho</th>
                    <th class="px-6 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Trạng thái</th>
                    <th class="px-6 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Thao tác</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                <?php if (empty($products)): ?>
                <tr>
                    <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                        Không tìm thấy sản phẩm nào
                    </td>
                </tr>
                <?php else: ?>
                <?php foreach ($products as $product): 
                        $images = json_decode($product['images'], true);
                        $image = $images[0] ?? '/uploads/no-image.png';
                    ?>
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4">
                        <img src="<?= $image ?>" alt="<?= $product['name'] ?>"
                            class="w-16 h-16 object-cover rounded-lg">
                    </td>
                    <td class="px-6 py-4">
                        <div class="font-semibold text-gray-900"><?= $product['name'] ?></div>
                        <div class="text-sm text-gray-500">SKU: <?= $product['sku'] ?? 'N/A' ?></div>
                    </td>
                    <td class="px-6 py-4">
                        <span class="text-sm text-gray-600"><?= $product['category_name'] ?? 'N/A' ?></span>
                    </td>
                    <td class="px-6 py-4 text-right">
                        <div class="font-semibold text-gray-900"><?= format_price($product['price']) ?></div>
                        <?php if ($product['sale_price']): ?>
                        <div class="text-sm text-red-600"><?= format_price($product['sale_price']) ?></div>
                        <?php endif; ?>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <span
                            class="<?= $product['stock_quantity'] > 0 ? 'text-green-600' : 'text-red-600' ?> font-semibold">
                            <?= $product['stock_quantity'] ?>
                        </span>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <?php if ($product['status']): ?>
                        <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-xs font-semibold">
                            Đang bán
                        </span>
                        <?php else: ?>
                        <span class="px-3 py-1 bg-gray-100 text-gray-800 rounded-full text-xs font-semibold">
                            Tạm ẩn
                        </span>
                        <?php endif; ?>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center justify-center space-x-2">
                            <a href="/admin/product-edit.php?id=<?= $product['id'] ?>"
                                class="text-blue-600 hover:text-blue-800 transition" title="Sửa">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                            </a>

                            <a href="/san-pham/<?= $product['slug'] ?>" target="_blank"
                                class="text-green-600 hover:text-green-800 transition" title="Xem">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </a>

                            <a href="?delete=<?= $product['id'] ?>"
                                onclick="return confirmDelete('Bạn có chắc muốn xóa sản phẩm này?')"
                                class="text-red-600 hover:text-red-800 transition" title="Xóa">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </a>
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
                <?= min($pagination['offset'] + $pagination['per_page'], $total) ?> trong tổng số <?= $total ?> sản phẩm
            </div>

            <nav class="flex space-x-2">
                <?php if ($pagination['current_page'] > 1): ?>
                <a href="?page=<?= $pagination['current_page'] - 1 ?><?= $search ? '&search='.urlencode($search) : '' ?><?= $category ? '&category='.$category : '' ?><?= $status !== '' ? '&status='.$status : '' ?>"
                    class="px-3 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                    ← Trước
                </a>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?>
                <?php if ($i === $pagination['current_page']): ?>
                <span class="px-3 py-2 bg-green-600 text-white rounded-lg"><?= $i ?></span>
                <?php elseif ($i === 1 || $i === $pagination['total_pages'] || abs($i - $pagination['current_page']) <= 2): ?>
                <a href="?page=<?= $i ?><?= $search ? '&search='.urlencode($search) : '' ?><?= $category ? '&category='.$category : '' ?><?= $status !== '' ? '&status='.$status : '' ?>"
                    class="px-3 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                    <?= $i ?>
                </a>
                <?php elseif (abs($i - $pagination['current_page']) === 3): ?>
                <span class="px-2">...</span>
                <?php endif; ?>
                <?php endfor; ?>

                <?php if ($pagination['current_page'] < $pagination['total_pages']): ?>
                <a href="?page=<?= $pagination['current_page'] + 1 ?><?= $search ? '&search='.urlencode($search) : '' ?><?= $category ? '&category='.$category : '' ?><?= $status !== '' ? '&status='.$status : '' ?>"
                    class="px-3 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                    Sau →
                </a>
                <?php endif; ?>
            </nav>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>