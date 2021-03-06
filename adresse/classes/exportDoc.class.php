<?php
/**
 * @author    Pierre DRILLIN
 * @copyright 2020 3liz
 *
 * @see      http://3liz.com
 *
 * @license    Mozilla Public Licence
 */
class exportDoc
{
    public function ExportDoc()
    {
    }

    public function exportBal($fileName, $result)
    {
        // Opening file
        $fp = fopen($fileName, 'w');
        $list = array();
        // Adding encode utf8 to the file
        fprintf($fp, chr(0xEF).chr(0xBB).chr(0xBF));
        // Adding first CSV line
        fputcsv($fp, array('cle_interop', 'uid_adresse', 'voie_nom', 'numero', 'suffixe', 'commune_nom', 'position', 'x', 'y', 'long', 'lat', 'source', 'date_derniere_maj'), ';');
        // Adding the data in the CSV file
        foreach ($result as $value) {
            fputcsv($fp, array($value->cle_interop, $value->uid_adresse, $value->voie_nom, $value->numero, $value->suffixe, $value->commune_nom, $value->position, $value->x, $value->y, $value->long, $value->lat, $value->commune_nom, $value->date_derniere_maj), ';');
        }
        fclose($fp);
    }

    public function exportVoieADelib($fileName, $repository, $project, $result, $com)
    {
        $fp = fopen($fileName, 'w');
        $list = array();
        fprintf($fp, chr(0xEF).chr(0xBB).chr(0xBF));
        foreach ($com as $value) {
            fputcsv($fp, array($value->cnom.' - '.$value->cinsee.' - Nombre de Voies : '.$value->nbid), ';');
        }
        foreach ($result as $value) {
            fputcsv($fp, array($value->nom_complet), ';');
        }
        fclose($fp);
    }
}
