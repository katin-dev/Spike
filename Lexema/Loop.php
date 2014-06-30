<?php
class Lexema_Loop extends Lexema_Tag {
	public function parse($data) {
		
		$html = "";
		$loopVar = $this->getVariableValue($this->getName(), $data);
		if($loopVar && is_array($loopVar)) {
			
			$arrayLength = count($loopVar);
			$position = 0;
			$params = $this->getParams($data);
			
			foreach ($loopVar as $item) {
				
				//$itemData = $data;
				
				/* хотим иметь доступ к переменным уровня выше */
				/* foreach ($data as $key => $value) {
					if($key != $this->getName()) {
						$itemData[$key] = $value;
					}
				} */
				
				/* нам нужны вспомогательные переменные */
				/* $itemData['_is_first_'] = (string) ($position == 0);
				$itemData['_is_last_'] = (string) ($position == $arrayLength - 1);
				$itemData['_pos_'] = $position; */

				//$itemData = array_merge($itemData, $item);
				
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