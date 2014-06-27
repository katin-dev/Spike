<?php

require_once "Lexema.php";
require_once "Lexema/Tag.php";
require_once "Lexema/Text.php";
require_once "Lexema/Variable.php";
require_once "Lexema/Callback.php";
require_once "Lexema/Condition.php";
require_once "Lexema/Loop.php";

class Parser {
	
	/**
	 * Распарсить переданный шаблон на основе переданных данных
	 * ! К сожалению, из-за того, что в нашей CMS используется один экземпляр объекта Parser, нельзя использовать переменные класса для управления потоком. Все переменные ($data, $content, $position) должны быть локальными переменными метода.
	 * @param string $content
	 * @param array $data
	 * @return string
	 */
	public function parse($content, $data) {
		
		$position = 0;
		$stack = array();
		
		while(!$this->isEof($content, $position)) {
			$lexema = $this->nextLexemma($content, $position);
			
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
	
	protected function nextLexemma($content, &$position) {
		
		$lex = null;
		/* начало лексемы */
		if(substr($content, $position, 2) == "{{") {
			$pos = strpos($content, "}}", $position);
			$length = $pos - $position + 2;
			$lex = new Lexema_Tag(substr($content, $position, $length));
			$position = $pos + 2;
		} else {
			/* произвольный HTML */
			$pos = strpos($content, "{{", $position);
			
			if($pos !== false) {
				$length = $pos - $position;
				$lex = new Lexema_Text(substr($content, $position, $length));
				$position = $pos;
			} else {
				$pos = strlen($content);
				$lex = new Lexema_Text(substr($content, $position));
				$position = $pos;
			}
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