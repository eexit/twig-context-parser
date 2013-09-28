# Twig Context Parser [![Build Status](https://travis-ci.org/eexit/twig-context-parser.png?branch=master)](https://travis-ci.org/eexit/twig-context-parser)

This small script uses the internal Twig tokenize/parser/compiler to obtain a template variable out of its rendering context.

If you are building a flat file Twig CMS based, it might be very useful to work using parsed flat file variable. There are many uses.

This parser basically return all your variables set in Twig using the block `set` as complex as it might be:

```
{% set foo = [
    {"bar":"baz"},
    "bar",
    range(0, 12, 2),
    ["yux", {
        "baz":"yea",
        "bar":"foo",
        "range":range(0, 100)
    }]
] %}
```

Will output:

```
array(1) {
  'foo' =>
  array(4) {
    [0] =>
    array(1) {
      'bar' =>
      string(3) "baz"
    }
    [1] =>
    string(3) "bar"
    [2] =>
    array(7) {
      [0] =>
      int(0)
      [1] =>
      int(2)
      ...
      [6] =>
      int(12)
    }
    [3] =>
    array(2) {
      [0] =>
      string(3) "yux"
      [1] =>
      array(3) {
        'baz' =>
        string(3) "yea"
        'bar' =>
        string(3) "foo"
        'range' =>
        array(101) {
          [0] =>
          int(0)
          [1] =>
          int(1)
          ...
          [99] =>
          int(99)
          [100] =>
          int(100)
        }
      }
    }
  }
}
```


## Installation

Supposing you are using [composer](http://getcomposer.org), add to your `composer.json`:

```json
{
    "require": {
        "eexit/twig-context-parser": "0.1.*"
    }
}
```

## Usage

```php
use Eexit\Twig\ContextParser\ContextParser;

$loader = new \Twig_Loader_String();
$twig = new \Twig_Environment($loader);

$template = $twig->parse($twig->tokenize("{% set foo = "bar" %}{% set baz = "yux" %}"));

$context = new ContextParser($twig);

var_dump($context->parse($template)->getContext());

/*
array(2) {
    'foo' =>
    string(3) "bar"
    'baz' =>
    string(3) "yux"
}
*/
```

The `ContextParser::getParser()` will return the node context once. Once you called the method, calling it again will return nothing.
This allows to use the same `ContextParser` instance to parse several template in a row.
