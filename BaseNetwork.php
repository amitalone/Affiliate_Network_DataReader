<?php
ini_set('memory_limit', '-1');
require_once('NetworkRecord.php');
require_once('CryptoUtil.php');
require_once('MemoryOfferTable.php');
include('simple_html_dom.php');

abstract class BaseNetwork {
	
	static $TRANSACTION_SERVICE_URL = "//transaction.php";
	private static $GBP_TO_USD = 1.65;
	
	protected function getCurlObject() {
		$ch = curl_init();
		curl_setopt ($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 6.1; WOW64; rv:31.0) Gecko/20100101 Firefox/31.0");
		return $ch;	
	}
	
	protected function getOfferID($offerName) {
		return MemoryOfferTable::getInstance()->getOfferID($offerName);
	}

	protected function fixNumber($lable){
		$lable = str_replace(",", "", $lable);
		$number = str_replace("$", "", $lable);
		$number = str_replace(",", "", $number);
		$number = floatval($number);
		$number =  number_format ($number, 2);
		$number = str_replace(",", "", $number);
		return $number;
	}

	protected function getCurrentDate() {
		$date = date("Y-n-j");
		return $date;
	}

	protected function getYesterday() {
		return date('Y-m-d',(strtotime ( '-1 day' , strtotime ( date("Y-n-j")) ) ));
	}

	protected function getCredentials($networkID) {
		$TRANSACTION_SERVICE_URL = self::$TRANSACTION_SERVICE_URL;
		$url = "$TRANSACTION_SERVICE_URL?cmd=authtoken&nwid=$networkID";
		$response = file_get_contents($url);
		$response = base64_decode($response);
		$response = CryptoUtil::crossConvert($response, "XXXXX");
		list($u, $p) = explode("|", $response);
		$data = array("usr"=>$u, "pwd"=>$p);
		return $data;
	}

	protected function uploadRecords($records) {
		 
		foreach($records as $record) {
			self::uploadRecord($record);
		}
	}

	protected function uploadRecord($record) {
		$TRANSACTION_SERVICE_URL = self::$TRANSACTION_SERVICE_URL;
		$networkID= $record->networkId;
		$offerId= $record->offerId;
		$activitiDate= $record->activityDate;
		$click=$record->clicks;
		$lead=$record->leads;
		$payout=$record->payout;
		$total=$record->total;
		$offerName=$record->offerName;
		$activityID=$record->activityID;
		$sbtID=$record->sbtID;
		$url = "$TRANSACTION_SERVICE_URL?cmd=aoptadd&nwid=$networkID&oid=$offerId&dt=$activitiDate&cl=$click&le=$lead&po=$payout&tl=$total&ofn=$offerName";
		$this->callDBService($url);
	// echo $url."<br>";
	}

	protected function uploadTimeTableRecords($records) {
		 
		foreach($records as $record) {
			self::uploadTimeTableRecord($record);
		}
	}

	protected function uploadTimeTableRecord($record) {
		$TRANSACTION_SERVICE_URL = self::$TRANSACTION_SERVICE_URL;
		$networkID= $record->networkId;
		$offerId= $record->offerId;
		$activitiDate= $record->activityDate;
		$click=$record->clicks;
		$lead=$record->leads;
		$payout=$record->payout;
		$total=$record->total;
		$offerName=$record->offerName;
		$activityID=$record->activityID;
		$sbtID=$record->sbtID;
		$url = "$TRANSACTION_SERVICE_URL?cmd=aottadd&nwid=$networkID&oid=$offerId&cl=$click&le=$lead&tl=$total&ofn=$offerName";
		$this->callDBService($url);
	}

	/**********************EXPT ATT15 ****************/

	protected function uploadTimeTableRecords15($records) {
		 
		foreach($records as $record) {
			self::uploadTimeTableRecord15($record);
		}
	}

