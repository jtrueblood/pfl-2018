<!doctype html>
<html class="no-js" lang="">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="x-ua-compatible" content="ie=edge">
        <title>MFL Weekly</title>
        <meta name="description" content="">
        <meta name="viewport" content="width=device-width, initial-scale=1">

<!--
        <link rel="stylesheet" href="css/normalize.css">
        <link rel="stylesheet" href="css/main.css">
        <script src="js/vendor/modernizr-2.8.3.min.js"></script>
-->
    </head>
    <body>



<?php 

$week = 14;
$year = 2015;
$lid = 47099;

/*
$week = 1;
$year = 2016;
$lid = 38954;
*/

$dayofweek = date('w', strtotime($date));
echo '<h4>'.$dayofweek.'</h4>';

$dayofweek = 4;


get_cache('players', 0);	
$players = $_SESSION['players'];

get_cache('mfl/linkidcache', 0);	
$linkidcache = $_SESSION['mfl/linkidcache'];

//printr($linkidcache, 0);


// team results by week
$jsonweeklyresult = file_get_contents('http://football25.myfantasyleague.com/'.$year.'/export?TYPE=weeklyResults&L='.$lid.'&W='.$week.'&JSON=1');
$jsonlivescores = file_get_contents('http://football25.myfantasyleague.com/'.$year.'/export?TYPE=liveScoring&L='.$lid.'&W='.$week.'&JSON=1');
$mflweeklyresult = json_decode($jsonweeklyresult, true);

$mfllive = json_decode($jsonlivescores, true);
//printr($mfllive, 0);

$jsonleague = file_get_contents('http://football25.myfantasyleague.com/'.$year.'/export?TYPE=league&L='.$lid.'&W='.$week.'&JSON=1');
$mflleague = json_decode($jsonleague, true);

$mflteams = $mflleague['league']['franchises']['franchise'];

foreach ($mflteams as $resort){
	$teamids[$resort['id']] = $resort['name'];
}


for ($i = 0; $i <= 5; $i++) {
	$game = $mfllive['liveScoring']['matchup'][$i]['franchise'];
	foreach ($game as $getteams){
		
		// set matchup0 and min0 vars
		${'matchup' . $i}[] = array($getteams['score'], $getteams['isHome'], $getteams[''], $getteams['gameSecondsRemaining'], $teamids[$getteams['id']], $getteams['playersCurrentlyPlaying'], $getteams['players']['player']);
		${'mins' . $i} == ${'matchup' . $i}[0][3] + ${'matchup' . $i}[1][3];
		
		// set result as FINAL
		if (${'mins' . $i} == 0){
			${'result' . $i} = ' (Final)';
		} 
		
		// Get boxscore data team a
		$name0 = $linkidcache[${'matchup' . $i}[0][6][0]['id']][0];
		${'pl0a' . $i} = explode(',', $name0);
		${'sc0a' . $i} = ${'matchup' . $i}[0][6][0]['score'];
		
		$name1 = $linkidcache[${'matchup' . $i}[0][6][1]['id']][0];
		${'pl1a' . $i} = explode(',', $name1);
		${'sc1a' . $i} = ${'matchup' . $i}[0][6][1]['score'];
		
		$name2 = $linkidcache[${'matchup' . $i}[0][6][2]['id']][0];
		${'pl2a' . $i} = explode(',', $name2);
		${'sc2a' . $i} = ${'matchup' . $i}[0][6][2]['score'];
		
		$name3 = $linkidcache[${'matchup' . $i}[0][6][3]['id']][0];
		${'pl3a' . $i} = explode(',', $name3);
		${'sc3a' . $i} = ${'matchup' . $i}[0][6][3]['score'];
		
		
		// Get boxscore data team b
		$name4 = $linkidcache[${'matchup' . $i}[1][6][0]['id']][0];
		${'pl0b' . $i} = explode(',', $name4);
		${'sc0b' . $i} = ${'matchup' . $i}[1][6][0]['score'];
		
		$name5 = $linkidcache[${'matchup' . $i}[1][6][1]['id']][0];
		${'pl1b' . $i} = explode(',', $name5);
		${'sc1b' . $i} = ${'matchup' . $i}[1][6][1]['score'];
		
		$name6 = $linkidcache[${'matchup' . $i}[1][6][2]['id']][0];
		${'pl2b' . $i} = explode(',', $name6);
		${'sc2b' . $i} = ${'matchup' . $i}[1][6][2]['score'];
		
		$name7 = $linkidcache[${'matchup' . $i}[1][6][3]['id']][0];
		${'pl3b' . $i} = explode(',', $name7);
		${'sc3b' . $i} = ${'matchup' . $i}[1][6][3]['score'];
		
		
	}
	
	//printr($matchup0, 0);
}

$allgames = array($matchup0, $matchup1, $matchup2, $matchup3, $matchup4);
printr($matchup0, 0);

?>

<?php
//send data to slack

