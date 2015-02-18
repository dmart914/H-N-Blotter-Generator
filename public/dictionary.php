<?php 

require_once("../includes/functions.php");
require_once("../includes/session.php");
require_once("../includes/mysql_functions.php");

include_once("../includes/layouts/header.php");

?>

<?php $message = message(); echo $message; ?>
<?php $all_errors = errors(); echo output_errors($all_errors); ?>

<p style="font-size:1.3em;margin:15px 0;text-align:center;width:100%"><a href="new_dictionary.php">Create new entry</a></p><br />
<?php echo output_all_dictionary_entries(); ?>

<?php include_once("../includes/layouts/footer.php"); ?>