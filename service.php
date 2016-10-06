<?php 
	require_once 'Idrive.php';
	require_once 'IdriveAC2.php';
	require_once 'ClickBooth.php';
	require_once 'CPABuilder.php';
	require_once 'MyMonetise.php';
	require_once 'FluxAds.php';
	require 'B2Direct.php';
	require_once 'Idrive1CK.php';
	require 'IdriveHitPath.php';
	
 
	
	

	$cmd = $_GET['cmd'];
	$nwid =@$_GET['nwid'];
	
	$svr = new Service();
	$svr->handleCommand($cmd, $nwid);
    
    class Service {
    	private static $CMD_GET_LATEST = "latest";
	    private static $CMD_SYNC_TO_PREV_DATE = "syncback";
	    private static $CMD_HOME_STAT = "homestat";
	    private static $CMD_CONVERSION_LATEST = "convlatest";
	    private static $CMD_CONVERSION_SYNC_TO_PREV_DATE = "convsyncback";
		private static $CMD_GET_TIME_STATS = "timestats";
		private static $CMD_GET_TIME_STATS_15 = "timestats15";
	    private static $METHOD_EXEC_FREQUENCY_SECOND = 300;
	    
	    private $clickBoothObj = null;
	    private $idriveObj = null;
	    private $idriveAc2Obj = null;
	    private $cpaBuilderObj = null;
	    private $myMonetiseObj = null;
	    private $fluxAdsObj = null;
		private $b2DirectObj = null;
		private $idriveCKObj = null;
	    
	    
	    public  function handleCommand($cmd, $nwid) {

	    	if("cb" ==$nwid) {
	    		$this->clickBoothObj = new ClickBooth();
	    	}
	    	if("cpa" ==$nwid) {
	    		$this->cpaBuilderObj = new CPABuilder();
	    	}
	    	if("idr" ==$nwid) {
	    		$this->idriveObj = new IdriveHitPath();
	    	}
	    	if("idr2" ==$nwid) {
	    		$this->idriveAc2Obj = new IdriveAC2();
	    	}
	    	if("fa" ==$nwid) {
	    		$this->fluxAdsObj = new FluxAds();
	    	}
	    	if("mm" ==$nwid) {
	    		$this->myMonetiseObj = new MyMonetise();
	    	}
			if("b2d" ==$nwid) {
	    		$this->b2DirectObj = new B2Direct();
	    	}

			if("idck" ==$nwid) {
	    		$this->idriveCKObj = new Idrive1CK();
	    	}

	    	
	    	// GET LATEST Conversions
	    	if(self::$CMD_CONVERSION_LATEST == $cmd) {
	    		
	    		 
	    		 
	    		if(null != $this->clickBoothObj) {
	    			
	    			$this->clickBoothObj->getLatestConversions();
	    		}
		    	if(null != $this->idriveObj) {
		    		$this->idriveObj->getLatestConversions();
		    	}
	    		if(null != $this->idriveAc2Obj) {
		    		$this->idriveAc2Obj->getLatestConversions();
		    	}
	    		if(null != $this->cpaBuilderObj) {
		    		$this->cpaBuilderObj->getLatestConversions();
		    	}
	    		if(null != $this->myMonetiseObj) {
		    		$this->myMonetiseObj->getLatestConversions();
		    	}
	    		if(null != $this->fluxAdsObj) {
		    		$this->fluxAdsObj->getLatestConversions();
		    	}
				if(null != $this->b2DirectObj) {
		    		$this->b2DirectObj->getLatestConversions();
		    	}
				if(null != $this->idriveCKObj) {
		    		$this->idriveCKObj->getLatestConversions();
		    	}
 
	    	}
	    	
	    	// Get Latest Stats
	    	if(self::$CMD_GET_LATEST == $cmd) {
	    		 
	    	 
	    		if(null != $this->clickBoothObj) {
		    		if(MemoryOfferTable::getInstance()->isCommandLocked("CLICK_BOOTH_GET_LATEST")) {return;}
		    		else {
		    			MemoryOfferTable::getInstance()->lockCommand("CLICK_BOOTH_GET_LATEST", self::$METHOD_EXEC_FREQUENCY_SECOND);
		    		}
		    		
		    		try {$this->clickBoothObj->getLatestStats();} catch (Exception $e) {};
	    		}
	    		 
		    	if(null != $this->idriveObj) {
		    		if(MemoryOfferTable::getInstance()->isCommandLocked("IDRIVE_GET_LATEST")) {return;}
		    		else {
		    			MemoryOfferTable::getInstance()->lockCommand("IDRIVE_GET_LATEST", self::$METHOD_EXEC_FREQUENCY_SECOND);
		    		}
		    		try {$this->idriveObj->getLatestStats();} catch (Exception $e) {};
		    	}
	    		if(null != $this->idriveAc2Obj) {
	    			if(MemoryOfferTable::getInstance()->isCommandLocked("IDRIVEAC2_GET_LATEST")) {return;}
		    		else {
		    			MemoryOfferTable::getInstance()->lockCommand("IDRIVEAC2_GET_LATEST", self::$METHOD_EXEC_FREQUENCY_SECOND);
		    		}
		    		try {$this->idriveAc2Obj->getLatestStats();} catch (Exception $e) {};
		    	}
	    		if(null != $this->cpaBuilderObj) {
	    			if(MemoryOfferTable::getInstance()->isCommandLocked("CPA_BUILDER_GET_LATEST")) {return;}
		    		else {
		    			MemoryOfferTable::getInstance()->lockCommand("CPA_BUILDER_GET_LATEST", self::$METHOD_EXEC_FREQUENCY_SECOND);
		    		}
		    		try {$this->cpaBuilderObj->getLatestStats();} catch (Exception $e) {};
		    	}
	    		if(null != $this->myMonetiseObj) {
	    			if(MemoryOfferTable::getInstance()->isCommandLocked("MYMONETISE_GET_LATEST")) {return;}
		    		else {
		    			MemoryOfferTable::getInstance()->lockCommand("MYMONETISE_GET_LATEST", self::$METHOD_EXEC_FREQUENCY_SECOND);
		    		}
		    		try {$this->myMonetiseObj->getLatestStats();} catch (Exception $e) {};
		    	}
	    		if(null != $this->fluxAdsObj) {
	    			if(MemoryOfferTable::getInstance()->isCommandLocked("FLUX_ADS_GET_LATEST")) {return;}
		    		else {
		    			MemoryOfferTable::getInstance()->lockCommand("FLUX_ADS_GET_LATEST", self::$METHOD_EXEC_FREQUENCY_SECOND);
		    		}
		    		try {$this->fluxAdsObj->getLatestStats();} catch (Exception $e) {};
		    	}
				 
				if(null != $this->b2DirectObj) {
	    			if(MemoryOfferTable::getInstance()->isCommandLocked("B2DIRECT_GET_LATEST")) {return;}
		    		else {
		    			MemoryOfferTable::getInstance()->lockCommand("B2DIRECT_GET_LATEST", self::$METHOD_EXEC_FREQUENCY_SECOND);
		    		}
				 
		    		try {$this->b2DirectObj->getLatestStats();} catch (Exception $e) {error_log($e);};
		    	}

				if(null != $this->idriveCKObj) {
	    		 
				 
		    		try {$this->idriveCKObj->getLatestStats();} catch (Exception $e) {error_log($e);};
		    	}

				
	    	}
	    	
	    	
	    // Sync conversion data to previous dates
	    	if(self::$CMD_HOME_STAT == $cmd) {
	    		if(null != $this->clickBoothObj) {
	    			echo json_encode($this->clickBoothObj->getHomePageStats());
	    		}
		    	if(null != $this->idriveObj) {
		    		echo json_encode($this->idriveObj->getHomePageStats());
		    	}
	    		if(null != $this->idriveAc2Obj) {
		    		echo json_encode($this->idriveAc2Obj->getHomePageStats());
		    	}
	    		if(null != $this->cpaBuilderObj) {
		    		echo json_encode($this->cpaBuilderObj->getHomePageStats());
		    	}
	    		if(null != $this->myMonetiseObj) {
		    		echo json_encode($this->myMonetiseObj->getHomePageStats());
		    	}
	    		if(null != $this->fluxAdsObj) {
		    		echo json_encode($this->fluxAdsObj->getHomePageStats());
		    	}

				if(null != $this->b2DirectObj) {
		    		echo json_encode($this->b2DirectObj->getHomePageStats());
		    	}
				if(null != $this->idriveCKObj) {
		    		echo json_encode($this->idriveCKObj->getHomePageStats());
		    	}

				
	    	}
	    	
	    // Sync data to previous dates
	    	if(self::$CMD_SYNC_TO_PREV_DATE == $cmd) {
	    		$this->clickBoothObj = new ClickBooth();
				$this->cpaBuilderObj = new CPABuilder();
				$this->idriveObj = new IdriveHitPath();
				$this->idriveAc2Obj = new IdriveAC2();
				$this->fluxAdsObj = new FluxAds();
				$this->b2DirectObj = new B2Direct();
				$this->idriveCKObj = new Idrive1CK();


				$this->myMonetiseObj = new MyMonetise();

	    		try {$this->clickBoothObj->syncToPreviousDate();} catch (Exception $e) {};
	    		try {$this->idriveObj->syncToPreviousDate();} catch (Exception $e) {};
				try {$this->cpaBuilderObj->syncToPreviousDate();} catch (Exception $e) {};
	    		try {$this->b2DirectObj->syncToPreviousDate();} catch (Exception $e) {};
				
				
	    	}
	    	
	    	// Sync conversion data to previous dates
	    	if(self::$CMD_CONVERSION_SYNC_TO_PREV_DATE == $cmd) {
	    		$this->clickBoothObj = new ClickBooth();
				$this->cpaBuilderObj = new CPABuilder();
				$this->idriveObj = new IdriveHitPath();
				$this->idriveAc2Obj = new IdriveAC2();
				$this->fluxAdsObj = new FluxAds();
				$this->myMonetiseObj = new MyMonetise();
				$this->b2DirectObj = new B2Direct();
				$this->idriveCKObj = new Idrive1CK();

				
				try {$this->clickBoothObj->syncConversionsToPreviousDate();} catch (Exception $e) {};
				try {$this->idriveObj->syncConversionsToPreviousDate();} catch (Exception $e) {};
				try {$this->cpaBuilderObj->syncConversionsToPreviousDate();} catch (Exception $e) {};
				try {$this->b2DirectObj->syncConversionsToPreviousDate();} catch (Exception $e) {};
			 
	    	}

			if(self::$CMD_GET_TIME_STATS == $cmd) {
	    		$this->clickBoothObj = new ClickBooth();
				$this->cpaBuilderObj = new CPABuilder();
				$this->idriveObj = new IdriveHitPath();
				$this->idriveAc2Obj = new IdriveAC2();
				$this->myMonetiseObj = new MyMonetise();
				$this->b2DirectObj = new B2Direct();
				$this->idriveCKObj = new Idrive1CK();

	    		try {$this->clickBoothObj->getCurrentTimeStats();} catch (Exception $e) {};
	    		try {$this->idriveObj->getCurrentTimeStats();} catch (Exception $e) {};
	    		try {$this->b2DirectObj->getCurrentTimeStats();} catch (Exception $e) {};
				try {$this->cpaBuilderObj->getCurrentTimeStats();} catch (Exception $e) {};
 
	    	}

			if(self::$CMD_GET_TIME_STATS_15 == $cmd) {
	    		$this->clickBoothObj = new ClickBooth();
				$this->cpaBuilderObj = new CPABuilder();
				$this->idriveObj = new IdriveHitPath();
				$this->idriveAc2Obj = new IdriveAC2();
				$this->myMonetiseObj = new MyMonetise();
				$this->b2DirectObj = new B2Direct();
				$this->idriveCKObj = new Idrive1CK();

	    		try {$this->clickBoothObj->getCurrentTimeStats15();} catch (Exception $e) {};
	    		try {$this->idriveObj->getCurrentTimeStats15();} catch (Exception $e) {};
	    		try {$this->cpaBuilderObj->getCurrentTimeStats15();} catch (Exception $e) {};
				try {$this->b2DirectObj->getCurrentTimeStats15();} catch (Exception $e) {};

	 
	    	}
	    	
	    	
	    } 
	    
	    
    }
?>