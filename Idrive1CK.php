<?php
require_once ('BaseNetwork.php');
require_once ('INetwork.php');

class Idrive1CK extends BaseNetwork implements iNetwork {
	static $networkid = "12";
	static $cookiejar = "";
	static $CAKE_CLICK_BOOTH_BASE_WEBSERVICE_URL = "http://control.idriveinteractive.com/";
	static $CAKE_CLICK_BOOTH_API_KEY = "XXXX";
	static $CAKE_CLICK_BOOTH_AFFILIATE_ID = "XXXX";
	
	function __construct() {
		$nid = self::$networkid;
		self::$cookiejar = "cookies/_____$nid.txt";
		$this->login();
	}
	
	public function importRecord($date = null) {
		if(null == $date) {
			$date = $this->getCurrentDate();
		}
		$records = $this->getNetworkRecords($date);
		$this->uploadRecords($records);
		
	}
	
	public function getCurrentTimeStats() {
		$date = $this->getCurrentDate();
		$records = $this->getNetworkRecords($date);
		$this->uploadTimeTableRecords($records);
	}

	public function getCurrentTimeStats15() {
		$date = $this->getCurrentDate();
		$records = $this->getNetworkRecords($date);
		$this->uploadTimeTableRecords15($records);
	}

	public function syncToPreviousDate() {
		$this->cleanYesterdayNetworkRecords(self::$networkid);
		$this->importRecord($this->getYesterday());
	}
	
	public function getLatestStats() {
		$this->cleanTodayNetworkRecords(self::$networkid);
		$this->importRecord();
	}
	
	public function getLatestConversions() {
		$this->cleanTodayConversions(self::$networkid);
		$this->importLeadSummary($this->getCurrentDate());
	}
	
	public function syncConversionsToPreviousDate() {
		$this->cleanYesterdayConversions(self::$networkid);
		$this->importLeadSummary($this->getYesterday());
	}
	
	public function adjustUnsetledAmount() {
	}
	
	public function importLeadSummary($date = null) {
		if(null == $date) {
			$date = $this->getCurrentDate();
		}
		$records = $this->getLeadSummary($date);
		$this->uploadConversionRecords($records);
		//print_r($records);
	}
	
	public function getHomePageStats() {
		$url = self::$CAKE_CLICK_BOOTH_BASE_WEBSERVICE_URL . "affiliates/api/2/reports.asmx/PerformanceSummary";
		$url = "$url?api_key=" . self::$CAKE_CLICK_BOOTH_API_KEY . "&affiliate_id=" . self::$CAKE_CLICK_BOOTH_AFFILIATE_ID . "&date=" . date ( "m/d/Y" );
		$response = $this->makeHTTPGet ( $url );
		
		$dom = str_get_html ( $response );
		$periods = $dom->find ( 'period' );
		$todayRev = "";
		$monthRev = "";
		
		foreach ( $periods as $period ) {
			$type = $period->find ( 'date_range', 0 )->plaintext;
			if ("Today" == $type) {
				$todayRev = $period->find ( 'current_revenue', 0 )->plaintext;
			}
			if ("MTD" == $type) {
				$monthRev = $period->find ( 'current_revenue', 0 )->plaintext;
			}
		}
		$todayRev = $this->fixNumber ( $todayRev );
		$monthRev = $this->fixNumber ( $monthRev );
		$data = array ("Total" => $this->fixNumber ( $this->getNetworkTotalRevenue ( self::$networkid ) ), "Today" => $todayRev, "This Month" => $monthRev );
		return $data;
	}
	
