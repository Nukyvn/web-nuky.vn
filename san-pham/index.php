<?php
require_once '../config.php';

// Get filters
$category_id = $_GET['category'] ?? null;
$search = $_GET['search'] ?? '';
$sort = $_GET['sort'] ?? 'newest';
$page = max(1, intval($_GET['page'] ?? 1));

// Build query
$where = ["status = 1"];
$params = [];

if ($category_id) {
    $where[] = "category_id = ?";
    $params[] = $category_id;
}

if ($search) {
    $where[] = "(name LIKE ? OR description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$where_sql = implode(' AND ', $where);

// Get total count
$count_sql = "SELECT COUNT(*) FROM products WHERE $where_sql";
$stmt = $pdo->prepare($count_sql);
$stmt->execute($params);
$total_products = $stmt->fetchColumn();

// Pagination
$pagination = paginate($total_products, $page);

// Sort options
$order_by = match($sort) {
    'price_asc' => 'price ASC',
    'price_desc' => 'price DESC',
    'name' => 'name ASC',
    default => 'created_at DESC'
};

// Get products
$sql = "SELECT * FROM products WHERE $where_sql ORDER BY $order_by LIMIT {$pagination['per_page']} OFFSET {$pagination['offset']}";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

// Get categories for filter
$categories = $pdo->query("SELECT * FROM categories WHERE status = 1 ORDER BY sort_order ASC")->fetchAll();

$page_title = 'Sản phẩm - ' . SITE_NAME;
$page_description = 'Khám phá các sản phẩm trà chất lượng cao từ xưởng Nguyên Ký';

include '../includes/header.php';
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
                        <span class="text-gray-500">Sản phẩm</span>
                    </div>
                </li>
            </ol>
        </nav>
    </div>
</div>

<section class="py-12 bg-white">
    <div class="container mx-auto px-4">
        <div class="flex flex-col lg:flex-row gap-8">

            <aside class="lg:w-64 flex-shrink-0">
                <div class="bg-white rounded-lg shadow-md p-6 sticky top-24">
                    <h3 class="text-xl font-bold mb-4" style="color: <?= COLOR_PRIMARY ?>">Bộ lọc</h3>

                    <div class="mb-6">
                        <label class="block text-sm font-semibold mb-2">Tìm kiếm</label>
                        <form method="GET" action="">
                            <input type="text" name="search" value="<?= htmlspecialchars($search) ?>"
                                placeholder="Nhập tên sản phẩm..."
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                            <?php if ($category_id): ?>
                            <input type="hidden" name="category" value="<?= $category_id ?>">
                            <?php endif; ?>
                        </form>
                    </div>

                    <div class="mb-6">
                        <label class="block text-sm font-semibold mb-2">Danh mục</label>
                        <ul class="space-y-2">
                            <li>
                                <a href="/san-pham"
                                    class="block py-1 <?= !$category_id ? 'text-green-600 font-semibold' : 'text-gray-700 hover:text-green-600' ?>">
                                    Tất cả sản phẩm
                                </a>
                            </li>
                            <?php foreach ($categories as $cat): ?>
                            <li>
                                <a href="/san-pham?category=<?= $cat['id'] ?>"
                                    class="block py-1 <?= $category_id == $cat['id'] ? 'text-green-600 font-semibold' : 'text-gray-700 hover:text-green-600' ?>">
                                    <?= $cat['name'] ?>
                                </a>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold mb-2">Sắp xếp</label>
                        <select name="sort" onchange="window.location.href=updateQueryParam('sort', this.value)"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                            <option value="newest" <?= $sort === 'newest' ? 'selected' : '' ?>>Mới nhất</option>
                            <option value="price_asc" <?= $sort === 'price_asc' ? 'selected' : '' ?>>Giá thấp đến cao
                            </option>
                            <option value="price_desc" <?= $sort === 'price_desc' ? 'selected' : '' ?>>Giá cao đến thấp
                            </option>
                            <option value="name" <?= $sort === 'name' ? 'selected' : '' ?>>Tên A-Z</option>
                        </select>
                    </div>
                </div>
            </aside>

            <div class="flex-1">
                <div class="flex justify-between items-center mb-6">
                    <h1 class="text-2xl font-bold">
                        <?php if ($search): ?>
                        Kết quả tìm kiếm "<?= htmlspecialchars($search) ?>"
                        <?php elseif ($category_id): ?>
                        <?php
                            $cat = array_filter($categories, fn($c) => $c['id'] == $category_id);
                            echo reset($cat)['name'] ?? 'Danh mục';
                            ?>
                        <?php else: ?>
                        Tất cả sản phẩm
                        <?php endif; ?>
                    </h1>
                    <span class="text-gray-600"><?= $total_products ?> sản phẩm</span>
                </div>

                <?php if (empty($products)): ?>
                <div class="text-center py-12">
                    <svg class="w-24 h-24 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                    </svg>
                    <h3 class="text-xl font-semibold text-gray-700 mb-2">Không tìm thấy sản phẩm</h3>
                    <p class="text-gray-500">Thử thay đổi bộ lọc hoặc từ khóa tìm kiếm</p>
                </div>
                <?php else: ?>
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                    <?php foreach ($products as $product): 
                        $images = json_decode($product['images'], true);
                        $image = $images[0] ?? '/uploads/no-image.png';
                        $price = $product['sale_price'] ?? $product['price'];
                    ?>
                    <div
                        class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-xl transition group product-item">
                        <a href="/san-pham/<?= $product['slug'] ?>" class="block relative overflow-hidden">
                            <img src="<?= $image ?>" alt="<?= $product['name'] ?>"
                                class="w-full h-64 object-cover group-hover:scale-110 transition duration-300 product-image">
                            <?php if ($product['sale_price']): ?>
                            <span
                                class="absolute top-2 right-2 bg-red-500 text-white px-2 py-1 rounded text-sm font-semibold">
                                -<?= round((1 - $product['sale_price'] / $product['price']) * 100) ?>%
                            </span>
                            <?php endif; ?>
                        </a>

                        <div class="p-4">
                            <h3 class="font-semibold text-lg mb-2 line-clamp-2">
                                <a href="/san-pham/<?= $product['slug'] ?>" class="hover:text-green-600">
                                    <?= $product['name'] ?>
                                </a>
                            </h3>

                            <div class="flex items-center justify-between mb-3">
                                <div>
                                    <span class="text-xl font-bold" style="color: <?= COLOR_PRIMARY ?>">
                                        <?= format_price($price) ?>
                                    </span>
                                    <?php if ($product['sale_price']): ?>
                                    <span class="text-sm text-gray-400 line-through ml-2">
                                        <?= format_price($product['price']) ?>
                                    </span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <button onclick="
    // 🛑 DÙNG 'var' ĐỂ TRÁNH LỖI PHẠM VI (REDECLARE)
    var container = this.closest('.product-item'); 
    var imgElement = container ? container.querySelector('.product-image') : null;
    
    // GỌI HÀM CHÍNH VỚI 2 THAM SỐ
    addToCart(<?= $product['id'] ?>, imgElement);
" class="w-full text-white py-2 rounded-lg transition font-semibold" style="background-color: <?= COLOR_PRIMARY ?>;"
                                onmouseover="this.style.backgroundColor='#1a5e20';"
                                onmouseout="this.style.backgroundColor='<?= COLOR_PRIMARY ?>';">
                                Thêm vào giỏ
                            </button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <?php if ($pagination['total_pages'] > 1): ?>
                <div class="flex justify-center mt-8">
                    <nav class="flex items-center space-x-2">
                        <?php if ($pagination['current_page'] > 1): ?>
                        <a href="?page=<?= $pagination['current_page'] - 1 ?><?= $category_id ? '&category='.$category_id : '' ?><?= $search ? '&search='.urlencode($search) : '' ?>"
                            class="px-3 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                            ← Trước
                        </a>
                        <?php endif; ?>

                        <?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?>
                        <?php if ($i === $pagination['current_page']): ?>
                        <span class="px-3 py-2 text-white rounded-lg font-semibold"
                            style="background-color: <?= COLOR_PRIMARY ?>;"><?= $i ?></span>
                        <?php elseif ($i === 1 || $i === $pagination['total_pages'] || abs($i - $pagination['current_page']) <= 2): ?>
                        <a href="?page=<?= $i ?><?= $category_id ? '&category='.$category_id : '' ?><?= $search ? '&search='.urlencode($search) : '' ?>"
                            class="px-3 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                            <?= $i ?>
                        </a>
                        <?php elseif (abs($i - $pagination['current_page']) === 3): ?>
                        <span class="px-2">...</span>
                        <?php endif; ?>
                        <?php endfor; ?>

                        <?php if ($pagination['current_page'] < $pagination['total_pages']): ?>
                        <a href="?page=<?= $pagination['current_page'] + 1 ?><?= $category_id ? '&category='.$category_id : '' ?><?= $search ? '&search='.urlencode($search) : '' ?>"
                            class="px-3 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                            Sau →
                        </a>
                        <?php endif; ?>
                    </nav>
                </div>
                <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>



<?php include ROOT_PATH . '/includes/footer.php'; ?>