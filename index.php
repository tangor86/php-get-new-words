<?php
	date_default_timezone_set("Europe/Riga");

	$souceDir = 'D:\DISKS\Dropbox\Subtitles\The Hollow';
	$allLines = [];
	$allWords = [];
	$breakAfterFirst = true;

	//https://regex101.com/r/MI8Bof/1
	$emptyReplaceExp = [
		'/^["--, ]*(.*?)["-,.?! ]*$/'
	];

	echo "\n\n\nNew run at:      " . date("H:i:s A") . " ==> \n\n";

	$handle = opendir($souceDir);

	/* This is the correct way to loop over the directory. */
    while (false !== ($entry = readdir($handle))) {
        echo "$entry\n";

		if ($entry != '..' && $entry != '.') {
			$content = file_get_contents($souceDir . '\\' . $entry);
			//print_r($content);

			$arr = preg_split("/(\r\n|\n|\r)/", $content);
			$allLines = array_merge($allLines, $arr);
			
			//if ($breakAfterFirst) {break;}
		}
    }

	echo "\nResult, ".count($allLines).":";
	print_r($allLines);

	foreach ($allLines as $k => $v) {
		$allLines[$k] = preg_replace($emptyReplaceExp[0], "$1", $v);
	}

	$allLines = array_filter($allLines);
	$allLines = array_values($allLines);

	print_r($allLines);

	foreach ($allLines as $k => $v) {
		$allLines[$k] = strtolower($v);
		$allWords = array_merge($allWords, explode(' ', $allLines[$k]));
	}

	//  [36291] => deal
	echo "\nResult (words), ".count($allWords).":";
	print_r($allWords);


	//print_r(join("\n", $allLines));
?>