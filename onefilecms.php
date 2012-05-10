<?php
// OneFileCMS - http://onefilecms.com/
// For license & copyright info, see OneFileCMS.License.BSD.txt

$version = "1.3.0";


if( phpversion() < '5.0.0' ) { exit("OneFileCMS requires PHP5 to operate. Please contact your host to upgrade your PHP installation."); };



// CONFIGURABLE INFO

$config_username  = "username";
$config_password  = "password";
$config_title     = "OneFileCMS";

$config_style_sheet = "onefilecms.css";  //Relative to this file.
//$config_style_sheet ="/onefilecms.css";  //Relative to site URL root.
//$config_style_sheet = "http://self-evident.github.com/OneFileCMS/onefilecms.css";

$MAX_IMG_W   = 810;   // Max width to display images. (page container = 810)
$MAX_IMG_H   = 1000;  // Max height.  I don't know, it just looks reasonable.

$config_favicon   = "/favicon.ico";
$config_editable  = "html,htm,php,css,js,txt,text,cfg,conf,ini,csv,svg";
$config_excluded  = ""; //files to exclude from directory listings
$config_itypes    = "jpg,gif,png,bmp,ico";  // Can be displayed on edit page.
$config_ftypes    = "jpg,gif,png,bmp,ico,svg,txt,cvs,css,php,htm,html,cfg,conf,js"; //used to select file icon
$config_fclass    = "img,img,img,img,img,svg,txt,txt,css,php,htm,htm,cfg,cfg,txt";  //used to select file icon
// END CONFIGURABLE INFO



//Make arrays out of a few $config_variables.  They are used in Index_Page() .
//Above, however, it's easier to config/change a simple string.
$ftypes   = (explode(",", strtolower($config_ftypes)));
$fclasses = (explode(",", strtolower($config_fclass)));
$itypes   = (explode(",", strtolower($config_itypes)));


$ONESCRIPT = $_SERVER["SCRIPT_NAME"];
$DOC_ROOT  = $_SERVER["DOCUMENT_ROOT"].'/';
$WEBSITE   = $_SERVER["HTTP_HOST"];

//Allows OneFileCMS.php to be started from any dir on the site.
chdir($DOC_ROOT);





//******************************************************************************
session_start();
global $page; $page = "index";
global $pagetitle; $pagetitle = $_SERVER['SERVER_NAME'];

if (isset($_POST["onefilecms_username"])) { $_SESSION['onefilecms_username'] = $_POST["onefilecms_username"]; }
if (isset($_POST["onefilecms_password"])) { $_SESSION['onefilecms_password'] = $_POST["onefilecms_password"]; }

if (($_SESSION['onefilecms_username'] == $config_username) and ($_SESSION['onefilecms_password'] == $config_password || md5($_SESSION['onefilecms_password']) == $config_password)) {
	$_SESSION['onefilecms_valid'] = "1";
} else {
	$_SESSION['onefilecms_valid'] = "0";
	$page = "login";
	$_GET["p"] = "login";
	unset($_GET["c"]);
	unset($_GET["d"]);
	unset($_GET["f"]);
	unset($_GET["i"]);
	unset($_GET["r"]);
}

if (isset($_GET["p"])) {
	// redirect on invalid page attempts
	$page = $_GET["p"];
	if (!in_array(strtolower($_GET["p"]), array(
		"copy","delete","deletefolder","edit","newfolder","index","login","logout","new","rename","renamefolder","upload"	)))
	{
		header("Location: ".$ONESCRIPT);
		$page = "index";
	}
}

//Check if "i" path exists & trim trailing slashes ///
function Check_ipath() { global $message;
	if (isset($_GET["i"])) {
		$_GET["i"] = rtrim($_GET["i"],"/");
		if (!is_dir($_GET["i"])) { $message = "Does not exist: ".$_GET["i"]; }
		while (!is_dir($_GET["i"])) { $_GET["i"] = dirname($_GET["i"]); }
		if ($_GET["i"] == '.') {unset($_GET["i"]);}
	}
}//end Check_ipath()
Check_ipath();

if ( ($page == "login") and ($_SESSION['onefilecms_valid']) ) {	
	$page = "index";
	header("Location: ".$ONESCRIPT);
}

if ($_GET["p"] == "login") { $pagetitle = "Log In"; }

if ($_GET["p"] == "logout") {
	$page = "login";
	$pagetitle = "Login";
	$_SESSION['onefilecms_valid'] = "0";
	session_destroy();
	$message = 'You have successfully logged out.';
}

if ($_GET["i"] == "") { unset($_GET["i"]); }
// End session startup**********************************************************




// entitize $_GET params *******************************************************
foreach ($_GET as $name => $value) { $_GET[$name] = htmlentities($value); }




//******************************************************************************
// Misc Functions

function is_empty($path){
	$empty = false;
	$dh = opendir($path);
	for($i = 3; $i; $i--) { $empty = (readdir($dh) === FALSE); }
	closedir($dh);
	return $empty;
}//end is_emtpy()



