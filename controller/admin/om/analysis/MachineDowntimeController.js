app.controller('MachineDowntimeController', ['$scope', '$rootScope', '$location', '$routeParams', '$http', '$cookieStore', '$timeout', 'spinnerService', '$filter', 'Upload', 'DTOptionsBuilder', 'DTColumnBuilder', '$q', '$compile', 'textAngularManager', '$templateRequest', '$sce',
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
                accoutfname: $cookieStore.get('acct_fname'),
                accoutlname: $cookieStore.get('acct_lname'), 
                username	: $cookieStore.get('username'),	
                userInformation: null, 
            },
            active: function () {  
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
                            $timeout(function () { 
                                $("#selchart").val('adv').change();
                            }, 200); 
                            $scope.monthlyreport(); 
                        }
                    }, function (response) {
                        $rootScope.modalDanger();
                    });
            },
            setup: function () {
                var urlData = {
                    'username': $scope.dashboard.values.username,
                    'accountid': $scope.dashboard.values.accountid
                }
                $http.post(apiUrl + 'admin/om/machines.php', urlData)
                .then(function (response, status) {
                    var data = response.data;
                    if (data.status == 'error') {
                        $rootScope.modalDanger();
                    } else {
                        $scope.machinelist = data.result;
                        
                    }
                }, function (response) {
                    $rootScope.modalDanger();
                });
            }
        }

        $scope.barOptions_stacked =   {  
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
                        fontSize:8
                    },
                    scaleLabel:{
                        display:false //label
                    },
                    gridLines: {
                    }, 
                    stacked: true // sumpay ang bar
                }],

                
                yAxes: [{
                    id: "1",
                    position:"right",
                    gridLines: {
                        display:true,
                        color: "#fff",
                        zeroLineColor: "#fff",
                        zeroLineWidth: 0
                    },
                    ticks: {
                        fontFamily: "'Open Sans Bold', sans-serif",
                        fontSize:8, 
                        min: 0,
                        max: 20,
                        stepSize: 2,
                        display: true
                    },
                    scaleLabel: {
                        display: true, 
                        fontColor: "orange",
                        labelString: 'Total of Kanban'
                    },
                    stacked: false // sumpay ang bar
                },{
                    id: "2",
                    position:"left",
                    gridLines: {
                        display:true,
                        color: "#fff",
                        zeroLineColor: "#fff",
                        zeroLineWidth: 0
                    },
                    ticks: {
                        fontFamily: "'Open Sans Bold', sans-serif",
                        fontSize:8, 
                        min: 0,
                        max: 1000,
                        stepSize: 200,
                        display: true
                    }, 
                    stacked: false // sumpay ang bar
                }]
            },
            legend:{
                display:true, //header legend
            }, 
            pointLabelFontFamily : "Quadon Extra Bold",
            scaleFontFamily : "Quadon Extra Bold",
            pan: {
                enabled: true,
                mode: 'xy',
            },
            zoom: {
                enabled: true,
                mode: 'x',
            }
        };

        $scope.barOptions_hstacked = {
            tooltips: {
                enabled: true,
                mode: 'single',
                callbacks: {
                    label: function(tooltipItems, data) {
                    var text = tooltipItems.datasetIndex === 0 ? 'Remarks Status 1' : 'Remarks Status '+(tooltipItems.datasetIndex+1)
                    return tooltipItems.yLabel + ' ' + text;
                    }
                }
            },
            hover :{
                animationDuration:0
            },
            scales: {
                xAxes: [{  
                    ticks: {
                        beginAtZero:true,
                        fontFamily: "'Open Sans Bold', sans-serif",
                        fontSize: 11, 
                        min: 0,
                        max:  24,
                        stepSize: 1,
                        callback: function(value, index, values) {
                            return  $scope.formatHour(value).toString();
                        }
                    },
                    scaleLabel:{
                        display:true
                    },
                    gridLines: {
                    }, 
                    stacked: true,
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
                        fontSize:15,
                    },
                    stacked: true
                }]
            },
            legend:{
                display:false,
            }, 
            pointLabelFontFamily : "Quadon Extra Bold",
            scaleFontFamily : "Quadon Extra Bold",
        };
        $scope.chartdetails = [];
        $scope.paginationchart = {};
        $scope.filter ={};
        $scope.monthlyreport = function () {  
            var d=new Date($('#startDate').val());   
            var month=d.getMonth() + 1;
            var year=d.getFullYear();
            $scope.filter.selmonth = month;   
            $scope.filter.selyears = year;   

            $scope.paginationchart.currentPage = 1
            $scope.paginationchart.totalItems = 0;
            $scope.paginationchart.pageSize = '10';
            $scope.paginationchart.maxSize = 5;   
            //$http.get('http://192.168.1.110:1880/getdashboardinfo?month='+month+'&year='+year)

            // var urlData = {
            //     'accountid': $scope.dashboard.values.accountid,
            //     'filter': $scope.filter,
            //     'pagination': $scope.paginationchart
            // };

            $http.get('http://192.168.1.110:1880/getmonthlyreport?selmonth='+$scope.filter.selmonth+'&selyears='+$scope.filter.selyears+'&machine='+$scope.filter.machine)
            // $http.get('http://192.168.1.110:1880/getmonthlyreport?obj=', urlData)
            .then(function(response) { 
                spinnerService.hide('form01spinner');
                var data = response.data.machines;  
                $scope.chartdetails = data; 
                $timeout(function () {   
                if($(".chart").select2('val') != '? undefined:undefined ?' && $(".chart").select2('val') != 'adv'){
                    for(var x=0; x<$scope.chartdetails.length; x++){  
                        //simple
                        var chr = new Chart(document.getElementById("mixedChart"+x).getContext("2d"), { 
                            type: 'bar',
                            data: {
                                datasets: [
                                {
                                    label: 'Kanban Count',
                                    type: 'line',  
                                    data: $scope.chartdetails[x].kanban_count,//[1,2,3,4,5,6,7,8,9,15,17,19,25,31], 
                                    // this dataset is drawn below 
                                    yAxisID: "1",
                                    borderColor: '#F79500', 
                                    pointBackgroundColor: '#F79500',
                                    order: 1
                                },{
                                    label: 'Finished Goods',
                                    data: $scope.chartdetails[x].fg, 
                                    type: 'line', 
                                    // this dataset is drawn on top
                                    yAxisID: "2",
                                    fill: false,  
                                    borderColor: 'rgb(54, 162, 235)',
                                    order: 4
                                },{
                                    label: 'Production Count',
                                    data: $scope.chartdetails[x].total,
                                    type: 'line', 
                                    // this dataset is drawn on top
                                    yAxisID: "2",
                                    fill: false,  
                                    borderColor: 'green',
                                    pointBackgroundColor: 'green',
                                    order: 4
                                },{
                                    label: 'Non Goods',
                                    type: 'line',  
                                    data: $scope.chartdetails[x].ng, 
                                    // this dataset is drawn below 
                                    yAxisID: "2",
                                    borderDash: [10,5],
                                    borderColor: 'rgb(255, 99, 132)',
                                    backgroundColor: '#D5D5D5',
                                    order: 3
                                }
                                ],
                                labels: $scope.chartdetails[x].dayno
                            },  options:  $scope.barOptions_stacked,
                         } 
                         ); 
                         chr.options.title.display = true;
                         chr.options.title.text = $scope.chartdetails[x].month_year;
                         chr.options.title.position = 'bottom';
                         chr.options.scales.yAxes[0].ticks.max = parseFloat(($scope.chartdetails[x].max_kanban + ($scope.chartdetails[x].max_kanban*1.5)).toFixed(2)); 
                         chr.options.scales.yAxes[0].ticks.stepSize = parseFloat(($scope.chartdetails[x].max_kanban*.5).toFixed(2));
                         chr.options.scales.yAxes[1].ticks.max = parseFloat(($scope.chartdetails[x].greatest_total + ($scope.chartdetails[x].greatest_total*.1)).toFixed(2)); 
                         chr.options.scales.yAxes[1].ticks.stepSize = parseFloat(($scope.chartdetails[x].greatest_total*.2).toFixed(2));
                         chr.options.scales.yAxes[0].scaleLabel.labelString = $scope.chartdetails[x].overall_kanban +' Overall Kanban';
                         chr.update();
    
                         //default 
                    }
                }else{
                    for(var x=0; x<$scope.chartdetails.length; x++){
                        //test
                        //$timeout(function () {  
                        var chrs = new Chart(document.getElementById("modalChartjs"+x).getContext("2d"), { 
                                type: 'horizontalBar', 
                                data: {  
                                    datasets: [{
                                        data: [1.5+x], 
                                        backgroundColor: "green",
                                        hoverBackgroundColor: "green",
                                        
                                    },{
                                        data: [2.5+x],    
                                        backgroundColor: "red",
                                        hoverBackgroundColor: "red"
                                    },{
                                        data: [1.5+x],    
                                        backgroundColor: "green",
                                        hoverBackgroundColor: "green"
                                    },{
                                        data: [3.5+x],    
                                        backgroundColor: "orange",
                                        hoverBackgroundColor: "orange"
                                    },{
                                        data: [2.5+x],    
                                        backgroundColor: "green",
                                        hoverBackgroundColor: "green"
                                    },{
                                        data: [0.5+x],    
                                        backgroundColor: "red",
                                        hoverBackgroundColor: "red"
                                    },{
                                        data: [1.5+x],    
                                        backgroundColor: "green",
                                        hoverBackgroundColor: "green"
                                    }],
                                    labels: [$scope.chartdetails[x].machine_name] //[''],//
                                },  options:  $scope.barOptions_hstacked,
                                } ); 
                            //}, 1000);  
                        //test
                        //advance
                        // var chr = new Chart(document.getElementById("mixedChart"+x).getContext("2d"), { 
                        //     type: 'bar',
                        //     data: {
                        //         datasets: [
                        //         {
                        //             label: 'Kanban Count',
                        //             type: 'line',  
                        //             data: $scope.chartdetails[x].kanban_count,//[1,2,3,4,5,6,7,8,9,15,17,19,25,31],
                        //             yAxisID: "1",
                        //             borderColor: '#F79500', 
                        //             pointBackgroundColor: '#F79500',
                        //             order: 1
                        //             },{
                        //             label: 'Non Goods',
                        //             data: $scope.chartdetails[x].ng, 
                        //             type: 'line',
                        //             lineTension: 0,
                                    
                        //             yAxisID: "2",
                        //             fill: false, 
                        //             borderDash: [10,5],
                        //             borderColor: 'rgb(255, 99, 132)',
                        //             order: 4
                        //         },{
                        //             label: 'Finished Goods',
                        //             data: $scope.chartdetails[x].fg, 
                        //             yAxisID: "2",
                        //             borderColor: 'rgb(54, 162, 235)',
                        //             backgroundColor: 'rgb(54, 162, 235)',
                        //             order: 3
                        //         },{
                        //             label: 'Production Count',
                        //             data: $scope.chartdetails[x].total, 
                        //             yAxisID: "2",
                        //             borderColor: '#02ad1b',
                        //             backgroundColor: '#02ad1b',
                        //             order: 3
                        //         } ],
                        //         labels: $scope.chartdetails[x].dayno
                        //     },  options:  $scope.barOptions_stacked,
                        //  } 
                        //  ); 
                        //  chr.options.title.display = true;
                        //  chr.options.title.text = $scope.chartdetails[x].month_year;
                        //  chr.options.title.position = 'bottom'; 
                        //  chr.options.scales.yAxes[0].ticks.max = parseFloat(($scope.chartdetails[x].max_kanban + ($scope.chartdetails[x].max_kanban*1.5)).toFixed(2)); 
                        //  chr.options.scales.yAxes[0].ticks.stepSize = parseFloat(($scope.chartdetails[x].max_kanban*.5).toFixed(2));
                        //  chr.options.scales.yAxes[1].ticks.max = parseFloat(($scope.chartdetails[x].greatest_total + ($scope.chartdetails[x].greatest_total*.1)).toFixed(2)); 
                        //  chr.options.scales.yAxes[1].ticks.stepSize = parseFloat(($scope.chartdetails[x].greatest_total*.2).toFixed(2));
                        //  chr.options.scales.yAxes[0].scaleLabel.labelString = $scope.chartdetails[x].overall_kanban +' Overall Kanban';
                        //  chr.update();
    
                         //default 
                    }
                } 
            }, 500); 
                $scope.paginationchart.totalItems = $scope.chartdetails.length;  
                // {x:'2019-01-01 18:20', y:20},
                // {x:'2019-01-01 19:50', y:45},
                // {x:'2019-01-01 21:15', y:25},
                // {x:'2019-01-01 21:45', y:30}
                //alert(moment(moment('2019-01-01 12:50').format('HH:mm'),'HH:mm'));
                //var testhr = moment($scope.formatHour(2)).format('HH:mm');
                //alert($scope.formatHour(2.5));
               
            })
            .catch(function(error) { 
                console.error('Error fetching data:', error);
            });

             
        } 

        $scope.formatHour= function(value) {
            minutes =  Math.round(value * 60);

            var h = Math.floor(minutes / 60);
            var m = minutes % 60;
            h = h < 10 ? '0' + h : h;
            m = m < 10 ? '0' + m : m;
            return h + ':' + m;
        }

        $scope.randomArray= function(length, max) {
            return Array.apply(null, Array(length)).map(function() {
                return Math.round(Math.random() * max);
            });
        }

        $(function () {
            var months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];;
            var date = new Date();

            var myDate = months[date.getMonth()] + ' ' + date.getFullYear(); 
            $('#startDate').datepicker({ 
                yearRange: "-5:+0",
                changeMonth: true,
                maxDate: 'M', 
                changeYear: true,
                showButtonPanel: true,
                dateFormat: 'MM yy',
                onClose: function(dateText, inst) { 
                    var month = $("#ui-datepicker-div .ui-datepicker-month :selected").val();
                    var year = $("#ui-datepicker-div .ui-datepicker-year :selected").val();
                    $(this).datepicker('setDate', new Date(year, month, 1));
                }
            });

            $('#startDate').datepicker('setDate', myDate); 
   
        });

        $(document).ready(function(){ 
            //setInterval($scope.intervaltest, 3000); 
            //setInterval($scope.monthlyreport, 5000); 
            setInterval(
                function clear() {
                        clearInterval(this) 
                   return clear;
                }()
            , 1000)
        });

        //This is where the Print Single and Multiple Charts depends - Adrian
        function convertCanvasToBase64PNG(canvasId) {
            var canvas = document.getElementById(canvasId);
            if (!canvas) {
              throw new Error('Canvas element not found');
            }
            return canvas.toDataURL('image/png');
          }

        //Print for Multiple Charts [many pages] - Adrian
          $scope.PrintAllCharts = function () {
            var chartHTML = '';
            var chartCounter = 0;
          
            // Iterate through each chart
            $scope.chartdetails.forEach(function (chart, index) {
              var chartId = 'mixedChart' + index;
          
              if (chartCounter % 2 === 0) {
                chartHTML += '<div style="page-break-before: ' + (chartCounter !== 0 ? 'always' : 'auto') + ';">';
              }
          
              // Append machine number and chart to the HTML string
              chartHTML += `<div>`;
              chartHTML += `<div><br><br>Chart for ${$scope.chartdetails[index].machine_name}</div>`;
              var base64Image = convertCanvasToBase64PNG(chartId);
              chartHTML += `<img src="${base64Image}" style="width:100%;"/><br><br>`;
              chartHTML += `</div>`;
          
              // Check if it's the end of the page
              if ((chartCounter + 1) % 2 === 0 || index === $scope.chartdetails.length - 1) {
                chartHTML += '</div>';
              }

              chartCounter++;
            });
            chartHTML = '<div style="text-align: center; font-size: 18px; font-weight: bold;">Charts for All Machines</div>' + chartHTML;

            // Now chartHTML contains all the images, print them together
            printJS({
              printable: chartHTML,
              type: 'raw-html',
              base64: false,
              documentTitle: '\u00A0',
            //   documentTitle: '\n\n\nCharts for All Machines',
            });
          };

        //Print for Single Chart [one page] - Adrian
        $scope.PrintSingleImage = function (index) {
            var chartId = 'mixedChart' + index;
            var chartHTML = '';

            chartHTML += `<div>`;
              chartHTML += `<div><br><br>Chart for ${$scope.chartdetails[index].machine_name}</div>`;
              var base64Image = convertCanvasToBase64PNG(chartId);
              chartHTML += `<img src="${base64Image}" style="width:100%;"/><br><br><br><br>`;
              
              chartHTML += `</div>`;

              printJS({
                printable: chartHTML,
                type: 'raw-html',
                base64: false,
                documentTitle: '\u00A0',
              });           
        }   

        // updated 01-29-2024 adrian
        $scope.modaltitle =''; 
        $scope.printindex =''; 
        $scope.setSelectedIndex = function(index) {
            $scope.modaltitle = $scope.chartdetails[index].machine_name;  
            $scope.printindex =index; 
            var chr = new Chart(document.getElementById("modalChart").getContext("2d"), { 
                type: 'bar',
                data: { 
                    datasets: [
                        {
                            label: 'Kanban Count',
                            type: 'line',  
                            data:  $scope.chartdetails[index].kanban_count,//[1,2,3,4,5,6,7,8,9,15,17,19,25,31], 
                            // this dataset is drawn below 
                            yAxisID: "1",
                            borderColor: '#F79500', 
                            pointBackgroundColor: '#F79500',
                            order: 1
                            },{
                            label: 'Non Goods',
                            data: $scope.chartdetails[index].ng, 
                            type: 'line',
                            lineTension: 0,
                            
                            yAxisID: "2",
                            // this dataset is drawn on top
                            fill: false, 
                            borderDash: [10,5],
                            borderColor: 'rgb(255, 99, 132)',
                            order: 4
                        },{
                            label: 'Finished Goods',
                            data: $scope.chartdetails[index].fg, 
                            // this dataset is drawn below
                            
                            yAxisID: "2",
                            borderColor: 'rgb(54, 162, 235)',
                            backgroundColor: 'rgb(54, 162, 235)',
                            order: 3
                        },{
                            label: 'Production Count',
                            data: $scope.chartdetails[index].total, 
                            // this dataset is drawn below
                            
                            yAxisID: "2",
                            borderColor: '#02ad1b',
                            backgroundColor: '#02ad1b',
                            order: 3
                        } ],
                    labels: $scope.chartdetails[index].dayno
                },  options:  $scope.barOptions_stacked,
            } 
            ); 
            chr.options.title.display = true;
            chr.options.title.text = $scope.chartdetails[index].month_year;
            chr.options.title.position = 'bottom'; 
            chr.options.scales.yAxes[0].ticks.max = parseFloat(($scope.chartdetails[index].max_kanban + ($scope.chartdetails[index].max_kanban*1.5)).toFixed(2)); 
            chr.options.scales.yAxes[0].ticks.stepSize = parseFloat(($scope.chartdetails[index].max_kanban*.5).toFixed(2));
            chr.options.scales.yAxes[1].ticks.max = parseFloat(($scope.chartdetails[index].greatest_total + ($scope.chartdetails[index].greatest_total*.1)).toFixed(2)); 
            chr.options.scales.yAxes[1].ticks.stepSize = parseFloat(($scope.chartdetails[index].greatest_total*.2).toFixed(2));
            chr.options.scales.yAxes[0].scaleLabel.labelString = $scope.chartdetails[index].overall_kanban +' Overall Kanban';
            chr.update();

        };

        $(document).ready(function () {
            if ($("body").hasClass("sidebar-collapse")) {
                $('.sidebar').removeClass("sidebar1280")
            } else {
                $('.sidebar').addClass('sidebar1280')
            }
            $('#selchart').select2();
            $('#selectmachine').select2();  
        });
    }]);
