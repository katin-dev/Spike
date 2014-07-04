<?php
namespace Spike\Lexema\Tag;

class Condition extends \Spike\Lexema\Tag {
	
	public function isTrue($condition, $data) {
		
		preg_match_all('/\b(?<![\'"])([\.\w]+)\b/sm', $condition, $m);
		
		$_vars = array();
		
		foreach ($m[1] as $key => $name) {
			if(!is_numeric($name) && !in_array($name, array('&&', '||'))) {
				$_vars[$name] =  $this->getVariableValue($name, $data);
				$condition = str_replace($name, '$_vars[\''.$name.'\']', $condition);
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