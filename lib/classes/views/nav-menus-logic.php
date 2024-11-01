<?php
// Nav Menu Locations, Nav Menus, & Current Screen
global $locations, $menu_locations, $num_locations, $nav_menus, $menu_count, $add_new_screen, $locations_screen;
?>
<div class="menu-logic-wrap" style="display:none;">
<table class="widefat" cellspacing="0" id="menu-logic-table">
  <thead>
    <tr>
      <th scope="col" class="manage-column column-id" style="max-width:10%;"><?php __( 'Menu ID', WPS_WPCM_DOMAIN ); ?></th>
      <th scope="col" class="manage-column column-name" style="max-width:20%;"><?php __( 'Menu Name', WPS_WPCM_DOMAIN ); ?></th>
      <?php if ( $num_locations ) : ?>
      <th scope="col" class="manage-column column-locations" style="max-width:20%;"><?php __( 'Theme Location', WPS_WPCM_DOMAIN ); ?></th>
      <?php endif; ?>
      <th scope="col" class="manage-column column-logic" style="max-width:50%;"><?php __( 'Logic', WPS_WPCM_DOMAIN ); ?></th>
    </tr>
  </thead>
  <tfoot>
    <tr>
      <th scope="col" class="manage-column column-id"><?php __( 'Menu ID', WPS_WPCM_DOMAIN ); ?></th>
      <th scope="col" class="manage-column column-name"><?php __( 'Menu Name', WPS_WPCM_DOMAIN ); ?></th>
      <?php if ( $num_locations ) : ?>
      <th scope="col" class="manage-column column-locations"><?php __( 'Theme Location', WPS_WPCM_DOMAIN ); ?></th>
      <?php endif; ?>
      <th scope="col" class="manage-column column-logic"><?php __( 'Logic', WPS_WPCM_DOMAIN ); ?></th>
    </tr>
  </tfoot>
  <tbody class="menu-logic">
    <?php foreach ( $nav_menus as $_menu ) { ?>
        <tr id="menu-logic-row">
            <td class="menu-logic-id" style="width:10%;">
                <strong><?php echo $_menu->term_id; ?></strong>
                <div class="logic-row-links">
                    <span class="logic-edit-menu-link">
                        <a href="<?php echo esc_url( add_query_arg( array( 'action' => 'edit', 'menu' => $_menu->term_id ), admin_url( 'nav-menus.php' ) ) ); ?>">
                            <?php _ex( 'Edit', 'menu' ); ?>
                        </a>
                    </span>
                </div><!-- .logic-row-links -->
            </td>
            <td class="menu-logic-name" style="width:20%;"><strong><?php echo $_menu->name; ?></strong></td>
      <?php if ( $num_locations ) : ?>
            <td class="menu-logic-locations" style="width:20%;">
                <select name="menu-logic[location][<?php echo $_menu->term_id; ?>]" id="locations-<?php echo $_menu->term_id; ?>">
                    <option value="0"><?php printf( '&mdash; %s &mdash;', esc_html__( 'Select a Menu' ) ); ?></option>
                    <?php foreach ( $locations as $location => $name ) : ?>
                        <?php $selected = isset( $menu_locations[$location] ) && $menu_locations[$location] == $_menu->term_id; ?>
                        <option <?php if ( $selected ) echo 'data-orig="true"'; ?> <?php selected( $selected ); ?> value="<?php echo $_menu->term_id; ?>">
                            <?php echo wp_html_excerpt( $name, 40, '&hellip;' ); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                
            </td><!-- .menu-location-menus -->
      <?php endif; ?>
            <td class="menu-logic-id" style="width:40%;"><?php __( 'LOGIC', WPS_WPCM_DOMAIN ); ?></td>
        </tr><!-- #menu-locations-row -->
    <?php } // foreach ?>
  </tbody>
</table>
</div>