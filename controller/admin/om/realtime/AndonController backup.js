app.controller('AndonController', ['$scope', '$rootScope', '$location', '$routeParams', '$http', '$cookieStore', '$timeout', 'spinnerService', '$filter', 'Upload', 'DTOptionsBuilder', 'DTColumnBuilder', '$q', '$compile', 'textAngularManager',
    function ($scope, $rootScope, $location, $routeParams, $http, $cookieStore, $timeout, spinnerService, $filter, Upload, DTOptionsBuilder, DTColumnBuilder, $q, $compile, textAngularManager) {

        $scope.headerTemplate = "view/admin/header/index.html";
        $scope.leftNavigationTemplate = "view/admin/om/sidebar/index.html";
        $scope.footerTemplate = "view/admin/footer/index.html";

        $scope.display_andon = 'adv';

        $scope.dashboard = {
            values: {
                loggedid	: $cookieStore.get('acct_id'),
                accountid	: $cookieStore.get('acct_id'),
                accteid		: $cookieStore.get('acct_eid'),
                accouttype	: $cookieStore.get('acct_type'),	
                accoutfname	: $cookieStore.get('acct_fname'),
                accoutlname	: $cookieStore.get('acct_lname'),
                acct_loc	: $cookieStore.get('acct_loc'),	
                userInformation: null,
                accttype:[],
                civilstat:[],
                emptypes:[],
                empstatus:[],
                joblvl:[],
                positions: [],
                labors:[],
                accounts:[],
            },
            active: function(){ 

                var urlData = {
                    'accountid': $scope.dashboard.values.accountid
                }
                $http.post(apiUrl+'tmsmems/loggedinuser.php',urlData)
                .then( function (response, status){			
                    var data = response.data;
                    if(data.status=='error'){	
                        $rootScope.modalDanger();
                    }else{ 
                        $scope.dashboard.values.userInformation = data; 
                        $scope.showAndon();
                    }				
                }, function(response) {
                    $rootScope.modalDanger();
                });	
            },
            setup: function(){ 
            }
        }

        $scope.intervaltest = function(){
            var ran = ['def','simp','adv'];
            var randomno = Math.random() * ran.length;
            randomno = Math.floor(randomno);
            
            $timeout(function () {
                $scope.display_andon = ran[randomno]; 
            }, 1000);
        }
        
       
        
        $scope.paginationandon = {}; 
        $scope.showAndon = function () { 
            $scope.andondetails = []; 
            $scope.paginationandon.currentPage = 1;
            $scope.paginationandon.totalItems = 0;
            $scope.paginationandon.pageSize = '10';
            $scope.paginationandon.length = 10;
            $scope.paginationandon.maxSize = 5;  
            function getdata() {
                if($location.path() == '/realtime/andon'){ 
                    $http.get('http://192.168.1.110:1880/getmachineinfo') 
                    .then(function(response) {
                        spinnerService.hide('form01spinner');
                        var data = response.data; 
                        $scope.andondetails = data;
                        $scope.andondetails.forEach(function(item, index) {
                            if(item.running == true && item.error == false){ // green 
                                item.patlite_color= '#29C7A4';
                            }else if(item.running == false && item.error == false){ // red
                                item.patlite_color= '#E94B5B';
                            }else{ // orange
                                item.patlite_color= '#FBA050';
                            }
                        });
                        $scope.paginationandon.totalItems = data.andondetails.length;  
                    })
                    .catch(function(error) {
                    // Handle error here
                        console.error('Error fetching data:', error);
                    });
                }
            } 

            $scope.$watchGroup(['paginationandon.currentPage', 'paginationandon.pageSize'], function () { 
                getdata();
                $scope.paginationandon.length = $scope.paginationandon.pageSize;
                 spinnerService.show('form01spinner');  
            });

            $scope.filterdata = function(){
                getdata(); 
            }

            $(document).ready(function(){ 
                setInterval(getdata, 5000); 
            });

        } 

        // $scope.dashboard.setup();
    }]);
