<?php
// =====================================================
// ĐỒNG BỘ TRẠNG THÁI XE & TÀI XẾ THEO CHUYẾN ĐI
// =====================================================

/**
 * Gọi hàm này mỗi khi trạng thái chuyến đi thay đổi.
 * Tự động tính lại trạng thái xe và tài xế dựa trên
 * toàn bộ chuyến đang active trong DB.
 */
function syncVehicleAndDriver($pdo, $vehicleId, $driverId) {
    // --- XE ---
    // Xe đang có chuyến departed nào không?
    $hasActive = $pdo->prepare("
        SELECT COUNT(*) FROM trips
        WHERE vehicle_id = ? AND status = 'departed'
    ");
    $hasActive->execute([$vehicleId]);
    $vStatus = $hasActive->fetchColumn() > 0 ? 'in_use' : 'available';
    $pdo->prepare("UPDATE vehicles SET status = ? WHERE id = ?")->execute([$vStatus, $vehicleId]);

    // --- TÀI XẾ ---
    $hasActive2 = $pdo->prepare("
        SELECT COUNT(*) FROM trips
        WHERE driver_id = ? AND status = 'departed'
    ");
    $hasActive2->execute([$driverId]);
    $dStatus = $hasActive2->fetchColumn() > 0 ? 'on_trip' : 'available';
    $pdo->prepare("UPDATE drivers SET status = ? WHERE id = ?")->execute([$dStatus, $driverId]);
}

/**
 * Khi sửa chuyến và đổi xe/tài xế, cần sync cả xe/tài xế cũ lẫn mới.
 */
function syncAll($pdo, $vehicleIds = [], $driverIds = []) {
    foreach (array_unique(array_filter($vehicleIds)) as $vid) {
        $hasActive = $pdo->prepare("SELECT COUNT(*) FROM trips WHERE vehicle_id = ? AND status = 'departed'");
        $hasActive->execute([$vid]);
        $s = $hasActive->fetchColumn() > 0 ? 'in_use' : 'available';
        $pdo->prepare("UPDATE vehicles SET status = ? WHERE id = ?")->execute([$s, $vid]);
    }
    foreach (array_unique(array_filter($driverIds)) as $did) {
        $hasActive = $pdo->prepare("SELECT COUNT(*) FROM trips WHERE driver_id = ? AND status = 'departed'");
        $hasActive->execute([$did]);
        $s = $hasActive->fetchColumn() > 0 ? 'on_trip' : 'available';
        $pdo->prepare("UPDATE drivers SET status = ? WHERE id = ?")->execute([$s, $did]);
    }
}
