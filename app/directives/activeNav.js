/**
 * 
 * Custom Directive to apply a class on selected li of a ul 
 * @author Abhishek Agrawal
 * @type Decorator Directive
 */

var app = app || {};


(function (app, $){

	app.directive('activeNav', function (){
		
		return {
		
			strict : 'A',
			scope : {
				
			},
			
			compile : function (tElement, tAttr){
				
				$(tElement).find('li a').each(function (i,el){
					// apply padding on all li elements
					$(el).css({padding : '7px', borderRadius : '10px'});
					
					//onHover (el);
					
					$(el).click(function (){
						makeAllListsTransparent ();
						makeListSelected (this);
					});
				});
				
				function onHover (el){
					
					$(el).hover (function(){
						$(this).css({backgroundColor : '#CBD0D4', borderRadius : '10px', color:'#000'});
					}, function (){
						$(this).css({backgroundColor : 'transparent', color:'#3BB4C8'});
					});
				};
				
				function makeAllListsTransparent(){
					tElement.find('li a').css({backgroundColor: 'transparent', color :'#3BB4C8'});
				}
				
				function makeListSelected(jq_dom_ele){
					$(jq_dom_ele).css({backgroundColor : '#CBD0D4',color:'#000'});
				}
			
			}
		};
		
	});
	
}(app, jQuery));