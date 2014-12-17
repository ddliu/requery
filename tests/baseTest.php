<?php
use ddliu\requery\Context;

class BaseTest extends PHPUnit_Framework_TestCase {
    const REGEXP_TITLE = '#<title>(.*)</title>#Uis';
    protected $q;
    public function setUp() {
        $this->q = new Context(file_get_contents(__DIR__.'/test.html'));
    }

    public function testFind() {
        $title = (string)$this->q->find(self::REGEXP_TITLE)[1];
        $this->assertEquals('{{page_title}}', $title);
    }

    public function testMustFind() {
        $title = $this->q->mustFind(self::REGEXP_TITLE)->extract(1);
        $this->assertEquals('{{page_title}}', $title);
    }

    /**
     * @expectedException ddliu\requery\QueryException
     */
    public function testMustFindException() {
        $this->q->mustFind('#<faketag>#');
    }

    public function testFindAll() {
        $lists = $this->q->find('#<div class="block">.*</div>#Uis')->findAll('#<li>.*</li>#Uis');
        $this->assertEquals(5, $lists->count());
    }

    public function testMustFindAll() {
        $lists = $this->q->find('#<div class="block">.*</div>#Uis')->mustFindAll('#<li>.*</li>#Uis');
        $this->assertEquals(5, $lists->count());
    }

    /**
     * @expectedException ddliu\requery\QueryException
     */
    public function testMustFindAllException() {
        $this->q->mustFindAll('#<faketag>#');
    }

    public function testExtract() {
        $title = $this->q->find('#<title>(.*)</title>#Uis')->extract(1);
        $this->assertEquals('{{page_title}}', $title);
        $data = $this->q->find('#<div class="block">.*</div>#Uis')->findAll('#<li>.*</li>#Uis')->findAll('#<a href="(?P<url>.*)">(?P<text>.*)</a>#Uis')
            ->extract('text');

        $this->assertEquals(5, count($data));
        $this->assertEquals('Link3', $data[2]);
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

    public function testArrayAccess() {
        $lists = $this->q->find('#<div class="block">.*</div>#Uis')->findAll('#<li>.*</li>#Uis');
        $this->assertEquals('<li><a href="#">Link4</a></li>', $lists[3]->extract(0));
    }
}