function Close_Button($classes) { //********************
	echo '<input type="button" class="button '.$classes.'" name="close" value="Close" onclick="parent.location=\'';
	echo $ONESCRIPT.'?i='.substr($_GET["f"],0,strrpos($_GET["f"],"/")).'\'">';
	?><script>document.edit_form.elements[1].focus();</script><?php // focus on [Close]
}// End Close_Button() //*******************************



function Cancel_Submit_Buttons($button_label) { //******
	global $ONESCRIPT, $varvar;

	// [Cancel] returns to either the current/path, or current/path/file
	if      ( isset($_GET["c"]) ) { $ipath = '?f='.$_GET["c"]; }
	else if ( isset($_GET["d"]) ) { $ipath = '?f='.$_GET["d"]; }
	else if ( isset($_GET["r"]) ) { $ipath = '?f='.$_GET["r"]; }
	else if	( isset($_GET["i"]) ) { $ipath = '?i='.rtrim($_GET["i"],"/"); }
	else    {                       $ipath = rtrim($varvar,"/"); }//end if/else

	?>
	<p>
		<input type="button" class="button" id="cancel" name="cancel" value="Cancel" onclick="parent.location='<?php echo $ONESCRIPT.$ipath; ?>'">
		<input type="submit" class="button" value="<?php echo $button_label;?>" style="margin-left: 1.3em;">
	</p>
	<script>document.getElementById('cancel').focus();</script>
	<?php
}// End Cancel_Submit_Buttons() //**********************



function message_box() { //*****************************
	global $ONESCRIPT, $message, $page;

	if (isset($message)) {
	?>
		<div id="message"><p>
		<!-- [X] to dismiss message box -->	
		<span><a href='<?php echo $ONESCRIPT.'?'.$_SERVER['QUERY_STRING']; ?>'
		onclick='document.getElementById("message").innerHTML = " ";return false'>
		[X]</a>
		</span>
		<?php echo $message.PHP_EOL ;?>
		</p></div>
	<?php } else {
		// On Edit page only, preserve vertical spacing for message even when empty.
		if ($page == "edit") { echo '<div id="message"></div>';}
	} //end isset($message)
} //end message_box()  *********************************



function show_image(){ //*******************************
	global $filename, $MAX_IMG_W, $MAX_IMG_H;
	
	$IMG = $filename;
	$img_info = getimagesize($IMG);

	$W=0; $H=1;
	$SCALE = 1; $TOOWIDE = 0; $TOOHIGH = 0;
	if ($img_info[$W] > $MAX_IMG_W) { $TOOWIDE = ( $MAX_IMG_W/$img_info[$W] );}
	if ($img_info[$H] > $MAX_IMG_H) { $TOOHIGH = ( $MAX_IMG_H/$img_info[$H] );}
	
	if ($TOOHIGH || $TOOWIDE) {
		if     (!$TOOWIDE)           {$SCALE = $TOOHIGH;}
		elseif (!$TOOHIGH)           {$SCALE = $TOOWIDE;}
		elseif ($TOOHIGH > $TOOWIDE) {$SCALE = $TOOWIDE;} //ex:if (.90 > .50)
		else                         {$SCALE = $TOOHIGH;}
	}

	echo '<p class="file_meta">';
	echo 'Image shown at ~'. round($SCALE*100) .'% of full size ('.$img_info[3].').</p>';
	echo '<div style="clear:both;"></div>';
	echo '<a href="/' . $IMG . '">';
	echo '<img src="/'.$IMG.'"  height="'.$img_info[$H]*$SCALE.'"></a>';
}// end show_image() ***********************************



function show_favicon(){
	global $config_favicon, $DOC_ROOT;
	if (file_exists($DOC_ROOT.$config_favicon)) { 
		echo '<img src="'.$config_favicon.'" alt="">'; 
	}
}// end show_favicon()

// End of misc funtions ********************************************************







// COPY FILE response code *****************************************************
if (isset($_GET["c"])) {
	$page = "copy"; $filename = $_GET["c"]; $pagetitle = "Copy";
}

if (isset($_POST["copy_filename"]) && $_SESSION['onefilecms_valid'] = "1" && $_POST["sessionid"] == session_id()) {
	$old_filename = $_POST["old_filename"];
	$filename = $_POST["copy_filename"];

	if (copy($old_filename, $filename)){ 
		$message  = '<b>"'.$old_filename.'"</b><br>';
		$message .= ' --- successfully copied to ---<br>';
		$message .= '<b>"'.$filename.'"</b>.';
	}else{
		$message .= '<b>(!) Error copying file:<br>"'.$filename.'"</b>.';
	}
}//end COPY FILE response code *************************************************





// DELETE FILE response code ***************************************************
if (isset($_GET["d"])) {
	$page = "delete"; $filename = $_GET["d"]; $pagetitle = "Delete";
}

if (isset($_POST["delete_filename"]) && $_SESSION['onefilecms_valid'] = "1" && $_POST["sessionid"] == session_id()) {
	$filename = $_POST["delete_filename"];

	if (unlink($filename)) {
		$message = '"<b>'.basename($filename).'</b>" successfully deleted.';
	}else{
		$message = '<b>(!) Error deleting "'.$filename.'"</b>.';
	}
}//end DELETE FILE response code ***********************************************





