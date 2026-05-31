<?php
// =====================================================
// CẤU HÌNH KẾT NỐI DATABASE
// =====================================================
// Ép hệ thống PHP lấy chính xác thời gian thực tế của máy chủ theo múi giờ Việt Nam
date_default_timezone_set('Asia/Ho_Chi_Minh');
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'vehicle_mgmt');
define('SITE_NAME', 'Hệ Thống Quản Lý Xe');

$pdo = null;
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
        ]
    );
} catch (PDOException $e) {
    die('<div style="font-family:sans-serif;padding:20px;background:#fee;border:1px solid red;margin:20px">
        <h3>❌ Lỗi kết nối Database</h3>
        <p>Vui lòng kiểm tra XAMPP đã bật MySQL chưa và đã import file <b>database.sql</b> chưa.</p>
        <small>' . htmlspecialchars($e->getMessage()) . '</small>
    </div>');
}

// Helper functions
function sanitize($str) {
    return htmlspecialchars(trim($str), ENT_QUOTES, 'UTF-8');
}

function formatDateTime($dt) {
    if (!$dt) return '—';
    return date('d/m/Y H:i', strtotime($dt));
}

function tripStatusLabel($status) {
    $map = [
        'scheduled' => ['label' => 'Đã lên lịch', 'class' => 'status-scheduled'],
        'departed'  => ['label' => 'Đã xuất phát', 'class' => 'status-departed'],
        'returned'  => ['label' => 'Đã về', 'class' => 'status-returned'],
        'cancelled' => ['label' => 'Đã hủy', 'class' => 'status-cancelled'],
    ];
    return $map[$status] ?? ['label' => $status, 'class' => ''];
}

function darkenColor($hex, $factor = 0.6) {
    $hex = str_replace('#', '', $hex);
    if (strlen($hex) == 3) {
        $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
    }
    $r = hexdec(substr($hex, 0, 2));
    $g = hexdec(substr($hex, 2, 2));
    $b = hexdec(substr($hex, 4, 2));
    
    $r = max(0, min(255, round($r * $factor)));
    $g = max(0, min(255, round($g * $factor)));
    $b = max(0, min(255, round($b * $factor)));
    
    return sprintf("#%02x%02x%02x", $r, $g, $b);
}

function lightenColor($hex, $add = 60) {
    $hex = str_replace('#', '', $hex);
    if (strlen($hex) == 3) {
        $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
    }
    $r = hexdec(substr($hex, 0, 2));
    $g = hexdec(substr($hex, 2, 2));
    $b = hexdec(substr($hex, 4, 2));
    
    $r = max(0, min(255, $r + $add));
    $g = max(0, min(255, $g + $add));
    $b = max(0, min(255, $b + $add));
    
    return sprintf("#%02x%02x%02x", $r, $g, $b);
}

function getVehicleBodyMarkup($type, $c, $dark, $light, $win, $wheel, $rim) {
    switch ($type) {
        case 'sedan':
            return '
            <path d="M8 30 L8 34 Q8 36 10 36 L38 36 Q40 36 40 34 L40 30 Q40 28 38 27 L34 25 L30 18 Q29 16 26 16 L20 16 Q17 16 16 18 L13 25 L10 27 Q8 28 8 30Z" fill="' . $c . '"/>
            <path d="M14 27 L33 27 L29.5 19 Q28.5 17 26 17 L21 17 Q18.5 17 17.5 19Z" fill="' . $win . '" opacity="0.9"/>
            <path d="M8 30 L40 30 L38 27 L10 27Z" fill="' . $dark . '"/>
            <circle cx="14" cy="36" r="4.5" fill="' . $wheel . '"/><circle cx="14" cy="36" r="2.5" fill="' . $rim . '"/>
            <circle cx="34" cy="36" r="4.5" fill="' . $wheel . '"/><circle cx="34" cy="36" r="2.5" fill="' . $rim . '"/>
            <rect x="36" y="29" width="3" height="2" rx="1" fill="#FCD34D" opacity="0.9"/>
            <rect x="9" y="29" width="3" height="2" rx="1" fill="#F87171" opacity="0.7"/>';
        case 'suv':
            return '
            <path d="M7 29 L7 34 Q7 36 9 36 L39 36 Q41 36 41 34 L41 29 L41 23 Q41 21 39 20 L34 19 L30 15 Q28 13 24 13 L19 13 Q16 13 14 15 L9 20 Q7 21 7 23Z" fill="' . $c . '"/>
            <path d="M13 20 L34 20 L30 15 Q28 14 24 14 L19 14 Q16.5 14 15 16Z" fill="' . $win . '" opacity="0.85"/>
            <path d="M7 27 L41 27" stroke="' . $dark . '" stroke-width="1.2" fill="none"/>
            <rect x="13" y="20" width="21" height="6.5" rx="1" fill="' . $win . '" opacity="0.75"/>
            <circle cx="13.5" cy="36" r="5" fill="' . $wheel . '"/><circle cx="13.5" cy="36" r="2.8" fill="' . $rim . '"/>
            <circle cx="34.5" cy="36" r="5" fill="' . $wheel . '"/><circle cx="34.5" cy="36" r="2.8" fill="' . $rim . '"/>
            <rect x="37" y="27" width="4" height="2.5" rx="1" fill="#FCD34D" opacity="0.9"/>
            <rect x="7" y="27" width="4" height="2.5" rx="1" fill="#F87171" opacity="0.7"/>';
        case 'mpv7':
            return '
            <path d="M7 30 L7 35 Q7 37 9 37 L39 37 Q41 37 41 35 L41 30 L41 22 Q41 20 39 19 L35 18 L31 14 Q29 12 25 12 L17 12 Q14 12 12 14 L9 18 Q7 19 7 22Z" fill="' . $c . '"/>
            <path d="M11 19 L36 19 L32 14 Q30 13 25 13 L18 13 Q15 13 13 15Z" fill="' . $win . '" opacity="0.8"/>
            <path d="M7 27 L41 27" stroke="' . $dark . '" stroke-width="1" fill="none"/>
            <rect x="11" y="19" width="25" height="7" rx="1.5" fill="' . $win . '" opacity="0.7"/>
            <line x1="24" y1="19" x2="24" y2="27" stroke="' . $dark . '" stroke-width="0.8" opacity="0.6"/>
            <circle cx="13" cy="37" r="5" fill="' . $wheel . '"/><circle cx="13" cy="37" r="2.8" fill="' . $rim . '"/>
            <circle cx="35" cy="37" r="5" fill="' . $wheel . '"/><circle cx="35" cy="37" r="2.8" fill="' . $rim . '"/>
            <rect x="38" y="27" width="3.5" height="2.5" rx="1" fill="#FCD34D" opacity="0.9"/>
            <rect x="6.5" y="27" width="3.5" height="2.5" rx="1" fill="#F87171" opacity="0.7"/>';
        case 'mpv8':
            return '
            <path d="M6 30 L6 35 Q6 37 8 37 L40 37 Q42 37 42 35 L42 30 L42 22 Q42 20 40 19 L36 18 L32 14 Q30 12 25 12 L17 12 Q14 12 12 14 L10 18 Q6 20 6 22Z" fill="' . $c . '"/>
            <path d="M10 19 L37 19 L32.5 14 Q30.5 13 25 13 L17.5 13 Q15 13 13 15Z" fill="' . $win . '" opacity="0.8"/>
            <rect x="10" y="19" width="27" height="7.5" rx="1.5" fill="' . $win . '" opacity="0.65"/>
            <line x1="21" y1="19" x2="21" y2="26.5" stroke="' . $dark . '" stroke-width="0.8" opacity="0.6"/>
            <line x1="28" y1="19" x2="28" y2="26.5" stroke="' . $dark . '" stroke-width="0.8" opacity="0.6"/>
            <path d="M6 27.5 L42 27.5" stroke="' . $dark . '" stroke-width="1" fill="none"/>
            <circle cx="12.5" cy="37" r="5" fill="' . $wheel . '"/><circle cx="12.5" cy="37" r="2.8" fill="' . $rim . '"/>
            <circle cx="35.5" cy="37" r="5" fill="' . $wheel . '"/><circle cx="35.5" cy="37" r="2.8" fill="' . $rim . '"/>
            <rect x="39" y="27" width="3.5" height="2.5" rx="1" fill="#FCD34D" opacity="0.9"/>
            <rect x="5.5" y="27" width="3.5" height="2.5" rx="1" fill="#F87171" opacity="0.7"/>';
        case 'minibus16':
            return '
            <rect x="5" y="16" width="38" height="22" rx="3" fill="' . $c . '"/>
            <rect x="5" y="16" width="38" height="8" rx="3" fill="' . $dark . '"/>
            <rect x="8" y="18" width="5" height="4.5" rx="1" fill="' . $win . '"/>
            <rect x="15" y="18" width="5" height="4.5" rx="1" fill="' . $win . '"/>
            <rect x="22" y="18" width="5" height="4.5" rx="1" fill="' . $win . '"/>
            <rect x="29" y="18" width="5" height="4.5" rx="1" fill="' . $win . '"/>
            <rect x="36" y="18" width="5" height="4.5" rx="1" fill="' . $win . '"/>
            <rect x="7" y="24.5" width="34" height="1" fill="' . $dark . '" opacity="0.5"/>
            <rect x="9" y="26" width="6" height="10" rx="1.5" fill="' . $dark . '" opacity="0.5"/>
            <circle cx="12" cy="37.5" r="4.5" fill="' . $wheel . '"/><circle cx="12" cy="37.5" r="2.5" fill="' . $rim . '"/>
            <circle cx="36" cy="37.5" r="4.5" fill="' . $wheel . '"/><circle cx="36" cy="37.5" r="2.5" fill="' . $rim . '"/>
            <rect x="39" y="22" width="4" height="2.5" rx="1" fill="#FCD34D" opacity="0.9"/>
            <rect x="5" y="22" width="4" height="2.5" rx="1" fill="#F87171" opacity="0.7"/>';
        case 'minibus':
            return '
            <rect x="4" y="15" width="40" height="23" rx="3.5" fill="' . $c . '"/>
            <rect x="4" y="15" width="40" height="9" rx="3.5" fill="' . $dark . '"/>
            <rect x="4" y="15" width="40" height="3" rx="3.5" fill="' . $light . '" opacity="0.4"/>
            <rect x="7" y="17" width="4.5" height="5" rx="1" fill="' . $win . '"/>
            <rect x="13" y="17" width="4.5" height="5" rx="1" fill="' . $win . '"/>
            <rect x="19" y="17" width="4.5" height="5" rx="1" fill="' . $win . '"/>
            <rect x="25" y="17" width="4.5" height="5" rx="1" fill="' . $win . '"/>
            <rect x="31" y="17" width="4.5" height="5" rx="1" fill="' . $win . '"/>
            <rect x="37" y="17" width="4.5" height="5" rx="1" fill="' . $win . '"/>
            <rect x="7" y="24" width="7" height="12" rx="1.5" fill="' . $dark . '" opacity="0.4"/>
            <circle cx="11" cy="37.5" r="4.5" fill="' . $wheel . '"/><circle cx="11" cy="37.5" r="2.5" fill="' . $rim . '"/>
            <circle cx="37" cy="37.5" r="4.5" fill="' . $wheel . '"/><circle cx="37" cy="37.5" r="2.5" fill="' . $rim . '"/>
            <rect x="40" y="20" width="4" height="2.5" rx="1" fill="#FCD34D" opacity="0.9"/>
            <rect x="4" y="20" width="4" height="2.5" rx="1" fill="#F87171" opacity="0.7"/>';
        case 'van':
            return '
            <path d="M6 22 L6 35 Q6 37 8 37 L40 37 Q42 37 42 35 L42 22 L42 19 Q42 17 40 16 L34 16 L30 12 Q28 11 24 11 L14 11 Q11 11 10 13 L8 16 Q6 17 6 19Z" fill="' . $c . '"/>
            <path d="M10 16 L30 16 L27 12 Q25.5 11.5 24 11.5 L15 11.5 Q12.5 11.5 11.5 13Z" fill="' . $win . '" opacity="0.8"/>
            <line x1="30" y1="11" x2="30" y2="37" stroke="' . $dark . '" stroke-width="1.5"/>
            <rect x="31" y="17" width="10" height="18" rx="1" fill="' . $dark . '" opacity="0.2"/>
            <circle cx="31" cy="26" r="1.5" fill="' . $dark . '" opacity="0.7"/>
            <circle cx="12.5" cy="37" r="5" fill="' . $wheel . '"/><circle cx="12.5" cy="37" r="2.8" fill="' . $rim . '"/>
            <circle cx="36" cy="37" r="5" fill="' . $wheel . '"/><circle cx="36" cy="37" r="2.8" fill="' . $rim . '"/>
            <rect x="39" y="22" width="3" height="2" rx="1" fill="#FCD34D" opacity="0.9"/>
            <rect x="6" y="22" width="3" height="2" rx="1" fill="#F87171" opacity="0.7"/>';
        case 'pickup':
            return '
            <path d="M7 26 L7 34 Q7 36 9 36 L41 36 Q43 36 43 34 L43 26 L43 22 L37 22 L37 19 Q37 17 35 16 L28 16 L24 13 Q22 12 19 12 L14 12 Q11 12 10 14 L8 17 Q7 18 7 20Z" fill="' . $c . '"/>
            <path d="M10 17 L29 17 L26 13 Q24.5 12.5 19.5 12.5 L15 12.5 Q12.5 12.5 11.5 14Z" fill="' . $win . '" opacity="0.8"/>
            <rect x="29" y="17" width="13" height="18" rx="1" fill="' . $dark . '" opacity="0.3"/>
            <line x1="29" y1="17" x2="29" y2="35" stroke="' . $dark . '" stroke-width="2"/>
            <line x1="32" y1="17" x2="32" y2="22" stroke="' . $dark . '" stroke-width="0.7" opacity="0.6"/>
            <line x1="36" y1="17" x2="36" y2="22" stroke="' . $dark . '" stroke-width="0.7" opacity="0.6"/>
            <circle cx="13" cy="36" r="5" fill="' . $wheel . '"/><circle cx="13" cy="36" r="2.8" fill="' . $rim . '"/>
            <circle cx="36" cy="36" r="5" fill="' . $wheel . '"/><circle cx="36" cy="36" r="2.8" fill="' . $rim . '"/>
            <rect x="40" y="23" width="3" height="2" rx="1" fill="#FCD34D" opacity="0.9"/>
            <rect x="7" y="23" width="3" height="2" rx="1" fill="#F87171" opacity="0.7"/>';
        case 'truck_s':
            return '
            <rect x="5" y="20" width="38" height="18" rx="2" fill="' . $c . '"/>
            <path d="M5 20 L5 38 L20 38 L20 20Z" fill="' . $dark . '" opacity="0.15"/>
            <rect x="7" y="22" width="10" height="7" rx="1.5" fill="' . $win . '"/>
            <rect x="20" y="14" width="23" height="24" rx="2" fill="' . $c . '"/>
            <rect x="20" y="14" width="23" height="2" rx="1" fill="' . $light . '" opacity="0.5"/>
            <line x1="20" y1="22" x2="43" y2="22" stroke="' . $dark . '" stroke-width="0.6" opacity="0.4"/>
            <line x1="20" y1="29" x2="43" y2="29" stroke="' . $dark . '" stroke-width="0.6" opacity="0.4"/>
            <line x1="30" y1="14" x2="30" y2="38" stroke="' . $dark . '" stroke-width="0.6" opacity="0.4"/>
            <circle cx="11" cy="38" r="4.5" fill="' . $wheel . '"/><circle cx="11" cy="38" r="2.5" fill="' . $rim . '"/>
            <circle cx="36" cy="38" r="4.5" fill="' . $wheel . '"/><circle cx="36" cy="38" r="2.5" fill="' . $rim . '"/>
            <rect x="5" y="24" width="3.5" height="2" rx="0.5" fill="#F87171" opacity="0.7"/>';
        case 'truck_m':
            return '
            <rect x="3" y="22" width="19" height="16" rx="2" fill="' . $c . '"/>
            <rect x="5" y="24" width="13" height="8" rx="1.5" fill="' . $win . '"/>
            <rect x="20" y="12" width="26" height="26" rx="2" fill="' . $c . '"/>
            <rect x="20" y="12" width="26" height="3" rx="1.5" fill="' . $light . '" opacity="0.4"/>
            <line x1="20" y1="22" x2="46" y2="22" stroke="' . $dark . '" stroke-width="0.8" opacity="0.5"/>
            <line x1="20" y1="30" x2="46" y2="30" stroke="' . $dark . '" stroke-width="0.8" opacity="0.5"/>
            <line x1="33" y1="12" x2="33" y2="38" stroke="' . $dark . '" stroke-width="0.8" opacity="0.5"/>
            <circle cx="9" cy="38" r="4.5" fill="' . $wheel . '"/><circle cx="9" cy="38" r="2.5" fill="' . $rim . '"/>
            <circle cx="28" cy="38" r="4.5" fill="' . $wheel . '"/><circle cx="28" cy="38" r="2.5" fill="' . $rim . '"/>
            <circle cx="40" cy="38" r="4.5" fill="' . $wheel . '"/><circle cx="40" cy="38" r="2.5" fill="' . $rim . '"/>
            <rect x="3" y="26" width="3" height="2" rx="0.5" fill="#F87171" opacity="0.7"/>';
        case 'truck_l':
            return '
            <rect x="2" y="23" width="16" height="15" rx="2" fill="' . $c . '"/>
            <rect x="4" y="25" width="11" height="7.5" rx="1.5" fill="' . $win . '"/>
            <rect x="16" y="10" width="30" height="28" rx="2" fill="' . $c . '"/>
            <rect x="16" y="10" width="30" height="3" rx="1.5" fill="' . $light . '" opacity="0.4"/>
            <line x1="16" y1="21" x2="46" y2="21" stroke="' . $dark . '" stroke-width="0.8" opacity="0.5"/>
            <line x1="16" y1="30" x2="46" y2="30" stroke="' . $dark . '" stroke-width="0.8" opacity="0.5"/>
            <line x1="28" y1="10" x2="28" y2="38" stroke="' . $dark . '" stroke-width="0.8" opacity="0.5"/>
            <line x1="37" y1="10" x2="37" y2="38" stroke="' . $dark . '" stroke-width="0.8" opacity="0.5"/>
            <circle cx="8" cy="38" r="4" fill="' . $wheel . '"/><circle cx="8" cy="38" r="2.2" fill="' . $rim . '"/>
            <circle cx="24" cy="38" r="4.5" fill="' . $wheel . '"/><circle cx="24" cy="38" r="2.5" fill="' . $rim . '"/>
            <circle cx="32" cy="38" r="4.5" fill="' . $wheel . '"/><circle cx="32" cy="38" r="2.5" fill="' . $rim . '"/>
            <circle cx="40" cy="38" r="4.5" fill="' . $wheel . '"/><circle cx="40" cy="38" r="2.5" fill="' . $rim . '"/>
            <rect x="2" y="27" width="3" height="2" rx="0.5" fill="#F87171" opacity="0.7"/>';
        case 'container':
            return '
            <rect x="2" y="24" width="14" height="14" rx="2" fill="' . $c . '"/>
            <rect x="3" y="26" width="10" height="7" rx="1.5" fill="' . $win . '"/>
            <rect x="14" y="9" width="33" height="29" rx="2" fill="' . $c . '"/>
            <rect x="14" y="9" width="33" height="3" rx="0" fill="' . $dark . '" opacity="0.2"/>
            <rect x="14" y="9" width="33" height="1.5" rx="1" fill="' . $light . '" opacity="0.3"/>
            <line x1="21" y1="12" x2="21" y2="38" stroke="' . $dark . '" stroke-width="0.7" opacity="0.4"/>
            <line x1="28" y1="12" x2="28" y2="38" stroke="' . $dark . '" stroke-width="0.7" opacity="0.4"/>
            <line x1="35" y1="12" x2="35" y2="38" stroke="' . $dark . '" stroke-width="0.7" opacity="0.4"/>
            <line x1="42" y1="12" x2="42" y2="38" stroke="' . $dark . '" stroke-width="0.7" opacity="0.4"/>
            <line x1="14" y1="19" x2="47" y2="19" stroke="' . $dark . '" stroke-width="0.6" opacity="0.3"/>
            <line x1="14" y1="27" x2="47" y2="27" stroke="' . $dark . '" stroke-width="0.6" opacity="0.3"/>
            <circle cx="43" cy="24" r="0.8" fill="' . $dark . '" opacity="0.7"/>
            <circle cx="45" cy="24" r="0.8" fill="' . $dark . '" opacity="0.7"/>
            <circle cx="7" cy="38" r="4" fill="' . $wheel . '"/><circle cx="7" cy="38" r="2.2" fill="' . $rim . '"/>
            <circle cx="22" cy="38" r="4.5" fill="' . $wheel . '"/><circle cx="22" cy="38" r="2.5" fill="' . $rim . '"/>
            <circle cx="31" cy="38" r="4.5" fill="' . $wheel . '"/><circle cx="31" cy="38" r="2.5" fill="' . $rim . '"/>
            <circle cx="40" cy="38" r="4.5" fill="' . $wheel . '"/><circle cx="40" cy="38" r="2.5" fill="' . $rim . '"/>
            <rect x="2" y="28" width="2.5" height="2" rx="0.5" fill="#F87171" opacity="0.7"/>';
        case 'bus':
            return '
            <rect x="3" y="12" width="42" height="27" rx="3.5" fill="' . $c . '"/>
            <rect x="3" y="20" width="42" height="3" fill="' . $dark . '" opacity="0.2"/>
            <rect x="6" y="13.5" width="5.5" height="5.5" rx="1.2" fill="' . $win . '"/>
            <rect x="13.5" y="13.5" width="5.5" height="5.5" rx="1.2" fill="' . $win . '"/>
            <rect x="21" y="13.5" width="5.5" height="5.5" rx="1.2" fill="' . $win . '"/>
            <rect x="28.5" y="13.5" width="5.5" height="5.5" rx="1.2" fill="' . $win . '"/>
            <rect x="36" y="13.5" width="5.5" height="5.5" rx="1.2" fill="' . $win . '"/>
            <rect x="4.5" y="13.5" width="5" height="7" rx="1.2" fill="' . $win . '" opacity="0.75"/>
            <rect x="3" y="23" width="3" height="4" rx="1" fill="' . $light . '" opacity="0.5"/>
            <rect x="5" y="22" width="7" height="14" rx="1.5" fill="' . $dark . '" opacity="0.3"/>
            <circle cx="12" cy="39" r="4.5" fill="' . $wheel . '"/><circle cx="12" cy="39" r="2.5" fill="' . $rim . '"/>
            <circle cx="36" cy="39" r="4.5" fill="' . $wheel . '"/><circle cx="36" cy="39" r="2.5" fill="' . $rim . '"/>
            <rect x="40" y="18" width="5" height="3" rx="1" fill="#FCD34D" opacity="0.9"/>
            <rect x="3" y="18" width="5" height="3" rx="1" fill="#F87171" opacity="0.7"/>';
        case 'ev':
            return '
            <path d="M9 28 L9 33 Q9 36 11 36 L37 36 Q39 36 39 33 L39 28 L39 24 Q39 22 38 21 L34 20 L30 17 Q28 16 24 16 L20 16 Q17 16 15 17 L11 20 Q9 21 9 23Z" fill="' . $c . '"/>
            <path d="M15 20 L33 20 L29.5 17.5 Q27.5 16.5 24 16.5 L21 16.5 Q18 16.5 16.5 18Z" fill="' . $win . '" opacity="0.9"/>
            <text x="22" y="31" font-size="9" fill="white" opacity="0.9" font-weight="bold">⚡</text>
            <path d="M9 26 L39 26" stroke="' . $dark . '" stroke-width="0.8" fill="none"/>
            <path d="M9 23 L39 23" stroke="' . $light . '" stroke-width="0.5" fill="none" opacity="0.4"/>
            <circle cx="14.5" cy="36" r="4.5" fill="' . $wheel . '"/><circle cx="14.5" cy="36" r="2.5" fill="' . $rim . '"/>
            <circle cx="33.5" cy="36" r="4.5" fill="' . $wheel . '"/><circle cx="33.5" cy="36" r="2.5" fill="' . $rim . '"/>
            <rect x="35" y="26" width="4" height="1.2" rx="0.6" fill="#7DD3FC" opacity="0.9"/>
            <rect x="9" y="26" width="4" height="1.2" rx="0.6" fill="#EF4444" opacity="0.8"/>';
        case 'service':
            return '
            <path d="M7 24 L7 35 Q7 37 9 37 L39 37 Q41 37 41 35 L41 24 L41 19 Q41 17 39 16 L34 16 L30 13 Q28 12 24 12 L15 12 Q12 12 11 13 L9 16 Q7 17 7 19Z" fill="' . $c . '"/>
            <path d="M11 16 L35 16 L31 13 Q29.5 12.5 24 12.5 L16 12.5 Q13.5 12.5 12.5 14Z" fill="' . $win . '" opacity="0.8"/>
            <rect x="7" y="24" width="34" height="3" fill="' . $light . '" opacity="0.3"/>
            <rect x="7" y="24" width="34" height="1" fill="white" opacity="0.15"/>
            <rect x="22" y="27" width="4" height="7" rx="1" fill="white" opacity="0.25"/>
            <circle cx="24" cy="27" r="2" fill="white" opacity="0.3"/>
            <circle cx="12.5" cy="37" r="5" fill="' . $wheel . '"/><circle cx="12.5" cy="37" r="2.8" fill="' . $rim . '"/>
            <circle cx="35.5" cy="37" r="5" fill="' . $wheel . '"/><circle cx="35.5" cy="37" r="2.8" fill="' . $rim . '"/>
            <rect x="38" y="22" width="3.5" height="2.5" rx="1" fill="#FCD34D" opacity="0.9"/>
            <rect x="6.5" y="22" width="3.5" height="2.5" rx="1" fill="#F87171" opacity="0.7"/>';
        case 'special':
            return '
            <rect x="5" y="18" width="38" height="20" rx="3" fill="' . $c . '"/>
            <rect x="12" y="12" width="24" height="8" rx="2" fill="' . $dark . '" opacity="0.6"/>
            <rect x="14" y="9" width="20" height="5" rx="1.5" fill="' . $dark . '" opacity="0.4"/>
            <rect x="15" y="9" width="4" height="2" rx="1" fill="#EF4444" opacity="0.9"/>
            <rect x="22" y="9" width="4" height="2" rx="1" fill="#3B82F6" opacity="0.9"/>
            <rect x="29" y="9" width="4" height="2" rx="1" fill="#EF4444" opacity="0.9"/>
            <rect x="7" y="20" width="10" height="7" rx="1.5" fill="' . $win . '"/>
            <line x1="20" y1="18" x2="20" y2="38" stroke="' . $dark . '" stroke-width="1.5"/>
            <rect x="21" y="20" width="5" height="5" rx="1" fill="' . $dark . '" opacity="0.2"/>
            <rect x="28" y="20" width="5" height="5" rx="1" fill="' . $dark . '" opacity="0.2"/>
            <rect x="35" y="20" width="5" height="5" rx="1" fill="' . $dark . '" opacity="0.2"/>
            <circle cx="11" cy="38" r="4.5" fill="' . $wheel . '"/><circle cx="11" cy="38" r="2.5" fill="' . $rim . '"/>
            <circle cx="36" cy="38" r="4.5" fill="' . $wheel . '"/><circle cx="36" cy="38" r="2.5" fill="' . $rim . '"/>
            <rect x="40" y="22" width="3" height="2" rx="0.5" fill="#FCD34D" opacity="0.9"/>
            <rect x="5" y="22" width="3" height="2" rx="0.5" fill="#F87171" opacity="0.7"/>';
            
        // 🏍️ CASE XE MÁY ĐƯỢC THÊM MỚI (ĐỒNG BỘ NÉT VẼ ĐƠN SẮC FLAT-STYLE)
        case 'motorcycle':
            return '
            <circle cx="13" cy="35" r="5" fill="' . $wheel . '"/><circle cx="13" cy="35" r="2.5" fill="' . $rim . '"/>
            <circle cx="35" cy="35" r="5" fill="' . $wheel . '"/><circle cx="35" cy="35" r="2.5" fill="' . $rim . '"/>
            <line x1="35" y1="35" x2="29" y2="15" stroke="' . $dark . '" stroke-width="2.2" stroke-linecap="round"/>
            <line x1="27" y1="15" x2="31" y2="15" stroke="#111" stroke-width="2.5" stroke-linecap="round"/>
            <rect x="18" y="26" width="11" height="8" rx="2" fill="' . $dark . '"/>
            <rect x="19" y="28" width="8" height="5" rx="1" fill="' . $light . '" opacity="0.8"/>
            <path d="M11 21 C 14 22, 18 22, 21 20 L 19 25 L 11 25 Z" fill="#111"/>
            <path d="M17 19 C 20 15, 27 15, 29 19 L 27 25 L 17 25 Z" fill="' . $c . '"/>
            <line x1="11" y1="31" x2="22" y2="31" stroke="' . $light . '" stroke-width="1.8" stroke-linecap="round"/>
            <rect x="31" y="17" width="2" height="2" rx="0.5" fill="#FCD34D" opacity="0.9"/>';
            
        // 🚲 CASE XE ĐẠP ĐƯỢC THÊM MỚI (ĐỒNG BỘ ĐỘ DÀY NÉT VẼ)
        case 'bicycle':
            return '
            <circle cx="13" cy="35" r="5.5" fill="none" stroke="' . $wheel . '" stroke-width="1.5"/><circle cx="13" cy="35" r="1.8" fill="' . $rim . '"/>
            <circle cx="35" cy="35" r="5.5" fill="none" stroke="' . $wheel . '" stroke-width="1.5"/><circle cx="35" cy="35" r="1.8" fill="' . $rim . '"/>
            <line x1="13" y1="35" x2="23" y2="35" stroke="' . $c . '" stroke-width="1.8" stroke-linecap="round"/>
            <line x1="13" y1="35" x2="21" y2="19" stroke="' . $c . '" stroke-width="1.8" stroke-linecap="round"/>
            <line x1="23" y1="35" x2="21" y2="19" stroke="' . $c . '" stroke-width="1.8" stroke-linecap="round"/>
            <line x1="23" y1="35" x2="31" y2="19" stroke="' . $c . '" stroke-width="1.8" stroke-linecap="round"/>
            <line x1="21" y1="19" x2="31" y2="19" stroke="' . $c . '" stroke-width="1.8" stroke-linecap="round"/>
            <line x1="35" y1="35" x2="31" y2="19" stroke="' . $c . '" stroke-width="1.8" stroke-linecap="round"/>
            <line x1="31" y1="19" x2="30" y2="13" stroke="' . $dark . '" stroke-width="1.5" stroke-linecap="round"/>
            <line x1="26" y1="13" x2="32" y2="13" stroke="#222" stroke-width="2" stroke-linecap="round"/>
            <path d="M18.5 19 Q21 17.5 23 19 L21 17 Z" fill="#222"/>
            <circle cx="23" cy="35" r="2.2" fill="' . $dark . '" stroke="' . $light . '" stroke-width="0.6"/>';
            
        default:
            return '<rect x="10" y="15" width="28" height="18" rx="3" fill="' . $c . '"/>';
    }
}

function getVehicleSVG($type, $color, $state = 'normal', $width = 48, $height = 48) {
    if (!$type) $type = 'sedan';
    if (!$color) $color = '#FF6B00';
    $isOffline = ($state === 'offline');
    $isSelected = ($state === 'selected');
    $c = $isOffline ? '#555555' : $color;
    $bodyOpacity = $isOffline ? '0.35' : '1';
    
    $ring = $isSelected
        ? '<circle cx="24" cy="24" r="22" fill="none" stroke="' . $c . '" stroke-width="1.5" stroke-dasharray="4 3" opacity="0.7"/>'
        : '';
        
    $glow = $isSelected
        ? '<filter id="glow"><feGaussianBlur stdDeviation="2" result="blur"/><feMerge><feMergeNode in="blur"/><feMergeNode in="SourceGraphic"/></feMerge></filter>'
        : '';
        
    $filterAttr = $isSelected ? ' filter="url(#glow)"' : '';
    
    $dark = darkenColor($c === '#555555' ? '#888888' : $c);
    $light = lightenColor($c === '#555555' ? '#555555' : $c, 60);
    $win = 'rgba(180,220,255,0.65)';
    $wheel = '#1a1a2e';
    $rim = '#3a3a5e';
    
    // Thêm hiệu ứng chống tàng hình nếu chọn màu trắng tinh tế (#FFFFFF) trên nền sáng
    $whiteStyle = '';
    if (strtoupper($c) === '#FFFFFF' || strtoupper($color) === '#FFFFFF') {
        $whiteStyle = ' style="filter: drop-shadow(0px 0px 0.8px rgba(0,0,0,0.6));"';
    }
    
    $bodyMarkup = getVehicleBodyMarkup($type, $c, $dark, $light, $win, $wheel, $rim);
    $g = '<g opacity="' . $bodyOpacity . '"' . $filterAttr . '>' . $bodyMarkup . '</g>';
    
    return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48" width="' . $width . '" height="' . $height . '"' . $whiteStyle . '><defs>' . $glow . '</defs>' . $ring . $g . '</svg>';
}

// =====================================================
// CẤU HÌNH GỬI MAIL (SMTP)
// =====================================================
define('SMTP_HOST', 'smtp.gmail.com');          // Máy chủ SMTP (Ví dụ của Gmail)
define('SMTP_PORT', 587);                       // Cổng TLS (587) hoặc SSL (465)
define('SMTP_USER', 'your_email@gmail.com');    // Email gửi tin
define('SMTP_PASS', 'xxxx xxxx xxxx xxxx');     // Mật khẩu ứng dụng (App Password)
define('SMTP_FROM_NAME', SITE_NAME);            // Tên hiển thị người gửi