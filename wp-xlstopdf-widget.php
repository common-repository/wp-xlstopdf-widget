<?php
/*
Plugin Name: WP EXCELTOPDF Converter Widget
Plugin URI: http://www.wp-xlstopdf-widget.com
Description: Integrate EXCEL to PDF converter into any worpress website
Version: 0.1
Author: investintech
Author URI: http://www.investintech.com
Usage: View readme.txt

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
load_plugin_textdomain('wp-xlstopdf-widget', false, dirname(plugin_basename(__FILE__)) . '/lang');
define('WPXLSTOPDFW_PLUGIN_DIR',dirname(__FILE__));         //Abs path to the plugin directory
define('WPXLSTOPDFW_PLUGIN_URL','/wp-content/plugins/wp-xlstopdf-widget');  
define('WPXLSTOPDFW_CONVERSION_TYPE','PDFCREATE');
define('WPXLSTOPDFW_APS_WEBSERVICE_DOMAIN','http://184.72.226.232');
define('WPXLSTOPDFW_APS_WEBSERVICE_URL',WPXLSTOPDFW_APS_WEBSERVICE_DOMAIN.'/APSWebService/APSWebService.asmx?WSDL');


if (!class_exists('Wpxlstopdfw')) {
	
	class Wpxlstopdfw {
		public $abs_site_root; 
		public $site_http_root;
		public $options;  // holds calculated options for each visited page on site. 
		public $wholeDir;
		public $unique;
		public $inputFileFolderURL;
		public $timeLimit = 100;  // in seconds, don't make this larger then 480
		public $client;
		public $subfolder_path;
		
		
        function Wpxlstopdfw() {
         	$this->site_http_root = get_option('siteurl');
         	//Root dir of the current site 
         	$this->abs_site_root= ABSPATH;  
         	$this->unique =   uniqid("anonym");
         	$this->wholeDir =  WPXLSTOPDFW_PLUGIN_DIR. '/files/'.$this->unique.'/';
         	$this->client = new SoapClient(WPXLSTOPDFW_APS_WEBSERVICE_URL,array('cache_wsdl' => WSDL_CACHE_NONE, 'compression' => SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP));
         	
         	$this->subfolder_path = parse_url(get_option('siteurl'), PHP_URL_PATH);
        }
        /*
         * Adding cron action to the WP crontab
         */
		function add_crons()  {
			wp_schedule_event(time(), 'daily', 'wpxlstopdfw_cleanup');
        }
        /*
         * Removes cron action from WP crontab
         */
        function remove_crons()  {
        	wp_clear_scheduled_hook('wpxlstopdfw_cleanup');
        }
        /*
         * Cron job function, deletes all converted files older then one day
         */
		function do_cleanup()  {
        	$curent= date("U");
        	$yesterday= $curent-86400;
        	$skindir = WPXLSTOPDFW_PLUGIN_DIR.'/files/';
        	
			$inside = scandir($skindir);
         	foreach($inside as $in) {
         		if (is_dir($skindir.$in) && $in != '.' && $in != '..') {
         			$modtime = filemtime($skindir.$in);
        			if ($modtime < $yesterday) {
        				$this->rmdirRecurse($skindir.$in);
        			}
         		}
         	}
        }
        /*
	 	* Removes all files and folders from passed in directory and removes passed in directory it self in the end 
	 	* Returns true if success and false on failure
	 	*/
		public function rmdirRecurse($path){
			$path = rtrim($path, '/').'/';
			$handle = @opendir($path);
		
			if(!$handle) return false;
		
			while (false !== ($file = @readdir($handle))) {		
				if($file != "." and $file != ".." ){
					$fullpath = $path.$file;				
					if( is_dir($fullpath) ){
						$this->rmdirRecurse($fullpath);					
					}else {
						unlink($fullpath);
					}	
				}			
			}
    		// Delete the root passed directory when it's empty
    		@closedir($handle);
			rmdir($path);
			return true;
		}
		
		
		/**
         * plugin options controller
        */
        function get_options() {
			static $o;
		
			if ( isset($o) && !is_admin() ) {
				return $o;
			}
			$o = get_option('wp_xlstopdf_widget');
		
			if ( $o === false ) {
				$o = $this->init_options();
			}
			return $o;
		}
	
		function init_options() {
			$o = array(
				0 => array(
				'type' => WPXLSTOPDFW_CONVERSION_TYPE,
				'max_size' => 1,
				'affid' => '',
				'afflink' => 1
				), 
			);
			update_option('wp_xlstopdf_widget', $o);		
			return $o;
		}
		/*
		 * Make admin settings pages and blocks
		 */
		function meta_boxes() {
        	if ( current_user_can('unfiltered_html') ) {
        		// settings page
        		add_options_page("WP EXCEL to PDF Converter Widget", "WP EXCEL to PDF Converter Widget", 'manage_options', "wp-xlstopdf-widget", array('WpxlstopdfwAdmin', 'make_admin_page'));
			}
        }
        /*
         * Make WP aware or needed JS libraries, so we can call then when needed
         */
        function add_mootools(&$scripts) {
        	if (!isset($twobuy_mm_object)) {
        		if (!$guessurl = site_url())
				$guessurl = wp_guess_url();

				$scripts->base_url = $guessurl;
				$scripts->default_version = get_bloginfo( 'version' );
        		$scripts->add( 'mootools-core', WPXLSTOPDFW_PLUGIN_URL.'/js/mootools-core-1.3.js', false, '1.3' );
				$scripts->add( 'mootools-more', WPXLSTOPDFW_PLUGIN_URL.'/js/mootools-more-1.3.js', array('mootools-core'), '1.3' );
				$scripts->add( 'swift-uploader', WPXLSTOPDFW_PLUGIN_URL.'/js/Swiff.Uploader.js', array('mootools-more'), '3.0' );
        	}
        } 

		function init_scripts($posts){
 			$o = $this->get_options();
			
			if (is_active_widget(false, false, 'wpxlstopdfw', true)){
				wp_enqueue_script("swift-uploader");
			}

			return $posts;
		}
		/*
		 * Rewrite rules functions
		 */
		function create_rewrite_rules($rewrite) {
			$newrules = array();
			$newrules['wpxlstopdfw-upload/(.*)$'] = 'index.php?wpxlstopdfw-upload=$matches[1]';
			$newrules['wpxlstopdfw-download/(.*)$'] = 'index.php?wpxlstopdfw-download=$matches[1]';
			return $newrules + $rewrite;
        }
        
	 	function add_query_var($vars) {
        	array_push($vars,'wpxlstopdfw-upload');
        	array_push($vars,'wpxlstopdfw-upload-type');
        	array_push($vars,'wpxlstopdfw-download');
			return($vars);
        }
        
		function flush_rules() {
			global $wp_rewrite;
			$wp_rewrite->flush_rules();
		}
		
		function parse_query() {
        	if ($this->is_wpc_template()) {	
				add_action('template_redirect', array($this, 'template_redirect_upload'));
			} elseif($this->is_wpc_template_download()) {
				add_action('template_redirect', array($this, 'template_redirect_download'));
			}
        }
        
		function is_wpc_template() {
    		global $wp_version;
    		$keyword = ( isset($wp_version) && ($wp_version >= 2.0) ) ? 
                get_query_var('wpxlstopdfw-upload') : 
                $GLOBALS['wpxlstopdfw-upload'];
                
			if (!is_null($keyword) && ($keyword != ''))
				return true;
			else
				return false;  
		}
		
		function is_wpc_template_download() {
    		global $wp_version;
    		$keyword = ( isset($wp_version) && ($wp_version >= 2.0) ) ? 
                get_query_var('wpxlstopdfw-download') : 
                $GLOBALS['wpxlstopdfw-download'];
                
			if (!is_null($keyword) && ($keyword != ''))
				return true;
			else
				return false;  
		}
		
		function template_redirect_upload() {
        	global $wp_the_query;
        	$args = array(
        			'max_size' => $wp_the_query->query_vars["wpxlstopdfw-upload"],
        			'type' => $wp_the_query->query_vars["wpxlstopdfw-upload-type"]
        			);

        	include WPXLSTOPDFW_PLUGIN_DIR . '/wp-xlstopdf-widget-upload.php';
			die;
        }
        
		function template_redirect_download() {
        	global $wp_the_query;
        	$args = array(
        			'path'=>$wp_the_query->query_vars["wpxlstopdfw-download"]
        			);

        	include WPXLSTOPDFW_PLUGIN_DIR . '/wp-xlstopdf-widget-download.php';
			die;
        }
        /*
		 * Function to proccess upload
		 * Actually uploading the file, and calling the web service to get the link with resulting file
		 * @param $args  array containing the max_size and type variables
		 */
         function process_upload($args) {
         	set_time_limit(0);
			ini_set('default_socket_timeout',480);
			
         	$o = $this->get_options();

         	$this->inputFileFolderURL =  $this->site_http_root . WPXLSTOPDFW_PLUGIN_URL. '/files/'.$this->unique.'/';

         	$msize = $args['max_size'];
         	$type = $args['type'];
         	
         	$error = false; 
			if (!isset($_FILES['Filedata']) || !is_uploaded_file($_FILES['Filedata']['tmp_name'])) {
				$error = 'Invalid Upload. Please try again. ';
			}
			
			if(!$error){
				if(isset($_FILES['Filedata']['name'])){
					if( ($msize*1024*1024) > $_FILES['Filedata']["size"]){
						$file_data = $this->process_filename($_FILES['Filedata']['name']);
						if (!$file_data['status']) {
							$error = $file_data['error'];
						}
						
						if(!$error){ $error = $this->create_upl_dirs($this->wholeDir); }
						
						if(!$error){
							
							$upload = $this->upload_input_file($this->wholeDir, $file_data, $_FILES);
							$jid = $this->uuid();
							$soap_args = array( 
										'conversion' => $type,
										'inputFileURL' => $this->inputFileFolderURL . $file_data['filename'],  // URL to the input file
										'jobID' => $jid,
										'callbackURL' => ''  // empty means that we don't need callback
									);
							try {
								$soap_res = $this->client->__soapCall('DoConversion_1',array( $soap_args ));

								if($soap_res->DoConversion_1Result->err == 'OK') {
									$Start = microtime(true);
									$End = 0;
									do {
										$End = microtime(true);
										if ( $End - $Start  >= $this->timeLimit){
											$error = 'The request has timed out!';
											break;
										}
    									$soap_ret = $this->client->__soapCall('GetPendingJobURL',array( array('jobID' => $jid) ));
									} while (!$soap_ret->GetPendingJobURLResult);
									
									$out = $soap_ret->GetPendingJobURLResult;
								} else {
									$error = $soap_res->DoConversion_1Result->err;
								}
								
							} catch (Exception $e) {
								$error = 'Error';//$soap_res->faultstring;
							}		
						}
						
					} else {
						$error = "You have to choose a file under ". $msize . " MB!";
					}
				} else {
					$error = "We can't find the file. Please try again. ";
				}
			}
			
         	if ($error) {
				$return = array(
					'status' => '0',
					'error' => $error
				);
			} else {
				$return = array(
					'status' => '1',
					'output' => $out
				);
			}
			
			return $return;
	
        }
		/*
		 * Function to proccess download link
		 * It could be skiped all together, but it's kept here if we need to do some actions for each downloaded file 
		 * @param $args  array containing the path variable, with absolute path returned by the web service 
		 */
        function process_download($args) {

        	$link = base64_decode($args['path']);
        	$file = file_get_contents(WPXLSTOPDFW_APS_WEBSERVICE_DOMAIN . $link);
        	$before_dot = str_replace('\\\'', '_', basename($link));
			$after_dot = substr($before_dot, strrpos($before_dot, '.') + 1);
        	
        	header('Content-type: application/'.$after_dot);
        	header('Content-Disposition: attachment; filename="'.basename($link).'"');
        	
        	echo $file;
        }
        
        
		/**
 		* Creates user directories for uploaded files
 		* @param $usrUploadDir  path
 		*/
		function create_upl_dirs($usrUploadDir) {
			if(!is_dir($usrUploadDir)){
				if( !@mkdir($usrUploadDir,0777) ){
					return "Could not create the 'upload' folder! Please try again. ";
				}
				chmod($usrUploadDir, 0777);
			}
			return false;
		}


		/**
 		* Parses filename and returns result or error
 		* @param $filename
 		*/
		function process_filename($filename="") {
			$return = array(
				'error' => "",
				'status' => 0
			);
		
			$f = str_replace(' ', '_', basename($filename));
			$f = str_replace('\\\'', '_', $f);
			$extension = substr($f, strrpos($f, '.') + 1);
			$onlyfname = substr($f, 0, strrpos($f, '.'));
			if(!$extension||$extension==""){
				$return['error'] = "No extension. Please try again. ";
				return $return;
			}
			if($filename == ""){
		  		$onlyfname = uniqid("filename_");
			}
		
			$f = $onlyfname.".".$extension;
		
			$return = array(
				'error' => "",
				'status' => 1,
				'onlyfname' => $onlyfname,
				'extension' => $extension,
				'filename' => $f
			);
			return $return;
		}
		
		/**
 		* Uploads the input file in filesystem, making it ready for conversion 
 		* @param string $usrUploadDir path
 		* @param string $usrConvertDir path
 		* @param array $fdata
 		* @param array $files
 		*/
		function upload_input_file($usrUploadDir, $fdata, $files) {
	
			$return = array(
				'error' => "",
				'status' => 0
			);
		
			if (!is_writable($usrUploadDir)){
				$return['error'] = "You don't have upload rights! ";
				return $return;
			} else{
				$uploadfile = $usrUploadDir . $fdata['filename'];
				if(is_uploaded_file($files['Filedata']['tmp_name'])) {
					if (move_uploaded_file($files['Filedata']['tmp_name'], $uploadfile)) {
						$return['status'] =	1;
						return $return;	
					} else {
						$return['error'] = "Could not upload file! Please try again. ";
						return $return;
					}
				} else {
					$return['error'] = "Could not upload file! Please try again. ";
					return $return;
				}
			}
		}
		/*
	 	* Discover all available skins in skins folder
	 	*/
		function getSkins() {
			$ret = array();
         	$skindir = WPXLSTOPDFW_PLUGIN_DIR.'/skins/';
         	$inside = scandir($skindir);
         	foreach($inside as $in) {
         		if (is_dir($skindir.$in) && $in != '.' && $in != '..') {
         			$ret[] = $in;
         		}
         	}
         	return $ret;      	
    	}
    	/*
     	* Generate unique windows style ID
     	*/  
    	function uuid() {
     		return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
         		mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),
         		mt_rand( 0, 0x0fff ) | 0x4000,
         		mt_rand( 0, 0x3fff ) | 0x8000,
         		mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ) );
 		}
	
	}
}

