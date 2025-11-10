<?php
require_once '../config.php';
require_login();

// Handle delete
if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM promotions WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    header('Location: /admin/promotions.php?success=deleted');
    exit;
}

// Handle add/edit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $id = $_POST['id'] ?? null;
        $title = clean_input($_POST['title']);
        $description = clean_input($_POST['description']);
        $discount_text = clean_input($_POST['discount_text']);
        $button_text = clean_input($_POST['button_text']);
        $button_link = clean_input($_POST['button_link']);
        $countdown_end = $_POST['countdown_end'] ? date('Y-m-d H:i:s', strtotime($_POST['countdown_end'])) : null;
        $status = isset($_POST['status']) ? 1 : 0;
        $sort_order = intval($_POST['sort_order'] ?? 0);
        
        // Handle image upload
        $banner_image = $_POST['existing_image'] ?? '';
        if (isset($_FILES['banner_image']) && $_FILES['banner_image']['error'] === UPLOAD_ERR_OK) {
            $result = upload_file($_FILES['banner_image'], 'promotions');
            if ($result['success']) {
                $banner_image = $result['url'];
            }
        }
        
        if ($id) {
            // Update
            $stmt = $pdo->prepare("UPDATE promotions SET title=?, description=?, banner_image=?, discount_text=?, button_text=?, button_link=?, countdown_end=?, status=?, sort_order=? WHERE id=?");
            $stmt->execute([$title, $description, $banner_image, $discount_text, $button_text, $button_link, $countdown_end, $status, $sort_order, $id]);
        } else {
            // Insert
            $stmt = $pdo->prepare("INSERT INTO promotions (title, description, banner_image, discount_text, button_text, button_link, countdown_end, status, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$title, $description, $banner_image, $discount_text, $button_text, $button_link, $countdown_end, $status, $sort_order]);
        }
        
        header('Location: /admin/promotions.php?success=saved');
        exit;
        
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Get all promotions
$stmt = $pdo->query("SELECT * FROM promotions ORDER BY sort_order ASC, created_at DESC");
$promotions = $stmt->fetchAll();

$page_title = 'Quản lý Khuyến mãi';
include 'includes/header.php';
?>

<div class="flex justify-between items-center mb-6">
    <h1 class="text-3xl font-bold text-gray-900">🎉 Quản lý Khuyến mãi</h1>
    <button onclick="openModal()"
        class="bg-green-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-green-700 transition">
        + Thêm khuyến mãi
    </button>
</div>

<?php if (isset($_GET['success'])): ?>
<div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
    <?= $_GET['success'] === 'saved' ? 'Đã lưu thành công!' : 'Đã xóa thành công!' ?>
</div>
<?php endif; ?>

<?php if (isset($error)): ?>
<div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
    <?= $error ?>
</div>
<?php endif; ?>

<!-- Promotions List -->
<div class="bg-white rounded-lg shadow-md overflow-hidden">
    <table class="w-full">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Banner</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tiêu đề</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Giảm giá</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Thời gian</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Trạng thái</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Thứ tự</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Hành động</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            <?php if (empty($promotions)): ?>
            <tr>
                <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                    Chưa có khuyến mãi nào. Nhấn "Thêm khuyến mãi" để tạo mới.
                </td>
            </tr>
            <?php else: ?>
            <?php foreach ($promotions as $promo): ?>
            <tr>
                <td class="px-6 py-4">
                    <?php if ($promo['banner_image']): ?>
                    <img src="<?= $promo['banner_image'] ?>" alt="" class="h-16 w-24 object-cover rounded">
                    <?php else: ?>
                    <div class="h-16 w-24 bg-gray-200 rounded flex items-center justify-center text-gray-400 text-xs">
                        No image
                    </div>
                    <?php endif; ?>
                </td>
                <td class="px-6 py-4">
                    <div class="font-semibold"><?= $promo['title'] ?></div>
                    <div class="text-sm text-gray-500"><?= substr($promo['description'], 0, 60) ?>...</div>
                </td>
                <td class="px-6 py-4">
                    <span class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded text-sm font-semibold">
                        <?= $promo['discount_text'] ?>
                    </span>
                </td>
                <td class="px-6 py-4">
                    <?php if ($promo['start_date']): ?>
                    <div>Bắt đầu: <?= date('d/m/Y H:i', strtotime($promo['start_date'])) ?></div>
                    <?php endif; ?>
                    <?php if ($promo['countdown_end']): ?>
                    <div>Kết thúc: <?= date('d/m/Y H:i', strtotime($promo['countdown_end'])) ?></div>
                    <?php else: ?>
                    <div>Không giới hạn thời gian</div>
                    <?php endif; ?>
                </td>
                <td class="px-6 py-4">
                    <?php if ($promo['status']): ?>
                    <span class="bg-green-100 text-green-800 px-2 py-1 rounded text-sm">Hoạt động</span>
                    <?php else: ?>
                    <span class="bg-gray-100 text-gray-800 px-2 py-1 rounded text-sm">Ẩn</span>
                    <?php endif; ?>
                </td>
                <td class="px-6 py-4 text-center">
                    <?= $promo['sort_order'] ?>
                </td>
                <td class="px-6 py-4">
                    <button onclick='editPromotion(<?= json_encode($promo) ?>)'
                        class="text-blue-600 hover:underline mr-3">Sửa</button>
                    <a href="?delete=<?= $promo['id'] ?>" onclick="return confirm('Xác nhận xóa?')"
                        class="text-red-600 hover:underline">Xóa</a>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Modal Form -->
<div id="promoModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg p-8 max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold" id="modalTitle">Thêm Khuyến mãi</h2>
            <button onclick="closeModal()" class="text-gray-500 hover:text-gray-700">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                    </path>
                </svg>
            </button>
        </div>

        <form method="POST" enctype="multipart/form-data" class="space-y-4">
            <input type="hidden" name="id" id="promo_id">
            <input type="hidden" name="existing_image" id="existing_image">

            <div>
                <label class="block text-sm font-semibold mb-2">Tiêu đề *</label>
                <input type="text" name="title" id="title" required
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500"
                    placeholder="VD: 🎉 FLASH SALE CUỐI TUẦN">
            </div>

            <div>
                <label class="block text-sm font-semibold mb-2">Mô tả</label>
                <textarea name="description" id="description" rows="3"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500"
                    placeholder="Mô tả chi tiết về chương trình khuyến mãi"></textarea>
            </div>

            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold mb-2">Text giảm giá *</label>
                    <input type="text" name="discount_text" id="discount_text" required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500"
                        placeholder="VD: GIẢM TỚI 50%">
                </div>

                <div>
                    <label class="block text-sm font-semibold mb-2">Text button</label>
                    <input type="text" name="button_text" id="button_text"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500"
                        placeholder="Mua ngay">
                </div>
            </div>

            <div>
                <label class="block text-sm font-semibold mb-2">Link button</label>
                <input type="text" name="button_link" id="button_link"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500"
                    placeholder="/san-pham">
            </div>

            <div>
                <label class="block text-sm font-semibold mb-2">Banner image</label>
                <input type="file" name="banner_image" accept="image/*"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg">
                <div id="current_image_preview" class="mt-2 hidden">
                    <img id="preview_img" src="" alt="" class="h-32 rounded">
                </div>
            </div>
            <div>
                <label class="block text-sm font-semibold mb-2">Ngày bắt đầu</label>
                <input type="datetime-local" name="start_date" id="start_date"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
            </div>

            <div>
                <label class="block text-sm font-semibold mb-2">Thời gian kết thúc đếm ngược</label>
                <input type="datetime-local" name="countdown_end" id="countdown_end"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                <p class="text-sm text-gray-500 mt-1">Để trống nếu không cần đếm ngược</p>
            </div>

            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold mb-2">Thứ tự hiển thị</label>
                    <input type="number" name="sort_order" id="sort_order" value="0"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                </div>

                <div class="flex items-center">
                    <label class="flex items-center cursor-pointer">
                        <input type="checkbox" name="status" id="status" value="1" checked class="mr-2">
                        <span class="font-semibold">Hiển thị trên trang chủ</span>
                    </label>
                </div>
            </div>

            <div class="flex justify-end gap-4 pt-4">
                <button type="button" onclick="closeModal()"
                    class="px-6 py-3 border border-gray-300 rounded-lg hover:bg-gray-50">
                    Hủy
                </button>
                <button type="submit"
                    class="bg-green-600 text-white px-8 py-3 rounded-lg font-semibold hover:bg-green-700">
                    💾 Lưu
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openModal() {
    document.getElementById('promoModal').classList.remove('hidden');
    document.getElementById('modalTitle').textContent = 'Thêm Khuyến mãi';
    document.querySelector('form').reset();
    document.getElementById('promo_id').value = '';
    document.getElementById('existing_image').value = '';
    document.getElementById('current_image_preview').classList.add('hidden');
}

function closeModal() {
    document.getElementById('promoModal').classList.add('hidden');
}

function editPromotion(promo) {
    document.getElementById('promoModal').classList.remove('hidden');
    document.getElementById('modalTitle').textContent = 'Sửa Khuyến mãi';

    document.getElementById('promo_id').value = promo.id;
    document.getElementById('title').value = promo.title;
    document.getElementById('description').value = promo.description || '';
    document.getElementById('discount_text').value = promo.discount_text;
    document.getElementById('button_text').value = promo.button_text;
    document.getElementById('button_link').value = promo.button_link || '';
    document.getElementById('sort_order').value = promo.sort_order;
    document.getElementById('status').checked = promo.status == 1;
    document.getElementById('existing_image').value = promo.banner_image || '';

    if (promo.countdown_end) {
        const date = new Date(promo.countdown_end);
        const formatted = date.toISOString().slice(0, 16);
        document.getElementById('countdown_end').value = formatted;
    }

    if (promo.banner_image) {
        document.getElementById('preview_img').src = promo.banner_image;
        document.getElementById('current_image_preview').classList.remove('hidden');
    }
}

// Close modal on escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeModal();
});
</script>

<?php include 'includes/footer.php'; ?>