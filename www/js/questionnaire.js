
var questionnaire = angular.module('questionnaire', []);

questionnaire.filter('round', function () {
    return function (number) {
        return (typeof number === 'number' && !isNaN(number)) ?Math.round(number) :0;
    };
});

function QuestionnaireBasicCtrl($scope, $http, $q, $timeout) {
    // urls
    $scope.saveUrl = '/';
    $scope.storeUrl = '/';
    $scope.whisperCompanyUrl = '/';

    $scope.loading = false;
    $scope.done = false;

    // form fields
    $scope.questionnaire = {
        sector: '',
        company_name: '',
        company_ic: '',
        company_size: '',
        company_address_street: '',
        company_address_city: '',
        company_address_postcode: '',
        xname: '',
        work_intensity: '',
        work_duration: '',
        work_position: '',
        manager_position: '',
        manager_firstname: '',
        manager_lastname: '',
        manager_academy_title: '',
        manager_phone: '',
        developer_position: '',
        developer_firstname: '',
        developer_lastname: '',
        developer_academy_title: '',
        developer_phone: '',
        save: ''
    };


    $scope.sectors = [
        { label: 'IT - Vývoj krabicového software', value: 1 },
        { label: 'IT - Správa', value: 2 },
        { label: 'IT - Služby', value: 3 },
        { label: 'IT - Vývoj webových stránek', value: 4 },
        { label: 'IT - jiné', value: 5 },
        { label: 'Bezpečnost', value: 10 },
        { label: 'Cestovní ruch – volný čas', value: 11 },
        { label: 'Doprava', value: 12 },
        { label: 'Ekologie', value: 13 },
        { label: 'Energetika', value: 14 },
        { label: 'Finančnictví', value: 15 },
        { label: 'Hornická činnost a činnosti obdobné', value: 16 },
        { label: 'Kultura', value: 17 },
        { label: 'Metrologie, zkušebnictví a technická normalizace', value: 18 },
        { label: 'Osobní služby', value: 19 },
        { label: 'Ostatní služby', value: 20 },
        { label: 'Potravinářství', value: 21 },
        { label: 'Právní služby', value: 22 },
        { label: 'Řemeslné činnosti', value: 23 },
        { label: 'Sociální služby', value: 24 },
        { label: 'Stavebnictví', value: 25 },
        { label: 'Technické služby', value: 26 },
        { label: 'Veterinární služby a zvířata', value: 27 },
        { label: 'Vzdělávání', value: 28 },
        { label: 'Zdravotnictví', value: 29 },
        { label: 'Zemědělství', value: 30 },
    ];

    $scope.company_sizes = [
        { label: 'Drobné (do 9 zaměstnanců)', value: 1 },
        { label: 'Malá (10 až 49 zaměstnanců)', value: 2 },
        { label: 'Střední (50 až 249 zaměstnanců)', value: 3 },
        { label: 'Velká (od 250 zaměstnanců)', value: 4 }
    ];

    $scope.work_durations = [
        { label: '1 až 3 měsíce', value: 3 },
        { label: '4 až 6 měsíců', value: 6 },
        { label: '7 měsíců až 1 rok ', value: 12 },
        { label: '2 roky', value: 24 },
        { label: '3 roky', value: 36 },
        { label: '4 roky', value: 48 },
        { label: '5 let', value: 60 },
        { label: '6 a více let', value: 72 }
    ];

    $scope.steps = {
        1: ['sector', 'company_name', 'company_ic', 'company_size'
            , 'company_address_street', 'company_address_city', 'company_address_postcode'],
        2: ['xname', 'work_intensity', 'work_duration', 'work_position'],
        3: [ // not needed
            //'manager_position', 'manager_firstname', 'manager_lastname', 'manager_academy_title', 'manager_phone'
            //,'developer_position', 'developer_firstname', 'developer_lastname', 'developer_academy_title', 'developer_phone'
        ],
        4: ['save']
    };


    $scope.step = function () {
        var q = $scope.questionnaire;
        var actualStep = null;
        _.forEach($scope.steps, function (fields, step) {
            if (actualStep !== null) return;
            var isSomeNotFilled = _.any(fields, function (field) {
                return typeof q[field] === 'undefined' || q[field] === '';
            });
            if (isSomeNotFilled) {
                actualStep = step;
            }
        });
        return actualStep;
    };

    $scope.completePercentage = function () {
        return $scope.countFilled() / $scope.count() * 100;
    };

    $scope.countFilled = function () {
        return _.countBy($scope.questionnaire, function (q) {
            return typeof q === 'undefined' || q === '' ?'empty' :'filled';
        })['filled'];
    };

    $scope.count = function () {
        return _.countBy($scope.questionnaire, function () { return 0; })[0];
    };

    $scope.showStep = function (step) {
        if ($scope.step() >= step)
            return true;
        return false;
    };

    var storeCancler = $q.defer();
    $scope.store = function () {
        storeCancler.resolve();
        storeCancler = $q.defer();
        var data = {
            'questionnaire': $scope.questionnaire
        };
        $http({method: 'POST', url: $scope.storeUrl, data: data, timeout: storeCancler.promise})
            .success(function (resp) {
            //console.log(resp);
        });
    };

    $scope.save = function () {
        storeCancler.resolve();
        $scope.loading = true;
        var data = {
            'questionnaire': $scope.questionnaire
        };
        $http.post($scope.saveUrl, data).success(function (resp) {
            $scope.loading = false;
            $scope.done = true;
            //console.log(resp);
        })
            .error(function (resp) {
                $scope.loading = false;
                //console.log(resp);
            });
    };

    $scope.$watch('questionnaire', function(newValue, oldValue) {
        //console.log(newValue);
        //console.log(oldValue);
        $scope.store();
    }, true);


    $scope.fillByCompany = function (type, company) {
        $scope.questionnaire.company_ic = company.ic;
        $scope.questionnaire.company_name = company.name;
        $scope.questionnaire.company_size = company.size;
        $scope.questionnaire.company_address_city = company.address_city;
        $scope.questionnaire.company_address_street = company.address_street;
        $scope.questionnaire.company_address_postcode = company.address_postcode;
        $scope.whisperer[type] = [];
    };


    // whisper
    var whispererCancler = $q.defer();
    var whispererTimer = $timeout(function () {}, 1);
    $scope.whisperer = {};
    $scope.whisper = function (type, model) {
        $timeout.cancel(whispererTimer);
        whispererTimer = $timeout(function () {
            whispererCancler.resolve();
            whispererCancler = $q.defer();
            if (typeof model === 'undefined' || !model)
                $scope.whisperer[type] = [];
            else
                $scope.whisperer[type] = [{ name: 'loading... (for '+model+')' }];

            $http({method: 'POST', url: $scope.whisperCompanyUrl, data: {
                model: model
            }, timeout: whispererCancler.promise})
                .success(function (resp) {
                    $scope.whisperer[type] = resp.whisperer;
                })
                .error(function (resp) {
                    //$scope.whisperer[type] = [];
                });
        }, 500);
    };


    var stored = {};
    $scope.store = function (name, value) {
        if (typeof value !== 'undefined') {
            $timeout(function () {
                stored[name] = value;
            }, 100);
        }
        return stored[name];
    }
}