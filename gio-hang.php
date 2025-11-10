<?php
require_once 'config.php';

$cart = get_cart();
$cart_total = get_cart_total();

$page_title = 'Giỏ hàng - ' . SITE_NAME;

include 'includes/header.php';
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
                        <span class="text-gray-500">Giỏ hàng</span>
                    </div>
                </li>
            </ol>
        </nav>
    </div>
</div>

<!-- Cart Section -->
<section class="py-12 bg-white" x-data="cartPage()">
    <div class="container mx-auto px-4">
        <h1 class="text-3xl font-bold mb-8">Giỏ hàng của bạn</h1>

        <?php if (empty($cart)): ?>
        <!-- Empty Cart -->
        <div class="text-center py-12">
            <svg class="w-32 h-32 text-gray-300 mx-auto mb-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
            <h2 class="text-2xl font-bold text-gray-700 mb-4">Giỏ hàng trống</h2>
            <p class="text-gray-500 mb-6">Bạn chưa có sản phẩm nào trong giỏ hàng</p>
            <a href="/san-pham"
                class="inline-block bg-green-600 text-white px-8 py-3 rounded-lg font-semibold hover:bg-green-700 transition">
                Tiếp tục mua sắm
            </a>
        </div>
        <?php else: ?>
        <div class="grid lg:grid-cols-3 gap-8">
            <!-- Cart Items -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <!-- Desktop Table -->
                    <div class="hidden md:block overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Sản phẩm</th>
                                    <th class="px-6 py-4 text-center text-sm font-semibold text-gray-700">Đơn giá</th>
                                    <th class="px-6 py-4 text-center text-sm font-semibold text-gray-700">Số lượng</th>
                                    <th class="px-6 py-4 text-right text-sm font-semibold text-gray-700">Thành tiền</th>
                                    <th class="px-6 py-4"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                <template x-for="(item, id) in items" :key="id">
                                    <tr>
                                        <td class="px-6 py-4">
                                            <div class="flex items-center">
                                                <img :src="item.image" :alt="item.name"
                                                    class="w-20 h-20 object-cover rounded-lg mr-4">
                                                <div>
                                                    <h3 class="font-semibold text-gray-900" x-text="item.name"></h3>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <span class="font-semibold" style="color: <?= COLOR_PRIMARY ?>"
                                                x-text="formatPrice(item.price)"></span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="flex items-center justify-center">
                                                <button @click="updateQuantity(id, item.quantity - 1)"
                                                    class="px-3 py-1 border border-gray-300 rounded-l-lg hover:bg-gray-100">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2" d="M20 12H4" />
                                                    </svg>
                                                </button>
                                                <input type="number" :value="item.quantity"
                                                    @change="updateQuantity(id, $event.target.value)" min="1"
                                                    class="w-16 text-center border-t border-b border-gray-300 py-1">
                                                <button @click="updateQuantity(id, item.quantity + 1)"
                                                    class="px-3 py-1 border border-gray-300 rounded-r-lg hover:bg-gray-100">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2" d="M12 4v16m8-8H4" />
                                                    </svg>
                                                </button>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <span class="font-bold text-lg" style="color: <?= COLOR_PRIMARY ?>"
                                                x-text="formatPrice(item.price * item.quantity)"></span>
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <button @click="removeItem(id)"
                                                class="text-red-600 hover:text-red-800 transition">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>

                    <!-- Mobile Cards -->
                    <div class="md:hidden divide-y">
                        <template x-for="(item, id) in items" :key="id">
                            <div class="p-4">
                                <div class="flex gap-4 mb-3">
                                    <img :src="item.image" :alt="item.name" class="w-24 h-24 object-cover rounded-lg">
                                    <div class="flex-1">
                                        <h3 class="font-semibold mb-2" x-text="item.name"></h3>
                                        <p class="text-lg font-bold" style="color: <?= COLOR_PRIMARY ?>"
                                            x-text="formatPrice(item.price)"></p>
                                    </div>
                                </div>

                                <div class="flex items-center justify-between">
                                    <div class="flex items-center">
                                        <button @click="updateQuantity(id, item.quantity - 1)"
                                            class="px-3 py-1 border border-gray-300 rounded-l-lg">-</button>
                                        <span class="px-4 py-1 border-t border-b border-gray-300"
                                            x-text="item.quantity"></span>
                                        <button @click="updateQuantity(id, item.quantity + 1)"
                                            class="px-3 py-1 border border-gray-300 rounded-r-lg">+</button>
                                    </div>

                                    <button @click="removeItem(id)" class="text-red-600">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </div>

                                <div class="mt-3 pt-3 border-t">
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Thành tiền:</span>
                                        <span class="font-bold text-lg" style="color: <?= COLOR_PRIMARY ?>"
                                            x-text="formatPrice(item.price * item.quantity)"></span>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <div class="mt-4 flex justify-between">
                    <a href="/san-pham" class="text-green-600 font-semibold hover:underline flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Tiếp tục mua sắm
                    </a>
                </div>
            </div>

            <!-- Order Summary -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-lg shadow-md p-6 sticky top-24">
                    <h2 class="text-xl font-bold mb-6">Tổng đơn hàng</h2>

                    <div class="space-y-3 mb-6">
                        <div class="flex justify-between text-gray-600">
                            <span>Tạm tính:</span>
                            <span class="font-semibold" x-text="formatPrice(total)"></span>
                        </div>
                        <div class="flex justify-between text-gray-600">
                            <span>Phí vận chuyển:</span>
                            <span class="font-semibold">Tính khi thanh toán</span>
                        </div>
                        <div class="border-t pt-3 flex justify-between">
                            <span class="text-lg font-bold">Tổng cộng:</span>
                            <span class="text-2xl font-bold" style="color: <?= COLOR_PRIMARY ?>"
                                x-text="formatPrice(total)"></span>
                        </div>
                    </div>

                    <a href="/thanh-toan"
                        class="block w-full bg-green-600 text-white text-center py-3 rounded-lg font-semibold hover:bg-green-700 transition mb-3">
                        Tiến hành thanh toán
                    </a>

                    <button @click="clearCart()"
                        class="block w-full border-2 border-red-600 text-red-600 text-center py-3 rounded-lg font-semibold hover:bg-red-600 hover:text-white transition">
                        Xóa giỏ hàng
                    </button>

                    <!-- Trust Badges -->
                    <div class="mt-6 pt-6 border-t space-y-3">
                        <div class="flex items-center text-sm text-gray-600">
                            <svg class="w-5 h-5 text-green-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                    clip-rule="evenodd" />
                            </svg>
                            Thanh toán an toàn
                        </div>
                        <div class="flex items-center text-sm text-gray-600">
                            <svg class="w-5 h-5 text-green-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                    clip-rule="evenodd" />
                            </svg>
                            Giao hàng toàn quốc
                        </div>
                        <div class="flex items-center text-sm text-gray-600">
                            <svg class="w-5 h-5 text-green-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                    clip-rule="evenodd" />
                            </svg>
                            Hỗ trợ 24/7
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</section>

