<?php
/*
Plugin Name: Dunstan-style Error Page
Plugin URI: http://www.andrewferguson.net/wordpress-plugins/#errorpage
Plugin Description: A fuller featured 404 error page modeled from http://1976design.com/blog/error/
Version: 1.3.1
Author: Andrew Ferguson
Author URI: http://www.andrewferguson.net/
*/

/*Use: Gives you a Dunstan style 404 error page.
/*


Dunstan-style Error Page - A fuller featured 404 error page modeled from http://1976design.com/blog/error/
Copyright (c) 2005-2006 Andrew Ferguson

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.

*/

add_action('admin_menu', 'afdn_error_page_optionsPage');	//Add Action for adding the options page to admin panel

function afdn_error_page_optionsPage(){						//Action function for adding the configuration panel to the Options Page
	if(function_exists('add_options_page')){
			add_options_page('Error Page', 'Error Page', 10, basename(__FILE__), 'afdn_error_page_myOptionsSubpanel');
	}
}

function afdn_is_comment_spam($name, $email, $comment) {					//Check to see if a submited error report could be spam
	$getOptions = unserialize(get_option("afdn_error_page"));

	if($getOptions['akismetKey'] == NULL)							//See if the API key has been set
		return "You have not entered a valid key!";					//If the API key isn't set, stop the checking process and just let the user know they don't have a key

	# populate comment information
	$comment_data = array(
		'user_ip'               => $_SERVER['REMOTE_ADDR'],			//Submitters IP Address
		'user_agent'            => $_SERVER['HTTP_USER_AGENT'],		//Submitters User Agent
    	'referrer'              => $_REQUEST['REFERER'],			//Submitters Referer
	    'comment_type'          => 'error_report',					//What type of 'comment' it is. Note: Akismet lets this be anything
	    'comment_author'        => $name,							//Submitters name
	    'comment_author_email'  => $email,							//Submitters email address
	    'comment_content'       => $comment,						//Submitters actual comment
	);

	# create akismet handle
	$ak = new afdn_Akismet($getOptions['akismetKey'], get_bloginfo('url'));

	# return akismet result (true for spam, false for ham)
	if($ak->check_comment($comment_data))
		return true;
	else
		return false;
}

function afdn_is_key_valid($keyID){							//Check the validity of the key

	//Create Akismet handle
	$ak = new afdn_Akismet($keyID, get_bloginfo('url'));

	//Check key
	if($ak->verify_key())
		return true;
	else
		return false;
}

