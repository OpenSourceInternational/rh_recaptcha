<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'reCAPTCHA for Powermail',
    'description' => 'Implements Google reCAPTCHA 2 for Powermail',
    'category' => 'plugin',
    'author' => 'Richard Haeser',
    'author_email' => 'richardhaeser@gmail.com',
    'state' => 'stable',
    'uploadfolder' => '0',
    'createDirs' => '',
    'clearCacheOnLoad' => 0,
    'version' => '2.0.0',
    'constraints' => [
        'depends' => [
            'typo3' => '10.5.0-11.9.99',
        ],
        'conflicts' => [],
        'suggests' => []
    ],
    'clearcacheonload' => false,
    'author_company' => null,
];
