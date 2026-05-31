<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
requireLogin();

$user = currentUser();

// ── 1. CƠ CHẾ GHI NHỚ NGÔN NGỮ XUYÊN TRANG BẰNG PHP SESSION ──
if (isset($_GET['lang'])) {
    $lang = $_GET['lang'];
    $_SESSION['lang'] = $lang; 
} else {
    $lang = $_SESSION['lang'] ?? 'vi'; 
}
if (!in_array($lang, ['vi', 'jp'])) { $lang = 'vi'; }

// ── BỘ TỪ ĐIỂN SONG NGỮ VIỆT - NHẬT CHO TRANG BOOKING ──
$lang_pack = [
    'vi' => [
        'nav_trips' => '📋 Chuyến Xe',
        'nav_create' => '➕ Tạo Chuyến',
        'nav_vehicles' => '🚙 Xe & Tài Xế',
        'nav_dashboard' => '🖥️ Màn Bảo Vệ',
        'nav_booking' => '📅 Đặt Lịch Xe',
        'logout' => 'Đăng xuất',
        'title' => '📅 Điều Phối & Đặt Lịch Chuyến Xe',
        'filter_title' => '⚙️ Bộ Lọc Lịch Xe',
        'lbl_all' => '— Tất cả —',
        'lbl_vehicle' => 'Chọn xe',
        'lbl_driver' => 'Chọn tài xế',
        'lbl_dept' => 'Phòng ban',
        'lbl_status' => 'Trạng thái',
        'color_legend' => '🎨 Chú Thích Trạng Thái',
        'status_pending' => 'Chờ duyệt',
        'status_scheduled' => 'Đã duyệt / Lên lịch',
        'status_departed' => 'Đang di chuyển',
        'status_returned' => 'Đã hoàn thành',
        'status_cancelled' => 'Đã hủy',
        
        // Form & Modals
        'modal_title_add' => '➕ Đặt Lịch Chuyến Xe Mới',
        'modal_title_edit' => '✏️ Chỉnh Sửa Đặt Lịch Chuyến Xe',
        'modal_title_detail' => '📋 Chi Tiết Đặt Lịch Chuyến Xe',
        'lbl_requester' => 'Người yêu cầu / Trưởng đoàn',
        'lbl_dept_form' => 'Phòng ban đăng ký',
        'lbl_companions' => 'Người đi cùng (cách nhau bằng dấu phẩy)',
        'lbl_route' => 'Lộ trình di chuyển',
        'lbl_origin' => 'Điểm xuất phát',
        'lbl_destination' => 'Điểm đến',
        'lbl_purpose' => 'Mục đích / Nội dung công việc',
        'lbl_start_time' => 'Thời gian bắt đầu',
        'lbl_end_time' => 'Thời gian kết thúc',
        'btn_cancel' => 'Hủy',
        'btn_save' => '💾 Lưu Đăng Ký',
        'btn_delete' => '🗑️ Xóa Lịch',
        'btn_edit' => '✏️ Sửa Lịch',
        
        // Warnings & Errors
        'err_time' => 'Thời gian kết thúc phải lớn hơn thời gian bắt đầu.',
        'err_vehicle_busy' => 'Xe này đã có lịch đặt trùng trong khoảng thời gian này.',
        'err_driver_busy' => 'Tài xế này đã có lịch chạy trùng trong khoảng thời gian này.',
        'err_required' => 'Vui lòng điền đầy đủ các thông tin bắt buộc (*).'
    ],
    'jp' => [
        'nav_trips' => '📋 運行リスト',
        'nav_create' => '➕ 運行作成',
        'nav_vehicles' => '🚙 車両・運転手',
        'nav_dashboard' => '🖥️ 警備員画面',
        'nav_booking' => '📅 車両予約システム',
        'logout' => 'ログアウト',
        'title' => '📅 車両予約・運行スケジュール管理',
        'filter_title' => '⚙️ スケジュールフィルター',
        'lbl_all' => '— すべて —',
        'lbl_vehicle' => '車両選択',
        'lbl_driver' => '運転手選択',
        'lbl_dept' => '部署選択',
        'lbl_status' => 'ステータス',
        'color_legend' => '🎨 ステータス凡例',
        'status_pending' => '承認待ち',
        'status_scheduled' => '配車済 (承認済)',
        'status_departed' => '外出中 (運行中)',
        'status_returned' => '帰着済 (完了)',
        'status_cancelled' => 'キャンセル',
        
        // Form & Modals
        'modal_title_add' => '➕ 新規車両運行予約',
        'modal_title_edit' => '✏️ 運行予約の編集',
        'modal_title_detail' => '📋 運行予約詳細情報',
        'lbl_requester' => '申請者 / 責任者',
        'lbl_dept_form' => '部署名',
        'lbl_companions' => '同乗者 (カンマ区切り)',
        'lbl_route' => '運行ルート',
        'lbl_origin' => '出発地',
        'lbl_destination' => '目的地',
        'lbl_purpose' => '目的 / 業務内容',
        'lbl_start_time' => '出発日時 (開始)',
        'lbl_end_time' => '帰着日時 (終了)',
        'btn_cancel' => 'キャンセル',
        'btn_save' => '💾 予約を保存',
        'btn_delete' => '🗑️ 予約を削除',
        'btn_edit' => '✏️ 予約を編集',
        
        // Warnings & Errors
        'err_time' => '終了日時は開始日時より後の時間を設定してください。',
        'err_vehicle_busy' => '選択した車両は、この時間帯に別の予約が入っています。',
        'err_driver_busy' => '選択した運転手は、この時間帯に別の運行が入っています。',
        'err_required' => '必須項目 (*) をすべて入力してください。'
    ]
];

$txt = $lang_pack[$lang];

