<?php

namespace Hcode\Model;

use \Hcode\DB\Sql;
use \Hcode\Model;
use \Hcode\Mailer;
use \Hcode\Model\User;

class Cart extends Model{

	const SESSION = "Cart";

	public static function getFromSession(){ //verifico a sessão do carrinho, ou seja, a sessão que o usuario obeteve do carrinho.

		$cart = new Cart();

		if(isset($_SESSION[Cart::SESSION]) && (int)$_SESSION[Cart::SESSION]['idcart'] > 0){// se seção existir e for maio que 0, meu carrinho ta na sessão e foi inserido ao banco

			$cart->get((int)$_SESSION[Cart::SESSION]['idcart']);//carrega o carrinho

		} else {

			$cart->getFromSessionID();

			if(!(int)$cart->getidcart() > 0) {

				$data = [

					'dessessionid'=>session_id()

				];

				if (User::checkLogin(false) === true){

					$user = User::getFromSession();

					$data['iduser'] = $user->getiduser(); // cria um carrinho relacionando ao  id do usuario
					
				} // padrão true por ser uma rota de admin. Como é no carrinho de comprar então esta false

				$cart->setData($data); //coloca a variavel dentro do cart

				$cart->save(); // salva no banco

				$cart->setToSession(); //colocar o carrinho novo na sessão
			}

		}

		return $cart;

	}

	public function setToSession() {

		$_SESSION[Cart::SESSION] = $this->getValues();

	}

	public function getFromSessionID() {

		$sql = new Sql();

		$results = $sql->select("SELECT * FROM tb_carts WHERE dessessionid = :dessessionid", [
			':dessessionid'=>session_id() //pega o id da sessão

		]);

		if(count($results) > 0){

			$this->setData($results[0]);

		}

	}

	public function get(int $idcart) {

		$sql = new Sql();

		$results = $sql->select("SELECT * FROM tb_carts WHERE idcart = :idcart", [
			':idcart'=>$idcart

		]);

		if(count($results) > 0){

			$this->setData($results[0]);

		}

	}
	
	public function save(){//salvando os dados do carrinho

		$sql = new Sql();

		$results = $sql->select("CALL sp_carts_save(:idcart, :dessessionid, :iduser, :deszipcode, :vlfreight, :nrdays)", [

				':idcart'=>$this->getidcart(),
				':dessessionid'=>$this->getdessessionid(),
				':iduser'=>$this->getiduser(),
				':deszipcode'=>$this->getdeszipcode(),
				':vlfreight'=>$this->getvlfreight(),
				':nrdays'=>$this->getnrdays()

		]);

		$this->setData($results[0]);

	}
	
	//adiciona produto no carrinho
	public function addProduct(Products $product){

		$sql = new Sql();
		$sql->query("INSERT INTO tb_cartsproducts (idcart, idproduct) VALUES (:idcart, :idproduct)", [
			':idcart'=>$this->getidcart(),
			':idproduct'=>$product->getidproduct()

		]);

	}

	//remove todos os produtos ou a quantaide deles no carrinho. No banco não será DELETADO e sim tera um status de removido.

	public function removeProduct(Products $product, $all = false){

		$sql = new Sql();


		if($all) {
			//remove o item inteiro
			$sql->query("UPDATE tb_cartsproducts SET dtremoved = NOW() WHERE idcart = :idcart AND idproduct = :idproduct AND dtremoved IS NULL", [

				':idcart'=>$this->getidcart(),
				':idproduct'=>$product->getidproduct()

			]);

		} else {

			//remove quantidade
			$sql->query("UPDATE tb_cartsproducts SET dtremoved = NOW() WHERE idcart = :idcart AND idproduct = :idproduct AND dtremoved IS NULL LIMIT 1", [

				':idcart'=>$this->getidcart(),
				':idproduct'=>$product->getidproduct()

			]);

		}

	}

	public function getProducts(){

		$sql = new Sql();

		$rows = $sql->select("
			SELECT b.idproduct, b.desproduct , b.vlprice, b.vlwidth, b.vlheight, b.vllength, b.vlweight, b.desurl, COUNT(*) AS nrqtd, SUM(b.vlprice) AS vltotal 
			FROM tb_cartsproducts a 
			INNER JOIN tb_products b ON a.idproduct = b.idproduct 
			WHERE a.idcart = :idcart AND a.dtremoved IS NULL 
			GROUP BY b.idproduct, b.desproduct , b.vlprice, b.vlwidth, b.vlheight, b.vllength, b.vlweight, b.desurl 
			ORDER BY b.desproduct
		", [
			':idcart'=>$this->getidcart()
		]);

		return Products::checkList($rows);

	}

}


?>