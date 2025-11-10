<?php
// Lấy danh mục sản phẩm từ database để hiển thị trong footer
$footer_categories = $pdo->query("SELECT id, name, slug FROM categories WHERE status = 1 ORDER BY sort_order ASC, name ASC LIMIT 5")->fetchAll();
?>

<footer class="mt-auto" style="background-color: <?= COLOR_PRIMARY ?>; color: <?= COLOR_SECONDARY ?>;">
    <div class="container mx-auto px-4 py-12">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
            <div>
                <h3 class="text-white text-xl font-bold mb-4">NUKY - Xưởng Trà Nguyên Ký</h3>
                <p class="mb-4">Chuyên sản xuất và cung cấp nguyên liệu trà chất lượng cao, dịch vụ OEM thương hiệu
                    riêng.</p>
                <div class="flex space-x-4">
                    <a href="<?= get_setting('facebook_url') ?>" target="_blank" class="transition"
                        onmouseover="this.style.color='<?= COLOR_ACCENT ?>'"
                        onmouseout="this.style.color='<?= COLOR_SECONDARY ?>'">
                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                            <path
                                d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z" />
                        </svg>
                    </a>
                    <a href="<?= get_setting('zalo_url') ?>" target="_blank" class="transition"
                        onmouseover="this.style.color='<?= COLOR_ACCENT ?>'"
                        onmouseout="this.style.color='<?= COLOR_SECONDARY ?>'">
                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                            <circle cx="12" cy="12" r="11" stroke="currentColor" stroke-width="1" fill="none"/>
                            <text x="12" y="16" text-anchor="middle" font-size="12" font-weight="bold" fill="currentColor">Z</text>
                        </svg>
                    </a>
                    <a href="mailto:<?= get_setting('contact_email') ?>" class="transition"
                        onmouseover="this.style.color='<?= COLOR_ACCENT ?>'"
                        onmouseout="this.style.color='<?= COLOR_SECONDARY ?>'">
                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                            <path
                                d="M20 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z" />
                        </svg>
                    </a>
                </div>
            </div>

            <div>
                <h3 class="text-white text-lg font-bold mb-4">Liên kết nhanh</h3>
                <ul class="space-y-2">
                    <li><a href="/" class="transition" onmouseover="this.style.color='<?= COLOR_ACCENT ?>'"
                            onmouseout="this.style.color='<?= COLOR_SECONDARY ?>'">Trang chủ</a></li>
                    <li><a href="/ve-chung-toi" class="transition" onmouseover="this.style.color='<?= COLOR_ACCENT ?>'"
                            onmouseout="this.style.color='<?= COLOR_SECONDARY ?>'">Giới thiệu</a></li>
                    <li><a href="/san-pham" class="transition" onmouseover="this.style.color='<?= COLOR_ACCENT ?>'"
                            onmouseout="this.style.color='<?= COLOR_SECONDARY ?>'">Sản phẩm</a></li>
                    <li><a href="/dich-vu" class="transition" onmouseover="this.style.color='<?= COLOR_ACCENT ?>'"
                            onmouseout="this.style.color='<?= COLOR_SECONDARY ?>'">Dịch vụ</a></li>
                    <li><a href="/bai-viet" class="transition" onmouseover="this.style.color='<?= COLOR_ACCENT ?>'"
                            onmouseout="this.style.color='<?= COLOR_SECONDARY ?>'">Bài viết</a></li>
                    <li><a href="/lien-he" class="transition" onmouseover="this.style.color='<?= COLOR_ACCENT ?>'"
                            onmouseout="this.style.color='<?= COLOR_SECONDARY ?>'">Liên hệ</a></li>
                </ul>
            </div>

            <div>
                <h3 class="text-white text-lg font-bold mb-4">Sản phẩm nổi bật</h3>
                <ul class="space-y-2">
                    <?php if (!empty($footer_categories)): ?>
                        <?php foreach ($footer_categories as $cat): ?>
                            <li>
                                <a href="/san-pham?category=<?= $cat['id'] ?>" class="transition"
                                    onmouseover="this.style.color='<?= COLOR_ACCENT ?>'"
                                    onmouseout="this.style.color='<?= COLOR_SECONDARY ?>'">
                                    <?= htmlspecialchars($cat['name']) ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <li><a href="/san-pham" class="transition"
                                onmouseover="this.style.color='<?= COLOR_ACCENT ?>'"
                                onmouseout="this.style.color='<?= COLOR_SECONDARY ?>'">Tất cả sản phẩm</a></li>
                    <?php endif; ?>
                    <li><a href="/dich-vu" class="transition" onmouseover="this.style.color='<?= COLOR_ACCENT ?>'"
                            onmouseout="this.style.color='<?= COLOR_SECONDARY ?>'">Dịch vụ OEM</a></li>
                </ul>
            </div>

            <div>
                <h3 class="text-white text-lg font-bold mb-4">Liên hệ</h3>
                <ul class="space-y-3">
                    <li class="flex items-start">
                        <svg class="w-5 h-5 mr-2 mt-1 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z"
                                clip-rule="evenodd" />
                        </svg>
                        <span><?= get_setting('contact_address') ?></span>
                    </li>
                    <li class="flex items-center">
                        <svg class="w-5 h-5 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path
                                d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z" />
                        </svg>
                        <a href="tel:<?= get_setting('contact_phone') ?>" class="transition"
                            onmouseover="this.style.color='<?= COLOR_ACCENT ?>'"
                            onmouseout="this.style.color='<?= COLOR_SECONDARY ?>'">
                            <?= get_setting('contact_phone') ?>
                        </a>
                    </li>
                    <li class="flex items-center">
                        <svg class="w-5 h-5 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z" />
                            <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z" />
                        </svg>
                        <a href="mailto:<?= get_setting('contact_email') ?>" class="transition"
                            onmouseover="this.style.color='<?= COLOR_ACCENT ?>'"
                            onmouseout="this.style.color='<?= COLOR_SECONDARY ?>'">
                            <?= get_setting('contact_email') ?>
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <div style="border-top: 1px solid rgba(255, 255, 255, 0.1);" class="mt-8 pt-8 text-center">
            <p>&copy; <?= date('Y') ?> NUKY - Xưởng Trà Nguyên Ký. All rights reserved.</p>
            <p class="mt-2 text-sm">Designed & Developed with ❤️</p>
        </div>
    </div>
