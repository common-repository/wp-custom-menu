<?php

/**
 * Menu Logic Core
 *
 * This file handles core classes.
 *
 * @category   Menu_Logic
 * @package    Core
 * @author     Travis Smith
 * @license    http://www.opensource.org/licenses/gpl-license.php GPL v2.0 (or later)
 * @link       http://wpsmith.net/
 * @since      1.0.0
 */

if ( !class_exists( 'WPS_WPCM_Utils' ) ) {
/**
 * Utility Class
 *
 * Debugging base class.
 * 
 * @access public
 * @since  0.1.0
 */
class WPS_WPCM_Utils {
    /**
     * Determines whether on Nav Menu Screen
     * 
     * @return bool Whether current screen is nav-menus.
     */
    public static function is_nav_screen() {
        $screen = get_current_screen();
        return ( 'nav-menus' == $screen->base );
    }
    
    /**
     * Debugging Function: Prints Variable on Page.
     * 
     * @param  mixed  $args Various to be printed.
     * @param  string $name Name of Args to be printed as header.
     */
    public static function pr( $args, $name = '', $echo = true ) {
        // Short circuit function
        if ( apply_filters( 'gs_debug_off', false ) ) return;

        if ( ! current_user_can( 'manage_options' ) ) return;
        $current_user = wp_get_current_user();

        if ( 'wpsmith' !== $current_user->user_login ) return;

        // Output Debug information
        $o = '';
        if ( '' != $name )
            $o .= '<strong>' . $name . '</strong><br />';
        $o .= '<pre>' . htmlspecialchars( print_r( $args, true ) ) . "</pre>\n";
        if ( $echo )
            echo $o;
        else
            return $o;
    }

    /**
     * Custom function_exists plugin that returns function name if function exists.
     * 
     * @since 0.1.0
     * @access public
     * @param string $f Function name.
     * @return bool   False if function does not exist.
     * @return string Function name if function does exist.
     */
    public static function function_exists( $f ) {
        if ( function_exists( $f ) )
            return $f;
        else
            return false;
    }
}
}

