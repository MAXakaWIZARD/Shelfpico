<?php

$i = new AcceptanceTester($scenario);
$i->wantTo('test orders');

$validUrls = [
    '/orders' => [
        'title' => 'Orders',
    ],
    '/orders/add' => [],
    '/orders/1' => [],
    '/orders/receipt/1_2020-10-29' => [],
    '/orders/receipt/1_2020-10-29?lang=UA' => [],
    '/orders/aggregated' => [],
];

foreach ($validUrls as $url => $checks) {
    $i->amOnPage($url);
    $i->seeResponseCodeIs(200);

    foreach ($checks as $selector => $text) {
        $i->see($text, $selector);
    }
}

$notFoundUrls = [
    '/orders/100500',
    '/orders/delete?id=100500',
];

foreach ($notFoundUrls as $url) {
    $i->amOnPage($url);
    $i->seeResponseCodeIs(404);
}
