<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/sync_status.php';
requireLogin();

$user = currentUser();

// ── KHỞI TẠO BỘ LỌC NGÔN NGỮ ĐỒNG BỘ THEO URL (MẶC ĐỊNH LÀ VI) ──
$lang = $_GET['lang'] ?? 'vi';
if (!in_array($lang, ['vi', 'jp'])) {
    $lang = 'vi';
}

// ── BỘ TỪ ĐIỂN SONG NGỮ VIỆT - NHẬT TOÀN DIỆN CHO PANEL CHUYẾN XE ──
$lang_pack = [
    'vi' => [
        'nav_trips' => '📋 Chuyến Xe',
        'nav_create' => '➕ Tạo Chuyến',
        'nav_vehicles' => '🚙 Xe & Tài Xế',
        'nav_dashboard' => '🖥️ Màn Bảo Vệ',
        'logout' => 'Đăng xuất',
        'stat_today' => 'Chuyến hôm nay',
        'stat_scheduled' => 'Đã lên lịch',
        'stat_departed' => 'Đang đi',
        'stat_returned' => 'Đã về',
        'stat_ready' => 'Xe sẵn sàng',
        'stat_drivers' => 'Tài xế rảnh',
        'table_title' => '📋 Danh Sách Chuyến Xe',
        'export_excel' => '📥 Xuất Excel',
        'tab_today' => 'Hôm nay',
        'tab_upcoming' => 'Sắp đi',
        'tab_active' => 'Đang đi',
        'tab_all' => 'Tất cả',
        'lbl_from' => 'Từ ngày',
        'lbl_to' => 'Đến ngày',
        'btn_filter' => '🔍 Lọc',
        'text_trips' => 'chuyến',
        'hint_max_6m' => '⚠️ Tối đa 6 tháng gần nhất',
        'err_invalid_date' => 'Ngày không hợp lệ.',
        'err_date_order' => 'Ngày kết thúc phải sau ngày bắt đầu.',
        'err_date_range' => 'Khoảng thời gian không được vượt quá 6 tháng gần nhất.',
        'err_max_allowed' => 'Khoảng thời gian tối đa cho phép là 6 tháng.',
        'err_fix_date' => '⛔ Vui lòng chọn khoảng thời gian hợp lệ để xem dữ liệu.',
        'no_data' => 'Không có chuyến nào.',
        'th_id' => '#',
        'th_veh_driver' => 'Xe / Tài Xế',
        'th_requester' => 'Người Yêu Cầu',
        'th_destination' => 'Điểm Đến',
        'th_purpose' => 'Mục Đích',
        'th_depart' => 'Xuất Phát',
        'th_expected' => 'Dự Kiến Về',
        'th_actual' => 'Về Thực Tế',
        'th_status' => 'Trạng Thái',
        'th_actions' => 'Thao Tác',
        'btn_edit' => '✏️ Sửa',
        'btn_depart' => '🚀 Xuất',
        'btn_return' => '✅ Về',
        'status_scheduled' => 'Đã lên lịch',
        'status_departed' => 'Đã xuất phát',
        'status_returned' => 'Đã về',
        'status_cancelled' => 'Đã hủy',
        'unit_cars' => ' xe'
    ],
    'jp' => [
        'nav_trips' => '📋 運行リスト',
        'nav_create' => '➕ 運行作成',
        'nav_vehicles' => '🚙 車両・運転手',
        'nav_dashboard' => '🖥️ 警備員画面',
        'logout' => 'ログアウト',
        'stat_today' => '本日の運行数',
        'stat_scheduled' => '配車完了',
        'stat_departed' => '外出中',
        'stat_returned' => '帰着済',
        'stat_ready' => '稼働可能車',
        'stat_drivers' => '待機運転手',
        'table_title' => '📋 運行スケジュール一覧',
        'export_excel' => '📥 Excel出力',
        'tab_today' => '本日',
        'tab_upcoming' => '出発前',
        'tab_active' => '運行中',
        'tab_all' => 'すべて',
        'lbl_from' => '開始日',
        'lbl_to' => '終了日',
        'btn_filter' => '🔍 検索',
        'text_trips' => '件',
        'hint_max_6m' => '⚠️ 直近6ヶ月まで選択可能',
        'err_invalid_date' => '日付が無効です。',
        'err_date_order' => '終了日は開始日より後の日付にしてください。',
        'err_date_range' => '直近6ヶ月以内の期間を選択してください。',
        'err_max_allowed' => '最大選択期間は6ヶ月です。',
        'err_fix_date' => '⛔ データを表示するには、有効な期間を選択してください。',
        'no_data' => '運行データがありません。',
        'th_id' => '#',
        'th_veh_driver' => '車両 / 運転手',
        'th_requester' => '申請者',
        'th_destination' => '目的地',
        'th_purpose' => '目的',
        'th_depart' => '出発日時',
        'th_expected' => '帰着予定',
        'th_actual' => '実帰着時刻',
        'th_status' => 'ステータス',
        'th_actions' => '操作',
        'btn_edit' => '✏️ 編集',
        'btn_depart' => '🚀 出発',
        'btn_return' => '✅ 帰着',
        'status_scheduled' => '配車済',
        'status_departed' => '出発済',
        'status_returned' => '帰着済',
        'status_cancelled' => 'キャンセル',
        'unit_cars' => '台'
    ]
];

