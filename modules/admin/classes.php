<?php
require_once '../../config/config.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

// Require admin role
requireRole('admin');

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['action'])) {
            switch ($_POST['action']) {
                case 'add':
                    $code = sanitizeInput($_POST['code']);
                    $name = sanitizeInput($_POST['name']);
                    $lecturer_id = (int)$_POST['lecturer_id'];
                    $semester = sanitizeInput($_POST['semester']);
                    $academic_year = sanitizeInput($_POST['academic_year']);
                    
                    $stmt = $pdo->prepare("INSERT INTO classes (code, name, lecturer_id, semester, academic_year) VALUES (?, ?, ?, ?, ?)");
                    $stmt->execute([$code, $name, $lecturer_id, $semester, $academic_year]);
                    $successMessage = lang('class_added');
                    logActivity($_SESSION['user_id'], "Added new class: $code");
                    break;
                    
                case 'edit':
                    $id = (int)$_POST['id'];
                    $code = sanitizeInput($_POST['code']);
                    $name = sanitizeInput($_POST['name']);
                    $lecturer_id = (int)$_POST['lecturer_id'];
                    $semester = sanitizeInput($_POST['semester']);
                    $academic_year = sanitizeInput($_POST['academic_year']);
                    
                    $stmt = $pdo->prepare("UPDATE classes SET code = ?, name = ?, lecturer_id = ?, semester = ?, academic_year = ? WHERE id = ?");
                    $stmt->execute([$code, $name, $lecturer_id, $semester, $academic_year, $id]);
                    $successMessage = lang('class_updated');
                    logActivity($_SESSION['user_id'], "Updated class: $code");
                    break;
                    
                case 'delete':
                    $id = (int)$_POST['id'];
                    $stmt = $pdo->prepare("DELETE FROM classes WHERE id = ?");
                    $stmt->execute([$id]);
                    $successMessage = lang('class_deleted');
                    logActivity($_SESSION['user_id'], "Deleted class ID: $id");
                    break;
            }
        }
    } catch (PDOException $e) {
        error_log("Class management error: " . $e->getMessage());
        $errorMessage = lang('error_occurred');
    }
}

