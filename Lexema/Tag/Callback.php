<?php
namespace Spike\Lexema\Tag;

class Callback extends \Spike\Lexema\Tag {
	
	private $rawResult = false;
	
	public function returnRawResult($flag) {
		$this->rawResult = $flag;
	}
	
	public function parse(&$data) {
		if(isset(\Spike\Lexema::$callback)) {
			
			\Spike\Timer::pause();
			$callbackResult =  call_user_func_array(\Spike\Lexema::$callback, array($this->getName(), $this->getParams($data), $this->getBody()));
			\Spike\Timer::resume();
			
			if(is_array($callbackResult) && $this->rawResult == false) {
				
				$tagName = str_replace(".", "_", $this->getName());
				$tag = new Loop('{{'.$tagName.' '.$this->getParamsString().'}}');
				$tag->setTags($this->getTags());
				
				$loopData = $data;
				$loopData[$tagName] = $callbackResult;
				
				return $tag->parse($loopData);
			} else {
				return $callbackResult;
			}
		}
	}
}
