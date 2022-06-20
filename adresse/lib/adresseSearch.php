<?php
/**
 * @author    Pierre DRILLIN
 * @copyright 2020 3liz
 *
 * @see      http://3liz.com
 *
 * @license    Mozilla Public Licence
 */
class adresseSearch
{
    /**
     * Query database and return json data.
     *
     * @param string     $sql
     * @param string     $profile
     * @param null|array $params
     *
     * @return array
     */
    private function query($sql, $profile, $params = null)
    {
        $cnx = \jDb::getConnection($profile);
        $cnx->beginTransaction();
        try {
            $resultset = $cnx->prepare($sql);
            $resultset->execute($params);
            $data = $resultset->fetchAll();
            $cnx->commit();
        } catch (\Exception $e) {
            $cnx->rollback();

            return array(
                'error',
                'A database error occured while executing the query',
                null,
            );
        }

        return array('success', 'Query executed with success', $data);
    }

    /**
     * Get a adresse object.
     *
     * @param string $sql
     * @param string $profile
     * @param array  $params
     *
     * @return array
     */
    public function execQuery($sql, $profile, $params = array())
    {
        list($status, $msgError, $data) = $this->query($sql, $profile, $params);
        if ($status == 'error') {
            return array(
                'error',
                $msgError,
                null,
            );
        }

        return array(
            'success',
            $msgError,
            $data,
        );
    }

    /**
     * Get profile from project and layer name.
     *
     * @param \Lizmap\Project\Project $lizmap_project Lizmap Project
     * @param string $layerName      Layer Name
     *
     * @return string or null
     */
    public function getProfile($lizmap_project, $layerName)
    {
        // Get layer
        $qgisLayer = $this->getLayer($lizmap_project, $layerName);
        if (!$qgisLayer) {
            return null;
        }
        // get profile
        return $qgisLayer->getDatasourceProfile(32);
    }

    /**
     * Get layer from project and layer name.
     *
     * @param \Lizmap\Project\Project $lizmap_project Lizmap Project
     * @param string $layerName      Layer Name
     *
     * @return object or null
     */
    public function getLayer($lizmap_project, $layerName)
    {
        if (!$lizmap_project) {
            return null;
        }
        $layer = $lizmap_project->findLayerByName($layerName);
        if (!$layer) {
            return null;
        }
        $layerId = $layer->id;
        $qgisLayer = $lizmap_project->getLayer($layerId);
        if (!$qgisLayer) {
            return null;
        }

        return $qgisLayer;
    }
}
