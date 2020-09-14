# Module Adresse Lizmap

Module Lizmap de gestion d'une base adresse

![demo](demo.png "demo")

Ce module s'appuie sur le [Plugin Adresse](https://github.com/3liz/qgis-gestion_base_adresse-plugin) pour la création de la base de données et la configuration d'un projet QGIS.

Le module Adresse permet la gestion des adresses sur les communes. Il ajoute de nouvelles fonctionnalités pour la gestion de documents et permet une édition de voie et adresse intuitive.

## Installation du module

* Récupérer le dossier `adresse` dans https://github.com/3liz/lizmap-adresse-module
* Dans le répertoire où est installé lizmap, le mettre dans le dossier `lizmap/lizmap-module/`
* Dans le fichier localconfig.ini.php qui ce situe dans lizmap/var/config il y a la partie `[modules]` où il faut y rajouter le niveau d’accès du module: 
  * `adresse.access = 2`
* lancer la commande: `php install/installer.php`

## Documentation

Documentation disponible [ici](docs/).