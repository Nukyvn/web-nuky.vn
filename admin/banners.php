<?php
require_once '../config.php';
require_login();

// Handle delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $pdo->prepare("DELETE FROM banners WHERE id = ?")->execute([$id]);
    header('Location: /admin/banners.php?msg=deleted');
    exit;
}

// Handle add/edit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id'] ?? 0);
    $data = [
        'title' => clean_input($_POST['title'] ?? ''),
        'subtitle' => clean_input($_POST['subtitle'] ?? ''),
        'link' => clean_input($_POST['link'] ?? ''),
        'button_text' => clean_input($_POST['button_text'] ?? ''),
        'position' => clean_input($_POST['position'] ?? 'hero'),
        'sort_order' => intval($_POST['sort_order'] ?? 0),
        'status' => isset($_POST['status']) ? 1 : 0
    ];
    
    // Handle image/video
    $image = $video = '';
    
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $result = upload_file($_FILES['image'], 'banners');
        if ($result['success']) $image = $result['url'];
    } elseif ($id) {
        $stmt = $pdo->prepare("SELECT image FROM banners WHERE id = ?");
        $stmt->execute([$id]);
        $image = $stmt->fetchColumn();
    }
    
    if (isset($_FILES['video']) && $_FILES['video']['error'] === UPLOAD_ERR_OK) {
        $result = upload_file($_FILES['video'], 'banners');
        if ($result['success']) $video = $result['url'];
    } elseif ($id) {
        $stmt = $pdo->prepare("SELECT video FROM banners WHERE id = ?");
        $stmt->execute([$id]);
        $video = $stmt->fetchColumn();
    }
    
    try {
        if ($id) {
            $sql = "UPDATE banners SET title = ?, subtitle = ?, image = ?, video = ?, link = ?, button_text = ?, position = ?, sort_order = ?, status = ? WHERE id = ?";
            $pdo->prepare($sql)->execute([$data['title'], $data['subtitle'], $image, $video, $data['link'], $data['button_text'], $data['position'], $data['sort_order'], $data['status'], $id]);
        } else {
            $sql = "INSERT INTO banners (title, subtitle, image, video, link, button_text, position, sort_order, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $pdo->prepare($sql)->execute([$data['title'], $data['subtitle'], $image, $video, $data['link'], $data['button_text'], $data['position'], $data['sort_order'], $data['status']]);
        }
        
        header('Location: /admin/banners.php?msg=saved');
        exit;
        
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Get banners
$banners = $pdo->query("SELECT * FROM banners ORDER BY sort_order ASC, created_at DESC")->fetchAll();

$page_title = 'Quản lý Banner';

include 'includes/header.php';
?>

<div class="flex justify-between items-center mb-6">
    <h1 class="text-3xl font-bold text-gray-900">Banners / Sliders</h1>
    <button onclick="openModal()"
        class="bg-green-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-green-700 transition flex items-center">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        Thêm banner
    </button>
</div>

<?php if (isset($_GET['msg'])): ?>
<div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
    <?php
    $messages = [
        'saved' => 'Đã lưu banner thành công!',
        'deleted' => 'Đã xóa banner thành công!'
    ];
    echo $messages[$_GET['msg']] ?? '';
    ?>
</div>
<?php endif; ?>

<!-- Banners Grid -->
<div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
    <?php if (empty($banners)): ?>
    <div class="col-span-full bg-white rounded-lg shadow-md p-12 text-center text-gray-500">
        <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
        </svg>
        <p class="text-lg">Chưa có banner nào</p>
    </div>
    <?php else: ?>
    <?php foreach ($banners as $banner): ?>
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="relative h-48 bg-gray-200">
            <?php if ($banner['video']): ?>
            <video src="<?= $banner['video'] ?>" class="w-full h-full object-cover"></video>
            <div class="absolute top-2 left-2 bg-purple-600 text-white px-2 py-1 rounded text-xs font-semibold">
                VIDEO
            </div>
            <?php elseif ($banner['image']): ?>
            <img src="<?= $banner['image'] ?>" alt="<?= $banner['title'] ?>" class="w-full h-full object-cover">
            <?php else: ?>
            <div class="flex items-center justify-center h-full">
                <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
            </div>
            <?php endif; ?>

            <div class="absolute top-2 right-2 flex gap-2">
                <?php if ($banner['status']): ?>
                <span class="bg-green-600 text-white px-2 py-1 rounded text-xs font-semibold">Hiển thị</span>
                <?php else: ?>
                <span class="bg-gray-600 text-white px-2 py-1 rounded text-xs font-semibold">Ẩn</span>
                <?php endif; ?>
            </div>
        </div>

        <div class="p-4">
            <h3 class="font-bold text-lg mb-2"><?= $banner['title'] ?></h3>
            <?php if ($banner['subtitle']): ?>
            <p class="text-sm text-gray-600 mb-3"><?= $banner['subtitle'] ?></p>
            <?php endif; ?>

            <div class="flex items-center justify-between text-sm text-gray-600 mb-4">
                <span class="bg-gray-100 px-2 py-1 rounded">
                    <?php
                        $positions = ['hero' => 'Hero', 'sidebar' => 'Sidebar', 'popup' => 'Popup'];
                        echo $positions[$banner['position']] ?? $banner['position'];
                        ?>
                </span>
                <span>Thứ tự: <?= $banner['sort_order'] ?></span>
            </div>

            <div class="flex gap-2">
                <button onclick='editBanner(<?= json_encode($banner) ?>)'
                    class="flex-1 bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700 transition text-sm font-semibold">
                    Sửa
                </button>
                <a href="?delete=<?= $banner['id'] ?>"
                    onclick="return confirmDelete('Bạn có chắc muốn xóa banner này?')"
                    class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition text-sm font-semibold">
                    Xóa
                </a>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- Modal Form -->
<div id="bannerModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-lg max-w-2xl w-full max-h-[90vh] overflow-y-auto">
        <div class="p-6 border-b">
            <div class="flex items-center justify-between">
                <h2 class="text-2xl font-bold" id="modalTitle">Thêm banner</h2>
                <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>

        <form method="POST" enctype="multipart/form-data" class="p-6 space-y-4">
            <input type="hidden" name="id" id="bannerId">

            <div>
                <label class="block text-sm font-semibold mb-2">Tiêu đề <span class="text-red-500">*</span></label>
                <input type="text" name="title" id="bannerTitle" required
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
            </div>

            <div>
                <label class="block text-sm font-semibold mb-2">Phụ đề</label>
                <textarea name="subtitle" id="bannerSubtitle" rows="2"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500"></textarea>
            </div>

            <div>
                <label class="block text-sm font-semibold mb-2">Hình ảnh</label>
                <input type="file" name="image" accept="image/*"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                <div id="currentImage" class="mt-2"></div>
            </div>

            <div>
                <label class="block text-sm font-semibold mb-2">Video (Optional)</label>
                <input type="file" name="video" accept="video/*"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                <div id="currentVideo" class="mt-2"></div>
                <p class="text-sm text-gray-500 mt-1">Video sẽ được ưu tiên hiển thị nếu có cả ảnh và video</p>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold mb-2">Link đến</label>
                    <input type="url" name="link" id="bannerLink" placeholder="https://example.com"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                </div>

                <div>
                    <label class="block text-sm font-semibold mb-2">Text nút bấm</label>
                    <input type="text" name="button_text" id="bannerButton" placeholder="Xem thêm"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold mb-2">Vị trí</label>
                    <select name="position" id="bannerPosition"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                        <option value="hero">Hero (Trang chủ)</option>
                        <option value="sidebar">Sidebar</option>
                        <option value="popup">Popup</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-semibold mb-2">Thứ tự</label>
                    <input type="number" name="sort_order" id="bannerSort" value="0" min="0"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                </div>
            </div>

            <div>
                <label class="flex items-center">
                    <input type="checkbox" name="status" id="bannerStatus" value="1" checked
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
    document.getElementById('modalTitle').textContent = 'Thêm banner';
    document.getElementById('bannerId').value = '';
    document.getElementById('bannerTitle').value = '';
    document.getElementById('bannerSubtitle').value = '';
    document.getElementById('bannerLink').value = '';
    document.getElementById('bannerButton').value = '';
    document.getElementById('bannerPosition').value = 'hero';
    document.getElementById('bannerSort').value = '0';
    document.getElementById('bannerStatus').checked = true;
    document.getElementById('currentImage').innerHTML = '';
    document.getElementById('currentVideo').innerHTML = '';
    document.getElementById('bannerModal').classList.remove('hidden');
}

