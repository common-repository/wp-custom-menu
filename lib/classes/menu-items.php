<?php

/**
 * Menu Item Classes
 *
 * This file handles menu item classes.
 *
 * @category   Menu_Logic
 * @package    Menus
 * @author     Travis Smith
 * @license    http://www.opensource.org/licenses/gpl-license.php GPL v2.0 (or later)
 * @link       http://wpsmith.net/
 * @since      1.0.0
 */

if ( !class_exists( 'MLCMI_Logic_Type' ) ) {
/**
 * Basic Logic Code Class
 *
 * Use the construct magic method to name field.
 * Use display method to output on the edit menu admin page.
 *
 * @uses   MLCMI_Select
 * @access public
 * @since  0.1.0
 */
class MLCMI_Logic_Type extends MLCMI_Select {
    
    /**
     * Field name.
     * 
     * @access public
     * @since  1.0.0
     * @var string $var Field name/slug.
     */
    public $field = 'logic_type';
    
    /**
     * Field name.
     * 
     * @access public
     * @since  1.0.0
     * @var string $var Field name.
     */
    public $field_name = 'Menu Logic Type';
    
    /**
     * Class name.
     * 
     * @access public
     * @since  1.0.0
     * @var string $var Class name.
     */
    public $c = 'MLCMI_Logic_Type';
    
    /**
     * Add checks to the start_el() & end_el() Walker methods.
     * 
     * Call parent::_construct().
     */
    public function loaded() {
        add_action( 'ml_walker_nav_menu_start_el_pre', array( $this, 'start_el' ), 1, 6 );
    }
    
    /**
     * Add checks to the start_el() & end_el() Walker methods.
     * 
     * Call parent::_construct().
     */
    public function start_el( $pre, $output, $item, $depth, $args, $id = 0 ) {
        // Add checks
        $f = $item->{$this->field};
        $class = isset( $this->children[ $f ] ) ? $this->children[ $f ] : '';
        $obj = new $class( 'void' );
        if ( is_object( $obj ) ) {
            add_action( 'ml_walker_nav_menu_end_el_pre', array( $obj, 'el_pre' ), 10, 6 );
            return $obj->el_pre( $pre, $output, $item, $depth, $args, $id );
        }
        
    }
    
    /**
     * Display function for edit menu.
     * Called by edit_display() method.
     * 
     * @access public
     * @since  1.0.0
     * @param int $item_id Item ID.
     * @param NavMenuObj $item Navigation post object.
     */
    public function display( $item_id, $item ) {
        _eb( 'Type', WPS_WPCM_DOMAIN );
        $options = $this->get_select_options();
        MLCMI_Logic_Type::select( $item, $options );
        // $this->description = ''; 
    }
    
    /**
     * Options of basic conditionals.
     * 
     * @access public
     * @since  1.0.0
     */
    public function get_select_options() {
        return array(
            'logic_basic' => __( 'Basic', WPS_WPCM_DOMAIN ),
            'logic'       => __( 'Custom', WPS_WPCM_DOMAIN ),
        );
    }
    
}
}

