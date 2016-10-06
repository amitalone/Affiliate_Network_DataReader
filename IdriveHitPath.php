<?php
require_once('BaseNetwork.php');
require_once('INetwork.php');

class IdriveHitPath extends BaseNetwork implements iNetwork {
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
		 
		$records = $this->getNetworkRecords($date);
		$this->uploadRecords($records);
		}

	public function syncToPreviousDate() {
		//echo "syncToPreviousDate()";
		$this->cleanYesterdayNetworkRecords(self::$networkid);
		$this->importRecord($this->getYesterday());
	}

	public function getLatestStats() {
		$this->cleanTodayNetworkRecords(self::$networkid);
		$this->importRecord();
	}

	public function getLatestConversions(){
		$this->cleanTodayConversions(self::$networkid);
		$this->importLeadSummary($this->getCurrentDate());
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
		$records = $this->getLeadSummary($date);
		$this->uploadConversionRecords($records);
	}
	
	private function login() {
		$credentials = $this->getCredentials(self::$networkid);
		$username = $credentials["usr"];
		$password = $credentials["pwd"];
		 
		$url = "http://affiliate.idrivecontrol.com/process.php";
		$postparams ="utype=agent&action=login&uname=$username&pword=$password";
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
		$url = "http://affiliate.idrivecontrol.com/dashboard/module/global_totals2x1.php?id=1583";
		curl_setopt($ch,CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_COOKIEJAR, self::$cookiejar); 
		curl_setopt($ch, CURLOPT_COOKIEFILE, self::$cookiejar); 
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); 
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
		$response = curl_exec($ch);
		
		 
		$dom = str_get_html($response);
		$divs=$dom->find("div.db_2x1_tile_label");
		foreach($divs as $div) {
			
			if($div->plaintext == "Earnings"){
				$div = $div->next_sibling();
				$today = $this->fixNumber($div->plaintext);
			}
			if($div->plaintext == "Lifetime"){
				$div = $div->next_sibling();
				$total = $this->fixNumber($div->plaintext);
			}
			if($div->plaintext == "Month to Date"){
				$div = $div->next_sibling();
				 
				$month = $this->fixNumber($div->plaintext);
			}
			 
				
		}

	 
		curl_exec($ch);
		$data = array("Total"=>$total, "Today"=>$today, "This Month"=>$month );
		return $data;

	}
	 
	
	public function getNetworkRecords($date) {
		//echo "getNetworkRecords()";
		$cookiejar=self::$cookiejar;
		$networkid=self::$networkid;
	
		list($year,$month,$day) = explode("-", $date);
		$udate = "$month/$day/$year";
		$udate = urlencode($udate);
	
		$ch = $this->getCurlObject();
		$url = "http://affiliate.idrivecontrol.com/report/subid.php";
		$postparams ="dateStart=$udate&dateEnd=$udate&curr_summary=%24+US+Dollars&curr_summary_default=%24+US+Dollars&filter_curr_value=&subid_summary=Sub+ID+Codes&subid_summary_default=Sub+ID+Codes&subid_c1_nofilter=on&subid_c1_input=&subid_c2_nofilter=on&subid_c2_input=&subid_c3_nofilter=on&subid_c3_input=&subid_campaign_nofilter=on&subid_campaign_input=&subid_c1c2c3_input=&subid_group_by=c1&rpp_summary=1000+Records+Per+Page&rpp_summary_default=1000+Records+Per+Page&filter_rpp_value=3&data_type=SCREEN&paging_page_0=0&paging_rpp_0=5000";

		curl_setopt($ch,CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_COOKIEJAR, $cookiejar); 
		curl_setopt($ch, CURLOPT_COOKIEFILE, $cookiejar); 
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); 
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
		curl_setopt($ch, CURLOPT_POSTFIELDS, $postparams);
		$response = curl_exec($ch);
		 
		$dom = str_get_html($response);
		 
		$div = $dom->find('div[id=contentCanvas]', 0); 
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
			//$c2 = @$divs[4]->plaintext;
			//$c3 = @$divs[5]->plaintext;
			if($id == "1310") {
			//	continue;
			}
			
			
			$offername =$c1;
			$offername =  trim($offername);
			$position =FALSE ;
			$position = strpos($offername, '&');
			if($position !==FALSE) {
				$offername = substr($offername, 0, $position);
			}

			
			$clicks = $this->toInteger($this->fixNumber(@$divs[6]->plaintext));
			$leads = $this->toInteger($this->fixNumber(@$divs[7]->plaintext));
			$total = $this->fixNumber(@$divs[10]->plaintext);
			$clicks = str_replace(",", "", $clicks);
			//echo "$offername --- $total \n";
			
	
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
			//echo "$total \n";
			$o->offerName = $offername;
			 
			array_push($recordList, $o);
		}
		 
		curl_exec($ch);
		return $recordList; 
	}
	
	public function getLeadSummary($date) {
	
		$cookiejar=self::$cookiejar;
		$networkid=self::$networkid;
	
		list($year,$month,$day) = explode("-", $date);
		$udate = "$month/$day/$year";
		$udate = urlencode($udate);
	
		$ch = $this->getCurlObject();
		$url = "http://affiliate.idrivecontrol.com/report/subid.php";
		$postparams ="dateStart=$udate&dateEnd=$udate&curr_summary=%24+US+Dollars&curr_summary_default=%24+US+Dollars&filter_curr_value=&subid_summary=Sub+ID+Codes&subid_summary_default=Sub+ID+Codes&subid_c1_nofilter=on&subid_c1_input=&subid_c2_nofilter=on&subid_c2_input=&subid_c3_nofilter=on&subid_c3_input=&subid_campaign_nofilter=on&subid_campaign_input=&subid_c1c2c3_input=&subid_group_by=all&rpp_summary=1000+Records+Per+Page&rpp_summary_default=1000+Records+Per+Page&filter_rpp_value=3&data_type=SCREEN&paging_page_0=0&paging_rpp_0=5000";

		curl_setopt($ch,CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_COOKIEJAR, $cookiejar); 
		curl_setopt($ch, CURLOPT_COOKIEFILE, $cookiejar); 
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); 
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
		curl_setopt($ch, CURLOPT_POSTFIELDS, $postparams);
		$response = curl_exec($ch);
		 
		$dom = str_get_html($response);

		
		$div = $dom->find('div[id=contentCanvas]', 0); 
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
			
			/*if($id == "1310") {
				list($offername, $activityId, $sbtid) = explode("|", $c1);
				$o = new NetworkRecord();
				$o->sbtID=$sbtid;
				$o->type="foreing";
				array_push($recordList, $o);
				continue;
	
			}*/
			
			 $offername = $c1;
			$position = strpos($offername, '&');
			
			if($position !==FALSE) {
				$oldoffer = $offername;
				$offername = substr($offername, 0, $position);

				$oldoffer = substr($oldoffer, $position+1);
				list($c2, $c3) = explode("&", $oldoffer);
				list($x, $c2) = explode("=", $c2);
				list($x, $c3) = explode("=", $c3);
			} 


			$activityId = $c2;
			$sbtid = $c3;
			// GROUP BY WILL ALWAYS RESULT IN SINGLE CLICK.
			$clicks = 1;
			$leads = 1;
			$total = $this->fixNumber(@$divs[10]->plaintext);
			if($total == 0) {
				continue;
			} 
			
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
		//print_r($recordList);
		return $recordList;
	}
	

}
  






?>