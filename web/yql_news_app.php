<html>  
  <head><title>YQL</title>  

  </head>  
  <body>  
   <h2>YQL</h2>  

<?php  
  //$BASE_URL = "https://query.yahooapis.com/v1/public/yql";  
  

       
    // Form YQL query and build URI to YQL Web service  
    //$yql_query = "select * from upcoming.events where location='$location' and search_text='$query'";  
    //$yql_query_url = $BASE_URL . "?q=" . urlencode($yql_query) . "&format=json";  
	
	//$yql_query = "select name, country from geo.places where text=\"san francisco, ca\"";  
  
	//$yql_query_url = $yql_base_url . "?q=" . urlencode($yql_query);
  	
	$test = "https://query.yahooapis.com/v1/public/yql?q=SELECT%20*%20FROM%20yahoo.finance.quote%20WHERE%20symbol%3D'%5Egspc'&format=json&env=store%3A%2F%2Fdatatables.org%2Falltableswithkeys";
	
	//$xml = "https://query.yahooapis.com/v1/public/yql?q=select%20name%2C%20country%20from%20geo.places%20where%20text%3D%22san%20francisco%2C%20ca%22";
	
    // Make call with cURL  
    $session = curl_init($test);  
    curl_setopt($session, CURLOPT_RETURNTRANSFER,true);  
    $json = curl_exec($session);  
    // Convert JSON to PHP object   
    $phpObj =  json_decode($json); 
  	
	/*$page = file_get_contents($xml, TRUE);
	$pageXML		= simplexml_load_string($page);
	$json			= json_encode($pageXML);
	$page 			= json_decode($json, TRUE);
	
	print_r($page);
	
	echo "<br><br>hello<br><br>";*/
	
	print_r($phpObj);
	
	echo "<br><br>hello<br><br>";
	
	/*foreach($phpObj->query as $when){
		$when .= $when->created;	
	}
	
	echo "Created: ".$when."<br>";*/
	
	foreach($phpObj->query->results as $index){
		$indexName 		= $index->Name;
		$indexChange	= $index->Change;
		$indexPrice		= $index->LastTradePriceOnly;
	}
	echo "As of: ".date('m/d/y g:i A')."<br>";
	echo $indexName;
	echo "<br>";
	echo $indexPrice;
	echo "<br>";
	echo $indexChange;
	echo "<br>";
		
    // Confirm that results were returned before parsing  
    /*if(!is_null($phpObj->query->results)){  
      // Parse results and extract data to display  
      foreach($phpObj->query->results->event as $event){  
        $events .= "<div><h2>" . $event->name . "</h2><p>";  
        $events .= html_entity_decode(wordwrap($event->country, 80, "<br/>"));  
      }  
    }  
    // No results were returned  
    if(emptyempty($events)){  
      $events = "Sorry, no events matching $query in $location";  
    }  
    // Display results and unset the global array $_GET  
    echo $events;  */
?>  
  </body>  
</html> 