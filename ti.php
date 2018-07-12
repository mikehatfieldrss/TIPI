<html>
<body>
<style>body { 
    background-color: lightblue;
	font-family: Arial, Helvetica, sans-serif;
	font-size: 75%;
	<!--padding: 40px 19px; -->
}</style>
<a href="ti.php">HOME</a><br><br>
<form action="ti.php" method="get">
	<input type="submit" name="Trigger" value="Trigger">
	<input type="submit" name="Download" value="Download">
	<input type="submit" name="Insert" value="Insert">
	<input type="submit" name="Read" value="Read">
	<input type="submit" name="Search" value="Search">
</form>
<?php
date_default_timezone_set('PST8PDT');										# Set Pacific Timezone

$csva = array('Device_1.csv','Device_2.csv');								# CSV filenames
$imagea = array('Device_1.bmp','Device_2.bmp');								# BMP filenames

$TESTMODE = true;

$hostname = "127.0.0.1";
$username = "webuser";
$password = "webuser123";
$database = "tipi";

if(isset($_GET['Trigger'])) {												# was Trigger passed to url?
	trigger();																# run trigger function
}
if(isset($_GET['Download'])) {												# was Download passed to url?
	download();																# run download function
}

function trigger() {
	global $TESTMODE;
	if (!$TESTMODE){
	$ini_array = parse_ini_file("picam.ini",true); 							# array to store the ini file contents
	$path = './phpseclib1.0.11/';						 					# path for phpseclib files
	set_include_path(get_include_path() . PATH_SEPARATOR . $path); 			# modify include path
	include "Net/SSH2.php"; 												# include ssh library
	
	
	for ($x=0;$x<count($ini_array['pis']['pi_name']);$x++) {
		$username = $ini_array['pis']['pi_login'][$x]; 						# get username from ini file array
		$password = $ini_array['pis']['pi_pw'][$x]; 						# get password from ini file array
		$host = $ini_array['pis']['pi_ip'][$x]; 							# get hostname (ip address) from ini file array
	
		$ssh = new Net_SSH2($host); 										# create ssh object for host
				
		$ssh->getServerPublicHostKey();										# add host key
	
		if (!$ssh->login($username, $password)) { 							# create ssh connection object for host
			exit('Login Failed'); 											# leave if the login fails
		}

		if (!$ssh->exec('cd TI && sudo ./Capture1 r')){						# trigger capture 1
			exit('Failed to capture camera 1');
		} 				
		
		if (!$ssh->exec('cd TI && sudo ./Capture2 r')){ 					# trigger capture 2
			exit('Failed to capture camera 2');
		} 				
	}
	}
}

function archive() {
	$archivefolder = 'old/';											# set archive folder variable
	
	$images = glob("*.bmp");											# lookup all .bmp files in archive folder

	foreach($images as $image) {		# loop through all .bmp files in archive folder
		rename($image,$archivefolder.$image); # move the last received images to the archive subfolder 
	}
}

function download() {
	$ini_array = parse_ini_file("picam.ini",true); 							# array to store the ini file contents
	$path = './phpseclib1.0.11/';						 					# path for phpseclib files

	global $TESTMODE;
	
	set_include_path(get_include_path() . PATH_SEPARATOR . $path); 			# modify include path
	include "Net/SFTP.php"; 												# include sftp library

	#$remotepath = '/home/pi/TI/';										# remote path for rpi
	$remotepath = 'TI/';												# remote path for local testing
	
	global $csva, $imagea;										# bring image and csv file name arrays to function
	
	archive();													# run archive function
	if (!$TESTMODE){
		for ($y=0;$y<count($ini_array['pis']['pi_name']);$y++) {            # loop through pis
			
			$name = $ini_array['pis']['pi_name'][$y];								# get name of pi
			$username = $ini_array['pis']['pi_login'][$y]; 							# get username from ini file array
			$password = $ini_array['pis']['pi_pw'][$y]; 								# get password from ini file array
			$host = $ini_array['pis']['pi_ip'][$y]; 							# get hostname (ip address) from ini file array

			$date = new Datetime();							# date variable for file name 

			echo "<b>Downloading from: </b>".$name." on ".$date->format('m-d-Y')." at ".$date->format('h:i:s')."<br>"; # output current pi name to page
		
			$sftp = new Net_SFTP($host); 											# create sftp object for host
			
			if (!$sftp->login($username, $password)) { 									# create sftp connection object for host
				exit('Login Failed'); 											# leave if the login fails
			}
		
			for($x=0;$x<$ini_array['pis']['pi_camcount'][$y];$x++){           # loop through cameras for image
				if(!$sftp->get($remotepath.$imagea[$x],rtrim($name.'-'.$imagea[$x],".bmp").'-'.$date->format('Y-m-d-His').".bmp")){
						Exit('Failed to download image: '.$remotepath.$imagea[$x]);
				}
			}
			
			for($x=0;$x<$ini_array['pis']['pi_camcount'][$y];$x++){			# loop through cameras for csv
				if(!$sftp->get($remotepath.$csva[$x],$name.'-'.$csva[$x])){
						Exit('Failed to download csv: '.$remotepath.$csva[$x]);
				}
			}
		
			
		}		
	}
	global $username, $password, $hostname, $database;
	$conn = new mysqli($hostname, $username, $password,  $database);

	if($conn->connect_error){
		die("Connection to database failed! ".$conn->connect_error);
	}
}

