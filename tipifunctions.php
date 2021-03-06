<?php
#FUNCTIONS###################################################################
function getlastimageid(){																											# function to read the highest value image id in image table
	global $dbusername, $dbpassword, $dbhostname, $database, $imgfolder;

	$conn = new mysqli($dbhostname, $dbusername, $dbpassword,  $database);

	if($conn->connect_error){
		die("Connection to database failed! ".$conn->connect_error);
	}

	$sql_lastimageid = "select max(id) as max_imageid from image";      					# get highest id value of image records

	$lastimageid_result = $conn->query($sql_lastimageid);

	if ($lastimageid_result->num_rows > 0) {
		while($row = $lastimageid_result->fetch_assoc()) {
			$lastimageid = ltrim($row["max_imageid"],"IMG");        									# get numeric part of image id
			$newimageid = $lastimageid++;                           									# increment the id number
			$newimageid = "IMG".str_pad($lastimageid,5,"0",STR_PAD_LEFT);   					# append the prefix to the new image id number
	   }
	} else {
		echo "0 results";
	}

	$conn->close();

	return $newimageid;
}

function getnewimageid(){																												# function to read the highest value image id in image table and generate a new one
	global $dbusername, $dbpassword, $dbhostname, $database, $imgfolder;

	$conn = new mysqli($dbhostname, $dbusername, $dbpassword,  $database);

	if($conn->connect_error){
		die("Connection to database failed! ".$conn->connect_error);
	}

	$sql_lastimageid = "select max(id) as max_imageid from image";  							# get highest id value of image records

	$lastimageid_result = $conn->query($sql_lastimageid);

	if ($lastimageid_result->num_rows > 0) {
		while($row = $lastimageid_result->fetch_assoc()) {
			$lastimageid = ltrim($row["max_imageid"],"IMG");            							# get numeric part of image id
			$newimageid = $lastimageid++;                               							# increment the id number
			$newimageid = "IMG".str_pad($lastimageid,5,"0",STR_PAD_LEFT);   					# prepend the prefix to the new image number
	   }
	} else {
		echo "0 results";
	}

	$conn->close();

	return $newimageid;
}

function getnewtempid(){																												# function to read the highest value temperature data id from the termperature table and generate a new one
	global $dbusername, $dbpassword, $dbhostname, $database;

	$conn = new mysqli($dbhostname, $dbusername, $dbpassword,  $database);

	if($conn->connect_error){
		die("Connection to database failed! ".$conn->connect_error);
	}

	$sql_lasttempid = "select max(id) as max_tempid from temperature";  					# get highest id value of temperature records

	$lasttempid_result = $conn->query($sql_lasttempid);

	if ($lasttempid_result->num_rows > 0) {
		while($row = $lasttempid_result->fetch_assoc()) {
			$lasttempid = ltrim($row["max_tempid"],"TEMP");             							# get numeric part of id temperature records
			$newtempid = $lasttempid++;                                 							# increment the id number
			$newtempid = "TEMP".str_pad($lasttempid,5,"0",STR_PAD_LEFT);    					# prepend the prefix to the new temperature id number
	   }
	} else {
		echo "0 results";
	}

	$conn->close();

	return $newtempid;
}

function getnewrelid(){																													# function to read the highest value relation id from the temp
	global $dbusername, $dbpassword, $dbhostname, $database;

	$conn = new mysqli($dbhostname, $dbusername, $dbpassword,  $database);

	if($conn->connect_error){
		die("Connection to database failed! ".$conn->connect_error);
	}

	$sql_lastrelid = "select max(id) as max_relid from imagetemprel";							# get highest id of imagetemprel records

	$lastrelid_result = $conn->query($sql_lastrelid);

	if ($lastrelid_result->num_rows > 0) {
		while($row = $lastrelid_result->fetch_assoc()) {
			$lastrelid = ltrim($row["max_relid"],"ITR");															# get numberic part of imagetemprel record id
			$newrelid = $lastrelid++;																									# increment the id number
			$newrelid = "ITR".str_pad($lastrelid,5,"0",STR_PAD_LEFT);									# prepend the prefix to the new relation id number
		}
	} else {
		echo "0 results";
	}

	$conn->close();

	return $newrelid;
}

