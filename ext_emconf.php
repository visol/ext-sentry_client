<?php

$EM_CONF[$_EXTKEY] = array (
	'title' => 'Sentry Client',
	'description' => 'Sentry Client for TYPO3 - https://www.getsentry.com/',
	'category' => 'services',
	'version' => '1.0.3',
	'state' => 'stable',
	'uploadfolder' => false,
	'createDirs' => '',
	'clearcacheonload' => true,
	'author' => 'Christoph Lehmann',
	'author_email' => 'christoph.lehmann@networkteam.com',
	'author_company' => 'networkteam GmbH',
	'constraints' => 
	array (
		'depends' => 
		array (
			'typo3' => '6.2.0-8.7.99',
		),
		'conflicts' => 
		array (
		),
		'suggests' => 
		array (
		),
	),
);