if ( !class_exists( 'MLCMI_Logic_Custom' ) ) {
/**
 * Logic Code Class
 *
 * Use the construct magic method to name field.
 * Use display method to output on the edit menu admin page.
 *
 * @uses   MLCMI_Text
 * @access public
 * @since  0.1.0
 */
class MLCMI_Logic_Custom extends MLCMI_Text {
    
    /**
     * Field name.
     * 
     * @access public
     * @since  1.0.0
     * @var string $var Field name/slug.
     */
    public $field = 'logic_custom';
    
    /**
     * Field name.
     * 
     * @access public
     * @since  1.0.0
     * @var string $var Field name.
     */
    public $field_name = 'Menu Logic Custom';
    
    /**
     * Class name.
     * 
     * @access public
     * @since  1.0.0
     * @var string $var Class name.
     */
    public $c = 'MLCMI_Logic_Custom';
    
    /**
     * Array of menu item IDs.
     * 
     * @since 0.1.0
     * @var array $var Array of menu item IDs.
     */
    public $ml_ids = array();
    
    /**
     * Add checks to the start_el() & end_el() Walker methods.
     * 
     * Call parent::_construct().
     */
    public function loaded() {
        // Add checks
        // add_action( 'ml_walker_nav_menu_start_el_pre', array( $this, 'el_pre' ), 10, 6 );
        // add_action( 'ml_walker_nav_menu_end_el_pre', array( $this, 'el_pre' ), 10, 5 );
    }
    
    /**
     * Checks & runs the menu item's logic.
     *
     * Checks to see if the menu item has logic. If it does,
     * it runs the logic.
     *
     * If true, show item.
     * 
     * @param WP_Post $item Menu item object.
     * @return bool Whether item's logic returns true/false.
     */
    public function check_logic( $item ) {
        $field = $this->field;

        if ( isset( $item->{$field} ) && $item->{$field} ) {
            $code = $item->{$field};
            if ( ml_is_callable( $code ) )
                return call_user_func( $code );
            
            $code = ml_get_logic( $code );
            if ( !eval( $code ) ) {
                return false;
            }
        }
        return true;
    }
    
    /**
	 * @see Walker::start_el()
	 * @since 0.1.0
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param object $item Menu item data object.
	 * @param int $depth Depth of menu item. Used for padding.
	 * @param int $current_page Menu item ID.
	 * @param object $args
	 */
	public function el_pre( $pre, $output, $item, $depth, $args, $id = 0 ) {
        // If logic fails, move on
        if ( ! $this->check_logic( $item ) ) {
            $this->ml_ids[] = $item->ID;
            return false;
        }

        return true;
    }
    
    /**
     * Display function for edit menu.
     * Called by edit_display() method.
     * 
     * @access public
     * @since  1.0.0
     * @param int $item_id Item ID.
     * @param NavMenuObj $item Navigation post object.
     */
    public function display( $item_id, $item ) {
        _eb( 'Condition', WPS_WPCM_DOMAIN );
        self::text( $item, 'widefat code' );
        $this->description = '<br />' . __( 'Enter conditional. If the function does not exist, it will return false.', WPS_WPCM_DOMAIN ); 
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
        if ( 'logic_custom' != $field || '' == $value || !is_string( $value ) ) {
            return $value;
        }
        
        // Remove Error code if present.
        if ( __( 'ERROR in your code! ', WPS_WPCM_DOMAIN ) == $value )
            return '';
        
        // Check code
        switch( ml_check_code( $value ) ) {
            case 'semicolons':
                return trim( str_replace( ';', '', $value ) );
            case 'error':
                return __( 'ERROR in your code! ', WPS_WPCM_DOMAIN ) . $value;
            case true:
                return $value;
            default:
                return apply_filters( 'ml_validate_' . $field, $value, $menu_item_db_id, $request, $menu_id, $args );
                break;
        }
        
    }
}
}

