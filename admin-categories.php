<?php

use \Hcode\PageAdmin;
use \Hcode\Model\User;
use \Hcode\Model\Category;
use \Hcode\Model\Products;

$app->get("/admin/categories", function(){

	User::verifyLogin();

	$search = (isset($_GET['search'])) ? $_GET['search'] : "";
	$page = (isset($_GET['page'])) ? (int)$_GET['page'] : 1;

	if($search != ''){

		$pagination = Category::getPageSearch($search, $page);


	} else{

		$pagination = Category::getPage($page);

	}

	
	$pages = [];

	for ($x = 0; $x < $pagination['pages']; $x++){

		array_push($pages, [

			'href' => '/admin/categories?'. http_build_query([

				'page'=>$x+1,
				'search'=>$search

			]),

			'text'=>$x+1

		]);

	}

	$page = new PageAdmin();

	$page->setTpl("categories", [

		'categories'=>$pagination['data'],
		'search'=>$search,
		'pages'=>$pages

	]);

});

$app->get("/admin/categories/create", function(){

	User::verifyLogin();

	$page = new PageAdmin();	

	$page->setTpl("categories-create");

});

$app->post("/admin/categories/create", function(){

	User::verifyLogin();

	$category = new Category();

	$category->setData($_POST);

	$category->save();

	header('Location: /admin/categories');
	exit;

});

$app->get("/admin/categories/:idcategory/delete", function($idcategory){

	User::verifyLogin();

	$category = new Category();

	$category->get((int)$idcategory);

	$category->delete();

	header('Location: /admin/categories');
	exit;

});



$app->get("/admin/categories/:idcategory", function($idcategory){

	User::verifyLogin();

	$category = new Category();

	$category->get((int)$idcategory);

	$page = new PageAdmin();	

	$page->setTpl("categories-update", [

		'category'=>$category->getValues()

	]);

});

$app->post("/admin/categories/:idcategory", function($idcategory){

	User::verifyLogin();

	$category = new Category();

	$category->get((int)$idcategory);

	$category->setData($_POST);

	$category->save();

	header('Location: /admin/categories');
	exit;

});

$app->get("/admin/categories/:idcategory/products", function($idcategory){

	User::verifyLogin();

	$category = new Category();

	$category->get((int)$idcategory);

	$page = new PageAdmin();	

	$page->setTpl("categories-products", [

		'category'=>$category->getValues(),
		'productsRelated'=>$category->getProducts(),
		'productsNotRelated'=>$category->getProducts(false)

	]);

});

$app->get("/admin/categories/:idcategory/products/:idprodcuts/add", function($idcategory, $idprodcuts){

	User::verifyLogin();

	$category = new Category();

	$category->get((int)$idcategory);

	$products = new Products();

	$products->get((int)$idprodcuts);

	$category->addProduct($products);

	header("Location: /admin/categories/".$idcategory."/products");
	exit;

});

$app->get("/admin/categories/:idcategory/products/:idprodcuts/remove", function($idcategory, $idprodcuts){

	User::verifyLogin();

	$category = new Category();

	$category->get((int)$idcategory);

	$products = new Products();

	$products->get((int)$idprodcuts);

	$category->removeProduct($products);

	header("Location: /admin/categories/".$idcategory."/products");
	exit;

});


?>
