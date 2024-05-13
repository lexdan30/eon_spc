app.controller('SetPlannerController', ['$scope', '$rootScope', '$location', '$routeParams', '$http', '$cookieStore', '$timeout', 'spinnerService', '$filter', 'Upload', 'DTOptionsBuilder', 'DTColumnBuilder', '$q', '$compile', 'textAngularManager', '$templateRequest', '$sce',
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
                        }
                    }, function (response) {
                        $rootScope.modalDanger();
                    });
            },
            setup: function () {
               
            }
        } 
         
        $scope.paginationplanner = {};
        $scope.filter = {};
        $scope.getplanneractiontypes = function () {
                $scope.paginationplanner.currentPage = 1
                $scope.paginationplanner.totalItems = 0;
                $scope.paginationplanner.pageSize = '10';
                $scope.paginationplanner.maxSize = 5;
                spinnerService.show('form01spinner');
    
                function getplanner() {
    
                    var urlData = {
                        'accountid': $scope.dashboard.values.accountid,
                        'filter': $scope.search,
                        'pagination': $scope.paginationplanner
                    };
    
                    $http.post(apiUrl + 'admin/om/settings/planner/plannerdata.php', urlData)
                        .then(function (response, status) {
                            var data = response.data;
                            if (data.status == 'error') {
                                $rootScope.modalDanger();
                            } else {
                                $scope.plannerdata = data.result;
                                $scope.paginationplanner.totalItems = data.totalItems;
                            } 
                            spinnerService.hide('form01spinner');
                        }, function (response) {
                            spinnerService.hide('form01spinner');
                            $rootScope.modalDanger();
                        });
                }
    
                $scope.$watchGroup(['paginationplanner.currentPage', 'paginationplanner.pageSize'], function () {
                    getplanner();
                });
    
                $scope.transSearch = function () {
                    getplanner(); 
                   $scope.getplanneractiontypes();
                }
    
                $scope.resetSearch = function () {  
                    $scope.search.stats  = ''; 
                    $scope.search.kanban_id = '';
                    getplanner();
                }

                $scope.resetAdd = function(){ 
                    $scope.add	= {};
                    $timeout(function () {	 
                        $("#stats").select2().select2("val", null);
                    }, 500); 
                }

                $scope.addkanban = function () {
        
                    var urlData = {
                        'accountid': $scope.dashboard.values.accountid,
                        'info': $scope.add
                    }
            
                    $http.post(apiUrl + 'admin/om/settings/planner/addkanban.php', urlData)
                    .then(function (response, status) {
                        var data = response.data;
                        if (data.status == "error") {
                            $rootScope.modalDanger();
                        } else {
                            $("#addkanbanmodal").modal("hide"); 
                            $.notify("Kanban was successfully inserted", "success");
                            $scope.getplanneractiontypes();
                        }
                    }, function (response) {
                        $rootScope.modalDanger();
                    });
                }

                $scope.edit_view = function( data ){
                    // $timeout(function () { 
                    //     $("#sstat").select2().select2("val", null);
                    //     $("#sstat").val('');
                    // }, 100); 
                    $scope.edit = data;
                    
                    if($scope.edit.kanban_id == null){
                        $("#ukanban_id").val('').change();
                    }else{
                        $timeout(function () {
                            $("#ukanban_id").val($scope.edit.kanban_id).change();
                        }, 500); 
                    }
                    if($scope.edit.wo_no == null){
                        $("#uwo_no").val('').change();
                    }else{
                        $("#uwo_no").val($scope.edit.wo_no).change();
                    }
                        
                    if($scope.edit.prod_no == null){
                        $("#uprod_no").val('').change();
                    }else{
                        $("#uprod_no").val($scope.edit.prod_no).change();
                    }
            
                    if($scope.edit.prod_qty == null){
                        $("#uprod_qty").val('').change();
                    }else{
                        $("#uprod_qty").val($scope.edit.prod_qty).change();
                    }

                  
                   $timeout(function () { 
                        $("#ustats").val($scope.edit.stats).change();
                    }, 200);  
                } 


                $scope.updatekanbantble = function () { 
            
                    var urlData = {
                        'accountid': $scope.dashboard.values.accountid,
                        'info': $scope.edit
                    }
            
                    console.log(urlData);
                    $http.post(apiUrl + 'admin/om/settings/planner/updatekanban.php', urlData)
                        .then(function (response, status) {
                            var data = response.data;
                            if (data.status == "error") {
                                $rootScope.modalDanger();
                            } else {
                                $("#updatekanbanmodal").modal("hide");
                                $.notify("Planner table updated", "success");
                                $scope.gettransactiontypes();
                            }
                        }, function (response) {
                            $rootScope.modalDanger();
                        }); 
                }

        }

        $(document).ready(function () {
            if ($("body").hasClass("sidebar-collapse")) {
                $('.sidebar').removeClass("sidebar1280")
            } else {
                $('.sidebar').addClass('sidebar1280')
            }
            $('#stats').select2();
            $('#sstats').select2();
            $('#ustats').select2();
            
        });
 


        $scope.dashboard.setup();
    }]);
