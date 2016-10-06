<?php
require_once ('BaseNetwork.php');
require_once ('INetwork.php');

class CPABuilder extends BaseNetwork implements iNetwork {
	static $networkid = "3";
	static $cookiejar = "";
	
	function __construct() {
		$nid = self::$networkid;
		self::$cookiejar = "cookies/_____$nid.txt";
		$this->login ();
	
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
	
	public function importRecord($date = null) {
		if (null == $date) {
			$date = $this->getCurrentDate ();
		}
	
		$records = $this->getNetworkRecords($date);
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
		if (null == $date) {
			$date = $this->getCurrentDate ();
		}
	
		$records = $this->getLeadSummary($date);
		$this->uploadConversionRecords($records);
	}
	public function getHomePageStats() {
		$ch =  $this->getCurlObject();
		$url = "http://affiliate.gwmtracker.com/logged.php?pgid=22";
		curl_setopt($ch,CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_COOKIEJAR, self::$cookiejar); 
		curl_setopt($ch, CURLOPT_COOKIEFILE, self::$cookiejar); 
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); 
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
		$response = curl_exec($ch);
	
		$dom = str_get_html($response);
		$container = $dom->find('div.snapshotContainer', 0);
		$table = $container->find('table', 0);
		$cells = $table->find('td');
		$month = str_replace("Month To Date:", "", $cells[3]->innertext);
		$total = str_replace("Lifetime:", "", $cells[7]->innertext);

		$month = $this->fixNumber($month);
		$total = $this->fixNumber($total);
		$today = $this->fixNumber($this->homeStatTotalToday());
		curl_exec($ch);
		$data = array("Total"=>$total, "Today"=>$today, "This Month"=>$month );
	
		return $data;
	}
	
	private function login() {
		$credentials = $this->getCredentials ( self::$networkid );
		$username = urldecode($credentials ["usr"]);
		$password = $credentials ["pwd"];
		
		$url = "http://affiliate.gwmtracker.com/login.php";
		$postparams = "username=$username&password=$password";
		
		$ch = $this->getCurlObject ();
		curl_setopt ( $ch, CURLOPT_URL, $url );
		curl_setopt ( $ch, CURLOPT_COOKIEJAR, self::$cookiejar );
		curl_setopt ( $ch, CURLOPT_VERBOSE, 0 );
		curl_setopt ( $ch, CURLOPT_HEADER, 1 );
		curl_setopt ( $ch, CURLINFO_HEADER_OUT, true );
		curl_setopt ( $ch, CURLOPT_COOKIESESSION, TRUE );
		curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, TRUE );
		curl_setopt ( $ch, CURLOPT_POSTFIELDS, $postparams );
		curl_exec   ( $ch );
		curl_close  ( $ch );
	}
	
	private function homeStatTotalToday() {
	   $month = date('n');
	   $day = date('j');
	   
	   $year= date('Y');
	   $cookiejar =self::$cookiejar;
	   
	    $postparams ="smonth=$month&sday=$day&syear=$year&emonth=$month&eday=$day&eyear=$year&quickselect=on&imageField.x=38&imageField.y=16";
	  
	    $ch = $this->getCurlObject ();
		$url = "http://affiliate.gwmtracker.com/logged.php?pgid=22";
		curl_setopt ( $ch, CURLOPT_URL, $url );
		curl_setopt ( $ch, CURLOPT_COOKIEJAR, $cookiejar );
		curl_setopt ( $ch, CURLOPT_COOKIEFILE, $cookiejar );
		curl_setopt ( $ch, CURLOPT_FOLLOWLOCATION, true );
		curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt ( $ch, CURLOPT_POSTFIELDS, $postparams );
		$response = curl_exec ( $ch );
		$dom = str_get_html ( $response );
		$tr = $dom->find ( 'tr[id=sort_snap_last]', 0 );
		$today = "0";
		if(null != $tr) {
			$td = $tr->find ( 'td' );
			$today = $td [count ( $td ) - 1]->innertext;
			$today = str_replace ( "<b>", "", $today );
			$today = str_replace ( "</b>", "", $today );
			$today = $this->fixNumber ( $today );
		}
		
		
		curl_exec($ch);
		return $today;
	}
	
	private function getNetworkRecords($date) {
	
		$cookiejar=self::$cookiejar;
		$networkid=self::$networkid;
	
		list($year,$month,$day) = explode("-", $date);
	
		$ch = $this->getCurlObject();
		$url = "http://affiliate.gwmtracker.com/rpt/subid_popup.php?subid_currency=1&syear=$year&smonth=$month&sday=$day&eyear=$year&emonth=$month&eday=$day&subid_c1c2c3=&subid_groupby=c1&subid_records=1000&subid_page=1";
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
			
			if (strpos($c1, '|') !== FALSE){
				continue;
			}
			
			$offername =$c1; 
			$clicks = $this->toInteger($this->fixNumber(@$divs[6]->plaintext));
			$leads = $this->toInteger($this->fixNumber(@$divs[7]->plaintext));
			$total = $this->fixNumber(@$divs[10]->plaintext);
	
		//echo "$offername \t $clicks \t $leads \t $total \n";
			$payout = 0;
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
	
			array_push($recordList, $o);
		}
		
		curl_exec($ch);
		return $recordList;
	} 
	
	private function getLeadSummary($date) {
	
		$cookiejar=self::$cookiejar;
		$networkid=self::$networkid;
	
		list($year,$month,$day) = explode("-", $date);
	
		$ch =  $this->getCurlObject();
		$url = "http://affiliate.gwmtracker.com/rpt/subid_popup.php?subid_currency=1&syear=2014&smonth=8&sday=15&eyear=2014&emonth=9&eday=4&subid_c1c2c3=&subid_groupby=all&subid_records=5000&subid_page=1";
	
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
			if (strpos($c1, '|') !== FALSE){
				continue;
			}
			
			$offername =$c1;
			$activityId = $c2;
			$sbtid = $c3;
			// GROUP BY WILL ALWAYS RESULT IN SINGLE CLICK.
			$clicks = 1;
			$leads = 1;
			$total = $this->fixNumber(@$divs[10]->plaintext);
			if($total == 0) {
				continue;
			}
		//echo "$offername \t $clicks \t $leads \t $total \n";
			$payout = 0;
			if($total > 0 && $leads > 0) {
				$payout = $total/$leads;
				$payout =$this->fixNumber($payout);
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
			$o->activityID=$activityId;
	
			array_push($recordList, $o);
		}
		
		curl_exec($ch);
		return $recordList;
	}
}

?>