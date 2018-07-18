<?php
#FUNCTIONS###################################################################
function getlastimageid(){
	global $username, $password, $hostname, $database, $imgfolder;
	
	$conn = new mysqli($hostname, $username, $password,  $database);

	if($conn->connect_error){
		die("Connection to database failed! ".$conn->connect_error);
	}
	
	$sql_lastimageid = "select max(id) as max_imageid from image";      # get highest id value of image records
	
	$lastimageid_result = $conn->query($sql_lastimageid);
	
	if ($lastimageid_result->num_rows > 0) {
		while($row = $lastimageid_result->fetch_assoc()) {
			$lastimageid = ltrim($row["max_imageid"],"IMG");        # get numeric part of image id
			$newimageid = $lastimageid++;                           # increment the id number
			$newimageid = "IMG".str_pad($lastimageid,5,"0",STR_PAD_LEFT);   # append the prefix to the new image id number
	   }
	} else {
		echo "0 results";
	}
	
	$conn->close();
	
	return $newimageid;
}

function getnewimageid(){
	global $username, $password, $hostname, $database, $imgfolder;
	
	$conn = new mysqli($hostname, $username, $password,  $database);

	if($conn->connect_error){
		die("Connection to database failed! ".$conn->connect_error);
	}
	
	$sql_lastimageid = "select max(id) as max_imageid from image";  # get highest id value of image records
	
	$lastimageid_result = $conn->query($sql_lastimageid);
	
	if ($lastimageid_result->num_rows > 0) {
		while($row = $lastimageid_result->fetch_assoc()) {
			$lastimageid = ltrim($row["max_imageid"],"IMG");            # get numeric part of image id
			$newimageid = $lastimageid++;                               # increment the id number
			$newimageid = "IMG".str_pad($lastimageid,5,"0",STR_PAD_LEFT);   #append the prefix to the new image number
	   }
	} else {
		echo "0 results";
	}

	$conn->close();
	
	return $newimageid;
}

function getnewtempid(){
	global $username, $password, $hostname, $database;
	
	$conn = new mysqli($hostname, $username, $password,  $database);

	if($conn->connect_error){
		die("Connection to database failed! ".$conn->connect_error);
	}

	$sql_lasttempid = "select max(id) as max_tempid from temperature";  # get highest id value of temperature records
	
	$lasttempid_result = $conn->query($sql_lasttempid);
	
	if ($lasttempid_result->num_rows > 0) {
		while($row = $lasttempid_result->fetch_assoc()) {
			$lasttempid = ltrim($row["max_tempid"],"TEMP");             # get numeric part of id temperature records
			$newtempid = $lasttempid++;                                 # increment the id number
			$newtempid = "TEMP".str_pad($lasttempid,5,"0",STR_PAD_LEFT);    # 
	   }
	} else {
		echo "0 results";
	}

	$conn->close();
	
	return $newtempid;
}

function getnewrelid(){
	global $username, $password, $hostname, $database;
	
	$conn = new mysqli($hostname, $username, $password,  $database);

	if($conn->connect_error){
		die("Connection to database failed! ".$conn->connect_error);
	}
	
	$sql_lastrelid = "select max(id) as max_relid from imagetemprel";
	
	$lastrelid_result = $conn->query($sql_lastrelid);
	
	## Generate next IDs for insert
	if ($lastrelid_result->num_rows > 0) {
		// output data of each row
		while($row = $lastrelid_result->fetch_assoc()) {    	
			$lastrelid = ltrim($row["max_relid"],"ITR");
			$newrelid = $lastrelid++;
			$newrelid = "ITR".str_pad($lastrelid,5,"0",STR_PAD_LEFT);
		}
	} else {
		echo "0 results";
	}

	$conn->close();
	
	return $newrelid;
}

function gettempdatafromfile($imagefile){
	global $username, $password, $hostname, $database;	
	
	$conn = new mysqli($hostname, $username, $password,  $database);
	
	echo "<br/>imagefile: ".$imagefile."<br/>";
	
	if($conn->connect_error){
		die("Connection to database failed! ".$conn->connect_error);
	}
	
	if($handle = fopen($imagefile,"r")){						# open csv 
		if($filedata = file_get_contents($imagefile)){
			echo "<br/>TEMPDATA: ".$filedata;
		}	
		Else {
			exit("Failed to get contents of ".$imagefile);
		}
	} 
	Else {
		exit("Failed to open ".$imagefile);
	}
}