$txt = $lang_pack[$lang];

// Ma trận ánh xạ nhãn trạng thái tĩnh của DB sang ngôn ngữ động
$status_text_map = [
    'scheduled' => $txt['status_scheduled'],
    'departed'  => $txt['status_departed'],
    'returned'  => $txt['status_returned'],
    'cancelled' => $txt['status_cancelled']
];

// Xử lý cập nhật trạng thái nhanh từ biểu mẫu nút bấm hành động
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'update_status') {
        $tripId = (int)$_POST['trip_id'];
        $status = $_POST['status'];
        $allowed = ['scheduled','departed','returned','cancelled'];
        if (in_array($status, $allowed)) {
            $ti = $pdo->prepare("SELECT vehicle_id, driver_id FROM trips WHERE id = ?");
            $ti->execute([$tripId]);
            $tripInfo = $ti->fetch();

            $actualReturn = ($status === 'returned') ? date('Y-m-d H:i:s') : null;
            $stmt = $pdo->prepare("UPDATE trips SET status=?, actual_return=? WHERE id=?");
            $stmt->execute([$status, $actualReturn, $tripId]);

            if ($tripInfo) {
                syncVehicleAndDriver($pdo, $tripInfo['vehicle_id'], $tripInfo['driver_id']);
            }
        }
    }
    // Duy trì tham số lọc và ngôn ngữ sau khi chuyển đổi trạng thái POST hành động
    header('Location: index.php?lang=' . $lang . '&filter=' . urlencode($_GET['filter'] ?? 'today'));
    exit;
}

