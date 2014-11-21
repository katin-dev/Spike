<?php
namespace Spike\Lexema\Tag;

class Condition extends \Spike\Lexema\Tag {
	
	public function isTrue($condition, $data) {
		
		preg_match_all('/\b(?<![\'"])([\.\w]+)\b/sm', $condition, $m, PREG_OFFSET_CAPTURE);
		
		$_vars = array();
		$mLength = count($m[1]);

		for($key = 0; $key < $mLength; $key ++) {
			list($name, $offset) = $m[1][$key];
			if(!is_numeric($name) && !in_array($name, array('&&', '||', '==', '!='))) {	// по-моему, в массиве нет необходимости
				$foundVar = false;
				$_vars[$name] =  $this->getVariableValue($name, $data, $foundVar);
				$varString = '$_vars[\''.$name.'\']';

				$length = strlen($condition);
				$condition = substr_replace($condition, $varString, $offset, strlen($name));
				$diff = strlen($condition) - $length;

				// распространяем смещение на оставшиеся блоки
				for($k = $key + 1; $k < $mLength; $k++) {
					$m[1][$k][1] += $diff;
				}
			}
		}
		
		$phpCode = 'return ' . $condition . ';';
		
		return eval($phpCode);
	}

	public function parse(&$data) {
		
		$conditionResult = $this->isTrue($this->getParamsString(), $data);
		
		$html = "";
		
		/* разберем все лексемы на те, которые должны выполниться в случае успеха и в случае неудачи */
		$conditionTags = array(
			'true' => array(), 
			'false' => array()
		);
		
		$tagsName = "true";
		
		foreach ($this->getTags() as $tag) {
			if($tag->getName() == 'else') {
				$tagsName = 'false';
			}
			$conditionTags[$tagsName][] = $tag;
		}
		
		/* выбираем лексемы согласно выполненному условию оператора if */
		if($conditionResult) {
			$tags = $conditionTags['true'];
		} else {
			$tags = $conditionTags['false'];
		}
		
		foreach ($tags as $tag) {
			$html .= $tag->parse($data);
		}
		
		return $html;
	}
}

?>