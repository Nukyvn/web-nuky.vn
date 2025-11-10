<?php
require_once '../config.php';
require_login();

// Handle delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $pdo->prepare("DELETE FROM videos WHERE id = ?")->execute([$id]);
    header('Location: /admin/videos.php?msg=deleted');
    exit;
}

// Handle add/edit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id'] ?? 0);
    $data = [
        'title' => clean_input($_POST['title'] ?? ''),
        'slug' => '',
        'description' => clean_input($_POST['description'] ?? ''),
        'video_url' => clean_input($_POST['video_url'] ?? ''),
        'video_type' => clean_input($_POST['video_type'] ?? 'upload'),
        'duration' => clean_input($_POST['duration'] ?? ''),
        'status' => isset($_POST['status']) ? 1 : 0
    ];

    $data['slug'] = unique_slug('videos', create_slug($data['title']), $id);

    // Handle thumbnail
    $thumbnail = '';
    if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] === UPLOAD_ERR_OK) {
        $result = upload_file($_FILES['thumbnail'], 'videos');
        if ($result['success']) {
            $thumbnail = $result['url'];
        }
    } elseif ($id) {
        $stmt = $pdo->prepare("SELECT thumbnail, video_type, video_url FROM videos WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $thumbnail = $row['thumbnail'];

        // Nếu là YouTube và thumbnail rỗng thì tự tạo
        if (empty($thumbnail) && $row['video_type'] === 'youtube' && !empty($row['video_url'])) {
            preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/)([^\&\?]+)/', $row['video_url'], $matches);
            $id_youtube = $matches[1] ?? null;
            if ($id_youtube) {
                $thumbnail = "https://img.youtube.com/vi/$id_youtube/hqdefault.jpg";
            }
        }
    }

    // Handle video upload
    if ($data['video_type'] === 'upload' && isset($_FILES['video_file']) && $_FILES['video_file']['error'] === UPLOAD_ERR_OK) {
        $result = upload_file($_FILES['video_file'], 'videos');
        if ($result['success']) {
            $data['video_url'] = $result['url'];
        }
    }

    try {
        if ($id) {
            $sql = "UPDATE videos SET title = ?, slug = ?, description = ?, video_url = ?, video_type = ?, thumbnail = ?, duration = ?, status = ? WHERE id = ?";
            $pdo->prepare($sql)->execute([$data['title'], $data['slug'], $data['description'], $data['video_url'], $data['video_type'], $thumbnail, $data['duration'], $data['status'], $id]);
        } else {
            $sql = "INSERT INTO videos (title, slug, description, video_url, video_type, thumbnail, duration, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $pdo->prepare($sql)->execute([$data['title'], $data['slug'], $data['description'], $data['video_url'], $data['video_type'], $thumbnail, $data['duration'], $data['status']]);
        }

        header('Location: /admin/videos.php?msg=saved');
        exit;
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Get videos
$videos = $pdo->query("SELECT * FROM videos ORDER BY created_at DESC")->fetchAll();

$page_title = 'Quản lý Video';

include 'includes/header.php';
?>

<div class="flex justify-between items-center mb-6">
    <h1 class="text-3xl font-bold text-gray-900">Video</h1>
    <button onclick="openModal()"
        class="bg-green-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-green-700 transition flex items-center">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        Thêm video
    </button>
</div>

<?php if (isset($_GET['msg'])): ?>
<div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
    <?php
        $messages = [
            'saved' => 'Đã lưu video thành công!',
            'deleted' => 'Đã xóa video thành công!'
        ];
        echo $messages[$_GET['msg']] ?? '';
        ?>
</div>
<?php endif; ?>

<!-- Videos Grid -->
<div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
    <?php if (empty($videos)): ?>
    <div class="col-span-full bg-white rounded-lg shadow-md p-12 text-center text-gray-500">
        <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
        </svg>
        <p class="text-lg">Chưa có video nào</p>
    </div>
    <?php else: ?>
    <?php foreach ($videos as $video): ?>
    <?php $thumb = get_video_thumbnail($video['video_url'], $video['thumbnail'], $video['video_type']); ?>
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="relative h-48">
            <img src="<?= $thumb ?>" alt="<?= htmlspecialchars($video['title']) ?>" class="w-full h-full object-cover">

            <!-- Play overlay -->
            <div class="absolute inset-0 flex items-center justify-center">
                <div class="w-16 h-16 bg-white bg-opacity-90 rounded-full flex items-center justify-center">
                    <svg class="w-8 h-8 text-green-600 ml-1" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M8 5v14l11-7z" />
                    </svg>
                </div>
            </div>

            <!-- Badges -->
            <div class="absolute top-2 right-2 flex gap-2">
                <?php
                    $type_badges = [
                        'upload' => ['bg-blue-600', 'Upload'],
                        'youtube' => ['bg-red-600', 'YouTube'],
                        'vimeo' => ['bg-cyan-600', 'Vimeo']
                    ];
                    $badge = $type_badges[$video['video_type']] ?? ['bg-gray-600', $video['video_type']];
                ?>
                <span
                    class="<?= $badge[0] ?> text-white px-2 py-1 rounded text-xs font-semibold"><?= $badge[1] ?></span>
                <?php if ($video['status']): ?>
                <span class="bg-green-600 text-white px-2 py-1 rounded text-xs font-semibold">Hiển thị</span>
                <?php else: ?>
                <span class="bg-gray-600 text-white px-2 py-1 rounded text-xs font-semibold">Ẩn</span>
                <?php endif; ?>
            </div>

            <?php if ($video['duration']): ?>
            <div
                class="absolute bottom-2 right-2 bg-black bg-opacity-80 text-white px-2 py-1 rounded text-xs font-semibold">
                <?= $video['duration'] ?>
            </div>
            <?php endif; ?>
        </div>

        <div class="p-4">
            <h3 class="font-bold text-lg mb-2 line-clamp-2"><?= htmlspecialchars($video['title']) ?></h3>
            <?php if ($video['description']): ?>
            <p class="text-sm text-gray-600 mb-3 line-clamp-2"><?= htmlspecialchars($video['description']) ?></p>
            <?php endif; ?>

            <div class="flex items-center justify-between text-sm text-gray-600 mb-4">
                <span><?= number_format($video['view_count']) ?> lượt xem</span>
                <span><?= time_ago($video['created_at']) ?></span>
            </div>

            <div class="flex gap-2">
                <button onclick='editVideo(<?= json_encode($video) ?>)'
                    class="flex-1 bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700 transition text-sm font-semibold">
                    Sửa
                </button>
                <a href="?delete=<?= $video['id'] ?>" onclick="return confirmDelete('Bạn có chắc muốn xóa video này?')"
                    class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition text-sm font-semibold">
                    Xóa
                </a>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>
</div>


<!-- Modal Form -->
<div id="videoModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-lg max-w-2xl w-full max-h-[90vh] overflow-y-auto">
        <div class="p-6 border-b">
            <div class="flex items-center justify-between">
                <h2 class="text-2xl font-bold" id="modalTitle">Thêm video</h2>
                <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>

        <form method="POST" enctype="multipart/form-data" class="p-6 space-y-4" x-data="videoForm()">
            <input type="hidden" name="id" id="videoId">

            <div>
                <label class="block text-sm font-semibold mb-2">Tiêu đề <span class="text-red-500">*</span></label>
                <input type="text" name="title" id="videoTitle" required
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
            </div>

            <div>
                <label class="block text-sm font-semibold mb-2">Mô tả</label>
                <textarea name="description" id="videoDescription" rows="3"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500"></textarea>
            </div>

            <div>
                <label class="block text-sm font-semibold mb-2">Loại video</label>
                <select name="video_type" id="videoType" x-model="videoType"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                    <option value="upload">Upload file</option>
                    <option value="youtube">YouTube URL</option>
                    <option value="vimeo">Vimeo URL</option>
                </select>
            </div>

            <div x-show="videoType === 'upload'">
                <label class="block text-sm font-semibold mb-2">Upload video file</label>
                <input type="file" name="video_file" accept="video/*"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                <div id="currentVideo" class="mt-2"></div>
            </div>

            <div x-show="videoType !== 'upload'">
                <label class="block text-sm font-semibold mb-2">Video URL</label>
                <input type="url" name="video_url" id="videoUrl" placeholder="https://www.youtube.com/watch?v=..."
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
            </div>

            <div>
                <label class="block text-sm font-semibold mb-2">Thumbnail</label>
                <input type="file" name="thumbnail" accept="image/*">
                <p class="text-sm text-gray-500">Có thể bỏ trống, sẽ tự lấy thumbnail từ video URL nếu không upload</p>
                <div id="currentThumbnail" class="mt-2"></div>
            </div>

            <div>
                <label class="block text-sm font-semibold mb-2">Thời lượng</label>
                <input type="text" name="duration" id="videoDuration" placeholder="5:30"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
            </div>

            <div>
                <label class="flex items-center">
                    <input type="checkbox" name="status" id="videoStatus" value="1" checked
                        class="w-5 h-5 text-green-600 rounded">
                    <span class="ml-2 font-semibold">Hiển thị</span>
                </label>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t">
                <button type="button" onclick="closeModal()"
                    class="px-6 py-3 border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                    Hủy
                </button>
                <button type="submit"
                    class="bg-green-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-green-700 transition">
                    Lưu
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function videoForm() {
    return {
        videoType: 'upload'
    }
}

function getVideoThumbnailJS(url) {
    let id = null;
    let match = url.match(/(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/)([^\&\?]+)/);
    if (match) id = match[1].split('?')[0]; // loại bỏ query string
    return id ? `https://img.youtube.com/vi/${id}/hqdefault.jpg` : '/uploads/default-video.webp';
}


function openModal() {
    document.getElementById('modalTitle').textContent = 'Thêm video';
    document.getElementById('videoId').value = '';
    document.getElementById('videoTitle').value = '';
    document.getElementById('videoDescription').value = '';
    document.getElementById('videoType').value = 'upload';
    document.getElementById('videoUrl').value = '';
    document.getElementById('videoDuration').value = '';
    document.getElementById('videoStatus').checked = true;
    document.getElementById('currentVideo').innerHTML = '';
    document.getElementById('currentThumbnail').innerHTML = '';
    document.getElementById('videoModal').classList.remove('hidden');
}

function editVideo(video) {
    document.getElementById('modalTitle').textContent = 'Sửa video';
    document.getElementById('videoId').value = video.id;
    document.getElementById('videoTitle').value = video.title;
    document.getElementById('videoDescription').value = video.description || '';
    document.getElementById('videoType').value = video.video_type;
    document.getElementById('videoUrl').value = video.video_url || '';
    document.getElementById('videoDuration').value = video.duration || '';
    document.getElementById('videoStatus').checked = video.status == 1;

    if (video.video_url && video.video_type === 'upload') {
        document.getElementById('currentVideo').innerHTML =
            `<video src="${video.video_url}" class="h-24 rounded-lg" controls></video>`;
    } else {
        document.getElementById('currentVideo').innerHTML = '';
    }

    let thumb = video.thumbnail;
    if (!thumb && video.video_type === 'youtube' && video.video_url) {
        thumb = getVideoThumbnailJS(video.video_url);
    }
    if (thumb) {
        document.getElementById('currentThumbnail').innerHTML =
            `<img src="${thumb}" class="h-24 rounded-lg">`;
    } else {
        document.getElementById('currentThumbnail').innerHTML = '';
    }


    document.getElementById('videoModal').classList.remove('hidden');
}

function closeModal() {
    document.getElementById('videoModal').classList.add('hidden');
}

document.getElementById('videoModal')?.addEventListener('click', function(e) {
    if (e.target === this) closeModal();
});
document.getElementById('videoUrl').addEventListener('input', function() {
    if (document.getElementById('videoType').value === 'youtube') {
        let thumb = getVideoThumbnailJS(this.value);
        document.getElementById('currentThumbnail').innerHTML =
            `<img src="${thumb}" class="h-24 rounded-lg">`;
    }
});
</script>

<?php include 'includes/footer.php'; ?>