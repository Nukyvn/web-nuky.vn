<?php
require_once __DIR__ . '/../config.php';

$slug = $_GET['slug'] ?? '';
$stmt = $pdo->prepare("SELECT * FROM videos WHERE slug = ? AND status = 1");
$stmt->execute([$slug]);
$video = $stmt->fetch();

if (!$video) {
    header('Location: /video');
    exit;
}

// Update view count
$pdo->prepare("UPDATE videos SET view_count = view_count + 1 WHERE id = ?")->execute([$video['id']]);

// Get related videos
$stmt = $pdo->prepare("SELECT * FROM videos WHERE id != ? AND status = 1 ORDER BY RAND() LIMIT 4");
$stmt->execute([$video['id']]);
$related = $stmt->fetchAll();

$page_title = $video['title'] . ' - Video - ' . SITE_NAME;
$page_description = $video['description'] ?: $video['title'];
$page_og_image = $video['thumbnail'];



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
                        <a href="/video" class="text-gray-700 hover:text-green-600">Video</a>
                    </div>
                </li>
                <li>
                    <div class="flex items-center">
                        <svg class="w-3 h-3 text-gray-400 mx-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                                clip-rule="evenodd" />
                        </svg>
                        <span class="text-gray-500 line-clamp-1"><?= $video['title'] ?></span>
                    </div>
                </li>
            </ol>
        </nav>
    </div>
</div>

<!-- Video Content -->
<section class="py-12 bg-white">
    <div class="container mx-auto px-4">
        <div class="grid lg:grid-cols-3 gap-8">

            <!-- Main Video -->
            <div class="lg:col-span-2">
                <!-- Video Player -->
                <div class="">
                    <?php if ($video['video_type'] === 'youtube'): ?>
                    <?php
                        // Extract YouTube ID
                        preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/', $video['video_url'], $matches);
                        $youtube_id = $matches[1] ?? '';
                        ?>
                    <?php if ($youtube_id): ?>
                    <iframe width="100%" height="480" src="https://www.youtube.com/embed/<?= $youtube_id ?>"
                        frameborder="0"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                        allowfullscreen>
                    </iframe>
                    <?php endif; ?>

                    <?php elseif ($video['video_type'] === 'vimeo'): ?>
                    <?php
                        preg_match('/vimeo\.com\/(\d+)/', $video['video_url'], $matches);
                        $vimeo_id = $matches[1] ?? '';
                        ?>
                    <?php if ($vimeo_id): ?>
                    <iframe width="100%" height="480" src="https://player.vimeo.com/video/<?= $vimeo_id ?>"
                        frameborder="0" allow="autoplay; fullscreen; picture-in-picture" allowfullscreen>
                    </iframe>
                    <?php endif; ?>

                    <?php else: ?>
                    <!-- Uploaded video -->
                    <video width="100%" height="480" controls class="w-full">
                        <source src="<?= $video['video_url'] ?>" type="video/mp4">
                        Trình duyệt của bạn không hỗ trợ video tag.
                    </video>
                    <?php endif; ?>
                </div>

                <!-- Video Info -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h1 class="text-3xl font-bold mb-4"><?= $video['title'] ?></h1>

                    <div class="flex flex-wrap items-center gap-4 text-gray-600 mb-6 pb-6 border-b">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            <?= number_format($video['view_count']) ?> lượt xem
                        </div>

                        <div class="flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <?= format_date($video['created_at']) ?>
                        </div>

                        <?php if ($video['duration']): ?>
                        <div class="flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <?= $video['duration'] ?>
                        </div>
                        <?php endif; ?>
                    </div>

                    <?php if ($video['description']): ?>
                    <div class="mb-6">
                        <h3 class="text-xl font-bold mb-3">Mô tả</h3>
                        <p class="text-gray-700 whitespace-pre-line"><?= $video['description'] ?></p>
                    </div>
                    <?php endif; ?>

                    <!-- Share -->
                    <div class="border-t pt-6">
                        <h3 class="text-lg font-bold mb-4">Chia sẻ video</h3>
                        <div class="flex gap-3">
                            <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode(SITE_URL . '/video/' . $video['slug']) ?>"
                                target="_blank"
                                class="flex items-center justify-center w-10 h-10 bg-blue-600 text-white rounded-full hover:bg-blue-700 transition">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                    <path
                                        d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z" />
                                </svg>
                            </a>

                            <button
                                onclick="navigator.share({title: '<?= addslashes($video['title']) ?>', url: window.location.href})"
                                class="flex items-center justify-center w-10 h-10 bg-gray-600 text-white rounded-full hover:bg-gray-700 transition">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar - Related Videos -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-lg shadow-md p-6 sticky top-24">
                    <h3 class="text-xl font-bold mb-4">Video liên quan</h3>

                    <?php if (empty($related)): ?>
                    <p class="text-gray-500 text-center py-8">Chưa có video liên quan</p>
                    <?php else: ?>
                    <div class="space-y-4">
                        <?php foreach ($related as $rel): ?>
                        <?php
 $rel_thumb = $rel['thumbnail'];

if (empty($rel_thumb) && $rel['video_type'] === 'youtube' && !empty($rel['video_url'])) {
    preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/)([^\&\?]+)/', $rel['video_url'], $matches);
    $id_youtube = $matches[1] ?? null;
    if ($id_youtube) $rel_thumb = "https://img.youtube.com/vi/$id_youtube/hqdefault.jpg";
}
?>
                        <a href="/video/<?= $rel['slug'] ?>" class="flex gap-3 hover:bg-gray-50 p-2 rounded transition">
                            <div class="relative w-32 h-20 flex-shrink-0">
                                <?php if ($rel_thumb): ?>
                                <img src="<?= $rel_thumb ?>" alt="<?= htmlspecialchars($rel['title']) ?>"
                                    class="w-full h-full object-cover">
                                <?php else: ?>
                                <div class="flex items-center justify-center h-full">
                                    <svg class="w-8 h-8 text-gray-600" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <?php endif; ?>

                                <div class="absolute inset-0 flex items-center justify-center">
                                    <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M8 5v14l11-7z" />
                                    </svg>
                                </div>

                                <?php if ($rel['duration']): ?>
                                <div
                                    class="absolute bottom-1 right-1 bg-black bg-opacity-80 text-white px-1 py-0.5 rounded text-xs">
                                    <?= $rel['duration'] ?>
                                </div>
                                <?php endif; ?>
                            </div>

                            <div class="flex-1 min-w-0">
                                <h4 class="font-semibold text-sm line-clamp-2 mb-1">
                                    <?= htmlspecialchars($rel['title']) ?></h4>
                                <div class="text-xs text-gray-500">
                                    <span><?= number_format($rel['view_count']) ?> lượt xem</span>
                                </div>
                            </div>
                        </a>
                        <?php endforeach; ?>

                    </div>
                    <?php endif; ?>

                    <div class="mt-6 pt-6 border-t">
                        <a href="/video" class="block text-center text-green-600 font-semibold hover:text-green-700">
                            Xem tất cả video →
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include ROOT_PATH . '/includes/footer.php'; ?>