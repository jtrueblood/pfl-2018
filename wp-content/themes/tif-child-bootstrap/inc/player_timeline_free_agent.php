<?php
	 echo '<div class="timeline-entry"> 
        <div class="timeline-label no-label">';
        $teami = $value['teams'];
        foreach($teami as $teams){
            echo '<p class="protected-by">Signed as Free Agent with <span class="text-bold">'.$teaminfo[$teams]['team'].'</span></p>';
        }
        echo '</div>
    </div>';
    
//  printr($teaminfo, 0);
?>