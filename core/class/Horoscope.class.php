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

class Horoscope extends eqLogic {
    
	public function Signe($Signe1) {
	log::add('Horoscope', 'debug', 'dans Belier()');
	$Signe=$Signe1;
//$Signe=$_GET["Signe"];
$Lien="http://www.asiaflash.com/horoscope/rss_horojour_$Signe.xml";
$Phrase="";
$Total=0;
$Fin=0;
$pos1=0;
$xmlData = file_get_contents($Lien);
str_replace('rss','xml',$xmlData );
$xml = new SimpleXMLElement($xmlData);
$Phrase=$xml->channel->item->description;

$pos1 = stripos($Phrase, "oeil</b><br>");
$pos1=$pos1+12;
$Total=strlen($Phrase);
$Fin=$Total-$pos1;
$Phrase=substr($Phrase,$pos1,$Fin);

$pos1 = stripos($Phrase, "<br><br>");
$Total=strlen($Phrase);

$Phrase=substr($Phrase,1,$pos1-1);

//echo $Phrase;
log::add('Horoscope', 'debug', 'Phrase générée2 : '.$Phrase);

				//mise à jour base de donnée Jeedom
				$cmd = $this->getCmd(null, 'HoroscopeDuJour');
                if (is_object($cmd)) {
                    // $cmd->setCollectDate($date);
                    $cmd->event($Phrase);
                    log::add('Horoscope', 'debug', 'Phrase stockée :' . $Phrase);
                }

//$mi_horoscope->updateJeedom();
//log::add('Horoscope', 'debug', 'Apprès updatejeedom '.$Phrase);

}
	
	
	
	/*     * *************************Attributs****************************** */



    /*     * ***********************Methode static*************************** */

    /*
     * Fonction exécutée automatiquement toutes les minutes par Jeedom
       */
	  public static function cron() {
		
		log::add('Horoscope', 'debug', 'Avant belier');
		 foreach (eqLogic::byType('Horoscope', true) as $mi_horoscope) {   
		log::add('Horoscope', 'debug', 'Après for each');
		   //$mi_flora->Belier();
		   //$Signe1='belier';
		   $Signe1='vierge';
		   $mi_horoscope->Signe($Signe1);
		   log::add('Horoscope', 'debug', 'Après Belier');
		   }
      } 
    /*
     * Fonction exécutée automatiquement toutes les heures par Jeedom
      public static function cronHourly() {
      }
     */
    /*
     * Fonction exécutée automatiquement tous les jours par Jeedom
      public static function cronDayly() {
      }
     */
    /*     * *********************Méthodes d'instance************************* */
	 public function updateJeedom() {
	log::add('Horoscope', 'debug', 'updateJeedom  param='.$Phrase );
		/*
        // store into Jeedom DB
        if ($Phrase=='' ) {
            log::add('MiFlora', 'error', 'Toutes les mesures a 0, erreur de connection Mi Flora');
        } else {
            //if ($temperature > 100) {
                log::add('MiFlora', 'error', 'Temperature >100 erreur de connection bluetooth');
            //} else {
                
                //
                $cmd = $this->getCmd(null, $Phrase);
                if (is_object($cmd)) {
                    $cmd->event($Phrase);
                    log::add('Horoscope', 'debug', $Phrase );
                }
				//
           // }
        }
    
	*/
	return 'ok';
	}
	
	public function Test1() {
	log::add('Horoscope', 'debug', 'dans Test1()');
	$Phrase="";
	Belier();
	}
	
	
	
    public function preInsert() {
        
    }

    public function postInsert() {
        
    }

    public function preSave() {
        
    }

    public function postSave() {
        
    }

    public function preUpdate() {
        
    }

    public function postUpdate()
    {
        
		$cmdlogic = HoroscopeCmd::byEqLogicIdAndLogicalId($this->getId(), 'HoroscopeDuJour');
        if (!is_object($cmdlogic)) {
            $HoroscopeCmd = new HoroscopeCmd();
            $HoroscopeCmd->setName(__('HoroscopeDuJour', __FILE__));
            $HoroscopeCmd->setEqLogic_id($this->id);
            $HoroscopeCmd->setLogicalId('HoroscopeDuJour');
            $HoroscopeCmd->setConfiguration('data', 'HoroscopeDuJour');
            $HoroscopeCmd->setEqType('Horoscope');
            $HoroscopeCmd->setType('info');
            $HoroscopeCmd->setSubType('other');
            //$HoroscopeCmd->setUnite('');
            $HoroscopeCmd->setIsHistorized(0);
            $HoroscopeCmd->save();
			
			}
			
        }

    public function preRemove() {
        
    }

    public function postRemove() {
        
    }

	

	
	
    /*
     * Non obligatoire mais permet de modifier l'affichage du widget si vous en avez besoin
      public function toHtml($_version = 'dashboard') {

      }
     */

    /*     * **********************Getteur Setteur*************************** */
}

class HoroscopeCmd extends cmd {
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
