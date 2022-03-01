<?php
	$authString = "SuperSecureStringDoNotShare";
	$DATA_DIR = "./data";

	if ($_SERVER["HTTP_X_ORIGIN_AUTH"] != $authString) {
		http_response_code(500);
		die();
	}

	$uri = $_SERVER["REDIRECT_URL"];


	$x = explode("/", $uri);
	list($emp, $hash, $frag, $type) = $x;
	if (is_numeric($frag)) {
		if (!is_dir("{$DATA_DIR}/{$hash}")) mkdir("data/{$hash}");
		if (!is_dir("{$DATA_DIR}/{$hash}/{$frag}")) mkdir("data/{$hash}/{$frag}");

		$start_path = "{$DATA_DIR}/{$hash}/start";
		$tick_path = "{$DATA_DIR}/{$hash}/tick";
		$path = "{$DATA_DIR}/{$hash}/{$frag}/{$type}";

		switch ($type) {
			case 'start':
				$data = $_GET;
				$data["signup_fragment"] = $frag;
				file_put_contents($start_path, json_encode($data));
				file_put_contents($path, file_get_contents('php://input'));
				break;
			
			case 'delta': case 'full':
				file_put_contents($path, file_get_contents('php://input'));
				if ($type == "full") {
					file_put_contents($tick_path, json_encode([
						"tick" => $_GET['tick'],
						"last_receive" => time(),
						"fragment" => $frag,
					]));
					file_put_contents("{$DATA_DIR}/{$hash}/{$frag}/tick", $_GET['tick']);
				}

				if (!is_file($start_path)) {
					http_response_code(205);
					die();
				}
				break;
		}
	}