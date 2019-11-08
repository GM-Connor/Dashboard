<?php 

function pageTitle() {
	global $self;
	return $self['page'];
}

function pageTemplate() {
	global $self;
	return $self['template'];
}

function slugify($text, $sep='-') {
	// replace non letter or digits by -
	$text = preg_replace('~[^\pL\d]+~u', $sep, $text);

	// transliterate
	$text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);

	// remove unwanted characters
	$text = preg_replace('~[^' . $sep . '\w]+~', '', $text);

	// trim
	$text = trim($text, $sep);

	// remove duplicate -
	$text = preg_replace('~-+~', $sep, $text);

	// lowercase
	$text = strtolower($text);

	if (empty($text)) {
		return 'na';
	}

	return $text;
}

?>
