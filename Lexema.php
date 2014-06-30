<?php
namespace Spike;
abstract class Lexema {

	public static $callback;	//Наверное, не лучшее решение
	/**
	 * Текстовое содержание открывающего тега лексемы: {{ var|modificator param1="{data.name}" }}
	 * @var string
	 */
	private $content;
	/**
	 * Текстовое содержание содержимого между открывающим и закрывающим тегом
	 * @var string
	 */
	private $body;
	
	/**
	 * Позиция текущей лексемы в шаблоне
	 * @var integer
	 */
	private $position;
	
	public function __construct($content) {
		$this->content = $content;
	}
	
	abstract public function getName();
	
	public function setPosition($pos) {
		$this->position = $pos;
	}
	public function getPosition() {
		return $this->position;
	}
	
	public function getContent() {
		return $this->content;
	}
	public function getBody() {
		return $this->body;
	}
	public function setBody($string) {
		$this->body = $string;
	}
	
	public function parse($data) {
		return $this->getContent();
	}
}

?>