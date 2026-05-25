<div class="panel widget">
    <div class="left-widget widget-body">
        <h4>50+ Point Games</h4>
        <hr>
        <?php
        //$weeks and $weekdata arrays come from season_weekhighs.php

            foreach($weekdata as $key => $value):
                foreach ($value as $k => $v):
                    if($v >= 50):
                        $weekfifties[$key][$k] = $v;
                        $weekfiftiescount[$k][] = $v;
                    endif;
                endforeach;
            endforeach;

            //printr($weekfiftiescount, 0);

            if($weekfifties):
                foreach($weekfifties as $key => $value):
                    $weekst = substr($key, -2);
                    foreach($value as $k => $v):
                        $fprint .= '<p>Week '.$weekst.' | <strong>';
                        if(is_array($value)):
                            $c = count($value);
                        endif;
                        $fprint .= $teamids[$k].'</strong> - '.$v.'</p>';

                    endforeach;
                endforeach;

                echo rtrim($fprint, '/');


                foreach ($weekfiftiescount as $team => $num):
                    $c = count($num);
                    $justcount[$team] = $c;
                endforeach;
                arsort($justcount);

                foreach($justcount as $t => $c):
                    $cstring .= $teamids[$t].' - '.$c.', ';
                endforeach;

                echo '<hr><h4>Total Count</h4>';
                echo rtrim($cstring, ', ');
            endif;

            //printr($justcount, 0);

            ?>
    </div>
</div>