<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
requireLogin();
if (!isAdmin()) { header('Location: index.php'); exit; }
$user = currentUser();

// ── 1. CƠ CHẾ GHI NHỚ NGÔN NGỮ XUYÊN TRANG BẰNG PHP SESSION ──
if (isset($_GET['lang'])) {
    $lang = $_GET['lang'];
    $_SESSION['lang'] = $lang; // Ghim chặt vào bộ nhớ phiên làm việc
} else {
    $lang = $_SESSION['lang'] ?? 'vi'; // Nếu duyệt trang bình thường, tự bốc bộ nhớ ra dùng
}
if (!in_array($lang, ['vi', 'jp'])) { $lang = 'vi'; }

// ── 2. BỘ TỪ ĐIỂN SONG NGỮ VIỆT - NHẬT CHO TRANG XE & TÀI XẾ ──
$lang_pack = [
    'vi' => [
        'nav_trips' => '📋 Chuyến Xe',
        'nav_create' => '➕ Tạo Chuyến',
        'nav_vehicles' => '🚙 Xe & Tài Xế',
        'nav_dashboard' => '🖥️ Màn Bảo Vệ',
        'logout' => 'Đăng xuất',
        'title' => '🚙 Quản Lý Xe & Tài Xế',
        'list_vehicles' => '🚗 Danh Sách Xe',
        'list_drivers' => '👤 Danh Sách Tài Xế',
        'btn_add_vehicle' => '+ Thêm xe',
        'btn_add_driver' => '+ Thêm tài xế',
        'th_plate' => 'Biển số',
        'th_name' => 'Tên xe',
        'th_type' => 'Loại',
        'th_status' => 'Trạng thái',
        'th_actions' => 'Thao tác',
        'th_fullname' => 'Họ tên',
        'th_phone' => 'SĐT',
        'th_license' => 'GPLX',
        'btn_edit' => '✏️ Sửa',
        'modal_v_add' => '➕ Thêm Xe Mới',
        'lbl_plate' => 'Biển số',
        'lbl_type' => 'Loại xe',
        'lbl_name' => 'Tên xe',
        'lbl_icon_color' => 'Biểu tượng xe & Màu sắc',
        'lbl_select_icon' => 'Chọn biểu tượng:',
        'lbl_select_color' => 'Chọn màu sắc:',
        'lbl_status' => 'Trạng thái',
        'lbl_notes' => 'Ghi chú',
        'btn_cancel' => 'Hủy',
        'btn_save' => '💾 Lưu',
        'modal_d_add' => '➕ Thêm Tài Xế Mới',
        'lbl_fullname' => 'Họ tên',
        'lbl_phone' => 'Số điện thoại',
        'lbl_license' => 'Số GPLX',
        'v_available' => 'Sẵn sàng',
        'v_in_use' => 'Đang dùng',
        'v_maintenance' => 'Bảo trì',
        'd_available' => 'Rảnh',
        'd_on_trip' => 'Đang đi',
        'd_off' => 'Nghỉ',
        'success_edit_v' => 'Cập nhật thông tin xe thành công!',
        'success_add_v' => 'Thêm xe mới thành công!',
        'err_del_v' => 'Không thể xóa cứng xe này vì dữ liệu đã được lưu trong lịch sử chuyến đi của hệ thống!',
        'success_del_v' => 'Đã xóa xe khỏi danh sách thành công!',
        'success_edit_d' => 'Cập nhật thông tin tài xế thành công!',
        'success_add_d' => 'Thêm tài xế mới thành công!',
        'err_del_d' => 'Không thể xóa cứng tài xế này vì dữ liệu dữ liệu đã được lưu trong lịch sử chuyến đi của hệ thống!',
        'success_del_d' => 'Đã xóa tài xế thành công!',
        'confirm_del_v' => 'Xóa xe này?',
        'confirm_del_d' => 'Xóa tài xế này?'
    ],
    'jp' => [
        'nav_trips' => '📋 運行リスト',
        'nav_create' => '➕ 運行作成',
        'nav_vehicles' => '🚙 車両・運転手',
        'nav_dashboard' => '🖥️ 警備員画面',
        'logout' => 'ログアウト',
        'title' => '🚙 車 loyalty 車両・運転手管理設定',
        'list_vehicles' => '🚗 車両マスターリスト',
        'list_drivers' => '👤 運転手マスターリスト',
        'btn_add_vehicle' => '+ 車両追加',
        'btn_add_driver' => '+ 運転手追加',
        'th_plate' => '車両番号',
        'th_name' => '車両名',
        'th_type' => 'タイプ',
        'th_status' => 'ステータス',
        'th_actions' => '操作',
        'th_fullname' => '氏名',
        'th_phone' => '電話番号',
        'th_license' => '運転免許',
        'btn_edit' => '✏️ 編集',
        'modal_v_add' => '➕ 新規車両登録',
        'lbl_plate' => '車両番号',
        'lbl_type' => '車両タイプ',
        'lbl_name' => '車両名',
        'lbl_icon_color' => 'アイコン・表示カラー',
        'lbl_select_icon' => 'アイコン選択:',
        'lbl_select_color' => 'カラー選択:',
        'lbl_status' => 'ステータス',
        'lbl_notes' => '備考',
        'btn_cancel' => '戻る',
        'btn_save' => '💾 保存',
        'modal_d_add' => '➕ 新規運転手登録',
        'lbl_fullname' => '氏名',
        'lbl_phone' => '電話番号',
        'lbl_license' => '免許証番号',
        'v_available' => '空車',
        'v_in_use' => '運行中',
        'v_maintenance' => '整備中',
        'd_available' => '待機中',
        'd_on_trip' => '運行中',
        'd_off' => '休暇',
        'success_edit_v' => '車両情報を更新しました。',
        'success_add_v' => '新規車両を登録しました。',
        'err_del_v' => '過去 của 運行履歴に関連付けられているため、この車両はシステムから削除できません！',
        'success_del_v' => '車両データを削除しました。',
        'success_edit_d' => '運転手情報を更新しました。',
        'success_add_d' => '新規運転手を登録しました。',
        'err_del_d' => '過去の運行履歴に関連付けられているため、this運転手はシステムから削除できません！',
        'success_del_d' => '運転手データを削除しました。',
        'confirm_del_v' => 'この車両を削除しますか？',
        'confirm_del_d' => 'この運転手を削除しますか？'
    ]
];

$txt = $lang_pack[$lang];

// Bản đồ trạng thái đa ngôn ngữ
$vStatusMap = ['available' => $txt['v_available'], 'in_use' => $txt['v_in_use'], 'maintenance' => $txt['v_maintenance']];
$dStatusMap = ['available' => $txt['d_available'], 'on_trip' => $txt['d_on_trip'], 'off' => $txt['d_off']];
$vTypes = ['Sedan','SUV','MPV','Van','Pickup','Bus','Motorcycle','Bicycle','Khác'];

