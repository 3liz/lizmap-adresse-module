var lizAdresse = function() {

lizMap.events.on({
   'lizmapeditiongeometryupdated': function(e){
     if (e.layerId == adresseConfig['point_adresse']['id']) {
       var form = undefined;
       var gColumn = undefined;
       var sColumn = undefined;
       var vColumn = undefined;
       var option = undefined;
       var val = undefined;
       var num = "";
       var suffixe = '';
       form = $('#edition-form-container form');
       nColumn = form.find('input[name="numero"]');
       sColumn = form.find('input[name="suffixe"]');
       vColumn = form.find('select[name="id_voie"]');
       var voie = '';
       option = 'idvoie';
       var options = {
                      repository: lizUrls.params.repository,
                      project: lizUrls.params.project,
                      geom: ''+e.geometry,
                      srid: e.srid,
                      opt: option
                  };
       var url = adresseConfig['urls']['getVoie'];
       $.getJSON(
           url,
           options,
           function( data, status, xhr ) {
               if(data){
                   option = data[0]['type_num'].toLowerCase();
                   options['opt'] = option;
                   voie = data[0]['id_voie'];
                   vColumn.val(voie);
                   vColumn.change();
                   $.getJSON(
                       url,
                       options,
                       function( data, status, xhr ) {
                           if(data){
                               nColumn.val(data[0]['num']);
                               sColumn.val(data[0]['suffixe']);
                           }
                       }
                   );
               }
           }
       );
     }
  }
});
 return {};
}();