if ( !class_exists( 'WPS_WPCM_Custom_Menu_Item' ) && class_exists( 'WPS_WPCM_Utils' ) ) {
/**
 * Abstract Custom Menu Item Class
 *
 * Use display method to output on the edit menu admin page.
 * 
 * @uses WPS_WPCM_Utils
 * @access public
 * @since  0.1.0
 */
abstract class WPS_WPCM_Custom_Menu_Item extends WPS_WPCM_Utils {
    
    /**
     * Field slug.
     * 
     * @access public
     * @since  1.0.0
     * @var string $var Field slug.
     */
    public $field = 'abstract';
    
    /**
     * Field name.
     * 
     * @access public
     * @since  1.0.0
     * @var string $var Field name.
     */
    public $field_name = 'abstract';
    
    /**
     * Item description.
     * 
     * @access public
     * @since  1.0.0
     * @var string $var Description.
     */
    public $description;
    
    /**
     * Child class name.
     * 
     * @access public
     * @since  1.0.0
     * @var string $var Class name.
     */
    protected $child;
    
    protected $children = array(
        'logic_basic'  => 'MLCMI_Logic_Basic',
        'logic'        => 'MLCMI_Logic',
        'logic_parent' => 'MLCMI_Logic_Parent',
        'logic_type'   => 'MLCMI_Logic_Type',
    );
    
    protected static $edit_nav_menu_walker = 'WPS_WPCM_Walker_Nav_Menu_Edit';
    protected static $nav_menu_walker = 'WPS_WPCM_Walker_Nav_Menu';
    protected $menu_id;
    
    /**
     * Constructor method.
     * 
     * @access public
     * @since  1.0.0
     * @param string $field Field slug.
     * @param int $menu_id Menu ID.
     */
    public function __construct( $void = false, $field = '', $menu_id = '' ) {
        $c           = get_called_class();
        $this->child = $c;

        // Set field slug
        if ( '' != $field )
            $this->field = sanitize_title_with_dashes( $field );
        $this->menu_id = $menu_id;
        
        // Localize Field name
        $this->field_name = __( $this->field_name, WPS_WPCM_DOMAIN );
        
        // Abort intialization override
        if ( false !== $void )
            return;
        
        // Allow over-rides to become compatible with others.
        $c::$edit_nav_menu_walker = apply_filters( 'ml_walker_nav_menu_edit', $c::$edit_nav_menu_walker, $menu_id, $field );
        $c::$nav_menu_walker      = apply_filters( 'ml_walker_nav_menu', $c::$nav_menu_walker, $menu_id, $field );
        
        // Add custom input field to menu item
        add_filter( 'wp_setup_nav_menu_item', array( $this, 'add_field' ) );

        // Save menu custom fields
        add_action( 'wp_update_nav_menu_item', array( $this, 'update_field' ), 10, 3 );
        
        // Edit Nav Menu Walker
        add_filter( 'wp_edit_nav_menu_walker', array( $this, 'edit_nav_menu_walker'), 999, 2 );
        
        // Add Walker Class
        add_filter( 'wp_nav_menu_args', array( $this, 'wp_nav_menu_args' ), 999 );
        
        // Add Manage Nav Columns
        add_filter( 'manage_nav-menus_columns', array( $this, 'menus_columns' ), 999 );
        
        // Output Display
        add_action( 'wpcm_custom_menu_item_edit_display', array( $this, 'edit_display' ), 10, 2 );
        
        // Maybe Validate
        add_action( 'ml_validate', array( $this, 'validate' ), 10, 6 );
        
        // Clean up ourself
        // add_action( 'delete_post', array( $this, 'remove_meta' ) );
        
        // Clear Gantry Menu Cache
        add_action( 'init', array( $this, 'clear_gantry_menu_cache' ) );
        
        // Allow Child class to hook here to remove anything
        if ( method_exists( $this, 'loaded' ) )
            add_action( 'init', array( $this, 'loaded' ) );
            
        // Add init hook
        add_action( 'init', array( $this, 'init' ) );
        
    }
    
    /**
     * Custom Menu Init Hook
     * 
     */
    public function init() {
        do_action( 'ml_custom_menu_item_init', $this->field );
        do_action( 'ml_custom_menu_item_init_' . $this->field );
    }
    
    /**
     * Output ending of Custom Menu Addition.
     * Optionally add description between </label> and </p>
     * 
     * @access public
     * @since  1.0.0
     * @param string $description Description to output.
     */
    public function start() {
        printf( '<p class="field-%s description description-wide">', $this->field );
    }
    
    /**
     * Output ending of Custom Menu Addition.
     * 
     * @access public
     * @since  1.0.0
     * @param string $description Description to output.
     */
    public function start_label( $item_id ) {
        printf( '<label for="edit-menu-item-%s-%s">', $this->field, $item_id );
    }
    
    /**
     * Display function for edit menu.
     * Calls display() method.
     * 
     * @access public
     * @since  1.0.0 
     * @param int $item_id Item ID.
     * @param NavMenuObj $item Navigation post object.
     */
    public function edit_display( $item_id, $item ) {
        $this->start();
        $this->start_label( $item_id );
        
        $this->display( $item_id, $item );
        
        $this->end_label();
        $this->description();
        $this->end();
    }
    
    /**
     * Outputs description.
     * 
     * @access public
     * @since  1.0.0 
     */
    public function description() {
        printf( '<span class="description">%s</span>', $this->description );
    }
    
    /**
     * Output ending label tag </label>.
     * 
     * @access public
     * @since  1.0.0 
     */
    public function end_label() {
        echo '</label>';
    }
    
    /**
     * Output ending tag </p> of Custom Menu Addition.
     * 
     * @access public
     * @since  1.0.0 
     */
    public function end() {
        echo '</p>';
    }
    
    /**
     * Manage columns/checkboxes for nav menu items.
     *
     * @access public
     * @since  1.0.0 
     * @param  array $menu_item Array of nav menu items..
     * @return array $menu_item Modified array of nav menu items..
    */
    public function menus_columns( $columns ) {
        $columns[ self::get_field() ] = self::get_field_name();
        return $columns;
    }
    
    /**
     * Add custom fields to $item nav object
     * in order to be used in custom Walker
     *
     * @access public
     * @since  1.0.0 
     * @param  WP_Post $menu_item Menu item.
     * @return WP_Post $menu_item Modified menu item.
    */
    public function add_field( $menu_item ) {
        $field = $this->field;
// self::pr( $field );

        $menu_item->{$field} = get_post_meta( $menu_item->ID, '_menu_item_' . $field, true );

        return $menu_item;

    }
    
    /**
     * Validates conditional function name.
     * 
     * @access public
     * @since  1.0.0
     * @param string $value Value entered.
     * @param string $field Field Name.
     * @param int $menu_item_db_id DB ID.
     * @param array $request Array of request.
     * @param array $args Array of args.
     * 
     * @return mixed Validated value.
     */
    public function validate( $value, $field, $menu_item_db_id, $request, $menu_id, $args ) {
        return $value;
    }
    
    /**
     * Save menu custom fields
     *
     * @access public
     * @since  1.0.0.0
     * @param int $menu_id Menu ID.
     * @param int $menu_item_db_id Menu item ID.
     * @param array $args Array of menu args.
     * @return void
    */
    public function update_field( $menu_id, $menu_item_db_id, $args ) {
        $field = $this->field;

        // Check if element is properly sent
        if ( isset( $_REQUEST['menu-item-' . $field ] ) && isset( $_REQUEST['menu-item-' . $field ][ $menu_item_db_id ] ) && is_array( $_REQUEST['menu-item-' . $field ] ) ) {
            $value = apply_filters( 'ml_validate', $_REQUEST['menu-item-' . $field ][ $menu_item_db_id ], $field, $menu_item_db_id, $_REQUEST, $menu_id, $args );
            update_post_meta( $menu_item_db_id, '_menu_item_' . $field, $value );
        } else {
            delete_post_meta( $menu_item_db_id, '_menu_item_' . $field );
        }

    }
    
    /**
     * Getter method for field name.
     *
     * @access public
     * @since  1.0.0 
     * @return string|object Class name OR object.
     * @return string Field name.
    */
    public static function get( $var, $c = '' ) {
        if ( '' == $c ) {
            $c = get_called_class();
        } elseif( is_object( $c ) ) {
            return $c->{$var};
            // $class = get_called_class();
            // $a     = (array)$c;
            // return $a[ $class::${$var} ];
        }

        $temp = new $c( 'void' );

        return $temp->{$var};
    }
    
    /**
     * Getter method for field name.
     *
     * @access public
     * @since  1.0.0 
     * @return string|object Class name OR object.
     * @return string Field name.
    */
    public static function get_field_name( $c = '' ) {
        return self::get( 'field_name', $c );
    }
    
    /**
     * Getter method for field slug.
     *
     * @access public
     * @since  1.0.0 
     * @return string|object Class name OR object.
     * @return string Field slug.
    */
    public static function get_field( $c = '' ) {
        return self::get( 'field', $c );
        // if ( '' == $c )
            // $c = get_called_class();
        // elseif( is_object( $c ) ) {
             // $class = get_called_class();
            // $a = (array)$c;
            // return $a[ $class::$field ];
        // }
        
        // return $c::$field;
    }
    
    /**
     * Set field value
     * 
     * @access public
     * @since  1.0.0 
     * @param string $value Field name.
     * @param string $c Class name.
     */
    public static function set_field( $value, $c = '' ) {
        if ( '' == $c )
            $c = get_called_class();
       
        $c::$field = $value;
        
    }
    
    /**
     * Define new Walker edit
     *
     * @access public
     * @since  1.0.0 
     * @param string $walker Walker class name.
     * @param int $menu_id Menu ID.
     * @return string Walker class name
    */
    public function edit_nav_menu_walker( $walker, $menu_id ) {
        
        return self::$edit_nav_menu_walker;

    }
    
    /**
     * Filter nav menu args.
     * 
     * @access public
     * @since  1.0.0 
     * @param array $args Args.
     * @return array Possible modified Args.
     */
    public function wp_nav_menu_args( $args ) {
        $walkerclass = self::$nav_menu_walker;
        // if ( isset( $args['walker'] ) && '' === $args['walker'] )
            // $args['walker'] = new $walkerclass;
        $args['walker'] = new $walkerclass; 
        
        return $args;
    }
    
    /**
     * Remove meta data for menu item.
     * 
     * @access public
     * @since 0.1.0
     * @param int $post_id Nav menu item post ID.
     */
    public function remove_meta( $post_id ) {
        $c = self::$child;
        if( is_nav_menu_item( $post_id ) )
            delete_post_meta( $post_id, '_menu_item_' . $c::$field );
    }
    
    /**
     * Compatibility fix for Gantry Framework
     * 
     * @access public
     * @since 0.1.0 
     */
    public function clear_gantry_menu_cache() {
        if( class_exists( 'GantryWidgetMenu' ) ) {
            GantryWidgetMenu::clearMenuCache();
        }
    }
    
    /**
     * Actual display method
     * 
     * @param int $item_id Item ID.
     * @param NavMenuObj $item Navigation post object.
     */
    abstract function display( $item_id, $item );
    
}
}

