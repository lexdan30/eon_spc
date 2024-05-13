app.controller('OMHomeController', ['$scope', '$rootScope', '$location', '$routeParams', '$http', '$cookieStore', '$timeout', 'spinnerService', '$filter', 'Upload', 'DTOptionsBuilder', 'DTColumnBuilder', '$q', '$compile', 'textAngularManager', '$templateRequest', '$sce', '$compile',
    function ($scope, $rootScope, $location, $routeParams, $http, $cookieStore, $timeout, spinnerService, $filter, Upload, DTOptionsBuilder, DTColumnBuilder, $q, $compile, textAngularManager, $templateRequest, $sce) {

        $scope.headerTemplate = "view/admin/header/index.html";
        $scope.leftNavigationTemplate = "view/admin/om/sidebar/index.html";
        $scope.footerTemplate = "view/admin/footer/index.html";
		$scope.search_month='';
        $scope.search_year=''; 
        $scope.dashboard = {
            values: {
                loggedid: $cookieStore.get('acct_id'),
                accountid: $cookieStore.get('acct_id'),
                accteid: $cookieStore.get('acct_eid'),
                accouttype: $cookieStore.get('acct_type'),
                accoutfname: $cookieStore.get('acct_fname'),
                accoutlname: $cookieStore.get('acct_lname'),
                acct_loc: $cookieStore.get('acct_loc'),
                username	: $cookieStore.get('username'),	
                userInformation: null,
                accounts: [],
                leaves: [],
                period: [],
                daterange: '',
                late_tbl: [],
                absent_tbl: [],
                late_details: [],
                absent_details: [],
                present_tbl: [],
                present_details: [],
                leaves_tbl: [],
                applications_data: [],
                bdates: [],
                dept_ctr: [], 
                dept_choose: ''
            },
            active: function () {
                $rootScope.getHRaccess();
                var urlData = {
                    'username': $scope.dashboard.values.username,
                    'accountid': $scope.dashboard.values.accountid
                }
                $http.post(apiUrl + 'tmsmems/loggedinuser.php', urlData)
                    .then(function (response, status) {
                        var data = response.data;
                        if (data.status == 'error') {
                            $rootScope.modalDanger();
                        } else {
                            $scope.dashboard.values.userInformation = data;
                        }
                    }, function (response) {
                        $rootScope.modalDanger();
                    });
            },
            setup: function () {
                // var urlData = {
                //     'accountid': $scope.dashboard.values.accountid
                // }
                // $http.post(apiUrl + 'admin/tk/setup/settings.php', urlData)
                //     .then(function (response, status) {
                //         var data = response.data;
                //         $scope.dashboard.values.accounts = data.accounts;
                //         $scope.dashboard.values.leaves = data.leaves;
                //         $scope.dashboard.values.period = data.period;
				// 		$scope.dashboard.values.department 	= data.departments;	
                //         $scope.dashboard.values.joblocation = data.joblocation;
                //         $scope.dashboard.values.daterange = moment($scope.dashboard.values.period.pay_start).format('MM/DD/YYYY') + ' - ' + moment($scope.dashboard.values.period.pay_end).format('MM/DD/YYYY');

                //         $("#picker1").daterangepicker({
                //             startDate: moment($scope.dashboard.values.period.pay_start).format('MM/DD/YYYY'),
                //             endDate: moment($scope.dashboard.values.period.pay_end).format('MM/DD/YYYY'),
                //             locale: {
                //                 cancelLabel: 'Clear',
                //                 format: 'MM/DD/YYYY'
                //             }
                //         });
                //         $("#picker1").on('apply.daterangepicker', function (ev, picker) {
                //             $timeout(function () {
                //                 $scope.attendance_counter(); 
                //                 return;
                //             }, 100);
                //         });
                //         $("#picker1").on('cancel.daterangepicker', function (ev, picker) {
                //             $timeout(function () {
                //                 var dateText = moment($scope.dashboard.values.period.pay_start).format('MM/DD/YYYY') + ' - ' + moment($scope.dashboard.values.period.pay_end).format('MM/DD/YYYY');
                //                 $("#picker1").val(dateText);
                //                 $scope.dashboard.values.daterange = dateText;
                //                 $scope.attendance_counter(); 
                //                 return;
                //             }, 100);

                //         });
                //         $scope.attendance_counter();
                //         $scope.birthdate_update();
                //        //$scope.dept_attendance_ctr();
                //     }, function (response) {
                //         $rootScope.modalDanger();
                //     });
            }
        }
		
		$scope.getTotalYTD = function(){
            var urlData = {
                'accountid': $scope.dashboard.values.accountid
            }
            $http.post(apiUrl + 'admin/hr/home/totalYTD.php', urlData)
                .then(function (response, status) {
                    var data = response.data;

                    if (data.status == 'error') {
                        $rootScope.modalDanger();
                    } else {
                        $scope.totalemployeesYTD = data;

                    }
                }, function (response) {
                    $rootScope.modalDanger();
                });

        }
		
		$scope.barchartdivyear = function () {
            var ctx = document.getElementById("bar1").getContext("2d");
            var d = new Date();
            var year = d.getFullYear();
            $('#selectyr').val(year);
            $timeout(function () {
                if($('#selectyr').val() == '' || $('#selectyr').val() == undefined){
                   
                    $('#selectyr').val(year);
                }
            }, 200);
            var urlData = {
                'accountid': $scope.dashboard.values.accountid,
                'search_year': $scope.search_year
            }
            
            $http.post(apiUrl + 'admin/hr/home/OTbyYEar.php', urlData)
            .then(function (response, status) {
                var data = response.data;

                $scope.yearss = data.yearss;

                if( data.status == "error" ){
                    $rootScope.modalDanger();
                }else{
                    $scope.data = {
                        labels: ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"],
                        datasets: [{
                            label: new Date().getFullYear(),
                            backgroundColor: "#a4a0a0",
                            data: data.OTyear
                        }]
                    };
                    var myBarChart = new Chart(ctx, {
                        type: 'bar',
                        // responsive: false,
                        data: $scope.data,
                        options: {
                            legend: {
                                display: false,
                                position:'bottom',
                                labels: {
                                    fontColor: 'black',
                                    
                                }
                            }
                        }
                        // options: {
                        //     barValueSpacing: 20,
                        //     scales: {
                        //     	yAxes: [{
                        //     		ticks: {
                        //     			min: 0,
                        //     		}
                        //     	}]
                        //     }
                        // }
                    });
                }
            }, function (response) {
                $rootScope.modalDanger();
            });
        }
		
		$scope.barchartdivmonth = function () {
            var ctx = document.getElementById("bar2").getContext("2d");
            var d = new Date();
            var month = d.getMonth();
            var months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
            $timeout(function () {
                if($('#selectmonth').val() == '' || $('#selectmonth').val() == undefined){
                    $('#selectmonth').val(month+1);
                }
            }, 1000);
            var date = new Date();

            var urlData = {
                'accountid': $scope.dashboard.values.accountid,
                'search_month' :$scope.search_month,
            }
            $http.post(apiUrl + 'admin/hr/home/OTbyMonth.php', urlData)
            .then(function (response, status) {
                var data = response.data;
                if( data.status == "error" ){
                    $rootScope.modalDanger();
                }else{
                    $scope.data = {
                        labels: data.emps,
                        datasets: [{
                            label: months[date.getMonth()],
                            backgroundColor: "#a4a0a0",
                            data: data.overtime
                        }],
                     
                    };
                    var myBarChart = new Chart(ctx, {
                        type: 'bar',
                        data: $scope.data,
                        options: {
                            legend: {
                                display: false,
                                position:'bottom',
                                labels: {
                                    fontColor: 'black',
                                    
                                }
                            }
                            // barValueSpacing: 20,
                            // scales: {
                            // 	yAxes: [{
                            // 		ticks: {
                            // 			min: 0,
                            // 		}
                            // 	}]
                            // }
                        }
                    });
                }
            }, function (response) {
                $rootScope.modalDanger();
            });
        }

        $scope.piechartdivs = function () {
            $timeout(function () {
                $scope.pie_sum1 = 0;
                $scope.pie_labels1 = [];
                $scope.pie_data1 = [];
                $scope.pie_colour1 = [];
                $scope.pie_options1 = {};
           
                var data_r = $("#dtrange_ot").val().split(" - ");
            
                var urlData = {
                    'accountid': $scope.dashboard.values.accountid, 
                    'search_date' : $("#dtrange_ot").val(),
                    'dateFrom'  : moment(data_r[0]).format('YYYY-MM-DD'),
                    'dateTo'    : moment(data_r[1]).format('YYYY-MM-DD')
                }
                
 
               
                $http.post(apiUrl + 'admin/hr/home/piechart_VS.php', urlData)
                    .then(function (response, status) {
                        var data = response.data;

                        // $scope.pie_pay_start = data.pay_start;
                        // $scope.pie_pay_end = data.pay_end;

                        if (data.status == "error") {
                            $rootScope.modalDanger();
                        } else {
                            $scope.pie_labels1 = data.lbl;
                            $scope.pie_data1 = data.ctr;
                            $scope.pie_colour1 = data.colour;
                            $scope.pie_sum1 = data.sum;
                            // $scope.pie_href = data.href;
                            // console.log(data);
                            $scope.pie_options1 = {
                                tooltips: {
                                    enabled: true
                                },
                                legend: {
                                    display: true,
                                    position: "bottom",
                                    labels: {
                                        boxWidth: 15,
                                        fontSize: 10,
                                        fontColor: 'rgb(255, 99, 132)',
                                        padding: -50,
                                        fontStyle: 'italic',
                                        generateLabels: function (chart) {
                                            var bg = chart.data.datasets[0].backgroundColor
                                            var ele = "<ul class='legend-labels'>";
                                            chart.data.datasets[0].data.forEach(function (item, index) {
                                                var perc = ((item / $scope.divisor) * 100).toFixed(2);
                                                ele = ele + '<li class="plabels"><span class="boxl" style="background-color:' + bg[index] + ';color:white">' + $scope.pie_data1[index] + '</span>  ' + $scope.pie_labels1[index] + '</li></a>';
                                            });
                                            ele = ele + "</ul>";
                                            $(".legend-scale").html(ele);
                                            return chart.generateLegend();
                                        }
                                    }
                                }
                            };
                        }
                    }, function (response) {
                        $rootScope.modalDanger();
                    });
            }, 1000);
        }

        $scope.barchartdivsOTbyTrans = function () {
            var ctx = document.getElementById("bar3").getContext("2d");
            var urlData = {
                'accountid': $scope.dashboard.values.accountid
            }
            $http.post(apiUrl + 'admin/hr/home/OTbyTrans.php', urlData)
            .then(function (response, status) {
                var data = response.data;
                if( data.status == "error" ){
                    $rootScope.modalDanger();
                }else{
                    $scope.data = {
                        labels: ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"],
                        datasets: [{
                            label: "REGOT",
                            backgroundColor: "#44a3c0",
                            data: data.regot
                        }, {
                            label: "REGOT>8",
                            backgroundColor: "#c55a11", 
                            data: data.regotg8
                        }, {
                            label: "SHWDOT",
                            backgroundColor: "#a4a0a0",
                            data: data.swhdot
                        },{
                            label: "SHWDOT>8",
                            backgroundColor: "#ffc000",
                            data: data.swhdotg8
                        },{
                            label: "LHWDOT",
                            backgroundColor: "#4472c4",
                            data: data.lhwdot
                        },{
                            label: "LHWDOT>8",
                            backgroundColor: "#70ad47",
                            data: data.lhwdotg8
                        },{
                            label: "RDOT",
                            backgroundColor: "#FF5733",
                            data: data.arr_rdot
                        },{
                            label: "RDOT>8",
                            backgroundColor: "#FFDD33",
                            data: data.arr_rdotg8
                        },{
                            label: "LHRDOT",
                            backgroundColor: "#3349FF",
                            data: data.arr_lhrdot
                        },{
                            label: "LHRDOT>8",
                            backgroundColor: "#7A33FF",
                            data: data.arr_lhrdotg8
                        },{
                            label: "SHRDOT",
                            backgroundColor: "#FF33F3",
                            data: data.arr_shrdot
                        },{
                            label: "SHRDOT>8",
                            backgroundColor: "#FF3342",
                            data: data.arr_shrdotg8
                        },{
                            label: "LSHOT",
                            backgroundColor: "#33FF39",
                            data: data.arr_lshot
                        },{
                            label: "LSHOT>8",
                            backgroundColor: "#FF8633",
                            data: data.arr_lshotg8
                        }]
                    };
                    var myBarChart = new Chart(ctx, {
                        type: 'bar',
                        data: $scope.data,
                        options: {
                            legend: {
                                display: true,
                                position:'top',
                                labels: {
                                    fontColor: 'black',
                                    
                                }
                            }
                            // barValueSpacing: 20,
                            // scales: {
                            // 	yAxes: [{
                            // 		ticks: {
                            // 			min: 0,
                            // 		}
                            // 	}]
                            // }
                        }
                    });
                }
            }, function (response) {
                $rootScope.modalDanger();
            });
        }
        
        $scope.barchartdivot = function () {
            var ctx = document.getElementById("bar1").getContext("2d");
            var urlData = {
                'accountid': $scope.dashboard.values.accountid
            }
            $http.post(apiUrl + 'admin/hr/home/OTbyYEar.php', urlData)
            .then(function (response, status) {
                var data = response.data;
                if( data.status == "error" ){
                    $rootScope.modalDanger();
                }else{
                    $scope.data = {
                        labels: ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"],
                        datasets: [{
                            label: "OT",
                            backgroundColor: "#a4a0a0",
                            data: data.leaves
                        }]
                    };
                    var myBarChart = new Chart(ctx, {
                        type: 'bar',
                        data: $scope.data,
                        options: {
                            barValueSpacing: 20,
                            scales: {
                            	yAxes: [{
                            		ticks: {
                            			min: 0,
                            		}
                            	}]
                            }
                        }
                    });
                }
            }, function (response) {
                $rootScope.modalDanger();
            });
        }

        $scope.totalemployeesOT = [];
        $scope.totalempOT = function () {
            //spinnerService.show('form01spinner1');
            var urlData = {
                'accountid'  : $scope.dashboard.values.accountid,
                'deppt'      : $scope.department,
                'costcenter' : $scope.costcenter,
                'jobloc'     : $scope.jobloc
                
            }
            $http.post(apiUrl + 'admin/hr/home/totalempOT.php', urlData)
                .then(function (response, status) {
                    var data = response.data;


                    //spinnerService.hide('form01spinner1');
                    if (data.status == 'error') {
                        $rootScope.modalDanger();
                    } else {
                        $scope.totalemployeesOT = data;
                    }
                }, function (response) {
                    $rootScope.modalDanger();
                });
                
        }
		
		$scope.getTotalLeaveYTD = function(){
            var urlData = {
                'accountid': $scope.dashboard.values.accountid
            }
            $http.post(apiUrl + 'admin/hr/home/leaves/totalLeaveYTD.php', urlData)
                .then(function (response, status) {
                    var data = response.data;

                    if (data.status == 'error') {
                        $rootScope.modalDanger();
                    } else {
                        $scope.totalemployeesLeaveYTD = data;

                    }
                }, function (response) {
                    $rootScope.modalDanger();
                });

        }

        $scope.headcount_functions_leave = function (){
            var ctx = document.getElementById("hbmLine23").getContext("2d");

            var urlData = {
                'accountid': $scope.dashboard.values.accountid,
            }
            $http.post(apiUrl + 'admin/hr/home/leaves/rangeCountLeave.php', urlData)
            .then(function (response, status) {

                var data = response.data;

                var config = {
                    type: 'line',
                    data: {
                        labels: data.lbl,
                        datasets: [{
                            backgroundColor: 'rgb(255, 159, 64)',
                            borderColor: 'rgb(255, 159, 64)',
                            lineTension: 0,
                            data: data.data,
                            fill: false,
                        }]
                    },
                    options: {
                        responsive: true,
                        scales: {
                            xAxes: [{
                                display: true,
                                scaleLabel: {
                                    display: true,
                                    labelString: 'Leave YTD'
                                }
                            }],
                            yAxes: [{
                                display: true,
                                scaleLabel: {
                                    display: true,
                                    labelString: ''
                                }
                            }]
                        }
                    }
                };
                var myLineChart = new Chart(ctx, config);
                    

            }, function (response) {
                $rootScope.modalDanger();
            });


        }

        $scope.barchartdivLeaveMonth = function () {
            var ctx = document.getElementById("bar23").getContext("2d");
            var d = new Date();
            var month = d.getMonth();
            
            var months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
            $timeout(function () {
                if($('#selectLVmonth').val() == '' || $('#selectLVmonth').val() == undefined){
                    $('#selectLVmonth').val(month+1);
                }
            }, 1000);
            var date = new Date();

            var urlData = {
                'accountid': $scope.dashboard.values.accountid,
                'search_leave_month' :$scope.search_leave_month,
            }
            $http.post(apiUrl + 'admin/hr/home/leaves/LeavebyMonth.php', urlData)
            .then(function (response, status) {
                var data = response.data;


                if( data.status == "error" ){
                    $rootScope.modalDanger();
                }else{
                    $scope.data = {
                        labels: data.depts,
                        datasets: [{
                            label: months[date.getMonth()],
                            backgroundColor: "#a4a0a0",
                            data: data.leaves
                        }],
                    
                    };
                    var myBarChart = new Chart(ctx, {
                        type: 'bar',
                        data: $scope.data,
                        options: {
                            legend: {
                                display: false,
                                position:'bottom',
                                labels: {
                                    fontColor: 'black',
                                    
                                }
                            }
                        }
                    });
                }
            }, function (response) {
                $rootScope.modalDanger();
            });
        }
        $scope.totalEmpLeaves = function () {
            var urlData = {
                'accountid'  : $scope.dashboard.values.accountid,
                'deppt'      : $scope.department,
                'costcenter' : $scope.costcenter,
                'jobloc'     : $scope.jobloc                
            } 
            $http.post(apiUrl + 'admin/hr/home/leaves/totalEmpLeaves.php', urlData)
                .then(function (response, status) {
                    var data = response.data;
                    if (data.status == 'error') {
                        $rootScope.modalDanger();
                    } else {
                        $scope.totalemployeesEmpLeaves = data;
                    }
                }, function (response) {
                    $rootScope.modalDanger();
                });
        }

        $scope.piechartdivsLeaves = function () {
            $timeout(function () {
                $scope.pie_sum1 = 0;
                $scope.pie_labels1 = [];
                $scope.pie_data1 = [];
                $scope.pie_colour1 = [];
                $scope.pie_options1 = {};
                var urlData = {
                    'accountid': $scope.dashboard.values.accountid
                }
                $http.post(apiUrl + 'admin/hr/home/leaves/piechartLeave_VS.php', urlData)
                    .then(function (response, status) {
                        var data = response.data;
                        if (data.status == "error") {
                            $rootScope.modalDanger();
                        } else {
                            $scope.pie_labels1 = data.lbl;
                            $scope.pie_data1 = data.ctr;
                            $scope.pie_colour1 = data.colour;
                            $scope.pie_sum1 = data.sum;
                            $scope.pie_options1 = {
                                tooltips: {
                                    enabled: true
                                },
                                legend: {
                                    display: true,
                                    position: "bottom",
                                    labels: {
                                        boxWidth: 15,
                                        fontSize: 10,
                                        fontColor: 'rgb(255, 99, 132)',
                                        padding: -50,
                                        fontStyle: 'italic',
                                        generateLabels: function (chart) {
                                            var bg = chart.data.datasets[0].backgroundColor
                                            var ele = "<ul class='legend-labels'>";
                                            chart.data.datasets[0].data.forEach(function (item, index) {
                                                var perc = ((item / $scope.divisor) * 100).toFixed(2);
                                                ele = ele + '<li class="plabels"><span class="boxl" style="background-color:' + bg[index] + ';color:white">' + $scope.pie_data1[index] + '</span>  ' + $scope.pie_labels1[index] + '</li></a>';
                                            });
                                            ele = ele + "</ul>";
                                            $(".legend-scale").html(ele);
                                            return chart.generateLegend();
                                        }
                                    }
                                }
                            };
                        }
                    }, function (response) {
                        $rootScope.modalDanger();
                    });
            }, 1000);
        }

        $scope.totbydeptname=[];
        $scope.totbydeptcount=[];
		
		// $scope.headcount_functions = {
		// 	values: {
		// 		totalhc		: 0,
		// 		rangetot	: 0,
		// 		datecurr	: moment().format('YYYY-MM-DD'),
		// 		range		: ''
		// 	},
		// 	total_emp: function(){
		// 		var urlData = {
        //             'accountid'	: $scope.dashboard.values.accountid,
		// 			'hdate'		: moment().format('YYYY-MM-DD')
        //         }
        //         $http.post(apiUrl + 'admin/hr/home/headcount/headcount.php', urlData)
		// 		.then(function (response, status) {
		// 			var data = response.data;
		// 			$scope.headcount_functions.values.totalhc = data.total;
		// 		}, function (response) {
		// 			$rootScope.modalDanger();
		// 		});
		// 	},
            
        //     total_range: function(){ 
        //         $scope.totbydeptname=[];
        //         $scope.totbydeptcount=[];
        //         var ctx = document.getElementById("hbmLine").getContext("2d");
        //         var startDate = $('#dtrangeLine').data('daterangepicker').startDate._d;
        //         var endDate = $('#dtrangeLine').data('daterangepicker').endDate._d;
               
        //         var urlData = {
        //             'accountid': $scope.dashboard.values.accountid,
        //             'dfrom'		: moment(startDate).format('YYYY-MM-DD'),
        //             'dto'		: moment(endDate).format('YYYY-MM-DD')
        //         }
        //         $http.post(apiUrl + 'admin/hr/home/headcount/countbydept.php', urlData)
        //         .then(function (response, status) {
        //             var data = response.data;
        //             $scope.total = data;
        //             $scope.tot = $scope.total[0].totsum;

        //             console.log('result total-count '+$scope.tot);

        //             for(var i=0; i<$scope.total[0].totbydept.length; i++) { 
        //                 $scope.totbydeptname.push($scope.total[0].totbydept[i].name);
        //                 $scope.totbydeptcount.push($scope.total[0].totbydept[i].tot);
        //             }

        //             for(var i=0; i<$scope.totbydeptname.length; i++) { 
        //                 console.log('result count ' +  $scope.totbydeptname[i]);
        //             }
                    
        //             var config = {
        //                     type: 'line',
        //                     data: {
        //                         labels:  $scope.totbydeptname,
        //                         datasets: [{
        //                         backgroundColor: 'rgb(255, 159, 64)',
        //                         borderColor: 'rgb(255, 159, 64)',
        //                         lineTension: 0,
        //                         data:  $scope.totbydeptcount,
        //                         fill: false,
        //                         }]
        //                     },
        //                     options: {
        //                         responsive: true,
        //                         scales: {
        //                             xAxes: [{
        //                                 display: true,
        //                                 scaleLabel: {
        //                                     display: true,
        //                                     labelString: 'Department'
        //                                 }
        //                             }],
        //                             yAxes: [{
        //                                 display: true,
        //                                 scaleLabel: {
        //                                     display: true,
        //                                     labelString: 'Value'
        //                                 }
        //                             }]
        //                         }
        //                     }
        //                 };
        //                 var myLineChart = new Chart(ctx, config);   
                        
        //         }, function (response) {
        //             $rootScope.modalDanger();
        //         });
        //     }
        // }
        
        $scope.headcount_functions = {
			values: {
				totalhc		: 0,
				rangetot	: 0,
				datecurr	: moment().format('YYYY-MM-DD'),
				range		: ''
			},
			total_emp: function(){
				var urlData = {
                    'accountid'	: $scope.dashboard.values.accountid,
					'hdate'		: moment().format('YYYY-MM-DD')
                }
                $http.post(apiUrl + 'admin/hr/home/headcount/headcount.php', urlData)
				.then(function (response, status) {
					var data = response.data;
					$scope.headcount_functions.values.totalhc = data.total;
				}, function (response) {
					$rootScope.modalDanger();
				});
			},
            
            total_range: function(){ 
                $scope.totbydeptname=[];
                $scope.totbydeptcount=[];
                $scope.totbydeptpresent=[];
                //var ctx = document.getElementById("hbmLine").getContext("2d");
                var startDate = $('#dtrangeLine').data('daterangepicker').startDate._d;
                var endDate = $('#dtrangeLine').data('daterangepicker').endDate._d;
               
                var urlData = {
                    'accountid': $scope.dashboard.values.accountid,
                    'dfrom'		: moment(startDate).format('YYYY-MM-DD'),
                    'dto'		: moment(endDate).format('YYYY-MM-DD')
                }
                $http.post(apiUrl + 'admin/hr/home/headcount/countbydept.php', urlData)
                .then(function (response, status) {
                    var data = response.data;
                    $scope.total = data;

                    $scope.totemp = $scope.total[0].totemp;
                    $scope.emptoday = $scope.total[0].emptoday;

                    for(var i=0; i<$scope.total[0].deptinfo.length; i++) { 
                        $scope.totbydeptname.push($scope.total[0].deptinfo[i].name);
                        $scope.totbydeptcount.push($scope.total[0].deptinfo[i].totalmanpower);
                        $scope.totbydeptpresent.push($scope.total[0].deptinfo[i].totalpresent);
                    }
                    
                    var barOptions_stacked = {
                        tooltips: {
                            enabled: true
                        },
                        hover :{
                            animationDuration:0
                        },
                        scales: {
                            xAxes: [{
                                ticks: {
                                    beginAtZero:true,
                                    fontFamily: "'Open Sans Bold', sans-serif",
                                    fontSize:11
                                },
                                scaleLabel:{
                                    display:true
                                },
                                gridLines: {
                                }, 
                                stacked: true
                            }],
                            yAxes: [{
                                gridLines: {
                                    display:false,
                                    color: "#fff",
                                    zeroLineColor: "#fff",
                                    zeroLineWidth: 0
                                },
                                ticks: {
                                    fontFamily: "'Open Sans Bold', sans-serif",
                                    fontSize:11
                                },
                                stacked: true
                            }]
                        },
                        legend:{
                            display:true,
                        },
                        animation: {
                            onComplete: function () {
                                var chartInstance = this.chart;
                                var ctx = chartInstance.ctx;
                                ctx.textAlign = "left";
                                ctx.font = "9px Open Sans";
                                ctx.fillStyle = "#000";
                    
                                Chart.helpers.each(this.data.datasets.forEach(function (dataset, i) {
                                    var meta = chartInstance.controller.getDatasetMeta(i);
                                    Chart.helpers.each(meta.data.forEach(function (bar, index) {
                                        data = dataset.data[index];
                                        if(i==0){
                                            ctx.fillText(data, bar._model.x+5, bar._model.y-4);
                                        } else {
                                            ctx.fillText(data, bar._model.x+5, bar._model.y-4);
                                        }
                                    }),this)
                                }),this);
                            }
                        },
                        pointLabelFontFamily : "Quadon Extra Bold",
                        scaleFontFamily : "Quadon Extra Bold",
                    };
                    
                    var ctx = document.getElementById("Chart1");
                    var myChart = new Chart(ctx, {
                        type: 'horizontalBar',
                        data: {
                            // $scope.totbydeptname=[];
                            // $scope.totbydeptcount=[];
                            labels: $scope.totbydeptname,
                            //labels: ["2014", "2013", "2012", "2011"],
                            datasets: [{
                                label: "Total Present",
                                data: $scope.totbydeptpresent,
                                backgroundColor: "#32CD32",
                                hoverBackgroundColor: "#4CBB17"
                            },{
                                label: "Total Employees",
                                data: $scope.totbydeptcount,
                                backgroundColor: "#98FB98",
                                hoverBackgroundColor: "#90EE90"
                            }]
                        },
                    
                        options: barOptions_stacked,
                    });
                        
                }, function (response) {
                    $rootScope.modalDanger();
                });
            }
		}
        
        $scope.headcount_functions2 = function () {

            // var chart = new CanvasJS.Chart("headLine", {
            //     animationEnabled: true,
            //     title:{
            //         text: ""
            //     },
            //     axisX: {
            //         valueFormatString: "DDD"
            //     },
            //     axisY: {
            //         prefix: "$"
            //     },
            //     toolTip: {
            //         shared: true
            //     },
            //     legend:{
            //         cursor: "pointer",
            //         itemclick: toggleDataSeries
            //     },
            //     data: [
                    
            //         {
            //         type: "stackedBar",
            //         name: "Meals",
            //         showInLegend: "true",
            //         xValueFormatString: "DD, MMM",
            //         yValueFormatString: "$#,##0",
            //         dataPoints: [
            //             { x: new Date(2017, 0, 30), y: 56 },
            //             { x: new Date(2017, 0, 31), y: 45 },
            //             { x: new Date(2017, 1, 1), y: 71 },
            //             { x: new Date(2017, 1, 2), y: 41 },
            //             { x: new Date(2017, 1, 3), y: 60 },
            //             { x: new Date(2017, 1, 4), y: 75 },
            //             { x: new Date(2017, 1, 5), y: 98 }
            //         ]
            //     },
            //     {
            //         type: "stackedBar",
            //         name: "Snacks",
            //         showInLegend: "true",
            //         xValueFormatString: "DD, MMM",
            //         yValueFormatString: "$#,##0",
            //         dataPoints: [
            //             { x: new Date(2017, 0, 30), y: 86 },
            //             { x: new Date(2017, 0, 31), y: 95 },
            //             { x: new Date(2017, 1, 1), y: 71 },
            //             { x: new Date(2017, 1, 2), y: 58 },
            //             { x: new Date(2017, 1, 3), y: 60 },
            //             { x: new Date(2017, 1, 4), y: 65 },
            //             { x: new Date(2017, 1, 5), y: 89 }
            //         ]
            //     }
            //     ]
            // });
            // chart.render();
            // function toggleDataSeries(e) {
            //     if(typeof(e.dataSeries.visible) === "undefined" || e.dataSeries.visible) {
            //         e.dataSeries.visible = false;
            //     }
            //     else {
            //         e.dataSeries.visible = true;
            //     }
            //     chart.render();
            // }   
          

        }
        
        $scope.attendance_counter = function () {
            var dateRange = $scope.dashboard.values.daterange.split("-");
            var urlData = {
                'accountid': $scope.dashboard.values.accountid,
                'start_date': moment(dateRange[0]).format('YYYY-MM-DD'),
                'end_date': moment(dateRange[1]).format('YYYY-MM-DD')
            }
            $http.post(apiUrl + 'admin/hr/home/counter1.php', urlData)
                .then(function (response, status) {
                    var data = response.data;
                    $scope.divisor = 0;
                    if (data.status == 'error') {
                        $rootScope.modalDanger();
                    } else {
                        $scope.divisor = (data.dayz * data.staff);
                        $scope.pie_labels = ["PRESENT", "ABSENT", "LATE", "VL", "SL", "LWOP"];
                        $scope.pie_data = [];
                        $timeout(function () {
                            $scope.pie_data = [data.present_ctr, data.absent_ctr, data.lte_ctr, data.vl_ctr, data.sl_ctr, data.lwop_ctr];
                            $scope.pie_options = {
                                tooltips: {
                                    enabled: true
                                },
                                legend: {
                                    display: true,
                                    position: "bottom",
                                    labels: {
                                        boxWidth: 15,
                                        fontSize: 10,
                                        fontColor: 'rgb(255, 99, 132)',
                                        padding: 5,
                                        fontStyle: 'italic',
                                        generateLabels: function (chart) {
                                            var bg = chart.data.datasets[0].backgroundColor
                                            var ele = "<ul class='legend-labels'>";
                                            chart.data.datasets[0].data.forEach(function (item, index) {
                                                var perc = ((item / $scope.divisor) * 100).toFixed(2);
                                                ele = ele + '<li><span style="font-weight:900;background-color:' + bg[index] + '">' + perc + '% </span>' + $scope.pie_labels[index] + '</li>';
                                            });
                                            ele = ele + "</ul>";
                                            $(".legend-scale").html(ele);
                                            return chart.generateLegend();
                                        }
                                    }
                                }
                            };
                        }, 100);
                        $timeout(function () {
                            $("#staff_data").text('' + data.staff);
                            $(".counter-count_set1").each(function () {
                                $(this).prop('Counter', 0).animate({
                                    Counter: $(this).text()
                                }, {
                                    duration: 2000,
                                    easing: 'swing',
                                    step: function (now) {
                                        $(this).text(Math.ceil(now));
                                    }
                                });
                            });
                        }, 100);
                    }
                }, function (response) {
                    $rootScope.modalDanger();
                });
        }


        $scope.birthdate_update = function () {
            var urlData = {
                'accountid': $scope.dashboard.values.accountid
            }
            $http.post(apiUrl + 'admin/hr/home/birthdates.php', urlData)
                .then(function (response, status) {
                    var data = response.data;

                    if (data.status == 'error') {
                        $rootScope.modalDanger();
                    } else {
                        $scope.birthdays = data;
                    }
                }, function (response) {
                    $rootScope.modalDanger();
                });
        }

        $scope.dept_attendance_ctr = function () {
            var dateRange = $scope.dashboard.values.daterange.split("-");
            var urlData = {
                'accountid': $scope.dashboard.values.accountid,
                'start_date': moment(dateRange[0]).format('YYYY-MM-DD'),
                'end_date': moment(dateRange[1]).format('YYYY-MM-DD')
            }
            $http.post(apiUrl + 'admin/hr/home/counter2.php', urlData)
                .then(function (response, status) {
                    var data = response.data;
                    $scope.dashboard.values.dept_ctr = [];
                    if (data.status == 'error') {
                        $rootScope.modalDanger();
                    } else {
                        $scope.dashboard.values.dept_ctr = data;
                        $timeout(function () {
                            $("#row_" + data[0].idunit).click();
                        }, 100);
                        $scope.dept_chart(data[0]);
                    }
                }, function (response) {
                    $rootScope.modalDanger();
                });
        }

        $scope.dept_chart = function (obj) {
            $scope.dashboard.values.dept_choose = obj.unit;
            $scope.bar_data = [];
            $scope.bar_labels = ['Present', 'Absent', 'Late', 'VL', 'SL', 'LWOP'];
            $scope.bar_data = [obj.present_ctr, obj.absent_ctr, obj.lte_ctr, obj.vl_ctr, obj.sl_ctr, obj.lwop_ctr];
            $scope.bar_options = { 
                scales: {
                    xAxes: [{
                        ticks: {
                            fontSize: 10
                        } 
                    }]
                }
            };
        }

        $scope.viewcontent = function (view) {
            var date = new Date();
            var dd = String(date.getDate()).padStart(2, '0');
            var mm = String(date.getMonth() + 1).padStart(2, '0'); 
            var yyyy = date.getFullYear();
            
            var firstDay = new Date(date.getFullYear(), date.getMonth(), 1);  
            lastDate = mm + '/' + dd + '/' + yyyy;

            if (view == 'headcount') {
                var content = $sce.getTrustedResourceUrl('view/admin/om/home/headcount.html'); 
                $templateRequest(content).then(function (template) {
                    $compile($("#content").html(template).contents())($scope); 
                    $("#dtrangeLine").daterangepicker({
						singleDatePicker	: false,
						showDropdowns		: true,
						minYear				: 1990,
						maxYear				: moment().format('YYYY'),
						startDate			: moment().format("MM/01/YYYY"),
						endDate				: moment().format("MM/" + moment().daysInMonth() + "/YYYY") 
					}, function(start, end, label) {
						$scope.headcount_functions.total_range();
					});
					$scope.headcount_functions.total_range();
                });
            }
            if (view == 'ovetime') {
                var content = $sce.getTrustedResourceUrl('view/admin/om/home/overtime.html');   
                $templateRequest(content).then(function (template) {
                    $compile($("#content").html(template).contents())($scope);
                    $("#dtrange_ot").daterangepicker();
                    $('#dtrange_ot').daterangepicker({ startDate: firstDay, endDate: lastDate }); 
                });
            }
            if (view == 'leaves') {
                var content = $sce.getTrustedResourceUrl('view/admin/om/home/leaves.html'); 
                $templateRequest(content).then(function (template) {
                    $compile($("#content").html(template).contents())($scope);
                });
            }
            if (view == 'lates') {
                var content = $sce.getTrustedResourceUrl('view/admin/om/home/lates.html');
                $templateRequest(content).then(function (template) {
                    $compile($("#content").html(template).contents())($scope);
                });
            }
        }
        $scope.today = new Date();

        $scope.headcountbymonth = function () {
            var urlData = {
                'accountid': $scope.dashboard.values.accountid
            }
            $http.post(apiUrl + 'admin/om/home/headcount/department.php', urlData)
                .then(function (response, status) {
                    var data = response.data;
                    if (data.status == 'error') {
                        $rootScope.modalDanger();
                    } else {
                        $scope.hcdeparment = data;

                        $scope.chart = document.getElementById("hbm").getContext("2d");
                        $scope.data = {
                            labels: ["Jan", "Feb", "March"],

                            datasets: [{
                                label: "Jan",

                                borderColor: 'rgba(255, 99, 132, 1)',
                                data: [1, 6]
                            }, {
                                label: "Feb",

                                borderColor: 'rgba(255, 99, 132, 1)',
                                data: [4, 6]
                            }
                                , {
                                label: "Mar",

                                borderColor: 'rgba(255, 99, 132, 1)',
                                data: [6, 6]
                            }
                            ]
                        };

                    }
                }, function (response) {
                    $rootScope.modalDanger();
                });


        }

        $scope.totalemp = function () {
            var urlData = {
                'accountid': $scope.dashboard.values.accountid
            }
            $http.post(apiUrl + 'admin/hr/home/totalemp.php', urlData)
                .then(function (response, status) {
                    var data = response.data;

                    if (data.status == 'error') {
                        $rootScope.modalDanger();
                    } else {
                        $scope.totalemployees = data;

                    }
                }, function (response) {
                    $rootScope.modalDanger();
                });
        }

        // $scope.announcement = function () {
        //     $scope.announcelist = [];
            
        //     var urlData = {
        //         'accountid': $scope.dashboard.values.accountid
        //     }
        //     $http.post(apiUrl + 'admin/hr/home/announcement.php', urlData)
        //         .then(function (response, status) {
        //             var data = response.data;
        //             if (data.status == 'error') {
        //                 $rootScope.modalDanger();
        //             } else {
        //                 $scope.announcelist = data;
        //             }
        //         }, function (response) {
        //             $rootScope.modalDanger();
        //         });
        // }
        $scope.announcement = function () {
            $scope.announcelist = [];
            var today = $filter('date')(new Date(),'yyyy-MM-dd');
            var urlData = {
                'accountid': $scope.dashboard.values.accountid
            }
            $http.post(apiUrl + 'admin/hr/home/announcement.php', urlData)
                .then(function (response, status) {
                    
                    var data = response.data;
                    var enddate;
                    if (data.status == 'error') {
                        $rootScope.modalDanger();
                    } else {
                        $scope.announce = data;
                        //$scope.announcelist = data;
                        // to filter announcement that is less than current date
						for(var i=0; i<$scope.announce.length; i++) {

							end=new Date($scope.announce[i].end);
							enddate = $filter('date')(new Date(end) - (24*60*60*1000),'yyyy-MM-dd');

							if(today <= enddate){
									$scope.announcelist.push({desc:$scope.announce[i].desc, description:$scope.announce[i].description, end: enddate, filename:$scope.announce[i].filename, filesize:$scope.announce[i].filesize, hasfile:$scope.announce[i].hasfile, id:$scope.announce[i].id, name:$scope.announce[i].name, start:$scope.announce[i].start, title:$scope.announce[i].title, type:$scope.announce[i].type});
							}
						}

						if($scope.announcelist.length > 0){
							$scope.anouncechecker = true;
							console.log("true");
						}else{
							$scope.anouncechecker = false;
							console.log("false");
						}
                    }
                }, function (response) {
                    $rootScope.modalDanger();
                });
        }

        $scope.notifications = function () {
            $scope.announcelist = [];
            var urlData = {
                'accountid': $scope.dashboard.values.accountid
            }
            $http.post(apiUrl + 'admin/hr/home/notifications.php', urlData)
                .then(function (response, status) {
                    var data = response.data;
                    if (data.status == 'error') {
                        $rootScope.modalDanger();
                    } else {
                        $scope.noti = data;
                    }
                }, function (response) {
                    $rootScope.modalDanger();
                });
        }

        $scope.applicants = function () {
            $scope.announcelist = [];
            var urlData = {
                'accountid': $scope.dashboard.values.accountid
            }
            $http.post(apiUrl + 'admin/hr/home/applicants.php', urlData)
                .then(function (response, status) {
                    var data = response.data;
                    if (data.status == 'error') {
                        $rootScope.modalDanger();
                    } else {
                        $scope.appli = data;
                    }
                }, function (response) {
                    $rootScope.modalDanger();
                });
        }


        $scope.viewfile = function (id) {
            var file = id;
            window.location = '/mph/assets/php/admin/org/activity/file/download.php?file=' + file;
        }

        $scope.changeRequest = function(){
            $scope.changeRequestList = [];
            var urlData = {
                'accountid': $scope.dashboard.values.accountid
            }
            $http.post(apiUrl + 'admin/hr/home/changeRequest.php', urlData)
                .then(function (response, status) {
                    var data = response.data;
                    if (data.status == 'error') {
                        $rootScope.modalDanger();
                    } else {
                        $scope.changeRequestList = data;
                    }
                }, function (response) {
                    $rootScope.modalDanger();
                });
        }

        $scope.viewCr = function (id){
            $scope.viewChangeReq = [];
            var urlData = {
                'id': id
            }
            $http.post(apiUrl + 'admin/hr/home/viewChangeRequest.php', urlData)
                .then(function (response, status) {
                    var data = response.data;
                    if (data.status == 'error') {
                        $rootScope.modalDanger();
                    } else {
                        $scope.viewChangeReq = data[0];
                    }
                }, function (response) {
                    $rootScope.modalDanger();
                });
        }

        $scope.aprroved = function (id) {

            var urlData = {
                'accountid'     : $scope.dashboard.values.accountid,
                'approve'       : $scope.viewChangeReq,
                'id'            : id,
                'view'          : $scope.view
            }
            console.log(urlData);
            $http.post(apiUrl + 'admin/hr/home/approvedChangeReq.php', urlData)
                .then(function (response, status) {
                    var data = response.data;

                    $scope.isSaving = false;
                    
                    if( data.status == "error" ){
                        $rootScope.modalDanger();
                        return;
                    }else{
						$("#myModal").modal("hide");
                        $rootScope.dymodalstat = true;
                        $rootScope.dymodaltitle= "Success!";
                        $rootScope.dymodalmsg  = "Approved successfully";
                        $rootScope.dymodalstyle = "btn-success";
                        $rootScope.dymodalicon = "fa fa-check";				
                        $("#dymodal").modal("show");
						$scope.changeRequest();
                    }	
                    			
                }, function (response) {
                    $rootScope.modalDanger();
                });
        }

        $scope.disaprroved = function (id) {
            var urlData = {
                'accountid'     : $scope.dashboard.values.accountid,
                'approve'       : $scope.viewChangeReq,
                'id'            : id
            }
            console.log(urlData);
            $http.post(apiUrl + 'admin/hr/home/disapprovedChangeReq.php', urlData)
                .then(function (response, status) {
                    var data = response.data;

                    $scope.isSaving = false;
                    
                    if( data.status == "error" ){
                        $rootScope.modalDanger();
                        return;
                    }else{
						$("#myModal").modal("hide");
                        $rootScope.dymodalstat = true;
                        $rootScope.dymodaltitle= "Success!";
                        $rootScope.dymodalmsg  = "Disapproved successfully";
                        $rootScope.dymodalstyle = "btn-success";
                        $rootScope.dymodalicon = "fa fa-check";				
                        $("#dymodal").modal("show");
						$scope.changeRequest();
                    }	
                    			
                }, function (response) {
                    $rootScope.modalDanger();
                });
        }

		$scope.getTotalMTD = function (){
            //spinnerService.show('form01spinner');
            var ctx = document.getElementById("bar7").getContext("2d");
            var urlData = {
                'accountid': $scope.dashboard.values.accountid
            }
            $http.post(apiUrl + 'admin/hr/home/tardiness/totalMTD.php', urlData)
            .then(function (response, status) {
                var data = response.data;

                $scope.totalMTD = data.getTotalsMTD;
                if( data.status == "error" ){
                    $rootScope.modalDanger();
                }else{
                    $scope.data = {
                        labels: ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"],
                        datasets: [{
                            label: "LATES",
                            backgroundColor: "#FFA500",
                            data: data.late
                        }, {
                            label: "ABSENCES",
                            backgroundColor: "#44a3c0",
                            data: data.absent
                        }, {
                            label: "UNDERTIME",
                            backgroundColor: "#c55a11",
                            data: data.undertime
                        }]
                    };
                    var myBarChart = new Chart(ctx, {
                        type: 'bar',
                        data: $scope.data,
                        options: {
                            legend: {
                                display: true,
                                position:'bottom',
                                labels: {
                                    fontColor: 'black',
                                    
                                }
                            }
                        }
                    });
                }
                //spinnerService.hide('form01spinner');
            }, function (response) {
               // spinnerService.hide('form01spinner');
                $rootScope.modalDanger();
            });
        }

        $scope.getAbsencesbyDept = function(){
            spinnerService.show('form01spinner');
            $timeout(function () {
                $scope.pie_sum1 = 0;
                $scope.pie_labels1 = [];
                $scope.pie_data1 = [];
                $scope.pie_colour1 = [];
                $scope.pie_options1 = {};
           
                var urlData = {
                    'accountid': $scope.dashboard.values.accountid,
                }
                
                $http.post(apiUrl + 'admin/hr/home/tardiness/byDept.php', urlData)
                    .then(function (response, status) {
                        var data = response.data;

                        if (data.status == "error") {
                            $rootScope.modalDanger();
                        } else {
                            $scope.pie_labels1 = data.lbl;
                            $scope.pie_data1 = data.ctr;
                            $scope.pie_colour1 = data.colour;
                            $scope.pie_sum1 = data.sum;
                            $scope.pie_options1 = {
                                tooltips: {
                                    enabled: true
                                },
                                legend: {
                                    display: true,
                                    position: "bottom",
                                    labels: {
                                        boxWidth: 15,
                                        fontSize: 10,
                                        fontColor: 'rgb(255, 99, 132)',
                                        padding: -50,
                                        fontStyle: 'italic',
                                        generateLabels: function (chart) {
                                            var bg = chart.data.datasets[0].backgroundColor
                                      
                                            var ele = "<ul class='legend-labels'>";
                                            chart.data.datasets[0].data.forEach(function (item, index) {
                                                var perc = ((item / $scope.divisor) * 100).toFixed(2);
                                                ele = ele + '<li class="plabels"><span class="boxl" style="background-color:' + bg[index] + ';color:white">' + $scope.pie_data1[index] + '</span>  ' + $scope.pie_labels1[index] + '</li></a>';
                                            });
                                            ele = ele + "</ul>";
                                            $(".legend-scale").html(ele);
                                            return chart.generateLegend();
                                        }
                                    }
                                }
                            };
                        }
                        spinnerService.hide('form01spinner');
                    }, function (response) {
                        spinnerService.hide('form01spinner');
                        $rootScope.modalDanger();
                    });
            }, 1000);
        }

        $scope.getLatesbyDept = function(){
            $timeout(function () {
                $scope.pie_sum2 = 0;
                $scope.pie_labels2 = [];
                $scope.pie_data2 = [];
                $scope.pie_colour2 = [];
                $scope.pie_options2 = {};
           
                var urlData = {
                    'accountid': $scope.dashboard.values.accountid,
                }
                
                $http.post(apiUrl + 'admin/hr/home/tardiness/byDeptLates.php', urlData)
                    .then(function (response, status) {
                        var data = response.data;

                        if (data.status == "error") {
                            $rootScope.modalDanger();
                        } else {
                            $scope.pie_labels2 = data.lbl2;
                            $scope.pie_data2 = data.ctr2;
                            $scope.pie_colour2 = data.colour2;
                            $scope.pie_sum2 = data.sum2;
                            $scope.pie_options2 = {
                                tooltips: {
                                    enabled: true
                                },
                                legend: {
                                    display: true,
                                    position: "bottom",
                                    labels: {
                                        boxWidth: 15,
                                        boxHeight: 15,
                                        fontSize: 10,
                                        fontColor: 'rgb(255, 99, 132)',
                                        padding: -50,
                                        fontStyle: 'italic',
                                        generateLabels: function (chart) {
                                            var bg = chart.data.datasets[0].backgroundColor
                                      
                                            var ele = "<ul class='legend-labels1'>";
                                            chart.data.datasets[0].data.forEach(function (item, index) {
                                                var perc = ((item / $scope.divisor) * 100).toFixed(2);
                                                ele = ele + '<li class="plabels"><span class="boxl" style="background-color:' + bg[index] + ';color:white">' + $scope.pie_data2[index] + '</span>  ' + $scope.pie_labels2[index] + '</li></a>';
                                            });
                                            ele = ele + "</ul>";
                                            $(".legend-scale2").html(ele);
                                            return chart.generateLegend();
                                        }
                                    }
                                }
                            };
                        }
                    }, function (response) {
                        $rootScope.modalDanger();
                    });
            }, 1000);
        }

        $scope.printHeadcount = function (divid) {
            var w = 1200;
            var h = 700;
            var left = Number((screen.width / 2) - (w / 2));
            var tops = Number((screen.height / 2) - (h / 2));
            var innerContents = document.getElementById(divid).innerHTML;
            var popupWinindow = window.open('', '_blank', 'width=1200,height=700,scrollbars=no,menubar=no,toolbar=no,location=no,status=no,titlebar=no, width=' + w + ', height=' + h + ', top=' + tops + ', left=' + left + '');
            popupWinindow.document.open();
            popupWinindow.document.write('<html><head><link rel="stylesheet" type="text/css" href="" /></head><body onload="window.print()">' + innerContents + ' </html>');
            popupWinindow.document.close();
        }
        // $scope.printHeadcount = function () {
        //     $('th.firstr').remove();
        //     $('td.firstr').remove();
        //     $('td.removetd').remove();
        //     $('.tblack').attr('style', 'color: black !important');
        //     var mywindow = window.open('', 'PRINT');
        //     mywindow.document.write('<html><head><title>' + document.title + '</title>');
        //     mywindow.document.write('</head><body >');
        //     mywindow.document.write('<h1>' + 'Department Attendance Report' + '</h1>');
        //     mywindow.document.write(document.getElementById('xports').innerHTML);
        //     mywindow.document.write('</body></html>');
        //     mywindow.document.close(); // necessary for IE >= 10
        //     mywindow.focus(); // necessary for IE >= 10*/
        //     mywindow.print();
        //     $scope.headcount_functions.total_range()();
        //     return false;
        // }

        $scope.PrintImage = function () {
            var startDate = $('#dtrangeLine').data('daterangepicker').startDate._d;
            var endDate = $('#dtrangeLine').data('daterangepicker').endDate._d;
            printJS({ printable: document.querySelector("#Chart1").toDataURL(), type: 'image', imageStyle: 'width:100%',documentTitle: "Headcount by Department - Date Range ("+ moment(startDate).format('YYYY-MM-DD') +' to '+moment(endDate).format('YYYY-MM-DD')+')'});
        }

		//printJS({printable: document.querySelector("#Chart1").toDataURL(), type: 'image', imageStyle: 'width:100%'});

        $(document).ready(function () {
            if ($("body").hasClass("sidebar-collapse")) {
                $('.sidebar').removeClass("sidebar1280")
            } else {
                $('.sidebar').addClass('sidebar1280')
            }
        });

        $scope.toggleside = function () {
            if ($("body").hasClass("sidebar-collapse")) {
                $('.sidebar').addClass('sidebar1280')
            } else {
                $('.sidebar').removeClass("sidebar1280")
            }
        }


        $rootScope.getCompanyName();
        $scope.dashboard.setup();
    }]);
