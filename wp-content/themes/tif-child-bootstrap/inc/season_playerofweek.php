<div class="panel widget">
    <div class="left-widget widget-body">
        <h4>Players of the Week</h4>
        <hr>
        <?php
        $potw = get_player_of_week();

        foreach($potw as $key => $value):
            $potwtopoint = array();
            $weekst = substr($key, -2);
            $yearst = substr($key, 0, 4);
            if($year == $yearst):
                $playerst = get_player_team_played_week($value, $key);
                $pointsst = get_player_points_by_week($value, $key);
                $potyst = get_player_of_week_player($value);
                foreach($potyst as $k => $pweeks):
                    if($pweeks <= $key):
                        $potwtopoint[] = $pweeks;
                    endif;
                endforeach;
                $potwyear[$weekst] = array(
                    'player' => pid_to_name($value, 0),
                    'team' => $playerst[0][0],
                    'points' => $pointsst[0][0],
                    'poty' => $potwtopoint,
                    'count' => count($potwtopoint)
                );
            endif;
        endforeach;

        foreach($potwyear as $key => $value):
            if ($value['count'] > 1):
                $c = '('.$value['count'].')';
            else:
                $c = '';
            endif;
            echo '<p>Week '.$key.' | <strong>'.$value['player'].'</strong>, '.$value['team'].' - '.$value['points'].' Points '.$c.'</p>';
        endforeach;
        ?>
    </div>
</div>