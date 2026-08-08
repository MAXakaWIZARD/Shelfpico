<?php

$i = new AcceptanceTester($scenario);
$i->wantTo('test console');

$basicCommands = [
    [
        'cmd' => '"' . $_SERVER['BIN_PHP'] . '" bin/console --env=test',
        'output' => [
            'Shélfpico console',
            'Available commands:'
        ]
    ],
    [
        'cmd' => '"' . $_SERVER['BIN_PHP'] . '" bin/console app:db-import --dry-run --env=test',
        'output' => [
            'Database import (DRY RUN)',
            'About to import file',
        ]
    ],
    [
        'cmd' => '"' . $_SERVER['BIN_PHP'] . '" bin/console doctrine:schema:validate --env=test',
        'output' => [], // Doctrine writes ALL output to STDERR via getErrorStyle(), making grabShellOutput() empty
    ],
];

foreach ($basicCommands as $item) {
    $i->runShellCommand($item['cmd'], true);
    
    foreach ($item['output'] as $text) {
        $i->seeInShellOutput($text);
    }
}
