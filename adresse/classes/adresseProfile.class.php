<?php
/**
* @package   lizmap
* @subpackage adresse
* @author    Pierre DRILLIN
* @copyright 2020 3liz
* @link      http://3liz.com
* @license    Mozilla Public Licence
*/

class adresseProfile {

    /**
    * Get the Name of the cadastre DB profile
    * @param project Project key
    * @param repository Repository key
    * @param layerName Name of the Parcelle layer
    * @param profile The default cadastre DB profile
    * @return Name of the cadastre DB profile
    */
    public static function get($repository, $project, $layerName, $profile = 'adresse') {
        $p = lizmap::getProject($repository.'~'.$project);
        $layer = $p->findLayerByName($layerName);
        if($layer){
            $layerId = $layer->id;
            $qgisLayer = $p->getLayer($layerId);
            if ($qgisLayer) {
                $profile = $qgisLayer->getDatasourceProfile();
            }
        }
        //jLog::log(json_encode($profile));
        return $profile;
    }

}
