<?php
/**
 * Logout - Distrugge la sessione
 */

// Distruggi tutte le variabili di sessione
$_SESSION = array();

// Distruggi la sessione
session_destroy();

// Redirect alla home
success('Logout effettuato con successo!');
redirect('/');
?>
