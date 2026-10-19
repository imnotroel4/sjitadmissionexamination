<?php
$servername = "localhost";
$username = "root";
$password = "";
$database = "sjitadmissionexamination";

$connection = new mysqli($servername, $username, $password, $database);

$name = "";
$lastschoolattended = "";
$desiredcourse = "";
$status = "";

$errorMessage = "";
$successMessage = "";

// Determine selected batch initially from GET or POST
$selectedBatch = $_GET['batch'] ?? $_POST['batch'] ?? 'Batch 2026';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST["name"];
    $lastschoolattended = $_POST["lastschoolattended"];
    $desiredcourse = $_POST["desiredcourse"];
    $status = $_POST["status"];
    $selectedBatch = $_POST['batch'] ?? 'Batch 2026';

    do {
        if (empty($name) || empty($lastschoolattended) || empty($desiredcourse) || empty($status)) {
            $errorMessage = "All the fields are required";
            break;
        }

        // Validate batch exists
        $stmt = $connection->prepare("SELECT batch_name FROM AdmissionBatches WHERE batch_name = ?");
        $stmt->bind_param("s", $selectedBatch);
        $stmt->execute();
        if (!$stmt->get_result()->fetch_assoc()) {
            $selectedBatch = 'Batch 2026';
        }

        // Add new student to database
        $sql = "INSERT INTO Attendance (name, lastschoolattended, desiredcourse, status, batch) VALUES (?, ?, ?, ?, ?)";
        $stmt = $connection->prepare($sql);
        $stmt->bind_param("sssss", $name, $lastschoolattended, $desiredcourse, $status, $selectedBatch);
        
        if ($stmt->execute()) {
            header("Location: /sjitadmissionexamination/index.php?batch=" . urlencode($selectedBatch));
            exit;
        } else {
            $errorMessage = "Database error: " . $connection->error;
            break;
        }

    } while (false);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sjit Admission Examination</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>
    <div class="aura-bg">
    <div class="aura-layer-1"></div>
    <div class="aura-layer-2"></div>

    <div class="aura-content">
        <div class="container my-5">
        <h2>New Students</h2>

        <?php
        if (!empty($errorMessage)) {
            echo "
            <div class='alert alert-warning alert-dismissible fade show' role='alert'>
                <strong>$errorMessage</strong>
                <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
            </div>
            ";
        }
        ?>

        <form method="post">
            <div class="row mb-3">
                <label class="col-sm-3 col-form-label">Name</label>
                <div class="col-sm-6">
                    <input type="text" class="form-control" name="name" value="<?php echo htmlspecialchars($name); ?>">
                </div>
            </div>

            <div class="row mb-3">
                <label class="col-sm-3 col-form-label">Last School Attended</label>
                <div class="col-sm-6">
                    <input type="text" class="form-control" name="lastschoolattended" value="<?php echo htmlspecialchars($lastschoolattended); ?>">
                </div>
            </div>

            <div class="row mb-3">
                <label class="col-sm-3 col-form-label">Desired Course</label>
                <div class="col-sm-6">
                    <input type="text" class="form-control" name="desiredcourse" value="<?php echo htmlspecialchars($desiredcourse); ?>">
                </div>
            </div>

            <div class="row mb-3">
                <label class="col-sm-3 col-form-label">Admission Batch</label>
                <div class="col-sm-6">
                    <select name="batch" class="form-select" required>
                        <?php
                        $batches = $connection->query("SELECT batch_name FROM AdmissionBatches ORDER BY id");
                        while ($b = $batches->fetch_assoc()):
                        ?>
                            <option value="<?= htmlspecialchars($b['batch_name']) ?>"
                                <?= $selectedBatch === $b['batch_name'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($b['batch_name']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
            </div>

            <div class="row mb-3">
                <label class="col-sm-3 col-form-label">Status</label>
                <div class="col-sm-6">
                    <input type="text" class="form-control" name="status" value="<?php echo htmlspecialchars($status); ?>">
                </div>
            </div>

            <?php
            if (!empty($successMessage)) {
                echo "
                <div class='row mb-3'>
                    <div class='offset-sm-3 col-sm-6'>
                        <div class='alert alert-success alert-dismissible fade show' role='alert'>
                            <strong>$successMessage</strong>
                            <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
                        </div>
                    </div>
                </div>
                ";
            }
            ?>

            <div class="row mb-3">
                <div class="offset-sm-3 col-sm-3 d-grid">
                    <button type="submit" class="btn btn-primary">Submit</button>
                </div>
                <div class="col-sm-3 d-grid">
                    <a class="btn btn-outline-primary" href="/sjitadmissionexamination/index.php?batch=<?php echo urlencode($selectedBatch); ?>" role="button">Cancel</a>
                </div>
            </div>
        </form>
    </div>
</body>
</html>