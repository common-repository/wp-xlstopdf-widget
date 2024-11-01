<?php

class Wpxlstopdfw_Widget extends WP_Widget {
	public $site_http_root;
	public $input = array();
	
	function Wpxlstopdfw_Widget() {
		$widget_ops = array('classname' => 'widget_wpxlstopdfw', 'description' => __('Add EXCELTOPDF Converter into sidebar'));
		$control_ops = array('width' => 400, 'height' => 350);
		$this->WP_Widget('wpxlstopdfw', __('WP EXCELTOPDF Converter Widget'), $widget_ops, $control_ops);
		$this->site_http_root = get_option('siteurl');
	}

	function widgets_init() {
		register_widget('Wpxlstopdfw_Widget');
	} # widgets_init()
	
	public function parseIt($matches) {
		return $this->input[$matches[1]];
	}
		
	function getWidgetStyle( $input ) {
		$this->input = $input;
		$script = '';
		if (file_exists(WPXLSTOPDFW_PLUGIN_DIR . '/skins/' . $input['skin'] . '/skin.css')) {
			$cscript = file_get_contents(WPXLSTOPDFW_PLUGIN_DIR . '/skins/' . $input['skin'] . '/skin.css');
			$script .= preg_replace_callback("~\[(.*?)\]~s",array($this, 'parseIt'),$cscript);
		} else {
			
		}
		return $script;	
	}
	
	function getWidgetScript( $max_size, $type, $proot ) {		

		$msize = $max_size *1024*1024;	
		//$proot = $_GET['proot'];	

		
		$filter = "{ 'Files (*.xls,*.xlsx)':'*.xls; *.xlsx'}";
		

		$script = "
		//$this->site_http_root
		var keyStr = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=';

		function encode64(input) {
			input = escape(input);
			var output = '';
			var chr1, chr2, chr3 = '';
			var enc1, enc2, enc3, enc4 = '';
			var i = 0;
		
			do {
				chr1 = input.charCodeAt(i++);
				chr2 = input.charCodeAt(i++);
				chr3 = input.charCodeAt(i++);
		
				enc1 = chr1 >> 2;
				enc2 = ((chr1 & 3) << 4) | (chr2 >> 4);
				enc3 = ((chr2 & 15) << 2) | (chr3 >> 6);
				enc4 = chr3 & 63;
		
				if (isNaN(chr2)) {
					enc3 = enc4 = 64;
				} else if (isNaN(chr3)) {
					enc4 = 64;
				}
		
				output = output +
					keyStr.charAt(enc1) +
					keyStr.charAt(enc2) +
					keyStr.charAt(enc3) +
					keyStr.charAt(enc4);
				chr1 = chr2 = chr3 = '';
				enc1 = enc2 = enc3 = enc4 = '';
			} while (i < input.length);
		
			return output;
		}
	
		var maxsize".md5($this->get_field_id('maxsize'))." = $msize;
		var filter".md5($this->get_field_id('filter'))." = $filter;
	
		window.addEvent('domready', function() {
			var note = document.id('note".md5($this->get_field_id('note'))."');
 		
			
		
 
		// Uploader instance
		var swf".md5($this->get_field_id('swf'))." = new Swiff.Uploader('$proot/wp-content/plugins/wp-xlstopdf-widget/js/Swiff.Uploader.swf', {
			url: '$proot/?wpxlstopdfw-upload=$msize&wpxlstopdfw-upload-type=$type',
			verbose: false,
			queued: 1,
			multiple: false,
			id: 'select-".$this->get_field_id('link')."', 
			instantStart: true,
			typeFilter: filter".md5($this->get_field_id('filter')).",
			timeLimit:480,
			fileSizeMax: maxsize".md5($this->get_field_id('maxsize')).",
			vars: {},
			onSelectSuccess: function(files) {		
				this.setEnabled(false);
			},
		
			onSelectFail: function(files) {
				if(files[0].size == 0){
					note.set('text', 'File is empty or corrupted. Please select correct file.');
				} else {
					note.set('text', 'File size is above ' + maxsize".md5($this->get_field_id('maxsize'))."/(1024*1024).round() + 'M. Please select smaller file.');
					alert('File size is above ' + maxsize".md5($this->get_field_id('maxsize'))."/(1024*1024).round() + 'M. Please select smaller file.');
				}
				note.addClass('red');
				note.removeClass('loading');
			},
			onFileStart: function() {
				note.addClass('loading');
				note.removeClass('red');
				note.set('text', 'Uploading file ...');			
			},
			onFileComplete: function(file) {
				
				var resp = JSON.decode(file.response.text);
				
				if (resp.status == 0) {
					note.removeClass('loading');
					note.addClass('red');
					note.set('text', resp.error );
				} else {
					note.removeClass('loading');
					note.removeClass('red');
					note.set('text', 'Success.');
					if (Browser.ie){
						var getAnchor = new Element('a', {
							'href': '$proot/?wpxlstopdfw-download=' + encode64(resp.output),
							'class': 'ieDownload',
							'html': 'Download'
						});
						getAnchor.inject(note);
					} else {
						top.location.href = '$proot/?wpxlstopdfw-download=' + encode64(resp.output);
					}
				}
				
				file.remove();
				this.setEnabled(true);
			},
			onComplete: function() {
			
			}
		});
 
		// Button state
		document.id('select-".$this->get_field_id('link')."').addEvents({
			click: function() {
				this.setStyle('background-position','0 -148px');
				return false;
			},
			mouseenter: function() {
				//alert(this.get('class'));
				this.addClass('hover');
				//swf".md5($this->get_field_id('swf')).".reposition();
			},
			mouseleave: function() {
				this.removeClass('hover');
				this.blur();
			},
			mousedown: function() {
				this.focus();
			}
		});
 
	});
	
";
		return $script;
	}
	
