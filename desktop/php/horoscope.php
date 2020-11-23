<?php
if (!isConnect('admin')) {
    throw new Exception('{{401 - Accès non autorisé}}');
}
// Déclaration des variables obligatoires
$plugin = plugin::byId('horoscope');
sendVarToJS('eqType', $plugin->getId());
$eqLogics = eqLogic::byType($plugin->getId());
?>

<div class="row row-overflow">
    <!-- Page d'accueil du plugin -->
    <div class="col-xs-12 eqLogicThumbnailDisplay">
        <legend><i class="fas fa-cog"></i> {{Gestion}}</legend>
        <!-- Boutons de gestion du plugin -->
        <div class="eqLogicThumbnailContainer">
            <div class="cursor eqLogicAction logoPrimary" data-action="add">
                <i class="fas fa-plus-circle"></i>
                <br />
                <span>{{Ajouter}}</span>
            </div>
            <div class="cursor eqLogicAction logoSecondary" data-action="gotoPluginConf">
                <i class="fas fa-wrench"></i><br>
                <span>{{Configuration}}</span>
            </div>
        </div>
        <!-- Champ de recherche -->
        <div class="input-group" style="margin:5px;">
            <input class="form-control roundedLeft" placeholder="{{Rechercher}}" id="in_searchEqlogic" />
            <div class="input-group-btn">
                <a id="bt_resetSearch" class="btn roundedRight" style="width:30px"><i class="fas fa-times"></i></a>
            </div>
        </div>
        <legend><i class="fas fa-address-card"></i> {{Mes Horoscopes}}</legend>
        <!-- Liste des équipements du plugin -->
        <div class="eqLogicThumbnailContainer">
            <?php
            foreach ($eqLogics as $eqLogic) {
                $opacity = ($eqLogic->getIsEnable()) ? '' : 'disableCard';
                echo '<div class="eqLogicDisplayCard cursor ' . $opacity . '" data-eqLogic_id="' . $eqLogic->getId() . '" >';
                if ($eqLogic->getConfiguration('signe') != '') {
                    echo '<img src="' . $eqLogic->getImage() . '"/>';
                } else {
                    echo '<img src="' . $plugin->getPathImgIcon() . '"/>';
                }
                echo '<br>';
                echo '<span class="name">' . $eqLogic->getHumanName(true, true) . '</span>';
                echo '</div>';
            }
            ?>
        </div>
    </div><!-- /.eqLogicThumbnailDisplay -->

    <!-- Page de présentation de l'équipement -->
    <div class="col-xs-12 eqLogic" style="display: none;">
        <!-- barre de gestion de l'équipement -->
        <div class="input-group pull-right" style="display:inline-flex">
            <span class="input-group-btn">
                <!-- Les balises <a></a> sont volontairement fermées à la ligne suivante pour éviter les espaces entre les boutons. Ne pas modifier -->
                <a class="btn btn-default btn-sm eqLogicAction roundedLeft" data-action="configure"><i class="fas fa-cogs"></i><span class="hidden-xs"> {{Configuration avancée}}</span>
                </a><a class="btn btn-default btn-sm eqLogicAction" data-action="copy"><i class="fas fa-copy"></i><span class="hidden-xs"> {{Dupliquer}}</span>
                </a><a class="btn btn-sm btn-success eqLogicAction" data-action="save"><i class="fas fa-check-circle"></i> {{Sauvegarder}}
                </a><a class="btn btn-danger btn-sm eqLogicAction roundedRight" data-action="remove"><i class="fas fa-minus-circle"></i> {{Supprimer}}</a>
            </span>
        </div>
        <!-- Onglets -->
        <ul class="nav nav-tabs" role="tablist">
            <li role="presentation"><a href="#" class="eqLogicAction" aria-controls="home" role="tab" data-toggle="tab" data-action="returnToThumbnailDisplay"><i class="fa fa-arrow-circle-left"></i></a></li>
            <li role="presentation" class="active"><a href="#eqlogictab" aria-controls="home" role="tab" data-toggle="tab"><i class="fas fa-tachometer-alt"></i> {{Equipement}}</a></li>
            <li role="presentation"><a href="#commandtab" aria-controls="profile" role="tab" data-toggle="tab"><i class="fa fa-list-alt"></i> {{Commandes}}</a></li>
        </ul>
        <div class="tab-content">
            <!-- Onglet de configuration de l'équipement -->
            <div role="tabpanel" class="tab-pane active" id="eqlogictab">
                <br />
                <div class="row">
                    <!-- Partie gauche de l'onglet "Equipements" -->
                    <!-- Paramètres généraux de l'équipement -->
                    <div class="col-lg-7">
                        <form class="form-horizontal">
                            <fieldset>
                                <legend> {{}}</legend> <!-- Partie générale non affiché <i class="fas fa-wrench"></i> {{Général}}" -->
                                <div class="form-group">
                                    <label class="col-sm-3 control-label">{{Nom de l'équipement}}</label>
                                    <div class="col-xs-11 col-sm-7">
                                        <input type="text" class="eqLogicAttr form-control" data-l1key="id" style="display : none;" />
                                        <input type="text" class="eqLogicAttr form-control" data-l1key="name" placeholder="{{Nom de l'équipement}}" />
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-3 control-label">{{Objet parent}}</label>
                                    <div class="col-xs-11 col-sm-7">
                                        <select id="sel_object" class="eqLogicAttr form-control" data-l1key="object_id">
                                            <option value="">{{Aucun}}</option>
                                            <?php
                                            $options = '';
                                            foreach ((jeeObject::buildTree(null, false)) as $object) {
                                                $options .= '<option value="' . $object->getId() . '">' . str_repeat('&nbsp;&nbsp;', $object->getConfiguration('parentNumber')) . $object->getName() . '</option>';
                                            }
                                            echo $options;
                                            ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-3 control-label">{{Catégorie}}</label>
                                    <div class="col-sm-9">
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
                                    <label class="col-sm-3 control-label">{{Options}}</label>
                                    <div class="col-xs-11 col-sm-7">
                                        <label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="isEnable" checked />{{Activer}}</label>
                                        <label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="isVisible" checked />{{Visible}}</label>
                                    </div>
                                </div>

                                <br />
                                <legend><i class="fas fa-cogs"></i> {{Paramètres}}</legend>
                                <div class="form-group">
                                    <label class="col-sm-3 control-label">{{Signe du zodiaque}}
                                        <sup><i class="fas fa-question-circle" title="{{Choisir son signe}}"></i></sup>
                                    </label>
                                    <div class="col-xs-11 col-sm-7">
                                        <select id="type_calcul" class="form-control eqLogicAttr" data-l1key="configuration" data-l2key="signe">
                                            <option value=''>{{Aucun}}</option>
                                            <option value='balance'>{{Balance}}</option>
                                            <option value='belier'>{{Bélier}}</option>
                                            <option value='cancer'>{{Cancer}}</option>
                                            <option value='capricorne'>{{Capricorne}}</option>
                                            <option value='gemeaux'>{{Gémeaux}}</option>
                                            <option value='lion'>{{Lion}}</option>
                                            <option value='poissons'>{{Poissons}}</option>
                                            <option value='sagittaire'>{{Sagittaire}}</option>
                                            <option value='scorpion'>{{Scorpion}}</option>
                                            <option value='taureau'>{{Taureau}}</option>
                                            <option value='vierge'>{{Vierge}}</option>
                                            <option value='verseau'>{{Verseau}}</option>
                                        </select>
                                    </div>
                                </div>
                                <!-- Champ de saisie du cron d'auto-actualisation + assistant cron -->
                                <!-- La fonction cron de la classe du plugin doit contenir le code prévu pour que ce champ soit fonctionnel -->
                                <div class="form-group">
                                    <label class="col-sm-3 control-label">{{Auto-actualisation}}
                                        <sup><i class="fas fa-question-circle" title="{{Fréquence de rafraîchissement de l'équipement}}"></i></sup>
                                    </label>
                                    <div class="col-xs-11 col-sm-7">
                                        <div class="input-group">
                                            <input type="text" class="eqLogicAttr form-control roundedLeft" data-l1key="configuration" data-l2key="autorefresh" placeholder="{{Cliquer sur ? pour afficher l'assistant cron}}" />
                                            <span class="input-group-btn">
                                                <a class="btn btn-default cursor jeeHelper roundedRight" data-helper="cron" title="Assistant cron">
                                                    <i class="fas fa-question-circle"></i>
                                                </a>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <br />
                            </fieldset>
                        </form>
                    </div>
                    <!-- Partie droite de l'onglet "Equipement" -->
                    <!-- Affiche l'icône du plugin par défaut mais vous pouvez y afficher les informations de votre choix -->
                    <div class="col-lg-5">
                        <form class="form-horizontal">
                            <fieldset>
                                <legend><i class="fas fa-info"></i> {{Informations}}</legend>
                                <div class=" form-group">
                                    <label class="col-sm-4 control-label"></label>
                                    <div class="col-sm-7 text-center">
                                        <img src="plugins/Freebox_OS/core/images/default.png" data-original=".jpg" id="img_device" class="img-responsive" style="width:120px" onerror="this.src='plugins/Freebox_OS/core/images/default.png'" />
                                    </div>
                                </div>
                            </fieldset>
                        </form>
                    </div>
                </div><!-- /.row-->
            </div> <!-- /.tabpanel #eqlogictab-->

            <!-- Onglet des commandes de l'équipement -->
            <div role="tabpanel" class="tab-pane" id="commandtab">
                <!-- <a class="btn btn-success btn-sm cmdAction pull-right" data-action="add" style="margin-top:5px;"><i class="fa fa-plus-circle"></i> {{Commandes}}</a> -->
                <br /><br />
                <div class="table-responsive">
                    <table id="table_cmd" class="table table-bordered table-condensed">
                        <thead>
                            <tr>
                                <th>{{Id}}</th>
                                <th>{{Nom}}</th>
                                <th>{{Type}}</th>
                                <th>{{Options}}</th>
                                <th>{{Paramètres}}</th>
                                <th>{{Action}}</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div><!-- /.tabpanel #commandtab-->

        </div><!-- /.tab-content -->
    </div><!-- /.eqLogic -->
</div><!-- /.row row-overflow -->
<?php
include_file('desktop', 'horoscope', 'js', 'horoscope');
include_file('core', 'plugin.template', 'js');
?>