	protected function uploadTimeTableRecord15($record) {
		$TRANSACTION_SERVICE_URL = self::$TRANSACTION_SERVICE_URL;
		$networkID= $record->networkId;
		$offerId= $record->offerId;
		$activitiDate= $record->activityDate;
		$click=$record->clicks;
		$lead=$record->leads;
		$payout=$record->payout;
		$total=$record->total;
		$offerName=$record->offerName;
		$activityID=$record->activityID;
		$sbtID=$record->sbtID;
		$url = "$TRANSACTION_SERVICE_URL?cmd=aottadd15&nwid=$networkID&oid=$offerId&cl=$click&le=$lead&tl=$total&ofn=$offerName";
		$this->callDBService($url);
	}


	protected function uploadConversionRecords($records) {
		foreach($records as $record) {
			self::uploadConversionRecord($record);
		}
	}
	
	protected function uploadConversionRecord($record) {
		$TRANSACTION_SERVICE_URL = self::$TRANSACTION_SERVICE_URL;
		$networkID= $record->networkId;
		$offerId= $record->offerId;
		$activitiDate= $record->activityDate;
		$click=$record->clicks;
		$lead=$record->leads;
		$payout=$record->payout;
		$total=$record->total;
		$offerName=$record->offerName;
		$activityID=$record->activityID;
		$sbtID=$record->sbtID;
		$type=$record->type;
		$url = "$TRANSACTION_SERVICE_URL?cmd=aoctadd&nwid=$networkID&oid=$offerId&dt=$activitiDate&cl=$click&le=$lead&po=$payout&tl=$total&ofn=$offerName&aci=$activityID&sbt=$sbtID&ty=$type";
	 
		
		 $this->callDBService($url);
	}

	protected function cleanYesterdayNetworkRecords($networkID) {
		$TRANSACTION_SERVICE_URL = self::$TRANSACTION_SERVICE_URL;
		$url = "$TRANSACTION_SERVICE_URL?cmd=cleanprev&nwid=$networkID";
		 $this->callDBService($url);
	}

	protected function cleanTodayNetworkRecords($networkID) {
		$TRANSACTION_SERVICE_URL = self::$TRANSACTION_SERVICE_URL;
		$url = "$TRANSACTION_SERVICE_URL?cmd=cleantoday&nwid=$networkID";
		 $this->callDBService($url);
	}

	protected function cleanYesterdayConversions($networkID) {
		$TRANSACTION_SERVICE_URL = self::$TRANSACTION_SERVICE_URL;
		$url = "$TRANSACTION_SERVICE_URL?cmd=cleanprevconversion&nwid=$networkID";
		 $this->callDBService($url);
	}

	protected function cleanTodayConversions($networkID) {
		$TRANSACTION_SERVICE_URL = self::$TRANSACTION_SERVICE_URL;
		$url = "$TRANSACTION_SERVICE_URL?cmd=cleantodaysconversion&nwid=$networkID";
		 $this->callDBService($url);
	}
	protected function  getNetworkTotalRevenue($networkid)  {
		$TRANSACTION_SERVICE_URL = self::$TRANSACTION_SERVICE_URL;
		$url = "$TRANSACTION_SERVICE_URL?cmd=nwtotal&nwid=$networkid";
		$out = file_get_contents($url);
		if(empty($out)) {
			$out = 0;
		}
		return $out;
	}
	
	protected function callDBService($url) {
		//file_get_contents($url);
		//error_log($url);
		//echo "$url<br>\n";
		$ch = $this->getCurlObject();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt ($ch, CURLOPT_VERBOSE, 0);
		curl_setopt ($ch, CURLOPT_HEADER, 1);
		curl_setopt ($ch, CURLINFO_HEADER_OUT,true);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_exec($ch); 
		curl_close ($ch); 
		 
		 
	}
	
	protected function makeHTTPGet($url) {
		$ch = $this->getCurlObject();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt ($ch, CURLOPT_VERBOSE, 0);
		curl_setopt ($ch, CURLOPT_HEADER, 1);
		curl_setopt ($ch, CURLINFO_HEADER_OUT,true);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		$out = curl_exec($ch); 
		curl_close ($ch);
		return $out;
	}
	
	protected  function toInteger($number) {
		$number = number_format($number);
		return str_replace(",", "", $number);
	}
	protected function gbpToUsd($usd) {
		return $usd * self::$GBP_TO_USD;
	}
	

}

?>