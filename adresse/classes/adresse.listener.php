<?php

/**
 * @package   lizmap
 * @subpackage adresse
 * @author    Pierre DRILLIN
 * @copyright 2020 3liz
 * @link      http://3liz.com
 * @license    Mozilla Public Licence
 */

class adresseListener extends jEventListener
{
    public function ongetMapAdditions($event)
    {

    // vérifier que le repository et le project correspondent à un projet lizmap
        $repository = $event->repository;
        $project = $event->project;
        $p = lizmap::getProject($repository . '~' . $project);
        if (!$p) {
            return;
        }

        if (!jAcl2::check('lizmap.tools.edition.use', $repository)) {
            return;
        }

        // vérifier que le projet contient la couche point_adresse et la couche voie

        $l = $p->findLayerByName('point_adresse');
        $vl = $p->findLayerByName('voie');
        if (!$l) {
            return;
        }

        $layer = $p->getLayer($l->id);
        if (!$layer->isEditable()) {
            return;
        }


        if (!$vl) {
            return;
        }

        $vlayer = $p->getLayer($vl->id);
        if (!$vlayer->isEditable()) {
            return;
        }

        $dLayer = $layer->getEditionCapabilities();
        $eLayer = $vlayer->getEditionCapabilities();

        // Check if user groups intersects groups allowed by project editor
        // If user is admin, no need to check for given groups
        if (jAuth::isConnected() and !jAcl2::check('lizmap.admin.repositories.delete') and property_exists($eLayer, 'acl') and $eLayer->acl) {
            // Check if configured groups white list and authenticated user groups list intersects
            $editionGroups = $eLayer->acl;
            $editionGroups = array_map('trim', explode(',', $editionGroups));
            if (is_array($editionGroups) and count($editionGroups) > 0) {
                $userGroups = jAcl2DbUserGroup::getGroups();
                if (!array_intersect($editionGroups, $userGroups)) {
                    return;
                }
            }
        }

        $juser = jAuth::getUserSession();
        if (!$juser) {
            $user_login = '';
        } else {
            $user_login = $juser->login;
        }

        $js = array();
        $jscode = array();
        $css = array();

        $adresseConfig = array();

        $adresseConfig['user'] = $user_login;

        $adresseConfig['point_adresse'] = array();
        $adresseConfig['point_adresse']['id'] = $layer->getId();
        $adresseConfig['point_adresse']['name'] = $layer->getName();

        $adresseConfig['voie'] = array();
        $adresseConfig['voie']['id'] = $vlayer->getId();
        $adresseConfig['voie']['name'] = $vlayer->getName();

        $adresseConfig['urls'] = array();
        $adresseConfig['urls']['select'] = jUrl::get('adresse~service:select');
        $adresseConfig['urls']['update'] = jUrl::get('adresse~service:update');
        $adresseConfig['urls']['export'] = jUrl::get('adresse~service:export');

        $bp = jApp::config()->urlengine['basePath'];

        $js = array();
        $js[] = jUrl::get('jelix~www:getfile', array('targetmodule' => 'adresse', 'file' => 'adresse.js'));

        if ($p->findLayerByName('v_commune')) {
            $js[] = jUrl::get('jelix~www:getfile', array('targetmodule' => 'adresse', 'file' => 'export_doc.js'));
        }

        if ($p->findLayerByName('v_certificat')) {
            $js[] = jUrl::get('jelix~www:getfile', array('targetmodule' => 'adresse', 'file' => 'certif_doc.js'));
        }

        $jscode = array(
      'var adresseConfig = ' . json_encode($adresseConfig)
    );

        $event->add(
            array(
        'js' => $js,
        'jscode' => $jscode
      )
        );
    }

    public function onLizmapEditionSaveCheckForm($event)
    {
        $form = $event->form;
        if (!$form) {
            return;
        }

        // vérifier que le repository et le project correspondent à un projet lizmap
        $repository = $event->repository;
        if (!$repository) {
            return;
        }
        $project = $event->project;
        if (!$project) {
            return;
        }

        if (!jAcl2::check('lizmap.tools.edition.use', $repository->getKey())) {
            return;
        }

        // vérifier la couche
        $layer = $event->layer;
        if (!$layer) {
            return;
        }
        if (!in_array($layer->getName(), array('point_adresse', 'voie'))) {
            return;
        }
        if (!$layer->isEditable()) {
            return;
        }

        // Check if user groups intersects groups allowed by project editor
        // If user is admin, no need to check for given groups
        $eLayer = $layer->getEditionCapabilities();
        if (jAuth::isConnected() and !jAcl2::check('lizmap.admin.repositories.delete') and property_exists($eLayer, 'acl') and $eLayer->acl) {
            // Check if configured groups white list and authenticated user groups list intersects
            $editionGroups = $eLayer->acl;
            $editionGroups = array_map('trim', explode(',', $editionGroups));
            if (is_array($editionGroups) and count($editionGroups) > 0) {
                $userGroups = jAcl2DbUserGroup::getGroups();
                if (!array_intersect($editionGroups, $userGroups)) {
                    return;
                }
            }
        }

        // User login
        $juser = jAuth::getUserSession();
        if (!$juser) {
            $user_login = '';
        } else {
            $user_login = $juser->login;
        }

        // forcer la valeur du champs createur
        if ($form->getControl('createur')) {
            $featureId = $event->featureId;
            if (!$featureId) {
                // Creation d'un nouvel objet
                $form->setData('createur', $user_login);
            } else {
                // Mise à jour d'un objet
                $modifiedControls = $form->getModifiedControls();
                // Conservation de la valeur du champs createur
                if (array_key_exists('createur', $modifiedControls) && $modifiedControls['createur']) {
                    $form->setData('createur', $modifiedControls['createur']);
                }
            }
        }
        // forcer la valeur du champs modificateur
        if ($form->getControl('modificateur')) {
            $form->setData('modificateur', $user_login);
        }

        $event->add(
            array(
                'check' => true
            )
        );
    }
}
