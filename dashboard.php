<?php
require_once 'includes/config.php';

// ── KHỞI TẠO BỘ LỌC NGÔN NGỮ ĐỒNG BỘ THEO URL (MẶC ĐỊNH LÀ VI) ──
$lang = $_GET['lang'] ?? 'vi';
if (!in_array($lang, ['vi', 'jp'])) {
    $lang = 'vi';
}

// ── BỘ TỪ ĐIỂN SONG NGỮ VIỆT - NHẬT CHUẨN CÔNG NGHIỆP ──
$lang_pack = [
    'vi' => [
        'brand' => 'PHÒNG BẢO VỆ',
        'center_title' => 'THEO DÕI XE RA VÀO',
        'stat_today' => 'Chuyến hôm nay',
        'stat_active' => 'Đang ra ngoài',
        'stat_upcoming' => 'Sắp xuất phát',
        'stat_returned' => 'Đã về hôm nay',
        'stat_ready' => 'Xe sẵn sàng',
        'stat_day' => 'Hôm nay',
        'panel_active' => '🚨 Xe Đang Ra Ngoài',
        'panel_upcoming' => ' Sắp Xuất Phát',
        'panel_returned' => ' Đã Về Hôm Nay',
        'panel_status' => ' Tình Trạng Xe',
        'no_active' => '✅ Không có xe nào đang ra ngoài',
        'no_data' => 'Không có chuyến nào',
        'no_returned' => 'Chưa có xe nào về',
        'unit_car' => 'xe',
        'out_label' => 'đã ra ngoài',
        'timeline_title' => ' THEO DÕI HÀNH TRÌNH',
        'overdue_warning' => '⚠️ QUÁ GIỜ VỀ',
        'meta_members' => 'Thành viên',
        'meta_driver' => 'Tài xế',
        'meta_depart' => 'Xuất lúc',
        'meta_expected' => 'Dự kiến về',
        'btn_list' => '📋 Danh Sách',
        'v_available' => 'Sẵn sàng',
        'v_in_use' => 'Đang dùng',
        'v_maintenance' => 'Bảo trì',
        'update_lbl' => 'Cập nhật:',
        'login' => 'Đăng nhập',
        
        // Modal & Popup Translations
        'modal_title' => '📋 CHI TIẾT CHUYẾN XE',
        'modal_leader' => 'Người yêu cầu / Trưởng đoàn',
        'modal_companions' => 'Thành viên đi cùng',
        'modal_origin' => 'Điểm xuất phát',
        'modal_destination' => 'Điểm đến',
        'modal_purpose' => 'Mục đích chuyến đi',
        'modal_depart_time' => 'Thời gian đi',
        'modal_expected_return' => 'Dự kiến về',
        'modal_actual_return' => 'Thực tế về lúc',
        'modal_gate_out_note' => 'Ghi chú xuất cổng',
        'modal_gate_in_note' => 'Ghi chú nhập cổng',
        'modal_empty' => 'Không có người đi cùng chuyến này',
        'modal_veh_title' => '🚙 CHI TIẾT PHƯƠNG TIỆN',
        'modal_veh_type' => 'Dòng xe / Loại xe',
        'modal_veh_notes' => 'Ghi chú kỹ thuật / Tình trạng',
        
        'status_scheduled' => 'Đã lên lịch',
        'status_returned' => 'Đã về',
        'status_departed' => 'Đang ra ngoài'
    ],
    'jp' => [
        'brand' => '警備室',
        'center_title' => '車両出入管理',
        'stat_today' => '本日の運行数',
        'stat_active' => '外出中',
        'stat_upcoming' => '出発予定',
        'stat_returned' => '本日帰着',
        'stat_ready' => '稼働可能車',
        'stat_day' => '本日',
        'panel_active' => '🚨 外出中の車両',
        'panel_upcoming' => ' 出発予定車両',
        'panel_returned' => ' 本日帰着車両',
        'panel_status' => ' 車両ステータス',
        'no_active' => '✅ 外出中の車両はありません',
        'no_data' => '運行予定なし',
        'no_returned' => '帰着車両なし',
        'unit_car' => '台',
        'out_label' => '外出経過',
        'timeline_title' => ' 運行ルート追跡',
        'overdue_warning' => '⚠️ 帰着超過',
        'meta_members' => '乗車メンバー',
        'meta_driver' => '運転手',
        'meta_depart' => '出発時刻',
        'meta_expected' => '帰着予定',
        'btn_list' => '📋 リスト',
        'v_available' => '空車',
        'v_in_use' => '運行中',
        'v_maintenance' => '整備中',
        'update_lbl' => '更新時間:',
        'login' => 'ログイン',
        
        // Modal & Popup Translations
        'modal_title' => '📋 運行詳細情報',
        'modal_leader' => '申請者 / 責任者',
        'modal_companions' => '同乗者',
        'modal_origin' => '出発地',
        'modal_destination' => '目的地',
        'modal_purpose' => '利用目的',
        'modal_depart_time' => '出発時間',
        'modal_expected_return' => '帰着予定',
        'modal_actual_return' => '実際の帰着',
        'modal_gate_out_note' => '出門備考',
        'modal_gate_in_note' => '入門備考',
        'modal_empty' => '同乗者はいません',
     
        'modal_veh_title' => '🚙 車両詳細ステータス',
        'modal_veh_type' => '車種 / 車両タイプ',
        'modal_veh_notes' => '整備メモ / 車両状態',
        
        'status_scheduled' => '配車済',
        'status_returned' => '帰着済',
        'status_departed' => '外出中'
    ]
];

$txt = $lang_pack[$lang];

