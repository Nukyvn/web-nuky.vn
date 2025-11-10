-- Script để update các promotion cũ không có start_date

-- Cách 1: Set start_date = created_at cho các promotion cũ
UPDATE promotions
SET start_date = created_at
WHERE start_date IS NULL;

-- Cách 2: Set start_date = ngày hiện tại cho các promotion đang active
UPDATE promotions
SET start_date = NOW()
WHERE start_date IS NULL AND status = 1;

-- Cách 3: Set start_date = countdown_end - 30 ngày (nếu countdown_end có giá trị)
UPDATE promotions
SET start_date = DATE_SUB(countdown_end, INTERVAL 30 DAY)
WHERE start_date IS NULL
  AND countdown_end IS NOT NULL;

-- Cách 4: Set cụ thể cho từng promotion
-- UPDATE promotions SET start_date = '2025-11-01 00:00:00' WHERE id = 1;
-- UPDATE promotions SET start_date = '2025-11-10 00:00:00' WHERE id = 2;

-- Kiểm tra kết quả
SELECT
    id,
    title,
    start_date,
    countdown_end,
    CASE
        WHEN start_date IS NULL THEN '❌ Thiếu start_date'
        WHEN countdown_end IS NULL THEN '❌ Thiếu countdown_end'
        WHEN NOW() BETWEEN start_date AND countdown_end THEN '✅ Đang diễn ra'
        WHEN NOW() < start_date THEN '🔵 Sắp diễn ra'
        ELSE '⚫ Đã kết thúc'
    END as status
FROM promotions
ORDER BY start_date DESC;
