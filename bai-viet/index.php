<?php
require_once __DIR__ . '/../config.php';

// Get filters
$search = $_GET['search'] ?? '';
$tag = $_GET['tag'] ?? '';
$page = max(1, intval($_GET['page'] ?? 1));

// Build query
$where = ['status = 1'];
$params = [];

if ($search) {
    $where[] = "(title LIKE ? OR excerpt LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($tag) {
    $where[] = "tags LIKE ?";
    $params[] = "%$tag%";
}

$where_sql = implode(' AND ', $where);

// Get total
$stmt = $pdo->prepare("SELECT COUNT(*) FROM articles WHERE $where_sql");
$stmt->execute($params);
$total = $stmt->fetchColumn();

// Pagination
$pagination = paginate($total, $page);

// Get articles
$sql = "SELECT * FROM articles WHERE $where_sql ORDER BY published_at DESC, created_at DESC LIMIT {$pagination['per_page']} OFFSET {$pagination['offset']}";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$articles = $stmt->fetchAll();

// Get popular tags
$all_tags = [];
$stmt = $pdo->query("SELECT tags FROM articles WHERE status = 1 AND tags IS NOT NULL AND tags != ''");
while ($row = $stmt->fetch()) {
    $tags = explode(',', $row['tags']);
    foreach ($tags as $t) {
        $t = trim($t);
        if ($t) {
            $all_tags[$t] = ($all_tags[$t] ?? 0) + 1;
        }
    }
}
arsort($all_tags);
$popular_tags = array_slice($all_tags, 0, 10, true);

$page_title = 'Bài viết - ' . SITE_NAME;
$page_description = 'Tin tức, kiến thức và kinh nghiệm về trà, kinh doanh trà sữa';

include ROOT_PATH . '/includes/header.php';
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
                        <span class="text-gray-500">Bài viết</span>
                    </div>
                </li>
            </ol>
        </nav>
    </div>
</div>