// ── THUẬT TOÁN KIỂM TRA TRÙNG LỊCH (CONFLICT DETECTION) ──
function checkOverlapConflicts($pdo, $start, $end, $vehicle_id, $driver_id, $exclude_trip_id = 0) {
    global $txt;
    $errors = [];

    // Kiểm tra trùng xe
    $stmt = $pdo->prepare("
        SELECT COUNT(*) FROM trips 
        WHERE vehicle_id = ? 
          AND status IN ('pending', 'scheduled', 'departed') 
          AND id != ? 
          AND (departure_time < ? AND expected_return > ?)
    ");
    $stmt->execute([$vehicle_id, $exclude_trip_id, $end, $start]);
    if ($stmt->fetchColumn() > 0) {
        $errors[] = $txt['err_vehicle_busy'];
    }

    // Kiểm tra trùng tài xế
    $stmt = $pdo->prepare("
        SELECT COUNT(*) FROM trips 
        WHERE driver_id = ? 
          AND status IN ('pending', 'scheduled', 'departed') 
          AND id != ? 
          AND (departure_time < ? AND expected_return > ?)
    ");
    $stmt->execute([$driver_id, $exclude_trip_id, $end, $start]);
    if ($stmt->fetchColumn() > 0) {
        $errors[] = $txt['err_driver_busy'];
    }

    return $errors;
}

// ── 2. CỔNG AJAX API XỬ LÝ DỮ LIỆU ĐỘNG ──
if (isset($_GET['api'])) {
    header('Content-Type: application/json; charset=utf-8');
    $api = $_GET['api'];

    // LẤY DANH SÁCH CHUYẾN XE (ĐỒNG BỘ BỘ LỌC)
    if ($api === 'get_trips') {
        $where = [];
        $params = [];
        if (!empty($_GET['vehicle_id'])) {
            $where[] = "t.vehicle_id = ?";
            $params[] = (int)$_GET['vehicle_id'];
        }
        if (!empty($_GET['driver_id'])) {
            $where[] = "t.driver_id = ?";
            $params[] = (int)$_GET['driver_id'];
        }
        if (!empty($_GET['dept'])) {
            $where[] = "t.requester_dept = ?";
            $params[] = trim($_GET['dept']);
        }
        if (!empty($_GET['status'])) {
            $where[] = "t.status = ?";
            $params[] = trim($_GET['status']);
        }

        $sql = "SELECT t.*, v.plate_number, v.vehicle_name, d.full_name as driver_name 
                FROM trips t
                LEFT JOIN vehicles v ON t.vehicle_id = v.id
                LEFT JOIN drivers d ON t.driver_id = d.id";
        if (!empty($where)) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $trips = $stmt->fetchAll();

        $events = [];
        foreach ($trips as $t) {
            // Định dạng màu sắc chuẩn Apple theo trạng thái
            $color = '#0a84ff'; // Mặc định: Đã duyệt (Xanh dương)
            if ($t['status'] === 'pending') $color = '#ffcc00'; // Vàng hổ phách
            elseif ($t['status'] === 'departed') $color = '#f97316'; // Cam di chuyển
            elseif ($t['status'] === 'returned') $color = '#30d158'; // Xanh hoàn thành
            elseif ($t['status'] === 'cancelled') $color = '#8e8e93'; // Xám hủy

            $events[] = [
                'id' => $t['id'],
                'title' => $t['plate_number'] . ' — ' . $t['destination'],
                'start' => $t['departure_time'],
                'end' => $t['expected_return'],
                'color' => $color,
                'extendedProps' => [
                    'vehicle_id' => $t['vehicle_id'],
                    'plate' => $t['plate_number'],
                    'vname' => $t['vehicle_name'],
                    'driver_id' => $t['driver_id'],
                    'driver' => $t['driver_name'],
                    'requester' => $t['requester_name'],
                    'dept' => $t['requester_dept'],
                    'companions' => $t['companions'],
                    'origin' => $t['origin'],
                    'destination' => $t['destination'],
                    'purpose' => $t['purpose'],
                    'status' => $t['status']
                ]
            ];
        }
        echo json_encode($events);
        exit;
    }

    // LẤY TÀI NGUYÊN TRỐNG THEO KHUNG GIỜ CHỌN (TRÁNH TRÙNG LỊCH CHỦ ĐỘNG)
    if ($api === 'get_available_resources') {
        $start = $_GET['start'];
        $end = $_GET['end'];
        $exclude_id = (int)($_GET['exclude_id'] ?? 0);

        // Xe không trùng lịch
        $stmt = $pdo->prepare("
            SELECT * FROM vehicles 
            WHERE status != 'maintenance'
              AND id NOT IN (
                SELECT DISTINCT vehicle_id FROM trips 
                WHERE status IN ('pending', 'scheduled', 'departed') 
                  AND id != ? 
                  AND (departure_time < ? AND expected_return > ?)
            )
            ORDER BY plate_number
        ");
        $stmt->execute([$exclude_id, $end, $start]);
        $avail_vehicles = $stmt->fetchAll();

        // Tài xế không trùng lịch
        $stmt = $pdo->prepare("
            SELECT * FROM drivers 
            WHERE status != 'off'
              AND id NOT IN (
                SELECT DISTINCT driver_id FROM trips 
                WHERE status IN ('pending', 'scheduled', 'departed') 
                  AND id != ? 
                  AND (departure_time < ? AND expected_return > ?)
            )
            ORDER BY full_name
        ");
        $stmt->execute([$exclude_id, $end, $start]);
        $avail_drivers = $stmt->fetchAll();

        echo json_encode([
            'vehicles' => $avail_vehicles,
            'drivers' => $avail_drivers
        ]);
        exit;
    }

    // LƯU CHUYẾN XE (TẠO MỚI HOẶC CẬP NHẬT BIỂU MẪU)
    if ($api === 'save_trip') {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Yêu cầu không hợp lệ.']);
            exit;
        }

        $trip_id = (int)($_POST['id'] ?? 0);
        $vehicle_id = (int)$_POST['vehicle_id'];
        $driver_id = (int)$_POST['driver_id'];
        $requester_name = trim($_POST['requester_name']);
        $requester_dept = trim($_POST['requester_dept']);
        $companions = trim($_POST['companions']);
        $origin = trim($_POST['origin']);
        $destination = trim($_POST['destination']);
        $purpose = trim($_POST['purpose']);
        $start = trim($_POST['start']);
        $end = trim($_POST['end']);
        $status = trim($_POST['status'] ?? 'pending');

        if (empty($vehicle_id) || empty($driver_id) || empty($requester_name) || empty($origin) || empty($destination) || empty($purpose) || empty($start) || empty("end")) {
            echo json_encode(['success' => false, 'message' => $txt['err_required']]);
            exit;
        }

        if (strtotime($end) <= strtotime($start)) {
            echo json_encode(['success' => false, 'message' => $txt['err_time']]);
            exit;
        }

        // Kiểm tra xung đột thời gian thực
        $conflicts = checkOverlapConflicts($pdo, $start, $end, $vehicle_id, $driver_id, $trip_id);
        if (!empty($conflicts)) {
            echo json_encode(['success' => false, 'message' => implode(' ', $conflicts)]);
            exit;
        }

        if ($trip_id > 0) {
            $stmt = $pdo->prepare("
                UPDATE trips SET vehicle_id=?, driver_id=?, requester_name=?, requester_dept=?, companions=?, origin=?, destination=?, purpose=?, departure_time=?, expected_return=?, status=?
                WHERE id=?
            ");
            $stmt->execute([$vehicle_id, $driver_id, $requester_name, $requester_dept, $companions, $origin, $destination, $purpose, $start, $end, $status, $trip_id]);
        } else {
            $stmt = $pdo->prepare("
                INSERT INTO trips (vehicle_id, driver_id, requester_name, requester_dept, companions, origin, destination, purpose, departure_time, expected_return, status, created_by)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$vehicle_id, $driver_id, $requester_name, $requester_dept, $companions, $origin, $destination, $purpose, $start, $end, $status, $user['id']]);
        }

        echo json_encode(['success' => true]);
        exit;
    }

    // THAY ĐỔI THỜI GIAN NHANH BẰNG KÉO THẢ / CO GIÃN SỰ KIỆN
    if ($api === 'update_time') {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Yêu cầu không hợp lệ.']);
            exit;
        }

        $trip_id = (int)$_POST['id'];
        $start = trim($_POST['start']);
        $end = trim($_POST['end']);

        $stmt = $pdo->prepare("SELECT vehicle_id, driver_id FROM trips WHERE id = ?");
        $stmt->execute([$trip_id]);
        $trip = $stmt->fetch();

        if (!$trip) {
            echo json_encode(['success' => false, 'message' => 'Chuyến xe không tồn tại.']);
            exit;
        }

        if (strtotime($end) <= strtotime($start)) {
            echo json_encode(['success' => false, 'message' => $txt['err_time']]);
            exit;
        }

        // Rà soát xung đột trước khi cấp phép lưu tọa độ thời gian mới
        $conflicts = checkOverlapConflicts($pdo, $start, $end, $trip['vehicle_id'], $trip['driver_id'], $trip_id);
        if (!empty($conflicts)) {
            echo json_encode(['success' => false, 'message' => implode(' ', $conflicts)]);
            exit;
        }

        $stmt = $pdo->prepare("UPDATE trips SET departure_time=?, expected_return=? WHERE id=?");
        $stmt->execute([$start, $end, $trip_id]);

        echo json_encode(['success' => true]);
        exit;
    }

    // XÓA CHUYẾN XE TRỰC TIẾP
    if ($api === 'delete_trip') {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Yêu cầu không hợp lệ.']);
            exit;
        }
        $trip_id = (int)$_POST['id'];
        $stmt = $pdo->prepare("DELETE FROM trips WHERE id = ?");
        $stmt->execute([$trip_id]);
        echo json_encode(['success' => true]);
        exit;
    }
}

// Lấy tài nguyên ban đầu cho bộ lọc tĩnh
$all_vehicles = $pdo->query("SELECT * FROM vehicles ORDER BY plate_number")->fetchAll();
$all_drivers = $pdo->query("SELECT * FROM drivers ORDER BY full_name")->fetchAll();
$all_depts = $pdo->query("SELECT DISTINCT requester_dept FROM trips WHERE requester_dept != '' ORDER BY requester_dept")->fetchAll();
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= $txt['title'] ?> — <?= SITE_NAME ?></title>
<link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;600;700&display=swap" rel="stylesheet">
<!-- FullCalendar 5 CSS & JS CDN -->
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/locales-all.min.js"></script>
<style>
:root {
    /* ── HỆ MÀU TỐI TINH TẾ CHUẨN APPLE ── */
    --bg: #050a12;          
    --bg-card: #0e1b2e;     
    --bg-card-hover: #1e3556;
    --bg-input: #2c2c2e;
    --bg-navbar: rgba(22, 22, 23, 0.8);
    --text: #f5f5f7;
    --text-secondary: #86868b;
    --border: rgba(255, 255, 255, 0.1);
    --cyan: #06b6d4;--green: #30d158;--yellow: #f59e0b;
    --red: #ff453a;--blue: #0a84ff;--purple: #8b5cf6;
    --dim-gray: #64748b;
    --radius-card: 16px;
    --radius-btn: 980px; /* Apple pill */
    --radius-input: 10px;
}

body.light-theme {
    --bg: #f5f5f7;
    --bg-card: #ffffff;
    --bg-card-hover: #eaf0f6;
    --bg-input: #f5f5f7;
    --bg-navbar: rgba(255, 255, 255, 0.8);
    --text: #1d1d1f;
    --text-secondary: #86868b;
    --border: rgba(0, 0, 0, 0.08);
    --cyan: #0071e3;
    --green: #34c759;
    --red: #ff3b30;
}

* { margin: 0; padding: 0; box-sizing: border-box; }
body {
    background: var(--bg);
    color: var(--text);
    font-family: -apple-system, BlinkMacSystemFont, "SF Pro Text", "Be Vietnam Pro", sans-serif;
    min-height: 100vh;
    padding-bottom: 60px;
    transition: background 0.3s ease, color 0.3s ease;
}

/* ── NAVBAR (FROSTED GLASS EFFECT) ── */
.navbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 40px;
    height: 52px;
    background: var(--bg-navbar);
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    border-bottom: 1px solid var(--border);
    position: sticky;
    top: 0;
    z-index: 999;
}
.nav-brand {
    font-weight: 600;
    font-size: 15px;
    letter-spacing: -0.2px;
    display: flex;
    align-items: center;
    gap: 8px;
}
.nav-links { display: flex; gap: 24px; }
.nav-links a {
    color: var(--text-secondary);
    text-decoration: none;
    font-size: 13px;
    font-weight: 500;
    padding: 6px 12px;
    border-radius: 6px;
    transition: all 0.2s;
}
.nav-links a:hover { color: var(--text); }
.nav-links a.active { color: var(--cyan); }
.nav-user { display: flex; align-items: center; gap: 16px; font-size: 13px; }
.btn-logout { color: var(--red); text-decoration: none; font-weight: 500; }

