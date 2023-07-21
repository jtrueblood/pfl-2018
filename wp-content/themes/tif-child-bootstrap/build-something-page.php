<?php
/*
 * Template Name: Get Probowl Player Boxcores
 * Description: Get
 */
 ?>

<?php get_header(); ?>

<?php

 ?>

<div class="boxed">
			
        <!--CONTENT CONTAINER-->
        <div id="content-container">

            <!--Page content-->
            <div id="page-content">

                <?php
                //printr($new['2001BradQB'],0);
                //printr($getseasonids , 0);
                //printr($rosterarray, 0);
                //printr($players, 0);
                //printr($teamrosters, 0);
                ?>

                <div class="col-xs-12">
                        <div class="panel">
                            <div class="panel-heading">
                                <h3 class="panel-title"><?php echo $teamid; ?> History Timeline</h3>

                            </div>
                        </div>
                </div>
            </div>

            </div><!--End page content-->

        </div><!--END CONTENT CONTAINER-->


    <?php include_once('main-nav.php'); ?>
    <?php include_once('aside.php'); ?>

</div>

<?php get_footer(); ?>