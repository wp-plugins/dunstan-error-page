<?php
/*
Plugin Name: Dunstan-style Error Page
Plugin URI: http://www.andrewferguson.net/wordpress-plugins/#errorpage
Plugin Description: A fuller featured 404 error page modeled from http://1976design.com/blog/error/
Version: 1.0
Author: Andrew Ferguson
Author URI: http://www.andrewferguson.net/
*/

/*Use: Gives you a Dunstan style 404 error page.
/*


Dunstan-style Error Page - A fuller featured 404 error page modeled from http://1976design.com/blog/error/
Copyright (c) 2005 Andrew Ferguson

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

function afdn_error_page_myOptionsSubpanel(){
$pluginName = "afdn_error_page";
$pluginVersion = "1.0";

	
	if (isset($_POST['info_update']) && (!empty($_POST['name'])) && (!empty($_POST['num_posts'])))
	{
		
		$results = array(	"name" => $_POST['name'],
							"num_posts" => $_POST['num_posts'],
							"accessed" => $_POST['accessed'],
							
							);
		update_option("afdn_error_page", serialize($results));
	}
	$afdn_error_page = get_option("afdn_error_page");
	?>
	
	<div class=wrap>
		<form method="post">
			<h2>Error Page</h2>
			<p>
			Only three configuration options right now. Type in the name of the owner of the blog, how many posts you want displayed on the 404 page, and how the error page is reached.
			You also need to set your 404 page to index.php?error=404. In apache you would add a line of code that looks similar to this: <code>ErrorDocument 404 "/index.php?error=404"</code>
			<br />
			If you don't have access to to you Apache config file or .htaccess file, of if you don't use Apache, you can use GET tags to send the information to the script. The tags are named "referer" and "requested".
			</p>
			<fieldset name="set1">
				<legend><?php _e('Settings', 'Localization name') ?></legend>
				Who is responsible for the egregious error: <input name="name" type="text" value="<?php echo $afdn_error_page["name"]; ?>" /><br >
				How many posts do you want displayed: <input name="num_posts" type="text" value="<?php echo $afdn_error_page["num_posts"]; ?>" /><br />
                How is this 404 page accessed:<br />
                Directly (Default) <input name="accessed" type="radio" value="directly"
                <?php
                    if(!isset($afdn_error_page["accessed"])||($afdn_error_page["accessed"]=="directly")){
                        echo " checked ";
                    }
                    ?>
                /> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
               Redirect <input name="accessed" type="radio" value="redirect"
                <?php
                    if($afdn_error_page["accessed"]=="redirect"){
                        echo " checked ";
                    }
                ?>
                />
			</fieldset>
			<div class="submit"><input type="submit" name="info_update" value="<?php
				_e('Update options', 'Localization name')
			 ?>&raquo;" /></div>
		</form>
	</div> <?
}



function afdn_error_page_optionsPage(){
	if(function_exists('add_options_page')){
			add_options_page('Error Page', 'Error Page', 10, basename(__FILE__), 'afdn_error_page_myOptionsSubpanel');
	}
}

add_action('admin_menu', 'afdn_error_page_optionsPage');

function afdn_error_page(){


	$referer = $_POST["referer"];
	$badpage = $_POST["badpage"];
	
	$name = $_POST["name"];
	$email = $_POST["email"];
	$comment = $_POST["comment"];
	
	if(isset($_POST["submit_quick"])) $submit_quick = true;
	if(isset($_POST["submit_feedback"])) $submit_feedback = true;
	
	
	if($submit_quick)
	{
		$message = "A 404 error was recieved by ".$_SERVER['REMOTE_ADDR']." on ". date("r", time()).".\n";
		$message .= "Referer: $referer\r\n";
		$message .= "Bad Page: $badpage\r\n";
		$message .= "User details: ".$_SERVER['HTTP_USER_AGENT']."\r\n";
		if(preg_match('/'.urlencode(get_settings('siteurl')).'/', urlencode($_SERVER['HTTP_REFERER']))){
			mail(get_option('admin_email'), '['.get_option("blogname").'] 404 Quick Error Report', $message);
			$reported = true;
		}
	}
	elseif($submit_feedback)
	{
		$message = "A 404 error was recieved by ".$_SERVER['REMOTE_ADDR']." on ". date("r", time()).".\n";
		$message .= "Referer: $referer\r\n";
		$message .= "Bad Page: $badpage\r\n";
		$message .= "User details: ".$_SERVER['HTTP_USER_AGENT']."\r\n";
		$message .= "Name: $name\r\n";
		$message .= "Email: $email\r\n";
		$message .= "Comment: $comment\r\n";
		if(preg_match('/'.urlencode(get_settings('siteurl')).'/', urlencode($_SERVER['HTTP_REFERER']))){
			mail(get_option('admin_email'), '['.get_option("blogname").'] 404 Error Report', $message);
			$reported = true;
		}
	}
	else
	{
		$reported = false;
	}
	
	?>
	
	
	
	<?php get_header(); ?>
	
	
	
	<?php
		$afdn_error_page = get_option("afdn_error_page");
		
		if($afdn_error_page["accessed"]=="redirect"){
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
	
					<p>For some reason, the page your trying to access doesn't exist. Hopefully the information below can be of some assistance - <?php echo $afdn_error_page["name"]; ?>. </p>
	
					<table border="0">
	  <tr>
		<td valign="top" width = "50%">					<h2>Your options</h2>
						<ol>
							<li>Submit an <a href="#quick" title="Jump down to the error reports &#8595;">error report form</a> &#8595;</li>
							<li>Go to the <a href="<?php echo get_settings('siteurl'); ?>" title="Go to the blog homepage">homepage</a></li>
							<li>Read the last <?php echo $afdn_error_page["num_posts"]; ?> blogs &#8594;</li>
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
		<td width="0" valign="top">					<h2>Last <?php echo $afdn_error_page["num_posts"]; ?> blog posts</h2>
						<ol>
							<?php get_archives('postbypost', $afdn_error_page["num_posts"], 'custom', '<li>', '</li>'); ?>
						</ol>
	</td>
	  </tr>
	  <tr>
		<td width="50%" valign="top">					<h2>Quick error report</h2>
						<p>You can quickly report this missing page by clicking the button below <small>(it will reload this page and send <? $nameArray = split(" ", $afdn_error_page["name"]); echo $nameArray[0]; ?> an email with the relevant details attached)</small>.</p>
						<form method="post" action="">
							<div>
								<input type="hidden" name="referer" value="<?PHP echo $httpReferer; ?>" />
								<input type="hidden" name="badpage" value="<?PHP echo $requestURI; ?>" />
							</div>
							<p><input type="submit" name="submit_quick" value="submit quick error report" <?php if($reported) echo "disabled"; ?> /></p>
						</form>
	</td>
		<td width="50%" valign="top">					<h2>Feedback request</h2>
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
	
	<?php //get_sidebar(); ?>
	
	<?php get_footer(); ?>
<?php
}
//add_filter('404_template', 'forceErrorPage');
function forceErrorPage(){
	return "http://www.andrewferguson.net/wp-content/plugins/afdn_error_page.php?isError=1";
}
?>