/* ── LAYOUT ── */
.container {
    max-width: 1400px;
    margin: 30px auto 0;
    padding: 0 24px;
}
.booking-grid {
    display: grid;
    grid-template-columns: 280px 1fr;
    gap: 24px;
}
@media (max-width: 1024px) {
    .booking-grid { grid-template-columns: 1fr; }
}

/* ── SIDE PANEL (FILTERS & LEGENDS) ── */
.filter-panel {
    display: flex;
    flex-direction: column;
    gap: 20px;
}
.apple-card {
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: var(--radius-card);
    padding: 20px;
    box-shadow: 0 4px 24px rgba(0,0,0,0.15);
}
body.light-theme .apple-card { box-shadow: 0 8px 30px rgba(0,0,0,0.03); }
.card-title {
    font-size: 15px;
    font-weight: 700;
    margin-bottom: 16px;
    letter-spacing: -0.2px;
}
.form-group {
    display: flex;
    flex-direction: column;
    gap: 6px;
    margin-bottom: 12px;
}
.form-group:last-child { margin-bottom: 0; }
label {
    font-size: 11px;
    font-weight: 600;
    color: var(--text-secondary);
    text-transform: uppercase;
    letter-spacing: 0.3px;
}
.form-control {
    background: var(--bg-input);
    border: 1px solid var(--border);
    border-radius: var(--radius-input);
    color: var(--text);
    padding: 10px 14px;
    font-size: 13px;
    outline: none;
    transition: all 0.2s;
    width: 100%;
}
.form-control:focus { border-color: var(--cyan); }

