<?php
/**
 * @package     Packager
 * @subpackage  Phar
 *
 * @copyright   Copyright (C) 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Phar class.
 *
 * @package     Packager
 * @subpackage  Phar
 * @since       1.0
 */
class PackagerPhar
{
	/**
	 * @var    Phar  The Phar object to be created/updated.
	 * @since  1.0
	 */
    private $_phar;

	/**
	 * @var    boolean  True to have whitespace stripped from PHP files while being imported into the Phar.
	 * @since  1.0
	 */
    private $_stripWhitespace;


    /**
     * @var    namespace  The current Namespace to be prepended
     * 	 * @since  1.1
     */
    private $_namespace = '';
	/**
	 * Object Constructor.
	 *
	 * @param   string   $path             The filesystem path to the Phar to create/update.
	 * @param   boolean  $stripWhitespace  True to strip whitespace from PHP files.
	 * @param   string   $name             The Phar alias.
	 *
	 * @since   1.0
	 * @throws  InvalidArgumentException
	 */
	public function __construct($path, $stripWhitespace = true, $name = null)
	{
		// Let's resolve oddities, etc.
		$path = realpath(dirname($path)) . '/' . basename($path);

		// Validate the Phar path.
		if (!is_dir(dirname($path)))
		{
			throw new InvalidArgumentException(sprintf('The path %s does not exist.', dirname($path)));
		}

		// Make sure we have a file name.
		$name = $name ? $name : basename($path);

		// Set some boolean options for the packager.
		$this->_stripWhitespace = (bool) $stripWhitespace;

		// Create the pharchive.
		$this->_phar = new Phar($path);
		$this->_phar->setAlias($name);
		$this->_phar->startBuffering();
	}

	/**
	 * Add a directory's files to the Phar package -- without recursing into children.
	 *
	 * @param   string  $path      The absolute filesystem path to the directory to import.
	 * @param   string  $pharPath  The local path within the phar to import the directory files.
	 *
	 * @return  PackagerPhar  This Phar object for chaining.
	 *
	 * @since   1.0
	 * @throws  InvalidArgumentException
	 */
	public function addDirectoryFiles($path, $pharPath)
	{
		// Validate the base path.
		if (!is_dir($path))
		{
			throw new InvalidArgumentException(sprintf('The path %s does not exist.', $path));
		}

		$path = realpath($path);

		// Iterate over the directory contents.
		$directory = new DirectoryIterator($path);
		foreach ($directory as $file)
		{
			if ($file->isFile() && preg_match('/\\.php$/i', $file))
			{
				$this->addFileContents($path . '/' . $file, $pharPath);
			}
		}

		return $this;
	}

