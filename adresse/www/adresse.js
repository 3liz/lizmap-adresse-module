/**
* @package   lizmap
* @subpackage adresse
* @author    Pierre DRILLIN
* @copyright 2020 3liz
* @link      http://3liz.com
* @license    Mozilla Public Licence
*/

/* global adresseConfig */

var lizAdresse = function () {
  var adresseMessageTimeoutId = null;
  function cleanAdresseMessage() {
    var $AdresseMessage = $('#lizmap-adresse-message');
    if ($AdresseMessage.length != 0) {
      $AdresseMessage.remove();
    }
    adresseMessageTimeoutId = null;
  }
  function addAdresseMessage(aMessage, aType, aClose) {
    if (adresseMessageTimeoutId) {
      window.clearTimeout(adresseMessageTimeoutId);
      adresseMessageTimeoutId = null;
    }
    var $AdresseMessage = $('#lizmap-adresse-message');
    if ($AdresseMessage.length != 0) {
      $AdresseMessage.remove();
    }
    lizMap.addMessage(aMessage, aType, aClose).attr('id', 'lizmap-adresse-message');
    adresseMessageTimeoutId = window.setTimeout(cleanAdresseMessage, 5000);
  }
  function redrawPointAdresseLayer() {
    var point_adresse_layer = lizMap.map.getLayersByName('point_adresse');
    var pLayer = point_adresse_layer[0];
    pLayer.redraw();
  }
  lizMap.events.on({
    'lizmapeditiongeometryupdated': function (e) {
      if (e.layerId == adresseConfig['point_adresse']['id']) {
        var form = $('#edition-form-container form');
        var nColumn = form.find('input[name="numero"]');
        var sColumn = form.find('select[name="suffixe"]');
        var vColumn = form.find('select[name="id_voie"]');
        var option = undefined;
        var voie = '';
        option = 'idvoie';
        var options = {
          repository: lizUrls.params.repository,
          project: lizUrls.params.project,
          geom: '' + e.geometry,
          srid: e.srid,
          opt: option
        };
        var url = adresseConfig['urls']['select'];
        if (form.find('input[name="liz_featureId"]').val() == '') {
          $.getJSON(
            url,
            options,
            function (data) {
              if (data) {
                option = data[0]['type_num'].toLowerCase();
                options['opt'] = option;
                voie = data[0]['id_voie'];
                vColumn.val(voie);
                vColumn.change();
                $.getJSON(
                  url,
                  options,
                  function (data) {
                    if (data) {
                      nColumn.val(data[0]['num']);
                      sColumn.val(data[0]['suffixe']);
                      sColumn.change();
                    }
                  }
                );
              }
            }
          );
        }
      }
    },
    'lizmappopupdisplayed': function () {
      $('div.lizmapPopupContent input.lizmap-popup-layer-feature-id').each(function () {

        var self = $(this);
        var val = self.val();
        var fid = val.split('.').pop();
        var layerId = val.replace('.' + fid, '');
        var getLayerConfig = lizMap.getLayerConfigById(layerId);
        if (getLayerConfig) {
          var layerName = getLayerConfig[0];
          var btnBar = self.next('span.popupButtonBar');
          if (btnBar.length == 0) {
            var eHtml = '<span class="popupButtonBar"></span></br>';
            self.after(eHtml);
            btnBar = self.next('span.popupButtonBar');
          }
          var btn = $('<button></button>');
          btn.addClass("btn btn-mini popup-adresse-reverse");
          var icon = $('<i></i>');
          var url = adresseConfig['urls']['update'];

          if (layerName == adresseConfig['voie']['name']) {
            icon.addClass('icon-refresh');
            btn.append(icon);
            btnBar.append(btn);
            var options = {
              repository: lizUrls.params.repository,
              project: lizUrls.params.project,
              id: '',
              opt: 'reverse'
            };
            btn.click(function () {
              var featId = self.val();
              var leid = featId.split('.');
              options['id'] = leid[1];
              if (confirm('Êtes-vous sûr de vouloir inverser la géométrie de la voie ?')) {
                $.getJSON(
                  url,
                  options,
                  function (data) {
                    if (data) {
                      if (data['type'] == 'success') {
                        addAdresseMessage(data['message'], 'info', true);
                        $('#dock-close').click();
                      } else {
                        addAdresseMessage(data['message'], 'error', true);
                      }
                    }
                  }
                );
              }
              return false;
            });

          }
          if (layerName == adresseConfig['point_adresse']['name']) {
            icon.addClass('icon-thumbs-up');
            btn.append(icon);
            btnBar.append(btn);
            var options = {
              repository: lizUrls.params.repository,
              project: lizUrls.params.project,
              id: '',
              opt: 'validation'
            };
            btn.click(function () {
              var featId = self.val();
              var leid = featId.split('.');
              options['id'] = leid[1];
              $.getJSON(
                url,
                options,
                function (data) {
                  if (data) {
                    if (data['type'] == 'success') {
                      addAdresseMessage(data['message'], 'info', true);
                      $('#dock-close').click();
                    } else {
                      addAdresseMessage(data['message'], 'error', true);
                    }
                  }
                }
              );
            });

          }
        }
      });
    },
    'lizmapeditionformdisplayed': function () {
      var login = adresseConfig['user'];
      var form = $('#edition-form-container form');
      var cColumn = form.find('input[name="createur"]');
      var mColumn = form.find('input[name="modificateur"]');
      if (form.find('input[name="liz_featureId"]').val() == '') {
        var cSelect = $('<select class="jforms-ctrl-menulist" name="createur" size="1"></select>');
        cSelect.attr('id', cColumn.attr('id'));
        cSelect.append('<option selected="selected">' + login + '</option>');
        cColumn.replaceWith(cSelect);
      } else {
        cColumn.attr('disabled', 'disabled');
      }
      var mSelect = $('<select class="jforms-ctrl-menulist" name="modificateur" size="1"></select>');
      mSelect.attr('id', mColumn.attr('id'));
      mSelect.append('<option selected="selected">' + login + '</option>');
      mColumn.replaceWith(mSelect);
    },
    'lizmapeditionfeaturemodified': function (e) {
      var layerId = e.layerId;
      var getLayerConfig = lizMap.getLayerConfigById(layerId);
      if (getLayerConfig) {
        var layerName = getLayerConfig[0];
        if (layerName == adresseConfig['voie']['name']) {
          redrawPointAdresseLayer();
        }
      }
    }
  });
  return {};
}();
