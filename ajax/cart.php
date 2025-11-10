<?php
require_once '../config.php';

header('Content-Type: application/json');

// Handle GET requests
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $action = $_GET['action'] ?? '';
    
    switch ($action) {
        case 'count':
            echo json_encode([
                'success' => true,
                'count' => get_cart_count()
            ]);
            break;
            
        case 'items':
            echo json_encode([
                'success' => true,
                'items' => get_cart(),
                'total' => get_cart_total()
            ]);
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
    exit;
}

// Handle POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $action = $input['action'] ?? '';
    
    switch ($action) {
        case 'add':
            $product_id = intval($input['product_id'] ?? 0);
            $quantity = intval($input['quantity'] ?? 1);
            
            if ($product_id > 0) {
                $result = add_to_cart($product_id, $quantity);
                echo json_encode([
                    'success' => $result,
                    'message' => $result ? 'Đã thêm vào giỏ hàng' : 'Không thể thêm sản phẩm',
                    'count' => get_cart_count(),
                    'total' => get_cart_total()
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'ID sản phẩm không hợp lệ']);
            }
            break;
            
        case 'update':
            $product_id = intval($input['product_id'] ?? 0);
            $quantity = intval($input['quantity'] ?? 1);
            
            if ($product_id > 0) {
                $result = update_cart($product_id, $quantity);
                echo json_encode([
                    'success' => $result,
                    'message' => $result ? 'Đã cập nhật giỏ hàng' : 'Không thể cập nhật',
                    'count' => get_cart_count(),
                    'total' => get_cart_total()
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'ID sản phẩm không hợp lệ']);
            }
            break;
            
        case 'remove':
            $product_id = intval($input['product_id'] ?? 0);
            
            if ($product_id > 0) {
                $result = remove_from_cart($product_id);
                echo json_encode([
                    'success' => $result,
                    'message' => $result ? 'Đã xóa khỏi giỏ hàng' : 'Không thể xóa sản phẩm',
                    'count' => get_cart_count(),
                    'total' => get_cart_total()
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'ID sản phẩm không hợp lệ']);
            }
            break;
            
        case 'clear':
            clear_cart();
            echo json_encode([
                'success' => true,
                'message' => 'Đã xóa toàn bộ giỏ hàng',
                'count' => 0,
                'total' => 0
            ]);
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
    exit;
}

echo json_encode(['success' => false, 'message' => 'Invalid request method']);