/**
 * USING ANGULARJS CORE HTTP SERVICE TO MAKE HTTP CALLS TO LOCAL OR REMOTE SERVERS
 * @author Abhishek Agrawal
 * @version 1.1.0
 * @fileOverview This service will return an HTTP promise object
 */

var app = app || {};

( function ( app ) {

	app.factory ( 'httpService', function ( $http ) {

		var http = {};

		http.makeRequest = function ( config ) {

			var request_url = config.url;
			var request_method = config.method;
			var request_data = {};

			if ( request_method === 'POST' ) {
				request_data = config.data;
			}

			return $http ( {
				url: request_url,
				method: request_method,
				data: request_data
			} );

		};

		return http;

	} );
} ) ( app );