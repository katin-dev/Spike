parser
======

Шаблонизатор для PHP

За основу синтаксиса был взят шаблонизатор https://github.com/pyrocms/lex , более ничего общего с Lex наш шаблонизатор не имеет

<h3>Переменные</h3>
<code>
{{ name }}<br />
{{ user.name }}
</code>

<h3>Условные операторы: </h3>
<code>{{ if user.age > 25 }}Взрослый парень{{/if}}</code>

<h3>Циклы</h3>
<pre>
{{ user.goods item="good" key="key" }} 
<b>{key}} : {{good.name}}</b>
{{/user.goods}}
</pre>
<h3>Callback</h3>
Для начала, следует установить callback: 
<pre>
$parser->setCallback(function ($name, $options, $template) {
    // обработка callback тегов
});
</pre>
После этого в шаблонах можно писать
<pre>
{{ module.news.getList ids="{ids}" order="date" }}
Здесь можно передать шаблон в callback (параметр $content) 
{{ /module.news.getList }}
</pre>
<h3></h3>


