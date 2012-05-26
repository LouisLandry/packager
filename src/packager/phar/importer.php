<?php
/**
 * @package     Packager
 * @subpackage  Phar
 *
 * @copyright   Copyright (C) 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * GitHub Repository importer class for the Phar packager.
 *
 * @package     Packager
 * @subpackage  Phar
 * @since       1.0
 */
abstract class PackagerPharImporter
{
	/**
	 * @var    PackagerPhar  The Phar package object in which to import the platform files.
	 * @since  1.0
	 */
	private $_packager;

	/**
	 * @var    string  The local path within the Phar package to import platform files.
	 * @since  1.0
	 */
	private $_pharPath;

	/**
	 * Object Constructor.
	 *
	 * @param   PackagerPhar  $packager  The Phar package object in which to import the platform files.
	 * @param   string        $pharPath  The local path within the Phar package to import platform files.
	 *
	 * @since   1.0
	 */
	public function __construct(PackagerPhar $packager, $pharPath = null)
	{
		// Set the packager and local path for the importer.
		$this->_packager = $packager;
		$this->_pharPath = $pharPath;
	}

	/**
	 * Import files based on the XML element from the manifest.
	 *
	 * @param   SimpleXMLElement  $el  The XML element containing information about how to import the files.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	abstract public function import(SimpleXMLElement $el);

	/**
	 * Import a directory's files into the Phar package; without recursing into children.
	 *
	 * @param   string  $path              The absolute filesystem path to the directory to import.
	 * @param   string  $pharPathExtended  The [optional] local path to append to the importer's local path.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 * @throws  InvalidArgumentException
	 */
	protected function importDirectoryFiles($path, $pharPathExtended = null)
	{
		$this->_packager->addDirectoryFiles($path, $this->_pharPath . $pharPathExtended);
	}

	/**
	 * Import a directory's files into the Phar package; recursing into children.
	 *
	 * @param   string  $path              The absolute filesystem path to the directory to import.
	 * @param   string  $pharPathExtended  The [optional] local path to append to the importer's local path.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 * @throws  InvalidArgumentException
	 */
	protected function importDirectoryRecursive($path, $pharPathExtended = null)
	{
		$this->_packager->addDirectoryRecursive($path, $this->_pharPath . $pharPathExtended);
	}

	/**
	 * Import a file into the Phar package.
	 *
	 * @param   string  $path              The absolute filesystem path to the file to import.
	 * @param   string  $pharPathExtended  The [optional] local path to append to the importer's local path.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 * @throws  InvalidArgumentException
	 */
	protected function importFile($path, $pharPathExtended = null)
	{
		$this->_packager->addFile($path, $this->_pharPath . $pharPathExtended);
	}
}
