
var questionnaire = angular.module('questionnaire', []);

questionnaire.filter('round', function () {
    return function (number) {
        return (typeof number === 'number' && !isNaN(number)) ?Math.round(number) :0;
    };
});

function QuestionnaireBasicCtrl($scope, $http) {
    // urls
    $scope.saveUrl = '/';
    $scope.storeUrl = '/';

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
        { label: 'Vývoj software', value: 1 },
        { label: 'Finančnictví', value: 2 }
    ];

    $scope.company_sizes = [
        { label: 'Malá', value: 1 },
        { label: 'Střední', value: 2 },
        { label: 'Velká', value: 3 }
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

    $scope.store = function () {
        var data = {
            'questionnaire': $scope.questionnaire
        };
        $http.post($scope.storeUrl, data).success(function (resp) {
            console.log(resp);
        });
    };

    $scope.save = function () {
        var data = {
            'questionnaire': $scope.questionnaire
        };
        $http.post($scope.saveUrl, data).success(function (resp) {
            console.log(resp);
        })
            .error(function (resp) {
                console.log(resp);
            });
    };

    $scope.$watch('questionnaire', function(newValue, oldValue) {
        console.log(newValue);
        console.log(oldValue);
        $scope.store();
    }, true);
}