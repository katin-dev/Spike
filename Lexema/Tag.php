<?php
class Lexema_Tag extends Lexema {
	
	private $tags;
	private $name;
	private $paramsString;	
	
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
	
	public function parse($data) {
		if($this->getName() == 'if') {
			/* условная лексема */
			$iAm = new Lexema_Condition($this->getContent());
			$iAm->setTags($this->getTags());
		} elseif (($value = $this->getVariableValue($this->getName(), $data)) !== null) {	//@TODO потенциальная ошибка: $data[$name] = null Переменная существует и равна null
			if(is_array($value)) {
				/* лексема - цикл */
				$iAm = new Lexema_Loop($this->getContent());
				$iAm->setTags($this->getTags());
			} else {
				/* лексема - переменная */
				return $value;
			}
		} else {
			/* лексема - callback */
			$iAm = new Lexema_Callback($this->getContent());
			$iAm->setTags($this->getTags());
		}
		
		return $iAm->parse($data);
	}
	
	/**
	 * Получить значение переменной
	 * @param string $name
	 * @param array $data
	 * @return Ambigous
	 */
	public function getVariableValue($name, $data) {
		if(strpos($name, ".") !== false) {
			$parts = explode(".", $name);
			while($parts) {
				$name = array_shift($parts);
				if(isset($data[$name])) {
					$data = $data[$name];
				} else {
					return null;
				}
			}
			return $data;
		} else {
			return isset($data[$name]) ? $data[$name] : null;
		}
	}
}

?>