// ── 3. HẬU ĐÀI XỬ LÝ BIỂU MẪU ĐỘNG ──
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'edit_vehicle') {
        $stmt = $pdo->prepare("UPDATE vehicles SET plate_number=?, vehicle_name=?, vehicle_type=?, status=?, notes=?, icon=?, icon_color=? WHERE id=?");
        $stmt->execute([trim($_POST['plate_number']), trim($_POST['vehicle_name']), trim($_POST['vehicle_type']), $_POST['status'], trim($_POST['notes']), trim($_POST['icon'] ?? 'sedan'), trim($_POST['icon_color'] ?? '#FF6B00'), (int)$_POST['id']]);
        $msg = $txt['success_edit_v'];
    }
    if ($action === 'add_vehicle') {
        $stmt = $pdo->prepare("INSERT INTO vehicles (plate_number, vehicle_name, vehicle_type, status, notes, icon, icon_color) VALUES (?,?,?,?,?,?,?)");
        $stmt->execute([trim($_POST['plate_number']), trim($_POST['vehicle_name']), trim($_POST['vehicle_type']), $_POST['status'], trim($_POST['notes']), trim($_POST['icon'] ?? 'sedan'), trim($_POST['icon_color'] ?? '#FF6B00')]);
        $msg = $txt['success_add_v'];
    }
    if ($action === 'delete_vehicle') {
        $id = (int)$_POST['id'];
        $count = $pdo->query("SELECT COUNT(*) FROM trips WHERE vehicle_id=$id")->fetchColumn();
        if ($count > 0) {
            $msg = $txt['err_del_v']; $msgType = 'error';
        } else {
            $pdo->prepare("DELETE FROM vehicles WHERE id=?")->execute([$id]);
            $msg = $txt['success_del_v'];
        }
    }
    if ($action === 'edit_driver') {
        $stmt = $pdo->prepare("UPDATE drivers SET full_name=?, phone=?, license_number=?, status=? WHERE id=?");
        $stmt->execute([trim($_POST['full_name']), trim($_POST['phone']), trim($_POST['license_number']), $_POST['status'], (int)$_POST['id']]);
        $msg = $txt['success_edit_d'];
    }
    if ($action === 'add_driver') {
        $stmt = $pdo->prepare("INSERT INTO drivers (full_name, phone, license_number, status) VALUES (?,?,?,?)");
        $stmt->execute([trim($_POST['full_name']), trim($_POST['phone']), trim($_POST['license_number']), $_POST['status']]);
        $msg = $txt['success_add_d'];
    }
    if ($action === 'delete_driver') {
        $id = (int)$_POST['id'];
        $count = $pdo->query("SELECT COUNT(*) FROM trips WHERE driver_id=$id")->fetchColumn();
        if ($count > 0) {
            $msg = $txt['err_del_d']; $msgType = 'error';
        } else {
            $pdo->prepare("DELETE FROM drivers WHERE id=?")->execute([$id]);
            $msg = $txt['success_del_d'];
        }
    }

    header('Location: vehicles.php?lang=' . $lang . ($msg ? '&msg='.urlencode($msg).'&type='.($msgType ?? 'success') : ''));
    exit;
}

if (isset($_GET['msg'])) {
    $msg = $_GET['msg'];
    $msgType = $_GET['type'] ?? 'success';
}

