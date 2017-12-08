/**
 * Custom Angular JS filter 
 * @author: Abhishek Agarwal
 * @version: 1.0
 */

var app = app || {};

(function (app) {

    /**
     * Pluralize string
     * @param {string} 
     */
    app.filter('pluralizeString', function ($filter, dateUtility) {

        return function (string) {

            var string_arr = string.split(' ');
            var pluralize_string = new Array();

            for (var i = 0; i < string_arr.length; i++) {

                var caps_first_char = $filter('uppercase')(string_arr[i][0]);
                for (var j = 1; j < string_arr[i].length; j++) {
                    caps_first_char += string_arr[i][j];
                }
                pluralize_string.push(caps_first_char);
            }

            return(pluralize_string.join(' '));
        };

    });

    /**
     * Trim space from string 
     * @param {string} input string from which space will be removed
     * @param {string} join_by_char string to replace space from input string 
     */
    app.filter('trimSpace', function () {

        return function (input, join_by_char) {

            var string_arr = input.split(' ');

            if (join_by_char !== null) {
                return string_arr.join(join_by_char);
            } else {
                return string_arr.join('');
            }
        };
    });

    /**
     * Filter to remove forward slash from a string and replace it with any character 
     */
    app.filter('removeForwardSlash', function () {
        return function (string, replace_char) {
            return string.replace(/\//g, replace_char);
        };
    });

    /**
     * Filter to get the first letter of a string 
     */
    app.filter('extractFirstLetter', function () {
        return function (string) {
            return string[0];
        };

    });

    /**
     * Function to paginate client side data 
     * @param {array}  <collection of data>
     * @param {number} start <offset>
     */

    app.filter('startFrom', function () {
        return function (data, start) {
            start = +start; //parse to int
            return data.slice(start);

        };
    });

    app.filter('serializeObj', function () {

        return function (obj) {
            var result = [];
            for (var property in obj)
                result.push(encodeURIComponent(property) + "=" + encodeURIComponent(obj[property]));

            return result.join("&");
        };

    });
    
    app.filter('employeeName', function (httpService, baseUrl) {

        return function (emp_id) {

            var employee_name = '';

            httpService.makeRequest({
                url: baseUrl + 'apis/helper.php?method=getEmployeeNameById&params=employee_id:' + emp_id,
                method: 'GET'
            }).then(function (data) {
                console.log(data.data);
                employee_name = data.data;
            });

            return employee_name;
        };
    });

    app.filter('getMonthName', function (dateUtility) {

        return function (month, type) {

            if (type === '') {
                type = 'short';
            }

            if (month === null) {
                return 'NA';
            }

            return dateUtility.get_month_name_from_number(parseInt(month), type);
        };
    });

    app.filter('split_string_by_char', function ($filter){
        
        return function(str,char) {
            
            var spliting_char = '';
            
            if(typeof char == 'undefined'){
                spliting_char = ' ';
            }else{
                spliting_char = char;
            }
            
            var strArr = str.split(char);
            
            var initialCapsStrArr = [];
            
            for(var i in strArr)
            {
                initialCapsStrArr.push($filter('pluralizeString')(strArr[i]));
            }
            
            return (initialCapsStrArr.join(' '));
            
        };
    });

})(app);