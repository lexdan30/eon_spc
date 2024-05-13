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
                accoutfname	: $cookieStore.get('acct_fname'),
                accoutlname	: $cookieStore.get('acct_lname'), 
                username	: $cookieStore.get('username'),	
                userInformation: null
            },
            active: function(){ 

                var urlData = {
                    'username': $scope.dashboard.values.username,
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
                            $scope.andondetails[index].status = item.running; //this is for machine status so that it will reflect or be the reference of the toggle select -adrian
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

        //Adrian for turning on/off the machine using the togglesSelect
        $scope.changeStatus = function (index) {
            $scope.andondetails[index].status = !$scope.andondetails[index].status;
        
            // Determine the machine number (mc) based on the index or any other logic
            var machineNumber = index + 1; // Adjust as needed
        
            // Define the URL for the HTTP request
            var apiUrl = 'http://192.168.1.110:1880/setmachine?mc=' + machineNumber +
                '&running=' + $scope.andondetails[index].status +
                '&error=false';
        
            // Perform an HTTP GET request to the determined URL
            $http.get(apiUrl)
                .then(function (response) {
                    // Handle the success response if needed
                    console.log('HTTP GET Success:', response.data);
                })
                .catch(function (error) {
                    // Handle the error if needed
                    console.error('HTTP GET Error:', error);
                });
        };

        // $scope.dashboard.setup();
    }]);