if ( !class_exists( 'MLCMI_Text' ) && class_exists( 'WPS_WPCM_Custom_Menu_Item' ) ) {
/**
 * Text Class
 *
 * @uses   WPS_WPCM_Custom_Menu_Item
 * @access public
 * @since  0.1.0
 */
abstract class MLCMI_Text extends WPS_WPCM_Custom_Menu_Item {
    
    /**
     * Text HTML Markup output.
     * 
     * @access public
     * @since  0.1.0
     * @param NavMenuObj $item Navigation post object.
     */
    public static function text( $item, $classes = '' ) {
        $c     = get_called_class();
        $field = $c::get_field();
        
        printf( '<input type="text" id="edit-menu-item-%1$s-%2$s" class="edit-menu-item-%1$s %4$s" name="menu-item-%1$s[%2$s]" value="%3$s" />', $field, $item->ID, $item->{$field}, $classes );
    }
    
}
}

if ( !class_exists( 'MLCMI_Checkbox' ) && class_exists( 'WPS_WPCM_Custom_Menu_Item' ) ) {
/**
 * Checkbox Class
 *
 * @uses   WPS_WPCM_Custom_Menu_Item
 * @access public
 * @since  0.1.0
 */
abstract class MLCMI_Checkbox extends WPS_WPCM_Custom_Menu_Item {
    
    /**
     * Checkbox HTML Markup output.
     * 
     * @access public
     * @since  0.1.0
     * @param NavMenuObj $item Navigation post object.
     */
    public static function checkbox( $item ) {
        $c     = get_called_class();
        $field = $c::get_field();
        $style = is_rtl() ? 'margin-left: 5px;' : 'margin-right: 5px;';

        printf( '<input type="checkbox" style="%4$s" id="edit-menu-item-%1$s-%2$s" class="edit-menu-item-%1$s" name="menu-item-%1$s[%2$s]" value="1" %3$s />', $field, $item->ID, checked( $item->{$field}, 1, false ), $style );
    }
    
}
}

