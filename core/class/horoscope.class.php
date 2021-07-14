<?php

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

/* * ***************************Includes********************************* */
require_once dirname(__FILE__) . '/../../../../core/php/core.inc.php';

class horoscope extends eqLogic
{

    protected static $_theme_mapping = [
        //clin_d_oeil' => 'horoscopeDuJour'
    ];

    /* Le gabarit de l'URL de récupération de l'horoscope - La chaine '%s' sera remplacée par la clé du signe de l'equipement */

    public static $_url_template = 'http://www.asiaflash.com/horoscope/rss_horojour_%s.xml';

    /*     * *************************Attributs****************************** */

    /*     * ***********************Methode static*************************** */
    public static function deadCmd()
    {
        return array();
    }
    public static $_widgetPossibility = array('custom' => true);

    public static function getHoroscopeForSigne($signe_zodiaque)
    {

        log::add('horoscope', 'debug', '│ Mise à jour pour le signe : ' . $signe_zodiaque);

        $url = sprintf(self::$_url_template, $signe_zodiaque);
        $xmlData = file_get_contents($url);
        $xml = new SimpleXMLElement($xmlData);

        // contient tous le champ description
        $description = $xml->channel->item->description;

        // extrait les paragraphes de la description
        $paragraphes = preg_split('/<br><br>/', $description);

        // la liste horoscope contient une cle par theme de l'horoscope - chaque nom de theme est repris tel quel depuis le XML - en supplement chaque nom de theme est duppliquer en remplacant tous les caracteres non alphabetique par des underscores
        $horoscope = ['themes' => [], 'themes_simple' => []];

        // filtre les paragraphes pour ne retourner que ceux contenant une phrase d'horoscope
        foreach ($paragraphes as $key => $paragraphe) {
            // elimine les paragraphes qui ne commence par la chaine suivante :
            if (substr($paragraphe, 0, strlen('<b>Horoscope')) !== '<b>Horoscope') {
                unset($paragraphes[$key]);
            } else {
                $paragraphe = strip_tags($paragraphe);
                $matches = [];
                if (preg_match('/^Horoscope\s*[^ ]+\s*-\s*(.*)\n(.*)/', $paragraphe, $matches) > 0) {
                    if (count($matches) == 3) {
                        $theme = $matches[1];
                        $theme = str_replace(' ', '', $theme);
                        $theme = str_replace('\'', '', $theme);
                        $phrase = $matches[2];
                        $theme_strip = strtolower(preg_replace('/[^\wéè]/', '_', $theme));
                        $horoscope['themes'][$theme] = $phrase;
                        $horoscope['themes_simple'][$theme_strip] = $phrase;
                    }
                }
            }
        }
        return $horoscope;
    }

    public static function cron()
    {
        foreach (eqLogic::byType('horoscope', true) as $eqLogic) {
            $autorefresh = $eqLogic->getConfiguration('autorefresh', '');

            if ($autorefresh == '')  continue;
            try {
                $cron = new Cron\CronExpression($autorefresh, new Cron\FieldFactory);
                if ($cron->isDue()) {
                    $eqLogic->getinformations();
                }
            } catch (Exception $e) {
                log::add('horoscope', 'error', __('Expression cron non valide pour ', __FILE__) . $eqLogic->getHumanName() . ' : ' . $autorefresh);
            }
        }
    }

    function templateWidget()
    {
        $return = array('info' => array('string' => array()));
        $return['info']['string']['Signe zodiaque'] = array(
            'template' => 'tmplmultistate',
            'replace' => array('#_desktop_width_#' => '60'),
            'test' => array(
                array('operation' => "#value# == 'balance'", 'state_light' => '<img src=plugins/horoscope/core/template/img/balance_light.png>', 'state_dark' => '<img src=plugins/horoscope/core/template/img/balance_dark.png>'),
                array('operation' => "#value# == 'belier'", 'state_light' => '<img src=plugins/horoscope/core/template/img/belier_light.png>', 'state_dark' => '<img src=plugins/horoscope/core/template/img/belier_light.png>'),
                array('operation' => "#value# == 'cancer'", 'state_light' => '<img src=plugins/horoscope/core/template/img/cancer_light.png>', 'state_dark' => '<img src=plugins/horoscope/core/template/img/cancer_dark.png>'),
                array('operation' => "#value# == 'capricorne'", 'state_light' => '<img src=plugins/horoscope/core/template/img/capricorne_light.png>', 'state_dark' => '<img src=plugins/horoscope/core/template/img/capricorne_dark.png>'),
                array('operation' => "#value# == 'gemeaux'", 'state_light' => '<img src=plugins/horoscope/core/template/img/gemeaux_light.png>', 'state_dark' => '<img src=plugins/horoscope/core/template/img/gemeaux_dark.png>'),
                array('operation' => "#value# == 'lion'", 'state_light' => '<img src=plugins/horoscope/core/template/img/lion_light.png>', 'state_dark' => '<img src=plugins/horoscope/core/template/img/lion_dark.png>'),
                array('operation' => "#value# == 'poissons'", 'state_light' => '<img src=plugins/horoscope/core/template/img/poissons_light.png>', 'state_dark' => '<img src=plugins/horoscope/core/template/img/poissons_dark.png>'),
                array('operation' => "#value# == 'sagittaire'", 'state_light' => '<img src=plugins/horoscope/core/template/img/sagittaire_light.png>', 'state_dark' => '<img src=plugins/horoscope/core/template/img/sagittaire_dark.png>'),
                array('operation' => "#value# == 'scorpion'", 'state_light' => '<img src=plugins/horoscope/core/template/img/scorpion_light.png>', 'state_dark' => '<img src=plugins/horoscope/core/template/img/scorpion_dark.png>'),
                array('operation' => "#value# == 'taureau'", 'state_light' => '<img src=plugins/horoscope/core/template/img/taureau_light.png>', 'state_dark' => '<img src=plugins/horoscope/core/template/img/taureau_dark.png>'),
                array('operation' => "#value# == 'vierge'", 'state_light' => '<img src=plugins/horoscope/core/template/img/vierge_light.png>', 'state_dark' => '<img src=plugins/horoscope/core/template/img/vierge_dark.png>'),
                array('operation' => "#value# == 'verseau'", 'state_light' => '<img src=plugins/horoscope/core/template/img/verseau_light.png>', 'state_dark' => '<img src=plugins/horoscope/core/template/img/verseau_dark.png>')
            )
        );
        return $return;
    }

