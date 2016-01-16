<?php
define('ROOT_DIR', __DIR__);

require ROOT_DIR . '/config/Options.php';
require ROOT_DIR . '/src/ParallelFor.php';
require ROOT_DIR . '/src/ZipcodeAPI.php';

Options::define();

$zipcodes = [1508510, 7930000, 3320000, 3292225, 2891204];

$executor = function($zipcodes) {
	$results = [];

	foreach($zipcodes as $index => $zipcode) {
		$zipcode_api = new ZipcodeAPI();
		$results[] = $zipcode_api->output($zipcode);
	}

	return $results;
};

$p = new ParallelFor;
$p->setNumChilds(JOBS);
$p->run($executor, $zipcodes);