function insert(){
	global $username, $password, $hostname, $database;
	$conn = new mysqli($hostname, $username, $password,  $database);

	if($conn->connect_error){
		die("Connection to database failed! ".$conn->connect_error);
	}
	$sql = "insert into ";

	$result = $conn->query($sql);

	if ($result->num_rows > 0) {
		// output data of each row
		while($row = $result->fetch_assoc()) {
			echo "image: ".$row["image"]." - Name: ".$row["tempdata"]."<br>";
		}
	} else {
		echo "0 results";
	}
	$conn->close();
	
}

$ini_array = parse_ini_file("picam.ini",true); 							# array to store the ini file contents

echo '<b>Most recent images:</b><br>';												
$images = glob("*.bmp");											# lookup all .bmp files in archive folder
#echo "<br/>".count($images)."<br/>";
$z = 0;
for($y=0;$y<count($ini_array['pis']['pi_name']);$y++){
	$name = $ini_array['pis']['pi_name'][$y];

	for($x=0;$x<$ini_array['pis']['pi_camcount'][$y];$x++){
	
		echo " Name: ".$images[($z)].':';
		echo '<a href="'.$images[$z].'"><img src="'.$images[$z].'"style="vertical-align:middle" width="320" height="320" ></a><br/><br/>'; # add image to page with link

			$csvs = glob($name.'-'.$csva[($x)]);
			foreach($csvs as $csv) {
			#$name = $ini_array['pis']['pi_name'][$x];
			if($handle = fopen($name.'-'.$csva[($x)],"r")){						# open csv 
				if($csvdata = fgetcsv($handle,",")){							# get csv data
					if($csvdata[count($csvdata)-1]==0){							# check for 0 at end of data array
						array_pop($csvdata);									# drop element if 0
					}
				}	
				Else {
					exit("Failed to get contents of ".$name.'-'.$csva[($x)]);
				}
			} 
			Else {
				exit("Failed to open ".$name.'-'.$csva[($x)]);
			}
				echo "Temperature range: ";
				echo "min: ".min($csvdata);
				echo " - max: ".max($csvdata)."<br/><br/>";
			}
		$z++;
	}
}

$conn = new mysqli($hostname, $username, $password,  $database);

if($conn->connect_error){
	die("Connection to database failed! ".$conn->connect_error);
}

## Generate next IDs for insert
$sql_lasttempid = "select max(id) as max_tempid from temperature";
$sql_lastimageid = "select max(id) as max_imageid from image";
$sql_lastrelid = "select max(id) as max_relid from imagetemprel";

$lasttempid_result = $conn->query($sql_lasttempid);
$lastimageid_result = $conn->query($sql_lastimageid);
$lastrelid_result = $conn->query($sql_lastrelid);

if ($lasttempid_result->num_rows > 0) {
    // output data of each row
    while($row = $lasttempid_result->fetch_assoc()) {
		$lasttempid = ltrim($row["max_tempid"],"TEMP");
		$newtempid = $lasttempid++;
		$newtempid = "TEMP".str_pad($lasttempid,5,"0",STR_PAD_LEFT);
		echo "<br/>newtempid: ".$newtempid."<br/>";
    }
} else {
    echo "0 results";
}

if ($lastimageid_result->num_rows > 0) {
    // output data of each row
    while($row = $lastimageid_result->fetch_assoc()) {
		$lastimageid = ltrim($row["max_imageid"],"IMG");
		$newimageid = $lastimageid++;
		$newimageid = "IMG".str_pad($lastimageid,5,"0",STR_PAD_LEFT);
		echo "<br/>newimageid: ".$newimageid."<br/>";
   }
} else {
    echo "0 results";
}

if ($lastrelid_result->num_rows > 0) {
    // output data of each row
	while($row = $lastrelid_result->fetch_assoc()) {    	
		$lastrelid = ltrim($row["max_relid"],"ITR");
		$newrelid = $lastrelid++;
		$newrelid = "ITR".str_pad($lastrelid,5,"0",STR_PAD_LEFT);
		echo "<br/>newrelid: ".$newrelid."<br/>";
    }
} else {
    echo "0 results";
}
$conn->close();
##



?>
</body>
</html>
