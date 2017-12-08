/**
 * @version 1.0
 * script.js 
 * main script file for common functionality in application
 * @author Abhishek Agrawal
 */

jQuery.noConflict();
(function ($){
    
                $(document).ready(function (){
                    
                    // Slide left nav list down or up 
                    $('.left-nav-expander').click(function (e){
                       
                        e.stopPropagation(); // Stop propagating event
                        
                        var slide_direction  = $('#left-navigation-block').attr('data-slide');
                        switch(slide_direction){
                            
                            case 'down':
                                // Animate left navigation block 
                                $('#left-navigation-block').animate({
                                    width:'0px'
                                },{
                                    duration:100,
                                    easing:'swing',
                                    done : function (){      
                                        $(this).attr('data-slide','up');
                                    }
                                });
                                
                                // Animate main content area
                                $('#content-layout').animate({
                                    marginLeft:'0px',width:'100%'
                                },{
                                    duration:100,
                                    easing:'swing',
                                    done: function (){}
                                });
                                
                                // Animate top header portion
                                $('#menu-header').animate({marginLeft:'0px',width:'100%'},{
                                    duration : 100,
                                    easing:'swing',
                                    done: function (){
                                        
                                    }
                                });
                                break;
                                
                            case 'up':
                                // Animate main content area
                                $('#content-layout').animate({
                                    marginLeft:'230px',
                                    width:'1119px'
                                },{
                                    duration:100,
                                    easing:'swing',
                                    done: function (){}
                                });
                                
                                // Animate top header section area
                                $('#menu-header').animate({
                                    marginLeft:'230px',
                                    width:'1119px'
                                },{
                                    duration:100,
                                    easing:'swing',
                                    done: function (){}
                                });
                                
                                // Animate left navigation block
                                $('#left-navigation-block').animate({
                                    width: '230px'
                                },{
                                    duration:100,
                                    easing:'swing',
                                    done : function (){
                                        $(this).attr('data-slide','down');
                                    }
                                });
                                break;
                            default:
                                $('#content-layout').animate({left:'0',width:'64%'},{duration:400,easing:'swing',done: function (){}}); 
                                $('#left-navigation-block').animate({
                                    left: '0%'
                                },{
                                    duration:400,
                                    easing:'swing',
                                    done : function (){
                                        $(this).attr('data-slide','down'); 
                                    }
                                });
                                break;
                        }
                            
                    });
                   
                });
                
            })(jQuery);