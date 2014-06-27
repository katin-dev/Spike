parser
======

Шаблонизатор для PHP

За основу синтаксиса был взят шаблонизатор https://github.com/pyrocms/lex , более ничего общего с Lex наш шаблонизатор не имеет

## Переменные
```
{{ name }}
{{ user.name }}
```

## Условные операторы:
```
{{ if user.age > 25 }}Взрослый парень{{ /if }}
```

## Циклы
```
{{ user.goods item="good" key="key" }} 
<b>{key}} : {{good.name}}</b>
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