function afdn_error_page_myOptionsSubpanel(){
	$pluginVersion = "1.3.1"; 																		//Current Version of plugin
	$updateURL = "http://dev.wp-plugins.org/file/dunstan-error-page/trunk/version.inc?format=txt";	//Where to check for updates

	if(isset($_POST["action"])){
		if (($_POST["action"] == 'info_update') && (afdn_is_key_valid($_POST['akismetKey']))){	//If updates were submited, check to see if the API key is valid
			$results = array(	"name" => $_POST['name'],
								"num_posts" => $_POST['num_posts'],
								"accessed" => $_POST['accessed'],
								"checkUpdate" => $_POST['checkUpdate'],
								"akismetKey" => $_POST['akismetKey'],
								);
			update_option("afdn_error_page", serialize($results));					//If it is, go ahead and update the information, including the API key
		}
		elseif($_POST["action"] == 'info_update'){
			$results = array(	"name" => $_POST['name'],
								"num_posts" => $_POST['num_posts'],
								"accessed" => $_POST['accessed'],
								"checkUpdate" => $_POST['checkUpdate'],
								);
			update_option("afdn_error_page", serialize($results));					//If it's not, update everything but the API key and....
			$keyInvalid = true;														//...set a flag that the API key was invalid
		}
		elseif($_POST["action"] == "unspam"){
			$getOptions = unserialize(get_option("afdn_error_page"));
			$spamArray = unserialize(get_option("afdn_error_page_spam"));
			foreach($_POST["not_spam"] as $spam_id){
					# populate comment information
					$comment_data = array(
											'user_ip'               => $spamArray[$spam_id]["remoteIP"],
											'user_agent'            => $spamArray[$spam_id]["userAgent"],
											'referrer'              => $spamArray[$spam_id]["referer"],
											'comment_type'          => 'error_report',
											'comment_author'        => $spamArray[$spam_id]["userName"],
											'comment_author_email'  => $spamArray[$spam_id]["userEmail"],
											'comment_content'       => $spamArray[$spam_id]["comment"],
										);
					# create akismet handle
					$ak = new afdn_Akismet($getOptions['akismetKey'], get_bloginfo('url'));
					$j = 0;
					if($ak->submit_ham($comment_data)){
						foreach($spamArray as $key => $value){
							if($spam_id != $key){
								$newSpamArray[$j] = $spamArray[$key];
								$j++;
							}
						}
						update_option("afdn_error_page_spam", serialize($newSpamArray));
					}
				}
				echo '<div id="message" class="updated fade"><p>Ham reported.</p></div>';
			}
		elseif($_POST["action"] == "deleteAll"){
			update_option("afdn_error_page_spam", NULL);
			echo '<div id="message" class="updated fade"><p>All spam deleted.</p></div>';
		}
		elseif($_GET["action"] = "isSpam"){
			$getOptions = unserialize(get_option("afdn_error_page"));
			$ak = new afdn_Akismet($getOptions['akismetKey'], get_bloginfo('url'));
			$comment_data = array(
											'user_ip'               => $_GET["remoteip"],
											'user_agent'            => $_GET["useragent"],
											'referrer'              => $_GET["referer"],
											'comment_type'          => 'error_report',
											'comment_author'        => $_GET["userbame"],
											'comment_author_email'  => $_GET["useremail"],
											'comment_content'       => $_GET["comment"],
										);
			if($ak->submit_spam($comment_data)){
				echo '<div id="message" class="updated fade"><p>Spam submited.</p></div>';
			}
		}
	}

	$getOptions = unserialize(get_option("afdn_error_page"));
	?>
	<div class=wrap>
		<form method="post">
		<h2>Error Page</h2>
			<fieldset name="management" class="options">
				<legend><strong>Management</strong></legend>
					Check for updates? <input name="checkUpdate" type="radio" value="1" <?php print($getOptions["checkUpdate"]==1?"checked":NULL)?> />Yes :: <input name="checkUpdate" type="radio" value="0" <?php print($getOptions["checkUpdate"]==0?"checked":NULL)?>/>No
					<?php if($getOptions["checkUpdate"]==1){				//If set to 1, then updates will be checked for
						echo "<br /><br />";
						$currentVersion = file_get_contents($updateURL);	//Get the latest version number
						if($currentVersion == $pluginVersion){				//Version is current
						  echo "You have the latest version.";
						}
						elseif($currentVersion > $pluginVersion){			//Version is not current
						  echo "You have version <strong>$pluginVersion</strong>, the current version is <strong>$currentVersion</strong>.<br />";
						  echo "Download the latest version at <a href=\"http://dev.wp-plugins.org/file/dunstan-error-page/tags/$currentVersion/afdn_error_page.php?format=raw\">http://dev.wp-plugins.org/file/dunstan-error-page/tags/$currentVersion/afdn_error_page.php?format=raw</a>";
						}
						elseif($currentVersion < $pluginVersion){			//Version is higher then current stable (i.e. Alpha/Beta version)
							echo "Beta version, eh?";
						}
					}
						?>
			</fieldset>

			<fieldset name="configuration" class="options">
			<legend><strong>Options</strong></legend>
				<p>Only three configuration options right now. Type in the name of the owner of the blog, how many posts you want displayed on the 404 page, and how the error page is reached. You also need to set your 404 page to index.php?error=404. In apache you would add a line of code that looks similar to this: <code>ErrorDocument 404 "/index.php?error=404"</code>
				<br />If you don't have access to your Apache config file or .htaccess file, of if you don't use Apache, you can use GET tags to send the information to the script. The tags are named "referer" and "requested".</p>
				<p>Who is responsible for the egregious error: <input name="name" type="text" value="<?php echo $getOptions["name"]; ?>" />
				<br />How many posts do you want displayed: <input name="num_posts" type="text" value="<?php echo $getOptions["num_posts"]; ?>" />
				<br />How is this 404 page accessed:
				<br />Directly (Default) <input name="accessed" type="radio" value="directly" <?php print($getOptions["accessed"]!="redirect"?" checked":NULL) ?> />
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Redirect
				<input name="accessed" type="radio" value="redirect" <?php print($getOptions["accessed"]=="redirect"?"checked":NULL) ?> /></p>
			</fieldset>

			<fieldset name="akismetSettings" class="options">
				<legend><strong>Akismet Settings</strong></legend>
				<?php if($keyInvalid){ //P.S. I took this from the Akismet plugin. It alerts the user if their key is invalid ?>
					<p style="padding: .5em; background-color: #f33; color: #fff; font-weight: bold;"><?php _e('Your key appears invalid. Double-check it.'); ?></p>
				<?php } ?>
				<p>
				<p>WordPress.com API Key: <input name="akismetKey" type="text" value="<?php echo $getOptions["akismetKey"]; ?>" /> (<a href="http://faq.wordpress.com/2005/10/19/api-key/" target="_blank">What is this?</a>)</p>
			</fieldset>
			<input type="hidden" name="action" value="info_update" />
			<div class="submit"><input type="submit" name="info_update" value="<?php _e('Update options', 'Localization name')
			 ?>&raquo;" /></div>
		</form>
		</div>
		<?php $spamArray = unserialize(get_option("afdn_error_page_spam")); ?>
		<div class="wrap">
			<h2>Caught Spam</h2>
			<p>You can delete all of the error page comment spam with a single click. This operation cannot be undone, so you may wish to check to ensure that no legitimate error page comments got through first.</p>
			<p>There are currently <?php print(is_array($spamArray)?count($spamArray):"0"); ?> error page comments identified as spam.
			<? if(is_array($spamArray)){ ?>
			<form method="post"><input type="hidden" name="action" value="deleteAll" /><input type="submit" name="deleteAll" value="Delete all" /></form>
			<? } ?>
			</p>

		</div>
		<?php if(is_array($spamArray)){ ?>
		<div class="wrap">
			<h2>Error Page Spam Comments</h2>
			<form method="post">
			<ol id="spam-list" class="commentlist">
			<?php
					if(is_array($spamArray)){
						for($i=0; $i<count($spamArray); $i++){

								echo "<li id='comment-$i' ".($i%2==1?"class=\"alternate\"":NULL)."><p>";

								echo "<strong>Name:</strong> ".$spamArray[$i]["userName"]." | ";
								echo "<strong>Email:</strong> ".$spamArray[$i]["userEmail"]." | ";
								echo "<strong>IP:</strong> ".$spamArray[$i]["remoteIP"]." | ";
								echo "<strong>Date/Time:</strong> ".$spamArray[$i]["dateTime"]."<br />\n";

								echo "<strong>Referer:</strong> ".$spamArray[$i]["referer"]." | ";
								echo "<strong>Bad Page:</strong> ".$spamArray[$i]["badPage"]." | ";
								echo "<strong>User-Agent:</strong> ".$spamArray[$i]["userAgent"]."<br />\n";

								echo "<strong>Comment:</strong><br /> ".$spamArray[$i]["comment"];

								echo "</p>";

								echo "<label for=\"spam-$i\"><input type=\"checkbox\" id=\"spam-$i\" name=\"not_spam[]\" value=\"$i\" />Not Spam</label>";

								echo "</li>\n";

						}
					}
					 ?>
			</ol>
			<input type="hidden" name="action" value="unspam" />
			<div class="submit"><input type="submit" name="unspam" value="<?php _e('Not Spam', 'Localization name')
			 ?>&raquo;" /></div>
			</form>
			<? } ?>
	</div> <?
}

