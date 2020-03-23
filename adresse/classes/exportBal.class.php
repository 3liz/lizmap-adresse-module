<?php
/**
 *
 */
class exportBAL{

  function exportBAL()
  {
  }

  function exportCSV($fileName, $result){
    $fp = fopen($fileName, 'w');
    $list = array();

    foreach ($resultset as $value) {
      $list[] = array($value->cle_interop, $value->uid_adresse, $value->voie_nom);
    }
    foreach ($list as $fields) {
     fputcsv($fp, $fields);
    }
    fclose($fp);
  }
}

?>
