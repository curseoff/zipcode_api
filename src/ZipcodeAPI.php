<?php

class ZipcodeAPI {
	const API_URL = 'http://zip.cgis.biz/csv/zip.php';

	public  function api_url($zn) {
		return self::API_URL . '?' . http_build_query(['zn' => $zn]);
	}

	public function address($csv) {
		$address = implode('', array_slice($csv, 12, 3));
		$address = preg_replace("|以下に掲載がない場合|", "", $address);

		return $address;
	}

	public  function output($zn) {
		$url = $this->api_url($zn);
		$csv = str_getcsv(mb_convert_encoding(file_get_contents($url), "UTF-8", "EUC-JP"), ",", '"');

		echo sprintf("%s : %s\n", $zn, $this->address($csv));
	}
} 