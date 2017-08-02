<?php
/**
 * 
 *@author Payment Plugins
 *@copyright 2015, PaymentPlugins.com
 *@property-read array $admin_page array of settings to display on the admin page.
 */
class SM_Admin_Screen {
	
	private static $admin_page = array('page_title', 'file_delimiter', 'customer_message', 'send_email', 
			'update_shipping', 'file_example');
	
	private static $tools_page = array('tools_title', 'file_path');
	
	private static $debug_page = array('debug_title', 'enable_debug');
	
	private static $license_page = array('license_title', 'license_key', 'license_status');
	
	/**
	 * Initialize all necessary functionality. 
	 */
	public static function init(){
		
		//Add all necessary functions to the appropriate menu slug.
		add_action('admin_menu', __CLASS__.'::initializeAdminMenu');
		
		//Adds all necessary js and css files to the admin screens.
		add_action('admin_init', __CLASS__.'::includeScripts');
		
		//Adds the save function to the admin_init action.
		add_action('admin_init', __CLASS__.'::saveConfigSettings');
	}

	public static function saveConfigSettings($keys){
		if(isset($_POST['save_shipping_manager_settings'])){
			self::savesettings(self::$admin_page);
		}
		elseif(isset($_POST['save_shipping_manager_debug'])){
			self::saveSettings(self::$debug_page);
		}
		elseif(isset($_POST['save_shipping_manager_delete_log'])){
			PP_SM()->log->delete_log();
		}
		elseif(isset($_POST['activate_shipping_manager_license'])){
			PP_SM()->activate_payments_license();
			wp_redirect(get_admin_url().'admin.php?page=shipping-manager-license');
		}
	}
	
	private static function saveSettings(array $keys){
		unset($keys['page_title']);
		foreach($keys as $key){
			$value = isset($_POST[$key]) ? $_POST[$key] : PP_SM()->required_settings[$key]['default'];
			PP_SM()->set_option($key, $value);
		}
		PP_SM()->saveSettings();
	}
	
	public static function includeScripts(){
		wp_enqueue_style('shipping-manager-styles', SHIPPING_MANAGER_ASSETS.'css/shipping-manager-admin.css');
		wp_enqueue_script('shipping-manager-js', SHIPPING_MANAGER_ASSETS.'js/shipping-manager.js');
	}
	
	public static function initializeAdminMenu(){
		add_submenu_page('woocommerce', __('Shipping Manager', 'shipping_Manager'), __('Shipping Manager', 'shipping_Manager'),
				 'manage_options', 'shipping-manager-settings', __CLASS__.'::shippingManagerPage');
		add_submenu_page('options.php', __('Tools', 'shipping_Manager'), __('Tools', 'shipping_Manager'),
				'manage_options', 'shipping-manager-tools', __CLASS__.'::shippingManagerTools');
		add_submenu_page('options.php', __('Debug Log', 'shipping_Manager'), __('Debug Log', 'shipping_Manager'),
				'manage_options', 'shipping-manager-debug', __CLASS__.'::displayDebugLog');
		add_submenu_page('options.php', __('License', 'shipping_Manager'), __('License', 'shipping_Manager'),
				'manage_options', 'shipping-manager-license', __CLASS__.'::displayLicenseActivation');
	}
	
	public static function shippingManagerPage(){
		self::getHeader();
		$html = '<div class="shipping-manager-container">';
		$html .= PP_HtmlHelper::startForm(array('method'=>'POST', 'class'=>array(''))).
			PP_HtmlHelper::startTable(array('class'=>array('shipping-manager-table')));
		foreach(self::$admin_page as $index => $key){
			$html .= PP_HtmlHelper::buildSettings($key, PP_SM()->required_settings[$key]);
		}
		$html .= PP_HtmlHelper::endTable();
		$html .= '<button class="shipping-manager-save" name="save_shipping_manager_settings">Save</button>'.
			PP_HtmlHelper::endForm();
		$html .= '</div>';
		echo $html;
	}
	
