<?php
require_once '../config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

// Validate input
$name = clean_input($input['name'] ?? '');
$phone = clean_input($input['phone'] ?? '');
$email = clean_input($input['email'] ?? '');
$message = clean_input($input['message'] ?? '');

if (empty($name) || empty($phone) || empty($message)) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng điền đầy đủ thông tin bắt buộc']);
    exit;
}

// Get client info
$ip_address = $_SERVER['REMOTE_ADDR'] ?? '';
$user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';

try {
    $stmt = $pdo->prepare("INSERT INTO chat_messages (customer_name, customer_phone, customer_email, message, ip_address, user_agent, status) 
                          VALUES (?, ?, ?, ?, ?, ?, 'new')");
    $stmt->execute([$name, $phone, $email, $message, $ip_address, $user_agent]);
    
    // Send notification email to admin
    $subject = "Tin nhắn mới từ Chat Box - Nuky.vn";
    $body = "
    <h2>Tin nhắn mới từ chat box</h2>
    <p><strong>Tên:</strong> $name</p>
    <p><strong>Số điện thoại:</strong> $phone</p>
    <p><strong>Email:</strong> $email</p>
    <p><strong>Nội dung:</strong></p>
    <p>$message</p>
    <hr>
    <p><strong>IP:</strong> $ip_address</p>
    <p><strong>Thời gian:</strong> " . date('d/m/Y H:i:s') . "</p>
    ";
    
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8\r\n";
    $headers .= "From: Nuky.vn <noreply@nuky.vn>\r\n";
    
    mail(ADMIN_EMAIL, $subject, $body, $headers);
    
    echo json_encode([
        'success' => true,
        'message' => 'Cảm ơn bạn! Chúng tôi sẽ liên hệ lại sớm nhất.'
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Có lỗi xảy ra. Vui lòng thử lại sau.'
    ]);
}