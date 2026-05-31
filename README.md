# 🚗 HỆ THỐNG QUẢN LÝ XE NỘI BỘ
## PHP + MySQL + XAMPP

---

## 📁 CẤU TRÚC FILE

```
vehicle_management/
├── index.php          — Trang chính: danh sách & quản lý chuyến xe
├── login.php          — Trang đăng nhập
├── logout.php         — Xử lý đăng xuất
├── trip_form.php      — Form tạo / chỉnh sửa chuyến xe
├── vehicles.php       — Quản lý xe & tài xế (Admin)
├── dashboard.php      — 🖥️ MÀN HÌNH PHÒNG BẢO VỆ (không cần login)
├── database.sql       — Script tạo database
├── includes/
│   ├── config.php     — Cấu hình kết nối DB
│   └── auth.php       — Xử lý phiên đăng nhập
└── assets/css/
    └── panel.css      — CSS dùng chung
```

---

## ⚙️ HƯỚNG DẪN CÀI ĐẶT

### Bước 1: Cài XAMPP
- Tải từ https://www.apachefriends.org
- Bật Apache + MySQL

### Bước 2: Copy thư mục
```
Copy thư mục vehicle_management/ vào:
C:\xampp\htdocs\vehicle_management\
```

### Bước 3: Tạo database
1. Mở trình duyệt → http://localhost/phpmyadmin
2. Click "Import" → chọn file `database.sql`
3. Click "Go"

### Bước 4: Truy cập
| URL | Mô tả |
|-----|-------|
| http://localhost/vehicle_management/login.php | Đăng nhập nhân sự |
| http://localhost/vehicle_management/dashboard.php | Màn hình bảo vệ |

---

## 👤 TÀI KHOẢN MẶC ĐỊNH

| Username | Mật khẩu | Quyền |
|----------|----------|-------|
| admin    | admin123 | Admin (toàn quyền) |
| nhansu   | nhansu123 | Staff (nhập liệu) |

---

## 🔧 CẤU HÌNH

Mở file `includes/config.php` để thay đổi:
```php
define('DB_HOST', 'localhost');  // Host MySQL
define('DB_USER', 'root');       // Username MySQL
define('DB_PASS', '');           // Password MySQL (XAMPP mặc định để trống)
define('DB_NAME', 'vehicle_mgmt'); // Tên database
```

---

## 📱 TÍNH NĂNG

### Panel Nhân Sự (login.php / index.php)
- ✅ Đăng nhập bảo mật
- ✅ Dashboard thống kê nhanh (hôm nay)
- ✅ Tạo / chỉnh sửa chuyến xe
- ✅ Cập nhật trạng thái nhanh (Xuất / Về)
- ✅ Lọc theo: Hôm nay / Sắp đi / Đang đi / Tất cả

### Form Tạo Chuyến (trip_form.php)
- ✅ Chọn xe, tài xế
- ✅ Nhập người yêu cầu, phòng ban
- ✅ Điểm đến, mục đích
- ✅ Giờ xuất / dự kiến về
- ✅ Ghi chú xuất/nhập cổng

### Dashboard Bảo Vệ (dashboard.php)
- ✅ KHÔNG cần đăng nhập
- ✅ Auto-refresh mỗi 15 giây
- ✅ Đồng hồ realtime
- ✅ Hiển thị xe đang ra ngoài (nổi bật)
- ✅ Danh sách sắp xuất phát
- ✅ Danh sách đã về hôm nay
- ✅ Tình trạng 3 xe

### Quản Trị (vehicles.php - Admin only)
- ✅ Cập nhật trạng thái xe
- ✅ Cập nhật trạng thái tài xế

---

## 💡 GỢI Ý NÂNG CẤP

1. **Thêm tài khoản** — thêm vào bảng `users` qua phpMyAdmin
2. **Thêm xe/tài xế** — thêm vào bảng `vehicles`/`drivers`
3. **Báo cáo tháng** — thêm trang `reports.php` query theo tháng
4. **API realtime** — dùng AJAX polling thay reload trang
5. **Thông báo SMS/Zalo** — tích hợp API khi xe quá giờ về