<!-- Articles Section -->
<section class="py-12 bg-white">
    <div class="container mx-auto px-4">
        <div class="flex flex-col lg:flex-row gap-8">

            <!-- Main Content -->
            <div class="flex-1">
                <div class="mb-8">
                    <h1 class="text-3xl font-bold mb-4" style="color: <?= COLOR_PRIMARY ?>">
                        <?php if ($search): ?>
                        Kết quả tìm kiếm "<?= htmlspecialchars($search) ?>"
                        <?php elseif ($tag): ?>
                        Bài viết về: <?= htmlspecialchars($tag) ?>
                        <?php else: ?>
                        Bài viết mới nhất
                        <?php endif; ?>
                    </h1>
                    <p class="text-gray-600">Tìm thấy <?= $total ?> bài viết</p>
                </div>

                <?php if (empty($articles)): ?>
                <div class="text-center py-12">
                    <svg class="w-24 h-24 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <h3 class="text-xl font-semibold text-gray-700 mb-2">Không tìm thấy bài viết</h3>
                    <p class="text-gray-500">Thử thay đổi từ khóa tìm kiếm</p>
                </div>
                <?php else: ?>
                <div class="space-y-8">
                    <?php foreach ($articles as $article): ?>
                    <article class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-xl transition">
                        <div class="md:flex">
                            <div class="md:w-1/3">
                                <a href="/bai-viet/<?= $article['slug'] ?>">
                                    <?php if ($article['thumbnail']): ?>
                                    <img src="<?= $article['thumbnail'] ?>" alt="<?= $article['title'] ?>"
                                        class="w-full h-64 md:h-full object-cover">
                                    <?php else: ?>
                                    <div class="w-full h-64 md:h-full bg-gray-200 flex items-center justify-center">
                                        <svg class="w-16 h-16 text-gray-400" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                    </div>
                                    <?php endif; ?>
                                </a>
                            </div>

                            <div class="md:w-2/3 p-6">
                                <div class="flex items-center text-sm text-gray-500 mb-3">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    <?= format_date($article['published_at'] ?? $article['created_at']) ?>

                                    <?php if ($article['author']): ?>
                                    <span class="mx-2">•</span>
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                    <?= $article['author'] ?>
                                    <?php endif; ?>

                                    <span class="mx-2">•</span>
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                    <?= number_format($article['view_count']) ?> lượt xem
                                </div>

                                <h2 class="text-2xl font-bold mb-3">
                                    <a href="/bai-viet/<?= $article['slug'] ?>" class="hover:text-green-600 transition">
                                        <?= $article['title'] ?>
                                    </a>
                                </h2>

                                <?php if ($article['excerpt']): ?>
                                <p class="text-gray-600 mb-4 line-clamp-3"><?= $article['excerpt'] ?></p>
                                <?php endif; ?>

                                <div class="flex items-center justify-between">
                                    <?php if ($article['tags']): ?>
                                    <div class="flex flex-wrap gap-2">
                                        <?php foreach (array_slice(explode(',', $article['tags']), 0, 3) as $t): ?>
                                        <a href="/bai-viet?tag=<?= urlencode(trim($t)) ?>"
                                            class="inline-block bg-gray-100 text-gray-600 text-xs px-2 py-1 rounded hover:bg-gray-200">
                                            #<?= trim($t) ?>
                                        </a>
                                        <?php endforeach; ?>
                                    </div>
                                    <?php endif; ?>

                                    <a href="/bai-viet/<?= $article['slug'] ?>"
                                        class="text-green-600 hover:text-green-700 font-semibold flex items-center">
                                        Đọc tiếp
                                        <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 5l7 7-7 7" />
                                        </svg>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </article>
                    <?php endforeach; ?>
                </div>

                <!-- Pagination -->
                <?php if ($pagination['total_pages'] > 1): ?>
                <div class="flex justify-center mt-8">
                    <nav class="flex space-x-2">
                        <?php if ($pagination['current_page'] > 1): ?>
                        <a href="?page=<?= $pagination['current_page'] - 1 ?><?= $search ? '&search='.urlencode($search) : '' ?><?= $tag ? '&tag='.urlencode($tag) : '' ?>"
                            class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                            ← Trước
                        </a>
                        <?php endif; ?>

                        <?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?>
                        <?php if ($i === $pagination['current_page']): ?>
                        <span class="px-4 py-2 bg-green-600 text-white rounded-lg"><?= $i ?></span>
                        <?php elseif ($i === 1 || $i === $pagination['total_pages'] || abs($i - $pagination['current_page']) <= 2): ?>
                        <a href="?page=<?= $i ?><?= $search ? '&search='.urlencode($search) : '' ?><?= $tag ? '&tag='.urlencode($tag) : '' ?>"
                            class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                            <?= $i ?>
                        </a>
                        <?php elseif (abs($i - $pagination['current_page']) === 3): ?>
                        <span class="px-2">...</span>
                        <?php endif; ?>
                        <?php endfor; ?>

                        <?php if ($pagination['current_page'] < $pagination['total_pages']): ?>
                        <a href="?page=<?= $pagination['current_page'] + 1 ?><?= $search ? '&search='.urlencode($search) : '' ?><?= $tag ? '&tag='.urlencode($tag) : '' ?>"
                            class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                            Sau →
                        </a>
                        <?php endif; ?>
                    </nav>
                </div>
                <?php endif; ?>
                <?php endif; ?>
            </div>

            <!-- Sidebar -->
            <aside class="lg:w-80 flex-shrink-0">
                <!-- Search -->
                <div class="bg-white rounded-lg shadow-md p-6 mb-6 sticky top-24">
                    <h3 class="text-xl font-bold mb-4">Tìm kiếm</h3>
                    <form method="GET" action="">
                        <div class="flex gap-2">
                            <input type="text" name="search" value="<?= htmlspecialchars($search) ?>"
                                placeholder="Tìm bài viết..."
                                class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                            <button type="submit"
                                class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Popular Tags -->
                <?php if (!empty($popular_tags)): ?>
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-xl font-bold mb-4">Tags phổ biến</h3>
                    <div class="flex flex-wrap gap-2">
                        <?php foreach ($popular_tags as $t => $count): ?>
                        <a href="/bai-viet?tag=<?= urlencode($t) ?>"
                            class="inline-block bg-gray-100 text-gray-700 px-3 py-1 rounded-lg hover:bg-green-100 hover:text-green-700 transition">
                            #<?= $t ?> <span class="text-xs text-gray-500">(<?= $count ?>)</span>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </aside>
        </div>
    </div>
</section>

<?php include ROOT_PATH . '/includes/footer.php'; ?>