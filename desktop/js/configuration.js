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

// Attribuer le même niveau à tous les logs du plugin.
document.getElementById('bt_savePluginLogConfig')?.addEventListener('click', function () {
  const pluginId = document.getElementById('span_plugin_id')?.textContent.trim();
  const logContainer = document.getElementById('div_plugin_log');
  if (!pluginId || !logContainer) return;

  const baseConfiguration = JSON.stringify(logContainer.getJeeValues('.configKey')[0]);
  document.querySelectorAll('.bt_plugin_conf_view_log').forEach(function (button) {
    const configuration = JSON.parse(
      baseConfiguration.replace('log::level::' + pluginId, 'log::level::' + button.dataset.log)
    );

    jeedom.config.save({
      configuration: configuration,
      error: function (error) {
        jeedomUtils.showAlert({message: error.message, level: 'danger'});
      },
      success: function () {
        jeedomUtils.showAlert({message: '{{Sauvegarde de la configuration des logs dev effectuée}}', level: 'success'});
        modifyWithoutSave = false;
      }
    });
  });
});

// Afficher la version du plugin devant la date d'installation.
const installDate = document.getElementById('span_plugin_install_date');
if (installDate) {
  installDate.textContent = 'v' + version + ' (' + installDate.textContent + ')';
}
