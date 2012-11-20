<?php
/**
 * @package     Packager
 * @subpackage  Application
 *
 * @copyright   Copyright (C) 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Joomla Packager application class.
 *
 * @package     Packager
 * @subpackage  Application
 * @since       1.0
 */
class PackagerApplicationCli extends JApplicationCli
{
	/**
	 * @var    PackagerPhar
	 * @since  1.0
	 */
    protected $_packager;

	/**
	 * @var    boolean  True if the application should work quietly.
	 * @since  1.0
	 */
    protected $_quiet;

	/**
	 * Show the usage screen.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	protected function displayUsage()
	{
		$this->out('Joomla Packager 1.0');
		$this->out();
		$this->out('Packages a PHP application/libraries into a Phar[chive].');
		$this->out();
		$this->out('Note: If there is a packager.xml file in the current directory it will be used by');
		$this->out(' default.');
		$this->out();
		$this->out('Usage:  packager.phar [options]');
		$this->out();
		$this->out('  -f <file>    Use given manifest file.');
		$this->out('  -q           Don\'t be so chatty.');
		$this->out();
		$this->out('Example: ./packager.phar');
		$this->out('Example: ./packager.phar -f /path/to/packager.xml');
		$this->out();
	}

	/**
	 * Execute the application.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function doExecute()
	{
			// Check on some basic inputs.
		$this->_quiet  = $this->input->getBool('q', false);
		$manifestPath = $this->input->getString('f', $this->get('cwd') . '/packager.xml');

		try
		{
			// Get the XML manifest parsed and as an object.
			$manifest = $this->_fetchPackageManifest($manifestPath);
		}
		catch (InvalidArgumentException $e)
		{
			$this->out('ERROR: ' . $e->getMessage());
			$this->displayUsage();
			$this->close();
		}

		// Print the header.
		$this->_quiet or $this->out();
		$this->_quiet or $this->out('Joomla Package Builder');
		$this->_quiet or $this->out('----------------------');
		$this->_quiet or $this->out('. reading the package manifest.');

		// If there isn't a code section in the manifest we have nothing to do.
		if (!isset($manifest->code[0]))
		{
			$this->_quiet or $this->out('. no code section found in the manifest.');
			return;
		}

		// Ensure that we have at maximum one platform entry in the manifest.
		if (isset($manifest->code[0]->platform[1]))
		{
			$this->_quiet or $this->out('. only one platform entry can be in a manifest.');
			return;
		}

		$this->_quiet or $this->out('. creating the package object.');

		// Create the packager object.
		$this->_packager = new PackagerPhar(
			(string) $manifest['destination'],
			((string) $manifest['minify'] == 'true') ? true : false,
			((string) $manifest['alias'])
		);

		$this->_quiet or $this->out('.. created the package object.');
		$this->_quiet or $this->out('. adding files from the code section.');

		// Process the items in the code section of the manifest.
		foreach ($manifest->code[0]->children() as $item)
		{

            // set the namespace
            $this->_packager->setNamespace((string) $item['namespace'] );
			switch ($item->getName())
			{
				// Import a single file.
				case 'file':
					$this->_quiet or $this->out(sprintf('.. importing %s.', (string) $item));
					$this->_packager->addFile(realpath(dirname($manifestPath)) . '/' . (string) $item, (string) $item['localPath']);
					break;

				// Import a folder ... either recursively or not.
				case 'folder':
					// Check to see if we want to import the folder recursively.
					if ((string) $item['recursive'] == 'true')
					{
						$this->_quiet or $this->out(sprintf('.. importing %s recursively.', (string) $item));
						$this->_packager->addDirectoryRecursive(realpath(dirname($manifestPath)) . '/' . (string) $item, (string) $item['localPath']);
					}
					else
					{
						$this->_quiet or $this->out(sprintf('.. importing %s.', (string) $item));
						$this->_packager->addDirectoryFiles(realpath(dirname($manifestPath)) . '/' . (string) $item, (string) $item['localPath']);
					}
					break;

				// Import the Joomla Platform.
				case 'platform':
					$this->_quiet or $this->out('... creating the platform importer.');

					// Create a new Joomla Platform importer for the package.
					$importer = new PackagerPharImporterJoomlaPlatform($this->_packager, (string) $item['localPath']);

					$this->_quiet or $this->out('.... created the platform importer.');

					$this->_quiet or $this->out('... importing the platform.');

					// Import the platform packages.
					$importer->import($item);

					$this->_quiet or $this->out('.... imported the platform.');
					break;

				// Import the Joomla Platform.
				case 'git':
					$this->_quiet or $this->out('... creating the git repository importer.');

					// Create a new Git repository importer for the package.
					$importer = new PackagerPharImporterGitRepository($this->_packager, (string) $item['localPath']);

					$this->_quiet or $this->out('.... created the git repository importer.');

					$this->_quiet or $this->out('... importing the repository.');

					// Import the repository paths.
					$importer->import($item);

					$this->_quiet or $this->out('.... imported the repository.');
					break;

				default:
					throw new InvalidArgumentException(sprintf('Unable to process tag <%s> for packaging.', $item->getName()));
					break;
			}
		}

		$this->_quiet or $this->out('. setting the package stub file(s).');

		if ((string) $manifest->code[0]['stub'])
		{
			// Set the package stub file.
			$this->_packager->setStub(
				realpath(dirname($manifestPath)) . '/' . (string) $manifest->code[0]['stub']
			);
		}
		else
		{
			// Set the package stub files.
			$this->_packager->setStubs(
				(string) $manifest->code[0]['cli'],
				(string) $manifest->code[0]['web']
			);
		}

		$this->_quiet or $this->out('.. set the package stub file(s).');

		$this->_quiet or $this->out('. writing the package to disk.');

		// Write the package out to disk.
		$this->_packager->write();
	}

	/**
	 * Get the package manifest object from a filesystem path.
	 *
	 * @param   string  $manifestPath  The absolute filesystem path to the manifest file to parse and return.
	 *
	 * @return  SimpleXMLElement
	 *
	 * @since   1.0
	 * @throws  InvalidArgumentException
	 */
    protected function _fetchPackageManifest($manifestPath)
	{
		// Set relative paths to be relative to the current working directory.
		if (strpos($manifestPath, '/') !== 0)
		{
			$manifestPath = $this->get('cwd') . '/' . $manifestPath;
		}

		// Ensure a path has been specified.
		if (empty($manifestPath))
		{
			throw new InvalidArgumentException('You must specify a manifest file path or use interactive mode.');
		}

		// Ensure the path exists.
		if (!is_file($manifestPath))
		{
			throw new InvalidArgumentException('The path specified for your manifest file does not exist.');
		}

		// Load the manifest and parse it.
		$manifest = simplexml_load_file(realpath($manifestPath));

		return $manifest;
	}
}
