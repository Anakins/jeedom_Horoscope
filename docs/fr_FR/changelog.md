---
layout: default
lang: fr_FR
title: Jeedom | Plugin Horoscope Changelog
---

# Info
## Description
Ce Plugin permettant de générer une phrase (selon différents thèmes) tous les jours en fonction des différents signes astrologiques.<br/>Très sympa pour donner l'horoscope le matin à une personne via TTS (par exemple avec la caméra Netatmo qui reconnait les visages) ou par SMS.


## Info sur les mises à jour
>*Important : en cas de mise à jour disponible pour laquelle il n’y a pas d’information dans cette section, c’est qu’elle n’intègre aucune nouveauté majeure. Cela peut être un ajout de documentation, une correction de documentation, des traductions ou bien de la correction de bugs mineurs.*

# Version 20200508
- Modification du systéme de Cron  (merci @GMips)
- Commande Refresh (sur la tuile, scénario etc)
- Mise à jour des logo (merci @Greg06500)
- Amélioration des logs
- Correction type de generic
- Support de PHP 7.3
- Migration vers font-awesome 5
- Migration affichage au format core V4
- La recherche des cmd pour mise à jour ne se fait plus par getConfiguration('data') mais par leur logicalId. Les cmd perdent leur data de configuration.
- Nettoyage des dossiers
- Mise à jour de la documentation
- Correction Bug : l'actualisation des données ne se fait plus si l'équipement est désactivé

>*Remarque : Il est conseillé de supprimer le plugin et ensuite le réinstaller*

# Version 1.06
- Mise à jour info.json (pour jeedom 3)

# Version 1.05 - 19/12/2016
- Seuil d’alerte du point de rosée configurable dans Informations. Valeur par défaut 2°C

# Version 1.04 - 18/12/2016
- Mise à jour du widget


# Version 1.03 - 18/12/2016
- Suppression de la classe bootstrap

# Version 1.02 - 18/12/2016
- Actualisation de l'horoscope apres la creation de l'equipement

# Version 1.0 - 18/12/2016

- Supporte les 12 signes du zodiaque.
- Premiere publication du plugin

# Version 1.0 - 26/09/2016

- Version intiales du plugin