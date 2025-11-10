<?php
require_once '../config.php';
require_login();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Basic settings
        update_setting('site_name', clean_input($_POST['site_name'] ?? ''));
        update_setting('site_description', clean_input($_POST['site_description'] ?? ''));
        update_setting('contact_phone', clean_input($_POST['contact_phone'] ?? ''));
        update_setting('contact_email', clean_input($_POST['contact_email'] ?? ''));
        update_setting('contact_address', clean_input($_POST['contact_address'] ?? ''));
        
        // Social links
        update_setting('facebook_url', clean_input($_POST['facebook_url'] ?? ''));
        update_setting('zalo_url', clean_input($_POST['zalo_url'] ?? ''));
        
        // Colors
        update_setting('color_primary', clean_input($_POST['color_primary'] ?? '#2E7D32'));
        update_setting('color_secondary', clean_input($_POST['color_secondary'] ?? '#F9F7F2'));
        update_setting('color_accent', clean_input($_POST['color_accent'] ?? '#FFD54F'));
        
        // SEO
        update_setting('meta_keywords', clean_input($_POST['meta_keywords'] ?? ''));
        
        // Google Maps
        update_setting('google_maps_embed', $_POST['google_maps_embed'] ?? '');
        
        // Testimonials
        update_setting('testimonials_title', clean_input($_POST['testimonials_title'] ?? ''));
        update_setting('testimonials_description', clean_input($_POST['testimonials_description'] ?? ''));
        
        // Logo upload
        if (isset($_FILES['site_logo']) && $_FILES['site_logo']['error'] === UPLOAD_ERR_OK) {
            $result = upload_file($_FILES['site_logo'], 'settings');
            if ($result['success']) {
                update_setting('site_logo', $result['url'], 'file');
            }
        }
        
        // OG Image upload
        if (isset($_FILES['meta_og_image']) && $_FILES['meta_og_image']['error'] === UPLOAD_ERR_OK) {
            $result = upload_file($_FILES['meta_og_image'], 'settings');
            if ($result['success']) {
                update_setting('meta_og_image', $result['url'], 'file');
            }
        }
        
        $success = 'Đã lưu cài đặt thành công!';
        
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Get current settings
$settings = get_all_settings();

$page_title = 'Cài đặt website';

include 'includes/header.php';
?>

<div class="mb-6">
    <h1 class="text-3xl font-bold text-gray-900">Cài đặt website</h1>
</div>

<?php if (isset($success)): ?>
<div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
    <?= $success ?>
</div>
<?php endif; ?>

<?php if (isset($error)): ?>
<div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
    <?= $error ?>
