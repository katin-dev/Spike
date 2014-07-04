<?php
namespace Spike\Lexema\Tag;

class Loop extends \Spike\Lexema\Tag {
	
	public function parse(&$data) {
		
		$html = "";
		$loopVar = $this->getVariableValue($this->getName(), $data);
		if($loopVar && is_array($loopVar)) {
			
			$arrayLength = count($loopVar);
			$position = 0;
			$params = $this->getParams($data);
			
			foreach ($loopVar as $item) {
				
				$item['_is_first_'] = (string) ($position == 0);
				$item['_is_last_'] = (string) ($position == $arrayLength - 1);
				$item['_pos_'] = $position;

				if(isset($params['item'])) {
					$itemData = array_merge($data, array($params['item'] => $item));
				} else {
					$itemData = array_merge($data, $item);
				}
				
				if(isset($params['key'])) {
					$itemData[$params['key']] = $position;
				}
				
				foreach ($this->getTags() as $tag) {
					$html .= $tag->parse($itemData);
				}
				
				$position ++;
			}
		}
		
		return $html;
	}
	
	private function itemData($data, $key) {
		unset($itemData[$this->getName()]);
	}
}

?>