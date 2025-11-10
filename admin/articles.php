<?php
require_once '../config.php';
require_login();

// Handle delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $pdo->prepare("DELETE FROM articles WHERE id = ?")->execute([$id]);
    header('Location: /admin/articles.php?msg=deleted');
    exit;
}

// Handle add/edit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id'] ?? 0);
    $data = [
        'title' => clean_input($_POST['title'] ?? ''),
        'slug' => '',
        'excerpt' => clean_input($_POST['excerpt'] ?? ''),
        'content' => $_POST['content'] ?? '',
        'author' => clean_input($_POST['author'] ?? ''),
        'tags' => clean_input($_POST['tags'] ?? ''),
        'status' => isset($_POST['status']) ? 1 : 0,
        'meta_title' => clean_input($_POST['meta_title'] ?? ''),
        'meta_description' => clean_input($_POST['meta_description'] ?? ''),
        'published_at' => !empty($_POST['published_at']) ? $_POST['published_at'] : date('Y-m-d H:i:s')
    ];
    
    $data['slug'] = unique_slug('articles', create_slug($data['title']), $id);
    
    // Handle thumbnail
    $thumbnail = '';
    if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] === UPLOAD_ERR_OK) {
        $result = upload_file($_FILES['thumbnail'], 'articles');
        if ($result['success']) {
            $thumbnail = $result['url'];
        }
    } elseif ($id) {
        $stmt = $pdo->prepare("SELECT thumbnail FROM articles WHERE id = ?");
        $stmt->execute([$id]);
        $thumbnail = $stmt->fetchColumn();
    }
    
    try {
        if ($id) {
            $sql = "UPDATE articles SET title = ?, slug = ?, excerpt = ?, content = ?, thumbnail = ?, 
                    author = ?, tags = ?, status = ?, meta_title = ?, meta_description = ?, published_at = ? WHERE id = ?";
            $pdo->prepare($sql)->execute([
                $data['title'], $data['slug'], $data['excerpt'], $data['content'], $thumbnail,
                $data['author'], $data['tags'], $data['status'], $data['meta_title'], $data['meta_description'],
                $data['published_at'], $id
            ]);
        } else {
            $sql = "INSERT INTO articles (title, slug, excerpt, content, thumbnail, author, tags, status, 
                    meta_title, meta_description, published_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $pdo->prepare($sql)->execute([
                $data['title'], $data['slug'], $data['excerpt'], $data['content'], $thumbnail,
                $data['author'], $data['tags'], $data['status'], $data['meta_title'], $data['meta_description'],
                $data['published_at']
            ]);
        }
        
        header('Location: /admin/articles.php?msg=saved');
        exit;
        
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Get filters
$search = $_GET['search'] ?? '';
$status_filter = $_GET['status'] ?? '';
$page = max(1, intval($_GET['page'] ?? 1));

// Build query
$where = ['1=1'];
$params = [];

if ($search) {
    $where[] = "(title LIKE ? OR excerpt LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($status_filter !== '') {
    $where[] = "status = ?";
    $params[] = $status_filter;
}

$where_sql = implode(' AND ', $where);

// Get total
$stmt = $pdo->prepare("SELECT COUNT(*) FROM articles WHERE $where_sql");
$stmt->execute($params);
$total = $stmt->fetchColumn();

// Pagination
$pagination = paginate($total, $page);

// Get articles
$sql = "SELECT * FROM articles WHERE $where_sql ORDER BY created_at DESC LIMIT {$pagination['per_page']} OFFSET {$pagination['offset']}";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$articles = $stmt->fetchAll();

$page_title = 'Quản lý bài viết';

include 'includes/header.php';
?>

<div class="flex justify-between items-center mb-6">
    <h1 class="text-3xl font-bold text-gray-900">Bài viết</h1>
    <a href="/admin/article-edit.php"
        class="bg-green-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-green-700 transition flex items-center">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        Thêm bài viết
    </a>
</div>

<?php if (isset($_GET['msg'])): ?>
<div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
    <?php
    $messages = [
        'saved' => 'Đã lưu bài viết thành công!',
        'deleted' => 'Đã xóa bài viết thành công!'
    ];
    echo $messages[$_GET['msg']] ?? '';
    ?>
</div>
<?php endif; ?>

<!-- Filters -->
<div class="bg-white rounded-lg shadow-md p-6 mb-6">
    <form method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
            <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Tìm kiếm bài viết..."
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
        </div>

        <div>
            <select name="status"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                <option value="">Tất cả trạng thái</option>
                <option value="1" <?= $status_filter === '1' ? 'selected' : '' ?>>Đã xuất bản</option>
                <option value="0" <?= $status_filter === '0' ? 'selected' : '' ?>>Nháp</option>
            </select>
        </div>

        <div class="flex gap-2">
            <button type="submit"
                class="flex-1 bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition">
                Lọc
            </button>
            <a href="/admin/articles.php"
                class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                Reset
            </a>
        </div>
    </form>
</div>

<!-- Articles Table -->
<div class="bg-white rounded-lg shadow-md overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Hình ảnh</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Tiêu đề</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Tác giả</th>
                    <th class="px-6 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Lượt xem</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Ngày đăng</th>
                    <th class="px-6 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Trạng thái</th>
                    <th class="px-6 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Thao tác</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                <?php if (empty($articles)): ?>
                <tr>
                    <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                        Chưa có bài viết nào
                    </td>
                </tr>
                <?php else: ?>
                <?php foreach ($articles as $article): ?>
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4">
                        <?php if ($article['thumbnail']): ?>
                        <img src="<?= $article['thumbnail'] ?>" alt="<?= $article['title'] ?>"
                            class="w-20 h-20 object-cover rounded-lg">
                        <?php else: ?>
                        <div class="w-20 h-20 bg-gray-200 rounded-lg flex items-center justify-center">
                            <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <?php endif; ?>
                    </td>
                    <td class="px-6 py-4">
                        <div class="font-semibold text-gray-900 mb-1"><?= $article['title'] ?></div>
                        <?php if ($article['excerpt']): ?>
                        <div class="text-sm text-gray-500 line-clamp-2"><?= $article['excerpt'] ?></div>
                        <?php endif; ?>
                        <?php if ($article['tags']): ?>
                        <div class="mt-2">
                            <?php foreach (explode(',', $article['tags']) as $tag): ?>
                            <span class="inline-block bg-gray-100 text-gray-600 text-xs px-2 py-1 rounded mr-1">
                                <?= trim($tag) ?>
                            </span>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-600">
                        <?= $article['author'] ?? 'Admin' ?>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <span class="text-gray-600"><?= number_format($article['view_count']) ?></span>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-600">
                        <?= format_date($article['published_at'] ?? $article['created_at']) ?>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <?php if ($article['status']): ?>
                        <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-xs font-semibold">
                            Đã xuất bản
                        </span>
                        <?php else: ?>
                        <span class="px-3 py-1 bg-gray-100 text-gray-800 rounded-full text-xs font-semibold">
                            Nháp
                        </span>
                        <?php endif; ?>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center justify-center space-x-2">
                            <a href="/admin/article-edit.php?id=<?= $article['id'] ?>"
                                class="text-blue-600 hover:text-blue-800 transition" title="Sửa">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                            </a>

                            <a href="/bai-viet/<?= $article['slug'] ?>" target="_blank"
                                class="text-green-600 hover:text-green-800 transition" title="Xem">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </a>

                            <a href="?delete=<?= $article['id'] ?>"
                                onclick="return confirmDelete('Bạn có chắc muốn xóa bài viết này?')"
                                class="text-red-600 hover:text-red-800 transition" title="Xóa">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php if ($pagination['total_pages'] > 1): ?>
    <div class="bg-gray-50 px-6 py-4 border-t">
        <div class="flex items-center justify-between">
            <div class="text-sm text-gray-600">
                Hiển thị <?= $pagination['offset'] + 1 ?> -
                <?= min($pagination['offset'] + $pagination['per_page'], $total) ?> trong tổng số <?= $total ?> bài viết
            </div>

            <nav class="flex space-x-2">
                <?php if ($pagination['current_page'] > 1): ?>
                <a href="?page=<?= $pagination['current_page'] - 1 ?><?= $search ? '&search='.urlencode($search) : '' ?><?= $status_filter !== '' ? '&status='.$status_filter : '' ?>"
                    class="px-3 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">← Trước</a>
                <?php endif; ?>

                <?php for ($i = max(1, $pagination['current_page'] - 2); $i <= min($pagination['total_pages'], $pagination['current_page'] + 2); $i++): ?>
                <?php if ($i === $pagination['current_page']): ?>
                <span class="px-3 py-2 bg-green-600 text-white rounded-lg"><?= $i ?></span>
                <?php else: ?>
                <a href="?page=<?= $i ?><?= $search ? '&search='.urlencode($search) : '' ?><?= $status_filter !== '' ? '&status='.$status_filter : '' ?>"
                    class="px-3 py-2 border border-gray-300 rounded-lg hover:bg-gray-50"><?= $i ?></a>
                <?php endif; ?>
                <?php endfor; ?>

                <?php if ($pagination['current_page'] < $pagination['total_pages']): ?>
                <a href="?page=<?= $pagination['current_page'] + 1 ?><?= $search ? '&search='.urlencode($search) : '' ?><?= $status_filter !== '' ? '&status='.$status_filter : '' ?>"
                    class="px-3 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">Sau →</a>
                <?php endif; ?>
            </nav>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>