<?php 

/* 
 * THE H&N COPS LOG GENERATOR 
 * Version 0.01
 *
 * This is a program which intakes Klamath County
 * Jail logs and Klamath County 911 records in PDF
 * form. 
 * It provides a formated list of applicable entries
 * for an H&N reporter to post at 
 * http://heraldandnews.com.
 *
 * Code written by Dave Martinez in the month of 
 * June 2014
 *
 */

// TO DO:
// Write search function to find entries
// Need libraries of applicable offenses
// Write function to obscure addresses
// 
// NON PRIORITY TO DO:
// Be more specific with error messages during upload

echo '<pre>';

$uploaddir = '/Users/incubuddy/Sites/cops-log-generator/';
$uploadedFileName = $_FILES['userfile']['name'];
$uploadedFile = $uploaddir . basename($_FILES['userfile']['name']);
$fileContent = "";
$finalList = array();

// Set the timezone
date_default_timezone_set("America/Los_Angeles");

function checkFile($file) {
	/* FUNCTION checkFile(pdffile)
	 * File validator. Makes sure the file is valid.
	 * Returns TRUE if file passes tests:
	 * - File is a PDF
	 * - File is less than or equal to 5mb in size. 
	 * 
	 */	

	if ($_FILES[$file]['type'] != 'application/pdf' && $_FILES[$file]['size'] <= 5000000) {
		return False;
	} else {
		return True;
	}

}

function convertToText($file) {
	/* FUNCTION convertToText
	 * Uses 'pdftotext' shell command to convert provided
	 * PDF file into text. 
	 * 
	 * THIS FUNCTION WILL NOT WORK WITHOUT pdftotext
	 * INSTALLED ON THE HOST COMPUTER
	 * 
	 * Takes drive location of the PDF file as $file 
	 * parameter. 
	 * 
	 * NOTE - Doesn't work with files with special characters.
	 * i.e. ( or -, not sure which. Could be differing rules
	 * between shell and PHP's FNCs. Before release,
	 * stricter file names should be written into checkFile()
	 * 
	 */	
		
	global $fileContent, $uploaddir;

	echo "Executing pdftotext on " . $file . "\n";
	$newFileName = $uploaddir . (str_replace('.pdf', '.txt', $_FILES['userfile']['name']));
	echo "attempting /usr/local/bin/pdftotext {$file} {$newFileName}";
	$fileContent .= shell_exec("/usr/local/bin/pdftotext {$file} -");

	echo "Inside the function, fileContent is \n";
	echo $fileContent;

	
	//echo "File name set to " . $fileName . "\n";

	//$fileContent = $file_get_contents($fileName);

	if ($fileContent !== "") {
		echo "Successfully converted.\n";
		return True;
	} else {
		echo "Failed to convert to text.\n";
		return False;
	}
}

//Checks if file is uploaded. This should be migrated to a function.
if (move_uploaded_file($_FILES['userfile']['tmp_name'], $uploadedFile)) {
	if (checkFile('userfile') == False) {
 		echo "<h2>Sorry, that's not a valid PDF file.</h2>\n";
 		echo "<p>The uploaded file must be a PDF file smaller than 5 MB in size.</p>";
 	} else {
		// successful PDF upload
		echo "File is valid, and was successfully uploaded.\n";
	}
} else {
	echo "Possible file upload attack!\n";
}

