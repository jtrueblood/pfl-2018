<?php
	 echo '<div class="timeline-entry"> 
        <div class="timeline-label no-label">';
        $teami = $value['teams'];
        foreach($teami as $teams){
            echo '<p class="protected-by"><span class="text-bold">'.$teams.'</span> added as Free Agent</p>';
        }
        echo '</div>
    </div>';
?>