if ( !class_exists( 'MLCMI_Logic_Parent' ) ) {
/**
 * Logic Children Class
 *
 * Use the construct magic method to name field.
 * Use display method to output on the edit menu admin page.
 *
 * @uses   MLCMI_Checkbox
 * @access public
 * @since  0.1.0
 */
class MLCMI_Logic_Parent extends MLCMI_Checkbox {
    
    /**
     * Field slug.
     * 
     * @access public
     * @since  1.0.0
     * @var string $var Field slug.
     */
    public $field = 'logic_parent';
    
    /**
     * Field name.
     * 
     * @access public
     * @since  1.0.0
     * @var string $var Field name.
     */
    public $field_name = 'Menu Logic Chidren';
    
    /**
     * Class name.
     * 
     * @access public
     * @since  1.0.0
     * @var string $var Class name.
     */
    public $c = 'MLCMI_Logic_Parent';
    
    /**
     * Current menu item.
     * 
     * @since 0.1.0
     * @var WP_Post $current_parent_item Nav menu post object.
     */
    public $current_parent_item = null;
    
    /**
     * Add checks to the start_el() & end_el() Walker methods.
     * 
     * Call parent::_construct().
     */
    public function loaded() {
        add_action( 'ml_walker_nav_menu_start_el_pre', array( $this, 'el_pre' ), 15, 6 );
    }
    
    /**
	 * @see Walker::start_el()
	 * @since 0.1.0
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param object $item Menu item data object.
	 * @param int $depth Depth of menu item. Used for padding.
	 * @param int $current_page Menu item ID.
	 * @param object $args
	 */
	public function el_pre( $pre, $output, $item, $depth, $args, $id = 0 ) {
        $f = $item->{$this->field};
        
        // false $pre means that the item is being filtered.
        if ( ! $pre ) {
            // Capture item, treat as parent
            $this->current_parent_item               = $item;
            $this->current_parent_item_hide_children = $f;
            return $pre;
        } elseif ( null != $this->current_parent_item ) {
            if ( $this->current_parent_item_hide_children && (int)$this->current_parent_item->ID !== (int)$item->menu_item_parent ) {
                $this->current_parent_item               = null;
                $this->current_parent_item_hide_children = false;
            }

            if ( $this->current_parent_item_hide_children )
                return false;
            else
                return true;
            
        }

        return true;
    }
    
    /**
     * Display function for edit menu.
     * Called by edit_display() method.
     * 
     * @access public
     * @since  1.0.0
     * @param int $item_id Item ID.
     * @param NavMenuObj $item Navigation post object.
     */
    public function display( $item_id, $item ) {
        MLCMI_Logic_Parent::checkbox( $item );
        _eb( 'Remove Children?', WPS_WPCM_DOMAIN );
        $this->description = __( 'If parent item\'s logic fails, remove children too?', WPS_WPCM_DOMAIN ); 
    }
    
}
}

