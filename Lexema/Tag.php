<?php
class Lexema_Tag extends Lexema {
	
	private $tags;
	private $name;
	private $paramsString;	
	
	public function __construct($content) {
		parent::__construct($content);
		if(preg_match('/^{{\s*([\/\w]+)(.*?)}}$/ims', $content, $m)) {
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
		} elseif (isset($data[$this->getName()])) {
			if(is_array($data[$this->getName()])) {
				/* лексема - цикл */
				$iAm = new Lexema_Loop($this->getContent());
				$iAm->setTags($this->getTags());
			} else {
				/* лексема - переменная */
				return $data[$this->getName()];
			}
		} else {
			/* лексема - callback */
			$iAm = new Lexema_Callback($this->getContent());
			$iAm->setTags($this->getTags());
		}
		
		return $iAm->parse($data);
	}
}

?>