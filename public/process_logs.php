<?php 

require_once("../includes/functions.php");
require_once("../includes/session.php");
require_once("../includes/mysql_functions.php");

if (!isset($_FILES['arrest_log_1']) && !isset($_FILES['em_log_1']) && !isset($_FILES['em_log_2'])){
    $_SESSION['errors']['upload'] = "Please upload a file.";
    redirect_to('index.php');
}



if ($_FILES['arrest_log_1']['error'] !== 4) {
    // arrest_log_1 processing
    
    // establish variable
    $arrest_log_1 = $_FILES['arrest_log_1'];
    
    // confirm and validate
    if (!confirm_upload($arrest_log_1) || !validate_file($arrest_log_1)) {
        // Failure
        redirect_to("index.php");
    } else {
        // Success, process arrest_log_1
        
        // Confirm the moved file
        $arrest_log_1["path"] = move_file($arrest_log_1);
        
        if (!$arrest_log_1["path"]) {
            $_SESSION['errors']["move_file"] = "Failed to move file.";
            redirect_to("index.php");
        }
        
        // convert to text
        $arrest_log_1["content"] = convert_to_text($arrest_log_1["path"]);

        // confirm conversion to text
        if (!$arrest_log_1["content"]) {
            $_SESSION['errors']["pdf_to_text"] = "Failed to convert PDF file to text.";
            redirect_to("index.php");
        }
             
        // Find names
        $arrest_log_1['arrest_entries'] = find_arrest_names($arrest_log_1['content']);
                 
        // Find addresses, add to entries
        $addresses = find_arrest_addresses($arrest_log_1['content']);
        $arrest_log_1['arrest_entries'] = add_data($addresses, $arrest_log_1['arrest_entries']);
 
        // Find dates of birth, add to entries
        $dobs = find_arrest_dobs($arrest_log_1['content']);
        $arrest_log_1['arrest_entries'] = add_data($dobs, $arrest_log_1['arrest_entries']);
                
        // Find places of birth and trailing states
        $birth_places = find_arrest_birth_places($arrest_log_1['content']);
        $arrest_log_1['arrest_entries'] = add_data($birth_places,$arrest_log_1['arrest_entries']);
         
        // Find arrest date and times
        $arrest_times = find_arrest_times($arrest_log_1['content']);
        $arrest_log_1['arrest_entries'] = add_data($arrest_times, $arrest_log_1['arrest_entries']);
        
        // Find charges and assign
        $charges = find_charges($arrest_log_1['content']);
        $arrest_log_1['arrest_entries'] = add_data($charges, $arrest_log_1['arrest_entries']);
        
        
        // Write the data to the session
        $_SESSION['arrest_log_data_1'] = $arrest_log_1['arrest_entries'];
                
        }  // end of arrest_log_1 processing
}

if ($_FILES['em_log_1']['error'] !== 4) {

    // em_log_1 processing
    if (!confirm_upload($_FILES['em_log_1']) || !validate_file($_FILES['em_log_1'])) {
        // Failure
        redirect_to("index.php");
    } else {
        // success, process em_log_1
               
        // establish the variable
        $em_log_1 = $_FILES['em_log_1'];
        
        // move and confirm file
        $em_log_1["path"] = move_file($em_log_1);
        
        if (!$em_log_1["path"]) {
            $_SESSION['errors']["move_file"] = "Failed to move 911 log 1 file.";
            redirect_to("index.php");
        } 
        
        // convert file to text
        $em_log_1["content"] = convert_to_text($em_log_1["path"]);
        // find 911 entries
        $em_log_1['entries'] = find_911_entries($em_log_1['content']);

        // Transform 911 data
        $em_log_1['entries'] = transform_911_data($em_log_1['entries']);
              
        // Write data to session
        $_SESSION['em_log_1'] = $em_log_1['entries'];
          
    }
}
 
redirect_to('finalize_logs.php');

?>