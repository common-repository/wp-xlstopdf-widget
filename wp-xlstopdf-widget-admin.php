<?php
/*
Copyright (C) <2010>  <WP EXCELTOPDF Converter Widget>

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.opensource.org/licenses/gpl-3.0.html>
*/

if (!class_exists('WpxlstopdfwAdmin')) {
	
	class WpxlstopdfwAdmin {
		public $abs_site_root;
		public $site_http_root;
		
        function WpxlstopdfwAdmin() {
         	$this->site_http_root = get_option('siteurl');
         	//Root dir of the current site 
         	$this->abs_site_root= ABSPATH;         	
        }
        /**
         * Global options page
         *
         */
        function make_admin_page() {
        	global $wpxlstopdfw_object;
        	
        	if ( !current_user_can('manage_options') )
				return;
			
			// Process the form
			if ( $_POST ) {	

				$fields = array(
					'skin',
					'max_size',
					'affid',
					'afflink'
				);
				
				foreach ((array) $fields as $key) {
					$field = trim($_POST['wp_xlstopdf_widget'][$key]);
					
					if ( !is_null($field) && $field != "") {
						$o[0][$key] = $field;
					} else {
						unset($o[0][$key]);
					}
				}
				update_option('wp_xlstopdf_widget', $o);
				
				echo '<div class="updated fade">' . "\n"
						. '<p>'
						. '<strong>'
						. __('Settings saved.')
						. '</strong>'
						. '</p>' . "\n"
						. '</div>' . "\n";
						
			}
			
			$o = $wpxlstopdfw_object->get_options();
        	
        	
        	echo '<div class="wrap">' . "\n"
			. '<form method="post" action="">' . "\n";
			echo '<h2>'
			. __('WP EXCELTOPDF Converter Widget Setup')
			. '</h2>' . "\n";
			echo '<p>You can set global options for WP EXCELTOPDF Converter Widgets here. Settings can be overriden with every single converter widget instance.</p>'."\n";
			echo '<ul>
				<li><strong>skin:</strong> '. "\t" .'One of predefined skins. Value should be the name of skin folder. You can create your own skins too.</li>
				<li><strong>max_size:</strong> '. "\t" .'number in MB</li>
			</ul>'."\n";
			echo '<table style="width: 100%; border-collapse: collapse; padding: 2px 0px; spacing: 2px 0px;">';
        	echo '<tr valign="top">' . "\n"
        		. '<td colspan="3">'
				. '<h2>Global Converter Settings:</h2>'
				. '</td>' . "\n"
        		. '</tr>'."\n";
        	echo '<tr valign="top">' . "\n"
        		. '<td width="15%" style="padding:15px 0;">'
				. 'Global Skin: '
				. '</td>' . "\n"
				. '<td width="25%" style="padding:15px 0;">'
				. '<select name="wp_xlstopdf_widget[skin]"> ';
				
				foreach($wpxlstopdfw_object->getSkins() as $skin) {
					echo '<option '.($o[0]['skin'] == $skin ?'selected':'').' value="'.$skin.'">'.$skin.'</option>';
				}
			echo '</select> '
				. '</td>' . "\n"
				. '<td width="59%" rowspan="2" style="padding:15px 0;">'
				. ''
				. '</td>'
        		. '</tr>'."\n";
        	echo '<tr valign="top">' . "\n"
        		. '<td width="15%" style="padding:15px 0;">'
				. 'Max File Size: '
				. '</td>' . "\n"
				. '<td width="25%" style="padding:15px 0;">'
				. '<input type="text" name="wp_xlstopdf_widget[max_size]" size="3" value="'.(isset($o[0]['max_size'])?$o[0]['max_size']:'').'"/> MB'
				. '</td>' . "\n"
				. '<td width="59%" rowspan="2" style="padding:15px 0;">'
				. ''
				. '</td>'
        		. '</tr>'."\n";
        	echo '<tr valign="top">' . "\n"
        		. '<td colspan="3">'
				. '<h2>Affiliate Settings:</h2>'
				. '<p>To signup to our affiliate program, please <a target="_blank" href="http://affiliates.investintech.com/signup">click here</a></p>'
				. '<p>If you are already Investintech affiliate you can enter your affiliate ID bellow.</p>'
				. '</td>' . "\n"
        		. '</tr>'."\n";	
        	echo '<tr valign="top">' . "\n"
        		. '<td width="15%" style="padding:15px 0;">'
				. 'Your Affiliate ID: '
				. '</td>' . "\n"
				. '<td width="25%" style="padding:15px 0;">'
				. '<input type="text" name="wp_xlstopdf_widget[affid]" value="'.(isset($o[0]['affid'])?$o[0]['affid']:'').'"/> '
				. '</td>' . "\n"
				. '<td width="59%" rowspan="2" style="padding:15px 0;">'
				. '</td>'
        		. '</tr>'."\n";
        	echo '<tr valign="top">' . "\n"
        		. '<td width="15%" style="padding:15px 0;">'
				. 'Display affiliate link in widgets ?: '
				. '</td>' . "\n"
				. '<td width="25%" style="padding:15px 0;">'
				. '<input type="checkbox" value="1" name="wp_xlstopdf_widget[afflink]" '.(isset($o[0]['afflink'])? ($o[0]['afflink'] == 1 ? 'checked' : '') :'').'/> '
				. '</td>' . "\n"
				. '<td width="59%" rowspan="2" style="padding:15px 0;">'
				. '</td>'
        		. '</tr>'."\n";
        	echo '</table>' . "\n";
        	echo '<p class="submit">'
			. '<input type="submit"'
				. ' value="' . esc_attr(__('Save Changes')) . '"'
				. ' />'
			. '</p>' . "\n";
			echo '</form>' . "\n"
			. '</div>' . "\n";
        	
        }
        
       
       
    }
}


if (class_exists("WpxlstopdfwAdmin")) {
	$wpxlstopdfw_admin_object = new WpxlstopdfwAdmin();
}
//Actions and Filters	
if (isset($wpxlstopdfw_admin_object)) {
	
}
?>