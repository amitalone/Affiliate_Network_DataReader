<?php
interface iNetwork
{
    public function importRecord($date =null);
    public function syncToPreviousDate();
	public function getLatestStats();
	public function getLatestConversions();
	public function syncConversionsToPreviousDate();
	public function adjustUnsetledAmount();
	public function importLeadSummary($date =null);
	public function getHomePageStats();
}
?>