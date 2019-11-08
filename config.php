<?php
ini_set( "display_errors", true );
define( "CLASS_PATH", "classes" );
define( "THEME", isset($_GET['theme']) ? $_GET['theme'] : 'default' );
define( "TEMPLATE_PATH", 'templates/' . THEME );

require( CLASS_PATH . "/Twitter.php" );
require( CLASS_PATH . "/Settings.php" );

function handleException( $exception ) {
	echo "Sorry, a problem occurred. Please try later.";
	error_log( $exception->getMessage() );
}

// set_exception_handler( 'handleException' );
?>