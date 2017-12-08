

/**
 * @fileOverview Directive
 * 
 */

var app = app || {};
var directive_compile_count = 0;
(function (app,count){
    
    // A directive is something that extend HTML 
    // Directive consists of a DDO (Directive Definition Object)
    // Directive has some properties to create a working directive 
    
    // Naming convention of directive name : name must be start with a small letter and follow camel casing pattern for directive name
    
    app.directive('contactForm',function (){
       var directive = {};
       
       directive.restrict = 'A';
       directive.priority = 0;
       directive.template = '<h3>This is contact form template loaded via directiv:'+(++count)+'</h3>';
//       directive.replace = true; // To hide/ show the parent element
//       directive.scope = {}; // Scope is now isolated
       return directive;
    });
    
    app.directive('insideContact',function (){
        var directive = {};
        
        directive.restrict = 'A';
        directive.priority = 0;
        directive.template = '<span>another directive</span>';
//        directive.replace = false; // To hide/ show the parent element
//        directive.scope = {}; // Scope is now isolated
        return directive;
    });
})(app,directive_compile_count);