<?php
if ( isset($_GET["no"]) ) {
    $no = $_GET["no"];

    if ( isset($_POST["confirm"]) ) {
        $servername = "localhost";
        $username = "root";
        $password = "";
        $database = "sjitadmissionexamination";

        $connection = new mysqli($servername, $username, $password, $database);

        $sql = "DELETE FROM attendance WHERE no=$no";
        $connection->query($sql);

        header("location: /sjitadmissionexamination/index.php");
        exit;
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Confirm Deletion</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<div class="aura-bg">
    <div class="aura-layer-1"></div>
    <div class="aura-layer-2"></div>

    <div class="aura-content">
        <div class="container my-5">
    <div class="alert alert-warning" role="alert">
        <h4 class="alert-heading">Are you sure?</h4>
        <p>Do you really want to delete client ID <strong><?php echo htmlspecialchars($no); ?></strong>? This action cannot be undone.</p>
        <hr>
        <form method="post" class="d-inline">
            <button type="submit" name="confirm" class="btn btn-danger">Yes, Delete</button>
        </form>
        <a href="/sjitadmissionexamination/index.php" class="btn btn-secondary">Cancel</a>
    </div>
</body>
</html>
<?php
    exit;
} else {
    header("location: /sjitadmissionexamination/index.php");
    exit;
}
?>