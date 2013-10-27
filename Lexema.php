<?php
abstract class Lexema {

	private $content;
	
	public function __construct($content) {
		$this->content = $content;
	}
	
	abstract public function getName();
	
	public function getContent() {
		return $this->content;
	}
	
	public function parse($data) {
		return $this->getContent();
	}
}

?>