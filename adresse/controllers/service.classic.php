<?php
/**
* @package   lizmap
* @subpackage adresse
* @author    Pierre DRILLIN
* @copyright 2020 3liz
* @link      http://3liz.com
* @license    All rights reserved
*/

class serviceCtrl extends jController {

  function select(){
    $rep = $this->getResponse('json');
    $filterParams = array();

    // vérifier que les paramètres repository, project, geom, srid sont non null ou vide

    $project = $this->param('project');
    $repository = $this->param('repository');
    $geom = $this->param('geom');
    $srid = $this->param('srid');
    $option = $this->param('opt');

    if(!$project){
      $rep->data = array('status'=>'error', 'message'=>'Project not find');
      return $rep;
    }

    if(!$repository){
      $rep->data = array('status'=>'error', 'message'=>'Repository not find');
      return $rep;
    }

    if(!$geom){
      $rep->data = array('status'=>'error', 'message'=>'Geometry not find');
      return $rep;
    }

    if(!$srid){
      $rep->data = array('status'=>'error', 'message'=>'SRID not find');
      return $rep;
    }

    $filterParams[] = $geom;
    $filterParams[] = $srid;

    if(!$option){
      $rep->data = array('status'=>'error', 'message'=>'Option not find');
      return $rep;
    }

    // vérifier que le repository et le project correspondent à un projet lizmap

    $p = lizmap::getProject($repository.'~'.$project);
    if( !$p ){
        $rep->data = array('status'=>'error', 'message'=>'A problem occured while loading project with Lizmap');
        return $rep;
    }

    if (!$p->checkAcl()) {
        $rep->data = array('status'=>'error', 'message'=>jLocale::get('view~default.repository.access.denied'));

        return $rep;
    }

    // vérifier que le projet contient la couche point_adresse

    $l = $p->findLayerByName('point_adresse');
    if(!$l){
      $rep->data = array('status'=>'error', 'message'=>'Layer '.$l->name.' does not exist');
      return $rep;
    }
    $layer = $p->getLayer($l->id);
    if (!$layer->isEditable()) {
      $rep->data = array('status'=>'error', 'message'=>'Layer '.$l->name.' is not Editable');
      return $rep;
    }

    // demander la voie éditable à proximité de la geom

    $autocomplete = jClasses::getService('adresse~search');
    try {
        $result = $autocomplete->getData( $repository, $project, $l->name, $filterParams, $option);
    } catch (Exception $e) {
        $result = Null;
    }

    $rep->data = $result->fetchAll();
    return $rep;
  }

  function update(){
    $rep = $this->getResponse('json');

    // vérifier que les paramètres repository, project, geom, srid sont non null ou vide

    $project = $this->param('project');
    $repository = $this->param('repository');
    $id = $this->param('id');
    $option = $this->param('opt');

    if(!$project){
      $rep->data = array('status'=>'error', 'message'=>'Project not find');
      return $rep;
    }

    if(!$repository){
      $rep->data = array('status'=>'error', 'message'=>'Repository not find');
      return $rep;
    }

    if(!$id){
      $rep->data = array('status'=>'error', 'message'=>'Id not find');
      return $rep;
    }

    if(!$option){
      $rep->data = array('status'=>'error', 'message'=>'Option not find');
      return $rep;
    }

    // vérifier que le repository et le project correspondent à un projet lizmap

    $p = lizmap::getProject($repository.'~'.$project);
    if( !$p ){
        $rep->data = array('status'=>'error', 'message'=>'A problem occured while loading project with Lizmap');
        return $rep;
    }

    if (!$p->checkAcl()) {
        $rep->data = array('status'=>'error', 'message'=>jLocale::get('view~default.repository.access.denied'));

        return $rep;
    }

    // vérifier que le projet contient la couche voie et  point_adresse

    $l = $p->findLayerByName('voie');
    if(!$l){
      $rep->data = array('status'=>'error', 'message'=>'Layer voie does not exist');
      return $rep;
    }
    $layer = $p->getLayer($l->id);
    if (!$layer->isEditable()) {
      $rep->data = array('status'=>'error', 'message'=>'Layer '.$l->name.' is not Editable');
      return $rep;
    }

    $pl = $p->findLayerByName('point_adresse');
    if(!$pl){
      $rep->data = array('status'=>'error', 'message'=>'Layer point_adresse does not exist');
      return $rep;
    }
    $player = $p->getLayer($pl->id);
    if (!$player->isEditable()) {
      $rep->data = array('status'=>'error', 'message'=>'Layer '.$pl->name.' is not Editable');
      return $rep;
    }

    // demander la voie éditable à proximité de la geom

    $autocomplete = jClasses::getService('adresse~update');
    try {
        $result = $autocomplete->apply( $repository, $project, $l->name, $id, $option);
    } catch (Exception $e) {
        $result = Null;
    }
    $message='';
    $typeRes='';

    if($result){
      $message = 'Update exécuté avec succès';
      $typeRes = 'success';
    }else{
      $message = 'Error lors de l\'update';
      $typeRes = 'error';
    }

    $rep->data = array('success' => ''.$result, 'type'=>$typeRes, 'message' => $message);
    return $rep;
  }

