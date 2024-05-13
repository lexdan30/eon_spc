app.controller('DashboardController', ['$scope', '$rootScope', '$location', '$routeParams', '$http', '$cookieStore', '$timeout', 'spinnerService', '$filter', 'Upload', 'DTOptionsBuilder', 'DTColumnBuilder', '$q', '$compile', 'textAngularManager', '$templateRequest', '$sce',
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
                            $scope.monthlyreport(); 
                        }
                    }, function (response) {
                        $rootScope.modalDanger();
                    });
            },
            setup: function () {
               
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

            $scope.paginationchart.currentPage = 1
            $scope.paginationchart.totalItems = 0;
            $scope.paginationchart.pageSize = '10';
            $scope.paginationchart.maxSize = 5;   

            $http.get('http://192.168.1.110:1880/getdashboardinfo?month='+month+'&year='+year)
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
                                datasets: [{
                                    label: 'Finished Goods',
                                    data: $scope.chartdetails[x].fg, 
                                    type: 'line', 
                                    // this dataset is drawn on top
                                    fill: false,  
                                    borderColor: 'rgb(54, 162, 235)',
                                    order: 4
                                },{
                                    label: 'Non Goods',
                                    data: $scope.chartdetails[x].ng, 
                                    type: 'line', 
                                    // this dataset is drawn on top
                                    fill: false, 
                                    borderDash: [10,5],
                                    borderColor: 'rgb(255, 99, 132)',
                                    order: 4
                                },{
                                    label: 'Production Count',
                                    type: 'line',  
                                    data: $scope.chartdetails[x].total, 
                                    // this dataset is drawn below
                                    borderColor: 'grey',
                                    backgroundColor: '#D5D5D5',
                                    order: 3
                                } ],
                                labels: $scope.chartdetails[x].dayno
                            },  options:  $scope.barOptions_stacked,
                         } 
                         ); 
                         chr.options.title.display = true;
                         chr.options.title.text = $scope.chartdetails[x].month_year;
                         chr.options.title.position = 'bottom';
                         chr.update();
    
                         //default 
                    }
                }else{
                    for(var x=0; x<$scope.chartdetails.length; x++){  
                        //advance
                        var chr = new Chart(document.getElementById("mixedChart"+x).getContext("2d"), { 
                            type: 'bar',
                            data: {
                                datasets: [{
                                    label: 'Production Count',
                                    data: $scope.chartdetails[x].total, 
                                    type: 'line',
                                    lineTension: 0,
                                    // this dataset is drawn on top
                                    fill: false, 
                                    borderDash: [10,5],
                                    borderColor: 'rgb(255, 99, 132)',
                                    order: 4
                                },{
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
                                    order: 3
                                } ],
                                labels: $scope.chartdetails[x].dayno
                            },  options:  $scope.barOptions_stacked,
                         } 
                         ); 
                         chr.options.title.display = true;
                         chr.options.title.text = $scope.chartdetails[x].month_year;
                         chr.options.title.position = 'bottom';
                         chr.update();
    
                         //default 
                    }
                } 
            }, 500); 
                $scope.paginationchart.totalItems = $scope.chartdetails.length;  
            })
            .catch(function(error) { 
                console.error('Error fetching data:', error);
            });

             
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


        $scope.dashboard.setup();
    }]);
