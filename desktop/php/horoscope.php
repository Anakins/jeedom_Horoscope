<?php
if (!isConnect('admin')) {
    throw new Exception('{{401 - Accès non autorisé}}');
}
$plugin = plugin::byId('horoscope');
sendVarToJS('eqType', $plugin->getId());
$eqLogics = eqLogic::byType($plugin->getId());
?>

<div class="row row-overflow">
    <div class="col-xs-12 eqLogicThumbnailDisplay">
        <legend><i class="fas fa-cog"></i> {{Gestion}}</legend>
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
        <legend><i class="fas fa-address-card"></i> {{Mes Horoscopes}}</legend>
        <input class="form-control" placeholder="{{Rechercher}}" id="in_searchEqlogic" />
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
    </div>
    <div class="col-xs-12 eqLogic" style="display: none;">
        <div class="input-group pull-right" style="display:inline-flex">
            <span class="input-group-btn">
                <a class="btn btn-default btn-sm eqLogicAction roundedLeft" data-action="configure"><i class="fas fa-cogs"></i> {{Configuration avancée}}</a><a class="btn btn-default btn-sm eqLogicAction" data-action="copy"><i class="fas fa-copy"></i> {{Dupliquer}}</a><a class="btn btn-sm btn-success eqLogicAction" data-action="save"><i class="fas fa-check-circle"></i> {{Sauvegarder}}</a><a class="btn btn-danger btn-sm eqLogicAction roundedRight" data-action="remove"><i class="fas fa-minus-circle"></i> {{Supprimer}}</a>
            </span>
        </div>

        <ul class="nav nav-tabs" role="tablist">
            <li role="presentation"><a href="#" class="eqLogicAction" aria-controls="home" role="tab" data-toggle="tab" data-action="returnToThumbnailDisplay"><i class="fas fa-arrow-circle-left"></i></a></li>
            <li role="presentation" class="active"><a href="#eqlogictab" aria-controls="home" role="tab" data-toggle="tab"><i class="fas fa-tachometer-alt"></i> {{Equipement}}</a></li>
            <li role="presentation"><a href="#commandtab" aria-controls="profile" role="tab" data-toggle="tab"><i class="fas fa-list-alt"></i> {{Commandes}}</a></li>
        </ul>
        <div class="tab-content" style="height:calc(100% - 50px);overflow:auto;overflow-x: hidden;">
            <div role="tabpanel" class="tab-pane active" id="eqlogictab">
                <br />
                <form class="form-horizontal col-sm-10">
                    <fieldset>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">{{Nom de l'équipement}}</label>
                            <div class="col-sm-3">
                                <input type="text" class="eqLogicAttr form-control" data-l1key="id" style="display : none;" />
                                <input type="text" class="eqLogicAttr form-control" data-l1key="name" placeholder="{{Nom de l\'équipement}}" />
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">{{Objet parent}}</label>
                            <div class="col-sm-3">
                                <select id="sel_object" class="eqLogicAttr form-control" data-l1key="object_id">
                                    <option value="">{{Aucun}}</option>
                                    <?php
                                    foreach (jeeObject::all() as $object) {
                                        echo '<option value="' . $object->getId() . '">' . $object->getName() . '</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">{{Catégorie}}</label>
                            <div class="col-sm-10">
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
                            <label class="col-sm-2 control-label"></label>
                            <div class="col-sm-10">
                                <label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="isEnable" checked />{{Activer}}</label>
                                <label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="isVisible" checked />{{Visible}}</label>
                            </div>
                        </div>
                    </fieldset>
                </form>

                <form class="form-horizontal col-sm-2">
                    <fieldset>
                        <div class="form-group">
                            <label class="col-sm-2 control-label"></label>
                            <div class="col-sm-8">
                                <img src="core/img/no_image.gif" data-original=".png" id="img_device" style="width:120px;" />
                            </div>
                        </div>
                    </fieldset>
                </form>
                <br />

                <hr>

                <legend><i class="fas fa-cog"></i> {{Paramètres}}</legend>
                <form class="form-horizontal col-sm-10">
                    <fieldset>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">{{Signe du zodiaque}}
                                <sup><i class="fas fa-question-circle" title="{{Choisir son signe}}"></i></sup>
                            </label>
                            <div class="col-sm-3">
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
                        <div class="form-group">
                            <label class="col-sm-2 control-label">{{Auto-actualisation}}
                                <sup><i class="fas fa-question-circle" title="{{Cron }}"></i></sup>
                            </label>
                            <div class="col-sm-3">
                                <div class="input-group">
                                    <input type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="autorefresh" placeholder="{{Auto-actualisation (cron)}}" />
                                    <span class="input-group-btn">
                                        <a class="btn btn-default cursor jeeHelper" data-helper="cron">
                                            <i class="fas fa-question-circle"></i>
                                        </a>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </fieldset>

                </form>
            </div>
            <div role="tabpanel" class="tab-pane" id="commandtab">
                <br />
                <table id="table_cmd" class="table table-bordered table-condensed">
                    <thead>
                        <tr>
                            <th width="50px"> ID</th>
                            <th width="650px">{{Nom}}</th>
                            <th>{{Paramètres}}</th>
                            <th width="120px">{{Options}}</th>
                            <th width="40px"></th>
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
include_file('desktop', 'horoscope', 'js', 'horoscope');
include_file('core', 'plugin.template', 'js');
?>