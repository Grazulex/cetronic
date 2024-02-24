<?php

declare(strict_types=1);

return [
    'order_new' => [
        'subject' => 'New order: ',
        'body' => 'Dear :name,<br><br>
         Thank you for your order number :reference. You can view your order history at any time by logging into your
         account on our website – www.cetronicbenelux.com.<br><br>
         We do our best to prepare it as soon as possible and send it to you. Please note that we may not have all your
         ordered products in stock and this could modify the content and the final invoice of this order.<br><br>
         Please do not hesitate to contact us if you have any questions regarding your order.<br><br>
         Regards,<br>
         The Cetronic Team',
    ],
    'cart_remove' => [
        'subject' => 'Product removed from cart',
        'body' => 'Dear :name,<br><br>
         Please note that the content of your basket has changed because the following products are no longer in stock:<br><br>
         :product<br><br>
         Regards,<br>
         The Cetronic Team',
    ],
    'cart_add' => [
        'subject' => 'Product added to cart',
        'body' => 'Dear :name,<br><br>
         Please note that some products you had in your shopping cart are currently available in stock. Here are the details of the products available again:<br><br>
         :product<br><br>
         Regards,<br>
         The Cetronic Team',
    ],
    'cart_update' => [
        'subject' => 'Product updated in cart',
        'body' => 'Dear :name,<br><br>
         Please note that some items you have in your shopping cart have changed prices. Here is the list of products that have changed prices:<br><br>
         :product<br><br>
         Regards,<br>
         The Cetronic Team',
    ],
];
