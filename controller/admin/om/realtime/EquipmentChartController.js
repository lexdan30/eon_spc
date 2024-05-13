app.controller('EquipmentChartController', ['$scope', '$rootScope', '$location', '$routeParams', '$http', '$cookieStore', '$timeout', 'spinnerService', '$filter', 'Upload', 'DTOptionsBuilder', 'DTColumnBuilder', '$q', '$compile', 'textAngularManager', '$templateRequest', '$sce',
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
                userInformation: null
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
                            $scope.viewChart();
                           
                        }
                    }, function (response) {
                        $rootScope.modalDanger();
                    });
            },
            setup: function () {
            }
        }

        $scope.barOptions_stacked = {
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
                        display:false //label
                    },
                    gridLines: {
                    }, 
                    stacked: true // sumpay ang bar
                }],
                yAxes: [{
                    gridLines: {
                        display:true,
                        color: "#fff",
                        zeroLineColor: "#fff",
                        zeroLineWidth: 0
                    },
                    ticks: {
                        fontFamily: "'Open Sans Bold', sans-serif",
                        fontSize:11
                    },
                    stacked: false // sumpay ang bar
                }]
            },
            legend:{
                display:true, //header legend
            },
            animation: {
                duration: 0
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


        $scope.randomArray= function(length, max) {
            return Array.apply(null, Array(length)).map(function() {
                return Math.round(Math.random() * max);
            });
        } 

        $scope.paginationchart = {}; 
        $scope.viewChart = function () { 
            $scope.chartdetails = []; 
            $scope.paginationchart.currentPage = 1;
            $scope.paginationchart.totalItems = 0;
            $scope.paginationchart.pageSize = '10';
            $scope.paginationchart.length = 10;
            $scope.paginationchart.maxSize = 5;  
            function getdata() {
                if($location.path() == '/realtime/eqchart'){ 
        
                    $http.get('http://192.168.1.110:1880/getrealtimeoutput')
                    .then(function(response) { 
                        var data = response.data.machines;  
                        $scope.chartdetails = data; 
                        //$timeout(function () {   
                        if($(".chart").select2('val') != '? undefined:undefined ?' && $(".chart").select2('val') != 'adv'){
                            for(var x=0; x<$scope.chartdetails.length; x++){  
                                //simple
                                var chr = new Chart(document.getElementById("mixedChart"+x).getContext("2d"), { 
                                    type: 'bar',
                                    data: {
                                        datasets: [{
                                            label: 'Finished Goods', 
                                            type: 'line', 
                                            data: $scope.chartdetails[x].fg, 
                                            // this dataset is drawn on top fill: false,  
                                            borderColor: 'rgb(54, 162, 235)', 
                                            //backgroundColor: 'lightblue',
                                            order: 1
                                        },{
                                            label: 'Non Goods', 
                                            type: 'line',  
                                            data: $scope.chartdetails[x].ng, 
                                            // this dataset is drawn on top fill: false, 
                                            borderDash: [10,5],
                                            borderColor: 'rgb(255, 99, 132)', 
                                            backgroundColor: 'pink',
                                            order: 4
                                        }  ],
                                        labels: $scope.chartdetails[x].time
                                    },  options:  $scope.barOptions_stacked,
                                 } 
                                 );
                                //  chr.options.title.display = true;
                                //  chr.options.title.text = $scope.chartdetails[x].month_year;
                                //  chr.options.title.position = 'bottom';
                                //  chr.update();
            
                                 //default 
                            }
                        }else{
                            for(var x=0; x<$scope.chartdetails.length; x++){  
                                //advance
                                var chr = new Chart(document.getElementById("mixedChart"+x).getContext("2d"), { 
                                    type: 'bar',
                                    data: {
                                        datasets: [ {
                                            label: 'Finished Goods',
                                            data: $scope.chartdetails[x].fg, 
                                            // this dataset is drawn below  
                                            borderColor: 'rgb(54, 162, 235)',
                                            backgroundColor: 'rgb(54, 162, 235)',
                                            order: 3
                                        },{
                                            label: 'Non Goods',
                                            data: $scope.chartdetails[x].ng, 
                                            // this dataset is drawn below  
                                            borderColor: '#02ad1b',
                                            backgroundColor: '#02ad1b',
                                            order: 1
                                        } ],
                                        labels: $scope.chartdetails[x].time
                                    },  options:  $scope.barOptions_stacked,
                                 } 
                                 ); 
                                //  chr.options.title.display = true;
                                //  chr.options.title.text = $scope.chartdetails[x].month_year;
                                //  chr.options.title.position = 'bottom';
                                //  chr.update();
            
                                 //default 
                            }
                        } 
                    //}, 500); 
                    
                    spinnerService.hide('form01spinner');
                    $scope.paginationchart.totalItems = $scope.chartdetails.length;  
                    })
                    .catch(function(error) { 
                        console.error('Error fetching data:', error);
                    }); 
                }
            } 

            $scope.$watchGroup(['paginationchart.currentPage', 'paginationchart.pageSize'], function () { 
                getdata();
                $scope.paginationchart.length = $scope.paginationchart.pageSize;
                 spinnerService.show('form01spinner');  
            });

            $scope.filterdata = function(){
                getdata(); 
            }

            $(document).ready(function(){ 
                setInterval(getdata, 5000); 
            });

        } 

         //This is where the Print Single and Multiple Charts depends
         function convertCanvasToBase64PNG(canvasId) {
            var canvas = document.getElementById(canvasId);
            if (!canvas) {
              throw new Error('Canvas element not found');
            }
            return canvas.toDataURL('image/png');
          }

        //Print for Multiple Charts [many pages]
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
              chartHTML += `<div><br><br>Chart for ${$scope.chartdetails[index].machine}</div>`;
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

        //Print for Single Chart [one page]
        $scope.PrintSingleImage = function (index) {
            var chartId = 'mixedChart' + index;
            var chartHTML = '';

            chartHTML += `<div>`;
              chartHTML += `<div><br><br>Chart for ${$scope.chartdetails[index].machine}</div>`;
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

        $scope.equipmodaltitle =''; 
        $scope.printindex =''; 
        $scope.setSelectedIndex = function(index) {
            $scope.equipmodaltitle = $scope.chartdetails[index].machine;  
            $scope.printindex =index; 
            var chr = new Chart(document.getElementById("equipmodalChart").getContext("2d"), { 
                type: 'bar',
                        data: {
                            datasets: [ {
                                label: 'Finished Goods',
                                data: $scope.chartdetails[index].fg, 
                                // this dataset is drawn below  
                                borderColor: 'rgb(54, 162, 235)',
                                backgroundColor: 'rgb(54, 162, 235)',
                                order: 3
                            },{
                                label: 'Non Goods',
                                data: $scope.chartdetails[index].ng, 
                                // this dataset is drawn below  
                                borderColor: '#02ad1b',
                                backgroundColor: '#02ad1b',
                                order: 1
                            } ],
                            labels: $scope.chartdetails[index].time
                        },  options:  $scope.barOptions_stacked,
                        } 
                        );  
            chr.options.title.display = true;
            chr.options.title.text = $scope.chartdetails[index].month_year;
            chr.options.title.position = 'bottom';
            chr.update();

        };

        //$scope.dashboard.setup();
    }]);