if ( !class_exists( 'MLCMI_Logic_Basic' ) ) {
/**
 * Basic Logic Code Class
 *
 * Use the construct magic method to name field.
 * Use display method to output on the edit menu admin page.
 *
 * @uses   MLCMI_Select
 * @access public
 * @since  0.1.0
 */
class MLCMI_Logic_Basic extends MLCMI_Select {
    
    /**
     * Field name.
     * 
     * @access public
     * @since  1.0.0
     * @var string $var Field name/slug.
     */
    public $field = 'logic_basic';
    
    /**
     * Field name.
     * 
     * @access public
     * @since  1.0.0
     * @var string $var Field name.
     */
    public $field_name = 'Menu Logic Basic';
    
    /**
     * Class name.
     * 
     * @access public
     * @since  1.0.0
     * @var string $var Class name.
     */
    public $c = 'MLCMI_Logic_Basic';
    
    /**
     * Array of menu item IDs.
     * 
     * @since 0.1.0
     * @var array $var Array of menu item IDs.
     */
    public $ml_ids = array();
    
    /**
     * Add checks to the start_el() & end_el() Walker methods.
     * 
     * Call parent::_construct().
     */
    public function loaded() {
        
        // Add chosen
        add_action( 'current_screen', array( $this, 'add_chosen' ), 99, 6 );
        
    }
    
    /**
     * Display function for edit menu.
     * Called by edit_display() method.
     * 
     * @access public
     * @since  1.0.0
     * @param int $item_id Item ID.
     * @param NavMenuObj $item Navigation post object.
     */
    public function display( $item_id, $item ) {
        _eb( 'Basic Conditions', WPS_WPCM_DOMAIN );
        $options = $this->get_select_options();
        MLCMI_Logic_Basic::multiselect( $item, $options );
        $this->description = '<br />' . __( 'Select conditional. Multiple conditions will be connected with AND.', WPS_WPCM_DOMAIN ); 
    }
    
    /**
     * Options of basic conditionals.
     * 
     * @access public
     * @since  1.0.0
     */
    public function get_select_options() {
        $defaults = array(
            'is_home'              => __( 'Home', WPS_WPCM_DOMAIN ),
            'is_front_page'        => __( 'Front Page', WPS_WPCM_DOMAIN ),
            'is_posts_page'        => __( 'Posts Page', WPS_WPCM_DOMAIN ),
            'is_single'            => __( 'Single', WPS_WPCM_DOMAIN ),
            'is_singular'          => __( 'Singular', WPS_WPCM_DOMAIN ),
            'is_post_type_archive' => __( 'Post Type Archive Page', WPS_WPCM_DOMAIN ),
            'is_page'              => __( 'Page', WPS_WPCM_DOMAIN ),
            'is_subpage'           => __( 'Sub-page', WPS_WPCM_DOMAIN ), // Not Native WP
            'is_page_template'     => __( 'Page Template', WPS_WPCM_DOMAIN ),
            'is_tag'               => __( 'Tag Archive', WPS_WPCM_DOMAIN ),
            'is_category'          => __( 'Category Archive', WPS_WPCM_DOMAIN ),
            'is_tax'               => __( 'Taxonomy Archive', WPS_WPCM_DOMAIN ),
            'is_author'            => __( 'Author Archive', WPS_WPCM_DOMAIN ),
            'is_date'              => __( 'Date Archive', WPS_WPCM_DOMAIN ),
            'is_year'              => __( 'Year Archive', WPS_WPCM_DOMAIN ),
            'is_month'             => __( 'Month Archive', WPS_WPCM_DOMAIN ),
            'is_day'               => __( 'Day Archive', WPS_WPCM_DOMAIN ),
            'is_time'              => __( 'Hour Archive', WPS_WPCM_DOMAIN ),
            'is_archive'           => __( 'Archive', WPS_WPCM_DOMAIN ),
            'is_search'            => __( 'Search', WPS_WPCM_DOMAIN ),
            'is_404'               => __( '404', WPS_WPCM_DOMAIN ),
            'is_paged'             => __( 'Paged', WPS_WPCM_DOMAIN ),
            'is_attachment'        => __( 'Attachment Page', WPS_WPCM_DOMAIN ),
            'is_feed'              => __( 'Feed', WPS_WPCM_DOMAIN ),
            'is_preview'           => __( 'Preview', WPS_WPCM_DOMAIN ),
            'in_the_loop'          => __( 'Inside Loop', WPS_WPCM_DOMAIN ),
            'is_rtl'               => __( 'RTL', WPS_WPCM_DOMAIN ),
            'is_user_logged_in'    => __( 'User Logged In', WPS_WPCM_DOMAIN ),
            'is_admin_bar_showing' => __( 'Admin Bar Showing', WPS_WPCM_DOMAIN ),
            // 'is_plugin_active',
        );
        
        // $conditionals = array_merge( $defaults, $this->multisite(), $this->plugins(), $this->roles() );
        $conditionals = $defaults;
        $all = array();
        
        // Add Nots
        foreach( $conditionals as $condition => $name ) {
            $all[ $condition ]      = $name;
            $all['!' . $condition ] = __( 'Not ', WPS_WPCM_DOMAIN ) . $name;
        }
        
        return apply_filters( 'menu_logic_basic_conditionals', $all );
    }
    
    /**
	 * @see Walker::start_el()
	 * @since 0.1.0
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param object $item Menu item data object.
	 * @param int $depth Depth of menu item. Used for padding.
	 * @param int $current_page Menu item ID.
	 * @param object $args
	 */
	public function el_pre( $pre, $output, $item, $depth, $args, $id = 0 ) {
        // If logic fails, move on
        if ( ! $this->check_logic( $item ) ) {
            $this->ml_ids[] = $item->ID;
            return false;
        }

        return true;
    }
    
    /**
     * Checks & runs the menu item's logic.
     *
     * Checks to see if the menu item has logic. If it does,
     * it runs the logic.
     *
     * If true, show item.
     * 
     * @param WP_Post $item Menu item object.
     * @return bool Whether item's logic returns true/false.
     */
    public function check_logic( $item ) {
        // $conditions = $item->logic_basic;
        $field      = $this->field;
        $conditions = $item->{$field};
        $results    = array();

        if ( $conditions ) {
            /** Cycle through conditions */
            foreach( $conditions as $condition ) {
            
                /** See if condition is callable */
                if ( !is_callable( $condition ) ) {
                
                    /** Check custom methods first & store */
                    if ( method_exists( $this, $condition ) )
                        $results[ $condition ] = call_user_func( array( $this, $condition ) );
                    
                    /** Check functions & store */
                    if ( function_exists( $condition ) )
                        $results[ $condition ] = call_user_func( $condition );
                        
                } else {
                
                    /** Condition is callable, store */
                    $results[ $condition ] = call_user_func( $condition );
                    
                }
                
            }

            /** Process Conditions */
            $r = array_unique( array_values( $results ) );

            if ( 1 < count( $r ) ) {
                return false;
            } else {
                return (bool)$r[0];
            }
        }
        
        /** Assume no conditions exist */
        return true;
    }
    
    // Check if page is an ancestor
    public static function is_ancestor() {
        global $wp_query;
        if ( in_array( get_the_ID(), $wp_query->post->ancestors ) ) {
            return true;
        } else {
            return false;
        }
    }

    public static function is_posts_page() {
        global $wp_query;
        return $wp_query->is_posts_page;
    }
    
    public static function is_subpage() {
        global $post;
        if ( is_page() && $post->post_parent )
            return $post->post_parent;
        else
            return false;
    }
    
    // @todo Add user caps
    public function user_caps() {
        // current_user_can
        
        if ( function_exists( 'members_get_capabilities' ) )
            $capabilities = members_get_capabilities();
    }
    
    // @todo Add Members plugin support
    public function roles() {
        // current_user_has_role Checks if the currently logged-in user has a specific role.  in functions.php from Members
        // has_role Checks if a given ID of a user has a specific role in functions.php from Members
        $roles = array();
        $defaults = array(
            'administrator' => __( 'Is Current User: Administrator', WPS_WPCM_DOMAIN ),
            'editor'        => __( 'Is Current User: Editor', WPS_WPCM_DOMAIN ),
            'author'        => __( 'Is Current User: Author', WPS_WPCM_DOMAIN ),
            'contributor'   => __( 'Is Current User: Contributor', WPS_WPCM_DOMAIN ),
            'subscriber'    => __( 'Is Current User: Subscriber', WPS_WPCM_DOMAIN ),
        );
        
        foreach( $defaults as $role => $name ) {
            $roles['current_user_can( "' . $role . '" )'] = $name;
        }
        
        /** Support Members plugin */
        $members_active_roles = array();
        if ( function_exists( 'members_get_active_roles' ) )
            $members_active_roles = members_get_active_roles();
        
        return apply_filters( 'menu_logic_basic_conditionals_roles', array_merge( $roles, $members_active_roles ) );
    }
    
    // @todo Add is_site()
    public function multisite() {
        if ( is_multisite() ) {
            $ms = array(
                'is_main_site',
                'is_super_admin',
            );
        } else {
            $ms = array();
        }
        return apply_filters( 'menu_logic_basic_conditionals_multisite', $ms );
    }
    
    // @todo Add other Commerce plugins
    public function plugins() {
        /** Shopp */
        $shopp = array(
            'is_shopp_page',
            'is_shopp_product',
            'is_shopp_collection',
            'is_shopp_search',
            'is_shopp_taxonomy',
            'is_shopp_smart_collection',
            'is_shopp_userlevel',
        );
        
        /** Woo Commerce */
        $woo = array(
            'is_woocommerce',
            'is_shop',
            'is_product_category',
            'is_product_tag',
            'is_product',
            'is_cart',
            'is_checkout',
            'is_account_page',
        );
        
        /** WP Ecommerce */
        $wpsc = array(
            'wpsc_page_is_selected',
            'wpsc_has_pages',
            
            /** WPE User Account */
            'is_wpsc_profile_page',
            'is_wpsc_downloads_page',
            'wpsc_has_purchases',
            'wpsc_has_purchases_this_month',
            'wpsc_has_downloads',
            
            /** WPE Products Page */
            'wpsc_is_viewable_taxonomy',
            'wpsc_is_in_category',

            /** WPE Single Product */
            'wpsc_is_product',
            'wpsc_is_single_product',

            /** WPE Checkout */
            'wpsc_is_checkout',
            'wpsc_has_regions',
        );
        
        /** Jetpack */
        $jetpack = array(
            'jetpack_is_mobile',
            'AtD_is_allowed',
            //Jetpack::is_module_active
        );
        
        /** The Events Calendar (Tribe) */
        $events = array(
            'tribe_is_event',
            'tribe_is_venue',
            'tribe_is_by_date',
            'tribe_is_day',
            'tribe_is_new_event_day',
            'tribe_is_past',
            'tribe_is_showing_all',
            'tribe_is_upcoming',
            'tribe_is_month',
            'tribe_has_venue',
            'tribe_has_organizer',
            'tribe_eb_is_live_event',
        );
        
        /** BuddyPress */
        $buddypress = array(
            'is_buddypress',
            'bp_is_root_blog',
            'bp_is_multiblog_mode',
            'bp_is_network_activated',
            'bp_is_akismet_active',
            'bp_is_friend',
            'bp_is_active',
            'bp_is_current_component',
            'bp_is_current_action',
            'bp_is_action_variable',
            'bp_is_current_item',
            'bp_is_single_item',
            'bp_is_item_admin',
            'bp_is_item_mod',
            'bp_is_directory',
            
            /** User */
            'bp_is_my_profile',
            'bp_is_user',
            'bp_is_user_activity',
            'bp_is_user_friends_activity',
            'bp_is_user_groups_activity',
            'bp_is_user_profile',
            'bp_is_user_profile_edit',
            'bp_is_user_change_avatar',
            'bp_is_user_forums',
            'bp_is_user_forums_started',
            'bp_is_user_forums_replied_to',
            'bp_is_user_groups',
            'bp_is_user_blogs',
            'bp_is_user_recent_posts',
            'bp_is_user_recent_commments',
            'bp_is_user_friends',
            'bp_is_user_friend_requests',
            'bp_is_user_settings',
            'bp_is_user_settings_general',
            'bp_is_user_settings_notifications',
            'bp_is_user_settings_account_delete',
            'bp_is_user_active',
            'bp_is_user_spammer',
            'bp_is_user_deleted',
            'bp_is_user_inactive',
            'bp_member_is_loggedin_user',
            
            /** Groups */
            'bp_is_group',
            'bp_is_group_home',
            'bp_is_group_create',
            'bp_is_group_admin_page',
            'bp_is_group_forum',
            'bp_is_group_forums_active',
            'bp_group_is_forum_enabled',
            'bp_is_group_activity',
            'bp_is_group_forum_topic',
            'bp_is_group_forum_topic_edit',
            'bp_is_group_members',
            'bp_is_group_invites',
            'bp_is_group_membership_request',
            'bp_is_group_leave',
            'bp_is_group_single',
            'bp_is_create_blog',
            'bp_group_is_visible',
            'bp_get_group_is_public',
            'bp_is_group_creator',
            'bp_group_is_admin',
            'bp_group_is_mod',
            'bp_group_list_admins',
            'bp_group_list_mods',
            'bp_group_is_member',
            'bp_group_is_user_banned',
            'bp_get_group_member_is_friend',
            
            /** Messages */
            'bp_is_user_messages',
            'bp_is_messages_inbox',
            'bp_is_messages_sentbox',
            'bp_is_messages_compose_screen',
            'bp_is_notices',
            'bp_is_messages_conversation',
            'bp_is_single',
            
            /** Registration */
            'bp_is_activation_page',
            'bp_is_register_page',
            
            /** Components */
            'bp_is_root_component',
            'bp_is_component_front_page',
            'bp_is_blog_page',
            'bp_is_active',
            'bp_is_members_component',
            'bp_is_profile_component',
            'bp_is_activity_component',
            'bp_is_blogs_component',
            'bp_is_messages_component',
            'bp_is_friends_component',
            'bp_is_groups_component',
            'bp_is_forums_component',
            'bp_is_settings_component',
            'bp_is_current_component_core',
            
            /** Theme Compatibility */
            'bp_is_theme_compat_active',
            'bp_is_theme_compat_original_template',
            
            /** Blogs */
            'bp_blogs_is_blog_recordable',
            'bp_blogs_is_blog_trackable',
            'bp_blogs_is_blog_hidden',
            
            /** Activity */
            'bp_get_activity_is_favorite',
            'bp_is_single_activity',
            
            /** Template Tags */
            'bp_get_the_topic_is_topic_open',
            'bp_get_the_topic_is_mine',
            'bp_is_edit_topic',
            
            /** bbPress */
            'bb_is_user_logged_in',
            'bb_is_user_authorized',
            'bb_is_login_required',
            'bb_is_front',
            'bb_is_forum',
            'bb_is_tags',
            'bb_is_tag',
            'bb_is_topic_edit',
            'bb_is_topic',
            'bb_is_feed',
            'bb_is_search',
            'bb_is_profile',
            'bb_is_favorites',
            'bb_is_view',
            'bb_is_statistics',
            'bb_is_admin',
            'bb_get_forum_is_category',
            'bb_is_topic_lastpage',
            'bb_is_first',
        );
        
        /** bbPress */
        $bbpress = array(
            'is_bbpress',
            'bbp_is_forum_archive',
            'bbp_is_forum_closed',
            'bbp_is_forum_category',
            'bbp_is_forum_private',
            'bbp_is_forum_open',
            'bbp_is_single_forum',
            'bbp_is_forum',
            'bbp_is_custom_post_type',
            'bbp_is_reply',
            'bbp_is_reply_edit',
            'bbp_is_reply_move',
            'bbp_is_reply_anonymous',
            'bbp_is_single_reply',
            'bbp_is_replies_created',
            'bbp_is_favorites',
            'bbp_is_subscriptions',
            'bbp_is_user_home',
            'bbp_is_user_home_edit',
            'bbp_is_single_user',
            'bbp_is_single_user_edit',
            'bbp_is_single_user_profile',
            'bbp_is_single_user_topics',
            'bbp_is_single_user_replies',
            'bbp_is_single_view',
            'bbp_is_search',
            'bbp_is_search_results',
            'bbp_is_edit',
            'bbp_is_topic',
            'bbp_is_topic_anonymous',
            'bbp_is_topic_open',
            'bbp_is_topic_tag',
            'bbp_is_topic_tag_edit',
            'bbp_is_topic_archive',
            'bbp_is_topic_merge',
            'bbp_is_topic_split',
            'bbp_is_topics_created',
            'bbp_is_user_keymaster',
            'bbp_is_user_keymaster',
        );
        
        $defaults = apply_filters( 'menu_logic_conditionals_plugins_defaults', array_merge( $shopp, $wpsc, $woo ) );
        $plugins = array();
        foreach( $defaults as $function ) {
            if ( $p = MLCMI_Logic_Basic::function_exists( $function ) )
                $plugins[] = $p;
        }
        
        // Sort plugin conditionals
        if ( apply_filters( 'menu_logic_sort_conditionals_plugins', $plugins ) )
            asort( $plugins );
        
        return apply_filters( 'menu_logic_basic_conditionals_plugins', array_unique( $plugins ) );
    }
    
}
}