    /*     * *********************Méthodes d'instance************************* */

    public function preInsert()
    {
        if ($this->getConfiguration('autorefresh') == '') {
            $this->setConfiguration('autorefresh', '0 5 * * *');
        }
    }

    public function postInsert()
    {
    }

    public function preSave()
    {
    }

    public function postSave()
    {
        $_eqName = $this->getName();
        log::add('horoscope', 'debug', 'postSave() =>' . $_eqName);
        $order = 1;

        $signe_zodiaque = $this->getConfiguration('signe');

        $this->getupdateSigne($signe_zodiaque, $order);

        //Fonction rafraichir
        $createRefreshCmd = true;
        $refresh = $this->getCmd(null, 'refresh');
        if (!is_object($refresh)) {
            $refresh = cmd::byEqLogicIdCmdName($this->getId(), __('Rafraichir', __FILE__));
            if (is_object($refresh)) {
                $createRefreshCmd = false;
            }
        }
        if ($createRefreshCmd) {
            if (!is_object($refresh)) {
                $refresh = new horoscopeCmd();
                $refresh->setLogicalId('refresh');
                $refresh->setIsVisible(1);
                $refresh->setName(__('Rafraichir', __FILE__));
            }
            $refresh->setType('action');
            $refresh->setSubType('other');
            $refresh->setEqLogic_id($this->getId());
            $refresh->save();
        }
    }

    public function preUpdate()
    {
        if (!$this->getIsEnable()) return;

        /*  ********************** Récupération signe *************************** */
        $signe_zodiaque = $this->getConfiguration('signe');

        if ($signe_zodiaque == '') {
            throw new Exception(__('Le champ "Signe du zodiaque" ne peut être vide', __FILE__));
            log::add('horoscope', 'error', '│ Configuration : Signe zodiaque inexistant : ' . $this->getConfiguration('signe'));
        }
    }

    public function postUpdate()
    {
        $this->getInformations();
    }

    public function preRemove()
    {
    }

    public function postRemove()
    {
    }

    public function getImage()
    {
        if ($this->getConfiguration('signe') != '') {
            $filename = 'plugins/horoscope/core/config/img/' . $this->getConfiguration('signe') . '.png';
            if (file_exists(__DIR__ . '/../../../../' . $filename)) {
                return $filename;
            }
        }
        return 'plugins/horoscope/plugin_info/horoscope_icon.png';
    }

