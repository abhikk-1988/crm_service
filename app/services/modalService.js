/**
 * @fileOverview Bootstrap modal service for application
 */

var app = app  || {};

(function (app){
    
    app.service('modalService', function (){
            
            this.size = '';
            
            this.title = '';
            
            this.template = '';
            
            this.templateUrl = '';
            
            this.showCloseBtn = false;
            
            this.showSaveBtn = false;
            
            this.showFooter = false;
            
            this.data = null;
            
            this.clearModal = function (){
                this.size           = '';
                this.title          = '';
                this.template       = '';
                this.templateUrl    = '';
                this.showFooter     = false;
                this.showCloseBtn   = false;
                this.showSaveBtn    = false;
                this.data = null;
            };
        });
    
})(app);