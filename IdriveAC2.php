<?php
require_once('BaseNetwork.php');
require_once('INetwork.php');

class IdriveAC2 extends BaseNetwork implements iNetwork {
	static $networkid = "X";
	static $cookiejar = "";
	
	function __construct() {
		$nid = self::$networkid;
		self::$cookiejar = "cookies/_____$nid.txt";
		$this->login();
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

	public function importRecord($date =null) {
		if(null == $date) {
			$date = $this->getCurrentDate();
		}
		$records = $this->getNetworkRecords();
		$this->uploadRecords($records);
	}

	public function syncToPreviousDate() {
		$this->cleanYesterdayNetworkRecords(self::$networkid);
		$this->importRecord($this->getYesterday());
	}

	public function getLatestStats() {
		$this->cleanTodayNetworkRecords(self::$networkid);
		$this->importRecord();
	}

	public function getLatestConversions(){
		$this->cleanTodayConversions(self::$networkid);
		$this->importLeadSummary();
	}

	public function syncConversionsToPreviousDate() {
		$this->cleanYesterdayConversions(self::$networkid);
		$this->importLeadSummary($this->getYesterday());
	}

	public function adjustUnsetledAmount() {
	}

	public function importLeadSummary($date =null) {
		if(null == $date) {
			$date = $this->getCurrentDate();
		}
		$records = $this->getLeadSummary();
		$this->uploadConversionRecords($records);
	}
	
	private function login() {
		$credentials = $this->getCredentials(self::$networkid);
		$username = $credentials["usr"];
		$password = $credentials["pwd"];
		 
		$url = "http://affiliate.idritracker.com/login.php";
		$postparams ="username=$username&password=$password";
		 
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
	
	public function getHomePageStats() {
		$ch =  $this->getCurlObject();
		$url = "http://affiliate.idritracker.com/logged.php";
		curl_setopt($ch,CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_COOKIEJAR, self::$cookiejar); 
		curl_setopt($ch, CURLOPT_COOKIEFILE, self::$cookiejar); 
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); 
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
		$response = curl_exec($ch);
	
		$dom = str_get_html($response);
		
		$container = $dom->find('ul[id=snapshotContainer]', 0);
		$list = $container->find('li');
		$month = str_replace("Month To Date:", "", $list[1]->innertext);
		$total = str_replace("Lifetime:", "", $list[3]->innertext);
		$month = $this->fixNumber($month);
		$total = $this->fixNumber($total);
	 	
		curl_exec($ch);
		$today = $this->homeStatTotalToday();
		$data = array("Total"=>$total, "Today"=>$today, "This Month"=>$month );
	
		return $data;
	}
	
	private function homeStatTotalToday() {

	   $month = date('n');
	   $day = date('j');
	   $year= date('Y');
	   $cookiejar =self::$cookiejar;
	   
	   $postparams ="smonth=$month&sday=$day&syear=$year&emonth=$month&eday=$day&eyear=$year&quickselect=on&imageField.x=45&imageField.y=30";
	  
	    $ch = $this->getCurlObject ();
		$url = "http://affiliate.idritracker.com/logged.php?pgid=22";
		curl_setopt ( $ch, CURLOPT_URL, $url );
		curl_setopt ( $ch, CURLOPT_COOKIEJAR, $cookiejar );
		curl_setopt ( $ch, CURLOPT_COOKIEFILE, $cookiejar );
		curl_setopt ( $ch, CURLOPT_FOLLOWLOCATION, true );
		curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt ( $ch, CURLOPT_POSTFIELDS, $postparams );
		$response = curl_exec ( $ch );
		 
		$dom = str_get_html ( $response );
		$tr = $dom->find ( 'tr[id=sort_snap_last]', 0 );
		if(null == $tr ) {
			return "0";
		}
		$td = $tr->find ( 'td' );
		$today = $td [count ( $td ) - 1]->innertext;
		$today = str_replace ( "<b>", "", $today );
		$today = str_replace ( "</b>", "", $today );
		$today = $this->fixNumber ( $today );
		curl_exec($ch);
		return $today;
	}
	
	private function getNetworkRecords($date = null) {
		if(null == $date) {
			$date = $this->getCurrentDate();
		}
	
		$cookiejar=self::$cookiejar;
		$networkid=self::$networkid;
	
		list($year,$month,$day) = explode("-", $date);
	
		$ch = $this->getCurlObject();
		$url = "http://affiliate.idritracker.com/rpt/subid_popup.php?subid_currency=1&syear=$year&smonth=$month&sday=$day&eyear=$year&emonth=$month&eday=$day&subid_c1c2c3=&subid_groupby=c1&subid_records=1000&subid_page=1";
		curl_setopt($ch,CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_COOKIEJAR, $cookiejar); 
		curl_setopt($ch, CURLOPT_COOKIEFILE, $cookiejar); 
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); 
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
		$response = curl_exec($ch);
		$dom = str_get_html($response);
		$div = $dom->find('div[id=report_subid_container]', 0); 
		
		$uls = $div->find('ul.y');
		if(null == $uls) {
			return array();
		}
		$ul = $uls[1];
		
		$lis = $ul->find('li');
		
		$recordList = array();
		foreach($lis as $li) {
			$divs = $li->find('div');
			$id = @$divs[1]->plaintext;
			$c1 = @$divs[3]->plaintext;
			$c2 = @$divs[4]->plaintext;
			$c3 = @$divs[5]->plaintext;
			if($id == "1310") {
				continue;
			}
			
			$offername =$c1; 
			$clicks = $this->fixNumber(@$divs[6]->plaintext);
			$leads = $this->fixNumber(@$divs[7]->plaintext);
			$total = $this->fixNumber(@$divs[10]->plaintext);
	
		//echo "$offername \t $clicks \t $leads \t $total \n";
			$payout = 0;
			if($total > 0 && $leads > 0) {
				$payout = $total/$leads;
			}
	
			$o = new NetworkRecord();
			$o->networkId = $networkid;
			$o->offerId = $this->getOfferID($offername);
			$o->activityDate = $date;
			$o->clicks = $clicks;
			$o->leads = $leads;
			$o->payout = $leads;
			$o->total = $total;
			$o->offerName = $offername;
	
			array_push($recordList, $o);
		}
		
		curl_exec($ch);
		return $recordList;
	}
	
