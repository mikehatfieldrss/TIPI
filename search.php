<html>
<head>
<link href="ti.css" rel="stylesheet" type="text/css" media="all">
<?php
#INCLUDE########################################################################
include 'tipifunctions.php';

#VARIABLES######################################################################
date_default_timezone_set('PST8PDT');																						# Set Pacific Timezone

$dbhostname = "192.168.70.33";                                                  # mysql server address
$dbusername = "webuser";                                                        # mysql username
$dbpassword = "webuser123";                                                     # mysql password
$database = "tipi";                                                             # mysql database

$imgfolder = "images/";                                                         # folder for images
$datepicked = "";                                                               # "declare" datepicked variable
?>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<script src="https://code.jquery.com/jquery-1.12.4.js"></script>                <!-- load jquery -->
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>          <!-- load jquery ui-->
<script>
$( function() {
  $( "#datepicker" ).datepicker({                                               // build jquery datepicker
          dateFormat:"yy-mm-dd"                                                 // format date as YYYY-MM-DD
  });
});
</script>
<a href="ti.php">HOME</a> - <a href='archive.php'>ARCHIVE</a> - <a href='search.php'>SEARCH</a><br><br>  <!-- navigation bar -->
</head>
<body>
  <form name="form1" action="<?php echo $_SERVER["PHP_SELF"]; ?>" method="POST">  <!-- build for for handling post -->
    <p>Date: <input type="text" name="txtDate" id="datepicker" size="6"></p>      <!-- build datepicker -->
    <input type="submit" name="submit" value="Search">                            <!-- submit button -->
  </form>
<?php
  if(isset($_POST["submit"]))                                                    # // <!-- was submit part of the post? -->
  {
    if($_POST["txtDate"]){                                                       # was the date picker variable passed? might be inital load
    $datepicked= $_POST["txtDate"];                                             # set datepicked var
    }
    else {
      echo "<br/>Pick a date<br/>";                                             # echo instructions to page
      exit;
    }
  }

  global $dbusername, $dbpassword, $dbhostname, $database;

  $conn = new mysqli($dbhostname, $dbusername, $dbpassword, $database);

  if($conn->connect_error){
  	die("Connection to database failed! ".$conn->connect_error);
  }
  if($datepicked){
    $sql_imagelist = "select image, date(createddatetime) as thedate, time(createddatetime) as thetime from image where Date(createddatetime) = '$datepicked'";  				# sql to get image filenames that have temperature data
    $imagelist_result = $conn->query($sql_imagelist);																# get archive images from $database
    echo "<div class='flex-container'>";																						# build flex object for images
    if ($imagelist_result->num_rows > 0){
      while($row = $imagelist_result->fetch_assoc()){
    		echo "<div>";																																# create flex item for each image
        $thedate = $row["thedate"];																				# read image date
    		$thetime = substr($row["thetime"],0,5);
    		$image = $row["image"];																											# read image
		    echo $image.":<br/>$thedate at $thetime<br/><a href='$image'><img class='oldimg' src='$image'></a><br/>"; # insert image as a link on page
    		$conn2 = new mysqli($dbhostname, $dbusername, $dbpassword, $database);			# create sql connection to read temperature data for current image

    		if($conn2->connect_error){
    			die("Connection to database failed! ".$conn2->connect_error);
    		}

    		$sql_gettempdata = "select tempdata from temperature t join imagetemprel i on i.tempid = t.id where i.imageid in (select id from image where image = '".$image."')"; # sql to get temperature data for current image
    		$gettempdata_result = $conn2->query($sql_gettempdata);											# get temperature data from $database
    		if ($gettempdata_result->num_rows > 0){																			# loop through all temperature data
    			while($row = $gettempdata_result->fetch_assoc()){
            $temp = explode(",", $row["tempdata"]);																	# write temperature data from database to string variable as CSV
    				array_pop($temp);																												# drop last value which is 0. the CSV terminates with a comma so a 0 is appended to the data
    ?>
    		<div class="archivebottomleft"><b>																					<!-- css class for lower left temperature value on image -->
    		<?php echo min($temp); ?>																										<!-- value for lower left temperature value on image -->
    	</div><div class="archivebottomright">																				<!-- css class for lower left temperature value on image -->
    		<?php echo max($temp); ?></b>																								<!-- value for lower right temperature value on image -->
    	</div><br/>
    <?php
    		$conn2->close();
    	}
    }
    Else {
    		echo "<br/><B>TEMPERATURE DATA NOT FOUND</B><br/>";													# print message when no temperature data is found for current image
    	}
      echo "</div>";																																	# close flex object div
    		}
    	}
      else {
        echo "0 results returned";
      }
    }
    else { echo "Pick a date";}
  ?>
</body>
</html>
