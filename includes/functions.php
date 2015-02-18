<?php

    $_SESSION["errors"] = array();
    $_SESSION["message"] = array();

    function add_data($source, $target) {
        /* Adds data from source array to
         * target array. Iterates through each 
         * data entry in source array and adds to
         * coexisiting target.
         * 
         * Returns new array with entries added.
         */
         
         for ($i = 0; $i < count($source); $i++) {
             foreach ($source[$i] as $key => $value) {
                 if (isset($target[$i][$key])) {
                     // If target key already exists,
                     // add to value.
                     $target[$i][$key] .= ' ' .  $value;
                 } else {
                    $target[$i][$key] = $value;
                }
             }
         }
         return $target;
    }
    
    function confirm_upload($file) {
        /* confirm_upload($file)
         * 
         * Returns true if uploaded file has no error.
         *  
         * Otherwise, writes error to $errors array and 
         * returns false.
         */
        
        switch ($file["error"]) {
            case UPLOAD_ERR_OK:
                return true;
            case UPLOAD_ERR_INI_SIZE:
                $_SESSION["errors"]["upload"] = "The uploaded file exceeds the maximum file size allows in php.ini.";
                return false;
            case UPLOAD_ERR_FORM_SIZE:
                $_SESSION["errors"]["upload"] = "The uploaded file exceeds the maximum file size allowed by the HTML form.";
                return false;
            case UPLOAD_ERR_PARTIAL: 
                $_SESSION["errors"]["upload"] = "The uploaded file was only partially uploaded.";
                return false;
            case UPLOAD_ERR_NO_FILE:
                $_SESSION["errors"]["upload"] = "No file was uploaded.";
                return false;
            case UPLOAD_ERR_NO_TMP_DIR:
                $_SESSION["errors"]["upload"] = "There is no temporary folder available.";
                return false;
            case UPLOAD_ERR_CANT_WRITE:
                $_SESSION["errors"]["upload"] = "Upload can't be saved to server.";
                return false;
            case UPLOAD_ERR_EXTENSION:
                $_SESSION["errors"]["upload"] = "The file has an unexpected extension.";
                return false;
            default: 
                $_SESSION["errors"]["upload"] = "Unknown upload error.";
                return false;
        }
    }
    
    function convert_to_text($file) {
    /* FUNCTION convertToText
     * Uses 'pdftotext' shell command to convert provided
     * PDF file into text. PDF must be OCR before upload.
     * 
     * THIS FUNCTION WILL NOT WORK WITHOUT pdftotext
     * INSTALLED ON THE HOST COMPUTER
     * 
     * Takes drive location of the PDF file as $file 
     * parameter. 
     * 
     * Returns the text of the PDF file.
     * 
     */ 
        
        $safe_file = escapeshellarg($file);
        $arg = "pdftotext -layout {$safe_file} -";
        $safe_arg = escapeshellcmd($arg);
        $content = shell_exec($arg);
        
        return $content;
        
    }
    
    function find_911_entries($string) {
    /* Takes a string of text from 911 logs.
     * 
     * Returns two level array of entries
     */
    
        $output = array(); 
        $counter = 0;
        $content = explode("\n", $string);
        
        foreach ($content as $line) {
            $line = trim($line);
            if (strlen($line) > 0) {
                if (strpos($line, "Event No.:") !== FALSE) {
                    preg_match_all("/Event No.:\s+\d+\s+Type:\s+(.+)(\s+)?Date.Time:\s+(.+)/", $line, $captured);
                    $output[$counter]['call_type'] = trim($captured[1][0]);
                    $output[$counter]['time'] = $captured[3][0];
                } elseif (strpos($line, "Location:") !== FALSE) {
                    preg_match_all("/Location:\s+(.+)\s+Agency:\s+(.+)/", $line, $captured);
                    $output[$counter]['location'] = trim($captured[1][0]);
                    $output[$counter]['agency'] = $captured[2][0];
                } elseif (strpos($line, "Location Com.:") !== FALSE) {
                    preg_match_all("/Location Com.: +(.+) +Case/", $line, $captured);
                    if (strlen(trim($captured[1][0])) > 0) {
                        $output[$counter]['location2'] = trim($captured[1][0]);
                    }
                } elseif (strpos($line, "Reported By:") !== FALSE) {
                    preg_match_all("/Reported By:(\s+)?(.+)?/", $line, $captured);
                    if (strlen($captured[2][0]) > 0) {
                        $output[$counter]['reported_by'] = $captured[2][0];
                    }
                } elseif (strpos($line, "Disposition:") !== FALSE) {
                    $counter++;
                } else {
                    $new_line = str_replace(array(",",".",";"), "", $line);
                    if (ctype_upper($new_line)) {
                        $output[$counter]['call_type'] = $output[$counter]['call_type'] . " " . $line;
                    }
                }
            }
        }
        return $output;  
    }
    
    function find_arrest_times($content) {
        /* Finds arret time and date
         * 
         * Returns array of timestamps
         */
         
         preg_match_all("/Arrest Date:\s+(([0-9\/]+)\s+([0-9:]+)\s+(PM|AM))/", $content, $matches);
         
         $final_list = array();
         $counter = 0;
         
         foreach ($matches[1] as $match) {
             if (empty($match)) {
                 $final_list[$counter]['arrest_date'] = null;
             } else {
                 $final_list[$counter]['arrest_date'] = date('Y-m-d H:i:s', strtotime($match));
             }
             $counter++;
         }
         
         return $final_list;
         
    }
    
    function find_arrest_birth_places($content) {
        /* Finds places of birth
         * 
         * Uses regex to find places of birth
         * 
         * Takes in string of text and returns
         * an array filled with strings.
         * 
         */
         
         preg_match_all("/Place of Birth:\s+([A-Z]{2})?/", $content, $matches);
    
        $final_list = array();
        $counter = 0;
    
        foreach ($matches[1] as $match) {
            if (empty($match)) {
                $final_list[$counter]['place_of_birth'] = null;
            } else {
                $final_list[$counter]['place_of_birth'] = trim($match);
            }
            $counter++;
        }
        
        return $final_list;
        
    }
    
    function find_charges($content) {
        /* Finds charges and assignments
         * 
         * Takes in content and array of names
         * 
         * Uses custom algorithm:
         *      Takes first name and finds line number
         *      Takes second name and finds line number
         *      Takes charge and finds line number
         *      IF the charge num. > first name num and 
         *      < second name num, assign charge to that
         *      pern
         *      IF charge num. > second name, advance
         *      name nums. to next and recheck.
         * 
         * Function then finds bail amounts and checks
         * to see if num. of bails == num. of charges
         * (it should be). If true, adds bails and 
         * assigns to $final_list array.
         * 
         * Returns array
         * 
         */  
         
         $lines = explode("\n", $content);
         $first_name_line_num = 0;
         $second_name_line_num = 0;
         $charge_line_num = 0;
         $name_pointer = 0;
         $charge_pointer = 0;
         $bail_pointer = 0;
         $charges_added = 0;
         $final_list = array();
         
         // Finds charges
         preg_match_all("/Charge:\s+([A-Z0-9 *\/\\\.'-]+)\s+Other Chargeable Statute:\s+([A-Z0-9 *\/\\\.'-]+)?\s+Arrest Type:\s+([A-Z0-9 *\/\\\.'-]+)?\s+/", $content, $charge_matches);
         
         // Finds names
         preg_match_all("/([A-Z-]+)\s?([A-Z-]+)?\s?([A-Z-]+)?,\s([A-Z-]+)\s?([A-Z-]+)?\s?([A-Z-]+)?\s?/", $content, $name_matches);
         
         // Finds bails
         preg_match_all("/Bail:\s+.([0-9\.,]+)/", $content, $bail_matches);
         
         // Create array for the amount of names
         for ($i=0; $i<count($name_matches[0]); $i++) {
             $final_list[$i]['charges'] = array();
             $final_list[$i]['total_bail'] = 0;
         }
          
         while ($charge_pointer < count($charge_matches[1])) {  
             if ($name_pointer+1 == count($name_matches[0])) {
                 // Handles final charges, where no second name exists                 
                 while ($charge_pointer < count($charge_matches[0])) {
                     foreach ($lines as $num => $line) {
                         if (strpos($line, $charge_matches[1][$charge_pointer]) !== false && strpos($charges_added, "^" . $num . ";") == false) {
                             array_push($final_list[$name_pointer]['charges'], array($charge_matches[1][$charge_pointer], $charge_matches[2][$charge_pointer], $charge_matches[3][$charge_pointer]));
                             $charge_pointer++;  
                             break;
                         }
                     }
                 }
                 break;
             }
                 
             foreach ($lines as $num => $line) {
                 // Find name line numbers
                 if (strpos($line, trim($name_matches[0][$name_pointer])) !==  false) {
                     $first_name_line_num = $num;
                 } elseif (strpos($line, trim($name_matches[0][$name_pointer+1])) !== FALSE) {
                     $second_name_line_num = $num;
                     break;
                 }
             }
             
             foreach ($lines as $num => $line) {
                 // Find charge line number
                 if (strpos($charges_added, "^{$num};") === false && strpos($line, $charge_matches[1][$charge_pointer]) !== false) {
                     $charge_line_num = $num;
                     break;
                 }
             }
             
             // Compare charge line number and line name numbers
             if ($charge_line_num >= $first_name_line_num && $charge_line_num <= $second_name_line_num) {
                 // Success: charge line num is >= first name line num
                 // and <= second name line num
                 array_push($final_list[$name_pointer]['charges'], array($charge_matches[1][$charge_pointer], $charge_matches[2][$charge_pointer], $charge_matches[3][$charge_pointer]));
                 $charges_added .= "^" . $charge_line_num . ";";
                 $charge_pointer++;
             } elseif ($charge_line_num > $second_name_line_num) {
                 // Failure: charge num > second name number    
                 $name_pointer++;
             }
         }
        
        // Reset $name_pointer for bail script
        
        $name_pointer = 0;
        $charge_pointer = 0;
        
        // Iterates through each charge for each individual, finding sum
        // of bail. Adds bail key and value to final list.
        
        foreach ($final_list as $index => $entry) {
            foreach ($entry['charges'] as $index_2 => $charge) {
                // array_push($charge, floatval(str_replace(',', '', $bail_matches[1][$bail_pointer])));
                $final_list[$name_pointer]['charges'][$charge_pointer][3] = floatval(str_replace(',', '', $bail_matches[1][$bail_pointer]));
                $final_list[$name_pointer]['total_bail'] += floatval(str_replace(',', '', $bail_matches[1][$bail_pointer]));
                $bail_pointer++;
                $charge_pointer++;
            }
            $name_pointer++;
            $charge_pointer = 0;
        }
        
        // Reset for dictionary lookup
        $name_pointer = 0;
        
        
        foreach ($final_list as $entry) {
            $charge_pointer = 0;
            foreach ($entry['charges'] as $charge){
                if ($result = dictionary_lookup($charge[0])) {
                    $final_list[$name_pointer]['charges'][$charge_pointer]['clean'] = $result['clean'];
                    $final_list[$name_pointer]['charges'][$charge_pointer]['include'] = $result['include'];
                }
                if ($result = dictionary_lookup($charge[1])) {
                    if (isset($final_list[$name_pointer]['charges'][$charge_pointer]['clean'])) {
                        $final_list[$name_pointer]['charges'][$charge_pointer]['clean'] .= ' (' . $result['clean'] . ')';
                    } else {
                        $final_list[$name_pointer]['charges'][$charge_pointer]['clean'] = $result['clean'];
                    }
                    if (!isset($final_list[$name_pointer]['charges'][$charge_pointer]['include']) || $final_list[$name_pointer]['charges'][$charge_pointer]['include'] == 0) {
                        $final_list[$name_pointer]['charges'][$charge_pointer]['include'] = $result['include'];
                    }
                }
                $charge_pointer++;
            }
            $name_pointer++;
        }
        
        return $final_list;     
    }

