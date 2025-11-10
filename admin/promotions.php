<?php
require_once '../config.php';
require_once 'includes/auth.php';

// Check admin authentication
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$page_title = 'Quản lý Chương trình Khuyến mãi';

// Handle Delete
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = $pdo->prepare("DELETE FROM promotions WHERE id = ?");
    if ($stmt->execute([$id])) {
        $_SESSION['success'] = 'Xóa chương trình thành công!';
    } else {
        $_SESSION['error'] = 'Có lỗi xảy ra khi xóa!';
    }
    header('Location: promotions.php');
    exit;
}

// Handle Add/Edit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'] ?? null;
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $discount_text = trim($_POST['discount_text']);
    $start_date = $_POST['start_date'];
    $countdown_end = $_POST['countdown_end'];
    $button_text = trim($_POST['button_text']);
    $button_link = trim($_POST['button_link']);
    $status = isset($_POST['status']) ? 1 : 0;
    $sort_order = (int)$_POST['sort_order'];

    // Handle image upload
    $banner_image = $_POST['existing_banner_image'] ?? '';
    if (isset($_FILES['banner_image']) && $_FILES['banner_image']['error'] == 0) {
        $upload_dir = '../uploads/promotions/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $file_extension = pathinfo($_FILES['banner_image']['name'], PATHINFO_EXTENSION);
        $new_filename = 'promotion_' . time() . '_' . rand(1000, 9999) . '.' . $file_extension;
        $upload_path = $upload_dir . $new_filename;

        if (move_uploaded_file($_FILES['banner_image']['tmp_name'], $upload_path)) {
            $banner_image = '/uploads/promotions/' . $new_filename;

            // Delete old image if exists
            if (!empty($_POST['existing_banner_image']) && file_exists('..' . $_POST['existing_banner_image'])) {
                unlink('..' . $_POST['existing_banner_image']);
            }
        }
    }

    if ($id) {
        // Update
        $stmt = $pdo->prepare("
            UPDATE promotions SET
                title = ?,
                description = ?,
                discount_text = ?,
                start_date = ?,
                countdown_end = ?,
                banner_image = ?,
                button_text = ?,
                button_link = ?,
                status = ?,
                sort_order = ?
            WHERE id = ?
        ");
        $result = $stmt->execute([
            $title, $description, $discount_text, $start_date, $countdown_end,
            $banner_image, $button_text, $button_link, $status, $sort_order, $id
        ]);

        if ($result) {
            $_SESSION['success'] = 'Cập nhật chương trình thành công!';
        } else {
            $_SESSION['error'] = 'Có lỗi xảy ra khi cập nhật!';
        }
    } else {
        // Insert
        $stmt = $pdo->prepare("
            INSERT INTO promotions (title, description, discount_text, start_date, countdown_end, banner_image, button_text, button_link, status, sort_order)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $result = $stmt->execute([
            $title, $description, $discount_text, $start_date, $countdown_end,
            $banner_image, $button_text, $button_link, $status, $sort_order
        ]);

        if ($result) {
            $_SESSION['success'] = 'Thêm chương trình mới thành công!';
        } else {
            $_SESSION['error'] = 'Có lỗi xảy ra khi thêm mới!';
        }
    }

    header('Location: promotions.php');
    exit;
}

// Get promotion for editing
$edit_promotion = null;
if (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = $pdo->prepare("SELECT * FROM promotions WHERE id = ?");
    $stmt->execute([$id]);
    $edit_promotion = $stmt->fetch();
}

// Get all promotions
$stmt = $pdo->query("SELECT * FROM promotions ORDER BY sort_order ASC, created_at DESC");
$promotions = $stmt->fetchAll();

include 'includes/header.php';
?>

<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><?= $page_title ?></h1>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#promotionModal" onclick="clearForm()">
            <i class="fas fa-plus"></i> Thêm chương trình mới
        </button>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <?= $_SESSION['success'] ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['success']); endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show">
        <?= $_SESSION['error'] ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['error']); endif; ?>

    <!-- Promotions Table -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th width="80">ID</th>
                            <th>Banner</th>
                            <th>Tiêu đề</th>
                            <th>Giảm giá</th>
                            <th>Thời gian</th>
                            <th>Trạng thái</th>
                            <th width="150">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($promotions)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-4">Chưa có chương trình khuyến mãi nào</td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($promotions as $promo):
                            $now = new DateTime();
                            $start = new DateTime($promo['start_date']);
                            $end = new DateTime($promo['countdown_end']);

                            if ($now >= $start && $now <= $end) {
                                $time_status = '<span class="badge bg-success">Đang diễn ra</span>';
                            } elseif ($now < $start) {
                                $days_until = $now->diff($start)->days;
                                $time_status = '<span class="badge bg-info">Còn ' . $days_until . ' ngày</span>';
                            } else {
                                $time_status = '<span class="badge bg-secondary">Đã kết thúc</span>';
                            }
                        ?>
                        <tr>
                            <td><?= $promo['id'] ?></td>
                            <td>
                                <?php if ($promo['banner_image']): ?>
                                <img src="<?= htmlspecialchars($promo['banner_image']) ?>"
                                     alt="Banner" class="img-thumbnail" style="max-width: 100px;">
                                <?php else: ?>
                                <span class="text-muted">Không có</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <strong><?= htmlspecialchars($promo['title']) ?></strong>
                                <?php if ($promo['description']): ?>
                                <br><small class="text-muted"><?= htmlspecialchars(substr($promo['description'], 0, 50)) ?>...</small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge bg-danger fs-6"><?= htmlspecialchars($promo['discount_text']) ?></span>
                            </td>
                            <td>
                                <small>
                                    <strong>Bắt đầu:</strong> <?= date('d/m/Y H:i', strtotime($promo['start_date'])) ?><br>
                                    <strong>Kết thúc:</strong> <?= date('d/m/Y H:i', strtotime($promo['countdown_end'])) ?>
                                </small>
                                <br>
                                <?= $time_status ?>
                            </td>
                            <td>
                                <?php if ($promo['status']): ?>
                                <span class="badge bg-success">Hoạt động</span>
                                <?php else: ?>
                                <span class="badge bg-secondary">Tắt</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-info" onclick="editPromotion(<?= htmlspecialchars(json_encode($promo)) ?>)">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <a href="?action=delete&id=<?= $promo['id'] ?>"
                                   class="btn btn-sm btn-danger"
                                   onclick="return confirm('Bạn có chắc muốn xóa chương trình này?')">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add/Edit Modal -->
