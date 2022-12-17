jQuery(document).ready(function($) {

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
	
	// Leaders Tables
	
	$('.leader-table').DataTable( {
	    "order": [[ 3, "desc" ]],
	    "lengthMenu": [[25, 50, 100, 200, -1], [25, 50, 100, 200, "All"]]
	} );
	
	$('.leaders-season').DataTable( {
	    "order": [[ 1, "desc" ]],
	    "lengthMenu": [[25, 50, 100, -1], [25, 50, 100, "All"]]
	} );
	
	$('.high-table').DataTable( {
	    "order": [[ 0, "desc" ]]
	} );
	
	$('.hall-table').DataTable( {
	    "order": [[ 2, "asc" ]],
	    "lengthMenu": [[100, -1], [50, 100, "All"]]
	} );
	
	$('.week-standings-table').DataTable( {
	    "order": [[ 1, "desc" ], [ 4, "desc" ]],
	    "paging":   false,
	    "info":     false,
	    "search": false
	} );

	//Transactions Table
	$('.transactions-table').DataTable( {
		"order": [[ 1, "desc" ],[ 2, "desc" ]],
		"paging":   false,
		"info":     false,
		"search": false
	} );
	
	// Draft Strategy Tables

	$('.qb-draft-table-new').DataTable( {
	    "order": [[ 17, "desc" ]],
		"lengthMenu": [[100, -1], ["All"]],
		"columnDefs": [
			{ "orderable": false, "targets": 0 }
		]
	} );

	$('.rb-draft-table-new').DataTable( {
		"order": [[ 14, "desc" ]],
		"lengthMenu": [[25, 50, 100, -1], ["All"]]
	} );

	$('.wr-draft-table-new').DataTable( {
		"order": [[ 14, "desc" ]],
		"lengthMenu": [[25, 50, 100, -1], ["All"]]
	} );

	$('.pk-draft-table-new').DataTable( {
		"order": [[ 11, "desc" ]],
		"lengthMenu": [[100, -1], ["All"]]
	} );

	/*
	$('.rb-draft-table').DataTable( {
	    "order": [[ 16, "desc" ]],
	    "lengthMenu": [[50, -1], [25, 50, 100, "All"]]
	} );
	 
	$('.wr-draft-table').DataTable( {
	    "order": [[ 16, "desc" ]],
	    "lengthMenu": [[50, -1], [25, 50, 100, "All"]]
	} );
	
	$('.pk-draft-table').DataTable( {
	    "order": [[ 14, "desc" ]],
	    "lengthMenu": [[50, -1], [25, 50, 100, "All"]]
	} );
*/

	
	var t = $('.qb-draft-table').DataTable( {
        "columnDefs": [ {
            "searchable": false,
            "orderable": false,
            "targets": 0
        } ],
        "order": [[ 17, "desc" ]],
	    "lengthMenu": [[50, -1], [25, 50, "All"]]
    } );
 
    t.on( 'order.dt search.dt', function () {
        t.column(0, {search:'applied', order:'applied'}).nodes().each( function (cell, i) {
            cell.innerHTML = i+1;
        } );
    } ).draw();
	  
	var t = $('.rb-draft-table').DataTable( {
        "columnDefs": [ {
            "searchable": false,
            "orderable": false,
            "targets": 0
        } ],
        "order": [[ 16, "desc" ]],
	    "lengthMenu": [[50, -1], [25, 50, 100, "All"]]
    } );
 
    t.on( 'order.dt search.dt', function () {
        t.column(0, {search:'applied', order:'applied'}).nodes().each( function (cell, i) {
            cell.innerHTML = i+1;
        } );
    } ).draw();  
    
    var t = $('.wr-draft-table').DataTable( {
        "columnDefs": [ {
            "searchable": false,
            "orderable": false,
            "targets": 0
        } ],
        "order": [[ 16, "desc" ]],
	    "lengthMenu": [[50, -1], [25, 50, 100, "All"]]
    } );
 
    t.on( 'order.dt search.dt', function () {
        t.column(0, {search:'applied', order:'applied'}).nodes().each( function (cell, i) {
            cell.innerHTML = i+1;
        } );
    } ).draw(); 
    
    var t = $('.te-draft-table').DataTable( {
        "columnDefs": [ {
            "searchable": false,
            "orderable": false,
            "targets": 0
        } ],
        "order": [[ 16, "desc" ]],
	    "lengthMenu": [[50, -1], [25, 50, 100, "All"]]
    } );
 
    t.on( 'order.dt search.dt', function () {
        t.column(0, {search:'applied', order:'applied'}).nodes().each( function (cell, i) {
            cell.innerHTML = i+1;
        } );
    } ).draw(); 
    
    
    var t = $('.pk-draft-table').DataTable( {
        "columnDefs": [ {
            "searchable": false,
            "orderable": false,
            "targets": 0
        } ],
        "order": [[ 14, "desc" ]],
	    "lengthMenu": [[50, -1], [25, 50, 100, "All"]]
    } );
 
    t.on( 'order.dt search.dt', function () {
        t.column(0, {search:'applied', order:'applied'}).nodes().each( function (cell, i) {
            cell.innerHTML = i+1;
        } );
    } ).draw(); 
	  
	  
	 
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
			barSpacing:'5px',
			yAxis: [{
		        max: 200,
		        title: {
		            text: 'Points'
		        }
		    }] 
			
		}); 
		
	$(".chzn-select").chosen(); 
	
	$("#playerSelect").click(function() {
	  //alert("Handler for .click() called.");
	  var dropselect6 = $("#playerDrop").val(); 
	  var headurl = (''+dropselect6);
	  window.location = headurl;	
	  //alert(url);
	});	
	
	$("#playerSelectScrape").click(function() {
	  //alert("Handler for .click() called.");
	  var dropselect23 = $("#playerDropScrape").val();
	  var headurl = ('/scrape-pro-football-ref/'+dropselect23);
	  window.location = headurl;	
	  //alert(url);
	});

	$("#playerSelectScrapeNew").click(function() {
		//alert("Handler for .click() called.");
		var dropselect22 = $("#playerDropScrapeNew").val();
		var headurl = ('/scrape-pro-football-ref-new/'+dropselect22);
		window.location = headurl;
		//alert(url);
	});

	$("#playerSelectScrapeNumber").click(function() {
		var dropselect15 = $("#playerDropScrapeNumber").val();
		var headurl = ('/scrape-pfr-for-numbers/'+dropselect15);
		window.location = headurl;
	});

	$("#playerSelectScrapeTwoPt").click(function() {
		var dropselect16 = $("#playerDropScrapeTwoPt").val();
		var headurl = ('/scrape-pfr-for-2pt/'+dropselect16);
		window.location = headurl;
	});
	
	
	$("#playerSelectUnis").click(function() {
	  //alert("Handler for .click() called.");
	  var dropselect10 = $("#playerSelectUnisDrop").val(); 
	  var headurl = ('/uniforms-helmets/'+dropselect10);
	  window.location = headurl;	
	  //alert(url);
	});	
	
	
	$("#teamSelect").click(function() {
	  //alert("Handler for .click() called.");
	  var dropselect7 = $("#teamDrop").val(); 
	  var headurl = (''+dropselect7);
	  window.location = headurl;	
	});	
	
	$("#yearbtn").click(function() {
	  //alert("Handler for .click() called.");
	  var yeardropselect = $("#pickyear").val(); 
	  var headurl = ('?id='+yeardropselect);
	  window.location = headurl;	
	});	
	
	$("#teambtn").click(function() {
	  //alert("Handler for .click() called.");
	  var teamdropselect = $("#pickteam").val(); 
	  var headurl = ('?id='+teamdropselect);
	  window.location = headurl;	
	});	
	
	
	$("img.player-image").error(function () { 
	    $(this).hide();
	    //or $(this).css({visibility:"hidden"}); 
	});

	// HANDLE BROKEN IMAGES
	$("img").error(function () {
		$(this).unbind("error").attr("src", "/wp-content/uploads/circ_logo_trans.png");
	});

	// Table row and column highlight on Scorigami Table
	$('.myCell').on('click', function() {
		$(this).closest('tr').addClass('highlight');
		$(this).closest('table').find('.myCell:nth-child(' + ($(this).index() + 1) + ')').addClass('highlight');
	});
	$('.myCell').on('mouseout', function() {
		$(this).closest('tr').removeClass('highlight');
		$(this).closest('table').find('.myCell:nth-child(' + ($(this).index() + 1) + ')').removeClass('highlight');
	});


}); /* CLOSE DOCUMENT */