/**
 * package: CRM
 * 
 * @fileOverview Factory Service dateUtility.js
 * @author Abhishek Agrawal
 * @description This factory provides some common date utilities 
 */

var app = app || {};

(function (app){
	
	app.factory('dateUtility', function ($filter){
		
		var date_utility = {};
		
		var monthNames = {
			full : ["January", "February", "March","April", "May", "June","July", "August", "September","October", "November", "December"],
			short : ["Jan", "Feb", "Mar","Apr", "May", "Jun","Jul", "Aug", "Sep","Oct", "Nov", "Dec"]
		};
		
		date_utility.current_date = new Date ();
		
		date_utility.current_month_in_textual_representation = function (type){
			var month = '';
			
			if(type === 'full'){
				month = monthNames.full[this.current_date.getMonth ()];
			}
			if(type === 'short'){
				month = monthNames.short[this.current_date.getMonth()];
			}
			
			return month;
		};
		
		date_utility.current_month_in_numeric_representation = function (){
			var month = this.current_date.getMonth ();
			return month;
		};
		
		date_utility.get_month_name_from_number = function (month_number, type){
			
			if(type === 'full'){
				return monthNames.full[parseInt(month_number)];
			}
			
			if(type === 'short'){
				return monthNames.short[parseInt(month_number)];
			}
		};
		
		date_utility.get_month_number_from_name = function (month_name){
	
			return monthNames.short.indexOf ($filter('pluralizeString')($filter('lowercase')(month_name.toString ().slice(0,3))));
		};
		
		date_utility.current_year = new Date ().getFullYear ();
		
		
		return date_utility;
	});
	
}(app));