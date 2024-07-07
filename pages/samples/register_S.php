<?php
// Include the database helper file
require_once '../../db.helper.php';

// Fetch Distinct Batch Data
$batchData = [];
$sqlBatchID = "SELECT DISTINCT BatchID FROM batch_registers";
$resultBatchID = $conn->query($sqlBatchID);
if ($resultBatchID->num_rows > 0) {
    while($row = $resultBatchID->fetch_assoc()) {
        $batchData[] = $row['BatchID'];
    }
}

// Check if the form data is set
if (isset($_POST['first_name']) && isset($_POST['last_name']) && isset($_POST['email']) && isset($_POST['password']) && isset($_POST['reg_no']) && isset($_POST['phone_no']) && isset($_POST['batch_ID'])) {
    // Get the form data from the POST request
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $reg_no = $_POST['reg_no'];
    $phone_no = $_POST['phone_no'];
    $batch_ID = $_POST['batch_ID'];

    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Prepare and execute the SQL query to insert the new user
    $sql = "INSERT INTO STUDENT_TABLE (FIRST_NAME, LAST_NAME, EMAIL, Password, REG_NO, PHONE_NO, BATCH_ID) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssi", $first_name, $last_name, $email, $hashed_password, $reg_no, $phone_no, $batch_ID);

    if ($stmt->execute()) {
        // Redirect to the login page
        header("Location: login_S.php");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }

    // Close the statement and connection
    $stmt->close();
    $conn->close();
}

