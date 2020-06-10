<?php
/**
* @package   lizmap
* @subpackage adresse
* @author    Pierre DRILLIN
* @copyright 2020 3liz
* @link      http://3liz.com
* @license    Mozilla Public Licence
*/

class update {

  protected $sql = array(
    'reverse'=>'UPDATE adresse.voie SET geom = ST_REVERSE(geom) WHERE id_voie = %s::integer;',
    'validation' =>'UPDATE adresse.point_adresse SET a_valider = False WHERE id_point = %s::integer;',
    'new_validation' =>'UPDATE adresse.point_adresse SET valide = True WHERE id_point = %s::integer;'
  );

  protected function getSql($option) {
      if(isset($this->sql[$option])){
        return $this->sql[$option];
      }
      return Null;
    }

  function query( $sql, $filterParams, $profile='adresse' ) {
      $cnx = jDb::getConnection( $profile );
      $p = $filterParams[0];
      $req = sprintf($sql, $p);
      return $cnx->exec($req);
  }

  /**
  * Get PDF generated by QGIS Server Cadastre plugin
  * @param project Project key
  * @param repository Repository key
  * @param geom Geometry as WKT
  * @param srid Cordiante system identifier
  */

  function apply($repository, $project, $layer, $id, $option) {

        $profile = adresseProfile::get($repository, $project, $layer);
        $this->repository = $repository;
        $this->project = $project;

        $filterParams = array();
        $filterParams[] = $id;

        // Run query
        $sql = $this->getSql($option);
        if(!$sql){
            return Null;
        }
        return $this->query( $sql, $filterParams, $profile );
    }
}
?>