//This is the function that handles all the error reporting
function afdn_error_page(){

	/*Start transfering values from POST*/
	$referer = $_POST["referer"];
	$badpage = $_POST["badpage"];

	$name = $_POST["name"];
	$email = $_POST["email"];
	$comment = $_POST["comment"];
	/*End transfering values from POST*/

	if(isset($_POST["submit_quick"])) $submit_quick = true;			//Is this a quick error report or...
	if(isset($_POST["submit_feedback"])) $submit_feedback = true;	//Is this a detailed error report?

	$siteURL = parse_url(get_settings('siteurl'));					//What is your actual site address?
	$referedURL = parse_url($_SERVER['HTTP_REFERER']);				//What did the referer say your site address was?

	if($submit_quick)																//For a quick error report
	{
		$message = "A 404 error was recieved by ".$_SERVER['REMOTE_ADDR']." on ". date("r", time()).".\n";
		$message .= "Referer: $referer\r\n";
		$message .= "Bad Page: $badpage\r\n";
		$message .= "User details: ".$_SERVER['HTTP_USER_AGENT']."\r\n";

		if(preg_match('/(www\.)?'.$referedURL['host'].'/', $siteURL['host'])){		//Check to make sure the referer at least appears to be coming from your site
			mail(get_option('admin_email'), '['.get_option("blogname").'] 404 Quick Error Report', $message);
			$reported = true;														//Flag so that the user knows their report has been sent
		}
	}
	elseif($submit_feedback)														//For a detailed error report
	{
		$isSpam = afdn_is_comment_spam($name, $email, $comment);
		$message = "A 404 error was recieved by ".$_SERVER['REMOTE_ADDR']." on ". date("r", time()).".\n";
		$message .= "Referer: $referer\r\n";
		$message .= "Bad Page: $badpage\r\n";
		$message .= "User details: ".$_SERVER['HTTP_USER_AGENT']."\r\n";
		$message .= "Name: $name\r\n";
		$message .= "Email: $email\r\n";
		$message .= "Comment: $comment\r\n";
		$message .= "Spam: ".($isSpam==true?"Yes":"No")."\r\n";
		$message .= "\r\n";
		$message .= "If this is spam, visit: ".get_settings("siteurl")."/wp-admin/options-general.php?page=afdn_error_page.php&isSpam=isSpam";
		$message .= "&referer=".urlencode($referer);
		$message .= "&badpage=".urlencode($badpage);
		$message .= "&useragent=".urlencode($_SERVER['HTTP_USER_AGENT']);
		$message .= "&username=".urlencode($name);
		$message .= "&useremail=".urlencode($email);
		$message .= "&comment=".urlencode($comment);
		$message .= "&remoteip=".urlencode($_SERVER['REMOTE_ADDR']);

		if(preg_match('/(www\.)?'.$referedURL['host'].'/', $siteURL['host'])){		//Check to make sure the referer at least appears to be coming from your site
			if(!$isSpam)
				mail(get_option('admin_email'), '['.get_option("blogname").'] 404 Error Report', $message);
			else{
				$spamArray = unserialize(get_option("afdn_error_page_spam"));


				if(is_array($spamArray))
					$i = count($spamArray);
				else
					$i = 0;

				$spamArray[$i] = array(	"errorCode" => "404",
											"remoteIP" => $_SERVER['REMOTE_ADDR'],
											"dateTime" => date("r", time()),
											"referer" => $referer,
											"badPage" => $badpage,
											"userAgent" => $_SERVER['HTTP_USER_AGENT'],
											"userName" => $name,
											"userEmail" => $email,
											"comment" => $comment,
											"isSpam" => true,
										);

				update_option("afdn_error_page_spam", serialize($spamArray));
			}

			$reported = true;														//Flag so that the user knows their report has been sent
		}
	}
	else
	{
		$reported = false;															//If a report is attempted to be filed, but didn't go through
	}

	?>

	<?php get_header(); ?>

	<?php
		$getOptions = unserialize(get_option("afdn_error_page"));

		if($getOptions["accessed"]=="redirect"){
		  $httpReferer = $_GET['referer'];
		  $requestURI = $_GET['requested'];

		}
		else{
		  $httpReferer = $_SERVER['HTTP_REFERER'];
		  $requestURI = $_SERVER['REQUEST_URI'];
		}

	?>
		<div id="content" style = "padding: 20px">
		<?php if($reported){?>
			<h3>Thank you for submitting an error report.</h3>
		<?php } ?>
			<p>For some reason, the page you're trying to access doesn't exist. Hopefully the information below can be of some assistance - <?php print(isset($getOptions["name"])?$getOptions["name"]:"Mgmt"); ?>.</p>
			<table border="0">
				<tr>
					<td valign="top" width = "50%">
						<h2>Your options</h2>
						<ol>
							<li>Submit an <a href="#quick" title="Jump down to the error reports &#8595;">error report form</a> &#8595;</li>
							<li>Go to the <a href="<?php echo get_settings('siteurl'); ?>" title="Go to the blog homepage">homepage</a></li>
							<li>Read the last <?php echo $getOptions["num_posts"]; ?> blogs &#8594;</li>
							<li>Search <?php echo get_settings('blogname'); ?> &#8595;</li>
						</ol>
						<p>
							 <center>
								<form method="get" id="searchform" action="<?php echo get_settings('siteurl'); ?>">
									<div>
										<input type="text" value="<?php echo wp_specialchars($s, 1); ?>" name="s" id="s" />
										<input type="submit" id="searchsubmit" value="Search" />
									</div>
								</form>
							</center>
						</p>
					</td>
					<td width="0" valign="top">
						<h2>Last <?php print(isset($getOptions["num_posts"])?$getOptions["num_posts"]:"5"); ?> blog posts</h2>
						<ol>
							<?php get_archives('postbypost', isset($getOptions["num_posts"])?$getOptions["num_posts"]:5, 'custom', '<li>', '</li>'); ?>
						</ol>
					</td>
				</tr>
				<tr>
					<td width="50%" valign="top">
						<h2>Quick error report</h2>
						<p>You can quickly report this missing page by clicking the button below <small>(it will reload this page and send an email with the relevant details attached)</small>.</p>
						<form method="post" action="">
							<div>
								<input type="hidden" name="referer" value="<?PHP echo $httpReferer; ?>" />
								<input type="hidden" name="badpage" value="<?PHP echo $requestURI; ?>" />
							</div>
							<p><input type="submit" name="submit_quick" value="submit quick error report" <?php if($reported) echo "disabled"; ?> /></p>
						</form>
					</td>
					<td width="50%" valign="top">
						<h2>Feedback request</h2>
						<p>If you&#8217;d like some feedback about the content you are looking for, please fill in the form below and I&#8217;ll get back to you. <small>(The page you were after, and the referring page, will be sent automatically.)</small></p>
						<form method="post" action="">
							<div>
								<input type="hidden" name="referer" value="<?PHP echo $httpReferer; ?>" />
								<input type="hidden" name="badpage" value="<?PHP echo $requestURI; ?>" />
							</div>
							<p>
								<label for="name">Name:</label><br /><input type="text" id="name" name="name" size="30" <?php if($reported) echo "disabled"; ?> /><br />
								<label for="email">Email:</label><br /><input type="text" id="email" name="email" size="30" <?php if($reported) echo "disabled"; ?> /><br />
								<label for="comment">Comment:</label><br /><textarea id="comment" name="comment" cols="22" rows="5" <?php if($reported) echo "disabled"; ?> ></textarea>
							</p>
							<p><input type="submit" name="submit_feedback" value="submit error report" <?php if($reported) echo "disabled"; ?> /></p>
						</form>
					</td>
				</tr>
			</table>
		</div>

	<?php get_footer(); ?>

	<?php
}