$vehicles = $pdo->query("SELECT * FROM vehicles ORDER BY plate_number")->fetchAll();
$drivers  = $pdo->query("SELECT * FROM drivers ORDER BY full_name")->fetchAll();
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= $txt['title'] ?> — <?= SITE_NAME ?></title>
<link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/panel.css">
<style>
body.light-theme {
    --navy: #f5f5f0; --navy2: #ffffff; --navy3: #eaf0f6; --white: #0f172a;     
    --border: rgba(15, 23, 42, 0.08); --cyan: #0891b2; --blue2: #2563eb;     
}
body.light-theme .nav-links a:hover, body.light-theme .nav-links a.active { background: rgba(0,0,0,0.05); }
body.light-theme .data-table tbody tr { border-bottom: 1px solid rgba(0,0,0,0.04); }
body.light-theme .data-table tbody tr:hover { background: rgba(0,0,0,0.015); }
body.light-theme .table-wrap { box-shadow: 0 4px 16px rgba(0,0,0,0.03); }
body.light-theme .btn-sm.edit { background: rgba(0,0,0,0.04); color: #0f172a; border-color: rgba(0,0,0,0.08); }
body.light-theme .form-control { background: #ffffff; border-color: rgba(0,0,0,0.15); color: #0f172a; }
body.light-theme .form-control:focus { background: rgba(59,130,246,0.04); }
body.light-theme .form-control option { background: #ffffff; color: #0f172a; }

/* PHONG CÁCH CƠ SỞ ĐỒ HỌA NÚT CHUYỂN ĐỔI NGÔN NGỮ ĐA TẦNG */
.lang-toggle-btn {
    background: rgba(255, 255, 255, 0.05); border: 1px solid var(--border); color: var(--cyan);
    padding: 6px; border-radius: 8px; cursor: pointer; display: inline-flex;
    align-items: center; justify-content: center; transition: all 0.2s ease;
    width: 30px; height: 30px; margin-right: 8px; flex-shrink: 0;
    text-decoration: none; font-family: 'Be Vietnam Pro', sans-serif; font-size: 11px; font-weight: 700;
}
body.light-theme .lang-toggle-btn { background: rgba(0, 0, 0, 0.04); color: var(--cyan); }

/* PHONG CÁCH NÚT THEME SWITCHER */
.theme-toggle-btn {
    background: rgba(255, 255, 255, 0.05); border: 1px solid var(--border); color: var(--cyan);
    padding: 6px; border-radius: 8px; cursor: pointer; display: inline-flex;
    align-items: center; justify-content: center; transition: all 0.2s ease;
    width: 30px; height: 30px; margin-right: 8px; flex-shrink: 0;
}
.theme-toggle-btn:hover { background: rgba(6, 182, 212, 0.15); border-color: var(--cyan); }
.theme-toggle-btn .moon-icon { display: none; }
.theme-toggle-btn .sun-icon { display: block; }
body.light-theme .theme-toggle-btn { background: rgba(0, 0, 0, 0.04); color: var(--yellow); }
body.light-theme .theme-toggle-btn:hover { background: rgba(245, 158, 11, 0.1); border-color: var(--yellow); }
body.light-theme .theme-toggle-btn .sun-icon { display: none; }
body.light-theme .theme-toggle-btn .moon-icon { display: block; }

/* 🚀 CẤU TRÚC PHÂN LỚP CSS CHO HỘP CẢNH BÁO POPUP TOAST THỜI THƯỢNG GÓC MÀN HÌNH */
.popup-alert {
    position: fixed; top: 24px; right: 24px;
    background: var(--navy2); border: 1px solid var(--border);
    border-left: 5px solid var(--cyan); padding: 15px 22px;
    border-radius: 12px; box-shadow: 0 12px 36px rgba(0,0,0,0.45);
    z-index: 100000; display: flex; align-items: center; gap: 14px;
    transform: translateX(160%); transition: transform 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    color: var(--white); font-size: 13px; font-weight: 500; max-width: 380px; line-height: 1.4;
}
body.light-theme .popup-alert { box-shadow: 0 12px 36px rgba(15,23,42,0.08); }
.popup-alert.error { border-left-color: var(--red); }
.popup-alert.success { border-left-color: var(--green); }
.popup-alert.show { transform: translateX(0); }
.popup-close { cursor: pointer; color: var(--gray); font-size: 14px; margin-left: auto; padding-left: 10px; transition: color 0.15s; }
.popup-close:hover { color: var(--white); }

.modal-backdrop { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.7); z-index: 999; align-items: center; justify-content: center; }
.modal-backdrop.open { display: flex; }
.modal { background: var(--navy2); border: 1px solid var(--border); border-radius: 16px; width: 520px; max-width: 95vw; animation: modalIn 0.2s ease; overflow: hidden; }
@keyframes modalIn { from { opacity:0; transform: scale(0.95) translateY(-10px); } to { opacity:1; transform: scale(1) translateY(0); } }
.modal-header { padding: 18px 24px; background: var(--navy3); border-bottom: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between; }
.modal-title { font-size: 16px; font-weight: 700; color: var(--cyan); }
.modal-close { background: none; border: none; color: var(--gray); font-size: 20px; cursor: pointer; padding: 0 4px; transition: color 0.15s; }
.modal-close:hover { color: var(--white); }
.modal-body { padding: 24px; }
.modal-footer { padding: 16px 24px; border-top: 1px solid var(--border); display: flex; gap: 10px; justify-content: flex-end; }
.section-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px; }
.section-header h3 { font-size: 16px; color: var(--cyan); }
.vbadge-available    { background:rgba(16,185,129,0.15); color:#34d399; }
.vbadge-in_use       { background:rgba(245,158,11,0.15); color:#f59e0b; }
.vbadge-maintenance  { background:rgba(239,68,68,0.1);   color:#fca5a5; }
.vbadge-on_trip      { background:rgba(245,158,11,0.15); color:#f59e0b; }
.vbadge-off          { background:rgba(100,116,139,0.15);color:#94a3b8; }
.btn-danger { padding: 4px 10px; border-radius: 6px; font-size: 11px; font-weight: 600; background: rgba(239,68,68,0.12); color: #fca5a5; border: 1px solid rgba(239,68,68,0.25); cursor: pointer; font-family: inherit; transition: background 0.15s; }
.btn-danger:hover { background: rgba(239,68,68,0.22); }
.icon-select-btn { border: 1px solid var(--border); background: var(--navy3); border-radius: 8px; width: 44px; height: 44px; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.15s ease; flex-shrink: 0; }
.icon-select-btn:hover { border-color: var(--cyan); background: rgba(6,182,212,0.1); }
.icon-select-btn.selected { border-color: var(--cyan); background: rgba(6,182,212,0.2); box-shadow: 0 0 8px rgba(6,182,212,0.4); }
.color-swatch-btn { width: 24px; height: 24px; border-radius: 50%; cursor: pointer; border: 2px solid transparent; transition: all 0.15s ease; box-shadow: 0 2px 4px rgba(0,0,0,0.3); }
.color-swatch-btn:hover { transform: scale(1.15); }
.color-swatch-btn.selected { border-color: #fff; box-shadow: 0 0 8px currentColor; transform: scale(1.1); }
.custom-scroll::-webkit-scrollbar { height: 4px; }
.custom-scroll::-webkit-scrollbar-track { background: rgba(255,255,255,0.02); }
.custom-scroll::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); border-radius: 2px; }
</style>
</head>
<body>

<script>
    if (localStorage.getItem('panel-theme') === 'light') {
        document.body.classList.add('light-theme');
    }
</script>

<?php if (isset($msg) && $msg): ?>
<div class="popup-alert <?= $msgType ?>" id="sysPopupAlert">
    <div><?= $msgType === 'error' ? '⚠️' : '✅' ?> <?= sanitize($msg) ?></div>
    <span class="popup-close" onclick="closePopupAlert()">✕</span>
</div>
<?php endif; ?>

<nav class="navbar">
    <div class="nav-brand"><span class="nav-icon">🚗</span><span><?= SITE_NAME ?></span></div>
    <div class="nav-links">
        <a href="index.php"><?= $txt['nav_trips'] ?></a>
        <a href="trip_form.php"><?= $txt['nav_create'] ?></a>
        <a href="vehicles.php" class="active"><?= $txt['nav_vehicles'] ?></a>
        <a href="dashboard.php" target="_blank" class="nav-dashboard"><?= $txt['nav_dashboard'] ?></a>
    </div>
    <div class="nav-user">
        <a href="?lang=<?= $lang === 'vi' ? 'jp' : 'vi' ?>" class="lang-toggle-btn" title="Chuyển đổi ngôn ngữ / 言語切替">
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
    <h2 style="margin-bottom:20px;font-size:22px;font-weight:700"><?= $txt['title'] ?></h2>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:28px">
        <div>
            <div class="section-header">
                <h3><?= $txt['list_vehicles'] ?> (<?= count($vehicles) ?>)</h3>
                <button class="btn-primary" style="font-size:13px;padding:7px 16px" onclick="openAddVehicle()"><?= $txt['btn_add_vehicle'] ?></button>
            </div>
            <div class="table-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th><?= $txt['th_plate'] ?></th>
                            <th><?= $txt['th_name'] ?></th>
                            <th><?= $txt['th_type'] ?></th>
                            <th><?= $txt['th_status'] ?></th>
                            <th style="text-align:center"><?= $txt['th_actions'] ?></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($vehicles as $v): ?>
                    <tr>
                        <td><span class="plate"><?= sanitize($v['plate_number']) ?></span></td>
                        <td style="font-weight:500; display:flex; align-items:center; gap:8px;">
                            <?= getVehicleSVG($v['icon'] ?? 'sedan', $v['icon_color'] ?? '#FF6B00', 'normal', 32, 32) ?>
                            <span><?= sanitize($v['vehicle_name']) ?></span>
                        </td>
                        <td style="color:var(--gray);font-size:12px"><?= sanitize($v['vehicle_type']) ?></td>
                        <td>
                            <span class="badge vbadge-<?= $v['status'] ?>"><?= $vStatusMap[$v['status']] ?></span>
                        </td>
                        <td style="text-align:center;white-space:nowrap">
                            <button class="btn-sm edit" onclick='openEditVehicle(<?= json_encode($v) ?>)'><?= $txt['btn_edit'] ?></button>
                            <form method="POST" style="display:inline" onsubmit="return confirm('<?= $txt['confirm_del_v'] ?>')">
                                <input type="hidden" name="action" value="delete_vehicle"><input type="hidden" name="id" value="<?= $v['id'] ?>">
                                <button type="submit" class="btn-danger">🗑️</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div>
            <div class="section-header">
                <h3><?= $txt['list_drivers'] ?> (<?= count($drivers) ?>)</h3>
                <button class="btn-primary" style="font-size:13px;padding:7px 16px" onclick="openAddDriver()"><?= $txt['btn_add_driver'] ?></button>
            </div>
            <div class="table-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th><?= $txt['th_fullname'] ?></th>
                            <th><?= $txt['th_phone'] ?></th>
                            <th><?= $txt['th_license'] ?></th>
                            <th><?= $txt['th_status'] ?></th>
                            <th style="text-align:center"><?= $txt['th_actions'] ?></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($drivers as $d): ?>
                    <tr>
                        <td style="font-weight:500"><?= sanitize($d['full_name']) ?></td>
                        <td style="font-size:12px;color:var(--gray)"><?= sanitize($d['phone']) ?></td>
                        <td style="font-size:12px;color:var(--gray)"><?= sanitize($d['license_number']) ?></td>
                        <td>
                            <span class="badge vbadge-<?= $d['status'] ?>"><?= $dStatusMap[$d['status']] ?></span>
                        </td>
                        <td style="text-align:center;white-space:nowrap">
                            <button class="btn-sm edit" onclick='openEditDriver(<?= json_encode($d) ?>)'><?= $txt['btn_edit'] ?></button>
                            <form method="POST" style="display:inline" onsubmit="return confirm('<?= $txt['confirm_del_d'] ?>')">
                                <input type="hidden" name="action" value="delete_driver"><input type="hidden" name="id" value="<?= $d['id'] ?>">
                                <button type="submit" class="btn-danger">🗑️</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal-backdrop" id="modalVehicle">
    <div class="modal">
        <div class="modal-header">
            <span class="modal-title" id="modalVehicleTitle">—</span>
            <button class="modal-close" onclick="closeModal('modalVehicle')">✕</button>
        </div>
        <form method="POST">
            <input type="hidden" name="action" id="vAction" value="edit_vehicle">
            <input type="hidden" name="id" id="vId">
            <input type="hidden" name="icon" id="vIcon" value="sedan">
            <input type="hidden" name="icon_color" id="vIconColor" value="#FF6B00">
            <div class="modal-body">
                <div class="form-row">
                    <div class="form-group">
                        <label><?= $txt['lbl_plate'] ?> <span class="req">*</span></label>
                        <input type="text" name="plate_number" id="vPlate" class="form-control" placeholder="51A-123.45" required>
                    </div>
                    <div class="form-group">
                        <label><?= $txt['lbl_type'] ?></label>
                        <select name="vehicle_type" id="vType" class="form-control">
                            <?php foreach ($vTypes as $t): ?><option value="<?=$t?>"><?=$t?></option><?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label><?= $txt['lbl_name'] ?> <span class="req">*</span></label>
                    <input type="text" name="vehicle_name" id="vName" class="form-control" required>
                </div>
                <div class="form-group">
                    <label><?= $txt['lbl_icon_color'] ?></label>
                    <div style="display: flex; gap: 15px; align-items: flex-start; margin-top: 5px;">
                        <div id="vIconPreviewBox" style="width: 72px; height: 72px; background: var(--navy3); border: 1px solid var(--border); border-radius: 12px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; box-shadow: inset 0 0 10px rgba(0,0,0,0.5);"></div>
                        <div style="flex: 1; display: flex; flex-direction: column; gap: 8px; min-width: 0;">
                            <div>
                                <span style="font-size: 11px; color: var(--gray); display: block; margin-bottom: 4px;"><?= $txt['lbl_select_icon'] ?></span>
                                <div id="vIconSelectGrid" style="display: flex; gap: 6px; overflow-x: auto; padding-bottom: 5px;" class="custom-scroll"></div>
                            </div>
                            <div>
                                <span style="font-size: 11px; color: var(--gray); display: block; margin-bottom: 4px;"><?= $txt['lbl_select_color'] ?></span>
                                <div id="vColorSelectGrid" style="display: flex; flex-wrap: wrap; gap: 6px;"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label><?= $txt['lbl_status'] ?></label>
                    <select name="status" id="vStatus" class="form-control">
                        <option value="available"><?= $txt['v_available'] ?></option>
                        <option value="in_use"><?= $txt['v_in_use'] ?></option>
                        <option value="maintenance"><?= $txt['v_maintenance'] ?></option>
                    </select>
                </div>
                <div class="form-group">
                    <label><?= $txt['lbl_notes'] ?></label>
                    <textarea name="notes" id="vNotes" class="form-control" rows="2"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" onclick="closeModal('modalVehicle')"><?= $txt['btn_cancel'] ?></button>
                <button type="submit" class="btn-primary"><?= $txt['btn_save'] ?></button>
            </div>
        </form>
    </div>
</div>

<div class="modal-backdrop" id="modalDriver">
    <div class="modal">
        <div class="modal-header">
            <span class="modal-title" id="modalDriverTitle">—</span>
            <button class="modal-close" onclick="closeModal('modalDriver')">✕</button>
        </div>
        <form method="POST">
            <input type="hidden" name="action" id="dAction" value="edit_driver">
            <input type="hidden" name="id" id="dId">
            <div class="modal-body">
                <div class="form-group">
                    <label><?= $txt['lbl_fullname'] ?> <span class="req">*</span></label>
                    <input type="text" name="full_name" id="dName" class="form-control" required>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label><?= $txt['lbl_phone'] ?></label>
                        <input type="text" name="phone" id="dPhone" class="form-control">
                    </div>
                    <div class="form-group">
                        <label><?= $txt['lbl_license'] ?></label>
                        <input type="text" name="license_number" id="dLicense" class="form-control">
                    </div>
                </div>
                <div class="form-group">
                    <label><?= $txt['lbl_status'] ?></label>
                    <select name="status" id="dStatus" class="form-control">
                        <option value="available"><?= $txt['d_available'] ?></option>
                        <option value="on_trip"><?= $txt['d_on_trip'] ?></option>
                        <option value="off"><?= $txt['d_off'] ?></option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" onclick="closeModal('modalDriver')"><?= $txt['btn_cancel'] ?></button>
                <button type="submit" class="btn-primary"><?= $txt['btn_save'] ?></button>
            </div>
        </form>
    </div>
</div>

<script>
function jsDarkenColor(hex, factor = 0.6) {
    hex = hex.replace('#', ''); if (hex.length === 3) hex = hex[0]+hex[0]+hex[1]+hex[1]+hex[2]+hex[2];
    const r = parseInt(hex.slice(0,2), 16), g = parseInt(hex.slice(2,4), 16), b = parseInt(hex.slice(4,6), 16);
    return `rgb(${Math.round(r*factor)},${Math.round(g*factor)},${Math.round(b*factor)})`;
}
function jsLightenColor(hex, add = 60) {
    hex = hex.replace('#', ''); if (hex.length === 3) hex = hex[0]+hex[0]+hex[1]+hex[1]+hex[2]+hex[2];
    return `rgb(${Math.min(255, parseInt(hex.slice(0,2), 16)+add)},${Math.min(255, parseInt(hex.slice(2,4), 16)+add)},${Math.min(255, parseInt(hex.slice(4,6), 16)+add)})`;
}
function jsDrawVehicleBody(type, c) {
    const dark = jsDarkenColor(c), light = jsLightenColor(c, 60), win = 'rgba(180,220,255,0.65)', wheel = '#1a1a2e', rim = '#3a3a5e';
    switch(type) {
        case 'sedan': return `<path d="M8 30 L8 34 Q8 36 10 36 L38 36 Q40 36 40 34 L40 30 Q40 28 38 27 L34 25 L30 18 Q29 16 26 16 L20 16 Q17 16 16 18 L13 25 L10 27 Q8 28 8 30Z" fill="${c}"/><path d="M14 27 L33 27 L29.5 19 Q28.5 17 26 17 L21 17 Q18.5 17 17.5 19Z" fill="${win}" opacity="0.9"/><path d="M8 30 L40 30 L38 27 L10 27Z" fill="${dark}"/><circle cx="14" cy="36" r="4.5" fill="${wheel}"/><circle cx="14" cy="36" r="2.5" fill="${rim}"/><circle cx="34" cy="36" r="4.5" fill="${wheel}"/><circle cx="34" cy="36" r="2.5" fill="${rim}"/><rect x="36" y="29" width="3" height="2" rx="1" fill="#FCD34D" opacity="0.9"/><rect x="9" y="29" width="3" height="2" rx="1" fill="#F87171" opacity="0.7"/>`;
        case 'suv': return `<path d="M7 29 L7 34 Q7 36 9 36 L39 36 Q41 36 41 34 L41 29 L41 23 Q41 21 39 20 L34 19 L30 15 Q28 13 24 13 L19 13 Q16 13 14 15 L9 20 Q7 21 7 23Z" fill="${c}"/><path d="M13 20 L34 20 L30 15 Q28 14 24 14 L19 14 Q16.5 14 15 16Z" fill="${win}" opacity="0.85"/><path d="M7 27 L41 27" stroke="${dark}" stroke-width="1.2" fill="none"/><rect x="13" y="20" width="21" height="6.5" rx="1" fill="${win}" opacity="0.75"/><circle cx="13.5" cy="36" r="5" fill="${wheel}"/><circle cx="13.5" cy="36" r="2.8" fill="${rim}"/><circle cx="34.5" cy="36" r="5" fill="${wheel}"/><circle cx="34.5" cy="36" r="2.8" fill="${rim}"/><rect x="37" y="27" width="4" height="2.5" rx="1" fill="#FCD34D" opacity="0.9"/><rect x="7" y="27" width="4" height="2.5" rx="1" fill="#F87171" opacity="0.7"/>`;
        case 'mpv7': return `<path d="M7 30 L7 35 Q7 37 9 37 L39 37 Q41 37 41 35 L41 30 L41 22 Q41 20 39 19 L35 18 L31 14 Q29 12 25 12 L17 12 Q14 12 12 14 L9 18 Q7 19 7 22Z" fill="${c}"/><path d="M11 19 L36 19 L32 14 Q30 13 25 13 L18 13 Q15 13 13 15Z" fill="${win}" opacity="0.8"/><path d="M7 27 L41 27" stroke="${dark}" stroke-width="1" fill="none"/><rect x="11" y="19" width="25" height="7" rx="1.5" fill="${win}" opacity="0.7"/><circle cx="13" cy="37" r="5" fill="${wheel}"/><circle cx="13" cy="37" r="2.8" fill="${rim}"/><circle cx="35" cy="37" r="5" fill="${wheel}"/><circle cx="35" cy="37" r="2.8" fill="${rim}"/><rect x="38" y="27" width="3.5" height="2.5" rx="1" fill="#FCD34D" opacity="0.9"/><rect x="6.5" y="27" width="3.5" height="2.5" rx="1" fill="#F87171" opacity="0.7"/>`;
        case 'mpv8': return `<path d="M6 30 L6 35 Q6 37 8 37 L40 37 Q42 37 42 35 L42 30 L42 22 Q42 20 40 19 L36 18 L32 14 Q30 12 25 12 L17 12 Q14 12 12 14 L10 18 Q6 20 6 22Z" fill="${c}"/><path d="M10 19 L37 19 L32.5 14 Q30.5 13 25 13 L17.5 13 Q15 13 13 15Z" fill="${win}" opacity="0.8"/><rect x="10" y="19" width="27" height="7.5" rx="1.5" fill="${win}" opacity="0.65"/><line x1="21" y1="19" x2="21" y2="26.5" stroke="${dark}" stroke-width="0.8" opacity="0.6"/><line x1="28" y1="19" x2="28" y2="26.5" stroke="${dark}" stroke-width="0.8" opacity="0.6"/><path d="M6 27.5 L42 27.5" stroke="${dark}" stroke-width="1" fill="none"/><circle cx="12.5" cy="37" r="5" fill="${wheel}"/><circle cx="12.5" cy="37" r="2.8" fill="${rim}"/><circle cx="35.5" cy="37" r="5" fill="${wheel}"/><circle cx="35.5" cy="37" r="2.8" fill="${rim}"/><rect x="39" y="27" width="3.5" height="2.5" rx="1" fill="#FCD34D" opacity="0.9"/><rect x="5.5" y="27" width="3.5" height="2.5" rx="1" fill="#F87171" opacity="0.7"/>`;
        case 'minibus16': return `<rect x="5" y="16" width="38" height="22" rx="3" fill="${c}"/><rect x="5" y="16" width="38" height="8" rx="3" fill="${dark}"/><rect x="8" y="18" width="5" height="4.5" rx="1" fill="${win}"/><rect x="15" y="18" width="5" height="4.5" rx="1" fill="${win}"/><rect x="22" y="18" width="5" height="4.5" rx="1" fill="${win}"/><rect x="29" y="18" width="5" height="4.5" rx="1" fill="${win}"/><rect x="36" y="18" width="5" height="4.5" rx="1" fill="${win}"/><rect x="7" y="24.5" width="34" height="1" fill="${dark}" opacity="0.5"/><rect x="9" y="26" width="6" height="10" rx="1.5" fill="${dark}" opacity="0.5"/><circle cx="12" cy="37.5" r="4.5" fill="${wheel}"/><circle cx="12" cy="37.5" r="2.5" fill="${rim}"/><circle cx="36" cy="37.5" r="4.5" fill="${wheel}"/><circle cx="36" cy="37.5" r="2.5" fill="${rim}"/><rect x="39" y="22" width="4" height="2.5" rx="1" fill="#FCD34D" opacity="0.9"/><rect x="5" y="22" width="4" height="2.5" rx="1" fill="#F87171" opacity="0.7"/>`;
        case 'minibus': return `<rect x="4" y="15" width="40" height="23" rx="3.5" fill="${c}"/><rect x="4" y="15" width="40" height="9" rx="3.5" fill="${dark}"/><rect x="4" y="15" width="40" height="3" rx="3.5" fill="${light}" opacity="0.4"/><rect x="7" y="17" width="4.5" height="5" rx="1" fill="${win}"/><rect x="13" y="17" width="4.5" height="5" rx="1" fill="${win}"/><rect x="19" y="17" width="4.5" height="5" rx="1" fill="${win}"/><rect x="25" y="17" width="4.5" height="5" rx="1" fill="${win}"/><rect x="31" y="17" width="4.5" height="5" rx="1" fill="${win}"/><rect x="37" y="17" width="4.5" height="5" rx="1" fill="${win}"/><rect x="7" y="24" width="7" height="12" rx="1.5" fill="${dark}" opacity="0.4"/><circle cx="11" cy="37.5" r="4.5" fill="${wheel}"/><circle cx="11" cy="37.5" r="2.5" fill="${rim}"/><circle cx="37" cy="37.5" r="4.5" fill="${wheel}"/><circle cx="37" cy="37.5" r="2.5" fill="${rim}"/><rect x="40" y="20" width="4" height="2.5" rx="1" fill="#FCD34D" opacity="0.9"/><rect x="4" y="20" width="4" height="2.5" rx="1" fill="#F87171" opacity="0.7"/>`;
        case 'van': return `<path d="M6 22 L6 35 Q6 37 8 37 L40 37 Q42 37 42 35 L42 22 L42 19 Q42 17 40 16 L34 16 L30 12 Q28 11 24 11 L14 11 Q11 11 10 13 L8 16 Q6 17 6 19Z" fill="${c}"/><path d="M10 16 L30 16 L27 12 Q25.5 11.5 24 11.5 L15 11.5 Q12.5 11.5 11.5 13Z" fill="${win}" opacity="0.8"/><line x1="30" y1="11" x2="30" y2="37" stroke="${dark}" stroke-width="1.5"/><rect x="31" y="17" width="10" height="18" rx="1" fill="${dark}" opacity="0.2"/><circle cx="31" cy="26" r="1.5" fill="${dark}" opacity="0.7"/><circle cx="12.5" cy="37" r="5" fill="${wheel}"/><circle cx="12.5" cy="37" r="2.8" fill="${rim}"/><circle cx="36" cy="37" r="5" fill="${wheel}"/><circle cx="36" cy="37" r="2.8" fill="${rim}"/><rect x="39" y="22" width="3" height="2" rx="1" fill="#FCD34D" opacity="0.9"/><rect x="6" y="22" width="3" height="2" rx="1" fill="#F87171" opacity="0.7"/>`;
        case 'pickup': return `<path d="M7 26 L7 34 Q7 36 9 36 L41 36 Q43 36 43 34 L43 26 L43 22 L37 22 L37 19 Q37 17 35 16 L28 16 L24 13 Q22 12 19 12 L14 12 Q11 12 10 14 L8 17 Q7 18 7 20Z" fill="${c}"/><path d="M10 17 L29 17 L26 13 Q24.5 12.5 19.5 12.5 L15 12.5 Q12.5 12.5 11.5 14Z" fill="${win}" opacity="0.8"/><rect x="29" y="17" width="13" height="18" rx="1" fill="${dark}" opacity="0.3"/><line x1="29" y1="17" x2="29" y2="35" stroke="${dark}" stroke-width="2"/><line x1="32" y1="17" x2="32" y2="22" stroke="${dark}" stroke-width="0.7" opacity="0.6"/><line x1="36" y1="17" x2="36" y2="22" stroke="${dark}" stroke-width="0.7" opacity="0.6"/><circle cx="13" cy="36" r="5" fill="${wheel}"/><circle cx="13" cy="36" r="2.8" fill="${rim}"/><circle cx="36" cy="36" r="5" fill="${wheel}"/><circle cx="36" cy="36" r="2.8" fill="${rim}"/><rect x="40" y="23" width="3" height="2" rx="1" fill="#FCD34D" opacity="0.9"/><rect x="7" y="23" width="3" height="2" rx="1" fill="#F87171" opacity="0.7"/>`;
        case 'truck_s': return `<rect x="5" y="20" width="38" height="18" rx="2" fill="${c}"/><path d="M5 20 L5 38 L20 38 L20 20Z" fill="${dark}" opacity="0.15"/><rect x="7" y="22" width="10" height="7" rx="1.5" fill="${win}"/><rect x="20" y="14" width="23" height="24" rx="2" fill="${c}"/><rect x="20" y="14" width="23" height="2" rx="1" fill="${light}" opacity="0.5"/><line x1="20" y1="22" x2="43" y2="22" stroke="${dark}" stroke-width="0.6" opacity="0.4"/><line x1="20" y1="29" x2="43" y2="29" stroke="${dark}" stroke-width="0.6" opacity="0.4"/><line x1="30" y1="14" x2="30" y2="38" stroke="${dark}" stroke-width="0.6" opacity="0.4"/><circle cx="11" cy="38" r="4.5" fill="${wheel}"/><circle cx="11" cy="38" r="2.5" fill="${rim}"/><circle cx="36" cy="38" r="4.5" fill="${wheel}"/><circle cx="36" cy="38" r="2.5" fill="${rim}"/><rect x="5" y="24" width="3.5" height="2" rx="0.5" fill="#F87171" opacity="0.7"/>`;
        case 'truck_m': return `<rect x="3" y="22" width="19" height="16" rx="2" fill="${c}"/><rect x="5" y="24" width="13" height="8" rx="1.5" fill="${win}"/><rect x="20" y="12" width="26" height="26" rx="2" fill="${c}"/><rect x="20" y="12" width="26" height="3" rx="1.5" fill="${light}" opacity="0.4"/><line x1="20" y1="22" x2="46" y2="22" stroke="${dark}" stroke-width="0.8" opacity="0.5"/><line x1="20" y1="30" x2="46" y2="30" stroke="${dark}" stroke-width="0.8" opacity="0.5"/><line x1="33" y1="12" x2="33" y2="38" stroke="${dark}" stroke-width="0.8" opacity="0.5"/><circle cx="9" cy="38" r="4.5" fill="${wheel}"/><circle cx="9" cy="38" r="2.5" fill="${rim}"/><circle cx="28" cy="38" r="4.5" fill="${wheel}"/><circle cx="28" cy="38" r="2.5" fill="${rim}"/><circle cx="40" cy="38" r="4.5" fill="${wheel}"/><circle cx="40" cy="38" r="2.5" fill="${rim}"/><rect x="3" y="26" width="3" height="2" rx="0.5" fill="#F87171" opacity="0.7"/>`;
        case 'truck_l': return `<rect x="2" y="23" width="16" height="15" rx="2" fill="${c}"/><rect x="4" y="25" width="11" height="7.5" rx="1.5" fill="${win}"/><rect x="16" y="10" width="30" height="28" rx="2" fill="${c}"/><rect x="16" y="10" width="30" height="3" rx="1.5" fill="${light}" opacity="0.4"/><line x1="16" y1="21" x2="46" y2="21" stroke="${dark}" stroke-width="0.5" opacity="0.5"/><line x1="16" y1="30" x2="46" y2="30" stroke="${dark}" stroke-width="0.5" opacity="0.5"/><line x1="28" y1="10" x2="28" y2="38" stroke="${dark}" stroke-width="0.5" opacity="0.5"/><line x1="37" y1="10" x2="37" y2="38" stroke="${dark}" stroke-width="0.5" opacity="0.5"/><circle cx="8" cy="38" r="4" fill="${wheel}"/><circle cx="8" cy="38" r="2.2" fill="${rim}"/><circle cx="24" cy="38" r="4.5" fill="${wheel}"/><circle cx="24" cy="38" r="2.5" fill="${rim}"/><circle cx="32" cy="38" r="4.5" fill="${wheel}"/><circle cx="32" cy="38" r="2.5" fill="${rim}"/><circle cx="40" cy="38" r="4.5" fill="${wheel}"/><circle cx="40" cy="38" r="2.5" fill="${rim}"/><rect x="2" y="27" width="3" height="2" rx="0.5" fill="#F87171" opacity="0.7"/>`;
        case 'container': return `<rect x="2" y="24" width="14" height="14" rx="2" fill="${c}"/><rect x="3" y="26" width="10" height="7" rx="1.5" fill="${win}"/><rect x="14" y="9" width="33" height="29" rx="2" fill="${c}"/><rect x="14" y="9" width="33" height="3" rx="0" fill="${dark}" opacity="0.2"/><rect x="14" y="9" width="33" height="1.5" rx="1" fill="${light}" opacity="0.3"/><line x1="21" y1="12" x2="21" y2="38" stroke="${dark}" stroke-width="0.7" opacity="0.4"/><line x1="28" y1="12" x2="28" y2="38" stroke="${dark}" stroke-width="0.7" opacity="0.4"/><line x1="35" y1="12" x2="35" y2="38" stroke="${dark}" stroke-width="0.7" opacity="0.4"/><line x1="42" y1="12" x2="42" y2="38" stroke="${dark}" stroke-width="0.7" opacity="0.4"/><line x1="14" y1="19" x2="47" y2="19" stroke="${dark}" stroke-width="0.6" opacity="0.3"/><line x1="14" y1="27" x2="47" y2="27" stroke="${dark}" stroke-width="0.6" opacity="0.3"/><circle cx="43" cy="24" r="0.8" fill="${dark}" opacity="0.7"/><circle cx="45" cy="24" r="0.8" fill="${dark}" opacity="0.7"/><circle cx="7" cy="38" r="4" fill="${wheel}"/><circle cx="7" cy="38" r="2.2" fill="${rim}"/><circle cx="22" cy="38" r="4.5" fill="${wheel}"/><circle cx="22" cy="38" r="2.5" fill="${rim}"/><circle cx="31" cy="38" r="4.5" fill="${wheel}"/><circle cx="31" cy="38" r="2.5" fill="${rim}"/><circle cx="40" cy="38" r="4.5" fill="${wheel}"/><circle cx="40" cy="38" r="2.5" fill="${rim}"/><rect x="2" y="28" width="2.5" height="2" rx="0.5" fill="#F87171" opacity="0.7"/>`;
        case 'bus': return `<rect x="3" y="12" width="42" height="27" rx="3.5" fill="${c}"/><rect x="3" y="20" width="42" height="3" fill="${dark}" opacity="0.2"/><rect x="6" y="13.5" width="5.5" height="5.5" rx="1.2" fill="${win}"/><rect x="13.5" y="13.5" width="5.5" height="5.5" rx="1.2" fill="${win}"/><rect x="21" y="13.5" width="5.5" height="5.5" rx="1.2" fill="${win}"/><rect x="28.5" y="13.5" width="5.5" height="5.5" rx="1.2" fill="${win}"/><rect x="36" y="13.5" width="5.5" height="5.5" rx="1.2" fill="${win}"/><rect x="4.5" y="13.5" width="5" height="7" rx="1.2" fill="${win}" opacity="0.75"/><rect x="3" y="23" width="3" height="4" rx="1" fill="${light}" opacity="0.5"/><rect x="5" y="22" width="7" height="14" rx="1.5" fill="${dark}" opacity="0.3"/><circle cx="12" cy="39" r="4.5" fill="${wheel}"/><circle cx="12" cy="39" r="2.5" fill="${rim}"/><circle cx="36" cy="39" r="4.5" fill="${wheel}"/><circle cx="36" cy="39" r="2.5" fill="${rim}"/><rect x="40" y="18" width="5" height="3" rx="1" fill="#FCD34D" opacity="0.9"/><rect x="3" y="18" width="5" height="3" rx="1" fill="#F87171" opacity="0.7"/>`;
        case 'ev': return `<path d="M9 28 L9 33 Q9 36 11 36 L37 36 Q39 36 39 33 L39 28 L39 24 Q39 22 38 21 L34 20 L30 17 Q28 16 24 16 L20 16 Q17 16 15 17 L11 20 Q9 21 9 23Z" fill="${c}"/><path d="M15 20 L33 20 L29.5 17.5 Q27.5 16.5 24 16.5 L21 16.5 Q18 16.5 16.5 18Z" fill="${win}" opacity="0.9"/><text x="22" y="31" font-size="9" fill="white" opacity="0.9" font-weight="bold">⚡</text><path d="M9 26 L39 26" stroke="${dark}" stroke-width="0.8" fill="none"/><path d="M9 23 L39 23" stroke="${light}" stroke-width="0.5" fill="none" opacity="0.4"/><circle cx="14.5" cy="36" r="4.5" fill="${wheel}"/><circle cx="14.5" cy="36" r="2.5" fill="${rim}"/><circle cx="33.5" cy="36" r="4.5" fill="${wheel}"/><circle cx="33.5" cy="36" r="2.5" fill="${rim}"/><rect x="35" y="26" width="4" height="1.2" rx="0.6" fill="#7DD3FC" opacity="0.9"/><rect x="9" y="26" width="4" height="1.2" rx="0.6" fill="#EF4444" opacity="0.8"/>`;
        case 'service': return `<path d="M7 24 L7 35 Q7 37 9 37 L39 37 Q41 37 41 35 L41 24 L41 19 Q41 17 39 16 L34 16 L30 13 Q28 12 24 12 L15 12 Q12 12 11 13 L9 16 Q7 17 7 19Z" fill="${c}"/><path d="M11 16 L35 16 L31 13 Q29.5 12.5 24 12.5 L16 12.5 Q13.5 12.5 12.5 14Z" fill="${win}" opacity="0.8"/><rect x="7" y="24" width="34" height="3" fill="${light}" opacity="0.3"/><rect x="7" y="24" width="34" height="1" fill="white" opacity="0.15"/><rect x="22" y="27" width="4" height="7" rx="1" fill="white" opacity="0.25"/><circle cx="24" cy="27" r="2" fill="white" opacity="0.3"/><circle cx="12.5" cy="37" r="5" fill="${wheel}"/><circle cx="12.5" cy="37" r="2.8" fill="${rim}"/><circle cx="35.5" cy="37" r="5" fill="${wheel}"/><circle cx="35.5" cy="37" r="2.8" fill="${rim}"/><rect x="38" y="22" width="3.5" height="2.5" rx="1" fill="#FCD34D" opacity="0.9"/><rect x="6.5" y="22" width="3.5" height="2.5" rx="1" fill="#F87171" opacity="0.7"/>`;
        case 'special': return `<rect x="5" y="18" width="38" height="20" rx="3" fill="${c}"/><rect x="12" y="12" width="24" height="8" rx="2" fill="${dark}" opacity="0.6"/><rect x="14" y="9" width="20" height="5" rx="1.5" fill="${dark}" opacity="0.4"/><rect x="15" y="9" width="4" height="2" rx="1" fill="#EF4444" opacity="0.9"/><rect x="22" y="9" width="4" height="2" rx="1" fill="#3B82F6" opacity="0.9"/><rect x="29" y="9" width="4" height="2" rx="1" fill="#EF4444" opacity="0.9"/><rect x="7" y="20" width="10" height="7" rx="1.5" fill="${win}"/><line x1="20" y1="18" x2="20" y2="38" stroke="${dark}" stroke-width="1.5"/><rect x="21" y="20" width="5" height="5" rx="1" fill="${dark}" opacity="0.2"/><rect x="28" y="20" width="5" height="5" rx="1" fill="${dark}" opacity="0.2"/><rect x="35" y="20" width="5" height="5" rx="1" fill="${dark}" opacity="0.2"/><circle cx="11" cy="38" r="4.5" fill="${wheel}"/><circle cx="11" cy="38" r="2.5" fill="${rim}"/><circle cx="36" cy="38" r="4.5" fill="${wheel}"/><circle cx="36" cy="38" r="2.5" fill="${rim}"/><rect x="40" y="22" width="3" height="2" rx="0.5" fill="#FCD34D" opacity="0.9"/><rect x="5" y="22" width="3" height="2" rx="0.5" fill="#F87171" opacity="0.7"/>`;
        
        // 🏍️ XE MÁY ĐƯỢC THIẾT KẾ ĐƠN SẮC FLAT-STYLE SANG TRỌNG
        case 'motorcycle': return `
            <circle cx="13" cy="35" r="5" fill="${wheel}"/><circle cx="13" cy="35" r="2.5" fill="${rim}"/>
            <circle cx="35" cy="35" r="5" fill="${wheel}"/><circle cx="35" cy="35" r="2.5" fill="${rim}"/>
            <line x1="35" y1="35" x2="29" y2="15" stroke="${dark}" stroke-width="2.2" stroke-linecap="round"/>
            <line x1="27" y1="15" x2="31" y2="15" stroke="#111" stroke-width="2.5" stroke-linecap="round"/>
            <rect x="18" y="26" width="11" height="8" rx="2" fill="${dark}"/>
            <rect x="19" y="28" width="8" height="5" rx="1" fill="${light}" opacity="0.8"/>
            <path d="M11 21 C 14 22, 18 22, 21 20 L 19 25 L 11 25 Z" fill="#111"/>
            <path d="M17 19 C 20 15, 27 15, 29 19 L 27 25 L 17 25 Z" fill="${c}"/>
            <line x1="11" y1="31" x2="22" y2="31" stroke="${light}" stroke-width="1.8" stroke-linecap="round"/>
            <rect x="31" y="17" width="2" height="2" rx="0.5" fill="#FCD34D" opacity="0.9"/>`;
            
        // 🚲 XE ĐẠP ĐỒ HỌA FLAT-VECTOR ĐỒNG BỘ ĐỘ DÀY NÉT VẼ
        case 'bicycle': return `
            <circle cx="13" cy="35" r="5.5" fill="none" stroke="${wheel}" stroke-width="1.5"/><circle cx="13" cy="35" r="1.8" fill="${rim}"/>
            <circle cx="35" cy="35" r="5.5" fill="none" stroke="${wheel}" stroke-width="1.5"/><circle cx="35" cy="35" r="1.8" fill="${rim}"/>
            <line x1="13" y1="35" x2="23" y2="35" stroke="${c}" stroke-width="1.8" stroke-linecap="round"/>
            <line x1="13" y1="35" x2="21" y2="19" stroke="${c}" stroke-width="1.8" stroke-linecap="round"/>
            <line x1="23" y1="35" x2="21" y2="19" stroke="${c}" stroke-width="1.8" stroke-linecap="round"/>
            <line x1="23" y1="35" x2="31" y2="19" stroke="${c}" stroke-width="1.8" stroke-linecap="round"/>
            <line x1="21" y1="19" x2="31" y2="19" stroke="${c}" stroke-width="1.8" stroke-linecap="round"/>
            <line x1="35" y1="35" x2="31" y2="19" stroke="${c}" stroke-width="1.8" stroke-linecap="round"/>
            <line x1="31" y1="19" x2="30" y2="13" stroke="${dark}" stroke-width="1.5" stroke-linecap="round"/>
            <line x1="26" y1="13" x2="32" y2="13" stroke="#222" stroke-width="2" stroke-linecap="round"/>
            <path d="M18.5 19 Q21 17.5 23 19 L21 17 Z" fill="#222"/>
            <circle cx="23" cy="35" r="2.2" fill="${dark}" stroke="${light}" stroke-width="0.6"/>`;
            
        default: return `<rect x="10" y="15" width="28" height="18" rx="3" fill="${c}"/>`;
    }
}
function jsMakeSVG(vehicleId, color, state = 'normal') {
    const isOffline = state === 'offline', isSelected = state === 'selected', col = isOffline ? '#555' : color;
    const ring = isSelected ? `<circle cx="24" cy="24" r="22" fill="none" stroke="${col}" stroke-width="1.5" stroke-dasharray="4 3" opacity="0.7"/>` : '';
    const glow = isSelected ? `<filter id="glow"><feGaussianBlur stdDeviation="2" result="blur"/><feMerge><feMergeNode in="blur"/><feMergeNode in="SourceGraphic"/></feMerge></filter>` : '';
    
    // 🛡️ CHỐNG TÀNG HÌNH MÀU TRẮNG: Nếu mã màu là trắng (#FFFFFF), phủ hiệu ứng drop-shadow để nhìn rõ bánh xe trên nền trắng sữa
    const whiteStyle = (col.toUpperCase() === '#FFFFFF') ? 'filter: drop-shadow(0px 0px 0.8px rgba(0,0,0,0.6));' : '';
    
    return `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48" width="56" height="56" style="${whiteStyle}"><defs>${glow}</defs>${ring}<g opacity="${isOffline ? '0.35' : '1'}"${isSelected ? ' filter="url(#glow)"' : ''}>${jsDrawVehicleBody(vehicleId, col)}</g></svg>`;
}

const VEHICLE_ICONS = [
  { id: 'sedan',     label: 'Xe 4 chỗ' }, { id: 'suv',       label: 'SUV' }, { id: 'mpv7',      label: 'Xe 7 chỗ' }, { id: 'mpv8',      label: 'Xe 8 chỗ' },
  { id: 'minibus16', label: 'Xe 16 chỗ' }, { id: 'minibus',   label: 'Minibus' }, { id: 'van',       label: 'Van' }, { id: 'pickup',    label: 'Pickup' },
  { id: 'truck_s',   label: 'Xe tải nhỏ' }, { id: 'truck_m',   label: 'Xe tải trung' }, { id: 'truck_l',   label: 'Xe tải lớn' }, { id: 'container', label: 'Container' },
  { id: 'bus',       label: 'Xe khách' }, { id: 'ev',        label: 'Xe điện' }, { id: 'service',   label: 'Xe dịch vụ' }, { id: 'special',   label: 'Xe chuyên dụng' },
  { id: 'motorcycle',label: 'Xe máy / Moto' }, { id: 'bicycle',   label: 'Xe đạp' } // Đã thêm 2 mã xe mới
];

const VEHICLE_COLORS = [
  { name: 'white',      value: '#FFFFFF' }, // ⚪ Đã bổ sung lựa chọn màu trắng tinh tế
  { name: 'orange',     value: '#FF6B00' }, { name: 'blue',       value: '#2563EB' }, { name: 'green',      value: '#16A34A' }, { name: 'red',        value: '#DC2626' },
  { name: 'purple',     value: '#9333EA' }, { name: 'cyan',       value: '#0891B2' }, { name: 'yellow',     value: '#EAB308' }, { name: 'rose',       value: '#F43F5E' },
  { name: 'teal',       value: '#14B8A6' }, { name: 'indigo',     value: '#6366F1' }, { name: 'lime',       value: '#84CC16' }, { name: 'deeporange', value: '#F97316' },
  { name: 'slate',      value: '#64748B' }, { name: 'darknavy',   value: '#0F172A' }, { name: 'pink',       value: '#EC4899' }, { name: 'violet',     value: '#A855F7' }
];

let selectorsInitialized = false;
function initVehicleSelectors() {
    if (selectorsInitialized) return;
    const iconGrid = document.getElementById('vIconSelectGrid'), colorGrid = document.getElementById('vColorSelectGrid');
    iconGrid.innerHTML = '';
    VEHICLE_ICONS.forEach(icon => {
        const btn = document.createElement('div'); btn.className = 'icon-select-btn'; btn.id = 'iconBtn_' + icon.id; btn.title = icon.label;
        btn.innerHTML = jsMakeSVG(icon.id, '#88899a').replace('width="56" height="56"', 'width="32" height="32"');
        btn.onclick = () => selectVehicleIcon(icon.id); iconGrid.appendChild(btn);
    });
    colorGrid.innerHTML = '';
    VEHICLE_COLORS.forEach(color => {
        const btn = document.createElement('div'); btn.className = 'color-swatch-btn'; btn.id = 'colorBtn_' + color.value.replace('#', '');
        btn.style.backgroundColor = color.value; btn.style.color = color.value; btn.title = color.name;
        btn.onclick = () => selectVehicleColor(color.value); colorGrid.appendChild(btn);
    });
    selectorsInitialized = true;
}

function selectVehicleIcon(id) {
    document.getElementById('vIcon').value = id;
    document.querySelectorAll('.icon-select-btn').forEach(btn => btn.classList.remove('selected'));
    const activeBtn = document.getElementById('iconBtn_' + id); if (activeBtn) activeBtn.classList.add('selected');
    updatePreview();
}
function selectVehicleColor(color) {
    document.getElementById('vIconColor').value = color;
    document.querySelectorAll('.color-swatch-btn').forEach(btn => btn.classList.remove('selected'));
    const activeBtn = document.getElementById('colorBtn_' + color.replace('#', '')); if (activeBtn) activeBtn.classList.add('selected');
    updatePreview();
}
function updatePreview() {
    const icon = document.getElementById('vIcon').value, color = document.getElementById('vIconColor').value;
    document.getElementById('vIconPreviewBox').innerHTML = jsMakeSVG(icon, color);
}

// ── DỊCH THUẬT ĐỘNG HOÀN TOÀN TRÊN POPUP MODAL QUA JAVASCRIPT ──
function openEditVehicle(v) {
    document.getElementById('modalVehicleTitle').textContent = ( '<?= $lang ?>' === 'vi' ? '✏️ Chỉnh Sửa Xe: ' : '✏️ 車両編集: ' ) + v.plate_number;
    document.getElementById('vAction').value = 'edit_vehicle'; document.getElementById('vId').value = v.id;
    document.getElementById('vPlate').value = v.plate_number; document.getElementById('vName').value = v.vehicle_name;
    document.getElementById('vNotes').value = v.notes || ''; document.getElementById('vType').value = v.vehicle_type; document.getElementById('vStatus').value = v.status;
    initVehicleSelectors(); selectVehicleIcon(v.icon || 'sedan'); selectVehicleColor(v.icon_color || '#FFFFFF');
    updatePreview();
    openModal('modalVehicle');
}
function openAddVehicle() {
    document.getElementById('modalVehicleTitle').textContent = '<?= $txt['modal_v_add'] ?>';
    document.getElementById('vAction').value = 'add_vehicle'; document.getElementById('vId').value = '';
    document.getElementById('vPlate').value = ''; document.getElementById('vName').value = ''; document.getElementById('vNotes').value = '';
    document.getElementById('vType').value = 'Sedan'; document.getElementById('vStatus').value = 'available';
    initVehicleSelectors(); selectVehicleIcon('sedan'); selectVehicleColor('#FFFFFF');
    updatePreview();
    openModal('modalVehicle');
}
function openEditDriver(d) {
    document.getElementById('modalDriverTitle').textContent = ( '<?= $lang ?>' === 'vi' ? '✏️ Chỉnh Sửa: ' : '✏️ 運転手編集: ' ) + d.full_name;
    document.getElementById('dAction').value = 'edit_driver'; document.getElementById('dId').value = d.id;
    document.getElementById('dName').value = d.full_name; document.getElementById('dPhone').value = d.phone || ''; document.getElementById('dLicense').value = d.license_number || ''; document.getElementById('dStatus').value = d.status;
    openModal('modalDriver');
}
function openAddDriver() {
    document.getElementById('modalDriverTitle').textContent = '<?= $txt['modal_d_add'] ?>';
    document.getElementById('dAction').value = 'add_driver'; document.getElementById('dId').value = '';
    document.getElementById('dName').value = ''; document.getElementById('dPhone').value = ''; document.getElementById('dLicense').value = ''; document.getElementById('dStatus').value = 'available';
    openModal('modalDriver');
}

function openModal(id) { document.getElementById(id).classList.add('open'); }
function closeModal(id) { document.getElementById(id).classList.remove('open'); }
document.querySelectorAll('.modal-backdrop').forEach(el => { el.addEventListener('click', function(e) { if (e.target === this) closeModal(this.id); }); });
document.addEventListener('keydown', e => { if (e.key === 'Escape') { document.querySelectorAll('.modal-backdrop.open').forEach(m => m.classList.remove('open')); } });

// 🚀 KÍCH HOẠT HIỆU ỨNG TRƯỢT XOÁY CHO HỘP THÔNG BÁO POPUP
function closePopupAlert() {
    const alertBox = document.getElementById('sysPopupAlert');
    if (alertBox) alertBox.classList.remove('show');
}
window.addEventListener('DOMContentLoaded', () => {
    const alertBox = document.getElementById('sysPopupAlert');
    if (alertBox) {
        setTimeout(() => { alertBox.classList.add('show'); }, 100);
        setTimeout(() => { closePopupAlert(); }, 4000); // Tự động biến mất sau 4 giây
    }
});

document.getElementById('theme-toggle').addEventListener('click', function() {
    document.body.classList.toggle('light-theme');
    if (document.body.classList.contains('light-theme')) { localStorage.setItem('panel-theme', 'light'); }
    else { localStorage.setItem('panel-theme', 'dark'); }
});
</script>
</body>
</html>