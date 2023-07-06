<?php
/**
 * @author    Pierre DRILLIN
 * @copyright 2020 3liz
 *
 * @see      http://3liz.com
 *
 * @license    Mozilla Public Licence
 */

/**
 * Installer for lizmap <=3.5
 */
class adresseModuleInstaller extends jInstallerModule
{
    public function install()
    {
        // Copy CSS and JS assets
        $this->copyDirectoryContent('../www/css', jApp::wwwPath('adresse/css'));
        $this->copyDirectoryContent('../www/js', jApp::wwwPath('adresse/js'));
    }
}
