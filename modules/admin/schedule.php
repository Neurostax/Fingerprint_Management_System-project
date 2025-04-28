<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';

requireRole('admin');

$message = '';
$error = '';

// Handle Add/Edit/Delete for Classes
if (isset($_POST['add_class'])) {
    $name = trim($_POST['name']);
    $code = trim($_POST['code']);
    $lecturer_id = $_POST['lecturer_id'];
    $date = $_POST['date'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];
    $location = $_POST['location'];
    if ($name && $code && $lecturer_id && $date && $start_time && $end_time) {
        $stmt = $pdo->prepare('INSERT INTO classes (name, code, lecturer_id, date, start_time, end_time, location) VALUES (?, ?, ?, ?, ?, ?, ?)');
        if ($stmt->execute([$name, $code, $lecturer_id, $date, $start_time, $end_time, $location])) {
            $message = 'Class added successfully!';
        } else {
            $error = 'Failed to add class.';
        }
    } else {
        $error = 'All fields are required.';
    }
}
if (isset($_POST['edit_class'])) {
    $id = $_POST['id'];
    $name = trim($_POST['name']);
    $code = trim($_POST['code']);
    $lecturer_id = $_POST['lecturer_id'];
    $date = $_POST['date'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];
    $location = $_POST['location'];
    $stmt = $pdo->prepare('UPDATE classes SET name=?, code=?, lecturer_id=?, date=?, start_time=?, end_time=?, location=? WHERE id=?');
    if ($stmt->execute([$name, $code, $lecturer_id, $date, $start_time, $end_time, $location, $id])) {
        $message = 'Class updated successfully!';
    } else {
        $error = 'Failed to update class.';
    }
}
if (isset($_POST['delete_class'])) {
    $id = $_POST['id'];
    $stmt = $pdo->prepare('DELETE FROM classes WHERE id=?');
    if ($stmt->execute([$id])) {
        $message = 'Class deleted.';
    } else {
        $error = 'Failed to delete class.';
    }
}

// Handle Add/Edit/Delete for CATs
if (isset($_POST['add_cat'])) {
    $class_id = $_POST['class_id'];
    $date = $_POST['date'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];
    $venue = $_POST['venue'];
    $stmt = $pdo->prepare('INSERT INTO cats (class_id, date, start_time, end_time, venue) VALUES (?, ?, ?, ?, ?)');
    if ($stmt->execute([$class_id, $date, $start_time, $end_time, $venue])) {
        $message = 'CAT added successfully!';
    } else {
        $error = 'Failed to add CAT.';
    }
}
if (isset($_POST['edit_cat'])) {
    $id = $_POST['id'];
    $class_id = $_POST['class_id'];
    $date = $_POST['date'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];
    $venue = $_POST['venue'];
    $stmt = $pdo->prepare('UPDATE cats SET class_id=?, date=?, start_time=?, end_time=?, venue=? WHERE id=?');
    if ($stmt->execute([$class_id, $date, $start_time, $end_time, $venue, $id])) {
        $message = 'CAT updated successfully!';
    } else {
        $error = 'Failed to update CAT.';
    }
}
if (isset($_POST['delete_cat'])) {
    $id = $_POST['id'];
    $stmt = $pdo->prepare('DELETE FROM cats WHERE id=?');
    if ($stmt->execute([$id])) {
        $message = 'CAT deleted.';
    } else {
        $error = 'Failed to delete CAT.';
    }
}

// Handle Add/Edit/Delete for Exams
if (isset($_POST['add_exam'])) {
    $class_id = $_POST['class_id'];
    $date = $_POST['date'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];
    $venue = $_POST['venue'];
    $stmt = $pdo->prepare('INSERT INTO exams (class_id, date, start_time, end_time, venue) VALUES (?, ?, ?, ?, ?)');
    if ($stmt->execute([$class_id, $date, $start_time, $end_time, $venue])) {
        $message = 'Exam added successfully!';
    } else {
        $error = 'Failed to add exam.';
    }
}
if (isset($_POST['edit_exam'])) {
    $id = $_POST['id'];
    $class_id = $_POST['class_id'];
    $date = $_POST['date'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];
    $venue = $_POST['venue'];
    $stmt = $pdo->prepare('UPDATE exams SET class_id=?, date=?, start_time=?, end_time=?, venue=? WHERE id=?');
    if ($stmt->execute([$class_id, $date, $start_time, $end_time, $venue, $id])) {
        $message = 'Exam updated successfully!';
    } else {
        $error = 'Failed to update exam.';
    }
}
if (isset($_POST['delete_exam'])) {
    $id = $_POST['id'];
    $stmt = $pdo->prepare('DELETE FROM exams WHERE id=?');
    if ($stmt->execute([$id])) {
        $message = 'Exam deleted.';
    } else {
        $error = 'Failed to delete exam.';
    }
}

