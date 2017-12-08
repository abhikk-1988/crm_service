    /**
     * Application constant js file 
     */

    // create a namesapce 
    var app_constant = app_constant || {};

    app_constant.protocol	= window.location.protocol;
    app_constant.hostname	= window.location.hostname; 
    app_constant.host		= window.location.host;	 
    if(app_constant.hostname === 'localhost'){
        app_constant.root = 'crm_local';
    }else{
        app_constant.root = 'crm/';
    }

//    app_constant.base_url	= 'http://' + '//'+ '52.77.73.171' + '/'+ app_constant.root + '/' ;

    app_constant.base_url = 'http://crm.bookmyhouse.biz/crm/';

    app_constant.pusher_app_key = '63248131fd9867c8e685';

    app_constant.pusher_events = ['my-event'];

    // Professions list
    app_constant.profession_list = [
        'Accounting/Finance','Advertising/PR/MR/Events','Agriculture/Dairy','Animation','Architecture/Interior Designing','Auto/Auto Ancillary','Aviation / Aerospace Firms','Banking/Financial Services/Broking','BPO/ITES','Brewery / Distillery','Broadcasting','Ceramics /Sanitary ware','Chemicals/PetroChemical/Plastic/Rubber','Construction/Engineering/Cement/Metals','Consumer Durables','Courier/Transportation/Freight','Defence/Government','Education/Teaching/Training','Electricals / Switchgears','Export/Import','Facility Management','Fertilizers/Pesticides','FMCG/Foods/Beverage','Food Processing','Gems & Jewellery','Glass','Heat Ventilation Air Conditioning','Hotels/Restaurants/Airlines/Travel','Industrial Products/Heavy Machinery','Insurance','Internet/Ecommerce','IT-Hardware & Networking','IT-Software/Software Services','KPO / Research /Analytics','Leather','Legal','Media/Dotcom/Entertainment','Medical/Healthcare/Hospital','Mining','NGO/Social Services','Office Equipment/Automation','Oil and Gas/Power/Infrastructure/Energy','Paper','Pharma/Biotech/Clinical Research','Printing/Packaging','Publishing','Real Estate/Property','Recruitment','Retail','Security/Law Enforcement','Semiconductors/Electronics','Shipping/Marine','Steel','Strategy /Management Consulting Firms','Sugar','Telcom/ISP','Textiles/Garments/Accessories','Tyres','Water Treatment / Waste Management','Wellness / Fitness / Sports / Beauty','Other'
    ];
	
	// Pusher connection domains
	app_constant.allowedPusherDomains = ['52.77.73.171','crm.bookmyhouse.biz','localhost'];