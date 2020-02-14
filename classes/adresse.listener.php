
<?php
class adresseListener extends jEventListener{

   function ongetMapAdditions ($event) {
       $js = array();
       $jscode = array();
       $css = array();

       $adresseConfig = array();

       $adresseConfig['urls'] = array();
       $adresseConfig['urls']['getVoie'] = jUrl::get('adresse~default:index');

       $bp = jApp::config()->urlengine['basePath'];

       $js = array(
          jUrl::get('jelix~www:getfile', array('targetmodule'=>'adresse', 'file'=>'adresse.js'))
       );

       $jscode = array(
                'var adresseConfig = ' . json_encode($adresseConfig)
       );

       $event->add(
           array(
               'js' => $js,
               'jscode' => $jscode
           )
       );
   }
}
?>
