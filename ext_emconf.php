<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Visual Diff',
    'description' => 'Visual diff extension for TYPO3 - compares two bases (A,B) and shows pages with broken frontend',
    'category' => 'be',
    'author' => 'DevSK',
    'author_email' => '',
    'state' => 'stable',
    'version' => '1.0.0',
    'constraints' => [
        'depends' => [
            'typo3' => '13.0.0-13.99.99',
            'scheduler' => '13.0.0-13.99.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
