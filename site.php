<?php

use \Hcode\Page;
use \Hcode\Model\Category;
use \Hcode\Model\Products;
use \Hcode\Model\Cart;

$app->get('/', function() {

	$products = Products::listAll();
    
	$page = new Page();

	$page->setTpl("index", [
		'products'=>Products::checkList($products)

	]);

});

$app->get("/categories/:idcategory", function($idcategory){

	$page = (isset($_GET['page'])) ? (int)$_GET['page'] : 1; //verificar se foi definido a pagina no meu url

	$category = new Category();
	
	$category->get((int)$idcategory); // garanto que vai vim um inteiro do idcategory

	$pagination = $category->getProductsPage($page);//passo o parâmetro de numero de paginas pego por $_GET

	$pages =[];

	for ($i=1; $i <= $pagination['pages']; $i++) { //total de paginas vai de 1 até meu total de paginas calculado pelo banco
		array_push($pages, [

			'link'=>'/categories/'. $category->getidcategory() . '?page=' . $i, 
			'page'=>$i
		]);//adicionar um array dentro do meu array pages
	}

	$page = new Page();

	$page->setTpl("category", [

		'category'=>$category->getValues(),
		'products'=>$pagination["data"], //recebendo os produtos da pagina
		'pages'=>$pages

	]);
});	

	$app->get("/products/:desurl", function($desurl){

		$products = new Products();

		$products->getFromURL($desurl);

		$page = new Page();

		$page->SetTpl("product-detail", [

			'product'=>$products->getValues(),
			'categories'=>$products->getCategories()

		]);

	});

	$app->get("/cart", function(){

		$cart = Cart::getFromSession();

		$page = new Page();

		$page->setTpl("cart", [

			'cart'=>$cart->getValues(),
			'products'=>$cart->getProducts()
		]);

	});

	$app->get("/cart/:idproduct/add", function($idproduct){

		$products = new Products();

		$products->get((int)$idproduct);

		$cart = Cart::getFromSession();

		$qtd = (isset($_GET['qtd'])) ? (int)$_GET['qtd'] : 1;

		for ($i = 0; $i < $qtd; $i++){

			$cart->addProduct($products);
		}


		header("Location: /cart");
		exit;

	});

	//template para remoção de quantidade
	$app->get("/cart/:idproduct/minus", function($idproduct){

		$products = new Products();

		$products->get((int)$idproduct);

		$cart = Cart::getFromSession();

		$cart->removeProduct($products);

		header("Location: /cart");
		exit;

	});


	//template de remoção total
	$app->get("/cart/:idproduct/remove", function($idproduct){

		$products = new Products();

		$products->get((int)$idproduct);

		$cart = Cart::getFromSession();

		$cart->removeProduct($products, true);

		header("Location: /cart");
		exit;

	});




?>