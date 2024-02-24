# Cetronic e-comm

[![Laravel Forge Site Deployment Status](https://img.shields.io/endpoint?url=https%3A%2F%2Fforge.laravel.com%2Fsite-badges%2F851146cb-8792-4524-bcfb-af34e8f1e8e6%3Fdate%3D1&style=for-the-badge)](https://forge.laravel.com)

[![pipeline status](https://gitlab.com/jnkconsultbv/cetronic/badges/master/pipeline.svg)](https://gitlab.com/jnkconsultbv/cetronic/-/commits/master)

[![Latest Release](https://gitlab.com/jnkconsultbv/cetronic/-/badges/release.svg)](https://gitlab.com/jnkconsultbv/cetronic/-/releases)


## Déploiement local du projet Laravel
### Outils globaux
Le déploiement du projet nécessite au préalable d'installer les outils suivants :

**Docker (via Docker Desktop)**
> :point_right: <b>Docker</b> est nécessaire pour pouvoir utiliser l'environnement "Laravel Sail" associé au projet.

* Lien vers docker desktop (à installer): https://docs.docker.com/desktop/
* Lien vers sail (pour information): https://laravel.com/docs/9.x/sail

**Composer**
> :point_right: <b>Composer</b> est nécessaire pour installer les paquets du projet Laravel courant.

* Lien vers composer (à installer) : https://getcomposer.org/


### Déploiement local du projet  
> :warning: <span style="font-weight: bold;">Attention</span> :warning: 
> Il est nécessaire d'avoir installé au préalable "composer", "docker" et "laravel" pour pouvoir déployer le projet localement. Docker doit être démarré pour pouvoir effectuer les commandes de l'outil `sail`.

1.  Copier le fichier `.env.dev` dans le fichier `.env`.
   
2.  Copier le dump de la base de données mysql à prendre en compte dans `backup.sql` (ou autre).
    > :memo: Contenu à récupérer auprès de l'admin projet.

3.  Copier les images des produits en base dans `storage/app/public/*`.
    > :memo: Contenu à récupérer auprès de l'admin projet.

4.  Depuis une console, **se placer à la racine du projet** et exécuter les commandes suivantes en fonction des besoins pour un premier déploiement rapide du projet.
   
5. Installer le projet via `composer install`:

    ```bash
    # Installation des paquets projet contenus dans composer.json
    $ composer install
    ```

6. Démarrer `Docker Desktop` (si ce n'est pas déjà fait)
   
7. Lancer le serveur de dev via le CLI `sail` et importer le dump de la bdd : 
    ```bash
    # Attribution d'un alias à l'outil CLI "Sail"
    $ alias sail='[ -f sail ] && sh sail || sh vendor/bin/sail'

    # Lancement d'un serveur local via sail + docker (daemon non bloquant)
    $ sail up -d 

    # Import du dump de la base de données mysql
    # "backup.sql" est à remplacer par le fichier de *.sql adéquat
    $ sail mysql -u sail -p password cetronics < backup.sql

    # Link du storage public depuis sail (si pas fait en local) 
    $ sail artisan storage:link

    # Mise à jour de la bdd
    $ sail artisan migrate
    ```

8. Accéder au projet monté via le lien : http://localhost

9.  Arrêter le serveur de dev : 
    ```bash
    # Arrêt du serveur sail (sans démontage des volumes persistants)
    $ sail stop

    # Arrêt du serveur sail (avec démontage des volumes persistants)
    $ sail down -v
    ```

### Recap des commandes de déploiement rapide

**<u>Composer</u>**
* Installation des paquets du projet : `composer install`

**<u>Laravel Sail</u>**

Lien Sail CLI : https://laravel.com/docs/9.x/sail#configuring-a-shell-alias
   * Accès au CLI Sail : `./vendor/bin/sail`
   * Création de l'alias de commande sail : `alias sail='[ -f sail ] && sh sail || sh vendor/bin/sail'`
  
Quelques commandes sail :
  * `sail up [-d]`
  * `sail stop`
  * `sail down [-v]`
  * `sail mysql`
  * `sail mysql -u [db_user] -p [db_password] [db_name] < [db_dump_filename].sql`
  * `sail artisan migrate`
  * `sail artisan storage:link [--force]`
  * `sail bin duster lint` (pour le linter)
  * `sail bin duster fix` (pour fixer le linter)
  * `sail pint` (pour lancer check quality code)
  * `sail pest` (pour lancer les tests unitaires)