function inserttempdata($csvp){																									# parameter is the array with the csv filenames
	global $dbusername, $dbpassword, $dbhostname, $database;

	$conn = new mysqli($dbhostname, $dbusername, $dbpassword,  $database);

	if($conn->connect_error){
		die("Connection to database failed! ".$conn->connect_error);
	}

	if($handle = fopen($csvp,"r")){																								# open csv
		if($tempdata = file_get_contents($csvp)){																		# read data from csv for loop
			$tempid = getnewtempid();																									# generate a new id for the temperature data if the file opens

			$sql_inserttempdata = "insert into temperature (id,tempdata,createddatetime) values ('".$tempid."','".$tempdata."',Now())"; # sql to write the temperature data to the database

			$inserttempdata_result = $conn->query($sql_inserttempdata);								# write temperature data to database
		}
		Else {
			exit("Failed to get contents of ".$csvp);
		}
	}
	Else {
		exit("Failed to open ".$csvp);
	}

	return $tempid;

}

function insertnewimage($newimageid){
	global $dbusername, $dbpassword, $dbhostname, $database, $imgfolder;

	$conn = new mysqli($dbhostname, $dbusername, $dbpassword,  $database);

	if($conn->connect_error){
		die("Connection to database failed! ".$conn->connect_error);
	}

	$sql_createimage = "insert into image (id,image,createddatetime) values ('".$newimageid."','".$imgfolder.$newimageid.".bmp',Now())";

	$createimage_result = $conn->query($sql_createimage);

	if(!$createimage_result){
		echo "<br/><b>Failed to insert</b><br/>";
	}

	$conn->close();
}

function insertrelation($imageid,$newtempid){
	global $dbusername, $dbpassword, $dbhostname, $database;

	$conn = new mysqli($dbhostname, $dbusername, $dbpassword, $database);

	if($conn->connect_error){
		die("Connection to database failed! ".$conn->connect_error);
	}

	$sql_insertrelation = "insert into imagetemprel (id,imageid,tempid) values ('".getnewrelid()."','".$imageid."','".$newtempid."')";

	$insertrelation_result = $conn->query($sql_insertrelation);

	return $insertrelation_result;
}

function showlatest(){
	$ini_array = parse_ini_file("tipi.ini",true); 																# array to store the ini file contents

	for ($x=0;$x<count($ini_array['pis']['pi_name']);$x++) {
		$cameracount = $ini_array['pis']['pi_camcount'][$x];
	}

	global $dbusername, $dbpassword, $dbhostname, $database, $imgfolder;

	$conn = new mysqli($dbhostname, $dbusername, $dbpassword,  $database);

	if($conn->connect_error){
		die("Connection to database failed! ".$conn->connect_error);
	}

	#$sql_lastimageid = "select max(id) as max_imageid, max(image) as image from image";
	$sql_lastimageid = "select id as max_imageid, image as image from image order by id desc limit 2";

	$lastimageid_result = $conn->query($sql_lastimageid);

	if ($lastimageid_result->num_rows > 0) {
		while($row = $lastimageid_result->fetch_assoc()) {
			$image = ltrim($row["image"],"IMG");
	   }
	} else {
		echo "0 results";
	}

	?>
	Name: <?php echo $image; ?>
	<div class="container"><a href="<?php echo $image ?>"><img class='newimg' src="<?php echo $image ?>"></a>
	<?php

	$sql_gettempdata = "select tempdata from temperature t join imagetemprel i on i.tempid = t.id where i.imageid in (select id from image where image = '".$image."')";

	$gettempdata_result = $conn->query($sql_gettempdata);

	if ($gettempdata_result->num_rows > 0){
		while($row = $gettempdata_result->fetch_assoc()){
			$temp = explode(",", $row["tempdata"]);
			array_pop($temp);
	?>
	<div class="bottomleft"><b><?php echo min($temp); ?></div>
  <div class="bottomright"><?php echo max($temp); ?></b></div><br/>
	<?php
		}
	} Else {
		echo "<br/><B>TEMPERATURE DATA NOT FOUND</B><br/>";
	}

	$conn->close();
}

