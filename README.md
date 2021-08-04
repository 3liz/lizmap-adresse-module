# Module Adresse Lizmap

[![Lint JS 🎳](https://github.com/3liz/lizmap-adresse-module/actions/workflows/test-lint-js.yml/badge.svg)](https://github.com/3liz/lizmap-adresse-module/actions/workflows/test-lint-js.yml)
[![Lint PHP 🎳](https://github.com/3liz/lizmap-adresse-module/actions/workflows/test-lint-php.yml/badge.svg)](https://github.com/3liz/lizmap-adresse-module/actions/workflows/test-lint-php.yml)
[![Release 🚀](https://github.com/3liz/lizmap-adresse-module/actions/workflows/release.yml/badge.svg)](https://github.com/3liz/lizmap-adresse-module/actions/workflows/release.yml)
[![Packagist](https://img.shields.io/packagist/v/lizmap/lizmap-adresse-module)](https://packagist.org/packages/lizmap/lizmap-adresse-module)

Module Lizmap de gestion d'une base adresse

![demo](demo.png "demo")

Ce module s'appuie sur [l'extension QGIS Adresse](https://github.com/3liz/qgis-gestion_base_adresse-plugin)
pour la création de la base de données et la configuration d'un projet QGIS.

Le module Adresse permet la gestion des adresses sur les communes. Il ajoute de nouvelles fonctionnalités pour
la gestion de documents et permet une édition de voie et adresse intuitive.

## Documentation

L'installation et l'utilisation du module se trouve sur 
[docs.3liz.org](https://docs.3liz.org/qgis-gestion_base_adresse-plugin/)

Le code source de la documentation est sur le dépôt de
[l'extension QGIS](https://github.com/3liz/qgis-gestion_base_adresse-plugin/).

## Installation du module

Depuis la version 0.9.1 du module, il est souhaitable de l'installer avec [Composer](https://getcomposer.org), 
le système de paquet pour PHP. Si vous ne pouvez pas, ou si vous utilisez 
lizmap 3.3 ou inférieur, passez à la section sur l'installation manuelle. 

### Installation automatique avec Composer et lizmap 3.4 ou plus

* dans `lizmap/my-packages`, créer le fichier `composer.json` s'il n'existe pas 
  déjà, en copiant le fichier `composer.json.dist`, qui s'y trouve.
* en ligne de commande, dans le répertoire `lizmap/my-packages/`, tapez :
  `composer require "lizmap/lizmap-adresse-module"`
* puis dans le répertoire `lizmap/install/`, lancer la commande : `php installer.php`

### Installation manuelle dans lizmap 3.3 ou 3.4 sans Composer

* Récupérer le dossier `adresse` dans https://github.com/3liz/lizmap-adresse-module
* Dans le répertoire où est installé lizmap, le mettre dans le dossier `lizmap/lizmap-module/`
* Dans le fichier `localconfig.ini.php` qui se situe dans `lizmap/var/config` il y a la partie `[modules]` où
  il faut y rajouter le niveau d’accès du module : 
  * `adresse.access = 2`
* lancer la commande : `php install/installer.php`

## Obtenir de l'aide
* Envoyez un e-mail à la liste de diffusion Lizmap à l'adresse https://lists.osgeo.org/pipermail/lizmap/
* Rejoignez-nous sur IRC , #lizmap sur https://libera.chat
* Ouvrez un ticket sur GitHub https://github.com/3liz/lizmap-adresse-module/issues
* Support commercial via 3liz, [Contactez-nous](mailto:info@3liz.com?subject=CommercialSupportRequest)
