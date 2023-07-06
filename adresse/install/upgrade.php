<?php
/**
 * @author    Pierre DRILLIN
 * @copyright 2021 3liz
 *
 * @see      http://3liz.com
 *
 * @license    Mozilla Public Licence
 */
class adresseModuleUpgrader extends jInstallerModule
{
    public function install()
    {
        // Copy CSS and JS assets
        $this->copyDirectoryContent('../www/css', jApp::wwwPath('adresse/css'), true);
        $this->copyDirectoryContent('../www/js', jApp::wwwPath('adresse/js'), true);
    }
}
