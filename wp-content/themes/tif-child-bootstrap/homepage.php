<?php
/*
 * Template Name: Homepage
 * Description: Homepage for the PFL Website
 */

/*
$url1=$_SERVER['REQUEST_URI'];
header("Refresh: 5; URL=$url1");
*/


$getplayer = $_GET['id'];
 
$playersassoc = get_players_assoc();
$i = 0;
foreach ($playersassoc as $key => $value){
	$playersid[] = $key;
}

$randomize = array_rand($playersid);


//$randomplayer = '2009AverWR';

if(isset($getplayer)){
	$randomplayer = $_GET['id'];
} else {
	$randomplayer = $playersid[$randomize];
}

$featuredplayer = $playersassoc[$randomplayer];
$first = $featuredplayer[0];
$last = $featuredplayer[1];
$position = $featuredplayer[2];
$rookie = $featuredplayer[3];
$mflid = $featuredplayer[4];
$name_merge = $last.', '.$first;


insert_wp_career_leaders($randomplayer);
$testprint = insert_wp_season_leaders($randomplayer);
// printr($testprint, 0);

// sets transient to player data array to random player on homepage
function set_randomplayerdata_trans() {
  global $randomplayer;
  $transient = get_transient( $randomplayer.'_trans' );
  if( ! empty( $transient ) ) {
    return $transient;
  } /*
else {
   	$set[$randomplayer] = get_player_data($randomplayer);
    set_transient( $randomplayer.'_trans', $set, DAY_IN_SECONDS );
    return $set;
  }
*/
  
}

/*
$teamget = get_team_results('PEP');
printr($teamget, 1);
*/

set_team_trans();
set_schedule_trans();

/*
$teams = get_teams();
printr($teams, 0);
*/



//$teamer = set_team_data_trans('SNR');
//$teamrec = team_record('SNR');
//printr($teamer, 1);



/*
foreach ($playersid as $id){
	set_allplayerdata_trans($id);
}
*/


get_header(); 
//start the loop
//build player id array. 

?>

<div class="boxed">
			
			<!--CONTENT CONTAINER-->
			<div id="content-container">

				
				<div id="page-title">
					<?php while (have_posts()) : the_post(); ?>
						<h1 class="page-header text-bold"><?php the_title();?></h1>
					<?php endwhile; wp_reset_query(); ?>
				</div>
				
				<!--Page content-->
				<div id="page-content">
				
				<!-- THE ROW -->
				<div class="row">
					
					<div class="col-xs-12 col-sm-6 eq-box-sm">	
						
						<div class="panel panel-bordered panel-light">
							<div class="panel-heading">
								<h3 class="panel-title">Select A Player</h3>
							</div>
							<div class="panel-body">
							<div class="col-xs-24 col-sm-18">	
								<select data-placeholder="Select an Existing Player" class="chzn-select" style="width:100%;" tabindex="1" id="playerDrop">
								<option value=""></option>
								
								<?php 	
								foreach ($playersassoc as $key => $selectplayer){
									$firsto = $selectplayer[0];
									$lasto = $selectplayer[1];
									$printselect .= '<option value="/?id='.$key.'">'.$firsto.' '.$lasto.'</option>';
								}
								echo $printselect;
								?>
							</select>
							</div>
							<div class="col-xs-24 col-sm-4">
								<button class="btn btn-warning" id="playerSelect">Select</button>
							</div>
						</div>
						
						
						
						<div class="panel panel-bordered panel-light">
<div class="panel-heading">
    <h3 class="panel-title">PHP Scripts</h3>
</div>
    <div class="panel-body">
        <div class="col-xs-24">
            <a href="https://api.myfantasyleague.com/2024/login?USERNAME=jtrueblood&PASSWORD=eur0TR@SH!&XML=1" target="_blank">Authenticate MFL API</a><br>
            <a href="/builds/build-drafts/?SQL=0&Y=2024">Import Draft</a><br>
            <a href="/create-new-player">Create New Player</a><br>
            <a href="/scrape-pfr-for-numbers/?id=1991AndePK">Scrape for Player Numbers</a><br>
            <a href="/builds/build-mfl-weekly/?SET=0&Y=2024&W=1&SQL=false&CURL=false">Run MFL Weekly Data Insert</a><br>
            <a href="/get-weekly-rosters-mfl/?Y=2024&W=1">Get Weekly Rosters from MFL</a><br>
            <a href="/player-ot-score/?SQL=0">Build Overtime Scores from MFL</a><br>
            <a href="/get-player-scores-for-playoffs/?SQL=0&Y=2022&W=15&S1=XXX&S2=XXX&S3=XXX&S4=XXX">Build Playoff Data 15 & 16</a><br>
            <a href="/team-rosters/?season=2024">Create Full Rosters by Season</a><br>
        </div>
    </div>

