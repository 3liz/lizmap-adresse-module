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
    /**
     * Function to create CSV file for 'BAL'.
     *
     * @param mixed $fileName
     * @param mixed $result
     */
    public function exportBal($fileName, $result)
    {
        // Opening file
        $fp = fopen($fileName, 'w');
        $list = array();
        // Adding encode utf8 to the file
        fprintf($fp, chr(0xEF) . chr(0xBB) . chr(0xBF));
        // Adding first CSV line
        fputcsv(
            $fp,
            array(
                'uid_adresse',
                'cle_interop',
                'commune_insee',
                'commune_nom',
                'commune_deleguee_insee',
                'commune_deleguee_nom',
                'voie_nom',
                'lieudit_complement_nom',
                'numero',
                'suffixe',
                'position',
                'x',
                'y',
                'long',
                'lat',
                'cad_parcelles',
                'source',
                'date_der_maj',
            ),
            ';'
        );
        // Adding the data in the CSV file
        foreach ($result as $value) {
            fputcsv(
                $fp,
                array(
                    $value->uid_adresse,
                    $value->cle_interop,
                    $value->commune_insee,
                    $value->commune_nom,
                    $value->commune_deleguee_insee,
                    $value->commune_deleguee_nom,
                    $value->voie_nom,
                    $value->lieudit_complement_nom,
                    $value->numero,
                    $value->suffixe,
                    $value->position,
                    $value->x,
                    $value->y,
                    $value->long,
                    $value->lat,
                    $value->cad_parcelles,
                    $value->source,
                    $value->date_der_maj,
                ),
                ';'
            );
        }
        fclose($fp);
    }

    /**
     * Function to create CSV file for 'délibérations'.
     *
     * @param mixed $fileName
     * @param mixed $result
     * @param mixed $com
     */
    public function exportVoieADelib($fileName, $result, $com)
    {
        $fp = fopen($fileName, 'w');
        fprintf($fp, chr(0xEF) . chr(0xBB) . chr(0xBF));
        foreach ($com as $value) {
            fputcsv($fp, array($value->cnom . ' - ' . $value->cinsee . ' - Nombre de Voies : ' . $value->nbid), ';');
        }
        foreach ($result as $value) {
            fputcsv($fp, array($value->nom_complet), ';');
        }
        fclose($fp);
    }
}
