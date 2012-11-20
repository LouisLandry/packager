<?php
/**
 * Main bootstrap codefile for the Joomla Platform with added namespace.
 * This file becomes part of the PHAR stub when the platform is built
 * into a single deployable archive to be used in Joomla applications.
 *
 *
 * @package    Joomla.Platform
 *
 * @copyright  Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

// Setup the Pharsanity!
\Phar::interceptFileFuncs();

// Set the platform root path as a constant if necessary
// Note: this constant should not be confused with JPATH_PLATFORM
// from the root platform.  This define sets the constant
// joomla\joomla\p12_4\JPATH_PLATFORM so there is no
// conflict.
if (!defined('JPATH_PLATFORM'))
{
	define('JPATH_PLATFORM', 'phar://' . __FILE__);
}

// Detect the native operating system type.
$os = strtoupper(substr(PHP_OS, 0, 3));

if (!defined('IS_WIN'))
{
	define('IS_WIN', ($os === 'WIN') ? true : false);
}
if (!defined('IS_UNIX'))
{
	define('IS_UNIX', (IS_WIN === false) ? true : false);
}

// Import the platform version library if necessary.
if (!class_exists('JPlatform'))
{
	require_once JPATH_PLATFORM . '/platform.php';
}

// Import the library loader if necessary.
if (!class_exists('JLoader'))
{
	require_once JPATH_PLATFORM . '/loader.php';
}

// Make sure that the Joomla Platform has been successfully loaded.
if (!class_exists('JLoader'))
{
    // NOTE: SPL exception must be referred to by it's namespace!
    throw new RuntimeException('Joomla Platform not loaded.');
}

// Setup the autoloaders.
    JLoader::setup();

// Register the Joomla namespace as a prefix
JLoader::registerPrefix(__NAMESPACE__.'\\J', JPATH_PLATFORM . '/joomla');

// Import the base Joomla Platform libraries.
JLoader::import('joomla.factory');

// Register classes for compatability with PHP 5.3
if (version_compare(PHP_VERSION, '5.4.0', '<'))
{
	JLoader::register(__NAMESPACE__.'\JsonSerializable', JPATH_PLATFORM . '/compat/jsonserializable.php');
}

// Register classes that don't follow one file per class naming conventions
// Because these are passed as strings, not classnames, we must add the namespace
// for them.
JLoader::register(__NAMESPACE__.'\'JText', JPATH_PLATFORM . '/joomla/language/text.php');
JLoader::register(__NAMESPACE__.'\'JRoute', JPATH_PLATFORM . '/joomla/application/route.php');

// End of the Phar Stub.
__HALT_COMPILER();?>