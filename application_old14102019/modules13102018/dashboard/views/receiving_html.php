<style type="text/css">
	.highcharts-credits{
		display: none;
	}
	.container{
		width: 50%;
		float: left;
	}
	.container > div > div{
		border: 1px solid #888;
	}
	.container > div{
		width: 100%;
		/* padding-bottom: 5px; */
		max-width: 100%;
	}
	.screen {
		height: 100vh;
		max-height: 100vh;
	}
	.odometer {
		display:inline
}
.yorfont {
	font-size: 75px;
	text-align: center;
	/*margin-top: 10%;*/
}
.yorfont2 {
	font-size: 50px;
	text-align: center;
}
</style>
<div class="container">
	<div>
		<div id="receiving" style="width: 100%; height: 33.333%;"></div>
	</div>

</div>
<div class="container">
	<div>
		<div id="sp2" style="width: 100%; height: 33.333;"></div>
	</div>

</div>
<div class="container">
	<div>
		<div id="stuffing" style="width: 100%; height: 33.333;"></div>
	</div>

</div>
<div class="container">
	<div>
		<div id="repo" style="width: 100%; height: 33.333;"></div>
	</div>

</div>
<div class="container">
	<div>
		<div id="stripping" style="width: 100%; height: 33.333;"></div>
	</div>

</div>
<div class="container">
	<div>
		<div id="yor" style="width: 100%; height: 33.333;">
			<div style="text-align: center; margin-top:-10px;"><h3> Y.O.R </h3></div>
			<div class="yorfont">
				<table align="center">
					<tr>
						<td><div id="yorpercent" class="yorpercent" style="display:inline">0</div></td>
						<td><div class="odometer" style="display:inline">%</div></td>
					</tr>
				</table>
			</div>
			<div class="yorfont2">
				<table align="center">
					<tr>
						<td><div id="stacking" class="stacking" style="display:inline">80</div></td>
						<td><div class="odometer">/</div></td>
						<td><div id="total" class="total" style="display:inline">3920</div></td>
					</tr>
				</table>
			</div>
		</div>
	</div>

