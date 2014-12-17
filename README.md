# requery [![Build Status](https://travis-ci.org/ddliu/requery.svg?branch=master)](https://travis-ci.org/ddliu/requery)

Query text data with the power of Regular Expression.

## Usage

```php
use ddliu\requer\Context;

$q = new Context($content);
$q->find('#<table>.*</table>#Uis')
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
```