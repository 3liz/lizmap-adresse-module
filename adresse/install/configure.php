<?php
/**
 * @author    Laurent Jouanneau
 * @copyright 2022 3liz
 *
 * @see      http://3liz.com
 *
 * @license    Mozilla Public Licence
 */

/**
 * Configurator for Lizmap 3.6+
 */
class adresseModuleConfigurator extends \Jelix\Installer\Module\Configurator {

    public function getDefaultParameters() {
        return array();
    }

    function configure(\Jelix\Installer\Module\API\ConfigurationHelpers $helpers)
    {
        // Copy CSS and JS assets
        $helpers->copyDirectoryContent('../www/css', jApp::wwwPath('adresse/css'), true);
        $helpers->copyDirectoryContent('../www/js', jApp::wwwPath('adresse/js'), true);
    }
}