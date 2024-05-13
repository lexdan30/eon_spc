app.controller('LoginController',function($scope, $rootScope, $location, $routeParams, $http, $cookieStore, $timeout, vcRecaptchaService,spinnerService){
	
	
	if($cookieStore.get('isAuthenticated')){		
		if( parseInt( $cookieStore.get('acct_type') ) != 3 ){
			$location.path('/analysis/dashboard-chart');	
		}else{
			$location.path("/permission");
		}
	}
	
	
	$scope.dymodalstat2 = false;
	$scope.dymodaltitle2= null;
	$scope.dymodalmsg2  = null;
	$scope.dymodalstyle2= null;
	$scope.dymodalicon2 = null;	
	
	$scope.mySignInForm = false;
	$scope.mySignUpForm = true;
	$scope.myForgetForm = true;	
	$scope.newUser = {
		userPassword:''
	};
	$scope.fname 		= null;
	$scope.lname 		= null;
	$scope.email 		= null;
	$scope.fname4get	= null;
	$scope.lname4get	= null;
	$scope.email4get	= null;
	$scope.username		= null;
	$scope.password		= null;
	$scope.actUname		= null;
	$scope.actCode		= null;
	$scope.publicKey 	='6Le__DMUAAAAANEinna87kE-72MA-TQoRxnZABWC'; //='6LeUtC0UAAAAANY2PAY3HBnmJfPQu9vAqfaEpts2';
	$scope.reset = false;
	$scope.revertReset = true;
	
	
	$scope.showForm = function( i ){
		if( parseInt(i) == 1 ){
			$scope.mySignInForm = false;
			$scope.mySignUpForm = true;
			$scope.myForgetForm = true;
		}else if( parseInt(i) == 2 ){
			$scope.mySignInForm = true;
			$scope.mySignUpForm = false;
			$scope.myForgetForm = true;
		}else if( parseInt(i) == 3 ){
			$scope.mySignInForm = true;
			$scope.mySignUpForm = true;
			$scope.myForgetForm = false;
			vcRecaptchaService.reload();
		}
		$scope.setFields();
	}
	$scope.resetpw = function(num){
		if(num == 1){
			$scope.revertReset = true;
			$scope.reset = false;
		}else{
			$scope.reset = true;
			$scope.revertReset = false;
		}
	}

	$scope.resetForm = function(){
		spinnerService.show('form01spinner');
		var urlData = {
			'uname' : $scope.resetusername, 
			'sss' : $scope.resetsss,
		};
		$http.post(apiUrl+"login/reset.php",urlData)
		.then( function (response, status){	
			spinnerService.hide('form01spinner');		
			var data = response.data;
			if(data.status=='nouname'){	
				$rootScope.dymodalstat = true;
				$rootScope.dymodaltitle= "Warning!";
				$rootScope.dymodalmsg  = "Please enter your username";
				$rootScope.dymodalstyle = "btn-warning";
				$rootScope.dymodalicon = "fa fa-exclamation-triangle";				
				$("#dymodal").modal("show");
			}else if(data.status=='nosss'){
				$rootScope.dymodalstat = true;
				$rootScope.dymodaltitle= "Warning!";
				$rootScope.dymodalmsg  = "Please enter your sss number";
				$rootScope.dymodalstyle = "btn-warning";
				$rootScope.dymodalicon = "fa fa-exclamation-triangle";				
				$("#dymodal").modal("show");
			}else if(data.status=='notfound'){
				$rootScope.dymodalstat = true;
				$rootScope.dymodaltitle= "Warning!";
				$rootScope.dymodalmsg  = "Incorrect username and/or SSS number";
				$rootScope.dymodalstyle = "btn-warning";
				$rootScope.dymodalicon = "fa fa-exclamation-triangle";				
				$("#dymodal").modal("show");
			}else if(data.status=='noemail'){
				$rootScope.dymodalstat = true;
				$rootScope.dymodaltitle= "Warning!";
				$rootScope.dymodalmsg  = "Email not found";
				$rootScope.dymodalstyle = "btn-warning";
				$rootScope.dymodalicon = "fa fa-exclamation-triangle";				
				$("#dymodal").modal("show");
			}else{
				$scope.resetusername = '';
				$scope.resetsss = '';
				$scope.resetpw(1);
				$rootScope.dymodalstat = true;
				$rootScope.dymodaltitle= "Success!";
				$rootScope.dymodalmsg  = "Email sent to your account for verification";
				$rootScope.dymodalstyle = "btn-info";
				$rootScope.dymodalicon = "fa fa-check";				
				$("#dymodal").modal("show");
			}
		}, function(response) {
			$rootScope.modalDanger();
		});	
	}

	$scope.cursor = function(){
		$timeout(function () {
			$('#uname').focus();
		}, 500);
	}
	
	$scope.loginForm = function(){
		var urlData = {
			'uname' : $scope.username,
			'passw' : $scope.password,
		};
		$http.post(apiUrl+"login/login.php",urlData)
		.then( function (response, status){			
			var data = response.data;
			if(data.status=='success'){		
				//var json_str = JSON.stringify(data.dpt1);		
				$scope.setFields();
				$cookieStore.put('isAuthenticated',new Date());
				$cookieStore.put('acct_id',data.id);
				$cookieStore.put('acct_eid',data.empid);
				$cookieStore.put('acct_fname',data.fname);
				$cookieStore.put('acct_lname',data.lname); 
				$cookieStore.put('username',data.username);
				$cookieStore.put('secret',data.secret); 
				$rootScope.global_branch="1";   
				$location.path('/analysis/dashboard-chart');
			}else if(data.status=='notfound'){	
				$rootScope.dymodalstat = true;
				$rootScope.dymodaltitle= "Warning!";
				$rootScope.dymodalmsg  = "Incorrect username and/or password";
				$rootScope.dymodalstyle = "btn-warning";
				$rootScope.dymodalicon = "fa fa-exclamation-triangle";				
				$("#dymodal").modal("show");
			}else if(data.status=='nopass'){	
				$rootScope.dymodalstat = true;
				$rootScope.dymodaltitle= "Warning!";
				$rootScope.dymodalmsg  = "Please enter your password";
				$rootScope.dymodalstyle = "btn-warning";
				$rootScope.dymodalicon = "fa fa-exclamation-triangle";				
				$("#dymodal").modal("show");
			}else if(data.status=='nouname'){	
				$rootScope.dymodalstat = true;
				$rootScope.dymodaltitle= "Warning!";
				$rootScope.dymodalmsg  = "Please enter your email address";
				$rootScope.dymodalstyle = "btn-warning";
				$rootScope.dymodalicon = "fa fa-exclamation-triangle";				
				$("#dymodal").modal("show");
			}else if(data.status=='error'){	
				$rootScope.modalDanger();
			}
		}, function(response) {
			$rootScope.modalDanger();
		});	
	}
	
	$scope.SignUpForm = function(){
		
		var regex=/^(?=.*[A-Za-z])(?=.*\d)(?=.*[$@$!%*#?&._])[A-Za-z\d$@$!%*#?&._]{6,}$/;		
		var x = ''+$scope.newUser.userPassword;				
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
			'fname'		: $scope.fname,
			'lname'		: $scope.lname,
			'email'		: $scope.email,
			'pass'		: $scope.newUser.userPassword,
			'idtype'	: 3,
			'verified'	: 0
		}
		$http.post(apiUrl+"login/create.php",urlData)
		.then( function (response, status){		
			var data = response.data;
			//console.log(data);
			if( data.status=="nofname" ){
				$rootScope.dymodalstat = true;
				$rootScope.dymodaltitle= "Warning!";
				$rootScope.dymodalmsg  = "Please enter your First Name";
				$rootScope.dymodalstyle = "btn-warning";
				$rootScope.dymodalicon = "fa fa-exclamation-triangle";				
				$("#dymodal").modal("show");
			}else if( data.status=="nolname" ){
				$rootScope.dymodalstat = true;
				$rootScope.dymodaltitle= "Warning!";
				$rootScope.dymodalmsg  = "Please enter your Last Name";
				$rootScope.dymodalstyle = "btn-warning";
				$rootScope.dymodalicon = "fa fa-exclamation-triangle";				
				$("#dymodal").modal("show");
			}else if( data.status=="noemail" ){
				$rootScope.dymodalstat = true;
				$rootScope.dymodaltitle= "Warning!";
				$rootScope.dymodalmsg  = "Please enter your Email Address";
				$rootScope.dymodalstyle = "btn-warning";
				$rootScope.dymodalicon = "fa fa-exclamation-triangle";				
				$("#dymodal").modal("show");
			}else if( data.status=="invalidemail" ){
				$rootScope.dymodalstat = true;
				$rootScope.dymodaltitle= "Warning!";
				$rootScope.dymodalmsg  = "You provided an invalid Email Address";
				$rootScope.dymodalstyle = "btn-warning";
				$rootScope.dymodalicon = "fa fa-exclamation-triangle";				
				$("#dymodal").modal("show");
			}else if( data.status=="nopass" ){
				$rootScope.dymodalstat = true;
				$rootScope.dymodaltitle= "Warning!";
				$rootScope.dymodalmsg  = "Please enter your Password";
				$rootScope.dymodalstyle = "btn-warning";
				$rootScope.dymodalicon = "fa fa-exclamation-triangle";				
				$("#dymodal").modal("show");
			}else if( data.status=="emailexist" ){
				$rootScope.dymodalstat = true;
				$rootScope.dymodaltitle= "Warning!";
				$rootScope.dymodalmsg  = "Email Address already exists";
				$rootScope.dymodalstyle = "btn-warning";
				$rootScope.dymodalicon = "fa fa-exclamation-triangle";				
				$("#dymodal").modal("show");
			}else if( data.status=="success" ){
				$rootScope.dymodalstat = true;
				$rootScope.dymodaltitle= "Success!";
				$rootScope.dymodalmsg  = "Email sent to your account for verification";
				$rootScope.dymodalstyle = "btn-info";
				$rootScope.dymodalicon = "fa fa-check";				
				$("#dymodal").modal("show");
				$scope.setFields();
			}else if( data.status=="error" ){
				$rootScope.modalDanger();
			}
		}, function(response) {
			$rootScope.modalDanger();
		});	
	}
	
	$scope.RetrieveForm = function(){	
		if(vcRecaptchaService.getResponse() === ""){ 
			$rootScope.dymodalstat = true;
			$rootScope.dymodaltitle= "Warning!";
			$rootScope.dymodalmsg  = "Please resolve the captcha and submit!";
			$rootScope.dymodalstyle = "btn-warning";
			$rootScope.dymodalicon = "fa fa-exclamation-triangle";				
			$("#dymodal").modal("show");
		}else{			
			var urlData = {
				'fname'					: $scope.fname4get,
				'lname'					: $scope.lname4get,
				'email'					: $scope.email4get,
				'recaptcha'				: vcRecaptchaService.getResponse()
			}
			$http.post(apiUrl+"login/retrieve.php",urlData)
			.then( function (response, status){		
				var data = response.data;
				if( data.status=="nofname" ){
					vcRecaptchaService.reload();
					$rootScope.dymodalstat = true;
					$rootScope.dymodaltitle= "Warning!";
					$rootScope.dymodalmsg  = "Please enter your First Name";
					$rootScope.dymodalstyle = "btn-warning";
					$rootScope.dymodalicon = "fa fa-exclamation-triangle";				
					$("#dymodal").modal("show");
				}else if( data.status=="nolname" ){
					vcRecaptchaService.reload();
					$rootScope.dymodalstat = true;
					$rootScope.dymodaltitle= "Warning!";
					$rootScope.dymodalmsg  = "Please enter your Last Name";
					$rootScope.dymodalstyle = "btn-warning";
					$rootScope.dymodalicon = "fa fa-exclamation-triangle";				
					$("#dymodal").modal("show");
				}else if( data.status=="noemail" ){
					vcRecaptchaService.reload();
					$rootScope.dymodalstat = true;
					$rootScope.dymodaltitle= "Warning!";
					$rootScope.dymodalmsg  = "Please enter your Email Address";
					$rootScope.dymodalstyle = "btn-warning";
					$rootScope.dymodalicon = "fa fa-exclamation-triangle";				
					$("#dymodal").modal("show");
				}else if( data.status=="invalidemail" ){
					vcRecaptchaService.reload();
					$rootScope.dymodalstat = true;
					$rootScope.dymodaltitle= "Warning!";
					$rootScope.dymodalmsg  = "You provided an invalid Email Address";
					$rootScope.dymodalstyle = "btn-warning";
					$rootScope.dymodalicon = "fa fa-exclamation-triangle";				
					$("#dymodal").modal("show");
				}else if( data.status=="emailnoexist" ){
					vcRecaptchaService.reload();
					$rootScope.dymodalstat = true;
					$rootScope.dymodaltitle= "Warning!";
					$rootScope.dymodalmsg  = "Account could not be found on our database";
					$rootScope.dymodalstyle = "btn-warning";
					$rootScope.dymodalicon = "fa fa-exclamation-triangle";				
					$("#dymodal").modal("show");
				}else if( data.status=="wronginfo" ){
					vcRecaptchaService.reload();
					$rootScope.dymodalstat = true;
					$rootScope.dymodaltitle= "Warning!";
					$rootScope.dymodalmsg  = "Wrong First and Last Name info for Email Address provided";
					$rootScope.dymodalstyle = "btn-warning";
					$rootScope.dymodalicon = "fa fa-exclamation-triangle";				
					$("#dymodal").modal("show");
				}else if( data.status=="unverified" ){
					//pop up send reverification code	
					var msg = "Your account is not yet verified.";	
					msg = msg + "<br/><br/>Please be sure not to add extra spaces. You will need to type in your username and activation code on the page that appears when you visit the URL. You only have until today to do this.";
					msg = msg + "<br/><br/>Your Username is: "+data.uname+"<br/>Your Activation ID is: " + data.code;
					$rootScope.dymodalstat = true;
					$rootScope.dymodaltitle= "Warning!";
					$rootScope.dymodalmsg  = msg
					$rootScope.dymodalstyle = "btn-warning";
					$rootScope.dymodalicon = "fa fa-exclamation-triangle";				
					$("#dymodal").modal("show");					
				}else if( data.status=="wrongcaptcha" ){
					vcRecaptchaService.reload();
					$rootScope.dymodalstat = true;
					$rootScope.dymodaltitle= "Warning!";
					$rootScope.dymodalmsg  = "Robots Not allowed (Captcha verification failed)";
					$rootScope.dymodalstyle = "btn-warning";
					$rootScope.dymodalicon = "fa fa-exclamation-triangle";				
					$("#dymodal").modal("show");
				}else if( data.status=="success" ){
					vcRecaptchaService.reload();
					$scope.fname4get	= null;
					$scope.lname4get	= null;
					$scope.email4get	= null;
					$rootScope.dymodalstat = true;
					$rootScope.dymodaltitle= "Success!";
					$rootScope.dymodalmsg  = "You can now log in using these credentials:<br/><br/>Email: "+data.uname+"<br/>Password: "+data.pass+"<br/><br/>New credentials are also sent to your email";
					$rootScope.dymodalstyle = "btn-info";
					$rootScope.dymodalicon = "fa fa-check";				
					$("#dymodal").modal("show");									
				}else if( data.status=="error" ){
					vcRecaptchaService.reload();
					$rootScope.modalDanger();
				}
				
			},function(response) {
				vcRecaptchaService.reload();
				$rootScope.modalDanger();
			});				
		}
	}
	
	$scope.activateForm = function(){
		var urlData = {
			'uname' : $scope.actUname,
			'code'  : $scope.actCode,
		};		
		$http.post(apiUrl+"login/activate.php",urlData)
		.then( function (response, status){
			var data = response.data;
			
			if( data.status=="error" ){
				$rootScope.modalDanger();
			}else if(data.status=='nouname'){	
				$rootScope.dymodalstat = true;
				$rootScope.dymodaltitle= "Warning!";
				$rootScope.dymodalmsg  = "Please enter your username";
				$rootScope.dymodalstyle = "btn-warning";
				$rootScope.dymodalicon = "fa fa-exclamation-triangle";				
				$("#dymodal").modal("show");
			}else if(data.status=='nopass'){	
				$rootScope.dymodalstat = true;
				$rootScope.dymodaltitle= "Warning!";
				$rootScope.dymodalmsg  = "Please enter your password";
				$rootScope.dymodalstyle = "btn-warning";
				$rootScope.dymodalicon = "fa fa-exclamation-triangle";				
				$("#dymodal").modal("show");
			}else if( data.status=="invalidemail" ){
				$rootScope.dymodalstat = true;
				$rootScope.dymodaltitle= "Warning!";
				$rootScope.dymodalmsg  = "You provided an invalid username";
				$rootScope.dymodalstyle = "btn-warning";
				$rootScope.dymodalicon = "fa fa-exclamation-triangle";				
				$("#dymodal").modal("show");
			}else if( data.status=="unotfound" ){
				$rootScope.dymodalstat = true;
				$rootScope.dymodaltitle= "Warning!";
				$rootScope.dymodalmsg  = "Username not found on our database";
				$rootScope.dymodalstyle = "btn-warning";
				$rootScope.dymodalicon = "fa fa-exclamation-triangle";				
				$("#dymodal").modal("show");
			}else if( data.status=="cnotfound" ){
				$rootScope.dymodalstat = true;
				$rootScope.dymodaltitle= "Warning!";
				$rootScope.dymodalmsg  = "Wrong activation code provided";
				$rootScope.dymodalstyle = "btn-warning";
				$rootScope.dymodalicon = "fa fa-exclamation-triangle";				
				$("#dymodal").modal("show");
			}else if( data.status=="verified" ){
				$rootScope.dymodalstat = true;
				$rootScope.dymodaltitle= "Warning!";
				$rootScope.dymodalmsg  = "Account already verified";
				$rootScope.dymodalstyle = "btn-warning";
				$rootScope.dymodalicon = "fa fa-exclamation-triangle";				
				$("#dymodal").modal("show");
			}else if( data.status=="unverified" ){
				//pop up send reverification code	
				var msg = "Your activation code has already expired. We have provided you with the updated code below.";	
				msg = msg + "<br/><br/>Please be sure not to add extra spaces. You will need to type in your username and activation code again. You only have until today to do this.";
				msg = msg + "<br/><br/>Your Username is: "+data.uname+"<br/>Your Activation ID is: " + data.code;
				$rootScope.dymodalstat = true;
				$rootScope.dymodaltitle= "Warning!";
				$rootScope.dymodalmsg  = msg
				$rootScope.dymodalstyle = "btn-warning";
				$rootScope.dymodalicon = "fa fa-exclamation-triangle";				
				$("#dymodal").modal("show");					
			}else if(data.status=='success'){				
				$scope.actUname=null;
				$scope.actCode=null;
				$cookieStore.put('isAuthenticated',new Date());
				$cookieStore.put('acct_id',data.id);
				$cookieStore.put('acct_fname',data.fname);
				$cookieStore.put('acct_lname',data.lname);
				$cookieStore.put('acct_email',data.email);
				$cookieStore.put('acct_type',data.id_type);
				
				if( parseInt(data.id_type) == 3 ){
					$location.path('/my/jobs');	
				}else{
					$location.path('/portal/applications');	
				}
			}
			
		},function(response) {
			$rootScope.modalDanger();
		});	
	}
	
	$scope.setFields = function(){
		$scope.newUser = {
			userPassword:''
		};
		$scope.fname 		= null;
		$scope.lname 		= null;
		$scope.email 		= null;
		$scope.fname4get	= null;
		$scope.lname4get	= null;
		$scope.email4get	= null;
		$scope.username		= null;
		$scope.password		= null;
	}
	
});