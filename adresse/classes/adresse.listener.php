<?php
/**
* @package   lizmap
* @subpackage adresse
* @author    Pierre DRILLIN
* @copyright 2020 3liz
* @link      http://3liz.com
* @license    Mozilla Public Licence
*/

class adresseListener extends jEventListener{

   function ongetMapAdditions ($event) {

        // vérifier que le repository et le project correspondent à un projet lizmap
        $repository = $event->repository;
        $project = $event->project;
        $p = lizmap::getProject($repository.'~'.$project);
        if( !$p ){
             return;
        }

        if(!jAcl2::check('lizmap.tools.edition.use', $repository)) {
          return;
        }

        // vérifier que le projet contient la couche point_adresse et la couche voie

        $l = $p->findLayerByName('point_adresse');
        $vl = $p->findLayerByName('voie');
        if(!$l){
          return;
        }

        $layer = $p->getLayer($l->id);
        if (!$layer->isEditable()){
          return;
        }


        if (!$vl){
          return;
        }

        $vlayer = $p->getLayer($vl->id);
        if (!$vlayer->isEditable()){
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
       if(!$juser){
         $user_login = '';
       }else{
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
       $js[] = jUrl::get('jelix~www:getfile', array('targetmodule'=>'adresse', 'file'=>'adresse.js'));

       if($p->findLayerByName('vue_com')){
           $js[] = jUrl::get('jelix~www:getfile', array('targetmodule'=>'adresse', 'file'=>'export_bal.js'));
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
}
?>
