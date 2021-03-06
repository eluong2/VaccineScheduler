<?php
$dbhost = "localhost";
$dbuser = "root";
$dbpwd = "MySQLServer";
$dbname = "Vaccination";

$conn = new mysqli($dbhost, $dbuser, $dbpwd, $dbname);
if($conn->connect_error)
{
    echo "Error: could not connect to the DB";
    exit;
}
// Initialize the session
session_start();

// Check if the user is already logged in, if yes then redirect him to welcome page
if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true){
    switch($_SESSION["access"]){
      case 3:
      header("location: admin.php");
      break;
      case 2:
      header("location: nurse.php");
      break;
      case 1:
      header("location: patient.php");
      break;
    }
}


// Define variables and initialize with empty values
$username = $password = $access= "";
$username_err = $password_err = $login_err = "";

// Processing form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST"){

    // Check if username is empty
    if(empty(trim($_POST["username"]))){
        $username_err = "Please enter username.";
    } else{
        $username = trim($_POST["username"]);
    }

    // Check if password is empty
    if(empty(trim($_POST["password"]))){
        $password_err = "Please enter your password.";
    } else{
        $password = trim($_POST["password"]);
    }

    // Validate credentials
    $id = 0;
    if(empty($username_err) && empty($password_err)){
        // Prepare a select statement
        $sql = "SELECT username, password FROM admin WHERE username = '";
        $sql .= $username."'";
        echo $sql;
        $result = $conn->query($sql);
        if($result->num_rows == 0){
          $sql = "SELECT username, password, EmployeeID FROM nurse WHERE username = '";
          $sql .= $username."'";
          echo $sql;
          $result = $conn->query($sql);
          if($result->num_rows == 0){
            $sql = "SELECT username, password, UserID FROM patient WHERE username = '";
            $sql .= $username."'";
            echo $sql;
            $result = $conn->query($sql);
            if($result->num_rows == 0){
              echo "here </br>";
               $login_err = "Invalid username.";
            } else{
              $access = "patient";
            }
          } else{
            $access = "nurse";
          }
        } else{
          $access = "admin";
        }
        if(empty($login_err)){
          $row = $result->fetch_assoc();
          $hashed = $row['password'];
          if(isset($row['EmployeeID'])){
            $id = $row['EmployeeID'];
          } elseif(isset($row['UserID'])){
            $id = $row['UserID'];
          }
          echo $hashed;
        }
        if(password_verify($password, $hashed)){
              $_SESSION["loggedin"] = true;
              $_SESSION["username"] = $username;

              if($access == "admin") $_SESSION["access"] = 3;
              elseif ($access == "nurse") $_SESSION["access"] = 2;
              elseif ($access == "patient") $_SESSION["access"] = 1;
              switch($_SESSION["access"]){
                case 3:
                header("location: admin.php");
                break;
                case 2:
                $_SESSION["id"] = $id;
                header("location: nurse.php");
                break;
                case 1:
                $_SESSION["id"] = $id;
                header("location: patient.php");
                break;
                default:
                header("location: welcome.php");
                break;

              }
              exit;
        } else {
          $login_err = "Invalid password.";
        }
      }


}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
</head>
<body>
    <div class="wrapper">
        <h2>Login</h2>
        <p>Please fill in your credentials to login.</p>

        <?php
        if(!empty($login_err)){
            echo '<div>' . $login_err . '</div>';
        }
        ?>

        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" class="form-control <?php echo (!empty($username_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $username; ?>">
                <span class="invalid-feedback"><?php echo $username_err; ?></span>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" class="form-control <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>">
                <span class="invalid-feedback"><?php echo $password_err; ?></span>
            </div>
            <div class="form-group">
                <input type="submit" class="btn btn-primary" value="Login">
            </div>
            <p>Don't have an account? <a href="PatientForm.php">Sign up now</a>.</p>
        </form>
    </div>
    <a href="logout.php" class="btn btn-danger ml-3">Sign Out of Your Account</a>

</body>
</html>
