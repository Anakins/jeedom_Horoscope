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

require_once dirname(__FILE__) . '/../../../core/php/core.inc.php';
include_file('core', 'authentification', 'php');
if (!isConnect()) {
    include_file('desktop', '404', 'php');
    die();
}
?>
<form class="form-horizontal">
    <fieldset>
	
	<span>
        <div class="form-group"> <br>
           <a href="https://github.com/Anakins/jeedom_Horoscope" target="_blank"><font size="+1"><center>Cliquer pour voir la documentation du plugin</center></font></a>
       </div>
	</span>
        <div class="form-group"> <br>

          <label class="col-lg-4 control-label">{{Fréquence de recuperation des données}}</label>
          <div class="col-lg-2">
              <select id="frequence" class="configKey form-control"  data-l1key="frequence" >
              
              
              <option value="5h">{{à 5H00 du matin}}</option>
			  <option value="1h">{{Toutes les 1 heure}}</option>
			  <option value="à minuit">{{à minuit}}</option>
			  <option value="1min">{{Toutes les 1 minute (pour les tests)}}</option>
               </select>
          </div>
        </div>
	
	<?php
	/*
        <div class="form-group">
            <label class="col-lg-4 control-label">{{Global param 1}}</label>
            <div class="col-lg-2">
                <input class="configKey form-control" data-l1key="param1" />
            </div>
        </div>
        <div class="form-group">
            <label class="col-lg-4 control-label">{{Global param 2}}</label>
            <div class="col-lg-2">
                <input class="configKey form-control" data-l1key="param2" value="80" />
            </div>
        </div>
        <div class="form-group">
            <label class="col-lg-4 control-label">{{Global param 2}}</label>
            <div class="col-lg-2">
                <select class="configKey form-control" data-l1key="param3">
                    <option value="value1">value1</option>
                    <option value="value2">value2</option>
                </select>
            </div>
        </div>
		*/
		?>
  </fieldset>
</form>

