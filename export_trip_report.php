<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
requireLogin();

// Load PhpSpreadsheet
require __DIR__ . '/vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Color;

// ─── Tăng giới hạn tài nguyên để tránh timeout ─────────────────
set_time_limit(120);
ini_set('memory_limit', '256M');

// ─── KHỞI TẠO BỘ LỌC NGÔN NGỮ ĐỒNG BỘ THEO URL (MẶC ĐỊNH LÀ VI) ──
$lang = $_GET['lang'] ?? 'vi';
if (!in_array($lang, ['vi', 'jp'])) {
    $lang = 'vi';
}

// ─── BỘ TỪ ĐIỂN SONG NGỮ VIỆT - NHẬT CHO TỆP BÁO CÁO EXCEL ───
$lang_pack = [
    'vi' => [
        'main_title' => 'BÁO CÁO LỊCH SỬ CHUYẾN XE',
        'label_time' => 'Thời gian: ',
        'label_export' => 'Xuất lúc: ',
        'label_total' => 'Tổng số: ',
        'unit_trip' => ' chuyến',
        'summary_prefix' => 'Tổng cộng: ',
        'summary_suffix' => ' chuyến xe',
        'err_no_range' => 'Vui lòng cung cấp khoảng thời gian hợp lệ để xuất dữ liệu.',
        'err_invalid_date' => 'Ngày không hợp lệ.',
        'err_date_order' => 'Ngày kết thúc phải sau ngày bắt đầu.',
        'err_date_range' => 'Khoảng thời gian vượt quá 6 tháng gần nhất. Ngày bắt đầu tối thiểu: ',
        'err_max_allowed' => 'Khoảng thời gian tối đa cho phép là 6 tháng.',
        'headers' => [
            'A' => 'STT',
            'B' => 'Họ tên nhân sự',
            'C' => 'Bộ phận / Phòng ban',
            'D' => 'Người đi cùng',
            'E' => 'Tên xe',
            'F' => 'Biển số xe',
            'G' => 'Tài xế',
            'H' => 'Điểm đi',
            'I' => 'Điểm đến',
            'J' => 'Thời gian khởi hành',
            'K' => 'Kết thúc / Quay về',
            'L' => 'Trạng thái',
        ],
        'status' => [
            'scheduled' => 'Đã lên lịch',
            'departed'  => 'Đang đi',
            'returned'  => 'Đã về',
            'cancelled' => 'Đã hủy',
        ],
        'filter_today' => 'Hôm nay — ',
        'filter_upcoming' => 'Sắp đi',
        'filter_active' => 'Đang đi',
        'filter_all_to' => ' đến ',
        'file_today' => 'hom-nay_',
        'file_upcoming' => 'sap-di_',
        'file_active' => 'dang-di_',
        'file_all' => 'tat-ca_',
        'file_default' => 'chuyen-xe_',
    ],
    'jp' => [
        'main_title' => '車両運行履歴レポート',
        'label_time' => '対象期間: ',
        'label_export' => '出力日時: ',
        'label_total' => '合計: ',
        'unit_trip' => ' 件',
        'summary_prefix' => '合計: ',
        'summary_suffix' => ' 件',
        'err_no_range' => 'データを出力するための有効な期間を指定してください。',
        'err_invalid_date' => '日付が無効です。',
        'err_date_order' => '終了日は開始日より後の日付にしてください。',
        'err_date_range' => '指定期間が直近6ヶ月を超えています。最古開始日: ',
        'err_max_allowed' => '最大選択可能期間は6ヶ月です。',
        'headers' => [
            'A' => 'No.',
            'B' => '申請者氏名',
            'C' => '部署名',
            'D' => '同乗者',
            'E' => '車両名',
            'F' => '車両番号',
            'G' => '運転手',
            'H' => '出発地',
            'I' => '目的地',
            'J' => '出発日時',
            'K' => '帰着予定 / 実帰着',
            'L' => 'ステータス',
        ],
        'status' => [
            'scheduled' => '配車済',
            'departed'  => '外出中',
            'returned'  => '帰着済',
            'cancelled' => 'キャンセル',
        ],
        'filter_today' => '本日 — ',
        'filter_upcoming' => '出発予定',
        'filter_active' => '外出中',
        'filter_all_to' => ' ～ ',
        'file_today' => '本日_',
        'file_upcoming' => '出発予定_',
        'file_active' => '外出中_',
        'file_all' => '全運行履歴_',
        'file_default' => '車両運行_',
    ]
];

$txt = $lang_pack[$lang];

$today      = date('Y-m-d');
$filterType = $_GET['filter'] ?? 'today';

// ─── Xác định khoảng thời gian xuất ────────────────────────────
$start_date = '';
$end_date   = '';
$whereClause = '';
$params      = [];