<div class="panel-heading">
    <h3 class="panel-title">Upload Player Image</h3>
</div>
<div class="panel-body">
    <div class="col-xs-24">
        <div id="pfl-image-upload">
            <div id="pfl-drop-zone" style="border:2px dashed #aaa;border-radius:8px;padding:30px 20px;text-align:center;cursor:pointer;background:#fafafa;transition:background .2s;">
                <p style="margin:0;color:#888;font-size:14px;">Drag &amp; drop a JPG or WebP here<br>or <strong>click to browse</strong></p>
                <p id="pfl-file-name" style="margin:8px 0 0;font-size:13px;color:#555;"></p>
            </div>
            <input type="file" id="pfl-file-input" accept=".jpg,.jpeg,.webp" style="display:none;">
            <div style="margin-top:12px;display:flex;gap:8px;align-items:center;">
                <input type="text" id="pfl-player-id" value="<?php echo esc_attr($randomplayer); ?>" readonly
                    style="flex:1;padding:7px 10px;border:1px solid #ccc;border-radius:4px;font-size:14px;background:#f5f5f5;color:#333;font-family:monospace;">
                <button id="pfl-upload-btn" disabled
                    style="padding:7px 18px;background:#337ab7;color:#fff;border:none;border-radius:4px;font-size:14px;cursor:pointer;opacity:.5;">
                    Upload
                </button>
            </div>
            <div id="pfl-upload-status" style="margin-top:10px;font-size:13px;display:none;"></div>
        </div>
    </div>
</div>

<script>
(function () {
    const dropZone  = document.getElementById('pfl-drop-zone');
    const fileInput = document.getElementById('pfl-file-input');
    const fileName  = document.getElementById('pfl-file-name');
    const playerInput = document.getElementById('pfl-player-id');
    const uploadBtn = document.getElementById('pfl-upload-btn');
    const status    = document.getElementById('pfl-upload-status');
    let selectedFile = null;

    function setFile(file) {
        selectedFile = file;
        fileName.textContent = file.name + ' (' + Math.round(file.size / 1024) + ' KB)';
        dropZone.style.background = '#eaf4ff';
        dropZone.style.borderColor = '#337ab7';
        checkReady();
    }

    function checkReady() {
        const ready = !!selectedFile;
        uploadBtn.disabled = !ready;
        uploadBtn.style.opacity = ready ? '1' : '.5';
    }

    dropZone.addEventListener('click', () => fileInput.click());
    fileInput.addEventListener('change', () => { if (fileInput.files[0]) setFile(fileInput.files[0]); });

    dropZone.addEventListener('dragover', e => { e.preventDefault(); dropZone.style.background = '#ddeeff'; });
    dropZone.addEventListener('dragleave', () => { dropZone.style.background = selectedFile ? '#eaf4ff' : '#fafafa'; });
    dropZone.addEventListener('drop', e => {
        e.preventDefault();
        if (e.dataTransfer.files[0]) setFile(e.dataTransfer.files[0]);
    });

    uploadBtn.addEventListener('click', function () {
        if (!selectedFile || !playerInput.value.trim()) return;

        uploadBtn.disabled = true;
        uploadBtn.textContent = 'Uploading…';
        status.style.display = 'block';
        status.style.color = '#555';
        status.innerHTML = '⏳ Processing…';

        const form = new FormData();
        form.append('action', 'upload_player_image');
        form.append('nonce', '<?php echo wp_create_nonce("upload_player_image"); ?>');
        form.append('player_id', playerInput.value.trim());
        form.append('image', selectedFile);

        fetch('<?php echo admin_url("admin-ajax.php"); ?>', { method: 'POST', body: form })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    status.style.color = '#2a7a2a';
                    status.innerHTML = '✓ Uploaded successfully.<br><pre style="font-size:11px;margin-top:6px;white-space:pre-wrap;">' + (data.data.message || '') + '</pre>';
                } else {
                    status.style.color = '#a00';
                    status.innerHTML = '✗ ' + (data.data?.message || 'Upload failed.');
                }
            })
            .catch(err => {
                status.style.color = '#a00';
                status.innerHTML = '✗ Request error: ' + err.message;
            })
            .finally(() => {
                uploadBtn.disabled = false;
                uploadBtn.textContent = 'Upload';
            });
    });
})();
</script>

