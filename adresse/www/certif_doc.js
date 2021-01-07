lizMap.events.on({
  'uicreated': function () {
    // Add Dock
    addCertifNumDock();

    // Activate tools
    initCertifView();
  }
});

function addCertifNumDock() {

  // Build HTML interface
  var html = '';
  html += '<div id="certif_form_container" style="text-align: right;">';
  html += '<div class="tabbable"> <!-- Only required for left/right tabs -->';
  html += '  <ul class="nav nav-tabs">';
  html += '    <li class="active"><a href="#tab1" data-toggle="tab">Par parcelle</a></li>';
  html += '    <li><a href="#tab2" data-toggle="tab">Par adresse</a></li>';
  html += '  </ul>';
  html += '  <div class="tab-content">';
  html += '    <div class="tab-pane active" id="tab1">';
  html += '       <br>';
  html += '      Commune: <select name="list-com-parcelle" id="list-com-parcelle">';
  html += '      </select><br>';
  html += '      Section: <select name="list-sec" id="list-sec">';
  html += '      </select><br>';
  html += '      Parcelle: <select name="list-parc" id="list-parc">';
  html += '      </select><br>';
  html += '      Propriétaire: <select name="list-prop" id="list-prop">';
  html += '      </select><br>';
  html += '      <button id="export_certif">Télécharger le Certificat</button>';
  html += '    </div>';
  html += '    <div class="tab-pane" id="tab2">';
  html += '       <br>';
  html += '      Commune: <select name="list-com-adresse" id="list-com-adresse">';
  html += '      </select><br>';
  html += '      Rue: <select id="list-rue" name="list-rue">';
  html += '      </select><br>';
  html += '      Numéro: <select id="list-num" name="list-num">';
  html += '      </select><br>';
  html += '      Propriétaire: <select id="list-prop2" name="list-prop2">';
  html += '      </select><br>';
  html += '      <button id="export_certif2">Télécharger le Certificat</button>';
  html += '    </div>';
  html += '  </div>';
  html += '</div>';
  html += '</div>';

  // Add Lizmap minidock
  lizMap.addDock(
    'certif-num',
    'Certificats de Numérotation',
    'minidock',
    html,
    'icon-file'
  );
}

