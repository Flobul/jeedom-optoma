<?php
/* This file is part of Jeedom. */
require_once dirname(__FILE__) . '/../../../core/php/core.inc.php';
include_file('core', 'authentification', 'php');
if (!isConnect('admin')) {
    include_file('desktop', '404', 'php');
    die();
}
$plugin = plugin::byId('Optoma');
$update = $plugin->getUpdate();
sendVarToJS('version', Optoma::$_pluginVersion);
?>
<?php include_file('desktop', 'configuration', 'css', 'Optoma'); ?>

<form class="form-horizontal" id="configuration_plugin_Optoma">
    <div class="optoma-config-page">
        <header class="optoma-config-hero">
            <div class="optoma-config-identity">
                <div class="optoma-config-logo"><img src="plugins/Optoma/plugin_info/Optoma_icon.png" alt="Optoma vidéoprojecteur"></div>
                <div><span class="optoma-config-eyebrow">{{Configuration du plugin}}</span><h2>{{Optoma vidéoprojecteur}}</h2><p>{{Configurez le rafraîchissement des informations et l'écoute Telnet optionnelle des trames envoyées par le vidéoprojecteur.}}</p></div>
            </div>
            <div class="optoma-config-meta">
                <span><i class="fas fa-code-branch"></i> {{Version}} <strong>v<?php echo htmlspecialchars((string) Optoma::$_pluginVersion, ENT_QUOTES, 'UTF-8'); ?></strong></span>
                <?php if (is_object($update)) { ?>
                    <span><i class="fas fa-stream"></i> <?php echo htmlspecialchars((string) $update->getConfiguration('version', 'stable'), ENT_QUOTES, 'UTF-8'); ?></span>
                    <span><i class="fas fa-cloud-download-alt"></i> <?php echo htmlspecialchars((string) $update->getSource(), ENT_QUOTES, 'UTF-8'); ?></span>
                <?php } ?>
            </div>
            <nav class="optoma-config-links" aria-label="{{Ressources du plugin}}">
                <a class="btn btn-sm optoma-config-link-documentation" target="_blank" rel="noopener noreferrer" href="<?php echo htmlspecialchars((string) $plugin->getDocumentation(), ENT_QUOTES, 'UTF-8'); ?>"><i class="fas fa-book"></i> {{Documentation}}</a>
                <a class="btn btn-sm optoma-config-link-changelog" target="_blank" rel="noopener noreferrer" href="<?php echo htmlspecialchars((string) $plugin->getChangelog(), ENT_QUOTES, 'UTF-8'); ?>"><i class="fas fa-list"></i> {{Changelog}}</a>
                <a class="btn btn-sm optoma-config-link-community" target="_blank" rel="noopener noreferrer" href="https://community.jeedom.com/tag/plugin-optoma"><i class="fas fa-comments"></i> {{Communauté}}</a>
            </nav>
        </header>

        <div class="optoma-config-grid">
            <section class="optoma-config-card">
                <header><span class="optoma-config-card-icon"><i class="fas fa-sync-alt"></i></span><div><h3>{{Rafraîchissement}}</h3><p>{{Fréquence de récupération des informations du vidéoprojecteur.}}</p></div></header>
                <div class="optoma-config-card-body">
                    <div class="form-group"><label class="col-sm-6 control-label">{{Intervalle de rafraîchissement}}</label><div class="col-sm-6"><select class="configKey form-control" data-l1key="autorefresh"><option value="* * * * *">{{Toutes les minutes}}</option><option value="*/2 * * * *">{{Toutes les 2 minutes}}</option><option value="*/3 * * * *">{{Toutes les 3 minutes}}</option><option value="*/4 * * * *">{{Toutes les 4 minutes}}</option><option value="*/5 * * * *">{{Toutes les 5 minutes}}</option><option value="*/10 * * * *">{{Toutes les 10 minutes}}</option><option value="*/15 * * * *">{{Toutes les 15 minutes}}</option><option value="*/30 * * * *">{{Toutes les 30 minutes}}</option><option value="*/45 * * * *">{{Toutes les 45 minutes}}</option><option value="">{{Jamais}}</option></select><span class="help-block">{{Une minute par défaut.}}</span></div></div>
                </div>
            </section>

            <section class="optoma-config-card">
                <header><span class="optoma-config-card-icon"><i class="fas fa-network-wired"></i></span><div><h3>{{Démon Telnet optionnel}}</h3><p>{{Écoute des trames INFO émises automatiquement par le vidéoprojecteur.}}</p></div></header>
                <div class="optoma-config-card-body">
                    <div class="form-group"><label class="col-sm-6 control-label">{{Adresse IP du vidéoprojecteur}}</label><div class="col-sm-6"><input type="text" class="configKey form-control deviceir" data-l1key="ip" inputmode="decimal"><span class="help-block">{{L'adresse peut être identique à celle d'un équipement.}}</span></div></div>
                    <div class="form-group"><label class="col-sm-6 control-label">{{Port d'écoute Telnet}}</label><div class="col-sm-6"><input type="number" min="1" max="65535" class="configKey form-control" data-l1key="listenport" placeholder="23"><span class="help-block">{{Le port 23 est requis pour recevoir les trames INFO automatiques.}}</span></div></div>
                    <div class="optoma-config-notice"><i class="fas fa-info-circle"></i><span>{{Les ports 1023 et 2023 permettent une connexion, mais ne transmettent pas ces messages automatiques.}}</span></div>
                </div>
            </section>
        </div>
    </div>
</form>
<?php include_file('desktop', 'configuration', 'js', 'Optoma'); ?>
