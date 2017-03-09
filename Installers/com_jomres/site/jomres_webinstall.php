<?php

if (!defined('DS'))
	{
	$detect_os = strtoupper($_SERVER["SERVER_SOFTWARE"]); // converted to uppercase
	$isWin32 = strpos($detect_os, "WIN32");
	$IIS = strpos($detect_os, "IIS");
	if (!isset($_SERVER["SERVER_SIGNATURE"]))
		$_SERVER["SERVER_SIGNATURE"] = "APACHE";
	$signature = strtoupper($_SERVER["SERVER_SIGNATURE"]);
	$apacheSig = strpos($signature, "APACHE");
	$dir =  dirname(realpath(__FILE__));
	if ( strpos($dir,":\\" ) )
		define("DS" , "\\");
	else
		{
		if ( $isWin32 === false  || $apacheSig == true)
			define("DS" , "/");
		else
			define("DS" , "\\");
		}
	}

define('JOMRESINSTALLPATH_TMP', dirname(__FILE__).DS."tmp" );
define('JOMRESINSTALLPATH_BASE', JOMRESINSTALLPATH_TMP.DS."jomres_install" );
define('JOMRESINSTALLPATH_WEBINSTALL', JOMRESINSTALLPATH_BASE.DS."jomres_install" );
define('JOMRESPATH_BASE',dirname(__FILE__).DS."jomres");
define('JOMRESINSTALLPATH_PACK_DIRECTORY', JOMRESINSTALLPATH_BASE.DS."unpacked");

$foldersToTestForWritability[]=JOMRESINSTALLPATH_TMP.DS;
$foldersToTestForWritability[]=JOMRESINSTALLPATH_BASE.DS;
$foldersToTestForWritability[]=JOMRESINSTALLPATH_WEBINSTALL.DS;
$foldersToTestForWritability[]=JOMRESPATH_BASE.DS;
$foldersToTestForWritability[]=JOMRESINSTALLPATH_PACK_DIRECTORY.DS;

foreach ($foldersToTestForWritability as $folder)
	{
	$result=jomresStatusTestFolderIsWritable($folder);
	if (!$result['result'])
		$results["folder_tests"][] = array("success"=>false,"message"=>$result['message']);
	else
		$results["folder_tests"][] = array("success"=>true,"message"=>"We tested <i>".$folder."</i> and we were able to write successfully to that folder");
	}


if (!file_exists('configuration.php') )
	{
	if (!file_exists('wp-config.php') )
		$results["configuration_tests"][]  = array("success"=>false,"message"=>"We cannot detect a copy of configuration.php or wp-config.php, please ensure that you are running this script within Wordpress's or Joomla's root folder.");
	else
		$results["configuration_tests"][]  = array("success"=>true,"message"=>"We have detected a copy of wp-config.php, you seem to be running this installation from Wordpress's root folder.");
	}
else
	$results["configuration_tests"][]  = array("success"=>true,"message"=>"We have detected a copy of configuration.php, you seem to be running this installation from Joomla's root folder.");

if (strnatcmp(phpversion(),'5.5.0') < 0) 
	$results["configuration_tests"][]  = array("success"=>false,"message"=>"Your PHP version is too low, you need to be running at least 5.5.0 to use Jomres.");
else
	$results["configuration_tests"][]  = array("success"=>true,"message"=>"You are running a version of PHP equal to, or higher than, 5.5.0");
	
/* if (!class_exists('ZipArchive')) 
	$results["configuration_tests"][]  = array("success"=>false,"message"=>"Your PHP installation does not have access to the ZipArchive class. Please see <a href=\"http://www.php.net/manual/en/zip.installation.php\" target=\"_blank\">this page</a> for more information, or ask your hosts to investigate the matter for you.");
else
	$results["configuration_tests"][]  = array("success"=>true,"message"=>"You have access to PHP's zip archive. Hooray.");
	 */
	
if (!function_exists('json_decode') )
	$results["configuration_tests"][]  = array("success"=>false,"message"=>"Function : json_decode is not available. Please ask your hosts how you can enable this function.");
else
	$results["configuration_tests"][]  = array("success"=>true,"message"=>"Function : json_decode is available.");
	
if (!function_exists('curl_init') )
	$results["configuration_tests"][]  = array("success"=>false,"message"=>"Function : curl_init is not available. To use the web installer PHP needs access to the CURL library functionality to download and install Jomres. You may need to ask your hosts to enable this php functionality. Alternatively, if you're running on localhost, you will need to enable the PHP extension mod_curl.");
else
	$results["configuration_tests"][]  = array("success"=>true,"message"=>"Function : curl_init is available. CURL is used throughout Jomres for things like Jomres installation and upgrading, setting up exchange rates, talking to Google maps etc.");


/* 
No longer an issue now that we've made this change https://jomres.trac.cvsdude.com/Jomres_core_v4/changeset/2794
if (!function_exists('bcmod') )
	$results["configuration_tests"][]  = array("success"=>false,"message"=>"Function : bcmod is not available. Please ask your hosts how you can enable this function in your PHP installation.");
else
	$results["configuration_tests"][]  = array("success"=>true,"message"=>"Function : bcmod is available."); */

if (!function_exists('imagejpeg'))
	$results["configuration_tests"][]  = array("success"=>false,"message"=>"Function : Your PHP installation does not have  JPEG support (GD) enabled. Please ask your hosts how you can enable this function in your PHP installation.");
else
	$results["configuration_tests"][]  = array("success"=>true,"message"=>"Function : JPEG support is enabled in PHP.");

	
// Firewall test
if (function_exists('curl_init') )
	{
	$curl_handle = curl_init("http://updates.jomres4.net/getlatest.php");
	curl_setopt($curl_handle, CURLOPT_HEADER,0);
	curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER ,1);
	$latestJomres = curl_exec($curl_handle);
	curl_close($curl_handle);
	if (strlen($latestJomres) == 0)
		$results["configuration_tests"][]  = array("success"=>false,"message"=>'We are unable to comunicate with the updates server, you might have a firewall preventing your server from communicating with the jomres.net servers. If you\'re on a web server as part of your hosting package you may need to ask your hosts how to resolve this, if you are on a Windows machine you may be able to modify your firewall settings to allow connections to certain servers. You need to configure your firewall allow your server to communicate with the domains "updates.jomres4.net", "plugins.jomres4.net" and "license-server.jomres.net"');
	else
		$results["configuration_tests"][]  = array("success"=>true,"message"=>"Firewall tests passed sucessfully.");
	}

if (!isset($_REQUEST['start']) )
	$pagetitle = "Testing your server's configuration";
else
	$pagetitle = "Starting your Jomres download";

