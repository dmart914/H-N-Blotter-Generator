<?php 

require_once("../includes/functions.php");
require_once("../includes/session.php");
require_once("../includes/mysql_functions.php");

if (!$_GET['id']) {
    redirect_to("dictionary.php");
}

$id = $_GET['id'];
$query = "DELETE FROM dictionary WHERE id = {$id} LIMIT 1";
$result = mysqli_query($connection, $query);

if ($result && mysqli_affected_rows($connection) == 1) {
    $_SESSION['message'] = "Entry deleted.";
    redirect_to("dictionary.php");
} else {
    $_SESSION['message'] = "Entry deletion failed.";
    redirect_to("dictionary.php");
}

?>