// DELETE FOLDER response code *************************************************
if ($_GET["p"] == "deletefolder") {
	if (!is_empty($_GET["i"])){
		$message = '<b>(!) Folder is not empty.</b>  Folders must be empty before they can be deleted.<br>';
		$page = "index";
		}
	else { $pagetitle = "Delete Folder"; }
}

if (isset($_POST["delete_foldername"]) && $_SESSION['onefilecms_valid'] = "1" && $_POST["sessionid"] == session_id()) {
	$foldername = $_POST["delete_foldername"];
	$_GET["i"] = $foldername;
	if (@rmdir($foldername)) {
		$_GET["i"] = dirname($foldername);
		$message = '"<b>'.basename($foldername).'/</b>" successfully deleted.';
	} else {
		$message = '<b>(!) "'.$foldername.'/"</b> is not empty, or other error occurred.';
	}
}//end DELETE FOLDER response code *********************************************





// EDIT Page response code *****************************************************

//*** If on Edit page, and [Save] clicked:
if (isset($_POST["filename"]) && $_SESSION['onefilecms_valid'] = "1" && $_POST["sessionid"] == session_id()) {
	$filename = $_POST["filename"];
	$content = stripslashes($_POST["content"]);
	$fp = @fopen($filename, "w");
	if ($fp) {
		fwrite($fp, $content);
		fclose($fp);
		$message = '<b>"'.$filename.'"</b> saved successfully.';
	}else{
		$message = '<b>(!) There was an error saving file.';
	}
}//***

//*** If in directory list, and a filename is clicked:
if (isset($_GET["f"])) {
	$filename = stripslashes($_GET["f"]);
	if (file_exists($filename)) {
		$page = "edit";
		$pagetitle = "Edit/View File";
		$fp = @fopen($filename, "r");
		if (filesize($filename) !== 0) {
			$filecontent = fread($fp, filesize($filename));
			$filecontent = htmlspecialchars($filecontent);
		}
		fclose($fp);
	} else {
		$page = "index";
		$message = '<b>"'.$filename.'"</b> does not exist.';
		unset ($filename);
	}
}//***
//End Edit page response code **************************************************





// NEW FILE response code ******************************************************
if ($_GET["p"] == "new") {$pagetitle = "New File"; }
if (isset($_POST["new_filename"]) && $_SESSION['onefilecms_valid'] = "1" && $_POST["sessionid"] == session_id()) {
	$filename  = $_POST["new_filename"];
	$_GET["i"] = $filename; Check_ipath();
	if (file_exists($filename)) {
		$message = '<b>(!) "'.$filename.'"</b> not created. A file with that name already exists.';
	} else {
		$handle = fopen($filename, 'w') or die("can't open file");
		fclose($handle);
		$message = '"<b>'.$filename.'</b>" created successfully.';
		$_GET["i"] = dirname($filename); //return to file's directory.
	}
}//end NEW FILE response code **************************************************





// NEW FOLDER response code ****************************************************
if ($_GET["p"] == "newfolder") {$pagetitle = "New Folder"; }
if (isset($_POST["new_folder"]) && $_SESSION['onefilecms_valid'] = "1" && $_POST["sessionid"] == session_id()) {
	$foldername = $_POST["new_folder"];
	$_GET["i"] = $foldername; Check_ipath();
	if (!is_dir($foldername)) {
		mkdir($foldername);
		$message = '"<b>'.$foldername.'/</b>" created successfully.';
		$_GET["i"] = $foldername;  //change to new directory
	} else {
		$message  = '<b>(!)</b> Folder already exists: ';
		$message .= '<b>'.$foldername.'/</b>';
	}
}//end NEW FOLDER response code ************************************************





// RENAME FILE response code ***************************************************
if (isset($_GET["r"])) {
	$filename = $_GET["r"];
	$pagetitle = "Rename File";
	$page = "rename";
}
if (isset($_POST["rename_filename"]) && $_SESSION['onefilecms_valid'] = "1" && $_POST["sessionid"] == session_id()) {
	$old_filename = $_POST["old_filename"];
	$filename = $_POST["rename_filename"];

	//Removed any trailing slashes
	$filename = rtrim($filename, '/');

	rename($old_filename, $filename);
	$message .= '"<b>'.$old_filename.'</b>"<br>';
	$message .= ' &nbsp; successfully renamed to:<br>';
	$message .= '"<b>'.$filename.'</b>"';
}//end RENAME FILE response code ***********************************************





// RENAME FOLDER response code *************************************************
if ($_GET["p"] == "renamefolder") {$pagetitle = "Rename Folder"; }
if (isset($_POST["new_foldername"]) && $_SESSION['onefilecms_valid'] = "1" && $_POST["sessionid"] == session_id()) {

	$old_foldername = $_POST["old_foldername"];
	$new_foldername = $_POST["new_foldername"];
	$_GET["i"]      = $old_foldername;
	Check_ipath();

	//Removed any trailing slashes
	$old_foldername = rtrim($old_foldername, '/');
	$new_foldername = rtrim($new_foldername, '/');

	if (rename($old_foldername, $new_foldername)) {
		$message .= '<b>"'.$old_foldername.'/"</b><br>';
		$message .= ' &nbsp; successfully renamed to:<br>';
		$message .= '<b>"'.$new_foldername.'/"</b>';
		$_GET["i"] = $new_foldername;  //return to new folder
	} else {
		$message = "<b>(!)</b> There was an error during rename. Try again and/or contact your admin.";
	}
}//end RENAME FOLDER response code *********************************************





