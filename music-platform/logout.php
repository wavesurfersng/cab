<?php
/**
 * Logout Handler
 */
require_once 'includes/functions.php';

session_destroy();
redirect('index.php');
?>
