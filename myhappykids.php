<?php
add_action( 'wp_enqueue_scripts', 'myhappykids_google_fonts' );
function myhappykids_google_fonts() {

	wp_enqueue_style( 'google-fonts', '//fonts.googleapis.com/css?family=Rancho|Gudea:400,700', array() );

}
?>