$toslack = '{
    "attachments": [
        {
            "fallback": "PFL Matchups",
            "pretext": "Week '.$week.' Matchups",
            "title": "PFL My Fantasy League",
            "title_link": "https://bit.ly/posse16",
            "fields": [
                {
                    "title": "'.$matchup0[0][4].' -- '.$matchup0[0][0].' | '.$matchup0[1][4].' -- '.$matchup0[1][0].' '.$result0.'",
                    "value": "'.$pl0a0[0].' - '.$sc0a0.', '.$pl1a0[0].' - '.$sc1a0.', '.$pl2a0[0].' - '.$sc2a0.', '.$pl3a0[0].' - '.$sc3a0.'\n'.$pl0b0[0].' - '.$sc0b0.', '.$pl1b0[0].' - '.$sc1b0.', '.$pl2b0[0].' - '.$sc2b0.', '.$pl3b0[0].' - '.$sc3b0.'\n:heavy_minus_sign:"
                },
                {
                    "title": "'.$matchup1[0][4].' -- '.$matchup1[0][0].' | '.$matchup1[1][4].' -- '.$matchup1[1][0].' '.$result1.'",
                    "value": "'.$pl0a1[0].' - '.$sc0a1.', '.$pl1a1[0].' - '.$sc1a1.', '.$pl2a1[0].' - '.$sc2a1.', '.$pl3a1[0].' - '.$sc3a1.'\n'.$pl0b1[0].' - '.$sc0b1.', '.$pl1b1[0].' - '.$sc1b1.', '.$pl2b1[0].' - '.$sc2b1.', '.$pl3b1[0].' - '.$sc3b1.'\n:heavy_minus_sign:"

                },
                {
                    "title": "'.$matchup2[0][4].' -- '.$matchup2[0][0].' | '.$matchup2[1][4].' -- '.$matchup2[1][0].' '.$result2.'",
                    "value": "'.$pl0a2[0].' - '.$sc0a2.', '.$pl1a2[0].' - '.$sc1a2.', '.$pl2a2[0].' - '.$sc2a2.', '.$pl3a2[0].' - '.$sc3a2.'\n'.$pl0b2[0].' - '.$sc0b2.', '.$pl1b2[0].' - '.$sc1b2.', '.$pl2b2[0].' - '.$sc2b2.', '.$pl3b2[0].' - '.$sc3b2.'\n:heavy_minus_sign:"

                },
                {
                    "title": "'.$matchup3[0][4].' -- '.$matchup3[0][0].' | '.$matchup3[1][4].' -- '.$matchup3[1][0].' '.$result3.'",
                    "value": "'.$pl0a3[0].' - '.$sc0a3.', '.$pl1a3[0].' - '.$sc1a3.', '.$pl2a3[0].' - '.$sc2a3.', '.$pl3a3[0].' - '.$sc3a3.'\n'.$pl0b3[0].' - '.$sc0b3.', '.$pl1b3[0].' - '.$sc1b3.', '.$pl2b3[0].' - '.$sc2b3.', '.$pl3b3[0].' - '.$sc3b3.'\n:heavy_minus_sign:"

                },
                {
                    "title": "'.$matchup4[0][4].' -- '.$matchup4[0][0].' | '.$matchup4[1][4].' -- '.$matchup4[1][0].' '.$result4.'",
                    "value": "'.$pl0a4[0].' - '.$sc0a4.', '.$pl1a4[0].' - '.$sc1a4.', '.$pl2a4[0].' - '.$sc2a4.', '.$pl3a4[0].' - '.$sc3a4.'\n'.$pl0b4[0].' - '.$sc0b4.', '.$pl1b4[0].' - '.$sc1b4.', '.$pl2b4[0].' - '.$sc2b4.', '.$pl3b4[0].' - '.$sc3b4.'\n:heavy_minus_sign:"
                }
            ],
            "image_url": "http://my-website.com/path/to/image.jpg",
            "thumb_url": "http://example.com/path/to/thumb.png",
            "footer": "PFL #slack"
        }
    ]
}';

printr($toslack, 0);

$url = "https://hooks.slack.com/services/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";    
/*
echo '<pre>'.json_encode($toslack, JSON_FORCE_OBJECT).'</pre>';
$content = json_encode($toslack, JSON_FORCE_OBJECT);
*/

$curl = curl_init($url);
curl_setopt($curl, CURLOPT_HEADER, false);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_HTTPHEADER,
        array("Content-type: application/json"));
curl_setopt($curl, CURLOPT_POST, true);
curl_setopt($curl, CURLOPT_POSTFIELDS, $toslack);

$json_response = curl_exec($curl);

$status = curl_getinfo($curl, CURLINFO_HTTP_CODE);

if ( $status != 201 ) {
    die("Error: call to URL $url failed with status $status, response $json_response, curl_error " . curl_error($curl) . ", curl_errno " . curl_errno($curl));
}

curl_close($curl);
$response = json_decode($json_response, true);



?>

<script src="https://code.jquery.com/jquery-1.12.4.min.js"></script>
               
    </body>
</html>
