<?php

$i = new AcceptanceTester($scenario);
$i->wantTo('test base pages');

$validUrls = [
    '/',
    '/dashboard/tags-stats',
];

foreach ($validUrls as $url) {
    $i->amOnPage($url);
    $i->seeResponseCodeIs(200);
}

$notFoundUrls = [
    '/whatever',
];

foreach ($notFoundUrls as $url) {
    $i->amOnPage($url);
    $i->seeResponseCodeIs(404);
}
