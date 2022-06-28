<?php
	function writeToFile($txt, $type = 'test') {
		$myfile = null;

		if ($type == 'result') {
			$myfile = fopen(SOURCE_DIR . '\\' . RESULT_OUTPUT_FILE, "w") or die("Unable to open file!");
		} else {
			$myfile = fopen(SOURCE_DIR . '\\' . TEST_OUTPUT_FILE, "w") or die("Unable to open file!");
		}
		
		if (is_array($txt)) {
			$txt = print_r($txt, true);
		}

		fwrite($myfile, $txt);
		fclose($myfile);
	}

	function tsEcho($txt = 'Now') {
		//echo $txt . ': ' . date("H:i:s A") . "\n";
		echo $txt . ': ' . date("H:i:s") . "\n";
	}

	function addStat($k, $arr) {
		global $allStats, $time_start;
		$allStats[$k] = count($arr);
		tsEcho($k);

		if ($k == 'The End') {
			//execution time of the script
			$time_end = microtime(true);
			echo 'Total execution time in seconds: ' . round($time_end - $time_start, 3);
		}
	}

	function imitateMerge(&$array1, &$array2) {
		foreach($array2 as $i) {
			$array1[] = $i;
		}
	}

	function updateOccurency(&$mainArr, $k) {
		global $allLinesOrigByEpisode;
		$words = explode(' ', $k);
		$chars = '"';

		foreach ($allLinesOrigByEpisode as $ep => $arr) {
			foreach ($arr as $lnum => $line) {
				foreach ($words as $word) {
					//if (stripos($line, $word) !== false) {
					if (preg_match("/(\b)".$word."\b/i", $line)) {
						$l1 = "\n\"";
						if ($lnum > 0) $l1 .= trim($arr[$lnum-1], $chars)."\n";
						$l2 = "\"";
						if ($lnum+1 < count($arr)) $l2 = "\n".trim($arr[$lnum+1], $chars).$l2;

						//$mainArr[$k] = $mainArr[$k] . ", " . "\"".trim($line, $chars)."\"" . " (".$ep.")";
						$mainArr[$k] = [
							"count" => $mainArr[$k],
							"episode" => $ep,
							"phrase" => $l1 . trim($line, $chars) . $l2
						];

						break 3;
					}
				}
			}
		}
	}
?>