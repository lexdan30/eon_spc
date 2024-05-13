app.controller('AdmHomeController',[ '$scope', '$rootScope', '$location', '$routeParams', '$http', '$cookieStore', '$timeout', 'spinnerService', '$filter', 'Upload', 'DTOptionsBuilder', 'DTColumnBuilder', '$q', '$compile','textAngularManager',
function($scope, $rootScope, $location, $routeParams, $http, $cookieStore, $timeout, spinnerService, $filter, Upload, DTOptionsBuilder, DTColumnBuilder, $q, $compile, textAngularManager ){
	
	$scope.headerTemplate="view/admin/header/index.html";
	$scope.leftNavigationTemplate="view/admin/home/sidebar/index.html";
	$scope.footerTemplate="view/admin/footer/index.html";	 

	$scope.dashboard = {
		values: {
			loggedid	: $cookieStore.get('acct_id'),
			accountid	: $cookieStore.get('acct_id'),
			accteid		: $cookieStore.get('acct_eid'),
			accouttype	: $cookieStore.get('acct_type'),	
			accoutfname	: $cookieStore.get('acct_fname'),
			accoutlname	: $cookieStore.get('acct_lname'),
			acct_loc	: $cookieStore.get('acct_loc'),	
			accountcomp	: $cookieStore.get('companies'),	
			username	: $cookieStore.get('username'),	
			userInformation: null,
			typeList:null, 
			statusList:null
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
					$rootScope.global_branch = data.db;
					$cookieStore.put('dept1',data.undersecretary);
					if(data.pw_flag == '1' ){
					    $("#editpassword").modal("show");
					}
				}
			}, function(response) {
				$rootScope.modalDanger();
			});	
		}		
	} 	


	$scope.dashboard.values.accountcomp = $scope.dashboard.values.accountcomp.split("--");

	if($cookieStore.get('selectedcomp')){	
		$scope.selectedcomp = $cookieStore.get('selectedcomp');
		
	}else{
		$scope.selectedcomp = $scope.dashboard.values.accountcomp[0];
	}

	$(document).ready(function () {
		$scope.$watch('selectedcomp', function() {
			//$scope.choosedb($scope.dbnames[$scope.selectedcomp]);
		});
	});

	$scope.choosedb = function(db){
		var urlData = {
			'accountid': $scope.dashboard.values.accountid,
			'db': db
		}

		$http.post(apiUrl + 'admin/dbselected.php', urlData)
			.then(function (response, status) {
				var data = response.data;
				console.log(data);
			}, function (response) {
				$rootScope.modalDanger();
			});
	}
	
}]);