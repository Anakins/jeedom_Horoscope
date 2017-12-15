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

    /**
     * Le nom du parametre contenant le signe configuré
     */
    const KEY_SIGNE = 'signe';

    /**
     * Liste des signes disponible
     * Il ne s'agit là que des clés de configuration, le nom affiché des signes
     * est configuré dans les translations
     */
    protected static $_signes = [
        'taureau' => 'Taureau',
        'belier' => 'Bélier',
        'poissons' => 'Poissons',
        'vierge' => 'Vierge',
        'capricorne' => 'Capricorne',
        'scorpion' => 'Scorpion',
        'sagittaire' => 'Sagittaire',
        'verseau' => 'Verseau',
        'cancer' => 'Cancer',
        'balance' => 'Balance',
        'gemeaux' => 'Gémeaux',
        'lion' => 'Lion'
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

    public static $_widgetPossibility = array('custom' => true);


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
        //log::add('horoscope', 'debug', 'Début de la fonction de calcul de l horoscope');
        if (empty($signe)) {
            throw new Exception("Erreur le parametre 'signe' est vide");
        }
		log::add('horoscope', 'debug', 'Mise à jour du signe : '.$signe.' : ');
		log::add('horoscope', 'debug', '-------------------------------------------');
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
                        $phrase = $matches[2];
                        $theme_strip = strtolower(preg_replace('/[^\wéè]/', '_', $theme));
                        $horoscope['themes'][$theme] = $phrase;
                        $horoscope['themes_simple'][$theme_strip] = $phrase;
						log::add('horoscope', 'debug', '--> Affectation : '.$theme.' => '.$phrase);
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
        $today = date('H');
        $frequence = config::byKey('frequence', 'horoscope');
       

        if ($frequence == '1min') {
            
			log::add('horoscope', 'info', '<----------------- MISE A JOUR DE L\'HOROSCOPE ----------------->');
			log::add('horoscope', 'debug', 'Position : Début de la boucle pour chaque équipement');
            foreach (eqLogic::byType('horoscope', true) as $mi_horoscope) {
                //log::add('horoscope', 'debug', 'Après chaque élément');

                //Procédure de calcul de l horoscope
				
                $mi_horoscope->updateHoroscope();
				log::add('horoscope', 'debug', 'MISE A JOUR DU WIDGET');
				log::add('horoscope', 'debug', '.');
                $mi_horoscope->refreshWidget();
				
            }
			log::add('horoscope', 'debug', 'Position : Fin de boucle pour chaque équipement');
        }
    }

     // Fonction exécutée automatiquement toutes les heures par Jeedom
    public static function cronHourly() {
        $today = date('H');
        $frequence = config::byKey('frequence', 'horoscope');
        log::add('horoscope', 'debug', '--------------------------DEBUT HOROSCOPE CRON HEURE-------------------------------------------');
        log::add('horoscope', 'debug', 'Fréquence : "'.$frequence.'" , heure Actuelle : '.$today);

        if (($frequence == '1h') ||  (($today == '00') && ($frequence == 'minuit')) ||  (($today == '05') && ($frequence == '5h'))  ) {
            log::add('horoscope', 'debug', 'Avant Lecture de chaque équipement');
			log::add('horoscope', 'info', '<----------------- MISE A JOUR DE L\'HOROSCOPE ----------------->');
			log::add('horoscope', 'debug', 'Position : Avant Lecture de chaque équipement');
            foreach (eqLogic::byType('horoscope', true) as $mi_horoscope) {
                log::add('horoscope', 'debug', 'Après chaque élément');

                //Procédure de calcul de l horoscope
			    $mi_horoscope->updateHoroscope();
				log::add('horoscope', 'debug', 'Mise à jour du widget');
				log::add('horoscope', 'debug', '.');
                $mi_horoscope->refreshWidget();
            }
        }
    }

    /*     * *********************Méthodes d'instance************************* */

    /**
     * Recuperer l'horoscope du jour et met à jour les commandes
     */
    public function updateHoroscope() {
        $signe = $this->getConfiguration(self::KEY_SIGNE);
        if (empty($signe)) {
            return;
        }

        $horoscope = self::getHoroscopeForSigne($signe);
				log::add('horoscope', 'debug', '.');
				log::add('horoscope', 'debug', 'Modification de : '.$this->getName().'.');
				
		
        // met a jour toutes les commandes contenants les phrases de l'horoscope
        foreach ($horoscope['themes'] as $theme_name => $message) {
            if (! is_string($message)) {
                continue;
            }
            // cree la commande si elle n'existe pas encore
            $horoscopeCmd = $this->getCmd(null, $theme_name);
    		if (!is_object($horoscopeCmd)) {
    			$horoscopeCmd = new horoscopeCmd();
                $horoscopeCmd->setName(__($theme_name, __FILE__));
                $horoscopeCmd->setEqLogic_id($this->getId());
                $horoscopeCmd->setLogicalId($theme_name);
				log::add('horoscope', 'debug', 'Création de : '.$this->getName().'->'.$theme_name);
                $horoscopeCmd->setIsVisible(false);
                $horoscopeCmd->setEqType('horoscope');
                $horoscopeCmd->setType('info');
                $horoscopeCmd->setSubType('string');
                $horoscopeCmd->setIsHistorized(0);
                $horoscopeCmd->save();
    		}
            $horoscopeCmd->event($message);
        }

        // met a jour les commandes specifique declarée dans le tableau de mapping
        foreach ($horoscope['themes_simple'] as $theme_name => $message) {
            // si un mapping specifique est defini alors on l'applique
            if (isset(self::$_theme_mapping[$theme_name])) {
                $specific_commande_name = self::$_theme_mapping[$theme_name];

                $horoscopeCmd = $this->getCmd(null, $specific_commande_name);
        		if (!is_object($horoscopeCmd)) {
        			$horoscopeCmd = new horoscopeCmd();
                    $horoscopeCmd->setName(__($theme_name, __FILE__));
                    $horoscopeCmd->setEqLogic_id($this->getId());
                    $horoscopeCmd->setLogicalId($theme_name);
					log::add('horoscope', 'debug', 'Création de : '.$this->getName().'->'.$theme_name);
                    $horoscopeCmd->setEqType('horoscope');
                    $horoscopeCmd->setType('info');
                    $horoscopeCmd->setSubType('string');
                    $horoscopeCmd->setIsHistorized(0);
                    $horoscopeCmd->save();
        		}
                $horoscopeCmd->event($message);
            }
        }
		log::add('horoscope', 'debug', '.');
    }

    /**
     * Met a jour la commande contenant le signe configure
     */
    public function updateSigne() {
        $signe = $this->getConfiguration(self::KEY_SIGNE);

        // met a jour la commande contenant le signe
        $horoscopeCmd = $this->getCmd(null, 'signe');
        if (!is_object($horoscopeCmd)) {
            log::add('horoscope', 'debug', 'L équipement (Signe) n a pas a été trouvé dans SAVE donc création :');
            $horoscopeCmd = new horoscopeCmd();
            $horoscopeCmd->setName(__('signe', __FILE__));
            $horoscopeCmd->setEqLogic_id($this->getId());
            $horoscopeCmd->setLogicalId('signe');
            $horoscopeCmd->setIsVisible(false);
            $horoscopeCmd->setEqType('horoscope');
            $horoscopeCmd->setType('info');
            $horoscopeCmd->setSubType('string');
            $horoscopeCmd->setIsHistorized(0);
            $horoscopeCmd->save();
        }
        $horoscopeCmd->event($signe);
    }

    public function preInsert() {

    }

    public function postInsert() {

    }

    public function preSave() {

    }

    public function postSave() {
        $this->updateSigne();
    }

    public function preUpdate() {
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

    public function postUpdate()
    {
        $this->updateHoroscope();
    }


    public function preRemove() {

    }

    public function postRemove() {

    }

    public function toHtml($_version = 'dashboard') {
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
    }

    /*     * **********************Getteur Setteur*************************** */
}

class horoscopeCmd extends cmd {
    /*     * *************************Attributs****************************** */


    /*     * ***********************Methode static*************************** */


    /*     * *********************Methode d'instance************************* */

    /*
     * Non obligatoire permet de demander de ne pas supprimer les commandes même si elles ne sont pas dans la nouvelle configuration de l'équipement envoyé en JS
      public function dontRemoveCmd() {
      return true;
      }
     */

    public function execute($_options = array()) {

    }

    /*     * **********************Getteur Setteur*************************** */
}

?>
