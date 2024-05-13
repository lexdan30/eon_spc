// JavaScript Document
app.config(['$interpolateProvider', '$httpProvider', '$routeProvider', '$locationProvider', function ($interpolateProvider, $httpProvider, $routeProvider, $locationProvider) {
    $httpProvider.defaults.userXDomain = true;
    $httpProvider.defaults.withCredentials = false;
    delete $httpProvider.defaults.headers.common['X-Requeste-With'];
    $httpProvider.defaults.headers.common['Accept'] = 'application/json';
    $httpProvider.defaults.headers.common['Content-Type'] = 'application/json';
    $httpProvider.defaults.headers.post['Content-Type'] = 'application/x-www-form-urlencoded; charset=UTF-8';
    $locationProvider.hashPrefix("");

    $routeProvider
        //LOGIN
        .when('/', {
            templateUrl: './view/login/index.html?v=npax07202020',
            controller: 'LoginController',
            resolve: {
                app: NotAuthenticated
            }
        })
        .when('/login', {
            templateUrl: './view/login/index.html?v=npax07202020',
            controller: 'LoginController',
            resolve: {
                app: NotAuthenticated
            }
        })  
        .when('/analysis/dashboard-chart', {
            templateUrl: './view/admin/om/analysis/dashboard/index.html?v=npax07202020',
            controller: 'DashboardController',
            resolve: {
                app: Authenticated
            }
        })
        .when('/analysis/machinedowntime', {
            templateUrl: './view/admin/om/analysis/machinedowntime/index.html?v=npax07202020',
            controller: 'MachineDowntimeController',
            resolve: {
                app: Authenticated
            }
        })
        .when('/analysis/kpi-details', {
            templateUrl: './view/admin/om/analysis/kpi-details/index.html?v=npax07202020',
            controller: 'KPIDetailsController',
            resolve: {
                app: Authenticated
            }
        })
        .when('/analysis/kpi-report', {
            templateUrl: './view/admin/om/analysis/kpi-report/index.html?v=npax07202020',
            controller: 'KPIReportController',
            resolve: {
                app: Authenticated
            }
        })
        .when('/analysis/operation-stat-report', {
            templateUrl: './view/admin/om/analysis/operation-stat-report/index.html?v=npax07202020',
            controller: 'OperationStatReportController',
            resolve: {
                app: Authenticated
            }
        })
        .when('/analysis/operation-stat-type-analysis', {
            templateUrl: './view/admin/om/analysis/operation-stat-type-analysis/index.html?v=npax07202020',
            controller: 'OperationStatTypeAnalysisController',
            resolve: {
                app: Authenticated
            }
        })
        .when('/analysis/operation-stat-summary', {
            templateUrl: './view/admin/om/analysis/operation-stat-summary/index.html?v=npax07202020',
            controller: 'OperationStatSummaryController',
            resolve: {
                app: Authenticated
            }
        })

        .when('/analysis/operation-stat-export', {
            templateUrl: './view/admin/om/analysis/operation-stat-export/index.html?v=npax07202020',
            controller: 'OperationStatExportController',
            resolve: {
                app: Authenticated
            }
        }) 
        .when('/realtime/andon', {
            templateUrl: './view/admin/om/realtime/andon/index.html?v=npax07102020C',
            controller: 'AndonController', 
            resolve: {
                app: Authenticated
            }
        })
        .when('/realtime/eqchart', {
            templateUrl: './view/admin/om/realtime/eqchart/index.html?v=npax07202020',
            controller: 'EquipmentChartController',
            resolve: {
                app: Authenticated
            }
        })
        .when('/realtime/eqlist', {
            templateUrl: './view/admin/om/realtime/eqlist/index.html?v=npax07202020',
            controller: 'EquipmentListController',
            resolve: {
                app: Authenticated
            }
        })
        .when('/admin/om/dashboard', {
            templateUrl: './view/admin/om/home/index.html?v=npax07202020',
            controller: 'OMHomeController',
            resolve: {
                app: Authenticated
            }
        })
        .when('/admin/om/employees', {
            templateUrl: './view/admin/om/employees/index.html?v=npax07202020',
            controller: '',
            resolve: {
                app: Authenticated
            }
        }) 
        .when('/om/educationalAttainment', { 
            templateUrl: './view/admin/om/report/educationalAttainment.html?v=npax07102020C',  
            controller: 'OMEducationalAttainmentController',
            resolve: {
                app: Authenticated
            }
        })
        .when('/om/employmentHistory', {
            templateUrl: './view/admin/om/report/employmentHistory.html?v=npax07102020C', 
            controller: 'OMemploymentHistoryController',
            resolve: {
                app: Authenticated
            }
        }) 
        .when('/settings/planner', {
            templateUrl: './view/admin/om/settings/planner/index.html?v=npax07202020',
            controller: 'SetPlannerController',
            resolve: {
                app: Authenticated
            }
        })
        .when('/settings/machine', {
            templateUrl: './view/admin/om/settings/machine/index.html?v=npax07202020',
            controller: 'SetMachineController',
            resolve: {
                app: Authenticated
            }
        })
        .when('/settings/material', {
            templateUrl: './view/admin/om/settings/material/index.html?v=npax07202020',
            controller: 'SetMaterialController',
            resolve: {
                app: Authenticated
            }
        })
        .when('/settings/operator', {
            templateUrl: './view/admin/om/settings/operator/index.html?v=npax07202020',
            controller: 'SetOperatorController',
            resolve: {
                app: Authenticated
            }
        })
        .when('/permission', {
            templateUrl: './view/error/403.html?v=npax07202020',
            controller: '404Controller'
        }).otherwise({
            templateUrl: './view/error/404.html?v=npax07202020',
            controller: '404Controller'
        });
}]);