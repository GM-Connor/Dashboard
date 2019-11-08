<?php

// Prerequisites
//-----------------------------------------------

require( "config.php" );
require( "functions.php");



// Url and pathing information
//-----------------------------------------------

$self = [
	'url' => "{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}",
	'host' => "{$_SERVER['HTTP_HOST']}",
	'location' => $_SERVER['REQUEST_URI'],
	'breadcrumbs' => explode('/', trim(explode('?', preg_replace('/\\/\\/+/i', '/', $_SERVER['REQUEST_URI']))[0], '/')),
	'get' => $_GET,
	'post' => $_POST
];

// Sanitize $self
if (sizeof($self['breadcrumbs']) == 1 && !$self['breadcrumbs'][0]) {
	$self['breadcrumbs'] = array();
}



// Level-0 page (home)
//-----------------------------------------------

if (sizeof($self['breadcrumbs']) == 0) {
	viewHome();
}



// Level-1 page
//-----------------------------------------------

elseif (sizeof($self['breadcrumbs']) == 1) {

	// Twitter page
	if ($self['breadcrumbs'][0] == 'twitter') {
		viewTwitter();
	}

	// Settings page
	else if ($self['breadcrumbs'][0] == 'settings') {
		viewSettings();
	}

	// Exceptions
	else {
		view404();
	}

}



// Exceptions
//-----------------------------------------------

else {
	view404();
}





// Template mapping
//-----------------------------------------------

// 404
function view404() {
	global $self;
	require( TEMPLATE_PATH . "/view404.php" );
}

// Home
function viewHome() {
	global $self;
	$self['page'] = 'Dashboard';
	$self['template'] = 'home';
	require( TEMPLATE_PATH . "/viewHome.php" );
}

// Twitter
function viewTwitter() {
	global $self;

	$self['page'] = 'Twitter';
	$self['template'] = 'twitter';
	$self['settings'] = new Settings();
	$self['twitter'] = new Twitter( $self['settings'] );

	if (isset($_POST['action'])) {
		if ($_POST['action'] == 'mark_as_read' && isset($_POST['user_id']) && isset($_POST['tweet_id']))
			$self['twitter']->addToUserReadTweets($_POST['user_id'], $_POST['tweet_id']);
	}

	require( TEMPLATE_PATH . "/viewTwitter.php" );
}

// Settings
function viewSettings() {
	global $self;

	$self['page'] = 'Settings';
	$self['template'] = 'settings';
	$self['settings'] = new Settings();

	if (isset($_POST['action'])) {
		if ($_POST['action'] == 'update' && isset($_POST['name']) && isset($_POST['value']) && isset($_POST['new_value']))
			$self['settings']->editSetting($_POST['name'], $_POST['new_value'], $_POST['value']);
		else if ($_POST['action'] == 'delete' && isset($_POST['name']) && isset($_POST['value']))
			$self['settings']->removeSetting($_POST['name'], $_POST['value']);
		else if ($_POST['action'] == 'add' && isset($_POST['name']) && isset($_POST['value']))
			$self['settings']->addSetting($_POST['name'], $_POST['value']);
	}

	require( TEMPLATE_PATH . "/viewSettings.php" );
}




?>