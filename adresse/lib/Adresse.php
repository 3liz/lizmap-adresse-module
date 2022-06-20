<?php

class Adresse
{
    /**
     * @var adresseSearch
     */
    protected $utils;
    protected $profile;

    public function __construct(adresseSearch $utils, $profile)
    {
        $this->utils = $utils;
        $this->profile = $profile;
    }

    /**
     * Function to get SQL for given option.
     *
     * @param string $option
     *
     * @return null|string $sql
     */
    protected function getSql($option = 'index')
    {
        $sql = array(
            'idvoie' => 'SELECT id_voie, nom_complet, type_num FROM( SELECT id_voie, type_num, nom_complet, ST_Distance(ST_geomfromtext($1, $2),geom) as dist
              FROM adresse.voie
              WHERE statut_voie_num IS FALSE ORDER BY dist LIMIT 1) AS d;',
            'classique' => 'SELECT * FROM adresse.calcul_num_adr(ST_geomfromtext($1,$2))',
            'metrique' => 'SELECT * FROM adresse.calcul_num_metrique(ST_geomfromtext($1,$2))',
            'bal' => 'SELECT * FROM adresse.v_export_bal  WHERE commune_insee = $1',
            'version' => 'SELECT me_version FROM adresse.metadata',
            'voie_delib' => 'SELECT DISTINCT v.nom_complet, v.nom_complet_maj FROM adresse.voie v JOIN adresse.commune c ON ST_intersects(v.geom, c.geom) WHERE c.insee_code = $1::text',
            'commune' => 'SELECT c.commune_nom as cnom, c.insee_code as cinsee, COUNT(v.id_voie) as nbid FROM adresse.commune c, adresse.voie v WHERE c.insee_code = $1::text AND ST_intersects(c.geom, v.geom) AND v.delib = true group by c.commune_nom, c.insee_code',
            'zip1' => "SELECT d.lien, d.nom_doc FROM adresse.document d, adresse.commune c WHERE d.id_commune = c.id_com AND c.insee_code = $1 AND d.type_document = 'delib' ORDER BY d.date_doc DESC LIMIT 1;",
            'zipAll' => "SELECT d.lien, d.nom_doc FROM adresse.document d, adresse.commune c WHERE d.id_commune = c.id_com AND c.insee_code = $1 AND d.type_document = 'delib' ORDER BY d.date_doc DESC;",
            'reverse' => 'UPDATE adresse.voie SET geom = ST_REVERSE(geom) WHERE id_voie = $1::integer RETURNING id_voie;',
            'validation' => 'UPDATE adresse.point_adresse SET a_valider = False WHERE id_point = $1::integer RETURNING id_point;',
            'new_validation' => 'UPDATE adresse.point_adresse SET valide = True WHERE id_point = $1::integer RETURNING id_point;',
        );

        return $sql[$option];
    }

    /**
     * Function to run some action in the database.
     *
     * @param string $option
     * @param mixed  $params
     *
     * @return array
     */
    protected function runDataBaseAction($option = 'index', $params)
    {
        $sql = $this->getSql($option);
        if (!$sql) {
            return array(
                'error',
                'Aucun requêtes SQL trouvées',
            );
        }

        $messages = array(
            'idvoie' => "Erreur lors de la sélection de la voie pour l'adresse créée",
            'classique' => 'Impossible de calculer le numéro du point',
            'metrique' => 'Impossible de calculer le numéro du point',
            'commune' => "Aucune voie à délibérer n'a été trouvées",
            'bal' => "Auccune données trouvées pour l'export BAL",
            'voie_delib' => "Aucune voie à délibérer n'a été trouvées",
            'zip1' => "Auccune données trouvées pour l'export SNA",
            'zipAll' => "Auccune données trouvées pour l'export SNA",
        );

        list($status, $msgError, $data) = $this->utils->execQuery($sql, $this->profile, $params);
        if ($status == 'error') {
            return array(
                $status,
                $msgError,
            );
        }

        // check if data not null
        if (!$data) {
            return array(
                'error',
                $messages[$option],
            );
        }

        return array('success', $data);
    }

    /**
     * Function to use runDatabaseAction with given option and return data.
     *
     * @param string $option
     * @param mixed  $params
     *
     * @return array
     */
    public function executeMethod($option, $params)
    {
        list($status, $result) = $this->runDatabaseAction($option, $params);
        if ($status == 'error') {
            return array(
                $status,
                $result,
            );
        }

        return array('success', $result);
    }
}