/*add_filter('404_template', 'forceErrorPage'); //For later ;-)
function forceErrorPage(){
	return "http://www.andrewferguson.net/wp-content/plugins/afdn_error_page.php?isError=1";
}
*/

#Original Akismet class by Paul Duncan at http://www.pablotron.org/?cid=1485

class afdn_Akismet {
  var $version;

  function afdn_Akismet($api_key, $blog) {
    $this->api_key = $api_key;
    $this->blog = $blog;

    $this->required_keys = array('user_ip', 'user_agent');
  }

  function check_comment($post_args) {
    $this->verify_post_args($post_args);
    return ($this->call('/1.1/comment-check', $post_args, "{$this->api_key}.rest.akismet.com") != 'false');
  }

  function submit_spam($post_args) {
    $this->verify_post_args($post_args);
    return ($this->call('/1.1/submit-spam', $post_args, "{$this->api_key}.rest.akismet.com") != 'false');
  }

  function submit_ham($post_args) {
    $this->verify_post_args($post_args);
    return ($this->call('/1.1/submit-ham', $post_args, "{$this->api_key}.rest.akismet.com") != 'false');
  }

  function verify_key() {
  	$sendKey = array('key' => $this->api_key);
	return ($this->call('/1.1/verify-key', $sendKey, "rest.akismet.com") != 'invalid');
  }