/* LEGEND COLOR ITEMS */
.legend-item {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 13px;
    margin-bottom: 10px;
    color: var(--text);
}
.legend-dot {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    flex-shrink: 0;
}

/* ── FULLCALENDAR CUSTOM APPLE THEME ── */
.calendar-card {
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: var(--radius-card);
    padding: 24px;
    box-shadow: 0 4px 24px rgba(0,0,0,0.15);
}
.fc-theme-standard td, .fc-theme-standard th {
    border-color: var(--border) !important;
}
.fc-theme-standard .fc-scrollgrid {
    border-color: var(--border) !important;
}
.fc-col-header-cell-cushion, .fc-daygrid-day-number {
    color: var(--text) !important;
    font-size: 12px;
    text-decoration: none !important;
}
.fc-day-today {
    background: rgba(6, 182, 212, 0.05) !important;
}
.fc-button-primary {
    background-color: var(--bg-input) !important;
    border-color: var(--border) !important;
    color: var(--text) !important;
    border-radius: var(--radius-input) !important;
    font-size: 13px !important;
    font-weight: 600 !important;
    transition: all 0.2s !important;
}
.fc-button-primary:hover {
    background-color: var(--text) !important;
    color: var(--bg) !important;
    border-color: var(--text) !important;
}
.fc-button-active {
    background-color: var(--cyan) !important;
    border-color: var(--cyan) !important;
    color: #fff !important;
}
.fc-event {
    border: none !important;
    border-radius: 6px !important;
    padding: 4px 6px !important;
    cursor: pointer;
    transition: transform 0.15s ease !important;
}
.fc-event:hover {
    transform: scale(1.02);
}

