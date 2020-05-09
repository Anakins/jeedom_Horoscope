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

class horoscope extends eqLogic {

    /* Le nom du parametre contenant le signe configuré */
    const KEY_SIGNE = 'signe';

    /*
     * Liste des signes disponible
     * Il ne s'agit là que des clés de configuration, le nom affiché des signes
     * est configuré dans les translation */

    protected static $_signes = [
        'balance' => 'Balance',
        'belier' => 'Bélier',
        'cancer' => 'Cancer',
        'capricorne' => 'Capricorne',
        'gemeaux' => 'Gémeaux',
        'lion' => 'Lion',
        'poissons' => 'Poissons',
        'sagittaire' => 'Sagittaire',
        'scorpion' => 'Scorpion',
        'taureau' => 'Taureau',
        'vierge' => 'Vierge',
        'verseau' => 'Verseau'
    ];

    /**
     * Mapping des themes en commandes
     * Permet de lier le nom d'un theme à une commande Jeedom avec un nom
     * specifique
     */
    protected static $_theme_mapping = [
        //clin_d_oeil' => 'horoscopeDuJour'
    ];

    /**
     * Le gabarit de l'URL de récupération de l'horoscope
     * La chaine '%s' sera remplacée par la clé du signe de l'equipement
     */
    public static $_url_template = 'http://www.asiaflash.com/horoscope/rss_horojour_%s.xml';

   // public static $_widgetPossibility = array('custom' => true);


    /*     * *************************Attributs****************************** */

    /*     * ***********************Methode static*************************** */

    /**
     * Recupere la liste des signes disponibles
     */
    public static function getSignes() {
        return self::$_signes;
    }


    /**
     * Recupere l'horoscope du signe donnée depuis l'URL et retourne les valeurs de l'horoscope
     *
     * @param
     */

    public static function getHoroscopeForSigne($signe) {

        if (empty($signe)) {
            throw new Exception("Erreur le parametre 'signe' est vide");
        }
        log::add('horoscope', 'debug', '│ Mise à jour pour le signe : ' . $signe);

        $url = sprintf(self::$_url_template, $signe);
        $xmlData = file_get_contents($url);
        $xml = new SimpleXMLElement($xmlData);

        # contient tous le champ description
        $description = $xml->channel->item->description;

        # extrait les paragraphes de la description
        $paragraphes = preg_split('/<br><br>/', $description);

        # la liste horoscope contient une cle par theme de l'horoscope
        # chaque nom de theme est repris tel quel depuis le XML
        # en supplement chaque nom de theme est duppliquer en remplacant tous les caracteres non
        # alphabetique par des underscores
        $horoscope = ['themes' => [], 'themes_simple' => []];

        # filtre les paragraphes pour ne retourner que ceux contenant une phrase d'horoscope
        foreach($paragraphes as $key => $paragraphe) {
            # elimine les paragraphes qui ne commence par la chaine suivante :
            if (substr($paragraphe, 0, strlen('<b>Horoscope')) !== '<b>Horoscope') {
                unset($paragraphes[$key]);
            } else {
                $paragraphe = strip_tags($paragraphe);
                $matches = [];
                if (preg_match('/^Horoscope\s*[^ ]+\s*-\s*(.*)\n(.*)/', $paragraphe, $matches) > 0) {
                    if (count($matches) == 3) {
                        $theme = $matches[1];
						$theme = str_replace(' ','', $theme);
                        $theme = str_replace('\'','', $theme);
                        $phrase = $matches[2];
                        $theme_strip = strtolower(preg_replace('/[^\wéè]/', '_', $theme));
                        $horoscope['themes'][$theme] = $phrase;
                        $horoscope['themes_simple'][$theme_strip] = $phrase;
						log::add('horoscope', 'debug', '│ Mise à jour commande : '.$theme.' : '.$phrase);
                    }
                }
            }
        }
        return $horoscope;
    }

    /**
     * Fonction exécutée automatiquement toutes les minutes par Jeedom
     */


