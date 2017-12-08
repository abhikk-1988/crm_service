/**
 * Custom directive for upload files 
 */

var app = app || {};

(function (app){
	
	
	app.directive ('fileModel', ['$parse', function ($parse){
			
			return {
				
				restrict : 'A',
				
				link : function (scope, element, attrs){
					
					var model;
					var modelSetter;
					
					model		= $parse(attrs.fileModel);
					modelSetter = model.assign;
					
					element.bind('change', function (){
						scope.$apply(function (){
							modelSetter(scope, element[0].files[0]);
						});
					});
				}	
			};	
	}]);
	
} (app));