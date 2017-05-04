<?

//debug
error_reporting(E_ALL);

include('Db.php');

class convertZip extends Db {
	//protected $CSVFile = 'free-zipcode-database-Primary.csv';
	protected $CSVFile = 'zipcode.csv';
	protected $delimiter = ',';
	protected $fieldNames = array();
	protected $records = array();
	
	public function __construct() {
		parent::__construct();
		ini_set("auto_detect_line_endings", true);
		$this->loadCSV();
		echo $this->getCreateTableSQL('zipcodes');
		$this->insertRecords();		
	}

	protected function loadCSV() {
		if (($handle = fopen($this->CSVFile, "r")) !== false) {
			$this->fieldNames = fgetcsv($handle, 20000, $this->delimiter);
			while (($record = fgetcsv($handle, 20000, $this->delimiter)) !== false) {
				$array = array();
				$index = 0;
				foreach ($this->fieldNames as $fieldName) {
					$array[$fieldName] = $record[$index];
					$index++;
				}
				$this->records[]=$array;
			}
		}
	}

	//generates a simple 'create table xxx based on field names
	protected function getCreateTableSQL($tablename) {
		$SQL='';
		foreach ($this->fieldNames as $fieldName) {
			if ($SQL!='') $SQL.=','."<br>";			
			$SQL.=$fieldName.'  varchar(20)';
		}
		$SQL.=')';
		$SQL='create table '.$tablename.' ('.$SQL;
		return $SQL;

	}

	private function insertRecords() {
		foreach($this->records as $record) {
			if (count($this->fieldNames)!=count($record)) {
				$this->debug($record);
				exit;
			}
			$this->debug($record);
			$SQL='insert into zipcodes set ';
			foreach ($record as $field => $value) {
				$SQL.=$field.'='.$this->q($value);
			}
			$SQL=substr_replace($SQL ,"",-1);
			echo $SQL;
			$this->exec($SQL);
		}
	}

}

$zip = new convertZip();
?>