	/**
	 * Add a directory's files to the Phar package recursively through child directories.
	 *
	 * @param   string  $path      The absolute filesystem path to the directory to import.
	 * @param   string  $pharPath  The local path within the phar to import the directory.
	 *
	 * @return  PackagerPhar  This Phar object for chaining.
	 *
	 * @since   1.0
	 * @throws  InvalidArgumentException
	 */
	public function addDirectoryRecursive($path, $pharPath)
	{
		// Validate the base path.
		if (!is_dir($path))
		{
			throw new InvalidArgumentException(sprintf('The path %s does not exist.', $path));
		}

		$path = realpath($path);

		// Iterate over the directory files recursively.
		$directory = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path), RecursiveIteratorIterator::SELF_FIRST);
		foreach ($directory as $file)
		{
			if (preg_match('/\\.php$/i', $file))
			{
				$this->addFileContents($file, $pharPath . str_replace($path, '', dirname($file)));
			}
		}

		return $this;
	}

	/**
	 * Add a file to the Phar package.
	 *
	 * @param   string  $path      The absolute filesystem path to the file to import.
	 * @param   string  $pharPath  The local path within the phar to import the file.
	 *
	 * @return  PackagerPhar  This Phar object for chaining.
	 *
	 * @since   1.0
	 * @throws  InvalidArgumentException
	 */
	public function addFile($path, $pharPath)
	{
		// Validate the base path.
		if (!is_file($path))
		{
			throw new InvalidArgumentException(sprintf('The path %s does not exist.', $path));
		}

		$this->addFileContents(realpath($path), $pharPath);

		return $this;
	}

	/**
	 * Set a given file as the stub for the Phar archive.
	 *
	 * @param   string  $path  The absolute filesystem path to the file to use as the Phar stub.
	 *
	 * @return  PackagerPhar  This Phar object for chaining.
	 *
	 * @since   1.0
	 */
	public function setStub($path)
	{
		$stub = file_get_contents($path);

		$this->_phar->setStub($stub);

		return $this;
	}

	/**
	 * Set stubs using the default Phar wrappers for CLI and Web SAPIs.  This isn't really recommended, but
	 * is a fairly safe fallback for standard use cases.
	 *
	 * @param   string  $cliPath  The local path within the Phar to use as the cli stub.
	 * @param   string  $webPath  The local path within the Phar to use as the web stub.
	 *
	 * @return  PackagerPhar  This Phar object for chaining.
	 *
	 * @since   1.0
	 */
	public function setStubs($cliPath = 'import.php', $webPath = null)
	{
		$this->_phar->setStub($this->_phar->createDefaultStub($cliPath, $webPath));

		return $this;
	}


    /**
     * Set code as the stub for the Phar archive.
     *
     * @param   string  $code  The PHP code for the stub
     *
     * @return  PackagerPhar  This Phar object for chaining.
     *
     * @since   1.0
     */
    public function setStubCode($code)
    {
        $this->_phar->setStub($code);

        return $this;
    }

	/**
	 * Write the Phar to disk.
	 *
	 * @return  PackagerPhar  This Phar object for chaining.
	 *
	 * @since   1.0
	 */
	public function write()
	{
		$this->_phar->stopBuffering();

		return $this;
	}

	/**
	 * Import the file at a given absolute path into the Phar at a given local path.  This method will honor
	 * the `stripWhitespace` setting for the file contents.  If true it will strip all comments and whitespace
	 * from the file contents before importing it.
	 *
	 * @param   string  $fullPath  The absolute filesystem path to the file to import.
	 * @param   string  $pharPath  The local path within the phar to import the file.
	 *
	 * @return  void
	 *
	 * @see     _stripWhitespace
	 * @since   1.0
	 */
	protected function addFileContents($fullPath, $pharPath = null)
	{

        // Get the code
        $code = $this->getFileContents($fullPath);

        // Build the Phar local path to the file.
        $pharPath = trim(trim($pharPath, ' /') . '/' . basename($fullPath), ' /');

        // Add the file contents to the Phar.
        $this->_phar->addFromString(
            $pharPath,$code
        );
	}

    /**
    /* get contents of the file at a given absolute path
     * This method will honor the `stripWhitespace` setting for
     * the file contents.  If true it will strip all comments and whitespace
     * from the file contents before importing it.
     *
     * @param   string  $fullPath  The absolute filesystem path to the file to import.
     *
     * @return  string  The php code processed for the namespace and whitespace
     *
     * @see     _stripWhitespace
     * @since   1.0
     */
    public function getFileContents($fullPath)
    {


        // Get the code
        $code = $this->_stripWhitespace ? php_strip_whitespace($fullPath) : file_get_contents($fullPath);

        // return the code processed for the default namespace
        return  $this->processNamespace($code);
    }


    /**
     * get the contents of a stub file
     *
     * @param   string  $path      The absolute filesystem path to the file to import.
     *
     * @return  string  The stub file contents code
     *
     * @since   1.0
     * @throws  InvalidArgumentException
     */
    public function getStubFileContents($path)
    {
        // Validate the base path.
        if (!is_file($path))
        {
            throw new InvalidArgumentException(sprintf('The path %s does not exist.', $path));
        }

        return $this->getFileContents(realpath($path));

    }
    /**
     * Set a given namespace to be prepended to files imported.
     *
     * @param   string  $ns  The absolute namespace to set for all files included from this point on.
     *
     * @return  PackagerPhar  This Phar object for chaining.
     *
     * @since   1.0
     */
    public function setNamespace($ns)
    {
        $this->_namespace = $ns;;

        return $this;
    }


    /**
     * Process code to add/remove default namespace
     * Namespace is always 3 lines:
     *   firstline starts with <?php and ends with a [CR]
     *   secondline starts with namespace and ends with [CR]
     *   thirdline ends with ?>[CR]
     *
     * @param   string  $code The code to be modified
     *
     * @return  string  The modified code.
     *
     * @since   1.0
     */
    public function processNamespace($code = '')
    {
        // remove existing default namespace
        $code = $this->stripNamespace($code);

        // add new default namespace
        $code = $this->prependNamespace($code);

        return $code;
    }

    /**
     * Prepends the configured namespace format to a codestring.
     * Namespace is always 3 lines:
     *   firstline starts with <?php and ends with a [CR]
     *   secondline starts with namespace and ends with [CR]
     *   thirdline ends with ?>[CR]
     *
     * @param   string  $code The code to be modified
     *
     * @return  string  The modified code.
     *
     * @since   1.0
     */
    public function prependNamespace($code = '')
    {

        // only set a default namespace if one is defined
        if ($this->_namespace != '') {
            $code = "<?php\nnamespace ".$this->_namespace.";\n?>\n".$code;
        }

        return $code;
    }

    /**
     * Strips the default namespace, if any, from the codestring
     * Namespace is always 3 lines:
     *   firstline starts with <?php and ends with a [CR]
     *   secondline starts with namespace and ends with [CR]
     *   thirdline ends with ?>[CR]
     *
     * This allows for comments/clarity to be added to any of the lines
     * as well as additional code specific to that namespace
     *
     * @param   string  $code The code to be modified
     *
     * @return  string  The modified code.
     *
     * @since   1.0
     */
    public function stripNamespace($code = '')
    {


        // convert the code string to an array
       $lines = explode("\n", $code);

        if (count($lines < 3)) {
            // there must be at least 3 lines termineate with CR
            // for there to be a header
            return $code;
        }

        // line 1 must start with opening the php
        $needle = '<?php';
        if (strpos(strtolower($lines[0]),$needle) !== 0) {
            return $code;
        }

        // line 2 must start with the namespace keyword
        $needle = 'namespace';
        if (strpos(strtolower($lines[1]),$needle) !== 0) {
            return $code;
        }

        // line 3 must end with closing the php
        $needle = '?>';
        $mustBe = strlen($lines[2]) - strlen($needle);
        if (strrpos($lines[2],$needle) !== $mustBe) {
            return $code;
        }

        // all conditions are set, strip the first 3 lines
        $lines = array_slice($lines, 3);
        return implode('\n', $lines);
    }

}
