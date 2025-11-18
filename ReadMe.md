🔧 Installation du projet

Clonez le dépôt

git clone git@github.com:AntonHarzhanau/Hamster.git
cd Hamster

Installez les dépendances PHP

composer install

Configurez les variables d'environnement

Copiez .env en .env.local :

cp .env .env.local

Indiquez les paramètres de connexion à la base de données :

DDATABASE_URL="postgresql://user:password@127.0.0.1:5432/hamster?serverVersion=16&charset=utf8"


Créez la base de données et exécutez les migrations

php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate

(Optionnel) Chargez les fixtures

php bin/console doctrine:fixtures:load

🌐 Lancement de l'application

Serveur intégré Symfony

symfony serve

L'application sera accessible à l'adresse : 👉 http://127.0.0.1:8000

Ou via PHP

php -S 127.0.0.1:8000 -t public
