<?php 

require_once("../includes/functions.php");
require_once("../includes/session.php");

echo "<pre>";

$content = convert_to_text('../test_sources/20140625-test-2.pdf');

// print_r($content);
$charges = find_charges($content);
print_r($charges);








echo "</pre>"; 




?>