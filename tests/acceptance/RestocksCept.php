<?php

$i = new AcceptanceTester($scenario);
$i->wantTo('test restocks');

$validUrls = [
    '/restocks' => [
        'title' => 'Restocks',
    ],
    '/restocks/add' => [],
    '/restocks/1' => [],
];

foreach ($validUrls as $url => $checks) {
    $i->amOnPage($url);
    $i->seeResponseCodeIs(200);

    foreach ($checks as $selector => $text) {
        $i->see($text, $selector);
    }
}

$notFoundUrls = [
    '/restocks/100500',
    '/restocks/delete?id=100500',
];

foreach ($notFoundUrls as $url) {
    $i->amOnPage($url);
    $i->seeResponseCodeIs(404);
}
