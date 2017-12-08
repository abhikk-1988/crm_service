/**
 * Custom Directive of showing loading image on element s
 */

var app = app || {};

(function (app,$){

	
	app.directive('loading', function ($compile){
	   
	   var loading  = {};
	   
	   loading.restrict = 'EA';
	   
	   loading.scope = {
		data : '=', // two way binding 
	   };
	   
	   loading.compile = function (tElement,tAttribute){
		
		// post link function 
		return function (scope,element,attr,controller){
		   
		};
	   };
	   
	   loading.controller = function ($scope,$element,$attrs){
		
		$scope.$watch('data', function (val){
		   
			if($scope.data.start === 1){
				$scope.attachLoader ();
			}
			
			if($scope.data.stop === 1){
				$scope.detachLoader ();
			}
		},true);
		
		$scope.attachLoader = function (){
		   
			if(angular.isUndefined ($scope.data.apply_default_css)){
				$element.css({backgroundColor: 'grey',opacity :'0.5'});
			}
			
			if(angular.isDefined ($scope.data.position)){
				$element.css({'position' : $scope.data.position});
			}
			
			var loading_gif = '<img src="stuffs/images/facebook.gif" class="center-loading"/>';
			var compiled_element = $compile(loading_gif)($scope);
			$element.append(compiled_element);
		};
		
		$scope.detachLoader = function (){
			$element.css({backgroundColor: 'transparent',opacity :1});
			$element.find('img').remove();
		};
	
	   };
	   
	   return loading;
	});
	
} (app,jQuery));