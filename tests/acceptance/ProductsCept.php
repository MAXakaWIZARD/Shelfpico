<?php

$i = new AcceptanceTester($scenario);
$i->wantTo('test products');

$validUrls = [
    '/products' => [
        'title' => 'Products',
    ],
    '/products/add' => [],
    '/products/1' => [],
];

foreach ($validUrls as $url => $checks) {
    $i->amOnPage($url);
    $i->seeResponseCodeIs(200);

    foreach ($checks as $selector => $text) {
        $i->see($text, $selector);
    }
}

$notFoundUrls = [
    '/products/100500',
    '/products/delete?id=100500',
];

foreach ($notFoundUrls as $url) {
    $i->amOnPage($url);
    $i->seeResponseCodeIs(404);
}
