<?php 

require_once("../includes/functions.php");
require_once("../includes/session.php");
require_once("../includes/mysql_functions.php");

if (isset($_SESSION['errors']) || empty($_POST)) {
    // Failure
    // Check for errors, fail if not empty
    // Check for arrest or 911 entries, fail if empty
    redirect_to('index.php');
} else {
    // Success
?>

<?php include_once("../includes/layouts/header.php"); ?>
        
        
<pre>
<?php echo output_arrest_entries($_POST); ?>
<?php echo output_em_entries($_POST); ?>
</pre>   
        
<?php include_once("../includes/layouts/footer.php"); ?>

<?php } // End error and POST check ?>