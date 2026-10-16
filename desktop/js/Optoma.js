/* This file is part of Jeedom.
 *
 * Jeedom is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Jeedom is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
 */

function optomaInsertAtCaret(input, text) {
  if (!input) return;
  const start = input.selectionStart ?? input.value.length;
  const end = input.selectionEnd ?? input.value.length;
  input.value = input.value.slice(0, start) + text + input.value.slice(end);
  input.selectionStart = input.selectionEnd = start + text.length;
  input.dispatchEvent(new Event('change', {bubbles: true}));
}

['table_cmd', 'table_info'].forEach(function (tableId) {
  const body = document.querySelector('#' + tableId + ' tbody');
  if (body && typeof Sortable !== 'undefined') {
    Sortable.create(body, {
      animation: 150,
      draggable: '.cmd',
      handle: '.cmd'
    });
  }
});

document.addEventListener('click', function (event) {
  let target = event.target.closest('.listEquipementInfo');
  if (target && target.closest('#table_info')) {
    jeedom.cmd.getSelectModal({cmd: {type: 'info'}}, function (result) {
      const input = target.closest('tr')?.querySelector(
        '.cmdAttr[data-l1key="configuration"][data-l2key="' + target.dataset.input + '"]'
      );
      optomaInsertAtCaret(input, result.human);
    });
    return;
  }

  target = event.target.closest('.listEquipementAction');
  if (target && target.closest('#table_cmd')) {
    const subtype = target.closest('.cmd')?.querySelector('.cmdAttr[data-l1key="subType"]')?.jeeValue();
    jeedom.cmd.getSelectModal({cmd: {type: 'action', subType: subtype}}, function (result) {
      const input = target.closest('tr')?.querySelector(
        '.cmdAttr[data-l1key="configuration"][data-l2key="' + target.dataset.input + '"]'
      );
      optomaInsertAtCaret(input, result.human);
    });
    return;
  }

  target = event.target.closest('#bt_weboptoma');
  if (target) {
    const nodeId = document.getElementById('idipoptoma')?.jeeValue() || '';
    jeeDialog.dialog({
      id: 'md_modal',
      title: '{{Interface Optoma}}',
      contentUrl: 'index.php?v=d&plugin=Optoma&modal=web&ip=' + encodeURIComponent(nodeId)
    });
    return;
  }

  target = event.target.closest('.changeIncludeState');
  if (target) {
    const state = target.dataset.state;
    amxDeviceDiscovery(state);
    jeedom.config.save({
      plugin: 'Optoma',
      configuration: {include_mode: state},
      error: function (error) {
        jeedomUtils.showAlert({message: error.message, level: 'danger'});
      },
      success: function () {
        optomaUpdateInclusionState(state === '1');
      }
    });
    return;
  }

  target = event.target.closest('#bt_healthoptoma');
  if (target) {
    jeeDialog.dialog({
      id: 'md_modal',
      title: '{{Santé Optoma}}',
      contentUrl: 'index.php?v=d&plugin=Optoma&modal=health'
    });
    return;
  }

  target = event.target.closest('#bt_documentationOptoma');
  if (target) {
    window.open(target.dataset.location, '_blank', 'noopener');
  }
});

function optomaUpdateInclusionState(enabled) {
  document.querySelectorAll('.changeIncludeState').forEach(function (element) {
    element.dataset.state = enabled ? '0' : '1';
  });

  document.querySelectorAll('.changeIncludeState:not(.card)').forEach(function (button) {
    button.classList.toggle('btn-default', !enabled);
    button.classList.toggle('btn-success', enabled);
    button.classList.remove('btn-danger');
    button.innerHTML = '<i class="fas fa-sign-in-alt fa-rotate-90"></i> ' +
      (enabled ? "{{Arrêter l'inclusion}}" : '{{Mode inclusion}}');
  });

  document.querySelectorAll('.changeIncludeState.card').forEach(function (card) {
    card.style.backgroundColor = enabled ? '#8000FF' : '#ffffff';
    const label = card.querySelector('span center');
    if (label) label.textContent = enabled ? "{{Arrêter l'inclusion}}" : '{{Mode inclusion}}';
  });

  const alert = document.getElementById('div_inclusionAlert');
  if (!alert) return;
  if (enabled) {
    alert.className = 'alert jqAlert alert-warning';
    alert.style.margin = '0 5px 15px 15px';
    alert.style.padding = '7px 35px 7px 15px';
    alert.textContent = "{{Vous êtes en mode inclusion. Cliquez à nouveau sur le bouton d'inclusion pour sortir de ce mode}}";
  } else {
    alert.className = '';
    alert.removeAttribute('style');
    alert.textContent = '';
  }
}