<div class="modal fade" id="promotionModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Thêm chương trình mới</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="promotion_id">
                    <input type="hidden" name="existing_banner_image" id="existing_banner_image">

                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label class="form-label">Tiêu đề <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="title" id="title" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Thứ tự</label>
                            <input type="number" class="form-control" name="sort_order" id="sort_order" value="0">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Mô tả</label>
                        <textarea class="form-control" name="description" id="description" rows="2"></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Text giảm giá <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="discount_text" id="discount_text"
                                   placeholder="VD: GIẢM 50%" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Banner Image</label>
                            <input type="file" class="form-control" name="banner_image" id="banner_image" accept="image/*">
                            <div id="current_image_preview" class="mt-2"></div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Ngày bắt đầu <span class="text-danger">*</span></label>
                            <input type="datetime-local" class="form-control" name="start_date" id="start_date" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Ngày kết thúc <span class="text-danger">*</span></label>
                            <input type="datetime-local" class="form-control" name="countdown_end" id="countdown_end" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Text nút</label>
                            <input type="text" class="form-control" name="button_text" id="button_text"
                                   value="Mua ngay" placeholder="Mua ngay">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Link nút</label>
                            <input type="text" class="form-control" name="button_link" id="button_link"
                                   value="/san-pham" placeholder="/san-pham">
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="status" id="status" value="1" checked>
                            <label class="form-check-label" for="status">
                                Kích hoạt chương trình
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-primary">Lưu chương trình</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function clearForm() {
    document.getElementById('modalTitle').textContent = 'Thêm chương trình mới';
    document.getElementById('promotion_id').value = '';
    document.getElementById('existing_banner_image').value = '';
    document.getElementById('title').value = '';
    document.getElementById('description').value = '';
    document.getElementById('discount_text').value = '';
    document.getElementById('start_date').value = '';
    document.getElementById('countdown_end').value = '';
    document.getElementById('button_text').value = 'Mua ngay';
    document.getElementById('button_link').value = '/san-pham';
    document.getElementById('status').checked = true;
    document.getElementById('sort_order').value = '0';
    document.getElementById('current_image_preview').innerHTML = '';
}

function editPromotion(promo) {
    document.getElementById('modalTitle').textContent = 'Chỉnh sửa chương trình';
    document.getElementById('promotion_id').value = promo.id;
    document.getElementById('existing_banner_image').value = promo.banner_image || '';
    document.getElementById('title').value = promo.title;
    document.getElementById('description').value = promo.description || '';
    document.getElementById('discount_text').value = promo.discount_text;

    // Format datetime for input
    if (promo.start_date) {
        const startDate = new Date(promo.start_date);
        document.getElementById('start_date').value = formatDateTimeLocal(startDate);
    }
    if (promo.countdown_end) {
        const endDate = new Date(promo.countdown_end);
        document.getElementById('countdown_end').value = formatDateTimeLocal(endDate);
    }

    document.getElementById('button_text').value = promo.button_text || 'Mua ngay';
    document.getElementById('button_link').value = promo.button_link || '/san-pham';
    document.getElementById('status').checked = promo.status == 1;
    document.getElementById('sort_order').value = promo.sort_order || 0;

    // Show current image
    if (promo.banner_image) {
        document.getElementById('current_image_preview').innerHTML =
            '<img src="' + promo.banner_image + '" class="img-thumbnail" style="max-width: 200px;"><br><small class="text-muted">Ảnh hiện tại</small>';
    }

    // Show modal
    new bootstrap.Modal(document.getElementById('promotionModal')).show();
}

function formatDateTimeLocal(date) {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    const hours = String(date.getHours()).padStart(2, '0');
    const minutes = String(date.getMinutes()).padStart(2, '0');
    return `${year}-${month}-${day}T${hours}:${minutes}`;
}
</script>

<?php include 'includes/footer.php'; ?>