function capture() {
		$ini_array = parse_ini_file("tipi.ini",true); 															# array to store the ini file contents
		$path = './phpseclib1.0.11/';						 																		# path for phpseclib files
		set_include_path(get_include_path() . PATH_SEPARATOR . $path); 							# modify include path
		include "Net/SSH2.php"; 																										# include ssh library

		for ($x=0;$x<count($ini_array['pis']['pi_name']);$x++) {
			$username = $ini_array['pis']['pi_login'][$x]; 														# get username from ini file array
			$password = $ini_array['pis']['pi_pw'][$x]; 															# get password from ini file array
			$host = $ini_array['pis']['pi_ip'][$x]; 																	# get hostname (ip address) from ini file array
			$cameracount = $ini_array['pis']['pi_camcount'][$x];

			$ssh = new Net_SSH2($host); 																							# create ssh object for host

			$ssh->getServerPublicHostKey();																						# add host key

			if (!$ssh->login($username, $password)) { 																# create ssh connection object for host
				exit('Login Failed'); 																									# leave if the login fails
			}

			if (!$ssh->exec('cd TI && sudo ./Capture1 r')){														# trigger capture 1
				exit('Failed to capture camera 1');
			}

			if (!$ssh->exec('cd TI && sudo ./Capture2 r')){ 													# trigger capture 2
				exit('Failed to capture camera 2');
			}
		}
		download();

}

function download() {
	$ini_array = parse_ini_file("tipi.ini",true); 																# array to store the ini file contents
	$path = './phpseclib1.0.11/';						 																			# path for phpseclib files

	global $newrelid,$newimageid,$newtempid;

	set_include_path(get_include_path() . PATH_SEPARATOR . $path); 								# modify include path
	include "Net/SFTP.php"; 																											# include sftp library

	#$remotepath = '/home/pi/TI/';																								# remote path for rpi
	$remotepath = 'TI/';																													# remote path for local testing

	global $csva, $imagea;																												# bring image and csv file name arrays to function
	global $imgfolder;

	for ($y=0;$y<count($ini_array['pis']['pi_name']);$y++) {           		 				# loop through pis

		$name = $ini_array['pis']['pi_name'][$y];																		# get name of pi
		$username = $ini_array['pis']['pi_login'][$y]; 															# get username from ini file array
		$password = $ini_array['pis']['pi_pw'][$y]; 																# get password from ini file array
		$host = $ini_array['pis']['pi_ip'][$y]; 																		# get hostname (ip address) from ini file array

		$date = new Datetime();																											# date variable for file name

		$sftp = new Net_SFTP($host); 																								# create sftp object for host

		if (!$sftp->login($username, $password)) { 																	# create sftp connection object for host
			exit('Login Failed'); 																										# leave if the login fails
		}

		for($x=0;$x<$ini_array['pis']['pi_camcount'][$y];$x++){           					# loop through cameras for image

			$imageid = getnewimageid();																								# generate new imageid

			if(!$sftp->get($remotepath.$imagea[$x],$imgfolder.$imageid.".bmp")){ 			# download bmp
				Exit('Failed to download image: '.$remotepath.$imagea[$x]);
			}

			insertnewimage($imageid);  																								# insert downloaded image to db

			if(!$sftp->get($remotepath.$csva[$x],$csva[$x])){													# download csv
				Exit('Failed to download csv: '.$remotepath.$csva[$x]);
			}

			$newtempid = inserttempdata($csva[$x]); 																	# insert csv data to db
			insertrelation($imageid,$newtempid);																			# create db record
		}
		echo "<b>Image Acquired: </b>";
		echo $name." on ".$date->format('m-d-Y');
		echo " at ".$date->format('h:i:s')."<br>"; 																	# output current pi name to page
	}
	showlatest();
}

																																								# search function - does not work
function searchbydate($date){
    global $dbusername, $dbpassword, $dbhostname, $database, $imgfolder;

    $conn = new mysqli($dbhostname, $dbusername, $dbpassword,  $database);

    if($conn->connect_error){
        die("Connection to database failed! ".$conn->connect_error);
    }

    $sql_searchbydate = "select id, image from image where date like ".$date."%";

    $searchbydate_result = $conn->query($sql_searchbydate);

    echo $date;
    if($searchbydate_result->num_rows > 0){
        while($rows = $searchbydate_result->fetch_assoc()){
          $image = $row['image'];
          echo $image;
          echo "<img src=".$image."><br/>";
        }
    }
}

?>
