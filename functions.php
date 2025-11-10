<?php
// functions.php - Core utility functions

// ============================================
// SETTINGS FUNCTIONS
// ============================================

function get_all_settings() {
    global $pdo;
    $stmt = $pdo->query("SELECT setting_key, setting_value FROM settings");
    $settings = [];
    while ($row = $stmt->fetch()) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
    return $settings;
}

function get_setting($key, $default = '') {
    global $pdo;
    $stmt = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = ?");
    $stmt->execute([$key]);
    $result = $stmt->fetchColumn();
    return $result !== false ? $result : $default;
}

function update_setting($key, $value, $type = 'text') {
    global $pdo;
    $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value, setting_type) VALUES (?, ?, ?) 
                          ON DUPLICATE KEY UPDATE setting_value = ?, setting_type = ?");
    return $stmt->execute([$key, $value, $type, $value, $type]);
}

// ============================================
// SLUG FUNCTIONS
// ============================================

function create_slug($string) {
    $string = trim($string);
    $string = preg_replace('/(à|á|ạ|ả|ã|â|ầ|ấ|ậ|ẩ|ẫ|ă|ằ|ắ|ặ|ẳ|ẵ)/', 'a', $string);
    $string = preg_replace('/(è|é|ẹ|ẻ|ẽ|ê|ề|ế|ệ|ể|ễ)/', 'e', $string);
    $string = preg_replace('/(ì|í|ị|ỉ|ĩ)/', 'i', $string);
    $string = preg_replace('/(ò|ó|ọ|ỏ|õ|ô|ồ|ố|ộ|ổ|ỗ|ơ|ờ|ớ|ợ|ở|ỡ)/', 'o', $string);
    $string = preg_replace('/(ù|ú|ụ|ủ|ũ|ư|ừ|ứ|ự|ử|ữ)/', 'u', $string);
    $string = preg_replace('/(ỳ|ý|ỵ|ỷ|ỹ)/', 'y', $string);
    $string = preg_replace('/(đ)/', 'd', $string);
    $string = preg_replace('/(À|Á|Ạ|Ả|Ã|Â|Ầ|Ấ|Ậ|Ẩ|Ẫ|Ă|Ằ|Ắ|Ặ|Ẳ|Ẵ)/', 'A', $string);
    $string = preg_replace('/(È|É|Ẹ|Ẻ|Ẽ|Ê|Ề|Ế|Ệ|Ể|Ễ)/', 'E', $string);
    $string = preg_replace('/(Ì|Í|Ị|Ỉ|Ĩ)/', 'I', $string);
    $string = preg_replace('/(Ò|Ó|Ọ|Ỏ|Õ|Ô|Ồ|Ố|Ộ|Ổ|Ỗ|Ơ|Ờ|Ớ|Ợ|Ở|Ỡ)/', 'O', $string);
    $string = preg_replace('/(Ù|Ú|Ụ|Ủ|Ũ|Ư|Ừ|Ứ|Ự|Ử|Ữ)/', 'U', $string);
    $string = preg_replace('/(Ỳ|Ý|Ỵ|Ỷ|Ỹ)/', 'Y', $string);
    $string = preg_replace('/(Đ)/', 'D', $string);
    $string = preg_replace('/[^a-zA-Z0-9\-\_]/', '-', $string);
    $string = preg_replace('/-+/', '-', $string);
    $string = strtolower($string);
    return trim($string, '-');
}

function unique_slug($table, $slug, $id = null) {
    global $pdo;
    $original_slug = $slug;
    $counter = 1;
    
    while (true) {
        $sql = "SELECT id FROM $table WHERE slug = ?";
        $params = [$slug];
        
        if ($id) {
            $sql .= " AND id != ?";
            $params[] = $id;
        }
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        
        if (!$stmt->fetch()) {
            return $slug;
        }
        
        $slug = $original_slug . '-' . $counter;
        $counter++;
    }
}

// ============================================
// FILE UPLOAD FUNCTIONS
// ============================================

function upload_file($file, $folder = '') {
    if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'Upload error'];
    }
    
    if ($file['size'] > MAX_UPLOAD_SIZE) {
        return ['success' => false, 'message' => 'File quá lớn'];
    }
    
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed = array_merge(ALLOWED_IMAGES, ALLOWED_VIDEOS);
    
    if (!in_array($ext, $allowed)) {
        return ['success' => false, 'message' => 'Định dạng file không được phép'];
    }
    
    $upload_dir = UPLOAD_PATH . $folder;
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    $filename = uniqid() . '_' . time() . '.' . $ext;
    $filepath = $upload_dir . '/' . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        $url = UPLOAD_URL . $folder . '/' . $filename;
        return ['success' => true, 'url' => $url, 'filename' => $filename];
    }
    
    return ['success' => false, 'message' => 'Không thể upload file'];
}

// ============================================
// CART FUNCTIONS
// ============================================

function get_cart() {
    return $_SESSION[CART_SESSION_KEY] ?? [];
}

