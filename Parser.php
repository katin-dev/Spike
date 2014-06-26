<?php

require_once "Lexema.php";
require_once "Lexema/Tag.php";
require_once "Lexema/Text.php";
require_once "Lexema/Callback.php";
require_once "Lexema/Condition.php";
require_once "Lexema/Loop.php";

class Parser {

	private $position = 0;
	private $content = "";
	private $data;
	private $stack = array();
	private $callback;
	
	public function parse($content, $data) {
		
		$this->content = $content;
		$this->data = $data;
		
		while(!$this->isEof()) {
			$lexema = $this->nextLexemma();
			
			if($lexema instanceof Lexema_Tag && $lexema->isCloseTag()) {
				/* ищем открывающий тег */
				
				$innerTags = array();
				while(($l = array_pop($this->stack)) != null) {
					if($l instanceof Lexema_Tag && $l->getName() == $lexema->getOpenTagName()) {
						/* нашли открывающий тег */
						$l->setTags(array_reverse($innerTags));
						array_push($this->stack, $l);
						break;
					}
					
					$innerTags[] = $l;
				}
				
			} else {
				array_push($this->stack, $lexema);
			}
		}
		
		$html = "";
		foreach ($this->stack as $lexema) {
			$html .=  $lexema->parse($data);
		}
		
		return $html;
	}
	
	protected function nextLexemma() {
		
		$lex = null;
		/* начало лексемы */
		if(substr($this->content, $this->position, 2) == "{{") {
			$pos = strpos($this->content, "}}", $this->position);
			$length = $pos - $this->position + 2;
			$lex = new Lexema_Tag(substr($this->content, $this->position, $length));
			$this->position = $pos + 2;
		} else {
			/* произвольный HTML */
			$pos = strpos($this->content, "{{", $this->position);
			
			if($pos !== false) {
				$length = $pos - $this->position;
				$lex = new Lexema_Text(substr($this->content, $this->position, $length));
				$this->position = $pos;
			} else {
				$pos = strlen($this->content);
				$lex = new Lexema_Text(substr($this->content, $this->position));
				$this->position = $pos;
			}
		}
		
		return $lex;
		
	}
	
	protected function isEof() {
		return $this->position == strlen($this->content);
	}
	
	public function setCallback($callback) {
		Lexema_Callback::$callback = $callback;
	}
	public function getCallback() {
		return Lexema_Callback::$callback;
	}
	
}

?>