<div class="panel-heading">
    <h3 class="panel-title">Python Scripts</h3>
</div>
<div class="panel-body">
    <div class="col-xs-24">
<h5>Navigate to Python Scripts Directory</h5>
<pre>cd /Users/jamietrueblood/Local\ Sites/posse-football-league/app/public/wp-content/themes/tif-child-bootstrap/pythonscripts</pre>
<h5>Get Image for Player from Google Images</h5> <!--python script -->
<pre>python3 getpflimage.py</pre>
<h5>ESPN API For NFL Player Game Stats</h5>
<pre>python3 getplayernfldata.py "Josh Allen" 2025 "1,2,3" No</pre>
<h5>Download all MFL Transactions for year to json</h5>
<pre>getmfltransactions.py 2025</pre>
<h5>Find and Replace Player ID -- used to fix errors</h5>
<pre>replace_player_id.py</pre>
<h5>Get Player Boxscore from Database / Make Corrections</h5>
<pre>python3 player_boxscore.py 1993BoniPK 1995 2</pre>
<h5>Insert single player game data into database</h5>
<pre># Using PFL Player ID
python3 build-single-player-mfl.py 2018AlleQB 2024 13
# Using player name
python3 build-single-player-mfl.py "Josh Allen" 2024 13
# Using MFL ID
python3 build-single-player-mfl.py 14477 2024 13</pre>
<h5>Insert end of season standings from week 14 to standYEAR</h5>
<pre># You will still need to manually update playoff seeds
python3 update-standings-table.py 2025</pre>
<h5>Import from Season Leaders into Probowl Boxscore</h5>
<pre>python3 insert_probowl_leaders.py 2025</pre>
<h5>Get Draft json data from MFL</h5>
<pre>python3 fetch_mfl_drafts.py --all</pre>
<h5>Analyze Draft Year</h5>
<pre>python3 analyze_draft_db.py 1998</pre>





</div>
                            </div>
						</div>

						<div class="panel panel-bordered panel-light">
							<div class="panel-heading">
								<h3 class="panel-title">Quick Links</h3>
							</div>
								<div class="panel-body">
									<div class="col-xs-24">	
										<a href="https://www.pro-football-reference.com/" target="_blank">Pro Football Reference</a><br>
										<a href="https://www.fantasypros.com/" target="_blank">Fantasy Pros</a><br>
										<a href="https://docs.google.com/document/d/1D8VZPOBn04zVXYQB1gr-xb1NDe2nYLn9Rqgl4oZVhXI/edit?usp=sharing" target="_blank">PFL Rules - Rev 2019</a><br>
										<a href="https://wrapbootstrap.com/theme/nifty-responsive-admin-template-WB0048JF7" target="_blank">Nifty for Bootstrap Theme</a><br>
                                        <a href="https://preview.themeon.net/nifty/index.html" target="_blank">Nifty Theme Demo</a>

                                        <hr>
                                        <a href="https://www48.myfantasyleague.com/2025/home/38954#0" target="_blank">MFL Website 2025</a><br>
                                        <a href="https://www48.myfantasyleague.com/2024/home/38954#0" target="_blank">MFL Website 2024</a><br>
                                        <a href="https://www48.myfantasyleague.com/2023/home/38954#0" target="_blank">MFL Website 2023</a><br>
                                        <a href="https://www48.myfantasyleague.com/2022/home/38954#0" target="_blank">MFL Website 2022</a><br>
                                        <a href="https://www48.myfantasyleague.com/2021/home/38954#0" target="_blank">MFL Website 2021</a><br>
										<a href="https://www58.myfantasyleague.com/2020/home/38954#0" target="_blank">MFL Website 2020</a><br>
										<a href="http://www58.myfantasyleague.com/2019/home/38954#0" target="_blank">MFL Website 2019</a><br>
										<a href="http://www58.myfantasyleague.com/2018/home/38954#0" target="_blank">MFL Website 2018</a><br>
										<a href="http://www58.myfantasyleague.com/2017/home/38954#0" target="_blank">MFL Website 2017</a><br>
										<a href="http://www58.myfantasyleague.com/2016/home/38954#0" target="_blank">MFL Website 2016</a><br>
										<a href="http://www58.myfantasyleague.com/2015/home/47099#0" target="_blank">MFL Website 2015</a><br>
										<hr>
										
									</div>
								</div>
							</div>
						</div>
					</div>
					
					
					
					<div class="col-xs-12 col-sm-6 eq-box-sm">
                            <div class="panel panel-bordered panel-light">
                                <div class="panel-body">

