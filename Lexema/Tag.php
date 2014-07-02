<?php
namespace Spike\Lexema;

class Tag extends \Spike\Lexema {
	
	private $tags;
	private $name;
	private $paramsString;
	private $closed = false;
	
	public function __construct($content) {
		parent::__construct($content);
		if(preg_match('/^{{\s*([\/\.\w]+)(.*?)}}$/ims', $content, $m)) {
			$this->name = $m[1];
			$this->paramsString = $m[2];
		} else {
			return null;
		}
	}
	
	public function getName() {
		return $this->name;
	}
	public function getParamsString() {
		return $this->paramsString;
	}
	public function setParamsString($string) {
		$this->paramsString = $string;
	}
	
	public function setTags($tags) {
		$this->tags = $tags;
	}
	public function pushTag($tag) {
		array_push($this->tags, $tag);
	}
	public function popTag() {
		return array_pop($this->tags);
	}
	public function getTags() {
		return $this->tags ? $this->tags : array();
	}
	public function hasTags() {
		return !empty($this->tags);
	}
	
	public function isCloseTag() {
		$c = $this->getName();
		return $c && $c[0] == '/';
	}
	
	public function getOpenTagName() {
		return substr($this->getName(), 1);
	}
	
	public function isClosed() {
		return $this->closed;
	}
	public function close() {
		$this->closed = true;
	}
	
	public function parse($data) {
		if($this->getName() == 'if') {
			/* условная лексема */
			$iAm = new Tag\Condition($this->getContent());
			$iAm->setTags($this->getTags());
		} elseif (($value = $this->getVariableValue($this->getName(), $data)) !== null) {	//@TODO потенциальная ошибка: $data[$name] = null Переменная существует и равна null
			if(is_array($value)) {
				/* лексема - цикл */
				$iAm = new Tag\Loop($this->getContent());
				$iAm->setTags($this->getTags());
			} else {
				$iAm = new Tag\Variable($value);
				$iAm->setParamsString($this->getParamsString());
			}
		} else {
			/* лексема - callback */
			$iAm = new Tag\Callback($this->getContent());
			$iAm->setTags($this->getTags());
			$iAm->setBody($this->getBody());
		}
		
		$value = $iAm->parse($data);
		
		// Модификаторы
		if(\Spike\Lexema::$callback && substr($iAm->getParamsString(), 0, 1) == '|') {
			$modificators = explode("|", substr($iAm->getParamsString(), 1));
			$value = $this->modifyValue($value, $modificators);
		}
		
		return $value;
	}
	
	/**
	 * Получить значение переменной
	 * @param string $name
	 * @param array $data
	 * @return Ambigous
	 */
	public function getVariableValue($name, $data) {
		
		// переменная может быть указана вместе с модификаторами: goods|count
		if(strpos($name, "|")) {
			$modificators = explode("|", $name);
			$name = array_shift($modificators);
		} else {
			$modificators = array();
		}
		
		$value = null;
		
		if(strpos($name, ".") !== false) {
			$parts = explode(".", $name);
			while($parts) {
				$name = array_shift($parts);
				if(isset($data[$name])) {
					$data = $data[$name];
				} else {
					$data = null;
					break;
				}
			}
			$value = $data;
		} else {
			$value = isset($data[$name]) ? $data[$name] : null;
		}
		
		
		if(\Spike\Lexema::$callback && $modificators) {
			$value = $this->modifyValue($value, $modificators);
		}
		
		return $value;
	}
	/**
	 * Прогнать значение переменной через модификаторы
	 * @param multiple $value
	 * @param array $modificators - список имён модификаторов
	 */
	private function modifyValue($value, $modificators) {
		foreach ($modificators as $modificatorName) {
			$value = call_user_func_array(\Spike\Lexema::$callback, array($modificatorName, array("value" => $value), null));
		}
		return $value;
	}
	
	/**
	 * Получить список переданных параметров
	 * @return array key-value массив
	 */
	public function getParams($data) {
		$params = array();
		
		// Построим работу в два этапа
		// 1. Этап - распарсить все строковые параметры с кавычками
		// 2. Этап - распарсить названия переменных (указаны без кавычек)
		
		$string = $this->getParamsString();
		
		// Парсим строковые параметры с кавычками
		preg_match_all('#([-_\w]+)\s*=\s*"([^"]+)"#ims', $string, $literals);
		if($literals[0]) {
			foreach ($literals[1] as $i => $name) {
				$params[$name] = $literals[2][$i];
				$string = str_replace($literals[0][$i], '', $string);
			}
		}
		
		// Парсим параметры с названиями переменных без кавычек
		preg_match_all('#([-_\w]+)\s*=\s*([-_|\.\w]+)#ims', $this->getParamsString(), $variables);
		if($variables[0]) {
			foreach ($variables[1] as $i => $name) {
				$params[$name] = $this->getVariableValue($variables[2][$i], $data);
			}
		}
		return $params;
	}
}

?>