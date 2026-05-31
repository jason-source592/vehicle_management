<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/sync_status.php';
requireLogin();

$user = currentUser();
$tripId = (int)($_GET['id'] ?? 0);
$trip = null;
$errors = [];
$success = '';

// ── KHỞI TẠO BỘ LỌC NGÔN NGỮ ĐỒNG BỘ THEO URL (MẶC ĐỊNH LÀ VI) ──
$lang = $_GET['lang'] ?? 'vi';
if (!in_array($lang, ['vi', 'jp'])) {
    $lang = 'vi';
}

// ── BỘ TỪ ĐIỂN SONG NGỮ VIỆT - NHẬT TOÀN DIỆN CHO FORM NHẬP LIỆU ──
$lang_pack = [
    'vi' => [
        'title_edit' => '✏️ Chỉnh Sửa Chuyến Xe #',
        'title_create' => '➕ Tạo Chuyến Xe Mới',
        'back_link' => '← Quay lại danh sách',
        'nav_trips' => '📋 Chuyến Xe',
        'nav_create' => '➕ Tạo Chuyến',
        'nav_vehicles' => '🚙 Xe & Tài Xế',
        'nav_dashboard' => '🖥️ Màn Bảo Vệ',
        'logout' => 'Đăng xuất',
        'sec_veh_driver' => '🚗 Thông Tin Xe & Tài Xế',
        'lbl_vehicle' => 'Xe',
        'opt_vehicle' => '— Chọn xe —',
        'lbl_driver' => 'Tài Xế',
        'opt_driver' => '— Chọn tài xế —',
        'status_busy' => ' (Đang bận)',
        'sec_requester' => '👤 Người Yêu Cầu',
        'lbl_name' => 'Họ tên',
        'lbl_dept' => 'Phòng / Ban',
        'lbl_companions' => 'Người đi cùng',
        'hint_companions' => '💡 Nhập các tên cách nhau bằng dấu phẩy (,) nếu có người đi cùng',
        'sec_trip_info' => '📍 Thông Tin Chuyến Đi',
        'lbl_origin' => 'Điểm xuất phát',
        'lbl_destination' => 'Điểm đến',
        'lbl_purpose' => 'Mục đích / Lý do',
        'lbl_depart_time' => 'Giờ xuất phát',
        'hint_time_format' => '💡 Nhập giờ theo format 24h (VD: 14:30 = 2:30 chiều)',
        'lbl_expected_return' => 'Dự kiến về lúc',
        'hint_optional' => '💡 Để trống nếu chưa biết',
        'sec_gate_info' => '🛡️ Thông Tin Cổng',
        'lbl_gate_out' => 'Ghi chú xuất cổng',
        'lbl_gate_in' => 'Ghi chú nhập cổng',
        'lbl_status' => 'Trạng thái',
        'status_scheduled' => 'Đã lên lịch',
        'status_departed' => 'Đã xuất phát',
        'status_returned' => 'Đã về',
        'status_cancelled' => 'Đã hủy',
        'btn_cancel' => 'Hủy',
        'btn_save' => '💾 Lưu Thay Đổi',
        'btn_create' => '✅ Tạo Chuyến Xe',
        'err_vehicle' => 'Vui lòng chọn xe.',
        'err_driver' => 'Vui lòng chọn tài xế.',
        'err_requester' => 'Vui lòng nhập tên người yêu cầu.',
        'err_origin' => 'Vui lòng nhập điểm xuất phát.',
        'err_destination' => 'Vui lòng nhập điểm đến.',
        'err_purpose' => 'Vui lòng nhập mục đích.',
        'err_depart_time' => 'Vui lòng nhập giờ xuất phát.',
        'success_update' => 'Cập nhật chuyến xe thành công!',
        'success_create' => 'Tạo chuyến xe thành công!',
        'msg_created' => '✅ Chuyến xe đã được tạo thành công! Dashboard bảo vệ sẽ cập nhật ngay.',
        
        // Cảnh báo trùng lịch bổ sung
        'err_date_invalid' => 'Giờ dự kiến về phải sau giờ xuất phát.',
        'err_vehicle_busy' => 'Phương tiện này đã có lịch chạy hoặc đang di chuyển trong khoảng thời gian được chọn.',
        'err_driver_busy' => 'Tài xế này đã có lịch chạy hoặc đang làm nhiệm vụ trong khoảng thời gian được chọn.'
    ],
    'jp' => [
        'title_edit' => '✏️ 運行情報の編集 #',
        'title_create' => '➕ 新規運行登録',
        'back_link' => '← 運行リストに戻る',
        'nav_trips' => '📋 運行リスト',
        'nav_create' => '➕ 運行作成',
        'nav_vehicles' => '🚙 車両・運転手',
        'nav_dashboard' => '🖥️ 警備員画面',
        'logout' => 'ログアウト',
        'sec_veh_driver' => '🚗 車両・運転手情報',
        'lbl_vehicle' => '車両',
        'opt_vehicle' => '— 車両を選択 —',
        'lbl_driver' => '運転手',
        'opt_driver' => '— 運転手を選択 —',
        'status_busy' => ' (運行中)',
        'sec_requester' => '👤 申請者情報',
        'lbl_name' => '申請者氏名',
        'lbl_dept' => '部署名',
        'lbl_companions' => '同乗者',
        'hint_companions' => '💡 同乗者がいる場合は、カンマ（,）で区切って名前を入力してください。',
        'sec_trip_info' => '📍 運行詳細情報',
        'lbl_origin' => '出発地',
        'lbl_destination' => '目的地',
        'lbl_purpose' => '目的 / 理由',
        'lbl_depart_time' => '出発日時',
        'hint_time_format' => '💡 24時間形式で入力してください（例：14:30）',
        'lbl_expected_return' => '帰着予定日時',
        'hint_optional' => '💡 不明な場合は空欄のままで構いません',
        'sec_gate_info' => '🛡️ 門衛確認情報',
        'lbl_gate_out' => '出門備考',
        'lbl_gate_in' => '入門備考',
        'lbl_status' => 'ステータス',
        'status_scheduled' => '配車済',
        'status_departed' => '出発済',
        'status_returned' => '帰着済',
        'status_cancelled' => 'キャンセル',
        'btn_cancel' => '戻る',
        'btn_save' => '💾 変更を保存',
        'btn_create' => '✅ 運行を登録',
        'err_vehicle' => '車両を選択してください。',
        'err_driver' => '運転手を選択してください。',
        'err_requester' => '申請者氏名を入力してください。',
        'err_origin' => '出発地を入力してください。',
        'err_destination' => '目的地を入力してください。',
        'err_purpose' => '目的を入力してください。',
        'err_depart_time' => '出発時刻を入力してください。',
        'success_update' => '運行情報の更新に成功しました！',
        'success_create' => '運行の登録に成功しました！',
        'msg_created' => '✅ 運行の登録に成功しました！警備員ダッシュボードに即座に反映されます。',
        
        // Cảnh báo trùng lịch bổ sung
        'err_date_invalid' => '帰着予定日時は出発日時より後の時刻にしてください。',
        'err_vehicle_busy' => '選択された車両は、指定の時間帯に既に運行中または予約が入っています。',
        'err_driver_busy' => '選択された運転手は、指定の時間帯に既にスケジュールが入っています。'
    ]
];