    /* Recuperer l'horoscope du jour et met à jour les commandes */
    public function getupdateHoroscope($signe_zodiaque, $order)
    {

        $horoscope = self::getHoroscopeForSigne($signe_zodiaque);

        // met a jour toutes les commandes contenants les phrases de l'horoscope
        foreach ($horoscope['themes'] as $theme_name => $message) {
            if (!is_string($message)) {
                continue;
            }
            log::add('horoscope', 'debug', "│ Modification de la commande : {$theme_name} : {$message}");

            $horoscopeCmd = $this->getCmd(null, $theme_name);
            if (!is_object($horoscopeCmd)) {
                $horoscopeCmd = new horoscopeCmd();
                $horoscopeCmd->setName(__($theme_name, __FILE__));
                $horoscopeCmd->setEqLogic_id($this->id);
                $horoscopeCmd->setLogicalId($theme_name);
                $horoscopeCmd->setConfiguration('data', $theme_name);
                $horoscopeCmd->setType('info');
                $horoscopeCmd->setSubType('string');
                $horoscopeCmd->setIsHistorized(0);
                $horoscopeCmd->setIsVisible(0);
                $horoscopeCmd->setDisplay('generic_type', 'GENERIC_INFO');
                $horoscopeCmd->setOrder($order);
                $order++;
                $horoscopeCmd->save();

                log::add('horoscope', 'debug', '│ Création de la commande : ' . $theme_name);
            }
            $this->checkAndUpdateCmd($theme_name, $message);
        }
        // Mise à jour les commandes specifique declarée dans le tableau de mapping
        foreach ($horoscope['themes_simple'] as $theme_name => $message) {
            // si un mapping specifique est defini alors on l'applique
            if (isset(self::$_theme_mapping[$theme_name])) {
                $specific_commande_name = self::$_theme_mapping[$theme_name];

                $horoscopeCmd = $this->getCmd(null, $specific_commande_name);
                if (!is_object($horoscopeCmd)) {
                    $horoscopeCmd = new horoscopeCmd();
                    $horoscopeCmd->setName(__($theme_name, __FILE__));
                    $horoscopeCmd->setEqLogic_id($this->id);
                    $horoscopeCmd->setLogicalId($specific_commande_name);
                    $horoscopeCmd->setConfiguration('data', $specific_commande_name);
                    $horoscopeCmd->setType('info');
                    $horoscopeCmd->setSubType('string');
                    $horoscopeCmd->setIsHistorized(0);
                    $horoscopeCmd->setIsVisible(0);
                    $horoscopeCmd->setDisplay('generic_type', 'GENERIC_INFO');
                    $horoscopeCmd->setOrder($order);
                    $order++;
                    $horoscopeCmd->save();

                    log::add('horoscope', 'debug', '│ Création de la commande : ' . $theme_name);
                }
                $this->checkAndUpdateCmd($specific_commande_name, $message);
            }
        }
        return $order;
    }

    public function getupdateSigne($signe_zodiaque, $order)
    {
        $_eqName = $this->getName();

        $horoscopeCmd = $this->getCmd(null, 'signe');
        if (!is_object($horoscopeCmd)) {
            $horoscopeCmd = new horoscopeCmd();
            $horoscopeCmd->setName(__('signe', __FILE__));
            $horoscopeCmd->setEqLogic_id($this->id);
            $horoscopeCmd->setLogicalId('signe');
            $horoscopeCmd->setConfiguration('data', 'signe');
            $horoscopeCmd->setType('info');
            $horoscopeCmd->setSubType('string');
            $horoscopeCmd->setIsHistorized(0);
            $horoscopeCmd->setIsVisible(1);
            $horoscopeCmd->setTemplate('dashboard', 'horoscope::Signe zodiaque');
            $horoscopeCmd->setTemplate('mobile', 'horoscope::Signe zodiaque');
            $horoscopeCmd->setDisplay('generic_type', 'GENERIC_INFO');
            $horoscopeCmd->setOrder($order);
            $order++;
            $horoscopeCmd->save();

            log::add('horoscope', 'debug', '┌───────── Création commande : ' . $_eqName);
            log::add('horoscope', 'debug', '│ Nouvelle commande : Signe');
            log::add('horoscope', 'debug', '└─────────');
        }

        $cmd = $this->getCmd('info', 'signe'); //Mise à jour de la valeur
        if (is_object($cmd)) {
            $cmd->setConfiguration('value', $signe_zodiaque);
            $cmd->save();
            $cmd->event($signe_zodiaque);
        }
        $this->checkAndUpdateCmd('signe', $signe_zodiaque);
        return $order;
    }

    /*     * **********************Getteur Setteur*************************** */


    public function getInformations()
    {
        if (!$this->getIsEnable()) return;

        $_eqName = $this->getName();
        log::add('horoscope', 'debug', '┌───────── MISE A JOUR : ' . $_eqName);
        $order = 1;

        /*  ********************** Récupération signe *************************** */
        $signe_zodiaque = $this->getConfiguration('signe');
        if ($signe_zodiaque == '') {
            throw new Exception(__('Le champ "Signe du zodiaque" ne peut être vide', __FILE__));
            log::add('horoscope', 'error', '│ Configuration : Signe zodiaque inexistant : ' . $this->getConfiguration('signe_zodiaque'));
        }
        log::add('horoscope', 'debug', '│ Signe du zodiaque : ' . $signe_zodiaque);

        /* Création/Update Signe */
        $this->getupdateSigne($signe_zodiaque, $order);
        $order = 2;

        /* Création/Update Horoscope */
        $this->getupdateHoroscope($signe_zodiaque, $order);

        log::add('horoscope', 'debug', '└─────────');
    }
    /*     * **********************Getteur Setteur*************************** */
}

class horoscopeCmd extends cmd
{
    /*     * *************************Attributs****************************** */

    /*     * ***********************Methode static*************************** */

    /*     * *********************Methode d'instance************************* */
    public function dontRemoveCmd()
    {
        return true;
    }
    public function execute($_options = array())
    {
        if ($this->getLogicalId() == 'refresh') {
            log::add('horoscope', 'debug', ' ─────────> ACTUALISATION MANUELLE');
            $this->getEqLogic()->getInformations();
            log::add('horoscope', 'debug', ' ─────────> FIN ACTUALISATION MANUELLE');
            return;
        }
    }
}