function add_to_cart($product_id, $quantity = 1) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT id, name, price, sale_price, images FROM products WHERE id = ? AND status = 1");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch();
    
    if (!$product) {
        return false;
    }
    
    $cart = get_cart();
    
    if (isset($cart[$product_id])) {
        $cart[$product_id]['quantity'] += $quantity;
    } else {
        $images = json_decode($product['images'], true);
        $cart[$product_id] = [
            'id' => $product['id'],
            'name' => $product['name'],
            'price' => $product['sale_price'] ?? $product['price'],
            'quantity' => $quantity,
            'image' => $images[0] ?? '/uploads/no-image.png'
        ];
    }
    
    $_SESSION[CART_SESSION_KEY] = $cart;
    return true;
}

function update_cart($product_id, $quantity) {
    $cart = get_cart();
    
    if (isset($cart[$product_id])) {
        if ($quantity <= 0) {
            unset($cart[$product_id]);
        } else {
            $cart[$product_id]['quantity'] = $quantity;
        }
        $_SESSION[CART_SESSION_KEY] = $cart;
        return true;
    }
    
    return false;
}

function remove_from_cart($product_id) {
    $cart = get_cart();
    if (isset($cart[$product_id])) {
        unset($cart[$product_id]);
        $_SESSION[CART_SESSION_KEY] = $cart;
        return true;
    }
    return false;
}

function clear_cart() {
    $_SESSION[CART_SESSION_KEY] = [];
}

function get_cart_total() {
    $cart = get_cart();
    $total = 0;
    foreach ($cart as $item) {
        $total += $item['price'] * $item['quantity'];
    }
    return $total;
}

function get_cart_count() {
    $cart = get_cart();
    $count = 0;
    foreach ($cart as $item) {
        $count += $item['quantity'];
    }
    return $count;
}

// ============================================
// ORDER FUNCTIONS
// ============================================

function generate_order_code() {
    return 'NK' . date('ymd') . strtoupper(substr(uniqid(), -6));
}

function create_order($data) {
    global $pdo;
    
    try {
        $pdo->beginTransaction();
        
        // Check or create customer
        $stmt = $pdo->prepare("SELECT id FROM customers WHERE phone = ?");
        $stmt->execute([$data['phone']]);
        $customer = $stmt->fetch();
        
        if ($customer) {
            $customer_id = $customer['id'];
        } else {
            $stmt = $pdo->prepare("INSERT INTO customers (full_name, phone, email, address) VALUES (?, ?, ?, ?)");
            $stmt->execute([$data['name'], $data['phone'], $data['email'], $data['address']]);
            $customer_id = $pdo->lastInsertId();
        }
        
        // Create order
        $order_code = generate_order_code();
        $cart = get_cart();
        $subtotal = get_cart_total();
        $total = $subtotal + ($data['shipping_fee'] ?? 0);
        
        $stmt = $pdo->prepare("INSERT INTO orders (order_code, customer_id, customer_name, customer_phone, customer_email, 
                              customer_address, notes, subtotal, shipping_fee, total, payment_method) 
                              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $order_code,
            $customer_id,
            $data['name'],
            $data['phone'],
            $data['email'],
            $data['address'],
            $data['notes'] ?? '',
            $subtotal,
            $data['shipping_fee'] ?? 0,
            $total,
            $data['payment_method'] ?? 'COD'
        ]);
        
        $order_id = $pdo->lastInsertId();
        
        // Add order items
        $stmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, product_name, product_image, price, quantity, subtotal) 
                              VALUES (?, ?, ?, ?, ?, ?, ?)");
        
        foreach ($cart as $item) {
            $item_subtotal = $item['price'] * $item['quantity'];
            $stmt->execute([
                $order_id,
                $item['id'],
                $item['name'],
                $item['image'],
                $item['price'],
                $item['quantity'],
                $item_subtotal
            ]);
        }
        
        // Update customer stats
        $pdo->prepare("UPDATE customers SET total_orders = total_orders + 1, total_spent = total_spent + ? WHERE id = ?")
            ->execute([$total, $customer_id]);
        
        $pdo->commit();
        clear_cart();
        
        return ['success' => true, 'order_id' => $order_id, 'order_code' => $order_code];
        
    } catch (Exception $e) {
        $pdo->rollBack();
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

// ============================================
// EMAIL FUNCTIONS
// ============================================

function send_order_email($order_id) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT o.*, GROUP_CONCAT(oi.product_name, ' x', oi.quantity SEPARATOR ', ') as items
                          FROM orders o
                          LEFT JOIN order_items oi ON o.id = oi.order_id
                          WHERE o.id = ?
                          GROUP BY o.id");
    $stmt->execute([$order_id]);
    $order = $stmt->fetch();
    
    if (!$order) return false;
    
    $subject = "Đơn hàng mới #{$order['order_code']} từ Nuky.vn";
    $message = "
    <h2>Đơn hàng mới #{$order['order_code']}</h2>
    <p><strong>Khách hàng:</strong> {$order['customer_name']}</p>
    <p><strong>Số điện thoại:</strong> {$order['customer_phone']}</p>
    <p><strong>Email:</strong> {$order['customer_email']}</p>
    <p><strong>Địa chỉ:</strong> {$order['customer_address']}</p>
    <p><strong>Sản phẩm:</strong> {$order['items']}</p>
    <p><strong>Tổng tiền:</strong> " . number_format($order['total']) . " VNĐ</p>
    <p><strong>Phương thức thanh toán:</strong> {$order['payment_method']}</p>
    ";
    
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8\r\n";
    $headers .= "From: Nuky.vn <noreply@nuky.vn>\r\n";
    
    return mail(ADMIN_EMAIL, $subject, $message, $headers);
}


