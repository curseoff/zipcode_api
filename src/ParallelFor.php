<?php

class ParallelFor
{
	private $num_child = 4;
	private $aggregator = null;

	public function __construct()
	{
		if( ! function_exists('pcntl_fork')) {
			throw new RuntimeException('This SAPI does not support pcntl functions.');
		}

		$func = function(&$result, $data) {
			if( ! is_array($result)) {
				$result = array();
			}
			$result = array_merge($result, $data);
		};

		$this->setAggregator($func);
	}

	public function setNumChilds($num)
	{
		if( ! is_numeric($num)) {
			throw new InvalidArgumentException('Argument #1($num) must be integer.');
		}

		$this->num_child = $num;
	}

	public function setAggregator($func)
	{
		if( ! $func instanceof Closure) {
			throw new InvalidArgumentException('Argument #2($callback) must be closure.');
		}
		$this->aggregator = $func;
	}

	public function run(Closure $executor, array $data, $opt = array()) {
		if( ! $executor instanceof Closure) {
			throw new InvalidArgumentException('Argument #2($executor) must be closure.');
		}

		$uniqid = uniqid(get_class($this), true);
		$shared_file_prefix = '/tmp/.parallel_for_' . $uniqid;

		$num_data = count($data);
		$count_per_child = ceil($num_data / $this->num_child);
		

		$num_works = 0;

		for($i = 0; $i < $this->num_child; $i++) {
			$pid = pcntl_fork();
			if($pid) {
				$num_works++;
				continue;
			}

			$offset = $i * $count_per_child;
			if($offset + $count_per_child >= $num_data) {
				$limit = $num_data - $offset;
			} else {
				$limit = $count_per_child;
			}
			
			$child_data = array_slice($data, $offset, $limit);
			
			$child_result = $executor($child_data, $opt);

			$shared_file = $shared_file_prefix . getmypid();
			file_put_contents($shared_file, serialize($child_result));

			exit;
		}

		$result = null;
		while($num_works > 0) {
			$stat = null;
			$pid = pcntl_wait($stat);

			$shared_file = $shared_file_prefix . $pid;
			$data = unserialize(file_get_contents($shared_file));

			$method = $this->aggregator;
			$method($result, $data);

			unlink($shared_file);

			$num_works--;
		}

		return $result;
	}
}