<!--                                    --><?php //alter_player_table_columns ($randomplayer); ?>
                                    <?php $mfrline = get_pfr_linescores_by_player($randomplayer);
                                            printr($mfrline, 0);
                                    ?>

                                    <?php while (have_posts()) : the_post(); ?>
                                        <p><?php the_content();?></p>
                                    <?php endwhile; wp_reset_query(); ?>
                                </div>
                            </div>

                            <div class="panel panel-bordered panel-light">
								<div class="panel-heading">
									<h3 class="panel-title">Basic Info</h3>
								</div>
								
								<div class="panel-body">
									<?php printr($featuredplayer, 0); ?>
								</div>
																
							</div>


					</div>

                    <!-- PLAYER SUPERCARD -->
                    <div class="col-xs-12 col-sm-12 eq-box-md">
                        <?php $supercard = supercard($randomplayer); ?>
                    </div>

                    <!-- PLAYER MFL TRANSACTIONS -->
                    <div class="col-xs-12 col-sm-8 eq-box-md">
                        <div class="panel panel-bordered panel-light">
                            <div class="panel-heading">
                                <h3 class="panel-title">MFL Player Transactions</h3>
                            </div>
                            <div class="panel-body">
                                <p>2011 - Present.  Must export json of Transactions from MFL api each season and save to 'mfl-transactions' directory.</p>
                                <?php
                                $printit = new_mfl_transactions($randomplayer);
                                $removeempty = array_filter($printit);
                                if($removeempty):
                                    printr($removeempty, 0);
                                else:
                                    echo '<h4>No MFL Transaction Data Found</h4>';
                                endif;
                                ?>
                            </div>
                        </div>
                    </div>
							
					<!-- MFL DATA CURL -->
					<div class="col-xs-24 col-sm-6 left-column">
						<div class="panel widget">
							<div class="widget-body text-center">
								<?php 
