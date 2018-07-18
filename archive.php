<html>
<head>
<link href="ti.css" rel="stylesheet" type="text/css" media="all">
</head>
<body>
<a href="ti.php">HOME</a><br><br>
<?php
#GLOBALS#####################################################################
date_default_timezone_set('PST8PDT');										# Set Pacific Timezone

$csva = array('Device_1.csv','Device_2.csv');								# CSV filenames
$imagea = array('Device_1.bmp','Device_2.bmp');								# BMP filenames

$TESTMODE = false;

$hostname = "127.0.0.1";
$username = "webuser";
$password = "webuser123";
$database = "tipi";

$imgfolder = "images/";

#BODY########################################################################
## display past images
global $username, $password, $hostname, $database;
?>
<div class="title">Image Archive:<br></div>
<?php
$images = glob($imgfolder."*.bmp");											# lookup all .bmp files in archive folder
$z = 0;
foreach($images as $image) { 
?>
Name: <?php echo $image; ?>	
<div class="container"><a href="<?php echo $image ?>"><img src="<?php echo $image ?>" ></a> 
<?php
	$conn = new mysqli($hostname, $username, $password, $database);

	if($conn->connect_error){
		die("Connection to database failed! ".$conn->connect_error);
	}

	$sql_gettempdata = "select tempdata from temperature t join imagetemprel i on i.tempid = t.id where i.imageid in (select id from image where image = '".$image."')";

	$gettempdata_result = $conn->query($sql_gettempdata);

	if ($gettempdata_result->num_rows > 0){
		while($row = $gettempdata_result->fetch_assoc()){
			$temp = explode(",", $row["tempdata"]);
			array_pop($temp);
?>
<div class="bottomleft"><b>
<?php echo min($temp); ?>
</div><div class="bottomright">
<?php echo max($temp); ?></b>
</div><br/>
<?php
		}
	} Else {
		echo "<br/><B>TEMPERATURE DATA NOT FOUND</B><br/>";
	}

	$conn->close();

	$z++;
}

?>
</body>
</html>