function amxDeviceDiscovery(_state) {
  domUtils.ajax({
    type: 'POST',
    url: 'plugins/Optoma/core/ajax/Optoma.ajax.php',
    data: {
      action: 'amxDeviceDiscovery',
      state: _state
    },
    dataType: 'json',
    global: false,
    error: function (request, status, error) {
      handleAjaxError(request, status, error);
    },
    success: function (data) {
      if (data.state !== 'ok') {
        jeedomUtils.showAlert({message: data.result, level: 'danger'});
        return;
      }

      const ipInput = document.querySelector('.eqLogicAttr[data-l1key="configuration"][data-l2key="IP"]');
      if (!ipInput) return;
      if (ipInput.jeeValue() !== '') {
        jeeDialog.confirm("{{Voulez-vous écraser l'adresse IP actuelle ?}}", function (confirmed) {
          if (confirmed) ipInput.jeeValue(data.result);
        });
      } else {
        ipInput.jeeValue(data.result);
      }
    }
  });
}

function addCmdToTable(_cmd) {
  _cmd = isset(_cmd) ? _cmd : {configuration: {}};
  _cmd.configuration = isset(_cmd.configuration) ? _cmd.configuration : {};
  if (init(_cmd.logicalId) === 'refresh') return;

  const isInfo = init(_cmd.type) === 'info';
  let html = '<tr class="cmd" data-cmd_id="' + init(_cmd.id) + '">';

  if (isInfo) {
    html += '<td><div style="display:inline-flex;">';
    html += '<span class="cmdAttr" data-l1key="id" style="display:none;"></span>';
    html += '<a class="cmdAction btn btn-default btn-sm" data-l1key="chooseIcon"><i class="fas fa-flag"></i> {{Icône}}</a>';
    html += '<span class="cmdAttr" data-l1key="display" data-l2key="icon"></span>';
    html += '<input class="cmdAttr form-control input-sm" data-l1key="name" placeholder="{{Nom}}"></div></td>';
    html += '<td><input class="cmdAttr form-control type input-sm" data-l1key="type" value="info" disabled style="margin-top:-5px;width:40%;display:inline-block;">';
    html += '<span class="subType" subType="' + init(_cmd.subType) + '" style="margin-top:-5px;width:50%;display:inline-block;"></span></td>';
    html += '<td><input readonly class="cmdAttr" id="' + init(_cmd.id) + 'value" style="width:200px;font-style:italic;"></td>';
    html += '<td><label class="checkbox-inline"><input type="checkbox" class="cmdAttr checkbox-inline" data-l1key="isVisible" checked>{{Afficher}}</label> ';
    html += '<label class="checkbox-inline"><input type="checkbox" class="cmdAttr checkbox-inline" data-l1key="isHistorized" checked>{{Historiser}}</label><br>';
    if (init(_cmd.subType) === 'numeric') {
      html += '<input class="cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="minValue" placeholder="{{Min}}" title="{{Min}}" style="display:inline-block;width:50px;">';
      html += '<input class="cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="maxValue" placeholder="{{Max}}" title="{{Max}}" style="display:inline-block;width:50px;">';
    }
    html += '</td>';
  } else {
    html += '<td><span class="cmdAttr" data-l1key="id" style="display:none;"></span>';
    html += '<input class="cmdAttr form-control input-sm" data-l1key="name" style="margin-top:0;width:70%;display:inline-block;" placeholder="{{Nom}}"></td>';
    html += '<td><input class="cmdAttr form-control type input-sm" data-l1key="type" value="action" disabled style="margin-top:-5px;width:40%;display:inline-block;">';
    html += '<span class="subType" subType="' + init(_cmd.subType) + '" style="margin-top:-5px;width:50%;display:inline-block;"></span></td>';
    html += '<td><select class="cmdAttr form-control tooltips input-sm" data-l1key="value" style="margin-top:0;width:40%;display:inline-block;" title="{{La valeur de la commande action vaut par défaut la commande information}}"><option value="">{{Aucune}}</option></select>';
    html += '<select class="cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="updateCmdId" style="margin-top:5px;margin-left:5px;width:40%;display:inline-block;" title="{{Commande information à mettre à jour}}"><option value="">{{Aucune}}</option></select></td>';
    html += '<td><label class="checkbox-inline"><input type="checkbox" class="cmdAttr checkbox-inline" data-l1key="isVisible" checked>{{Afficher}}</label> ';
    html += '<label class="checkbox-inline"><input type="checkbox" class="cmdAttr checkbox-inline" data-l1key="isHistorized" checked>{{Historiser}}</label></td>';
  }

  html += '<td>';
  if (is_numeric(_cmd.id)) {
    html += '<a class="btn btn-default btn-xs cmdAction expertModeVisible tooltips" title="{{Configuration de la commande}}" data-action="configure"><i class="fas fa-cogs"></i></a> ';
    html += '<a class="btn btn-default btn-xs cmdAction tooltips" title="{{Test de la commande}}" data-action="test"><i class="fas fa-rss"></i></a> ';
    html += '<i class="fas fa-minus-circle pull-right cmdAction cursor" title="{{Supprimer la commande}}" data-action="remove"></i>';
  }
  html += '</td></tr>';

  const template = document.createElement('tbody');
  template.innerHTML = html;
  const row = template.firstElementChild;
  const tableBody = document.querySelector(isInfo ? '#table_info tbody' : '#table_cmd tbody');
  if (!row || !tableBody) return;
  tableBody.appendChild(row);
  row.setJeeValues(_cmd, '.cmdAttr');

  if (isInfo) {
    row.querySelector('.cmdAttr[data-l1key="type"]')?.jeeValue(init(_cmd.type));
    jeedom.cmd.changeType(row, init(_cmd.subType));
    const valueInput = row.querySelector('#' + CSS.escape(String(init(_cmd.id))) + 'value');
    if (valueInput && is_numeric(_cmd.id)) {
      valueInput.value = 'loading';
      jeedom.cmd.execute({
        id: _cmd.id,
        cache: 0,
        notify: false,
        success: function (result) {
          valueInput.value = result;
        }
      });
    }
    return;
  }

  jeedom.eqLogic.buildSelectCmd({
    id: document.querySelector('.eqLogicAttr[data-l1key="id"]')?.jeeValue(),
    filter: {type: 'info'},
    error: function (error) {
      jeedomUtils.showAlert({message: error.message, level: 'danger'});
    },
    success: function (result) {
      row.querySelector('.cmdAttr[data-l1key="value"]')?.insertAdjacentHTML('beforeend', result);
      row.querySelector('.cmdAttr[data-l1key="configuration"][data-l2key="updateCmdId"]')?.insertAdjacentHTML('beforeend', result);
      row.setJeeValues(_cmd, '.cmdAttr');
      jeedom.cmd.changeType(row, init(_cmd.subType));
    }
  });
}