// Thống kê nhanh danh mục
$stats = $pdo->query("
    SELECT
        COUNT(*) as total,
        SUM(status='scheduled') as scheduled,
        SUM(status='departed') as departed,
        SUM(status='returned') as returned,
        SUM(status='cancelled') as cancelled
    FROM trips WHERE DATE(departure_time) = CURDATE()
")->fetch();

$availableVehicles = $pdo->query("SELECT COUNT(*) FROM vehicles WHERE status='available'")->fetchColumn();
$totalVehicles     = $pdo->query("SELECT COUNT(*) FROM vehicles")->fetchColumn();
$availableDrivers  = $pdo->query("SELECT COUNT(*) FROM drivers WHERE status='available'")->fetchColumn();
$totalDrivers      = $pdo->query("SELECT COUNT(*) FROM drivers")->fetchColumn();

// ─── Xử lý bộ lọc dữ liệu ────────────────────────────────────────
$filter     = $_GET['filter'] ?? 'today';
$dateError  = '';
$start_date = '';
$end_date   = '';
$params     = [];

if ($filter === 'today') {
    $where = "DATE(t.departure_time) = CURDATE()";
} elseif ($filter === 'upcoming') {
    $where = "t.departure_time > NOW() AND t.status='scheduled'";
} elseif ($filter === 'active') {
    $where = "t.status='departed'";
} elseif ($filter === 'all') {
    $sixMonthsAgo = date('Y-m-d', strtotime('-6 months'));
    $today        = date('Y-m-d');
    $default30    = date('Y-m-d', strtotime('-29 days'));

    $raw_start = trim($_GET['start_date'] ?? '');
    $raw_end   = trim($_GET['end_date'] ?? '');

    if ($raw_start === '' || $raw_end === '') {
        $start_date = $default30;
        $end_date   = $today;
    } else {
        $start_date = $raw_start;
        $end_date   = $raw_end;
    }

    $ts_start = strtotime($start_date);
    $ts_end   = strtotime($end_date);
    $ts_6m    = strtotime($sixMonthsAgo);

    if ($ts_start === false || $ts_end === false) {
        $dateError  = $txt['err_invalid_date'];
        $start_date = $default30;
        $end_date   = $today;
    } elseif ($ts_end < $ts_start) {
        $dateError  = $txt['err_date_order'];
    } elseif ($ts_start < $ts_6m) {
        $dateError  = $txt['err_date_range'] . ' (From ' . date('d/m/Y', $ts_6m) . ').';
    } elseif (($ts_end - $ts_start) > 182 * 86400) {
        $dateError  = $txt['err_max_allowed'];
    } else {
        if ($ts_end > strtotime($today)) {
            $end_date = $today;
        }
    }

    if ($dateError) {
        $where  = "1=0";
        $params = [];
    } else {
        $where  = "DATE(t.departure_time) BETWEEN :start_date AND :end_date";
        $params = [':start_date' => $start_date, ':end_date' => $end_date];
    }
} else {
    $where = "1=1";
}

if (!empty($params)) {
    $sql = "SELECT t.*, v.plate_number, v.vehicle_name, d.full_name as driver_name, u.full_name as created_by_name
            FROM trips t
            LEFT JOIN vehicles v ON t.vehicle_id = v.id
            LEFT JOIN drivers d ON t.driver_id = d.id
            LEFT JOIN users u ON t.created_by = u.id
            WHERE $where
            ORDER BY t.departure_time DESC LIMIT 500";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $trips = $stmt->fetchAll();
} else {
    $trips = $pdo->query("
        SELECT t.*, v.plate_number, v.vehicle_name, d.full_name as driver_name, u.full_name as created_by_name
        FROM trips t
        LEFT JOIN vehicles v ON t.vehicle_id = v.id
        LEFT JOIN drivers d ON t.driver_id = d.id
        LEFT JOIN users u ON t.created_by = u.id
        WHERE $where
        ORDER BY t.departure_time DESC LIMIT 100
    ")->fetchAll();
}

// ─── ĐÍNH KÈM THAM SỐ NGÔN NGỮ ĐỘNG VÀO URL XUẤT EXCEL ───
$exportUrl = '';
if ($filter === 'all' && !$dateError) {
    $exportUrl = 'export_trip_report.php?filter=all'
               . '&start_date=' . urlencode($start_date)
               . '&end_date='   . urlencode($end_date);
} elseif ($filter !== 'all') {
    $exportUrl = 'export_trip_report.php?filter=' . urlencode($filter);
}
$exportUrl .= '&lang=' . $lang; // Luồng chuyển đổi ngôn ngữ tệp báo cáo Excel hạ tầng ngầm
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= $txt['table_title'] ?> — <?= SITE_NAME ?></title>
<link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/panel.css">
<style>
/* ── CONFIG LIGHT THEME TRẮNG SỮA TRÊN PANEL CHUYẾN XE ── */
body.light-theme {
    --navy: #f5f5f0;      
    --navy2: #ffffff;     
    --navy3: #eaf0f6;     
    --white: #0f172a;     
    --border: rgba(15, 23, 42, 0.08); 
    --cyan: #0891b2;      
    --blue2: #2563eb;     
}
body.light-theme .nav-links a:hover,
body.light-theme .nav-links a.active { background: rgba(0,0,0,0.05); }
body.light-theme .data-table tbody tr { border-bottom: 1px solid rgba(0,0,0,0.04); }
body.light-theme .data-table tbody tr:hover { background: rgba(0,0,0,0.015); }
body.light-theme .stat-card { box-shadow: 0 4px 12px rgba(0,0,0,0.02); }
body.light-theme .table-wrap { box-shadow: 0 4px 16px rgba(0,0,0,0.03); }
body.light-theme .filter-tab:hover { background: rgba(0,0,0,0.04); }
body.light-theme .date-filter-panel { background: rgba(0,0,0,0.015); border-color: rgba(0,0,0,0.05); }
body.light-theme .date-filter-panel input[type="date"] { background: #ffffff; border-color: rgba(0,0,0,0.12); color: #0f172a; }
body.light-theme .date-filter-panel input[type="date"]::-webkit-calendar-picker-indicator { filter: invert(0); }

/* PHONG CÁCH NÚT CHUYỂN ĐỔI THEME MẶT TRỜI / MẶT TRĂNG GÓC PHẢI */
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
.theme-toggle-btn:hover { background: rgba(6, 182, 212, 0.15); border-color: var(--cyan); }
.theme-toggle-btn .moon-icon { display: none; }
.theme-toggle-btn .sun-icon { display: block; }

body.light-theme .theme-toggle-btn { background: rgba(0, 0, 0, 0.04); color: var(--yellow); }
body.light-theme .theme-toggle-btn:hover { background: rgba(245, 158, 11, 0.1); border-color: var(--yellow); }
body.light-theme .theme-toggle-btn .sun-icon { display: none; }
body.light-theme .theme-toggle-btn .moon-icon { display: block; }

.date-filter-panel {
    display: flex; align-items: center; gap: 10px; flex-wrap: wrap; padding: 12px 16px;
    background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.07);
    border-radius: 10px; margin-bottom: 14px; animation: fadeSlideIn .2s ease;
}
@keyframes fadeSlideIn { from { opacity:0; transform:translateY(-6px); } to { opacity:1; transform:translateY(0); } }
.date-filter-panel label { font-size: 12px; font-weight: 600; color: var(--gray); text-transform: uppercase; letter-spacing: .5px; margin-bottom: 0; }
.date-filter-panel input[type="date"] { padding: 7px 11px; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; color: var(--white); font-family: 'Be Vietnam Pro', sans-serif; font-size: 13px; outline: none; transition: border-color .2s; cursor: pointer; }
.date-filter-panel input[type="date"]:focus { border-color: var(--blue2); }
.date-filter-panel input[type="date"]::-webkit-calendar-picker-indicator { filter: invert(0.7); cursor: pointer; }
.date-filter-panel .btn-filter { padding: 7px 18px; background: linear-gradient(135deg, var(--blue), var(--blue2)); border: none; border-radius: 8px; color: #fff; font-family: 'Be Vietnam Pro', sans-serif; font-size: 13px; font-weight: 600; cursor: pointer; transition: transform .15s, box-shadow .15s; white-space: nowrap; }
.date-filter-panel .btn-filter:hover { transform: translateY(-1px); box-shadow: 0 4px 14px rgba(26,86,219,.4); }
.date-filter-panel .filter-hint { font-size: 11px; color: var(--gray); margin-left: 4px; }
.alert-date-error { display: flex; align-items: center; gap: 8px; padding: 10px 16px; background: rgba(239,68,68,0.1); border: 1px solid rgba(239,68,68,0.3); border-radius: 9px; color: #fca5a5; font-size: 13px; font-weight: 500; margin-bottom: 12px; animation: fadeSlideIn .2s ease; }
.range-info { font-size: 12px; color: var(--gray); background: rgba(59,130,246,0.08); border: 1px solid rgba(59,130,246,0.15); border-radius: 7px; padding: 5px 12px; white-space: nowrap; }
@media (max-width: 640px) {
    .date-filter-panel { flex-direction: column; align-items: stretch; }
    .date-filter-panel input[type="date"] { width: 100%; }
    .date-filter-panel .btn-filter { width: 100%; text-align: center; }
}
</style>
</head>
<body>

<script>
    if (localStorage.getItem('panel-theme') === 'light') {
        document.body.classList.add('light-theme');
    }
</script>

<nav class="navbar">
    <div class="nav-brand"><span class="nav-icon">🚗</span><span><?= SITE_NAME ?></span></div>
    <div class="nav-links">
        <a href="index.php?lang=<?= $lang ?>" class="active"><?= $txt['nav_trips'] ?></a>
        <a href="trip_form.php?lang=<?= $lang ?>"><?= $txt['nav_create'] ?></a>
        <?php if (isAdmin()): ?><a href="vehicles.php?lang=<?= $lang ?>"><?= $txt['nav_vehicles'] ?></a><?php endif; ?>
        <a href="dashboard.php?lang=<?= $lang ?>" target="_blank" class="nav-dashboard"><?= $txt['nav_dashboard'] ?></a>
    </div>
    <div class="nav-user">
        <a href="?lang=<?= $lang === 'vi' ? 'jp' : 'vi' ?>&filter=<?= urlencode($filter) ?><?= $start_date ? '&start_date='.$start_date.'&end_date='.$end_date : '' ?>" class="theme-toggle-btn" title="Chuyển đổi ngôn ngữ / 言語切替" style="text-decoration:none; font-family:'Be Vietnam Pro',sans-serif; font-size:11px; font-weight:700; display:inline-flex;">
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
    <div class="stats-row">
        <div class="stat-card blue"><div class="stat-num"><?= $stats['total'] ?></div><div class="stat-label"><?= $txt['stat_today'] ?></div></div>
        <div class="stat-card yellow"><div class="stat-num"><?= $stats['scheduled'] ?></div><div class="stat-label"><?= $txt['stat_scheduled'] ?></div></div>
        <div class="stat-card green"><div class="stat-num"><?= $stats['departed'] ?></div><div class="stat-label"><?= $txt['stat_departed'] ?></div></div>
        <div class="stat-card gray"><div class="stat-num"><?= $stats['returned'] ?></div><div class="stat-label"><?= $txt['stat_returned'] ?></div></div>
        <div class="stat-card teal"><div class="stat-num"><?= $availableVehicles ?>/<?= $totalVehicles ?></div><div class="stat-label"><?= $txt['stat_ready'] ?></div></div>
        <div class="stat-card purple"><div class="stat-num"><?= $availableDrivers ?>/<?= $totalDrivers ?></div><div class="stat-label"><?= $txt['stat_drivers'] ?></div></div>
    </div>

    <div class="table-header">
        <h2 class="table-title"><?= $txt['table_title'] ?></h2>
        <div class="table-actions">
            <div class="filter-tabs">
                <?php foreach (['today' => $txt['tab_today'], 'upcoming' => $txt['tab_upcoming'], 'active' => $txt['tab_active'], 'all' => $txt['tab_all']] as $k => $v): ?>
                <a href="?filter=<?= $k ?>&lang=<?= $lang ?>" class="filter-tab <?= ($filter === $k ? 'active' : '') ?>"><?= $v ?></a>
                <?php endforeach; ?>
            </div>

            <?php if ($filter === 'all' && $dateError): ?>
            <button class="btn-primary" disabled style="opacity:.45;cursor:not-allowed"><?= $txt['export_excel'] ?></button>
            <?php else: ?>
            <a href="<?= htmlspecialchars($exportUrl) ?>" class="btn-primary"><?= $txt['export_excel'] ?></a>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($filter === 'all'): ?>
    <form method="GET" id="date-filter-form" novalidate>
        <input type="hidden" name="filter" value="all">
        <input type="hidden" name="lang" value="<?= $lang ?>">
        <div class="date-filter-panel">
            <label for="start_date"><?= $txt['lbl_from'] ?></label>
            <input type="date" id="start_date" name="start_date" value="<?= htmlspecialchars($start_date) ?>" min="<?= date('Y-m-d', strtotime('-6 months')) ?>" max="<?= date('Y-m-d') ?>" required>

            <label for="end_date"><?= $txt['lbl_to'] ?></label>
            <input type="date" id="end_date" name="end_date" value="<?= htmlspecialchars($end_date) ?>" min="<?= date('Y-m-d', strtotime('-6 months')) ?>" max="<?= date('Y-m-d') ?>" required>

            <button type="submit" class="btn-filter"><?= $txt['btn_filter'] ?></button>

            <?php if (!$dateError && $start_date && $end_date): ?>
            <span class="range-info">📅 <?= date('d/m/Y', strtotime($start_date)) ?> → <?= date('d/m/Y', strtotime($end_date)) ?> &nbsp;·&nbsp; <?= count($trips) ?> <?= $txt['text_trips'] ?></span>
            <?php endif; ?>
            <span class="filter-hint"><?= $txt['hint_max_6m'] ?></span>
        </div>
    </form>

    <?php if ($dateError): ?><div class="alert-date-error">⛔ <?= htmlspecialchars($dateError) ?></div><?php endif; ?>
    <?php endif; ?>

    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th><?= $txt['th_id'] ?></th>
                    <th><?= $txt['th_veh_driver'] ?></th>
                    <th><?= $txt['th_requester'] ?></th>
                    <th><?= $txt['th_destination'] ?></th>
                    <th><?= $txt['th_purpose'] ?></th>
                    <th><?= $txt['th_depart'] ?></th>
                    <th><?= $txt['th_expected'] ?></th>
                    <th><?= $txt['th_actual'] ?></th>
                    <th><?= $txt['th_status'] ?></th>
                    <th><?= $txt['th_actions'] ?></th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($trips)): ?>
            <tr><td colspan="10" class="no-data"><?= $dateError ? $txt['err_fix_date'] : $txt['no_data'] ?></td></tr>
            <?php else: ?>
            <?php foreach ($trips as $t): 
                $sl = tripStatusLabel($t['status']); 
                $statusText = $status_text_map[$t['status']] ?? $sl['label'];
            ?>
            <tr class="trip-row <?= $t['status'] ?>">
                <td class="td-id"><?= $t['id'] ?></td>
                <td>
                    <div class="vehicle-info">
                        <span class="plate"><?= sanitize($t['plate_number']) ?></span>
                        <span class="vehicle-name"><?= sanitize($t['vehicle_name']) ?></span>
                        <span class="driver">👤 <?= sanitize($t['driver_name']) ?></span>
                    </div>
                </td>
                <td>
                    <div><?= sanitize($t['requester_name']) ?></div>
                    <?php if ($t['requester_dept']): ?><small class="dept"><?= sanitize($t['requester_dept']) ?></small><?php endif; ?>
                </td>
                <td class="destination"><?= sanitize($t['destination']) ?></td>
                <td class="purpose-cell"><?= sanitize(mb_strimwidth($t['purpose'], 0, 50, '...')) ?></td>
                <td class="time-cell"><?= formatDateTime($t['departure_time']) ?></td>
                <td class="time-cell"><?= formatDateTime($t['expected_return']) ?></td>
                <td class="time-cell <?= $t['actual_return'] ? 'has-value' : '' ?>"><?= $t['actual_return'] ? formatDateTime($t['actual_return']) : '—' ?></td>
                <td><span class="badge <?= $sl['class'] ?>"><?= $statusText ?></span></td>
                <td class="actions-cell">
                    <a href="trip_form.php?id=<?= $t['id'] ?>&lang=<?= $lang ?>" class="btn-sm edit"><?= $txt['btn_edit'] ?></a>
                    <?php if ($t['status'] === 'scheduled'): ?>
                    <form method="POST" action="index.php?lang=<?= $lang ?>&filter=<?= urlencode($filter) ?>" style="display:inline">
                        <input type="hidden" name="action" value="update_status"><input type="hidden" name="trip_id" value="<?= $t['id'] ?>"><input type="hidden" name="status" value="departed">
                        <button class="btn-sm depart"><?= $txt['btn_depart'] ?></button>
                    </form>
                    <?php elseif ($t['status'] === 'departed'): ?>
                    <form method="POST" action="index.php?lang=<?= $lang ?>&filter=<?= urlencode($filter) ?>" style="display:inline">
                        <input type="hidden" name="action" value="update_status"><input type="hidden" name="trip_id" value="<?= $t['id'] ?>"><input type="hidden" name="status" value="returned">
                        <button class="btn-sm return"><?= $txt['btn_return'] ?></button>
                    </form>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
// Frontend date validation logic
(function() {
    const form = document.getElementById('date-filter-form'); if (!form) return;
    const inStart = document.getElementById('start_date'); const inEnd = document.getElementById('end_date');
    const SIX_MONTHS_MS = 182 * 24 * 60 * 60 * 1000; const today = new Date(); today.setHours(23,59,59,999);
    const minDate = new Date(Date.now() - SIX_MONTHS_MS); const minStr = minDate.toISOString().slice(0,10);
    inStart.min = minStr; inEnd.min = minStr;
    function showInlineError(input, msg) { input.setCustomValidity(msg); input.reportValidity(); }
    form.addEventListener('submit', function(e) {
        const s = new Date(inStart.value); const en = new Date(inEnd.value);
        inStart.setCustomValidity(''); inEnd.setCustomValidity('');
        if (isNaN(s) || isNaN(en)) { showInlineError(inStart, '<?= $txt['err_invalid_date'] ?>'); e.preventDefault(); return; }
        if (en < s) { showInlineError(inEnd, '<?= $txt['err_date_order'] ?>'); e.preventDefault(); return; }
        if (en - s > SIX_MONTHS_MS) { showInlineError(inEnd, '<?= $txt['err_max_allowed'] ?>'); e.preventDefault(); return; }
        if (s < minDate) { showInlineError(inStart, '<?= $txt['hint_max_6m'] ?>'); e.preventDefault(); return; }
    });
    inStart.addEventListener('change', function() { if (inEnd.value && inEnd.value < inStart.value) { inEnd.value = inStart.value; } inEnd.min = inStart.value; });
})();

// Giao diện sáng tối
document.getElementById('theme-toggle').addEventListener('click', function() {
    document.body.classList.toggle('light-theme');
    if (document.body.classList.contains('light-theme')) { localStorage.setItem('panel-theme', 'light'); }
    else { localStorage.setItem('panel-theme', 'dark'); }
});

// Auto-refresh mỗi 30s (Tránh tab All)
<?php if ($filter !== 'all'): ?>
setTimeout(() => { if (!window.location.search.includes('start_date')) location.reload(); }, 30000);
<?php endif; ?>
</script>
</body>
</html>