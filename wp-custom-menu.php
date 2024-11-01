<?php

/**
 * Plugin Name: WP Custom Menu
 * Plugin URI: http://wpsmith.net
 * Description: Easily, conditionally control menu items.
 * Version: 0.1.0
 * Author: Travis Smith
 * Author URI: http://wpsmith.net
 *
 * License:
 * Copyright (C) 2012  Travis Smith
 * 
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 * 
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 * 
 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

/** Set Constants */
define( 'WPS_WPCM_DOMAIN', 'wp-custom-menu' );
define( 'WPS_WPCM_VERSION', '0.1.0' );
define( 'WPS_WPCM_PLUGIN_FILE', plugin_basename( __FILE__ ) );
define( 'WPS_WPCM_PLUGIN_BASENAME', basename( __FILE__ ) ); // Not used at the moment
define( 'WPS_WPCM_PLUGIN_DIR', dirname( __FILE__ ) ); // Not used at the moment
define( 'WPS_WPCM_PLUGIN_DIR_SLUG', basename( WPS_WPCM_PLUGIN_DIR ) ); // Not used at the moment
define( 'WPS_WPCM_DONATE_URL', 'http://wpsmith.net/donation' );

/** Require Files */
require_once( 'lib/functions.php' );
require_once( 'lib/classes/walker.php' );
require_once( 'lib/classes/core.php' );
require_once( 'lib/classes/menu-items.php' );

/** Instantiate new menu fields */
// Menu Items Logic
$_menu_logic_type   = new MLCMI_Logic_Type();
$_menu_logic_basic  = new MLCMI_Logic_Basic();
$_menu_logic_custom = new MLCMI_Logic_Custom();
$_menu_logic_cb     = new MLCMI_Logic_Parent();

add_filter( 'plugin_action_links', 'ml_donate', 10, 2 );
/**
 * Add Menus & Donate Action Link.
 * 
 * @param array $links Array of links.
 * @param string $file Basename of plugin.
 * 
 * @return array $links Maybe modified array of links.
 */
function ml_donate( $links, $file ) {
    if ( $file == WPS_WPCM_PLUGIN_FILE ) {
        array_unshift( $links, sprintf( '<a href="%s">%s</a>', admin_url( 'nav-menus.php' ), __( 'Menus', WPS_WPCM_DOMAIN ) ) );
        array_push( $links, sprintf( '<a href="%s" target="_blank">%s</a>', WPS_WPCM_DONATE_URL, __( 'Donate', WPS_WPCM_DOMAIN ) ) );
    }
    return $links;
}