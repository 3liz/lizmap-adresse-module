<?php
/**
 * @author    Pierre DRILLIN
 *
 * @contributor René-Luc D'Hont
 * @contributor Laurent Jouanneau
 *
 * @copyright 2020-2022 3liz
 *
 * @see      http://3liz.com
 *
 * @license    Mozilla Public Licence
 */
class serviceCtrl extends jController
{
    /**
     * @var adresseCheck
     */
    protected $adresseCheck;

    /**
     * @var Adresse
     */
    protected $adresse;

    protected $option;

    protected $repository;

    /**
     * Method to run all check and instantiate var.
     *
     * @return array
     */
    private function check()
    {
        $project = $this->param('project');
        if (!$project) {
            return array('error', 'Project not found');
        }

        $repository = $this->param('repository');
        if (!$repository) {
            return array('error', 'Repository not found');
        }

        $this->option = $this->param('opt');
        if (!$this->option) {
            return array('error', 'Option not found');
        }

        $lizmap_project = lizmap::getProject($repository . '~' . $project);
        if (!$lizmap_project) {
            return array(
                'error',
                'profile not found for this project.',
            );
        }

        $this->repository = $repository;

        $utils = new adresseSearch();

        $profile = $utils->getProfile($lizmap_project, 'v_point_adresse');
        if (!$profile) {
            return array(
                'error',
                'profile not found for this project.',
            );
        }

        $this->adresseCheck = new adresseCheck($utils, $lizmap_project, $profile);

        list($status, $message) = $this->adresseCheck->allCheck();
        if ($status == false) {
            return array(
                'error',
                $message,
            );
        }

        $this->adresse = new Adresse($utils, $profile);

        return array(
            'success',
            $message,
        );
    }

    /**
     * Method to get some data in database.
     *
     * @return Jsonresponse
     */
    public function select()
    {
        $rep = $this->getResponse('json');
        $filterParams = array();

        // vérifier que les paramètres repository, project, geom, srid sont non null ou vide
        $geom = $this->param('geom');
        $srid = $this->param('srid');

        if (!$geom) {
            $rep->data = array('status' => 'error', 'message' => 'Geometry not find');

            return $rep;
        }

        if (!$srid) {
            $rep->data = array('status' => 'error', 'message' => 'SRID not find');

            return $rep;
        }

        list($status, $result) = $this->check();
        if ($status == 'error') {
            $rep->data = array('status' => $status, 'message' => $result);

            return $rep;
        }

        $filterParams[] = $geom;
        $filterParams[] = $srid;

        list($status, $result) = $this->adresse->executeMethod($this->option, $filterParams);
        if ($status == 'error') {
            $rep->data = array('status' => $status, 'message' => $result);

            return $rep;
        }

        $rep->data = array('status' => $status, 'data' => $result);

        return $rep;
    }

    /**
     * Method to update some data in database.
     *
     * @return Jsonresponse
     */
    public function update()
    {
        $rep = $this->getResponse('json');

        // vérifier que les paramètres repository, project, geom, srid sont non null ou vide
        $id = $this->param('id');

        if (!$id) {
            $rep->data = array('status' => 'error', 'message' => 'Id not find');

            return $rep;
        }

        list($status, $result) = $this->check();
        if ($status == 'error') {
            $rep->data = array('status' => $status, 'message' => $result);

            return $rep;
        }

        // récupération de l'option pour définir la bonne méthode
        $opt = $this->option;

        // demander la voie éditable à proximité de la geom
        if ($opt == 'validation') {
            list($status, $result) = $this->adresse->executeMethod('version', array());
            if ($status == 'error') {
                $rep->data = array('status' => $status, 'message' => $result);

                return $rep;
            }
            $result = (array) $result[0];
            $version = $result['me_version'];
            if (version_compare($version, '0.3.0', '>=')) {
                $opt = 'new_validation';
            }
            $message = 'Validation du point adresse effectuée avec succès';
        }

        if ($opt == 'reverse') {
            $message = 'Inversion du sens de la voie effectuée avec succès';
        }

        $filterParams = array();
        $filterParams[] = $id;
        list($status, $result) = $this->adresse->executeMethod($opt, $filterParams);
        if ($status == 'error') {
            $rep->data = array('status' => $status, 'message' => $result);

            return $rep;
        }

        $rep->data = array('status' => $status, 'message' => $message);

        return $rep;
    }

