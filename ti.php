<html>
<head>
<link href="ti.css" rel="stylesheet" type="text/css" media="all">
</head>
<body>
<a href="ti.php">HOME</a> - <a href='archive.php'>ARCHIVE</a><br><br>
<form action="ti.php" method="get">
	<input type="submit" name="Capture" value="Capture">
	<input type="submit" name="Download" value="Download">
	<!--<input type="submit" name="Search" value="Search">-->
</form>
<?php
#INCLUDE########################################################################
include 'tipifunctions.php';

#GLOBALS########################################################################
date_default_timezone_set('PST8PDT');																						# Set Pacific Timezone

$csva = array('Device_1.csv','Device_2.csv');																		# CSV filenames
$imagea = array('Device_1.bmp','Device_2.bmp');																	# BMP filenames

$TESTMODE = false;

#mysql info
$dbhostname = "192.168.70.33";
$dbusername = "webuser";
$dbpassword = "webuser123";
$database = "tipi";

$imgfolder = "images/";

#BODY###########################################################################
if(isset($_GET['Capture'])) {																										# was Trigger passed to url?
	capture();																																		# run trigger function
}
if(isset($_GET['Download'])) {																									# was Download passed to url?
	download();																																		# run download function
}

?>
</body>
</html>
