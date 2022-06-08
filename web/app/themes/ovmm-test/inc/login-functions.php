<?php
/**
 * Login Functions - various things regarding logging in
 */

// Replace login logo with the projects logo
function jb_login_logo() {
	?>
  <style type="text/css">
    #login h1 a {
      background-image: url("<?php echo JB_IMG_URL . '/jb-admin-bar-logo.png'; ?>") !important;
			background-size: contain !important;
      width: 320px !important;
      height: 160px !important;
    }
  </style>
<?php
}
add_action( 'login_head', 'jb_login_logo' );


// Update the login link
function jb_login_link() {
	return JB_SITE_URL;
}
add_action( 'login_headerurl', 'jb_login_link' );


// Replace login tooltip
function jb_login_tooltip() {
	return JB_SITE_NAME;
}
add_action( 'login_headertitle', 'jb_login_tooltip' );

