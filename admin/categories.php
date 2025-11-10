<?php
require_once '../config.php';
require_login();

// Handle delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    
    // Check if category has products
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM products WHERE category_id = ?");
    $stmt->execute([$id]);
    $count = $stmt->fetchColumn();
    
    if ($count > 0) {
        header('Location: /admin/categories.php?error=has_products');
        exit;
    }
    
    $pdo->prepare("DELETE FROM categories WHERE id = ?")->execute([$id]);
    header('Location: /admin/categories.php?msg=deleted');
    exit;
}

// Handle add/edit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id'] ?? 0);
    $data = [
        'name' => clean_input($_POST['name'] ?? ''),
        'slug' => '',
        'description' => clean_input($_POST['description'] ?? ''),
        'parent_id' => !empty($_POST['parent_id']) ? intval($_POST['parent_id']) : null,
        'sort_order' => intval($_POST['sort_order'] ?? 0),
        'status' => isset($_POST['status']) ? 1 : 0
    ];
    
    // Generate slug
    $data['slug'] = unique_slug('categories', create_slug($data['name']), $id);
    
    // Handle image
    $image = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $result = upload_file($_FILES['image'], 'categories');
        if ($result['success']) {
            $image = $result['url'];
        }
    } elseif ($id) {
        $stmt = $pdo->prepare("SELECT image FROM categories WHERE id = ?");
        $stmt->execute([$id]);
        $image = $stmt->fetchColumn();
    }
    
    try {
        if ($id) {
            $sql = "UPDATE categories SET name = ?, slug = ?, description = ?, image = ?, parent_id = ?, sort_order = ?, status = ? WHERE id = ?";
            $pdo->prepare($sql)->execute([$data['name'], $data['slug'], $data['description'], $image, $data['parent_id'], $data['sort_order'], $data['status'], $id]);
        } else {
            $sql = "INSERT INTO categories (name, slug, description, image, parent_id, sort_order, status) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $pdo->prepare($sql)->execute([$data['name'], $data['slug'], $data['description'], $image, $data['parent_id'], $data['sort_order'], $data['status']]);
        }
        
        header('Location: /admin/categories.php?msg=saved');
        exit;
        
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Get categories
$categories = $pdo->query("SELECT c.*, p.name as parent_name, 
                          (SELECT COUNT(*) FROM products WHERE category_id = c.id) as product_count
                          FROM categories c
                          LEFT JOIN categories p ON c.parent_id = p.id
                          ORDER BY c.sort_order ASC, c.name ASC")->fetchAll();

$page_title = 'Quản lý danh mục';

include 'includes/header.php';
?>

<div class="flex justify-between items-center mb-6">
    <h1 class="text-3xl font-bold text-gray-900">Danh mục sản phẩm</h1>
    <button onclick="openModal()"
        class="bg-green-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-green-700 transition flex items-center">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        Thêm danh mục
    </button>
</div>

<?php if (isset($_GET['msg'])): ?>
<div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
    <?php
    $messages = [
        'saved' => 'Đã lưu danh mục thành công!',
        'deleted' => 'Đã xóa danh mục thành công!'
    ];
    echo $messages[$_GET['msg']] ?? '';
    ?>
</div>
<?php endif; ?>

<?php if (isset($_GET['error']) && $_GET['error'] === 'has_products'): ?>
<div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
    Không thể xóa danh mục này vì vẫn còn sản phẩm!
</div>
<?php endif; ?>

<?php if (isset($error)): ?>
<div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
    <?= $error ?>
</div>
<?php endif; ?>

<!-- Categories Table -->
<div class="bg-white rounded-lg shadow-md overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Hình ảnh</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Tên danh mục</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Slug</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Danh mục cha</th>
                    <th class="px-6 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Sản phẩm</th>
                    <th class="px-6 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Thứ tự</th>
                    <th class="px-6 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Trạng thái</th>
                    <th class="px-6 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Thao tác</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                <?php if (empty($categories)): ?>
                <tr>
                    <td colspan="8" class="px-6 py-8 text-center text-gray-500">
                        Chưa có danh mục nào
                    </td>
                </tr>
                <?php else: ?>
                <?php foreach ($categories as $cat): ?>
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4">
                        <?php if ($cat['image']): ?>
                        <img src="<?= $cat['image'] ?>" alt="<?= $cat['name'] ?>"
                            class="w-16 h-16 object-cover rounded-lg">
                        <?php else: ?>
                        <div class="w-16 h-16 bg-gray-200 rounded-lg flex items-center justify-center">
                            <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <?php endif; ?>
                    </td>
                    <td class="px-6 py-4">
                        <div class="font-semibold text-gray-900"><?= $cat['name'] ?></div>
                        <?php if ($cat['description']): ?>
                        <div class="text-sm text-gray-500 line-clamp-1"><?= $cat['description'] ?></div>
                        <?php endif; ?>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-600">
                        <?= $cat['slug'] ?>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-600">
                        <?= $cat['parent_name'] ?? '-' ?>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <span class="text-gray-900 font-semibold"><?= $cat['product_count'] ?></span>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <span class="text-gray-600"><?= $cat['sort_order'] ?></span>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <?php if ($cat['status']): ?>
                        <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-xs font-semibold">
                            Hiển thị
                        </span>
                        <?php else: ?>
                        <span class="px-3 py-1 bg-gray-100 text-gray-800 rounded-full text-xs font-semibold">
                            Ẩn
                        </span>
                        <?php endif; ?>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center justify-center space-x-2">
                            <button onclick='editCategory(<?= json_encode($cat) ?>)'
                                class="text-blue-600 hover:text-blue-800 transition" title="Sửa">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                            </button>

                            <a href="?delete=<?= $cat['id'] ?>"
                                onclick="return confirmDelete('Bạn có chắc muốn xóa danh mục này?')"
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
</div>

<!-- Modal Form -->
<div id="categoryModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-lg max-w-2xl w-full">
        <div class="p-6 border-b">
            <div class="flex items-center justify-between">
                <h2 class="text-2xl font-bold" id="modalTitle">Thêm danh mục</h2>
                <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>

        <form method="POST" enctype="multipart/form-data" class="p-6 space-y-4">
            <input type="hidden" name="id" id="categoryId">

            <div>
                <label class="block text-sm font-semibold mb-2">Tên danh mục <span class="text-red-500">*</span></label>
                <input type="text" name="name" id="categoryName" required
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
            </div>

            <div>
                <label class="block text-sm font-semibold mb-2">Mô tả</label>
                <textarea name="description" id="categoryDescription" rows="3"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500"></textarea>
            </div>

            <div>
                <label class="block text-sm font-semibold mb-2">Hình ảnh</label>
                <input type="file" name="image" accept="image/*"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                <div id="currentImage" class="mt-2"></div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold mb-2">Danh mục cha</label>
                    <select name="parent_id" id="categoryParent"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                        <option value="">-- Không có --</option>
                        <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>"><?= $cat['name'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-semibold mb-2">Thứ tự sắp xếp</label>
                    <input type="number" name="sort_order" id="categorySort" value="0" min="0"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                </div>
            </div>

            <div>
                <label class="flex items-center">
                    <input type="checkbox" name="status" id="categoryStatus" value="1" checked
                        class="w-5 h-5 text-green-600 rounded">
                    <span class="ml-2 font-semibold">Hiển thị</span>
                </label>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t">
                <button type="button" onclick="closeModal()"
                    class="px-6 py-3 border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                    Hủy
                </button>
                <button type="submit"
                    class="bg-green-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-green-700 transition">
                    Lưu
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openModal() {
    document.getElementById('modalTitle').textContent = 'Thêm danh mục';
    document.getElementById('categoryId').value = '';
    document.getElementById('categoryName').value = '';
    document.getElementById('categoryDescription').value = '';
    document.getElementById('categoryParent').value = '';
    document.getElementById('categorySort').value = '0';
    document.getElementById('categoryStatus').checked = true;
    document.getElementById('currentImage').innerHTML = '';
    document.getElementById('categoryModal').classList.remove('hidden');
}

function editCategory(cat) {
    document.getElementById('modalTitle').textContent = 'Sửa danh mục';
    document.getElementById('categoryId').value = cat.id;
    document.getElementById('categoryName').value = cat.name;
    document.getElementById('categoryDescription').value = cat.description || '';
    document.getElementById('categoryParent').value = cat.parent_id || '';
    document.getElementById('categorySort').value = cat.sort_order;
    document.getElementById('categoryStatus').checked = cat.status == 1;

    if (cat.image) {
        document.getElementById('currentImage').innerHTML = `<img src="${cat.image}" class="h-20 rounded-lg">`;
    } else {
        document.getElementById('currentImage').innerHTML = '';
    }

    document.getElementById('categoryModal').classList.remove('hidden');
}

function closeModal() {
    document.getElementById('categoryModal').classList.add('hidden');
}

// Close on click outside
document.getElementById('categoryModal')?.addEventListener('click', function(e) {
    if (e.target === this) closeModal();
});
</script>

<?php include 'includes/footer.php'; ?>