/*
    function charges_lookup($arrest_entries) {
        foreach ($arrest_entries as $entry) {
            $c1 = 0;
            foreach ($entry['charges'] as $charge) {
                $c2 = 0;
                foreach($charge as $key => $piece) {
                    if (!is_numeric($piece) && $result = dictionary_lookup($piece)) {
                        $arrest_entries[$c1]['charges_defined'][$c2][$key] = $result['clean'];
                        $arrest_entries[$c1]['charges_defined'][$c2]['include'] = $result['include'];
                        $c2++;
                    } else {
                        $arrest_entries[$c1]['charges_defined'][$c2] = null;
                        $c2++;
                    }
                }
             $c1++;   
            }
        }
        
        return $arrest_entries;
    }
 */
    
    function find_arrest_dobs($content) {
        /* Finds dates of birth
         * 
         * Uses regex to find dates of birth. 
         * 
         * Takes in string of text, returns 
         * an array of timestamp dates and ages
         */
            
        preg_match_all("/Date of Birth:\s+([0-9\/]+)/", $content, $matches);
        
        $final_list = array();
        $counter = 0;
        
        foreach ($matches[1] as $match) {
            if (empty($match)) {
                $final_list[$counter]['date_of_birth'] = null;
                $final_list[$counter]['age'] = null;
            } else {
                $final_list[$counter]['date_of_birth'] = date('Y-m-d H:i:s', strtotime($match));
                $final_list[$counter]['age'] = floor(((time()-strtotime($match))/(3600*24*365)));
            }
            $counter++;
        }
        
        return $final_list;
    }
    
    function find_arrest_names($content) {
        /* Find names
         * 
         * Uses regex to find names in the text of a content
         * entry. Takes hits and formats them to clean 
         * Firstname Middlename Lastname entries.  
         * 
         * Returns an array of array(first_name, last_name).
         * 
         */
         
         preg_match_all("/([A-Z-]+)\s?([A-Z-]+)?\s?([A-Z-]+)?,\s([A-Z-]+)\s?([A-Z-]+)?\s?([A-Z-]+)?\s?/", $content, $matches);
         
         $final_list = array();
         $counter = 0;
         
         // Iterate through each name in the 0 index
         foreach ($matches[0] as $match) {
             
             $working_match = explode(",", $match);
             // Remove 'NMN' (which means No Middle Name)
             $working_match[1] = str_replace('NMN', '', $working_match[1]);
             $final_list[$counter]['first_name'] = ucwords(strtolower(trim($working_match[1])));
             $final_list[$counter]['last_name'] = ucwords(strtolower(trim($working_match[0])));
             $counter++;
         }

        return $final_list;
    
    }

    function find_arrest_addresses($content) {
        /* Finds addresses.
         * 
         * Takes string of content
         * 
         * Returns array of addresses
         */
         
         preg_match_all("/Address:\s+([A-Z0-9].+)\s+([A-Z ]+)?,\s+([A-Z]{2})/", $content, $matches);
         
         $final_list = array();
         $counter = 0;
         
         foreach ($matches[0] as $match) {
             $final_list[$counter]['address_street'] = ucwords(strtolower(trim($matches[1][$counter])));
             $final_list[$counter]['address_city'] = ucwords(strtolower(trim($matches[2][$counter])));
             $final_list[$counter]['address_state'] = trim($matches[3][$counter]);
             $counter++;
         }
         
         return $final_list;
        
    }
    
    function output_single_em_log_entry($entry) {
        $output = "";
        
        // Location
        $working_location = explode(" ", $entry['location']);
        if (is_numeric($working_location[0])) {
            // If the first word of the location is a number
            // Explode the string, use the second word as start
            // Obscure the block number
            // Add to output
            $location = "";
            $len = count($working_location);
            for ($i=1; $i<$len; $i++) {
                $location .= " ";
                $location .= $working_location[$i];
            }
            $location .= ", ";
            $location .= substr($working_location[0], 0, -2);
            $location .= "00 block, ";
            $output .= $location;
        } else {
            $output .= $entry['location'];
            $output .= ", ";
        }
        
        // Check for '/' in location, replace with 'and' and
        // capitalize the next word
        if (strpos($output, '/')) {
            $working_output = explode('/', $output);

            $output = $working_output[0];
            $output .= " and ";
            $output .= ucwords($working_output[1]);
        }
        
        // Call type
            $output .= $entry['call_type_clean'];
            $output .= " reported ";
            
        // Date
            $output .= date("l", $entry['time']);
            if (date("G", $entry['time']) < 11) {
                $output .= " morning.";
            } elseif (date("G", $entry['time']) >= 11 && date("G", $entry['time']) < 17) {
                $output .= " afternoon.";
            } elseif (date("G", $entry['time']) >= 17 && date("G", $entry['time']) < 19) {
                $output .= " evening.";
            } elseif (date("G", $entry['time']) >= 19) {
                $output .= " night.";   
            }
       return trim($output);
    }

    function output_em_entries($entries) {
        $output = "";
        $entry_pointer = 0;
        
        while ($entry_pointer < count($entries)) {
            if (isset($entries["em_log_entry_{$entry_pointer}"]) && isset($entries["em_log_entry_{$entry_pointer}_include"])) {
                $output .= "<p>";
                $output .= $entries["em_log_entry_{$entry_pointer}"];
                $output .= "</p>";
            }     
            $entry_pointer++;
        }
        return $output;
    }
    
    function output_arrest_entries($entries) {
        /* Takes in an array of arrest entries from process_logs.php
         * and returns a string of formatted entries.
         */
         
         $output = "";
         $arrest_entry_pointer = 0;
         $charge_entry_pointer = 0;
         
         while (isset($entries["arrest_entry_{$arrest_entry_pointer}_first_name"])) {
             if (isset($entries["arrest_entry_{$arrest_entry_pointer}_include"])) {
                 $output .= "<p class=\"arrest-entry\">";
                 $output .= $entries["arrest_entry_{$arrest_entry_pointer}_first_name"];
                 $output .= " ";
                 $output .= $entries["arrest_entry_{$arrest_entry_pointer}_last_name"];
                 $output .= ", ";
                 $output .= $entries["arrest_entry_{$arrest_entry_pointer}_age"];
                 $output .= ", ";
                 $output .= $entries["arrest_entry_{$arrest_entry_pointer}_address_street"];
                 $output .= " ";
                 $output .= $entries["arrest_entry_{$arrest_entry_pointer}_address_city"];
                 $output .= " ";
                 $output .= $entries["arrest_entry_{$arrest_entry_pointer}_address_state"];
                 $output .= ", ";
                 while (isset($entries["arrest_entry_{$arrest_entry_pointer}_charge_{$charge_entry_pointer}"])) {
                     $output .= $entries["arrest_entry_{$arrest_entry_pointer}_charge_{$charge_entry_pointer}"];
                     $output .= ", ";
                     $charge_entry_pointer++;
                 }
                 $charge_entry_pointer = 0; // reset the charge entry pointer
                 $output .= "held in lieu of $";
                 $output .= number_format($entries["arrest_entry_{$arrest_entry_pointer}_bail"], 2);
                 $output .= " bail.";
                 $output .= "</p>";
             }
             $arrest_entry_pointer++;
         }
         return $output;
    }
    
    function output_charge($charge=array()) {
        /* Takes in an array of charge information
         * generated from find_charges() and 
         * returns a string
         */
         
         $output = "";
         
         if (isset($charge['clean'])) {
             $output = $charge['clean'];
         } else {
             
             if (empty($charge[1])) {
                 // Usually means its a new charge, not FTA
                 $output .= $charge[0];
             } elseif (strpos($charge[0], "FAIL TO APPEAR") !== false) {
                 // Failure to appear
                 $output .= "Failure to appear (" . $charge[1] . ")";
             } else {
                 // Default case returns as is
                 $output .= $charge[1] . $charge[0];
             }
             $output = strtolower($output);
         }
         
         return $output;
         
    }
    
    function output_errors($errors=array()) {
        /* Output errors
         * 
         * Returns errors in HTML format if there are any.
         * 
         */ 
        
        $output = "";
        if (!empty($errors)) {
            $output .= "<div class=\"error\">";
            $output .= "Please fix the following errors: ";
            $output .= "<ul>";
            foreach($errors as $key => $error) {
                $output .= "<li>";
                $output .= $error;
                $output .= "</li>";
            }
            $output .= "</ul>";
            $output .= "</div>";
            }
        return $output;
    }
    
    function output_message($message) {
        /* Output errors
         * 
         * Returns errors in HTML format if there are any.
         * 
         */ 
        
        $output = "";
        if (!empty($message)) {
            $output .= "<div class=\"message\">";
            $output .= "<ul>";
                $output .= "<li>";
                $output .= $message;
                $output .= "</li>";
            $output .= "</ul>";
            $output .= "</div>";
            }
        return $output;
    }
    
    function output_version() {
        /* Returns string of text with current version number */
        
        $version = 0.3;
        
        return strval($version);
    }
    
    function move_file($file) {
        /* move_file($file)
         *  
         * Moves files to the uploads directory.  
         * 
         * Returns new path and filename on success and false on 
         * failure.
         */
        $uploads_directory = "../uploads";
        $file_tmp_name = $file['tmp_name'];
        $file_name = $file['name'];
        $new_file_location = $uploads_directory . "/" . $file_name;
        
        if (!move_uploaded_file($file_tmp_name, $new_file_location)) {
            return false;
        } else {
            return $new_file_location;
        }
         
    }
    
    function redirect_to($new_location) {
        header("Location: " . $new_location);
        exit;
    }
    
    function transform_911_data($entries=array()) {
        $counter = 0;
        foreach ($entries as $entry) {
            // Call type dictionary (could probably be moved to array, seperate file)
            // Include number indicates whether this is included by default
            switch ($entry['call_type']) {
                case 'DISTURBANCE, VERBAL':
                    $entries[$counter]['call_type_clean'] = "verbal disturbance";
                    $entries[$counter]['include'] = 0;
                    break;
                case 'HAZARD':
                    $entries[$counter]['call_type_clean'] = "hazard";
                    $entries[$counter]['include'] = 0;
                    break;
                case 'SUSPICIOUS PERSON, VEHICLES, ETC.':
                    $entries[$counter]['call_type_clean'] = "suspicious activity";
                    $entries[$counter]['include'] = 0;
                    break;
                case 'VEHICLE STOP':
                    $entries[$counter]['call_type_clean'] = "vehicle stop";
                    $entries[$counter]['include'] = 0;
                    break;
                case 'NOISE COMPLAINT':
                    $entries[$counter]['call_type_clean'] = "noise complaint";
                    $entries[$counter]['include'] = 0;
                    break;
                case 'SUBJECT STOP':
                    $entries[$counter]['call_type_clean'] = "subject stop";
                    $entries[$counter]['include'] = 0;
                    break;
                case 'ASSAULT':
                    $entries[$counter]['call_type_clean'] = "assault";
                    $entries[$counter]['include'] = 1;
                    break;
                case 'CITIZEN COMPLAINT':
                    $entries[$counter]['call_type_clean'] = "citizen complaint";
                    $entries[$counter]['include'] = 0;
                    break;
                case 'PROPERTY ALARM':
                    $entries[$counter]['call_type_clean'] = "property alarm";
                    $entries[$counter]['include'] = 0;
                    break;
                case 'INTOXICATED PERSON':
                    $entries[$counter]['call_type_clean'] = "intoxicated person";
                    $entries[$counter]['include'] = 0;
                    break;
                case 'UNLAWFUL ENTRY  MOTOR  VEH': 
                    $entries[$counter]['call_type_clean'] = "unlawful entry into a motor vehicle";
                    $entries[$counter]['include'] = 1;
                    break;
                case  'UNLAWFUL ENTRY MOTOR  VEH':
                    $entries[$counter]['call_type_clean'] = "unlawful entry into a motor vehicle";
                    $entries[$counter]['include'] = 1;
                    break;
                case 'CIVIL MATTER':
                    $entries[$counter]['call_type_clean'] = "civil matter";
                    $entries[$counter]['include'] = 0;
                    break;
                case 'EXTRA PATROL':
                    $entries[$counter]['call_type_clean'] = "extra patrol";
                    $entries[$counter]['include'] = 0;
                    break;
                case 'OUTSIDE ASSIST':
                    $entries[$counter]['call_type_clean'] = "outside assist";
                    $entries[$counter]['include'] = 0;
                    break;
                case 'THEFT':
                    $entries[$counter]['call_type_clean'] = "theft";
                    $entries[$counter]['include'] = 1;
                    break;
                case 'SHOTS HEARD':
                    $entries[$counter]['call_type_clean'] = "shots heard";
                    $entries[$counter]['include'] = 0;
                    break;
                case 'FOLLOW UP':
                    $entries[$counter]['call_type_clean'] = "follow up";
                    $entries[$counter]['include'] = 0;
                    break;
                case 'POSSIBLE DECEASED  PERSON':
                    $entries[$counter]['call_type_clean'] = "possible deceased person";
                    $entries[$counter]['include'] = 0;
                    break;
                case 'NARACOTICS INFORMATION':
                    $entries[$counter]['call_type_clean'] = "narcotics information";
                    $entries[$counter]['include'] = 0;
                    break;
                case 'VANDALISM MISCHIEF':
                    $entries[$counter]['call_type_clean'] = "vandalism";
                    $entries[$counter]['include'] = 1;
                    break;
                case 'BURGLARY':
                    $entries[$counter]['call_type_clean'] = "burglary";
                    $entries[$counter]['include'] = 1;
                    break;
                case 'ALARM DURESS, PANIC, ETC':
                    $entries[$counter]['call_type_clean'] = "alarm or duress";
                    $entries[$counter]['include'] = 0;
                    break;
                case 'WARRANT SERVICE':
                    $entries[$counter]['call_type_clean'] = "warrant service";
                    $entries[$counter]['include'] = 0;
                    break;
                case 'JUVENILE PROBLEM':
                    $entries[$counter]['call_type_clean'] = "juvenile problem";
                    $entries[$counter]['include'] = 0;
                    break;
                case 'RUNAWAY':
                    $entries[$counter]['call_type_clean'] = "runaway";
                    $entries[$counter]['include'] = 0;
                    break;
                case 'TRAFFIC RELATED':
                    $entries[$counter]['call_type_clean'] = "traffic related call";
                    $entries[$counter]['include'] = 0;
                    break;
                default:
                    $entries[$counter]['call_type_clean'] = strtolower($entry['call_type']);
                    $entries[$counter]['include'] = 0;
                    break;
                } // end call_type switch
            $entries[$counter]['location'] = ucwords(strtolower($entry['location']));
            if (isset($entry['location2'])) {
                $entries[$counter]['location2'] = ucwords(strtolower($entry['location2']));
            }
            if (isset($entry['reported_by'])) {
                $entries[$counter]['reported_by'] = ucwords(strtolower($entry['reported_by']));
            }
            $entries[$counter]['time'] = strtotime($entry['time']);
            $counter++;
        }
        return $entries;
    }

    function output_original($charge) {
        $output = $charge[0];
        if (!empty($charge[1])) {
            $output .= " / " . $charge[1];
        }
        if (!empty($charge[2])) {
            $output .= " / " . $charge[2];
        }
        
        return $output;
    }
    
    function transform_arrest_data($entries=array()) {
        
    }
    
    function validate_file($file) {
    /* FUNCTION validate_file(pdffile)
     * File validator. Makes sure the file is valid.
     * Returns TRUE if file passes tests:
     * - File is a PDF
     * - File is less than or equal to 5mb in size. 
     * 
     */ 
        
        $output = "";
        if ($file['type'] != 'application/pdf') {
            $output .= "File is not a PDF.<br />";
        } elseif ($file['size'] > 5000000) {
            $output .= "File is larger than 5MBs.<br />";
        } elseif (strpos($file['name'], '(') > 0) {
           $output .= "File name has a disallowed character: ( or )<br />";
        }
        
        if ($output != "") {
            $_SESSION['errors']["validate_file"] = $output;
            return false;
        } else {
            return true;
        }
    }
    
    $even_odd = 1;
    function even_odd() {
        global $even_odd;
        if ($even_odd == 1) {
            $even_odd = 0;
            return "odd";
        } elseif ($even_odd == 0) {
            $even_odd = 1;                    
            return "even";
        }
    }
    
    function has_presence($value) {
        return isset($value) && $value !== "";
    }
    
    function fieldname_as_text($fieldname) {
        $fieldname = str_replace("_", " ", $fieldname);
        $fieldname = ucfirst($fieldname);
        return $fieldname;
    }
    
    function validate_presences($required_fields) {
        global $errors;
        foreach ($required_fields as $field) {
            $value = trim($_POST[$field]);
            if (!has_presence($value)) {
                $_SESSION["errors"]["presence"] = fieldname_as_text($field) . " can't be blank.";
            }
        }
    }
    
    function has_max_length($value, $max) {
        return strlen($value) <= $max;
    }

    function validate_max_lengths($fields_with_max_lengths) {
        global $errors;
        // Expects an assoc. array
        foreach($fields_with_max_lengths as $field => $max) {
            $value = trim($_POST[$field]);
            if (!has_max_length($value, $max)) {
                $errors[$field] = fieldname_as_text($field) . " is too long.";
            }
        }
    }
    
    function array_sort($array, $on, $order=SORT_ASC) {
        $new_array = array();
        $sortable_array = array();
        
        if (count($array) > 0) {
            foreach ($array as $k => $v) {
                if (is_array($v)) {
                    foreach($v as $k2 => $v2) {
                        if ($k2 == $on) {
                            $sortable_array[$k] = $v2;
                        }
                    }
                } else {
                    $sortable_array[$k] = $v;
                }
            }
            switch ($order) {
                case SORT_ASC:
                    asort($sortable_array);
                    break;
                case SORT_DESC:
                    arsort($sortable_array);
                    break;
            }
            
            foreach ($sortable_array as $k => $v) {
                $new_array[$k] = $array[$k];
            }    
        }
        return $new_array;
    }
    
?>