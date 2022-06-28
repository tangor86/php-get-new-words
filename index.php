<?php

	ini_set('memory_limit', '256M');
	date_default_timezone_set("Europe/Riga");
	$time_start = microtime(true); 
	$allStats = [];

	define('WORD_MIN_LENGTH', 3);
	define('SOURCE_DIR', 'D:\DISKS\Dropbox\Subtitles\The Hollow');
	define('TEST_OUTPUT_FILE', 'out.txt');
	define('RESULT_OUTPUT_FILE', 'result.txt');
	define('IGNORE_LIST_FILE', 'ignore.txt');

	include "functions.php";
	
	$allLines = [];
	$allLinesOrigByEpisode = [];
	$allWords = [];
	$ignoreList = file(SOURCE_DIR . '\\' . IGNORE_LIST_FILE, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
	$ignoreListHash = [];
	$onlyOneFile = 's1_1.txt';

	foreach ($ignoreList as $k => $v) {
		$ignoreListHash[$v] = true;
		$ignoreListHash[$v."'m"] = true;
		$ignoreListHash[$v."'d"] = true;
		$ignoreListHash[$v."'ll"] = true;
		$ignoreListHash[$v."'re"] = true;
		$ignoreListHash[$v."'ve"] = true;
		$ignoreListHash[$v."n't"] = true;
		$ignoreListHash[$v."s"] = true;
		$ignoreListHash[$v."d"] = true;
		$ignoreListHash[$v."ed"] = true;
		$ignoreListHash[$v."ally"] = true;
		$ignoreListHash[$v."ing"] = true;
		$ignoreListHash[$v."ting"] = true;
		$ignoreListHash[$v."ion"] = true;
		$ignoreListHash[$v."ions"] = true;
		$ignoreListHash[$v."ted"] = true;
	}

	//https://regex101.com/r/MI8Bof/1
	//https://regex101.com/r/BGG6iL/1
	$emptyReplaceExp = [
		'/^["--,.=:\s\[\]]*(.*?)["--,.=:;?!\s\[\]]*$/m'
	]; 

	tsEcho("New run");

	$handle = opendir(SOURCE_DIR);
 
	$onlyOneFile = null;

	/* This is the correct way to loop over the directory. */
    while (false !== ($entry = readdir($handle))) {
		$m = [];
		if ($entry != '..' && $entry != '.' && preg_match("/s([0-9]+_[0-9]+)/", $entry, $m)) {
			if (is_null($onlyOneFile) || (!is_null($onlyOneFile) && $onlyOneFile == $entry)) {
				echo "$entry ";
				//print_r($m);
				//$content = file_get_contents(SOURCE_DIR . '\\' . $entry);
				//print_r($content);

				//$arr = preg_split("/(\r\n|\n|\r)/", $content);
				//$arr = explode("\n", $content);

				$arr = file(SOURCE_DIR . '\\' . $entry, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
				$allLines = array_merge($allLines, $arr);

				$allLinesOrigByEpisode[$m[1]] = $arr;
			}
			//if ($breakAfterFirst) {break;}
		}
    }
	closedir($handle);

	//echo memory_get_usage() . "<br>\n";
	//echo memory_get_peak_usage() . "<br>\n"; 

	echo "\n";
	addStat('Total lines read', $allLines);

	foreach ($allLines as $k => $v) {
		$allLines[$k] = preg_replace($emptyReplaceExp[0], "$1", $v);
	}

	$allLines = array_filter($allLines);
	$allLines = array_values($allLines);

	addStat('Total lines after filtering', $allLines);

	//print_r($allLines);

	foreach ($allLines as $k => $v) {
		//$allLines[$k] = strtolower($v);
		//$allWords = array_merge($allWords, explode(' ', $allLines[$k]));
		//imitateMerge($allWords, explode(' ', $allLines[$k]));
		$tmpArr = explode(' ', strtolower($v));
		imitateMerge($allWords, $tmpArr);
	}

	addStat('Words created', $allWords);

	//apply regexp to words
	foreach ($allWords as $k => $v) {
		$allWords[$k] = preg_replace($emptyReplaceExp[0], "$1", $v);
		$allWords[$k] = preg_replace("/'s$/", "", $allWords[$k]);
	}

	// almost unprocessed: [36291] => deal
	addStat('Words after preg_replace', $allWords);
	//print_r($allWords);

	$allWords = array_filter($allWords, function($v) {
		global $ignoreListHash;
		return $v != null && strlen($v) >= WORD_MIN_LENGTH && !is_numeric($v) && !isset($ignoreListHash[$v]) && !preg_match("/^[0-9]+[:\/]+[0-9]+$/m", $v);
	});

	addStat('Total words > 1 length', $allWords);

	//print_r($allWords);

	$ret_header = print_r($allStats, true);
	$ret = $ret_header . "\n\n" . join("\n", $allLines);
	
	writeToFile($ret, 'test');

	addStat('Output created', $allWords);

	$resArr = array_count_values($allWords);

	$mergeEndings = ['s', 'ally'];
	foreach ($mergeEndings as $mergeEnding) {
		foreach ($resArr as $key => $value) {
			if (isset($resArr[$key])) {
				$skey = $key.$mergeEnding;
				$newKey = $key . ' ' . $skey;
				if (isset($resArr[$skey])) {
					$resArr[$newKey] = $resArr[$key]+$resArr[$skey];
					unset($resArr[$key]);
					unset($resArr[$skey]);
				}
			}
		}
	}

	ksort($resArr);

	foreach ($resArr as $key => $value) {
		updateOccurency($resArr, $key);
	}

	writeToFile($resArr, 'result');
	addStat('The End', $resArr);


	// echo "\n";
	// print_r($allStats);

	//print_r(join("\n", $allLines));
?>