<!doctype html>
<html>
<head>
<script type="text/javascript" src="http://maps.googleapis.com/maps/api/js?key=AIzaSyAOj0_u0DRE2dK8X9YptdCXtxt89UCqfoo"></script>
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js"></script>
<style>
body {
	font-family : helvetica, arial;
}
button {
	padding: 5px;
	font-size: 120%;
}
textarea {
	float: left;
	clear: none;
	width: 200px;
	height: 360px;
	margin-left: 10px;
}
#map-canvas {
	width:700px;
	height:400px;
	float: left;
	clear: none;
}
</style>
</head>
<body>
<h3>Click on the map to create a polygon - then click "search" to find zipcodes</h3>
<button id="search">Search</button><button id="draw">Draw</button><button id="circle">Circle</button><button id="clear">Clear</button>
<hr>
<div id="map-canvas"></div>
&nbsp;&nbsp;zipcodes :<br>
<textarea id="zipcodes"></textarea>

<script>
var usaCenter = new google.maps.LatLng(39.8106460, -98.5569760);
var poly, map, move, drawPolygonMouseupHandler,mouseDownHandler, newShape, maskShape,polygons=[];
var searchCoords=[];
google.maps.event.addDomListener(window,'load', createMap)
function createMap() {
	map = new google.maps.Map(document.getElementById("map-canvas"), {
			center: usaCenter,
            zoom: 4,
            gestureHandling: 'auto',
            fullscreenControl: false,
            zoomControl: false,
            disableDoubleClickZoom: false,
            mapTypeControl: false,
            scaleControl: true,
            scrollwheel: true,
            streetViewControl: false,
            draggable : true,
            clickableIcons: false,
            mapTypeId: google.maps.MapTypeId.ROADMAP,
            styles: [{"featureType": "all","elementType": "labels.text.fill","stylers": [{    "saturation": 36},{    "color": "#333333"},{    "lightness": 40}]},{"featureType": "all","elementType": "labels.text.stroke","stylers": [{    "visibility": "on"},{    "color": "#ffffff"},{    "lightness": 16}]},{"featureType": "all","elementType": "labels.icon","stylers": [{    "visibility": "off"}]},{"featureType": "administrative","elementType": "geometry.fill","stylers": [{    "color": "#fbfbfb"},{    "lightness": 20},{    "visibility": "on"},{    "saturation": "14"},{    "gamma": "1.09"},{    "weight": "6.35"}]},{"featureType": "administrative","elementType": "geometry.stroke","stylers": [{    "color": "#fefefe"},{    "lightness": 17},{    "weight": 1.2}]},{"featureType": "administrative.country","elementType": "geometry","stylers": [{    "visibility": "on"},{    "hue": "#ff0000"},{    "saturation": "0"}]},{"featureType": "administrative.country","elementType": "geometry.fill","stylers": [{    "visibility": "on"},{    "color": "#ca3030"},{    "saturation": "14"},{    "lightness": "13"},{    "weight": "2.34"}]},{"featureType": "administrative.country","elementType": "geometry.stroke","stylers": [{    "visibility": "on"},{    "color": "#e1dddd"}]},{"featureType": "administrative.country","elementType": "labels","stylers": [{    "visibility": "on"},{    "saturation": "12"},{    "color": "#7e5050"}]},{"featureType": "administrative.country","elementType": "labels.text","stylers": [{    "visibility": "on"}]},{"featureType": "administrative.country","elementType": "labels.text.fill","stylers": [{    "visibility": "on"},{    "saturation": "-49"},{    "lightness": "-16"}]},{"featureType": "administrative.country","elementType": "labels.text.stroke","stylers": [{    "visibility": "off"}]},{"featureType": "administrative.province","elementType": "geometry.fill","stylers": [{    "visibility": "off"},{    "color": "#ff0000"}]},{"featureType": "administrative.province","elementType": "geometry.stroke","stylers": [{    "visibility": "on"},{    "color": "#bdb8b8"},{    "weight": "0.91"}]},{"featureType": "landscape","elementType": "geometry","stylers": [{    "color": "#f5f5f5"},{    "lightness": 20}]},{"featureType": "landscape.man_made","elementType": "geometry","stylers": [{    "visibility": "off"}]},{"featureType": "landscape.man_made","elementType": "geometry.fill","stylers": [{    "visibility": "off"},{    "hue": "#ff0000"},{    "weight": "0.01"}]},{"featureType": "landscape.man_made","elementType": "geometry.stroke","stylers": [{    "visibility": "off"},{    "hue": "#ff0000"}]},{"featureType": "poi","elementType": "geometry","stylers": [{    "color": "#f5f5f5"},{    "lightness": 21}]},{"featureType": "poi.park","elementType": "geometry","stylers": [{    "color": "#dedede"},{    "lightness": 21},{    "visibility": "off"}]},{"featureType": "road.highway","elementType": "geometry.fill","stylers": [{    "color": "#d3cece"},{    "lightness": 17}]},{"featureType": "road.highway","elementType": "geometry.stroke","stylers": [{    "color": "#ffffff"},{    "lightness": 29},{    "weight": 0.2}]},{"featureType": "road.arterial","elementType": "geometry","stylers": [{    "color": "#ffffff"},{    "lightness": 18}]},{"featureType": "road.local","elementType": "geometry","stylers": [{    "color": "#ffffff"},{    "lightness": 16}]},{"featureType": "transit","elementType": "geometry","stylers": [{    "color": "#f2f2f2"},{    "lightness": 19}]},{"featureType": "water","elementType": "geometry","stylers": [{    "color": "#d6f3fb"},{    "lightness": 17}]}]
	});
	
}
//For Converting Circle to Polygon
function convertToPolygon(center, radius, numSides) {
    for (var gpos, pointsForSearching = [], degreeStep = 360 / numSides, i = 0; i < numSides; i++) gpos = google.maps.geometry.spherical.computeOffset(center, radius, degreeStep * i), pointsForSearching.push({
        lat: gpos.lat(),
        lng: gpos.lng()
        
    });
    return pointsForSearching
}

