<?php

$i = new AcceptanceTester($scenario);
$i->wantTo('test search');

$validUrls = [
    '/search' => [],
    '/search?tag=1' => [],
    '/search?term=5w50' => [],
];

foreach ($validUrls as $url => $checks) {
    $i->amOnPage($url);
    $i->seeResponseCodeIs(200);

    foreach ($checks as $selector => $text) {
        $i->see($text, $selector);
    }
}

$notFoundUrls = [
    '/search?tag=65535',
];

foreach ($notFoundUrls as $url) {
    $i->amOnPage($url);
    $i->seeResponseCodeIs(404);
}

$i->amOnPage('/');
$i->submitForm('#search-form', ['term' => '5w50']);
$i->seeResponseCodeIs(200);
$i->see('Search results for term "5w50"', 'h1');
$i->seeElement('a', ['href' => '/products/1']);

$i->amOnPage('/');
$i->submitForm('#search-form', ['term' => '5ц50']);
$i->seeResponseCodeIs(200);
$i->see('Search results for term "5w50"', 'h1');
$i->seeElement('a', ['href' => '/products/1']);

$i->amOnPage('/');
$i->submitForm('#search-form', ['term' => 'BlaBlaFakeTerm']);
$i->seeResponseCodeIs(200);
$i->see('Search results for term "BlaBlaFakeTerm"', 'h1');
$i->see('0', 'span.badge');
