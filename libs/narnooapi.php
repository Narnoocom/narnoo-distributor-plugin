<?php 

include "narnoo-php-sdk/vendor/autoload.php";


use Narnoo\Connect\Connect;
use Narnoo\Media\Media;
use Narnoo\Access\Access;



class Narnoosdk
{

	protected $token;

	public function __construct($token){
		$this->token = $token;
	}


	public function following($page=1){

		if(!empty($page)){
			$value = array(
				'page' => $page
			);
		}
		
		$connect = new connect();
		$connect->setToken($this->token);
		$list 	 = $connect->getFollowing($value);
		if(!empty($list)){
			return $list;
		}else{
			return NULL;
		}
	}


	public function find($page=1){

		if(!empty($page)){
			$value = array(
				'page' => $page
			);
		}
		
		$connect = new connect();
		$connect->setToken($this->token);
		$list 	 = $connect->findBusinesses($value);
		if(!empty($list)){
			return $list;
		}else{
			return NULL;
		}
	}


	public function search($search){

		$connect = new connect();
		$connect->setToken($this->token);
		$list 	 = $connect->searchBusinesses($search);
		if(!empty($list)){
			return $list;
		}else{
			return NULL;
		}
	}

	public function followBusiness($connect){

		$connect = new connect();
		$connect->setToken($this->token);
		$list 	 = $connect->followBusinesses($connect);
		if(!empty($list)){
			return $list;
		}else{
			return NULL;
		}
	}

	public function getImages($value){

		$media = new media();
		$media->setToken($this->token);
		$list 	 = $media->getImages($value);
		if(!empty($list)){
			return $list;
		}else{
			return NULL;
		}
	}

	public function getBusinessImages($businessType, $businessId,$value){

		$media = new Access();
		$media->setToken($this->token);
		$list 	 = $media->getBusinessImages($businessType, $businessId, $value);
		if(!empty($list)){
			return $list;
		}else{
			return NULL;
		}
	}

	public function getPrints($value){

		$media = new media();
		$media->setToken($this->token);
		$list 	 = $media->getPrints($value);
		if(!empty($list)){
			return $list;
		}else{
			return NULL;
		}
	}

	public function getBusinessPrints($businessType, $businessId, $value){

		$media = new Access();
		$media->setToken($this->token);
		$list 	 = $media->getBusinessPrints($businessType, $businessId, $value);
		if(!empty($list)){
			return $list;
		}else{
			return NULL;
		}
	}

	public function getVideos($value){

		$media = new media();
		$media->setToken($this->token);
		$list 	 = $media->getVideos($value);
		if(!empty($list)){
			return $list;
		}else{
			return NULL;
		}
	}

	public function getBusinessVideos($businessType, $businessId, $value){

		$media = new Access();
		$media->setToken($this->token);
		$list 	 = $media->getBusinessVideos($businessType, $businessId, $value);
		if(!empty($list)){
			return $list;
		}else{
			return NULL;
		}
	}


}
