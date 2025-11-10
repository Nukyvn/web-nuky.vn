<?php
require_once __DIR__ . '/../config.php';

// Get filters
$search = $_GET['search'] ?? '';
$page = max(1, intval($_GET['page'] ?? 1));

// Build query
$where = ['status = 1'];
$params = [];

if ($search) {
    $where[] = "(title LIKE ? OR description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$where_sql = implode(' AND ', $where);

// Get total
$stmt = $pdo->prepare("SELECT COUNT(*) FROM videos WHERE $where_sql");
$stmt->execute($params);
$total = $stmt->fetchColumn();

// Pagination
$pagination = paginate($total, $page);

// Get videos
$sql = "SELECT * FROM videos WHERE $where_sql ORDER BY created_at DESC LIMIT {$pagination['per_page']} OFFSET {$pagination['offset']}";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$videos = $stmt->fetchAll();

$page_title = 'Video - ' . SITE_NAME;
$page_description = 'Thư viện video về trà, quy trình sản xuất và hướng dẫn pha chế';

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
                        <span class="text-gray-500">Video</span>
                    </div>
                </li>
            </ol>
        </nav>
    </div>
</div>

<!-- Videos Section -->
<section class="py-12 bg-white">
    <div class="container mx-auto px-4">

        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold mb-4" style="color: <?= COLOR_PRIMARY ?>">
                <?php if ($search): ?>
                Kết quả tìm kiếm "<?= htmlspecialchars($search) ?>"
                <?php else: ?>
                Thư viện Video
                <?php endif; ?>
            </h1>
            <p class="text-gray-600">Tìm thấy <?= $total ?> video</p>
        </div>

        <!-- Search -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-8">
            <form method="GET" class="flex gap-4">
                <input type="text" name="search" value="<?= htmlspecialchars($search) ?>"
                    placeholder="Tìm kiếm video..."
                    class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                <button type="submit"
                    class="bg-green-600 text-white px-8 py-2 rounded-lg hover:bg-green-700 transition font-semibold">
                    Tìm kiếm
                </button>
                <?php if ($search): ?>
                <a href="/video" class="px-8 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                    Reset
                </a>
                <?php endif; ?>
            </form>
        </div>

        <?php if (empty($videos)): ?>
        <div class="text-center py-12">
            <svg class="w-24 h-24 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
            </svg>
            <h3 class="text-xl font-semibold text-gray-700 mb-2">Không tìm thấy video</h3>
            <p class="text-gray-500">Thử thay đổi từ khóa tìm kiếm</p>
        </div>
        <?php else: ?>
        <!-- Videos Grid -->
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($videos as $video): ?>
            <?php
        // Xác định thumbnail
        $thumb = $video['thumbnail'];
        if (empty($thumb) && $video['video_type'] === 'youtube' && !empty($video['video_url'])) {
            preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/)([^\&\?]+)/', $video['video_url'], $matches);
            $id_youtube = $matches[1] ?? null;
            if ($id_youtube) {
                $thumb = "https://img.youtube.com/vi/$id_youtube/hqdefault.jpg";
            }
        }
        ?>
            <article class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-xl transition">
                <a href="/video/<?= $video['slug'] ?>" class="block relative">
                    <div class="relative h-56">
                        <?php if ($thumb): ?>
                        <img src="<?= $thumb ?>" alt="<?= htmlspecialchars($video['title']) ?>"
                            class="w-full h-full object-cover">
                        <?php else: ?>
                        <div class="flex items-center justify-center h-full bg-gray-100">
                            <svg class="w-16 h-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <?php endif; ?>

                        <!-- Play Button Overlay -->
                        <div class="absolute inset-0 flex items-center justify-center">
                            <div class="w-16 h-16 bg-white bg-opacity-90 rounded-full flex items-center justify-center">
                                <svg class="w-8 h-8 text-green-600 ml-1" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M8 5v14l11-7z" />
                                </svg>
                            </div>
                        </div>

                        <?php if ($video['duration']): ?>
                        <div
                            class="absolute bottom-2 right-2 bg-black bg-opacity-80 text-white px-2 py-1 rounded text-xs font-semibold">
                            <?= htmlspecialchars($video['duration']) ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </a>

                <div class="p-4">
                    <h2 class="font-bold text-lg mb-2 line-clamp-2">
                        <a href="/video/<?= $video['slug'] ?>" class="hover:text-green-600">
                            <?= htmlspecialchars($video['title']) ?>
                        </a>
                    </h2>

                    <?php if ($video['description']): ?>
                    <p class="text-sm text-gray-600 mb-3 line-clamp-2"><?= htmlspecialchars($video['description']) ?>
                    </p>
                    <?php endif; ?>

                    <div class="flex items-center justify-between text-sm text-gray-500">
                        <span class="flex items-center">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            <?= number_format($video['view_count']) ?> lượt xem
                        </span>
                        <span><?= time_ago($video['created_at']) ?></span>
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
                <a href="?page=<?= $pagination['current_page'] - 1 ?><?= $search ? '&search='.urlencode($search) : '' ?>"
                    class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                    ← Trước
                </a>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?>
                <?php if ($i === $pagination['current_page']): ?>
                <span class="px-4 py-2 bg-green-600 text-white rounded-lg"><?= $i ?></span>
                <?php elseif ($i === 1 || $i === $pagination['total_pages'] || abs($i - $pagination['current_page']) <= 2): ?>
                <a href="?page=<?= $i ?><?= $search ? '&search='.urlencode($search) : '' ?>"
                    class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                    <?= $i ?>
                </a>
                <?php elseif (abs($i - $pagination['current_page']) === 3): ?>
                <span class="px-2">...</span>
                <?php endif; ?>
                <?php endfor; ?>

                <?php if ($pagination['current_page'] < $pagination['total_pages']): ?>
                <a href="?page=<?= $pagination['current_page'] + 1 ?><?= $search ? '&search='.urlencode($search) : '' ?>"
                    class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                    Sau →
                </a>
                <?php endif; ?>
            </nav>
        </div>
        <?php endif; ?>
        <?php endif; ?>
    </div>
</section>

<?php include ROOT_PATH . '/includes/footer.php'; ?>