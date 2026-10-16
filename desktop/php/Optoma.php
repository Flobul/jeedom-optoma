<?php
if (!isConnect('admin')) {
    throw new Exception('{{401 - Accès non autorisé}}');
}
$plugin = plugin::byId('Optoma');
sendVarToJS('eqType', $plugin->getId());
$eqLogics = eqLogic::byType($plugin->getId());
$state = config::byKey('include_mode', 'Optoma', 0);
echo '<div id="div_inclusionAlert"></div>';
if ($state == 1) {
    echo '<div class="alert jqAlert alert-warning" id="div_inclusionAlert" style="margin : 0px 5px 15px 15px; padding : 7px 35px 7px 15px;">{{Vous êtes en mode inclusion. Cliquez à nouveau sur le bouton d\'inclusion pour sortir de ce mode}}</div>';
}
?>
<?php include_file('desktop', 'Optoma', 'css', 'Optoma'); ?>
<div class="row row-overflow">
  <div class="col-lg-10 col-md-9 col-sm-8 eqLogicThumbnailDisplay" style="border-left: solid 1px #EEE; padding-left: 25px;">
    <legend class="help" data-help="{{Pour utiliser le mode inclusion, il faut au préalable avoir activé la fonction AMX Device Discovery sur votre vidéoprojecteur.}}"><i class="fas fa-cog"></i> {{Gestion}}</legend>
    <div class="eqLogicThumbnailContainer">
      <?php
      if ($state == 1) {
          echo '<div class="cursor changeIncludeState card" data-state="0" >';
          echo '<center>';
          echo '<i class="fas fa-sign-in-alt fa-rotate-90"></i>';
          echo '</center>';
          echo '<span><center>{{Arrêter inclusion}}</center></span>';
          echo '</div>';
      } else {
          echo '<div class="cursor changeIncludeState card" data-state="1" ';
          echo '<center>';
          echo '<i class="fas fa-sign-in-alt fa-rotate-90"></i>';
          echo '</center>';
          echo '<span title="{{Recherche automatique du vidéoprojecteur grâce à la fonction AMX Device Discovery.}}"><center>{{Mode inclusion AMX}}</center></span>';
          echo '</div>';
      }
      ?>
      <div class="cursor eqLogicAction logoPrimary" data-action="add">
        <i class="fas fa-plus-circle"></i>
        <br>
        <span>{{Ajouter}}</span>
      </div>
      <div class="cursor eqLogicAction logoSecondary" data-action="gotoPluginConf">
        <i class="fas fa-wrench"></i>
        <br>
        <span>{{Configuration}}</span>
      </div>
      <div class="cursor logoSecondary" id="bt_healthoptoma">
        <i class="fas fa-heartbeat"></i>
        <span>
          <center>{{Santé}}</center>
        </span>
      </div>
      <div class="cursor logoSecondary" id="bt_documentationOptoma" data-location="<?=$plugin->getDocumentation()?>">
        <i class="icon loisir-livres"></i>
        <br><br>
        <span>{{Documentation}}</span>
      </div>
    </div>
    <legend><i class="fas fa-photo-video"></i> {{Mes vidéoprojecteurs}}</legend>
    <div class="input-group" style="margin:5px;">
      <input class="form-control roundedLeft" placeholder="{{Rechercher}}" id="in_searchEqlogic" />
      <div class="input-group-btn">
        <a id="bt_resetSearch" class="btn roundedRight" style="width:30px"><i class="fas fa-times"></i></a>
      </div>
    </div>
    <div class="eqLogicThumbnailContainer">
      <?php
          foreach ($eqLogics as $eqLogic) {
              $opacity = ($eqLogic->getIsEnable()) ? '' : 'disableCard';
              $model = (empty($eqLogic->getConfiguration('model'))) ? 'Sans modèle' : $eqLogic->getConfiguration('model');
              $IP = (empty($eqLogic->getConfiguration('IP'))) ? 'Aucune IP' : $eqLogic->getConfiguration('IP');
              $actionMethod = (empty($eqLogic->getConfiguration('actionMethod'))) ? 'Aucune' : $eqLogic->getConfiguration('actionMethod');
              $infoMethod = (empty($eqLogic->getConfiguration('infoMethod'))) ? 'Aucune' : $eqLogic->getConfiguration('infoMethod');
              if (is_object($eqLogic->getCmd('info', 'Powerstatus'))) {
                  $status = ($eqLogic->getCmd('info', 'Powerstatus')->execCmd() == 1) ? "{{Allumé}}" : "{{Éteint}}";
                  $status = "État : " . $status;
              } else {
                  $status = "";
              }
              echo '<div class="eqLogicDisplayCard cursor '.$opacity.'" data-eqLogic_id="' . $eqLogic->getId() . '">';
              echo '<img src="' . $plugin->getPathImgIcon() . '" height="105" width="95"
                      title="{{Nom}} : ' . $eqLogic->getName() . '</br>
                      {{Modèle}} : ' . $model . '</br>
                      IP : ' . $IP . '</br>
                      ' . $status . '</br>
                      {{Méthode action}} : ' . $actionMethod . '</br>
                      {{Méthode info}} : ' . $infoMethod . '</br>
                      ">';
              echo "<br>";
              echo '<span class="name" style="font-size : 14px;position:relative; top : 15px;word-break: break-all;white-space: pre-wrap;word-wrap: break-word;">' . $eqLogic->getHumanName(true, true) . '</span>';
              echo '</div>';
          }
      ?>
    </div>
  </div>

  <div class="col-xs-12 eqLogic" style="display: none;">
    <div class="input-group pull-right" style="display:inline-flex">
      <span class="input-group-btn">
        <a class="btn btn-default btn-sm eqLogicAction roundedLeft" data-action="configure"><i class="fas fa-cogs"></i> {{Configuration avancée}}</a>
        <a class="btn btn-default btn-sm eqLogicAction" data-action="copy"><i class="fas fa-copy"></i> {{Dupliquer}}</a>
        <a class="btn btn-sm btn-success eqLogicAction" data-action="save"><i class="fas fa-check-circle"></i> {{Sauvegarder}}</a>
        <a class="btn btn-danger btn-sm eqLogicAction roundedRight" data-action="remove"><i class="fas fa-minus-circle"></i> {{Supprimer}}</a>
      </span>
    </div>
    <ul class="nav nav-tabs" role="tablist">
      <li role="presentation"><a href="#" class="eqLogicAction" aria-controls="home" role="tab" data-toggle="tab" data-action="returnToThumbnailDisplay"><i class="fas fa-arrow-circle-left"></i></a></li>
      <li role="presentation" class="active"><a href="#eqlogictab" aria-controls="home" role="tab" data-toggle="tab"><i class="fas fa-tachometer-alt"></i> {{Équipement}}</a></li>
      <li role="presentation"><a href="#commandtab" aria-controls="profile" role="tab" data-toggle="tab"><i class="fas fa-list-alt"></i> {{Commandes}}</a></li>
      <li role="presentation"><a href="#infotab" aria-controls="profile" role="tab" data-toggle="tab"><i class="fas fa-info-circle"></i> {{Informations}}</a></li>
    </ul>
    <div class="tab-content" style="height:calc(100% - 50px);overflow:auto;overflow-x: hidden;">
      <div role="tabpanel" class="tab-pane active" id="eqlogictab">
        <div class="col-xs-6">
          <form class="form-horizontal">
            <fieldset>
              <div class="form-group">
                <legend><i class="fas fa-sitemap icon_green"></i> {{Général}}</legend>
                <label class="col-sm-4 control-label">{{Nom du vidéoprojecteur}}</label>
                <div class="col-sm-5">
                  <input type="text" class="eqLogicAttr form-control" data-l1key="id" style="display : none;" />
                  <input type="text" class="eqLogicAttr form-control" data-l1key="name" placeholder="{{Nom du vidéoprojecteur}}" />
                </div>
              </div>
              <div class="form-group">
                <label class="col-sm-4 control-label">{{Objet parent}}</label>
                <div class="col-sm-5">
                  <select id="sel_object" class="eqLogicAttr form-control" data-l1key="object_id">
                    <option value="">{{Aucun}}</option>
                    <?php
                      $options = '';
                      foreach ((jeeObject::buildTree(null, false)) as $object) {
                          $options .= '<option value="' . $object->getId() . '">' . str_repeat('&nbsp;&nbsp;', $object->getConfiguration("parentNumber")) . $object->getName() . '</option>';
                      }
                      echo $options;
                    ?>
                  </select>
                </div>
              </div>

              <div class="form-group">
                <label class="col-sm-4 control-label">{{Catégorie}}</label>
                <div class="col-sm-8">
                  <?php
                      foreach (jeedom::getConfiguration('eqLogic:category') as $key => $value) {
                          echo '<label class="checkbox-inline">';
                          echo '<input type="checkbox" class="eqLogicAttr" data-l1key="category" data-l2key="' . $key . '" />' . $value['name'];
                          echo '</label>';
                      }
                  ?>
                </div>
              </div>

              <div class="form-group">
                <label class="col-sm-4 control-label">{{Options}}</label>
                <div class="col-sm-8">
                  <label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="isEnable" checked />{{Activer}}</label>
                  <label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="isVisible" checked />{{Visible}}</label>
                </div>
              </div>

              <div class="form-group">
                <label class="col-sm-4 control-label help" data-help="{{Cocher la case pour utiliser le widget associé au type de l'appareil.}}</br>{{Laissez décoché pour laisser le core générer le widget par défaut.}}">{{Widget équipement}}
                </label>
                <div class="col-sm-8">
                  <input type="checkbox" class="eqLogicAttr form-control" id="widgetTemplate" data-l1key="configuration" data-l2key="widgetTemplate" />
                </div>
              </div>

            </fieldset>
            <legend><i class="fas fa-cogs icon_blue"></i> {{Paramètres du vidéoprojecteur}}
            </legend>
            <fieldset>
              <div class="form-group">
                <label class="col-sm-3 control-label help" data-help="{{Entrez l'adresse IP de votre vidéoprojecteur.}}
{{Si vous n'avez pas activé la fonction AMX Device Discovery,
saisissez l'adresse manuellement.}}">{{Adresse IP}}

                </label>
                <div class="col-sm-5">
                  <input id="idipoptoma" type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="IP" placeholder="192.168.X.XXX" />
                </div>
              </div>

              <div class="form-group">
                <label class="col-sm-3 control-label">{{Accès à la page web}}</label>
                <div class="col-sm-3">
                  <a class="btn btn-default  pull-left" id="bt_weboptoma"><i class="fas fa-cogs"></i> {{Interface web Optoma}}</a>
                </div>
              </div>
              <div class="form-group" style="display:none">
                <label class="col-sm-3 control-label help" data-help="{{Entrez l'identifiant de votre vidéoprojecteur.}}
{{Ce champ est grisé sur certains vidéoprojecteurs.}}
{{Par défaut : admin}}">{{Identifiant et mot de passe}}
                </label>

                <div class="col-sm-3">
                  <input type="text" class="eqLogicAttr form-control" disabled data-l1key="configuration" data-l2key="username" placeholder="admin" />
                </div>

                <div class="col-sm-3">
                  <input type="password" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="password" placeholder="******" />
                </div>
            </fieldset>
          </form>
        </div>
        <div class="col-sm-6">
          <form class="form-horizontal">
            <legend><i class="fas fa-wrench icon_orange"></i> {{Configuration}}</legend>
            <fieldset>
              <div class="form-group">
                <div class="col-sm-4 control-label">
                  <a id="bt_autoDetectModule" class="btn btn-warning" title="{{Recréer les commandes}}" style="display:none"><i class="fas fa-search"></i> {{Recréer les commandes}}</a>
                </div>
              </div>
              <div class="form-group" style="display:none">
                <label class="col-sm-4 control-label help" data-help="{{Sélectionnez le fichier de configuration des commandes.}}">{{Fichier de configuration}}
                </label>
                <div class="col-sm-5 control-label">
                  <select class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="fileconf"></select>
                </div>
              </div>

              <div class="form-group">
                <label class="col-sm-5 control-label help" data-help="{{Sélectionnez la méthode d'envoi des commandes action.}}">{{Méthode de communication (action)}}
                </label>
                <div class="col-sm-5">
                  <select class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="actionMethod">
                    <option value="" disabled="disabled">{{Aucun}}</option>
                    <option value="API">{{API web}}</option>
                    <option value="TELNET">{{Telnet}}</option>
                    <option value="CRESTRON">{{Crestron (en test)}}</option>
                    <option value="API-TELNET">{{API + TELNET}}</option>
                  </select>
                </div>
              </div>

              <div class="form-group">
                <label class="col-sm-5 control-label help" data-help="{{Sélectionnez la méthode de récupération des commandes informations}}">{{Méthode de communication (info)}}
                </label>
                <div class="col-sm-5">
                  <select class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="infoMethod">
                    <option value="" disabled="disabled">{{Aucun}}</option>
                    <option value="API">{{API web}}</option>
                    <option value="TELNET">{{Telnet}}</option>
                    <option value="API-TELNET">{{API + TELNET}}</option>
                  </select>
                </div>
              </div>

              <div class="form-group" id="APIgroup">
                <label class="col-sm-4 control-label help" data-help="{{Sélectionnez l'adresse de l'API de votre vidéoprojecteur.}}">{{Adresse API}}
                </label>
                <div class="col-sm-5">
                  <select id="idipoptoma" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="API">
                    <option value="">{{Aucun}}</option>
                    <option value="/form/control_cgi">/form/control_cgi</option>
                    <option value="/tgi/control.tgi">/tgi/control.tgi (non géré actuellement)</option>
                  </select>
                </div>
              </div>

              <div class="form-group" id="TELNETgroup">
                <label class="col-sm-4 control-label help" data-help="{{Sélectionnez le port telnet utilisé pour récupérer les informations.}}">{{Port telnet}}
                </label>
                <div class="col-sm-5">
                  <select class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="telnetPort">
                    <option value="">{{Aucun}}</option>
                    <option value="23" disabled="disabled">23 (utilisé par le démon)</option>
                    <option value="1023">1023</option>
                    <option value="2023">2023</option>
                  </select>
                </div>
                <label class="col-sm-4 control-label help" data-help="{{Sélectionnez l'ID de votre vidéoprojecteur.}}</br>{{Si vous ne le connaissez pas, laissez OO.}}">{{ID du vidéoprojecteur}}</label>
                <div class="col-sm-5">
                  <select class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="ID">
                    <?php
                      for ($a=0; $a< 10; $a++) {
                          for ($b=0; $b< 10; $b++) {
                              if ($a == 0 && $b == 0) {
                                  echo '<option value="' . $a . $b . '" selected>' . $a . $b . ' (tous)</option>';
                              } else {
                                  echo '<option value="' . $a . $b . '">' . $a . $b . '</option>';
                              }
                          }
                      }
                    ?>
                  </select>
                </div>
                <label class="col-sm-4 control-label">{{Délai d'attente entre chaque commande envoyée (en ms)}}</label>
                <div class="col-sm-3">
                  <select class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="waitdelay">
                    <option value="0">0 ms</option>
                    <option value="100">100 ms</option>
                    <option value="200">200 ms</option>
                    <option value="500">500 ms</option>
                  </select>
                </div>
              </div>

            </fieldset>

            <legend><i class="fas fa-info-circle icon_yellow"></i> {{Informations}}</legend>
            <fieldset>

              <div class="form-group">
                <table id="table_infoseqlogic" class="col-sm-9 table-bordered table-condensed" style="border-radius: 10px;">
                  <thead>
                  </thead>
                  <tbody>
                  </tbody>
                </table>
                </br>
              </div>
              <div class="form-group">
                <div class="col-sm-10">
                  <center>
                    <img src="core/img/no_image.gif" data-original=".svg" id="img_device" class="img-responsive" style="max-height:450px;max-width:400px" onerror="this.src='plugins/Optoma/core/config/img/UHD.png'" />
                  </center>
                </div>
              </div>
              <div class="form-group" style="display:none">
                <label class="col-sm-2 control-label">{{Paramètres}}</label>
                <div class="col-sm-10">
                  <a class="btn btn-primary" id="bt_configureDevice" title='{{Configurer}}'><i class="fas fa-wrench"></i> {{Configuration}}</a>
                </div>
              </div>
            </fieldset>
          </form>
        </div>
      </div>


      <div role="tabpanel" class="tab-pane" id="commandtab">
        <table id="table_cmd" class="table table-bordered table-condensed">
          <thead>
            <tr>
              <th style="width: 300px;">{{Nom}}</th>
              <th style="width: 220px;">{{Type}}</th>
              <th>{{Commande information à mettre à jour}}</th>
              <th style="width: 200px;">{{Paramètres}}</th>
              <th style="width: 90px;">{{Action}}</th>
            </tr>
          </thead>
          <tbody>
          </tbody>
        </table>
      </div>
      <div role="tabpanel" class="tab-pane" id="infotab">
        <table id="table_info" class="table table-bordered table-condensed">
          <thead>
            <tr>
              <th style="width: 250px;">{{Nom}}</th>
              <th style="width: 220px;">{{Type}}</th>
              <th>{{Valeur}}</th>
              <th style="width: 200px;">{{Paramètres}}</th>
              <th style="width: 90px;">{{Action}}</th>
            </tr>
          </thead>
          <tbody>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<?php
include_file('desktop', 'Optoma', 'js', 'Optoma');
include_file('core', 'plugin.template', 'js');
?>