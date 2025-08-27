<?php
require_once '../includes/db.php';
require_once '../includes/header.php';
require_once '../includes/sidebar_employee.php';

// --- Get Upcoming Holidays (Next 3 months) ---
$three_months_later = date('Y-m-d', strtotime('+3 months'));
$upcoming_stmt = $conn->prepare("
    SELECT * FROM dayoff 
    WHERE Dayoff_start_date BETWEEN CURDATE() AND ? 
    ORDER BY Dayoff_start_date ASC
");
$upcoming_stmt->bind_param("s", $three_months_later);
$upcoming_stmt->execute();
$upcoming_holidays = $upcoming_stmt->get_result();
// ---------------------------------------------


// --- Get All Holidays for the Current Year ---
$current_year = date('Y');
$all_year_stmt = $conn->prepare("
    SELECT * FROM dayoff 
    WHERE YEAR(Dayoff_start_date) = ? 
    ORDER BY Dayoff_start_date ASC
");
$all_year_stmt->bind_param("s", $current_year);
$all_year_stmt->execute();
$all_holidays_this_year = $all_year_stmt->get_result();
// -------------------------------------------

?>
<div class="main-content p-4">
    <h4 class="mb-1">วันหยุด</h4>
    <p class="text-muted">ตรวจสอบวันหยุดประจำปี</p>

    <div class="card mb-4">
        <div class="card-header">
            <i class="bi bi-calendar-event"></i> วันหยุดที่จะมาถึง (3 เดือนข้างหน้า)
        </div>
        <div class="card-body">
            <?php if ($upcoming_holidays->num_rows > 0): ?>
                <ul class="list-group list-group-flush">
                <?php while($row = $upcoming_holidays->fetch_assoc()): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <?php echo htmlspecialchars($row['Dayoff_Name']); ?>
                        <span class="badge bg-primary rounded-pill">
                            <?php echo date('d/m/Y', strtotime($row['Dayoff_start_date'])); ?>
                        </span>
                    </li>
                <?php endwhile; ?>
                </ul>
            <?php else: ?>
                <div class="text-center p-3">
                    <i class="bi bi-calendar-x fs-1 text-muted"></i>
                    <p class="mt-2 text-muted">ไม่มีวันหยุดในช่วง 3 เดือนข้างหน้า</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
           <i class="bi bi-calendar3"></i> วันหยุดทั้งหมดประจำปี <?php echo $current_year; ?>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>วันที่</th>
                            <th>ชื่อวันหยุด</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($all_holidays_this_year->num_rows > 0): ?>
                            <?php while($row = $all_holidays_this_year->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo date('d/m/Y', strtotime($row['Dayoff_start_date'])); ?></td>
                                <td><?php echo htmlspecialchars($row['Dayoff_Name']); ?></td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="2" class="text-center text-muted">ไม่มีข้อมูลวันหยุดสำหรับปีนี้</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php
require_once '../includes/footer.php';
$upcoming_stmt->close();
$all_year_stmt->close();
$conn->close();
?>