// Fetch data for forms
$lecturers = $pdo->query('SELECT id, name FROM users WHERE role = "lecturer"')->fetchAll();
$classes = $pdo->query('SELECT id, name FROM classes')->fetchAll();
$all_classes = $pdo->query('SELECT * FROM classes ORDER BY date DESC')->fetchAll();
$all_cats = $pdo->query('SELECT cats.*, classes.name as class_name FROM cats JOIN classes ON cats.class_id = classes.id ORDER BY cats.date DESC')->fetchAll();
$all_exams = $pdo->query('SELECT exams.*, classes.name as class_name FROM exams JOIN classes ON exams.class_id = classes.id ORDER BY exams.date DESC')->fetchAll();

$page_title = 'Schedule Management';
require_once __DIR__ . '/../../includes/header.php';
?>
<div class="container mt-4">
    <a href="dashboard.php" class="btn btn-secondary mb-3"><i class="fas fa-arrow-left me-1"></i> Back to Dashboard</a>
    <h2>Schedule Management</h2>
    <?php if ($message): ?>
        <div class="alert alert-success"><?php echo $message; ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>
    <ul class="nav nav-tabs mb-3" id="scheduleTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="classes-tab" data-bs-toggle="tab" data-bs-target="#classes" type="button" role="tab">Classes</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="cats-tab" data-bs-toggle="tab" data-bs-target="#cats" type="button" role="tab">CATs</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="exams-tab" data-bs-toggle="tab" data-bs-target="#exams" type="button" role="tab">Exams</button>
        </li>
    </ul>
    <div class="tab-content" id="scheduleTabsContent">
        <!-- Classes Tab -->
        <div class="tab-pane fade show active" id="classes" role="tabpanel">
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title">Add Class</h5>
                    <form method="post" class="row g-3">
                        <div class="col-md-3">
                            <input type="text" name="name" class="form-control" placeholder="Class Name" required>
                        </div>
                        <div class="col-md-2">
                            <input type="text" name="code" class="form-control" placeholder="Code" required>
                        </div>
                        <div class="col-md-2">
                            <select name="lecturer_id" class="form-control" required>
                                <option value="">Select Lecturer</option>
                                <?php foreach ($lecturers as $lect): ?>
                                    <option value="<?php echo $lect['id']; ?>"><?php echo htmlspecialchars($lect['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <input type="date" name="date" class="form-control" required>
                        </div>
                        <div class="col-md-1">
                            <input type="time" name="start_time" class="form-control" required>
                        </div>
                        <div class="col-md-1">
                            <input type="time" name="end_time" class="form-control" required>
                        </div>
                        <div class="col-md-1">
                            <input type="text" name="location" class="form-control" placeholder="Location">
                        </div>
                        <div class="col-md-12 mt-2">
                            <button type="submit" name="add_class" class="btn btn-primary">Add Class</button>
                        </div>
                    </form>
                </div>
            </div>
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">All Classes</h5>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Code</th>
                                    <th>Lecturer</th>
                                    <th>Date</th>
                                    <th>Start</th>
                                    <th>End</th>
                                    <th>Location</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($all_classes as $class): ?>
                                    <tr>
                                        <td><?php echo $class['id']; ?></td>
                                        <td><?php echo htmlspecialchars($class['name']); ?></td>
                                        <td><?php echo htmlspecialchars($class['code']); ?></td>
                                        <td><?php echo htmlspecialchars($class['lecturer_id']); ?></td>
                                        <td><?php echo htmlspecialchars($class['date']); ?></td>
                                        <td><?php echo htmlspecialchars($class['start_time']); ?></td>
                                        <td><?php echo htmlspecialchars($class['end_time']); ?></td>
                                        <td><?php echo htmlspecialchars($class['location']); ?></td>
                                        <td>
                                            <form method="post" style="display:inline-block" onsubmit="return confirm('Are you sure you want to delete this class?');">
                                                <input type="hidden" name="id" value="<?php echo $class['id']; ?>">
                                                <button type="submit" name="delete_class" class="btn btn-danger btn-sm">Delete</button>
                                            </form>
                                            <!-- Edit functionality can be added here -->
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <!-- CATs Tab -->
        <div class="tab-pane fade" id="cats" role="tabpanel">
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title">Add CAT</h5>
                    <form method="post" class="row g-3">
                        <div class="col-md-3">
                            <select name="class_id" class="form-control" required>
                                <option value="">Select Class</option>
                                <?php foreach ($classes as $c): ?>
                                    <option value="<?php echo $c['id']; ?>"><?php echo htmlspecialchars($c['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <input type="date" name="date" class="form-control" required>
                        </div>
                        <div class="col-md-2">
                            <input type="time" name="start_time" class="form-control" required>
                        </div>
                        <div class="col-md-2">
                            <input type="time" name="end_time" class="form-control" required>
                        </div>
                        <div class="col-md-3">
                            <input type="text" name="venue" class="form-control" placeholder="Venue" required>
                        </div>
                        <div class="col-md-12 mt-2">
                            <button type="submit" name="add_cat" class="btn btn-primary">Add CAT</button>
                        </div>
                    </form>
                </div>
            </div>
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">All CATs</h5>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Class</th>
                                    <th>Date</th>
                                    <th>Start</th>
                                    <th>End</th>
                                    <th>Venue</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($all_cats as $cat): ?>
                                    <tr>
                                        <td><?php echo $cat['id']; ?></td>
                                        <td><?php echo htmlspecialchars($cat['class_name']); ?></td>
                                        <td><?php echo htmlspecialchars($cat['date']); ?></td>
                                        <td><?php echo htmlspecialchars($cat['start_time']); ?></td>
                                        <td><?php echo htmlspecialchars($cat['end_time']); ?></td>
                                        <td><?php echo htmlspecialchars($cat['venue']); ?></td>
                                        <td>
                                            <form method="post" style="display:inline-block" onsubmit="return confirm('Are you sure you want to delete this CAT?');">
                                                <input type="hidden" name="id" value="<?php echo $cat['id']; ?>">
                                                <button type="submit" name="delete_cat" class="btn btn-danger btn-sm">Delete</button>
                                            </form>
                                            <!-- Edit functionality can be added here -->
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <!-- Exams Tab -->
        <div class="tab-pane fade" id="exams" role="tabpanel">
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title">Add Exam</h5>
                    <form method="post" class="row g-3">
                        <div class="col-md-3">
                            <select name="class_id" class="form-control" required>
                                <option value="">Select Class</option>
                                <?php foreach ($classes as $c): ?>
                                    <option value="<?php echo $c['id']; ?>"><?php echo htmlspecialchars($c['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <input type="date" name="date" class="form-control" required>
                        </div>
                        <div class="col-md-2">
                            <input type="time" name="start_time" class="form-control" required>
                        </div>
                        <div class="col-md-2">
                            <input type="time" name="end_time" class="form-control" required>
                        </div>
                        <div class="col-md-3">
                            <input type="text" name="venue" class="form-control" placeholder="Venue" required>
                        </div>
                        <div class="col-md-12 mt-2">
                            <button type="submit" name="add_exam" class="btn btn-primary">Add Exam</button>
                        </div>
                    </form>
                </div>
            </div>
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">All Exams</h5>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Class</th>
                                    <th>Date</th>
                                    <th>Start</th>
                                    <th>End</th>
                                    <th>Venue</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($all_exams as $exam): ?>
                                    <tr>
                                        <td><?php echo $exam['id']; ?></td>
                                        <td><?php echo htmlspecialchars($exam['class_name']); ?></td>
                                        <td><?php echo htmlspecialchars($exam['date']); ?></td>
                                        <td><?php echo htmlspecialchars($exam['start_time']); ?></td>
                                        <td><?php echo htmlspecialchars($exam['end_time']); ?></td>
                                        <td><?php echo htmlspecialchars($exam['venue']); ?></td>
                                        <td>
                                            <form method="post" style="display:inline-block" onsubmit="return confirm('Are you sure you want to delete this exam?');">
                                                <input type="hidden" name="id" value="<?php echo $exam['id']; ?>">
                                                <button type="submit" name="delete_exam" class="btn btn-danger btn-sm">Delete</button>
                                            </form>
                                            <!-- Edit functionality can be added here -->
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
<?php require_once __DIR__ . '/../../includes/footer.php'; ?> 