	public static function shippingManagerTools(){
		self::getHeader();
		$html = '<div class="shipping-manager-container"><div class="upload-overlay"><div class="shipping-manager-loader"></div></div>';
		$html .= PP_HtmlHelper::startTable(array('class'=>array('shipping-manager-table')));
		foreach(self::$tools_page as $index => $key){
			$html .= PP_HtmlHelper::buildSettings($key, PP_SM()->required_settings[$key]);
		}
		$html .= PP_HtmlHelper::endTable();
		$html .= '<div id="success-messages"><div class="close"><img
				src="'.SHIPPING_MANAGER_ASSETS.'images/close.png"/></div><div class="shipping-messages-success"></div></div>
						<div id="shipping-messages"><div class="close"><img id="close-message" 
				src="'.SHIPPING_MANAGER_ASSETS.'images/close.png"/></div><div class="shipping-manager-errors"></div></div>';
		$html .= '<div><button class="shipping-manager-upload" id="upload_file">Upload</button></div>';
		$html .= '<input type="hidden" id="ajax_url" value="'.get_admin_url().'admin-ajax.php?action=upload_shipping_file"/>';
		$html .= '</div>';
		
		echo $html;
	}
	
	public static function displayCustomerMessage(){
		return '<textarea class="shipping-manager-customer-message" name="customer_message">'.PP_SM()->get_option('customer_message').'</textarea>';
	}
	
	public static function displayDelimiterOptions($key, $args){
		$html = '<select id="'.$key.'" name="'.$key.'">';
		foreach($args['options'] as $index => $option){
			$checked = $index === PP_SM()->get_option($key) ? 'selected="checked"' : '';
			$html .= '<option id="'.$index.'" value="'.$index.'" '.$checked.'>'.$option['symbol'].'</option>';
		}
		$html .= '</select>';
		return $html;
	}
	
	public static function displayDebugLog(){
		self::getHeader();
		$html = '<div class="shipping-manager-container">';
		$html .= PP_HtmlHelper::startForm(array('method'=>'POST', 'class'=>array())).PP_HtmlHelper::startTable(array('class'=>array('shipping-manager-table')));
		foreach(self::$debug_page as $index => $key){
			$html .= PP_HtmlHelper::buildSettings($key, PP_SM()->required_settings[$key]);
		}
		$html .= PP_HtmlHelper::endTable().'<div><button class="shipping-manager-save" 
				name="save_shipping_manager_debug">Save</button><button class="shipping-manager-save" 
				name="save_shipping_manager_delete_log">Delete</button></div>'.PP_HtmlHelper::endForm();
		$html .= PP_SM()->log->displayDebugLog();
		echo $html;
	}
	
	public static function displayLicenseActivation(){
		self::getHeader();
		$html = '<div class="shipping-manager-container">';
		$html .= PP_HtmlHelper::startForm(array('method'=>'POST', 'class'=>array())).PP_HtmlHelper::startTable(array('class'=>array('shipping-manager-table')));
		foreach(self::$license_page as $index => $key){
			$html .= PP_HtmlHelper::buildSettings($key, PP_SM()->required_settings[$key]);
		}
		$html .= PP_HtmlHelper::endTable().'<div><button class="shipping-manager-save"
				name="activate_shipping_manager_license">Activate</button></div>'.PP_HtmlHelper::endForm();
		echo $html;
	}
	public static function getHeader(){
		?>
		<div class="shipping-manager-header">
			<ul>
				<li><a href="?page=shipping-manager-settings"><?php echo __('Settings Page', 'shipping_manager')?></a></li>
				<li><a href="?page=shipping-manager-tools"><?php echo __('Shipping Tools', 'shipping_manager')?></a></li>
				<li><a href="?page=shipping-manager-debug"><?php echo __('Debug Log', 'shipping_manager')?></a></li>
				<li><a href="?page=shipping-manager-license"><?php echo __('License', 'shipping_manager')?></a></li>
			</ul>
		</div>
		<?php 
	}
	
	public static function displayFileImage(){
		return '<div class="shipping-file-example"><img src="'.SHIPPING_MANAGER_ASSETS.'images/shipping-file.png"/></div>';
	}
}

SM_Admin_Screen::init();