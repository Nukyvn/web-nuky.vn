<?php
// router.php - Router cho PHP Development Server

// Hàm này được sử dụng để kiểm tra xem một đường dẫn có phải là tài nguyên tĩnh (static) không.
// Nếu là static (CSS, JS, ảnh), server PHP sẽ xử lý, không cần routing.
if (preg_match('/\.(?:png|jpg|jpeg|gif|css|js|webp|ico|svg)$/', $_SERVER["REQUEST_URI"])) {
    return false;
}

// Lấy URI và chuẩn hóa nó
$request_uri = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');

// Nếu URI khớp với một file/thư mục đã tồn tại trong thư mục gốc (ví dụ: admin, uploads), 
// hãy để server PHP xử lý (tránh trường hợp truy cập thẳng admin/index.php mà không qua router)
if (file_exists($request_uri) && !is_dir($request_uri)) {
    return false;
}

// Tách URI thành các phần tử
$parts = explode('/', $request_uri);
$first_segment = $parts[0] ?? '';

// --- Bắt đầu Logic Routing ---

// 1. Trang chủ
if (empty($request_uri) || $request_uri === 'index.php') {
    require 'index.php';
    return true;
}
// 2. Admin Routes
if ($first_segment === 'admin') {
    
    // Lấy đường dẫn còn lại sau /admin/ (ví dụ: 'products', 'login', 'ajax/cart.php')
    $admin_path = substr($request_uri, 6); // Cắt bỏ 'admin/' (6 ký tự)

    // Nếu chỉ là /admin (hoặc /admin/) -> trỏ về admin/index.php
    if (empty($admin_path)) {
        if (file_exists('admin/index.php')) {
            require 'admin/index.php';
            return true;
        }
    }

    // Xử lý các tuyến đường con của admin
    // Ví dụ: /admin/products -> admin/products.php
    // Ví dụ: /admin/ajax/cart.php -> admin/ajax/cart.php
    if (!empty($admin_path)) {
        $target_file = 'admin/' . $admin_path;
        
        // Nếu đường dẫn trỏ đến một file PHP đã tồn tại (ví dụ: admin/products.php)
        if (file_exists($target_file . '.php')) {
            require $target_file . '.php';
            return true;
        }
        
        // Nếu đường dẫn trỏ đến một file đã tồn tại (ví dụ: admin/ajax/cart.php)
        if (file_exists($target_file)) {
            require $target_file;
            return true;
        }
    }
}
// 2. Products (san-pham)
if ($first_segment === 'san-pham') {
    if (count($parts) === 1) {
        // Route: /san-pham -> san-pham/index.php (Danh sách)
        if (file_exists('san-pham/index.php')) {
            require 'san-pham/index.php';
            return true;
        }
    } elseif (count($parts) === 2) {
        // Route: /san-pham/slug -> san-pham/detail.php (Chi tiết)
        $_GET['slug'] = $parts[1];
        if (file_exists('san-pham/detail.php')) {
            require 'san-pham/detail.php';
            return true;
        }
        // Thử trường hợp tên file là [slug].php
        if (file_exists('san-pham/[slug].php')) {
            require 'san-pham/[slug].php';
            return true;
        }
    }
}


// 3. Cart & Checkout (gio-hang, thanh-toan)
if ($first_segment === 'gio-hang' && count($parts) === 1 && file_exists('gio-hang.php')) {
    require 'gio-hang.php';
    return true;
}

if ($first_segment === 'thanh-toan') {
    if (count($parts) === 1) {
        // Route: /thanh-toan -> thanh-toan.php (Trang thanh toán chính)
        if (file_exists('thanh-toan.php')) {
            require 'thanh-toan.php';
            return true;
        }
    } elseif (count($parts) === 2 && $parts[1] === 'thanh-cong') {
        // Route: /thanh-toan/thanh-cong -> thanh-cong.php (Trang báo thành công)
        
        // *Đảm bảo file thanh-cong.php nằm ở thư mục gốc*
        if (file_exists('thanh-cong.php')) { 
            require 'thanh-cong.php';
            return true;
        }
    }
}

// 4. Articles (bai-viet) - CHÚ Ý: Cần đảm bảo các file này tồn tại!
if ($first_segment === 'bai-viet') {
    if (count($parts) === 1) {
        // Route: /bai-viet -> bai-viet/index.php (Danh sách)
        if (file_exists('bai-viet/index.php')) {
            require 'bai-viet/index.php';
            return true;
        }
    } elseif (count($parts) === 2) {
        // Route: /bai-viet/slug -> bai-viet/detail.php (Chi tiết)
        $_GET['slug'] = $parts[1];
        if (file_exists('bai-viet/detail.php')) {
            require 'bai-viet/detail.php';
            return true;
        }
    }
}

// 5. Videos - CHÚ Ý: Cần đảm bảo các file này tồn tại!
if ($first_segment === 'video') {
    if (count($parts) === 1) {
        // Route: /video -> video/index.php (Danh sách)
        if (file_exists('video/index.php')) {
            require 'video/index.php';
            return true;
        }
    } elseif (count($parts) === 2) {
        // Route: /video/slug -> video/detail.php (Chi tiết)
        $_GET['slug'] = $parts[1];
        if (file_exists('video/detail.php')) {
            require 'video/detail.php';
            return true;
        }
    }
}

// 6. Static Pages (ve-chung-toi, dich-vu, lien-he)
if ($request_uri === 've-chung-toi' && file_exists('ve-chung-toi.php')) {
    require 've-chung-toi.php';
    return true;
}
if ($request_uri === 'dich-vu' && file_exists('dich-vu.php')) {
    require 'dich-vu.php';
    return true;
}
if ($request_uri === 'lien-he' && file_exists('lien-he.php')) {
    require 'lien-he.php';
    return true;
}

// 7. 404 Not Found (Nếu không khớp với bất kỳ route nào)
http_response_code(404);
if (file_exists('404.php')) {
    require '404.php';
} else {
    echo "<h1>404 Not Found</h1><p>The requested URL was not found on this server.</p>";
}
return true;