<?php

return array(

		'page_title'=>array(
				'type'=>'title',
				'title'=>__('Shipping Manager Settings', 'worldpayus'),
				'class'=>array(),
				'value'=>'',
				'default'=>'',
				'description'=>__('The settings maintained here will determine the type of 
						functionality that will be enabled. Admins can control how emails are sent, 
						the status of orders once tracking is added, etc.', 'shipping_manager')
		),
		'debug_title'=>array(
				'type'=>'title',
				'title'=>__('Debug Log', 'shipping_manager'),
				'class'=>array(),
				'value'=>'',
				'default'=>'',
				'description'=>__('The settings maintained here will determine the type of
						functionality that will be enabled. Admins can control how emails are sent,
						the status of orders once tracking is added, etc.', 'shipping_manager')
		),
		'license_title'=>array(
				'type'=>'title',
				'title'=>__('License Activation', 'shipping_manager'),
				'class'=>array(),
				'value'=>'',
				'default'=>'',
				'description'=>__('To enable this plugin, you must first purchase a license from https://paymentplugins.com.
						Upon purchase, you will receive a license key which you will enter on this page to activate the plugin.', 'shipping_manager')
		),
		'tools_title'=>array(
				'type'=>'title',
				'title'=>__('File Upload', 'shipping_manager'),
				'class'=>array(),
				'value'=>'',
				'default'=>'',
				'description'=>__('On this page you will enter the file path for the csv that contains the order_id and tracking #
						for your shipments. Once you enter the file path, click upload. If there are any issues and you have enabled debug,
						log entries will be present on the log page. Upload your file by clicking on the Media > Add New link on the 
						Admin navigation panel.', 'shipping_manager')
		),
		'file_delimiter'=>array(
				'type'=>'custom',
				'function'=>'SM_Admin_Screen::displayDelimiterOptions',
				'default'=>',',
				'options'=>array(
						'comma'=>array('symbol'=>','), 
						'pipe'=>array('symbol'=>'|'),
						'colon'=>array('symbol'=>':'),
						'semicolon'=>array('symbol'=>';')
				),
				'title'=>__('File Delimiter', 'shipping_manager'),
				'class'=>array(),
				'tool_tip'=>true,
				'description'=>__('The file delimiter is how the program separates the order_id and the tracking information. 
						So if a comma separates the order and the tracking, select a comma. It is recommended that you use a comma.', 'shipping_manager')
				
		),
		'customer_message'=>array(
				'type'=>'custom',
				'title'=>__('Customer Message', 'shipping_manager'),
				'value'=>'',
				'default'=>'',
				'class'=>array(''),
				'function'=>'SM_Admin_Screen::displayCustomerMessage',
				'tool_tip'=>true,
				'description'=>__('This is the customer message that will be included as a note and sent via email if you have
						enabled the email option.', 'shipping_manager')						
			),
		'send_email'=>array(
				'type'=>'checkbox',
				'value'=>'yes',
				'default'=>'',
				'title'=>__('Send Email', 'shipping_manager'),
				'class'=>array(),
				'tool_tip'=>true,
				'description'=>__('If enabled, customers will receive an email that contains the tracking number for their
						shipment.', 'shipping_manager')
				
		),
		'update_shipping'=>array(
				'type'=>'checkbox',
				'value'=>'yes',
				'default'=>'',
				'title'=>__('Update Shipping', 'shipping_manager'),
				'class'=>array(),
				'tool_tip'=>true,
				'description'=>__('If enabled, the status of the order will be updated to reflect that the shipment
						has been sent.', 'shipping_manager')
		),
		'enable_debug'=>array(
				'type'=>'checkbox',
				'value'=>'yes',
				'default'=>'',
				'title'=>__('Enable Debug', 'shipping_manager'),
				'class'=>array(),
				'tool_tip'=>true,
				'description'=>__('If enabled, a log of the admin\'s actions will be recorded. The log can be deleted if space is
						an issue.', 'worldpayus')
		),
		'file_path'=>array(
				'type'=>'text',
				'value'=>'',
				'default'=>'/uploads/2015/12/tracking_numbers.csv',
				'title'=>__('File Path', 'shipping_manager'),
				'class'=>array('shipping-mananger-upload-field'),
				'tool_tip'=>true,
				'description'=>__('This is the file path of the file you want to use for your orders and tracking.
						The path should point to a file located within the wordpress uploads folder.', 'worldpayus')
		),
		'license_key'=>array(
				'type'=>'text',
				'value'=>'',
				'default'=>'',
				'title'=>__('License Key', 'shipping_manager'),
				'class'=>array(''),
				'tool_tip'=>true,
				'description'=>__('To activate your license, you must purchase a license key from paymentplugins.com.
						Enter the license here and click the activate button. This will enable the functionality of this plugin.', 'worldpayus')
		),
		'license_status'=>array(
				'type'=>'text',
				'value'=>'',
				'default'=>'Inactive',
				'title'=>__('License Status', 'shipping_manager'),
				'class'=>array(''),
				'tool_tip'=>true,
				'description'=>__('This setting displays the status of your license. If you have purchased a license then this
						option will display the words active.', 'worldpayus')
		),
		'file_example'=>array(
				'type'=>'custom',
				'function'=>'SM_Admin_Screen::displayFileImage',
				'default'=>'',
				'title'=>__('File Format', 'shipping_manager'),
				'class'=>array(),
				'tool_tip'=>true,
				'description'=>__('This is image depicts how the file format should be. The first column should contain
						the order id and the second column contains the tracking information.', 'worldpayus')
		)
);

?>