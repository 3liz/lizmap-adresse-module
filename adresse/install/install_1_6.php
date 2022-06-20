<?php
/**
 * @author    Pierre DRILLIN
 * @copyright 2020 3liz
 *
 * @see      http://3liz.com
 *
 * @license    Mozilla Public Licence
 */
class adresseModuleInstaller extends jInstallerModule
{
    public function install()
    {
        //if ($this->firstDbExec())
        //    $this->execSQLScript('sql/install');

        /*if ($this->firstExec('acl2')) {
            jAcl2DbManager::addSubject('my.subject', 'adresse~acl.my.subject', 'subject.group.id');
            jAcl2DbManager::addRight('admins', 'my.subject'); // for admin group
        }
        */
        // Copy CSS and JS assets
        $this->copyDirectoryContent('../www/css', jApp::wwwPath('assets/adresse/css'));
        $this->copyDirectoryContent('../www/js', jApp::wwwPath('assets/adresse/js'));
    }
}