function findEntries() {
	/* FUNCTION findEntries
	 * 
	 * Uses preg_match to find relevant entries for the cops log.
	 * Stores entries in a variable.
	 *  
	 * TO DO:
	 * Get address
	 * 	Store address in variable
	 * Get birthdate NOTE: THIS WOULD WORK BETTER AS AN OBJECT, NOT ARRAY
	 * 	Convert birthdate to age
	 * 	Store age in variable
	 * 
	 * 
	 */
	
	// Global varbiable delcarations
	global $finalList, $fileContent, $entry;
	
	// Name search
	preg_match_all("~([A-Z]+) ?([A-Z]+)?, ?([A-Z]+)? ([A-Z]+)\n~", $fileContent, $nameMatches);
	
//	echo "Here's a output of the nameSearch<br/ >";
//	print_r($nameMatches);
	
	// Write the names of each individual to the finalList variable
	for ($i = 0; $i < count($nameMatches[1]); $i++) {
	// For loop counts last names, runs as many times
		// NEED TO REWRITE THIS TO HANDLE SURNAMES (JR, III)
		if ((empty($nameMatches[3][$i]) == TRUE) || ($nameMatches[4][$i] == 'NMN') && (empty($nameMatches[2][$i]) == TRUE)) {
			// No middle name and no surname
			$finalList[$i] = array('Name' => $nameMatches[3][$i] . ' ' . $nameMatches[1][$i]);
		} elseif ((empty($nameMatches[3][$i]) == TRUE) || ($nameMatches[4][$i] == 'NMN')) {
			// No middle name, has surname
			$finalList[$i] = array('Name' => $nameMatches[4][$i] . ' ' . $nameMatches[1][$i] . ' ' . $nameMatches[2][$i]);
		} elseif (empty($nameMatches[2][$i]) == TRUE) {
			// Middle name, no surname
			$finalList[$i] = array('Name' => $nameMatches[3][$i] . ' ' . $nameMatches[4][$i] . ' ' . $nameMatches[1][$i]);
		} else {
			// All names
			$finalList[$i] = array ('Name' => $nameMatches[3][$i] . ' ' . $nameMatches[4][$i] . ' ' . $nameMatches[1][$i] . ' ' . $nameMatches[2][$i]);
		}
	$finalList[$i]['Name'] = ucwords(strtolower($finalList[$i]['Name']));
	}
	
	// Address search
	preg_match_all("~Address: (.+)?\n~", $fileContent, $addressMatches);
	
//	echo "Here's an output of matched addresses<br/>";
//	print_r($addressMatches);
	
	// Counts addresses and assigned them to the finalList array
	for ($i = 0; $i < count($addressMatches[1]); $i++) {
		if (empty($addressMatches[1][$i]) == TRUE) {
			$finalList[$i]['Address'] = 'Address not listed.';
		} else {
			$finalList[$i]['Address'] = ucwords(strtolower($addressMatches[1][$i]));
		}
	}
	
	// Birthday search
	preg_match_all("~Date of Birth: (\d+\W\d+\W\d+)?~", $fileContent, $birthdateMatches);
	
//	echo "Here's what birthdates were gathered.<br />";
//	print_r($birthdateMatches);
	
	//Adds birthdates and ages to finalList array
	for ($i = 0; $i < count($birthdateMatches[1]); $i++) {
		if (empty($birthdateMatches[1][$i]) == TRUE) {
			$finalList[$i]['Date of birth'] = 'No date of birth listed.';
			$finalList[$i]['Age'] = 'No age listed.';
		} else {
			$finalList[$i]['Date of birth'] = date('Y-m-d H:i:s', strtotime($birthdateMatches[1][$i]));
			$finalList[$i]['Age'] = floor(((time()-strtotime($finalList[$i]['Date of birth']))/(3600*24*365)));
		}
	}
	
	// Finds place of birth and trailing state for address
	preg_match_all("~Place of Birth: (.+)? Date of Birth: (.+)\s?\s?, (..)?~", $fileContent, $birthAddressMatches);
	
//	echo "Here's what I found for the birth place and address state matches:<br />";
//	print_r($birthAddressMatches);
	
	for ($i = 0; $i < count($birthAddressMatches[1]); $i++) {
		// Adds places of birth
		if (empty($birthAddressMatches[1][$i]) == TRUE) {
			$finalList[$i]['Place of birth'] = 'No place of birth listed.';
		} else {
			$finalList[$i]['Place of birth'] = $birthAddressMatches[1][$i];
		}
		// Adds state to address
		if (empty($birthAddressMatches[3][$i]) != TRUE) {
			$finalList[$i]['Address'] = $finalList[$i]['Address'] . ", " . $birthAddressMatches[3][$i];
		}
		
	}
		
	// Arrest date and time search
	preg_match_all("~Arrest Date: (\d+\W\d+\W\d+ \d+:\d+:\d+ (AM|PM))?~", $fileContent, $arrestDateMatches);

//	echo "Here's what arrest dates were gathered.<br />";
//	print_r($arrestDateMatches);
	
	// Adds arrest dates in PHP date formate to finalList array
	for ($i = 0; $i < count($arrestDateMatches[1]); $i++) {
		if (empty($arrestDateMatches[1][$i]) == TRUE) {
			$finalList[$i]['Arrest date'] = 'No arrest date listed.';
		} else {
			$finalList[$i]['Arrest date'] = date('Y-m-d H:i:s', strtotime($arrestDateMatches[1][$i]));
		}
	}
	
	// Charge search
	preg_match_all("~Charge: (.+) Other Chargeable Statute: (.+)?\w?Arrest Type: ?(.+)?~", $fileContent, $chargeMatches);
	
//	echo "Here's the charges I found. <br />";
//	print_r($chargeMatches);

	/*
	 * HOW CAN I ASSIGN CHARGES TO THE PROPER PERSONS?
	 * 
	 * Take the person's name, find the line number in fileContents
	 * of that person's name. 
	 * 
	 * Take the next person's name, find the line number of 
	 * that person's name.
	 * 
	 * Take the charge, find the line number of that charge.
	 * 
	 * If the chargeLN > Person1LN AND chargeLN < Person2LN
	 * assign the charge to Person1
	 * 
	 * Else advance to the next person
	 * 
	 * If there's no entry greater than current Person1,
	 * assign the charges left to Person1
	 * 
	 * 
	 */

	// Explode the string into lines
	$lines = explode("\n", $fileContent);
	$firstNameNumber = 0;
	$secondNameNumber = 0;
	$chargeNum = 0;
	$nameCounter = 0;
	$chargesAdded = "";
	$chargeCounter = 0;
	
	
	for ($i = 0; $i < count($finalList); $i++) {
		$finalList[$i]['Charges'] = array();
	}
	
	// While the charge counter is less than the count of charges
		// Iterate through each line
			// If a line contains the first person's name
				// Assign number to $firstNameNumber
			// If a line has contains the second person's name
				// Assign number to $secondNameNumber
				// break
		
		// Iterate through each line
			// if a line contains the charge AND the line is not in $chargesAdded
				// Assign $chargeNumber
		
		// If charge number is > $firstNameNumber and < $secondNameNumber
			// Assign the charge
			// Add the line number to $chargesAdded
			// +1 charge counter
		// Else if the charge is > $secondNameNumber
			// +1 the name counter
			
	while ($chargeCounter < count($chargeMatches[0])) {
		echo 'Now looking for charge ' . $chargeMatches[0][$chargeCounter] . '(ChargeMatches ' . $chargeCounter . ')<br/>';
		

		if (($nameCounter+1) == count($finalList)) {
			echo 'Adding final charges.<br/>';
			while ($chargeCounter < count($chargeMatches[0])) {
				foreach ($lines as $num => $line) {
					if ((strpos($line, $chargeMatches[0][$chargeCounter]) !== FALSE) && (strpos($chargesAdded, " " . $num . ";") == FALSE)) {
						array_push($finalList[$nameCounter]['Charges'], array($chargeMatches[1][$chargeCounter], $chargeMatches[2][$chargeCounter], $chargeMatches[3][$chargeCounter]));
						$chargeCounter += 1;
					}
				}	
			}
			break;
		}
		
		foreach ($lines as $num => $line) {
			if (strpos($line, $nameMatches[0][$nameCounter]) !== FALSE) {
				$firstNameNumber = $num;
				echo 'Found Name 1 match: ' . $line . '(' . $num . ')<br/>';
			} elseif (strpos($line, substr($nameMatches[0][$nameCounter+1], 0, -1)) !== FALSE) {
				$secondNameNumber = $num;
				echo 'Found Name 2 match: ' . $line . '(' . $num . ')<br/>';
				break;
			}
		}

		foreach ($lines as $num => $line) {
			if ((strpos($chargesAdded, "^{$num};") === FALSE) AND (strpos($line, $chargeMatches[0][$chargeCounter]) !== FALSE)) {
				echo 'Charge found: ' . $line . '(' . $num . ')<br/>';
				echo 'strpos is ' . strpos($chargesAdded, "^{$num};") . '<br/>';
				$chargeNum = $num;
				break;
			} 
		}
		
		if (($chargeNum >= $firstNameNumber) && ($chargeNum <= $secondNameNumber)) {
			array_push($finalList[$nameCounter]['Charges'], array($chargeMatches[1][$chargeCounter], $chargeMatches[2][$chargeCounter], $chargeMatches[3][$chargeCounter]));
			$chargesAdded .= "^" . $num . ";";
			$chargeCounter += 1;
			echo 'Adding charge to ' . $finalList[$nameCounter]['Name'] . '<br/>';
			echo 'Charges added so far: ' . $chargesAdded . "<br/>";
		} elseif ($chargeNum >= $secondNameNumber) {
					$nameCounter += 1;
					echo 'Breaking<br/>';
		}
	}


}


// TESTING HARNESS

// For now, I'm going to pull in a sample text file to test with
// $fileContent = file_get_contents('test.txt');
// print_r($fileContent);
// findEntries($fileContent);
// print_r($finalList);

checkFile($uploadedFileName);
convertToText($uploadedFileName);
findEntries($fileContent);



// echo "Attempting to convert\n";
// convertToText($uploadedFile);


// echo "fileContent is " . $fileContent . "\n";

// echo 'Here is some more debugging info:';
// print_r($_FILES);
// print_r($fileContent);

print_r($finalList);

echo "</pre>";

/* Potenial bugs
 * 	What happens to age field if no birthday listed?
 * 	All fields need to be able to handle blank fields
 * 	Not importing LAWLER III, FRANK HARRISON properly
 */

?>