<?php

class Options {
	const SHORTOPTS = "";
	const LONGOPTS = ['jobs:'];

	const DEFAULT_JOBS = 5;

	public static function define() {
		$options = getopt(self::SHORTOPTS, self::LONGOPTS);

		$jobs = (isset($options['jobs']) && is_numeric($options['jobs']) && $options['jobs']  > 0) ? (int)$options['jobs'] : self::DEFAULT_JOBS;
		define('JOBS' , $jobs);
	}
}