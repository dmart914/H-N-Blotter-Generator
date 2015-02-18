<?php require_once("../includes/functions.php");
require_once("../includes/mysql_functions.php");
require_once("../includes/session.php");

$people = array(
    12345 => array(
        'id' => 12345,
        'first_name' => 'Joe',
        'surname' => 'Bloggs',
        'age' => 23,
        'sex' => 'm'
    ),
    12346 => array(
        'id' => 12346,
        'first_name' => 'Adam',
        'surname' => 'Smith',
        'age' => 18,
        'sex' => 'm'
    ),
    12347 => array(
        'id' => 12347,
        'first_name' => 'Amy',
        'surname' => 'Jones',
        'age' => 21,
        'sex' => 'f'
    )
);

$entry = 'UNLAWFUL MANUF MARIJ';

?>
<pre>
    <?php
print_r(dictionary_lookup($entry));
    
    print_r(array_sort($people, 'age', SORT_DESC)); // Sort by oldest first
print_r(array_sort($people, 'surname', SORT_ASC)); // Sort by surname
    ?>
</pre>
?>