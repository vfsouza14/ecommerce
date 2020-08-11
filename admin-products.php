<?php

use \Hcode\PageAdmin;
use \Hcode\Model\User;
use \Hcode\Model\Products;

$app->get("/admin/products", function(){

	User::verifyLogin();

	$search = (isset($_GET['search'])) ? $_GET['search'] : "";
	$page = (isset($_GET['page'])) ? (int)$_GET['page'] : 1;

	if($search != ''){

		$pagination = Products::getPageSearch($search, $page);


	} else{

		$pagination = Products::getPage($page);

	}

	
	$pages = [];

	for ($x = 0; $x < $pagination['pages']; $x++){

		array_push($pages, [

			'href' => '/admin/products?'. http_build_query([

				'page'=>$x+1,
				'search'=>$search

			]),

			'text'=>$x+1

		]);

	}

	$page = new PageAdmin();

	$page->setTpl("products", [

		"products"=>$pagination['data'],
		"search"=>$search,
		"pages"=>$pages

	]);

});	

$app->get("/admin/products/create", function(){

	User::verifyLogin();

	$page = new PageAdmin();


	$page->setTpl("products-create");

});

$app->post("/admin/products/create", function(){

	User::verifyLogin();

	$products = new Products();

	$products->setData($_POST);

	$products->save();

	header("Location: /admin/products");
	exit;

});

$app->get("/admin/products/:idproduct", function($idproduct){

	User::verifyLogin();

	$products = new Products();

	$products->get((int)$idproduct);

	$page = new PageAdmin();


	$page->setTpl("products-update", [

		'product'=>$products->getValues()

	]);

});

$app->post("/admin/products/:idproduct", function($idproduct){

	User::verifyLogin();

	$products = new Products();

	$products->get((int)$idproduct);

	$products->setData($_POST);

	$products->save();

	$products->setPhoto($_FILES["file"]);

	header('Location: /admin/products');
	exit;

});

$app->get("/admin/products/:idproduct/delete", function($idproduct){

	User::verifyLogin();

	$products = new Products();

	$products->get((int)$idproduct);

	$products->delete();

	header('Location: /admin/products');
	exit;

});	

?>