    /**
     * Method to donwload file.
     *
     * @return binaryResponse|JsonResponse|zipResponse
     */
    public function export()
    {
        $test = '#^[0-9]{1}[0-9AB]{1}[0-9]{3}$#';
        $rep = $this->getResponse('json');
        $insee = $this->param('insee');

        if (!$insee) {
            $rep->data = array('status' => 'error', 'message' => 'Code insee not found');

            return $rep;
        }
        if (!preg_match($test, $insee)) {
            $rep->data = array('status' => 'error', 'message' => 'Code insee not available');

            return $rep;
        }

        list($status, $result) = $this->check();
        if ($status == 'error') {
            $rep->data = array('status' => $status, 'message' => $result);

            return $rep;
        }

        $params = array($insee);
        list($status, $result) = $this->adresse->executeMethod($this->option, $params);
        if ($status == 'error') {
            $rep->data = array('status' => $status, 'message' => $result);

            return $rep;
        }

        $leDoc = new exportDoc();
        $tempPath = jApp::tempPath('export');

        jFile::createDir($tempPath);

        if (!$result) {
            $rep->data = array('status' => 'error', 'message' => 'Aucun résultat trouvé');

            return $rep;
        }

        if ($this->option == 'bal') {
            $type = 'binary';
            $fileName = tempnam($tempPath, 'exportbal-');
            $leDoc->exportBal($fileName, $result);
            $name = date('Ymd') . '_bal_' . $insee . '.csv';
        } elseif ($this->option == 'voie_delib') {
            $type = 'binary';
            list($status, $data) = $this->adresse->executeMethod('commune', $params);
            if ($status == 'error') {
                $rep->data = array('status' => $status, 'message' => $data);

                return $rep;
            }
            $fileName = tempnam($tempPath, 'voieADelib-');
            $leDoc->exportVoieADelib($fileName, $result, $data);
            $name = 'Voie_A_Deliberer_' . $insee . '.csv';
        } else {
            $type = 'zip';
            $fileName = tempnam($tempPath, 'exportbal-');
            list($status, $data) = $this->adresse->executeMethod('bal', $params);
            if ($status == 'error') {
                $rep->data = array('status' => $status, 'message' => $data);

                return $rep;
            }
            $leDoc->exportBal($fileName, $data);
            $name = date('Ymd') . '_export_SNA_' . $insee . '.zip';
        }
        $repo = lizmap::getRepository($this->repository);  // c'est peut être déjà fait dans ton contrôleur, à toi de voir

        $rep = null;
        $rep = $this->getResponse($type);
        $cheminRepo = $repo->getPath();
        if ($type == 'zip') {
            $rep->zipFilename = $name;
            $fileBalName = date('Ymd') . '_bal_' . $insee . '.csv';
            $rep->content->addFile($fileName, $fileBalName);
            // $rep->content->addDir($folder.'/', 'Délibérations', true);
            foreach ($result as $value) {
                $ext = pathinfo($value->lien)['extension'];
                $rep->content->addFile($cheminRepo . $value->lien, 'Deliberations/' . $value->nom_doc . '.' . $ext);
            }
        } elseif ($type == 'binary') {
            $rep->deleteFileAfterSending = true;
            $rep->fileName = $fileName;
            $rep->outputFileName = $name;
            $rep->mimeType = 'text/csv';
            $rep->doDownload = true;
        }

        return $rep;
    }
}
