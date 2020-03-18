lizMap.events.on({

    'uicreated': function(e) {
        // Activate GPX manager tool when the map loads
        var activateBalOnStartup = true;

        // File format based on extension
        var gpxFileFormat = new OpenLayers.Format.GPX();
        var gpxFileExt = 'ext';

        // Add Dock
        addBalDock();

        // Activate tools
        initBalView(activateBalOnStartup);
    },
    'minidockclosed': function(e) {
        if ( e.id == 'bal-export' ) {
            $("#bal_none_toggle").click();
        }
    }
});

function addBalDock(){

    // Build HTML interface
    var html = '';
    html+= '<div id="bal_form_container" style="">';
    html+= ' <select name="liste-com">';
    html+= ' </select>';
    html+= '</div>'
    html+= '<button id="export_bal">Exporter</button>'

    // Add Lizmap minidock
    lizMap.addDock(
        'bal-export',
        'Export d\'adresse au format BAL',
        'minidock',
        html,
        'icon-road'
    );
}

function initBalView(activateGpxOnStartup) {
  var form = $('#bal_form_container');
  var cColumn = form.find('select[name="liste-com"]');
  var getFeatureUrlData = lizMap.getVectorLayerWfsUrl( 'vue_com', null, null, 'none' );
  getFeatureUrlData['options']['PROPERTYNAME'] = 'insee_code,commune_nom';
  $.post( getFeatureUrlData['url'], getFeatureUrlData['options'], function(data) {
      if ( !data.features )
        data = JSON.parse(data);
      var features = data.features;
      console.log(typeof features);
      console.log(features[0]);
      for (var i = 0; i < features.length; i++) {
        cColumn.append(new Option(features[i]['properties']['commune_nom'], features[i]['properties']['insee_code']));
        console.log(features[i]['properties']);
      }
  });
  $('#export_bal').click(function(){
    console.log('ok');
    lizMap.exportVectorLayer('point_adresse', 'CSV', false);
  });
}
