<?php
$servername = "localhost";
$username = "root";
$password = "";
$database = "sjitadmissionexamination";

$connection = new mysqli(
    $servername,
    $username,
    $password,
    $database
);

if ($connection->connect_error) {
    die("Connection failed: " . $connection->connect_error);
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $batch = trim($_POST["batch_name"] ?? "");

    if ($batch === "") {
        $message = "Please enter a batch name.";
    } else {
        $stmt = $connection->prepare(
            "INSERT IGNORE INTO AdmissionBatches (batch_name)
             VALUES (?)"
        );

        $stmt->bind_param("s", $batch);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            header(
                "Location: /sjitadmissionexamination/index.php?batch=" . urlencode($batch)
            );
            exit;
        } else {
            $message = "This batch already exists. You can select it below.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Start New Student List</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="aura-bg">
    <div class="aura-layer-1"></div>
    <div class="aura-layer-2"></div>

    <div class="aura-content">
        <div class="container my-5">
    <h2>Start New Student List</h2>
    <p>Create a new admission batch without deleting existing records.</p>

    <?php if ($message !== ""): ?>
        <div class="alert alert-warning">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <form method="POST">
        <div class="mb-3">
            <label for="batch_name" class="form-label">
                New Batch Name
            </label>
            <input type="text" name="batch_name" id="batch_name"
                   class="form-control" value="Batch 2027" required>
        </div>

        <button type="submit" class="btn btn-success">
            Create New List
        </button>

        <a href="/sjitadmissionexamination/index.php" class="btn btn-secondary"> 
            Cancel
        </a>
    </form>
</div>
</body>
</html>
