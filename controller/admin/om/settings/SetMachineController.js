app.controller('SetMachineController', ['$scope', '$rootScope', '$location', '$routeParams', '$http', '$cookieStore', '$timeout', 'spinnerService', '$filter', 'Upload', 'DTOptionsBuilder', 'DTColumnBuilder', '$q', '$compile', 'textAngularManager', '$templateRequest', '$sce',
    function ($scope, $rootScope, $location, $routeParams, $http, $cookieStore, $timeout, spinnerService, $filter, Upload, DTOptionsBuilder, DTColumnBuilder, $q, $compile, textAngularManager, $templateRequest, $sce) {

        $scope.headerTemplate = "view/admin/header/index.html";
        $scope.leftNavigationTemplate = "view/admin/om/sidebar/index.html";
        $scope.footerTemplate = "view/admin/footer/index.html";
		$scope.search_month='';
        $scope.search_year=''; 
        $scope.qrcode='m01';
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
                            // $scope.monthlyreport(); 
                        }
                    }, function (response) {
                        $rootScope.modalDanger();
                    });
            },
            setup: function () {
               
            }
        } 

       //begin region add function
       $scope.paginationmachine = {};
        $scope.filter = {};
        $scope.getmachineactiontypes = function () {
                $scope.paginationmachine.currentPage = 1
                $scope.paginationmachine.totalItems = 0;
                $scope.paginationmachine.pageSize = '10';
                $scope.paginationmachine.maxSize = 5;
                spinnerService.show('form01spinner');
    
                function getmachine() {
    
                    var urlData = {
                        'accountid': $scope.dashboard.values.accountid,
                        'filter': $scope.search,
                        'pagination': $scope.paginationmachine
                    };
    
                    $http.post(apiUrl + 'admin/om/settings/machine/machinedata.php', urlData)
                        .then(function (response, status) {
                            var data = response.data;
                            if (data.status == 'error') {
                                $rootScope.modalDanger();
                            } else {
                                $scope.machinedata = data.result;
                                $scope.paginationmachine.totalItems = data.totalItems;
                            } 
                            spinnerService.hide('form01spinner');
                        }, function (response) {
                            spinnerService.hide('form01spinner');
                            $rootScope.modalDanger();
                        });
                }
    
                $scope.$watchGroup(['paginationmachine.currentPage', 'paginationmachine.pageSize'], function () {
                    getmachine();
                });
    
                $scope.transSearch = function () {
                    getmachine(); 
                   $scope.getmachineactiontypes();
                }
    
                $scope.resetSearch = function () {  
                    $scope.search.stats  = ''; 
                    $scope.search.machine_name = '';
                    getmachine();
                }

                $scope.resetAdd = function(){ 
                    $scope.add	= {};
                    $timeout(function () {	 
                        $("#stats").select2().select2("val", null);
                    }, 500); 
                }

                $scope.addmachine = function () {
        
                    var urlData = {
                        'accountid': $scope.dashboard.values.accountid,
                        'info': $scope.add
                    }
            
                    $http.post(apiUrl + 'admin/om/settings/machine/addmachine.php', urlData)
                    .then(function (response, status) {
                        var data = response.data;
                        if (data.status == "error") {
                            $rootScope.modalDanger();
                        } else {
                            $("#addmachinemodal").modal("hide"); 
                            $.notify("Machine was successfully inserted", "success");
                            $scope.getmachineactiontypes();
                        }
                    }, function (response) {
                        $rootScope.modalDanger();
                    });
                }

                $scope.edit_view = function( data ){
                    $scope.edit = data;
                    
                    if($scope.edit.machine_code == null){
                        $("#umachine_code").val('').change();
                    }else{
                        $timeout(function () {
                            $("#umachine_code").val($scope.edit.machine_code).change();
                        }, 500); 
                    }
                    if($scope.edit.machine_name == null){
                        $("#umachine_name").val('').change();
                    }else{
                        $timeout(function () {
                            $("#umachine_name").val($scope.edit.machine_name).change();
                        }, 500); 
                    }
                    if($scope.edit.machine_pic == null){
                        $("#umachine_pic").val('').change();
                    }else{
                        $("#umachine_pic").val($scope.edit.machine_pic).change();
                    }
                  
                        
                    if($scope.edit.description == null){
                        $("#udescription").val('').change();
                    }else{
                        $("#udescription").val($scope.edit.description).change();
                    }
                    if($scope.edit.locator_code == null){
                        $("#ulocator_code").val('').change();
                    }else{
                        $("#ulocator_code").val($scope.edit.locator_code).change();
                    }
                    if($scope.edit.location == null){
                        $("#ulocation").val('').change();
                    }else{
                        $("#ulocation").val($scope.edit.location).change();
                    }
                    if($scope.edit.control_line == null){
                        $("#ucontrol_line").val('').change();
                    }else{
                        $("#ucontrol_line").val($scope.edit.control_line).change();
                    }

                  
                   $timeout(function () { 
                        $("#ustats").val($scope.edit.stats).change();
                        $("#uauto_off").val($scope.edit.auto_off).change();
                    }, 200);
                } 


                $scope.updatemachinetble = function () { 
            
                    var urlData = {
                        'accountid': $scope.dashboard.values.accountid,
                        'info': $scope.edit
                    }
            
                    console.log(urlData);
                    $http.post(apiUrl + 'admin/om/settings/machine/updatemachine.php', urlData)
                        .then(function (response, status) {
                            var data = response.data;
                            if (data.status == "error") {
                                $rootScope.modalDanger();
                            } else {
                                $("#updatemachinemodal").modal("hide");
                                $.notify("Machine table updated", "success");
                                $scope.gettransactiontypes();
                            }
                        }, function (response) {
                            $rootScope.modalDanger();
                        }); 
                }

        }

        $scope.setQR = function () { 
            var code='Machine1'
            var qr = new QRCode(document.getElementById("qrcode"), code);
        }

        $scope.generateQRCode= function (mcode) { 
            $scope.printqr = mcode;
            const canvas = document.getElementById("qrcodeCanvas");//$scope.$refs.qrcodeCanvas; //document.getElementById("qrcodeCanvas");
            const context = canvas.getContext('2d');
  
            // Clear the canvas
            context.clearRect(0, 0, canvas.width, canvas.height);
  
            // Generate QR code
            const qrCodeData = mcode.machine_code;
            const qrCodeSize = 185;
  
            // Set canvas size
            canvas.width = qrCodeSize;
            canvas.height = qrCodeSize;
  
            // Create QR code image
            const qrCodeImage = new Image();
            qrCodeImage.src = $scope.generateQRCodeDataUrl(qrCodeData, qrCodeSize);
  
            // Draw QR code image on the canvas once loaded
            qrCodeImage.onload = () => {
              context.drawImage(qrCodeImage, 0, 0, qrCodeSize, qrCodeSize);
            };
        }
  
        $scope.generateQRCodeDataUrl= function (data, size) {
        const qr = qrcode(0, 'M'); // Create a QR code generator with the desired error correction level ('M' = medium)
        qr.addData(data); // Add the data to the QR code generator
        qr.make(); // Generate the QR code

        // Set the size and padding of the canvas
        const canvas = document.createElement('canvas');
        canvas.width = size;
        canvas.height = size;
        const context = canvas.getContext('2d');
        context.fillStyle = '#ffffff';
        context.fillRect(0, 0, size, size);

        // Draw the QR code on the canvas
        const moduleCount = qr.getModuleCount();
        const cellSize = size / moduleCount;
        for (let row = 0; row < moduleCount; row++) {
            for (let col = 0; col < moduleCount; col++) {
            if (qr.isDark(row, col)) {
                context.fillStyle = '#000000';
            } else {
                context.fillStyle = '#ffffff';
            }
            context.fillRect(col * cellSize, row * cellSize, cellSize, cellSize);
            }
        }

        // Return the data URL of the canvas
        return canvas.toDataURL();
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
