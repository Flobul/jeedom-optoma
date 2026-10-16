<?php
/* This file is part of Jeedom. */
if (!isConnect('admin')) {
    throw new Exception(__('401 - Accès non autorisé', __FILE__));
}
$eqLogics = Optoma::byType('Optoma');
$eqLogics = is_array($eqLogics) ? $eqLogics : array();
$enabledCount = 0;
$healthyCount = 0;
$poweredCount = 0;
foreach ($eqLogics as $healthEqLogic) {
    if ($healthEqLogic->getIsEnable()) $enabledCount++;
    if ($healthEqLogic->getStatus('state') !== 'nok') $healthyCount++;
    $powerCommand = $healthEqLogic->getCmd('info', 'Powerstatus');
    if (is_object($powerCommand) && (int) $powerCommand->execCmd() === 1) $poweredCount++;
}
?>
<?php include_file('desktop', 'health', 'css', 'Optoma'); ?>

<div id="optoma-health">
    <header class="optoma-health-hero">
        <span class="optoma-health-hero-icon"><i class="fas fa-heartbeat"></i></span>
        <div><span class="optoma-health-eyebrow">{{Diagnostic du plugin}}</span><h2>{{Santé Optoma}}</h2><p>{{État des vidéoprojecteurs, alimentation, versions et dernières communications.}}</p></div>
    </header>

    <section class="optoma-health-summary" aria-label="{{Résumé de la santé Optoma}}">
        <article><span class="optoma-health-summary-icon"><i class="fas fa-video"></i></span><div><span>{{Vidéoprojecteurs}}</span><strong><?php echo count($eqLogics); ?></strong><small>{{configuré(s)}}</small></div></article>
        <article><span class="optoma-health-summary-icon"><i class="fas fa-power-off"></i></span><div><span>{{Actifs}}</span><strong><?php echo (int) $enabledCount; ?></strong><small><?php echo max(0, count($eqLogics) - $enabledCount); ?> {{désactivé(s)}}</small></div></article>
        <article><span class="optoma-health-summary-icon"><i class="fas fa-check-circle"></i></span><div><span>{{Sans erreur}}</span><strong><?php echo (int) $healthyCount; ?></strong><small>{{statut Jeedom OK}}</small></div></article>
        <article><span class="optoma-health-summary-icon"><i class="fas fa-lightbulb"></i></span><div><span>{{Allumés}}</span><strong><?php echo (int) $poweredCount; ?></strong><small>{{signalés sous tension}}</small></div></article>
    </section>

    <section class="optoma-health-panel">
        <div class="optoma-health-panel-header"><i class="fas fa-list"></i><h3>{{Équipements Optoma}}</h3></div>
        <div class="table-responsive">
            <table class="table table-condensed table-striped tablesorter" id="table_healthoptoma">
                <thead><tr><th>{{Appareil}}</th><th>{{IP}}</th><th>{{Modèle}}</th><th>{{Statut}}</th><th>{{Allumé}}</th><th>{{Versions}}</th><th>{{Dernière communication}}</th><th>{{Date création}}</th></tr></thead>
                <tbody>
                    <?php foreach ($eqLogics as $eqLogic) {
                        $powerCommand = $eqLogic->getCmd('info', 'Powerstatus');
                        $powered = is_object($powerCommand) && (int) $powerCommand->execCmd() === 1;
                        $versions = implode(' · ', array_filter(array(
                            (string) $eqLogic->getConfiguration('LANFirmwareVersion', ''),
                            (string) $eqLogic->getConfiguration('RS232Version', ''),
                            (string) $eqLogic->getConfiguration('SoftwareVersion', '')
                        ), function ($value) { return $value !== ''; }));
                    ?>
                        <tr<?php echo $eqLogic->getIsEnable() ? '' : ' class="optoma-health-disabled"'; ?>>
                            <td><a href="<?php echo htmlspecialchars($eqLogic->getLinkToConfiguration(), ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($eqLogic->getHumanName(true), ENT_QUOTES, 'UTF-8'); ?></a></td>
                            <td><span class="label label-info"><?php echo htmlspecialchars((string) $eqLogic->getConfiguration('IP', 'N/A'), ENT_QUOTES, 'UTF-8'); ?></span></td>
                            <td><?php echo htmlspecialchars(trim((string) $eqLogic->getConfiguration('type', '') . ' ' . (string) $eqLogic->getConfiguration('model', '')), ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><span class="label label-<?php echo $eqLogic->getStatus('state') === 'nok' ? 'danger' : 'success'; ?>"><?php echo $eqLogic->getStatus('state') === 'nok' ? '{{NOK}}' : '{{OK}}'; ?></span></td>
                            <td><span class="label label-<?php echo $powered ? 'success' : 'default'; ?>"><i class="fas <?php echo $powered ? 'fa-lightbulb' : 'fa-power-off'; ?>"></i> <?php echo $powered ? '{{OUI}}' : '{{NON}}'; ?></span></td>
                            <td><?php echo htmlspecialchars($versions !== '' ? $versions : 'N/A', ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo htmlspecialchars((string) $eqLogic->getStatus('lastCommunication'), ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo htmlspecialchars((string) $eqLogic->getConfiguration('createtime'), ENT_QUOTES, 'UTF-8'); ?></td>
                        </tr>
                    <?php } ?>
                    <?php if ($eqLogics === array()) { ?><tr><td colspan="8" class="text-center text-muted">{{Aucun vidéoprojecteur Optoma}}</td></tr><?php } ?>
                </tbody>
            </table>
        </div>
    </section>
</div>