	private function login() {
		$credentials = $this->getCredentials ( self::$networkid );
		$username = urlencode($credentials ["usr"]);
		$password = urlencode($credentials ["pwd"]);
		
		$url = "http://control.idriveinteractive.com/login.ashx";
		$postparams = "u=$username&p=$password";
	 
		$ch = $this->getCurlObject();
		curl_setopt($ch,CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_COOKIEJAR, self::$cookiejar); 
		curl_setopt ($ch, CURLOPT_VERBOSE, 0);
		curl_setopt ($ch, CURLOPT_HEADER, 1);
		curl_setopt ($ch, CURLINFO_HEADER_OUT,true);
		curl_setopt($ch, CURLOPT_COOKIESESSION, TRUE);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $postparams);
		curl_exec($ch); 
		curl_close ($ch); 
		
		 
	}
	
	 
	private function getCBFMTate($date) {
		return date("m/d/Y",(strtotime ( $date) ));
	}
	
	
	public function getNetworkRecords($date) {
		error_log("========== Idrive1CK SYNC REQUEST =========");
		$cbDate = $this->getCBFMTate($date);
		$cbDate = urlencode($cbDate); 
		
		$networkid=self::$networkid;

	
		$url = "http://control.idriveinteractive.com/affiliates/Extjs.ashx?s=subaffsummary";
		
		//$postparams = "groupBy=&groupDir=ASC&o=sub_id&d=ASC&report_view_id=417&report_id=95&report_views=Default&enter_subaffiliate_id=&exclude_bot_traffic=1&start_date=$cbDate&end_date=$cbDate&n=100&offer=";
		 
		 $postparams ="groupBy=&groupDir=ASC&o=sub_id&d=DESC&report_view_id=95&report_id=95&report_views=Default&date_range=yesterday&start_date=$cbDate&end_date=$cbDate&n=200&enter_subaffiliate_id=";
		//error_log("========== $postparams =========");
		$ch = $this->getCurlObject ();
		curl_setopt ( $ch, CURLOPT_URL, $url );
		curl_setopt ( $ch, CURLOPT_COOKIEJAR, self::$cookiejar );
		curl_setopt($ch, CURLOPT_COOKIEFILE, self::$cookiejar); 
		curl_setopt ( $ch, CURLOPT_VERBOSE, 0 );
		curl_setopt ( $ch, CURLOPT_HEADER, 0 );
		curl_setopt ( $ch, CURLINFO_HEADER_OUT, false );
		curl_setopt ( $ch, CURLOPT_COOKIESESSION, TRUE );
		curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, TRUE );
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); 
		curl_setopt ( $ch, CURLOPT_POSTFIELDS, $postparams );
		$response = curl_exec ( $ch );
		error_log($response);

		$json = json_decode($response);
		$rows = $json->rows;
		$recordList = array();
		foreach($rows as $row) {
			$offername = $row->sub_id;
			if(empty($offername) || "" == $offername) {
				continue;
			}
			$clicks = $row->clicks;
			$leads = $row->conversions;
			$total = $this->fixNumber($row->revenue);
			$payout = 0;
			if($total > 0 && $leads > 0) {
				$payout = $total/$leads;
				$payout = $this->fixNumber($payout);
			}
			
			$o = new NetworkRecord();
			$o->networkId = $networkid;
			$o->offerId = $this->getOfferID($offername);
			$o->activityDate = $date;
			$o->clicks = $this->toInteger($this->fixNumber($clicks));
			$o->leads = $this->toInteger($this->fixNumber($leads));
			$o->payout = $payout;
			$o->total = $total;
			$o->offerName = $offername;
			array_push($recordList, $o);
		}
	 
		return $recordList;
		 
	}
	
	private function getLeadSummary($date) {
		$cbDate = $this->getCBFMTate($date);
		$cbDate = urlencode($cbDate); 
		
		$networkid=self::$networkid;
	
		$url = "http://control.idriveinteractive.com/affiliates/Extjs.ashx?s=leadreport";
		$postparams = "groupBy=&groupDir=ASC&o=date&d=ASC&report_view_id=419&report_id=97&report_views=Default&show_changes=0&exclude_bot_traffic=1&date_range=lastmonth&start_date=$cbDate&end_date=$cbDate&change_since=&include_new_conversions=0&n=30&disposition=";
		$ch = $this->getCurlObject ();
		curl_setopt ( $ch, CURLOPT_URL, $url );
		curl_setopt ( $ch, CURLOPT_COOKIEJAR, self::$cookiejar );
		curl_setopt($ch, CURLOPT_COOKIEFILE, self::$cookiejar); 
		curl_setopt ( $ch, CURLOPT_VERBOSE, 0 );
		curl_setopt ( $ch, CURLOPT_HEADER, 0 );
		curl_setopt ( $ch, CURLINFO_HEADER_OUT, false );
		curl_setopt ( $ch, CURLOPT_COOKIESESSION, TRUE );
		curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, TRUE );
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); 
		curl_setopt ( $ch, CURLOPT_POSTFIELDS, $postparams );
		$response = curl_exec ( $ch );
		
		$json = json_decode($response);
		$rows = $json->rows;
		$recordList = array();
		
		foreach($rows as $row) {
			$offername = $row->subid_1;
			$activityID = $row->subid_2;
			$sbtid = $row->subid_3;
			if(empty($offername) || "" == $offername) {
				continue;
			}
			$clicks = 1;
			$leads = 1;
			$total = $this->fixNumber($row->price);
			if($total == 0) {
				continue;
			}
			$payout = $total;
			if($total > 0 && $leads > 0) {
				$payout = $total/$leads;
				$payout = $this->fixNumber($payout);
			}
			
			$o = new NetworkRecord();
			$o->networkId = $networkid;
			$o->offerId = $this->getOfferID($offername);
			$o->activityDate = $date;
			$o->clicks = $clicks;
			$o->leads = $leads;
			$o->payout = $payout;
			$o->total = $total;
			$o->offerName = $offername;
			$o->sbtID=$sbtid;
			$o->activityID=$activityID;
			array_push($recordList, $o);
		} 
		return $recordList;
	}
}
?>