<div class="panel">
	<div class="panel-body">
	</div>
</div>


<div class="panel">
	<div class="panel-body">

		<!-- Default choosen -->
		<!--===================================================-->
		<div class="row">
		
		<div class="col-xs-24 col-sm-18">
			<select data-placeholder="Select A Team..." class="chzn-select" style="width:100%;" tabindex="2" id="teamDrop">
				<option value=""></option>
				
				<?php 	
				foreach ($team_all_ids as $key => $value){
					$printselect .= '<option value="/teams/?id='.$key.'">'.$value['team'].'</option>';
				}
				echo $printselect;
				?>
			</select>
			</div>
			<div class="col-xs-24 col-sm-6">
				<button class="btn btn-warning" id="teamSelect">Select</button>
			</div>
		</div>
		<!--===================================================-->

	</div>
</div>
