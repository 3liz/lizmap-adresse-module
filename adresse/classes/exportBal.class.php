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

    foreach ($result as $fields) {
      fputcsv($fp, $fields);
    }

    fclose($fp);
  }
}

?>
