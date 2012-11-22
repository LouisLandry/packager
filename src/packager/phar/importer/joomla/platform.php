<?php
/**
 * @package     Packager
 * @subpackage  Phar
 *
 * @copyright   Copyright (C) 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Joomla Platform importer class for the Phar packager.
 *
 * @package     Packager
 * @subpackage  Phar
 * @since       1.0
 */
class PackagerPharImporterJoomlaPlatform extends PackagerPharImporter
{
	/**
	 * Import the Joomla Platform based on the XML element from the manifest.
	 *
	 * @param   SimpleXMLElement  $el  The XML element containing information about how to import the platform.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function import(SimpleXMLElement $el)
	{
		// Fetch the git repository.
		$basePath = $this->_fetchGitRepository('http://github.com/joomla/joomla-platform.git', (string) $el['version']);

		// Append the Joomla Platform libraries folder to the base path.
		$basePath .= '/libraries';

		// Check to see if legacy support should be imported.
		$legacy = (((string) $el['legacy'] == 'true') ? true : false);

        // Check to see if external libraries should be imported.
        $external = (((string) $el['external'] == 'false') ? false : true);

        // Check to see if external libraries should be imported.
        $hard = (((string) $el['hard'] == 'false') ? false : true);


        if ($hard) {
		    // Validate that the platform import file exists.
		    if (!is_file($basePath . '/import.php'))
		    {
			    throw new InvalidArgumentException('The platform import file could not be found.');
		    }

		    // Validate that the platform loader file exists.
		    if (!is_file($basePath . '/loader.php'))
		    {
			    throw new InvalidArgumentException('The platform loader file could not be found.');
		    }

		    // Add the hard requirements.
		    $this->importFile($basePath . '/loader.php');
		    $this->importFile($basePath . '/platform.php');
		    $this->importFile($basePath . '/import.php');

		    // If legacy is enabled import the legacy file.
		    if ($legacy)
		    {
			    $this->importFile($basePath . '/import.legacy.php');
		    }
        }
		// Get the appropriate Joomla Platform packages to import.
		$packages = $this->_fetchPackagesToImport($el, $basePath);

		// If no packages were specified then assume we go for everything.
		if (empty($packages))
		{
			// Add everything in the main package folder.
			$this->importDirectoryRecursive($basePath . '/joomla', '/joomla');

			// Add everything in the legacy package folder if enabled.
			if ($legacy)
			{
				$this->importDirectoryRecursive($basePath . '/legacy', '/legacy');
			}
		}
		else
		{
			// Add just the enumerated packages.
			foreach ($packages as $package)
			{
				// Add the package from the main package folder.
				$this->importDirectoryRecursive($basePath . '/joomla/' . $package, '/joomla/' . $package);

				// Add the package from the legacy package folder if enabled.
				if ($legacy)
				{
					$this->importDirectoryRecursive($basePath . '/legacy/' . $package, '/legacy/' . $package);
				}
			}

			// Add just the files in the main package folder.
			$this->importDirectoryFiles($basePath . '/joomla', '/joomla');

			// Add just the files in the legacy package folder if enabled.
			if ($legacy)
			{
				$this->importDirectoryFiles($basePath . '/legacy', '/legacy');
			}
		}

        if ($external) {
		// Add the external library dependencies.
		$this->importDirectoryRecursive($basePath . '/phpmailer', '/phpmailer');
		$this->importDirectoryRecursive($basePath . '/phputf8', '/phputf8');
		$this->importDirectoryRecursive($basePath . '/simplepie', '/simplepie');
        }
	}

	/**
	 * Get the base path for a clone of the Git repository (creating it if necessary).
	 *
	 * @param   string  $url  The URL of the git repository to clone/update.
	 * @param   string  $ref  The ref to use in the git repository.
	 *
	 * @return  string  The base path for the git repository.
	 *
	 * @since   1.0
	 * @throws  RuntimeException
	 */
	private function _fetchGitRepository($url, $ref = 'master')
	{
		// Create a Git repository object within the system tmp folder for the url.
		$root = sys_get_temp_dir() .'/'. md5($url);

		// If the folder doesn't exist attempt to create it.
		if (!is_dir($root))
		{
			mkdir($root, 0777, true);
		}

		// Instantiate the repository object.
		$repo = new PackagerGitRepository($root);

		// Only clone the repository if it doesn't exist.
		if (!$repo->exists())
		{
			$repo->create();
		}

		// Get a clean checkout of the branch/tag required.
		$repo->fetch()
			->branchCheckout($ref)
			->clean();

		return $root;
	}

	/**
	 * Get all Joomla Platform packages to import based on the XML element.
	 *
	 * @param   SimpleXMLElement  $el        The XML element containing information about how to import the platform.
	 * @param   string            $basePath  The filesystem path to the Joomla Platform libraries.
	 *
	 * @return  array  The packages to import.
	 *
	 * @since   1.0
	 */
	private function _fetchPackagesToImport(SimpleXMLElement $el, $basePath)
	{
		// Initialize variables.
		$packages = array();

		// If we have no packages specified assume we'll get them all imported.
		if (!isset($el->packages[0]) || !isset($el->packages[0]->package[0]))
		{
			return $packages;
		}

		// Check to see if legacy support should be imported.
		$legacy = (((string) $el['legacy'] == 'true') ? true : false);

		// Get the package set element and determine if we are using an exclusion rule or not.
		$packageSet = $el->packages[0];
		$exclude = (((string) $packageSet['exclude'] == 'true') ? true : false);

		// Get the enumerated packages from the XML.
		$enumerated = array();
		foreach ($packageSet->package as $p)
		{
			$enumerated[] = (string) $p['name'];
		}

		// We are using an exclusion rule.  Sounds like work.
		if ($exclude)
		{
			// Iterate over the main package directory contents.
			$directory = new DirectoryIterator($basePath . '/joomla');
			foreach ($directory as $child)
			{
				if ($child->isDir() && !$child->isDot() && !in_array($child->getFilename(), $enumerated))
				{
					$packages[] = $child->getFilename();
				}
			}

			// Iterate over the legacy package directory contents if legacy is enabled.
			if ($this->_legacy)
			{
				$directory = new DirectoryIterator($basePath . '/legacy');
				foreach ($directory as $child)
				{
					if ($child->isDir() && !$child->isDot() && !in_array($child->getFilename(), $enumerated))
					{
						$packages[] = $child->getFilename();
					}
				}
			}
		}
		// Easy peasy, just get the enumerated packages.
		else
		{
			$packages = $enumerated;
		}

		// Make sure we have unique values.
		$packages = array_unique($packages);

		return $packages;
	}
}
