var form = undefined;
var gColumn = undefined;
var geomField= undefined;
var editLayer = undefined;
var option = undefined;
var val = undefined;
var num = undefined;
var suffixe = '';
lizMap.events.on({
   'lizmapeditiongeometryupdated': function(e){
     form = $('#edition-form-container form');
     gColumn = form.find('input[name="numero"]');
     //geomField = form.find('input[name="'+gColumn+'"]');
     console.log(e.srid);

     console.log(''+e.geometry);
     option = 'idvoie';
     var options = {
                    repository: lizUrls.params.repository,
                    project: lizUrls.params.project,
                    geom: ''+e.geometry,
                    srid: e.srid,
                    opt: option
                };
     var url = adresseConfig['urls']['getVoie'];
     console.log(url);
     $.getJSON(
         url,
         options,
         function( data, status, xhr ) {
             if(data){
                 option = data[0]['type_num'].toLowerCase();
                 options['opt'] = option;
                 console.log(options['opt']);
                 console.log(option);
                 $.getJSON(
                     url,
                     options,
                     function( data, status, xhr ) {
                         if(data){
                             console.log(data);
                             val = Array.from(data[0]['calcul_num_adr']);
                             if(val.length == 4){
                               num = parseInt(val[1]);
                             }else if (val.length > 4) {
                               num = parseInt(val[1]);
                               for(i=3; i< val.length - 1; i++)
                               suffixe.concat(val[i]);
                             }
                             gColumn.val(num);
                             console.log(num);
                             console.log(suffixe);
                         }
                     }
                 );
             }
         }
     );
  }
});
