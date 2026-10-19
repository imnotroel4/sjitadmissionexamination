
<?php
$servername = "localhost";
$username = "root";
$password = "";
$database = "sjitadmissionexamination";

// Connect to the database
$connection = new mysqli(
    $servername,
    $username,
    $password,
    $database
);

// Check database connection
if ($connection->connect_error) {
    die("Database connection failed: " . $connection->connect_error);
}

// Get the batch name FIRST
$batch = trim($_POST["batch_name"] ?? "");

// Validate the request
if ($_SERVER["REQUEST_METHOD"] !== "POST" || $batch === "") {
    die("Invalid batch selection.");
}

// Start a transaction
$connection->begin_transaction();

try {
    // Delete attendance records for the selected batch
    $stmt = $connection->prepare(
        "DELETE FROM Attendance WHERE batch = ?"
    );
    $stmt->bind_param("s", $batch);
    $stmt->execute();
    $stmt->close();

    // Delete the selected admission batch
    $stmt = $connection->prepare(
        "DELETE FROM AdmissionBatches WHERE batch_name = ?"
    );
    $stmt->bind_param("s", $batch);
    $stmt->execute();
    $stmt->close();

    // Commit changes
    $connection->commit();

    $connection->close();

    header(
        "Location: /sjitadmissionexamination/index.php?message=batch_deleted"
    );
    exit;

} catch (mysqli_sql_exception $e) {
    // Undo changes if an error occurs
    $connection->rollback();
    $connection->close();

    error_log("Batch deletion failed: " . $e->getMessage());
    die("Failed to delete the batch. Please try again.");
}
?>
