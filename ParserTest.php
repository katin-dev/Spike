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
	 * Проверяем запись вида {{user.name}}
	 */
	public function testInsertArrayItem() {
		$content = "Hello, {{user.name}}!";
		$result = $this->Parser->parse($content, array("user" => array("name" => "Vasya")));
		
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
	 * Проверка всех false условий
	 *  
	 */
	public function testFalseIf() {
		$content = "I like {{if flag}}PHP{{else}}C++{{/if}}";
		
		$result = $this->Parser->parse($content, array("flag" => false));
		$this->assertEquals("I like C++", $result);
		
		$result = $this->Parser->parse($content, array("flag" => null));
		$this->assertEquals("I like C++", $result);
		
		$result = $this->Parser->parse($content, array("flag" => array()));
		$this->assertEquals("I like C++", $result);
		
		$result = $this->Parser->parse($content, array("flag" => ""));
		$this->assertEquals("I like C++", $result);
	}
	/**
	 * Проверка всех true условий
	 */
	public function testTrueIf() {
		$content = "I like {{if flag}}PHP{{else}}C++{{/if}}";
		
		$result = $this->Parser->parse($content, array("flag" => true));
		$this->assertEquals("I like PHP", $result);
		
		$result = $this->Parser->parse($content, array("flag" => 1));
		$this->assertEquals("I like PHP", $result);
		
		$result = $this->Parser->parse($content, array("flag" => array("1")));
		$this->assertEquals("I like PHP", $result);
		
		$result = $this->Parser->parse($content, array("flag" => "1"));
		$this->assertEquals("I like PHP", $result);
	}
	
	/**
	 * Проверка значения элемента массива: {{ if user.is_active}}
	 */
	public function testIfArrayContain() {
		$content = "I like {{if i.php}}PHP{{else}}C++{{/if}}";
		$result = $this->Parser->parse($content, array("i" => array("php" => true)));
		$this->assertEquals("I like PHP", $result);
		
		$result = $this->Parser->parse($content, array("i" => array("php" => false)));
		$this->assertEquals("I like C++", $result, "False if i.php = FALSE");
		
		$result = $this->Parser->parse($content, array("i" => array("c++" => true)));
		$this->assertEquals("I like C++", $result, "not isset() => false");
	}
	
	/**
	 * Сравнение значения элемента массива с константой
	 */
	public function testIfArrayItemAgainstConstant() {
		$content = "I am {{if i.age > 25}}professional{{else}}junior{{/if}}";
		
		$result = $this->Parser->parse($content, array("i" => array("age" => 18)));
		$this->assertEquals("I am junior", $result);
		
		$result = $this->Parser->parse($content, array("i" => array("age" => 26)));
		$this->assertEquals("I am professional", $result);
	}
	/**
	 * Сравнение значения элемента массива с другой переменной (другим элементом массива)
	 */
	public function testIfArrayItemAgainstVariables() {
		$content = "{{if user.limit > user.requests}}ALLOW{{else}}DENY{{/if}}";
		$data = array(
			"user" => array(
				"limit" => 10, 
				"requests" => 5
			)
		);
	
		$result = $this->Parser->parse($content, $data);
		$this->assertEquals("ALLOW", $result);
	
		$data['user']['requests'] = 10;
		$result = $this->Parser->parse($content, $data);
		$this->assertEquals("DENY", $result);
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
	
	public function testLoopBy2ndLevelArray() {
		$content = "Goods:{{client.basket}}{{name}}<br />{{/client.basket}}";
		
		$result = $this->Parser->parse($content, array("client" => array("basket" => array(
			array("name" => "Pineapple"),
			array("name" => "Melone"),
			array("name" => "Kiwi")
		))));
		$this->assertEquals("Goods:Pineapple<br />Melone<br />Kiwi<br />", $result);
	}
	
	/**
	 * Проверить запись вида {{ goods item="good" key="key" }}
	 */
	public function testLoopByItem() {
		$content = $this->Parser->parse('{{ goods item="good" key="key" }}<b>{{key}}:{{good.name}}</b>{{/goods}}', array( 
			"goods" => array(
				array("name" => "Cherry"), 
				array("name" => "Apple"),
				array("name" => "Banana")
			)
		));
		
		$this->assertEquals('<b>0:Cherry</b><b>1:Apple</b><b>2:Banana</b>', $content);
	}
	
	/**
	 * Цикл в цикле с доступом к элементу из родительского цикла.
	 */
	public function testLoopNested() {
		$content = $this->Parser->parse('{{ users item="user"}}<b>{{user.name}}</b><ul>{{user.goods}}<li>{{name}}({{user.id}})</li>{{/user.goods}}</ul>{{/users}}', array(
			"users" => array(
				array(
					"id" => 5, 
					"name" => "Sergey", 
					"goods" => array(
						array("name" => "Cherry"),
						array("name" => "Apple"),
						array("name" => "Banana")
					) 
				)
			)
		));
		
		$this->assertEquals('<b>Sergey</b><ul><li>Cherry(5)</li><li>Apple(5)</li><li>Banana(5)</li></ul>', $content);
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
	
	/**
	 * Тестируем простой вызов callback. 
	 * Надо проверить, что callback вызывается и тег заменяется на результат его выполнения. 
	 */
	public function testCallbackSimple() {
		$template = "{{module.news}}<div>{{text}}</div>{{/module.news}}";
		$this->Parser->setCallback(function ($name, $options, $content) {
			return $name;
		});
		$content = $this->Parser->parse($template, array());
		
		$this->assertEquals("module.news", $content);
	}

	/**
	 * В callback должны передаваться параметры и шаблон (текст между открывающим и закрывающим тегом)
	 * @return mixed
	 */
	public function testCallbackWithParams() {
		$template = "{{module.news order=\"date DESC\" ids=\"1,2,3,4,5\" }}<b>{{order}}</b><b>{{ids}}</b>{{/module.news}}";
		$this->Parser->setCallback(function ($name, $options, $content) {
			return str_replace(array("{{order}}", "{{ids}}"), array($options['order'], $options['ids']), $content);
		});
		
		$content = $this->Parser->parse($template, array());
			
		$this->assertEquals("<b>date DESC</b><b>1,2,3,4,5</b>", $content);
	}
	/**
	 * Безопасная передача переменных через параметры вызова callback.
	 * Под безопасностью понимается не вставка значения переменной в текст, а передача значения переменной напрямую в параметры callback
	 * Пример: {{ module.news ids="{ids}" }}
	 * @return number
	 */
	public function testCallbackWithVarInParams() {
		$template = '{{module.news ids="{ids}" }}{{/module.news}}';
		$this->Parser->setCallback(function ($name, $options, $content) {
			return count($options['ids']);
		});
		
		//ids - это массив! Лекс бы вставил {{module.news ids="Array" }} а потом бы вызвал callback 
		$ids = array_fill(0, 10000, 1);
		//Передаём в качестве параметра callback'у массив (объект или ещё что-то не примитивное)
		$content = $this->Parser->parse($template, array("ids" => $ids));
		$this->assertEquals(10000, $content);
	}
	
	/**
	 * Если callback вернул массив а не строку, то мутируем в Loop
	 */
	public function testCallback2Loop() {
		$template = '{{module.users.getList}}<b>{{name}}</b>{{/module.users.getList}}';
		$this->Parser->setCallback(function ($name, $options, $content) {
			return array(
				array("name" => "Sergey"),
				array("name" => "Ivan"),
				array("name" => "Kirill"),
			);
		});
		$content = $this->Parser->parse($template, array());
		
		$this->assertEquals('<b>Sergey</b><b>Ivan</b><b>Kirill</b>', $content);
	}
	
	/**
	 * Модификаторы для переменных. Прим: {{ description|escape }} 
	 */
	public function testModificator() {
		$template = '{{name|escape}}';
		$this->Parser->setCallback(function ($name, $options, $content) {
			return $name.':'.$options['value'].':'.empty($content);
		});
		$content = $this->Parser->parse($template, array("name" => "Sergey"));
		$this->assertEquals('escape:Sergey:1', $content);
	}
	
	/**
	 * Применение модификатора к параметрам callback
	 * {{ spellcount value="{ids|count}" }}
	 */
	public function testModificatorForParams() {
		$template = '{{ spellcount value="{ids|count}" }}';
		$this->Parser->setCallback(function ($name, $options, $content) {
			switch ($name) {
				case 'count':
					return count($options['value']);
				case "spellcount": 
					return $options['value'];
			}
		});
		
		$count = $this->Parser->parse($template, array(
			"ids" => array(1, 2, 3, 4, 5)
		));
		
		$this->assertEquals(5, $count);
	}
	
	
}

