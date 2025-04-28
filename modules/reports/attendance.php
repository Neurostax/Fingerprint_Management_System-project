<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';

// Check if user is logged in and has permission to view reports
if (!isLoggedIn() || !in_array($_SESSION['role'], ['admin', 'lecturer'])) {
    redirect('login.php');
}

// Get filter parameters
$class_id = isset($_GET['class_id']) ? (int)$_GET['class_id'] : 0;
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d', strtotime('-1 month'));
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');
$export = isset($_GET['export']) ? $_GET['export'] : '';

// Get available classes
$stmt = $pdo->prepare("
    SELECT c.*, l.name as lecturer_name 
    FROM classes c 
    JOIN users l ON c.lecturer_id = l.id 
    " . ($_SESSION['role'] === 'lecturer' ? "WHERE c.lecturer_id = ?" : "") . "
    ORDER BY c.name
");
if ($_SESSION['role'] === 'lecturer') {
    $stmt->execute([$_SESSION['user_id']]);
} else {
    $stmt->execute();
}
$classes = $stmt->fetchAll();

// Get attendance data
$params = [];
$sql = "
    SELECT a.*, u.name as student_name, u.reg_number, c.name as class_name 
    FROM attendance a 
    JOIN users u ON a.student_id = u.id 
    JOIN classes c ON a.class_id = c.id 
    WHERE DATE(a.created_at) BETWEEN ? AND ?
";
$params[] = $start_date;
$params[] = $end_date;

if ($class_id > 0) {
    $sql .= " AND a.class_id = ?";
    $params[] = $class_id;
}

if ($_SESSION['role'] === 'lecturer') {
    $sql .= " AND c.lecturer_id = ?";
    $params[] = $_SESSION['user_id'];
}

$sql .= " ORDER BY a.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$attendance = $stmt->fetchAll();

// Handle export
if ($export) {
    if ($export === 'csv') {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="attendance_report.csv"');
        
        $output = fopen('php://output', 'w');
        
        // Add CSV headers
        fputcsv($output, ['Date', 'Time', 'Student Name', 'Registration Number', 'Class', 'Status']);
        
        // Add data
        foreach ($attendance as $record) {
            fputcsv($output, [
                date('Y-m-d', strtotime($record['created_at'])),
                date('H:i', strtotime($record['created_at'])),
                $record['student_name'],
                $record['reg_number'],
                $record['class_name'],
                $record['status']
            ]);
        }
        
        fclose($output);
        exit;
    } elseif ($export === 'pdf') {
        require_once __DIR__ . '/../../vendor/autoload.php';
        
        // Create new PDF document
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        
        // Set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Attendance System');
        $pdf->SetTitle('Attendance Report');
        
        // Set default header data
        $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, 'Attendance Report', 'Generated on ' . date('Y-m-d H:i'));
        
        // Set header and footer fonts
        $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
        $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
        
        // Set default monospaced font
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
        
        // Set margins
        $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
        
        // Set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
        
        // Add a page
        $pdf->AddPage();
        
        // Set font
        $pdf->SetFont('helvetica', '', 10);
        
        // Add table header
        $html = '<table border="1" cellpadding="4">
            <tr style="background-color: #f0f0f0;">
                <th>Date</th>
                <th>Time</th>
                <th>Student Name</th>
                <th>Registration Number</th>
                <th>Class</th>
                <th>Status</th>
            </tr>';
        
        // Add table rows
        foreach ($attendance as $record) {
            $html .= '<tr>
                <td>' . date('Y-m-d', strtotime($record['created_at'])) . '</td>
                <td>' . date('H:i', strtotime($record['created_at'])) . '</td>
                <td>' . htmlspecialchars($record['student_name']) . '</td>
                <td>' . htmlspecialchars($record['reg_number']) . '</td>
                <td>' . htmlspecialchars($record['class_name']) . '</td>
                <td>' . htmlspecialchars($record['status']) . '</td>
            </tr>';
        }
        
        $html .= '</table>';
        
        // Output the HTML content
        $pdf->writeHTML($html, true, false, true, false, '');
        
        // Close and output PDF document
        $pdf->Output('attendance_report.pdf', 'D');
        exit;
    }
}

$page_title = lang('attendance_reports');
require_once __DIR__ . '/../../includes/header.php';
?>

<div class="container mt-4">
    <h2><?php echo lang('attendance_reports'); ?></h2>
    
    <form method="GET" class="mb-4">
        <div class="row">
            <div class="col-md-3">
                <div class="form-group">
                    <label for="class_id"><?php echo lang('class'); ?></label>
                    <select class="form-control" id="class_id" name="class_id">
                        <option value="0"><?php echo lang('all_classes'); ?></option>
                        <?php foreach ($classes as $class): ?>
                            <option value="<?php echo $class['id']; ?>" <?php echo $class_id == $class['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($class['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label for="start_date"><?php echo lang('start_date'); ?></label>
                    <input type="date" class="form-control" id="start_date" name="start_date" value="<?php echo $start_date; ?>">
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label for="end_date"><?php echo lang('end_date'); ?></label>
                    <input type="date" class="form-control" id="end_date" name="end_date" value="<?php echo $end_date; ?>">
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label>&nbsp;</label>
                    <div>
                        <button type="submit" class="btn btn-primary"><?php echo lang('filter'); ?></button>
                        <a href="?<?php echo http_build_query(array_merge($_GET, ['export' => 'csv'])); ?>" class="btn btn-success">
                            <?php echo lang('export_csv'); ?>
                        </a>
                        <a href="?<?php echo http_build_query(array_merge($_GET, ['export' => 'pdf'])); ?>" class="btn btn-danger">
                            <?php echo lang('export_pdf'); ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>
    
    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th><?php echo lang('date'); ?></th>
                    <th><?php echo lang('time'); ?></th>
                    <th><?php echo lang('student_name'); ?></th>
                    <th><?php echo lang('registration_number'); ?></th>
                    <th><?php echo lang('class'); ?></th>
                    <th><?php echo lang('status'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($attendance as $record): ?>
                    <tr>
                        <td><?php echo date('Y-m-d', strtotime($record['created_at'])); ?></td>
                        <td><?php echo date('H:i', strtotime($record['created_at'])); ?></td>
                        <td><?php echo htmlspecialchars($record['student_name']); ?></td>
                        <td><?php echo htmlspecialchars($record['reg_number']); ?></td>
                        <td><?php echo htmlspecialchars($record['class_name']); ?></td>
                        <td><?php echo lang($record['status']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?> 