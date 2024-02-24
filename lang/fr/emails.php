<?php

declare(strict_types=1);

return [
    'order_new' => [
        'subject' => 'Nouvelle commande: ',
        'body' => 'Cher :name,<br><br>
         Merci pour votre commande n° :reference. Vous pouvez consulter l&#39;historique de vos commandes à tout moment en
         vous connectant à votre compte sur notre site Web – www.cetronicbenelux.com.<br><br>
         Nous faisons notre maximum pour la préparer dans les plus brefs délais et vous l’envoyer. Veuillez noter que
         nous pourrions ne pas avoir tous vos produits commandés en stock et que cela pourrait modifier le contenu et la
         facture finale de cette commande.<br><br>
         N’hésitez pas à nous contacter si vous aviez des questions concernant votre commande.<br><br>
         Bien à vous,<br>
         L&#39;équipe Cetronic',
    ],
    'cart_remove' => [
        'subject' => 'Produit supprimé du panier',
        'body' => 'Cher :name,<br><br>
         Veuillez noter que le contenu de votre panier a changé car les produits suivants ne sont plus de stock :<br><br>
         :product<br><br>
         Cordialement,<br>
         L’équipe Cetronic',
    ],
    'cart_add' => [
        'subject' => 'Produit ajouté au panier',
        'body' => 'Cher :name,<br><br>
         Veuillez noter que certains produits que vous aviez dans votre panier sont à niveau disponibles en stock. Voici le détails des produits à nouveau disponibles :<br><br>
         :product<br><br>
         Cordialement,<br>
         L’équipe Cetronic',
    ],
    'cart_update' => [
        'subject' => 'Produit mis à jour dans le panier',
        'body' => 'Cher :name,<br><br>
         Veuillez noter que certains articles que vous avez dans votre panier ont changé de prix. Voici la liste des produits qui ont changés de prix :<br><br>
         :product<br><br>
         Cordialement,<br>
         L’équipe Cetronic',
    ],
];
