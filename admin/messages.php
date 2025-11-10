<?php
require_once '../config.php';
require_login();

// Handle delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $pdo->prepare("DELETE FROM chat_messages WHERE id = ?")->execute([$id]);
    header('Location: /admin/messages.php?msg=deleted');
    exit;
}

// Handle mark as read
if (isset($_GET['mark_read'])) {
    $id = intval($_GET['mark_read']);
    $pdo->prepare("UPDATE chat_messages SET status = 'read' WHERE id = ?")->execute([$id]);
    header('Location: /admin/messages.php');
    exit;
}

// Handle mark as replied
if (isset($_POST['mark_replied'])) {
    $id = intval($_POST['message_id']);
    $pdo->prepare("UPDATE chat_messages SET status = 'replied' WHERE id = ?")->execute([$id]);
    echo json_encode(['success' => true]);
    exit;
}

// Get filters
$search = $_GET['search'] ?? '';
$status_filter = $_GET['status'] ?? '';
$page = max(1, intval($_GET['page'] ?? 1));

// Build query
$where = ['1=1'];
$params = [];

if ($search) {
    $where[] = "(customer_name LIKE ? OR customer_phone LIKE ? OR customer_email LIKE ? OR message LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($status_filter) {
    $where[] = "status = ?";
    $params[] = $status_filter;
}

$where_sql = implode(' AND ', $where);

// Get total
$stmt = $pdo->prepare("SELECT COUNT(*) FROM chat_messages WHERE $where_sql");
$stmt->execute($params);
$total = $stmt->fetchColumn();

// Pagination
$pagination = paginate($total, $page, 20);

// Get messages
$sql = "SELECT * FROM chat_messages WHERE $where_sql ORDER BY created_at DESC LIMIT {$pagination['per_page']} OFFSET {$pagination['offset']}";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$messages = $stmt->fetchAll();

// Get stats
$stats = [
    'new' => $pdo->query("SELECT COUNT(*) FROM chat_messages WHERE status = 'new'")->fetchColumn(),
    'read' => $pdo->query("SELECT COUNT(*) FROM chat_messages WHERE status = 'read'")->fetchColumn(),
    'replied' => $pdo->query("SELECT COUNT(*) FROM chat_messages WHERE status = 'replied'")->fetchColumn()
];

$page_title = 'Quản lý tin nhắn';

include 'includes/header.php';
?>

<div class="mb-6">
    <h1 class="text-3xl font-bold text-gray-900">Tin nhắn</h1>
</div>

<?php if (isset($_GET['msg']) && $_GET['msg'] === 'deleted'): ?>
<div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
    Đã xóa tin nhắn thành công!
</div>
<?php endif; ?>

<!-- Stats -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600 mb-1">Tin nhắn mới</p>
                <p class="text-3xl font-bold text-red-600"><?= number_format($stats['new']) ?></p>
            </div>
            <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600 mb-1">Đã đọc</p>
                <p class="text-3xl font-bold text-blue-600"><?= number_format($stats['read']) ?></p>
            </div>
            <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600 mb-1">Đã trả lời</p>
                <p class="text-3xl font-bold text-green-600"><?= number_format($stats['replied']) ?></p>
            </div>
            <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                </svg>
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="bg-white rounded-lg shadow-md p-6 mb-6">
    <form method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
            <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Tìm kiếm tin nhắn..."
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
        </div>

        <div>
            <select name="status"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                <option value="">Tất cả trạng thái</option>
                <option value="new" <?= $status_filter === 'new' ? 'selected' : '' ?>>Mới</option>
                <option value="read" <?= $status_filter === 'read' ? 'selected' : '' ?>>Đã đọc</option>
                <option value="replied" <?= $status_filter === 'replied' ? 'selected' : '' ?>>Đã trả lời</option>
            </select>
        </div>

        <div class="flex gap-2">
            <button type="submit"
                class="flex-1 bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition">
                Lọc
            </button>
            <a href="/admin/messages.php"
                class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                Reset
            </a>
        </div>
    </form>
</div>

