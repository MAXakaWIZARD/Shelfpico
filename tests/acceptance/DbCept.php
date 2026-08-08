<?php

$i = new AcceptanceTester($scenario);
$i->wantTo('test db comtroller');

$validUrls = [
    '/db/schema-update',
    '/db/import',
    '/db/backup',
];

foreach ($validUrls as $url) {
    $i->amOnPage($url);
    $i->seeResponseCodeIs(200);
}
