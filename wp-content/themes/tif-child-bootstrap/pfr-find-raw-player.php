<?php
/*
 * Template Name: PFR Player Score From Raw
 * Description: Used to check Pro Football Refernce raw player data to help fill in gaps where we know the score of the position, but don't know the player.
 */
 ?>

<?php

$year = 1992;
$week = 10;
$seekscore = 12;
$position = 'WR';

function csvToJson($csvFilePath)
{
    // Check if the file exists
    if (!file_exists($csvFilePath) || !is_readable($csvFilePath)) {
        $getval = json_encode(["error" => "File not found or not readable"]);
        return $getval;
    }

    $header = null;
    $data = [];

    // Open the CSV file for reading
    if (($handle = fopen($csvFilePath, 'r')) !== false) {
        // Read the header row
        if (($header = fgetcsv($handle)) !== false) {
            // Read each row of the CSV file
            while (($row = fgetcsv($handle)) !== false) {
                $data[] = array_combine($header, $row);
            }
        }
        fclose($handle);
    } else {
        $getval = json_encode(["error" => "Unable to open the file"]);
        return $getval;
    }

    // Convert the data to JSON and print it
    return $data;
}

// Call the function with the path to your CSV file
$raw_pass = csvToJson('wp-content/themes/tif-child-bootstrap/pfr-raw-season/'.$year.'-pass.csv');
$raw_rush = csvToJson('wp-content/themes/tif-child-bootstrap/pfr-raw-season/'.$year.'-rush.csv');
$raw_rec = csvToJson('wp-content/themes/tif-child-bootstrap/pfr-raw-season/'.$year.'-rec.csv');
$raw_kick = csvToJson('wp-content/themes/tif-child-bootstrap/pfr-raw-season/'.$year.'-kick.csv');

//printr($raw_pass, 1);

if($position != 'K'):
    foreach ($raw_pass as $key => $value):
        $pflscore = pos_score_converter($year, $value['passyards'], $value['passtd'], 0, 0, 0, 0, 0);
        $passing[$value['week']][$value['player']][] = array('data' => $value, 'pflscore' => $pflscore);
    endforeach;

    foreach ($raw_rush as $key => $value):
        $pflscore = pos_score_converter($year, 0, 0, $value['rushyards'], $value['rushtd'], 0, 0, 0);
        $rushing[$value['week']][$value['player']][] = array('data' => $value, 'pflscore' => $pflscore);
    endforeach;

    foreach ($raw_rec as $key => $value):
        $pflscore = pos_score_converter($year, 0, 0, 0, 0, 0, $value['recyards'], $value['rectd']);
        $receptions[$value['week']][$value['player']][] = array('data' => $value, 'pflscore' => $pflscore);
    endforeach;

    //Enter the vars here
    $weekpass = $passing[$week];
    $weekrush = $rushing[$week];
    $weekrec = $receptions[$week];

    $joinweek = array_merge_recursive($weekpass, $weekrush, $weekrec);
    foreach ($joinweek as $key => $value):
        $join[$key] = $value;
    endforeach;

    foreach($join as $key => $value):
        if($position == $value[0]['data']['position']):
            $scoring = $value[0]['pflscore'] + $value[1]['pflscore'] + $value[2]['pflscore'];
            $playerscorecheck[$key] = $scoring;
        endif;
    endforeach;

    foreach($playerscorecheck as $key => $value):
        if($value == $seekscore):
            $playerscorecheck[$key] = $value;
        else:
            unset($playerscorecheck[$key]);
        endif;
    endforeach;

else:
    foreach ($raw_kick as $key => $value):
        $pflscore = pk_score_converter($year, $value['xpm'], $value['fgm']);
        $kicks[$value['week']][$value['player']][] = array('data' => $value, 'pflscore' => $pflscore);
    endforeach;
    $weekkick = $kicks[$week];
    foreach ($weekkick as $key => $value):
        $joinkick[$key] = $value[0]['pflscore'];
    endforeach;

    foreach($joinkick as $key => $value):
        if($value == $seekscore):
            $joinkick[$key] = $value;
        else:
            unset($joinkick[$key]);
        endif;
    endforeach;

    printr($joinkick, 1);
endif;

//printr($join, 0);

printr($playerscorecheck, 0);

?>


<?php get_footer(); ?>