// Handle AJAX request to fetch registration numbers based on batch ID
if (isset($_POST['action']) && $_POST['action'] === 'fetchRegNumbers' && isset($_POST['batch_ID'])) {
    $batch_ID = $_POST['batch_ID'];
    $registerData = [];
    $sqlRegisterNo = "SELECT RegisterNo FROM batch_registers WHERE BatchID = ?";
    $stmt = $conn->prepare($sqlRegisterNo);
    $stmt->bind_param("i", $batch_ID);
    $stmt->execute();
    $resultRegisterNo = $stmt->get_result();
    if ($resultRegisterNo->num_rows > 0) {
        while($row = $resultRegisterNo->fetch_assoc()) {
            $registerData[] = $row['RegisterNo'];
        }
    }
    echo json_encode($registerData);
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Marks-Manger Student Registration Form</title>
    <!-- plugins:css -->
    <link rel="stylesheet" href="../../vendors/mdi/css/materialdesignicons.min.css">
    <link rel="stylesheet" href="../../vendors/base/vendor.bundle.base.css">
    <!-- endinject -->
    <!-- plugin css for this page -->
    <!-- End plugin css for this page -->
    <!-- inject:css -->
    <link rel="stylesheet" href="../../css/style.css">
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <!-- endinject -->
    <link rel="shortcut icon" href="../../images/favicon.png" />
</head>
<body>
    <div class="container-scroller">
        <div class="container-fluid page-body-wrapper full-page-wrapper">
            <div class="content-wrapper d-flex align-items-stretch auth auth-img-bg">
                <div class="row flex-grow">
                    <div class="col-lg-6 d-flex align-items-center justify-content-center">
                        <div class="auth-form-transparent text-left p-3">
                            <div class="brand-logo">
                            </div>
                            <h4>New here?</h4>
                            <h6 class="font-weight-light">Join us today! It takes only a few steps</h6>
                            <form class="pt-3" action="register_S.php" method="post">
                                <div class="form-group">
                                    <label>First Name</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend bg-transparent">
                                            <span class="input-group-text bg-transparent border-right-0">
                                                <i class="mdi mdi-account-outline text-primary"></i>
                                            </span>
                                        </div>
                                        <input type="text" class="form-control form-control-lg border-left-0" name="first_name" placeholder="First Name" required>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>Last Name</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend bg-transparent">
                                            <span class="input-group-text bg-transparent border-right-0">
                                                <i class="mdi mdi-account-outline text-primary"></i>
                                            </span>
                                        </div>
                                        <input type="text" class="form-control form-control-lg border-left-0" name="last_name" placeholder="Last Name" required>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>Email</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend bg-transparent">
                                            <span class="input-group-text bg-transparent border-right-0">
                                                <i class="mdi mdi-email-outline text-primary"></i>
                                            </span>
                                        </div>
                                        <input type="email" class="form-control form-control-lg border-left-0" name="email" placeholder="Email" required>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>Password</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend bg-transparent">
                                            <span class="input-group-text bg-transparent border-right-0">
                                                <i class="mdi mdi-lock-outline text-primary"></i>
                                            </span>
                                        </div>
                                        <input type="password" class="form-control form-control-lg border-left-0" name="password" id="exampleInputPassword" placeholder="Password" required>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>Batch ID</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend bg-transparent">
                                            <span class="input-group-text bg-transparent border-right-0">
                                                <i class="mdi mdi-account-outline text-primary"></i>
                                            </span>
                                        </div>
                                        <select class="form-control form-control-lg border-left-0 select2" name="batch_ID" id="batch_ID" required>
                                            <option value="" disabled selected>Select Batch ID</option>
                                            <?php foreach ($batchData as $batchID) { ?>
                                                <option value="<?php echo $batchID; ?>"><?php echo $batchID; ?></option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>Registration Number</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend bg-transparent">
                                            <span class="input-group-text bg-transparent border-right-0">
                                                <i class="mdi mdi-account-outline text-primary"></i>
                                            </span>
                                        </div>
                                        <select class="form-control form-control-lg border-left-0 select2" name="reg_no" id="reg_no" required>
                                            <option value="" disabled selected>Select Registration Number</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>Phone Number</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend bg-transparent">
                                            <span class="input-group-text bg-transparent border-right-0">
                                                <i class="mdi mdi-phone-outline text-primary"></i>
                                            </span>
                                        </div>
                                        <input type="text" class="form-control form-control-lg border-left-0" name="phone_no" placeholder="Phone Number" required>
                                    </div>
                                </div>
                                
                                <div class="mb-4">
                                    <div class="form-check">
                                        <label class="form-check-label text-muted">
                                            <input type="checkbox" class="form-check-input" required>
                                            I agree to all Terms & Conditions
                                        </label>
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <button type="submit" class="btn btn-block btn-primary btn-lg font-weight-medium auth-form-btn">SIGN UP</button>
                                </div>
                                <div class="text-center mt-4 font-weight-light">
                                    Already have an account? <a href="login_S.php" class="text-primary">Login</a>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="col-lg-6 register-half-bg d-flex flex-row">
                    </div>
                </div>
            </div>
            <!-- content-wrapper ends -->
        </div>
        <!-- page-body-wrapper ends -->
    </div>
    <!-- container-scroller -->
    <!-- plugins:js -->
    <script src="../../vendors/base/vendor.bundle.base.js"></script>
    <!-- endinject -->
    <!-- inject:js -->
    <script src="../../js/off-canvas.js"></script>
    <script src="../../js/hoverable-collapse.js"></script>
    <script src="../../js/template.js"></script>
    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.select2').select2();

            $('#batch_ID').on('change', function() {
                var batch_ID = $(this).val();
                console.log("Batch ID selected: " + batch_ID);
                $.ajax({
                    url: 'register_S.php',
                    type: 'POST',
                    data: {
                        action: 'fetchRegNumbers',
                        batch_ID: batch_ID
                    },
                    success: function(response) {
                        console.log("Response received: " + response);
                        var data = JSON.parse(response);
                        var options = '<option value="" disabled selected>Select Registration Number</option>';
                        for (var i = 0; i < data.length; i++) {
                            options += '<option value="' + data[i] + '">' + data[i] + '</option>';
                        }
                        $('#reg_no').html(options).trigger('change');
                    },
                    error: function(xhr, status, error) {
                        console.error("AJAX error: " + status + " - " + error);
                    }
                });
            });
        });
    </script>
    <!-- endinject -->
</body>
</html>