<script>
function cartPage() {
    return {
        items: <?= json_encode($cart) ?>,
        total: <?= $cart_total ?>,

        calculateTotal() {
            this.total = Object.values(this.items).reduce((sum, item) => {
                return sum + (item.price * item.quantity);
            }, 0);
        },

        updateQuantity(productId, quantity) {
            quantity = Math.max(1, parseInt(quantity));

            fetch('/ajax/cart.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        action: 'update',
                        product_id: productId,
                        quantity: quantity
                    })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        this.items[productId].quantity = quantity;
                        this.calculateTotal();
                        this.updateCartCount();
                    }
                });
        },

        removeItem(productId) {
            if (!confirm('Bạn có chắc muốn xóa sản phẩm này?')) return;

            fetch('/ajax/cart.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        action: 'remove',
                        product_id: productId
                    })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        delete this.items[productId];
                        this.calculateTotal();
                        this.updateCartCount();

                        if (Object.keys(this.items).length === 0) {
                            window.location.reload();
                        }
                    }
                });
        },

        clearCart() {
            if (!confirm('Bạn có chắc muốn xóa toàn bộ giỏ hàng?')) return;

            fetch('/ajax/cart.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        action: 'clear'
                    })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        window.location.reload();
                    }
                });
        },

        updateCartCount() {
            fetch('/ajax/cart.php?action=count')
                .then(res => res.json())
                .then(data => {
                    document.querySelector('[data-cart-count]').textContent = data.count;
                });
        },

        formatPrice(price) {
            return new Intl.NumberFormat('vi-VN', {
                style: 'currency',
                currency: 'VND'
            }).format(price);
        }
    }
}
</script>

<?php include 'includes/footer.php'; ?>