function inserttempdata($csvp){
	global $username, $password, $hostname, $database;
	
	$conn = new mysqli($hostname, $username, $password,  $database);

	if($conn->connect_error){
		die("Connection to database failed! ".$conn->connect_error);
	}
	
	if($handle = fopen($csvp,"r")){						# open csv 
		if($tempdata = file_get_contents($csvp)){
			$tempid = getnewtempid();
				
			$sql_inserttempdata = "insert into temperature (id,tempdata,createddatetime) values ('".$tempid."','".$tempdata."',Now())";
			
			$inserttempdata_result = $conn->query($sql_inserttempdata);
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
	global $username, $password, $hostname, $database, $imgfolder;
	
	$conn = new mysqli($hostname, $username, $password,  $database);

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
	global $username, $password, $hostname, $database;

	$conn = new mysqli($hostname, $username, $password, $database);
	
	if($conn->connect_error){
		die("Connection to database failed! ".$conn->connect_error);
	}
	
	$sql_insertrelation = "insert into imagetemprel (id,imageid,tempid) values ('".getnewrelid()."','".$imageid."','".$newtempid."')";
	
	$insertrelation_result = $conn->query($sql_insertrelation);
	
	return $insertrelation_result;
}

function showlatest(){
	global $username, $password, $hostname, $database, $imgfolder;
	
	$conn = new mysqli($hostname, $username, $password,  $database);

	if($conn->connect_error){
		die("Connection to database failed! ".$conn->connect_error);
	}
	
	$sql_lastimageid = "select max(id) as max_imageid, max(image) as image from image";
	
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
	<div class="container"><a href="<?php echo $image ?>"><img src="<?php echo $image ?>" ></a> 
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
	global $TESTMODE;
	if (!$TESTMODE){
		$ini_array = parse_ini_file("tipi.ini",true); 							# array to store the ini file contents
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

function download() {
	$ini_array = parse_ini_file("tipi.ini",true); 							# array to store the ini file contents
	$path = './phpseclib1.0.11/';						 					# path for phpseclib files

	global $TESTMODE,$newrelid,$newimageid,$newtempid;
	if($TESTMODE){
		echo "<hr/>";
		echo "<br/>newrelid: ".getnewrelid()."<br/>";
		echo "<br/>newimageid: ".getlastimageid()."<br/>";
		echo "<br/>newtempid: ".getnewtempid()."<br/>";
		echo "<hr/>";
	}
	set_include_path(get_include_path() . PATH_SEPARATOR . $path); 			# modify include path
	include "Net/SFTP.php"; 												# include sftp library

	#$remotepath = '/home/pi/TI/';										# remote path for rpi
	$remotepath = 'TI/';												# remote path for local testing
	
	global $csva, $imagea;										# bring image and csv file name arrays to function
	global $imgfolder;
	
	if (!$TESTMODE){
		for ($y=0;$y<count($ini_array['pis']['pi_name']);$y++) {            # loop through pis
			
			$name = $ini_array['pis']['pi_name'][$y];								# get name of pi
			$username = $ini_array['pis']['pi_login'][$y]; 							# get username from ini file array
			$password = $ini_array['pis']['pi_pw'][$y]; 								# get password from ini file array
			$host = $ini_array['pis']['pi_ip'][$y]; 							# get hostname (ip address) from ini file array

			$date = new Datetime();							# date variable for file name 

			echo "<b>Image Acquired: </b>".$name." on ".$date->format('m-d-Y')." at ".$date->format('h:i:s')."<br>"; # output current pi name to page
		
			$sftp = new Net_SFTP($host); 											# create sftp object for host
			
			if (!$sftp->login($username, $password)) { 									# create sftp connection object for host
				exit('Login Failed'); 											# leave if the login fails
			}
		
			for($x=0;$x<$ini_array['pis']['pi_camcount'][$y];$x++){           # loop through cameras for image
				$imageid = getnewimageid();
				if(!$sftp->get($remotepath.$imagea[$x],$imgfolder.$imageid.".bmp")){
					Exit('Failed to download image: '.$remotepath.$imagea[$x]);
				}
				insertnewimage($imageid);
				if(!$sftp->get($remotepath.$csva[$x],$csva[$x])){
					Exit('Failed to download csv: '.$remotepath.$csva[$x]);
				}
				$newtempid = inserttempdata($csva[$x]);
				insertrelation($imageid,$newtempid);
			}
		}
		showlatest();	
	}
}

function searchbydate($date){
    global $username, $password, $hostname, $database, $imgfolder;
	
    $conn = new mysqli($hostname, $username, $password,  $database);

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
