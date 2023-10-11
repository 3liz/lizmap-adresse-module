<?php
/**
 * @author    Pierre DRILLIN
 *
 * @contributor Laurent Jouanneau
 *
 * @copyright 2021-2022 3liz
 *
 * @see      http://3liz.com
 *
 * @license    Mozilla Public Licence
 */
class adresseCheck
{
    protected $layers_required = array('v_point_adresse', 'voie');

    protected $profile;

    /**
     * @var \Lizmap\Project\Project
     */
    protected $lizmap_project;

    /**
     * @var adresseSearch
     */
    protected $search;

    protected $sql = array(
        'check_ext' => "SELECT extname FROM pg_extension WHERE extname = 'postgis';",
        'check_schema' => "SELECT schema_name FROM information_schema.schemata WHERE schema_name = 'adresse'",
    );

    /**
     * @param \Lizmap\Project\Project $lizmap_project
     * @param mixed                   $profile
     */
    public function __construct(adresseSearch $utils, $lizmap_project, $profile)
    {
        $this->search = $utils;
        $this->lizmap_project = $lizmap_project;
        $this->profile = $profile;
    }

    /**
     * Function to check Project.
     *
     * @return array
     */
    public function checkProject()
    {
        if (!$this->lizmap_project) {
            return array('error', 'Adresse module: Project adresse not found');
        }

        if (!$this->lizmap_project->checkAcl()) {
            return array('error', \jLocale::get('view~default.repository.access.denied'));
        }

        return array('success', 'Project is good');
    }

    /**
     * Function to check Profile.
     *
     * @return array
     */
    public function checkProfile()
    {
        if (!$this->profile) {
            return array('error', 'Adresse module: Profile for database connection not found');
        }

        return array('success', 'Profile is good');
    }

    /**
     * Function to check if extensions are installed.
     *
     * @return array
     */
    public function checkDbExtension()
    {
        list($status, $message, $result) = $this->search->execQuery($this->sql['check_ext'], $this->profile, array());
        if ($status == 'error') {
            return array(
                'error',
                $message,
            );
        }

        if (count($result) != 1) {
            return array(
                'error',
                'Adresse module error: Extension postgis missing in database',
            );
        }

        return array(
            'success',
            'All extensions required exists',
        );
    }

    /**
     * Function to check if schema is good.
     *
     * @return array
     */
    public function checkDbSchema()
    {
        list($status, $message, $result) = $this->search->execQuery($this->sql['check_schema'], $this->profile, array());
        if ($status == 'error') {
            return array(
                'error',
                $message,
            );
        }

        if (count($result) != 1) {
            return array(
                'error',
                'Adresse module error: Schema adresse missing in database',
            );
        }

        return array(
            'success',
            'The schema exists',
        );
    }

    /**
     * Function to run all check.
     *
     * @return array
     */
    public function allCheck()
    {
        list($status, $message) = $this->checkProject();
        if ($status == 'error') {
            return array(
                $status,
                $message,
            );
        }

        list($status, $message) = $this->checkProfile();
        if ($status == 'error') {
            return array(
                $status,
                $message,
            );
        }

        list($status, $message) = $this->checkDbExtension();
        if ($status == 'error') {
            return array(
                $status,
                $message,
            );
        }

        list($status, $message) = $this->checkDbSchema();
        if ($status == 'error') {
            return array(
                $status,
                $message,
            );
        }

        list($status, $message) = $this->checkProjectLayers();
        if ($status == 'error') {
            return array(
                $status,
                $message,
            );
        }

        return array(
            'success',
            'All check executed with success',
        );
    }

    /**
     * Function to check layers required exists.
     *
     * @return array
     */
    public function checkProjectLayers()
    {
        foreach ($this->layers_required as $lname) {
            list($status, $message) = $this->checkProjectLayer($lname);
            if ($status == 'error') {
                return array(
                    $status,
                    $message,
                );
            }
        }

        return array(
            $status,
            'All layers required are good',
        );
    }

    /**
     * Function to check if layer exists.
     *
     * @param mixed $layer_name
     *
     * @return array
     */
    public function checkProjectLayer($layer_name)
    {
        $layer = $this->lizmap_project->findLayerByName($layer_name);
        if (!$layer) {
            return array(
                'error',
                "Adresse module error: Layer ${layer_name} missing in project",
            );
        }

        return array(
            'success',
            'Layer is good',
        );
    }

    /**
     * Function to check right of user.
     *
     * @param mixed $layer_name
     *
     * @return array
     */
    public function checkCanUserEdit($layer_name)
    {
        $qgisLayer = $this->search->getLayer($this->lizmap_project, $layer_name);
        if (!$qgisLayer) {
            return array(
                'error',
                "Layer ${layer_name} not found to check profile",
            );
        }
        if (method_exists($qgisLayer, 'canCurrentUserEdit')) {
            // Lizmap 3.5+
            if (!$qgisLayer->canCurrentUserEdit()) {
                return array(
                    'error',
                    "Adresse module: User can't access to the module dock",
                );
            }
        } else {
            // Lizmap 3.4 and lower. DEPRECATED
            $eLayer = $qgisLayer->getEditionCapabilities();

            // Check if user groups intersects groups allowed by project editor
            // If user is admin, no need to check for given groups
            if (jAuth::isConnected() and !jAcl2::check('lizmap.admin.repositories.delete') and property_exists($eLayer, 'acl') and $eLayer->acl) {
                // Check if configured groups white list and authenticated user groups list intersects
                $editionGroups = $eLayer->acl;
                $editionGroups = array_map('trim', explode(',', $editionGroups));
                if (is_array($editionGroups) and count($editionGroups) > 0) {
                    $userGroups = jAcl2DbUserGroup::getGroups();
                    if (!array_intersect($editionGroups, $userGroups)) {
                        return array(
                            'error',
                            "Adresse module: User can't access to the module dock",
                        );
                    }
                }
            }
        }
    }
}
