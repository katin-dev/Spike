<?php
require_once 'Parser.php';

/*
 * Задача: написать шаблонизатор на PHP.
 * Основные возможности: 
 * 1. Вставка значений переменных по их названию. Пример: {{name}} => "Sergey"
 * 2. Поддержка условного оператора if - else. 
 * 3. Применение шаблона для каждого элемента из массива, если указанная переменная - это массив. 
 *    Пример: {{users}}<div class="user">{{name}}</div>{{/users}}, если users - массив, то надо напечатать 
 *    <div class="user">Имя пользователя</div> для каждого пользователя из {{users}}
 *    В этом блоке должны быть доступны переменные _is_first_ (элемент является первым в списке), _is_last_ (элемент является последним в списке), _pos_ (позиция текущего элемента), которые означают 
 *    
 *
 *    Пример шаблона: 
<div id="Users">
	<h1>Список клиентов</h1>
	{{users}}
	<h2>{{name}}</h2>
	{{if age < max_age}}<div class="warning">Опасно</div>{{else}}Подойдет{{/if}}
	{{if goods}}
		<ul class="goods">
		{{goods}}
		<li {{if _is_first_}}class="first"{{/if}}>
			{{_pos_}}{{name}} - {{price}} руб.
		</li>
		{{/goods}}
		</ul>
	{{/if}}
	{{/users}}
</div>

Надо реализовать класс Parser так, чтоб ParserTest проходил все тесты. 
*/

/**
 * Parser test case.
 */
class ParserTest extends PHPUnit_Framework_TestCase {
	
	/**
	 *
	 * @var Parser
	 */
	private $Parser;
	
	/**
	 * Prepares the environment before running a test.
	 */
	protected function setUp() {
		parent::setUp ();
		$this->Parser = new Parser();
	}
	
	/**
	 * Cleans up the environment after running a test.
	 */
	protected function tearDown() {
		$this->Parser = null;
		parent::tearDown ();
	}
	
	/**
	 * Constructs the test case.
	 */
	public function __construct() {
	}
	
	/**
	 * Текст без тегов возвращается как есть. 
	 */
	public function testParseText() {
		$content = "Hello, World!";
		$result = $this->Parser->parse($content, array());
		
		$this->assertEquals($content, $result);
	}
	
	/**
	 * Тег переменной заменятся на значение этой переменной.
	 */
	public function testParseVar() {
		$content = "Hello, {{name}}!";
		$result = $this->Parser->parse($content, array("name" => "Vasya"));
		
		$this->assertEquals("Hello, Vasya!", $result);
	}
	
	/**
	 * Условная конструкция if else
	 */
	public function testParseIfBasic() {
		$content = "You are {{if win}}win{{else}}lose{{/if}}!";
		$result = $this->Parser->parse($content, array("win" => true));
		$this->assertEquals("You are win!", $result);

		$result = $this->Parser->parse($content, array("win" => false));
		$this->assertEquals("You are lose!", $result);
	}
	
	/**
	 * Передача переменной в условную конструкцию if else
	 */
	public function testParseVariableInIf() {
		$content = "Goods: {{if count > 0}}{{count}}{{else}}not set{{/if}}";
		$result = $this->Parser->parse($content, array("count" => 10));
		$this->assertEquals("Goods: 10", $result);
	
		$result = $this->Parser->parse($content, array("count" => 0));
		$this->assertEquals("Goods: not set", $result);
	}
	
	/**
	 * Применение шаблона ко всем элементам списка, если переменная - это массив
	 * В шаблоне указаны названия ключей элемента списка, которые должны замениться на значения. 
	 */
	public function testLoop() {
		$content = "Goods:{{goods}}{{name}} - {{price}}\n{{/goods}}";
		
		$result = $this->Parser->parse($content, array("goods" => array(
			array(
				"name" => "Pineapple", 
				"price" => "2.5"
			), 
			array(
					"name" => "Melone",
					"price" => "1"
			),
			array(
					"name" => "Kiwi",
					"price" => "1.7"
			)
		)));
		$this->assertEquals("Goods:Pineapple - 2.5\nMelone - 1\nKiwi - 1.7\n", $result);
	}
	
	/**
	 * Внутри цикла должны работать условные конструкции
	 */
	public function testIfInLoop() {
		$content = "Goods:{{goods}}{{if price > 1}}{{name}} - {{price}}\n{{/if}}{{/goods}}";
		
		$result = $this->Parser->parse($content, array("goods" => array(
				array(
						"name" => "Pineapple",
						"price" => "2.5"
				),
				array(
						"name" => "Melone",
						"price" => "1"
				),
				array(
						"name" => "Kiwi",
						"price" => "1.7"
				)
		)));
		$this->assertEquals("Goods:Pineapple - 2.5\nKiwi - 1.7\n", $result);
	}
	
	/**
	 * Циклы могут быть вложенными
	 */
	public function testNestedLoop() {
		$template = "Users:{{users}}<h2>{{name}}</h2>{{if goods}}<ul>{{goods}}<li>{{title}}:{{count}}</li>{{/goods}}</ul>{{/if}}{{/users}}";
		$users = array(
			array(
				"name" => "Vasya", 
				"goods" => array(
					array(
						"title" => "Pineapple", 
						"count" => 3
					), 
					array(
						"title" => "Melone",
						"count" => 1
					)
				)
			), 
			array(
				"name" => "Olga"
			)
		);
		
		$content = $this->Parser->parse($template, array("users" => $users));
		
		$this->assertEquals("Users:<h2>Vasya</h2><ul><li>Pineapple:3</li><li>Melone:1</li></ul><h2>Olga</h2>", $content);
	}
}

