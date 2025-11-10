# Hướng dẫn sử dụng hệ thống 2 chương trình khuyến mãi

## 📋 Tổng quan

Hệ thống tự động hiển thị 2 chương trình khuyến mãi gần nhất:
1. **Chương trình ĐANG DIỄN RA** (màu đỏ/cam)
2. **Chương trình SẮP DIỄN RA** (màu xanh/tím) - hiển thị "còn X ngày"

## 🗄️ Cấu trúc Database

```sql
-- Bảng promotions đã có sẵn, chỉ cần thêm field start_date
ALTER TABLE promotions
ADD COLUMN start_date DATETIME DEFAULT NULL AFTER discount_text;
```

### Các field quan trọng:
- `start_date`: Ngày bắt đầu chương trình
- `countdown_end`: Ngày kết thúc chương trình (để countdown)
- `status`: 1 = active, 0 = inactive

## 🎯 Logic tự động

### Chương trình 1 - ĐANG DIỄN RA
```sql
SELECT * FROM promotions
WHERE start_date <= NOW()
AND countdown_end >= NOW()
AND status = 1
ORDER BY start_date DESC
LIMIT 1
```

### Chương trình 2 - SẮP DIỄN RA
```sql
SELECT * FROM promotions
WHERE start_date > NOW()
AND status = 1
ORDER BY start_date ASC
LIMIT 1
```

## 📝 Ví dụ thêm dữ liệu

```sql
-- Chương trình 1: ĐANG diễn ra (từ 1/11 đến 15/11)
INSERT INTO promotions (
    title,
    description,
    discount_text,
    start_date,
    countdown_end,
    button_text,
    button_link,
    banner_image,
    status
) VALUES (
    'BLACK FRIDAY SALE 2025',
    'Giảm giá cực sốc toàn bộ sản phẩm trà',
    'GIẢM 50%',
    '2025-11-01 00:00:00',
    '2025-11-15 23:59:59',
    'Mua ngay',
    '/san-pham',
    '/uploads/black-friday.jpg',
    1
);

-- Chương trình 2: SẮP diễn ra (từ 20/11 đến 30/11)
INSERT INTO promotions (
    title,
    description,
    discount_text,
    start_date,
    countdown_end,
    button_text,
    button_link,
    banner_image,
    status
) VALUES (
    'CYBER MONDAY SALE',
    'Ưu đãi đặc biệt dành riêng cho khách hàng thân thiết',
    'GIẢM 40%',
    '2025-11-20 00:00:00',
    '2025-11-30 23:59:59',
    'Xem chi tiết',
    '/san-pham',
    '/uploads/cyber-monday.jpg',
    1
);
```

## 🎨 Thiết kế

### Chương trình ĐANG DIỄN RA (Slot 1)
- **Màu nền**: Gradient đỏ → cam (`from-red-500 to-orange-500`)
- **Badge**: Màu vàng với icon ⚡ và hiệu ứng `animate-pulse`
- **Countdown**: Đếm ngược đến khi **KẾT THÚC** (countdown_end)
- **Button**: Màu vàng với text đỏ

### Chương trình SẮP DIỄN RA (Slot 2)
- **Màu nền**: Gradient xanh → tím (`from-blue-500 to-purple-600`)
- **Badge**: Màu xanh lá với icon 🎯
- **Số ngày còn lại**: Hiển thị rõ "Còn X ngày nữa"
- **Countdown**: Đếm ngược đến khi **BẮT ĐẦU** (start_date)
- **Thời gian bắt đầu**: Hiển thị ngày giờ cụ thể
- **Button**: Màu xanh lá với text xanh đậm

## 🔄 Tự động chuyển đổi

Hệ thống sẽ **TỰ ĐỘNG** chuyển đổi dựa trên thời gian:

### Ví dụ Timeline:
```
Hôm nay: 10/11/2025

Chương trình A: 01/11 - 15/11 (ĐANG diễn ra) → Hiển thị slot 1
Chương trình B: 20/11 - 30/11 (SẮP diễn ra) → Hiển thị slot 2
```

Khi đến ngày **16/11**:
```
Chương trình A: Đã kết thúc → Không hiển thị
Chương trình B: 20/11 - 30/11 (SẮP diễn ra) → Vẫn ở slot 2
```

Khi đến ngày **20/11**:
```
Chương trình B: 20/11 - 30/11 (ĐANG diễn ra) → Chuyển sang slot 1
Chương trình C: 01/12 - 10/12 (SẮP diễn ra) → Hiển thị slot 2
```

## 🎭 Trạng thái Empty State

Nếu không có chương trình nào, sẽ hiển thị placeholder với icon:
- Slot 1: Icon đồng hồ + "Hiện không có chương trình nào đang diễn ra"
- Slot 2: Icon lịch + "Chưa có chương trình sắp diễn ra"

## 📱 Responsive

- **Desktop**: 2 cột song song
- **Mobile**: Stack thành 1 cột

## ⚙️ Yêu cầu kỹ thuật

1. **Alpine.js**: Đã có sẵn (dùng cho countdown)
2. **Tailwind CSS**: Đã có sẵn (dùng cho styling)
3. **PHP 7.4+**: Với PDO enabled
4. **MySQL 5.7+**

## 🚀 Cách test

1. Thêm 2 promotion như ví dụ trên
2. Điều chỉnh `start_date` và `countdown_end` để:
   - Promotion 1: Đang diễn ra (start_date trong quá khứ, countdown_end trong tương lai)
   - Promotion 2: Sắp diễn ra (start_date trong tương lai)
3. Reload trang chủ để xem kết quả

## 🔧 Tùy chỉnh

### Thay đổi màu sắc
Tìm class trong code:
- Slot 1: `from-red-500 to-orange-500` → đổi thành màu khác
- Slot 2: `from-blue-500 to-purple-600` → đổi thành màu khác

### Thay đổi số lượng
Muốn hiển thị 3 hoặc 4 promotion? Sửa:
- `grid md:grid-cols-2` → `grid md:grid-cols-3`
- `LIMIT 1` trong SQL → `LIMIT 2`

## 📞 Support

Nếu có vấn đề, kiểm tra:
1. Field `start_date` đã được thêm vào database chưa
2. Dữ liệu promotion có `status = 1` không
3. Thời gian `start_date` và `countdown_end` có đúng không
4. Console browser có báo lỗi JavaScript không
