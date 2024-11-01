<?php

/**
 * Functions File
 *
 * This file defines the Child Theme's constants & tells WP not to update.
 *
 * @category   Menu_Logic
 * @package    Functions
 * @author     Travis Smith
 * @license    http://www.opensource.org/licenses/gpl-license.php GPL v2.0 (or later)
 * @link       http://wpsmith.net/
 * @since      1.1.0
 */

/**
 * Adds return to code;
 * 
 * @param string $code PHP Code.
 * @return string $code PHP Code.
 */
if ( !function_exists( 'ml_get_logic' ) ) {
function ml_get_logic( $code ) {
    if ( '' == $code )
        $code = "return true;";
    
    // Add return
    if ( false === stristr( $code, "return" ) ) {
        $code = "return (" . $code . ");";
    }
    
    return $code;
}
}

/**
 * Check the code & determine clean method.
 * Check code with runkit_lint, if present.
 * Self-validate only checking semicolons.
 * 
 * @since  1.0.0
 * @param string $code PHP Code.
 * @return bool Whether code is clean.
 */
if ( !function_exists( 'ml_check_code' ) ) {
function ml_check_code( $code ) {
    
    // Run Lint, if available
    if ( function_exists( 'runkit_lint' ) && runkit_lint( $code ) )
        return true;
    elseif ( function_exists( 'runkit_lint' ) && ! runkit_lint( $code ) )
        return 'error';
    // Self-validate
    elseif ( ! function_exists( 'runkit_lint' ) ) {
        // Check for semi-colons
        if ( preg_match( "/\;/", $code ) )
            return 'semicolons';
            
        // Check for parenthesis
        if ( !preg_match( "/\(\)/", $code ) )
            return 'parenthesis';
    }
    
    // Assume code is clean
    return apply_filters( 'ml_check_code', true );

}
}

/**
 * Callable function
 * 
 * @param string $value PHP Code.
 * @return mixed
 */
if ( !function_exists( 'ml_is_callable' ) ) {
function ml_is_callable( $value ) {
    $v = '';
    // Validate function name
    if ( preg_match( "/^[a-z_]\w+$/i", $value, $o ) ) {
        $v = $value;
    // Check for ()
    } elseif( preg_match( "/\(\)/", $value, $o ) ) {
        $v = trim( preg_replace( '/\s*\([^)]*\)/', '', $value ) );
    }
    
    // Check callable
    if ( is_callable( $v ) )
        return $v;
    else
        return '';
}
}

if ( !function_exists( '_eb' ) ) {
/**
 * Outputs string with <br/> at end.
 * 
 * @since  1.0.0
 * @param string $output Text to output via __()
 * @param string $domain Translatable domain.
 * @return string Output markup.
 */
function _eb( $output, $domain = WPS_WPCM_DOMAIN ) {
    echo __b( $output, $domain );
}
}

if ( !function_exists( '__b' ) ) {
/**
 * Returns output string with <br/> at end.
 * 
 * @since  1.0.0
 * @param string $output Text to output via __()
 * @param string $domain Translatable domain.
 * @return string Output markup.
 */
function __b( $output, $domain = WPS_WPCM_DOMAIN ) {
    if ( false !== $domain )
        return __( $output, $domain ) . '<br/>';
    else
        return $output . '<br/>';
}
}

/**
 * Deprecated. Determines whether eval() is allowed.
 * 
 * @return bool Whether eval is allowed.
 */
function ml_is_eval_allowed() {
    $isevalfunctionavailable = false;
    $evalcheck = "\$isevalfunctionavailable = true;";
    eval( $evalcheck );
    return $isevalfunctionavailable;
}

/**
 * Deprecated. Safely runs eval code using butfering.
 * 
 * @param string $code PHP Code.
 * @return bool Whether eval is allowed.
 */
function ml_safe_eval( $code ) {
    ob_start();
    eval( $code );
    $eval = ob_get_contents();
    ob_end_clean();
    if ( is_bool( $eval ) )
        return $eval;
    else
        return false;
}

if ( !function_exists( 'has_role' ) ) {

	/**
	 * Checks if a given ID of a user has a specific role.
	 *
	 * @since 0.1
	 * @uses WP_User() Gets a user object based on an ID.
	 * @param $role string Role to check for against the user.
	 * @param $user_id int The ID of the user to check.
	 * @return true|false bool Whether the user has the role.
	 */
	function has_role( $role = '', $user_id = '' ) {

		/* If no role or user ID was added, return false. */
		if ( !$role || !$user_id )
			return false;

		/* Make sure the ID is an integer. */
		$user_id = (int)$user_id;

		/* Get the user object. */
		$user = new WP_User( $user_id );

		/* If the user has the role, return true. */
		if ( $user->has_cap( $role ) )
			return true;

		/* Return false if the user doesn't have the role. */
		return false;
	}
}

if ( !function_exists( 'current_user_has_role' ) ) {

	/**
	 * Checks if the currently logged-in user has a specific role.
	 *
	 * @since 0.1
	 * @uses current_user_can() Checks whether the user has the given role.
	 * @param $role string The role to check for.
	 * @return true|false bool Whether the user has the role.
	 */
	function current_user_has_role( $role = '' ) {

		/* If no role was input, return false. */
		if ( !$role )
			return false;

		/* If the current user has the role, return true. */
		if ( current_user_can( $role ) )
			return true;

		/* If the current user doesn't have the role, return false. */
		return false;
	}
}
