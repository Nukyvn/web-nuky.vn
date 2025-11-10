// ===========================================
// HÀM 1: UTILITY (Hàm tiện ích)
// ===========================================

function updateQueryParam(key, value) {
  const url = new URL(window.location.href);
  url.searchParams.set(key, value);
  return url.toString();
}

// ===========================================
// HÀM 2: CART UI & ANIMATION
// ===========================================

// 1. Hàm kích hoạt Nảy liên tục
function activateCartBounce() {
  const cartCountElement = document.querySelector("[data-cart-count]");
  if (cartCountElement) {
    cartCountElement.classList.add("animate-bounce");
  }
}
activateCartBounce();
// 2. Hàm dừng Nảy
function deactivateCartBounce() {
  const cartCountElement = document.querySelector("[data-cart-count]");
  if (cartCountElement) {
    cartCountElement.classList.remove("animate-bounce");
  }
}

// 3. Hàm Nảy Tạm thời (Cho sự kiện Fly-to-Cart)
function temporaryCartBounce() {
  activateCartBounce();
  setTimeout(deactivateCartBounce, 1200);
}

// Alias cho dễ nhớ
function updateCartCount() {
  fetch("/ajax/cart.php?action=count")
    .then((res) => res.json())
    .then((data) => {
      const countElement = document.querySelector("[data-cart-count]");
      if (countElement) {
        countElement.textContent = data.count;
      }
    });
}

/**
 * Hiệu ứng hình ảnh sản phẩm bay về giỏ hàng.
 * Đã sửa lỗi ReferenceError và lỗi hiển thị animation.
 */
function flyToCart(productImgElement) {
  const cartIcon = document.getElementById("cart-icon-target");

  if (!productImgElement || !cartIcon) return;

  const startRect = productImgElement.getBoundingClientRect();
  const endRect = cartIcon.getBoundingClientRect();

  // 1. Tạo bản sao và thiết lập style bắt đầu
  const flyingElement = productImgElement.cloneNode(true); // 🛑 ĐÃ KHAI BÁO

  flyingElement.style.position = "fixed";
  flyingElement.style.zIndex = "99999";
  flyingElement.style.top = `${startRect.top}px`;
  flyingElement.style.left = `${startRect.left}px`;
  flyingElement.style.width = `${startRect.width}px`;
  flyingElement.style.height = `${startRect.height}px`;
  flyingElement.style.opacity = "1";
  flyingElement.style.pointerEvents = "none"; // Không thể click
  flyingElement.style.borderRadius = "8px";

  // Đặt transition ở đây, nhưng chưa kích hoạt
  flyingElement.style.transition = "all 1s cubic-bezier(0.7, -0.5, 0.4, 1.2)";

  document.body.appendChild(flyingElement);

  // 2. 🚀 Sử dụng requestAnimationFrame để kích hoạt transition
  // Đảm bảo trình duyệt đã render vị trí ban đầu (A) trước khi chuyển sang vị trí cuối (B)
  requestAnimationFrame(() => {
    // Tọa độ mục tiêu (Bay đến giữa icon giỏ hàng)
    const targetTop = endRect.top + endRect.height / 2 - 15;
    const targetLeft = endRect.left + endRect.width / 2 - 15;

    // BẮT ĐẦU CHUYỂN ĐỘNG VÀ THU NHỎ
    flyingElement.style.top = `${targetTop}px`;
    flyingElement.style.left = `${targetLeft}px`;
    flyingElement.style.width = "20px";
    flyingElement.style.height = "20px";
    flyingElement.style.opacity = "0";
    flyingElement.style.transform = "scale(0.1) rotate(360deg)";

    // 3. Dọn dẹp (chạy sau 1000ms, khớp với thời gian transition)
    setTimeout(() => {
      flyingElement.remove();
      temporaryCartBounce(); // 🛑 Gọi hàm nảy tạm thời đã định nghĩa
    }, 1000);
  });
}

// ===========================================
// HÀM 3: XỬ LÝ SỰ KIỆN CART VÀ ROUTING
// ===========================================

function handleAddToCartClick(buttonElement, productId) {
  debugger;
  var productContainer = buttonElement.closest(".product-item");
  var productImg = productContainer
    ? productContainer.querySelector(".product-image")
    : null;

  addToCart(productId, productImg);
}

function addToCart(productId, productImgElement) {
  // Đảm bảo nhận 2 tham số
  fetch("/ajax/cart.php", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ action: "add", product_id: productId }),
  })
    .then((res) => res.json())
    .then((data) => {
      if (data.success) {
        // Kích hoạt hiệu ứng bay
        if (productImgElement) {
          flyToCart(productImgElement);
        } else {
          // Dự phòng nếu không tìm thấy hình ảnh
          bounceCartIcon();
        }
        updateCartCount();
      } else {
        alert(data.message || "Thêm sản phẩm thất bại.");
      }
    });
}
activateCartBounce();
