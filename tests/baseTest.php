<?php
use ddliu\requery\Context;

class BaseTest extends PHPUnit_Framework_TestCase {
    protected $q;
    public function setUp() {
        $this->q = new Context(file_get_contents(__DIR__.'/test.html');
    }

    public function testAll() {
        // title
        $title = (string)$this->q->find('`<title>(.*)</title>`Uis')[1];
        $this->assertEquals('Document', $title);

        // links
        $links = $this->q
            ->find('`<div class="block">.*</div>`Uis')
            ->findAll('`<li>.*</li>`Uis');

        $this->assertEquals(5, count($links));
        $links[3]->find('`<a href="(.*)">(.*)</a>`Uis');
    }
}