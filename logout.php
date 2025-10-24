<?php
require_once 'helpers.php';
require_once 'auth.php';

logout_user();
flash_set('success', 'Anda telah logout. Sampai jumpa!');
header('Location: login.php');
exit;
