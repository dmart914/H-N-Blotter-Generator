<?php 
    
    define("DB_SERVER", "localhost");
    define("DB_USER", "cops_log");
    define("DB_PASS", "ilikeCh33s3");
    define("DB_NAME", "cops_log");
    
    // 1. Create a database connection
    $connection = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME);
     
    // 1.1 Test if connection occured
    if(mysqli_connect_errno()) {
        die("Database connection failed: " .
            mysqli_connect_error() . 
            " (" . mysqli_connect_errno() . ")"
        );
    }
    
    // Functions 
    
    function confirm_query($result_set) {
        if (!$result_set) {
            die("Database query failed.");
        }
    }
    
    function mysql_prep($string) {
        global $connection;
        
        $escaped_string = mysqli_real_escape_string($connection, $string);
        return $escaped_string;
    }
    
    
    function get_rand_quote() {
        /* Gets a random quote from the quotes
         * table in MySQL database.  Returns
         * The text of the quote.
         */
         global $connection;
         
         $query =   "SELECT * FROM quotes ";
         $query .=  "ORDER BY rand() ";
         $query .=  "LIMIT 1";
         
         $result = mysqli_query($connection, $query);
         confirm_query($result);
         
         return $result;
    }
    
    function output_rand_quote() {
        $result_set = get_rand_quote();
        $result = mysqli_fetch_assoc($result_set);
        
        $output = "<div id=\"quote\">";
        $output .= "<p class=\"quote-content\">\"" . htmlspecialchars($result['content']) . "\"</p>";
        $output .= "<p class=\"quote-author\">-&nbsp;" . htmlspecialchars($result ['author']) . "</p>";
        $output .= "</div>";
        
        return $output;
    }
    
    function get_column_names($table) {
        global $connection; 
        
        $query = "SHOW COLUMNS FROM {$table}";
        
        $result_set = mysqli_query($connection, $query);
        confirm_query($result_set);
        
        $output = array();
        while ($result = mysqli_fetch_assoc($result_set)) {
            array_push($output, $result['Field']);
        }
        return $output;
    }
    
    function get_all_dictionary_entries() {
        global $connection;
        
        $query = "SELECT * FROM dictionary ";
        $query .= "ORDER BY id ASC";
        
        $result = mysqli_query($connection, $query);
        confirm_query($query);
        
        return $result;
    }
    
    function output_all_dictionary_entries() {
        $result_set = get_all_dictionary_entries();
        $output = "<table id=\"dictionary-entries\">";
        $output .= "<tr class=\"dictionary-headers\">";
        
        $headers = get_column_names("dictionary");
        foreach ($headers as $header) {
            $output .= "<th>" . htmlspecialchars($header) . "</th>";
        }
        $output .= "<th>Modify</th>";
        
        $output .= "</tr>";
        $output .= "<tr class=\"spacer\"></tr>";
        while ($result = mysqli_fetch_assoc($result_set)) {
            $output .= "<tr class=\"dictionary-entry " . even_odd() . "\">";
            foreach ($result as $key => $column) {
                $output .= "<td class=" . htmlspecialchars($key) . ">";
                $output .= htmlspecialchars($column);
                $output .= "</td>";
            }
            $output .= "<td class=\"edit\"><a href=\"edit_dictionary.php?id=" . $result['id'] . "\">Edit</a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href=\"delete_dictionary.php?id=" . $result['id'] . "\" onclick=\"return confirm('Are you sure?')\">Delete</a></td>";
            $output .= "</tr>";
        }
        
        $output .= "</table>";
        
        return $output;
    }

    function get_dictionary_by_id($id) {
        global $connection;
        
        $safe_dictionary_id = mysqli_real_escape_string($connection, $id);
        
        $query = "SELECT * ";
        $query .= "FROM dictionary ";
        $query .= "WHERE id = {$safe_dictionary_id} ";
        $query .= "LIMIT 1";
        
        $dictionary_set = mysqli_query($connection, $query);
        confirm_query($dictionary_set);
        
        if ($dictionary = mysqli_fetch_assoc($dictionary_set)) {
            return $dictionary;   
        } else {
            return null;
        } 
    }
    
    function dictionary_lookup($entry) {
       global $connection;
       
       $entry = preg_replace("/\s{2,}/", " ", $entry);
       
       $safe_entry = mysqli_real_escape_string($connection, $entry);
       
       $query = "SELECT * ";
       $query .= "FROM dictionary ";
       $query .= "WHERE raw = '{$safe_entry}' ";
       $query .= "LIMIT 1";
       
       $result_set = mysqli_query($connection, $query);
       confirm_query($result_set);
       
       if ($result = mysqli_fetch_assoc($result_set)) {
           return $result;
       } else {
           return null;
       }
    }
    
?>