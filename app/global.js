app.run(['$rootScope', '$http', '$cookieStore', '$location', '$routeParams', '$window', '$timeout', function ($rootScope, $http , $cookieStore , $location, $routeParams, $window, $timeout) {
    /*
	$timeout(function () {
		if($location.path() == '/lateral-transfer---current---' && $routeParams.p!=undefined){
			$cookieStore.put('emailurl', window.location.href);
		}
		if($location.path() == '/wage-increase---current---' && $routeParams.p!=undefined){
			$cookieStore.put('emailurl', window.location.href);
		}
		if($location.path() == '/promotion-and-upgradation---current---' && $routeParams.p!=undefined){
			$cookieStore.put('emailurl', window.location.href);
		}
	}, 100);
	*/
	
	$rootScope.bg=[];
    $rootScope.password_old=null;
	$rootScope.password_confirm=null;
	$rootScope.confirm_password_old=null;
    $rootScope.edit_newUser = {
		userPassword:''
	};
	$rootScope.currperiod = [];
	$rootScope.idleave	  = '';
  
	$rootScope.dymodalstat = false;
	$rootScope.dymodaltitle= null;
	$rootScope.dymodalmsg  = null;
	$rootScope.dymodalstyle= null;
	$rootScope.dymodalicon = null;	
	$rootScope.dyentry = 0;	
	$rootScope.title ="HRIS";
	
	
	$rootScope.changeBranch=function(br){
		$cookieStore.put('global_branch',br);
		$rootScope.global_branch=br;
		$window.location.reload();
	}
	
	// change password
	$rootScope.clearPassword=function(){
		$rootScope.password_old=null;
		$rootScope.confirm_password_old=null;
		$rootScope.password_confirm=null;
		$rootScope.edit_newUser = {
			userPassword:''
		};
	}	

	$rootScope.getadminacct = function(){
		return $cookieStore.get('acct_id');
	}
	
	$rootScope.editpassword = function(){
		
		var regex=/^(?=.*[A-Za-z])(?=.*\d)(?=.*[$@$!%*#?&._])[A-Za-z\d$@$!%*#?&._]{6,}$/;		
		var x = ''+$rootScope.edit_newUser.userPassword;				
		if(!x.match(regex)){
			$rootScope.dymodalstat = true;
			$rootScope.dymodaltitle= "Warning!";
			$rootScope.dymodalmsg  = "Invalid Password Format";
			$rootScope.dymodalstyle = "btn-warning";
			$rootScope.dymodalicon = "fa fa-exclamation-triangle";				
			$("#dymodal").modal("show");
			return;
		}
		
		var urlData = {
			'id'			 			: $cookieStore.get('acct_id'),  
			'oldpassword'	 			: $cookieStore.get('hash_key'),
			'confirm_oldpassword'		: $rootScope.confirm_password_old,
			'newpassword'	 			: $rootScope.edit_newUser.userPassword,
			'confirmpassword'			: $rootScope.password_confirm,
			'fname'			 			: $cookieStore.get('acct_fname'),
			'lname'			 			: $cookieStore.get('acct_lname'),
			'email'			 			: $cookieStore.get('acct_email')
		}
		$http.post('/eon_spc/assets/php/editpassword.php',urlData)
		.then( function (response, status){			
			var data = response.data;			
			if( data.status == "success" ){
				$("#editpassword").modal("hide");
				$rootScope.dymodalstat = true;
				$rootScope.dymodaltitle= "Success!";
				$rootScope.dymodalmsg  = "Password updated successfuly";
				$rootScope.dymodalstyle = "btn-info";
				$rootScope.dymodalicon = "fa fa-check";				
				$("#dymodal").modal("show");
				$timeout(function () {
					$("#dymodal").modal("hide");			
				}, 1000);	
				$timeout(function () {	
					$rootScope.logOut();
				}, 2000);

			}else if( data.status == "error" ){
				$rootScope.modalDanger();
			
			}
			else if( data.status == "oldpassworddidnotmatch" ){
				$rootScope.dymodalstat = true;
				$rootScope.dymodaltitle= "Warning!";
				$rootScope.dymodalmsg  = "Old Password did not match";
				$rootScope.dymodalstyle = "btn-warning";
				$rootScope.dymodalicon = "fa fa-exclamation-triangle";				
				$("#dymodal").modal("show");
			}else if( data.status == "passwordnotfound" ){
				$rootScope.dymodalstat = true;
				$rootScope.dymodaltitle= "Warning!";
				$rootScope.dymodalmsg  = "Incorrect password";
				$rootScope.dymodalstyle = "btn-warning";
				$rootScope.dymodalicon = "fa fa-exclamation-triangle";				
				$("#dymodal").modal("show");
			}else if( data.status == "passwordblank" ){
				$rootScope.dymodalstat = true;
				$rootScope.dymodaltitle= "Warning!";
				$rootScope.dymodalmsg  = "Please enter data on new password field and match it with confirm password field";
				$rootScope.dymodalstyle = "btn-warning";
				$rootScope.dymodalicon = "fa fa-exclamation-triangle";				
				$("#dymodal").modal("show");
			}else if( data.status == "passwordnotmatch" ){
				$rootScope.dymodalstat = true;
				$rootScope.dymodaltitle= "Warning!";
				$rootScope.dymodalmsg  = "New password field and Confirm password field must be equal";
				$rootScope.dymodalstyle = "btn-warning";
				$rootScope.dymodalicon = "fa fa-exclamation-triangle";				
				$("#dymodal").modal("show");
			}
		}, function(response) {
			$rootScope.modalDanger();
		});
	}

	$rootScope.getCurrentPeriod = function(){
		$rootScope.currperiod = [];
		var urlData = {
			'accountid': $cookieStore.get('acct_id')
		}
		$http.post('/eon_spc/assets/php/admin/tk/setup/settings.php',urlData)
		.then( function (response, status){			
			var data = response.data;
			$rootScope.currperiod 	= data.period;	
			$cookieStore.put('pay_start',$rootScope.currperiod.pay_start);
			$cookieStore.put('pay_end',$rootScope.currperiod.pay_end);
			$cookieStore.put('id',$rootScope.currperiod.id);
			$cookieStore.put('type',$rootScope.currperiod.type);
		}, function(response) {
			$rootScope.modalDanger();
		});
	}
	
	// active menu
	$rootScope.activeclass = function(){
		var curr = $window.location;			
		curr = curr.toString(); 
		$(".sidebar-menu").find(".active").removeClass("active");
		$timeout(function () {	
			$(".sidebar-menu li").each(function(){
				if($(this).attr("id")){				
					var eleId = $(this).attr("id");
					eleId = eleId; 
					var n = curr.indexOf(eleId); 
					
					if(n>0){
						//alert(curr);
						$("#"+eleId).closest( ".treeview" ).addClass("active");
						$("#"+eleId).addClass("active");
					} 
				}
			}); 
		}, 500);
	}
	
	$rootScope.getHRaccess = function(){
		$http.post('/eon_spc/assets/php/admin/hr/home/hrportalaccess.php')
		.then( function (response, status){			
			var data = response.data;
			$rootScope.hrmanager 	= data.hr;	
		}, function(response) {
			$rootScope.modalDanger();
		});
	}
	
	$rootScope.logOut = function(){
		$cookieStore.remove('companies');
		$cookieStore.remove('selectedcomp');
		$cookieStore.remove('isAuthenticated');
		$cookieStore.remove('acct_id');
		$cookieStore.remove('acct_fname');
		$cookieStore.remove('acct_lname');
		$cookieStore.remove('acct_email');
		$cookieStore.remove('acct_type');
		$cookieStore.remove('acct_eid');
		$cookieStore.remove('acct_loc');	
		$cookieStore.remove('global_branch');
		$cookieStore.remove('mvmodalclick');
		$cookieStore.remove('pay_start');
		$cookieStore.remove('pay_end');
		$location.path('/');
	} 
	
	// ajax error
	// $rootScope.modalDanger = function(){
	// 	$rootScope.dymodalstat = true;
	// 	$rootScope.dymodaltitle= "Oops...";
	// 	$rootScope.dymodalmsg  = "Something Went Wrong! Please Reload The Page.";
	// 	$rootScope.dymodalstyle = "btn-danger";	
	// 	$rootScope.dymodalicon = "fa fa-exclamation-circle";	
	// 	$("#dymodal").modal("show");
	// }
	$rootScope.modalDanger = function(){
		$rootScope.dymodalstat = true;
		$rootScope.dymodaltitle= "Oops...";
		$rootScope.dymodalmsg  = "This operation has been cancelled due to some issues. Please contact your N-Pax support if this issue persists.";
		$rootScope.dymodalstyle = "btn-danger";	
		$rootScope.dymodalicon = "fa fa-exclamation-circle";	
		$("#dymodal").modal("show");
	}

	$rootScope.getCompanyName = function(){

		var urlData = {
			'accountid': $cookieStore.get('acct_id')
		}
		
		$http.post(apiUrl+"admin/hr/report/getCompanyName.php", urlData)
		.then( function (response, status){     
			var data = response.data;
			
			$rootScope.compensationCompanyName = data;
	
	
		}, function(response) {
			$rootScope.modalDanger();
		}); 

	}

	$rootScope.getAllEmployeeReportFunc = function(){
		var urlData = {
			'accountid': $cookieStore.get('acct_id')
		}
		$http.post("/eon_spc/assets/php/admin/hr/report/getAllEmployeeReport.php", urlData)
		.then(function(result){
			if(result.data.status == "empty"){
				$rootScope.getAllEmployeeReport = [];
			}else{
				$rootScope.getAllEmployeeReport = result.data;
			}
		},function(error){}).finally(function(){});
	} 

	$rootScope.allEmployeePositionTitleFunc = function(){
		var urlData = {
			'accountid': $cookieStore.get('acct_id')
		}
		$http.post("/eon_spc/assets/php/admin/hr/report/allEmployeePositionTitle.php", urlData)
		.then(function(result){
			if(result.data.status == "empty"){
				$rootScope.allEmployeePositionTitle = [];
			}else{
				$rootScope.allEmployeePositionTitle = result.data;
			}
		},function(error){}).finally(function(){});
	}

	$rootScope.allEmployeeDepartmentNameFunc = function(){
		var urlData = {
			'accountid': $cookieStore.get('acct_id')
		}
		$http.post("/eon_spc/assets/php/admin/hr/report/allEmployeeDepartmentName.php", urlData)
		.then(function(result){
			if(result.data.status == "empty"){
				$rootScope.allEmployeeDepartmentName = [];
			}else{
				$rootScope.allEmployeeDepartmentName = result.data;
			}
		},function(error){}).finally(function(){});
	}

	$rootScope.allEmployeeDepartmentCodeFunc = function(){
		var urlData = {
			'accountid': $cookieStore.get('acct_id')
		}
		$http.post("/eon_spc/assets/php/admin/hr/report/allEmployeeDepartmentCode.php", urlData)
		.then(function(result){
			if(result.data.status == "empty"){
				$rootScope.allEmployeeDepartmentCode = [];
			}else{
				$rootScope.allEmployeeDepartmentCode = result.data;
			}
		},function(error){}).finally(function(){});
	}

	$rootScope.allEmploymentStatusFunc = function(){
		var urlData = {
			'accountid': $cookieStore.get('acct_id')
		}
		$http.post("/eon_spc/assets/php/admin/hr/employee/allEmploymentStatus.php", urlData)
		.then(function(result){
			if(result.data.status == "empty"){
				$rootScope.allEmploymentStatus = [];
			}else{
				$rootScope.allEmploymentStatus = result.data;
			}
		},function(error){}).finally(function(){});
	}
	
	//$rootScope.getCurrentPeriod();
	$rootScope.board_leave	= {
		"ty": null, 
		"st": null,
		"df": null,
		"dt": null
	};
	
	$(window).on('popstate', function() {
		$(".modal-backdrop").remove();
	});
	
	 $rootScope.apptype = function (type, header) {
        $cookieStore.remove('header');
        $cookieStore.remove('type');
        $cookieStore.put('header', header);
        $cookieStore.put('type', type);
    }

    $rootScope.leavecounts = function () {
        var urlData = {
            'accountid': $cookieStore.get('acct_id')
        }
        $http.post("/eon_spc/assets/php/admin/mng/leave/leavecounts.php", urlData)
            .then(function (response, status) {
                $rootScope.leavetypelist = response.data;
            }, function (response) {
                $rootScope.modalDanger();
            });
    }
	 $rootScope.timekeepingcounts = function () {
        var urlData = {
            'accountid': $cookieStore.get('acct_id')
        }
        $http.post("/eon_spc/assets/php/admin/mng/timekeeping/timekeepingcounts.php", urlData)
            .then(function (response, status) {
                $rootScope.timekeepingcount = response.data;
            }, function (response) {
                $rootScope.modalDanger();
            });
    }
	
}]);

// URL
var apiUrl = "/eon_spc/assets/php/";