document.body.addEventListener('Optoma::includeDevice', function (event) {
  const options = event.detail ?? '';
  if (modifyWithoutSave) {
    jeedomUtils.showAlert({message: "{{Un périphérique vient d'être inclus/exclus. Veuillez réactualiser la page}}", level: 'warning'});
  } else if (options === '') {
    window.location.reload();
  } else {
    window.location.href = 'index.php?v=d&p=Optoma&m=Optoma&id=' + encodeURIComponent(options);
  }
});

function getConfFile(_eqLogic) {
  domUtils.ajax({
    type: 'POST',
    url: 'plugins/Optoma/core/ajax/Optoma.ajax.php',
    data: {
      action: 'getConfFile',
      id: _eqLogic.id,
      type: _eqLogic.configuration.type
    },
    dataType: 'json',
    global: false,
    error: function (request, status, error) {
      handleAjaxError(request, status, error);
    },
    success: function (data) {
      const select = document.querySelector('.eqLogicAttr[data-l1key="configuration"][data-l2key="fileconf"]');
      if (!select) return;
      select.innerHTML = '';
      if (data.result.length > 1) {
        select.insertAdjacentHTML('beforeend', '<option value="' + data.result + '">' + data.result + '</option>');
        select.style.display = '';
        if (isset(_eqLogic.configuration.fileconf)) select.jeeValue(_eqLogic.configuration.fileconf);
      } else {
        select.style.display = 'none';
      }
      modifyWithoutSave = false;
    }
  });
}

