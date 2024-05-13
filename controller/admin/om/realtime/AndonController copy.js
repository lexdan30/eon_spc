app.controller('AndonController', ['$scope', '$rootScope', '$location', '$routeParams', '$http', '$cookieStore', '$timeout', 'spinnerService', '$filter', 'Upload', 'DTOptionsBuilder', 'DTColumnBuilder', '$q', '$compile', 'textAngularManager',
    function ($scope, $rootScope, $location, $routeParams, $http, $cookieStore, $timeout, spinnerService, $filter, Upload, DTOptionsBuilder, DTColumnBuilder, $q, $compile, textAngularManager) {

        $scope.headerTemplate = "view/admin/header/index.html";
        $scope.leftNavigationTemplate = "view/admin/om/sidebar/index.html";
        $scope.footerTemplate = "view/admin/footer/index.html";

        $scope.display_andon = 'def';

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
                        $timeout(function () { 
                            $("#selandon").val('def').change();
                        }, 100); 
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
        $scope.blinking = '';
        $scope.blinking_stat = false;
        $scope.blinking_machines = [false,false,false,false,false,false,false,false]
        $scope.showAndon = function () { 
            $scope.andondetails = []; 
            $scope.paginationandon.currentPage = 1;
            $scope.paginationandon.totalItems = 0;
            $scope.paginationandon.pageSize = '5';
            // $scope.paginationandon.length = 10;
            $scope.paginationandon.maxSize = 5;  
            function getdata() {
                if($location.path() == '/realtime/andon'){ 
                    $http.get('http://192.168.1.110:1880/getmachineinfo') 
                    .then(function(response) {
                        spinnerService.hide('form01spinner');
                        var data = response.data.result; 
                        $scope.andondetails = data;
                        $scope.andondetails.forEach(function(item, index) {
                            $scope.andondetails[index].status = item.running;  //this is for machine status so that it will reflect or be the reference of the toggle select -adrian
                            if(item.auto_off == 1){
                               //alert($scope.blinking_stat.index);
                               $scope.blinking_stat = $scope.blinking_machines[index+1];
                                if(!$scope.blinking_stat || item.running){ 
                                    $scope.changeStatus(index,'prog');
                                    //$scope.blinking_stat = true;
                                    $scope.blinking_machines[index+1] = true;
                                    $scope.sendEmail(item,'off'); 
                                }
                            }else if(item.run_blinking == 'run_blink'){
                                $scope.sendEmail(item,'warn'); 
                            } 
                            if(item.running == true && item.error == false){ // green 
                                item.patlite_color= '#29C7A4';
                            }else if(item.running == false && item.error == false){ // red
                                item.patlite_color= '#E94B5B';
                            }else{ // orange
                                item.patlite_color= '#FBA050';
                            }
                           
                        });
                        $scope.paginationandon.totalItems = response.data.totalItems;  
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
        $scope.control_machineno = '';
        $scope.control_no = '';
        $scope.control_index = '';
        $scope.changeStatus = function (index,parent_trigger) {
            if($scope.andondetails[index].blinking == 'blink' && parent_trigger == 'click'){
                var r = confirm("Are you sure you want to continue?");
                if (r == true) {
                    $scope.control_machineno = $scope.andondetails[index].name;
                    $scope.control_no = $scope.andondetails[index].machines_controlline; 
                    $scope.control_index = index; 
                    $("#updatecontrolmodal").modal("show");
                    return;
                } 
            } 
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
        }

        // $scope.dashboard.setup();

        $scope.sendEmail = function (machine,auto_status) {  
            var urlData = {
                'username': $scope.dashboard.values.username,
                'accountid': $scope.dashboard.values.accountid,
                'info':machine,
                'auto_status':auto_status //off or warn
            }
            $http.post(apiUrl + 'admin/om/notification.php', urlData)
            .then(function (response, status) {
                var data = response.data;
                if (data.status == 'error') {
                    $rootScope.modalDanger();
                }
            }, function (response) {
                $rootScope.modalDanger();
            });
        }

        $scope.toggleFullScreen = function () { 

            var elem = document.getElementById("andonfull");
            if ((document.fullScreenElement !== undefined && document.fullScreenElement === null) || (document.msFullscreenElement !== undefined && document.msFullscreenElement === null) || (document.mozFullScreen !== undefined && !document.mozFullScreen) || (document.webkitIsFullScreen !== undefined && !document.webkitIsFullScreen)) {
              if (elem.requestFullScreen) {
                elem.requestFullScreen();
              } else if (elem.mozRequestFullScreen) {
                elem.mozRequestFullScreen();
              } else if (elem.webkitRequestFullScreen) {
                elem.webkitRequestFullScreen(Element.ALLOW_KEYBOARD_INPUT);
              } else if (elem.msRequestFullscreen) {
                elem.msRequestFullscreen();
              }
            } else {
              if (document.cancelFullScreen) {
                document.cancelFullScreen();
              } else if (document.mozCancelFullScreen) {
                document.mozCancelFullScreen();
              } else if (document.webkitCancelFullScreen) {
                document.webkitCancelFullScreen();
              } else if (document.msExitFullscreen) {
                document.msExitFullscreen();
              }
            }
        }

        $scope.updateControlLine = function (control_no,index) { 
            var id = $scope.andondetails[index].id;
            var urlData = {
                'username': $scope.dashboard.values.username,
                'accountid': $scope.dashboard.values.accountid,
                'control_no':control_no,
                'machine_id':id
            }
            $http.post(apiUrl + 'admin/om/realtime/andon_control.php', urlData)
            .then(function (response, status) {
                var data = response.data;
                if (data.status == 'error') {
                    $rootScope.modalDanger();
                }else{ 
                    $("#updatecontrolmodal").modal("hide");
                    $.notify("Control line updated", "success");
                    $scope.andondetails[index].status = !$scope.andondetails[index].status; 
                    var machineNumber = index + 1;   
                    var apiUrl = 'http://192.168.1.110:1880/setmachine?mc=' + machineNumber +
                        '&running=' + $scope.andondetails[index].status +
                        '&error=false'; 
                    $http.get(apiUrl)
                    .then(function (response) { 
                        console.log('HTTP GET Success:', response.data);
                    })
                    .catch(function (error) { 
                        console.error('HTTP GET Error:', error);
                    });
                }
            }, function (response) {
                $rootScope.modalDanger();
            }); 
        }

        $(document).ready(function () {
            if ($("body").hasClass("sidebar-collapse")) {
                $('.sidebar').removeClass("sidebar1280")
            } else {
                $('.sidebar').addClass('sidebar1280')
            }
            $('.selandon').select2();  
        });
    }]);
