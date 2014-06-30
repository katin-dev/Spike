Spike
======

Spike - это лучший некомпилируемый шаблонизатор для PHP

За основу синтаксиса был взят шаблонизатор https://github.com/pyrocms/lex , более ничего общего с Lex наш шаблонизатор не имеет

## Переменные
```
{{ name }}
{{ user.name }}
{{ category.description|escape }} - применение модификатора к переменной
```

## Условные операторы:
```
{{ if user.age > 25 }}
  Взрослый мужик
{{ else }}
  {{ if user.age > 18 }}
    Парнишка
  {{ else }}
    Ребёнок
  {{ /if }}
{{ /if }}
```

## Циклы
```php 
$parser->parse($template, [
 'goods' => [
    ['name' => 'Pineapple'],
    ['name' => 'Melone'],
    ['name' => 'Kiwi'],
 ]
]);
```

Простое использование (содержимое переменной $template): 
```
{{ goods }}
<li>{{name}}</li>
{{ /goods }}
```

Циклы с параметрами <b>item</b> и <b>key</b>
```
{{ user.goods item="good" key="key" }} 
<b>{{key}} : {{good.name}}</b>
{{/user.goods}}
```

Использование вспомогательных переменных <b>_is_first_</b> <b>_is_last_</b> <b>_pos_</b>
```
{{ user.goods item="good" }} 
  <div {{if good._is_first_}}class="first"{{/if}}>
    <span class="number">{{good._pos_}}</span>
    <strong>{{good.name}}</strong>
  </div>
  {{ if good._is_last_ == 0 }}
  <div class="separator"></div>
  {{/if}}
{{/user.goods}}
```



## Callback
Для начала, следует установить callback: 
```php
$parser->setCallback(function ($name, $options, $template) {
    // обработка callback тегов
});
```
После этого в шаблонах можно писать
```
{{ module.news.getList ids="{ids}" order="date" }}
Здесь можно передать шаблон в callback (параметр $content) 
{{ /module.news.getList }}
```
Обратите внимание: 
* переменные в параметрах записываются через одинарную фигурную скобку `{ids}`
* в параметрах можно указывать модификаторы:
```
{{ spellcount value="{goods|count}" words="товар,товара,товаров" }}
```

