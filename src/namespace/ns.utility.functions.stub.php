<?php
/**
 * Namespace utility functions for the Joomla Platform with added namespace.
 * This file becomes part of the PHAR stub when the platform is built
 * into a single deployable archive to be used in Joomla applications.
 *
 * Utility functions to provide sanity for classes and methods
 * designed for a global namespace and now placed into a namespace
 *
 * Based almost byte for byte on the work of Rommel Santor
 * * @link    http://rommelsantor.com/clog/2011/04/10/php-5-3-dynamic-namespace-resolution/
 *
 * @package    Joomla.Platform
 *
 * @copyright  Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 *

 */

/**
 * Intelligent namespace prepender.
 *
 * This function will prepend the functions namespace to any string which
 * does not already include a namespace
 *
 * @param   string  $i_name  A string containing the name of a class, function, variable, or constant
 *
 * @return  string  The string after processing
 *
 * @since   11.1
 */
function ns($i_name) {
    if (!is_scalar($i_name))
        return $i_name;
    return strpos($i_name, '\\') !== false ? $i_name : (__NAMESPACE__ . '\\' . $i_name);
}

/**
 * Intelligent namespace stripper.
 *
 * This function will convert class, function, variable, and constant
 * names from within the functions namespace to the global namespace
 *
 * @param   string  $i_name  A string containing the name of a class, function, variable, or constant
 *
 * @return  string  The string after processing
 *
 */
function globalNs($i_name) {
    if (!is_scalar($i_name))
        return $i_name;
    return strpos($i_name, __NAMESPACE__) === 0 ? str_replace(__NAMESPACE__, '', $i_name, 1) : $i_name ;
}

/**
 * Intelligent constant function.
 *
 * This function ensure unnamespaced args to the constant function
 * are given this functions namespace
 *
 * @param   string  $i_name  A string containing the name of a constant
 *
 * @return  mixed   The value of the constant
 *
 */
function constant($i_name)        { return @\constant(ns($i_name)); }

/**
 * Intelligent class_exists function.
 *
 * This function ensure unnamespaced args to the class_exists function
 * are given this functions namespace
 *
 * @param   string  $i_name  A string containing the name of a class
 *
 * @return  boolean  True if the class exists
 *
 */
function class_exists($i_name)    { return \class_exists(ns($i_name)); }


/**
 * Intelligent call_user_func_array function.
 *
 * This function ensure unnamespaced function or class
 * args to the call_user_func_array function
 * are given this functions namespace
 *
 * @param   mixed $i_func   A function, class, or method to be called
 *
 *
 * @param   mixed $i_args   Arguments for the function
 *
 * @return  mixed   Returns the value of the callback, or FALSE on error
 *
 */
function call_user_func_array($i_func, $i_args) {
    is_array($i_func)
        ? (!is_object($i_func[0]) && ($i_func[0] = ns($i_func[0])))
        : (!is_object($i_func) && !function_exists($i_func) && ($i_func = ns($i_func)));
    return \call_user_func_array($i_func, $i_args);
}


/**
 * Intelligent call_user_func function.
 *
 * This function ensure unnamespaced function or class
 * args to the call_user_func_array function
 * are given this functions namespace
 *
 * @param   mixed $i_func   A function, class, or method to be called
 *
 * @return  mixed   Returns the value of the callback, or FALSE on error
 *
 */
function call_user_func($i_func) {
    return call_user_func_array($i_func, array_shift($args = func_get_args()));
}
?>