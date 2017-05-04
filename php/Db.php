<?php

class Db {
	private $database;
	private $hostname;
	private $username;
	private $password;
	private $link;
	protected $last_execution_time;
  
	public static function getInstance(){
		static $db = null;
		if ( $db == null ) $db = new Db();
		return $db;
	}

	public function __construct() { 
		
			//your credentials here
			$this->database = 'map-test';
			$this->hostname = 'localhost';
			$this->username = 'root';
			$this->password = 'root';

		try {
			$this->link=mysqli_connect($this->hostname,
						  $this->username,
						  $this->password);
			if ($this->link->connect_error) {
				die('Databaseforbindelse mislykkedes : '.$this->link->connect_error);
			} else {
				mysqli_select_db ($this->link,$this->database);
			}

		} catch (Exception $e){
			throw new Exception('Databaseforbindelse mislykkedes ..');
			exit;
 		}
	}

	public function exec($SQL) {
		mysqli_query($this->link,$SQL);
	}

	public function query($SQL) {
		$ms=microtime(true);
		$result=mysqli_query($this->link, $SQL);
		$ms=microtime(true)-$ms;
		$this->last_execution_time=($ms*1000).' ms.';
		return $result;
	}

	public function getRow($SQL) {
		$result=mysqli_query($this->link, $SQL);
		$result=mysqli_fetch_array($result);
		return $result;
	}

	public function hasData($SQL) {
		$result=mysqli_query($this->link, $SQL);
		return is_array(@mysqli_fetch_array($result));
	}

	public function getRecCount($table) {
		$SQL='select count(*) from '.$table;
		$count=$this->getRow($SQL);
		return $count[0];
	}		

	public function affected_rows() {
		return mysqli_affected_rows($this->link);
	}

	public function lastIndex() {
		return mysqli_insert_id($this->link);
	}

	public function q($string, $comma = true) {
		$string=mysqli_real_escape_string($string, $this->link);
		return $comma ? '"'.$string.'",' : '"'.$string.'"';
	}

	public function isLocalHost() {
		$host = $_SERVER["SERVER_ADDR"]; 
		return (($host=='127.0.0.1') || ($host=='::1'));
	}

	public function setCharset() {
		if ($this->isLocalHost()) {
			mysqli_set_charset('utf8');
		} else {
			mysqli_set_charset('utf8');
		}
	}

	//extra
	public function debug($a) {
		echo '<pre>';
		print_r($a);
		echo '</pre>';
	}

	public function removeLastChar($s) {
		return substr_replace($s ,"", -1);
	}

	public function fileDebug($text) {
		$fh = fopen($this->debugFile, 'a') or die();
		fwrite($fh, $text."\n");
		fclose($fh);
	}

	public function resetDebugFile() {
		$fh = fopen($this->debugFile, 'a') or die();
		ftruncate($fh, 0);
		fclose($fh);
	}

}

?>
