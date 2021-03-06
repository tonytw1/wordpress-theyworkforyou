<?php
/*
Plugin Name: TheyWorkForYou for Wordpress
Plugin URI: http://philipjohn.co.uk/category/plugins/theyworkforyou/
Description: Provides tools for bloggers based on mySociety's TheyWorkForYou.com
Author: Philip John, Tony McCrae
Version: 0.1c
Author URI: http://philipjohn.co.uk

Future features list;
 * Custom date format
 
*/
/*  Copyright 2009  Philip John Ltd  (email : talkto@philipjohn.co.uk)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the Affero General Public License as published
    by the Affero Inc; either version 2 of the License, or (at your
    option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the Affero General Public License
    along with this program; if not, see http://www.affero.org/oagpl.html
*/

include 'twfy_api.php';


// The settings page for global TheyWorkForYou settings like the API key
function twfy_settings() {
	// The form has been submitted, so do the dirty work
	if ($_POST['twfy_hidden'] == "Y"){
		$twfy_api_key = trim(htmlentities($_POST['twfy_api_key']));
		
		if ($twfy_api_key == '' || validateApiKeyFormat($twfy_api_key)) {	
	        $twfy_settings = array(
    	 	  	'api_key' => $twfy_api_key           
    	    );
			update_option('twfy_settings', $twfy_settings);        
			echo '<div class="updated"><p><strong>'. __('Options saved.' ) .'</strong></p></div>';
			
		} else {
			?>
			<div class="error">
			<p>The API key you have entered does not appear to be in the correct format; no changes have been made 
			<p>(expected a key 24 characters long, containing letters only).</p>
			</div>
			<?php
		}
	}
	
	$twfy_settings = get_option('twfy_settings');
	?>
	<div class="wrap">
		<h2><?php _e('TheyWorkForYou Settings'); ?></h2>
		<form name="twfy_form" method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">  
			<input type="hidden" name="twfy_hidden" value="Y">
			
           	<p>To use this plugin, you will need to obtain an <a href="http://www.theyworkforyou.com/api/">API key</a> from TheyWorkForYou.</p>
			<label for="twfy_api_key">TheyWorkForYou API key: </label>
           	<input type="text" id="twfy_title" size="30" name="twfy_api_key" value="<?php echo stripslashes($twfy_settings['api_key']);?>" />
           	
			<p class="submit">  
				<input type="submit" name="Submit" value="<?php _e('Update Options') ?>" />
			</p>  
		</form>
	</div>
	<?php
}


// The settings panel for the MPs recent activity widget
function twfy_recent_activity_widget_options_control() {	
	$twfy_settings = get_option('twfy_settings');
	$api_key = $twfy_settings['api_key'];
	
	if (!$api_key) {
		?>
		<p>Sorry, no API key detected. Please add a TheyWorkForYou API key to the Settings / TheyWorkForYou panel.</p>
		<?php
		return;
	}
		
	// The form has been submitted, so do the dirty work
	if ($_POST['twfy_hidden'] == "Y"){	
		// Log the ID number of the MP
		$twfy_person_id = trim(htmlentities($_POST['twfy_person_id']));
		$twfy_title = trim($_POST['twfy_title']);
		$twfy_desc = trim(htmlentities($_POST['twfy_desc']));
		$twfy_date = trim(htmlentities($_POST['twfy_date']));
		$twfy_limit = trim(htmlentities($_POST['twfy_limit']));
		$twfy_link = trim(htmlentities($_POST['twfy_link']));

		// TODO move to it's option hash
        $twfy_recent_activity_options = array(
            'person_id' => $twfy_person_id,
            'title' => $twfy_title,
            'desc' => $twfy_desc,
            'date' => $twfy_date,
            'limit' => $twfy_limit,
            'link' => $twfy_link
        );
		update_option('twfy_recent_activity_widget', $twfy_recent_activity_options);        
		echo '<div class="updated"><p><strong>'. __('Options saved.' ) .'</strong></p></div>';
	}

	
	$twfy_recent_activity_widget_options = get_option('twfy_recent_activity_widget');
	$MPs = getMpsList($api_key);
	?>
	<div class="wrap">	
		<form name="twfy_form" method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">  
			<input type="hidden" name="twfy_hidden" value="Y">			
           	
           	<h3><?php _e('Choose your MP'); ?></h3>
			<p>
			<select name="twfy_person_id" id="twfy_person_id">
				<?php
					foreach ($MPs as $MP_id => $MP_name){
						echo '<option value="'.$MP_id.'"';
						if ($twfy_recent_activity_widget_options['person_id'] == $MP_id){
							echo ' selected="selected"';
						}
						echo '>'.$MP_name.'</option>'."\n";
					}
				?>
			</select>
			</p>
				
	     	<fieldset>
	           	<p>
	              	<label for="twfy_title">Widget title: </label>
	              	<input type="text" id="twfy_title" name="twfy_title" value="<?php echo stripslashes($twfy_recent_activity_widget_options['title']);?>" />
	          	</p>
	           	<p>
	            	Show description?<br/>
	              	<label for="twfy_desc_yes"><input type="radio" id="twfy_desc_yes" name="twfy_desc" value="1" <?php if ($twfy_recent_activity_widget_options['desc']==1){echo 'checked="checked" ';} ?>/> Yes</label><br/>
	               	<label for="twfy_desc_no"><input type="radio" id="twfy_desc_no" name="twfy_desc" value="0" <?php if ($twfy_recent_activity_widget_options['desc']==0){echo 'checked="checked" ';} ?>/> No</label>
	           	</p>
	           	<p>
	               	Show date?<br/>
	               	<label for="twfy_date_yes"><input type="radio" id="twfy_date_yes" name="twfy_date" value="1" <?php if ($twfy_recent_activity_widget_options['date']==1){echo 'checked="checked" ';} ?>/> Yes</label><br/>
	               	<label for="twfy_date_no"><input type="radio" id="twfy_date_no" name="twfy_date" value="0" <?php if ($twfy_recent_activity_widget_options['date']==0){echo 'checked="checked" ';} ?>/> No</label>
	           	</p>
	           	<p>
	              	<label for="twfy_limit">How many items should be shown?: </label>
	               	<input type="text" id="twfy_limit" name="twfy_limit" value="<?php echo $twfy_recent_activity_widget_options['limit'];?>" />
	           	</p>
	           	<p>
	               	Show link to MP on TheyWorkForYou.com?<br/>
	               	<label for="twfy_link_yes"><input type="radio" id="twfy_link_yes" name="twfy_link" value="1" <?php if ($twfy_recent_activity_widget_options['link']==1){echo 'checked="checked" ';} ?>/> Yes</label><br/>
	               	<label for="twfy_link_no"><input type="radio" id="twfy_link_no" name="twfy_link" value="0" <?php if ($twfy_recent_activity_widget_options['link']==0){echo 'checked="checked" ';} ?>/> No</label>
	           	</p>
	      	</fieldset>
	      		      		
		</form>
	</div>
	<?php
}



