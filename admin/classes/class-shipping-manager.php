<?php 
/**
 * The shipping manager class handles all admin requests for configuration changes and order updates. 
 * @author Payment Plugins
 * @copyright 2015 PaymentPlugins.com
 *
 *@property-read array $required_settings property that contains all confugrations settings
 */
class Shipping_Manager {
	
	public $required_settings = null; 
	
	protected $settings = null;

	public static $_instance = null;
	
	public $log = null;
	
	public $debug;
	
	private $license = false;
	
	public static $delimiterOptions = array('comma'=>',', 'pipe'=>'|', 'colon'=>':', 'semicolon'=>';');
	
	public $settings_name = 'shipping_manager_settings';
	
	/**
	 * Initialize the instance and set all necessary properties for the class. 
	 */
	public function __construct(){
		self::$_instance = $this;
		$this->required_settings = include_once SHIPPING_MANAGER.'admin/includes/shipping-manager-settings.php';
		$this->init_settings();
		$this->verify_license_for_settingsPage();
	}
	
	/**
	 * /initialize all settins and configurations. 
	 */
	private function init_settings(){
		$this->settings = get_option($this->settings_name, array());
		$this->log = new ShippingManager_DebugLog();
		$this->debug = $this->get_option('enable_debug') === 'yes' ? true : false;
		$this->check_license();
		$this->license = $this->get_option('license_status') === 'active' ? true : false;
	}
	
	public static function init(){
		add_action('wp_ajax_upload_shipping_file', __CLASS__.'::handFileUpload');
	}
	
	public function get_option($key, $value = false){
		if(! isset($this->settings[$key])){
			$this->settings[$key] = isset($this->required_settings[$key]['default'])
				? $this->required_settings[$key]['default'] : '';
		}
		return $this->settings[$key];
	}
	
	public function set_option($key, $value = ''){
		$this->settings[$key] = $value;
	}
	
