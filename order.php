<?php

use \Hcode\Page;
use \Hcode\Model\Category;
use \Hcode\Model\Products;
use \Hcode\Model\Cart;
use \Hcode\Model\Address;
use \Hcode\Model\User;
use \Hcode\Model\Order;
use \Hcode\Model\OrderStatus;

$app->get("/order/:idorder", function($idorder){

	User::verifyLogin(false);

	$order = new Order();

	$order->get((int)$idorder);

	$page = new Page();
	$page->setTpl("payment", [

		'order'=>$order->getValues()

	]);

});

$app->get("/boleto/:idorder", function(){



});

?>