    public static function cron() {
        foreach (eqLogic::byType('horoscope', true) as $eqLogic) {
            $autorefresh = $eqLogic->getConfiguration('autorefresh', '');

            if ($autorefresh == '')  continue;
            try {
                $cron = new Cron\CronExpression($autorefresh, new Cron\FieldFactory);
			if ($cron->isDue()) {
				$eqLogic->refresh();
			}
		} catch (Exception $e) {
			log::add('horoscope', 'error', __('Expression cron non valide pour ', __FILE__) . $eqLogic->getHumanName() . ' : ' . $autorefresh);
		}
	}
}



    /*     * *********************Méthodes d'instance************************* */
    public function preSave() {
		if ($this->getConfiguration('autorefresh') == '') {
			$this->setConfiguration('autorefresh', '0 5 * * *');
		}
	}


    public function refresh() {
        $this->getinformations();
    }

    public function preUpdate() {
        if (!$this->getIsEnable()) return;

        $signe = $this->getConfiguration(self::KEY_SIGNE);
        if ($signe == '') {
            log::add('horoscope', 'debug', 'preUpdate: signe vide');
            throw new Exception(__("Vous n'avez configuré aucun signe.", __FILE__));
        }


        if (!array_key_exists($signe, self::getSignes())) {
            log::add('horoscope', 'debug', 'preUpdate: signe inexistant renseigne');
            throw new Exception(__("Le signe renseigné n'existe pas.", __FILE__) . " '$signe'");
        }
    }

    public function postInsert() {

    }

    public function postSave() {
        $_eqName = $this->getName();
        log::add('horoscope', 'debug', '=> Save : '.$_eqName );

        $order = 1;

        /*  ********************** Lancement création Signe *************************** */
        $this->updateSigne();

        //Fonction rafraichir
        $refresh = $this->getCmd(null, 'refresh');
        if (!is_object($refresh)) {
            $refresh = new horoscopeCmd();
            $refresh->setLogicalId('refresh');
            $refresh->setIsVisible(1);
            $refresh->setName(__('Rafraichir', __FILE__));
            $refresh->setOrder($order);
        }
        $refresh->setEqLogic_id($this->getId());
        $refresh->setType('action');
        $refresh->setSubType('other');
        $refresh->save();

    }


