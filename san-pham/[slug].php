<?php
require_once '../config.php';

// Get product by slug
$slug = $_GET['slug'] ?? '';
$stmt = $pdo->prepare("SELECT p.*, c.name as category_name, c.slug as category_slug 
                       FROM products p 
                       LEFT JOIN categories c ON p.category_id = c.id 
                       WHERE p.slug = ? AND p.status = 1");
$stmt->execute([$slug]);
$product = $stmt->fetch();

if (!$product) {
    header('Location: /san-pham');
    exit;
}

// Update view count
$pdo->prepare("UPDATE products SET view_count = view_count + 1 WHERE id = ?")->execute([$product['id']]);

// Get related products
$stmt = $pdo->prepare("SELECT * FROM products 
                       WHERE category_id = ? AND id != ? AND status = 1 
                       ORDER BY RAND() LIMIT 4");
$stmt->execute([$product['category_id'], $product['id']]);
$related_products = $stmt->fetchAll();

// Parse images
$images = json_decode($product['images'], true) ?: ['/uploads/no-image.png'];
$price = $product['sale_price'] ?? $product['price'];

$page_title = $product['name'] . ' - ' . SITE_NAME;
$page_description = $product['short_description'] ?? strip_tags(substr($product['description'], 0, 160));
$page_og_image = $images[0];

include '../includes/header.php';
?>

<!-- Breadcrumb -->
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
                        <a href="/san-pham" class="text-gray-700 hover:text-green-600">Sản phẩm</a>
                    </div>
                </li>
                <?php if ($product['category_name']): ?>
                <li>
                    <div class="flex items-center">
                        <svg class="w-3 h-3 text-gray-400 mx-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                                clip-rule="evenodd" />
                        </svg>
                        <a href="/san-pham?category=<?= $product['category_id'] ?>"
                            class="text-gray-700 hover:text-green-600">
                            <?= $product['category_name'] ?>
                        </a>
                    </div>
                </li>
                <?php endif; ?>
                <li>
                    <div class="flex items-center">
                        <svg class="w-3 h-3 text-gray-400 mx-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                                clip-rule="evenodd" />
                        </svg>
                        <span class="text-gray-500"><?= $product['name'] ?></span>
                    </div>
                </li>
            </ol>
        </nav>
    </div>
</div>

<!-- Product Detail -->
<section class="py-12 bg-white">
    <div class="container mx-auto px-4">
        <div class="grid md:grid-cols-2 gap-12" x-data="productDetail()">

            <!-- Images Gallery -->
            <div>
                <!-- Main Image -->
                <div class="mb-4 relative overflow-hidden rounded-lg">
                    <img :src="currentImage" alt="<?= $product['name'] ?>"
                        class="w-full h-96 md:h-[500px] object-cover">
                    <?php if ($product['sale_price']): ?>
                    <span class="absolute top-4 right-4 bg-red-500 text-white px-3 py-2 rounded-lg text-lg font-bold">
                        -<?= round((1 - $product['sale_price'] / $product['price']) * 100) ?>%
                    </span>
                    <?php endif; ?>
                </div>

                <!-- Thumbnail Gallery -->
                <?php if (count($images) > 1): ?>
                <div class="grid grid-cols-4 gap-2">
                    <?php foreach ($images as $img): ?>
                    <img src="<?= $img ?>" alt="<?= $product['name'] ?>" @click="currentImage = '<?= $img ?>'"
                        :class="currentImage === '<?= $img ?>' ? 'ring-2 ring-green-600' : ''"
                        class="w-full h-24 object-cover rounded-lg cursor-pointer hover:opacity-80 transition">
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>

            <!-- Product Info -->
            <div>
                <h1 class="text-3xl md:text-4xl font-bold mb-4"><?= $product['name'] ?></h1>

                <?php if ($product['short_description']): ?>
                <p class="text-gray-600 text-lg mb-6"><?= $product['short_description'] ?></p>
                <?php endif; ?>

                <!-- Price -->
                <div class="mb-6">
                    <div class="flex items-baseline gap-3 mb-2">
                        <span class="text-4xl font-bold" style="color: <?= COLOR_PRIMARY ?>">
                            <?= format_price($price) ?>
                        </span>
                        <?php if ($product['sale_price']): ?>
                        <span class="text-2xl text-gray-400 line-through">
                            <?= format_price($product['price']) ?>
                        </span>
                        <?php endif; ?>
                    </div>
                    <?php if ($product['sale_price']): ?>
                    <span class="inline-block bg-red-100 text-red-700 px-3 py-1 rounded-full text-sm font-semibold">
                        Tiết kiệm <?= format_price($product['price'] - $product['sale_price']) ?>
                    </span>
                    <?php endif; ?>
                </div>

                <!-- Product Info Grid -->
                <div class="bg-gray-50 rounded-lg p-4 mb-6">
                    <div class="grid grid-cols-2 gap-4">
                        <?php if ($product['sku']): ?>
                        <div>
                            <span class="text-sm text-gray-500">Mã sản phẩm:</span>
                            <p class="font-semibold"><?= $product['sku'] ?></p>
                        </div>
                        <?php endif; ?>

                        <?php if ($product['weight']): ?>
                        <div>
                            <span class="text-sm text-gray-500">Trọng lượng:</span>
                            <p class="font-semibold"><?= $product['weight'] ?></p>
                        </div>
                        <?php endif; ?>

                        <?php if ($product['expiry_date']): ?>
                        <div>
                            <span class="text-sm text-gray-500">Hạn sử dụng:</span>
                            <p class="font-semibold"><?= $product['expiry_date'] ?></p>
                        </div>
                        <?php endif; ?>

                        <div>
                            <span class="text-sm text-gray-500">Tình trạng:</span>
                            <p class="font-semibold text-green-600">
                                <?= $product['stock_quantity'] > 0 ? 'Còn hàng' : 'Hết hàng' ?>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Quantity & Add to Cart -->
                <div class="flex items-center gap-4 mb-6">
                    <div class="flex items-center border-2 border-gray-300 rounded-lg">
                        <button @click="quantity = Math.max(1, quantity - 1)"
                            class="px-4 py-2 hover:bg-gray-100 transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                            </svg>
                        </button>
                        <input type="number" x-model="quantity" min="1"
                            class="w-16 text-center border-0 focus:ring-0 font-semibold">
                        <button @click="quantity++" class="px-4 py-2 hover:bg-gray-100 transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 4v16m8-8H4" />
                            </svg>
                        </button>
                    </div>

                    <button @click="addToCart(<?= $product['id'] ?>, quantity)"
                        class="flex-1 bg-green-600 text-white py-3 px-6 rounded-lg font-semibold text-lg hover:bg-green-700 transition flex items-center justify-center gap-2">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        Thêm vào giỏ hàng
                    </button>
                </div>

                <!-- Contact Buttons -->
                <div class="grid grid-cols-2 gap-4 mb-6">
                    <a href="tel:<?= get_setting('contact_phone') ?>"
                        class="border-2 border-green-600 text-green-600 py-3 px-6 rounded-lg font-semibold text-center hover:bg-green-600 hover:text-white transition flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path
                                d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z" />
                        </svg>
                        Gọi ngay
                    </a>
                    <a href="<?= get_setting('zalo_url') ?>" target="_blank"
                        class="bg-blue-500 text-white py-3 px-6 rounded-lg font-semibold text-center hover:bg-blue-600 transition flex items-center justify-center gap-2">
                        Chat Zalo
                    </a>
                </div>

                <!-- Features -->
                <div class="border-t pt-6">
                    <ul class="space-y-3">
                        <li class="flex items-center text-gray-700">
                            <svg class="w-5 h-5 text-green-600 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                    clip-rule="evenodd" />
                            </svg>
                            Sản phẩm chính hãng 100%
                        </li>
                        <li class="flex items-center text-gray-700">
                            <svg class="w-5 h-5 text-green-600 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                    clip-rule="evenodd" />
                            </svg>
                            Đổi trả trong 7 ngày nếu có lỗi
                        </li>
                        <li class="flex items-center text-gray-700">
                            <svg class="w-5 h-5 text-green-600 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                    clip-rule="evenodd" />
                            </svg>
                            Giao hàng toàn quốc
                        </li>
                        <li class="flex items-center text-gray-700">
                            <svg class="w-5 h-5 text-green-600 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                    clip-rule="evenodd" />
                            </svg>
                            Hỗ trợ 24/7
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Product Description -->
        <?php if ($product['description']): ?>
        <div class="mt-12">
            <div class="border-b border-gray-200 mb-6">
                <h2 class="text-2xl font-bold pb-4" style="color: <?= COLOR_PRIMARY ?>">Mô tả sản phẩm</h2>
            </div>
            <div class="product-content ck-content">
                <?= $product['description'] ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Related Products -->
        <?php if (!empty($related_products)): ?>
        <div class="mt-12">
            <h2 class="text-2xl font-bold mb-6" style="color: <?= COLOR_PRIMARY ?>">Sản phẩm liên quan</h2>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                <?php foreach ($related_products as $related): 
                    $rel_images = json_decode($related['images'], true);
                    $rel_image = $rel_images[0] ?? '/uploads/no-image.png';
                    $rel_price = $related['sale_price'] ?? $related['price'];
                ?>
                <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-xl transition">
                    <a href="/san-pham/<?= $related['slug'] ?>">
                        <img src="<?= $rel_image ?>" alt="<?= $related['name'] ?>" class="w-full h-48 object-cover">
                    </a>
                    <div class="p-4">
                        <h3 class="font-semibold mb-2 line-clamp-2">
                            <a href="/san-pham/<?= $related['slug'] ?>" class="hover:text-green-600">
                                <?= $related['name'] ?>
                            </a>
                        </h3>
                        <p class="text-lg font-bold" style="color: <?= COLOR_PRIMARY ?>">
                            <?= format_price($rel_price) ?>
                        </p>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</section>
<style>
/* Giữ nguyên tất cả style từ CKEditor */
.product-content * {
    max-width: inherit !important;
    font-size: inherit !important;
    line-height: inherit !important;
}

/* Giới hạn width ảnh nhưng không override style inline */
.product-content img {
    height: auto !important;
    max-width: 100% !important;
    border-radius: 6px;
    display: block;
    margin: 12px auto;
}

/* Khoảng cách đoạn văn */
.product-content p {
    margin: 14px 0;
}

/* Heading chuẩn đẹp và responsive */
.product-content h1,
.product-content h2,
.product-content h3 {
    margin-top: 24px;
    margin-bottom: 16px;
    font-weight: 700;
    line-height: 1.3;
}

/* Table chuẩn đẹp */
.product-content table {
    width: 100% !important;
    border-collapse: collapse;
    margin: 16px 0;
}

.product-content th,
.product-content td {
    border: 1px solid #e5e7eb;
    padding: 8px 12px;
}

.product-content th {
    background: #f9fafb;
    font-weight: 600;
}

/* Danh sách */
.product-content ul,
.product-content ol {
    margin: 12px 0 12px 20px;
}

.product-content li {
    margin: 6px 0;
}

.prose p[style*="text-align:center"],
.prose div[style*="text-align:center"],
.prose figure[style*="text-align:center"] {
    text-align: center !important;
}

.prose p[style*="text-align:right"],
.prose div[style*="text-align:right"],
.prose figure[style*="text-align:right"] {
    text-align: right !important;
}

.prose p[style*="text-align:justify"],
.prose div[style*="text-align:justify"],
.prose figure[style*="text-align:justify"] {
    text-align: justify !important;
}

.prose img {
    display: block;
    margin-left: auto;
    margin-right: auto;

    .product-content {
        max-width: 800px;
        margin: 0 auto;
        font-size: 1rem;
        line-height: 1.75;
    }

    /* Dùng style inline từ CKEditor là chính, không ép text-align */
    .product-content * {
        text-align: inherit !important;
    }

    /* Ảnh luôn theo format CKEditor */
    .product-content img {
        max-width: 100% !important;
        height: auto !important;
        display: block;
        margin-left: auto;
        margin-right: auto;
    }

    /* Respect alignment in CKEditor */
    .product-content [style*="text-align:center"] {
        text-align: center !important;
    }

    .product-content [style*="text-align:right"] {
        text-align: right !important;
    }

    .product-content [style*="text-align:justify"] {
        text-align: justify !important;
    }
}
</style>
<script>
function productDetail() {
    return {
        currentImage: '<?= $images[0] ?>',
        quantity: 1,

        addToCart(productId, qty) {
            fetch('/ajax/cart.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        action: 'add',
                        product_id: productId,
                        quantity: qty
                    })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        alert('Đã thêm vào giỏ hàng!');
                        this.updateCartCount();
                    }
                });
        },

        updateCartCount() {
            fetch('/ajax/cart.php?action=count')
                .then(res => res.json())
                .then(data => {
                    document.querySelector('[data-cart-count]').textContent = data.count;
                });
        }
    }
}
</script>

<?php include '../includes/footer.php'; ?>