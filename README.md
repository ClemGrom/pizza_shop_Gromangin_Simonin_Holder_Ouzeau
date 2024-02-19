# PizzaShop

> Gromangin Clément,
> Simonin Enzo,
> Holder Jules

## Installation

- Clonez le projet
```
git clone https://github.com/ClemGrom/pizza_shop_Gromangin_Simonin_Holder_Ouzeau.git
```

- Installez les dépendances dans les dossiers : auth.pizza-shop, catalogue.pizza-shop, gateway.pizza-shop, shop.pizza-shop
```
composer install
```
Si vous avez une erreur avec l’installation des packages de shop.pizza-shop activez
l’extension : sockets de php

- Lancez les containers docker depuis le dossier pizza.shop.components
```
docker-compose up -d
```

### Configuration de rabbitmq :

- Lien : http://localhost:15672
```
User : admin, mot de passe : admin
```

- Créer un exchange nommé "pizzashop" de type "DIRECT" :
```
Bouton exchanges, Name: pizzashop, Bouton add exchange
```

- Créer une queue nommée "nouvelles_commandes" de type "classic" avec la propriété Durable :
```
Bouton queues and streams, Type : classic, Name : nouvelles_commandes , Bouton add queue
```

- Créer un binding entre l’exchange "pizzashop" et la queue "nouvelles_commandes" avec la routing key "nouvelle" :
```
Bouton exchanges, pizzashop, To queue : nouvelles_commandes, Routing key : nouvelle, Bouton bind
```

- Créer un utilisateur avec un mot de passe puis lui donner les permissions * sur toutes les actions :
```
Bouton admin, Username : user, Password : user, Bouton add user, Clique sur user, Bouton set permission
```

- Créer une queue dédiée dans le serveur RabbitMQ, nommée "suivi_commandes" avec un binding sur l’exchange "pizzashop" avec la routing key "suivi" :
```
Bouton queues and streams, Type : classic, Name : suivi_commandes , Bouton add queue Bouton exchanges, Clique sur Pizzashop, To queue : suivi_commandes, Routing key : suivi, Bouton bind
```

#### Un redémarrage de certain conteneur docker est nécessaire afin que certain service comme express js et le web sockets fonctionne :
```
docker restart pizzashopcomponents-api.service-1 pizzashopcomponentswebsockets.service-1
```

### Insérez les données dans les bases de données auth, catalogue et commande :

- Auth :
```
Lien : http://localhost:41222/?server=pizza-shop.auth.db&username=pizza_auth&db=pizza_auth
Mot de passe : pizza_auth
Les données sont dans le dossier auth.pizza-shop/sql, ajouté le fichier pizza_shop.auth.schema.sql
et ensuite le fichier pizza_shop.auth.data.sql
```

- Catalogue :
```
Lien : http://localhost:41222/?pgsql=pizza-shop.catalogue.db&username=pizza_catalog&db=pizza_catalog&ns=public
Mot de passe : pizza_catalog
Les données sont dans le dossier catalogue.pizza-shop/sql, ajouté le fichier pizza_shop.catalogue.schema.sql
et ensuite le fichier pizza_shop.catalogue.data.sql
```

- Commande :
```
Lien : http://localhost:41222/?server=pizza-shop.commande.db&username=pizza_shop&db=pizza_shop&select=commande
Mot de passe : pizza_shop
Les données sont dans le dossier shop.pizza-shop/sql, ajouté le fichier pizza_shop.commande.schema.sql
et ensuite le fichier pizza_shop.commande.data.sql
```

- Express :
```
Lien : http://localhost:41222/?server=pizza-shop.commande.db&username=pizza_shop&db=pizza_shop&select=commande
Mot de passe : pizza_express
Les données sont dans le dossier express.pizza-shop/sql, ajouté le fichier pizza_shop.schema.sql
et ensuite le fichier pizza_shop.data.sql
```

