<?php

$i = new AcceptanceTester($scenario);
$i->wantTo('test tags');

$validUrls = [
    '/tags',
    '/tags/add',
    '/tags/2',
];

foreach ($validUrls as $url) {
    $i->amOnPage($url);
    $i->seeResponseCodeIs(200);
}

$notFoundUrls = [
    '/tags/100500',
    '/tags/delete?id=100500',
];

foreach ($notFoundUrls as $url) {
    $i->amOnPage($url);
    $i->seeResponseCodeIs(404);
}

$i->amGoingTo('Create tag with invalid data');
$i->amOnPage('/tags/add');
$i->seeResponseCodeIs(200);
$i->fillField('#tag_edit_form input[name="title"]', '');
$i->click('#tag_edit_form button[type="submit"]');
$i->seeInCurrentUrl('/tags/save');
$i->seeResponseCodeIs(200);
$i->see('title:' . "\n" . '    ERROR: This value should not be blank.', '.alert-danger');

$i->amGoingTo('Create tag');
$i->amOnPage('/tags/add');
$i->seeResponseCodeIs(200);
$i->seeInTitle('Add tag');
$i->see('Add tag', '.card-header');
$tagTitle = 'Test tag ' . mt_rand(1, 100);
$i->fillField('#tag_edit_form input[name="title"]', $tagTitle);
$i->click('#tag_edit_form button[type="submit"]');
$i->seeResponseCodeIs(200);
$i->seeInCurrentUrl('/tags/add');
$i->seeInTitle('Add tag');
$i->see('Add tag', '.card-header');
$i->see('Created', '.badge.bg-success');
$tagId = $i->grabFromCurrentUrl('~prevCreatedId=(\d+)~');

$i->amGoingTo('Edit tag');
$i->amOnPage('/tags/' . $tagId);
$i->seeResponseCodeIs(200);
$i->seeInTitle($tagTitle);
$i->see('Edit tag', '.card-header');
$i->fillField('#tag_edit_form input[name="title"]', 'Test tag new');
$i->click('#tag_edit_form button[type="submit"]');
$i->seeResponseCodeIs(200);
$i->seeInCurrentUrl('/tags/' . $tagId);
$i->seeInTitle('Test tag new');
$i->see('Edit tag', '.card-header');
$i->see('Saved', '.badge.bg-success');

$i->amGoingTo('Delete tag');
$i->amOnPage('/tags/delete?id=' . $tagId);
$i->seeResponseCodeIs(200);
$i->amOnPage('/tags/' . $tagId);
$i->seeResponseCodeIs(404);
