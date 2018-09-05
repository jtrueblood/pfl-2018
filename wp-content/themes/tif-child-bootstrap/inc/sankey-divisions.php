<script type="text/javascript">
						jQuery(document).ready(function() {
							
							Highcharts.chart('sankeydivisions', {

						    title: {
						        text: 'Highcharts Sankey Diagram'
						    },
						
						    series: [{
						        keys: ['from', 'to', 'weight'],
						        data: [
						            ['Euro-Trashers', 'PFL Division', 4 ],
						            ['Euro-Trashers', 'EGAD', 23 ],
									['Peppers', 'PFL Division', 4 ],
						            ['Peppers', 'EGAD', 23 ],
									['Warriorz', 'PFL Division', 4 ],
						            ['Warriorz', 'EGAD', 23 ],
						            ['Red-Barons', 'PFL Division', 3 ],
						            ['Bustas', 'PFL Division', 1 ],
						            ['Bustas', 'EGAD', 17 ],
						            ['Rising Sons', 'EGAD', 15 ],
						            ['Rising Sons', 'MGAC', 6 ],
						            ['Rising Sons', 'EGAD', 2 ],
						            ['Mad Max', 'EGAD', 4 ],
						            ['Pherns', 'EGAD', 15 ],
						            ['Pherns', 'MGAC', 6 ],
									['Attack', 'MGAC', 6 ],
									['Hats', 'EGAD', 2 ],
									['Destruction', 'MGAC', 2 ],
						            ['C-Men', 'PFL Division', 4 ],
						            ['C-Men', 'DGAS', 23 ],
									['Tsongas', 'PFL Division', 4 ],
						            ['Tsongas', 'DGAS', 23 ],
									['Bulls', 'PFL Division', 4 ],
						            ['Bulls', 'DGAS', 23 ],
						            ['SNiners', 'PFL Division', 4 ],
						            ['SNiners', 'DGAS', 23 ],
						            
						        ],
						        type: 'sankey',
						        name: 'Sankey demo series'
						    }]
						
						});
							
						});	
						</script>
						
						<div id="sankeydivisions"></div>
						
						
						
						