function drawPolygonMouseupHandler() {

    var coordinatesForPolygonSearch, i;
    if (newShape) {
        var path = newShape.getPath(),
            theArrayofLatLng = path.getArray(),
            currentMPerPx = [, 21282, 16355, 10064, 5540, 2909, 1485, 752, 378, 190, 95, 48, 24, 12, 6, 3, 1.48, .74, .37, .19][map.getZoom()],
            pathToDrawPolygon = creditToGDouglasPeucker(theArrayofLatLng, currentMPerPx * 3);
        for (newShape != null && (newShape.setMap(null), newShape = null), maskShape != null && (maskShape.setMap(null), maskShape = null), coordinatesForPolygonSearch = [], i = 0; i < pathToDrawPolygon.length; i++) {
        	coordinatesForPolygonSearch.push({
	            lat: pathToDrawPolygon[i].lat(),
	            lng: pathToDrawPolygon[i].lng()
        	});
       	}
       	if (coordinatesForPolygonSearch.length < 3) {
            console.log("Please draw a larger area to search.");
            return
   		}else{
   			drawPolygon(coordinatesForPolygonSearch);
   			google.maps.event.removeListener(mouseDownHandler);
            enableMap(map)
   		}
    }
};
function drawPolygon(path) {
	searchCoords = path;
    var pathToDrawPolygon, i, polygonOptions, polygon;
    for ( pathToDrawPolygon = [], i = 0; i < path.length; i++) pathToDrawPolygon.push(new google.maps.LatLng(Number(path[i].lat), Number(path[i].lng)));
    polygonOptions = {
    	map:map,
        fillColor: "#FF0000",
        fillOpacity: .2,
        strokeColor: "#FF0000",
        strokeWeight: 3,
        clickable: !1,
        zIndex: 1,
        editable: !1,
        paths: pathToDrawPolygon
    };
    polygons.push(polygon = new google.maps.Polygon(polygonOptions));
}
function disableMap(map) {
    map.setOptions({
        draggable: !1,
        zoomControl: !1,
        scrollwheel: !1,
        disableDoubleClickZoom: !0
    })
}

