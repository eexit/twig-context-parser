<?php

namespace Eexit\Twig\ContextParser;

/**
 * ContextParserTest.php
 *
 * @author Joris Berthelot <joris@berthelot.tel>
 * @copyright Copyright (c) 2013, Joris Berthelot
 * @licence http://www.opensource.org/licenses/mit-license.php MIT License
 */
class ContextParserTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {   
        $loader = new \Twig_Loader_String();
        $this->twig = new \Twig_Environment($loader, array(
            'optimizations'     => 0,
            'autoescape'      => false
        ));
    }

    public function testGetContextReturnsNullWhenNotYetParsed()
    {
        $contextParser = new ContextParser($this->twig);
        $this->assertNull($contextParser->getContext());
    }

    public function testGetContextResetsContextOnceAfterReturned()
    {
        $twig = $this->twig;
        $contextParser = new ContextParser($this->twig);

        $node1 = $twig->parse($twig->tokenize('{% set foo = "bar" %}'));
        $node2 = $twig->parse($twig->tokenize('{% set bar = "baz" %}'));
        
        $this->assertEquals($contextParser->parse($node1)->getContext(), array('foo' => 'bar'));
        
        $this->assertEquals($contextParser->parse($node2)->getContext(), array('bar' => 'baz'));
        $this->assertNull($contextParser->getContext());
    }

    /**
     * @dataProvider dataProvider
     */
    public function testParser($source, $expected)
    {
        $twig = $this->twig;
        $contextParser = new ContextParser($twig);

        $node = $twig->parse($twig->tokenize($source));
        $this->assertEquals($contextParser->parse($node)->getContext(), $expected);
    }

    public function dataProvider()
    {
        return array(
            array(' ', null),
            array('{{ "Hello world!" }}', null),
            array('{% block foo %}{% endblock %}', null),
            array('{% set foo = "bar" %}', array('foo' => 'bar')),
            array('{% block foo %}{% set foo = "bar" %}{% endblock %}', array('foo' => 'bar')),
            array('{% set foo = "bar" %}{% set baz = "yux" %}', array('foo' => 'bar', 'baz' => 'yux')),
            array('{% set foo = "bar" %}{% block test %}{% set baz = "yux" %}{% endblock %}', array('foo' => 'bar', 'baz' => 'yux')),
            array('{% set foo = ["bar"] %}', array('foo' => array('bar'))),
            array('{% set foo = ["bar", "baz"] %}', array('foo' => array('bar', 'baz'))),
            array('{% set foo = [] %}', array('foo' => array())),
            array('{% set bar = ["foo", ["baz"], {"yux":"pux"}, "yea"] %}', array('bar' => array('foo', array('baz'), array('yux' => 'pux'), 'yea'))),
            array('{% set range = range(0, 3) %}', array('range' => range(0, 3))),
            array('{% set range = range(0, 6, 2) %}', array('range' => range(0, 6, 2))),
            array('{% set foo = [{"bar":"baz"}, "bar", range(0, 12, 2), ["yux", {"baz":"yea", "bar":"foo", "range":range(0, 100)}]] %}', array('foo' => array(array('bar' => 'baz'), 'bar', range(0, 12, 2), array('yux', array('baz' => 'yea', 'bar' => 'foo', 'range' => range(0, 100))))))
        );
    }
}
