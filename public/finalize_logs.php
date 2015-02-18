<?php 

require_once("../includes/functions.php");
require_once("../includes/session.php");
require_once("../includes/mysql_functions.php");

if (!empty($_SESSION['errors']) || (!isset($_SESSION['arrest_log_data_1']) && !isset($_SESSION['em_log_1']) && !isset($_SESSION['em_log_2']))) {
    // Failure: errors exist or there is no data in the session
    $_SESSION['errors']['finalize'] = "Something went wrong while processing the logs.";
    redirect_to('index.php');
} else {
    // Success
    
    // To do:
        // Store the arrest entry data in a variable
        // Clear the session
        // Iterate through all the arrest entries
            // Output data into forms, formatting the data for the cops log.
            // Make it editable so the user can change anything that's wrong
            // Provide an 'include' checkbox so the user can decide if they want
            // the entry to be part of the cops log
            // Send via GET to final page, where user can copy and paste finished log
            
    // Store data in a variable
    $arrest_log_data_1 = (isset($_SESSION['arrest_log_data_1'])) ? $_SESSION['arrest_log_data_1'] : null;
    $em_log_1 = (isset($_SESSION['em_log_1'])) ? $_SESSION['em_log_1'] : null;
    $em_log_2 = (isset($_SESSION['em_log_2'])) ? $_SESSION['em_log_2'] : null;
    
    // Clear the session
    session_unset();
    
    // echo "<pre>";
    // print_r($_SESSION);
    // echo "</pre>";
    
}

?>
<?php include_once("../includes/layouts/header.php"); ?>

        <h2>Here's the data I retreived for you to finalize...</h2><br />
        <p>When you're done, hit submit.</p><br />
        <?php $all_errors = errors(); echo output_errors($all_errors); ?>
        <form action="output_logs.php" method="post">
        <?php // Begin iteration of arrest entry data...
            $arrest_entry_pointer = 0;
            foreach ($arrest_log_data_1 as $entry) {        
        ?>
        

            <p class="input-header third_width">First name:<br />
                <input class="input-data arrest_name" type="text" name="arrest_entry_<?php echo $arrest_entry_pointer; ?>_first_name" value="<?php echo $entry['first_name']; ?>" />
            </p>
            <p class="input-header third_width">Last name:<br />
                <input class="input-data arrest_name" type="text" name="arrest_entry_<?php echo $arrest_entry_pointer; ?>_last_name" value="<?php echo $entry['last_name']; ?>" />
            </p>
            <p class="input-header">Age:<br />
                <input class="input-data arrest_age" type="text" name="arrest_entry_<?php echo $arrest_entry_pointer; ?>_age" value="<?php echo $entry['age']; ?>" />
            </p>
            <br />
            <p class="input-header full_width">Address:<br />
                <input class="input-data arrest_address_street" type="text" name="arrest_entry_<?php echo $arrest_entry_pointer; ?>_address_street" value="<?php echo $entry['address_street']; ?>" />
            </p>
            <p class="input-header most_width">City:<br />
                <input class="input-data arrest_address_city most_width" type="text" name="arrest_entry_<?php echo $arrest_entry_pointer; ?>_address_city" value="<?php echo $entry['address_city']; ?>" />
            </p>
            <p class="input-header">State:<br />
                <input class="input-data arrest_address_state" type="text" name="arrest_entry_<?php echo $arrest_entry_pointer; ?>_address_state" value="<?php echo $entry['address_state']; ?>" />
            </p>
            <br />
            <p class="input-header full_width">Charges:<br />
                <?php $charge_pointer = 0; // iterate charges
                foreach ($entry['charges'] as $charge) {  ?>
                        <p class="original_charge">Original: <?php echo output_original($charge); ?></p>
                        <input class="input-data arrest_charge" type="text" name="arrest_entry_<?php echo $arrest_entry_pointer; ?>_charge_<?php echo $charge_pointer; ?>" value="<?php echo output_charge($charge); ?>" />
                        <input class="input-data arrest_charge_bail" type="text" name="arrest_entry_<?php echo $arrest_entry_pointer; ?>_charge_<?php echo $charge_pointer; ?>_bail" value="<?php echo $charge[3]; ?>"<br />
                <?php   $charge_pointer++; 
                } // end charge iteration 
                $charge_pointer = 0; ?>  
            </p>
            <br />
            <p class="input-header">Total bail:<br />
                <input class="input-data arrest_bail" type="text" name="arrest_entry_<?php echo $arrest_entry_pointer; ?>_bail" value="<?php echo $entry['total_bail']; ?>" />    
            </p>
            <p class="input-header">Include entry?<input class="input-include" type="checkbox" name="arrest_entry_<?php echo $arrest_entry_pointer; ?>_include" value="1" checked /></p>
            <?php $arrest_entry_pointer++; // End of entry ?>
            <hr />
        
        <?php } // End iteration of arrest entry data ?>
        <?php if (isset($em_log_1) || isset($em_log_2)) { ?>
            <h2>Dispatch logs</h2>
            <p><?php $em_log_counter = 0; 
            if ($em_log_1) {
                foreach ($em_log_1 as $entry) { ?>
                    <input class="em_log_entry" type="text" name="em_log_entry_<?php echo $em_log_counter; ?>" value="<?php echo output_single_em_log_entry($entry); ?>" />
                    <input class="input-include" type="checkbox" name="em_log_entry_<?php echo $em_log_counter; ?>_include" value="1" <?php if ($entry['include'] == 1) {echo "checked";} ?> /> 
                    <?php $em_log_counter++; ?>
                <?php } // end $em_log_1 iteration ?> 
            <?php } // end $em_log_1 conditional ?>
        <?php } // End 911 logs ?>
            </p>
            
            <input type="submit" value="submit" class="button" />
        
        </form>

<?php include_once("../includes/layouts/footer.php"); ?>