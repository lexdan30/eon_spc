app.controller('SetOperatorController', ['$scope', '$rootScope', '$location', '$routeParams', '$http', '$cookieStore', '$timeout', 'spinnerService', '$filter', 'Upload', 'DTOptionsBuilder', 'DTColumnBuilder', '$q', '$compile', 'textAngularManager', '$templateRequest', '$sce',
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
                            $scope.monthlyreport(); 
                        }
                    }, function (response) {
                        $rootScope.modalDanger();
                    });
            },
            setup: function () {
               
            }
        } 
         
        $scope.paginationoperator = {};
        $scope.filter = {};
        $scope.getoperatoractiontypes = function () {
            $scope.paginationoperator.currentPage = 1
            $scope.paginationoperator.totalItems = 0;
            $scope.paginationoperator.pageSize = '10';
            $scope.paginationoperator.maxSize = 5;
            spinnerService.show('form01spinner');

            function getoperator() {

                var urlData = {
                    'accountid': $scope.dashboard.values.accountid,
                    'filter': $scope.search,
                    'pagination': $scope.paginationoperator
                };

                $http.post(apiUrl + 'admin/om/settings/operator/operator.php', urlData)
                    .then(function (response, status) {
                        var data = response.data;
                        if (data.status == 'error') {
                            $rootScope.modalDanger();
                        } else {
                            $scope.operatordata = data.result;
                            $scope.paginationoperator.totalItems = data.totalItems;
                        } 
                        spinnerService.hide('form01spinner');
                    }, function (response) {
                        spinnerService.hide('form01spinner');
                        $rootScope.modalDanger();
                    });
            }

            $scope.$watchGroup(['paginationoperator.currentPage', 'paginationoperator.pageSize'], function () {
                getoperator();
            });

            $scope.transSearch = function () {
                getoperator(); 
               $scope.getoperatoractiontypes();
            }

            $scope.resetSearch = function () {  
                $scope.search.stats  = ''; 
                $scope.search.emp_id = '';
                $scope.search.operators_name = '';
                $scope.search.position = '';
                getoperator();
            }

            $scope.resetAdd = function(){ 
                $scope.add	= {};
                $timeout(function () {	 
                    $("#stats").select2().select2("val", null);
                }, 500); 
            }
            
            $scope.addoperator = function () {
        
                var urlData = {
                    'accountid': $scope.dashboard.values.accountid,
                    'info': $scope.add
                }
        
                $http.post(apiUrl + 'admin/om/settings/operator/addoperator.php', urlData)
                .then(function (response, status) {
                    var data = response.data;
                    if (data.status == "error") {
                        $rootScope.modalDanger();
                    } else {
                        $("#addoperatormodal").modal("hide"); 
                        $.notify("Operator was successfully inserted", "success");
                        $scope.getoperatoractiontypes();
                    }
                }, function (response) {
                    $rootScope.modalDanger();
                });
            }

            $scope.edit_view = function( data ){
                $scope.edit = data;
                
                if($scope.edit.emp_id == null){
                    $("#uemp_id").val('').change();
                }else{
                    $timeout(function () {
                        $("#uemp_id").val($scope.edit.emp_id).change();
                    }, 500); 
                }
                if($scope.edit.operators_name == null){
                    $("#uoperators_name").val('').change();
                }else{
                    $("#uoperators_name").val($scope.edit.operators_name).change();
                }
                if($scope.edit.position == null){
                    $("#uposition").val('').change();
                }else{
                    $("#uposition").val($scope.edit.position).change();
                }
              
               $timeout(function () { 
                    $("#ustats").val($scope.edit.stats).change();
                }, 200);  
            } 


            $scope.updateoperatortble = function () { 
        
                var urlData = {
                    'accountid': $scope.dashboard.values.accountid,
                    'info': $scope.edit
                }
        
                console.log(urlData);
                $http.post(apiUrl + 'admin/om/settings/operator/updateoperator.php', urlData)
                    .then(function (response, status) {
                        var data = response.data;
                        if (data.status == "error") {
                            $rootScope.modalDanger();
                        } else {
                            $("#updateoperatormodal").modal("hide");
                            $.notify("Operator table updated", "success");
                            $scope.getoperatoractiontypes();
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