</footer>

<div x-data="chatBox()" x-show="isOpen" x-cloak class="fixed bottom-4 right-4 z-50">

    <button @click="toggleChat()" class="text-white rounded-full p-4 shadow-lg transition"
        style="background-color: <?= COLOR_ACCENT ?>;" x-bind:class="{ 'animate-bounce': !chatOpen }">

        <svg x-show="!chatOpen" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
        </svg>
        <svg x-show="chatOpen" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>
    </button>

    <div x-show="chatOpen" x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100"
        x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 transform scale-100"
        x-transition:leave-end="opacity-0 transform scale-95"
        class="absolute bottom-20 right-0 w-80 bg-white rounded-lg shadow-2xl overflow-hidden">

        <div class="text-white p-4" style="background-color: <?= COLOR_PRIMARY ?>;">
            <h3 class="font-bold text-lg">Chat với chúng tôi</h3>
            <p class="text-sm opacity-90">Chúng tôi luôn sẵn sàng hỗ trợ bạn</p>
        </div>

        <div class="p-4 h-96 overflow-y-auto bg-gray-50">
            <div x-show="!formSubmitted">
                <p class="mb-4 text-gray-700">Xin chào! Vui lòng để lại thông tin để chúng tôi có thể hỗ trợ bạn tốt
                    nhất.</p>

                <form @submit.prevent="submitForm()" class="space-y-3">
                    <div>
                        <input type="text" x-model="formData.name" placeholder="Họ và tên *" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:border-transparent">
                    </div>
                    <div>
                        <input type="tel" x-model="formData.phone" placeholder="Số điện thoại *" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:border-transparent">
                    </div>
                    <div>
                        <input type="email" x-model="formData.email" placeholder="Email"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:border-transparent">
                    </div>
                    <div>
                        <textarea x-model="formData.message" placeholder="Nội dung tin nhắn *" required rows="4"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:border-transparent resize-none"></textarea>
                    </div>
                    <button type="submit" class="w-full text-white py-2 rounded-lg font-semibold transition"
                        style="background-color: <?= COLOR_PRIMARY ?>;">
                        Gửi tin nhắn
                    </button>
                </form>
            </div>

            <div x-show="formSubmitted" class="text-center py-8">
                <svg class="w-16 h-16 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                    style="color: <?= COLOR_PRIMARY ?>;">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <h3 class="text-xl font-bold text-gray-900 mb-2">Cảm ơn bạn!</h3>
                <p class="text-gray-600">Chúng tôi đã nhận được tin nhắn và sẽ liên hệ lại sớm nhất.</p>
            </div>
        </div>
    </div>
</div>

<script>
function chatBox() {
    return {
        isOpen: true,
        chatOpen: false,
        formSubmitted: false,
        formData: {
            name: '',
            phone: '',
            email: '',
            message: ''
        },

        init() {
            // Auto popup after 10 seconds
            setTimeout(() => {
                if (!this.chatOpen && !localStorage.getItem('chat_closed')) {
                    this.chatOpen = true;
                }
            }, 10000);
        },

        toggleChat() {
            this.chatOpen = !this.chatOpen;
            if (!this.chatOpen) {
                localStorage.setItem('chat_closed', 'true');
            }
        },

        async submitForm() {
            try {
                const response = await fetch('/ajax/chat.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(this.formData)
                });

                const data = await response.json();

                if (data.success) {
                    this.formSubmitted = true;
                    setTimeout(() => {
                        this.chatOpen = false;
                        this.formSubmitted = false;
                        this.formData = {
                            name: '',
                            phone: '',
                            email: '',
                            message: ''
                        };
                    }, 3000);
                }
            } catch (error) {
                alert('Có lỗi xảy ra. Vui lòng thử lại.');
            }
        }
    }
}
</script>

<script src="/assets/js/cart.js"></script>

<style>
[x-cloak] {
    display: none !important;
}
</style>
</body>

</html>