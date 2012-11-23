<?php
/**
 * @package     Packager
 * @subpackage  Phar
 *
 * @copyright   Copyright (C) 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Git Repository importer class for the Phar packager.
 *
 * @package     Packager
 * @subpackage  Phar
 * @since       1.0
 */
class PackagerPharImporterGitRepository extends PackagerPharImporter
{
	/**
	 * Import the Git Repository based on the XML element from the manifest.
	 *
	 * @param   SimpleXMLElement  $el  The XML element containing information about how to import the repository.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function import(SimpleXMLElement $el)
	{
		// Fetch the git repository.
		$repositoryPath = $this->_fetchGitRepository((string) $el['url'], (string) $el['ref']);

		// Process the items in the code section of the manifest.
		foreach ($el->children() as $item)
		{
			switch ($item->getName())
			{
				// Import a single file.
				case 'file':
					$this->importFile($repositoryPath . '/' . (string) $item, (string) $item['localPath']);
					break;

				// Import a folder ... either recursively or not.
				case 'folder':
					// Check to see if we want to import the folder recursively.
					if ((string) $item['recursive'] == 'true')
					{
						$this->importDirectoryRecursive($repositoryPath . '/' . (string) $item, (string) $item['localPath']);
					}
					else
					{
						$this->importDirectoryFiles($repositoryPath . '/' . (string) $item, (string) $item['localPath']);
					}
					break;

				default:
					throw new InvalidArgumentException(sprintf('Unable to process tag <%s> with the Git repository importer.', $item->getName()));
					break;
			}
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
		$root = sys_get_temp_dir() . '/'. md5($url);

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

			$repo->create($url);
		}

		// Get a clean checkout of the branch/tag required.

		$repo->fetch()
			->branchCheckout($ref)
			->clean();

		return $root;
	}
}
