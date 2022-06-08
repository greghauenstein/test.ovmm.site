<?php
/**
 * Role Functions - various things for working with roles
 */

function jb_setup_cpt_permissions( $cpt ) {

  if ( defined( 'ADMIN_ROLES' ) && !empty( ADMIN_ROLES ) && !empty( $cpt ) ):
    foreach ( ADMIN_ROLES as $role_name ):

      $role = get_role( $role_name );

      $role->add_cap( 'read_' . $cpt );
      $role->add_cap( 'edit_' . $cpt );
      $role->add_cap( 'delete_' . $cpt );
      $role->add_cap( 'edit_' . $cpt . 's' );
  		$role->add_cap( 'edit_others_' . $cpt . 's' );
  		$role->add_cap( 'publish_' . $cpt . 's' );
  		$role->add_cap( 'read_private_' . $cpt . 's' );

    endforeach;
  endif;

}
