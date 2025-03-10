<?php
echo "data received by Thong\n";
$min = $_POST['min'];
$max = $_POST['max'];
$remote = $_SERVER['REMOTE_ADDR'];
$today = date('Y-m-d H:i:s');
$record = $today.' '.$min.' '.$max.' '.$remote."\n";
$fname = "mydata.txt";
if(file_exists($fname))
	$fp = fopen($fname, "a");
else $fp = fopen($fname, "w");
fwrite ($fp, $record);
fclose($fp);
echo "data stored\n";










//echo 'It\'ll be interesting to know about the string. ';
//echo"\n";
// to escape the backslash within the string
//echo 'A \\ is named as backslash. ';
//echo"\n";

/*
// use of back-slash to display string with apostrophe
echo 'It\'ll be interesting to know about the string. ';
echo"\n";
*/
?>

