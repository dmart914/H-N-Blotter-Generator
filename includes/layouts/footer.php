</div> <!-- End "CONTENT" div -->
<div id="foot">Version <?php echo output_version(); ?><br />&copy;&nbsp;<?php echo date("Y"); ?>&nbsp;Herald and News</div>
    </body>
</html>

<?php
    // 5. Close database connection
    
    if (isset($connection)) {
        mysqli_close($connection);
    }
?>