    /**
     * Recuperer l'horoscope du jour et met à jour les commandes
     */
    public function getupdateHoroscope() {


        $signe = $this->getConfiguration(self::KEY_SIGNE);
        log::add('horoscope', 'debug', '│ Signe : '.$signe);

        if (empty($signe)) {
            return;
        }

        $horoscope = self::getHoroscopeForSigne($signe);


        // met a jour toutes les commandes contenants les phrases de l'horoscope
        foreach ($horoscope['themes'] as $theme_name => $message) {
            if (! is_string($message)) {
                continue;
                log::add('horoscope', 'debug', '│ Modification de l\'équipement : '.$this->getName() .$message);
            }
            // création de la commande si elle n'existe pas encore
            $horoscopeCmd = $this->getCmd(null, $theme_name);
    		if (!is_object($horoscopeCmd)) {
    			$horoscopeCmd = new horoscopeCmd();
                $horoscopeCmd->setName(__($theme_name, __FILE__));
                $horoscopeCmd->setEqLogic_id($this->id);
                $horoscopeCmd->setLogicalId($theme_name);
                $horoscopeCmd->setConfiguration('data', $theme_name);
                $horoscopeCmd->setType('info');
                $horoscopeCmd->setSubType('string');
                $horoscopeCmd->setIsVisible(0);
                $horoscopeCmd->setIsHistorized(0);
                $horoscopeCmd->setDisplay('generic_type','GENERIC_INFO');
                $horoscopeCmd->setTemplate('dashboard','core::multiline');
                $horoscopeCmd->setTemplate('mobile','core::multiline');
                $horoscopeCmd->save();

                log::add('horoscope', 'debug', '│ Création de la commande : '.$this->getName().'->'.$theme_name);
    		}
            $horoscopeCmd->event($message);
            $horoscopeCmd->setEqLogic_id($this->id);
            $horoscopeCmd->setDisplay('generic_type','GENERIC_INFO');
            $horoscopeCmd->save();
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
                    $horoscopeCmd->setLogicalId($theme_name);
                    $horoscopeCmd->setConfiguration('data', $theme_name);
                    $horoscopeCmd->setType('info');
                    $horoscopeCmd->setSubType('string');
                    $horoscopeCmd->setIsHistorized(0);
                    $horoscopeCmd->setIsVisible(0);
					$horoscopeCmd->setDisplay('generic_type','GENERIC_INFO');
                    $horoscopeCmd->setTemplate('dashboard','core::multiline');
                    $horoscopeCmd->setTemplate('mobile','core::multiline');
                    $horoscopeCmd->save();

                    log::add('horoscope', 'debug', '│ Création de la commande : '.$this->getName().'->'.$theme_name);
        		}
                $horoscopeCmd->event($message);
                $horoscopeCmd->setEqLogic_id($this->id);
                $horoscopeCmd->setDisplay('generic_type','GENERIC_INFO');
                $horoscopeCmd->save();
            }
        }

    }

    public function updateSigne() {
        //Met a jour la commande contenant le signe configure
        $signe = $this->getConfiguration(self::KEY_SIGNE);

        // met a jour la commande contenant le signe
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
            $horoscopeCmd->setDisplay('generic_type','GENERIC_INFO');
            $horoscopeCmd->setTemplate('dashboard','core::multiline');
            $horoscopeCmd->setTemplate('mobile','core::multiline');
            $horoscopeCmd->setOrder($order);
            $order ++;
            $horoscopeCmd->save();

            log::add('horoscope', 'debug', '│ Création de la commande Signe');
        }
        $horoscopeCmd->event($signe);
        $horoscopeCmd->setEqLogic_id($this->id);
        $horoscopeCmd->setDisplay('generic_type','GENERIC_INFO');
        $horoscopeCmd->save();

        return $order;
    }



    /*     * **********************Getteur Setteur*************************** */
    public function postUpdate() {
        $this->getInformations();
    }

    public function getInformations() {
        if (!$this->getIsEnable()) return;

        if ($this->getConfiguration('autorefresh') == '') {
			throw new Exception(__('Le champs ne peut être vide', __FILE__));
		}

        $_eqName = $this->getName();
        log::add('horoscope', 'debug', '┌───────── MISE A JOUR : '.$_eqName );

        /*  ********************** Lancement création Signe *************************** */
        //$this->updateSigne();


        /*     * ********************** Update Horoscope*************************** */
       $this->getupdateHoroscope();

        log::add('horoscope', 'debug', '└─────────');
    }



   /*  public function toHtml($_version = 'dashboard') {
        $replace = $this->preToHtml($_version);
        if (!is_array($replace)) {
            return $replace;
        }
        $version = jeedom::versionAlias($_version);
        if ($this->getDisplay('hideOn' . $version) == 1) {
            return '';
        }
        foreach ($this->getCmd('info') as $cmd) {
            $replace['#' . $cmd->getLogicalId() . '_id#'] = $cmd->getId();
            $replace['#' . $cmd->getLogicalId() . '#'] = $cmd->execCmd();
            $replace['#' . $cmd->getLogicalId() . '_collect#'] = $cmd->getCollectDate();
            if ($cmd->getIsHistorized() == 1) {
                $replace['#' . $cmd->getLogicalId() . '_history#'] = 'history cursor';
            }
        }

        log::add('horoscope','debug', $this->postToHtml($_version, template_replace($replace, getTemplate('core', $version, 'horoscope', 'horoscope'))));

        return $this->postToHtml($_version, template_replace($replace, getTemplate('core', $version, 'horoscope', 'horoscope')));
    }*/


    /*     * **********************Getteur Setteur*************************** */
}

class horoscopeCmd extends cmd {
    /*     * *************************Attributs****************************** */


    /*     * ***********************Methode static*************************** */


    /*     * *********************Methode d'instance************************* */
    public function dontRemoveCmd() {
        return true;
    }

    public function execute($_options = array()) {
        if ($this->getLogicalId() == 'refresh') {
            log::add('horoscope', 'debug', ' ─────────> ACTUALISATION MANUELLE');
            $this->getEqLogic()->getInformations();
            log::add('horoscope', 'debug', ' ─────────> FIN ACTUALISATION MANUELLE');
            return;
		}
    }

}

?>
