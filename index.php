
<?php
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $connection = new mysqli(
        "localhost",
        "root",
        "",
        "sjitadmissionexamination"
    );

    $connection->set_charset("utf8mb4");

    // Get all available admission batches
    $batchResult = $connection->query(
        "SELECT batch_name FROM AdmissionBatches ORDER BY id"
    );

    $batches = $batchResult->fetch_all(MYSQLI_ASSOC);
    $batchNames = array_column($batches, "batch_name");

    // Use the requested batch only if it exists
    $requestedBatch = trim($_GET["batch"] ?? "");

    if (
        $requestedBatch !== "" &&
        in_array($requestedBatch, $batchNames, true)
    ) {
        $batch = $requestedBatch;
    } else {
        // Otherwise, select the first available batch
        $batch = $batchNames[0] ?? "";
    }

    // Initialize result variables
    $students = [];
    $summary = [];
    $totalStudents = 0;

    // Get students and course summary only if a batch exists
    if ($batch !== "") {
        // Get students in the selected batch
        $stmt = $connection->prepare(
            "SELECT * FROM Attendance
             WHERE batch = ?
             ORDER BY no ASC"
        );

        $stmt->bind_param("s", $batch);
        $stmt->execute();

        $students = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        // Get course enrollment summary
        $summaryStmt = $connection->prepare(
            "SELECT desiredcourse, COUNT(*) AS total_students
             FROM Attendance
             WHERE batch = ?
             GROUP BY desiredcourse
             ORDER BY total_students DESC"
        );

        $summaryStmt->bind_param("s", $batch);
        $summaryStmt->execute();

        $summary = $summaryStmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $summaryStmt->close();

        // Calculate total examinees
        foreach ($summary as $row) {
            $totalStudents += (int) $row["total_students"];
        }
    }

    $connection->close();

} catch (mysqli_sql_exception $e) {
    error_log("Index database error: " . $e->getMessage());
    http_response_code(500);
    die("A database error occurred. Please try again later.");
}

// Escape output safely
function e($value) {
    return htmlspecialchars(
        (string) ($value ?? ""),
        ENT_QUOTES,
        "UTF-8"
    );
}

// Check for redirect messages
$message = $_GET["message"] ?? "";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>SJIT Admission Examination</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>

<body>
<div class="aura-bg">
    <div class="aura-layer-1"></div>
    <div class="aura-layer-2"></div>

    <div class="aura-content">
        <div class="container my-5">

    <h2>List of Students</h2>

    <?php if ($message === "batch_deleted"): ?>
        <div class="alert alert-success alert-dismissible fade show"
             role="alert">
            Admission batch deleted successfully.
            <button type="button" class="btn-close"
                    data-bs-dismiss="alert"
                    aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="d-flex flex-wrap gap-2 mb-3">

        <?php if ($batch !== ""): ?>
            <a class="btn btn-primary"
               href="create.php?batch=<?= urlencode($batch) ?>">
                New Student
            </a>
        <?php endif; ?>

        <a class="btn btn-success" href="start_new_list.php">
            Start New List
        </a>

    </div>

    <?php if (count($batches) > 0): ?>

        
<form method="GET" action="index.php" class="mb-3">
    <label for="batch" class="form-label">
        Select Admission Batch
    </label>

    <div class="d-flex flex-wrap gap-2 align-items-center">

        <!-- Batch Dropdown -->
        <select name="batch"
                id="batch"
                class="form-select"
                style="flex: 1; min-width: 220px;">

            <?php foreach ($batches as $b): ?>
                <option
                    value="<?= e($b["batch_name"]) ?>"
                    <?= $batch === $b["batch_name"] ? "selected" : "" ?>
                >
                    <?= e($b["batch_name"]) ?>
                </option>
            <?php endforeach; ?>

        </select>

        <!-- View List Button -->
        <button type="submit" class="btn btn-secondary">
            View List
        </button>

        <!-- Delete Selected Batch Button -->
        <button
            type="submit"
            form="deleteBatchForm"
            class="btn btn-danger"
            onclick="return confirm('Delete the selected batch and ALL its students? This action cannot be undone.');"
        >
            Delete Selected Batch
        </button>

    </div>
</form>

<!-- Separate POST form for deleting the batch -->
<form id="deleteBatchForm"
      method="POST"
      action="deletebatch.php">

    <input type="hidden"
           name="batch_name"
           id="delete_batch_name"
           value="<?= e($batch) ?>">

</form>

<script>
document.getElementById("batch").addEventListener("change", function () {
    document.getElementById("delete_batch_name").value = this.value;
});
</script>


        <h4><?= e($batch) ?></h4>

        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>NO</th>
                        <th>Name</th>
                        <th>Last School Attended</th>
                        <th>Desired Course</th>
                        <th>Status</th>
                        <th>Created At</th>
                        <th>Action</th>
                    </tr>
                </thead>

                <tbody>
                <?php if (count($students) > 0): ?>
                    <?php $i = 1; ?>

                    <?php foreach ($students as $row): ?>
                        <tr>
                            <td><?= $i++ ?></td>
                            <td><?= e($row["name"] ?? "") ?></td>
                            <td><?= e($row["lastschoolattended"] ?? "") ?></td>
                            <td><?= e($row["desiredcourse"] ?? "") ?></td>
                            <td><?= e($row["status"] ?? "") ?></td>
                            <td>
                                <?= e($row["created_at"] ?? "-") ?>
                            </td>
                            <td>
                                <div class="d-flex gap-1">
                                    <a
                                        class="btn btn-primary btn-sm"
                                        href="edit.php?no=<?= (int) $row["no"] ?>"
                                    >
                                        Edit
                                    </a>

                                    <a
                                        class="btn btn-danger btn-sm"
                                        href="delete.php?no=<?= (int) $row["no"] ?>&amp;batch=<?= urlencode($batch) ?>"
                                        onclick="return confirm('Delete this student?');"
                                    >
                                        Delete
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>

                <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center">
                            No students in this batch yet.
                            Click New Student to add an examinee.
                        </td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>

        <h2 class="mt-5">Desired Course Enrollment Summary</h2>

        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead class="table-dark">
                    <tr>
                        <th>No.</th>
                        <th>Desired Course</th>
                        <th>Number of Examinees</th>
                    </tr>
                </thead>

                <tbody>
                <?php if (count($summary) > 0): ?>
                    <?php $n = 1; ?>

                    <?php foreach ($summary as $row): ?>
                        <tr>
                            <td><?= $n++ ?></td>
                            <td><?= e($row["desiredcourse"] ?? "") ?></td>
                            <td><?= (int) $row["total_students"] ?></td>
                        </tr>
                    <?php endforeach; ?>

                <?php else: ?>
                    <tr>
                        <td colspan="3" class="text-center">
                            No course enrollment records for this batch.
                        </td>
                    </tr>
                <?php endif; ?>
                </tbody>

                <tfoot>
                    <tr class="table-primary">
                        <th colspan="2">Total Examinees:</th>
                        <th><?= $totalStudents ?></th>
                    </tr>
                </tfoot>
            </table>
        </div>

    <?php else: ?>

        <div class="alert alert-info">
            No admission batches are available. Click
            <strong>Start New List</strong> to create a new batch.
        </div>

    <?php endif; ?>

</div>

<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js">
</script>
</body>
</html>
