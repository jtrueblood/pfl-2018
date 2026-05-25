<div class="panel widget">
    <div class="left-widget widget-body">
        <h4>Week Highs</h4>
        <hr>
        <?php
            $weeks = array('01','02','03','04','05','06','07','08','09','10','11','12','13','14');
            foreach($weeks as $week):
                $id = $year.$week;
                $weekdata[$id] = get_team_score_by_week($id);
            endforeach;

            foreach($weekdata as $key => $value):
                $max = max($value);
                foreach ($value as $k => $v):
                    if($v == $max):
                        $weekhighs[$key][$k] = $v;
                    endif;
                endforeach;
            endforeach;

            //printr($weekhighs, 0);

            foreach($weekhighs as $key => $value):
                $weekst = substr($key, -2);
                $print .= '<p>Week '.$weekst.' | <strong>';
                    foreach($value as $k => $v):
                        if(is_array($value)):
                            $c = count($value);
                        endif;
                        $print .= $teamids[$k];
                        if($c > 1):
                            $print .= ', ';
                        endif;
                    endforeach;
                $print .= '</strong> - '.$v.'</p>';
            endforeach;

            echo rtrim($print, ', ');

        ?>
    </div>
</div>