if (class_exists("Wpxlstopdfw")) {
	$wpxlstopdfw_object = new Wpxlstopdfw();
}
//Actions and Filters	
if (isset($wpxlstopdfw_object)) {
	/*
	 * Cleanup crons related
	 */
	register_activation_hook(__FILE__,array(&$wpxlstopdfw_object, 'add_crons'));
	register_deactivation_hook(__FILE__, array(&$wpxlstopdfw_object, 'remove_crons'));
	add_action('wpxlstopdfw_cleanup', array(&$wpxlstopdfw_object, 'do_cleanup'));
	/*
	 * Admin related
	 */
	function wp_xlstopdf_widget_admin() {
		include WPXLSTOPDFW_PLUGIN_DIR . '/wp-xlstopdf-widget-admin.php';
	}
	foreach ( array('page.php', 'post-new.php', 'post.php', 'settings_page_wp-xlstopdf-widget') as $hook ) {
		add_action("load-$hook", 'wp_xlstopdf_widget_admin');
	}
	add_action('admin_menu', array(&$wpxlstopdfw_object, 'meta_boxes'), 30);	
	//add_shortcode('wpdftoxls', array(&$wpxlstopdfw_object,'shortcode'));
	/*
	 * Front End related
	 */
	add_action('wp_default_scripts', array(&$wpxlstopdfw_object, 'add_mootools'), 0);
	
	add_filter('the_posts', array(&$wpxlstopdfw_object, 'init_scripts'));
	add_filter('rewrite_rules_array', array(&$wpxlstopdfw_object,'create_rewrite_rules'));
	add_filter('query_vars', array(&$wpxlstopdfw_object, 'add_query_var'));
	add_filter('init', array(&$wpxlstopdfw_object, 'flush_rules'));
	add_action('parse_query', array(&$wpxlstopdfw_object, 'parse_query'));
	/*
	 * Widgets
	 */
	include_once(WPXLSTOPDFW_PLUGIN_DIR.'/wp-xlstopdf-widget-widget.php');
}
?>