// Recent activity widget
function twfy_recent_activity_widget($args){
	extract($args); // Prep to display widget code
    
    $twfy_recent_activity_widget_options = get_option('twfy_recent_activity_widget');
	
	echo $before_widget;
	echo $before_title.stripslashes($twfy_recent_activity_widget_options['title']).$after_title;
	twfy_recent_activity_widget_contents();
	echo $after_widget;
}


// Recent activity DASHBOARD widget
function twfy_recent_activity_dbwidget(){
    twfy_recent_activity_widget_contents();
}



// Contents for recent activity widgets
function twfy_recent_activity_widget_contents(){
	
	$twfy_settings = get_option('twfy_settings');	
	if ($twfy_settings['api_key'] == FALSE) {
		echo "<p>Sorry, no API key detected. Please add a TheyWorkForYou API key to the Settings / TheyWorkForYou panel.</p>";
		return;
	}
    $api_key = $twfy_settings['api_key'];    	
	
	$twfy_recent_activity_widget_options = get_option('twfy_recent_activity_widget');
    if ($twfy_recent_activity_widget_options['person_id'] !== FALSE){ // Not if the ID isn't set.
    	$xml = getActivityXmlForPerson($twfy_recent_activity_widget_options['person_id'], $api_key);
    	
    	if (!$xml || !$xml->rows->match) {
    		echo "<p>No results found.</p>";
    		return;
    	}
    	
        echo "<ul>\n";
        $i = 0; //counter for number of meetings
        foreach ($xml->rows->match as $match){
            if ($i>=$twfy_recent_activity_widget_options['limit']) { break; } // don't list more than 5 meetings
            $date = strtotime($match->hdate);
            echo '<li>';
            if ($twfy_recent_activity_widget_options['date']==1){ echo date('j M', $date).': '; }
            echo '<a href="http://www.theyworkforyou.com'.$match->listurl.'">'.$match->parent->body.'</a>';
            if ($twfy_recent_activity_widget_options['desc']==1){ echo '<br/>'.$match->body; }
            echo '</li>'."\n";
            $i++; //increment the counter
        }
        echo "</ul>\n";
        
        if ($twfy_recent_activity_widget_options['link']==1){
            // Link back to the MPs page on TWFY
            $MPurl = (string )$xml->rows->match->speaker->url;
            echo '<p>More from <a href="http://www.theyworkforyou.com'.$MPurl.'">TheyWorkForYou.com</a></p>';
        }
    }
    else {
        echo "<p>Sorry, no MP has been selected. Please select an MP from the settings page.</p>";
    }
}



// Initialising function
function twfy_init(){
	register_sidebar_widget(__('MPs Recent Activity'), 'twfy_recent_activity_widget');
    $twfy_default_options = array(
        'person_id'=>'10068',
        'title'=>'MPs recent activity',
        'desc'=>1,
        'date'=>1,
        'limit'=>5,
        'link'=>1
    );
	register_widget_control("MPs Recent Activity", "twfy_recent_activity_widget_options_control");
	add_option('twfy_settings');
    add_option('twfy_recent_activity_widget', $twfy_default_options);
}


// Add the settings page
function twfy_actions(){
	add_options_page('TheyWorkForYou Settings', 'TheyWorkForYou', 5, 'TheyWorkForYou', 'twfy_settings');
}

// Dashboard widgets init function 
function twfy_add_dashboard_widgets(){
	wp_add_dashboard_widget('twfy-recent-activity-widget', 'MPs Recent Activity', 'twfy_recent_activity_dbwidget');
}
	
add_action("plugins_loaded", "twfy_init");
add_action('admin_menu', 'twfy_actions');
add_action('wp_dashboard_setup', 'twfy_add_dashboard_widgets');

?>