	private function getLeadSummary($date = null) {
		if(null == $date) {
			$date = $this->getCurrentDate();
		}
	
		$cookiejar=self::$cookiejar;
		$networkid=self::$networkid;
	
		list($year,$month,$day) = explode("-", $date);
	
		$ch =  $this->getCurlObject();
		$url = "http://affiliate.idritracker.com/rpt/subid_popup.php?subid_currency=1&syear=$year&smonth=$month&sday=$day&eyear=$year&emonth=$month&eday=$day&subid_c1c2c3=&subid_groupby=all&subid_records=5000&subid_page=1";
	
		curl_setopt($ch,CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_COOKIEJAR, $cookiejar); 
		curl_setopt($ch, CURLOPT_COOKIEFILE, $cookiejar); 
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); 
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
		$response = curl_exec($ch);
		$dom = str_get_html($response);
		
		$div = $dom->find('div[id=report_subid_container]', 0); 
		$uls = $div->find('ul.y');
		$ul = $uls[1];
		$lis = $ul->find('li');
		
		$recordList = array();
		foreach($lis as $li) {
			$divs = $li->find('div');
			$id = @$divs[1]->plaintext;
			$c1 = @$divs[3]->plaintext;
			$c2 = @$divs[4]->plaintext;
			$c3 = @$divs[5]->plaintext;
			if($id == "1310") {
				list($offername, $activityId, $sbtid) = explode("|", $c1);
				$o = new NetworkRecord();
				$o->sbtID=$sbtid;
				$o->type="foreing";
				array_push($recordList, $o);
				continue;
	
			}
			
			$offername =$c1;
			$activityId = $c2;
			$sbtid = $c3;
			$clicks =  $this->fixNumber(@$divs[6]->plaintext);
			$leads = $this->fixNumber(@$divs[7]->plaintext);
			$total = $this->fixNumber(@$divs[10]->plaintext);
			if($total == 0) {
				continue;
			}
			
		//echo "$offername \t $clicks \t $leads \t $total \n";
			$payout = 0;
			if($total > 0 && $leads > 0) {
				$payout = $total/$leads;
			}
	
			$o = new NetworkRecord();
			$o->networkId = $networkid;
			$o->offerId = $this->getOfferID($offername);
			$o->activityDate = $date;
			$o->clicks = $clicks;
			$o->leads = $leads;
			$o->payout = $leads;
			$o->total = $total;
			$o->offerName = $offername;
			$o->sbtID=$sbtid;
			$o->activityID=$activityId;
	
			array_push($recordList, $o);
		}
		
		curl_exec($ch);
		return $recordList;
	}

}
  






?>