// UPLOAD FILE response code ***************************************************
if ($_GET["p"] == "upload") {$pagetitle = "Upload File"; }
if (isset($_FILES['upload_filename']['name']) && $_SESSION['onefilecms_valid'] = "1" && $_POST["sessionid"] == session_id()) {

	$filename    = $_FILES['upload_filename']['name'];
	$newfilename = $filename;
	$destination = $_POST["upload_destination"];
	$destintaion = rtrim($destination,"/").'/';  //make sure only a single trailing slash
	$savefile    = $destination.$filename;
	$_GET["i"]   = rtrim($destination,"/");

	if (($filename == "")){ 
		$message = "<b>(!) No file selected for upload... </b>";
	}else{
		$message   = 'Uploading: <b>"'.$filename.'"</b> to <b>"'.$destination.'"</b>';
		
		//if file_exists(), serialize filename until it doesn't
		$serialize = 0;
		if (file_exists($savefile)) {
			$message .= '<br><b>(!)</b> A file with that name already exists in the target directory.<br>';
			$savefile_info = pathinfo($savefile);

			while (file_exists($savefile)) {
				$serialize = sprintf("%04d", ++$serialize); //  0001, 0002, 0003, etc...
				$newfilename = $savefile_info['filename'].'.'.$serialize.'.'.$savefile_info['extension'];
				$savefile = $destination . $newfilename;
			}
			$message .= 'Saving as: <b>"</b>'.'<b>'.$newfilename.'"</b>';
		}
		//end serialize filename *****************************/

		if(move_uploaded_file($_FILES['upload_filename']['tmp_name'], $savefile)) {
			$message .= '<br>Upload successful.';
		} else{
			$message .= "<br><b>(!) There was an error.</b> Upload or rename may have failed.";
		}
	}
} //end Upload file response code **********************************************










function Login_Page() { //******************************************************
	global $ONESCRIPT;
?>
	<h2>Log In</h2>
	<form method="post" action="<?php echo $ONESCRIPT; ?>">
		<p>
			<label for="onefilecms_username">Username:</label>
			<input type="text" name="onefilecms_username" id="onefilecms_username" class="login_input">
		</p>
		<p>
			<label for="onefilecms_password">Password:</label>
			<input type="password" name="onefilecms_password" id="onefilecms_password" class="login_input">
		</p>
			
		<input type="submit" class="button" value="Enter">
	</form>
	<script>document.getElementById('onefilecms_username').focus();</script>
<?php } //end Login_Page() *****************************************************





function list_files() { // ...in a vertical table ******************************
	
global $ONESCRIPT, $varvar, $config_excluded, $ftypes, $fclasses;
	
$files = glob($varvar."{,.}*", GLOB_BRACE);
natcasesort($files);

echo '<table class="index_T">';
	foreach ($files as $file) {
		$fc++;
		$excludeme = 0;
		$config_excludeds = explode(",", $config_excluded);
			
		foreach ($config_excludeds as $config_exclusion) {
			if (strrpos(basename($file),$config_exclusion) !== False && 
			strrpos(basename($file),$config_exclusion) !== "") { 
				$excludeme = 1;
			}
		}

		if (!is_dir($file) && $excludeme == 0) {
			
			//Determine file type & set cooresponding class.
			$file_class = "";
			$ext = end( explode(".", strtolower($file)) );
			
			for ($x=0; $x < count($ftypes); $x++ ){
				if ($ext == $ftypes[$x]){ $file_class = $fclasses[$x]; } 
			}
?>
			<tr>
				<td>
					<?php echo "<a href='", $ONESCRIPT, "?f=", $file, "'"; ?>
					<?php echo 'class="',  $file_class, '">', basename($file), '</a>'; ?>
				</td>
				<td class="meta_T meta_size">&nbsp;
					<?php echo number_format(filesize($file)).""; ?> B
				</td>
				<td class="meta_T meta_time"> &nbsp;
					<script>FileTimeStamp(<?php echo filemtime($file); ?>);</script>
				</td>
			</tr>
<?php 
		}//end if !is_dir
	}//end foreach file
echo '</table>';
}//end list_files() ************************************************************





