$(document).ready(function() {

$("#schedulebtn").click(function() {
  //alert("Handler for .click() called.");
  var dropselectYear = $("#comboYear").val(); 
  var dropselectWeek = $("#comboWeek").val(); 
  var headurl = ('?Y='+dropselectYear+'&W='+dropselectWeek);
  window.location = headurl;	
  //alert(url);
});
    
$("#addMe").click(function(){
    counter++;

    $("#theCount").text(counter);
});
    

var counter = 0;
$("#nextplayerbtn").click(function() {	
  var headurl = ('?id=');
  window.location = headurl + counter++;
});

// leaders settings

 $('.leader-table').DataTable( {
        "order": [[ 3, "desc" ]],
        "lengthMenu": [[10, 25, 50, 100, 200, -1], [10, 25, 50, 100, 200, "All"]]
 } );
 
  $('.leaders-season').DataTable( {
        "order": [[ 1, "desc" ]],
        "lengthMenu": [[10, 50, 100, -1], [10, 50, 100, "All"]]
 } );
 

 
  $('.high-table').DataTable( {
        "order": [[ 0, "desc" ]]
 } );
 
 
 
 var posfilter = '';
 
 $("#qblink").click(function() {	
 	 var posfilter = 'QB';
 	 console.log(posfilter);
} );
  $("#rblink").click(function() {	
 	 var posfilter = 'RB';
 	 console.log(posfilter);
} );
  $("#wrlink").click(function() {	
 	 var posfilter = 'WR';
 	 console.log(posfilter);
} );
  $("#pklink").click(function() {	
 	 var posfilter = 'K';
 	 console.log(posfilter);
} );
 
 $('#nerdrankingtable').DataTable( {
        "lengthMenu": [[-1, 25, 10], ["All", 25, 10]],
        "searchCols": [
		    null,
		    { "search": posfilter, "escapeRegex": false },
		    null,
		    null,
		    null,
		    null,
		    null,
		    null
		  ]
 } );
 
    
// change column bootstrap class width of tables    

$('#quarterback_wrapper .col-sm-12').removeClass();
$('#quarterback_wrapper .col-sm-6').removeClass().addClass('col-sm-12');
$('#runningback_wrapper .col-sm-12').removeClass();
$('#runningback_wrapper .col-sm-6').removeClass().addClass('col-sm-12');
$('#receiver_wrapper .col-sm-12').removeClass();
$('#receiver_wrapper .col-sm-6').removeClass().addClass('col-sm-12');
$('#kicker_wrapper .col-sm-12').removeClass();
$('#kicker_wrapper .col-sm-6').removeClass().addClass('col-sm-12');
$('#high_wrapper .col-sm-12').removeClass();
$('#high_wrapper .col-sm-6').removeClass().addClass('col-sm-12');
$('#demo-dt-basic_wrapper .col-sm-12').removeClass();
$('#demo-dt-basic_wrapper .col-sm-6').removeClass().addClass('col-sm-12');	
	

var filtering = $('#demo-foo-filtering');

filtering.footable().on('footable_filtering', function (e) {
	var selected = $('#demo-foo-filter-status').find(':selected').val();
	e.filter += (e.filter && e.filter.length > 0) ? ' ' + selected : selected;
	e.clear = !e.filter;
});

// Filter status
$('#demo-foo-filter-status').change(function (e) {
	e.preventDefault();
	filtering.trigger('footable_filter', {filter: $(this).val()});
});

// Search input
$('#demo-foo-search').on('input', function (e) {
	e.preventDefault();
	filtering.trigger('footable_filter', {filter: $(this).val()});
});
	      	
       
$('.seasonbar').sparkline('html', 
	{ 
		type: 'bar', 
		barColor: '#5fa2dd', 
		height: '100px', 
		barWidth: '16px', 
		barSpacing:'5px' 
		
	}); 
	
$(".chzn-select").chosen(); 

$("#playerSelect").click(function() {
  //alert("Handler for .click() called.");
  var dropselect6 = $("#playerDrop").val(); 
  var headurl = (''+dropselect6);
  window.location = headurl;	
  	//alert(url);
});	

$("#yearbtn").click(function() {
  //alert("Handler for .click() called.");
  var yeardropselect = $("#pickyear").val(); 
  var headurl = ('?id='+yeardropselect);
  window.location = headurl;	
  	//alert(url);
});	

$("#teambtn").click(function() {
  //alert("Handler for .click() called.");
  var teamdropselect = $("#pickteam").val(); 
  var headurl = ('?id='+teamdropselect);
  window.location = headurl;	
  	//alert(url);
});	


$("img.player-image").error(function () { 
    $(this).hide();
    //or $(this).css({visibility:"hidden"}); 
});




	   
	        
}); /* CLOSE DOCUMENT */