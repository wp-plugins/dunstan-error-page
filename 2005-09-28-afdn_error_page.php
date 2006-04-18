<?php
/*
Plugin Name: Dunstan-style Error Page
Plugin URI: http://www.andrewferguson.net/wordpress-plugins/
Plugin Description: Add template tages to coutn down the years, days, hours, minutes, and seconds to a particular event
Version: 0.6
Author: Andrew Ferguson
Author URI: http://www.andrewferguson.net
*/

/*Use: Gives you a Dunstan style 404 error page.
/*
Dunstan-style Error Page

This code is licensed under the MIT License.
http://www.opensource.org/licenses/mit-license.php
Copyright (c) 2005 Andrew Ferguson

Permission is hereby granted, free of charge, to any person
obtaining a copy of this software and associated
documentation files (the "Software"), to deal in the
Software without restriction, including without limitation
the rights to use, copy, modify, merge, publish,
distribute, sublicense, and/or sell copies of the Software,
and to permit persons to whom the Software is furnished to
do so, subject to the following conditions:

The above copyright notice and this permission notice shall
be included in all copies or substantial portions of the
Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY
KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE
WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR
PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS
OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR
OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR
OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE
SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
*/

function afdn_error_page_myOptionsSubpanel(){

	
	if (isset($_POST['info_update']) && (!empty($_POST['name'])) && (!empty($_POST['num_posts'])))
	{
		
		$results = array(	"name" => $_POST['name'],
							"num_posts" => $_POST['num_posts'],
							"accessed" => $_POST['accessed'],
							
							);
		
		print_r($results); //Debug line
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
?>