function optomaToggleCommunicationGroups() {
  const infoMethod = document.querySelector('.eqLogicAttr[data-l1key="configuration"][data-l2key="infoMethod"]')?.jeeValue();
  const actionMethod = document.querySelector('.eqLogicAttr[data-l1key="configuration"][data-l2key="actionMethod"]')?.jeeValue();
  const usesApi = ['API', 'API-TELNET'].includes(infoMethod) || ['API', 'API-TELNET'].includes(actionMethod);
  const usesTelnet = ['TELNET', 'API-TELNET'].includes(infoMethod) || ['TELNET', 'API-TELNET'].includes(actionMethod);
  const apiGroup = document.getElementById('APIgroup');
  const telnetGroup = document.getElementById('TELNETgroup');
  if (apiGroup) apiGroup.style.display = usesApi ? '' : 'none';
  if (telnetGroup) telnetGroup.style.display = usesTelnet ? '' : 'none';
}

document.querySelectorAll('.eqLogicAttr[data-l1key="configuration"][data-l2key="actionMethod"], .eqLogicAttr[data-l1key="configuration"][data-l2key="infoMethod"]').forEach(function (select) {
  select.addEventListener('change', optomaToggleCommunicationGroups);
});

function printEqLogic(_eqLogic) {
  printEqLogicTab(_eqLogic);
  document.body.setJeeValues(_eqLogic, '.eqLogicAttr');
  initCheckBox();
  optomaToggleCommunicationGroups();
  modifyWithoutSave = false;
}

function printEqLogicTab(_eqLogic) {
  const body = document.querySelector('#table_infoseqlogic tbody');
  if (!body) return;
  body.innerHTML = '';

  printEqLogicHelper('{{Type}}', 'type', _eqLogic);
  printEqLogicHelper('{{Modèle}}', 'model', _eqLogic);
  printEqLogicHelper('{{Adresse MAC}}', 'MAC', _eqLogic);
  printEqLogicHelper('{{Version RS232}}', 'RS232Version', _eqLogic);
  printEqLogicHelper('{{Version LAN}}', 'LANFirmwareVersion', _eqLogic);
  printEqLogicHelper('{{Version Firmware}}', 'SoftwareVersion', _eqLogic);
  printEqLogicHelper('{{Découverte auto}}', 'auto_discovery', _eqLogic);
  printEqLogicHelper('{{Ports ouverts}}', 'openPorts', _eqLogic);

  if (isset(_eqLogic.configuration.model) && _eqLogic.configuration.model !== undefined) {
    document.getElementById('img_device')?.setAttribute('src', 'plugins/Optoma/core/config/img/' + _eqLogic.configuration.model + '.png');
  }
}

function printEqLogicHelper(_label, _name, _eqLogic) {
  const eqLogic = isset(_eqLogic.result) ? _eqLogic.result : _eqLogic;
  if (!isset(eqLogic.configuration[_name]) || eqLogic.configuration[_name] === undefined) return;

  const body = document.querySelector('#table_infoseqlogic tbody');
  if (!body) return;
  body.insertAdjacentHTML('beforeend',
    '<tr><td class="col-sm-4"><span style="font-size:1em;">' + _label + '</span></td>' +
    '<td><span class="label label-default" style="font-size:1em;white-space:unset!important">' +
    '<span class="eqLogicAttr" data-l1key="configuration" data-l2key="' + _name + '"></span>' +
    '</span></td></tr>'
  );
  body.lastElementChild.setJeeValues(eqLogic, '.eqLogicAttr');
}
