<?php 

require_once("../includes/functions.php");
require_once("../includes/session.php");
require_once("../includes/mysql_functions.php");

if (isset($_POST['submit'])) {
    // Create entry
    
    $required_fields = array("include", "raw");
    validate_presences($required_fields);
    
    $fields_with_max_lengths = array("raw" => 200, "clean" => 200);
    validate_max_lengths($fields_with_max_lengths);
    
    if (empty($_SESSION['errors'])) {
        $include = (int) $_POST['include'];
        $raw = mysql_prep($_POST['raw']);
        $clean = mysql_prep($_POST['clean']);
        
        $query = "INSERT INTO dictionary (";
        $query .= "include, raw, clean";
        $query .= ") VALUES (";
        $query .= "{$include}, '{$raw}', '{$clean}'";
        $query .= ")";
        $result = mysqli_query($connection, $query);
        
        if ($result) {
            // Successful
            $_SESSION["message"] = "Dictionary entry created.";
            redirect_to("dictionary.php");
        } else {
            // Failure
            $_SESSION["message"] = "Dictionary entry creation failed.";
            redirect_to("dictionary.php");
        }
     } else {
        redirect_to("dictionary.php");
     }
    
}

?>

<?php include_once("../includes/layouts/header.php"); ?>
<?php $message = message(); echo $message; ?>
<?php $all_errors = errors(); echo output_errors($all_errors); ?>

    <form action="new_dictionary.php" method="post">
        Include this crime by default?
        <select name="include">
            <option value="1">Yes (1)</option>
            <option value="0">No (0)</option>
        </select>
        <br />
        Raw text to look for:<br />
        <input type="text" name="raw" value="" /><br />
        Clean text to output:<br />
        <input type="text" name="clean" value="" /><br />
        <input type="submit" name="submit" value="Submit" class="button" /> 
    </form>    
    



<?php include_once("../includes/layouts/footer.php"); ?>