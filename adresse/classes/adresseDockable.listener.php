<?php

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

        \jClasses::inc('adresse~adresseSearch');
        $utils = new adresseSearch();

        $profile = $utils->getProfile($lizmap_project, 'v_point_adresse');
        if (!$profile) {
            return;
        }

        \jClasses::inc('adresse~adresseCheck');
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
            $bp = jApp::config()->urlengine['basePath'];
            // dock
            $content = '<lizmap-adresse></lizmap-adresse>';
            $dock = new lizmapMapDockItem(
                'adresse-exports',
                'Gestion des documents',
                $content,
                98,
                $bp . 'assets/adresse/css/export_doc.css',
                $bp . 'assets/adresse/js/export_doc.js',
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
            $bp = jApp::config()->urlengine['basePath'];
            // dock
            $content = '<adresse-certifcat-num></adresse-certifcat-num>';
            $dock = new lizmapMapDockItem(
                'adresse-certificats',
                'Certificats de Numérotation',
                $content,
                99,
                $bp . 'assets/adresse/css/certif_doc.css',
                $bp . 'assets/adresse/js/certif_doc.js',
                array('type' => 'module')
            );
            $event->add($dock);
        }
    }
}