	function widget( $args, $instance ) {
		
		global $wpxlstopdfw_object;
		$o = $wpxlstopdfw_object->get_options();
		
		extract($args);
		$title = apply_filters('widget_title', empty($instance['title']) ? '' : $instance['title']);
		$type = WPXLSTOPDFW_CONVERSION_TYPE;
		$skin = apply_filters( 'widget_wpxlstopdfw', $instance['skin'] );
		$max_size = apply_filters( 'widget_wpxlstopdfw', !$instance['max_size'] ? $o[0]['max_size'] : $instance['max_size'] );
		
		echo $before_widget;
		echo (strlen(trim($title)) > 0 ?$before_title.$title.$after_title:'');
		$output = '
		<style type="text/css">
		'.$this->getWidgetStyle(array(
			'skin' => $skin,
			'type' => $type,
			'site_subfolder' => $wpxlstopdfw_object->subfolder_path,
			'plugin_url' => WPXLSTOPDFW_PLUGIN_URL,
			'content_id' => $this->get_field_id('content'),
			'container_id' => $this->get_field_id('container'),
			'browse-wrap_id' => 'browse-wrap'.md5($this->get_field_id('browse-wrap')),
			'browse_id' => "browse".md5($this->get_field_id('browse')),
			'note_id' => "note".md5($this->get_field_id('note'))
		)).'
		</style>
		<script type="text/javascript">
		'. $this->getWidgetScript($max_size, $type, $wpxlstopdfw_object->subfolder_path) .'
		</script>
		<div class="wpxlstopdfw-content" id="'.$this->get_field_id('content').'">
			<div class="wpxlstopdfw-container" id="'.$this->get_field_id('container').'">
				<div class="wpxlstopdfw-note" id="note'.md5($this->get_field_id('note')).'">
					Please click the Browse button to upload your Excel file 
				</div>
				<div class="wpxlstopdfw-browse-wrap" id="browse-wrap'.md5($this->get_field_id('browse-wrap')).'">
					<div class="wpxlstopdfw-browse" id="browse'.md5($this->get_field_id('browse')).'">
						<div class="button-wrap"><a href="#" id="select-'.$this->get_field_id('link').'"></a></div>
					</div>
				</div>
			
			</div>'.
			( isset($o[0]['afflink']) && $o[0]['afflink'] == 1 ?
			'<div class="guarantee">
				<a href="http://www.investintech.com/able2extract.html?invt='.$o[0]['affid'].'c142"><strong>Convert Excel to PDF</strong></a>
			</div>'
			:
			''
			)
		.'</div>';
		
		 
		
		echo $output; //$type.$skin.$max_size.$this->get_field_id('type');
		
		echo $after_widget;
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['skin'] = $new_instance['skin'];
		$instance['max_size'] = $new_instance['max_size'];
		
		return $instance;
	}

	function form( $instance ) {
		global $wpxlstopdfw_object;
		$o = $wpxlstopdfw_object->get_options();
		
		$instance = wp_parse_args( (array) $instance, array( 'title' => '', 'skin' =>  $o[0]['skin'], 'max_size' => $o[0]['max_size']) );
		$title = strip_tags($instance['title']);
		$skin = $instance['skin'];
		$max_size = strip_tags($instance['max_size']);
?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" /></p>
		<p>
			<label for="<?php echo $this->get_field_id('skin'); ?>"><?php _e('Skin:'); ?></label>
			<select id="<?php echo $this->get_field_id('skin'); ?>" name="<?php echo $this->get_field_name('skin'); ?>">
				<?php
					foreach ($wpxlstopdfw_object->getSkins()as $sk) {
						echo '<option '.($sk==$skin?'selected':'').' value="'.$sk.'">'.$sk.'</option>';
					} 
				?>
			</select>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('max_size'); ?>"><?php _e('Max Size:'); ?></label>
			<input type="text" id="<?php echo $this->get_field_id('max_size'); ?>" name="<?php echo $this->get_field_name('max_size'); ?>" value="<?php echo ( $max_size ? $max_size : '' );?>"/>
		</p>
<?php
	}
}

add_action('widgets_init', array('Wpxlstopdfw_Widget', 'widgets_init'));
?>