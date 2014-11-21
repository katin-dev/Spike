<?php

namespace Spike\Lexema;

class Params
{
	private $paramString;
	public function __construct($string) {
		$this->paramString = $string;
	}
	
	public function getString() {
		return $this->paramString;
	}
	
	/**
	 * Получить список переданных параметров
	 * @return array key-value массив
	 */
	public function getParams($data) {
		$params = array();
		preg_match_all('#([-_\w]+)\s*=\s*"([^"]+)"#ims', $this->getString(), $m);
		if(!empty($m[0])) {
	
			$keys = $m[1];
			$values = $m[2];
	
			$params =  array();
			foreach ($keys as $i => $key) {
				if(preg_match('/^{(.*)}$/', $values[$i], $m)) {
					$foundVar = false; 
					$params[$key] = \Spike\Lexema::getVariableValue($m[1], $data, $foundVar);
				} else {
					$params[$key] = $values[$i];
				}
			}
		}
		return $params;
	}
}