function Index_Page(){ //*******************************************************
	global $ONESCRIPT, $varvar, $config_excluded, $ftypes, $fclasses;
	$varvar = ""; //must be global - also used in list_files()
	if (isset($_GET["i"])) { $varvar = $_GET["i"]."/"; }

 	// Current path. ie: docroot/current/path/ 
	// Each level is a link to that level.
	echo '<h2>';
		$full_path = basename(getcwd());
		if (isset($_GET["i"])) { $full_path = basename(getcwd()).'/'.$_GET["i"]; }

		$path_levels = explode("/",$full_path);
		$levels = count($path_levels); //If levels=3, indexes = 0, 1, 2  etc...

		//docroot folder of site
		if ($_GET["i"] == "") { 
			echo $path_levels[0].' /'; // if at root, no need for link.
		} else {
			echo '<a href="'.$ONESCRIPT.'" class="path"> '.$path_levels[0].' </a>/';
		}

		//Remainder of current/path
		for ($x=1; $x < $levels; $x++) {
			if ($x !== 1){ $current_path .= '/'; }
			$current_path = $current_path.$path_levels[$x];
			echo '<a href="'.$ONESCRIPT.'?i='.$current_path.'" class="path"> ';
			echo ' '.$path_levels[$x]." </a>/";
		}
	?></h2>

	<!--==== List folders/sub-directores ====-->
	<p class="index_folders">
		<?php
		$folders = glob($varvar."*",GLOB_ONLYDIR);
		natcasesort($folders);
		foreach ($folders as $folder) {
			echo '<a href="'.$ONESCRIPT.'?i='.$folder.'" class="index_folder">';

			echo basename($folder).' /</a>';
		} ?>
	</p>

	<?php list_files(); //******************* ?>
	
	<!-- Upload/New/Rename/Copy/etc... links -->
	<p class="front_links">
		<a href="<?php echo $ONESCRIPT.'?p=upload&amp;i='.$varvar; ?>"    class="upload">Upload File</a>
		<a href="<?php echo $ONESCRIPT.'?p=new&amp;i='.$varvar; ?>"       class="new">New File</a>
		<a href="<?php echo $ONESCRIPT.'?p=newfolder&amp;i='.$varvar; ?>" class="newfolder">New Folder</a>
		<?php if ($varvar !== "") { ?>
			<a href="<?php echo $ONESCRIPT.'?p=renamefolder&amp;i='.$varvar; ?>" class="renamefolder">
			Rename Folder</a>
			<a href="<?php echo $ONESCRIPT.'?p=deletefolder&amp;i='.$varvar; ?>" class="deletefolder">
			Delete Folder</a>
		<?php } ?>
	</p>
<?php
}//end Index_Page()*************************************************************






function Edit_Page() { //*******************************************************
	global $ONESCRIPT, $varvar, $filename, $filecontent, $config_editable, $config_itypes;
	$varvar = '?f='.$filename;
	$ext = end( explode(".", strtolower($filename)) );
?>
	<h2 id="edit_header">File: &ldquo;<a href="/<?php echo $filename; ?>"> 
	<?php echo $filename; ?> 
	</a>&rdquo;</h2>

	<form id="edit_form" name="edit_form" method="post" action="<?php echo $ONESCRIPT.$varvar; ?>">
		<p class="file_meta">
		<span class="meta_size">Size<b>: </b> <?php echo number_format(filesize($filename)); ?> bytes</span> &nbsp; &nbsp; 
		<span class="meta_time">Updated<b>: </b><script>FileTimeStamp(<?php echo filemtime($filename); ?>, 1);</script></span><br>
		</p>
		<input type="hidden" name="sessionid" value="<?php echo session_id(); ?>">
		<?php Close_Button("close"); ?><div style="clear:both;"></div>
		
		<?php if (strpos($config_itypes,$ext) === false) { //If non-image, show textarea
			if (strpos($config_editable,$ext) === false) { //
			?>	<p>
				<textarea id="disabled_content" class="textinput disabled" cols="70" rows="3" disabled="disabled">Non-text or unkown file type. Edit disabled.</textarea>
				</p>
			<?php } else { ?>
				<p>
				<input type="hidden" name="filename" id="filename" class="textinput" value="<?php echo $filename; ?>">
				<textarea id="file_content" onkeyup="Check_for_changes(event);" name="content" class="textinput" cols="70" rows="25"><?php echo $filecontent; ?></textarea>
				</p>
			<?php } //end if editable ?>	
		<?php  } //end if non-image, show textarea ?>
		
		<p class="buttons_right">
		<input type="submit" class="button" value="Save"                  onclick="submitted = true;" id="save_file">
		<input type="button" class="button" value="Reset - loose changes" onclick="Reset_File()"      id="reset">
		<script>
			document.getElementById('save_file').disabled = "disabled";
			document.getElementById('reset').disabled     = "disabled";
		</script>
		<input type="button" class="button" value="Rename/Move"           onclick="parent.location='<?php echo $ONESCRIPT.'?r='.$filename; ?>'">
		<input type="button" class="button" value="Delete"                onclick="parent.location='<?php echo $ONESCRIPT.'?d='.$filename; ?>'">
		<input type="button" class="button" value="Copy"                  onclick="parent.location='<?php echo $ONESCRIPT.'?c='.$filename; ?>'">
		<?php Close_Button(""); ?>
		</p>
	</form>
	<div style="clear:both;"></div>

	<?php if (strpos($config_itypes,$ext) !== false) { show_image(); } ?>

	<?php if (strpos($config_editable,$ext) !== false) { //if editable file..?>
		<?php Edit_Page_javascript(); ?>
		<div id="edit_note">
		NOTE: On some browsers, such as Chrome, if you click the browser [Back] then browser [Forward] (or vice versa), the file state may not be accurate.  To correct, click the browser's [Reload].
		</div>
	<?php } //end if editable ?>

<?php }; //End Edit_Page *******************************************************






