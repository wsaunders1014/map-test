<?php 
error_reporting( E_ALL );
include('Db.php');

class LatLng {
    public $lat;
    public $lng;

    function LatLng($lat, $lng) {
        $this->lat = $lat;
        $this->lng = $lng;
    }
}

class Zip extends Db {
	protected $polygon = array();
	protected $result;

	public function __construct() {
		parent::__construct();
		$this->createPolygon();
		$this->getZipcodesRaw('zipcodes');
		$this->getZipcodesInPolygon();
	}

	protected function createPolygon() {
		$latLngs=$_GET['latlngs'];
		$latLngs=explode(']', $latLngs);
		foreach ($latLngs as $latLng) {
			$latLng=str_replace(array('[',']'), '', $latLng);
			if ($latLng!='') {
				$latLng=explode(',', $latLng);
				$this->polygon[]=new LatLng($latLng[0], $latLng[1]);
			}
		}
	}

	protected function getZipcodesRaw($table) {
		$latmin=85;
		$latmax=-85;
		$lngmin=180;
		$lngmax=-180;

		foreach ($this->polygon as $latLng) {
			if ($latmin>$latLng->lat) $latmin=$latLng->lat;
			if ($latmax<$latLng->lat) $latmax=$latLng->lat;
			if ($lngmin>$latLng->lng) $lngmin=$latLng->lng;
			if ($lngmax<$latLng->lng) $lngmax=$latLng->lng;
		}

		switch ($table) {
			case 'zip' :
				$param= '('.
					'(Lat>'.$latmin.') and '.
					'(Lat<'.$latmax.') and '.
					'(Lng>'.$lngmin.') and '.
					'(Lng<'.$lngmax.') '.

				')';
				$SQL='SELECT Zipcode, Lat, Lng FROM zip WHERE '.$param;
				break;

			case 'zipcodes' :
				$param= '('.
					'(latitude>'.$latmin.') and '.
					'(latitude<'.$latmax.') and '.
					'(longitude>'.$lngmin.') and '.
					'(longitude<'.$lngmax.') '.
				')';
				$SQL='SELECT zip, latitude, longitude FROM zipcodes WHERE '.$param;
				break;

			default :
				break;			

		}

		$this->result=$this->query($SQL);
	}

	protected function getZipcodesInPolygon() {
		$zipcodes='';
		//echo print_r($this->result);
		while ($row = mysqli_fetch_assoc($this->result)) {
			$lat=$row['latitude'];
			$lng=$row['longitude'];
			if ($this->pointInPolygon(new LatLng($lat, $lng), $this->polygon)) {
				if ($zipcodes!='') $zipcodes.=', ';
				$zipcodes.=$row['zip'];
			}
		}
		echo $zipcodes;
	}

	protected function pointInPolygon($point, $polygon) {
		$c = 0;
		$p1 = $polygon[0];
		$n = count($polygon);

		for ($i=1; $i<=$n; $i++) {
			$p2 = $polygon[$i % $n];
			if ($point->lng > min($p1->lng, $p2->lng)
				&& $point->lng <= max($p1->lng, $p2->lng)
				&& $point->lat <= max($p1->lat, $p2->lat)
				&& $p1->lng != $p2->lng) {
					$xinters = ($point->lng - $p1->lng) * ($p2->lat - $p1->lat) / ($p2->lng - $p1->lng) + $p1->lat;
					if ($p1->lat == $p2->lat || $point->lat <= $xinters) {
						$c++;
					}
			}
			$p1 = $p2;
		}
		return $c%2!=0;
	}

}

$zip = new Zip();


?>
