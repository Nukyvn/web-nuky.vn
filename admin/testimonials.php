<?php
require_once '../config.php';
require_login();
// Handle testimonial deletion
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id_to_delete = intval($_GET['id']);
    
    // Optional: Delete avatar file first
    $stmt_avatar = $pdo->prepare("SELECT customer_avatar FROM testimonials WHERE id = ?");
    $stmt_avatar->execute([$id_to_delete]);
    $avatar_url = $stmt_avatar->fetchColumn();
    
    // Convert URL to file path and delete (assuming UPLOAD_URL and UPLOAD_PATH logic)
    if ($avatar_url && strpos($avatar_url, UPLOAD_URL) === 0) {
        $file_path = UPLOAD_PATH . substr($avatar_url, strlen(UPLOAD_URL));
        if (file_exists($file_path)) {
            unlink($file_path);
        }
    }

    // Delete from database
    $stmt = $pdo->prepare("DELETE FROM testimonials WHERE id = ?");
    $stmt->execute([$id_to_delete]);
    
    header('Location: testimonials.php?success=' . urlencode('Đánh giá đã được xóa thành công!'));
    exit;
}
// Handle testimonial submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customer_name = clean_input($_POST['customer_name'] ?? '');
    $customer_title = clean_input($_POST['customer_title'] ?? '');
    $rating = intval($_POST['rating'] ?? 5);
    $content = clean_input($_POST['content'] ?? '');
    
    // Handle avatar upload
    $customer_avatar = '';
    if (isset($_FILES['customer_avatar']) && $_FILES['customer_avatar']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = UPLOAD_PATH . 'testimonials/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        $filename = time() . '_' . basename($_FILES['customer_avatar']['name']);
        $target_file = $upload_dir . $filename;
        if (move_uploaded_file($_FILES['customer_avatar']['tmp_name'], $target_file)) {
            $customer_avatar = UPLOAD_URL . 'testimonials/' . $filename;
        }
    }
    
    // Insert into database
    $stmt = $pdo->prepare("INSERT INTO testimonials (customer_name, customer_avatar, customer_title, rating, content, sort_order, status) VALUES (?, ?, ?, ?, ?, 0, 1)");
    $stmt->execute([$customer_name, $customer_avatar, $customer_title, $rating, $content]);
    
    header('Location: testimonials.php?success=1');
    exit;
}
// Fetch existing testimonials
$stmt = $pdo->query("SELECT * FROM testimonials ORDER BY sort_order ASC, created_at DESC");
$testimonials = $stmt->fetchAll();
?>
<?php include 'includes/header.php'; ?>
<div class="container mx-auto px-4 py-6">
    <h1 class="text-2xl font-bold mb-4">Quản lý Đánh giá Khách hàng</h1>
    <?php if (isset($_GET['success'])): ?>
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-6">
        Đánh giá đã được gửi thành công!
    </div>
    <?php endif; ?>
    <!-- Testimonial Form -->
    <div class="bg-white shadow-md rounded-lg p-6 mb-8">
        <h2 class="text-xl font-bold mb-4">Thêm Đánh giá Mới</h2>
        <form method="POST" enctype="multipart/form-data">
            <div class="mb-4">
                <label class="block text-gray-700 font-medium mb-2" for="customer_name">Tên Khách hàng</label>
                <input type="text" id="customer_name" name="customer_name" required
                    class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-green-500">
            </div>
            <div class="mb-4">
                <label class="block text-gray-700 font-medium mb-2" for="customer_title">Chức vụ / Tiêu đề</label>
                <input type="text" id="customer_title" name="customer_title"
                    class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-green-500">
            </div>
            <div class="mb-4">
                <label class="block text-gray-700 font-medium mb-2" for="rating">Đánh giá (1-5)</label>
                <input type="number" id="rating" name="rating" min="1" max="5" value="5" required
                    class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-green-500">
            </div>
            <div class="mb-4">
                <label class="block text-gray-700 font-medium mb-2" for="content">Nội dung Đánh giá</label>
                <textarea id="content" name="content" rows="4" required
                    class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-green-500"></textarea>
            </div>
            <div class="mb-4">
                <label class="block text-gray-700 font-medium mb-2" for="customer_avatar">Ảnh Đại diện (tùy
                    chọn)</label>
                <input type="file" id="customer_avatar" name="customer_avatar" accept="image/*" class="w-full">
            </div>
            <button type="submit"
                class="bg-green-500 hover:bg-green-600 text-white font-bold px-6 py-2 rounded-lg transition">Gửi Đánh
                giá</button>
        </form>
    </div>
    <!-- Existing Testimonials -->
    <div class="bg-white shadow-md rounded-lg p-6">
        <h2 class="text-xl font-bold mb-4">Đánh giá Hiện có</h2>
        <?php if (count($testimonials) === 0): ?>
        <p>Chưa có đánh giá nào.</p>
        <?php else: ?>
        <table class="w-full table-auto border-collapse">
            <thead>
                <tr class="bg-gray-100">
                    <th class="border px-4 py-2 text-left">Tên Khách hàng</th>
                    <th class="border px-4 py-2 text-left">Ảnh Đại diện</th>
                    <th class="border px-4 py-2 text-left">Chức vụ / Tiêu đề</th>
                    <th class="border px-4 py-2 text-left">Đánh giá</th>
                    <th class="border px-4 py-2 text-left">Nội dung</th>
                    <th class="border px-4 py-2 text-left">Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($testimonials as $testimonial): ?>
                <tr>
                    <td class="border px-4 py-2"><?= htmlspecialchars($testimonial['customer_name']) ?></td>
                    <td class="border px-4 py-2">
                        <?php if ($testimonial['customer_avatar']): ?>
                        <img src="<?= htmlspecialchars($testimonial['customer_avatar']) ?>" alt="Avatar"
                            class="w-16 h-16 object-cover rounded-full">
                        <?php else: ?>
                        <span class="text-gray-500">Chưa có ảnh</span>
                        <?php endif; ?>
                    </td>
                    <td class="border px-4 py-2"><?= htmlspecialchars($testimonial['customer_title']) ?></td>
                    <td class="border px-4 py-2"><?= str_repeat('⭐', $testimonial['rating']) ?></td>
                    <td class="border px-4 py-2"><?= htmlspecialchars($testimonial['content']) ?></td>
                    <td class="border px-4 py-2">
                        <button
                            class="bg-blue-500 hover:bg-blue-600 text-white font-bold px-4 py-2 rounded-lg transition edit-testimonial-btn"
                            data-id="<?= $testimonial['id'] ?>"
                            data-name="<?= htmlspecialchars($testimonial['customer_name'], ENT_QUOTES) ?>"
                            data-title="<?= htmlspecialchars($testimonial['customer_title'], ENT_QUOTES) ?>"
                            data-rating="<?= $testimonial['rating'] ?>"
                            data-content="<?= htmlspecialchars($testimonial['content'], ENT_QUOTES) ?>"
                            data-avatar-url="<?= htmlspecialchars($testimonial['customer_avatar'], ENT_QUOTES) ?>">
                            Chỉnh sửa
                        </button>
                        <button
                            class="bg-red-500 hover:bg-red-600 text-white font-bold px-4 py-2 rounded-lg transition delete-testimonial-btn"
                            data-id="<?= $testimonial['id'] ?>">
                            Xóa
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>

