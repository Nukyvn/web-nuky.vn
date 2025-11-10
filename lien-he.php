<?php
require_once 'config.php';

$success_message = '';
$error_message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = clean_input($_POST['name'] ?? '');
    $phone = clean_input($_POST['phone'] ?? '');
    $email = clean_input($_POST['email'] ?? '');
    $message = clean_input($_POST['message'] ?? '');
    
    if (empty($name) || empty($phone) || empty($message)) {
        $error_message = 'Vui lòng điền đầy đủ thông tin bắt buộc.';
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO chat_messages (customer_name, customer_phone, customer_email, message, status) 
                                  VALUES (?, ?, ?, ?, 'new')");
            $stmt->execute([$name, $phone, $email, $message]);
            
            // Send email
            $subject = "Liên hệ mới từ website - Nuky.vn";
            $body = "
            <h2>Liên hệ mới từ website</h2>
            <p><strong>Tên:</strong> $name</p>
            <p><strong>Số điện thoại:</strong> $phone</p>
            <p><strong>Email:</strong> $email</p>
            <p><strong>Nội dung:</strong></p>
            <p>$message</p>
            ";
            
            $headers = "MIME-Version: 1.0\r\n";
            $headers .= "Content-type: text/html; charset=UTF-8\r\n";
            $headers .= "From: Nuky.vn <noreply@nuky.vn>\r\n";
            
            mail(ADMIN_EMAIL, $subject, $body, $headers);
            
            $success_message = 'Cảm ơn bạn đã liên hệ! Chúng tôi sẽ phản hồi trong thời gian sớm nhất.';
            
        } catch (Exception $e) {
            $error_message = 'Có lỗi xảy ra. Vui lòng thử lại sau.';
        }
    }
}

$page_title = 'Liên hệ - ' . SITE_NAME;
$google_maps = get_setting('google_maps_embed');

include 'includes/header.php';
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
                        <span class="text-gray-500">Liên hệ</span>
                    </div>
                </li>
            </ol>
        </nav>
    </div>
</div>

