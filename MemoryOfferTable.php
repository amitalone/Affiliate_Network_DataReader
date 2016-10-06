<?php
	class MemoryOfferTable {
		protected static $_instance;
		private static $_memObject;
		public static function getInstance() 
		{
			if (self::$_instance === NULL)
			{
				self::$_instance = new MemoryOfferTable();
				self::$_memObject =  new Memcached();
				self::$_memObject->addServer('localhost', 11211);
			}
	 
			return self::$_instance;
		}
		
		final protected function __construct() { }
		final protected function __clone() { }

		public function getOfferID($offerName) {
			$offerID = self::$_memObject->get($offerName);
			if(null == $offerID) {
				$url = "service.php?cmd=getofferid&oname=$offerName";
				$id = file_get_contents($url);
				$id = trim($id);
				self::$_memObject->set($offerName, $id);
				$offerID =$id; 
			}
			return $offerID;
		}
		
		public function lockCommand($commandKey, $expire) {
			self::$_memObject->set($commandKey, $commandKey, $expire);
		}
		
		public function isCommandLocked($commandKey) {
			if(null ==  self::$_memObject->get($commandKey) ) {
				return false;
			}
			return true;
		}
	}
?>