function initCertifView() {
  var form = $('#certif_form_container');
  var cColumn = form.find('select[name="list-com-parcelle"]');
  var c2Column = form.find('select[name="list-com-adresse"]');
  var getFeatureUrlData = lizMap.getVectorLayerWfsUrl('commune', null, null, 'none');
  getFeatureUrlData['options']['PROPERTYNAME'] = 'id_com,commune_nom,insee_code';
  $.post(getFeatureUrlData['url'], getFeatureUrlData['options'], function (data) {
    if (!data.features)
      data = JSON.parse(data);
    var features = data.features;
    cColumn.append(new Option('Choisir', ''));
    c2Column.append(new Option('Choisir', ''));
    for (var i = 0; i < features.length; i++) {
      cColumn.append(new Option(features[i]['properties']['commune_nom'], features[i]['properties']['insee_code']));
      c2Column.append(new Option(features[i]['properties']['commune_nom'], features[i]['properties']['id_com']));
    }
  });

  $('#list-com-adresse').change(function () {
    onComboBoxChanged('commune-voie', this.value);
  });
  $('#list-rue').change(function () {
    onComboBoxChanged('voie', this.value);
  });
  $('#list-num').change(function () {
    onComboBoxChanged('num', this.value);
  });
  $('#list-com-parcelle').change(function () {
    onComboBoxChanged('commune-sec', this.value);
  });
  $('#list-sec').change(function () {
    onComboBoxChanged('sec', this.value);
  });
  $('#list-parc').change(function () {
    onComboBoxChanged('parcelle', this.value);
  });
  $('#export_certif2').click(function () {
    getAtlas('v_certificat', 'list-prop2');
  });
  $('#export_certif').click(function () {
    getAtlas('v_certificat', 'list-prop');
  });

  function getAtlas(layerName, combo) {
    var layerId = null;
    var fid = $('#' + combo).val();
    if (layerName in lizMap.config.layers) {
      layerId = lizMap.config.layers.v_certificat.id;
      for (var i in lizMap.config.printTemplates) {
        var t = lizMap.config.printTemplates[i];
        if ('atlas' in t) {
          if (layerId == t.atlas.coverageLayer) {
            // Build URL
            var url = OpenLayers.Util.urlAppend(
              lizUrls.wms,
              OpenLayers.Util.getParameterString(lizUrls.params)
            );
            url += '&SERVICE=WMS';
            url += '&VERSION=1.3.0&REQUEST=GetPrintAtlas';
            url += '&FORMAT=pdf';
            url += '&EXCEPTIONS=application/vnd.ogc.se_inimage&TRANSPARENT=true';
            url += '&DPI=100';
            url += '&TEMPLATE=' + t.title;
            url += '&LAYER=' + layerName;
            url += '&EXP_FILTER=id_view IN (\'' + fid + '\')';
            window.location = url;
          }
        }
      }
    }
  }
  var com, sec = null;
  function onComboBoxChanged(combo_name, combo_value) {
    var layer_name, layer_field, child_combo = null;
    var propertie1, propertie2 = null;
    var form = $('#certif_form_container');
    if (combo_name == 'commune-voie') {
      layer_name = 'v_voie';
      layer_field = 'id_com = ' + combo_value;
      child_combo = 'list-rue';
      propertie1 = 'nom_complet';
      propertie2 = 'id_voie';
    } else if (combo_name == 'voie') {
      layer_name = 'point_adresse';
      layer_field = 'id_voie = ' + combo_value;
      child_combo = 'list-num';
      propertie1 = 'numero';
      propertie2 = 'id_point';
    } else if (combo_name == 'num') {
      layer_name = 'v_certificat';
      layer_field = 'id_point = ' + combo_value;
      child_combo = 'list-prop2';
      propertie1 = 'p_nom';
      propertie2 = 'id_view';
    } else if (combo_name == 'commune-sec') {
      com = combo_value;
      layer_name = 'v_section';
      layer_field = "insee = '" + combo_value + "'";
      child_combo = 'list-sec';
      propertie1 = 'tex';
      propertie2 = 'tex';
    } else if (combo_name == 'sec') {
      sec = combo_value;
      layer_name = 'v_parcelle';
      layer_field = "section = '" + combo_value + "' AND " + "insee = '" + com + "'";
      child_combo = 'list-parc';
      propertie1 = 'parcelle';
      propertie2 = 'parcelle';
    } else if (combo_name == 'parcelle') {
      sec = sec.replace('0', ' ');
      numparc = combo_value
      layer_name = 'v_certificat';
      layer_field = "tex = '" + combo_value + "' AND " + "insee_code = '" + com + "'" + ' AND ' + " ccosec = '" + sec + "'";
      child_combo = 'list-prop';
      propertie1 = 'p_nom';
      propertie2 = 'id_view';
    }
    var layer_filter = layer_name + ':' + layer_field;
    // ou null si pas besoin de filtre
    // var layer_filter = null;
    lizMap.getFeatureData(layer_name, layer_filter, null, 'none', false, null, null,
      function (aName, aFilter, aFeatures) {
        // On récupère les objets
        var features = aFeatures;
        var column = form.find('select[name="' + child_combo + '"]');
        column.find('option').remove().end();
        if (features && features.length > 0) {
          // Fill in child combobox
          column.append(new Option('Choisir', ''));
          for (var i = 0; i < features.length; i++) {
            column.append(new Option(features[i]['properties'][propertie1], features[i]['properties'][propertie2]));
          }
        }
        return false;
      }
    );
  }
}
