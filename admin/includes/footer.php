</main>

<!-- Footer -->
<footer class="bg-white border-t py-4 px-6">
    <div class="flex flex-col md:flex-row items-center justify-between text-sm text-gray-600">
        <p>&copy; <?= date('Y') ?> NUKY - Xưởng Trà Nguyên Ký. All rights reserved.</p>
        <p>Phiên bản 1.0.0</p>
    </div>
</footer>
</div>
</div>

<!-- Toast Notification Component -->
<div x-data="toast()" x-show="show" x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
    x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0"
    x-transition:leave-end="opacity-0 translate-y-2" class="fixed bottom-4 right-4 z-50 max-w-sm">
    <div :class="type === 'success' ? 'bg-green-100 border-green-400 text-green-700' : 'bg-red-100 border-red-400 text-red-700'"
        class="border px-4 py-3 rounded-lg shadow-lg">
        <div class="flex items-center justify-between">
            <p x-text="message"></p>
            <button @click="show = false" class="ml-4">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                        d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                        clip-rule="evenodd" />
                </svg>
            </button>
        </div>
    </div>
</div>

<script>
// Toast notification system
function toast() {
    return {
        show: false,
        message: '',
        type: 'success',

        showToast(msg, type = 'success') {
            this.message = msg;
            this.type = type;
            this.show = true;

            setTimeout(() => {
                this.show = false;
            }, 3000);
        }
    }
}

// Global helper functions
window.showSuccess = function(message) {
    Alpine.store('toast').showToast(message, 'success');
}

window.showError = function(message) {
    Alpine.store('toast').showToast(message, 'error');
}

// Confirm delete
window.confirmDelete = function(message = 'Bạn có chắc muốn xóa?') {
    return confirm(message);
}

// Format price
window.formatPrice = function(price) {
    return new Intl.NumberFormat('vi-VN', {
        style: 'currency',
        currency: 'VND'
    }).format(price);
}
</script>

</body>

</html>