function enableMap(map) {
    map.setOptions({
        draggable: !0,
        scrollwheel: !0,
        zoomControl: !0,
        disableDoubleClickZoom: !1
    })
}
function creditToGDouglasPeucker(source, kink) {
    var n_source, n_stack, n_dest, start, end, sig, dev_sqr, max_dev_sqr, band_sqr, x12, y12, d12, x13, y13, d13, x23, y23, d23, F = Math.PI / 180 * .5,
        index = [],
        sig_start = [],
        sig_end = [],
        r, i;
    if (source.length < 3) return source;
    for (n_source = source.length, band_sqr = kink * 360 / (12756274 * Math.PI), band_sqr *= band_sqr, n_dest = 0, sig_start[0] = 0, sig_end[0] = n_source - 1, n_stack = 1; n_stack > 0;)
        if (start = sig_start[n_stack - 1], end = sig_end[n_stack - 1], n_stack--, end - start > 1) {
            for (x12 = source[end].lng() - source[start].lng(), y12 = source[end].lat() - source[start].lat(), Math.abs(x12) > 180 && (x12 = 360 - Math.abs(x12)), x12 *= Math.cos(F * (source[end].lat() + source[start].lat())), d12 = x12 * x12 + y12 * y12, i = start + 1, sig = start, max_dev_sqr = -1; i < end; i++) x13 = source[i].lng() - source[start].lng(), y13 = source[i].lat() - source[start].lat(), Math.abs(x13) > 180 && (x13 = 360 - Math.abs(x13)), x13 *= Math.cos(F * (source[i].lat() + source[start].lat())), d13 = x13 * x13 + y13 * y13, x23 = source[i].lng() - source[end].lng(), y23 = source[i].lat() - source[end].lat(), Math.abs(x23) > 180 && (x23 = 360 - Math.abs(x23)), x23 *= Math.cos(F * (source[i].lat() + source[end].lat())), d23 = x23 * x23 + y23 * y23, dev_sqr = d13 >= d12 + d23 ? d23 : d23 >= d12 + d13 ? d13 : (x13 * y12 - y13 * x12) * (x13 * y12 - y13 * x12) / d12, dev_sqr > max_dev_sqr && (sig = i, max_dev_sqr = dev_sqr);
            max_dev_sqr < band_sqr ? (index[n_dest] = start, n_dest++) : (n_stack++, sig_start[n_stack - 1] = sig, sig_end[n_stack - 1] = end, n_stack++, sig_start[n_stack - 1] = start, sig_end[n_stack - 1] = sig)
        } else index[n_dest] = start, n_dest++;
    for (index[n_dest] = n_source - 1, n_dest++, r = [], i = 0; i < n_dest; i++) r.push(source[index[i]]);
    return r
};

$("#search").click(function() {
	if(isCircle){
		searchCoords = convertToPolygon(circle.getCenter(), circle.getRadius(), 50);
	}
	var latLngs = '';
	for (var i=0;i<searchCoords.length;i++) {
		var ll=searchCoords[i].lat+','+searchCoords[i].lng;
		latLngs+='['+ll.toString()+']';
	}
	var url='php/zip.php?latlngs='+latLngs;
	$.ajax({
		url: url, 
		success : function(response) {
			$('#zipcodes').text(response);
			console.log(response.split(', ').length)
		}
	});
});
var startingPoint;
var mouseUpHandler;
$('#draw').click(function(){
	// map.set('draggable',false)
	// map.set('guestureHandling','none');
	disableMap(map);
	clearPolygons();
	mouseDownHandler = google.maps.event.addListener(map, "mousedown", function(event) {
		newShape = new google.maps.Polyline({
	        strokeColor: "#FF0000",
	        strokeWeight: 3,
	        clickable: !1,
	        zIndex: 1,
	        editable: !1
	    });
	    maskShape = new google.maps.Polygon({
	        fillColor: "#FF0000",
	        fillOpacity: .2,
	        strokeOpacity: 0,
	        clickable: !1,
	        zIndex: 1,
	        editable: !1
	    });
	    newShape.setMap(map);
     	move = google.maps.event.addListener(map, "mousemove", function(e) {
     		startingPoint = e.latLng;
	        newShape.getPath().push(e.latLng);
	        maskShape.setMap(null);
	        maskShape.setPath(newShape.getPath());
	        maskShape.setMap(map);
	    });
		
    });
    mouseUpHandler = window.addEventListener("mouseup", function() {
	    move && (google.maps.event.removeListener(move), drawPolygonMouseupHandler(),enableMap(map), window.removeEventListener("mouseup", mouseUpHandler))
	});
});
$('#clear').on('click', function(){
	clearPolygons();
	circle.setMap(null);
	circle = null;
	isCircle = false;
	searchCoords = []
	$('#zipcodes').html('')
});
var circle, isCircle = false;
$('#circle').on('click',function(){
	isCircle = true;
	google.maps.event.addListenerOnce(map, 'click', function(e){
		circle = new google.maps.Circle({
			strokeColor: '#FF0000',
	      	strokeOpacity: 1,
	      	strokeWeight: 3,
	      	fillColor: '#ff0000',
	      	fillOpacity: .2,
	      	center: e.latLng,
	      	radius: 500000,
	      	editable:true,
	      	draggable:true,
	      	map:map
		});
	});
});
function clearPolygons(){
	for(var i = 0;i<polygons.length;i++){
		polygons[i].setMap(null);
	}
	polygons = [];
}
</script>


</body>
</html>