/**
 * Skeleton Class
 *
 * Use the construct magic method to name field.
 * Use display method to output on the edit menu admin page.
 *
 * @uses   WPS_WPCM_Custom_Menu_Item
 * @access public
 * @since  0.1.0
 */
if ( !class_exists( 'MLCMI' ) ) {
class MLCMI extends WPS_WPCM_Custom_Menu_Item {
    
    /**
     * Field slug.
     * 
     * @access public
     * @since  1.0.0
     * @var string $var Field slug.
     */
    public $field = 'tbd';
    
    /**
     * Field name.
     * 
     * @access public
     * @since  1.0.0
     * @var string $var Field name.
     */
    public $field_name = 'TBD';
    
    /**
     * Class name.
     * 
     * @access public
     * @since  1.0.0
     * @var string $var Class name.
     */
    public $c = 'MLCMI';
    
    /**
     * Add checks to the start_el() & end_el() Walker methods.
     * 
     * Call parent::_construct().
     */
    public function __construct( $field, $args ) {
        MLCMI::$field = $field;
        $defaults = apply_filters( 'ml_custom_menu_item_default_callbacks', array(
            'display_cb'  => '',
            'validate_cb' => '',
        ) );
        
        $args = wp_parse_args( $args, $defaults );
        
        // Set callbacks
        $this->display_cb  = $args['display_cb'];
        $this->validate_cb = $args['validate_cb'];
        
        // Load Parent
        parent::__construct();
    }
    
    
    /**
     * Output HTML for editing nav menu item in admin.
     * Set description, if needed.
     * 
     * @access public
     * @since  1.0.0
     * @param int $item_id Item ID.
     * @param NavMenuObj $item Navigation post object.
     */
    public function display( $item_id, $item ) {
        if ( is_callable( $this->display_cb ) )
            call_user_func( $this->display_cb, $item_id, $item );
    }
    
    /**
     * Validates conditional function name.
     * Optional, exists like this in the core class.
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
        if ( is_callable( $this->validate_cb ) )
            return call_user_func( $this->validate_cb, $item_id, $item );
        return $value;
    }
    
}
}

/**
 * Skeleton Class
 *
 * Use the construct magic method to name field.
 * Use display method to output on the edit menu admin page.
 *
 * @uses   WPS_WPCM_Custom_Menu_Item
 * @access public
 * @since  0.1.0
 */
if ( !class_exists( 'MLCMI_Skeleton' ) ) {
class MLCMI_Skeleton extends WPS_WPCM_Custom_Menu_Item {
    
    /**
     * Field name.
     * 
     * @access public
     * @since  1.0.0
     * @var string $var Field name/slug.
     */
    public $field = 'skeleton';
    
    /**
     * Class name.
     * 
     * @access public
     * @since  1.0.0
     * @var string $var Class name.
     */
    public $c = 'MLCMI_Skeleton';
    
    /**
     * Output HTML for editing nav menu item in admin.
     * Set description, if needed.
     * 
     * @access public
     * @since  1.0.0
     * @param int $item_id Item ID.
     * @param NavMenuObj $item Navigation post object.
     */
    public function display( $item_id, $item ) {
        MLCMI_Skeleton::$description = __( 'My description', WPS_WPCM_DOMAIN );
        // do something
    }
    
    /**
     * Validates conditional function name.
     * Optional, exists like this in the core class.
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
    
}
}

