
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Malware Scanner</title>

    <!-- Need for Bootstrap's related JavaScript plugins -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
    <script src="bootstrap.min.js"></script>
    <link href="bootstrap.min.css" rel="stylesheet">



    <style>
        #topContainer
        {
   			background-color: bisque;
   			height:100%;
   			width:100%;
   			background-size:cover;
            text-align: center;
   		}



   		.marginBottom
        {
   			margin-bottom:30px;

   		}


    </style>

</head>
<body data-spy="scroll" data-target=".navbar-collapse">

<?php
require_once('mysql_connection.php');

/*********************************************************************
*
*PHP FUNCTIONS
*
*********************************************************************/

//Sanitizing function
function test_input($input_data)
{
    $input_data = trim($input_data);
    $input_data = stripcslashes($input_data);
    $input_data = htmlspecialchars($input_data);
    return $input_data;
}

//Directing to another page
function page_redirect($location = NULL)
{
    if($location != NULL)
    {
        header("Location: {$location}");
        exit();
    }
}

//start seession if no session
if(!isset($_SESSION))
{
    session_start();
}

$message = "";
$error = "";

//If signout is true, end session.
if(isset($_GET["signout"]) == true)
{
	session_destroy();
    echo "<script type=\"text/javascript\"> alert('You Have Been Successfully Logged Out!'); </script>";
}

//For Salting Password Inputs
$salt1 = "qm&h*";
$salt2 = "pg!@";

/*********************************************************************
*
*Login Authentication
*
*********************************************************************/

if(isset($_POST['submit']) == "Sign In" && isset($_POST['login_email']) && isset($_POST['login_password']))
{
    //Sanitizing login email and password inputed values before send to database
	$login_email = $database->mysql_prep($_POST['login_email']);
	$login_password = $database->mysql_prep($_POST['login_password']);

    //Check if the email matched with admin's email in the database
    $sqlCheckAdmin = "Select * from admin where email = '$login_email'";
    $admin1 = $database->query($sqlCheckAdmin);
    $rowAdmin = $database->fetch_array($admin1);

    //hashing and salting non-admin  user's login input password
    if(!$rowAdmin)
    {
        $login_password = hash('ripemd128', "$salt2$login_password$salt1");
    }

    //Creating Queries for matching  email and password inputs with the one on database for authentication
    $user_login_query = "Select * from user where email = '$login_email' and password = '$login_password'  ";
    $admin_login_query = "Select * from admin where email = '$login_email' and password = '$login_password' ";

	$query_result1 = $database->query($user_login_query);
	$query_result2 = $database->query($admin_login_query);


	$tuple1 = $database->fetch_array($query_result1);
	$tuple2 = $database->fetch_array($query_result2);

    //Alert if login Email and Password fields are empty
    //Redirecting to user or admin page after sucessful login authentication
	if ($login_email === "" || $login_password === "")
    {
		echo "<script type=\"text/javascript\"> alert('Please Enter: Username and Password'); </script>";
	}
    elseif($tuple1)
    {
		$_SESSION['id'] = $tuple['id'];
		page_redirect('user.php');

	}elseif($tuple2)
    {
		$_SESSION['id'] = $tuple['id'];
		page_redirect('admin.php');
	}
    else
    {
        $error .= 'Invalid Username And Password Combination! <br>';
    }
}

/*********************************************************************
*
*Form Form Validation
*
*********************************************************************/

//Initializing Variables
$name = $email = $password = $confirm_password = $name_error = $email_error = $password_error = $confirm_password_error = $success = "";

