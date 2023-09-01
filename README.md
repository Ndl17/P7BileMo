# Projet 7 OpenClassrooms - Créez un web service exposant une API

## Score de qualité du code :
https://insight.symfony.com/projects/87481171-eda5-4af1-82f1-92494b4daa61/big.svg

## Informations :

## Identifiants pour se connecter :

#### Utilisateur Verifié :
* Identifiant : mail@mail.com
* Mot de Passe : password


## Prérequis :
* PHP 8.1.13, Composer, Symfony 6. 


## Installation :
* Etape 1 : Installez l’ensemble des fichier de ce repo dans le dossier web de votre environnement local.
* Etape 2 : Modifiez les constantes du fichier .env  selon les information de votre bdd: 
DATABASE_URL="mysql://username:password@127.0.0.1:3306/snowtricks?serverVersion=8&charset=utf8mb4"
* Etape 3 :  Effectuez la commande "composer install" depuis le répertoire du projet cloné
* Etape 5 : Effectuez la commande php bin/console doctrine:database:create pour créer la base de données 
* Etape 6 : Pour recréer la structure de la bdd, lancez la commande suivante : php bin/console doctrine:migrations:migrate
* Etape 7 : Pour recréer le jeu de donnée: php bin/console doctrine:fixtures:load
* Etape 8 : Générez les clés SSH pour se faire:
```
1. Allez dans le dossier config
2. Créez un dossier "jwt"
3. Tapez les commandes suivantes dans votre terminal, pour chaque commande spécifiez votre passphrase:
	$ openssl genpkey -out config/jwt/private.pem -aes256 -algorithm rsa -pkeyopt rsa_keygen_bits:4096
	$ openssl pkey -in config/jwt/private.pem -out config/jwt/public.pem -pubout
4. Notez votre passphrase à la ligne "JWT_PASSPHRASE=" de votre fichier .env.local
```

* Etape 9 : Démarrez le projet en utilisant la commande suivante : php bin/console server:start, accédez à la doc via l'url suivante: http://127.0.0.1:8000/api/doc

## Librairies utilisées :
FakerPhp
Hateoas
Nelmio
