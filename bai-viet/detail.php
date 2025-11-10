<?php
require_once __DIR__ . '/../config.php';

$slug = $_GET['slug'] ?? '';
$stmt = $pdo->prepare("SELECT * FROM articles WHERE slug = ? AND status = 1");
$stmt->execute([$slug]);
$article = $stmt->fetch();

if (!$article) {
    header('Location: /bai-viet');
    exit;
}

// Update view count
$pdo->prepare("UPDATE articles SET view_count = view_count + 1 WHERE id = ?")->execute([$article['id']]);

// Get related articles (same tags)
$related = [];
if ($article['tags']) {
    $tags = explode(',', $article['tags']);
    $first_tag = trim($tags[0]);
    $stmt = $pdo->prepare("SELECT * FROM articles WHERE tags LIKE ? AND id != ? AND status = 1 ORDER BY RAND() LIMIT 3");
    $stmt->execute(["%$first_tag%", $article['id']]);
    $related = $stmt->fetchAll();
}

$page_title = $article['meta_title'] ?: $article['title'] . ' - ' . SITE_NAME;
$page_description = $article['meta_description'] ?: $article['excerpt'];
$page_og_image = $article['thumbnail'];

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
                        <a href="/bai-viet" class="text-gray-700 hover:text-green-600">Bài viết</a>
                    </div>
                </li>
                <li>
                    <div class="flex items-center">
                        <svg class="w-3 h-3 text-gray-400 mx-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                                clip-rule="evenodd" />
                        </svg>
                        <span class="text-gray-500 line-clamp-1"><?= $article['title'] ?></span>
                    </div>
                </li>
            </ol>
        </nav>
    </div>
</div>

<!-- Article Content -->
<article class="py-12 bg-white">
    <div class="container mx-auto px-4">
        <div class="max-w-4xl mx-auto">

            <!-- Header -->
            <header class="mb-8">
                <h1 class="text-4xl md:text-5xl font-bold mb-4"><?= $article['title'] ?></h1>

                <div class="flex flex-wrap items-center gap-4 text-gray-600 mb-6">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <?= format_date($article['published_at'] ?? $article['created_at']) ?>
                    </div>

                    <?php if ($article['author']): ?>
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        <?= $article['author'] ?>
                    </div>
                    <?php endif; ?>

                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        <?= number_format($article['view_count']) ?> lượt xem
                    </div>
                </div>

                <?php if ($article['tags']): ?>
                <div class="flex flex-wrap gap-2">
                    <?php foreach (explode(',', $article['tags']) as $tag): ?>
                    <a href="/bai-viet?tag=<?= urlencode(trim($tag)) ?>"
                        class="inline-block bg-green-100 text-green-700 px-3 py-1 rounded-full text-sm font-semibold hover:bg-green-200 transition">
                        #<?= trim($tag) ?>
                    </a>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </header>

            <!-- Featured Image -->
            <?php if ($article['thumbnail']): ?>
            <div class="mb-8">
                <img src="<?= $article['thumbnail'] ?>" alt="<?= $article['title'] ?>"
                    class="w-full rounded-lg shadow-lg">
            </div>
            <?php endif; ?>

            <!-- Excerpt -->
            <?php if ($article['excerpt']): ?>
            <div class="text-xl text-gray-700 mb-8 p-6 bg-gray-50 rounded-lg border-l-4 border-green-600">
                <?= $article['excerpt'] ?>
            </div>
            <?php endif; ?>

            <!-- Content -->
            <div class="article-content">
                <?= $article['content'] ?>
            </div>

            <!-- Share Buttons -->
            <div class="border-t border-b py-6 mb-8">
                <h3 class="text-lg font-bold mb-4">Chia sẻ bài viết</h3>
                <div class="flex gap-3">
                    <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode(SITE_URL . '/bai-viet/' . $article['slug']) ?>"
                        target="_blank"
                        class="flex items-center justify-center w-10 h-10 bg-blue-600 text-white rounded-full hover:bg-blue-700 transition">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                            <path
                                d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z" />
                        </svg>
                    </a>

                    <button
                        onclick="navigator.share({title: '<?= addslashes($article['title']) ?>', url: window.location.href})"
                        class="flex items-center justify-center w-10 h-10 bg-gray-600 text-white rounded-full hover:bg-gray-700 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Author Info -->
            <?php if ($article['author']): ?>
            <div class="bg-gray-50 rounded-lg p-6 mb-12">
                <div class="flex items-start">
                    <div
                        class="w-16 h-16 bg-green-600 rounded-full flex items-center justify-center mr-4 flex-shrink-0">
                        <span class="text-white font-bold text-2xl">
                            <?= mb_substr($article['author'], 0, 1) ?>
                        </span>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold mb-2">Tác giả: <?= $article['author'] ?></h3>
                        <p class="text-gray-600">Biên tập viên tại <?= SITE_NAME ?></p>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Related Articles -->
            <?php if (!empty($related)): ?>
            <div>
                <h2 class="text-2xl font-bold mb-6">Bài viết liên quan</h2>
                <div class="grid md:grid-cols-3 gap-6">
                    <?php foreach ($related as $rel): ?>
                    <article class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-xl transition">
                        <a href="/bai-viet/<?= $rel['slug'] ?>">
                            <?php if ($rel['thumbnail']): ?>
                            <img src="<?= $rel['thumbnail'] ?>" alt="<?= $rel['title'] ?>"
                                class="w-full h-48 object-cover">
                            <?php else: ?>
                            <div class="w-full h-48 bg-gray-200 flex items-center justify-center">
                                <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            </div>
                            <?php endif; ?>
                        </a>
                        <div class="p-4">
                            <h3 class="font-bold text-lg mb-2 line-clamp-2">
                                <a href="/bai-viet/<?= $rel['slug'] ?>" class="hover:text-green-600">
                                    <?= $rel['title'] ?>
                                </a>
                            </h3>
                            <p class="text-sm text-gray-500"><?= format_date($rel['created_at']) ?></p>
                        </div>
                    </article>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</article>

<style>
.article-content p,
.article-content h1,
.article-content h2,
.article-content h3,
.article-content h4,
.article-content h5,
.article-content h6,
.article-content blockquote,
.article-content figure {
    text-align: inherit;
    /* để style inline CKEditor có hiệu lực */
}

.article-content figure {
    display: block;
    width: 100%;
    margin: 0;
    text-align: inherit;
    /* tôn trọng text-align inline */
}

.article-content figure img {
    max-width: 100%;
    height: auto;
    display: inline-block;
    /* để alignment của figure tác dụng */
}

.article-content * {
    margin: 0;
    padding: 0;
}
</style>

<?php include ROOT_PATH . '/includes/footer.php'; ?>