function Upload_Page() { //*****************************************************
	global $ONESCRIPT, $varvar;
	$varvar = ""; if (isset($_GET["i"])) { $varvar = "?i=".$_GET["i"]; }
?>
	<h2>Upload</h2>
	<form enctype="multipart/form-data" action="<?php echo $ONESCRIPT.$varvar; ?>" method="post">
		<input type="hidden" name="sessionid" value="<?php echo session_id(); ?>">
		<input type="hidden" name="MAX_FILE_SIZE" value="100000">
		<p>
			<label for="upload_destination">Destination:</label>
			<input type="text" name="upload_destination" value="<?php echo $_GET["i"]; ?>/" class="textinput">
		</p>
		<p>
			<label for="upload_filename">File:</label>
			<input name="upload_filename" type="file" size="93">
		</p>
		<p><?php Cancel_Submit_Buttons("Upload"); ?></p>
	</form>
<?php } //end Upload_Page() ****************************************************





function New_File_Page() { //***************************************************
	global $ONESCRIPT, $varvar;
	$varvar = "";
	if (isset($_GET["i"])) { $varvar = "?i=".$_GET["i"]; }
?>
		<h2>New File</h2>
		<p>Existing files with the same name will not be overwritten.</p>
		<form method="post" id="new" action="<?php echo $ONESCRIPT.$varvar; ?>">
			<input type="hidden" name="sessionid" value="<?php echo session_id(); ?>">
			<p>
				<label for="new_filename">New filename: </label>
				<input type="text" name="new_filename" id="new_filename" class="textinput" value="<?php echo $_GET["i"]; ?>/">
			</p>
			<p>	<?php Cancel_Submit_Buttons("Create"); ?> </p>
		</form>
<?php
}//end New_File_Page()**********************************************************





function Copy_File_Page(){ //***************************************************
	global $ONESCRIPT, $varvar, $filename;
	
	$extension    = strrchr($filename, ".");
	$slug         = dirname($filename).'/'.pathinfo($filename,PATHINFO_FILENAME);
	$varvar       = "?i=".dirname($_GET["c"]);
	$new_filename = $slug."_COPY_".date("YmdHi").$extension;
?>
	<h2>Copy &ldquo;<a href="/<?php echo $filename; ?> "> <?php echo $filename; ?> </a> &rdquo;</h2>
	<p><b>( ! )</b> Existing files with the same filename are automatically overwritten... Be careful!</p>

	<form method="post" id="new" action="<?php echo $ONESCRIPT.$varvar; ?>">
		<input type="hidden" name="sessionid" value="<?php echo session_id(); ?>">
		<p>
			<label>Old filename:</label>
			<input type="hidden" name="old_filename" value="<?php echo $filename; ?>">
			<input type="text" name="dummy" value="<?php echo $filename; ?>" class="textinput" disabled="disabled">
		</p>
		<p>
			<label for="copy_filename">New filename:</label>
			<input type="text" name="copy_filename" id="copy_filename" 
			       class="textinput" value="<?php echo $new_filename; ?>">
		</p>
		<p>	<?php Cancel_Submit_Buttons("Copy"); ?>	</p>
	</form>
<?php }//end Copy_File_Page() **************************************************





function Rename_File_Page() { //************************************************
	global $ONESCRIPT, $varvar, $filename;
	$varvar = "?i=".dirname($_GET["r"]);
?>
	<h2>Rename &ldquo;<a href="/<?php echo $filename; ?>">	<?php echo $filename; ?> </a>&rdquo;</h2>
	<p>Existing files with the same filename are automatically overwritten... Be careful!</p>
	<p>To move a file, preface its name with the folder's name, as in 
	"<i>foldername/filename.txt</i>." The folder must already exist.</p>

	<form method="post" action="<?php echo $ONESCRIPT.$varvar;	?>">
		<input type="hidden" name="sessionid" value="<?php echo session_id(); ?>">
		<p>
			<label>Old filename:</label>
			<input type="hidden" name="old_filename" value="<?php echo $filename; ?>">
			<input type="text" name="dummy" value="<?php echo $filename; ?>" class="textinput" disabled="disabled">
		</p>
		<p>
			<label for="rename_filename">New filename:</label>
			<input type="text" name="rename_filename" id="rename_filename" class="textinput" value="<?php echo $filename; ?>">
		</p>
		<p><?php Cancel_Submit_Buttons("Rename"); ?></p>
	</form>
<?php } //end Rename_File_Page() ***********************************************





function Delete_File_Page() { //************************************************
	global $ONESCRIPT, $varvar, $filename;
	$varvar = '?i='.dirname($_GET["d"]); 
?>
	<h2>Delete &ldquo;<a href="/<?php echo $filename; ?>">
	<?php echo $filename; ?></a> &rdquo; ?</h2>
	<p>Are you sure?</p>

	<form method="post" action="<?php echo $ONESCRIPT.$varvar; ?>">
		<input type="hidden" name="sessionid" value="<?php echo session_id(); ?>">
		<p>
			<input type="hidden" name="delete_filename" value="<?php echo $filename; ?>">
			<?php Cancel_Submit_Buttons("DELETE"); ?>
		</p>
	</form>
<?php } //end Delete_File_Page() ***********************************************





