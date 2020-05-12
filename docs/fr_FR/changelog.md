---
layout: default
title: Plugin horoscope - Changelog
lang: fr_FR
pluginId: horoscope
---

# Info
## Description
Ce Plugin permet de générer une phrase (selon différents thèmes) tous les jours en fonction des différents signes astrologiques.<br/>Très sympa pour donner l'horoscope le matin à une personne via TTS (par exemple avec la caméra Netatmo qui reconnait les visages) ou par SMS.

## Important
>***Pour rappel*** s'il n'y a pas d'information sur la mise à jour, c'est que celle-ci concerne uniquement de la mise à jour de documentation, de traduction ou de texte.

# Version 20200512
- Modification du système de Cron (merci @Mips), il se configure ou pas sur chaque équipement.
- Possibilité de rafraichir le widget depuis un scénario.
- Commande Refresh (sur la tuile, scénario etc).
- Mise à jour des widgets à la norme Core V4. (Pas de Widget disponible pour le Core V3)
- Mise à jour logo plugin avec la nouvelle norme Jeedom (merci @Greg06500)
- Mise à jour des images pour les widgets (merci @Dankoss001)
- Amélioration des logs
- Liste des équipements avec le logo du signe
- Correction type de generic
- Support de PHP 7.3
- Migration vers font-awesome 5
- Migration affichage au format core V4
- La recherche des cmd pour mise à jour ne se fait plus par getConfiguration('data') mais par leur logicalId. Les cmd perdent leur data de configuration.
- Nettoyage des dossiers
- Mise à jour de la documentation au format mardock
- Correction Bug : l'actualisation des données ne se fait plus si l'équipement est désactivé

>*Remarque : Il est conseillé de supprimer le plugin et ensuite le réinstaller*

# Version 1.06
- Mise à jour info.json (pour jeedom 3)

# Version 1.05 - 19/12/2016
- Mise à jour du widget

# Version 1.04 - 18/12/2016
- Mise à jour du widget

# Version 1.03 - 18/12/2016
- Suppression de la classe bootstrap

# Version 1.02 - 18/12/2016
- Actualisation de l'horoscope après la création de l'équipement

# Version 1.0 - 18/12/2016

- Supporte les 12 signes du zodiaque.
- Première publication du plugin

# Version 1.0 - 26/09/2016
- Version initiales du plugin