  function export(){

    $test='#^[0-9]{1}[0-9AB]{1}[0-9]{3}$#';
    $filterParams = array();
    $project = $this->param('project');
    $repository = $this->param('repository');
    $insee = $this->param('insee');
    $option = 'export';

    $rep = $this->getResponse('json');
    if(!$project){
      $rep->data = array('status'=>'error', 'message'=>'Project not found');
      return $rep;
    }

    if(!$repository){
      $rep->data = array('status'=>'error', 'message'=>'Repository not found');
      return $rep;
    }

    if(!$insee){
      $rep->data = array('status'=>'error', 'message'=>'Code insee not found');
      return $rep;
    }
    if(!preg_match($test, $insee)){
      $rep->data = array('status'=>'error', 'message'=>'Code insee not available');
      return;
    }
    $filterParams[] = $insee;

    if(!$option){
      $rep->data = array('status'=>'error', 'message'=>'Option not found');
      return $rep;
    }

    // vérifier que le repository et le project correspondent à un projet lizmap

    $p = lizmap::getProject($repository.'~'.$project);
    if( !$p ){
        $rep->data = array('status'=>'error', 'message'=>'A problem occured while loading project with Lizmap');
        return $rep;
    }

    if (!$p->checkAcl()) {
        $rep->data = array('status'=>'error', 'message'=>jLocale::get('view~default.repository.access.denied'));
        return $rep;
    }

    // vérifier que le projet contient la couche point_adresse

    $l = $p->findLayerByName('point_adresse');
    if(!$l){
      $rep->data = array('status'=>'error', 'message'=>'Layer '.$l->name.' does not exist');
      return $rep;
    }

    $autocomplete = jClasses::getService('adresse~search');
    try {
        $result = $autocomplete->getData( $repository, $project, 'point_adresse', $filterParams, $option);
    } catch (Exception $e) {
        $result = Null;
    }
    $leBal = jClasses::getService('adresse~exportBal');
    $tempPath = jApp::tempPath('export');

    jFile::createDir($tempPath);

    $fileName = tempnam($tempPath, 'exportbal-');

    if($result != Null){
      $leBal->exportCSV($fileName, $result);

    }else {
      $rep->data = array('status'=>'error', 'message'=>'Aucun résultat trouvé');
      return $rep;
    }
    $rep = $this->getResponse('binary');

    $rep->deleteFileAfterSending = true;
    $rep->fileName = $fileName;
    $rep->outputFileName = date(ymd).'_bal_'.$insee.'.csv';
    $rep->mimeType = 'text/csv';
    $rep->doDownload = true; // true si tu veux que l'utilisateur ait une boite de dialogue "sauver sous"

    return $rep;

  }
}