function New_Folder_Page() { //*************************************************
	global $ONESCRIPT, $varvar;
	$varvar = "";
	if (isset($_GET["i"])) { $varvar = "?i=".$_GET["i"]; }
?>
	<h2>New Folder</h2>
	<p>Existing folders with the same name will not be overwritten.</p>
	<form method="post" action="<?php echo $ONESCRIPT.$varvar; ?>">
		<input type="hidden" name="sessionid" value="<?php echo session_id(); ?>">
		<p>
			<label for="new_folder">Folder name: </label>
			<input type="text" name="new_folder" id="new_folder" class="textinput" value="<?php echo $_GET["i"]; ?>/">
		</p>
		<p>	<?php Cancel_Submit_Buttons("Create"); ?> </p>
	</form>
<?php } // end New_Folder_Page() ***********************************************





function Rename_Folder_Page() { //**********************************************
	global $ONESCRIPT, $varvar;
	$varvar = '?i='.$_GET["i"];
?>
	<h2>Rename Folder &ldquo;<?php echo $_GET["i"]; ?>&rdquo;</h2>
	<form method="post" action="<?php echo $ONESCRIPT.$varvar; ?>">
		<input type="hidden" name="sessionid" value="<?php echo session_id(); ?>">
		<p>
			<label>Old name:</label><input type="hidden" name="old_foldername" value="<?php echo $_GET["i"]; ?>">
			<input type="text" name="dummy" value="<?php echo $_GET["i"]; ?>" class="textinput" disabled="disabled">
		</p>
		<p>
			<label for="new_foldername">New name:</label>
			<input type="text" name="new_foldername" id="new_foldername" class="textinput" value="<?php echo $_GET["i"]; ?>">
		</p>
		<p><?php Cancel_Submit_Buttons("Rename"); ?></p>
	</form>
<?php } //end Rename_Folder_Page() *********************************************





function Delete_Folder_Page(){ //***********************************************
	global $ONESCRIPT, $varvar;
	$varvar = "?i=".dirname($_GET['i']);
?>
	<h2>Delete Folder &nbsp;&ldquo; <?php echo $_GET["i"]; ?>/ &rdquo; &nbsp;?</h2>
	<form method="post" action="<?php echo $ONESCRIPT.$varvar; ?>">
		<input type="hidden" name="sessionid" value="<?php echo session_id(); ?>">
		<p>
		<input type="hidden" name="delete_foldername" value="<?php echo $_GET["i"]; ?>">
		<?php Cancel_Submit_Buttons("DELETE"); ?>
		</p>
	</form>
<?php } //end Delete_Folder_Page() //*******************************************










function Load_Selected_Page(){ //***********************************************
	global $page;
	if ($page == "login")        { Login_Page();         }
	if ($page == "index")        { Index_Page();         }
	if ($page == "edit")         { Edit_Page();          }
	if ($page == "upload")       { Upload_Page();        }
	if ($page == "new")          { New_File_Page();      }
	if ($page == "copy")         { Copy_File_Page();     }
	if ($page == "rename")       { Rename_File_Page();   }
	if ($page == "delete")       { Delete_File_Page();   }
	if ($page == "newfolder")    { New_Folder_Page();    }
	if ($page == "renamefolder") { Rename_Folder_Page(); }
	if ($page == "deletefolder") { Delete_Folder_Page(); }
}//end Load_Selected_Page() ****************************************************










//******************************************************************************
function time_stamp_scripts() {  ?>

<script>//Dispaly file's timestamp in user's local time 

function pad(num){ 
	if ( num < 10 ){ num = "0" + num; }
	return num
}


function FileTimeStamp(php_filemtime, show_offset){

	//php's filemtime returns seconds, javascript's date() uses milliseconds.
	var FileMTime = php_filemtime * 1000; 

	var TIMESTAMP  = new Date(FileMTime);
	var YEAR  = TIMESTAMP.getFullYear();
	var	MONTH = pad(TIMESTAMP.getMonth() + 1);
	var DATE  = pad(TIMESTAMP.getDate());
	var HOURS = TIMESTAMP.getHours();
	var MINS  = pad(TIMESTAMP.getMinutes());
	var SECS  = pad(TIMESTAMP.getSeconds());

	if( HOURS < 12){ AMPM = "am"; }
	else           { AMPM = "pm"; HOURS = HOURS - 12; }
	HOURS = pad(HOURS);

	var GMT_offset = -(TIMESTAMP.getTimezoneOffset()); //Yes, I know - seems wrong, but it's works.

	if (GMT_offset < 0) { NEG=-1; SIGN="-"; } else { NEG=1; SIGN="+"; } 

	var offset_HOURS = Math.floor(NEG*GMT_offset/60);
	var offset_MINS  = pad( NEG * GMT_offset % 60 );
	var offset_FULL  = "UTC " + SIGN + offset_HOURS + ":" + offset_MINS;

	if (show_offset){ var DATETIME = YEAR+"-"+MONTH+"-"+DATE+" &nbsp;"+HOURS+":"+MINS+" "+AMPM+" ("+offset_FULL+")"; }
	else            { var DATETIME = YEAR+"-"+MONTH+"-"+DATE+" &nbsp;"+HOURS+":"+MINS+" "+AMPM; }
	
	document.write( DATETIME );

}//end FileTimeStamp(php_filemtime)
</script>
<?php }//end time_stamp_scripts() **********************************************




