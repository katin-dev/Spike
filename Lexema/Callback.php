<?php
class Lexema_Callback extends Lexema_Tag {
	
	public function parse($data) {
		if(isset(Lexema::$callback)) {
			$callbackResult =  call_user_func_array(Lexema::$callback, array($this->getName(), $this->getParams($data), $this->getTemplate()));
			
			if(is_array($callbackResult)) {
				
				$tagName = str_replace(".", "_", $this->getName());
				$tag = new Lexema_Loop('{{'.$tagName.' '.$this->getParamsString().'}}');
				$tag->setTags($this->getTags());
				
				return $tag->parse(array(
					$tagName => $callbackResult
				));
			} else {
				return $callbackResult;
			}
		}
	}
	
	/**
	 * Получить переданный в callback шаблон
	 * @return string
	 */
	public function getTemplate() {
		$template = "";
		foreach ($this->getTags() as $tag) {
			$template .= $tag->getContent();
		}
		return $template;
	}
}