</div>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data" class="space-y-6">

    <!-- General Settings -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-xl font-bold mb-6">Thông tin chung</h2>

        <div class="grid md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-semibold mb-2">Tên website</label>
                <input type="text" name="site_name" value="<?= $settings['site_name'] ?? '' ?>" required
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
            </div>

            <div>
                <label class="block text-sm font-semibold mb-2">Số điện thoại</label>
                <input type="tel" name="contact_phone" value="<?= $settings['contact_phone'] ?? '' ?>" required
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
            </div>

            <div class="md:col-span-2">
                <label class="block text-sm font-semibold mb-2">Mô tả website</label>
                <textarea name="site_description" rows="3" required
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500"><?= $settings['site_description'] ?? '' ?></textarea>
            </div>

            <div>
                <label class="block text-sm font-semibold mb-2">Email</label>
                <input type="email" name="contact_email" value="<?= $settings['contact_email'] ?? '' ?>" required
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
            </div>

            <div>
                <label class="block text-sm font-semibold mb-2">Địa chỉ</label>
                <input type="text" name="contact_address" value="<?= $settings['contact_address'] ?? '' ?>" required
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
            </div>
        </div>
    </div>

    <!-- Logo & Images -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-xl font-bold mb-6">Logo & Hình ảnh</h2>

        <div class="grid md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-semibold mb-2">Logo website</label>
                <?php if (!empty($settings['site_logo'])): ?>
                <div class="mb-3">
                    <img src="<?= $settings['site_logo'] ?>" alt="Logo" class="h-16">
                </div>
                <?php endif; ?>
                <input type="file" name="site_logo" accept="image/*"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                <p class="text-sm text-gray-500 mt-1">Định dạng: PNG, JPG, SVG. Kích thước khuyến nghị: 200x50px</p>
            </div>

            <div>
                <label class="block text-sm font-semibold mb-2">Ảnh Open Graph (Meta)</label>
                <?php if (!empty($settings['meta_og_image'])): ?>
                <div class="mb-3">
                    <img src="<?= $settings['meta_og_image'] ?>" alt="OG Image" class="h-32">
                </div>
                <?php endif; ?>
                <input type="file" name="meta_og_image" accept="image/*"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                <p class="text-sm text-gray-500 mt-1">Kích thước khuyến nghị: 1200x630px</p>
            </div>
        </div>
    </div>

    <!-- Theme Colors -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-xl font-bold mb-6">Màu sắc giao diện</h2>

        <div class="grid md:grid-cols-3 gap-6">
            <div>
                <label class="block text-sm font-semibold mb-2">Màu chính (Primary)</label>
                <div class="flex gap-2">
                    <input type="color" name="color_primary" value="<?= $settings['color_primary'] ?? '#2E7D32' ?>"
                        class="w-20 h-12 rounded-lg border border-gray-300">
                    <input type="text" value="<?= $settings['color_primary'] ?? '#2E7D32' ?>" readonly
                        class="flex-1 px-4 py-3 border border-gray-300 rounded-lg bg-gray-50">
                </div>
            </div>

            <div>
                <label class="block text-sm font-semibold mb-2">Màu phụ (Secondary)</label>
                <div class="flex gap-2">
                    <input type="color" name="color_secondary" value="<?= $settings['color_secondary'] ?? '#F9F7F2' ?>"
                        class="w-20 h-12 rounded-lg border border-gray-300">
                    <input type="text" value="<?= $settings['color_secondary'] ?? '#F9F7F2' ?>" readonly
                        class="flex-1 px-4 py-3 border border-gray-300 rounded-lg bg-gray-50">
                </div>
            </div>

            <div>
                <label class="block text-sm font-semibold mb-2">Màu nhấn (Accent)</label>
                <div class="flex gap-2">
                    <input type="color" name="color_accent" value="<?= $settings['color_accent'] ?? '#FFD54F' ?>"
                        class="w-20 h-12 rounded-lg border border-gray-300">
                    <input type="text" value="<?= $settings['color_accent'] ?? '#FFD54F' ?>" readonly
                        class="flex-1 px-4 py-3 border border-gray-300 rounded-lg bg-gray-50">
                </div>
            </div>
        </div>

        <div class="mt-4 p-4 bg-gray-50 rounded-lg">
            <p class="text-sm text-gray-600">
                <strong>Lưu ý:</strong> Sau khi thay đổi màu sắc, người dùng có thể cần làm mới trang (F5) để thấy cập
                nhật.
            </p>
        </div>
    </div>

    <!-- Social Media -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-xl font-bold mb-6">Mạng xã hội</h2>

        <div class="grid md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-semibold mb-2">Facebook URL</label>
                <input type="url" name="facebook_url" value="<?= $settings['facebook_url'] ?? '' ?>"
                    placeholder="https://facebook.com/your-page"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
            </div>

            <div>
                <label class="block text-sm font-semibold mb-2">Zalo URL</label>
                <input type="url" name="zalo_url" value="<?= $settings['zalo_url'] ?? '' ?>"
                    placeholder="https://zalo.me/0123456789"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
            </div>
        </div>
    </div>

    <!-- Google Maps -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-xl font-bold mb-6">📍 Google Maps</h2>

        <div>
            <label class="block text-sm font-semibold mb-2">Google Maps Embed Code</label>
            <textarea name="google_maps_embed" rows="5"
                placeholder='<iframe src="https://www.google.com/maps/embed?pb=..." width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy"></iframe>'
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 font-mono text-sm"><?= $settings['google_maps_embed'] ?? '' ?></textarea>
            <div class="mt-2 p-4 bg-blue-50 rounded-lg border border-blue-200">
                <p class="text-sm text-blue-800 mb-2"><strong>💡 Hướng dẫn lấy mã nhúng:</strong></p>
                <ol class="text-sm text-blue-700 list-decimal list-inside space-y-1">
                    <li>Mở <a href="https://www.google.com/maps" target="_blank" class="underline">Google Maps</a></li>
                    <li>Tìm kiếm địa chỉ của bạn</li>
                    <li>Nhấn nút "Share" (Chia sẻ)</li>
                    <li>Chọn tab "Embed a map" (Nhúng bản đồ)</li>
                    <li>Copy toàn bộ mã <code>&lt;iframe&gt;...&lt;/iframe&gt;</code> và dán vào đây</li>
                </ol>
            </div>

            <?php if (!empty($settings['google_maps_embed'])): ?>
            <div class="mt-4">
                <p class="text-sm font-semibold mb-2">Xem trước:</p>
                <div class="border rounded-lg p-2">
                    <?= $settings['google_maps_embed'] ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Testimonials Settings -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-xl font-bold mb-6">⭐ Phần Đánh giá Khách hàng</h2>

        <div class="space-y-4">
            <div>
                <label class="block text-sm font-semibold mb-2">Tiêu đề section</label>
                <input type="text" name="testimonials_title"
                    value="<?= $settings['testimonials_title'] ?? 'Khách hàng nói gì về chúng tôi' ?>"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
            </div>

            <div>
                <label class="block text-sm font-semibold mb-2">Mô tả ngắn</label>
                <textarea name="testimonials_description" rows="2"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500"><?= $settings['testimonials_description'] ?? 'Hàng ngàn khách hàng tin tưởng và hài lòng với sản phẩm của Nguyên Ký' ?></textarea>
            </div>

            <div class="p-4 bg-yellow-50 rounded-lg border border-yellow-200">
                <p class="text-sm text-yellow-800">
                    <strong>📝 Lưu ý:</strong> Nội dung đánh giá của khách hàng được quản lý tại
                    <a href="/admin/testimonials.php" class="underline font-semibold">trang Testimonials</a>
                </p>
            </div>
        </div>
    </div>

    <!-- SEO Settings -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-xl font-bold mb-6">SEO</h2>

        <div>
            <label class="block text-sm font-semibold mb-2">Meta Keywords</label>
            <input type="text" name="meta_keywords" value="<?= $settings['meta_keywords'] ?? '' ?>"
                placeholder="trà sữa, nguyên liệu, OEM, đóng gói"
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
            <p class="text-sm text-gray-500 mt-1">Phân cách bằng dấu phẩy</p>
        </div>
    </div>

    <!-- Submit Button -->
    <div class="flex justify-end gap-4">
        <a href="/admin/" class="px-6 py-3 border border-gray-300 rounded-lg hover:bg-gray-50 transition">
            Hủy
        </a>
        <button type="submit"
            class="bg-green-600 text-white px-8 py-3 rounded-lg font-semibold hover:bg-green-700 transition">
            💾 Lưu cài đặt
        </button>
    </div>

</form>

<script>
// Update color input text when color picker changes
document.querySelectorAll('input[type="color"]').forEach(input => {
    input.addEventListener('change', function() {
        this.nextElementSibling.value = this.value;
    });
});
</script>

<?php include 'includes/footer.php'; ?>