/* ── APPLE BUTTONS ── */
.btn-apple {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 9px 20px;
    border-radius: var(--radius-btn);
    font-size: 13px;
    font-weight: 500;
    cursor: pointer;
    border: none;
    transition: all 0.2s cubic-bezier(0.25, 1, 0.5, 1);
    font-family: inherit;
    text-decoration: none;
}
.btn-apple-primary { background: var(--cyan); color: #fff; }
.btn-apple-primary:hover { background: var(--accent-hover); transform: scale(1.02); }
.btn-apple-secondary { background: var(--bg-input); color: var(--text); border: 1px solid var(--border); }
.btn-apple-secondary:hover { background: var(--text); color: var(--bg); }
.btn-apple-danger { background: rgba(255, 69, 58, 0.15); color: var(--red); border: 1px solid rgba(255, 69, 58, 0.2); }
.btn-apple-danger:hover { background: var(--red); color: #fff; }

/* ── MODALS (FROSTED GLASS CARD) ── */
.modal-backdrop {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.6);
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
    z-index: 1000;
    align-items: center;
    justify-content: center;
}
.modal-backdrop.open { display: flex; }
.modal {
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: 20px;
    width: 500px;
    max-width: 95vw;
    box-shadow: 0 20px 60px rgba(0,0,0,0.5);
    animation: modalIn 0.3s cubic-bezier(0.25, 1, 0.5, 1);
    overflow: hidden;
}
@keyframes modalIn {
    from { opacity: 0; transform: scale(0.96) translateY(10px); }
    to { opacity: 1; transform: scale(1) translateY(0); }
}
.modal-header {
    padding: 18px 24px;
    background: rgba(255,255,255,0.01);
    border-bottom: 1px solid var(--border);
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.modal-title { font-size: 16px; font-weight: 700; color: var(--text); }
.modal-close { background: none; border: none; color: var(--text-secondary); font-size: 18px; cursor: pointer; }
.modal-close:hover { color: var(--text); }
.modal-body {
    padding: 24px;
    display: flex;
    flex-direction: column;
    gap: 16px;
    max-height: 75vh;
    overflow-y: auto;
}
.modal-footer {
    padding: 16px 24px;
    border-top: 1px solid var(--border);
    display: flex;
    gap: 10px;
    justify-content: flex-end;
}

/* ── TOGGLE BUTTONS ── */
.lang-toggle-btn, .theme-toggle-btn {
    background: var(--bg-input);
    border: 1px solid var(--border);
    color: var(--text-secondary);
    padding: 6px;
    border-radius: 8px;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s ease;
    width: 32px; height: 32px;
    text-decoration: none;
    font-size: 11px;
    font-weight: 700;
}
.lang-toggle-btn:hover, .theme-toggle-btn:hover {
    color: var(--text);
    border-color: var(--cyan);
}

/* ── DETAIL MODAL CARD ── */
.detail-group {
    background: rgba(255, 255, 255, 0.02);
    border: 1px solid var(--border);
    border-radius: 10px;
    padding: 12px;
}
body.light-theme .detail-group { background: rgba(0,0,0,0.015); }
.detail-label { font-size: 10px; color: var(--text-secondary); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px; font-weight: 600; }
.detail-value { font-size: 14px; font-weight: 600; color: var(--text); }

.companion-item { display: flex; align-items: center; gap: 8px; padding: 4px 0; font-size: 13px; }
.companion-dot { width: 6px; height: 6px; background: var(--cyan); border-radius: 50%; }

.custom-scroll::-webkit-scrollbar { width: 4px; }
.custom-scroll::-webkit-scrollbar-track { background: transparent; }
.custom-scroll::-webkit-scrollbar-thumb { background: var(--border); border-radius: 2px; }
</style>
</head>
<body>

<nav class="navbar">
    <div class="nav-brand"><span class="nav-icon">🚗</span><span><?= SITE_NAME ?></span></div>
    <div class="nav-links">
        <a href="index.php?lang=<?= $lang ?>"><?= $txt['nav_trips'] ?></a>
        <a href="trip_form.php?lang=<?= $lang ?>"><?= $txt['nav_create'] ?></a>
        <a href="booking.php?lang=<?= $lang ?>" class="active"><?= $txt['nav_booking'] ?></a>
        <?php if (isAdmin()): ?><a href="vehicles.php?lang=<?= $lang ?>"><?= $txt['nav_vehicles'] ?></a><?php endif; ?>
        <a href="dashboard.php?lang=<?= $lang ?>" target="_blank" class="nav-dashboard"><?= $txt['nav_dashboard'] ?></a>
    </div>
    <div class="nav-user">
        <a href="?lang=<?= $lang === 'vi' ? 'jp' : 'vi' ?>" class="lang-toggle-btn" title="Chuyển đổi ngôn ngữ">
            <?= $lang === 'vi' ? 'JP' : 'VI' ?>
        </a>
        <button id="theme-toggle" class="theme-toggle-btn" title="Thay đổi giao diện">
            <svg class="sun-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="5"></circle><line x1="12" y1="1" x2="12" y2="3"></line><line x1="12" y1="21" x2="12" y2="23"></line><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line><line x1="1" y1="12" x2="3" y2="12"></line><line x1="21" y1="12" x2="23" y2="12"></line><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line></svg>
            <svg class="moon-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path></svg>
        </button>
        <span>👤 <?= sanitize($user['full_name']) ?></span>
        <a href="logout.php" class="btn-logout"><?= $txt['logout'] ?></a>
    </div>
</nav>

<div class="container">
    <div class="booking-grid">
        
        <!-- BỘ LỌC ĐA NĂNG BÊN TRÁI -->
        <div class="filter-panel">
            <div class="apple-card">
                <div class="card-title"><?= $txt['filter_title'] ?></div>
                
                <div class="form-group">
                    <label><?= $txt['lbl_vehicle'] ?></label>
                    <select id="filterVehicle" class="form-control" onchange="refreshCalendar()">
                        <option value=""><?= $txt['lbl_all'] ?></option>
                        <?php foreach ($all_vehicles as $v): ?>
                        <option value="<?= $v['id'] ?>"><?= sanitize($v['plate_number']) ?> — <?= sanitize($v['vehicle_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label><?= $txt['lbl_driver'] ?></label>
                    <select id="filterDriver" class="form-control" onchange="refreshCalendar()">
                        <option value=""><?= $txt['lbl_all'] ?></option>
                        <?php foreach ($all_drivers as $d): ?>
                        <option value="<?= $d['id'] ?>"><?= sanitize($d['full_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label><?= $txt['lbl_dept'] ?></label>
                    <select id="filterDept" class="form-control" onchange="refreshCalendar()">
                        <option value=""><?= $txt['lbl_all'] ?></option>
                        <?php foreach ($all_depts as $dept): ?>
                        <option value="<?= sanitize($dept['requester_dept']) ?>"><?= sanitize($dept['requester_dept']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label><?= $txt['lbl_status'] ?></label>
                    <select id="filterStatus" class="form-control" onchange="refreshCalendar()">
                        <option value=""><?= $txt['lbl_all'] ?></option>
                        <option value="pending"><?= $txt['status_pending'] ?></option>
                        <option value="scheduled"><?= $txt['status_scheduled'] ?></option>
                        <option value="departed"><?= $txt['status_departed'] ?></option>
                        <option value="returned"><?= $txt['status_returned'] ?></option>
                        <option value="cancelled"><?= $txt['status_cancelled'] ?></option>
                    </select>
                </div>
            </div>

            <!-- CHÚ THÍCH MÀU SẮC ĐỒNG BỘ -->
            <div class="apple-card">
                <div class="card-title"><?= $txt['color_legend'] ?></div>
                <div class="legend-item"><div class="legend-dot" style="background:#ffcc00;"></div><span><?= $txt['status_pending'] ?></span></div>
                <div class="legend-item"><div class="legend-dot" style="background:#0a84ff;"></div><span><?= $txt['status_scheduled'] ?></span></div>
                <div class="legend-item"><div class="legend-dot" style="background:#f97316;"></div><span><?= $txt['status_departed'] ?></span></div>
                <div class="legend-item"><div class="legend-dot" style="background:#30d158;"></div><span><?= $txt['status_returned'] ?></span></div>
                <div class="legend-item"><div class="legend-dot" style="background:#8e8e93;"></div><span><?= $txt['status_cancelled'] ?></span></div>
            </div>
        </div>

        <!-- KHU VỰC LỊCH CHUYẾN XE (FULLCALENDAR) -->
        <div class="calendar-card">
            <h2 style="margin-bottom:20px; font-size:20px; letter-spacing: -0.3px;"><?= $txt['title'] ?></h2>
            <div id="calendar"></div>
        </div>

    </div>
</div>

<!-- POPUP 1: ĐĂNG KÝ / CHỈNH SỬA CHUYẾN XE -->
<div class="modal-backdrop" id="modalBooking">
    <div class="modal">
        <div class="modal-header">
            <span class="modal-title" id="modalBookingTitle">—</span>
            <button class="modal-close" onclick="closeModal('modalBooking')">✕</button>
        </div>
        <form id="formBooking" onsubmit="saveBookingForm(event)">
            <input type="hidden" name="id" id="bId">
            <div class="modal-body custom-scroll">
                
                <div class="form-row">
                    <div class="form-group">
                        <label><?= $txt['lbl_start_time'] ?> <span class="req">*</span></label>
                        <input type="datetime-local" name="start" id="bStart" class="form-control" onchange="onTimeRangeChanged()" required>
                    </div>
                    <div class="form-group">
                        <label><?= $txt['lbl_end_time'] ?> <span class="req">*</span></label>
                        <input type="datetime-local" name="end" id="bEnd" class="form-control" onchange="onTimeRangeChanged()" required>
                    </div>
                </div>

                <div class="form-section-title" style="font-size: 12px; font-weight: 700; color: var(--cyan); margin: 8px 0 4px; text-transform: uppercase;"><?= $txt['sec_veh_driver'] ?></div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label><?= $txt['lbl_vehicle'] ?> <span class="req">*</span></label>
                        <select name="vehicle_id" id="bVehicle" class="form-control" required>
                            <!-- AJAX load dynamic -->
                        </select>
                    </div>
                    <div class="form-group">
                        <label><?= $txt['lbl_driver'] ?> <span class="req">*</span></label>
                        <select name="driver_id" id="bDriver" class="form-control" required>
                            <!-- AJAX load dynamic -->
                        </select>
                    </div>
                </div>

                <div class="form-section-title" style="font-size: 12px; font-weight: 700; color: var(--cyan); margin: 8px 0 4px; text-transform: uppercase;">👤 NHÂN SỰ YÊU CẦU</div>

                <div class="form-row">
                    <div class="form-group">
                        <label><?= $txt['lbl_requester'] ?> <span class="req">*</span></label>
                        <input type="text" name="requester_name" id="bRequester" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label><?= $txt['lbl_dept_form'] ?></label>
                        <input type="text" name="requester_dept" id="bDept" class="form-control">
                    </div>
                </div>

                <div class="form-group">
                    <label><?= $txt['lbl_companions'] ?></label>
                    <input type="text" name="companions" id="bCompanions" class="form-control">
                </div>

                <div class="form-section-title" style="font-size: 12px; font-weight: 700; color: var(--cyan); margin: 8px 0 4px; text-transform: uppercase;"><?= $txt['lbl_route'] ?></div>

                <div class="form-row">
                    <div class="form-group">
                        <label><?= $txt['lbl_origin'] ?> <span class="req">*</span></label>
                        <input type="text" name="origin" id="bOrigin" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label><?= $txt['lbl_destination'] ?> <span class="req">*</span></label>
                        <input type="text" name="destination" id="bDestination" class="form-control" required>
                    </div>
                </div>

                <div class="form-group">
                    <label><?= $txt['lbl_purpose'] ?> <span class="req">*</span></label>
                    <textarea name="purpose" id="bPurpose" class="form-control" rows="2" required></textarea>
                </div>

                <!-- ADMIN mới hiển thị được trạng thái -->
                <?php if (isAdmin()): ?>
                <div class="form-group" style="max-width: 200px;">
                    <label><?= $txt['lbl_status'] ?></label>
                    <select name="status" id="bStatus" class="form-control">
                        <option value="pending"><?= $txt['status_pending'] ?></option>
                        <option value="scheduled"><?= $txt['status_scheduled'] ?></option>
                        <option value="departed"><?= $txt['status_departed'] ?></option>
                        <option value="returned"><?= $txt['status_returned'] ?></option>
                        <option value="cancelled"><?= $txt['status_cancelled'] ?></option>
                    </select>
                </div>
                <?php endif; ?>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn-apple btn-apple-secondary" onclick="closeModal('modalBooking')"><?= $txt['btn_cancel'] ?></button>
                <button type="submit" class="btn-apple btn-apple-primary"><?= $txt['btn_save'] ?></button>
            </div>
        </form>
    </div>
</div>

<!-- POPUP 2: HIỂN THỊ CHI TIẾT ĐẶT XE -->
<div class="modal-backdrop" id="modalDetail">
    <div class="modal">
        <div class="modal-header">
            <span class="modal-title"><?= $txt['modal_title_detail'] ?></span>
            <button class="modal-close" onclick="closeModal('modalDetail')">✕</button>
        </div>
        <div class="modal-body custom-scroll">
            <div style="display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid var(--border); padding-bottom:12px; margin-bottom: 6px;">
                <div>
                    <span class="tc-plate" id="dtPlate" style="font-size:16px;">—</span>
                    <div id="dtVname" style="font-size:12px; color:var(--text-secondary); margin-top:6px; font-weight:600;">—</div>
                </div>
                <span class="badge" id="dtStatusBadge" style="font-size:11px; padding:4px 12px; border-radius:99px; text-transform:uppercase; font-weight:700;">—</span>
            </div>

            <div class="detail-group">
                <div class="detail-label"><?= $txt['lbl_requester'] ?></div>
                <div class="detail-value" id="dtRequester">—</div>
                <div style="font-size:12px; color:var(--text-secondary); margin-top:2px;" id="dtDept">—</div>
            </div>

            <div class="detail-group">
                <div class="detail-label"><?= $txt['lbl_companions'] ?></div>
                <div id="dtCompanionsList"></div>
            </div>

            <div class="detail-group" style="display:grid; grid-template-columns: 1fr 1fr; gap:12px;">
                <div>
                    <div class="detail-label"><?= $txt['lbl_origin'] ?></div>
                    <div class="detail-value" id="dtOrigin">—</div>
                </div>
                <div>
                    <div class="detail-label"><?= $txt['lbl_destination'] ?></div>
                    <div class="detail-value" id="dtDestination">—</div>
                </div>
            </div>

            <div class="detail-group">
                <div class="detail-label"><?= $txt['lbl_purpose'] ?></div>
                <div class="detail-value" style="font-weight:normal; line-height: 1.4;" id="dtPurpose">—</div>
            </div>

            <div class="detail-group">
                <div class="detail-label"><?= $txt['lbl_driver'] ?></div>
                <div class="detail-value" id="dtDriver" style="color:var(--cyan);">—</div>
            </div>

            <div class="detail-group" style="display:grid; grid-template-columns: 1fr 1fr; gap:12px;">
                <div>
                    <div class="detail-label"><?= $txt['lbl_start_time'] ?></div>
                    <div class="detail-value" style="font-family:monospace;" id="dtStart">—</div>
                </div>
                <div>
                    <div class="detail-label"><?= $txt['lbl_end_time'] ?></div>
                    <div class="detail-value" style="font-family:monospace;" id="dtEnd">—</div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-apple btn-apple-secondary" onclick="closeModal('modalDetail')">Đóng</button>
            <?php if (isAdmin()): ?>
            <button type="button" class="btn-apple btn-apple-danger" id="dtBtnDelete" onclick="deleteBooking()"><?= $txt['btn_delete'] ?></button>
            <button type="button" class="btn-apple btn-apple-primary" id="dtBtnEdit" onclick="editBooking()"><?= $txt['btn_edit'] ?></button>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
let calendar;
let activeEvent = null; // Sự kiện đang được mở chi tiết

document.addEventListener('DOMContentLoaded', function() {
    // Khởi tạo lịch FullCalendar v5 chuyên nghiệp
    const calendarEl = document.getElementById('calendar');
    calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'timeGridWeek',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        locale: '<?= $lang ?>' === 'jp' ? 'ja' : 'vi',
        editable: <?= isAdmin() ? 'true' : 'false' ?>, // Chỉ Admin được kéo thả
        selectable: true,
        selectMirror: true,
        dayMaxEvents: true,
        slotMinTime: '06:00:00',
        slotMaxTime: '22:00:00',
        events: 'booking.php?api=get_trips',

        // Khi thả sự kiện (Thay đổi tọa độ thời gian di chuyển)
        eventDrop: function(info) {
            updateTripTime(info);
        },
        // Khi kéo giãn sự kiện (Thay đổi thời lượng chuyến xe)
        eventResize: function(info) {
            updateTripTime(info);
        },
        // Khi bấm kéo chọn khung giờ trống để đăng ký nhanh
        select: function(info) {
            openCreateModal(info.startStr, info.endStr);
        },
        // Khi click xem chi tiết chuyến xe
        eventClick: function(info) {
            openDetailModal(info.event);
        }
    });
    calendar.render();
});

// LÀM MỚI LỊCH DI CHUYỂN DỰA THEO BỘ LỌC ĐA NĂNG
function refreshCalendar() {
    const vehicle = document.getElementById('filterVehicle').value;
    const driver = document.getElementById('filterDriver').value;
    const dept = document.getElementById('filterDept').value;
    const status = document.getElementById('filterStatus').value;

    const url = `booking.php?api=get_trips&vehicle_id=${vehicle}&driver_id=${driver}&dept=${encodeURIComponent(dept)}&status=${status}`;
    
    // Gỡ nguồn dữ liệu cũ và nạp bộ lọc động
    calendar.getEventSources()[0].remove();
    calendar.addEventSource(url);
}

// KHAI BÁO CÁC MỐC THỜI GIAN ĐỂ AJAX QUÉT TÀI NGUYÊN TRỐNG THỰC TẾ
let originalVehicleId = 0;
let originalDriverId = 0;

function onTimeRangeChanged() {
    const startVal = document.getElementById('bStart').value;
    const endVal = document.getElementById('bEnd').value;
    const tripId = document.getElementById('bId').value;

    if (!startVal || !endVal) return;

    // Gọi API động lấy tài xế và xe trống
    fetch(`booking.php?api=get_available_resources&start=${encodeURIComponent(formatISO(startVal))}&end=${encodeURIComponent(formatISO(endVal))}&exclude_id=${tripId}`)
        .then(res => res.json())
        .then(data => {
            const vSelect = document.getElementById('bVehicle');
            const dSelect = document.getElementById('bDriver');

            vSelect.innerHTML = '';
            dSelect.innerHTML = '';

            // Nạp danh sách xe khả dụng
            data.vehicles.forEach(v => {
                const opt = document.createElement('option');
                opt.value = v.id;
                opt.textContent = `${v.plate_number} — ${v.vehicle_name}`;
                if (v.id === originalVehicleId) opt.selected = true;
                vSelect.appendChild(opt);
            });

            // Nạp danh sách tài xế khả dụng
            data.drivers.forEach(d => {
                const opt = document.createElement('option');
                opt.value = d.id;
                opt.textContent = d.full_name;
                if (d.id === originalDriverId) opt.selected = true;
                dSelect.appendChild(opt);
            });
        });
}

function formatISO(dateTimeStr) {
    // Chuyển đổi định dạng DATETIME của HTML5 thành format chuẩn SQL
    return dateTimeStr.replace('T', ' ') + ':00';
}

function formatDisplayDate(dateStr) {
    if (!dateStr) return '—';
    const d = new Date(dateStr);
    const pad = v => String(v).padStart(2, '0');
    return `${pad(d.getDate())}/${pad(d.getMonth()+1)}/${d.getFullYear()} ${pad(d.getHours())}:${pad(d.getMinutes())}`;
}

// ── ĐIỀU KHIỂN CÁC POPUP MODALS ──
function openModal(id) {
    document.getElementById(id).classList.add('open');
}
function closeModal(id) {
    document.getElementById(id).classList.remove('open');
}

// BẬT POPUP TẠO MỚI KHI CLICK CHỌN GIỜ TRÊN LỊCH
function openCreateModal(startISO, endISO) {
    document.getElementById('formBooking').reset();
    document.getElementById('bId').value = '';
    document.getElementById('modalBookingTitle').textContent = '<?= $txt['modal_title_add'] ?>';
    
    // Đưa thời gian đã chọn trên lịch vào form (Cắt bỏ timezone để phù hợp datetime-local)
    document.getElementById('bStart').value = startISO.substring(0, 16);
    document.getElementById('bEnd').value = endISO.substring(0, 16);

    originalVehicleId = 0;
    originalDriverId = 0;

    onTimeRangeChanged(); // Kích hoạt nạp tài nguyên trống
    openModal('modalBooking');
}

// XEM CHI TIẾT CHUYẾN XE KHI CLICK VÀO KHỐI LỊCH
function openDetailModal(event) {
    activeEvent = event;
    const props = event.extendedProps;

    document.getElementById('dtPlate').textContent = props.plate;
    document.getElementById('dtVname').textContent = props.vname;
    document.getElementById('dtRequester').textContent = props.requester;
    document.getElementById('dtDept').textContent = props.dept || '—';
    document.getElementById('dtOrigin').textContent = props.origin;
    document.getElementById('dtDestination').textContent = props.destination;
    document.getElementById('dtPurpose').textContent = props.purpose;
    document.getElementById('dtDriver').textContent = props.driver;
    document.getElementById('dtStart').textContent = formatDisplayDate(event.start);
    document.getElementById('dtEnd').textContent = formatDisplayDate(event.end);

    // Xử lý danh sách bạn đồng hành
    const companionsList = document.getElementById('dtCompanionsList');
    companionsList.innerHTML = '';
    if (!props.companions || props.companions.trim() === '') {
        companionsList.innerHTML = `<div style="color:var(--text-secondary);font-size:12px;font-style:italic;">${translations.modal_empty}</div>`;
    } else {
        props.companions.split(',').forEach(c => {
            const div = document.createElement('div');
            div.className = 'companion-item';
            div.innerHTML = `<div class="companion-dot"></div><div>${c.trim()}</div>`;
            companionsList.appendChild(div);
        });
    }

    // Trạng thái Badge mượt mà
    const statusBadge = document.getElementById('dtStatusBadge');
    statusBadge.className = 'badge';
    let statusLabel = '';
    if (props.status === 'pending') { statusBadge.style.backgroundColor = '#ffcc00'; statusBadge.style.color = '#000'; statusLabel = '<?= $txt['status_pending'] ?>'; }
    else if (props.status === 'scheduled') { statusBadge.style.backgroundColor = '#0a84ff'; statusBadge.style.color = '#fff'; statusLabel = '<?= $txt['status_scheduled'] ?>'; }
    else if (props.status === 'departed') { statusBadge.style.backgroundColor = '#f97316'; statusBadge.style.color = '#fff'; statusLabel = '<?= $txt['status_departed'] ?>'; }
    else if (props.status === 'returned') { statusBadge.style.backgroundColor = '#30d158'; statusBadge.style.color = '#fff'; statusLabel = '<?= $txt['status_returned'] ?>'; }
    else if (props.status === 'cancelled') { statusBadge.style.backgroundColor = '#8e8e93'; statusBadge.style.color = '#fff'; statusLabel = '<?= $txt['status_cancelled'] ?>'; }
    statusBadge.textContent = statusLabel;

    openModal('modalDetail');
}

// CHUYỂN TỪ POPUP CHI TIẾT SANG BIỂU MẪU SỬA (CHỈ ADMIN)
function editBooking() {
    if (!activeEvent) return;
    closeModal('modalDetail');

    const props = activeEvent.extendedProps;
    document.getElementById('bId').value = activeEvent.id;
    document.getElementById('modalBookingTitle').textContent = '<?= $txt['modal_title_edit'] ?>';
    
    document.getElementById('bStart').value = activeEvent.startStr.substring(0, 16);
    document.getElementById('bEnd').value = activeEvent.endStr.substring(0, 16);
    document.getElementById('bRequester').value = props.requester;
    document.getElementById('bDept').value = props.dept;
    document.getElementById('bCompanions').value = props.companions;
    document.getElementById('bOrigin').value = props.origin;
    document.getElementById('bDestination').value = props.destination;
    document.getElementById('bPurpose').value = props.purpose;
    
    if (document.getElementById('bStatus')) {
        document.getElementById('bStatus').value = props.status;
    }

    // Ghim xe và tài xế hiện tại để bộ lọc không lọc mất
    originalVehicleId = props.vehicle_id;
    originalDriverId = props.driver_id;

    onTimeRangeChanged(); // Kích hoạt bộ lọc
    openModal('modalBooking');
}

// LƯU FORM ĐẶT XE QUA AJAX
function saveBookingForm(e) {
    e.preventDefault();
    const fd = new FormData(document.getElementById('formBooking'));
    
    // Ép giờ giấc về định dạng SQL
    fd.set('start', formatISO(fd.get('start')));
    fd.set('end', formatISO(fd.get('end')));

    fetch('booking.php?api=save_trip', {
        method: 'POST',
        body: fd
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            closeModal('modalBooking');
            calendar.refetchEvents(); // Nạp lại lịch ngay lập tức
        } else {
            alert('⚠️ Thao tác thất bại: ' + data.message);
        }
    });
}

// XỬ LÝ KÉO THẢ VÀ CO GIÃN SỰ KIỆN (REAL-TIME CONFLICT CHECK)
function updateTripTime(info) {
    const fd = new FormData();
    fd.append('id', info.event.id);
    
    // Định dạng lại mốc giờ mới sau khi di dời sự kiện
    const startStr = info.event.start.toISOString().replace('T', ' ').substring(0, 19);
    const endStr = info.event.end ? info.event.end.toISOString().replace('T', ' ').substring(0, 19) : startStr;
    
    fd.append('start', startStr);
    fd.append('end', endStr);

    fetch('booking.php?api=update_time', {
        method: 'POST',
        body: fd
    })
    .then(res => res.json())
    .then(data => {
        if (!data.success) {
            alert('⚠️ ' + data.message);
            info.revert(); // Trả sự kiện về vị trí cũ nếu dính trùng lịch
        }
    });
}

// XÓA CHUYẾN ĐI (CHỈ ADMIN)
function deleteBooking() {
    if (!activeEvent) return;
    if (!confirm('Xóa lịch đặt chuyến xe này?')) return;

    const fd = new FormData();
    fd.append('id', activeEvent.id);

    fetch('booking.php?api=delete_trip', {
        method: 'POST',
        body: fd
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            closeModal('modalDetail');
            calendar.refetchEvents();
        } else {
            alert('⚠️ Không thể xóa: ' + data.message);
        }
    });
}

// ── ĐIỀU KHIỂN CHUYỂN ĐỔI THEME HOÀN TOÀN ĐỒNG BỘ ──
document.getElementById('theme-toggle').addEventListener('click', function() {
    document.body.classList.toggle('light-theme');
    if (document.body.classList.contains('light-theme')) {
        localStorage.setItem('panel-theme', 'light');
    } else {
        localStorage.setItem('panel-theme', 'dark');
    }
});
</script>
</body>
</html>