if ( !class_exists( 'MLCMI_Select' ) && class_exists( 'WPS_WPCM_Custom_Menu_Item' ) ) {
/**
 * Select Class
 *
 * @uses   WPS_WPCM_Custom_Menu_Item
 * @access public
 * @since  0.1.0
 */
abstract class MLCMI_Select extends WPS_WPCM_Custom_Menu_Item {
    
    /**
     * Checkbox HTML Markup output.
     * 
     * @access public
     * @since  0.1.0
     * @param NavMenuObj $item Navigation post object.
     */
    public static function select( $item, $options, $none = false ) {
        $c     = get_called_class();
        $field = $c::get_field();

        // printf( '<select id="edit-menu-item-%1$s-%2$s" class="chosen-select edit-menu-item-%1$s" name="menu-item-%1$s[%2$s]">', $field, $item->ID );
        printf( '<select id="edit-menu-item-%1$s-%2$s" class="edit-menu-item-%1$s" name="menu-item-%1$s[%2$s]">', $field, $item->ID );
            if ( $none )
                printf( '<option value="">%s</option>', __( $none, WPS_WPCM_DOMAIN ) );
            foreach ( $options as $value => $name )
                printf( '<option value="%s"%s>%s</option>', $value, selected( $item->{$field}, $value, false ), __( $name, WPS_WPCM_DOMAIN ) );
        echo '</select>';
    }
    
    /**
     * Multiselect HTML Markup output.
     * 
     * @access public
     * @since  0.1.0
     * @param NavMenuObj $item Navigation post object.
     */
    public static function multiselect( $item, $options, $placeholder = '' ) {
        $c     = get_called_class();
        $field = $c::get_field();

        // $temp = new $c;
        // $field = $temp->field;
        $placeholder = '' == $placeholder ? __( 'Select', WPS_WPCM_DOMAIN ) : $placeholder;
        $o = array();
        printf( '<select multiple data-placeholder="%3$s" id="edit-menu-item-%1$s-%2$s" class="chosen-select edit-menu-item-%1$s" name="menu-item-%1$s[%2$s][]" style="width: 250px;">', $field, $item->ID, $placeholder );
            foreach ( (array)$options as $value => $name ) {
// wp_die( self::pr( array( 'c' =>$c, 'field' => $field, 'item' => $item, 'item->field' => $item->{$field}, 'value' => $value, 'options' => $options, ) ) );
                $values = !empty( $item->{$field} ) ? $item->{$field} : array();
                $selected = in_array( $value, (array)$values ) ? ' selected="selected"' : '';
// echo '</select>'; wp_die( 'test'.self::pr( array( $item, $selected, $item->{$field} ) ) );
                // printf( '<option value="%s"%s>%s</option>', $value, selected( $c::get_field( $item ), $value, false ), $name );
                printf( '<option value="%s"%s>%s</option>', $value, $selected, $name );
                $o[] = array( 'opt_value' => $value, 'selected' => $selected, 'name' => $name, '$item->{$field}' => $item->{$field} );
            }
        echo '</select>';
// wp_die( self::pr( array( 'c' =>$c, 'field' => $field, 'item' => $item, ) ) );
    }
    
    /**
     * Instantiates Chosen
     * 
     * @access public
     * @since  0.1.0
     * @uses WPS_WPCM_Chosen
     */
    public static function add_chosen() {
        if ( WPS_WPCM_Utils::is_nav_screen() ) {
            global $_ml_chosen;
            $_ml_chosen = new WPS_WPCM_Chosen();
        }
    }
    
}
}

