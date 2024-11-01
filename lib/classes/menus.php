<?php

/**
 * Menu Classes
 *
 * This file handles menu classes.
 *
 * @category   Menu_Logic
 * @package    Menus
 * @author     Travis Smith
 * @license    http://www.opensource.org/licenses/gpl-license.php GPL v2.0 (or later)
 * @link       http://wpsmith.net/
 * @since      1.0.0
 */

if ( !class_exists( 'WPS_WPCM_Menu' ) ) {
class WPS_WPCM_Menu {
    
    /**
     * Constructor method.
     * 
     * @access public
     * @since  1.0.0
     * @param string $field Field slug.
     * @param int $menu_id Menu ID.
     */
    public function __construct( $void = false ) {
        
        add_action( 'init', array( $this, 'register_scripts' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
        add_action( 'load-nav-menus.php', array( $this, 'menu_logic_table' ) );
        // add_action( 'after_menu_locations_table', array( $this, 'after_menu_locations_table' ) );
        
    }
    
    public function register_scripts() {
        $suffix = '.js';
        wp_register_script( 'menu-logic', plugins_url( "js/jquery.menu-logic$suffix", dirname( dirname( __FILE__ ) ) ), array( 'jquery', ), WPS_WPCM_VERSION, false );
    }
    
    public function enqueue_scripts() {
        if ( WPS_WPCM_Utils::is_nav_screen() ) {
            wp_enqueue_script( 'menu-logic' );
            $action = isset( $_GET['action'] ) ? $_GET['action'] : '';
            $args = array(
                'nav_tab' => $this->get_nav_tab(),
                'action'  => $action,
            );
            
            wp_localize_script( 'menu-logic', 'ml', $args );
        }
    }
    
    public function get_nav_tab( $action = 'menu-logic', $label = 'Menu Logic' ) {
        $class = isset( $_GET['action'] ) && $action == $_GET['action'] ? ' nav-tab-active' : '';
        return sprintf( '<a href="%s" class="menu-logic-tab nav-tab %s">%s</a>', add_query_arg( array( 'action' => $action, ), admin_url( 'nav-menus.php' ) ), $class, __( $label, WPS_WPCM_DOMAIN ) );
    }
    
    /**
     * Menu Logic Table
     * 
     */
    public function menu_logic_table() {
        require_once( 'views/nav-menus-logic.php' );
    }
    
}
}
