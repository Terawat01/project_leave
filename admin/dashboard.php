<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['position_id'] != 4) {
    // เดิม: header("Location: login.php");
    header('Location: ../login.php'); // ← ชี้ขึ้นไประดับบน
    exit();
}

require_once '../includes/db.php';
require_once '../includes/header.php';
require_once '../includes/sidebar_admin.php';

// --- PHP Logic for fetching data ---

// 1. Stats Cards Data
$pending_count = $conn->query("SELECT COUNT(*) as count FROM emp_leave WHERE Leave_Status_ID = '3'")->fetch_assoc()['count'];
$approved_count = $conn->query("SELECT COUNT(*) as count FROM emp_leave WHERE Leave_Status_ID = '1'")->fetch_assoc()['count'];
$employee_count = $conn->query("SELECT COUNT(*) as count FROM employee")->fetch_assoc()['count'];
$total_requests = $conn->query("SELECT COUNT(*) as count FROM emp_leave")->fetch_assoc()['count'];
$approved_percentage = ($total_requests > 0) ? round(($approved_count / $total_requests) * 100) : 0;

// 2. Pie Chart Data (Leave Types)
$pie_chart_result = $conn->query("
    SELECT lt.Leave_Type_Name, COUNT(el.Leave_ID) as count 
    FROM emp_leave el 
    JOIN leave_type lt ON el.Leave_Type_ID = lt.Leave_Type_ID 
    GROUP BY lt.Leave_Type_Name
");
$pie_chart_labels = [];
$pie_chart_data = [];
while($row = $pie_chart_result->fetch_assoc()) {
    $pie_chart_labels[] = $row['Leave_Type_Name'];
    $pie_chart_data[] = $row['count'];
}

// 3. Bar Chart Data (Monthly Stats for last 6 months)
$bar_chart_labels = [];
$bar_chart_data = [];
for ($i = 5; $i >= 0; $i--) {
    $month = date('m', strtotime("-$i month"));
    $year = date('Y', strtotime("-$i month"));
    $month_name_th = date('M', strtotime("-$i month")); // Using English month names for consistency with libraries
    
    $bar_chart_labels[] = $month_name_th;
    
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM emp_leave WHERE MONTH(Request_date) = ? AND YEAR(Request_date) = ?");
    $stmt->bind_param("ss", $month, $year);
    $stmt->execute();
    $count = $stmt->get_result()->fetch_assoc()['count'];
    $bar_chart_data[] = $count;
}
?>
<div class="main-content p-4">
    <h4 class="mb-1">แดชบอร์ดผู้ดูแลระบบ</h4>
    <p class="text-muted">ภาพรวมระบบการลางานองค์กร</p>

    <div class="row">
        <div class="col-lg-3 col-md-6">
            <div class="card p-3 mb-3">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="text-muted">คำขอลาทั้งหมด</h6>
                        <h4 class="mb-0"><?php echo $total_requests; ?></h4>
                    </div>
                    <div class="fs-2 text-muted"><i class="bi bi-calendar-week"></i></div>
                </div>
            </div>
        </div>
         <div class="col-lg-3 col-md-6">
            <div class="card p-3 mb-3">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="text-muted">รอการอนุมัติ</h6>
                        <h4 class="mb-0"><?php echo $pending_count; ?></h4>
                    </div>
                    <div class="fs-2 text-muted"><i class="bi bi-clock-history"></i></div>
                </div>
            </div>
        </div>
         <div class="col-lg-3 col-md-6">
            <div class="card p-3 mb-3">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="text-muted">อนุมัติแล้ว</h6>
                        <h4 class="mb-0"><?php echo $approved_count; ?> <small class="fs-6 text-muted"><?php echo $approved_percentage; ?>%</small></h4>
                    </div>
                    <div class="fs-2 text-muted"><i class="bi bi-check-circle"></i></div>
                </div>
            </div>
        </div>
         <div class="col-lg-3 col-md-6">
            <div class="card p-3 mb-3">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="text-muted">พนักงานทั้งหมด</h6>
                        <h4 class="mb-0"><?php echo $employee_count; ?></h4>
                    </div>
                    <div class="fs-2 text-muted"><i class="bi bi-people"></i></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-5">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">ประเภทการลา</h5>
                    <div style="position: relative; height:320px">
                        <canvas id="leaveTypePieChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-7">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">สถิติรายเดือน</h5>
                    <div style="position: relative; height:320px">
                        <canvas id="monthlyStatsBarChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    // Pie Chart for Leave Types
    const pieCtx = document.getElementById('leaveTypePieChart').getContext('2d');
    new Chart(pieCtx, {
        type: 'pie',
        data: {
            labels: <?php echo json_encode($pie_chart_labels); ?>,
            datasets: [{
                label: 'จำนวนคำขอ',
                data: <?php echo json_encode($pie_chart_data); ?>,
                backgroundColor: ['#dc3545', '#fd7e14', '#28a745', '#6c757d', '#17a2b8', '#ffc107', '#0dcaf0'],
                hoverOffset: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
        }
    });

    // Bar Chart for Monthly Stats
    const barCtx = document.getElementById('monthlyStatsBarChart').getContext('2d');
    new Chart(barCtx, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($bar_chart_labels); ?>,
            datasets: [{
                label: 'จำนวนคำขอลา',
                data: <?php echo json_encode($bar_chart_data); ?>,
                backgroundColor: 'rgba(54, 162, 235, 0.6)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 5
                    }
                }
            }
        }
    });
});
</script>

<?php
require_once '../includes/footer.php';
?>