if ( !class_exists( 'WPS_WPCM_Scripts' ) ) {
/**
 * Base Scripts Class
 *
 * Methods to wrap inline JavaScript with script tags
 * and determine suffix.
 *
 * @access public
 * @since  0.1.0
 */
class WPS_WPCM_Scripts {
    
    /**
     * Prepares suffix to have .min attached conditionally
     * based on SCRIPT_DEBUG or WP_DEBUG.
     * 
     * @param string $type Whether JS/CSS.
     * @return string 
     */
    public static function suffix( $type = 'css' ) {
        $suffix = ( ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) || ( defined( 'WP_DEBUG' ) && WP_DEBUG ) ) ? '.' . $type : '.min.' . $type;
        return $suffix;
    }
    
    /**
     * Wrap JavaScript with inline tags
     * 
     * @param string $script JavaScript.
     */
    public function script_tags( $script, $type = 'text/javascript' ) {
        printf( '<script type="%s">%s</script>', $type, $script );
    }
    
}
}

if ( !class_exists( 'WPS_WPCM_Chosen' ) && class_exists( 'WPS_WPCM_Scripts' ) ) {
/**
 * Chosen jQuery Select Class
 *
 * @uses WPS_WPCM_Scripts
 * @access public
 * @since  0.1.0
 */
class WPS_WPCM_Chosen extends WPS_WPCM_Scripts {
    
    public $selector;
    public $args;
    
    public function __construct( $selector = '.chosen-select', $args = array() ) {
        $this->selector = $selector;
        $this->args     = !empty( $args ) ? $args : array(
            'no_results_text' => __( 'Oops, nothing found!', WPS_WPCM_DOMAIN ),
            'width'           => '100%',
        );
        
        add_action( 'admin_print_styles-nav-menus.php', array( $this, 'enqueue_styles' ) );
        add_action( 'admin_print_styles-nav-menus.php', array( $this, 'print_styles' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
        add_action( 'admin_footer-nav-menus.php', array( $this, 'chosen_init' ) );
    }
    
    // @todo Add chosen
    public function enqueue_scripts( $hook_suffix ) {
        $suffix = WPS_WPCM_Chosen::suffix( 'js' );
        wp_enqueue_script( 'menu-logic-chosen', plugins_url( "js/chosen.jquery$suffix", dirname( dirname( __FILE__ ) ) ), array( 'jquery', ), WPS_WPCM_VERSION, false );
    }
    
    public function print_styles() {
        ?>
        <style>.chosen-choices li input { min-height: 25px; }</style>
        <?php
    }
    
    public function enqueue_styles() {
        $suffix = WPS_WPCM_Chosen::suffix();
        wp_enqueue_style( 'menu-logic-chosen-css', plugins_url( "css/chosen$suffix", dirname( dirname( __FILE__ ) ) ), array(), WPS_WPCM_VERSION );
    }
    
    public function chosen_init() {
        $this->script_tags( sprintf( 'jQuery("%s").chosen(%s);', $this->selector, json_encode( $this->args ) ) );
    }
    
}
}

if ( ! class_exists( 'WPS_Enforcer' ) ) {
/**
 * Enforcer class.
 * Checks & ensures that classes contain constants & properties.
 *
 * If a constant's value is still set as 'abstract', an exception is thrown.
 * If a property's value is still unset, an exception is thrown.
 * To use, place inside __construct() in abstract class:
 *      $child = get_called_class();
 *      WPS_Enforcer::__add( __CLASS__, $child );
 */
class WPS_Enforcer {
    /**
     * Adds the Reflection Class & calls the check method
     * on class's the properties & constants.
     * 
     * @uses WPS_Enforcer::check()
     * @param string $c      Current abstracted class.
     * @param string $forced Class being enforced.
     */
    public static function __add( $c, $forced ) {
        $reflection = new ReflectionClass( $c );
        $constantsForced = $reflection->getConstants();
        WPS_Enforcer::check( $forced, $constantsForced, 'constant' );
        
        $propertiesForced = $reflection->getProperties();
        WPS_Enforcer::check( $forced, $propertiesForced, 'property' );
    }
    
    /**
     * Checks class to enforce whether a constant or property has been set.
     * 
     * @param string $c      Abstracted class.
     * @param string $forced Class being enforced.
     * @param string $type   Whether a constant or property.
     */
    public static function check( $c, $forced, $type ) {
        foreach ( $forced as $t => $v ) {
            switch ( $type ) {
                case 'constant':
                    if ( 'abstract' == constant("$c::$t") ) {
                        throw new Exception( "Undefined $t in " . (string) $c );
                    }
                    break;
                case 'property':
                    $var = $v->name;
                    if ( 'abstract' === $c::$$var ) {
                        throw new Exception( 'Undefined $' .$var . ' in ' . (string) $c );
                    }
                    break;
            }
        }
    }
}
}