<!-- Contact Section -->
<section class="py-12 bg-white">
    <div class="container mx-auto px-4">
        <div class="text-center mb-12">
            <h1 class="text-4xl font-bold mb-4" style="color: <?= COLOR_PRIMARY ?>">Liên hệ với chúng tôi</h1>
            <p class="text-xl text-gray-600">Chúng tôi luôn sẵn sàng lắng nghe và hỗ trợ bạn</p>
        </div>

        <div class="grid md:grid-cols-2 gap-12">
            <!-- Contact Form -->
            <div class="bg-white rounded-lg shadow-md p-8">
                <h2 class="text-2xl font-bold mb-6">Gửi tin nhắn cho chúng tôi</h2>

                <?php if ($success_message): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                    <?= $success_message ?>
                </div>
                <?php endif; ?>

                <?php if ($error_message): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                    <?= $error_message ?>
                </div>
                <?php endif; ?>

                <form method="POST" class="space-y-4">
                    <div>
                        <label class="block text-sm font-semibold mb-2">Họ và tên <span
                                class="text-red-500">*</span></label>
                        <input type="text" name="name" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                            placeholder="Nguyễn Văn A">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold mb-2">Số điện thoại <span
                                class="text-red-500">*</span></label>
                        <input type="tel" name="phone" required pattern="[0-9]{10,11}"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                            placeholder="0987654321">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold mb-2">Email</label>
                        <input type="email" name="email"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                            placeholder="email@example.com">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold mb-2">Nội dung <span
                                class="text-red-500">*</span></label>
                        <textarea name="message" required rows="5"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent resize-none"
                            placeholder="Nhập nội dung tin nhắn..."></textarea>
                    </div>

                    <button type="submit"
                        class="w-full bg-green-600 text-white py-3 rounded-lg font-semibold hover:bg-green-700 transition">
                        Gửi tin nhắn
                    </button>
                </form>
            </div>

            <!-- Contact Info -->
            <div>
                <div class="bg-white rounded-lg shadow-md p-8 mb-6">
                    <h2 class="text-2xl font-bold mb-6">Thông tin liên hệ</h2>

                    <div class="space-y-6">
                        <div class="flex items-start">
                            <div
                                class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mr-4 flex-shrink-0">
                                <svg class="w-6 h-6 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z"
                                        clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-bold text-lg mb-1">Địa chỉ</h3>
                                <p class="text-gray-600"><?= get_setting('contact_address') ?></p>
                            </div>
                        </div>

                        <div class="flex items-start">
                            <div
                                class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mr-4 flex-shrink-0">
                                <svg class="w-6 h-6 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path
                                        d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-bold text-lg mb-1">Hotline</h3>
                                <a href="tel:<?= get_setting('contact_phone') ?>"
                                    class="text-gray-600 hover:text-green-600">
                                    <?= get_setting('contact_phone') ?>
                                </a>
                            </div>
                        </div>

                        <div class="flex items-start">
                            <div
                                class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mr-4 flex-shrink-0">
                                <svg class="w-6 h-6 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z" />
                                    <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-bold text-lg mb-1">Email</h3>
                                <a href="mailto:<?= get_setting('contact_email') ?>"
                                    class="text-gray-600 hover:text-green-600">
                                    <?= get_setting('contact_email') ?>
                                </a>
                            </div>
                        </div>

                        <div class="flex items-start">
                            <div
                                class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mr-4 flex-shrink-0">
                                <svg class="w-6 h-6 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z"
                                        clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-bold text-lg mb-1">Giờ làm việc</h3>
                                <p class="text-gray-600">Thứ 2 - Thứ 7: 8:00 - 17:00</p>
                                <p class="text-gray-600">Chủ nhật: Nghỉ</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Social Links -->
                <div class="bg-white rounded-lg shadow-md p-8">
                    <h2 class="text-2xl font-bold mb-6">Kết nối với chúng tôi</h2>
                    <div class="flex space-x-4">
                        <a href="<?= get_setting('facebook_url') ?>" target="_blank"
                            class="w-12 h-12 bg-blue-600 rounded-full flex items-center justify-center text-white hover:bg-blue-700 transition">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z" />
                            </svg>
                        </a>

                        <a href="<?= get_setting('zalo_url') ?>" target="_blank"
                            class="w-12 h-12 bg-blue-500 rounded-full flex items-center justify-center text-white hover:bg-blue-600 transition">
                            <span class="font-bold">Z</span>
                        </a>

                        <a href="mailto:<?= get_setting('contact_email') ?>"
                            class="w-12 h-12 bg-red-600 rounded-full flex items-center justify-center text-white hover:bg-red-700 transition">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M20 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z" />
                            </svg>
                        </a>
                    </div>
                </div>

                <!-- Facebook Page Plugin -->
                <div class="bg-white rounded-lg shadow-md p-8 mt-6">
                    <h2 class="text-2xl font-bold mb-6">Fanpage Facebook</h2>
                    <div class="fb-page" data-href="<?= get_setting('facebook_url') ?>" data-tabs="timeline"
                        data-width="500" data-height="300" data-small-header="false" data-adapt-container-width="true"
                        data-hide-cover="false" data-show-facepile="true">
                    </div>
                </div>
            </div>
        </div>

        <!-- Google Maps Section -->
        <section class="py-16 bg-white">
            <div class="container mx-auto px-4">
                <div class="text-center mb-8">
                    <h2 class="text-3xl md:text-4xl font-bold mb-4" style="color: <?= COLOR_PRIMARY ?>">
                        📍 Vị trí của chúng tôi
                    </h2>
                    <p class="text-gray-600">Ghé thăm xưởng sản xuất để trải nghiệm sản phẩm trực tiếp</p>
                </div>

                <div class="max-w-5xl mx-auto">
                    <div class="rounded-2xl overflow-hidden shadow-xl border border-gray-200">
                        <?= $google_maps ?>
                    </div>

                    <!-- Contact Info Cards -->
                    <div class="grid md:grid-cols-3 gap-6 mt-8">
                        <div class="bg-gray-50 rounded-lg p-6 text-center hover:shadow-md transition">
                            <div class="w-12 h-12 rounded-full flex items-center justify-center mx-auto mb-4"
                                style="background-color: <?= COLOR_PRIMARY ?>20;">
                                <svg class="w-6 h-6" style="color: <?= COLOR_PRIMARY ?>" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                            </div>
                            <h3 class="font-bold text-lg mb-2">Địa chỉ</h3>
                            <p class="text-gray-600"><?= get_setting('contact_address', 'Việt Nam') ?></p>
                        </div>

                        <div class="bg-gray-50 rounded-lg p-6 text-center hover:shadow-md transition">
                            <div class="w-12 h-12 rounded-full flex items-center justify-center mx-auto mb-4"
                                style="background-color: <?= COLOR_PRIMARY ?>20;">
                                <svg class="w-6 h-6" style="color: <?= COLOR_PRIMARY ?>" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                </svg>
                            </div>
                            <h3 class="font-bold text-lg mb-2">Điện thoại</h3>
                            <a href="tel:<?= get_setting('contact_phone') ?>"
                                class="text-gray-600 hover:text-green-600 transition">
                                <?= get_setting('contact_phone', '0947009933') ?>
                            </a>
                        </div>

                        <div class="bg-gray-50 rounded-lg p-6 text-center hover:shadow-md transition">
                            <div class="w-12 h-12 rounded-full flex items-center justify-center mx-auto mb-4"
                                style="background-color: <?= COLOR_PRIMARY ?>20;">
                                <svg class="w-6 h-6" style="color: <?= COLOR_PRIMARY ?>" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <h3 class="font-bold text-lg mb-2">Email</h3>
                            <a href="mailto:<?= get_setting('contact_email') ?>"
                                class="text-gray-600 hover:text-green-600 transition">
                                <?= get_setting('contact_email', 'orders@nuky.vn') ?>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Facebook SDK -->
        <div id="fb-root"></div>
        <script async defer crossorigin="anonymous"
            src="https://connect.facebook.net/vi_VN/sdk.js#xfbml=1&version=v12.0">
        </script>
    </div>
</section>

<?php include 'includes/footer.php'; ?>