/*
									$pmflid = $wpdb->get_results("select mflid from wp_players where p_id = '$randomplayer'");
									$theid = $pmflid[0]->mflid;
*/
// 									printr($theid, 0);
									if(!empty($mflid)){
										echo 'From MFL API ';
										$mfldeet = get_mfl_player_details($mflid); 
										printr($mfldeet, 0);
										
										$inches = $mfldeet['height'];
										$getweight = $mfldeet['weight'];
										$getcollege = $mfldeet['college'];
										$getnumber = $mfldeet['jersey'];
										$getbirthdate = $mfldeet['birthdate'];
										
										$feet = floor($inches/12);
										$inches = ($inches%12);
										$getheight = $feet.'-'.$inches;
										
										//$php_timestamp_date = date("Y/m/d", $getbirthdate);
										//echo "".$php_timestamp_date."";
										
// 										foreach ($mflids as $key => $value){

											$wpdb->query(
											"UPDATE wp_players
											SET weight = $getweight, height = '$getheight', college = '$getcollege', number = $getnumber 
											WHERE p_id = '$randomplayer'"
											);

// 										}

										
									} else {
										echo 'MFL ID Not Found';
										
										// FOR POSITION PLAYERS (QB, RB, WR)
										//$pfr_id = $featuredplayer[10];
										//$firstInit =  $pfr_id[0];
										//$firstInitCap = strtoupper($firstInit);
										
										// FOR KICKERS (PK)
										//$pfr_id = 'reveifua01';

										$firstInitCap = strtoupper($last[0]);
										//$firstshort = substr($first, 0, 3);
										//$lastshort = substr($last, 0, 5);
										//$pfr_id = strtolower($lastshort.$firstshort).'01';
										$pfr_id = $featuredplayer[10];		
										
										if($pfr_id):	
											include_once('simplehtmldom/simple_html_dom.php');
											
											    $html = file_get_html('https://www.pro-football-reference.com/players/'.$firstInitCap.'/'.$pfr_id.'.htm');
											
												if($html):
												    $pfr_name = $html->find('h1', 0);
												    $pfr_height = $html->find('#meta div p[3] span[1]', 0);
													$pfr_weight = $html->find('#meta div p[3] span[2]', 0);
													
													$pfr_number = $html->find('.uni_holder', 0);
													$e = explode(" ", $pfr_number);
													$p = explode("=", $e[4]);
													$q = end($p);
													$number = rtrim($q, '"');
													
													$pfr_college = $html->find('#meta div p[5]', 0);
													$c = strip_tags($pfr_college);
													$d = str_replace("College:", "", $c );
													$e = explode(",", $d);
													$college = str_replace("(College Stats)", "", $e);
													
													$info = array(
														'name' => strip_tags($pfr_name), 
														'height' => strip_tags($pfr_height),
														'weight' => strip_tags($pfr_weight),
														'college' => trim($college[0]),
														'number' => $number,
													);
													
													printr($d, 0);
													
													$wpdb->query(
													"UPDATE wp_players
													SET weight = '$info[weight]', height = '$info[height]', college = '$info[college]', number = '$info[number]', pfruri = '$pfr_id'
													WHERE p_id = '$randomplayer'"
													);
													
													else:
													
													echo "PRO FOOTBALL REF PAGE NOT FOUND<br>";
													
												endif;
											
											else:
											
											echo "PRO FOOTBALL REF ID NOT FOUND";
											
											endif;
										?>
	
										<div class="panel-heading">
											<h3 class="panel-title">Player Info Scraped from Pro Football Reference</h3>
											
										</div>
										<div class="panel-body">
										<p>Pro Football Ref ID: <?php echo $firstInit.'/'.$pfr_id; ?></p>
										<?php printr($info,0); ?>
										</div>
					
										<?php
										
									}
									
									
								?>
							</div>
						</div>
				
					
					</div>
					

				<!-- THE ROW -->
				<div class="row">
					<div class="col-xs-12 col-sm-6 eq-box-sm">
							<div class="panel panel-bordered panel-light">
								<div class="panel-heading">
									<h3 class="panel-title">Games</h3>
								</div>
								<div class="panel-body">
									
									
									<?php 
										echo '<p>get_player_game_streak($playerid);</p>';
										echo '<p>displays games in a row played by a player.  Excludes bye weeks.</p>';
											$gamestreak = get_player_game_streak($randomplayer); 
											printr($gamestreak, 0);

                                        echo '<p>get_player_team_games($playerid)</p>';
                                        echo '<p>Returns an array of games played for a specific team by the player</p>';
                                            $playerteamcount = get_player_team_games($randomplayer);
                                            printr($playerteamcount, 0);
										
										echo '<p>get_player_record($playerid);</p>';
										echo '<p>displays just the teams that the player played for by weekid => ETS</p>';
											$record = get_player_record($randomplayer);
											printr($record, 0);
										
										echo '<p>get_player_data($playerid);</p>';
										echo '<p>gets all player data from table including record, vs, location, etc.  basically, all of players career boxscores</p>';
											$playerrecord = get_player_data($randomplayer);
											printr($playerrecord, 0);
									?>
								</div>
							</div>
					</div>
					
					
					<div class="col-xs-12 col-sm-6 eq-box-sm">
							<div class="panel panel-bordered panel-light">
								<div class="panel-heading">
									<h3 class="panel-title">Return By Season - Rookie Year Shown</h3>
								</div>
								<div class="panel-body">
									<p>get_player_season_stats($player, $year);</p>
									<?php 
										
										// this used to set transient to team data tables.  It is memory heavy so sometimes you need to disable other page functions or set printr val to 1 to die() after function.  Must manually toggle team IDS and build each.
										
										$byseason = get_player_season_stats($randomplayer, $rookie);
										if (isset($byseason)){
									    	printr($byseason, 0);
									    }


										
									?>
								</div>
							</div>
							
							
							<div class="panel panel-bordered panel-light">
								<div class="panel-heading">
									<h3 class="panel-title">Player Drafted</h3>
								</div>
								<div class="panel-body">
									<p>get_drafts_player($pid);</p>
									<p>All drafted instances by player</p>
									<?php 							
										$draftedplayer = get_drafts_player($randomplayer);
										if (isset($draftedplayer)){
									    	printr($draftedplayer, 0);
									    }
									?>
									<p>get_drafts_player_first_instance($pid);</p>
									<p>First drafted instance by player</p>
									<?php 							
										$draftedplayerfirst = get_drafts_player_first_instance($randomplayer);
										if (isset($draftedplayerfirst)){
									    	printr($draftedplayerfirst, 0);
									    }
									?>
									
								</div>
							</div>
							
							
					</div>
					
					
					
					
					
					<div class="col-xs-12 col-sm-6 eq-box-sm">
							
							<div class="panel panel-bordered panel-light">
								<div class="panel-heading">
									<h3 class="panel-title">Player Playoff Boxscores</h3>
								</div>
								<div class="panel-body">
									<p>playerplayoffs($pid);</p>
									<?php 
										$playoffsplayer = playerplayoffs($randomplayer);
										printr($playoffsplayer, 0);

									?>
								</div>
							</div>
							
							
							<div class="panel panel-bordered panel-light">
								<div class="panel-heading">
									<h3 class="panel-title">Trades Involving Player</h3>
								</div>
								<div class="panel-body">
									<p>get_trade_by_player($pid);</p>
									<?php 
										$playerbytrade = get_trade_by_player($randomplayer);
										printr($playerbytrade, 0);

									?>
								</div>
							</div>
							
							
							<div class="panel panel-bordered panel-light">
								<div class="panel-heading">
									<h3 class="panel-title">Score By Week.</h3>
									
								</div>
								<div class="panel-body">
									<p>get_one_player_week(pid, weekid);</p>
									<?php
										$firstweek = key($record);
										echo 'First Week Played as example - '.$firstweek;
										$getboxscore = get_one_player_week($randomplayer, $firstweek);
										printr($getboxscore, 0);

									?>
								</div>
							</div>
							
							
							
					</div>
					
					
					<div class="col-xs-12 col-sm-6 eq-box-sm">
							<div class="panel panel-bordered panel-light">
								<div class="panel-heading">
									<h3 class="panel-title">Career Stats</h3>
								</div>
								
								<div class="panel-body">
									<p>get_player_career_stats($pid);</p>
							<?php $career = get_player_career_stats($randomplayer); 
								printr($career, 0);
								$careerpts = $career['points'];
								echo 'Career Points: '.$careerpts;
							?>
							
								</div>
							</div>
					</div>
					
					
					<div class="col-xs-12 col-sm-6 eq-box-sm">
							<div class="panel panel-bordered panel-light">
								<div class="panel-heading">
									<h3 class="panel-title">Awards</h3>
								</div>
								
								<div class="panel-body">
									<p>get_player_award($pid);</p>
							<?php $awards = get_player_award($randomplayer); 
								printr($awards, 0);
							?>
							
								</div>
							</div>
					</div>
					
					<div class="col-xs-12 col-sm-6 eq-box-sm">
						
							<div class="panel panel-bordered panel-light">
								<div class="panel-heading">
									<h3 class="panel-title">MFL Data</h3>
								</div>
								
								<div class="panel-body">
									<p>playerid_mfl_to_pfl();</p>
									<p>get array of linked MFL and PFL ids
									<?php
										$mflids = playerid_mfl_to_pfl();
										printr($mflids, 0);
									?>
							
								</div>
							</div>



					</div>


					
				</div>
				
				
		</div>
		<?php include_once('main-nav.php'); ?>
	</div>
	
</div>



		
<?php get_footer(); ?>