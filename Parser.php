<?php

require_once "Lexema.php";
require_once "Lexema/Tag.php";
require_once "Lexema/Text.php";
require_once "Lexema/Variable.php";
require_once "Lexema/Callback.php";
require_once "Lexema/Condition.php";
require_once "Lexema/Loop.php";
require_once "Lexema/Params.php";
require_once "DataStack.php";

class Parser {
	
	/**
	 * Данные складываются в стек. Это позволяет вложенным шаблонам получать доступ к переменным более верхнего уровня.
	 * @var DataStack
	 */
	private $dataStack;
	
	public function parse($content, $data) {
		
		$this->getDataStack()->pushData($data);
		
		$position = 0;
		$stack = array();
		
		while(!$this->isEof($content, $position)) {
			$lexema = $this->nextLexemma($content, $position);
			
			if($lexema instanceof Lexema_Tag && $lexema->isCloseTag()) {
				/* ищем открывающий тег */
				
				$innerTags = array();
				while(($l = array_pop($stack)) != null) {
					if($l instanceof Lexema_Tag && $l->getName() == $lexema->getOpenTagName()) {
						// нашли открывающий тег
						$l->setTags(array_reverse($innerTags));
						$l->setBody(substr($content, $l->getPosition(), $position - $l->getPosition() - strlen($lexema->getContent())));
						array_push($stack, $l);
						break;
					}
					
					$innerTags[] = $l;
				}
				
				// @TODO Проверить, найден ли открывающий тег. Если нет - бросить исключение. 
				
			} else {
				array_push($stack, $lexema);
			}
		}
		
		$html = "";
		foreach ($stack as $lexema) {
			$html .=  $lexema->parse($this->getDataStack()->getData());
		}
		
		$this->getDataStack()->popData();
		
		return $html;
	}
	
	protected function nextLexemma($content, &$position) {
		
		$lex = null;
		if(substr($content, $position, 2) == "{{") {
			// Начало лексемы
			$pos = strpos($content, "}}", $position);
			$length = $pos - $position + 2;
			$text = substr($content, $position, $length);
			$lex = new Lexema_Tag(substr($content, $position, $length));
			$position = $pos + 2;
			$lex->setPosition($position);
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
	/**
	 * @return DataStack
	 */
	public function getDataStack() {
		if(empty($this->dataStack)) {
			$this->dataStack = new DataStack();
		}
		return $this->dataStack;
	}
	
}

?>