<?php
/**
 * script to automate the generation of the
 * package.xml file.
 *
 * $Id$
 *
 * @author      Stephan Schmidt <schst@php-tools.net>
 * @package     HTTP_SessionServer
 * @subpackage  Tools
 */

/**
 * uses PackageFileManager
 */ 
require_once 'PEAR/PackageFileManager.php';

/**
 * current version
 */
$version = '0.3.0';

/**
 * current state
 */
$state = 'alpha';

/**
 * release notes
 */
$notes = <<<EOT
Initial PEAR release.
EOT;

/**
 * package description
 */
$description = <<<EOT
HTTP_SessionServer is a simple PHP based daemon that helps you maintaining state between physically different hosts.
HTTP_SessionServer implements a very simple protocol to store and retrieve data on the server. The storage backend is driver based, currently only a storage for the filesystem has been implemented, but you may easily change this.
HTTP_SessionServer comes with a matching client implementation using Net_Socket as well as a session save handler.
EOT;

$package = new PEAR_PackageFileManager();

$result = $package->setOptions(array(
    'package'           => 'HTTP_SessionServer',
    'summary'           => 'Daemon to store session data that can be accessed via a simple protocol.',
    'description'       => $description,
    'version'           => $version,
    'state'             => $state,
    'license'           => 'PHP License',
    'filelistgenerator' => 'cvs',
    'ignore'            => array('package.php', 'package.xml'),
    'notes'             => $notes,
    'simpleoutput'      => true,
    'baseinstalldir'    => 'HTTP',
    'packagedirectory'  => './',
    'dir_roles'         => array('docs' => 'doc',
                                 'examples' => 'doc',
                                 'tests' => 'test',
                                 )
    ));

if (PEAR::isError($result)) {
    echo $result->getMessage();
    die();
}

$package->addMaintainer('schst', 'lead', 'Stephan Schmidt', 'schst@php-tools.net');
$package->addMaintainer('luckec', 'developer', 'Carsten Lucke', 'luckec@php.net');

$package->addDependency('PEAR', '', 'has', 'pkg', false);
$package->addDependency('php', '4.3.0', 'ge', 'php', false);
$package->addDependency('Net_Server', '0.12.0', 'ge', 'pkg', false);
$package->addDependency('Net_Socket', '', 'has', 'pkg', false);
$package->addDependency('pcntl', '', 'has', 'ext', false);
$package->addDependency('DB', '', 'has', 'pkg', true);

if (isset($_GET['make']) || (isset($_SERVER['argv'][1]) && $_SERVER['argv'][1] == 'make')) {
    $result = $package->writePackageFile();
} else {
    $result = $package->debugPackageFile();
}

if (PEAR::isError($result)) {
    echo $result->getMessage();
    die();
}
?>