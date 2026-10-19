<?php
$servername = "localhost";
$username = "root";
$password = "";
$database = "sjitadmissionexamination";

$connection = new mysqli($servername, $username, $password, $database);

$no = "";
$name = "";
$lastschoolattended = "";
$desiredcourse = "";
$status = "";

$errorMessage = "";
$successMessage = "";

if ( $_SERVER['REQUEST_METHOD'] == 'GET' ) {
    //GET method: Show the data of the student

    if ( !isset($_GET["no"]) ) {
        header("location: /sjitadmissionexamination/index.php");
        exit;
    }

    $no = $_GET["no"];

    $sql = "SELECT * FROM attendance WHERE no=$no";
    $result = $connection->query($sql);
    $row = $result->fetch_assoc();

    if (!$row) {
        header("location: /sjitadmissionexamination/index.php");
        exit;
    }

    $name = $row["name"];
    $lastschoolattended = $row["lastschoolattended"];
    $desiredcourse = $row["desiredcourse"];
    $status = $row["status"];
}
else {
    //POST method: Update the data of the client

    $no = $_POST["no"] ??"";
    $name = $_POST["name"];
    $lastschoolattended = $_POST["lastschoolattended"];
    $desiredcourse = $_POST["desiredcourse"];
    $status = $_POST["status"];

    do {
        if ( empty($no) || empty($name) || empty($lastschoolattended) || empty($desiredcourse) || empty($status) ) {
            $errorMessage = "All the fields are required";
            break;
        }

        $sql = "UPDATE attendance " .
               "SET name = '$name', lastschoolattended = '$lastschoolattended', desiredcourse = '$desiredcourse', status = '$status' " .
               "WHERE no = $no";

            $result = $connection->query($sql);

            if (!$result) {
                $errorMessage = "Invalid query: " . $connection->error;
                break;
            }

            $successMessage = "Student updated correctly";

            header("location: /sjitadmissionexamination/index.php");
            exit;

        
    }   while (false);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SJIT Admission Examination</title>
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
        <h2>Edit Attendance - SJIT Admission Examination</h2>

        <?php
        if ( !empty($errorMessage) ) {
            echo "
            <div class='alert alert-warning alert-dismissible fade show' role='alert'>
                <strong>$errorMessage</strong>
                <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
            </div>
            ";
        }
        ?>

        <form method="post">
            <input type="hidden" name="no" value="<?php echo $no; ?>">
            <div class="row mb-3">
                <label class="col-sm-3 col-form-label">Name</label>
                <div class="col-sm-6">
                    <input type="text" class="form-control" name="name" value="<?php echo $name; ?>">
                </div>
            </div>

            <div class="row mb-3">
                <label class="col-sm-3 col-form-label">Last School Attended</label>
                <div class="col-sm-6">
                    <input type="text" class="form-control" name="lastschoolattended" value="<?php echo $lastschoolattended; ?>">
                </div>
            </div>

            <div class="row mb-3">
                <label class="col-sm-3 col-form-label">Desired Course</label>
                <div class="col-sm-6">
                    <input type="text" class="form-control" name="desiredcourse" value="<?php echo $desiredcourse; ?>">
                </div>
            </div>

            <div class="row mb-3">
                <label class="col-sm-3 col-form-label">Status</label>
                <div class="col-sm-6">
                    <input type="text" class="form-control" name="status" value="<?php echo $status; ?>">
                </div>
            </div>

            <?php
            if ( !empty($successMessage)) {
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
                    <a class="btn btn-outline-primary" href="/sjitadmissionexamination/index.php" role="button">Cancel</a>
                </div>
            </div>

        </form>
    </div>
    
</body>
</html>