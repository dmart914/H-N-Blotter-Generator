<?php 

require_once("../includes/functions.php");

// $content = convert_to_text_em("../uploads/911-log-1.pdf");
// $content = convert_to_text_em("/Users/incubuddy/Sites/cops/test_sources/911-log-1.pdf");
// $content = explode("\n", $content);

// echo "<pre>";
// print_r($content);
// echo "</pre>";

/*
$file = "../uploads/20140625-test-1.pdf";
$bn_file = basename($file, ".pdf");
echo $bn_file;
*/
// convert_to_text_em("/Users/incubuddy/Sites/cops/test_sources/911-log-1.pdf");

// $content = file_get_contents("../test_sources/911-log-1.html");
// $content = explode("\n", $content);

// print_r($content);
echo "<pre>";
$content = convert_to_text_em("../uploads/911-log-1.pdf");
$content = find_911_entries($content);
$content = transform_911_data($content);

echo output_single_em_log_entry($content[62]);

echo "<br/>";
print_r($content);

echo "</pre>";
?>