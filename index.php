<?php
session_start();

if (isset($_SESSION['usuario_id'])) {
    header('Location: dashboard.php');
    exit;
} else {
    header('Location: auth/login.php');
    exit;
}
