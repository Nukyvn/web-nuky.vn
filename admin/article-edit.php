<?php
require_once '../config.php';
require_login();

$id = intval($_GET['id'] ?? 0);
$article = null;

if ($id) {
    $stmt = $pdo->prepare("SELECT * FROM articles WHERE id = ?");
    $stmt->execute([$id]);
    $article = $stmt->fetch();
    
    if (!$article) {
        header('Location: /admin/articles.php');
        exit;
    }
}

// Handle form submission (processed in articles.php)

$page_title = $id ? 'Sửa bài viết' : 'Thêm bài viết';

include 'includes/header.php';
?>

<div class="mb-6">
    <div class="flex items-center mb-2">
        <a href="/admin/articles.php" class="text-gray-600 hover:text-gray-900 mr-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
        </a>
        <h1 class="text-3xl font-bold text-gray-900"><?= $id ? 'Sửa bài viết' : 'Thêm bài viết' ?></h1>
    </div>
</div>

<form method="POST" action="/admin/articles.php" enctype="multipart/form-data" class="space-y-6">
    <input type="hidden" name="id" value="<?= $id ?>">

    <div class="grid lg:grid-cols-3 gap-6">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Basic Info -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-bold mb-4">Nội dung bài viết</h2>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-semibold mb-2">Tiêu đề <span
                                class="text-red-500">*</span></label>
                        <input type="text" name="title" value="<?= $article['title'] ?? '' ?>" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold mb-2">Mô tả ngắn</label>
                        <textarea name="excerpt" rows="3"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500"><?= $article['excerpt'] ?? '' ?></textarea>
                        <p class="text-sm text-gray-500 mt-1">Hiển thị trong danh sách bài viết và SEO description</p>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold mb-2">Nội dung <span
                                class="text-red-500">*</span></label>
                        <textarea id="editor" name="content"><?= $article['content'] ?? '' ?></textarea>
                        <p class="text-sm text-gray-500 mt-1">Hỗ trợ HTML và Markdown</p>
                    </div>
                </div>
            </div>

            <!-- SEO -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-bold mb-4">SEO</h2>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-semibold mb-2">Meta Title</label>
                        <input type="text" name="meta_title" value="<?= $article['meta_title'] ?? '' ?>"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                        <p class="text-sm text-gray-500 mt-1">Để trống để sử dụng tiêu đề bài viết</p>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold mb-2">Meta Description</label>
                        <textarea name="meta_description" rows="3"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500"><?= $article['meta_description'] ?? '' ?></textarea>
                        <p class="text-sm text-gray-500 mt-1">Để trống để sử dụng mô tả ngắn</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Publish -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-bold mb-4">Xuất bản</h2>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-semibold mb-2">Ngày đăng</label>
                        <input type="datetime-local" name="published_at"
                            value="<?= $article ? date('Y-m-d\TH:i', strtotime($article['published_at'] ?? $article['created_at'])) : date('Y-m-d\TH:i') ?>"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                    </div>

                    <label class="flex items-center">
                        <input type="checkbox" name="status" value="1" <?= ($article['status'] ?? 1) ? 'checked' : '' ?>
                            class="w-5 h-5 text-green-600 rounded">
                        <span class="ml-2 font-semibold">Xuất bản ngay</span>
                    </label>
                </div>

                <div class="mt-6 pt-6 border-t space-y-2">
                    <button type="submit"
                        class="w-full bg-green-600 text-white py-3 rounded-lg font-semibold hover:bg-green-700 transition">
                        <?= $id ? 'Cập nhật' : 'Thêm mới' ?>
                    </button>
                    <a href="/admin/articles.php"
                        class="block w-full text-center border border-gray-300 py-3 rounded-lg hover:bg-gray-50 transition">
                        Hủy
                    </a>
                </div>
            </div>

            <!-- Featured Image -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-bold mb-4">Ảnh đại diện</h2>

                <?php if ($article && $article['thumbnail']): ?>
                <div class="mb-4">
                    <img src="<?= $article['thumbnail'] ?>" alt="Thumbnail" class="w-full rounded-lg">
                </div>
                <?php endif; ?>

                <input type="file" name="thumbnail" accept="image/*"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                <p class="text-sm text-gray-500 mt-2">Kích thước khuyến nghị: 1200x630px</p>
            </div>

            <!-- Author & Tags -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-bold mb-4">Thông tin thêm</h2>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-semibold mb-2">Tác giả</label>
                        <input type="text" name="author" value="<?= $article['author'] ?? $_SESSION['user_name'] ?>"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold mb-2">Tags</label>
                        <input type="text" name="tags" value="<?= $article['tags'] ?? '' ?>"
                            placeholder="Trà, Kinh doanh, Sức khỏe"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                        <p class="text-sm text-gray-500 mt-1">Phân cách bằng dấu phẩy</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>


<script src="/ckeditor/ckeditor.js"></script>
<script>
ClassicEditor
    .create(document.querySelector('#editor'), {
        ckfinder: {
            uploadUrl: '/admin/upload_image.php' // đường dẫn upload hình
        },
        toolbar: [
            'heading', '|',
            'bold', 'italic', 'underline', 'strikethrough', '|',
            'link', 'imageUpload', 'blockQuote', 'insertTable', '|',
            'bulletedList', 'numberedList', 'indent', 'outdent', '|',
            'alignment',
            'undo', 'redo'
        ],
        alignment: {
            options: ['left', 'center', 'right', 'justify'] // tùy chọn canh
        }
    })
    .catch(error => {
        console.error(error);
    });
</script>

<?php include 'includes/footer.php'; ?>