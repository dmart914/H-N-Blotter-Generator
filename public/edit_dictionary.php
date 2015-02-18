<?php 

require_once("../includes/functions.php");
require_once("../includes/session.php");
require_once("../includes/mysql_functions.php");

?>

<?php 

if (!isset($_GET['id']) && !isset($_POST['submit'])) {
    $_SESSION['errors']['edit'] = "Please select a page to edit.";
    redirect_to("index.php");
}

if (isset($_POST['submit'])) {
    // Update entry
    
    $required_fields = array("include", "raw");
    validate_presences($required_fields);
    
    $fields_with_max_lengths = array("raw" => 200, "clean" => 200);
    validate_max_lengths($fields_with_max_lengths);
    
    if(empty($_SESSION['errors'])) {
        // Perform update
        
        $id = $_POST['id'];
        $include = (int) $_POST['include'];
        $raw = mysql_prep($_POST['raw']);
        $clean = mysql_prep($_POST['clean']);
        
        // Query
        $query = "UPDATE dictionary SET ";
        $query .= "include = {$include}, ";
        $query .= "raw = '{$raw}', ";
        $query .= "clean = '{$clean}' ";
        $query .= "WHERE id = {$id} ";
        $query .= "LIMIT 1";
        $result = mysqli_query($connection, $query);
        
        if ($result && mysqli_affected_rows($connection) >= 0) {
            // Success
            $_SESSION["message"] = "Dictionary entry updated.";
            redirect_to("edit_dictionary.php?id={$id}");
        } else {
            // Failure
            $_SESSION["message"] = "Dictionary entry update failed.";
            redirect_to("edit_dictionary.php?id={$id}");
        }
        
    } else {
        redirect_to("edit_dictionary.php?id={$_POST['id']}");
    }
    
} 

$dictionary_entry = get_dictionary_by_id($_GET['id']); 

include_once("../includes/layouts/header.php"); ?>

    <?php $message = message(); echo $message; ?>
    <?php $all_errors = errors(); echo output_errors($all_errors); ?>
    <form action="edit_dictionary.php" method="post">
        <input type="hidden" name="id" value="<?php echo $dictionary_entry['id']; ?>" />
        ID:&nbsp;&nbsp;<?php echo $dictionary_entry['id']; ?><br />
        Include this crime by default?
        <select name="include">
            <option value="1"<?php if ($dictionary_entry['include'] == 1) { echo " selected=\"selected\""; } ?>>Yes (1)</option>
            <option value="0"<?php if ($dictionary_entry['include'] == 0) { echo " selected=\"selected\""; } ?>>No (0)</option>
        </select>
        <br />
        Raw text to look for:<br />
        <input type="text" name="raw" value="<?php echo $dictionary_entry['raw']; ?>" /><br />
        Clean text to output:<br />
        <input type="text" name="clean" value="<?php echo $dictionary_entry['clean']; ?>" /><br />
        <input type="submit" name="submit" value="Submit" class="button" /> 
    </form>    
    
<?php include_once("../includes/layouts/footer.php"); ?>