// ── TRUY VẤN DỮ LIỆU CƠ SỞ DỮ LIỆU ──
$userToday = date('Y-m-d');
$stats = $pdo->query("
    SELECT
        COUNT(*) as total,
        SUM(status='scheduled') as scheduled,
        SUM(status='departed') as departed,
        SUM(status='returned') as returned
    FROM trips WHERE DATE(departure_time) = '$userToday'
")->fetch();

$active = $pdo->query("
    SELECT t.*, v.plate_number, v.vehicle_name, v.vehicle_type, v.icon, v.icon_color,
           d.full_name as driver_name, d.phone as driver_phone
    FROM trips t
    LEFT JOIN vehicles v ON t.vehicle_id = v.id
    LEFT JOIN drivers d ON t.driver_id = d.id
    WHERE t.status = 'departed'
    ORDER BY t.departure_time ASC
")->fetchAll();

$upcoming = $pdo->query("
    SELECT t.*, v.plate_number, v.vehicle_name, d.full_name as driver_name
    FROM trips t
    LEFT JOIN vehicles v ON t.vehicle_id = v.id
    LEFT JOIN drivers d ON t.driver_id = d.id
    WHERE t.status = 'scheduled' AND DATE(t.departure_time) = '$userToday'
    ORDER BY t.departure_time ASC LIMIT 8
")->fetchAll();

$returned = $pdo->query("
    SELECT t.*, v.plate_number, v.vehicle_name, d.full_name as driver_name
    FROM trips t
    LEFT JOIN vehicles v ON t.vehicle_id = v.id
    LEFT JOIN drivers d ON t.driver_id = d.id
    WHERE t.status = 'returned' AND DATE(t.departure_time) = '$userToday'
    ORDER BY t.actual_return DESC LIMIT 5
")->fetchAll();

$vehicles = $pdo->query("SELECT * FROM vehicles ORDER BY plate_number")->fetchAll();

function minutesOut($departure) {
    return max(0, round((time() - strtotime($departure)) / 60));
}
function formatMinsOut($mins) {
    global $lang;
    if ($mins < 60) return $mins . ($lang === 'jp' ? '分' : ' phút');
    $h = floor($mins/60); $m = $mins % 60;
    return $h . 'g' . ($m > 0 ? $m . 'p' : '');
}
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= $txt['brand'] ?> — <?= SITE_NAME ?></title>
<link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@500;600;700&family=Be+Vietnam+Pro:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
*{margin:0;padding:0;box-sizing:border-box}
:root{
    /* ── HỆ MÀU TỐI TINH TẾ CHUẨN APPLE ── */
    --bg: #050a12;          
    --bg2: #0e1b2e;         
    --bg3: #162844;         
    --bg-card-hover: #223a5e; /* Hover màu tối nâng tông xanh sáng nổi bật hẳn */
    --cyan:#06b6d4;--green:#10b981;--yellow:#f59e0b;
    --red:#ef4444;--blue:#3b82f6;--purple:#8b5cf6;
    --white:#e2eaf6;--dim:#627c9f;
    --border:rgba(6,182,212,0.15);
    
    --radius-lg: 24px;
    --radius-md: 16px;
    --radius-sm: 8px;
    
    --apple-blue: #0A84FF;
    --apple-green: #32D74B;
    --apple-orange: #FF9F0A;
    --apple-red: #FF453A;
}
html,body{min-height:100vh;font-family:'Be Vietnam Pro',sans-serif;background:var(--bg);color:var(--white);overflow-x:hidden}

/* ── CONFIG LIGHT THEME TRẮNG SỮA ── */
body.light-theme {
    --bg: #f5f5f0;       
    --bg2: #ffffff;      
    --bg3: #eaf0f6;      
    --bg-card-hover: #d9e5f3; /* Hover màu sáng của Apple dịu mát tương phản cao */
    --white: #0f172a;    
    --dim: #64748b;      
    --border: rgba(15, 23, 42, 0.08); 
    --cyan: #0071e3;     
    --blue: #2563eb;
}
body.light-theme::before { background: radial-gradient(circle, rgba(37,99,235,0.05) 0%, transparent 70%); }
body.light-theme::after { background: radial-gradient(circle, rgba(245,158,11,0.03) 0%, transparent 70%); }
body.light-theme .rrow { opacity: 0.85; }
body.light-theme .car-shadow { background: radial-gradient(ellipse, rgba(15,23,42,0.15) 0%, transparent 70%); }

/* HOVER CHẾ ĐỘ SÁNG CHUẨN APPLE */
body.light-theme .trip-card:hover {
    background: #d9e5f3 !important;
}

/* ── PHONG CÁCH NÚT CHUYỂN ĐỔI THEME GÓC PHẢI ── */
.theme-toggle-btn {
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid var(--border);
    color: var(--cyan);
    padding: 8px;
    border-radius: 10px;
    cursor: pointer;
    display: inline-flex;
    align-items: center; justify-content: center;
    transition: all 0.2s ease;
    width: 34px; height: 34px;
    flex-shrink: 0;
}
.theme-toggle-btn:hover {
    background: rgba(6, 182, 212, 0.15);
    border-color: var(--cyan);
    box-shadow: 0 0 8px var(--cyan);
}
.theme-toggle-btn .moon-icon { display: none; }
.theme-toggle-btn .sun-icon { display: block; }

body.light-theme .theme-toggle-btn {
    background: rgba(0, 0, 0, 0.04);
    color: var(--yellow);
}
body.light-theme .theme-toggle-btn:hover {
    background: rgba(245, 158, 11, 0.12);
    border-color: var(--yellow);
    box-shadow: 0 0 8px var(--yellow);
}
body.light-theme .theme-toggle-btn .sun-icon { display: none; }
body.light-theme .theme-toggle-btn .moon-icon { display: block; }

/* ── HEADER ── */
.hdr{background:var(--bg2);border-bottom:1px solid var(--border);padding:12px 28px;display:flex;align-items:center;justify-content:space-between;position:sticky;top:0;z-index:100}
.hdr-brand{font-family:'Rajdhani',sans-serif;font-size:20px;font-weight:700;color:var(--cyan);letter-spacing:2px;display:flex;align-items:center;gap:10px}
.hdr-center{font-family:'Rajdhani',sans-serif;font-size:24px;font-weight:700;letter-spacing:3px;color:var(--white)}
#clock{font-variant-numeric: tabular-nums; font-family:'Rajdhani',sans-serif;font-size:34px;font-weight:700;color:var(--cyan);letter-spacing:4px;text-align:right}
#date-lbl{font-size:11px;color:var(--dim);text-align:right;letter-spacing:1px;margin-top:2px}
.hdr-right{display:flex;align-items:center;gap:10px}
.btn-hdr-login{
    display:inline-flex;align-items:center;gap:7px;
    padding:8px 18px;
    background:linear-gradient(135deg,rgba(6,182,212,0.18),rgba(59,130,246,0.18));
    border:1px solid rgba(6,182,212,0.4);
    border-radius:10px;
    color:var(--cyan);
    font-family:'Be Vietnam Pro',sans-serif;
    font-size:13px;font-weight:600;
    text-decoration:none;
    letter-spacing:0.3px;
    cursor:pointer;
    transition:background 0.2s,border-color 0.2s,transform 0.15s,box-shadow 0.2s;
    white-space:nowrap;
}
.btn-hdr-login:hover{
    background:linear-gradient(135deg,rgba(6,182,212,0.30),rgba(59,130,246,0.30));
    border-color:var(--cyan);
    transform:translateY(-1px);
    box-shadow:0 4px 16px rgba(6,182,212,0.25);
}
.btn-hdr-login svg{flex-shrink:0}

/* ── LAYOUT ── */
.wrap{padding:18px 22px;display:flex;flex-direction:column;gap:18px}

/* ── STATS ROW ── */
.stats{display:grid; grid-template-columns: repeat(6, 1fr); gap:10px;}
.scard{background:var(--bg2);border:1px solid var(--border);border-radius:12px;padding:16px;text-align:center;position:relative;overflow:hidden}
.scard::before{content:'';position:absolute;top:0;left:0;right:0;height:2px}
.scard.sc-cyan::before{background:var(--cyan)}.scard.sc-yellow::before{background:var(--yellow)}.scard.sc-blue::before{background:var(--blue)}.scard.sc-green::before{background:var(--green)}.scard.sc-red::before{background:var(--red)}.scard.sc-dim::before{background:var(--dim)}
.sn { font-variant-numeric: tabular-nums; font-family: 'Rajdhani', sans-serif; font-size: 38px; font-weight: 700; line-height: 1; }
.sc-cyan .sn { color: var(--cyan); } .sc-yellow .sn { color: var(--yellow); } .sc-blue .sn { color: var(--blue); } .sc-green .sn { color: #34d399; } .sc-red .sn { color: #f87171; } .sc-dim .sn { color: var(--dim); }
.sl { font-size: 10px; color: var(--dim); text-transform: uppercase; letter-spacing: .7px; margin-top: 5px; }

/* ── MAIN GRID ── */
.grid { display: grid; grid-template-columns: 1fr 340px; gap: 18px; }

/* ── PANEL ── */
.panel { background: var(--bg2); border: 1px solid var(--border); border-radius: 14px; overflow: hidden; }
.phead { padding: 13px 18px; background: var(--bg3); border-bottom: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between; }
.ptitle { font-family: 'Rajdhani', sans-serif; font-size: 15px; font-weight: 700; letter-spacing: 1.5px; color: var(--cyan); text-transform: uppercase; }
.pbadge { font-size: 11px; font-weight: 700; padding: 3px 10px; border-radius: 99px; background: var(--yellow); color: #000; }
.pbadge.g { background: var(--green); color: #fff; } .pbadge.b { background: var(--blue); color: #fff; } .pbadge.r { background: var(--red); color: #fff; }

/* ════════════════════════════════════════════
   TRIP CARD ULTRA-COMPACT (BỐ CỤC SIÊU TINH GỌN)
════════════════════════════════════════════ */
.active-list { padding: 10px 12px; display: flex; flex-direction: column; gap: 10px; }

.trip-card {
    background-color: var(--bg3);
    border: 1px solid var(--border);
    border-radius: 12px;
    overflow: hidden;
    position: relative;
    width: 100%;
    cursor: pointer; /* Cho người dùng biết toàn bộ card có thể click */
    /* Đồng bộ chuyển đổi màu nền qua thuộc tính background-color để mượt mà */
    transition: background-color 0.25s cubic-bezier(0.25, 1, 0.5, 1), border-color 0.25s ease, transform 0.2s ease, box-shadow 0.25s ease;
}

/* SỬA LỖI HOVER CHỐNG CHÌM CHỮ CHO CẢ HAI CHẾ ĐỘ SÁNG / TỐI ĐỒNG BỘ */
.trip-card:hover {
    background-color: var(--bg-card-hover) !important; 
    border-color: var(--cyan);
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(6, 182, 212, 0.18);
}

/* ── HIỆU ỨNG VIỀN ĐỎ SÁNG NHẤP NHÁY NHẸ CHO XE QUÁ GIỜ (REAL-TIME WARNING) ── */
@keyframes dark-overdue-pulse {
    0% {
        border-color: rgba(239, 68, 68, 0.35);
        box-shadow: 0 0 4px rgba(239, 68, 68, 0.1), 0 6px 18px rgba(0, 0, 0, 0.15);
    }
    50% {
        border-color: rgba(239, 68, 68, 0.85);
        box-shadow: 0 0 12px rgba(239, 68, 68, 0.4), 0 6px 18px rgba(0, 0, 0, 0.15);
    }
    100% {
        border-color: rgba(239, 68, 68, 0.35);
        box-shadow: 0 0 4px rgba(239, 68, 68, 0.1), 0 6px 18px rgba(0, 0, 0, 0.15);
    }
}

@keyframes light-overdue-pulse {
    0% {
        border-color: rgba(255, 59, 48, 0.3);
        box-shadow: 0 0 4px rgba(255, 59, 48, 0.08), 0 4px 12px rgba(0, 0, 0, 0.03);
    }
    50% {
        border-color: rgba(255, 59, 48, 0.75);
        box-shadow: 0 0 10px rgba(255, 59, 48, 0.25), 0 4px 12px rgba(0, 0, 0, 0.03);
    }
    100% {
        border-color: rgba(255, 59, 48, 0.3);
        box-shadow: 0 0 4px rgba(255, 59, 48, 0.08), 0 4px 12px rgba(0, 0, 0, 0.03);
    }
}

.trip-card.overdue-card {
    animation: dark-overdue-pulse 2s infinite ease-in-out;
}

body.light-theme .trip-card.overdue-card {
    animation: light-overdue-pulse 2s infinite ease-in-out;
}

/* KHUNG CHỨA SIÊU TINH GỌN PHẲNG */
.tc-compact-layout {
    padding: 10px 14px;
    display: flex;
    flex-direction: column;
    gap: 8px;
}

/* HÀNG ĐẦU TIÊN: CHỨA TẤT CẢ THÀNH PHẦN HOẠT ĐỘNG CHÍNH CHUNG MỘT DÒNG */
.tc-header-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    width: 100%;
}

/* ICON XE LỚN (+50% kích thước ban đầu) */
.compact-car-wrap {
    flex-shrink: 0;
    width: 70px;
    height: 40px;
    position: relative;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
}
.compact-car-wrap .car-svg {
    width: 70px;
    height: 40px;
    z-index: 2;
}
.compact-car-wrap .car-shadow {
    position: absolute;
    bottom: -3px;
    width: 70px;
    height: 6px;
    background: radial-gradient(ellipse, rgba(245, 158, 11, 0.3) 0%, transparent 70%);
    border-radius: 50%;
    filter: blur(2px);
    z-index: 1;
}

/* THÔNG TIN BIỂN SỐ VÀ TÊN XE */
.tc-plate-info {
    display: flex;
    flex-direction: column;
    gap: 2px;
    min-width: 110px;
    flex-shrink: 0;
}
.tc-plate {
    display: inline-block;
    font-family: 'Rajdhani', sans-serif;
    font-size: 15px;
    font-weight: 700;
    color: #000;
    background: var(--yellow);
    padding: 1px 8px;
    border-radius: 4px;
    letter-spacing: 1px;
    text-align: center;
    box-shadow: 0 0 8px rgba(245, 158, 11, 0.2);
}
.tc-vname {
    font-size: 11px;
    color: var(--dim);
    font-weight: 600;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 110px;
}

/* THANH TRẠNG THÁI LIÊN TỤC NẰM GIỮA BIỂN SỐ VÀ THỜI GIAN */
.timeline-compact {
    flex: 1;
    display: flex;
    flex-direction: column;
    justify-content: center;
    gap: 3px;
    min-width: 120px;
}
.timeline-wrapper { 
    position: relative; 
    height: 14px; 
    display: flex; 
    align-items: center; 
}
.timeline-track { 
    position: absolute; 
    left: 0; 
    right: 0; 
    height: 4px; 
    background: rgba(255, 255, 255, 0.08); 
    border-radius: 99px; 
}
body.light-theme .timeline-track {
    background: rgba(0, 0, 0, 0.06);
}

.timeline-fill {
    height: 100%;
    width: 0%;
    background-color: var(--stage-color, var(--cyan));
    border-radius: 99px;
    transition: width 0.6s cubic-bezier(0.4, 0, 0.2, 1), background-color 0.8s ease;
    box-shadow: 0 0 6px var(--stage-color);
}

.car-on-track { 
    position: absolute; 
    top: 50%; 
    left: 0; 
    transform: translate(-50%, -50%); 
    z-index: 10; 
    color: var(--stage-color, var(--cyan));
    transition: left 0.6s cubic-bezier(0.4, 0, 0.2, 1), color 0.8s ease; 
    display: flex; 
    align-items: center; 
    justify-content: center; 
}
.car-on-track svg { 
    width: 18px;
    height: 10px;
    filter: drop-shadow(0 0 3px var(--stage-color, var(--cyan))); 
}

.timeline-times { 
    display: flex; 
    justify-content: space-between;
    font-size: 10px; 
    color: var(--dim); 
    font-weight: 500;
}
.timeline-times span {
    font-family: 'Rajdhani', sans-serif;
    font-weight: 600;
}

/* THỜI GIAN ĐÃ RA NGOÀI COMPACT */
.compact-timer {
    flex-shrink: 0;
    text-align: center;
    background: rgba(255, 255, 255, 0.02);
    border: 1px solid var(--border);
    border-radius: 6px;
    padding: 3px 8px;
    min-width: 75px;
}
.compact-timer .tc-timer-num {
    font-variant-numeric: tabular-nums;
    font-family: 'Rajdhani', sans-serif;
    font-size: 15px;
    font-weight: 700;
    color: var(--yellow);
    line-height: 1.1;
}
.compact-timer .tc-timer-lbl {
    font-size: 8px;
    color: var(--dim);
    text-transform: uppercase;
    letter-spacing: .5px;
    margin-top: 1px;
}

/* HÀNG THỨ HAI: THÔNG TIN CHI TIẾT (ĐIỂM ĐẾN, MỤC ĐÍCH, CẢNH BÁO TRỄ) */
.tc-details-row {
    display: flex;
    align-items: center;
    gap: 16px;
    font-size: 12px;
    border-top: 1px solid rgba(255, 255, 255, 0.03);
    padding-top: 6px;
    color: var(--white);
    flex-wrap: wrap;
}
body.light-theme .tc-details-row {
    border-top-color: rgba(0, 0, 0, 0.04);
}
.tc-dest-badge {
    font-weight: 600;
    color: var(--white);
    white-space: nowrap;
}
.tc-purpose-badge {
    color: var(--dim);
    flex: 1;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    min-width: 150px;
}

/* NHÃN CẢNH BÁO QUÁ GIỜ THU GỌN INLINE - Chỉ hiển thị khi card có class .overdue-card */
.overdue-inline-badge {
    display: none; /* Ẩn mặc định */
    background: rgba(239, 68, 68, 0.15);
    border: 1px solid rgba(239, 68, 68, 0.25);
    color: #fca5a5;
    padding: 1px 6px;
    border-radius: 4px;
    font-size: 10px;
    font-weight: 700;
    white-space: nowrap;
    animation: pulse-text-compact 1.5s ease-in-out infinite;
}
.trip-card.overdue-card .overdue-inline-badge {
    display: inline-block; /* Chỉ hiển thị thực tế khi quá giờ */
}
body.light-theme .overdue-inline-badge {
    background: rgba(239, 68, 68, 0.05); 
    border-color: rgba(239, 68, 68, 0.15);
    color: #b91c1c; 
}
@keyframes pulse-text-compact { 0%, 100% { opacity: 1; } 50% { opacity: .7; } }

/* CẬP NHẬT: Chia 3 cột đối xứng hoàn hảo sau khi gỡ bỏ hoàn toàn cột THÀNH VIÊN */
.tc-meta { padding: 5px 14px; background: rgba(0, 0, 0, 0.2); border-top: 1px solid rgba(255, 255, 255, 0.04); display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 0; }
.tc-meta-item { text-align: center; padding: 1px 6px; }
.tc-meta-item + .tc-meta-item { border-left: 1px solid rgba(255, 255, 255, 0.06); }
.tc-ml { font-size: 8px; color: var(--dim); text-transform: uppercase; letter-spacing: .5px; margin-bottom: 1px; }
.tc-mv { font-size: 11px; font-weight: 600; color: var(--white); }
.tc-mv.cyan { color: var(--cyan); }

/* POPUP */
.db-modal-backdrop { display: none; position: fixed; inset: 0; background: rgba(4, 9, 17, 0.85); backdrop-filter: blur(4px); z-index: 1000; align-items: center; justify-content: center; }
.db-modal-backdrop.open { display: flex; }
.db-modal { background: var(--bg2); border: 1px solid var(--border); border-radius: 14px; width: 460px; max-width: 90vw; box-shadow: 0 16px 48px rgba(0, 0, 0, 0.6); animation: dbModalIn 0.25s cubic-bezier(0.34, 1.56, 0.64, 1); overflow: hidden; }
@keyframes dbModalIn { from { opacity: 0; transform: scale(0.9) translateY(10px); } to { opacity: 1; transform: scale(1) translateY(0); } }
.db-modal-header { padding: 14px 18px; background: var(--bg3); border-bottom: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between; }
.db-modal-title { font-family: 'Rajdhani', sans-serif; font-size: 15px; font-weight: 700; color: var(--cyan); letter-spacing: 1px; }
.db-modal-close { background: none; border: none; color: var(--dim); font-size: 18px; cursor: pointer; transition: color 0.15s; }
.db-modal-close:hover { color: var(--white); }
.db-modal-body { padding: 20px; display: flex; flex-direction: column; gap: 14px; }
.db-member-group { background: rgba(255, 255, 255, 0.02); border: 1px solid rgba(255, 255, 255, 0.04); border-radius: 8px; padding: 12px; }
.db-member-label { font-size: 10px; color: var(--dim); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 6px; font-weight: 600; }
.db-member-value { font-size: 14px; font-weight: 600; color: var(--white); }
.db-companion-item { display: flex; align-items: center; gap: 8px; padding: 6px 0; border-bottom: 1px solid rgba(255, 255, 255, 0.03); font-size: 13px; color: var(--white); }
.db-companion-item:last-child { border-bottom: none; }
.db-companion-dot { width: 6px; height: 6px; background: var(--cyan); border-radius: 50%; box-shadow: 0 0 4px var(--cyan); flex-shrink: 0; }

/* ── SIDE PANELS (MÃ HOÁ PHẢN HỒI HOVER VÀ TRỎ CHUỘT) ── */
.side { display: flex; flex-direction: column; gap: 14px; }

.veh-item, .urow, .rrow {
    cursor: pointer;
    transition: background-color 0.2s cubic-bezier(0.25, 1, 0.5, 1), transform 0.12s ease;
}
.veh-item:hover, .urow:hover, .rrow:hover {
    background-color: rgba(255, 255, 255, 0.04);
    transform: scale(1.01);
}
body.light-theme .veh-item:hover, 
body.light-theme .urow:hover, 
body.light-theme .rrow:hover {
    background-color: var(--bg-card-hover) !important;
}

.veh-item { display: flex; align-items: center; gap: 10px; padding: 10px 18px; border-bottom: 1px solid rgba(255, 255, 255, 0.04); }
.veh-item:last-child { border-bottom: none; }
.vdot { width: 9px; height: 9px; border-radius: 50%; flex-shrink: 0; }
.vdot.available { background: var(--green); box-shadow: 0 0 6px var(--green); }
.vdot.in_use { background: var(--yellow); animation: dotPulse 1.5s infinite; }
.vdot.maintenance { background: var(--red); }
@keyframes dotPulse { 0%, 100% { box-shadow: 0 0 0 0 rgba(245, 158, 11, .4); } 50% { box-shadow: 0 0 0 5px rgba(245, 158, 11, 0); } }
.vplate { font-family: 'Rajdhani', sans-serif; font-size: 15px; font-weight: 700; color: var(--white); min-width: 95px; }
.vname { font-size: 11px; color: var(--dim); }
.vstatus { margin-left: auto; font-size: 11px; font-weight: 600; padding: 3px 10px; border-radius: 99px; }
.vstatus.available { background: rgba(16, 185, 129, .12); color: #34d399; }
.vstatus.in_use { background: rgba(245, 158, 11, .12); color: var(--yellow); }
.vstatus.maintenance { background: rgba(239, 68, 68, .1); color: #f87171; }

.urow { display: grid; grid-template-columns: 90px 1fr auto; align-items: center; gap: 8px; padding: 9px 18px; border-bottom: 1px solid rgba(255, 255, 255, 0.04); font-size: 13px; }
.urow:last-child { border-bottom: none; }
.uplat { font-family: 'Rajdhani', sans-serif; font-size: 14px; font-weight: 700; color: var(--cyan); }
.udest { font-size: 12px; color: var(--white); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.uwho { font-size: 11px; color: var(--dim); }
.utime { font-family: 'Rajdhani', sans-serif; font-size: 17px; font-weight: 600; color: var(--green); white-space: nowrap; }

.rrow { display: grid; grid-template-columns: 90px 1fr auto; align-items: center; gap: 8px; padding: 9px 18px; border-bottom: 1px solid rgba(255, 255, 255, 0.04); opacity: .65; }
.rrow:last-child { border-bottom: none; }
.rplat { font-family: 'Rajdhani', sans-serif; font-size: 13px; font-weight: 600; color: var(--dim); }
.rdest { font-size: 11px; color: #475569; }
.rwho { font-size: 10px; color: #334155; }
.rtime { font-family: 'Rajdhani', sans-serif; font-size: 15px; color: var(--green); }

.empty { text-align: center; padding: 20px; color: var(--dim); font-size: 13px; }

/* ── REFRESH BAR ── */
.rbar { position: fixed; bottom: 0; left: 0; right: 0; height: 3px; background: rgba(6, 182, 212, .08); z-index: 200; }
.rprog { height: 100%; background: linear-gradient(90deg, var(--cyan), var(--blue)); width: 0; transition: width .3s linear; }
.rlabel { position: fixed; bottom: 6px; right: 14px; font-size: 10px; color: var(--dim); z-index: 200; }

/* ════════════════════════════════════════════
   CƠ CHẾ RESPONSIVE ĐA TẦNG CHO MỌI THIẾT BỊ
════════════════════════════════════════════ */

/* 1. MÀN HÌNH TIVI BẢO VỆ CỠ LỚN (TV 50-INCH HOẶC ĐỘ PHÂN GIẢI 1920PX TRỞ LÊN) */
@media (min-width: 1920px) {
    body { font-size: 16px; }
    .wrap { max-width: 1820px; margin: 0 auto; gap: 26px; }
    .grid { grid-template-columns: 1fr 420px; gap: 24px; }
    
    .sn { font-size: 52px; }
    .sl { font-size: 12px; }
    .scard { padding: 22px; }
    
    .hdr { height: 80px; padding: 0 40px; }
    .hdr-brand { font-size: 24px; }
    .hdr-center { font-size: 28px; }
    #clock { font-size: 42px; }
    #date-lbl { font-size: 14px; }
    
    .tc-compact-layout { padding: 16px 24px; gap: 14px; }
    .compact-car-wrap { width: 90px; height: 50px; }
    .compact-car-wrap .car-svg { width: 90px; height: 50px; }
    .compact-car-wrap .car-shadow { width: 90px; height: 8px; }
    .tc-plate { font-size: 18px; padding: 3px 12px; }
    .tc-vname { font-size: 13px; }
    .timeline-compact { gap: 6px; }
    .timeline-wrapper { height: 18px; }
    .car-on-track svg { width: 24px; height: 14px; }
    .timeline-times { font-size: 12px; }
    .compact-timer { padding: 6px 14px; min-width: 90px; }
    .compact-timer .tc-timer-num { font-size: 20px; }
    .tc-details-row { font-size: 14px; padding-top: 10px; gap: 24px; }
    
    .tc-meta { padding: 8px 20px; grid-template-columns: 1fr 1fr 1fr; }
    .tc-ml { font-size: 10px; }
    .tc-mv { font-size: 14px; }
}

/* 2. THIẾT BỊ DI ĐỘNG & MÀN HÌNH CO HẸP (MOBILE & TABLET < 1024PX) */
@media (max-width: 1024px) {
    .grid { grid-template-columns: 1fr; gap: 18px; }
    .stats { grid-template-columns: repeat(3, 1fr); gap: 8px; }
    .sn { font-size: 30px; }
    .sl { font-size: 9px; }
    .scard { padding: 12px; }
    
    .tc-header-row { flex-direction: column; align-items: stretch; gap: 12px; text-align: center; }
    .compact-car-wrap { margin: 0 auto; }
    .tc-plate-info { align-items: center; text-align: center; }
    .timeline-compact { width: 100%; margin: 6px 0; }
    .compact-timer { margin: 0 auto; width: 100%; max-width: 180px; }
    
    .tc-details-row { justify-content: center; text-align: center; gap: 12px; }
    .tc-meta { grid-template-columns: 1fr 1fr 1fr; }
}

/* 3. ĐIỆN THOẠI CẦY TAY SIÊU NHỎ (< 480PX) */
@media (max-width: 480px) {
    .hdr { padding: 12px 16px; flex-direction: column; gap: 10px; text-align: center; }
    .hdr-brand, .hdr-center, .hdr-right { justify-content: center; }
    #clock { text-align: center; font-size: 28px; }
    #date-lbl { text-align: center; }
    
    .stats { grid-template-columns: repeat(2, 1fr); }
    .sn { font-size: 26px; }
    .sl { font-size: 8px; }
    
    /* Giao diện meta chuyển dọc nhẹ nhàng trên màn hình cực nhỏ */
    .tc-meta { grid-template-columns: 1fr; row-gap: 6px; }
    .tc-meta-item + .tc-meta-item { border-left: none; border-top: 1px solid rgba(255, 255, 255, 0.04); padding-top: 6px; }
    body.light-theme .tc-meta-item + .tc-meta-item { border-top-color: rgba(0, 0, 0, 0.05); }
}
</style>
</head>
<body>
<script>
// Khôi phục giao diện tối ưu ngay từ khi bắt đầu dựng body để tránh nhấp nháy (flash)
if (localStorage.getItem('panel-theme') === 'light') {
    document.body.classList.add('light-theme');
}
</script>

<div class="hdr">
    <div class="hdr-brand">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
        <?= $txt['brand'] ?>
    </div>
    <div class="hdr-center"><?= $txt['center_title'] ?></div>
    <div class="hdr-right">
        <div style="text-align:right">
            <div id="clock">--:--:--</div>
            <div id="date-lbl"></div>
        </div>
        
        <a href="?lang=<?= $lang === 'vi' ? 'jp' : 'vi' ?>" class="theme-toggle-btn" title="Chuyển đổi ngôn ngữ / 言語切替" style="text-decoration:none; font-family:'Rajdhani',sans-serif; font-size:12px; font-weight:700; display:inline-flex;">
            <?= $lang === 'vi' ? 'JP' : 'VI' ?>
        </a>

        <button id="theme-toggle" class="theme-toggle-btn" title="Thay đổi giao diện Sáng / Tối">
            <svg class="sun-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="5"></circle><line x1="12" y1="1" x2="12" y2="3"></line><line x1="12" y1="21" x2="12" y2="23"></line><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line><line x1="1" y1="12" x2="3" y2="12"></line><line x1="21" y1="12" x2="23" y2="12"></line><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line></svg>
            <svg class="moon-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path></svg>
        </button>

        <?php if (!isset($user) || !$user): ?>
        <a href="login.php" id="btn-login" class="btn-hdr-login">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                <path d="M15 3h4a2 2 0 012 2v14a2 2 0 01-2 2h-4"/>
                <polyline points="10 17 15 12 10 7"/>
                <line x1="15" y1="12" x2="3" y2="12"/>
            </svg>
            <span class="btn-label"><?= $txt['login'] ?></span>
        </a>
        <?php endif; ?>
    </div>
</div>

<div class="wrap">

    <div class="stats">
        <div class="scard sc-cyan"><div class="sn"><?= $stats['total'] ?></div><div class="sl"><?= $txt['stat_today'] ?></div></div>
        <div class="scard sc-yellow"><div class="sn"><?= count($active) ?></div><div class="sl"><?= $txt['stat_active'] ?></div></div>
        <div class="scard sc-blue"><div class="sn"><?= $stats['scheduled'] ?></div><div class="sl"><?= $txt['stat_upcoming'] ?></div></div>
        <div class="scard sc-green"><div class="sn"><?= $stats['returned'] ?></div><div class="sl"><?= $txt['stat_returned'] ?></div></div>
        <?php $va=array_filter($vehicles,fn($v)=>$v['status']==='available'); ?>
        <div class="scard sc-<?= count($va)>0?'green':'red' ?>">
            <div class="sn"><?= count($va) ?>/<?= count($vehicles) ?></div>
            <div class="sl"><?= $txt['stat_ready'] ?></div>
        </div>
        <div class="scard sc-dim"><div class="sn"><?= date('d/m') ?></div><div class="sl"><?= $txt['stat_day'] ?></div></div>
    </div>

    <div class="grid">

        <div class="panel">
            <div class="phead">
                <div class="ptitle"><?= $txt['panel_active'] ?></div>
                <span class="pbadge <?= count($active)===0?'g':'' ?>"><?= count($active) ?> <?= $txt['unit_car'] ?></span>
            </div>

            <?php if (empty($active)): ?>
            <div class="no-active">
                <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path d="M5 17H3a2 2 0 01-2-2V5a2 2 0 012-2h11a2 2 0 012 2v3"/>
                    <rect x="9" y="11" width="14" height="10" rx="2"/>
                    <circle cx="12" cy="21" r="1"/><circle cx="20" cy="21" r="1"/>
                </svg>
                <p><?= $txt['no_active'] ?></p>
            </div>
            <?php else: ?>
            <div class="active-list">
            <?php foreach ($active as $t):
                $minsOut = minutesOut($t['departure_time']);
                $depTime = strtotime($t['departure_time']);
                $expRetTime = $t['expected_return'] ? strtotime($t['expected_return']) : time() + 3600;
                $nowTime = time();
                $isOverdue = ($nowTime > $expRetTime && $t['status'] === 'departed');
                
                $vColor = $t['icon_color'] ?? '#f59e0b';
                $vColorRgb = '245, 158, 11'; 
                $hex = str_replace('#', '', $vColor);
                if (strlen($hex) == 6) {
                    $r = hexdec(substr($hex, 0, 2));
                    $g = hexdec(substr($hex, 2, 2));
                    $b = hexdec(substr($hex, 4, 2));
                    $vColorRgb = "$r, $g, $b";
                }
            ?>
            <!-- CẬP NHẬT: Cho phép click trực tiếp vào Trip Card để hiển thị toàn bộ chi tiết chuyến đi -->
            <div class="trip-card" 
                 data-trip-id="<?= $t['id'] ?>" 
                 data-dep="<?= $t['departure_time'] ?>" 
                 data-exp="<?= $t['expected_return'] ?>" 
                 data-vcolor="<?= $vColor ?>"
                 data-plate="<?= sanitize($t['plate_number']) ?>"
                 data-vname="<?= sanitize($t['vehicle_name']) ?>"
                 data-driver="<?= sanitize($t['driver_name'] ?? '—') ?>"
                 data-requester="<?= sanitize($t['requester_name']) ?>"
                 data-dept="<?= sanitize($t['requester_dept'] ?? '—') ?>"
                 data-companions="<?= sanitize($t['companions'] ?? '') ?>"
                 data-origin="<?= sanitize($t['origin'] ?? '—') ?>"
                 data-destination="<?= sanitize($t['destination']) ?>"
                 data-purpose="<?= sanitize($t['purpose']) ?>"
                 data-depart="<?= date('H:i', strtotime($t['departure_time'])) ?>"
                 data-expected="<?= $t['expected_return'] ? date('H:i', strtotime($t['expected_return'])) : '—' ?>"
                 data-status="departed"
                 data-gate-out="<?= sanitize($t['gate_out_note'] ?? '—') ?>"
                 data-gate-in="<?= sanitize($t['gate_in_note'] ?? '—') ?>"
                 onclick="openTripDetailModal(this)">
                
                <!-- BỐ CỤC SIÊU TINH GỌN (ULTRA COMPACT LAYOUT) -->
                <div class="tc-compact-layout">
                    
                    <!-- HÀNG THỨ 1: CHỨA TOÀN BỘ CÁC THÀNH PHẦN HOẠT ĐỘNG CHÍNH -->
                    <div class="tc-header-row">
                        
                        <!-- Icon xe được tăng kích thước 50% -->
                        <div class="car-icon-wrap compact-car-wrap">
                            <div class="car-shadow" style="background:radial-gradient(ellipse, rgba(<?= $vColorRgb ?>,0.4) 0%, transparent 70%);"></div>
                            <div class="motion-lines" style="height: 20px;">
                                <div class="motion-line" style="background:rgba(<?= $vColorRgb ?>,0.5);"></div>
                                <div class="motion-line" style="background:rgba(<?= $vColorRgb ?>,0.5);"></div>
                                <div class="motion-line" style="background:rgba(<?= $vColorRgb ?>,0.5);"></div>
                            </div>
                            <div class="car-svg">
                                <?= getVehicleSVG($t['icon'] ?? 'sedan', $vColor, 'normal', 70, 40) ?>
                            </div>
                        </div>

                        <!-- Biển số & Tên xe -->
                        <div class="tc-plate-info">
                            <div class="tc-plate"><?= sanitize($t['plate_number']) ?></div>
                            <div class="tc-vname"><?= sanitize($t['vehicle_name']) ?></div>
                        </div>

                        <!-- Thanh trạng thái nằm gọn ở giữa -->
                        <div class="timeline-compact">
                            <div class="timeline-wrapper">
                                <div class="timeline-track">
                                    <div class="timeline-fill"></div>
                                </div>
                                <div class="car-on-track">
                                    <?= getVehicleSVG($t['icon'] ?? 'sedan', 'currentColor', 'normal', 18, 10) ?>
                                </div>
                            </div>
                            <div class="timeline-times">
                                <span class="tl-start"><?= date('H:i', strtotime($t['departure_time'])) ?></span>
                                <span class="tl-middle" data-duration="">--:--</span>
                                <span class="tl-end"><?= $t['expected_return'] ? date('H:i', strtotime($t['expected_return'])) : '--:--' ?></span>
                            </div>
                        </div>

                        <!-- Thời gian đã ra ngoài -->
                        <div class="tc-timer compact-timer">
                            <div class="tc-timer-num live-timer" data-depart="<?= date('Y-m-d H:i:s', strtotime($t['departure_time'])) ?>">
                                <?= formatMinsOut($minsOut) ?>
                            </div>
                            <div class="tc-timer-lbl"><?= $txt['out_label'] ?></div>
                        </div>
                    </div>

                    <!-- HÀNG THỨ 2: CHI TIẾT (ĐIỂM ĐẾN, MỤC ĐÍCH, CẢNH BÁO TRỄ) -->
                    <div class="tc-details-row">
                        <div class="tc-dest-badge">📍 <?= sanitize($t['destination']) ?></div>
                        <div class="tc-purpose-badge">💬 <?= sanitize(mb_strimwidth($t['purpose'],0,80,'...')) ?></div>
                        
                        <!-- CHUYỂN ĐỔI CHẠY THỜI GIAN THỰC QUA CSS/JS -->
                        <span class="overdue-inline-badge"><?= $txt['overdue_warning'] ?></span>
                    </div>
                </div>

                <!-- 3. PHẦN CHỮ KÝ VÀ THÔNG TIN PHỤ (TC-META) - ĐÃ BỎ MỤC THÀNH VIÊN ĐỂ CHIA 3 CỘT ĐỐI XỨNG TUYỆT ĐỐI -->
                <div class="tc-meta">
                    <div class="tc-meta-item">
                        <div class="tc-ml"><?= $txt['meta_driver'] ?></div>
                        <div class="tc-mv cyan"><?= sanitize($t['driver_name']) ?></div>
                    </div>
                    <div class="tc-meta-item">
                        <div class="tc-ml"><?= $txt['meta_depart'] ?></div>
                        <div class="tc-mv"><?= date('H:i', strtotime($t['departure_time'])) ?></div>
                    </div>
                    <div class="tc-meta-item">
                        <div class="tc-ml"><?= $txt['meta_expected'] ?></div>
                        <!-- CẬP NHẬT: Loại bỏ class "green" để màu sắc đồng bộ, tự động đổi về đen trong light theme và trắng/xám trong dark theme -->
                        <div class="tc-mv"><?= $t['expected_return'] ? date('H:i', strtotime($t['expected_return'])) : '—' ?></div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>

        <div class="side">

            <div class="panel">
                <div class="phead">
                    <div class="ptitle">🚙 <?= $txt['panel_status'] ?></div>
                </div>
                <?php foreach ($vehicles as $v):
                    $sl=['available'=>$txt['v_available'],'in_use'=>$txt['v_in_use'],'maintenance'=>$txt['v_maintenance']];
                ?>
                <!-- SỬA ĐỒI: THÊM THUỘC TÍNH DỮ LIỆU ĐỂ SINH POPUP THÔNG TIN XE -->
                <div class="veh-item" style="display:flex; align-items:center; gap:12px;"
                     data-plate="<?= sanitize($v['plate_number']) ?>"
                     data-name="<?= sanitize($v['vehicle_name']) ?>"
                     data-type="<?= sanitize($v['vehicle_type']) ?>"
                     data-status="<?= $v['status'] ?>"
                     data-notes="<?= sanitize($v['notes'] ?? '') ?>"
                     onclick="openVehicleDetailModal(this)">
                    <div style="flex-shrink:0;">
                        <?= getVehicleSVG($v['icon'] ?? 'sedan', $v['icon_color'] ?? '#FF6B00', $v['status'] === 'maintenance' ? 'offline' : 'normal', 36, 36) ?>
                    </div>
                    <div>
                        <div class="vplate"><?= sanitize($v['plate_number']) ?></div>
                        <div class="vname"><?= sanitize($v['vehicle_name']) ?></div>
                    </div>
                    <span class="vstatus <?= $v['status'] ?>"><?= $sl[$v['status']] ?></span>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="panel">
                <div class="phead">
                    <div class="ptitle">⏰ <?= $txt['panel_upcoming'] ?></div>
                    <span class="pbadge b"><?= count($upcoming) ?></span>
                </div>
                <?php if (empty($upcoming)): ?>
                <div class="empty"><?= $txt['no_data'] ?></div>
                <?php else: ?>
                <?php foreach ($upcoming as $t): ?>
                <!-- SỬA ĐỒI: THÊM THUỘC TÍNH DỮ LIỆU ĐỂ SINH POPUP CHUYẾN SẮP XUẤT PHÁT -->
                <div class="urow"
                     data-plate="<?= sanitize($t['plate_number']) ?>"
                     data-vname="<?= sanitize($t['vehicle_name']) ?>"
                     data-driver="<?= sanitize($t['driver_name'] ?? '—') ?>"
                     data-requester="<?= sanitize($t['requester_name']) ?>"
                     data-dept="<?= sanitize($t['requester_dept'] ?? '—') ?>"
                     data-companions="<?= sanitize($t['companions'] ?? '') ?>"
                     data-origin="<?= sanitize($t['origin'] ?? '—') ?>"
                     data-destination="<?= sanitize($t['destination']) ?>"
                     data-purpose="<?= sanitize($t['purpose']) ?>"
                     data-depart="<?= date('H:i', strtotime($t['departure_time'])) ?>"
                     data-expected="<?= $t['expected_return'] ? date('H:i', strtotime($t['expected_return'])) : '—' ?>"
                     data-status="scheduled"
                     onclick="openTripDetailModal(this)">
                    <div class="uplat"><?= sanitize($t['plate_number']) ?></div>
                    <div>
                        <div class="udest"><?= sanitize(mb_strimwidth($t['destination'],0,28,'...')) ?></div>
                        <div class="uwho"><?= sanitize($t['requester_name']) ?></div>
                    </div>
                    <div class="utime"><?= date('H:i',strtotime($t['departure_time'])) ?></div>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <div class="panel">
                <div class="phead">
                    <div class="ptitle">✅ <?= $txt['panel_returned'] ?></div>
                    <span class="pbadge g"><?= count($returned) ?></span>
                </div>
                <?php if (empty($returned)): ?>
                <div class="empty"><?= $txt['no_returned'] ?></div>
                <?php else: ?>
                <?php foreach ($returned as $t): ?>
                <!-- SỬA ĐỒI: THÊM THUỘC TÍNH DỮ LIỆU ĐỂ SINH POPUP CHUYẾN ĐÃ VỀ -->
                <div class="rrow"
                     data-plate="<?= sanitize($t['plate_number']) ?>"
                     data-vname="<?= sanitize($t['vehicle_name']) ?>"
                     data-driver="<?= sanitize($t['driver_name'] ?? '—') ?>"
                     data-requester="<?= sanitize($t['requester_name']) ?>"
                     data-dept="<?= sanitize($t['requester_dept'] ?? '—') ?>"
                     data-companions="<?= sanitize($t['companions'] ?? '') ?>"
                     data-origin="<?= sanitize($t['origin'] ?? '—') ?>"
                     data-destination="<?= sanitize($t['destination']) ?>"
                     data-purpose="<?= sanitize($t['purpose']) ?>"
                     data-depart="<?= date('H:i', strtotime($t['departure_time'])) ?>"
                     data-expected="<?= $t['expected_return'] ? date('H:i', strtotime($t['expected_return'])) : '—' ?>"
                     data-actual="<?= $t['actual_return'] ? date('H:i', strtotime($t['actual_return'])) : '—' ?>"
                     data-gate-out="<?= sanitize($t['gate_out_note'] ?? '—') ?>"
                     data-gate-in="<?= sanitize($t['gate_in_note'] ?? '—') ?>"
                     data-status="returned"
                     onclick="openTripDetailModal(this)">
                    <div class="rplat"><?= sanitize($t['plate_number']) ?></div>
                    <div>
                        <div class="rdest"><?= sanitize(mb_strimwidth($t['destination'],0,28,'...')) ?></div>
                        <div class="rwho"><?= sanitize($t['requester_name']) ?></div>
                    </div>
                    <div class="rtime"><?= $t['actual_return']?date('H:i',strtotime($t['actual_return'])):'' ?></div>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- POPUP 1: HIỂN THỊ CHI TIẾT CHUYẾN XE (ĐỒNG BỘ APPLE DESIGN) -->
<div class="db-modal-backdrop" id="tripDetailModal">
    <div class="db-modal" style="width: 480px;">
        <div class="db-modal-header">
            <span class="db-modal-title"><?= $txt['modal_title'] ?></span>
            <button class="db-modal-close" onclick="closeModal('tripDetailModal')">✕</button>
        </div>
        <div class="db-modal-body" style="max-height: 80vh; overflow-y: auto;">
            <div style="display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid var(--border); padding-bottom:12px; margin-bottom: 6px;">
                <div>
                    <span class="tc-plate" id="tdPlate" style="font-size:16px;">—</span>
                    <div id="tdVname" style="font-size:12px; color:var(--dim); margin-top:6px; font-weight:600;">—</div>
                </div>
                <span class="badge" id="tdStatus" style="font-size:11px; padding:4px 12px; border-radius:99px; text-transform:uppercase; font-weight:700;">—</span>
            </div>
            <div class="db-member-group">
                <div class="db-member-label"><?= $txt['modal_leader'] ?></div>
                <div class="db-member-value" id="tdRequester">—</div>
                <div style="font-size:12px; color:var(--dim); margin-top:2px;" id="tdDept">—</div>
            </div>
            <div class="db-member-group">
                <div class="db-member-label"><?= $txt['modal_companions'] ?></div>
                <div id="tdCompanionsList"></div>
            </div>
            <div class="db-member-group" style="display:grid; grid-template-columns: 1fr 1fr; gap:12px;">
                <div>
                    <div class="db-member-label"><?= $txt['modal_origin'] ?></div>
                    <div class="db-member-value" id="tdOrigin">—</div>
                </div>
                <div>
                    <div class="db-member-label"><?= $txt['modal_destination'] ?></div>
                    <div class="db-member-value" id="tdDestination">—</div>
                </div>
            </div>
            <div class="db-member-group">
                <div class="db-member-label"><?= $txt['modal_purpose'] ?></div>
                <div class="db-member-value" style="font-size:13px; font-weight:normal;" id="tdPurpose">—</div>
            </div>
            <div class="db-member-group" style="display:grid; grid-template-columns: 1fr 1fr; gap:12px;">
                <div>
                    <div class="db-member-label"><?= $txt['modal_depart_time'] ?></div>
                    <div class="db-member-value" style="font-family:'Rajdhani'; font-size:16px;" id="tdDepart">—</div>
                </div>
                <div>
                    <div class="db-member-label"><?= $txt['modal_expected_return'] ?></div>
                    <div class="db-member-value" style="font-family:'Rajdhani'; font-size:16px;" id="tdExpected">—</div>
                </div>
            </div>
            <div class="db-member-group" id="tdActualRow" style="display:none;">
                <div class="db-member-label"><?= $txt['modal_actual_return'] ?></div>
                <div class="db-member-value" style="font-family:'Rajdhani'; font-size:16px; color:var(--green);" id="tdActual">—</div>
            </div>
            <div class="db-member-group" id="tdNotesRow" style="display:none; grid-template-columns: 1fr 1fr; gap:12px;">
                <div>
                    <div class="db-member-label"><?= $txt['modal_gate_out_note'] ?></div>
                    <div class="db-member-value" style="font-size:13px; font-weight:normal;" id="tdGateOut">—</div>
                </div>
                <div>
                    <div class="db-member-label"><?= $txt['modal_gate_in_note'] ?></div>
                    <div class="db-member-value" style="font-size:13px; font-weight:normal;" id="tdGateIn">—</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- POPUP 3: HIỂN THỊ CHI TIẾT TÌNH TRẠNG XE (ĐỒNG BỘ APPLE DESIGN) -->
<div class="db-modal-backdrop" id="vehicleDetailModal">
    <div class="db-modal" style="width: 440px;">
        <div class="db-modal-header">
            <span class="db-modal-title"><?= $txt['modal_veh_title'] ?></span>
            <button class="db-modal-close" onclick="closeModal('vehicleDetailModal')">✕</button>
        </div>
        <div class="db-modal-body">
            <div style="display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid var(--border); padding-bottom:12px; margin-bottom: 6px;">
                <div>
                    <span class="tc-plate" id="vdPlate" style="font-size:16px;">—</span>
                    <div id="vdName" style="font-size:13px; font-weight:600; margin-top:6px;">—</div>
                </div>
                <span class="vstatus" id="vdStatus" style="font-size:11px; padding:4px 12px; border-radius:99px; text-transform:uppercase; font-weight:700;">—</span>
            </div>
            <div class="db-member-group">
                <div class="db-member-label"><?= $txt['modal_veh_type'] ?></div>
                <div class="db-member-value" id="vdType">—</div>
            </div>
            <div class="db-member-group">
                <div class="db-member-label"><?= $txt['modal_veh_notes'] ?></div>
                <div class="db-member-value" style="font-size:13px; font-weight:normal; line-height: 1.4;" id="vdNotes">—</div>
            </div>
        </div>
    </div>
</div>

<div class="rbar"><div class="rprog" id="prog"></div></div>
<div class="rlabel" id="rlbl"><?= $txt['update_lbl'] ?> <?= date('H:i:s') ?></div>

<script>
// ── ĐỒNG BỘ ĐỘ LỆCH THỜI GIAN THỰC TẾ MÁY CHỦ VÀ MÁY KHÁCH ──
const serverTimeAtLoad = <?= time() * 1000 ?>;
const clientTimeAtLoad = Date.now();
const serverClientDelta = serverTimeAtLoad - clientTimeAtLoad;

function getServerTime() {
    return new Date(Date.now() + serverClientDelta);
}

// ── REALTIME CLOCK ──
function tick(){
    const n = getServerTime();
    const p=v=>String(v).padStart(2,'0');
    document.getElementById('clock').textContent=p(n.getHours())+':'+p(n.getMinutes())+':'+p(n.getSeconds());
    const days=<?= $lang === 'jp' ? "['日','月','火','水','木','金','土']" : "['Chủ Nhật','Thứ Hai','Thứ Ba','Thứ Tư','Thứ Năm','Thứ Sáu','Thứ Bảy']" ?>;
    document.getElementById('date-lbl').textContent=(<?= $lang === 'jp' ? "''" : "days[n.getDay()] + ', '" ?>)+p(n.getDate())+'/'+p(n.getMonth()+1)+'/'+n.getFullYear()+(<?= $lang === 'jp' ? "' (' + days[n.getDay()] + ')'" : "''" ?>);
}
setInterval(tick,1000); tick();

// ── TIMER: "đã ra ngoài" update realtime ──
function fmtMins(mins){
    if(mins<60) return mins+ '<?= $lang === 'jp' ? "分" : " phút" ?>';
    const h=Math.floor(mins/60), m=mins%60;
    return h+'g'+(m>0?m+'p':'');
}
function updateTimers(){
    document.querySelectorAll('.live-timer').forEach(el=>{
        const dep=new Date(el.dataset.depart);
        const mins=Math.max(0,Math.floor((getServerTime().getTime() - dep.getTime())/60000));
        el.textContent=fmtMins(mins);
        if(mins>480) el.style.color='#f87171';
        else el.style.color='';
    });
}
setInterval(updateTimers, 1000);
updateTimers();

// ── TIMELINE REALTIME PROGRESS & STATE SELECTION (ĐƠN SẮC LIÊN TỤC) ──
function updateTimelines(){
    document.querySelectorAll('.trip-card').forEach(card=>{
        const depStr=card.dataset.dep;
        const expStr=card.dataset.exp;
        if(!depStr||!expStr) return;

        const dep=new Date(depStr).getTime();
        const exp=new Date(expStr).getTime();
        const now=getServerTime().getTime();

        let progress=(now-dep)/(exp-dep);
        progress=Math.max(0,Math.min(1,progress));

        const isOverdue = now > exp;
        
        // Chuyển đổi dải màu mượt mà dựa theo từng chặng tiến trình
        let stageColor = '#10b981'; // Giai đoạn 1: 0% - 50% (Xanh lục mềm mại)

        if (isOverdue) {
            stageColor = '#ef4444'; // Giai đoạn trễ giờ (Đỏ san hô)
        } else if (progress > 0.8) {
            stageColor = '#f97316'; // Giai đoạn 3: > 80% (Cam ấm áp)
        } else if (progress > 0.5) {
            stageColor = '#f59e0b'; // Giai đoạn 2: 50% - 80% (Vàng hổ phách)
        }

        // Gán biến CSS để các khối con kế thừa màu đồng bộ tự động
        card.style.setProperty('--stage-color', stageColor);

        const fillEl = card.querySelector('.timeline-fill');
        if (fillEl) {
            fillEl.style.width = (progress * 100) + '%';
        }

        const carEl = card.querySelector('.car-on-track');
        if (carEl) {
            carEl.style.left = (progress * 100) + '%';
        }

        // KÍCH HOẠT HIỆU ỨNG VIỀN ĐỎ SÁNG NHẤP NHÁY NHẸ CHO XE QUÁ GIỜ (REAL-TIME WARNING)
        if (isOverdue) {
            card.classList.add('overdue-card');
        } else {
            card.classList.remove('overdue-card');
        }

        const durationEl=card.querySelector('[data-duration]');
        if(durationEl){
            const totalMins=Math.round((exp-dep)/60000);
            const h=Math.floor(totalMins/60);
            const m=totalMins%60;
            durationEl.textContent=(h>0?h+'h':'')+(m>0?m+'m':'--:--');
        }
    });
}

setInterval(updateTimelines,200);
updateTimelines();

// ── BẢN DỊCH TỪ ĐIỂN ĐA NGÔN NGỮ CHO JAVASCRIPT POPUP ──
const translations = {
    status_scheduled: '<?= $lang === "jp" ? "配車済" : "Đã lên lịch" ?>',
    status_returned: '<?= $lang === "jp" ? "帰着済" : "Đã về" ?>',
    status_departed: '<?= $lang === "jp" ? "外出中" : "Đang ra ngoài" ?>',
    v_available: '<?= $txt["v_available"] ?>',
    v_in_use: '<?= $txt["v_in_use"] ?>',
    v_maintenance: '<?= $txt["v_maintenance"] ?>',
    modal_empty: '<?= $txt["modal_empty"] ?>',
    overdue_warning: '<?= $txt["overdue_warning"] ?>'
};

// ── JAVASCRIPT ĐIỀU KHIỂN POPUP BÓC TÁCH DATA AN TOÀN TUYỆT ĐỐI ──
let isModalOpen = false;

// ── JAVASCRIPT POPUP CHI TIẾT CHUYẾN XE (ĐỒNG BỘ HOÀN TOÀN KHI CLICK TRỰC TIẾP VÀO TRIP-CARD) ──
function openTripDetailModal(el) {
    isModalOpen = true;
    document.getElementById('tdPlate').textContent = el.getAttribute('data-plate');
    document.getElementById('tdVname').textContent = el.getAttribute('data-vname') + ' (' + (el.getAttribute('data-driver') || '—') + ')';
    document.getElementById('tdRequester').textContent = el.getAttribute('data-requester');
    document.getElementById('tdDept').textContent = el.getAttribute('data-dept');
    document.getElementById('tdOrigin').textContent = el.getAttribute('data-origin');
    document.getElementById('tdDestination').textContent = el.getAttribute('data-destination');
    document.getElementById('tdPurpose').textContent = el.getAttribute('data-purpose');
    document.getElementById('tdDepart').textContent = el.getAttribute('data-depart');
    document.getElementById('tdExpected').textContent = el.getAttribute('data-expected');

    // Phân tích danh sách người đi cùng thực tế
    const companionsStr = el.getAttribute('data-companions') || '';
    const listContainer = document.getElementById('tdCompanionsList');
    listContainer.innerHTML = '';
    if (!companionsStr.trim()) {
        listContainer.innerHTML = `<div style="color:var(--dim);font-size:12px;font-style:italic;padding:4px 0;">${translations.modal_empty}</div>`;
    } else {
        companionsStr.split(',').map(m => m.trim()).filter(Boolean).forEach(member => {
            const div = document.createElement('div');
            div.className = 'db-companion-item';
            div.innerHTML = `<div class="db-companion-dot"></div><div>${member}</div>`;
            listContainer.appendChild(div);
        });
    }

    // Thiết kế Badge trạng thái đồng bộ động
    const status = el.getAttribute('data-status');
    const isOverdue = el.classList.contains('overdue-card');
    const statusEl = document.getElementById('tdStatus');
    statusEl.className = 'badge';
    
    if (isOverdue) {
        statusEl.textContent = translations.overdue_warning;
        statusEl.style.backgroundColor = 'var(--red)';
        statusEl.style.color = '#fff';
    } else if (status === 'scheduled') {
        statusEl.textContent = translations.status_scheduled;
        statusEl.style.backgroundColor = 'var(--blue)';
        statusEl.style.color = '#fff';
    } else if (status === 'returned') {
        statusEl.textContent = translations.status_returned;
        statusEl.style.backgroundColor = 'var(--green)';
        statusEl.style.color = '#fff';
    } else if (status === 'departed') {
        statusEl.textContent = translations.status_departed;
        statusEl.style.backgroundColor = 'var(--cyan)';
        statusEl.style.color = '#fff';
    }

    // Hiển thị phần thông tin chỉ có khi xe đã về thực tế
    const actualRow = document.getElementById('tdActualRow');
    const notesRow = document.getElementById('tdNotesRow');
    if (status === 'returned') {
        document.getElementById('tdActual').textContent = el.getAttribute('data-actual') || '—';
        document.getElementById('tdGateOut').textContent = el.getAttribute('data-gate-out') || '—';
        document.getElementById('tdGateIn').textContent = el.getAttribute('data-gate-in') || '—';
        actualRow.style.display = 'block';
        notesRow.style.display = 'grid';
    } else {
        actualRow.style.display = 'none';
        notesRow.style.display = 'none';
    }

    document.getElementById('tripDetailModal').classList.add('open');
}

// ── JAVASCRIPT POPUP TÌNH TRẠNG CHI TIẾT PHƯƠNG TIỆN XE ──
function openVehicleDetailModal(el) {
    isModalOpen = true;
    document.getElementById('vdPlate').textContent = el.getAttribute('data-plate');
    document.getElementById('vdName').textContent = el.getAttribute('data-name');
    document.getElementById('vdType').textContent = el.getAttribute('data-type');
    
    const notes = (el.getAttribute('data-notes') || '').trim();
    document.getElementById('vdNotes').textContent = notes ? notes : '—';

    // Thể hiện trạng thái vận hành xe
    const status = el.getAttribute('data-status');
    const statusEl = document.getElementById('vdStatus');
    statusEl.className = 'vstatus ' + status;
    if (status === 'available') {
        statusEl.textContent = translations.v_available;
        statusEl.style.backgroundColor = 'rgba(16, 185, 129, 0.12)';
        statusEl.style.color = 'var(--green)';
    } else if (status === 'in_use') {
        statusEl.textContent = translations.v_in_use;
        statusEl.style.backgroundColor = 'rgba(245, 158, 11, 0.12)';
        statusEl.style.color = 'var(--yellow)';
    } else {
        statusEl.textContent = translations.v_maintenance;
        statusEl.style.backgroundColor = 'rgba(239, 68, 68, 0.1)';
        statusEl.style.color = 'var(--red)';
    }

    document.getElementById('vehicleDetailModal').classList.add('open');
}

function closeModal(id) {
    isModalOpen = false;
    document.getElementById(id).classList.remove('open');
}

document.querySelectorAll('.db-modal-backdrop').forEach(backdrop => {
    backdrop.addEventListener('click', function(e) {
        if (e.target === this) closeModal(this.id);
    });
});

document.addEventListener('keydown', e => {
    if (e.key === 'Escape') {
        document.querySelectorAll('.db-modal-backdrop.open').forEach(m => closeModal(m.id));
    }
});

// ── ĐIỀU KHIỂN CHUYỂN ĐỔI THEME SÁNG / TỐI ĐỒNG BỘ HOÀN TOÀN ──
document.getElementById('theme-toggle').addEventListener('click', function() {
    document.body.classList.toggle('light-theme'); // Sử dụng document.body đồng bộ với CSS là body.light-theme
    if (document.body.classList.contains('light-theme')) {
        localStorage.setItem('panel-theme', 'light');
    } else {
        localStorage.setItem('panel-theme', 'dark');
    }
});

// ── AUTO REFRESH MỖI 15 GIÂY VỚI CHU KỲ QUÉT 300MS CHUẨN XÁC ──
const REFRESH=15000;
let elapsed=0;
const prog=document.getElementById('prog');
setInterval(()=>{
    if(!isModalOpen) {
        elapsed+=300; // Nhịp tịnh tiến 300ms 
        prog.style.width=Math.min(elapsed/REFRESH*100,100)+'%';
        if(elapsed>=REFRESH){elapsed=0;location.reload();}
    }
},300); // Đặt chu kỳ lặp chuẩn 300ms (300mil)
</script>
</body>
</html>