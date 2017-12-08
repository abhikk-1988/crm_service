/**
 * 
 * @fileOverview  Pusher Client Side Connection Class
 * @type Boolean
 * @author Abhishek Agrawal
 */

var Pusher			= Pusher || {};
var app_constant	= app_constant || {};

// Enable pusher logging - don't include this in production
Pusher.logToConsole = true;

var bmh_pusher; // global variable

function pusherClient () {

	var _app_key = app_constant.pusher_app_key;
	var _pusher_instance; // hold singleton object of pusher connection
	var _channel = '';
	var _events = app_constant.pusher_events;

	/**
	 *  Create new pusher connection 
	 * @returns {pusherClient._pusher_instance|Pusher}
	 * 
	 */
	function createConnection () {
		if ( typeof _pusher_instance === 'undefined' ) {
			// create and return new pusher object  
			_pusher_instance = new Pusher ( _app_key );
		}
		return _pusher_instance; // already created connection
	}


	/**
	 *  Get pusher connection
	 * @returns {undefined}
	 */
	this.getConnection = function () {
		createConnection ();
	};

	/**
	 * Subscribe to new a channel
	 **/
	this.subscribeToChannel = function ( channel_name ) {
		if ( typeof _pusher_instance !== 'undefined' ) {
			_channel = _pusher_instance.subscribe ( channel_name );
		}
	};

	// To get a specific channel
	this.getChannel = function ( channel_name ) {
		return _pusher_instance.channel ( channel_name );
	};


	// To Disconnect from pusher
	this.disconnectPusher = function () {

		if ( typeof _pusher_instance === 'undefined' ) {
			this.getConnection ();
		}

		_pusher_instance.disconnect ();
		_pusher_instance = undefined;
	};


	// To unbind channel(s)
	this.unsubscribeFromChannels = function ( channels ) {

		// ensure always receive in array
		if ( typeof channels === 'object' ) {

			if ( channels.length > 0 ) {

				channels.forEach ( function ( channel_name ) {
					_pusher_instance.unsubscribe ( channel_name );
				} );
			}
		}
	}

	/**
	 * Binding Events to Channel
	 * @returns {undefined}
	 */
	this.bindToEvents = function () {

		// here we binding events to client itself not with specific channel
		if ( typeof _channel != '' ) {
			_events.forEach ( function ( event_name ) {
				_channel.bind ( event_name, function ( data ) {
					showToast ( data.message, data.title, data.notification_type );
				} );
			} );
		}
	}

	// To unbind events from channel
	this.unbindEvents = function ( channel_events ) {

		if ( _channel != '' ) {

			if ( channel_events.length > 0 ) {

				channel_events.forEach ( function ( event ) {
					_channel.unbind ( event, function () {
						console.log ( event + ' unbinds successfully' );
					} );
				} );
			}
		}
	}

	// Return current state of pusher 
	this.getConnectionCurrentState = function () {
		if ( typeof _pusher_instance !== 'undefined' ) {
			return _pusher_instance.connection.state;
		}
	};

	this.trackConnectionState = function () {

		_pusher_instance.connection.bind ( 'state_change', function ( states ) {

			console.log ( 'PUSHER STATES: ' + states.current );
			if ( states.current === 'connected' ) {
				console.log ( 'Pusher connection established' );
			}

			if ( states.current === 'disconnected' ) {
				console.log ( 'Pusher disconnected' );
			}

		} );
	};
}