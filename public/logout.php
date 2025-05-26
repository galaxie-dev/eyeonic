//<?php
//require_once '../includes/auth.php';
//logoutUser();
//session_start();
session_unset();
session_destroy();
header("Location: login.php");
exit;