<!-- Messages List -->
<div class="space-y-4">
    <?php if (empty($messages)): ?>
    <div class="bg-white rounded-lg shadow-md p-12 text-center text-gray-500">
        <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
        </svg>
        <p class="text-lg">Chưa có tin nhắn nào</p>
    </div>
    <?php else: ?>
    <?php foreach ($messages as $msg): ?>
    <div
        class="bg-white rounded-lg shadow-md overflow-hidden <?= $msg['status'] === 'new' ? 'ring-2 ring-red-500' : '' ?>">
        <div class="p-6">
            <div class="flex items-start justify-between mb-4">
                <div class="flex items-start flex-1">
                    <div
                        class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mr-4 flex-shrink-0">
                        <span class="text-green-600 font-bold text-lg">
                            <?= mb_substr($msg['customer_name'], 0, 1) ?>
                        </span>
                    </div>

                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-1">
                            <h3 class="font-bold text-lg text-gray-900"><?= $msg['customer_name'] ?></h3>
                            <?php
                                $status_colors = [
                                    'new' => 'bg-red-100 text-red-800',
                                    'read' => 'bg-blue-100 text-blue-800',
                                    'replied' => 'bg-green-100 text-green-800'
                                ];
                                $status_names = [
                                    'new' => 'Mới',
                                    'read' => 'Đã đọc',
                                    'replied' => 'Đã trả lời'
                                ];
                                ?>
                            <span
                                class="px-2 py-1 rounded-full text-xs font-semibold <?= $status_colors[$msg['status']] ?>">
                                <?= $status_names[$msg['status']] ?>
                            </span>
                        </div>

                        <div class="flex items-center gap-4 text-sm text-gray-600 mb-3">
                            <span class="flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                </svg>
                                <?= $msg['customer_phone'] ?>
                            </span>

                            <?php if ($msg['customer_email']): ?>
                            <span class="flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                                <?= $msg['customer_email'] ?>
                            </span>
                            <?php endif; ?>

                            <span class="flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <?= time_ago($msg['created_at']) ?>
                            </span>
                        </div>

                        <div class="bg-gray-50 rounded-lg p-4 mb-4">
                            <p class="text-gray-700"><?= nl2br($msg['message']) ?></p>
                        </div>

                        <div class="flex items-center gap-2">
                            <?php if ($msg['status'] === 'new'): ?>
                            <a href="?mark_read=<?= $msg['id'] ?>"
                                class="text-blue-600 hover:text-blue-800 flex items-center text-sm font-semibold">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Đánh dấu đã đọc
                            </a>
                            <?php endif; ?>

                            <a href="tel:<?= $msg['customer_phone'] ?>"
                                class="text-green-600 hover:text-green-800 flex items-center text-sm font-semibold">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                </svg>
                                Gọi điện
                            </a>

                            <?php if ($msg['customer_email']): ?>
                            <a href="mailto:<?= $msg['customer_email'] ?>"
                                class="text-purple-600 hover:text-purple-800 flex items-center text-sm font-semibold">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                                Gửi email
                            </a>
                            <?php endif; ?>

                            <a href="?delete=<?= $msg['id'] ?>"
                                onclick="return confirmDelete('Bạn có chắc muốn xóa tin nhắn này?')"
                                class="text-red-600 hover:text-red-800 flex items-center text-sm font-semibold ml-auto">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                                Xóa
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- Pagination -->
<?php if ($pagination['total_pages'] > 1): ?>
<div class="bg-white rounded-lg shadow-md p-6 mt-6">
    <div class="flex items-center justify-between">
        <div class="text-sm text-gray-600">
            Hiển thị <?= $pagination['offset'] + 1 ?> -
            <?= min($pagination['offset'] + $pagination['per_page'], $total) ?> trong tổng số <?= $total ?> tin nhắn
        </div>

        <nav class="flex space-x-2">
            <?php if ($pagination['current_page'] > 1): ?>
            <a href="?page=<?= $pagination['current_page'] - 1 ?><?= $search ? '&search='.urlencode($search) : '' ?><?= $status_filter ? '&status='.$status_filter : '' ?>"
                class="px-3 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">← Trước</a>
            <?php endif; ?>

            <?php for ($i = max(1, $pagination['current_page'] - 2); $i <= min($pagination['total_pages'], $pagination['current_page'] + 2); $i++): ?>
            <?php if ($i === $pagination['current_page']): ?>
            <span class="px-3 py-2 bg-green-600 text-white rounded-lg"><?= $i ?></span>
            <?php else: ?>
            <a href="?page=<?= $i ?><?= $search ? '&search='.urlencode($search) : '' ?><?= $status_filter ? '&status='.$status_filter : '' ?>"
                class="px-3 py-2 border border-gray-300 rounded-lg hover:bg-gray-50"><?= $i ?></a>
            <?php endif; ?>
            <?php endfor; ?>

            <?php if ($pagination['current_page'] < $pagination['total_pages']): ?>
            <a href="?page=<?= $pagination['current_page'] + 1 ?><?= $search ? '&search='.urlencode($search) : '' ?><?= $status_filter ? '&status='.$status_filter : '' ?>"
                class="px-3 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">Sau →</a>
            <?php endif; ?>
        </nav>
    </div>
</div>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>