$txt = $lang_pack[$lang];

if ($tripId) {
    $stmt = $pdo->prepare("SELECT * FROM trips WHERE id = ?");
    $stmt->execute([$tripId]);
    $trip = $stmt->fetch();
    if (!$trip) { header('Location: index.php'); exit; }
}

$vehicles = $pdo->query("SELECT * FROM vehicles ORDER BY plate_number")->fetchAll();
$drivers  = $pdo->query("SELECT * FROM drivers ORDER BY full_name")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'vehicle_id'      => (int)$_POST['vehicle_id'],
        'driver_id'       => (int)$_POST['driver_id'],
        'requester_name'  => trim($_POST['requester_name']),
        'requester_dept'  => trim($_POST['requester_dept']),
        'companions'      => trim($_POST['companions']),
        'origin'          => trim($_POST['origin']),
        'destination'     => trim($_POST['destination']),
        'purpose'         => trim($_POST['purpose']),
        'departure_time'  => trim($_POST['departure_time']),
        'expected_return' => trim($_POST['expected_return']) ?: null,
        'status'          => trim($_POST['status']),
        'gate_out_note'   => trim($_POST['gate_out_note']),
        'gate_in_note'    => trim($_POST['gate_in_note']),
    ];

    // Xác nhận các trường bắt buộc
    if (!$data['vehicle_id'])     $errors[] = $txt['err_vehicle'];
    if (!$data['driver_id'])      $errors[] = $txt['err_driver'];
    if (!$data['requester_name']) $errors[] = $txt['err_requester'];
    if (!$data['origin'])         $errors[] = $txt['err_origin'];
    if (!$data['destination'])    $errors[] = $txt['err_destination'];
    if (!$data['purpose'])        $errors[] = $txt['err_purpose'];
    if (!$data['departure_time']) $errors[] = $txt['err_depart_time'];

    // ── KIỂM TRA LOGIC THỜI GIAN VÀ TRÙNG LỊCH (NEW) ──
    if (empty($errors)) {
        $p_start = strtotime($data['departure_time']);
        $p_end   = $data['expected_return'] ? strtotime($data['expected_return']) : null;

        // 1. Kiểm tra nếu thời gian về sớm hơn hoặc bằng thời gian đi
        if ($p_end && $p_end <= $p_start) {
            $errors[] = $txt['err_date_invalid'];
        }

        // Chỉ kiểm tra trùng lịch đối với chuyến xe có trạng thái cần chiếm giữ (Đã lên lịch hoặc Đang đi)
        if (empty($errors) && in_array($data['status'], ['scheduled', 'departed'])) {
            
            // Tính toán mốc thời gian đề xuất (Nếu không có giờ về, tạm tính giả định bận 4 tiếng)
            $proposed_start = $data['departure_time'];
            $proposed_end   = $data['expected_return'] ?: date('Y-m-d H:i:s', $p_start + 14400);

            // A. Kiểm tra xung đột xe
            $stmtVeh = $pdo->prepare("
                SELECT COUNT(*) FROM trips 
                WHERE vehicle_id = :vehicle_id
                  AND status IN ('scheduled', 'departed')
                  AND id != :current_trip_id
                  AND :proposed_start < IFNULL(expected_return, DATE_ADD(departure_time, INTERVAL 4 HOUR))
                  AND :proposed_end > departure_time
            ");
            $stmtVeh->execute([
                ':vehicle_id'      => $data['vehicle_id'],
                ':current_trip_id' => $tripId,
                ':proposed_start'  => $proposed_start,
                ':proposed_end'    => $proposed_end
            ]);
            if ($stmtVeh->fetchColumn() > 0) {
                $errors[] = $txt['err_vehicle_busy'];
            }

            // B. Kiểm tra xung đột tài xế
            $stmtDrv = $pdo->prepare("
                SELECT COUNT(*) FROM trips 
                WHERE driver_id = :driver_id
                  AND status IN ('scheduled', 'departed')
                  AND id != :current_trip_id
                  AND :proposed_start < IFNULL(expected_return, DATE_ADD(departure_time, INTERVAL 4 HOUR))
                  AND :proposed_end > departure_time
            ");
            $stmtDrv->execute([
                ':driver_id'       => $data['driver_id'],
                ':current_trip_id' => $tripId,
                ':proposed_start'  => $proposed_start,
                ':proposed_end'    => $proposed_end
            ]);
            if ($stmtDrv->fetchColumn() > 0) {
                $errors[] = $txt['err_driver_busy'];
            }
        }
    }

    // ── LƯU DỮ LIỆU KHI KHÔNG CÓ LỖI XUNG ĐỘT ──
    if (empty($errors)) {
        if ($tripId) {
            $oldVehicleId = $trip['vehicle_id'];
            $oldDriverId  = $trip['driver_id'];

            $stmt = $pdo->prepare("UPDATE trips SET vehicle_id=?,driver_id=?,requester_name=?,requester_dept=?,companions=?,
    origin=?,destination=?,purpose=?,departure_time=?,expected_return=?,status=?,gate_out_note=?,gate_in_note=? WHERE id=?");
            $stmt->execute([...array_values($data), $tripId]);

            // Đồng bộ lại trạng thái cũ và mới của Xe & Tài xế
            syncAll($pdo,
                [$oldVehicleId, $data['vehicle_id']],
                [$oldDriverId,  $data['driver_id']]
            );

            $success = $txt['success_update'];
            $stmt2 = $pdo->prepare("SELECT * FROM trips WHERE id = ?");
            $stmt2->execute([$tripId]);
            $trip = $stmt2->fetch();
        } else {
            $stmt = $pdo->prepare("INSERT INTO trips (vehicle_id,driver_id,requester_name,requester_dept,companions,
    origin,destination,purpose,departure_time,expected_return,status,gate_out_note,gate_in_note,created_by)
    VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
            $stmt->execute([...array_values($data), $user['id']]);
            $newId = $pdo->lastInsertId();

            // Đồng bộ trạng thái xe & tài xế mới nhận chuyến
            syncVehicleAndDriver($pdo, $data['vehicle_id'], $data['driver_id']);

            header("Location: trip_form.php?id=$newId&lang=$lang&msg=created");
            exit;
        }
    }
}

// ── TỰ ĐỘNG KHỞI TẠO KHUNG GIỜ HÀNH CHÍNH THEO YÊU CẦU ──
$timeOptions = [];
for ($t = strtotime('07:30'); $t <= strtotime('16:00'); $t += 1800) {
    $timeOptions[] = date('H:i', $t);
}
$timeOptions[] = '16:10'; 
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php 
    if ($tripId) {
        echo ($lang === 'vi' ? 'Sửa Chuyến Xe' : '運行編集');
    } else {
        echo ($lang === 'vi' ? 'Tạo Chuyến Xe' : '運行作成');
    }
?> — <?= SITE_NAME ?></title>
<link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/panel.css">
<style>
/* ── CONFIG LIGHT THEME TRẮNG SỮA BAN NGÀY ── */
body.light-theme {
    --navy:   #f5f5f0;    
    --navy2:  #ffffff;    
    --navy3:  #eaf0f6;    
    --white:  #0f172a;    
    --border: rgba(15, 23, 42, 0.08); 
    --cyan:   #0891b2;    
    --blue2:  #2563eb;    
}
body.light-theme .nav-links a:hover,
body.light-theme .nav-links a.active {
    background: rgba(0,0,0,0.05);
}
body.light-theme .form-card {
    box-shadow: 0 4px 24px rgba(0,0,0,0.03);
    background: #ffffff;
}
body.light-theme .form-control {
    background: #ffffff;
    border-color: rgba(0,0,0,0.15);
    color: #0f172a;
}
body.light-theme .form-control:focus {
    background: rgba(59,130,246,0.04);
}
body.light-theme .form-control option {
    background: #ffffff;
    color: #0f172a;
}
body.light-theme select.form-control {
    color: #0f172a;
}
body.light-theme .form-control::-webkit-calendar-picker-indicator {
    filter: invert(0);
}

/* PHONG CÁCH NÚT CHUYỂN ĐỔI THEME GÓC PHẢI NAV */
.theme-toggle-btn {
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid var(--border);
    color: var(--cyan);
    padding: 6px;
    border-radius: 8px;
    cursor: pointer;
    display: inline-flex;
    align-items: center; justify-content: center;
    transition: all 0.2s ease;
    width: 30px; height: 30px;
    margin-right: 8px;
    flex-shrink: 0;
}
.theme-toggle-btn:hover {
    background: rgba(6, 182, 212, 0.15);
    border-color: var(--cyan);
}
.theme-toggle-btn .moon-icon { display: none; }
.theme-toggle-btn .sun-icon { display: block; }

body.light-theme .theme-toggle-btn {
    background: rgba(0, 0, 0, 0.04);
    color: var(--yellow);
}
body.light-theme .theme-toggle-btn:hover {
    background: rgba(245, 158, 11, 0.1);
    border-color: var(--yellow);
}
body.light-theme .theme-toggle-btn .sun-icon { display: none; }
body.light-theme .theme-toggle-btn .moon-icon { display: block; }

/* ── KHÔNG PHÁ BỐ CỤC UI CHO HỘP ĐỊA ĐIỂM & GIỜ GIẤC ── */
.form-group {
    position: relative; 
}
.fixed-location-box {
    position: absolute;
    top: 100%; left: 0; right: 0;
    background: var(--navy2);
    border: 1px solid var(--border);
    border-radius: 8px;
    margin-top: 5px;
    max-height: 160px;
    overflow-y: auto;
    z-index: 99; 
    display: none;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.4);
}
body.light-theme .fixed-location-box {
    box-shadow: 0 8px 24px rgba(15, 23, 42, 0.08);
}
.location-item, .time-item {
    padding: 9px 14px;
    font-size: 13px;
    font-weight: 500;
    cursor: pointer;
    color: var(--white);
    display: flex;
    align-items: center;
    gap: 8px;
    transition: background 0.15s ease, color 0.15s ease;
}
.location-item:hover, .time-item:hover {
    background: rgba(6, 182, 212, 0.12);
    color: var(--cyan);
}
.location-item::before { content: "🏢"; font-size: 12px; }
.time-item::before { content: "⏰"; font-size: 12px; }

.custom-scroll::-webkit-scrollbar { height: 4px; width: 4px; }
.custom-scroll::-webkit-scrollbar-track { background: rgba(255, 255, 255, 0.02); }
.custom-scroll::-webkit-scrollbar-thumb { background: rgba(255, 255, 255, 0.1); border-radius: 2px; }
</style>
</head>
<body>
<script>
// Khôi phục giao diện tối ưu ngay từ khi bắt đầu dựng body để tránh nhấp nháy (flash)
if (localStorage.getItem('panel-theme') === 'light') {
    document.body.classList.add('light-theme');
}
</script>

<nav class="navbar">
    <div class="nav-brand"><span class="nav-icon">🚗</span><span><?= SITE_NAME ?></span></div>
    <div class="nav-links">
        <a href="index.php?lang=<?= $lang ?>"><?= $txt['nav_trips'] ?></a>
        <a href="trip_form.php?lang=<?= $lang ?>" class="active"><?= $txt['nav_create'] ?></a>
        <?php if (isAdmin()): ?><a href="vehicles.php?lang=<?= $lang ?>"><?= $txt['nav_vehicles'] ?></a><?php endif; ?>
        <a href="dashboard.php?lang=<?= $lang ?>" target="_blank" class="nav-dashboard"><?= $txt['nav_dashboard'] ?></a>
    </div>
    <div class="nav-user">
        <a href="?lang=<?= $lang === 'vi' ? 'jp' : 'vi' ?><?= $tripId ? '&id='.$tripId : '' ?>" class="theme-toggle-btn" title="Chuyển đổi ngôn ngữ / 言語切替" style="text-decoration:none; font-family:'Be Vietnam Pro',sans-serif; font-size:11px; font-weight:700; display:inline-flex;">
            <?= $lang === 'vi' ? 'JP' : 'VI' ?>
        </a>

        <button id="theme-toggle" class="theme-toggle-btn" title="Thay đổi giao diện Sáng / Tối">
            <svg class="sun-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="5"></circle><line x1="12" y1="1" x2="12" y2="3"></line><line x1="12" y1="21" x2="12" y2="23"></line><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line><line x1="1" y1="12" x2="3" y2="12"></line><line x1="21" y1="12" x2="23" y2="12"></line><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line></svg>
            <svg class="moon-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path></svg>
        </button>
        <span>👤 <?= sanitize($user['full_name']) ?></span>
        <a href="logout.php" class="btn-logout"><?= $txt['logout'] ?></a>
    </div>
</nav>

<div class="container">
    <div class="form-page-header">
        <a href="index.php?lang=<?= $lang ?>" class="back-link"><?= $txt['back_link'] ?></a>
        <h2><?= $tripId ? $txt['title_edit'].$tripId : $txt['title_create'] ?></h2>
    </div>

    <?php if (!empty($errors)): ?>
    <div class="alert alert-error">
        <?php foreach ($errors as $e): ?><div>⚠️ <?= sanitize($e) ?></div><?php endforeach; ?>
    </div>
    <?php endif; ?>
    <?php if ($success): ?>
    <div class="alert alert-success">✅ <?= sanitize($success) ?></div>
    <?php endif; ?>
    <?php if (isset($_GET['msg']) && $_GET['msg'] === 'created'): ?>
    <div class="alert alert-success"><?= $txt['msg_created'] ?></div>
    <?php endif; ?>

    <div class="form-card">
        <form method="POST">
            <div class="form-section">
                <h3 class="section-title"><?= $txt['sec_veh_driver'] ?></h3>
                <div class="form-row">
                    <div class="form-group">
                        <label><?= $txt['lbl_vehicle'] ?> <span class="req">*</span></label>
                        <select name="vehicle_id" class="form-control" required>
                            <option value="">— <?= $txt['opt_vehicle'] ?> —</option>
                            <?php foreach ($vehicles as $v): ?>
                            <option value="<?= $v['id'] ?>"
                                <?= (($trip['vehicle_id'] ?? $_POST['vehicle_id'] ?? '') == $v['id']) ? 'selected' : '' ?>
                                class="status-<?= $v['status'] ?>">
                                <?= sanitize($v['plate_number']) ?> — <?= sanitize($v['vehicle_name']) ?>
                                <?= $v['status'] !== 'available' ? $txt['status_busy'] : '' ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label><?= $txt['lbl_driver'] ?> <span class="req">*</span></label>
                        <select name="driver_id" class="form-control" required>
                            <option value="">— <?= $txt['opt_driver'] ?> —</option>
                            <?php foreach ($drivers as $d): ?>
                            <option value="<?= $d['id'] ?>"
                                <?= (($trip['driver_id'] ?? $_POST['driver_id'] ?? '') == $d['id']) ? 'selected' : '' ?>>
                                <?= sanitize($d['full_name']) ?> — <?= sanitize($d['phone']) ?>
                                <?= $d['status'] !== 'available' ? $txt['status_busy'] : '' ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>

            <div class="form-section">
                <h3 class="section-title"><?= $txt['sec_requester'] ?></h3>
                <div class="form-row">
                    <div class="form-group">
                        <label><?= $txt['lbl_name'] ?> <span class="req">*</span></label>
                        <input type="text" name="requester_name" class="form-control"
                            value="<?= sanitize($trip['requester_name'] ?? $_POST['requester_name'] ?? '') ?>"
                            placeholder="Nguyễn Văn A / 山田太郎" required>
                    </div>
                    <div class="form-group">
                        <label><?= $txt['lbl_dept'] ?></label>
                        <input type="text" name="requester_dept" class="form-control"
                            value="<?= sanitize($trip['requester_dept'] ?? $_POST['requester_dept'] ?? '') ?>"
                            placeholder="GA / Accounting / Sales">
                    </div>
                </div>
                <div class="form-group">
                    <label><?= $txt['lbl_companions'] ?></label>
                    <input type="text" name="companions" class="form-control"
                        value="<?= sanitize($trip['companions'] ?? $_POST['companions'] ?? '') ?>"
                        placeholder="Name 1, Name 2, Name 3">
                    <small style="color:var(--gray);font-size:11px;margin-top:4px;display:block"><?= $txt['hint_companions'] ?></small>
                </div>
            </div>

            <div class="form-section">
                <h3 class="section-title"><?= $txt['sec_trip_info'] ?></h3>
                
                <div class="form-row">
                    <div class="form-group">
                        <label><?= $txt['lbl_origin'] ?> <span class="req">*</span></label>
                        <input type="text" name="origin" id="inputOrigin" class="form-control" autocomplete="off"
                            value="<?= sanitize($trip['origin'] ?? $_POST['origin'] ?? '') ?>"
                            placeholder="AMATA HEAD OFFICE..." required>
                        
                        <div class="fixed-location-box custom-scroll" id="boxOrigin">
                            <div class="location-item" data-value="AMATA HEAD OFFICE">AMATA HEAD OFFICE</div>
                            <div class="location-item" data-value="HONAI FACTORY">HONAI FACTORY</div>
                            <div class="location-item" data-value="AGTEX WAREHOUSE">AGTEX WAREHOUSE</div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label><?= $txt['lbl_destination'] ?> <span class="req">*</span></label>
                        <input type="text" name="destination" id="inputDest" class="form-control" autocomplete="off"
                            value="<?= sanitize($trip['destination'] ?? $_POST['destination'] ?? '') ?>"
                            placeholder="HONAI FACTORY..." required>
                        
                        <div class="fixed-location-box custom-scroll" id="boxDest">
                            <div class="location-item" data-value="AMATA HEAD OFFICE">AMATA HEAD OFFICE</div>
                            <div class="location-item" data-value="HONAI FACTORY">HONAI FACTORY</div>
                            <div class="location-item" data-value="AGTEX WAREHOUSE">AGTEX WAREHOUSE</div>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label><?= $txt['lbl_purpose'] ?> <span class="req">*</span></label>
                    <textarea name="purpose" class="form-control" rows="3"
                        placeholder="Description..." required><?= sanitize($trip['purpose'] ?? $_POST['purpose'] ?? '') ?></textarea>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label><?= $txt['lbl_depart_time'] ?> <span class="req">*</span></label>
                        <div style="display:flex;gap:8px;align-items:flex-end">
                            <div style="flex:1">
                                <input type="date" name="departure_date" class="form-control"
                                    value="<?= $trip ? date('Y-m-d', strtotime($trip['departure_time'])) : date('Y-m-d') ?>" required>
                            </div>
                            <div style="flex:1; position: relative;">
                                <input type="text" name="departure_time_only" id="inputDepTime" class="form-control time-input" autocomplete="off"
                                    value="<?= $trip ? date('H:i', strtotime($trip['departure_time'])) : '' ?>"
                                    placeholder="HH:MM (08:30)" pattern="[0-2][0-9]:[0-5][0-9]" required>
                                
                                <div class="fixed-location-box custom-scroll" id="boxDepTime">
                                    <?php foreach ($timeOptions as $time): ?>
                                    <div class="time-item" data-value="<?= $time ?>"><?= $time ?></div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                        <small style="color:var(--gray);font-size:11px;margin-top:4px;display:block"><?= $txt['hint_time_format'] ?></small>
                        <input type="hidden" name="departure_time" id="departure_time_hidden">
                    </div>
                    <div class="form-group">
                        <label><?= $txt['lbl_expected_return'] ?></label>
                        <div style="display:flex;gap:8px;align-items:flex-end">
                            <div style="flex:1">
                                <input type="date" name="expected_return_date" class="form-control"
                                    value="<?= ($trip && $trip['expected_return']) ? date('Y-m-d', strtotime($trip['expected_return'])) : date('Y-m-d') ?>">
                            </div>
                            <div style="flex:1; position: relative;">
                                <input type="text" name="expected_return_time_only" id="inputRetTime" class="form-control time-input" autocomplete="off"
                                    value="<?= ($trip && $trip['expected_return']) ? date('H:i', strtotime($trip['expected_return'])) : '' ?>"
                                    placeholder="HH:MM (16:10)" pattern="[0-2][0-9]:[0-5][0-9]">

                                <div class="fixed-location-box custom-scroll" id="boxRetTime">
                                    <?php foreach ($timeOptions as $time): ?>
                                    <div class="time-item" data-value="<?= $time ?>"><?= $time ?></div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                        <small style="color:var(--gray);font-size:11px;margin-top:4px;display:block"><?= $txt['hint_optional'] ?></small>
                        <input type="hidden" name="expected_return" id="expected_return_hidden">
                    </div>
                </div>
            </div>

            <div class="form-section">
                <h3 class="section-title"><?= $txt['sec_gate_info'] ?></h3>
                <div class="form-row">
                    <div class="form-group">
                        <label><?= $txt['lbl_gate_out'] ?></label>
                        <input type="text" name="gate_out_note" class="form-control"
                            value="<?= sanitize($trip['gate_out_note'] ?? '') ?>"
                            placeholder="...">
                    </div>
                    <div class="form-group">
                        <label><?= $txt['lbl_gate_in'] ?></label>
                        <input type="text" name="gate_in_note" class="form-control"
                            value="<?= sanitize($trip['gate_in_note'] ?? '') ?>"
                            placeholder="...">
                    </div>
                </div>
                <div class="form-group" style="max-width:240px">
                    <label><?= $txt['lbl_status'] ?></label>
                    <select name="status" class="form-control">
                        <option value="scheduled" <?= (($trip['status'] ?? 'scheduled') == 'scheduled') ? 'selected' : '' ?>><?= $txt['status_scheduled'] ?></option>
                        <option value="departed" <?= (($trip['status'] ?? 'scheduled') == 'departed') ? 'selected' : '' ?>><?= $txt['status_departed'] ?></option>
                        <option value="returned" <?= (($trip['status'] ?? 'scheduled') == 'returned') ? 'selected' : '' ?>><?= $txt['status_returned'] ?></option>
                        <option value="cancelled" <?= (($trip['status'] ?? 'scheduled') == 'cancelled') ? 'selected' : '' ?>><?= $txt['status_cancelled'] ?></option>
                    </select>
                </div>
            </div>

            <div class="form-actions">
                <a href="index.php?lang=<?= $lang ?>" class="btn-secondary"><?= $txt['btn_cancel'] ?></a>
                <button type="submit" class="btn-primary">
                    <?= $tripId ? $txt['btn_save'] : $txt['btn_create'] ?>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Tổng hợp ngày + giờ thành chuỗi Datetime hoàn chỉnh trước khi submit
document.querySelector('form').addEventListener('submit', function(e) {
    const depDate = document.querySelector('input[name="departure_date"]').value;
    const depTime = document.querySelector('input[name="departure_time_only"]').value;
    const expDate = document.querySelector('input[name="expected_return_date"]').value;
    const expTime = document.querySelector('input[name="expected_return_time_only"]').value;

    if (depDate && depTime) {
        document.getElementById('departure_time_hidden').value = depDate + ' ' + depTime + ':00';
    }
    if (expDate && expTime) {
        document.getElementById('expected_return_hidden').value = expDate + ' ' + expTime + ':00';
    } else {
        document.getElementById('expected_return_hidden').value = '';
    }
});

// ĐIỀU KHIỂN CHUYỂN ĐỔI THEME VÀ LƯU BỘ NHỚ TRÊN FORM NHẬP LIỆU
document.getElementById('theme-toggle').addEventListener('click', function() {
    document.body.classList.toggle('light-theme');
    if (document.body.classList.contains('light-theme')) {
        localStorage.setItem('panel-theme', 'light');
    } else {
        localStorage.setItem('panel-theme', 'dark');
    }
});

// JAVASCRIPT ĐIỀU KHIỂN HỘP GỢI Ý ĐỊA ĐIỂM CỐ ĐỊNH
function bindLocationSuggestion(inputId, boxId) {
    const input = document.getElementById(inputId);
    const box = document.getElementById(boxId);

    input.addEventListener('focus', () => { box.style.display = 'block'; });
    input.addEventListener('blur', () => { setTimeout(() => { box.style.display = 'none'; }, 200); });

    box.querySelectorAll('.location-item').forEach(item => {
        item.addEventListener('click', () => {
            input.value = item.getAttribute('data-value');
            box.style.display = 'none';
        });
    });
}

// JAVASCRIPT ĐIỀU KHIỂN HỘP GỢI Ý MỐC GIỜ HÀNH CHÍNH
function bindTimeSuggestion(inputId, boxId) {
    const input = document.getElementById(inputId);
    const box = document.getElementById(boxId);

    input.addEventListener('focus', () => { box.style.display = 'block'; });
    input.addEventListener('blur', () => { setTimeout(() => { box.style.display = 'none'; }, 200); });

    box.querySelectorAll('.time-item').forEach(item => {
        item.addEventListener('click', () => {
            input.value = item.getAttribute('data-value');
            box.style.display = 'none';
        });
    });
}

// Kích hoạt toàn bộ các hộp gợi ý tự động
bindLocationSuggestion('inputOrigin', 'boxOrigin');
bindLocationSuggestion('inputDest', 'boxDest');
bindTimeSuggestion('inputDepTime', 'boxDepTime');
bindTimeSuggestion('inputRetTime', 'boxRetTime');
</script>
</body>
</html>