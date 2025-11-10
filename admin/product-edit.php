<?php
require_once '../config.php';
require_login();

$id = intval($_GET['id'] ?? 0);
$product = null;

if ($id) {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$id]);
    $product = $stmt->fetch();
    
    if (!$product) {
        header('Location: /admin/products.php');
        exit;
    }
}

// Get categories
$categories = $pdo->query("SELECT * FROM categories WHERE status = 1 ORDER BY name")->fetchAll();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'category_id' => intval($_POST['category_id'] ?? 0),
        'name' => clean_input($_POST['name'] ?? ''),
        'slug' => '',
        'short_description' => clean_input($_POST['short_description'] ?? ''),
        'description' => $_POST['description'] ?? '',
        'price' => floatval($_POST['price'] ?? 0),
        'sale_price' => !empty($_POST['sale_price']) ? floatval($_POST['sale_price']) : null,
        'stock_quantity' => intval($_POST['stock_quantity'] ?? 0),
        'sku' => clean_input($_POST['sku'] ?? ''),
        'weight' => clean_input($_POST['weight'] ?? ''),
        'expiry_date' => clean_input($_POST['expiry_date'] ?? ''),
        'featured' => isset($_POST['featured']) ? 1 : 0,
        'status' => isset($_POST['status']) ? 1 : 0,
        'meta_title' => clean_input($_POST['meta_title'] ?? ''),
        'meta_description' => clean_input($_POST['meta_description'] ?? ''),
        'meta_keywords' => clean_input($_POST['meta_keywords'] ?? '')
    ];
    
    // Generate slug
    $data['slug'] = unique_slug('products', create_slug($data['name']), $id);
    
    // Handle images
    $images = [];
    if (!empty($_POST['existing_images'])) {
        $images = json_decode($_POST['existing_images'], true);
    }
    
    // Upload new images
    if (!empty($_FILES['images']['name'][0])) {
        foreach ($_FILES['images']['name'] as $key => $name) {
            if ($_FILES['images']['error'][$key] === UPLOAD_ERR_OK) {
                $file = [
                    'name' => $_FILES['images']['name'][$key],
                    'type' => $_FILES['images']['type'][$key],
                    'tmp_name' => $_FILES['images']['tmp_name'][$key],
                    'error' => $_FILES['images']['error'][$key],
                    'size' => $_FILES['images']['size'][$key]
                ];
                
                $result = upload_file($file, 'products');
                if ($result['success']) {
                    $images[] = $result['url'];
                }
            }
        }
    }
    
    $data['images'] = json_encode($images);
    
    try {
        if ($id) {
            // Update
            $sql = "UPDATE products SET 
                    category_id = ?, name = ?, slug = ?, short_description = ?, description = ?,
                    price = ?, sale_price = ?, stock_quantity = ?, sku = ?, weight = ?,
                    expiry_date = ?, images = ?, featured = ?, status = ?,
                    meta_title = ?, meta_description = ?, meta_keywords = ?
                    WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $data['category_id'], $data['name'], $data['slug'], $data['short_description'], $data['description'],
                $data['price'], $data['sale_price'], $data['stock_quantity'], $data['sku'], $data['weight'],
                $data['expiry_date'], $data['images'], $data['featured'], $data['status'],
                $data['meta_title'], $data['meta_description'], $data['meta_keywords'],
                $id
            ]);
        } else {
            // Insert
            $sql = "INSERT INTO products (category_id, name, slug, short_description, description,
                    price, sale_price, stock_quantity, sku, weight, expiry_date, images, featured, status,
                    meta_title, meta_description, meta_keywords)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $data['category_id'], $data['name'], $data['slug'], $data['short_description'], $data['description'],
                $data['price'], $data['sale_price'], $data['stock_quantity'], $data['sku'], $data['weight'],
                $data['expiry_date'], $data['images'], $data['featured'], $data['status'],
                $data['meta_title'], $data['meta_description'], $data['meta_keywords']
            ]);
        }
        
        header('Location: /admin/products.php?msg=saved');
        exit;
        
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

$page_title = $id ? 'Sửa sản phẩm' : 'Thêm sản phẩm';

include 'includes/header.php';
?>

<div class="mb-6">
    <div class="flex items-center mb-2">
        <a href="/admin/products.php" class="text-gray-600 hover:text-gray-900 mr-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
        </a>
        <h1 class="text-3xl font-bold text-gray-900"><?= $id ? 'Sửa sản phẩm' : 'Thêm sản phẩm' ?></h1>
    </div>
</div>

<?php if (isset($error)): ?>
<div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
    <?= $error ?>
</div>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data" x-data="productForm()" class="space-y-6">
    <div class="grid lg:grid-cols-3 gap-6">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Basic Info -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-bold mb-4">Thông tin cơ bản</h2>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-semibold mb-2">Tên sản phẩm <span
                                class="text-red-500">*</span></label>
                        <input type="text" name="name" value="<?= $product['name'] ?? '' ?>" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold mb-2">Mô tả ngắn</label>
                        <textarea name="short_description" rows="3"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500"><?= $product['short_description'] ?? '' ?></textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold mb-2">Mô tả chi tiết</label>
                        <textarea id="editor" name="description" rows="10"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
    <?= $product['description'] ?? '' ?>
