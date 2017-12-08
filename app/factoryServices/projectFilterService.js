/**
 * Project Filters values factory service 
 */

var app = app || {};

(function (app){
    
	app.factory('projectFilters',function (){
        
		var filter_values = {};
        
		// Budget 
		filter_values.budget_range = {
            
			min : [
			{
				value : '1000000',
				label : '10',
				currency_suffix : 'Lacs',
				selected : ''
			},
 			{
				value : '2000000',
				label : '20',
				currency_suffix : 'Lacs',
				selected : ''
			},
			{
				value : '3000000',
				label : '30',
				currency_suffix : 'Lacs',
				selected : ''
			},
 			{
				value : '4000000',
				label : '40',
				currency_suffix : 'Lacs',
				selected : ''
			},
			{
				value : '5000000',
				label : '50',
				currency_suffix : 'Lacs',
				selected : ''
			},
            {
                value : '6000000',
                label : '60',
                currency_suffix : 'Lacs',
                selected : ''
            },
            {
                value : '7000000',
                label : '70',
                currency_suffix : 'Lacs',
                selected : ''
            },
            {
                value : '8000000',
                label : '80',
                currency_suffix : 'Lacs',
                selected : ''
            },
            {
                value : '9000000',
                label : '90',
                currency_suffix : 'Lacs',
                selected : ''
            },
            {
                value : '10000000',
                label : '1.0',
                currency_suffix : 'Cr',
                selected : ''
            },
            {
                value : '12000000',
                label : '1.2',
                currency_suffix : 'Cr',
                selected : ''
            },
            {
                value : '15000000',
                label : '1.5',
                currency_suffix : 'Cr',
                selected : ''
            },
            {
                value : '20000000',
                label : '2.0',
                currency_suffix : 'Cr',
                selected : ''
            },
            {
                value : '25000000',
                label : '2.5',
                currency_suffix : 'Cr',
                selected : ''
            },
            {
                value : '30000000',
                label : '3.0',
                currency_suffix : 'Cr',
                selected : ''
            },
            {
                value : '35000000',
                label : '3.5',
                currency_suffix : 'Cr',
                selected : ''
            },
            {
                value : '40000000',
                label : '4.0',
                currency_suffix : 'Cr',
                selected : ''
            },
            {
                value : '45000000',
                label : '4.5',
                currency_suffix : 'Cr',
                selected : ''
            }  
],
			max : [
			{
				value : '2000000',
				label : '20',
				currency_suffix : 'Lacs',
				selected : ''
			},
 			{
				value : '3000000',
				label : '30',
				currency_suffix : 'Lacs',
				selected : ''
			},
 
			{
				value : '4000000',
				label : '40',
				currency_suffix : 'Lacs',
				selected : ''
			},
 			{
				value : '5000000',
				label : '50',
				currency_suffix : 'Lacs',
				selected : ''
			},
 
			{
				value : '6000000',
				label : '60',
				currency_suffix : 'Lacs',
				selected : ''
			},
            {
                value : '7000000',
                label : '70',
                currency_suffix : 'Lacs',
                selected : ''
            },
            {
                value : '8000000',
                label : '80',
                currency_suffix : 'Lacs',
                selected : ''
            },
            {
                value : '9000000',
                label : '90',
                currency_suffix : 'Lacs',
                selected : ''
            },
            {
                value : '10000000',
                label : '1.0',
                currency_suffix : 'Cr',
                selected : ''
            },
            {
                value : '12000000',
                label : '1.2',
                currency_suffix : 'Cr',
                selected : ''
            },
            {
                value : '15000000',
                label : '1.5',
                currency_suffix : 'Cr',
                selected : ''
            },
            {
                value : '20000000',
                label : '2.0',
                currency_suffix : 'Cr',
                selected : ''
            },
            {
                value : '25000000',
                label : '2.5',
                currency_suffix : 'Cr',
                selected : ''
            },
            {
                value : '30000000',
                label : '3.0',
                currency_suffix : 'Cr',
                selected : ''
            },
            {
                value : '35000000',
                label : '3.5',
                currency_suffix : 'Cr',
                selected : ''
            },
            {
                value : '40000000',
                label : '4.0',
                currency_suffix : 'Cr',
                selected : ''
            },
            {
                value : '45000000',
                label : '4.5',
                currency_suffix : 'Cr',
                selected : ''
            },
            {
                value : '50000000',
                label : '5',
                currency_suffix : 'Cr',
                selected : ''
            },
]
};
        
        // properry types
		filter_values.property_types = [
		{
			label : 'Plot',
			value : 'PLOT',
			type : 'R'
		},
		{
			label : 'Flat',
			value : 'FLAT',
			type : 'R'
		},
		{
			label : 'House/Villa',
			value : 'HOUSE/VILLA',
			type : 'R'
		},
		{
			label : 'Builder floor',
			value : 'BUILDER FLOOR',
			type : 'R'
		},
		{
			label : 'Office Space',
			value : 'OFFICE SPACE',
			type : 'C'
		},
		{
			label : 'Shop/Showroom',
			value : 'SHOP/SHOWROOM',
			type : 'C'
		},
		{
			label : 'Service Apartment',
			value : 'SERVICE APARTMENT',
			type : 'C'
		}
	];
		
		filter_values.property_status = [
			{
				label : 'Under construction',
				value : 'under construction'
			},
			{
				label : 'Ready To Move',
				value : 'ready to move'
			},
			{
				label : 'New Launch',
				value : 'new launch'
			}
		];
		
		return filter_values;
	});	
}) (app);