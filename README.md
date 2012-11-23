Joomla Packager
===============

The Joomla packager application is a self-contained [Phar](http://php.net/manual/book.phar.php) built using the Joomla Platform.  Its purpose is to build PHP archives for easy deployment and management.  This can be used for Joomla Platform applications, Joomla CMS extensions, or really any other PHP application or libraries.

## Requirements

* PHP 5.3+

## Usage

The packager requires an XML manifest to build a package.  By default it will look for `packager.xml` in the current working directory.  You can optionally tell it what XML manifest to use by passing the `-f <file>` argument during execution.


Using the Joomla Packager is really as hard as writing an XML manifest for your package.  You can see below for how to write the manifest files for your application or package.


## XML Manifest

The XML manifest files read by the Joomla packager contain a root `<packager>` element and a single child `<code>` of the `<packager>` element.

### The &lt;packager&gt; Element

The `<packager>` element is the root node in the XML manifest file.  As such it is required for all manifests and currently has the following possible attributes:

<table>
	<tr>
		<th>Attribute</th>
		<th>Required</th>
		<th>Description</th>
	</tr>
	<tr>
		<td>alias</td>
		<td>False</td>
		<td>By default the alias will be set to the filename of the destination phar file.</td>
	</tr>
	<tr>
		<td>destination</td>
		<td>True</td>
		<td>The file path for the phar file to be written.  This can be either absolute or relative to the XML manifest.</td>
	</tr>
	<tr>
		<td>minify</td>
		<td>False</td>
		<td>If you want to have all php files imported into the phar file stripped of whitespace and comments set this attribute to "true".  This decreases the overall package size dramatically without introducing compression.  Note that this will make debugging infinitely more difficult since line numbers collapse and everything will likely be on line 2.  You've been warned.</td>
	</tr>
</table>

### The &lt;code&gt; Element

The `<code>` element is the immediate child of the root `<packager>` element.  This element contains the description of all of the files that are pacakged into the phar.

<table>
	<tr>
		<th>Attribute</th>
		<th>Required</th>
		<th>Description</th>
	</tr>
	<tr>
		<td>stub</td>
		<td>False</td>
		<td>The contents of the file at this <strong>absolute file path</strong> are executed when the Phar is included/executed.  This is a great place to put class loaders or front controllers.  See <a href="http://php.net/manual/phar.setstub.php">Phar::setStub()</a>.  <em>Note: you don't have to import this file into the Phar in any other way.</em></td>
	</tr>
</table>

### The &lt;file&gt; Element

The `<file>` element can live as a child of the `<code>` tag or the `<git>` tag.  It imports a file into the phar either from the local filesystem [or a specific git repository if within a `<git>` tag].

<table>
	<tr>
		<th>Attribute</th>
		<th>Required</th>
		<th>Description</th>
	</tr>
	<tr>
		<td>localPath</td>
		<td>False</td>
		<td>If the <strong>localPath</strong> attribute is set then the given file will be imported into the Phar underneath the path given as the attribute value.  For example: <code>&lt;file&gt;path/to/foo.php&lt;/file&gt;</code> would mean that <code>foo.php</code> is simply imported into the root path within the Phar.  If the <strong>localPath</strong> attribute is set that would change: <code>&lt;file localPath="bar/baz"&gt;path/to/foo.php&lt;/file&gt;</code> would mean that <code>foo.php</code> is  imported into the Phar underneath the <strong>localPath</strong> like <code>bar/baz/foo.php</code>.  <em>Note: if <code>&lt;file&gt;</code> is within a <code>&lt;git&gt;</code> tag it is possible to have the <strong>localPath</strong> be appended to a <strong>localPath</strong> set within the <code>&lt;git&gt;</code> tag itself.</em></td>
	</tr>
		<tr>
        	<td>namespace</td>
        	<td>null</td>
        	<td>If the <strong>namespace</strong> attribute is set then for each php file included will have this namespace declaration added to the beginning of the code.</td>
        </tr>
</table>

### The &lt;folder&gt; Element

The `<folder>` element can live as a child of the `<code>` tag or the `<git>` tag.  It imports a folder into the phar either from the local filesystem [or a specific git repository if within a `<git>` tag].

<table>
	<tr>
		<th>Attribute</th>
		<th>Required</th>
		<th>Description</th>
	</tr>
	<tr>
		<td>localPath</td>
		<td>False</td>
		<td>If the <strong>localPath</strong> attribute is set then the given folder will be imported into the Phar underneath the path given as the attribute value.  For example: <code>&lt;folder&gt;path/to/foo&lt;/folder&gt;</code> would mean that <code>foo</code> is simply imported into the root path within the Phar.  If the <strong>localPath</strong> attribute is set that would change: <code>&lt;folder localPath="bar/baz"&gt;path/to/foo&lt;/folder&gt;</code> would mean that <code>foo</code> is  imported into the Phar underneath the <strong>localPath</strong> like <code>bar/baz/foo</code>.  <em>Note: if <code>&lt;folder&gt;</code> is within a <code>&lt;git&gt;</code> tag it is possible to have the <strong>localPath</strong> be appended to a <strong>localPath</strong> set within the <code>&lt;git&gt;</code> tag itself.</em></td>
	</tr>
	<tr>
		<td>recursive</td>
		<td>False</td>
		<td>If you want to have all children of the folder recursively imported into the phar set this attribute to "true".  Otherwise only the files in the exact folder will be imported.</td>
	</tr>
		<tr>
        	<td>namespace</td>
        	<td>null</td>
        	<td>If the <strong>namespace</strong> attribute is set then for each php file included will have this namespace declaration added to the beginning of the code.</td>
        </tr>
</table>

### The &lt;git&gt; Element

The `<git>` element can live as a child of the `<code>` tag only.  It allows you to package parts of an existing git repository into your phar.  It will clone the repository specified in the `<git>` tag into a temporary location and then import any `<file>`s or `<folder>`s specified within the `<git>` tag.

<table>
	<tr>
		<th>Attribute</th>
		<th>Required</th>
		<th>Description</th>
	</tr>
	<tr>
		<td>url</td>
		<td>True</td>
		<td>The URL of the git repository from which you want to import files.  Anything that is valid input to <code>git clone</code> should be fine.</td>
	</tr>
	<tr>
		<td>ref</td>
		<td>True</td>
		<td>The branch or tag of the git repository you want to use for importing files.</td>
	</tr>
	<tr>
		<td>localPath</td>
		<td>False</td>
		<td>If the <strong>localPath</strong> attribute is set then any files or folders will be imported into the phar underneath the path given as the attribute value.</td>
	</tr>
		<tr>
        	<td>namespace</td>
        	<td>null</td>
        	<td>If the <strong>namespace</strong> attribute is set then for each php file included will have this namespace declaration added to the beginning of the code.</td>
        </tr>
</table>

#### Example: Import a file from a git repository into Phar root.
```xml
<git url="http://domain.com/repository.git" ref="master">
	<file>foo.php</file>
</git>
```
results in `phar://foo.php`.

#### Example: Import a libraries from a git repository into Phar path `lib/`.
```xml
<git url="http://domain.com/repository.git" ref="master" localPath="lib">
	<folder recursive="true">libraries</file>
</git>
```
or

```xml
<git url="http://domain.com/repository.git" ref="master">
	<folder recursive="true" localPath="lib">libraries</file>
</git>
```
both result in `phar://lib/{LIBRARIES}`.

### The &lt;platform&gt; Element

The `<platform>` element is used for packaging up the Joomla Platform into the phar.  You can specify whether legacy should be enabled and also optionally which packages you want to import using the `<packages>` and `<package>` tags.

<table>
	<tr>
		<th>Attribute</th>
		<th>Required</th>
		<th>Description</th>
	</tr>
	<tr>
		<td>version</td>
		<td>True</td>
		<td>The Joomla Platform version you would like to import.  This can be any version number ["11.4", "12.1", etc] or "master".</td>
	</tr>
	<tr>
		<td>legacy</td>
		<td>False</td>
		<td>If you want to import the legacy libraries for the Joomla Platform into the phar set this attribute to "true".</td>
	</tr>
	<tr>
	    <td>hard</td>
        <td>True</td>
    	<td>If you want to exclude the base Joomla Platform libraries needed in order to use the platform set this flag to "false."</td>
    </tr>
    <tr>
    	<td>external</td>
        <td>False</td>
        <td>If you want to exclude the external libraries for the Joomla Platform into the phar set this attribute to "false".</td>
    </tr>
	<tr>
		<td>localPath</td>
		<td>False</td>
		<td>If the <strong>localPath</strong> attribute is set then the platform will be imported into the phar underneath the path given as the attribute value.</td>
	</tr>
	<tr>
    	<td>namespace</td>
    	<td>null</td>
    	<td>If the <strong>namespace</strong> attribute is set then for each php file included will have this namespace declaration added to the beginning of the code.</td>
    </tr>
</table>

#### The &lt;packages&gt; Element

The `<packages>` element may only be underneath the `<platform>` tag and serves as a container for `<package>` elements.  Additionally it has an optional attribute **exclude** which affects how the contents of the `<package>` tags are treated.

<table>
	<tr>
		<th>Attribute</th>
		<th>Required</th>
		<th>Description</th>
	</tr>
	<tr>
		<td>exclude</td>
		<td>False</td>
		<td>If you want to import all Joomla Platform packages <em>except</em> the ones listed in child <code>&lt;package&gt;</code> tags set this attribute to "true".  Otherwise only the packages listed in child <code>&lt;package&gt;</code> tags will be imported.  <em>Note: if no child <code>&lt;package&gt;</code> tags are present then all packages will be imported regardless of this attribute.</em></td>
	</tr>
</table>

#### The &lt;package&gt; Element

The `<package>` element may only be underneath the `<packages>` tag and has no attributes.  It is used to enumerate the Joomla Platform packages to be imported into the phar.

#### Example: Import the Joomla Platform version 12.1 without legacy at path `lib/`.
```xml
<platform version="12.1" localPath="lib" />
```

#### Example: Import the Joomla Platform [log package only] at version 11.4 without legacy at path `lib/log/`.
```xml
<platform version="11.4" localPath="lib/log">
	<packages>
		<package name="log" />
	</packages>
</platform>
```

#### Example: Import the Joomla Platform [everything but the GitHub package] at current master with legacy at path `libraries/`.
```xml
<platform version="master" legacy="true" localPath="libraries">
	<packages exclude="true">
		<package name="github" />
	</packages>
</platform>
```

### Example XML Manifest
```xml
<?xml version="1.0" encoding="UTF-8"?>
<packager minify="true" destination="foo.phar">
	<code stub="main.stub.php">
		<!-- Import the local folder "foo" into /foo in the phar. -->
		<folder recursive="true" localPath="foo">foo</folder>
		
		<!-- Import the lib folder of this repo into /magic in the phar. -->
		<git url="git://domain.com/repo-path.git" ref="3.0">
			<folder recursive="true" localPath="magic">lib</folder>
		</git>
		
		<!-- We only want to import a small subset of the Joomla Platform. -->
		<platform version="12.1" localPath="lib">
			<packages>
				<package name="application" />
				<package name="registry" />
				<package name="input" />
				<package name="filter" />
				<package name="event" />
				<package name="log" />
				<package name="object" />
			</packages>
		</platform>
	</code>	
</packager>
```
