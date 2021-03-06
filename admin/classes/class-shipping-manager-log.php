<?php
class ShippingManager_DebugLog {

	const POST_TYPE = 'shipping_log';
	const MAX_SIZE	= 100;

	/**
	 * Write the error message to the log post.
	*/
	public function writeErrorToLog($message){
		if(! PP_SM()->debug){
			return;
		}
		$post_id = $this->getPostId();
		if($post_id){
			$post_meta = get_post_meta($post_id, 'debug_log', true);
			$post_meta[] = sprintf('<div class="shipping-log-entry">%s
				 <strong>Error Message:</strong>&nbsp%s</div>', $this->getTimeStamp(), $message);
			$this->savePostMeta($post_meta);
		}
	}

	public function writeToLog($message){
		if(! PP_SM()->debug){
			return;
		}
		$post_id = $this->getPostId();
		if($post_id){
			$post_meta = get_post_meta($post_id, 'debug_log', true);
			$post_meta[] = sprintf('<div class="shipping-log-entry">%s
				 <strong>Message:</strong>&nbsp%s</div>', $this->getTimeStamp(), $message);
			$this->savePostMeta($post_meta);
		}
	}
	
	public function addValueToLoop($value, $entry){
		$html = '';
		if(! is_array($value)){
			$html = '<span class="shipping-log-entry">'.$entry.':</span>&nbsp<span class="shipping-log-value">'.$value.'</span>&nbsp&nbsp';
		}
		else if(is_array($value)){
			$html = '<span class="shipping-log-array">'.$entry.'</span>&nbsp';
			foreach($value as $e=>$v){
				$html .= $this->addValueToLoop($v, $e);
			}
		}
		return $html;
	}
	/**
	 * Returns the current post id that has less then 100 entries.
	 * @return int |boolean
	 */
	private function getPostId(){
		$posts = get_posts(array(
				'post_type'=>self::POST_TYPE,
				'posts_per_page'=>-1,
				'post_status'=>'any'
		));
		if(!empty($posts) && is_array($posts)){
			foreach($posts as $post){
				$log_entries = get_post_meta($post->ID, 'debug_log', true);
				if(is_array($log_entries)){
					if(count($log_entries) < self::MAX_SIZE){
						return $post->ID;
					}
				}
			}
		}
		$result = wp_insert_post(array('post_type'=>self::POST_TYPE, 'post_title'=>'Shipping Manager Log'), true);
		if(!is_wp_error($result)){
			return $result;
		}
		return false;
	}

	/**
	 * Returns an array of all the log entries from each post.
	 * @return multitype:
	 */
	private function getPostMeta(){
		$posts = get_posts(array(
				'post_type'=>self::POST_TYPE,
				'posts_per_page'=>-1,
				'post_status'=>'any'
		));
		if(!empty($posts) && is_array($posts)){
			$meta = array();
			foreach($posts as $post){
				$array = get_post_meta($post->ID, 'debug_log', true);
				if(!empty($array)){
					$meta = array_merge($meta, $array);
				}
			}
			return $meta;
		}
		else {
			$post = array('post_type'=>self::POST_TYPE, 'post_title'=>'Shipping Manager Log');
			$result = wp_insert_post($post, true);
			if(! is_wp_error($result)){
				$message[] = sprintf('<div class="shipping-log-entry"><p>Debug log created on %s.</p></div>', $this->getTimeStamp());
				update_post_meta($result, 'debug_log', $message);
				return get_post_meta($result, 'debug_log', $message);
			}
		}
	}

	public function savePostMeta($post_meta){
		update_post_meta($this->getPostId(), 'debug_log', $post_meta);
	}
	public function displayDebugLog(){
		$html =  null;
		$logs = $this->getPostMeta();
		foreach($logs as $log => $entry){
			$html .= $entry;
		}
		return $html;
	}

	public function delete_log(){
		$posts = get_posts(array(
				'post_type'=>self::POST_TYPE,
				'posts_per_page'=>-1,
				'post_status'=>'any'
		));
		if(!empty($posts) && is_array($posts)){
			foreach ($posts as $post){
				wp_delete_post($post->ID, true);
			}
		}
	}
	private function getTimeStamp(){
		return date('m/d/Y:G:i:s', time());
	}
}
?>