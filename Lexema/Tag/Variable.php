<?php
namespace Spike\Lexema\Tag;

class Variable extends \Spike\Lexema\Tag {
	public function parse(&$data) {
		return $this->getContent();
	}
}
?>