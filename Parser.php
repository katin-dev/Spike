<?php

require_once "Lexema.php";
require_once "Lexema/Tag.php";
require_once "Lexema/Text.php";
require_once "Lexema/Variable.php";
require_once "Lexema/Callback.php";
require_once "Lexema/Condition.php";
require_once "Lexema/Loop.php";
require_once "Lexema/Params.php";

class Parser {

	public function parse($content, $data) {
		
		$position = 0;
		$stack = array();
		
		while(!$this->isEof($content, $position)) {
			$lexema = $this->nextLexemma($content, $position, $data);
			
			if($lexema instanceof Lexema_Tag && $lexema->isCloseTag()) {
				/* ищем открывающий тег */
				
				$innerTags = array();
				while(($l = array_pop($stack)) != null) {
					if($l instanceof Lexema_Tag && $l->getName() == $lexema->getOpenTagName()) {
						/* нашли открывающий тег */
						$l->setTags(array_reverse($innerTags));
						array_push($stack, $l);
						break;
					}
					
					$innerTags[] = $l;
				}
				
			} else {
				array_push($stack, $lexema);
			}
		}
		
		$html = "";
		foreach ($stack as $lexema) {
			$html .=  $lexema->parse($data);
		}
		
		return $html;
	}
	
	protected function nextLexemma($content, &$position, &$data) {
		
		$lex = null;
		if(substr($content, $position, 2) == "{{") {
			// Начало лексемы
			$pos = strpos($content, "}}", $position);
			$length = $pos - $position + 2;
			$text = substr($content, $position, $length);
			$lex = new Lexema_Tag(substr($content, $position, $length));
			$position = $pos + 2;
		} else {
			
			// Какой-то произвольный HTML код. Просто запоминаем его как текст.
			$pos = strpos($content, "{{", $position);
			
			if($pos !== false) {
				$length = $pos - $position;
				$text = substr($content, $position, $length);
			} else {
				$text = substr($content, $position);
				// смещаем позицию в конец шаблона. Разбор закончен.
				$pos = strlen($content);
			}
			
			$lex = new Lexema_Text($text);
			$position = $pos;
		}
		
		return $lex;
	}
	
	protected function isEof($content, $position) {
		return $position == strlen($content);
	}
	
	public function setCallback($callback) {
		Lexema::$callback = $callback;
	}
	public function getCallback() {
		return Lexema::$callback;
	}
	
}

?>