<!-- Edit Testimonial Modal -->
<div id="editTestimonialModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-lg p-6 m-4">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-bold">Chỉnh sửa Đánh giá</h2>
            <button type="button" id="closeModalBtn" class="text-gray-500 hover:text-gray-700 text-2xl leading-none">
                &times;
            </button>
        </div>
        <form method="POST" id="editTestimonialForm" enctype="multipart/form-data">
            <input type="hidden" id="edit_id" name="id">

            <div class="mb-4">
                <label class="block text-gray-700 font-medium mb-2" for="edit_customer_name">Tên Khách hàng</label>
                <input type="text" id="edit_customer_name" name="customer_name" required
                    class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div class="mb-4">
                <label class="block text-gray-700 font-medium mb-2" for="edit_customer_title">Chức vụ / Tiêu đề</label>
                <input type="text" id="edit_customer_title" name="customer_title"
                    class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div class="mb-4">
                <label class="block text-gray-700 font-medium mb-2" for="edit_rating">Đánh giá (1-5)</label>
                <input type="number" id="edit_rating" name="rating" min="1" max="5" required
                    class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div class="mb-4">
                <label class="block text-gray-700 font-medium mb-2" for="edit_content">Nội dung Đánh giá</label>
                <textarea id="edit_content" name="content" rows="4" required
                    class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
            </div>
            <div class="mb-4">
                <label class="block text-gray-700 font-medium mb-2" for="edit_customer_avatar">Ảnh Đại diện (tùy
                    chọn)</label>
                <input type="file" id="edit_customer_avatar" name="customer_avatar" accept="image/*" class="w-full">
                <p id="currentAvatarInfo" class="text-sm text-gray-500 mt-1"></p>
            </div>

            <button type="submit"
                class="bg-blue-500 hover:bg-blue-600 text-white font-bold px-6 py-2 rounded-lg transition">Lưu Thay
                Đổi</button>
        </form>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('editTestimonialModal');
    const form = document.getElementById('editTestimonialForm');
    const closeModalBtn = document.getElementById('closeModalBtn');
    const editBtns = document.querySelectorAll('.edit-testimonial-btn');

    // Function to open modal and populate data
    editBtns.forEach(button => {
        button.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            const name = this.getAttribute('data-name');
            const title = this.getAttribute('data-title');
            const rating = this.getAttribute('data-rating');
            const content = this.getAttribute('data-content');
            const avatarUrl = this.getAttribute('data-avatar-url');

            // Populate form fields
            document.getElementById('edit_id').value = id;
            document.getElementById('edit_customer_name').value = name;
            document.getElementById('edit_customer_title').value = title;
            document.getElementById('edit_rating').value = rating;
            document.getElementById('edit_content').value = content;

            // Display current avatar info
            const avatarInfo = document.getElementById('currentAvatarInfo');
            if (avatarUrl) {
                avatarInfo.innerHTML = 'Ảnh hiện tại: <a href="' + avatarUrl +
                    '" target="_blank" class="text-blue-500 hover:underline">Xem ảnh</a>. Bỏ qua nếu không thay đổi.';
            } else {
                avatarInfo.textContent = 'Chưa có ảnh đại diện. Chọn file mới để thêm.';
            }

            // Update form action URL (assuming you'll handle the update submission on this page)
            // form.action = 'testimonials.php?action=update'; // You might need this later

            // Show modal
            modal.style.display = 'flex';
        });
    });

    // Function to close modal
    closeModalBtn.addEventListener('click', function() {
        modal.style.display = 'none';
    });

    // Close modal when clicking outside
    window.addEventListener('click', function(event) {
        if (event.target === modal) {
            modal.style.display = 'none';
        }
    });
});

//xoá testimonial
document.addEventListener('DOMContentLoaded', function() {
    const deleteButtons = document.querySelectorAll('.delete-testimonial-btn');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function() {
            const testimonialId = this.getAttribute('data-id');
            if (confirm('Bạn có chắc muốn xóa đánh giá này?')) {
                window.location.href = 'testimonials.php?action=delete&id=' + testimonialId;
            }
        });
    });
});
</script>
<?php include 'includes/footer.php'; ?>