<?php
// upload_image.php
require_once '../config.php';
require_login(); // đảm bảo user đã đăng nhập

// Thư mục lưu ảnh
$uploadDir = __DIR__ . '/../uploads/images/';

// Tạo thư mục nếu chưa tồn tại
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// Kiểm tra file upload
if (!empty($_FILES['upload']['name'])) {
    $file = $_FILES['upload'];
    $filename = basename($file['name']);
    
    // Tạo tên file duy nhất tránh trùng
    $ext = pathinfo($filename, PATHINFO_EXTENSION);
    $base = pathinfo($filename, PATHINFO_FILENAME);
    $newFilename = $base . '-' . time() . '.' . $ext;
    $targetFile = $uploadDir . $newFilename;

    // Di chuyển file lên server
    if (move_uploaded_file($file['tmp_name'], $targetFile)) {
        // Trả về JSON chuẩn CKEditor
        echo json_encode([
            'uploaded' => 1,
            'fileName' => $newFilename,
            'url' => '/uploads/images/' . $newFilename
        ]);
    } else {
        echo json_encode([
            'uploaded' => 0,
            'error' => [
                'message' => 'Không thể upload file, vui lòng thử lại.'
            ]
        ]);
    }
} else {
    echo json_encode([
        'uploaded' => 0,
        'error' => [
            'message' => 'Không có file được gửi.'
        ]
    ]);
}