  function verify_post_args($post_args) {
    # iterate over required keys and verify each one
    foreach ($this->required_keys as $key)
      if (!array_key_exists($key, $post_args))
        die("missing required akismet key '$key'");
  }

	function call($meth, $post_args, $host, $port = 80) {

		$post_args['blog'] = $this->blog;

		foreach($post_args as $key => $value){
			$http_content .= $key."=".urlencode($value)."&";
		}
		$http_content = rtrim($http_content, "&");

		$http_request  = "POST $meth HTTP/1.0\r\n";
		$http_request .= "Host: $host\r\n";
		$http_request .= "Content-Type: application/x-www-form-urlencoded; charset=" . get_settings('blog_charset') . "\r\n";
		$http_request .= "Content-Length: " . strlen($http_content) . "\r\n";
		$http_request .= "User-Agent: Wordpress/".get_bloginfo('version')." | afdn_errorPage/1.2\r\n";
		$http_request .= "\r\n";
		$http_request .= $http_content;

		$response = '';
		if( false !== ( $fs = fsockopen($host, $port, $errno, $errstr, 3) ) ) {
			fwrite($fs, $http_request);
			while ( !feof($fs) )
				$response .= fgets($fs, 1160); // One TCP-IP packet
			fclose($fs);
			$response = explode("\r\n\r\n", $response, 2);
		}
		return $response[1];
		}
}
?>