function editBanner(banner) {
    document.getElementById('modalTitle').textContent = 'Sửa banner';
    document.getElementById('bannerId').value = banner.id;
    document.getElementById('bannerTitle').value = banner.title;
    document.getElementById('bannerSubtitle').value = banner.subtitle || '';
    document.getElementById('bannerLink').value = banner.link || '';
    document.getElementById('bannerButton').value = banner.button_text || '';
    document.getElementById('bannerPosition').value = banner.position;
    document.getElementById('bannerSort').value = banner.sort_order;
    document.getElementById('bannerStatus').checked = banner.status == 1;

    if (banner.image) {
        document.getElementById('currentImage').innerHTML = `<img src="${banner.image}" class="h-24 rounded-lg">`;
    } else {
        document.getElementById('currentImage').innerHTML = '';
    }

    if (banner.video) {
        document.getElementById('currentVideo').innerHTML =
            `<video src="${banner.video}" class="h-24 rounded-lg" controls></video>`;
    } else {
        document.getElementById('currentVideo').innerHTML = '';
    }

    document.getElementById('bannerModal').classList.remove('hidden');
}

function closeModal() {
    document.getElementById('bannerModal').classList.add('hidden');
}

document.getElementById('bannerModal')?.addEventListener('click', function(e) {
    if (e.target === this) closeModal();
});
</script>

<?php include 'includes/footer.php'; ?>