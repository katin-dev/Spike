<?php
class Lexema_Callback extends Lexema_Tag {
	
	public static $callback;
	
	public function parse($data) {
		if(isset(self::$callback)) {
			return call_user_func_array(self::$callback, array($this->getParams($data), $this->getTemplate()));
		}
	}
	
	/**
	 * Получить список переданных параметров
	 * @return array key-value массив
	 */
	public function getParams($data) {
		$params = array();
		preg_match_all('#([-_\w]+)\s*=\s*"([^"]+)"#ims', $this->getParamsString(), $m);
		if(!empty($m[0])) {
			
			$keys = $m[1];
			$values = $m[2];
			 
			$params =  array();
			foreach ($keys as $i => $key) {
				if(preg_match('/^{(.*)}$/', $values[$i], $m)) {
					$params[$key] = $this->getVariableValue($m[1], $data);
				} else {
					$params[$key] = $values[$i];
				}
			}
		}
		return $params;
	}
	
	/**
	 * Получить переданный в callback шаблон
	 * @return string
	 */
	public function getTemplate() {
		$template = "";
		foreach ($this->getTags() as $tag) {
			$template .= $tag->getContent();
		}
		return $template;
	}
}
