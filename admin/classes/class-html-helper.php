<?php
class PP_HtmlHelper {
	
	private static $inputTypes = array('text', 'radio', 'checkbox');
	private static $htmlTypes = array('img', 'span', 'div', 'label');
	private static $defaults = array('title'=>'', 'value'=>'', 'type'=>'', 'tool_tip'=>false, 'decription'=>'', 'default'=>'', 'disabled'=>false);
	
	public static function buildSettings($key, $value){
		$html = null;
		$value = wp_parse_args($value, self::$defaults);
		switch($value['type']){
			case 'title':
				$html = self::buildTitle($key, $value);
				break;
			case 'text':
			case 'checkbox':
			case 'radio':
			case 'password':
				$html = '<tr class="shipping-manager-table-row">'.self::buildLabel($key, $value)
					.self::buildInputHTML($key, $value).'</tr>';
				break;
			case 'select':
				$html = '<tr class="shipping-manager-table-row">'.self::buildLabel($key, $value)
					.self::buildSelectHTML($key, $value).'</tr>';
				break;
			case 'custom':
				$html = '<tr class="shipping-manager-table-row">'.self::buildLabel($key, $value)
					.self::callCustomFunction($key, $value).'</tr>';	
					break;
		}
		return $html;
	}
	
	private static function buildTitle($key, $value){
		$title = '<div class="account-title">';
		if(! empty($value['title'])){
			$title .= '<h1>'.$value['title'].'</h1>';
		}
		$title .= '</div>';
		if(! empty($value['description'])){
			$title .= self::buildTitleDescription($value);
		}
		return $title;
	}
	
	public static function startTable($args){
		$defaults = array('class'=>array());
		$args = wp_parse_args($args, $defaults);
		$class = implode('" "', $args['class']);
		return '<table class="'.$class.'"><tbody>';
	}
	
	public static function endTable($args = null){
		return '</tbody></table>';
	}
	/**
	 * Builds the input field from the given parameters.
	 * @param string $key
	 * @param array $value
	 */
	private static function buildLabel($key, $value){
		$label = false;
		if(isset($value['title'])){
			$label = '<th><label>'.$value['title'].'</label>';
			if($value['tool_tip']){
				$label .= self::buildToolTip($value);
			}
			$label .= '</th>';
		}
		
		return $label;
	}
	
	/**
	 * 
	 * @param string $key
	 * @param array $value
	 */
	private static function buildInputHTML($key, $value){
		if(isset($value['value']) && is_array($value['value'])){
			return self::buildInputsHTML($key, $value);
		}
		$option = PP_SM()->get_option($key);
		$input = '<td class="shipping-manager-settings-td"><input ';
		if(! empty($value['type'])){
			$input .= 'type="'.$value['type'].'" ';
		}
		if(isset($key)){
			$input .= 'name="'.$key.'" id="'.$key.'" ';
		}
		if(! empty($value['placeholder'])){
			$input .= 'placeholder="'.$value['placeholder'].'" ';
		}
		if($value['disabled']){
			$input .= 'disabled ';
		}
		if(! empty($value['class']) && is_array($value['class'])){
			$string = '';
			foreach($value['class'] as $class){
				$string .= $class.' ';
			}
			$input .= 'class="'.$string.'" ';
		}
		else $input .= 'class="" ';
		
		if(! empty($option)) {
			if($value['type'] === 'text'){
				$input .= 'value="'.$option.'" ';
			}
			if($value['type'] === 'checkbox'){
				$input .= 'checked="checked" ';
				if(! empty($value['value'])){
					$input .= 'value="'.$value['value'].'" ';
				}
				else $input .= 'value="'.$value['default'].'" ';
				
			}
			if($value['type'] === 'password'){
				$input .= 'value="'.$option.'" ';
			}
		}
		else {
			if($value['type'] === 'text'){
				if(! empty($value['value'])){
					$input .= 'value="'.$value['value'].'" ';
				}
				else $input .= 'value="'.$value['default'].'" ';
			}
			elseif($value['type'] === 'checkbox'){
				if(! empty($value['value'])){
					$input .= 'value="'.$value['value'].'" ';
				}
				else $input .= 'value="yes" ';
			}
			
		}
		$input .= '/>';
		if(isset($value['img'])){
			$class = '';
			if(! empty($value['img']['class'])){
				$class = implode(' ', $value['img']['class']);
			}
			$input .= '<img src="'.$value['img']['src'].'" class="'.$class.'"/>';
		}
		$input .= '</td>';
		return $input;
	}
	
	private static function buildSelectHTML($key, $value){
		$element = '<td class="shipping-manager-settings-td"><select name="'.$key.'" >';
		$option = PP_SM()->get_option($key);
		foreach($value['value'] as $v){
			$element .= '<option value="'.$v.'" ';
			if(! empty($option) && $option === $v){
				$element .= 'selected="selected" ';
			}
			$element .= '>'.$v.'</option>';
		}
		$element .= '</select></td>';
		return $element;
	}
	
	private static function buildInputsHTML($key, $value){
		$element = '<td class="shipping-manager-settings-td">';
		$option = PP_SM()->get_option($key);
		foreach($value['value'] as $index=>$array){
			$element .= '<div class="shipping-manager-settings-div"><input type="'.$value['type'].'" value="'.$index.'" ';
			if($value['type'] === 'radio'){
				$element .= 'name="'.$key.'" ';
			}
			elseif($value['type'] === 'checkbox'){
				$element .= 'name="'.$index.'" ';
			}
			if($option == $index || ! empty($option[$index])){
				$element .= 'checked="checked" ';
			}				
			$element .= '/>';
			if(in_array($value['type'], self::$inputTypes)){
				$element .= '<input type="'.$array['type'].'" ';
				foreach($array as $k=>$v){
					$element .= $k.'="'.$v.'" ';
				}
				$element .= '/>';
			}
			elseif(in_array($array['type'], self::$htmlTypes)){
				$element .= self::buildHTMLElement($array, $array['type']);
			}
			$element .= '</div>';
		}
		$element .= '</td>';
		return $element;
	}
	
	private static function buildHTMLElement($array){
		$type = $array['type'];
		unset($array['type']);
		$element = '<'.$type.' ';
		foreach($array as $k=>$v){
			$element .= $k.'="'.$v.'" ';
		}
		$element .= '</'.$type.'>';
		return $element;
	}
	
	private static function buildToolTip($value){
		return '<span class="shipping-manager-tooltip"><img src="'.SHIPPING_MANAGER_ASSETS.'images/question.png"/>
						<p class="tooltip-description">'.$value['description'].'</span>';
	}
	
	private static function buildTitleDescription($value){
		return '<div class="shipping-manager-title-description">'.$value['description'].'</div>';
	}
	
	private static function callCustomFunction($key, $value){
		$class = implode(' ', $value['class']);
		return '<td class="shipping-manager-settings-td"><div class="shipping-manager-settings-div '.$class.'">'
				.call_user_func($value['function'], $key, $value).'</div></td>';
	}
	
	public static function startForm($args = array('class'=>array())){
		$class = implode(' ', $args['class']);
		return '<form method="'.$args['method'].'" class="'.$class.'">';
	}
	
	public static function endForm(){
		return '</form>';
	}
}