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
	
	public function parse(&$data) {
		
		$tag = $this->mutate($this, $data);
		$value = $tag->parse($data);
		
		// Модификаторы
		if(\Spike\Lexema::$callback && substr($tag->getParamsString(), 0, 1) == '|') {
			$modificators = explode("|", substr($tag->getParamsString(), 1));
			$value = $this->modifyValue($value, $modificators, $data);
		}
		
		return $value;
	}
	
	protected function mutate($tag, $data) {
		if($tag->getName() == 'if') {
			/* условная лексема */
			$iAm = new Tag\Condition($tag->getContent());
			$iAm->setTags($tag->getTags());
		} elseif($tag->getName() == 'set') {
			$iAm = new Tag\Assign($tag->getContent());
			$iAm->setTags($tag->getTags());
		} else {
			$foundVar = false;
			$value = $this->getVariableValue($tag->getName(), $data, $foundVar);
			if ($foundVar) {
				if(is_array($value)) {
					/* лексема - цикл */
					$iAm = new Tag\Loop($tag->getContent());
					$iAm->setTags($tag->getTags());
				} else {
					$iAm = new Tag\Variable($value);
					$iAm->setParamsString($tag->getParamsString());
				}
			} else {
				/* лексема - callback */
				$iAm = new Tag\Callback($tag->getContent());
				$iAm->setTags($tag->getTags());
				$iAm->setBody($tag->getBody());
			}
		}
		
		return $iAm;
	}
	
	/**
	 * Получить значение переменной
	 * @param string $name
	 * @param array $data
	 * @return Ambigous
	 */
	public function getVariableValue($name, $data, &$foundVar) {
		
		if($name == 'true') {
			return true;
		} 
		if($name == 'false') {
			return false;
		}
		
		// переменная может быть указана вместе с модификаторами: goods|count
		if(strpos($name, "|")) {
			$modificators = explode("|", $name);
			$name = array_shift($modificators);
		} else {
			$modificators = array();
		}
		
		$value = null;
		$foundVar = true;	//флаг сигнализирует о том, что переменная была найдена
		
		if(strpos($name, ".") !== false) {
			$parts = explode(".", $name);
			while($parts) {
				$name = array_shift($parts);
				if(isset($data[$name])) {
					$data = $data[$name];
				} else {
					$data = null;
					$foundVar = false;	// не найдена
					break;
				}
			}
			$value = $data;
		} else {
			if(isset($data[$name])) {
				$value = $data[$name];
			} else {
				$value = null;
				$foundVar = false;	// не надена
			}
		}
		
		if(\Spike\Lexema::$callback && $modificators) {
			$value = $this->modifyValue($value, $modificators, $data);
		}
		
		return $value;
	}
	/**
	 * Прогнать значение переменной через модификаторы
	 * @param multiple $value
	 * @param array $modificators - список имён модификаторов
	 */
	private function modifyValue($value, $modificators, $data) {
		foreach ($modificators as $modificatorName) {
			$modificatorName = trim($modificatorName);
			
			$arguments = array(
				"value" => $value
			);
			
			//Вычленение аргументов из модификаторов
			if(preg_match('/^(\w+)\(([^\)]+)\)$/', $modificatorName, $m)) {
				$modificatorName = $m[1];
				foreach (explode(",", $m[2]) as $varNumber => $varName) {
					$varName = trim($varName);
					if(preg_match('/^"([^"]*)"$/', $varName, $literalMatch)) {
						$arguments[$varNumber + 1] = $literalMatch[1];  
					} else {
						$found = false;
						$arguments[$varNumber + 1] = $this->getVariableValue($varName, $data, $found);
					}
					
				}
			}
			
			$value = call_user_func_array(\Spike\Lexema::$callback, array($modificatorName, $arguments, null));
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
				$foundVar = false;
				$params[$name] = $this->getVariableValue($variables[2][$i], $data, $foundVar);
				if($foundVar == false) {
					// Может, это callback ? 
					$tag = new \Spike\Lexema\Tag\Callback('{{' . $variables[2][$i] . '}}');
					$tag->returnRawResult(true);
					$params[$name] = $tag->parse($data);
				}
			}
		}
		return $params;
	}
}

?>