if ($filterType === 'all') {
    $raw_start = trim($_GET['start_date'] ?? '');
    $raw_end   = trim($_GET['end_date']   ?? '');
    $sixMonthsAgo = date('Y-m-d', strtotime('-6 months'));

    // Validate
    if ($raw_start === '' || $raw_end === '') {
        http_response_code(400);
        exit($txt['err_no_range']);
    }

    $ts_start = strtotime($raw_start);
    $ts_end   = strtotime($raw_end);

    if ($ts_start === false || $ts_end === false) {
        http_response_code(400);
        exit($txt['err_invalid_date']);
    }
    if ($ts_end < $ts_start) {
        http_response_code(400);
        exit($txt['err_date_order']);
    }
    if ($ts_start < strtotime($sixMonthsAgo)) {
        http_response_code(400);
        $dateFmt = ($lang === 'jp') ? date('Y/m/d', strtotime($sixMonthsAgo)) : date('d/m/Y', strtotime($sixMonthsAgo));
        exit($txt['err_date_range'] . $dateFmt);
    }
    if (($ts_end - $ts_start) > 182 * 86400) {
        http_response_code(400);
        exit($txt['err_max_allowed']);
    }

    $start_date  = date('Y-m-d', $ts_start);
    $end_date    = date('Y-m-d', min($ts_end, strtotime($today)));
    $whereClause = "DATE(t.departure_time) BETWEEN :start_date AND :end_date";
    $params      = [':start_date' => $start_date, ':end_date' => $end_date];

} elseif ($filterType === 'today') {
    $start_date  = $today;
    $end_date    = $today;
    $whereClause = "DATE(t.departure_time) = :today";
    $params      = [':today' => $today];

} elseif ($filterType === 'upcoming') {
    $start_date  = $today;
    $end_date    = date('Y-m-d', strtotime('+7 days'));
    $whereClause = "t.departure_time > NOW() AND t.status = 'scheduled'";

} elseif ($filterType === 'active') {
    $start_date  = $today;
    $end_date    = $today;
    $whereClause = "t.status = 'departed'";

} else {
    $start_date  = $today;
    $end_date    = $today;
    $whereClause = "DATE(t.departure_time) = :today";
    $params      = [':today' => $today];
}

// ─── Truy vấn dữ liệu ───────────────────────────────────────────
$sql = "
    SELECT
        t.id,
        t.requester_name,
        t.requester_dept,
        t.companions,
        v.vehicle_name,
        v.plate_number,
        d.full_name   AS driver_name,
        t.origin,
        t.destination,
        t.departure_time,
        t.expected_return,
        t.actual_return,
        t.status
    FROM trips t
    LEFT JOIN vehicles v ON t.vehicle_id = v.id
    LEFT JOIN drivers d  ON t.driver_id  = d.id
    WHERE $whereClause
    ORDER BY t.departure_time ASC
";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$trips = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ─── Cấu hình màu ô trạng thái giữ nguyên ─────────────────────
$statusColors = [
    'scheduled' => 'FFFFF3CC', // vàng nhạt
    'departed'  => 'FFFFD9B3', // cam nhạt
    'returned'  => 'FFD4EDDA', // xanh nhạt
    'cancelled' => 'FFFFE0E0', // đỏ nhạt
];

// ─── Định dạng hiển thị mốc thời gian bộ lọc theo ngôn ngữ ──────
$displayDateFormat = ($lang === 'jp') ? 'Y/m/d' : 'd/m/Y';
if ($filterType === 'all') {
    $periodLabel = date($displayDateFormat, strtotime($start_date)) . $txt['filter_all_to'] . date($displayDateFormat, strtotime($end_date));
} elseif ($filterType === 'today') {
    $periodLabel = $txt['filter_today'] . date($displayDateFormat);
} else {
    $periodLabel = $txt['filter_' . $filterType] ?? date($displayDateFormat);
}

// ─── Tạo Spreadsheet ────────────────────────────────────────────
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Report');

// ── Tiêu đề chính ──
$sheet->setCellValue('A1', $txt['main_title']);
$sheet->mergeCells('A1:L1'); 
$sheet->getStyle('A1')->applyFromArray([
    'font' => ['bold' => true, 'size' => 16, 'color' => ['argb' => 'FF1A3A5C']],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFD6E4F7']],
]);
$sheet->getRowDimension(1)->setRowHeight(32);

// ── Dòng khoảng thời gian ──
$sheet->setCellValue('A2', $txt['label_time'] . $periodLabel);
$sheet->mergeCells('A2:L2'); 
$sheet->getStyle('A2')->applyFromArray([
    'font' => ['italic' => true, 'size' => 11, 'color' => ['argb' => 'FF4A6080']],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
]);
$sheet->getRowDimension(2)->setRowHeight(20);

// ── Dòng xuất file ──
$exportDateLog = ($lang === 'jp') ? date('Y/m/d H:i:s') : date('H:i:s d/m/Y');
$sheet->setCellValue('A3', $txt['label_export'] . $exportDateLog . ' | ' . $txt['label_total'] . count($trips) . $txt['unit_trip']);
$sheet->mergeCells('A3:L3'); 
$sheet->getStyle('A3')->applyFromArray([
    'font' => ['size' => 10, 'color' => ['argb' => 'FF8A9BB0']],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
]);
$sheet->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$sheet->getRowDimension(3)->setRowHeight(18);

