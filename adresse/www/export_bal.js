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
      for (var i = 0; i < features.length; i++) {
        cColumn.append(new Option(features[i]['properties']['commune_nom'], features[i]['properties']['insee_code']));
      }
  });
  $('#export_bal').click(function(){
    var insee = cColumn.val();
    var option = 'export';
    var options = {
                   repository: lizUrls.params.repository,
                   project: lizUrls.params.project,
                   insee: insee,
                   opt: option
               };
    var url = adresseConfig['urls']['export'];
    downloadFile(url, options);
  });
}

function downloadFile( url, parameters ) {
   var xhr = new XMLHttpRequest();
   xhr.open('POST', url, true);
   xhr.responseType = 'arraybuffer';
   xhr.onload = function () {
       if (this.status === 200) {
           var filename = "";
           var disposition = xhr.getResponseHeader('Content-Disposition');
           if (disposition && disposition.indexOf('attachment') !== -1) {
               var filenameRegex = /filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/;
               var matches = filenameRegex.exec(disposition);
               if (matches != null && matches[1]) filename = matches[1].replace(/['"]/g, '');
           }
           var type = xhr.getResponseHeader('Content-Type');

           var blob = typeof File === 'function'
               ? new File([this.response], filename, { type: type })
               : new Blob([this.response], { type: type });
           if (typeof window.navigator.msSaveBlob !== 'undefined') {
               // IE workaround for "HTML7007: One or more blob URLs were revoked by closing the blob for which they were created. These URLs will no longer resolve as the data backing the URL has been freed."
               window.navigator.msSaveBlob(blob, filename);
           } else {
               var URL = window.URL || window.webkitURL;
               var downloadUrl = URL.createObjectURL(blob);

               if (filename) {
                   // use HTML5 a[download] attribute to specify filename
                   var a = document.createElement("a");
                   // safari doesn't support this yet
                   if (typeof a.download === 'undefined') {
                       window.location = downloadUrl;
                   } else {
                       a.href = downloadUrl;
                       a.download = filename;
                       document.body.appendChild(a);
                       a.click();
                   }
               } else {
                   window.location = downloadUrl;
               }

               setTimeout(function () { URL.revokeObjectURL(downloadUrl); }, 100); // cleanup
           }
       }
   };
   xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
   xhr.send($.param(parameters));
}
