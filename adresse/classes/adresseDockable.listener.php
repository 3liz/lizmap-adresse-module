<?php
/**
 * @author    Pierre DRILLIN
 * @contributor René-Luc D'Hont
 * @contributor Laurent Jouanneau
 * @copyright 2021-2022 3liz
 *
 * @see      http://3liz.com
 *
 * @license    Mozilla Public Licence
 */
class adresseDockableListener extends \jEventListener
{
    public function onmapMiniDockable($event)
    {
        $repository = $event->repository;
        $project = $event->project;

        $lizmap_project = lizmap::getProject($repository . '~' . $project);
        if (!$lizmap_project) {
            return;
        }

        $utils = new adresseSearch();

        $profile = $utils->getProfile($lizmap_project, 'v_point_adresse');
        if (!$profile) {
            return;
        }

        $adresseCheck = new adresseCheck($utils, $lizmap_project, $profile);

        list($code, $message) = $adresseCheck->allCheck();
        if ($code == 'error') {
            \jLog::log($message, 'warning');

            return;
        }

        list($code, $message) = $adresseCheck->checkProjectLayer('v_commune');
        if ($code == 'error') {
            \jLog::log($message, 'warning');
        }

        // Project name must contains 'adresse' to enable the module
        if ($code !== 'error') {
            $bp = jApp::urlBasePath();
            // dock
            $content = '<lizmap-adresse></lizmap-adresse>';
            $dock = new lizmapMapDockItem(
                'adresse-exports',
                'Gestion des documents',
                $content,
                98,
                $bp . 'adresse/css/export_doc.css',
                $bp . 'adresse/js/export_doc.js',
                array('type' => 'module')
            );
            $event->add($dock);
        }

        list($code, $message) = $adresseCheck->checkProjectLayer('v_certificat');
        if ($code == 'error') {
            \jLog::log($message, 'warning');
        }

        // Project name must contains 'adresse' to enable the module
        if ($code !== 'error') {
            $bp = jApp::urlBasePath();
            // dock
            $content = '<adresse-certifcat-num></adresse-certifcat-num>';
            $dock = new lizmapMapDockItem(
                'adresse-certificats',
                'Certificats de Numérotation',
                $content,
                99,
                $bp . 'adresse/css/certif_doc.css',
                $bp . 'adresse/js/certif_doc.js',
                array('type' => 'module')
            );
            $event->add($dock);
        }
    }
}
