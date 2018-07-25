<html>
<head>
<link href="ti.css" rel="stylesheet" type="text/css" media="all">
</head>
<body>
<a href="ti.php">HOME</a><br><br>
<?php
#GLOBALS########################################################################
date_default_timezone_set('PST8PDT');																						# Set Pacific Timezone

$csva = array('Device_1.csv','Device_2.csv');																		# CSV filenames
$imagea = array('Device_1.bmp','Device_2.bmp');																	# BMP filenames

$TESTMODE = false;

$dbhostname = "192.168.70.33";
$dbusername = "webuser";
$dbpassword = "webuser123";
$database = "tipi";

$imgfolder = "images/";

#BODY###########################################################################
## display past images
global $dbusername, $dbpassword, $dbhostname, $database;
?>
<div class="title">Image Archive:<br></div>
<?php
$conn = new mysqli($dbhostname, $dbusername, $dbpassword, $database);

if($conn->connect_error){
	die("Connection to database failed! ".$conn->connect_error);
}

$sql_imagelist = "select image,createddatetime from image where id in (select imageid from imagetemprel) order by createddatetime desc, image desc";  	# get image filenames that have temperature data

$imagelist_result = $conn->query($sql_imagelist);
echo "<div class='row'>";
$w = 0;
if ($imagelist_result->num_rows > 0){
	while($row = $imagelist_result->fetch_assoc()){
		echo "<div class='column'>";
		$w++;
		$datetime = $row["createddatetime"];
		$image = $row["image"];
		echo $image.":<br/>".$datetime.":<br/><a href='$image'><img class='oldimg' src='$image'></a><br/>";
		$conn2 = new mysqli($dbhostname, $dbusername, $dbpassword, $database);

		if($conn2->connect_error){
			die("Connection to database failed! ".$conn2->connect_error);
		}

		$sql_gettempdata = "select tempdata from temperature t join imagetemprel i on i.tempid = t.id where i.imageid in (select id from image where image = '".$image."')";
		$gettempdata_result = $conn2->query($sql_gettempdata);

		if ($gettempdata_result->num_rows > 0){
			while($row = $gettempdata_result->fetch_assoc()){
				$temp = explode(",", $row["tempdata"]);
				array_pop($temp);
?>
		<div class="archivebottomleft"><b>
		<?php echo min($temp); ?>
	</div><div class="archivebottomright">
		<?php echo max($temp); ?></b>
	</div><br/>
<?php
		$conn2->close();
	}
}
Else {
		echo "<br/><B>TEMPERATURE DATA NOT FOUND</B><br/>";
	}
echo "</div>";
	#$conn->close();
	if($w==6){
		echo "</div><div class='row'>";
		$w=0;
	}
		}
	}


?>
</body>
</html>
