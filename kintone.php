<?php
/**
 * Copyright 2015 soft-village.com. All Rights Reserved.
 * http://www.soft-village.com/
 * Licensed under the PHP License, version 3.01.
 */

namespace Ktn;

class DataAccess 
{
	// const
	const BASE_URL = "https://<SUBDOMAIN>.cybozu.com/k/v1/";
	const SINGL_JSON = "record.json";
	const MULTIPLE_JSON = "records.json";
	const API_JSON = "apis.json";
	const METHOD_SELECT = "GET";
	const METHOD_INSERT = "POST";
	const METHOD_UPDATE = "PUT";
	const METHOD_DELETE = "DELETE";
	
	// 
	public $errMsg = "";
	public $postKey = 0;
	public $responseData;
	
	private $ktnSubdomain;
	private $ktnAppriId;
	private $ktnToken;
	private $ktnUrlSingle;
	private $ktnUrlMultiple;
	
	// new
	function __construct($subdomain, $appriid, $token, $check){
		$this->ktnSubdomain = $subdomain;
		$this->ktnAppriId = $appriid;
		$this->ktnToken = $token;
		if($check) $this->chkConfig();
	}
	
	// 設定
	public function confSubdomain($subdomain, $check){
		$this->ktnSubdomain = $subdomain;
		if($check ) $this->chkConfig();
	}
	public function confAppriid($appriid, $check){
		$this->ktnAppriId = $appriid;
		if($$check) $this->chkConfig();
	}
	public function confToken($token, $check){
		$this->$ktnToken = $token;
		if($check) $this->chkConfig();
	}
	
	// 複数レコード取得
	public function getRecodes($query, $limit, $offset){
		$this->errMsg = "";
		$data = array(
			'app' => $this->ktnAppriId ,
			'query' => $query." limit ". $limit." offset ".$offset,
			'totalCount' =>  true
		);	
		$context = $this->setContext(self::METHOD_SELECT, $data);
		$url = str_replace("<SUBDOMAIN>", $this->ktnSubdomain, self::BASE_URL).self::MULTIPLE_JSON;
		$json = file_get_contents($url , false, stream_context_create($context));
		$this->responseData = json_decode($json, true);
		if(!isset($this->responseData['records'])){
			$this->errMsg = "Get Recodes Error.";
			return false;
		}
		return true;
	}
	
	// 1レコード追加
	public function postRecode($record){
		$this->errMsg = "";
		$this->postKey = 0;
		$data = array(
			'app'  => $this->ktnAppriId ,
			'record' => $record
		);	
		$context = $this->setContext(self::METHOD_INSERT, $data);
		$url = str_replace("<SUBDOMAIN>", $this->ktnSubdomain, self::BASE_URL).self::SINGL_JSON ;
		$json = file_get_contents($url , false, stream_context_create($context));
		$this->responseData = json_decode($json, true);
		if(isset($this->responseData['message'])){
			$this->errMsg = $this->responseData['message'];
			return false;
		}
		$this->postKey = $this->responseData['id'];
		return true;
	}
	
	// 設定試験
	public function chkConfig(){
		$this->errMsg = "";
		$context = $this->testContext();
		$url = str_replace("<SUBDOMAIN>", $this->ktnSubdomain, self::BASE_URL).self::API_JSON;
		$json = file_get_contents($url , false, stream_context_create($context));
		$json = json_decode($json, true);
		if(!is_array($json)){
			$this->errMsg = "Access Error 01.";
			return false;
		}
		return true;
	}
	
	// Hedder Context 生成
	private function setHadderToken(){
		return array(
			"Content-type: application/json",
			"X-Cybozu-API-Token: ".$this->ktnToken
		);
	}
	private function setContext($method, $data){
		return array(
			'http' => array(
				'method'  => $method,
				'header'  => implode("\r\n", $this->setHadderToken()),
				'content' => json_encode($data)
			)
		);
	}
	
	// API 試験
	private function testHadderToken(){
		return array(
			"X-Cybozu-API-Token: ".$this->ktnToken
		);
	}
	private function testContext(){
		return array(
			'http' => array(
				'method'  => self::METHOD_SELECT,
				'header'  => implode("\r\n", $this->testHadderToken())
			)
		);
	}
}
?>