// ── Header cột song ngữ tuần tự ──
foreach ($txt['headers'] as $col => $label) {
    $sheet->setCellValue($col . '5', $label);
}

$sheet->getStyle('A5:L5')->applyFromArray([
    'font' => [
        'bold'  => true,
        'size'  => 11,
        'color' => ['argb' => 'FFFFFFFF'],
    ],
    'fill' => [
        'fillType'   => Fill::FILL_SOLID,
        'startColor' => ['argb' => 'FF1A56DB'],
    ],
    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_CENTER,
        'vertical'   => Alignment::VERTICAL_CENTER,
        'wrapText'   => true,
    ],
    'borders' => [
        'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFADC6E5']],
    ],
]);
$sheet->getRowDimension(5)->setRowHeight(26);

// ── Dữ liệu dòng ──
$row = 6;
$dataTimeFormat = ($lang === 'jp') ? 'Y/m/d H:i' : 'd/m/Y H:i';

foreach ($trips as $i => $trip) {
    $statusKey = strtolower($trip['status'] ?? '');
    
    $returnVal = $trip['actual_return']
        ? date($dataTimeFormat, strtotime($trip['actual_return']))
        : ($trip['expected_return'] ? date($dataTimeFormat, strtotime($trip['expected_return'])) : '—');

    $sheet->setCellValue('A' . $row, $i + 1);
    $sheet->setCellValue('B' . $row, $trip['requester_name'] ?? '');
    $sheet->setCellValue('C' . $row, $trip['requester_dept'] ?? '');
    $sheet->setCellValue('D' . $row, $trip['companions']     ?? ''); 
    $sheet->setCellValue('E' . $row, $trip['vehicle_name']   ?? '');
    $sheet->setCellValue('F' . $row, $trip['plate_number']   ?? '');
    $sheet->setCellValue('G' . $row, $trip['driver_name']    ?? '');
    $sheet->setCellValue('H' . $row, $trip['origin']         ?? '');
    $sheet->setCellValue('I' . $row, $trip['destination']    ?? '');
    $sheet->setCellValue('J' . $row, $trip['departure_time'] ? date($dataTimeFormat, strtotime($trip['departure_time'])) : '');
    $sheet->setCellValue('K' . $row, $returnVal);
    $sheet->setCellValue('L' . $row, $txt['status'][$statusKey] ?? $trip['status']);

    // Màu nền xen kẽ
    $rowBg = ($i % 2 === 0) ? 'FFF8FAFF' : 'FFFFFFFF';
    $sheet->getStyle('A' . $row . ':L' . $row)->applyFromArray([
        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => $rowBg]],
        'borders' => [
            'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFD0DCF0']],
        ],
        'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
    ]);

    // Màu ô trạng thái tiếng Nhật / tiếng Việt động
    $statusBg = $statusColors[$statusKey] ?? 'FFFFFFFF';
    $sheet->getStyle('L' . $row)->applyFromArray([
        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => $statusBg]],
        'font' => ['bold' => true],
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
    ]);

    // Định vị căn giữa khối
    $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle('F' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); 
    $sheet->getStyle('J' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); 
    $sheet->getStyle('K' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); 

    $sheet->getRowDimension($row)->setRowHeight(22);
    $row++;
}

// ── Dòng tổng kết ──
if (count($trips) > 0) {
    $sheet->setCellValue('A' . $row, $txt['summary_prefix'] . count($trips) . $txt['summary_suffix']);
    $sheet->mergeCells('A' . $row . ':L' . $row); 
    $sheet->getStyle('A' . $row)->applyFromArray([
        'font' => ['bold' => true, 'italic' => true, 'color' => ['argb' => 'FF1A3A5C']],
        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFD6E4F7']],
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT],
        'borders' => [
            'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFADC6E5']],
        ],
    ]);
}

// ── Định kích thước cố định cột ──
$colWidths = [
    'A' => 6,   // STT
    'B' => 22,  // Họ tên
    'C' => 22,  // Bộ phận
    'D' => 25,  // Người đi cùng
    'E' => 20,  // Tên xe
    'F' => 14,  // Biển số
    'G' => 20,  // Tài xế
    'H' => 22,  // Điểm đi
    'I' => 22,  // Điểm đến
    'J' => 18,  // Khởi hành
    'K' => 18,  // Kết thúc
    'L' => 16,  // Trạng thái
];
foreach ($colWidths as $col => $width) {
    $sheet->getColumnDimension($col)->setWidth($width);
}

$sheet->freezePane('A6');

// ─── Đồng bộ tên tệp tải về theo ngôn ngữ hiển thị ─────────────────
$filePrefix = $txt['file_' . $filterType] ?? $txt['file_default'];
if ($filterType === 'all') {
    $filename = $filePrefix . $start_date . '_' . $end_date . '.xlsx';
} else {
    $filename = $filePrefix . $today . '.xlsx';
}

// ─── Xuất tệp tin nhị phân ra Output ────────────────────────────────
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: max-age=0, must-revalidate');
header('Pragma: public');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;