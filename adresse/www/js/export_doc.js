class adresseExportDoc extends HTMLElement {

    constructor() {
        super();
    }

    connectedCallback() {
        var html = '';
        html += '<div class="mini-dock-close btn-export-doc-close" title="close" style="padding:7px;float:right;cursor:pointer;">';
        html += '<i class="icon-remove icon-white"></i>';
        html += '</div >';
        html += '<div class="adresse-exports">';
        html += '<h3>';
        html += '<span class="title">';
        html += '<i class="icon"></i>';
        html += '<span class="text"> Gestion des documents</span>';
        html += '</span>';
        html += '</h3>';
        html += '<div class="menu-content">';
        html += '<div id="bal_form_container" style="">';
        html += '<select name="liste-com"></select>';
        html += '</div>';
        html += '<button id="export_bal">Export BAL</button><br><br>';
        html += '<button id="delib_voie">Exporter voies à délibérer</button><br><br>';
        html += '<button id="export_sna">Export SNA</button>';
        html += '<label>';
        html += '<input type="checkbox" id="derniereDelib" name="derniereDelib" checked>Dernière délibération>';
        html += '</label>';
        html += '</div>';
        html += '</div>';

        this.innerHTML = html;

        lizMap.events.on({
            uicreated: () => {
                initBalView()
            }
        });

        function initBalView() {
            var form = $('#bal_form_container');
            var cColumn = form.find('select[name="liste-com"]');
            var getFeatureUrlData = lizMap.getVectorLayerWfsUrl('v_commune', null, null, 'none');
            getFeatureUrlData['options']['PROPERTYNAME'] = 'insee_code,commune_nom';
            $.post(getFeatureUrlData['url'], getFeatureUrlData['options'], function (data) {
                if (!data.features)
                    data = JSON.parse(data);
                var features = data.features;
                for (var i = 0; i < features.length; i++) {
                    cColumn.append(new Option(features[i]['properties']['commune_nom'], features[i]['properties']['insee_code']));
                }
            });
            var url = `${lizUrls.basepath}index.php/adresse/service/export/`;
            var options = {
                repository: lizUrls.params.repository,
                project: lizUrls.params.project,
                insee: undefined,
                opt: ''
            };
            $('#export_bal').click(function () {
                var insee = cColumn.val();
                var leOpt = 'bal';
                options['insee'] = insee;
                options['opt'] = leOpt;
                downloadFile(url, options);
            });

            $('#delib_voie').click(function () {
                var insee = cColumn.val();
                var leOpt = 'voie_delib';
                options['insee'] = insee;
                options['opt'] = leOpt;
                downloadFile(url, options);
            });

            $('#export_sna').click(function () {
                var insee = cColumn.val();
                var leOpt = undefined;
                if (document.getElementById('derniereDelib').checked) {
                    leOpt = 'zip1';
                } else {
                    leOpt = 'zipAll';
                }
                options['insee'] = insee;
                options['opt'] = leOpt;
                downloadFile(url, options);
            });

            $('.btn-export-doc-close').click(function () {
                $('#button-adresse-exports').click();
                return false;
            });
        }

        function downloadFile(url, parameters) {
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
                            // If here the response is json error so we can convert the response
                            // in json and use it to display the message
                            var str = new TextDecoder().decode(this.response)
                            var json = JSON.parse(str)
                            if (json['status'] == 'error') {
                                lizMap.addMessage(json['message'], 'error', true)
                                return;
                            }
                            //window.location = downloadUrl;
                        }

                        setTimeout(function () { URL.revokeObjectURL(downloadUrl); }, 100); // cleanup
                    }
                }
            };
            xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
            xhr.send($.param(parameters));
        }
    }

}

window.customElements.define('lizmap-adresse', adresseExportDoc);