// Get all classes with lecturer information
try {
    $stmt = $pdo->query("
        SELECT c.*, u.name as lecturer_name 
        FROM classes c 
        LEFT JOIN users u ON c.lecturer_id = u.id 
        ORDER BY c.academic_year DESC, c.semester, c.code
    ");
    $classes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get all lecturers for dropdown
    $stmt = $pdo->query("SELECT id, name FROM users WHERE role = 'lecturer' ORDER BY name");
    $lecturers = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Get classes error: " . $e->getMessage());
    $errorMessage = lang('error_occurred');
}

$pageTitle = lang('class_management');
require_once '../../includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <h1 class="h3 mb-4"><?php echo lang('class_management'); ?></h1>
        </div>
    </div>

    <!-- Add Class Button -->
    <div class="row mb-4">
        <div class="col-12">
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addClassModal">
                <i class="fas fa-plus-circle me-2"></i>
                <?php echo lang('add_class'); ?>
            </button>
        </div>
    </div>

    <!-- Classes Table -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th><?php echo lang('class_code'); ?></th>
                                    <th><?php echo lang('class_name'); ?></th>
                                    <th><?php echo lang('lecturer'); ?></th>
                                    <th><?php echo lang('semester'); ?></th>
                                    <th><?php echo lang('academic_year'); ?></th>
                                    <th><?php echo lang('actions'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($classes as $class): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($class['code']); ?></td>
                                        <td><?php echo htmlspecialchars($class['name']); ?></td>
                                        <td><?php echo htmlspecialchars($class['lecturer_name'] ?? lang('not_assigned')); ?></td>
                                        <td><?php echo htmlspecialchars($class['semester']); ?></td>
                                        <td><?php echo htmlspecialchars($class['academic_year']); ?></td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-primary edit-class" 
                                                    data-id="<?php echo $class['id']; ?>"
                                                    data-code="<?php echo htmlspecialchars($class['code']); ?>"
                                                    data-name="<?php echo htmlspecialchars($class['name']); ?>"
                                                    data-lecturer-id="<?php echo $class['lecturer_id']; ?>"
                                                    data-semester="<?php echo htmlspecialchars($class['semester']); ?>"
                                                    data-academic-year="<?php echo htmlspecialchars($class['academic_year']); ?>">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-danger delete-class" 
                                                    data-id="<?php echo $class['id']; ?>"
                                                    data-code="<?php echo htmlspecialchars($class['code']); ?>">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Class Modal -->
<div class="modal fade" id="addClassModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="action" value="add">
                
                <div class="modal-header">
                    <h5 class="modal-title"><?php echo lang('add_class'); ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label"><?php echo lang('class_code'); ?></label>
                        <input type="text" class="form-control" name="code" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label"><?php echo lang('class_name'); ?></label>
                        <input type="text" class="form-control" name="name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label"><?php echo lang('lecturer'); ?></label>
                        <select class="form-select" name="lecturer_id">
                            <option value=""><?php echo lang('select_lecturer'); ?></option>
                            <?php foreach ($lecturers as $lecturer): ?>
                                <option value="<?php echo $lecturer['id']; ?>">
                                    <?php echo htmlspecialchars($lecturer['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label"><?php echo lang('semester'); ?></label>
                        <select class="form-select" name="semester" required>
                            <option value="1"><?php echo lang('semester_1'); ?></option>
                            <option value="2"><?php echo lang('semester_2'); ?></option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label"><?php echo lang('academic_year'); ?></label>
                        <input type="text" class="form-control" name="academic_year" 
                               pattern="\d{4}-\d{4}" placeholder="YYYY-YYYY" required>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <?php echo lang('cancel'); ?>
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <?php echo lang('save'); ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Class Modal -->
<div class="modal fade" id="editClassModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id" id="editClassId">
                
                <div class="modal-header">
                    <h5 class="modal-title"><?php echo lang('edit_class'); ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label"><?php echo lang('class_code'); ?></label>
                        <input type="text" class="form-control" name="code" id="editClassCode" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label"><?php echo lang('class_name'); ?></label>
                        <input type="text" class="form-control" name="name" id="editClassName" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label"><?php echo lang('lecturer'); ?></label>
                        <select class="form-select" name="lecturer_id" id="editClassLecturer">
                            <option value=""><?php echo lang('select_lecturer'); ?></option>
                            <?php foreach ($lecturers as $lecturer): ?>
                                <option value="<?php echo $lecturer['id']; ?>">
                                    <?php echo htmlspecialchars($lecturer['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label"><?php echo lang('semester'); ?></label>
                        <select class="form-select" name="semester" id="editClassSemester" required>
                            <option value="1"><?php echo lang('semester_1'); ?></option>
                            <option value="2"><?php echo lang('semester_2'); ?></option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label"><?php echo lang('academic_year'); ?></label>
                        <input type="text" class="form-control" name="academic_year" 
                               id="editClassAcademicYear" pattern="\d{4}-\d{4}" placeholder="YYYY-YYYY" required>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <?php echo lang('cancel'); ?>
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <?php echo lang('save'); ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Class Modal -->
<div class="modal fade" id="deleteClassModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" id="deleteClassId">
                
                <div class="modal-header">
                    <h5 class="modal-title"><?php echo lang('delete_class'); ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                
                <div class="modal-body">
                    <p><?php echo lang('confirm_delete_class'); ?> <strong id="deleteClassCode"></strong>?</p>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <?php echo lang('cancel'); ?>
                    </button>
                    <button type="submit" class="btn btn-danger">
                        <?php echo lang('delete'); ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Edit class
    document.querySelectorAll('.edit-class').forEach(button => {
        button.addEventListener('click', function() {
            document.getElementById('editClassId').value = this.dataset.id;
            document.getElementById('editClassCode').value = this.dataset.code;
            document.getElementById('editClassName').value = this.dataset.name;
            document.getElementById('editClassLecturer').value = this.dataset.lecturerId;
            document.getElementById('editClassSemester').value = this.dataset.semester;
            document.getElementById('editClassAcademicYear').value = this.dataset.academicYear;
            
            new bootstrap.Modal(document.getElementById('editClassModal')).show();
        });
    });
    
    // Delete class
    document.querySelectorAll('.delete-class').forEach(button => {
        button.addEventListener('click', function() {
            document.getElementById('deleteClassId').value = this.dataset.id;
            document.getElementById('deleteClassCode').textContent = this.dataset.code;
            
            new bootstrap.Modal(document.getElementById('deleteClassModal')).show();
        });
    });
});
</script>

<?php require_once '../../includes/footer.php'; ?> 