</textarea>

                    </div>
                </div>
            </div>

            <!-- Images -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-bold mb-4">Hình ảnh sản phẩm</h2>

                <!-- Existing Images -->
                <?php if ($product && $product['images']): 
                    $existing_images = json_decode($product['images'], true);
                ?>
                <div class="grid grid-cols-4 gap-4 mb-4">
                    <?php foreach ($existing_images as $img): ?>
                    <div class="relative group">
                        <img src="<?= $img ?>" class="w-full h-32 object-cover rounded-lg">
                        <button type="button" @click="removeImage('<?= $img ?>')"
                            class="absolute top-2 right-2 bg-red-500 text-white p-1 rounded-full opacity-0 group-hover:opacity-100 transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    <?php endforeach; ?>
                </div>
                <input type="hidden" name="existing_images" x-model="existingImagesJson">
                <?php endif; ?>

                <input type="file" name="images[]" multiple accept="image/*"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                <p class="text-sm text-gray-500 mt-2">Có thể chọn nhiều ảnh. Định dạng: JPG, PNG, GIF, WEBP. Tối đa
                    5MB/ảnh.</p>
            </div>

            <!-- SEO -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-bold mb-4">SEO</h2>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-semibold mb-2">Meta Title</label>
                        <input type="text" name="meta_title" value="<?= $product['meta_title'] ?? '' ?>"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold mb-2">Meta Description</label>
                        <textarea name="meta_description" rows="3"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500"><?= $product['meta_description'] ?? '' ?></textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold mb-2">Meta Keywords</label>
                        <input type="text" name="meta_keywords" value="<?= $product['meta_keywords'] ?? '' ?>"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
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
                    <label class="flex items-center">
                        <input type="checkbox" name="status" value="1" <?= ($product['status'] ?? 1) ? 'checked' : '' ?>
                            class="w-5 h-5 text-green-600 rounded">
                        <span class="ml-2 font-semibold">Đang bán</span>
                    </label>

                    <label class="flex items-center">
                        <input type="checkbox" name="featured" value="1"
                            <?= ($product['featured'] ?? 0) ? 'checked' : '' ?> class="w-5 h-5 text-green-600 rounded">
                        <span class="ml-2 font-semibold">Sản phẩm nổi bật</span>
                    </label>
                </div>

                <div class="mt-6 pt-6 border-t space-y-2">
                    <button type="submit"
                        class="w-full bg-green-600 text-white py-3 rounded-lg font-semibold hover:bg-green-700 transition">
                        <?= $id ? 'Cập nhật' : 'Thêm mới' ?>
                    </button>
                    <a href="/admin/products.php"
                        class="block w-full text-center border border-gray-300 py-3 rounded-lg hover:bg-gray-50 transition">
                        Hủy
                    </a>
                </div>
            </div>

            <!-- Category -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-bold mb-4">Danh mục</h2>
                <select name="category_id" required
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                    <option value="">Chọn danh mục</option>
                    <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat['id'] ?>"
                        <?= ($product['category_id'] ?? '') == $cat['id'] ? 'selected' : '' ?>>
                        <?= $cat['name'] ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Pricing -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-bold mb-4">Giá bán</h2>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-semibold mb-2">Giá gốc (VNĐ) <span
                                class="text-red-500">*</span></label>
                        <input type="number" name="price" value="<?= $product['price'] ?? '' ?>" required min="0"
                            step="1000"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold mb-2">Giá khuyến mãi (VNĐ)</label>
                        <input type="number" name="sale_price" value="<?= $product['sale_price'] ?? '' ?>" min="0"
                            step="1000"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                    </div>
                </div>
            </div>

            <!-- Inventory -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-bold mb-4">Kho hàng</h2>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-semibold mb-2">SKU</label>
                        <input type="text" name="sku" value="<?= $product['sku'] ?? '' ?>"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold mb-2">Số lượng</label>
                        <input type="number" name="stock_quantity" value="<?= $product['stock_quantity'] ?? 0 ?>"
                            min="0"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold mb-2">Trọng lượng</label>
                        <input type="text" name="weight" value="<?= $product['weight'] ?? '' ?>" placeholder="500g"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold mb-2">Hạn sử dụng</label>
                        <input type="text" name="expiry_date" value="<?= $product['expiry_date'] ?? '' ?>"
                            placeholder="12 tháng"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
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
<script>
function productForm() {
    return {
        existingImages: <?= json_encode($existing_images ?? []) ?>,

        get existingImagesJson() {
            return JSON.stringify(this.existingImages);
        },

        removeImage(url) {
            if (confirm('Bạn có chắc muốn xóa ảnh này?')) {
                this.existingImages = this.existingImages.filter(img => img !== url);
            }
        }
    }
}
</script>

<?php include 'includes/footer.php'; ?>