<?php
use ddliu\requery\Context;

class BaseTest extends PHPUnit_Framework_TestCase {
    protected $q;
    public function setUp() {
        $this->q = new Context(file_get_contents(__DIR__.'/test.html'));
    }

    public function testFind() {
        $title = (string)$this->q->find('#<title>(.*)</title>#Uis')[1];
        $this->assertEquals('{{page_title}}', $title);
    }

    public function testFindAll() {
        $lists = $this->q->find('#<div class="block">.*</div>#Uis')->findAll('#<li>.*</li>#Uis');
        $this->assertEquals(5, $lists->count());
    }

    public function testExtract() {

    }

    public function testExtractAll() {

    }

    public function testThen() {
        $test = $this;
        $this->q->find('#<table>.*</table>#Uis')
            ->then(function($table) {
                $table->findAll('#<th>(.*)</th>#Uis')
                    ->each(function($th) {
                        echo 'th: '.$th[1]."\n";
                    });
            })
            ->then(function($table) {
                $table->find('#<tbody>.*</tbody>#Uis')->findAll('#<tr>.*</tr>#Uis')
                    ->each(function($tr) {
                        $tr->findAll('#<td>(.*)</td>#Uis')
                            ->each(function($td) {
                                echo 'td: '.$td[1]."\n";
                            });
                    });
            });
    }
}