function Edit_Page_javascript() { //********************************************
?>
	<!--======== Provide feedback re: unsaved changes ========-->
	<script>
	    
	var File_textarea    = document.getElementById('file_content');
	var Save_File_button = document.getElementById('save_file');
	var Reset_button     = document.getElementById('reset');

	var start_value = File_textarea.value;
	var submitted   = false
	var changed     = false;



	// The following events only apply when the element is active.
	// [Save] is disabled unless there are changes to the open file.
	Save_File_button.onfocus = function() {Save_File_button.style.backgroundColor = "rgb(255,250,150)";}
	Save_File_button.onblur  = function() {Save_File_button.style.backgroundColor ="#Fee";}
	Save_File_button.onmouseover = function() {Save_File_button.style.backgroundColor = "rgb(255,250,150)";}
	Save_File_button.onmouseout  = function() {Save_File_button.style.backgroundColor = "#Fee";}



	function Reset_file_status_indicators() {
		changed = false;
		File_textarea.style.backgroundColor = "#eFe";  //light green
		Save_File_button.style.backgroundColor = "";
		Save_File_button.style.borderColor = "";
		Save_File_button.style.borderWidth = "1px";
		Save_File_button.disabled = "disabled";
		Save_File_button.value = "Save";
		Reset_button.disabled = "disabled";
		//File_textarea.focus();
	}


	window.onbeforeunload = function() {
		if ( changed && !submitted) { 
			//FF4+ Ingores the supplied msg below & only uses a system msg for the prompt.
			return "               Unsaved changes will be lost!";
		}
	}


	window.onunload = function() {
		//without this, a browser back then forward would reload file with local/
		// unsaved changes, but with a green b/g as tho that's the file's contents.
		if (!submitted) {
			File_textarea.value = start_value;
			Reset_file_status_indicators();
		}
	}


	//With selStart & selEnd == 0, moves cursor to start of text field.
	function setSelRange(inputEl, selStart, selEnd) { 
		if (inputEl.setSelectionRange) { 
			inputEl.focus(); 
			inputEl.setSelectionRange(selStart, selEnd); 
		} else if (inputEl.createTextRange) { 
			var range = inputEl.createTextRange(); 
			range.collapse(true); 
			range.moveEnd('character', selEnd); 
			range.moveStart('character', selStart); 
			range.select(); 
		} 
	}


	function Check_for_changes(event){
		var keycode=event.keyCode? event.keyCode : event.charCode;
		changed = (File_textarea.value != start_value);
		if (changed){
			document.getElementById('message').innerHTML = " "; // Must have a space, or it won't clear the msg.
			File_textarea.style.backgroundColor = "#Fee";  //light red
			Save_File_button.style.backgroundColor ="#Fee";
			Save_File_button.style.borderColor = "#F44";   //less light red
			Save_File_button.style.borderWidth = "1px";
			Save_File_button.disabled = "";
			Reset_button.disabled = "";
			Save_File_button.value = "SAVE CHANGES!";
		}else{
			Reset_file_status_indicators()
		}
	}


	//Reset textarea value to when page was loaded.
	//Used by [Reset] button, and when page unloads (browser back, etc). 
	//Needed becuase if the page is reloaded (ctl-r, or browser back/forward, etc.), 
	//the text stays changed, but "changed" gets set to false, which looses warning.
	function Reset_File() {
		if (changed) {
			if ( !(confirm("Reset file and loose unsaved changes?")) ) { return; }
		}
		File_textarea.value = start_value;
		Reset_file_status_indicators();
		setSelRange(File_textarea, 0, 0) //MOve cursor to start of textarea.
	}
	
	
	Reset_file_status_indicators()
	</script>

<?php }//End Edit_Page_javascript() ********************************************






//******************************************************************************
//******************************************************************************
?><!DOCTYPE html>

<html>
<head>

<title><?php echo $config_title.' - '.$pagetitle; ?></title>

<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta name="robots" content="noindex">

<?php //style_sheet(); ?>
<link href="<?php echo $config_style_sheet;?>" type="text/css" rel="stylesheet">

<?php if ( ($page == "index") || ($page == "edit") ) { time_stamp_scripts(); } ?>

</head>

<body class="page_<?php echo $page; ?>">

<div class="container">

<div class="header">
	<?php echo '<a href="', $ONESCRIPT, '" id="logo">', $config_title; ?></a>
	<?php echo $version; ?>
	
	<?php if ((isset($_SESSION['onefilecms_valid'])) && ($_SESSION['onefilecms_valid'] == "1")) { ?>
		<div class="nav">
			<a href="/"><?php show_favicon(); ?>&nbsp; 
			<b><?php echo $WEBSITE; ?>/</b>  &nbsp;- &nbsp;
			Visit Site</a> |
			<a href="<?php echo $ONESCRIPT; ?>?p=logout">Log Out</a>
		</div>
	<?php } ?>
</div><!-- end header -->


<?php message_box(); ?>


<?php Load_Selected_Page(); ?>


<div class="footer">
	<hr><br><br>
</div>

</div><!-- end container -->

</body>
</html>