</div>

      <script language = "JavaScript">
         $(document).ready(function() {
         			//alert($(window).height());

         			$(window).resize(function(){
         				$('.container > div > div').height(($(window).height() - 50) /3);
         				//$('.container > div > div').width($(window).width() /2);
         			});
         			$('.container > div > div').height(($(window).height() - 50) /3);
         			//$('.container > div > div').width($(window).width() /2);
					function requestDataRec() {
						$.ajax({
							url: '<?php echo MAIN_DOMAIN ?>/dashboard/get_data_receiving_json',
								success: function(point) {
									console.log('aaa');
									var done = parseInt(point[0].DONE);
									var outstanding = parseInt(point[0].OUTSTANDING);
									var total = parseInt(point[0].TOTAL);
									var percent_done = Math.round(done/total*100);
									var percent_out = Math.round(outstanding/total*100);
									receiving.setTitle(null, { text: 'TOTAL :' + total});
										receiving.series[0].remove();
										receiving.addSeries({
											name: 'Total',
											colorByPoint: true,
											data: [
												{
														name: 'Finished : ' + done,
														y: percent_done
												}, {
														name: 'Outstanding : ' + outstanding,
														y: percent_out,
														sliced: true,
														selected: true
												},
											]
										});
										//setTimeout(requestDataRec, 60000);
										var socket = io.connect(Contants.getNodeServer());
									      socket.on('updateReceiving', function() {
									        requestDataRec();
									      });
								},
								failure: function(point){

								},
								cache: false
						});
				};

					function requestDataSp2() {
						$.ajax({
								url: '<?php echo MAIN_DOMAIN ?>/dashboard/getDataSp2',
								success: function(point) {
									var done = parseInt(point[0].DONE);
									var outstanding = parseInt(point[0].OUTSTANDING);
									var total = parseInt(point[0].TOTAL);
									var percent_done = Math.round(done/total*100);
									var percent_out = Math.round(outstanding/total*100);
									sp2.setTitle(null, { text: 'TOTAL :' + total});
										sp2.series[0].remove();
										sp2.addSeries({
											name: 'Total',
											colorByPoint: true,
											data: [
												{
														name: 'Finished : ' + done,
														y: percent_done
												}, {
														name: 'Outstanding : ' + outstanding,
														y: percent_out,
														sliced: true,
														selected: true
												},
											]
										});
										//setTimeout(requestDataSp2, 60000);
										var socket = io.connect(Contants.getNodeServer());
									      socket.on('updateSP2', function() {
									        requestDataSp2();
									      });
								},
								cache: false
						});
				};

				function requestDataStuffing() {
					$.ajax({
							url: '<?php echo MAIN_DOMAIN ?>/dashboard/getDataStuffing',
							success: function(point) {
								var done = parseInt(point[0].DONE);
								var outstanding = parseInt(point[0].OUTSTANDING);
								var total = parseInt(point[0].TOTAL);
								var percent_done = Math.round(done/total*100);
								var percent_out = Math.round(outstanding/total*100);
								stuffing.setTitle(null, { text: 'TOTAL :' + total});
									stuffing.series[0].remove();
									stuffing.addSeries({
										name: 'Total',
										colorByPoint: true,
										data: [
											{
													name: 'Finished : ' + done,
													y: percent_done
											}, {
													name: 'Outstanding : ' + outstanding,
													y: percent_out,
													sliced: true,
													selected: true
											},
										]
									});
									//setTimeout(requestDataStuffing, 60000);
									var socket = io.connect(Contants.getNodeServer());
									      socket.on('updateStuffingProcess', function() {
									        requestDataStuffing();
									      });
							},
							cache: false
					});
			};

			function requestDataStripping() {
				$.ajax({
						url: '<?php echo MAIN_DOMAIN ?>/dashboard/getDataStripping',
						success: function(point) {
							var done = parseInt(point[0].DONE);
							var outstanding = parseInt(point[0].OUTSTANDING);
							var total = parseInt(point[0].TOTAL);
							var percent_done = Math.round(done/total*100);
							var percent_out = Math.round(outstanding/total*100);
							stripping.setTitle(null, { text: 'TOTAL :' + total});
								stripping.series[0].remove();
								stripping.addSeries({
									name: 'Total',
									colorByPoint: true,
									data: [
										{
												name: 'Finished : ' + done,
												y: percent_done
										}, {
												name: 'Outstanding : ' + outstanding,
												y: percent_out,
												sliced: true,
												selected: true
										},
									]
								});
								//setTimeout(requestDataStripping, 60000);
								var socket = io.connect(Contants.getNodeServer());
									      socket.on('updateStrippingProcess', function() {
									        requestDataStripping();
									      });
						},
						cache: false
				});
		};

			function requestDataRepo() {
				$.ajax({
						url: '<?php echo MAIN_DOMAIN ?>/dashboard/getDataRepo',
						success: function(point) {
							var done = parseInt(point[0].DONE);
							var outstanding = parseInt(point[0].OUTSTANDING);
							var total = parseInt(point[0].TOTAL);
							var percent_done = Math.round(done/total*100);
							var percent_out = Math.round(outstanding/total*100);
							repo.setTitle(null, { text: 'TOTAL :' + total});
								repo.series[0].remove();
								repo.addSeries({
									name: 'Total',
									colorByPoint: true,
									data: [
										{
												name: 'Finished : ' + done,
												y: percent_done
										}, {
												name: 'Outstanding : ' + outstanding,
												y: percent_out,
												sliced: true,
												selected: true
										},
									]
								});
								//setTimeout(requestDataRepo, 60000);
								var socket = io.connect(Contants.getNodeServer());
									      socket.on('updateRepo', function() {
									        requestDataRepo();
									      });
						},
						cache: false
				});
		};

		receiving = Highcharts.chart('receiving', {
				chart: {
						plotBackgroundColor: null,
						plotBorderWidth: null,
						plotShadow: false,
						type: 'pie',
						options3d: {
					enabled: true,
					alpha: 45,
					beta: 0,
				},
				events: {
							load: requestDataRec
					}
				},
				title: {
						text: 'Receiving'
				},
				plotOptions: {
						pie: {
							depth: 35,
								allowPointSelect: true,
								cursor: 'pointer',
								dataLabels: {
										enabled: false
								},
								showInLegend: true,
								dataLabels: {
							enabled: true,
							format: '{point.y}%'
							}
						}
				},
				series: [{
						name: 'Total',
						colorByPoint: true
				}]
		});


			sp2 = Highcharts.chart('sp2', {
			    chart: {
			        plotBackgroundColor: null,
			        plotBorderWidth: null,
			        plotShadow: false,
			        type: 'pie',
							options3d: {
						enabled: true,
						alpha: 45,
						beta: 0,
					},
					events: {
								load: requestDataSp2
						}
			    },
			    title: {
			        text: 'SP2'
			    },
			    plotOptions: {
			        pie: {
								depth: 35,
			            allowPointSelect: true,
									colors: ["#90EE7E", "#7798BF"],
			            cursor: 'pointer',
			            dataLabels: {
			                enabled: false
			            },
			            showInLegend: true,
									dataLabels: {
								enabled: true,
								format: '{point.y}%'
								}
			        }
			    },
			    series: [{
			        name: 'Total',
			        colorByPoint: true
			    }]
			});

			stuffing = Highcharts.chart('stuffing', {
				chart: {
						plotBackgroundColor: null,
						plotBorderWidth: null,
						plotShadow: false,
						type: 'pie',
						options3d: {
					enabled: true,
					alpha: 45,
					beta: 0
				},
				events: {
							load: requestDataStuffing
					}
				},
				title: {
						text: 'Stuffing'
				},
				plotOptions: {
						pie: {
								allowPointSelect: true,
								depth: 35,
								// innerSize: '40%',
								colors: ["#FF0066", "#AAEEEE"],
								cursor: 'pointer',
								showInLegend: true,
								dataLabels: {
								enabled: true,
							format: '{point.y}%',
							inside: true,
							// distance: -50,
							}
						}
				},
				series: [{
						name: 'Total',
						colorByPoint: true
				}]
			});

			repo = Highcharts.chart('repo', {
				chart: {
						plotBackgroundColor: null,
						plotBorderWidth: null,
						plotShadow: false,
						type: 'pie',
						options3d: {
					enabled: true,
					alpha: 45,
					beta: 0
				},
				events: {
							load: requestDataRepo
					}
				},
				title: {
						text: 'REPO'
				},
				plotOptions: {
						pie: {
								allowPointSelect: true,
								depth: 35,
								// innerSize: '40%',
								colors: ["#EEAAEE", "#55BF3B"],
								cursor: 'pointer',
								showInLegend: true,
								dataLabels: {
								enabled: true,
							format: '{point.y}%',
							inside: true,
							// distance: -50,
							}
						}
				},
				series: [{
						name: 'Total',
						colorByPoint: true
				}]
			});

			stripping = Highcharts.chart('stripping', {
				chart: {
						plotBackgroundColor: null,
						plotBorderWidth: null,
						plotShadow: false,
						type: 'pie',
						options3d: {
					enabled: true,
					alpha: 45,
					beta: 0
				},
				events: {
							load: requestDataStripping
					}
				},
				title: {
						text: 'Stripping'
				},
				plotOptions: {
						pie: {
								allowPointSelect: true,
								depth: 35,
								// innerSize: '40%',
								colors: ["#DF5353", "#7798BF"],
								cursor: 'pointer',
								showInLegend: true,
								dataLabels: {
								enabled: true,
							format: '{point.y}%',
							inside: true,
							// distance: -50,
							}
						}
				},
				series: [{
						name: 'Total',
						colorByPoint: true
				}]
			});

			//set value
			var yorpercent = new Odometer({
			  el: document.querySelector('.yorpercent')
			});

			var stacking = new Odometer({
			  el: document.querySelector('.stacking')
			});

			var total = new Odometer({
			  el: document.querySelector('.total')
			});

			function requestDataYor() {
				$.ajax({
						url: '<?php echo MAIN_DOMAIN ?>/dashboard/getDataYor',
						success: function(point) {
								var stacking_ = parseInt(point.STACKING);
								var yor = parseInt(point.YOR);
								var total_ = Math.round(stacking_/yor*100);
								var rand = Math.floor(Math.random() * 10);
								var rand2 = Math.floor(Math.random() * 15);
								var rand3 = Math.floor(Math.random() * 20);
								// yorpercent.innerHTML = rand;
								yorpercent.update(total_);
								stacking.update(stacking_);
								total.update(yor);
								//setTimeout(requestDataYor, 1000);
								var socket = io.connect(Contants.getNodeServer());
									      socket.on('updateYardMonitoring', function() {
									        requestDataYor();
									      });
						},
						cache: false
				});
		};
		requestDataYor();

  });


</script>
