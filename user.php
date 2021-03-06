<?php
require_once('mysql_connection.php');

/*********************************************************************
*
*PHP FUNCTIONS
*
*********************************************************************/

//Directing to another page
function page_redirect($location = NULL)
{
    if($location != NULL)
    {
        header("Location: {$location}");
        exit();
    }
}

// function checks if the uploaded file has any error,
function check_file_error($file_error)
{
    if ($file_error === 0)
    {
        return true;
    }
    else
    {
    echo "Error: Uploading The File Failed!";
    }
}

// function that retrieved integer from text file and print on the page
function retrieve_integer($file_contents)
{
    $words = preg_split('/[\s]+/', $file_contents, -1, PREG_SPLIT_NO_EMPTY);

    foreach ($words as $value)
    {
        if (ctype_digit($value))
        {
            echo $value."\n";
        }
    }
}

// function that checks the file size. if it is more than 100 mb than it will throw error
function check_file_size($file_size)
{
    if ($file_size < 1000000)
    {
        return true;
    }
    else
    {
        echo "File Is Too Large! Please Upload A File That Is Less Than 100 MegaBytes.";
    }
}


 function ascii2hex($ascii) {
  $hex = '';
  for ($i = 0; $i < strlen($ascii); $i++) {
    $byte = strtoupper(dechex(ord($ascii{$i})));
    $byte = str_repeat('0', 2 - strlen($byte)).$byte;
    $hex.=$byte." ";
  }
  return $hex;
}


//start seession if no session
if(!isset($_SESSION))
{
    session_start();
}

/*********************************************************************
*
*Check If File Is Infected Or Not
*
*********************************************************************/

//initializing variables
$malware_detected = $no_malware_detected = $error = $inserted = "";

//Check if uploaded file is infected or not
if (isset($_POST['selectedFile']) == 'SCAN')
{
    //Assigning the file metadata informations and upload error
    $file_name = $_FILES['file']['name'];
    $file_size = $_FILES['file']['size'];
    $file_type = $_FILES['file']['type'];
    $file_error = $_FILES['file']['error'];


    $file_contents = file_get_contents($_FILES['file']['tmp_name']);
    $contents = "";
    $detected_malware = false;

    //If filename is not empty and exists
    if(isset($file_name) && !empty($file_name))
    {       //if no upload error
            if (check_file_error($file_error))
            {   //if file size is less than 100MB
                if (check_file_size($file_size))
                {  //loop the file size
                   for($i = 0; $i < $file_size; $i++)
                   { // get the current ASCII character representation of the current byte
                        $asciiCharacter = $file_contents[$i];
                        $contents .= ascii2hex($asciiCharacter);
                    }
                    $contents = str_replace(' ', '', $contents);
                    $sql = "Select signature from malware";
                    $result = $database->query($sql);
                    while ($row = mysqli_fetch_array($result, MYSQLI_BOTH))
                    {
                      $a = $row['signature'];
                      if(strpos($contents, $a) !== false)
                      {
                        //hole display message of infected file
                        $malware_detected =  "Malware Detected In You File";
                        $detected_malware = true; //bolean value detection
                        break;
                      }
                    }
                    //hold display message of clean file
                    if(!$detected_malware)
                    {
                      $no_malware_detected = "The File is Clean of Malware, You Are Safe!";
                    }
                }
            }
        }
    else
    {
        $error =  "Please Pick A File";
    }
}


?>

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

    <script>
		$(".contentContainer").css("min-height",$(window).height());
    </script>

    <script type="text/javascript">
    function submitForm(action)
        {
            var form = document.getElementById('form1');
            form.action = action;
            form.submit();
        }
    </script>

    <style>

        #topContainer
        {
   			background-color: bisque;
   			height:100%;
   			width:100%;
   			background-size:cover;
            text-align: center;
   		}


    </style>

</head>
<body data-spy="scroll" data-target=".navbar-collapse">

<!-- Navigation Bar -->
<div class="navbar navbar-default navbar-fixed-top">
  	<div class="container">
  		<div class="navbar-header">
  			<h3 class="navbar-brand">Malware Scanner</h3>
  		</div>
  		<div class="collapse navbar-collapse pull-right">
  			<ul class=" navbar-nav nav">
            <li style="font-weight: bold; margin-top: 16px; margin-right: 10px;">ADMIN PAGE</li>
  			     <li><a href="index.php">SIGN OUT</a></li>
  				</ul>
  		</div>
  	</div>
</div>

<!-- Scanner And Uploader -->
<div class="container contentContainer" id="topContainer">
    <br><br><br><br><br>
    <div class="col-md-4 malware">
        <h4 id="text">SCAN FILE </h4>
        <form method="POST" enctype="multipart/form-data" class="malwareFile">
            <input type="file" name="file" id="checkmalware" class="input-large form-control"><br><br>
            <input type="submit" name="selectedFile" class="form-control btn btn-success btnAdmin" value="SCAN">
        </form>
        <br><br>
        <?php
        if ($error){echo '<div class="alert alert-danger">'.addslashes($error).'</div>';}
        if ($malware_detected){echo '<div class="alert alert-danger">'.addslashes($malware_detected).'</div>';}
        if ($no_malware_detected){echo '<div class="alert alert-success">'.addslashes($no_malware_detected).'</div>';}
        if ($inserted) {
        echo '<div class="alert alert-success">'.addslashes($inserted).'</div>'; }
        ?>
    </div>

</div>


</body>
</html>


