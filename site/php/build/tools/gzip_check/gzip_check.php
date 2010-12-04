<?php
function download($site, $gzip=0) {
	// Headers
	$headers = array('Accept-Encoding: compress, gzip');
	$ch = curl_init($site);
	if ($gzip==1)
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$content = curl_exec($ch);
		return strlen($content);
}

if ($argc < 2) {
	echo "Usage: php $argv[0]n";
} else {
	$start = time();
	echo "No-Compression:    ".download($argv[1]);
	printf("tTime: %2.2f seconds n", number_format(((time() - $start))));
	$start = time();
	echo "With-Compression:  ".download($argv[1], 1);
	printf("tTime: %2.2f seconds n", number_format(((time() - $start))));
}

echo "nDownloading compressed and non-compressed versionsn10 times each and then calculating average... n";
$times = array();
for ($i=0;$i<20;$i++) {
	$start = time();
	download($argv[1]);
	array_push($times, (time() - $start));
}
echo "Non-Compressed version: ".average($times) . "n";

$times = array();
for ($i=0;$i<20;$i++) {
	$start = time();
	download($argv[1], 1);
	array_push($times, (time() - $start));
}
echo "Compressed version: ".average($times) . "n";

// Helper to get average
function average(array $a){
	return array_sum($a) / count($a);
}
?>