// Bổ sung vào phần ORDER FUNCTIONS

function get_order_by_code($order_code) {
    global $pdo;
    
    // Lấy thông tin đơn hàng chính
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE order_code = ? LIMIT 1");
    $stmt->execute([$order_code]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$order) {
        return false;
    }

    // Lấy các sản phẩm trong đơn hàng (tùy chọn, không bắt buộc nếu chỉ hiển thị tổng)
    $stmt_items = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ?");
    $stmt_items->execute([$order['id']]);
    $order['items'] = $stmt_items->fetchAll(PDO::FETCH_ASSOC);

    return $order;
}

function get_payment_method_name($method_code) {
    $methods = [
        'COD' => 'Thanh toán khi nhận hàng (COD)',
        'bank_transfer' => 'Chuyển khoản ngân hàng',
        'momo' => 'Ví điện tử Momo',
        // Thêm các phương thức khác nếu có
    ];
    return $methods[$method_code] ?? $method_code;
}

// ============================================
// PAGINATION FUNCTIONS
// ============================================

function paginate($total_items, $current_page = 1, $per_page = ITEMS_PER_PAGE) {
    $total_pages = ceil($total_items / $per_page);
    $current_page = max(1, min($current_page, $total_pages));
    $offset = ($current_page - 1) * $per_page;
    
    return [
        'total_items' => $total_items,
        'total_pages' => $total_pages,
        'current_page' => $current_page,
        'per_page' => $per_page,
        'offset' => $offset
    ];
}

// ============================================
// SECURITY FUNCTIONS
// ============================================

function clean_input($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

function is_logged_in() {
    return isset($_SESSION['user_id']);
}

function require_login() {
    if (!is_logged_in()) {
        header('Location: /admin/login.php');
        exit;
    }
}

function check_admin() {
    require_login();
    if ($_SESSION['user_role'] !== 'admin') {
        die('Access denied');
    }
}

// ============================================
// FORMAT FUNCTIONS
// ============================================

function format_price($price) {
    return number_format($price, 0, ',', '.') . ' VNĐ';
}

function format_date($date) {
    return date('d/m/Y H:i', strtotime($date));
}

function time_ago($datetime) {
    $time = strtotime($datetime);
    $diff = time() - $time;
    
    if ($diff < 60) return 'vừa xong';
    if ($diff < 3600) return floor($diff / 60) . ' phút trước';
    if ($diff < 86400) return floor($diff / 3600) . ' giờ trước';
    if ($diff < 604800) return floor($diff / 86400) . ' ngày trước';
    
    return date('d/m/Y', $time);
}

// ============================================
// GET THUMBNAIL FUNCTIONS
// ============================================
/**
 * Lấy thumbnail mặc định từ video nếu không có thumbnail riêng
 * @param string $video_url URL video (YouTube/Vimeo)
 * @param string|null $thumbnail Thumbnail đã upload (nếu có)
 * @return string URL thumbnail
 */
function get_video_id($url) {
    $patterns = [
        '/youtube\.com\/watch\?v=([^\&\?]+)/', // https://www.youtube.com/watch?v=ID
        '/youtube\.com\/embed\/([^\&\?]+)/',   // https://www.youtube.com/embed/ID
        '/youtu\.be\/([^\&\?]+)/'              // https://youtu.be/ID?...
    ];

    foreach ($patterns as $pattern) {
        if (preg_match($pattern, $url, $matches)) {
            return $matches[1];
        }
    }
    return null;
}

function get_video_thumbnail($video_url, $thumbnail, $video_type) {
    // Nếu đã upload thumbnail thì dùng
    if (!empty($thumbnail)) return $thumbnail;

    // Nếu YouTube thì tự tạo URL thumbnail
    if ($video_type === 'youtube' && !empty($video_url)) {
        preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/)([^\&\?]+)/', $video_url, $matches);
        $id_youtube = $matches[1] ?? null;
        if ($id_youtube) return "https://img.youtube.com/vi/$id_youtube/hqdefault.jpg";
    }

    // Fallback image nếu không có thumbnail
    return '/uploads/default-video.webp';
}

?>