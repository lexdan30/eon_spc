// No Session
var NotAuthenticated = ['$q', '$cookieStore', '$location', '$rootScope', function($q, $cookieStore, $location, $rootScope){
	var defer = $q.defer();
	defer.resolve();
	return defer.promise;
}]
// allow session create or login
var Authenticated = ['$q', '$cookieStore', '$location', '$rootScope', function($q, $cookieStore, $location, $rootScope){
	var url = $location.url();
	var defer = $q.defer();

	if($cookieStore.get('isAuthenticated')){		
		if( parseInt( $cookieStore.get('acct_type') ) != 3 || $cookieStore.get('dept1')!='' || $cookieStore.get('promApp1')!= 0 || $cookieStore.get('dptmtrx').length > 0 || $cookieStore.get('latTransApp1')!= 0 || $cookieStore.get('WageIncApp1')!= 0 ){ //Jerald//Dan
			defer.resolve();
		}else if( parseInt( $cookieStore.get('acct_type') ) == 3 && ( url.indexOf("emp") >= 0 || url.indexOf("home") >= 0 ) ){
			defer.resolve();
		}else{
			$location.path("/permission");
		}
		//defer.resolve();
	}else{
		$location.path("/");
	}	
	return defer.promise;
}]