	private function verify_license_for_settingsPage(){
		if($this->get_option('license_status') === 'active'){
			$this->required_settings['license_status']['value'] = 'Active';
			$this->required_settings['license_key']['value'] = $this->get_option('license_key');
			$this->required_settings['license_status']['img'] = array('src'=>SHIPPING_MANAGER_ASSETS.'
					images/checkmark.svg', 'class'=>array('shipping-manager-checkmark'));
		}
	}
	
	public function saveSettings(){
		update_option($this->settings_name, $this->settings);
	}
	
	public function isActive(){
		return $this->license;
	}
	/**
	 * Intercepts Ajax call for the shipping file upload. 
	 */
	public static function handFileUpload(){
		$response = array();
		if(! PP_SM()->isActive()){
			PP_SM()->log->writeErrorToLog('You must purchase a license before using the file upload functionality');
			PP_SM()->sendResponse(array('result'=>'failure', 'message'=>'You must purchase a license before using
					this functionality.'));
		}
		if(! isset($_POST['file_path']) || empty($_POST['file_path'])){
			PP_SM()->sendResponse(array('result'=>'failure', 'message'=>'The file path cannot be empty'));
		}
		PP_SM()->executeShippingFile($_POST['file_path']);
	}
	
	public function sendResponse($args){
		echo json_encode($args);
		die();
	}
	
	private function executeShippingFile($path = ''){
		if($path[0] !== '/'){
			$path .= '/'.$path;
		}
		$path = WP_CONTENT_DIR.$path;
		if(! file_exists($path)){
			$this->sendResponse(array('result'=>'failure', 'message'=>'The file path 
				does not contain a valid file. Please check the path you have entered.'));
		}
		$delimiter = self::$delimiterOptions[$this->get_option('file_delimiter')];
		if(!$file = fopen($path, 'r')){
			$this->sendResponse(array('result'=>'failure', 'message'=>'The file could not be opened.'));
		}
		while(! feof($file)){
			$pairs[] = fgetcsv($file, 0, $delimiter);
		}
		fclose($file);
		if(is_array($pairs)){
			$this->updateOrdersWithTracking($pairs);
		}
	}
	
	private function updateOrdersWithTracking($pairs){
		if(! $this->isActive()){
			return;
		}
		$sendEmail = $this->get_option('send_email') === 'yes' ? 1 : 0;
		
		foreach($pairs as $index => $pair){
			$order_id = isset($pair[0]) ? $pair[0] : '';
			$update_status = $this->get_option('update_shipping') === 'yes' ? true : false;
			if($order = wc_get_order($order_id)){
				$tracking = isset($pair[1]) ? $pair[1] : '';
				update_post_meta($order->id, '_tracking_number', $tracking);
				$note = '';
				$customer_message = $this->get_option('customer_message');
				if(! empty($customer_message)){
					$note .= $this->get_option('customer_message');
				}
				$note .= 'Tracking Number: '.$tracking;
				$note = apply_filters('update_shipping_manager_customer_note', $note, $order_id);
				$order->add_order_note($note, $sendEmail);
				if($update_status){
					try {
						$order->update_status('completed', __('Tracking updated for order '.$order_id, 'shipping_manager'));
					}
					catch(Exception $e){
						$this->log->writeErrorToLog($e->getMessage());
					}
				}
				$this->log->writeToLog(sprintf('Order ID %s was updated with Tracking #%s.', $order_id, $tracking));
			}
			else{
				$this->log->writeErrorToLog(sprintf('Order ID %s was not found in the system. Line number %s.', $order_id, $index));
			}
		}
		$this->sendResponse(array('result'=>'success', 'message'=>__('The Orders were updated. 
				Please check the debug log for specifics.', 'shipping_manager')));
	}
	
	public function activate_payments_license(){
		$license_key = isset($_POST['license_key']) ? $_POST['license_key'] : '';
	
		$api_params = array('slm_action'=>'slm_activate',
				'secret_key'=>SHIPPINGMANAGER_LICENSE_VERIFICATION_KEY,
				'license_key'=>$license_key
	
		);
		$response = wp_remote_get(add_query_arg($api_params, SHIPPINGMANAGER_LICENSE_ACTIVATION_URL), array('timeout' => 20));
	
		if(is_wp_error($response)){
			foreach($response->get_error_messages() as $message => $value){
				PP_SM()->log->writeErrorToLog($response);
			}
			add_action('admin_notices', 'WP_Manager::displayAdminErrorMessage');
			return false;
		}
	
		$response['body'] = json_decode($response['body']);
	
		if($response['body']->result === 'success'){
			$nextCheck = new DateTime();
			$nextCheck->setTimestamp(time());
			$nextCheck->add(new DateInterval('P5D'));
			PP_SM()->set_option('license_status', 'active');
			PP_SM()->set_option('license_check', $nextCheck->getTimestamp());
			PP_SM()->set_option('license_key', $license_key);
			PP_SM()->log->writeToLog(sprintf('License key %s was successfully activated.', $license_key));
			add_action('admin_notices', 'Shipping_Manager::displayAdminMessage');
			PP_SM()->saveSettings();
			return true;
		}
		else {
			PP_SM()->log->writeErrorToLog(sprintf('%s. License Key: %s', $response['body']->message, $license_key));
			add_action('admin_notices', 'Shipping_Manager::displayAdminErrorMessage');
			return false;
		}
	}
	
	public function check_license(){
		$nextCheck = $this->get_option('license_check');
		$email = get_option('admin_email', true);
		$url = get_site_url();
		$api_params = array('slm_action'=>'slm_check',
					'secret_key'=>SHIPPINGMANAGER_LICENSE_VERIFICATION_KEY,
					'license_key'=>$this->get_option('license_key')
			);
			
		$response = wp_remote_get(add_query_arg($api_params, SHIPPINGMANAGER_LICENSE_ACTIVATION_URL), array('timeout' => 20));
		
		if(! is_wp_error($response)){
			if(isset($response['body'])){
				$body = json_decode($response['body'], true);
				if($body['result'] === 'success'){
					$this->set_option('license_status', 'active');
				}
				else $this->set_option('license_status', 'inactive');
			}
		}
		else{
			$this->set_option('license_status', 'inactive');
		}

	}
	
	public static function displayAdminMessage(){
		$message = 'Your License Key has been activated.';
		echo '<div class="updated"><p>'.$message.'</p></div>';
	}
	
	public static function displayAdminErrorMessage(){
		$message = 'Your License Key could not be activated at this time. Check the debug log for error specifics. If
				the problem persists, please contact support@paymentplugins.com';
		echo '<div class="error"><p>'.$message.'</p></div>';
	}
	
}
Shipping_Manager::init();
/**
 * Helper function that retreives the isntance of Shipping_Manager.
 * @return Shipping_Manager
 */
function PP_SM(){
	if(Shipping_Manager::$_instance){
		return Shipping_Manager::$_instance;
	}
	return new Shipping_Manager();
}
?>