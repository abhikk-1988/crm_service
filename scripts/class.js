/**
 * Js learning file 
 */

// window objet is the central and global object 


function addition (number){
	
	var private_number = 15;
	
	//  closure 
	var add = function (value){
		
		if(value){
			return (number + private_number + value);
		}else{
			return (number + private_number)
		}
	};
	
	return add;
};

var add_function = addition (10);
console.log(add_function(20));