//Form Validation
if(isset($_POST['submit']) =='Sign Up' && isset($_POST['name']) &&isset($_POST['email']) && isset($_POST['password']) && isset($_POST['confirm_password']))
{
    //Sanitizing Form's inputs
    $name = $database->mysql_entities_fix_string(test_input($_POST['name']));
    $email = $database->mysql_entities_fix_string(test_input($_POST['email']));

    //Form empty inputs catcher
    if(empty($_POST['name']))
    {
        $error .= "Please Enter Your Name!<br>";
    }
    if(empty($_POST['email']))
    {
      	$error .= "Please Enter Your Email!<br>";
    }
    if(empty($_POST['password']))
    {
      	$error .= "Please Enter Your Password!<br>";
    }
    if (!preg_match("/^[A-Za-z]*$/", $name)) {
        $error .= "Name Can Only Have Letters And White Space<br>";
    }
    if (!((strpos($email, ".") > 0) && (strpos($email, "@") > 0)) || preg_match("/[^a-zA-Z0-9.@_-]/", $email))
    {
        $error .= "The Email address is invalid<br>";
    }






/*
*Password Confirmation and Validation
*If form's password and confirm password are matched and not have empty fields
*change this later
*/

if(($_POST["password"] == $_POST["confirm_password"]) && !empty($_POST['password']))
{

//Sanitizing
$password = $database->mysql_entities_fix_string(test_input($_POST["password"]));
$cconfirm_password = $database->mysql_entities_fix_string(test_input($_POST["confirm_password"]));


    if (strlen($_POST["password"]) < '6')
    {
        $error_prompt .= "Password Must Have At Least 6 Characters!<br>";
    }
        elseif(!preg_match("#[a-z]+#",$password))
        {
            $error_prompt .= "Your Password Must Contain At Least 1 Lowercase Letter!<br>";
        }
        elseif(!preg_match("#[A-Z]+#",$password))
        {
            $error_prompt .= "Password Must Contain At Least 1 Capital Letter!<br>";
        }
        elseif(!preg_match("#[0-9]+#",$password))
        {
            $error_prompt .= "Password Must Have At Least 1 Number!<br>";
        }

    //password and confirm password matching error catcher
    if($_POST["password"] !== $_POST["confirm_password"])
    {
        $error .= "Password did not match<br>";
    }

    //hashing and salting Form's password
    $password = hash('ripemd128', "$salt2$password$salt1");
    //$password = md5((md5($_POST['email']).$password));

    //check if email already registered
    if($error == "")
    {

        $check_duplicate = $database->query("select * from `user` where email = '".$database->mysql_prep($email)."'");
        $username_result = $database->num_rows($check_duplicate);

        $query_insert = "";
        $statement = null;

        if (!$username_result)
        {
            $query_insert = 'insert into user(name, email, password) values(?, ?, ?)';
            $statement = $database->prepare_query($query_insert);
            $statement->bind_param('sss', $name, $email, $password);
            echo "<script type=\"text/javascript\">alert('You have been successfully registered. Please login');</script>";

            $statement->execute();
            $statement->close();
        }
        else
        {
            $error = "Email Already Exist In Database! <br>Please Try Another Email";
        }
    }
}
}
$database->close_connection(); //close connection


?>

<!-- Navigation Bar Container-->
<div class="navbar navbar-default navbar-fixed-center">
    <div class="container">
        <div class="navbar-header">
            <button class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
  			</button>
            <a href="index.php" class="navbar-brand">Malware Scanner</a>
        </div>
        <div class="collapse navbar-collapse">
            <form class="navbar-form navbar-right" method="post">

                <!-- Login Email Field -->
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="login_email" placeholder="Email" class="form-control"
                           value="<?php if(isset($_POST['login_email'])){ echo $_POST['login_email']; }else{echo " "; } ?>" />
                </div>

                <!-- Login Password Field -->
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="login_password" placeholder="Password" class="form-control" />
                </div>

                <!-- Login Button -->
                <input type="submit" name="submit" class="btn btn-success" value="LOG IN">
            </form>
        </div>
    </div>
</div>

 <!-- Form Container -->
<div class="container contentContainer" id="topContainer">
    <div class="row">
        <div class="col-md-6 col-md-offset-3" id="topRow">
            <h1 class="marginTop">Malware Scanner</h1>
            <form class="marginTop" method="post">

                <!-- Name Field -->
                <div class="form-group">
                    <label for="name">Name</label><span class="error">*<?php echo "$name_error"; ?></span>
                    <input type="text" name="name" class="form-control" placeholder="Enter Name"
                           value="<?php if(isset($_POST['name'])){echo $_POST['name'];}else{echo " ";} ?>" />
                </div>

                <!-- Email Field -->
                <div class="form-group">
                    <label for="email">Email Address</label><span class="error">*<?php echo "$email_error"; ?></span>
                    <input type="email" name="email" class="form-control" placeholder="Email Address" value="<?php if(isset($_POST['email'])){echo $_POST['email'];}else{echo " ";}?>" />
                </div>

                <!-- Password Field -->
                <div class="form-group">
                    <label for="password">Password</label><span class="error">*<?php echo "$password_error"; ?></span>
                    <input type="password" name="password" class="form-control" placeholder="Password" />
                    <!-- Confirm Password Field -->
                    <div class="form-group">
                        <label for="confirm_password">Confirm Password</label><span class="error">*<?php echo "$confirm_password_error"; ?></span>
                        <input type="password" name="confirm_password" class="form-control" placeholder="Confirm Password" />
                        <br>
                    </div>

                <!-- Form Field' Error Notifications -->
                <?php
                    if($error){echo '<div class="alert alert-danger">'.addslashes($error).'</div>';}
                    if($success){echo '<div class="alert alert-success">'.addslashes($success).'</div>';}
                    if($message) {echo '<div class="alert alert-success">'.addslashes($message).'</div>';}
                ?>
                    <h2 class="bold marginTop">Sign Up And Scans For Free!</h2>

                    <!-- SIGN UP Button -->
                    <input type="submit" name="submit" value="SIGN UP" class="btn btn-success btn-lg marginTop" />
                    <br>
                    <br>
                </div>
            </form>
        </div>
    </div>
</div>

</body>
</html>


