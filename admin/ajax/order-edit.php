<?php
require_once '../../config.php';
require_login();

$id = intval($_GET['id'] ?? 0);

$stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
$stmt->execute([$id]);
$order = $stmt->fetch();

if (!$order) {
    echo '<p class="text-center text-red-600 p-6">Không tìm thấy đơn hàng</p>';
    exit;
}
?>

<div class="flex items-center justify-between mb-6">
    <h2 class="text-2xl font-bold">Cập nhật đơn hàng #<?= $order['order_code'] ?></h2>
    <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>
    </button>
</div>

<form method="POST" action="/admin/orders.php" class="space-y-6">
    <input type="hidden" name="update_status" value="1">
    <input type="hidden" name="order_id" value="<?= $order['id'] ?>">

    <div class="bg-gray-50 rounded-lg p-4">
        <div class="grid md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-semibold mb-2">Trạng thái đơn hàng</label>
                <select name="status" required
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                    <option value="pending" <?= $order['status'] === 'pending' ? 'selected' : '' ?>>Chờ xử lý</option>
                    <option value="confirmed" <?= $order['status'] === 'confirmed' ? 'selected' : '' ?>>Đã xác nhận
                    </option>
                    <option value="processing" <?= $order['status'] === 'processing' ? 'selected' : '' ?>>Đang xử lý
                    </option>
                    <option value="shipping" <?= $order['status'] === 'shipping' ? 'selected' : '' ?>>Đang giao hàng
                    </option>
                    <option value="completed" <?= $order['status'] === 'completed' ? 'selected' : '' ?>>Hoàn thành
                    </option>
                    <option value="cancelled" <?= $order['status'] === 'cancelled' ? 'selected' : '' ?>>Đã hủy</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-semibold mb-2">Trạng thái thanh toán</label>
                <select name="payment_status" required
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                    <option value="unpaid" <?= $order['payment_status'] === 'unpaid' ? 'selected' : '' ?>>Chưa thanh
                        toán</option>
                    <option value="paid" <?= $order['payment_status'] === 'paid' ? 'selected' : '' ?>>Đã thanh toán
                    </option>
                    <option value="refunded" <?= $order['payment_status'] === 'refunded' ? 'selected' : '' ?>>Đã hoàn
                        tiền</option>
                </select>
            </div>
        </div>
    </div>

    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
        <div class="flex items-start">
            <svg class="w-5 h-5 text-blue-600 mt-0.5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <div class="text-sm text-blue-800">
                <p class="font-semibold mb-1">Lưu ý:</p>
                <ul class="list-disc list-inside space-y-1">
                    <li>Khách hàng sẽ nhận được thông báo khi trạng thái đơn hàng thay đổi</li>
                    <li>Hãy đảm bảo cập nhật đúng trạng thái để theo dõi đơn hàng hiệu quả</li>
                </ul>
            </div>
        </div>
    </div>

    <div class="flex justify-end gap-3 pt-4 border-t">
        <button type="button" onclick="closeModal()"
            class="px-6 py-3 border border-gray-300 rounded-lg hover:bg-gray-50 transition">
            Hủy
        </button>
        <button type="submit"
            class="px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition font-semibold">
            Cập nhật
        </button>
    </div>
</form>