echo '
<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
			<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
			<link rel="stylesheet" type="text/css" href="//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.min.css">
			<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
			<script src="//netdna.bootstrapcdn.com/bootstrap/3.1.1/js/bootstrap.min.js"></script>
			<title>Jomres installation/upgrade</title>
			<style>
				.steps a {
					padding: 10px 12px 10px 25px;
					margin-right: 5px;
					background: #efefef;
					position: relative;
					display: inline-block;
					color:#000;
					text-decoration:none;
				}
				.steps a:hover {cursor: pointer; cursor: hand;}
				.steps a:before {
					width: 0;
					height: 0;
					border-top: 20px inset transparent;
					border-bottom: 20px inset transparent;
					position: absolute;
					content: "";
					top: 0;
					left: 0;
				}
				.steps a:after {
					width: 0;
					height: 0;
					border-top: 20px inset transparent;
					border-bottom: 20px inset transparent;
					border-left: 20px solid #efefef;
					position: absolute;
					content: "";
					top: 0;
					right: -20px;
					z-index: 2;
				}
				/*.steps a:last-child:after,*/
				.steps a:first-child:before {
					border: none;
				}
				.steps a:first-child {
					padding-left:15px;   
					-webkit-border-radius: 4px 0 0 4px;
					   -moz-border-radius: 4px 0 0 4px;
							border-radius: 4px 0 0 4px;
				}
				.steps a:last-child {
					-webkit-border-radius: 0 4px 4px 0;
					   -moz-border-radius: 0 4px 4px 0;
							border-radius: 0 4px 4px 0;
				}
				.steps .current {
					background: #007ACC;
					color: #fff;
				}
				.steps .current:after {
					border-left-color: #007ACC;
				}
			</style>

		<script>
		$(function() {
		// setTimeout() function will be fired after page is loaded
		// it will wait for 5 sec. and then will fire
		// $(".alert-success").hide() function
			setTimeout(function() {
				$(".alert-success").delay(500).fadeOut(\'slow\');
				$(".alert-warning").delay(500).fadeOut(\'slow\');
				
			}, 5000);
		});
		</script>
	</head>
<body>

<div class="container">
	<p>&nbsp;</p>
	<img src="http://www.jomres.net/images/jomres.png" class="img-responsive"/>
	<p>&nbsp;</p>
	<div class="alert alert-warning">'.$pagetitle.'</div>
';

$server_tests_pass = true;


$folder_check_results = '';
$configuration_check_results = '';

foreach ($results as $key=>$messages)
	{
	foreach ($messages as $message)
		{
		if (!$message['success'])
			{
			if ($key == "configuration_tests")
				$configuration_check_results .= '<div class="alert alert-danger">'.$message['message'].'</div>';
			else
				$folder_check_results .= '<div class="alert alert-danger">'.$message['message'].'</div>';
				
			$server_tests_pass = false;
			}
		else
			{
			if ($key == "configuration_tests")
				$configuration_check_results .= '<div class="alert alert-success"><strong>Congratulations.</strong> '.$message['message'].'</div>';
			else
				$folder_check_results .= '<div class="alert alert-success"><strong>Congratulations.</strong> '.$message['message'].'</div>';
			}
		}
	}

// Not currently needed
 if (!$server_tests_pass)
	{
	echo $configuration_check_results;
	echo $folder_check_results;
	$install_failure_message = '';
	foreach ($results["configuration_tests"] as $test)
		{
		if (!$test['success'])
			{
			$install_failure_message .= $test['message']." \r\n";
			}
		}
	// if (!$install_failure_message == "") // Will only report an error on configuration settings, we're unable to do anything about permissions errors
		// @mail("webinstall_failures@jomres.net","Web installation configuration setting failed",$install_failure_message);
	}

if ($server_tests_pass)
	echo '<div class="alert alert-success">Congratulations, your system passed all of our checks and you are ready to install Jomres</div>';
else
	echo '<div class="alert alert-error">Unfortunately, one or more system tests failed, please correct the reported issues and reload this page to rerun the tests.</div>
	<form action="'.$_SERVER['PHP_SELF'].'" method=get ><input type="submit" name="reload" class="btn" style="font-size:1.2em" onClick="submit()" value="Reload"></input></form>
	';


if ($server_tests_pass&& !isset($_REQUEST['start']))
	{
	$text = "";
	if (isset($error_message))
		{
		echo '<div class="alert alert-error">'.$error_message.'</div>';
		}

	?>
	<script type="text/javascript">
	function submitform()
	{
	  document.myform.submit();
	}
	</script>
	<h2 class="page-header"> Welcome to the web installer for Jomres</h2>
	
	<div class="visible-desktop steps">
		<a class="current"><span>Download Jomres</span></a>
		<a><span>Prepare installation</span></a>
		<a><span>Complete installation</span></a>
	</div>

	<p>&nbsp;</p>
	<p>
		The purpose of this script is to download Jomres direct to your web server, unpack it and perform the file and directory creation stage of your Jomres installation. Once this has been completed you will be taken to the file install_jomres.php which is used to both install Jomres and upgrade any existing installations. Please click the "Continue" button when you are ready to start the download.
	</p>
	
	<form action="<?php echo $_SERVER['PHP_SELF'];?>" method="get" >
		<input type="submit" class="btn btn-success btn-lg" onClick="submit();" value="Continue >>" />
		<input type="hidden" name="start" value="1" />
		<input type="hidden" name="includebeta" value="0" />
		<?php
		if (isset($_REQUEST['modal']))
			{
			?>
			<input type="hidden" name="modal" value="1" />
			<?php
			}
			?>
	</form>

	<p>&nbsp;</p>
	<p>Alternatively, if you want to install the latest build, which may or may not be an Alpha/Beta/Release Candidate click this button</p>
	
	<form action="<?php echo $_SERVER['PHP_SELF'];?>" method="get" >
		<input type="submit" class="btn btn-warning" onClick="submit();" value="Continue >>" />
		<input type="hidden" name="start" value="1" />
		<input type="hidden" name="includebeta" value="1" />
		<?php
		if (isset($_REQUEST['modal']))
			{
			?>
			<input type="hidden" name="modal" value="1" />
			<?php
			}
			?>
	</form>

	<?php
	}
elseif ($server_tests_pass && isset($_REQUEST['start']) )
	{
	$include_beta = (bool)$_REQUEST['includebeta'];
	set_time_limit(0);

	PiwikTracker::$URL = 'http://analytics.jomres.net/';
	$piwikTracker = new PiwikTracker( $idSite = 2 );
	// Sends Tracker request via http, regardless of whether the user's chosen to enable or disable tracking. If they have disabled it, then the remote server will just disregard the tracking information sent, so there's no need to check here for the tracking cookie.
	$piwikTracker->doTrackGoal($idGoal = 1);
	
	echo "Time limit set to ".ini_get('max_execution_time')." (this is just to stop the server timing out while we do the download)<br>";

	global $debugging;

	$testing=true;
	$debugging=array();

	emptyDir(JOMRESINSTALLPATH_BASE);

	$curl_handle = curl_init("http://updates.jomres4.net/getlatest.php?includebeta=".(string)$include_beta);
	
	curl_setopt($curl_handle, CURLOPT_HEADER,0);  // DO NOT RETURN HTTP HEADERS 
	curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER ,1);  // RETURN THE CONTENTS OF THE CALL
	$latestJomres = curl_exec($curl_handle);
	curl_close($curl_handle);
	if (strlen($latestJomres) == 0)
		{
		echo "Couldn't identify new Jomres version. Quitting. It is possible that a firewall may be preventing this server from communicating with the updates server. If so, please disable it, or allow your web server to communicate with the server at \"updates.jomres4.net\", port 80. ";
		exit;
		}

	$newfilename=JOMRESINSTALLPATH_BASE.DS."jomres.zip";
	$out = fopen($newfilename, 'wb');
	if ($out == FALSE)
		{
		print "Couldn't create new file $newfilename. Possible file permission problem?<br/>";
		exit;
		}
	
	//echo "<br/>Starting download of $latestJomres<br>";
	
	$curl_handle = curl_init($latestJomres);
	curl_setopt($curl_handle, CURLOPT_FILE, $out);
	curl_setopt($curl_handle, CURLOPT_HEADER, 0);
	curl_setopt($curl_handle, CURLOPT_URL, $latestJomres);
	curl_exec($curl_handle);
	curl_close($curl_handle);
	fclose($out);
	@curl_close($curl_handle);
	@fclose($out);
	if (!file_exists($newfilename) && filesize($newfilename)>0 )
		{
		echo "Something went wrong downloading Jomres. Quitting";
		return;
		}

	$proceed = false;
	
	if (is_dir(JOMRESINSTALLPATH_PACK_DIRECTORY))
		$proceed = true;
	elseif (mkdir(JOMRESINSTALLPATH_PACK_DIRECTORY))
		$proceed = true;
	
	if ($proceed)
		{
 		if (class_exists('ZipArchive')) 
			{
			$zip = new ZipArchive();
			$zip->open($newfilename);
			if ($zip->open($newfilename) === TRUE) 
				{
				//echo "Unzipping ".JOMRESINSTALLPATH_BASE.DS."jomres.zip to ".JOMRESINSTALLPATH_PACK_DIRECTORY."<br/>";
				$zip->extractTo(JOMRESINSTALLPATH_PACK_DIRECTORY);
				$zip->close();
				}
			}
		else
			{
			$zip = new dUnzip2($newfilename); 
			// Activate debug
			$zip->debug = false;

			// Unzip all the contents of the zipped file to the new folder/directory
			$zip->getList();
			$zip->unzipAll(JOMRESINSTALLPATH_PACK_DIRECTORY);
			}

 		//echo "Moving files from ".JOMRESINSTALLPATH_PACK_DIRECTORY." to ".dirname(__FILE__).DS."jomres"."<br/>";
		dirmv(JOMRESINSTALLPATH_PACK_DIRECTORY, JOMRESPATH_BASE, true, $funcloc = DS);
		//echo "Completed the download and extraction. Please ensure that you visit <a href=\"jomres/install_jomres.php\" >install_jomres.php</a> to complete the installation.<br/>";
		if (isset($_GET['modal']))
			echo ' <script>window.location.replace("jomres/install_jomres.php?modal=1");</script>';
		else
			echo ' <script>window.location.replace("jomres/install_jomres.php");</script>';
		emptyDir(JOMRESINSTALLPATH_BASE);
		emptyDir(JOMRESINSTALLPATH_PACK_DIRECTORY);
		@rmdir(JOMRESINSTALLPATH_BASE);
		@rmdir(JOMRESINSTALLPATH_PACK_DIRECTORY);
		echo "Please remember to ensure that you've deleted the contents of the ".JOMRESINSTALLPATH_BASE." directory once you are ready to start using Jomres.";
		} 
	}
	echo '
			
		</div>
		<div style="clear:both;"></div>
	</body>
</html>';
	// Ends here
	
	
	// -----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
	function jomresStatusTestFolderIsWritable($path)
		{
		$tmpFile="temp.txt";
		$tmpDir="jomres_test_dir";
		if (!is_dir($path) )
			{
			if (!mkdir($path))
				return array("result"=>false,"message"=>"Directory ".$path." doesn't exist and we cannot create it.");
			}
		if (!is_writable($path) )
			return array("result"=>false,"message"=>"Directory ".$path." isn't writable");
		if (!touch($path.$tmpFile) )
			return array("result"=>false,"message"=>"Could not write ".$path.$tmpFile);
		if (!file_exists($path.$tmpFile) )
			return array("result"=>false,"message"=>"Could not find ".$path.$tmpFile." after seeming to be able to create it.");
		if (!unlink($path.$tmpFile) )
			return array("result"=>false,"message"=>"Could not delete ".$path.$tmpFile);

		if (!mkdir($path.$tmpDir) )
			return array("result"=>false,"message"=>"Could not make temporary folder ".$path.$tmpDir);
		if (!rmdir($path.$tmpDir) )
			return array("result"=>false,"message"=>"Could not remove temporary folder ".$path.$tmpDir);
		return array("result"=>true,"message"=>"Pass");
		}
	
	// -----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
	// http://www.php.net/manual/en/function.unlink.php#79940
	function emptyDir($dir) 
		{
		if(!$dh = @opendir($dir)) return;
		while (false !== ($obj = readdir($dh))) 
			{
			if($obj=='.' || $obj=='..') continue;
			if (!@unlink($dir.DS.$obj)) emptyDir($dir.DS.$obj, true);
			}
		closedir($dh);
		if ($dir != JOMRESINSTALLPATH_BASE)
			@rmdir($dir);
		}

	// http://www.php.net/manual/en/function.rename.php#61152
	function dirmv($source, $dest, $overwrite = false, $funcloc = NULL)
		{
		$debugging=array();
		/*
		if(is_null($funcloc))
			{
			$dest .= '/' . strrev(substr(strrev($source), 0, strpos(strrev($source), null)));
			$funcloc = '/';
			}
		*/
		if(!is_dir( $dest . $funcloc))
			mkdir( $dest . $funcloc); // make subdirectory before subdirectory is copied
		//echo "Opening " . $source . $funcloc."<br/>";
		if($handle = opendir( $source . $funcloc))
			{ // if the folder exploration is sucsessful, continue
			//echo "Opened ". $source . $funcloc."<br/>";
			while(false !== ($file = readdir($handle)))
				{ // as long as storing the next file to $file is successful, continue
				if($file != '.' && $file != '..')
					{
					$path  = $source . $funcloc . $file;
					$path2 = $dest . $funcloc . $file;

					if(is_file( $path))
						{
						if(!is_file( $path2))
							{
							if(!@rename( $path,  $path2))
								{
								echo '<font color="red">File ('.$path.') could not be moved, likely a permissions problem.</font><br/>';
								}
							} 
						else
							if($overwrite)
								{
								if(!@unlink( $path2))
									{
									echo 'Unable to overwrite file ("'.$path2.'"), likely to be a permissions problem.<br/>';
									} 
								else
									{
									if(!@rename( $path,  $path2))
										{
										echo '<font color="red">File ('.$path.') could not be moved while overwritting, likely a permissions problem.</font><br/>';
										}
									else
										$debugging[]= "Moved ".$path."<br/> to ".$path2."<br/>";
									}
								}
							else
								echo "Not allowed to overwrite" .$path2."<br/>";
						}
					elseif(is_dir( $path))
						{
						dirmv($source, $dest, $overwrite, $funcloc . $file . '/'); //recurse!
						rmdir( $path);
						}
					}
				}
			closedir($handle);
			}
		//echo "Finished upgrade <br/>";
		} // end of dirmv()
	
// Piwik tracking code included to help us to understand whether or not the installer worked as expected. As both jomres_webinstall.php and install_jomres.php are deleted after installation it makes sense to include the class here, instead of a library file.

/**
 * Piwik - Open source web analytics
 * 
 * Client to record visits, page views, Goals, Ecommerce activity (product views, add to carts, Ecommerce orders) in a Piwik server.
 * This is a PHP Version of the piwik.js standard Tracking API.
 * For more information, see http://piwik.org/docs/tracking-api/
 * 
 * This class requires: 
 *  - json extension (json_decode, json_encode) 
 *  - CURL or STREAM extensions (to issue the http request to Piwik)
 *  
 * @license released under BSD License http://www.opensource.org/licenses/bsd-license.php
 * @version $Id: PiwikTracker.php 4787 2011-05-23 11:09:58Z matt $
 * @link http://piwik.org/docs/tracking-api/
 *
 * @category Piwik
 * @package PiwikTracker
 */

/**
 * @package PiwikTracker
 */
class PiwikTracker
{
	/**
	 * Piwik base URL, for example http://example.org/piwik/
	 * Must be set before using the class by calling 
	 *  PiwikTracker::$URL = 'http://yourwebsite.org/piwik/';
	 * 
	 * @var string
	 */
	static public $URL = '';
	
	/**
	 * API Version
	 * 
	 * @ignore
	 * @var int
	 */
	const VERSION = 1;
	
	/**
	 * @ignore
	 */
	public $DEBUG_APPEND_URL = '';
	
	/**
	 * Visitor ID length
	 * 
	 * @ignore
	 */
	const LENGTH_VISITOR_ID = 16;
	
	/**
	 * Builds a PiwikTracker object, used to track visits, pages and Goal conversions 
	 * for a specific website, by using the Piwik Tracking API.
	 * 
	 * @param int $idSite Id site to be tracked
	 * @param string $apiUrl "http://example.org/piwik/" or "http://piwik.example.org/"
	 * 						 If set, will overwrite PiwikTracker::$URL
	 */
    function __construct( $idSite, $apiUrl = false )
    {
    	$this->cookieSupport = true;
    	$this->userAgent = false;
    	$this->localHour = false;
    	$this->localMinute = false;
    	$this->localSecond = false;
    	$this->hasCookies = false;
    	$this->plugins = false;
    	$this->visitorCustomVar = false;
    	$this->pageCustomVar = false;
    	$this->customData = false;
    	$this->forcedDatetime = false;
    	$this->token_auth = false;
    	$this->attributionInfo = false;
    	$this->ecommerceLastOrderTimestamp = false;
    	$this->ecommerceItems = array();

    	$this->requestCookie = '';
    	$this->idSite = $idSite;
    	$this->urlReferrer = @$_SERVER['HTTP_REFERER'];
    	$this->pageUrl = self::getCurrentUrl();
    	$this->ip = @$_SERVER['REMOTE_ADDR'];
    	$this->acceptLanguage = @$_SERVER['HTTP_ACCEPT_LANGUAGE'];
    	$this->userAgent = @$_SERVER['HTTP_USER_AGENT'];
    	if(!empty($apiUrl)) {
    		self::$URL = $apiUrl;
    	}
    	$this->visitorId = substr(md5(uniqid(rand(), true)), 0, self::LENGTH_VISITOR_ID);
    }
    
    /**
     * Sets the current URL being tracked
     * 
     * @param string Raw URL (not URL encoded)
     */
	public function setUrl( $url )
    {
    	$this->pageUrl = $url;
    }

    /**
     * Sets the URL referrer used to track Referrers details for new visits.
     * 
     * @param string Raw URL (not URL encoded)
     */
    public function setUrlReferrer( $url )
    {
    	$this->urlReferrer = $url;
    }
    
    /**
     * @deprecated 
     * @ignore
     */
    public function setUrlReferer( $url )
    {
    	$this->setUrlReferrer($url);
    }
    
    /**
     * Sets the attribution information to the visit, so that subsequent Goal conversions are 
     * properly attributed to the right Referrer URL, timestamp, Campaign Name & Keyword.
     * 
     * This must be a JSON encoded string that would typically be fetched from the JS API: 
     * piwikTracker.getAttributionInfo() and that you have JSON encoded via JSON2.stringify() 
     * 
     * @param string $jsonEncoded JSON encoded array containing Attribution info
     * @see function getAttributionInfo() in http://dev.piwik.org/trac/browser/trunk/js/piwik.js 
     */
    public function setAttributionInfo( $jsonEncoded )
    {
    	$decoded = json_decode($jsonEncoded, $assoc = true);
    	if(!is_array($decoded)) 
    	{
    		throw new Exception("setAttributionInfo() is expecting a JSON encoded string, $jsonEncoded given");
    	}
    	$this->attributionInfo = $decoded;
    }

    /**
     * Sets Visit Custom Variable.
     * See http://piwik.org/docs/custom-variables/
     * 
     * @param int Custom variable slot ID from 1-5
     * @param string Custom variable name
     * @param string Custom variable value
     * @param string Custom variable scope. Possible values: visit, page
     */
    public function setCustomVariable($id, $name, $value, $scope = 'visit')
    {
    	if(!is_int($id))
    	{
    		throw new Exception("Parameter id to setCustomVariable should be an integer");
    	}
    	if($scope == 'page')
    	{
    		$this->pageCustomVar[$id] = array($name, $value);
    	}
    	elseif($scope == 'visit')
    	{
    		$this->visitorCustomVar[$id] = array($name, $value);
    	}
    	else
    	{
    		throw new Exception("Invalid 'scope' parameter value");
    	}
    }
    
    /**
     * Returns the currently assigned Custom Variable stored in a first party cookie.
     * 
     * This function will only work if the user is initiating the current request, and his cookies
     * can be read by PHP from the $_COOKIE array.
     * 
     * @param int Custom Variable integer index to fetch from cookie. Should be a value from 1 to 5
     * @param string Custom variable scope. Possible values: visit, page
     * 
     * @return array|false An array with this format: array( 0 => CustomVariableName, 1 => CustomVariableValue )
     * @see Piwik.js getCustomVariable()
     */
    public function getCustomVariable($id, $scope = 'visit')
    {
    	if($scope == 'page')
    	{
    		return isset($this->pageCustomVar[$id]) ? $this->pageCustomVar[$id] : false;
    	}
    	else if($scope != 'visit')
    	{
    		throw new Exception("Invalid 'scope' parameter value");
    	}
    	if(!empty($this->visitorCustomVar[$id]))
    	{
    		return $this->visitorCustomVar[$id];
    	}
    	$customVariablesCookie = 'cvar.'.$this->idSite.'.';
    	$cookie = $this->getCookieMatchingName($customVariablesCookie);
    	if(!$cookie)
    	{
    		return false;
    	}
    	if(!is_int($id))
    	{
    		throw new Exception("Parameter to getCustomVariable should be an integer");
    	}
    	$cookieDecoded = json_decode($cookie, $assoc = true);
    	if(!is_array($cookieDecoded)
    		|| !isset($cookieDecoded[$id])
    		|| !is_array($cookieDecoded[$id])
    		|| count($cookieDecoded[$id]) != 2)
    	{
    		return false;
    	}
    	return $cookieDecoded[$id];
    }
    
    
    
    
    /**
     * Sets the Browser language. Used to guess visitor countries when GeoIP is not enabled
     * 
     * @param string For example "fr-fr"
     */
    public function setBrowserLanguage( $acceptLanguage )
    {
    	$this->acceptLanguage = $acceptLanguage;
    }

    /**
     * Sets the user agent, used to detect OS and browser.
     * If this function is not called, the User Agent will default to the current user agent.
     *  
     * @param string $userAgent
     */
    public function setUserAgent($userAgent)
    {
    	$this->userAgent = $userAgent;
    }
    

    /**
     * Tracks a page view
     * 
     * @param string $documentTitle Page title as it will appear in the Actions > Page titles report
     * @return string Response
     */
    public function doTrackPageView( $documentTitle )
    {
    	$url = $this->getUrlTrackPageView($documentTitle);
    	return $this->sendRequest($url);
    } 
    
    /**
     * Records a Goal conversion
     * 
     * @param int $idGoal Id Goal to record a conversion
     * @param int $revenue Revenue for this conversion
     * @return string Response
     */
    public function doTrackGoal($idGoal, $revenue = false)
    {
    	$url = $this->getUrlTrackGoal($idGoal, $revenue);
    	return $this->sendRequest($url);
    }
    
    /**
     * Tracks a download or outlink
     * 
     * @param string $actionUrl URL of the download or outlink
     * @param string $actionType Type of the action: 'download' or 'link'
     * @return string Response
     */
    public function doTrackAction($actionUrl, $actionType)
    {
        // Referrer could be udpated to be the current URL temporarily (to mimic JS behavior)
    	$url = $this->getUrlTrackAction($actionUrl, $actionType);
    	return $this->sendRequest($url); 
    }

    /**
     * Adds an item in the Ecommerce order.
     * 
     * This should be called before doTrackEcommerceOrder(), or before doTrackEcommerceCartUpdate().
     * This function can be called for all individual products in the cart (or order).
     * SKU parameter is mandatory. Other parameters are optional (set to false if value not known).
     * Ecommerce items added via this function are automatically cleared when doTrackEcommerceOrder() or getUrlTrackEcommerceOrder() is called.
     * 
     * @param string $sku (required) SKU, Product identifier 
     * @param string $name (optional) Product name
     * @param string $category (optional) Product category
     * @param float|int $price (optional) Individual product price (supports integer and decimal prices)
     * @param int $quantity (optional) Product quantity. If not specified, will default to 1 in the Reports 
     */
    public function addEcommerceItem($sku, $name = false, $category = false, $price = false, $quantity = false)
    {
    	if(empty($sku))
    	{
    		throw new Exception("You must specify a SKU for the Ecommerce item");
    	}
    	$this->ecommerceItems[$sku] = array( $sku, $name, $category, $price, $quantity );
    }
    
    /**
	 * Tracks a Cart Update (add item, remove item, update item).
	 * 
	 * On every Cart update, you must call addEcommerceItem() for each item (product) in the cart, 
	 * including the items that haven't been updated since the last cart update.
	 * Items which were in the previous cart and are not sent in later Cart updates will be deleted from the cart (in the database).
	 * 
	 * @param float $grandTotal Cart grandTotal (typically the sum of all items' prices)
	 */ 
    public function doTrackEcommerceCartUpdate($grandTotal)
    {
    	$url = $this->getUrlTrackEcommerceCartUpdate($grandTotal);
    	return $this->sendRequest($url); 
    }
    
    /**
	 * Tracks an Ecommerce order.
	 * 
	 * If the Ecommerce order contains items (products), you must call first the addEcommerceItem() for each item in the order.
	 * All revenues (grandTotal, subTotal, tax, shipping, discount) will be individually summed and reported in Piwik reports.
	 * Only the parameters $orderId and $grandTotal are required. 
	 * 
	 * @param string|int $orderId (required) Unique Order ID. 
	 * 				This will be used to count this order only once in the event the order page is reloaded several times.
	 * 				orderId must be unique for each transaction, even on different days, or the transaction will not be recorded by Piwik.
	 * @param float $grandTotal (required) Grand Total revenue of the transaction (including tax, shipping, etc.)
	 * @param float $subTotal (optional) Sub total amount, typically the sum of items prices for all items in this order (before Tax and Shipping costs are applied) 
	 * @param float $tax (optional) Tax amount for this order
	 * @param float $shipping (optional) Shipping amount for this order
	 * @param float $discount (optional) Discounted amount in this order
     */
    public function doTrackEcommerceOrder($orderId, $grandTotal, $subTotal = false, $tax = false, $shipping = false, $discount = false)
    {
    	$url = $this->getUrlTrackEcommerceOrder($orderId, $grandTotal, $subTotal, $tax, $shipping, $discount);
    	return $this->sendRequest($url); 
    }
    
    /**
     * Sets the current page view as an item (product) page view, or an Ecommerce Category page view.
     * 
     * This must be called before doTrackPageView() on this product/category page. 
     * It will set 3 custom variables of scope "page" with the SKU, Name and Category for this page view.
     * Note: Custom Variables of scope "page" slots 3, 4 and 5 will be used.
     *  
     * On a category page, you may set the parameter $category only and set the other parameters to false.
     * 
     * Tracking Product/Category page views will allow Piwik to report on Product & Categories 
     * conversion rates (Conversion rate = Ecommerce orders containing this product or category / Visits to the product or category)
     * 
     * @param string $sku Product SKU being viewed
     * @param string $name Product Name being viewed
     * @param string $category Category being viewed. On a Product page, this is the product's category
     */
    public function setEcommerceView($sku = false, $name = false, $category = false)
    {
    	if(!empty($sku)) {
    		$this->pageCustomVar[3] = array('_pks', $sku);
    	}
    	if(!empty($name)) {
    		$this->pageCustomVar[4] = array('_pkn', $name);
    	}
    	if(!empty($category)) {
    		$this->pageCustomVar[5] = array('_pkc', $category);
    	}
    }
    
    /**
     * Returns URL used to track Ecommerce Cart updates
     * Calling this function will reinitializes the property ecommerceItems to empty array 
     * so items will have to be added again via addEcommerceItem()  
     * @ignore
     */
    public function getUrlTrackEcommerceCartUpdate($grandTotal)
    {
    	$url = $this->getUrlTrackEcommerce($grandTotal);
    	return $url;
    }
    
    /**
     * Returns URL used to track Ecommerce Orders
     * Calling this function will reinitializes the property ecommerceItems to empty array 
     * so items will have to be added again via addEcommerceItem()  
     * @ignore
     */
    public function getUrlTrackEcommerceOrder($orderId, $grandTotal, $subTotal = false, $tax = false, $shipping = false, $discount = false)
    {
    	if(empty($orderId))
    	{
    		throw new Exception("You must specifiy an orderId for the Ecommerce order");
    	}
    	$url = $this->getUrlTrackEcommerce($grandTotal, $subTotal, $tax, $shipping, $discount);
    	$url .= '&ec_id=' . urlencode($orderId);
    	$this->ecommerceLastOrderTimestamp = $this->getTimestamp();
    	return $url;
    }
    
    /**
     * Returns URL used to track Ecommerce orders
     * Calling this function will reinitializes the property ecommerceItems to empty array 
     * so items will have to be added again via addEcommerceItem()  
     * @ignore
     */
    protected function getUrlTrackEcommerce($grandTotal, $subTotal = false, $tax = false, $shipping = false, $discount = false)
    {
    	if(!is_numeric($grandTotal))
    	{
    		throw new Exception("You must specifiy a grandTotal for the Ecommerce order (or Cart update)");
    	}
    	
    	$url = $this->getRequest( $this->idSite );
    	$url .= '&idgoal=0';
    	if(!empty($grandTotal))
    	{
    		$url .= '&revenue='.$grandTotal;
    	}
    	if(!empty($subTotal))
    	{
    		$url .= '&ec_st='.$subTotal;
    	}
    	if(!empty($tax))
    	{
    		$url .= '&ec_tx='.$tax;
    	}
    	if(!empty($shipping))
    	{
    		$url .= '&ec_sh='.$shipping;
    	}
    	if(!empty($discount))
    	{
    		$url .= '&ec_dt='.$discount;
    	}
    	if(!empty($this->ecommerceItems))
    	{
    		// Removing the SKU index in the array before JSON encoding
    		$items = array();
    		foreach($this->ecommerceItems as $item)
    		{
    			$items[] = $item;
    		}
    		$url .= '&ec_items='. urlencode(json_encode($items));
    	}
    	$this->ecommerceItems = array();
    	return $url;
    }
    
    /**
     * @see doTrackPageView()
     * @param string $documentTitle Page view name as it will appear in Piwik reports
     * @return string URL to piwik.php with all parameters set to track the pageview
     */
    public function getUrlTrackPageView( $documentTitle = false )
    {
    	$url = $this->getRequest( $this->idSite );
    	if(!empty($documentTitle)) {
    		$url .= '&action_name=' . urlencode($documentTitle);
    	}
    	return $url;
    }
    
    /**
     * @see doTrackGoal()
     * @param string $actionUrl URL of the download or outlink
     * @param string $actionType Type of the action: 'download' or 'link'
     * @return string URL to piwik.php with all parameters set to track the goal conversion
     */
    public function getUrlTrackGoal($idGoal, $revenue = false)
    {
    	$url = $this->getRequest( $this->idSite );
		$url .= '&idgoal=' . $idGoal;
    	if(!empty($revenue)) {
    		$url .= '&revenue=' . $revenue;
    	}
    	return $url;
    }
        
    /**
     * @see doTrackAction()
     * @param string $actionUrl URL of the download or outlink
     * @param string $actionType Type of the action: 'download' or 'link'
     * @return string URL to piwik.php with all parameters set to track an action
     */
    public function getUrlTrackAction($actionUrl, $actionType)
    {
    	$url = $this->getRequest( $this->idSite );
		$url .= '&'.$actionType.'=' . $actionUrl .
				'&redirect=0';
		
    	return $url;
    }

    /**
     * Overrides server date and time for the tracking requests. 
     * By default Piwik will track requests for the "current datetime" but this function allows you 
     * to track visits in the past. All times are in UTC.
     * 
     * Allowed only for Super User, must be used along with setTokenAuth()
     * @see setTokenAuth()
     * @param string Date with the format 'Y-m-d H:i:s', or a UNIX timestamp
     */
    public function setForceVisitDateTime($dateTime)
    {
    	$this->forcedDatetime = $dateTime;
    }
    
    /**
     * Overrides IP address
     * 
     * Allowed only for Super User, must be used along with setTokenAuth()
     * @see setTokenAuth()
     * @param string IP string, eg. 130.54.2.1
     */
    public function setIp($ip)
    {
    	$this->ip = $ip;
    }
    
    /**
     * Forces the requests to be recorded for the specified Visitor ID
     * rather than using the heuristics based on IP and other attributes.
     * 
     * This is typically used with the Javascript getVisitorId() function.
     * 
     * Allowed only for Super User, must be used along with setTokenAuth()
     * @see setTokenAuth()
     * @param string $visitorId 16 hexadecimal characters visitor ID, eg. "33c31e01394bdc63"
     */
    public function setVisitorId($visitorId)
    {
    	if(strlen($visitorId) != self::LENGTH_VISITOR_ID)
    	{
    		throw new Exception("setVisitorId() expects a ".self::LENGTH_VISITOR_ID." characters ID");
    	}
    	$this->forcedVisitorId = $visitorId;
    }
    
    /**
     * If the user initiating the request has the Piwik first party cookie, 
     * this function will try and return the ID parsed from this first party cookie (found in $_COOKIE).
     * 
     * If you call this function from a server, where the call is triggered by a cron or script
     * not initiated by the actual visitor being tracked, then it will return 
     * the random Visitor ID that was assigned to this visit object.
     * 
     * This can be used if you wish to record more visits, actions or goals for this visitor ID later on.
     * 
     * @return string 16 hex chars visitor ID string
     */
    public function getVisitorId()
    {
    	if(!empty($this->forcedVisitorId))
    	{
    		return $this->forcedVisitorId;
    	}
    	
    	$idCookieName = 'id.'.$this->idSite.'.';
    	$idCookie = $this->getCookieMatchingName($idCookieName);
    	if($idCookie !== false)
    	{
    		$visitorId = substr($idCookie, 0, strpos($idCookie, '.'));
    		if(strlen($visitorId) == self::LENGTH_VISITOR_ID)
    		{
    			return $visitorId;
    		}
    	}
    	return $this->visitorId;
    }

    /**
     * Returns the currently assigned Attribution Information stored in a first party cookie.
     * 
     * This function will only work if the user is initiating the current request, and his cookies
     * can be read by PHP from the $_COOKIE array.
     * 
     * @return string JSON Encoded string containing the Referer information for Goal conversion attribution.
     *                Will return false if the cookie could not be found
     * @see Piwik.js getAttributionInfo()
     */
    public function getAttributionInfo()
    {
    	$attributionCookieName = 'ref.'.$this->idSite.'.';
    	return $this->getCookieMatchingName($attributionCookieName);
    }
    
	/**
	 * Some Tracking API functionnality requires express authentication, using either the 
	 * Super User token_auth, or a user with 'admin' access to the website.
	 * 
	 * The following features require access:
	 * - force the visitor IP
	 * - force the date & time of the tracking requests rather than track for the current datetime
	 * - force Piwik to track the requests to a specific VisitorId rather than use the standard visitor matching heuristic
	 *
	 * @param string token_auth 32 chars token_auth string
	 */
	public function setTokenAuth($token_auth)
	{
		$this->token_auth = $token_auth;
	}

    /**
     * Sets local visitor time
     * 
     * @param string $time HH:MM:SS format
     */
    public function setLocalTime($time)
    {
    	list($hour, $minute, $second) = explode(':', $time);
    	$this->localHour = (int)$hour;
    	$this->localMinute = (int)$minute;
    	$this->localSecond = (int)$second;
    }
    
    /**
     * Sets user resolution width and height.
     *
     * @param int $width
     * @param int $height
     */
    public function setResolution($width, $height)
    {
    	$this->width = $width;
    	$this->height = $height;
    }
    
    /**
     * Sets if the browser supports cookies 
     * This is reported in "List of plugins" report in Piwik.
     *
     * @param bool $bool
     */
    public function setBrowserHasCookies( $bool )
    {
    	$this->hasCookies = $bool ;
    }
    
    /**
     * Will append a custom string at the end of the Tracking request. 
     * @param string $string
     */
    public function setDebugStringAppend( $string )
    {
    	$this->DEBUG_APPEND_URL = $string;
    }
    
    /**
     * Sets visitor browser supported plugins 
     *
     * @param bool $flash
     * @param bool $java
     * @param bool $director
     * @param bool $quickTime
     * @param bool $realPlayer
     * @param bool $pdf
     * @param bool $windowsMedia
     * @param bool $gears
     * @param bool $silverlight
     */
    public function setPlugins($flash = false, $java = false, $director = false, $quickTime = false, $realPlayer = false, $pdf = false, $windowsMedia = false, $gears = false, $silverlight = false)
    {
    	$this->plugins = 
    		'&fla='.(int)$flash.
    		'&java='.(int)$java.
    		'&dir='.(int)$director.
    		'&qt='.(int)$quickTime.
    		'&realp='.(int)$realPlayer.
    		'&pdf='.(int)$pdf.
    		'&wma='.(int)$windowsMedia.
    		'&gears='.(int)$gears.
    		'&ag='.(int)$silverlight
    	;
    }
    
    /**
     * By default, PiwikTracker will read third party cookies 
     * from the response and sets them in the next request.
     * This can be disabled by calling this function.
     * 
     * @return void
     */
    public function disableCookieSupport()
    {
    	$this->cookieSupport = false;
    }
    
    /**
     * @ignore
     */
    protected function sendRequest($url)
    {
		$timeout = 600; // Allow debug while blocking the request
		$response = '';

		if(!$this->cookieSupport)
		{
			$this->requestCookie = '';
		}
		if(function_exists('curl_init'))
		{
			$ch = curl_init();
			curl_setopt_array($ch, array(
				CURLOPT_URL => $url,
				CURLOPT_USERAGENT => $this->userAgent,
				CURLOPT_HEADER => true,
				CURLOPT_TIMEOUT => $timeout,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_HTTPHEADER => array(
					'Accept-Language: ' . $this->acceptLanguage,
					'Cookie: '. $this->requestCookie,
				),
			));
			ob_start();
			$response = @curl_exec($ch);
			ob_end_clean();
			$header = $content = '';
			if(!empty($response))
			{
				list($header,$content) = explode("\r\n\r\n", $response, $limitCount = 2);
			}
		}
		else if(function_exists('stream_context_create'))
		{
			$stream_options = array(
				'http' => array(
					'user_agent' => $this->userAgent,
					'header' => "Accept-Language: " . $this->acceptLanguage . "\r\n" .
					            "Cookie: ".$this->requestCookie. "\r\n" ,
					'timeout' => $timeout, // PHP 5.2.1
				)
			);
			$ctx = stream_context_create($stream_options);
			$response = file_get_contents($url, 0, $ctx);
			$header = implode("\r\n", $http_response_header); 
			$content = $response;
		}
		// The cookie in the response will be set in the next request
		preg_match_all('/^Set-Cookie: (.*?);/m', $header, $cookie);
		if(!empty($cookie[1]))
		{
			// in case several cookies returned, we keep only the latest one (ie. XDEBUG puts its cookie first in the list)
			if(is_array($cookie[1]))
			{
				$cookie = end($cookie[1]);
			}
			else
			{
				$cookie = $cookie[1];
			}
			if(strpos($cookie, 'XDEBUG') === false)
			{
				$this->requestCookie = $cookie;
			}
		}

		return $content;
    }
    
    /**
     * Returns current timestamp, or forced timestamp/datetime if it was set
     * @return string|int
     */
    protected function getTimestamp()
    {
    	return !empty($this->forcedDatetime) 
    		? strtotime($this->forcedDatetime) 
    		: time();
    }
    
    /**
     * @ignore
     */
    protected function getRequest( $idSite )
    {
    	if(empty(self::$URL))
    	{
    		throw new Exception('You must first set the Piwik Tracker URL by calling PiwikTracker::$URL = \'http://your-website.org/piwik/\';');
    	}
    	if(strpos(self::$URL, '/piwik.php') === false
    		&& strpos(self::$URL, '/proxy-piwik.php') === false)
    	{
    		self::$URL .= '/piwik.php';
    	}
    	
    	$url = self::$URL .
	 		'?idsite=' . $idSite .
			'&rec=1' .
			'&apiv=' . self::VERSION . 
	        '&rand=' . mt_rand() .
    	
    		// PHP DEBUGGING: Optional since debugger can be triggered remotely
    		(!empty($_GET['XDEBUG_SESSION_START']) ? '&XDEBUG_SESSION_START=' . @$_GET['XDEBUG_SESSION_START'] : '') . 
	        (!empty($_GET['KEY']) ? '&KEY=' . @$_GET['KEY'] : '') .
    	 
    		// Only allowed for Super User, token_auth required
			(!empty($this->ip) ? '&cip=' . $this->ip : '') .
    		(!empty($this->forcedVisitorId) ? '&cid=' . $this->forcedVisitorId : '&_id=' . $this->visitorId) . 
			(!empty($this->forcedDatetime) ? '&cdt=' . urlencode($this->forcedDatetime) : '') .
			(!empty($this->token_auth) ? '&token_auth=' . urlencode($this->token_auth) : '') .
	        
			// These parameters are set by the JS, but optional when using API
	        (!empty($this->plugins) ? $this->plugins : '') . 
			(($this->localHour !== false && $this->localMinute !== false && $this->localSecond !== false) ? '&h=' . $this->localHour . '&m=' . $this->localMinute  . '&s=' . $this->localSecond : '' ).
	        (!empty($this->width) && !empty($this->height) ? '&res=' . $this->width . 'x' . $this->height : '') .
	        (!empty($this->hasCookies) ? '&cookie=' . $this->hasCookies : '') .
	        (!empty($this->ecommerceLastOrderTimestamp) ? '&_ects=' . urlencode($this->ecommerceLastOrderTimestamp) : '') .
	        
	        // Various important attributes
	        (!empty($this->customData) ? '&data=' . $this->customData : '') . 
	        (!empty($this->visitorCustomVar) ? '&_cvar=' . urlencode(json_encode($this->visitorCustomVar)) : '') .
	        (!empty($this->pageCustomVar) ? '&cvar=' . urlencode(json_encode($this->pageCustomVar)) : '') .
	        
	        // URL parameters
	        '&url=' . urlencode($this->pageUrl) .
			'&urlref=' . urlencode($this->urlReferrer) .
	        
	        // Attribution information, so that Goal conversions are attributed to the right referrer or campaign
	        // Campaign name
    		(!empty($this->attributionInfo[0]) ? '&_rcn=' . urlencode($this->attributionInfo[0]) : '') .
    		// Campaign keyword
    		(!empty($this->attributionInfo[1]) ? '&_rck=' . urlencode($this->attributionInfo[1]) : '') .
    		// Timestamp at which the referrer was set
    		(!empty($this->attributionInfo[2]) ? '&_refts=' . $this->attributionInfo[2] : '') .
    		// Referrer URL
    		(!empty($this->attributionInfo[3]) ? '&_ref=' . urlencode($this->attributionInfo[3]) : '') .

    		// DEBUG 
	        $this->DEBUG_APPEND_URL
        ;
    	// Reset page level custom variables after this page view
    	$this->pageCustomVar = false;
    	
    	return $url;
    }
    
    
    /**
     * Returns a first party cookie which name contains $name
     * 
     * @param string $name
     * @return string String value of cookie, or false if not found
     * @ignore
     */
    protected function getCookieMatchingName($name)
    {
    	// Piwik cookie names use dots separators in piwik.js, 
    	// but PHP Replaces . with _ http://www.php.net/manual/en/language.variables.predefined.php#72571
    	$name = str_replace('.', '_', $name);
    	foreach($_COOKIE as $cookieName => $cookieValue)
    	{
    		if(strpos($cookieName, $name) !== false)
    		{
    			return $cookieValue;
    		}
    	}
    	return false;
    }

	/**
	 * If current URL is "http://example.org/dir1/dir2/index.php?param1=value1&param2=value2"
	 * will return "/dir1/dir2/index.php"
	 *
	 * @return string
     * @ignore
	 */
	static protected function getCurrentScriptName()
	{
		$url = '';
		if( !empty($_SERVER['PATH_INFO']) ) { 
			$url = $_SERVER['PATH_INFO'];
		} 
		else if( !empty($_SERVER['REQUEST_URI']) ) 	{
			if( ($pos = strpos($_SERVER['REQUEST_URI'], '?')) !== false ) {
				$url = substr($_SERVER['REQUEST_URI'], 0, $pos);
			} else {
				$url = $_SERVER['REQUEST_URI'];
			}
		} 
		if(empty($url)) {
			$url = $_SERVER['SCRIPT_NAME'];
		}

		if($url[0] !== '/')	{
			$url = '/' . $url;
		}
		return $url;
	}

	/**
	 * If the current URL is 'http://example.org/dir1/dir2/index.php?param1=value1&param2=value2"
	 * will return 'http'
	 *
	 * @return string 'https' or 'http'
     * @ignore
	 */
	static protected function getCurrentScheme()
	{
		if(isset($_SERVER['HTTPS'])
				&& ($_SERVER['HTTPS'] == 'on' || $_SERVER['HTTPS'] === true))
		{
			return 'https';
		}
		return 'http';
	}

	/**
	 * If current URL is "http://example.org/dir1/dir2/index.php?param1=value1&param2=value2"
	 * will return "http://example.org"
	 *
	 * @return string
     * @ignore
	 */
	static protected function getCurrentHost()
	{
		if(isset($_SERVER['HTTP_HOST'])) {
			return $_SERVER['HTTP_HOST'];
		}
		return 'unknown';
	}

	/**
	 * If current URL is "http://example.org/dir1/dir2/index.php?param1=value1&param2=value2"
	 * will return "?param1=value1&param2=value2"
	 *
	 * @return string
     * @ignore
	 */
	static protected function getCurrentQueryString()
	{
		$url = '';	
		if(isset($_SERVER['QUERY_STRING'])
			&& !empty($_SERVER['QUERY_STRING']))
		{
			$url .= '?'.$_SERVER['QUERY_STRING'];
		}
		return $url;
	}
	
	/**
	 * Returns the current full URL (scheme, host, path and query string.
	 *  
	 * @return string
     * @ignore
	 */
    static protected function getCurrentUrl()
    {
		return self::getCurrentScheme() . '://'
			. self::getCurrentHost()
			. self::getCurrentScriptName() 
			. self::getCurrentQueryString();
	}
}

function Piwik_getUrlTrackPageView( $idSite, $documentTitle = false )
{
	$tracker = new PiwikTracker($idSite);
	return $tracker->getUrlTrackPageView($documentTitle);
}
function Piwik_getUrlTrackGoal($idSite, $idGoal, $revenue = false)
{
	$tracker = new PiwikTracker($idSite);
	return $tracker->getUrlTrackGoal($idGoal, $revenue);
}


// 25/07/2012 (v2.664)
// - unzip was NOT respecting chmod parameters, and always setting to 0777. (thanks to Stef Dawson, http://stefdawson.com)
// 19/08/2011 (v2.663)
// - unzipAll was using double slashes (path//filename) to save files. (thanks to Karen Peyton).
// 09/08/2010 (v2.662)
// - unzipAll parameters fully reviewed and fixed. Thanks Ronny Dreschler and Conor Mac Aoidh.
// 12/05/2010 (v2.661)
// - Fixed E_STRICT notice: "Only variables should be passed by reference". Thanks Erik W.
// 24/03/2010 (v2.66)
// - Fixed bug inside unzipAll when dirname is "." (thanks to Thorsten Groth)
// - Added character "¥" to the string conversion table (ex: caixa d¥·gua)
// 27/02/2010
// - Removed PHP4 support (file_put_contents redeclaration).
// 04/12/2009 (v2.65)
// * Added character translation to decode accents and/or special characters.
// 10/11/2009
// * Some security added to avoid malicious ZIP files (relative dirs)
// * unzipAll() will output by default to same folder of the caller script
// 25/09/2009
// - Code optimization to reduce memory usage (uncompress(&$contents))
// 12/07/2009 (2.62)
// - Debug messages are shown only when explicit.
// - New method: getLastError()

##############################################################
# Class dUnzip2 v2.663
#
#  Author: Alexandre Tedeschi (d)
#  E-Mail: alexandrebr at gmail dot com
#  Londrina - PR / Brazil
#
#  Objective:
#    This class allows programmer to easily unzip files on the fly.
#
#  Requirements:
#    This class requires extension ZLib Enabled. It is default
#    for most site hosts around the world, and for the PHP Win32 dist.
#
#  To do:
#   * Error handling
#   * Write a PHP-Side gzinflate, to completely avoid any external extensions
#   * Write other decompress algorithms
#
#  Methods:
#  * dUnzip2($filename)         - Constructor - Opens $filename
#  * getList([$stopOnFile])     - Retrieve the file list
#  * getExtraInfo($zipfilename) - Retrieve more information about compressed file
#  * getZipInfo([$entry])       - Retrieve ZIP file details.
#  * unzip($zipfilename, [$outfilename, [$applyChmod]]) - Unzip file
#  * unzipAll([$outDir, [$zipDir, [$maintainStructure, [$applyChmod]]]])
#  * close()                    - Close file handler, but keep the list
#  * __destroy()                - Close file handler and release memory
#
#  If you modify this class, or have any ideas to improve it, please contact me!
#  You are allowed to redistribute this class, if you keep my name and contact e-mail on it.
#
#  PLEASE! IF YOU USE THIS CLASS IN ANY OF YOUR PROJECTS, PLEASE LET ME KNOW!
#  If you have problems using it, don't think twice before contacting me!
#
##############################################################

class dUnzip2{
	Function getVersion(){
		return "2.664";
	}
	// Public
	var $fileName;
	var $lastError;
	var $compressedList; // You will problably use only this one!
	var $centralDirList; // Central dir list... It's a kind of 'extra attributes' for a set of files
	var $endOfCentral;   // End of central dir, contains ZIP Comments
	var $debug;
	
	// Private
	var $fh;
	var $zipSignature = "\x50\x4b\x03\x04"; // local file header signature
	var $dirSignature = "\x50\x4b\x01\x02"; // central dir header signature
	var $dirSignatureE= "\x50\x4b\x05\x06"; // end of central dir signature
	
	// Public
	Function __construct($fileName){
		$this->fileName       = $fileName;
		$this->compressedList = 
		$this->centralDirList = 
		$this->endOfCentral   = Array();
	}
	
	Function getList($stopOnFile=false){
		if(sizeof($this->compressedList)){
			$this->debugMsg(1, "Returning already loaded file list.");
			return $this->compressedList;
		}
		
		// Open file, and set file handler
		$fh = fopen($this->fileName, "r");
		$this->fh = &$fh;
		if(!$fh){
			$this->debugMsg(2, "Failed to load file.");
			return false;
		}
		
		$this->debugMsg(1, "Loading list from 'End of Central Dir' index list...");
		if(!$this->_loadFileListByEOF($fh, $stopOnFile)){
			$this->debugMsg(1, "Failed! Trying to load list looking for signatures...");
			if(!$this->_loadFileListBySignatures($fh, $stopOnFile)){
				$this->debugMsg(1, "Failed! Could not find any valid header.");
				$this->debugMsg(2, "ZIP File is corrupted or empty");
				return false;
			}
		}
		
		if($this->debug){
			#------- Debug compressedList
			$kkk = 0;
			echo "<table border='0' style='font: 11px Verdana; border: 1px solid #000'>";
			foreach($this->compressedList as $fileName=>$item){
				if(!$kkk && $kkk=1){
					echo "<tr style='background: #ADA'>";
					foreach($item as $fieldName=>$value)
						echo "<td>$fieldName</td>";
					echo '</tr>';
				}
				echo "<tr style='background: #CFC'>";
				foreach($item as $fieldName=>$value){
					if($fieldName == 'lastmod_datetime')
						echo "<td title='$fieldName' nowrap='nowrap'>".date("d/m/Y H:i:s", $value)."</td>";
					else
						echo "<td title='$fieldName' nowrap='nowrap'>$value</td>";
				}
				echo "</tr>";
			}
			echo "</table>";
			
			#------- Debug centralDirList
			$kkk = 0;
			if(sizeof($this->centralDirList)){
				echo "<table border='0' style='font: 11px Verdana; border: 1px solid #000'>";
				foreach($this->centralDirList as $fileName=>$item){
					if(!$kkk && $kkk=1){
						echo "<tr style='background: #AAD'>";
						foreach($item as $fieldName=>$value)
							echo "<td>$fieldName</td>";
						echo '</tr>';
					}
					echo "<tr style='background: #CCF'>";
					foreach($item as $fieldName=>$value){
						if($fieldName == 'lastmod_datetime')
							echo "<td title='$fieldName' nowrap='nowrap'>".date("d/m/Y H:i:s", $value)."</td>";
						else
							echo "<td title='$fieldName' nowrap='nowrap'>$value</td>";
					}
					echo "</tr>";
				}
				echo "</table>";
			}
		
			#------- Debug endOfCentral
			$kkk = 0;
			if(sizeof($this->endOfCentral)){
				echo "<table border='0' style='font: 11px Verdana' style='border: 1px solid #000'>";
				echo "<tr style='background: #DAA'><td colspan='2'>dUnzip - End of file</td></tr>";
				foreach($this->endOfCentral as $field=>$value){
					echo "<tr>";
					echo "<td style='background: #FCC'>$field</td>";
					echo "<td style='background: #FDD'>$value</td>";
					echo "</tr>";
				}
				echo "</table>";
			}
		}
		
		return $this->compressedList;
	}
	Function getExtraInfo($compressedFileName){
		return
			isset($this->centralDirList[$compressedFileName])?
			$this->centralDirList[$compressedFileName]:
			false;
	}
	Function getZipInfo($detail=false){
		return $detail?
			$this->endOfCentral[$detail]:
			$this->endOfCentral;
	}
	
	Function unzip($compressedFileName, $targetFileName=false, $applyChmod=0777){
		if(!sizeof($this->compressedList)){
			$this->debugMsg(1, "Trying to unzip before loading file list... Loading it!");
			$this->getList(false, $compressedFileName);
		}
		
		$fdetails = &$this->compressedList[$compressedFileName];
		if(!isset($this->compressedList[$compressedFileName])){
			$this->debugMsg(2, "File '<b>$compressedFileName</b>' is not compressed in the zip.");
			return false;
		}
		if(substr($compressedFileName, -1) == "/"){
			$this->debugMsg(2, "Trying to unzip a folder name '<b>$compressedFileName</b>'.");
			return false;
		}
		if(!$fdetails['uncompressed_size']){
			$this->debugMsg(1, "File '<b>$compressedFileName</b>' is empty.");
			return $targetFileName?
				file_put_contents($targetFileName, ""):
				"";
		}
		
		fseek($this->fh, $fdetails['contents-startOffset']);
		$toUncompress = fread($this->fh, $fdetails['compressed_size']);
		$ret = $this->uncompress(
				$toUncompress,
				$fdetails['compression_method'],
				$fdetails['uncompressed_size'],
				$targetFileName
			);
		unset($toUncompress);
		
		// Vince changed
		// if($applyChmod && $targetFileName)
			// chmod($targetFileName, $applyChmod);
		
		if(is_dir($targetFileName))
			{
			if ($this->allow_chmodding)
				chmod($targetFileName, $this->permissions_directories);
			//echo "Chmodded ".$targetFileName." to ".$this->permissions_directories." <br/>";
			}
		elseif (is_file($targetFileName))
			{
			if ($this->allow_chmodding)
				chmod($targetFileName, $this->permissions_files);
			//echo "Chmodded ".$targetFileName." to ".$this->permissions_files." <br/>";
			}
		
		// end
		
		return $ret;
	}
	Function unzipAll($targetDir=false, $baseDir="", $maintainStructure=true, $applyChmod=0777){
	
		// Vince added. We will override the class's built in chmod functionality and just do it ourselves
		$this->permissions_directories = "0755";
		$this->permissions_files = "0644";
		$this->allow_chmodding = false;
		// end
		
		if($targetDir === false)
			$targetDir = dirname($_SERVER['SCRIPT_FILENAME'])."/";
		
		if(substr($targetDir, -1) == "/")
			$targetDir = substr($targetDir, 0, -1);
		
		$lista = $this->getList();
		if(sizeof($lista)) foreach($lista as $fileName=>$trash){
			$dirname  = dirname($fileName);
			$outDN    = "$targetDir/$dirname";
			
			if(substr($dirname, 0, strlen($baseDir)) != $baseDir)
				continue;
			
			if(!is_dir($outDN) && $maintainStructure){
				$str = "";
				$folders = explode("/", $dirname);
				foreach($folders as $folder){
					$str = $str?"$str/$folder":$folder;
					if(!is_dir("$targetDir/$str")){
						$this->debugMsg(1, "Creating folder: $targetDir/$str");
						mkdir("$targetDir/$str");
						// Vince changed
						/*if($applyChmod)
							chmod("$targetDir/$str", $applyChmod); */
						if ($this->allow_chmodding)
							chmod($targetDir.DS.$str, $this->permissions_directories);
						//echo "Chmodded $targetDir/$str to ".$this->permissions_directories." <br/>";
						// end
					}
				}
			}
			if(substr($fileName, -1, 1) == "/")
				continue;
			
			$maintainStructure?
				$this->unzip($fileName, "$targetDir/$fileName", $applyChmod):
				$this->unzip($fileName, "$targetDir/".basename($fileName), $applyChmod);
		}
	}
	
	Function close(){     // Free the file resource
		if($this->fh)
			fclose($this->fh);
	}
	Function __destroy(){ 
		$this->close();
	}
	
	// Private (you should NOT call these methods):
	Function uncompress(&$content, $mode, $uncompressedSize, $targetFileName=false){
		switch($mode){
			case 0:
				// Not compressed
				return $targetFileName?
					file_put_contents($targetFileName, $content):
					$content;
			case 1:
				$this->debugMsg(2, "Shrunk mode is not supported... yet?");
				return false;
			case 2:
			case 3:
			case 4:
			case 5:
				$this->debugMsg(2, "Compression factor ".($mode-1)." is not supported... yet?");
				return false;
			case 6:
				$this->debugMsg(2, "Implode is not supported... yet?");
				return false;
			case 7:
				$this->debugMsg(2, "Tokenizing compression algorithm is not supported... yet?");
				return false;
			case 8:
				// Deflate
				return $targetFileName?
					file_put_contents($targetFileName, gzinflate($content, $uncompressedSize)):
					gzinflate($content, $uncompressedSize);
			case 9:
				$this->debugMsg(2, "Enhanced Deflating is not supported... yet?");
				return false;
			case 10:
				$this->debugMsg(2, "PKWARE Date Compression Library Impoloding is not supported... yet?");
				return false;
           case 12:
               // Bzip2
               return $targetFileName?
                   file_put_contents($targetFileName, bzdecompress($content)):
                   bzdecompress($content);
			case 18:
				$this->debugMsg(2, "IBM TERSE is not supported... yet?");
				return false;
			default:
				$this->debugMsg(2, "Unknown uncompress method: $mode");
				return false;
		}
	}
	Function debugMsg($level, $string){
		if($this->debug){
			if($level == 1)
				echo "<b style='color: #777'>dUnzip2:</b> $string<br>";
			
			if($level == 2)
				echo "<b style='color: #F00'>dUnzip2:</b> $string<br>";
		}
		$this->lastError = $string;
	}
	Function getLastError(){
		return $this->lastError;
	}
	
	Function _loadFileListByEOF(&$fh, $stopOnFile=false){
		// Check if there's a valid Central Dir signature.
		// Let's consider a file comment smaller than 1024 characters...
		// Actually, it length can be 65536.. But we're not going to support it.
		
		for($x = 0; $x < 1024; $x++){
			fseek($fh, -22-$x, SEEK_END);
			
			$signature = fread($fh, 4);
			if($signature == $this->dirSignatureE){
				// If found EOF Central Dir
				$eodir['disk_number_this']   = unpack("v", fread($fh, 2)); // number of this disk
				$eodir['disk_number']        = unpack("v", fread($fh, 2)); // number of the disk with the start of the central directory
				$eodir['total_entries_this'] = unpack("v", fread($fh, 2)); // total number of entries in the central dir on this disk
				$eodir['total_entries']      = unpack("v", fread($fh, 2)); // total number of entries in
				$eodir['size_of_cd']         = unpack("V", fread($fh, 4)); // size of the central directory
				$eodir['offset_start_cd']    = unpack("V", fread($fh, 4)); // offset of start of central directory with respect to the starting disk number
				$zipFileCommentLenght        = unpack("v", fread($fh, 2)); // zipfile comment length
				$eodir['zipfile_comment']    = $zipFileCommentLenght[1]?fread($fh, $zipFileCommentLenght[1]):''; // zipfile comment
				$this->endOfCentral = Array(
					'disk_number_this'=>$eodir['disk_number_this'][1],
					'disk_number'=>$eodir['disk_number'][1],
					'total_entries_this'=>$eodir['total_entries_this'][1],
					'total_entries'=>$eodir['total_entries'][1],
					'size_of_cd'=>$eodir['size_of_cd'][1],
					'offset_start_cd'=>$eodir['offset_start_cd'][1],
					'zipfile_comment'=>$eodir['zipfile_comment'],
				);
				
				// Then, load file list
				fseek($fh, $this->endOfCentral['offset_start_cd']);
				$signature = fread($fh, 4);
				
				while($signature == $this->dirSignature){
					$dir['version_madeby']      = unpack("v", fread($fh, 2)); // version made by
					$dir['version_needed']      = unpack("v", fread($fh, 2)); // version needed to extract
					$dir['general_bit_flag']    = unpack("v", fread($fh, 2)); // general purpose bit flag
					$dir['compression_method']  = unpack("v", fread($fh, 2)); // compression method
					$dir['lastmod_time']        = unpack("v", fread($fh, 2)); // last mod file time
					$dir['lastmod_date']        = unpack("v", fread($fh, 2)); // last mod file date
					$dir['crc-32']              = fread($fh, 4);              // crc-32
					$dir['compressed_size']     = unpack("V", fread($fh, 4)); // compressed size
					$dir['uncompressed_size']   = unpack("V", fread($fh, 4)); // uncompressed size
					$fileNameLength             = unpack("v", fread($fh, 2)); // filename length
					$extraFieldLength           = unpack("v", fread($fh, 2)); // extra field length
					$fileCommentLength          = unpack("v", fread($fh, 2)); // file comment length
					$dir['disk_number_start']   = unpack("v", fread($fh, 2)); // disk number start
					$dir['internal_attributes'] = unpack("v", fread($fh, 2)); // internal file attributes-byte1
					$dir['external_attributes1']= unpack("v", fread($fh, 2)); // external file attributes-byte2
					$dir['external_attributes2']= unpack("v", fread($fh, 2)); // external file attributes
					$dir['relative_offset']     = unpack("V", fread($fh, 4)); // relative offset of local header
					$dir['file_name']           = fread($fh, $fileNameLength[1]);                             // filename
					$dir['extra_field']         = $extraFieldLength[1] ?fread($fh, $extraFieldLength[1]) :''; // extra field
					$dir['file_comment']        = $fileCommentLength[1]?fread($fh, $fileCommentLength[1]):''; // file comment			
					
					// Convert the date and time, from MS-DOS format to UNIX Timestamp
					$BINlastmod_date = str_pad(decbin($dir['lastmod_date'][1]), 16, '0', STR_PAD_LEFT);
					$BINlastmod_time = str_pad(decbin($dir['lastmod_time'][1]), 16, '0', STR_PAD_LEFT);
					$lastmod_dateY = bindec(substr($BINlastmod_date,  0, 7))+1980;
					$lastmod_dateM = bindec(substr($BINlastmod_date,  7, 4));
					$lastmod_dateD = bindec(substr($BINlastmod_date, 11, 5));
					$lastmod_timeH = bindec(substr($BINlastmod_time,   0, 5));
					$lastmod_timeM = bindec(substr($BINlastmod_time,   5, 6));
					$lastmod_timeS = bindec(substr($BINlastmod_time,  11, 5));	
					
					// Some protection agains attacks...
					$dir['file_name']     = $this->_decodeFilename($dir['file_name']);
					if(!$dir['file_name'] = $this->_protect($dir['file_name']))
						continue;
					
					$this->centralDirList[$dir['file_name']] = Array(
						'version_madeby'=>$dir['version_madeby'][1],
						'version_needed'=>$dir['version_needed'][1],
						'general_bit_flag'=>str_pad(decbin($dir['general_bit_flag'][1]), 8, '0', STR_PAD_LEFT),
						'compression_method'=>$dir['compression_method'][1],
						'lastmod_datetime'  =>mktime($lastmod_timeH, $lastmod_timeM, $lastmod_timeS, $lastmod_dateM, $lastmod_dateD, $lastmod_dateY),
						'crc-32'            =>str_pad(dechex(ord($dir['crc-32'][3])), 2, '0', STR_PAD_LEFT).
											  str_pad(dechex(ord($dir['crc-32'][2])), 2, '0', STR_PAD_LEFT).
											  str_pad(dechex(ord($dir['crc-32'][1])), 2, '0', STR_PAD_LEFT).
											  str_pad(dechex(ord($dir['crc-32'][0])), 2, '0', STR_PAD_LEFT),
						'compressed_size'=>$dir['compressed_size'][1],
						'uncompressed_size'=>$dir['uncompressed_size'][1],
						'disk_number_start'=>$dir['disk_number_start'][1],
						'internal_attributes'=>$dir['internal_attributes'][1],
						'external_attributes1'=>$dir['external_attributes1'][1],
						'external_attributes2'=>$dir['external_attributes2'][1],
						'relative_offset'=>$dir['relative_offset'][1],
						'file_name'=>$dir['file_name'],
						'extra_field'=>$dir['extra_field'],
						'file_comment'=>$dir['file_comment'],
					);
					$signature = fread($fh, 4);
				}
				
				// If loaded centralDirs, then try to identify the offsetPosition of the compressed data.
				if($this->centralDirList) foreach($this->centralDirList as $filename=>$details){
					$i = $this->_getFileHeaderInformation($fh, $details['relative_offset']);
					$this->compressedList[$filename]['file_name']          = $filename;
					$this->compressedList[$filename]['compression_method'] = $details['compression_method'];
					$this->compressedList[$filename]['version_needed']     = $details['version_needed'];
					$this->compressedList[$filename]['lastmod_datetime']   = $details['lastmod_datetime'];
					$this->compressedList[$filename]['crc-32']             = $details['crc-32'];
					$this->compressedList[$filename]['compressed_size']    = $details['compressed_size'];
					$this->compressedList[$filename]['uncompressed_size']  = $details['uncompressed_size'];
					$this->compressedList[$filename]['lastmod_datetime']   = $details['lastmod_datetime'];
					$this->compressedList[$filename]['extra_field']        = $i['extra_field'];
					$this->compressedList[$filename]['contents-startOffset']=$i['contents-startOffset'];
					if(strtolower($stopOnFile) == strtolower($filename))
						break;
				}
				return true;
			}
		}
		return false;
	}
	Function _loadFileListBySignatures(&$fh, $stopOnFile=false){
		fseek($fh, 0);
		
		$return = false;
		for(;;){
			$details = $this->_getFileHeaderInformation($fh);
			if(!$details){
				$this->debugMsg(1, "Invalid signature. Trying to verify if is old style Data Descriptor...");
				fseek($fh, 12 - 4, SEEK_CUR); // 12: Data descriptor - 4: Signature (that will be read again)
				$details = $this->_getFileHeaderInformation($fh);
			}
			if(!$details){
				$this->debugMsg(1, "Still invalid signature. Probably reached the end of the file.");
				break;
			}
			$filename = $details['file_name'];
			$this->compressedList[$filename] = $details;
			$return = true;
			if(strtolower($stopOnFile) == strtolower($filename))
				break;
		}
		
		return $return;
	}
	Function _getFileHeaderInformation(&$fh, $startOffset=false){
		if($startOffset !== false)
			fseek($fh, $startOffset);
		
		$signature = fread($fh, 4);
		if($signature == $this->zipSignature){
			# $this->debugMsg(1, "Zip Signature!");
			
			// Get information about the zipped file
			$file['version_needed']     = unpack("v", fread($fh, 2)); // version needed to extract
			$file['general_bit_flag']   = unpack("v", fread($fh, 2)); // general purpose bit flag
			$file['compression_method'] = unpack("v", fread($fh, 2)); // compression method
			$file['lastmod_time']       = unpack("v", fread($fh, 2)); // last mod file time
			$file['lastmod_date']       = unpack("v", fread($fh, 2)); // last mod file date
			$file['crc-32']             = fread($fh, 4);              // crc-32
			$file['compressed_size']    = unpack("V", fread($fh, 4)); // compressed size
			$file['uncompressed_size']  = unpack("V", fread($fh, 4)); // uncompressed size
			$fileNameLength             = unpack("v", fread($fh, 2)); // filename length
			$extraFieldLength           = unpack("v", fread($fh, 2)); // extra field length
			$file['file_name']          = fread($fh, $fileNameLength[1]); // filename
			$file['extra_field']        = $extraFieldLength[1]?fread($fh, $extraFieldLength[1]):''; // extra field
			$file['contents-startOffset']= ftell($fh);
			
			// Bypass the whole compressed contents, and look for the next file
			fseek($fh, $file['compressed_size'][1], SEEK_CUR);
			
			// Convert the date and time, from MS-DOS format to UNIX Timestamp
			$BINlastmod_date = str_pad(decbin($file['lastmod_date'][1]), 16, '0', STR_PAD_LEFT);
			$BINlastmod_time = str_pad(decbin($file['lastmod_time'][1]), 16, '0', STR_PAD_LEFT);
			$lastmod_dateY = bindec(substr($BINlastmod_date,  0, 7))+1980;
			$lastmod_dateM = bindec(substr($BINlastmod_date,  7, 4));
			$lastmod_dateD = bindec(substr($BINlastmod_date, 11, 5));
			$lastmod_timeH = bindec(substr($BINlastmod_time,   0, 5));
			$lastmod_timeM = bindec(substr($BINlastmod_time,   5, 6));
			$lastmod_timeS = bindec(substr($BINlastmod_time,  11, 5));
			
			// Some protection agains attacks...
			$file['file_name']     = $this->_decodeFilename($file['file_name']);
			if(!$file['file_name'] = $this->_protect($file['file_name']))
				return false;
			
			// Mount file table
			$i = Array(
				'file_name'         =>$file['file_name'],
				'compression_method'=>$file['compression_method'][1],
				'version_needed'    =>$file['version_needed'][1],
				'lastmod_datetime'  =>mktime($lastmod_timeH, $lastmod_timeM, $lastmod_timeS, $lastmod_dateM, $lastmod_dateD, $lastmod_dateY),
				'crc-32'            =>str_pad(dechex(ord($file['crc-32'][3])), 2, '0', STR_PAD_LEFT).
									  str_pad(dechex(ord($file['crc-32'][2])), 2, '0', STR_PAD_LEFT).
									  str_pad(dechex(ord($file['crc-32'][1])), 2, '0', STR_PAD_LEFT).
									  str_pad(dechex(ord($file['crc-32'][0])), 2, '0', STR_PAD_LEFT),
				'compressed_size'   =>$file['compressed_size'][1],
				'uncompressed_size' =>$file['uncompressed_size'][1],
				'extra_field'       =>$file['extra_field'],
				'general_bit_flag'  =>str_pad(decbin($file['general_bit_flag'][1]), 8, '0', STR_PAD_LEFT),
				'contents-startOffset'=>$file['contents-startOffset']
			);
			return $i;
		}
		return false;
	}
	
	Function _decodeFilename($filename){
		$from = "\xb7\xb5\xb6\xc7\x8e\x8f\x92\x80\xd4\x90\xd2\xd3\xde\xd6\xd7\xd8\xd1\xa5\xe3\xe0".
		        "\xe2\xe5\x99\x9d\xeb\xe9\xea\x9a\xed\xe8\xe1\x85\xa0\x83\xc6\x84\x86\x91\x87\x8a".
				"\x82\x88\x89\x8d\xa1\x8c\x8b\xd0\xa4\x95\xa2\x93\xe4\x94\x9b\x97\xa3\x96\xec\xe7".
				"\x98Ô";
		$to   = "¿¡¬√ƒ≈∆«»… ÀÃÕŒœ–—“”‘’÷ÿŸ⁄€‹›ﬁﬂ‡·‚„‰ÂÊÁËÈÍÎÏÌÓÔÒÚÛÙıˆ¯˘˙˚˝˛ˇ¥";

		return strtr($filename, $from, $to);
	}
	Function _protect($fullPath){
		// Known hack-attacks (filename like):
		//   /home/usr
		//   ../../home/usr
		//   folder/../../../home/usr
		//   sample/(x0)../home/usr
		
		$fullPath = strtr($fullPath, ":*<>|\"\x0\\", "......./");
		while($fullPath[0] == "/")
			$fullPath = substr($fullPath, 1);
		
		if(substr($fullPath, -1) == "/"){
			$base     = '';
			$fullPath = substr($fullPath, 0, -1);
		}
		else{
			$base     = basename($fullPath);
			$fullPath = dirname($fullPath);
		}
		
		$parts   = explode("/", $fullPath);
		$lastIdx = false;
		foreach($parts as $idx=>$part){
			if($part == ".")
				unset($parts[$idx]);
			elseif($part == ".."){
				unset($parts[$idx]);
				if($lastIdx !== false){
					unset($parts[$lastIdx]);
				}
			}
			elseif($part === ''){
				unset($parts[$idx]);
			}
			else{
				$lastIdx = $idx;
			}
		}
		
		$fullPath = sizeof($parts)?implode("/", $parts)."/":"";
		return $fullPath.$base;
	}
}
