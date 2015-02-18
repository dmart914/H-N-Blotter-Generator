<?php
/* THE H&N BLOTTER GENERATOR
 * Version 0.1
 * 
 * The program assists with the generation of 
 * law enforcement blotter for reports at the 
 * Herald and News. 
 * 
 * Program written by Dave Martinez
 * Summer of 2014
 * 
 * Contact: dmartinez@heraldandnews.com
 * 
 * Version 0.1 released August 19, 2014
 * 
 */
 
require_once("../includes/functions.php");
require_once("../includes/session.php");
require_once("../includes/mysql_functions.php");
    
?>

<?php include_once("../includes/layouts/header.php"); ?>

    <ul id="menu">
        <li class="menu-item"><a href="dictionary.php">Dictionary</a></li>
    </ul>
    <?php $all_errors = errors(); echo output_errors($all_errors); ?>
    <h2>Please choose a document to process:</h2><br />
    <form enctype="multipart/form-data" action="process_logs.php" method="post">
        <input type="hidden" name="MAX_FILE_SIZE" value="5000000" />
        <span class="upload_label">Arrest log 1:&nbsp;&nbsp;</span>
        <input type="file" name="arrest_log_1" class="button upload" />
        <br />
        <span class="upload_label">911 log 1:&nbsp;&nbsp;</span>
        <input type="file" name="em_log_1" class="button upload"  />
        <br />  
        <p>
            <input type="submit" value="Submit" class="button" />
        </p>
    </form>
    

<?php include_once("../includes/layouts/footer.php"); ?>