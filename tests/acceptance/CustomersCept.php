<?php

$i = new AcceptanceTester($scenario);
$i->wantTo('test customers');

$validUrls = [
    '/customers' => [
        'title' => 'Customers',
    ],
    '/customers/add' => [],
    '/customers/1' => [],
];

foreach ($validUrls as $url => $checks) {
    $i->amOnPage($url);
    $i->seeResponseCodeIs(200);

    foreach ($checks as $selector => $text) {
        $i->see($text, $selector);
    }
}

$notFoundUrls = [
    '/customers/100500',
    '/customers/delete?id=100500',
];

foreach ($notFoundUrls as $url) {
    $i->amOnPage($url);
    $i->seeResponseCodeIs(404);
}
