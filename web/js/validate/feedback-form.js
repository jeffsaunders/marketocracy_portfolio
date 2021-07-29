/*					// --- Flip Layers
						function show(id, focus) {
							document.getElementById(id).style.visibility = "visible";
							document.getElementById(id).style.display = "block";
							if (focus){
								document.getElementById(focus).focus();
							}
						}
						function hide(id, focus) {
							document.getElementById(id).style.visibility = "hidden";
							document.getElementById(id).style.display = "none";
							if (focus){
								document.getElementById(focus).focus();
							}
						}
						function toggle(id, focus) {
							if (document.getElementById(id).style.visibility == "hidden"){
								document.getElementById(id).style.visibility = "visible";
								document.getElementById(id).style.display = "block";
							}else{
								document.getElementById(id).style.visibility = "hidden";
								document.getElementById(id).style.display = "none";
							}
							if (focus){
								document.getElementById(focus).focus();
							}
						}

						// --- Only accept digits (numbers)
						function onlyNumbers(e,o){
						//alert(o.value);
							var keynum
							var keychar
							var ltrcheck
							var crcheck
							if(window.event){ // IE
								keynum = e.keyCode
							}else if(e.which){ // Chrome/Firefox/Opera/Safari/Others
								keynum = e.which
							}
							//alert(keynum);
						//	if (keynum == 08 || keynum == 46 || keynum == 13 || !keynum) return true; // Backspace, decimal point, enter, or navigation (arrow) key
							if (keynum == 08 || keynum == 13 || !keynum) return true; // Backspace, enter, or navigation (arrow) key
							keychar = String.fromCharCode(keynum)
							//alert(keychar);
							ltrcheck = /\D/ //Regular expression for NON-digit (letter)
							crcheck = /\cM/ //Regular expression ctrl-M (enter)
							if (crcheck.test(keychar)) o.blur();
							return !ltrcheck.test(keychar) //Return true if not a letter
						}

						// --- Format a phone number as (NNN) NNN-NNNN
						function formatPhone(o){
//							var errorBG =	"background:#FF0000; width:250px";
//							var normalBG =	"background:#FDFDFD; width:250px";
							var oValue =	o.value;
							// remove all "(", ")", "-", and spaces
							oValue = oValue.replace(/\(/g, '');
							oValue = oValue.replace(/\)/g, '');
							oValue = oValue.replace(/\-/g, '');
							oValue = oValue.replace(/\s+/g, '');
							// remove all alpha characters
							oValue = oValue.replace(/[a-zA-Z]+/g,'');
							// stop checking and let it pass if it's empty - form validator will catch it if it can't be blank
							if (oValue.length < 1){
								return true;
							// make sure it's 10 digits if there are any
							}else if (oValue.length < 10){
								o.focus();
//								o.setAttribute("style", errorBG);
								alert('The phone number needs at least 10 digits');
//								o.setAttribute("style", normalBG);
								// Must set a timeout or the prior element's destruction will clobber the focus() call
								// and leave the cursor in the next field
								setTimeout(function() { document.getElementById(o.id).focus(); }, 10);
								return false;
							}
							// make sure they are all numbers
						// no need - replaced with alpha removal above
						//	if (isNaN(oValue)){
						//		o.setAttribute("style", errorBG);
						//		alert('The phone number must be numeric');
						//		o.setAttribute("style", normalBG);
						//		// Must set a timeout or the prior element's destruction will clobber the focus() call
						//		// and leave the cursor in the next field
						//		setTimeout(function() { document.getElementById(o.id).focus(); }, 10);
						//		return false;
						//	}
							// ok, seems good
						//	if (oValue.length < 10){
						//		o.value = o.value;
						//	}else if (oValue.length == 10){
							if (oValue.length == 10){
								o.value = '(' + (oValue.substr(0, 3) + ') ' + oValue.substr(3, 3) + '-' + oValue.substr(6, 4));
							}else{
								o.value = '(' + (oValue.substr(0, 3) + ') ' + oValue.substr(3, 3) + '-' + oValue.substr(6, 4) + ' ext ' + oValue.substr(10));
							}
							return true;
						}

						// --- Trim string
						function trim(str){
							if(!str || typeof str != 'string') return null;
						    return str.replace(/^[\s]+/,'').replace(/[\s]+$/,'').replace(/[\s]{2,}/,' ');
						}

						// --- form validation
						function validate(theForm){
							// Global Declarations
//							var errorBG =	"#FF0000";
//							var warningBG =	"#FFFF00";
//							var normalBG =	"#FDFDFD";
//							var transBG =	"transparent";

							// CONTACT INFO---

							// First Name
							if (theForm.first_name.value == ""){
//								window.location.hash = "contact_info_question";
								theForm.first_name.focus();
//								theForm.first_name.style.background = errorBG;
								alert("Please Enter Your First Name");
//								theForm.first_name.style.background = normalBG;
								return false;
							}
							// Last Name
							if (theForm.last_name.value == ""){
//  								window.location.hash = "contact_info_question";
								theForm.last_name.focus();
//								theForm.last_name.style.background = errorBG;
								alert("Please Enter Your Last Name");
//								theForm.last_name.style.background = normalBG;
								return false;
							}
							// Email
							if (theForm.email.value == ""){
//								window.location.hash = "contact_info_question";
								theForm.email.focus();
//								theForm.email.style.background = errorBG;
								alert("Please Enter Your Email Address");
//								theForm.email.style.background = normalBG;
								return false;
							}
							// Email - check for "@"
							if (theForm.email.value.indexOf("@") == -1) {
//								window.location.hash = "contact_info_question";
								theForm.email.focus();
//								theForm.email.style.background = errorBG;
								alert('Your Email Must Contain an "@"');
//								theForm.email.style.background = normalBG;
								return false;
							}
							// Email - check for "@" as first char.
							if (theForm.email.value.indexOf("@") == 0) {
//  								window.location.hash = "contact_info_question";
								theForm.email.focus();
//								theForm.email.style.background = errorBG;
								alert('Your Email Cannot Start With a "@"');
//								theForm.email.style.background = normalBG;
								return false;
							}
							// Email - check for anything after "@"
							if (theForm.email.value.length == (theForm.email.value.indexOf("@")+1)) {
//								window.location.hash = "contact_info_question";
								theForm.email.focus();
//								theForm.email.style.background = errorBG;
								alert('Your Email Must Contain a Domain Name After the "@"');
//								theForm.email.style.background = normalBG;
								return false;
							}
							// Email - check for multiple "@"
							if (theForm.email.value.substring((theForm.email.value.indexOf("@")+1),theForm.email.value.length).indexOf("@") != -1) {
//								window.location.hash = "contact_info_question";
								theForm.email.focus();
//								theForm.email.style.background = errorBG;
								alert('Your Email Must Contain Only One "@"');
//								theForm.email.style.background = normalBG;
								return false;
							}
							// Email - check for "."
							if (theForm.email.value.indexOf(".") == -1) {
//								window.location.hash = "contact_info_question";
								theForm.email.focus();
//								theForm.email.style.background = errorBG;
								alert('Your Email Must Contain a "."');
//								theForm.email.style.background = normalBG;
								return false;
							}
							// Email - check for "." as first char.
							if (theForm.email.value.indexOf(".") == 0) {
//								window.location.hash = "contact_info_question";
								theForm.email.focus();
//								theForm.email.style.background = errorBG;
								alert('Your Email Cannot Start With a "."');
//								theForm.email.style.background = normalBG;
								return false;
							}
							// Email - check for ".."
							if (theForm.email.value.indexOf("..") != -1) {
//								window.location.hash = "contact_info_question";
								theForm.email.focus();
//								theForm.email.style.background = errorBG;
								alert('Your Email Must Not Contain Adjacent Dots ("..")');
//								theForm.email.style.background = normalBG;
								return false;
							}
							// Email - check for "." adjacent to "@"
							if (theForm.email.value.indexOf(".@") != -1 || theForm.email.value.indexOf("@.") != -1) {
//								window.location.hash = "contact_info_question";
								theForm.email.focus();
//								theForm.email.style.background = errorBG;
								alert('Your Email Must Not Contain Adjacent Dots and Ats (".@" or "@.") ');
//								theForm.email.style.background = normalBG;
								return false;
							}
							// Email - check for TLD
							if (theForm.email.value.length == (theForm.email.value.indexOf(".")+1)) {
//								window.location.hash = "contact_info_question";
								theForm.email.focus();
//								theForm.email.style.background = errorBG;
								alert('Your Email Must Contain a Top Level Domain (i.e. ".com") After the "."');
//								theForm.email.style.background = normalBG;
								return false;
							}
							// Email - check for TLD at least 2 char.
							var domain = theForm.email.value.substring((theForm.email.value.indexOf("@")+1),theForm.email.value.length);
							if (domain.length - (domain.indexOf(".")+1) < 2) {
//								window.location.hash = "contact_info_question";
								theForm.email.focus();
//								theForm.email.style.background = errorBG;
								alert('The Top Level Domain (i.e. ".com") Must Contain At Least 2 Characters');
//								theForm.email.style.background = normalBG;
								return false;
							}
							// Email - check for "_" in TLD
							if (theForm.email.value.indexOf("_") > theForm.email.value.indexOf("@")) {
//								window.location.hash = "contact_info_question";
								theForm.email.focus();
//								theForm.email.style.background = errorBG;
								alert('The Top Level Domain (i.e. ".com") Cannot Contain Any Underscores ("_")');
//								theForm.email.style.background = normalBG;
								return false;
							}
							// Email - check for spaces
							if (theForm.email.value.indexOf(" ") != -1) {
//								window.location.hash = "contact_info_question";
								theForm.email.focus();
//								theForm.email.style.background = errorBG;
								alert('Your Email Must Not Contain Any Spaces (" ")');
//								theForm.email.style.background = normalBG;
								return false;
							}
							// Email - check for illegal char.
							var email_regex = /^\w(?:\w|-|\.(?!\.|@))*@\w(?:\w|-|\.(?!\.))*\.\w{2,3}/;
							if (email_regex.test(theForm.email.value) == false) {
//								window.location.hash = "contact_info_question";
								theForm.email.focus();
//								theForm.email.style.background = errorBG;
								alert('Your Email Must Not Contain Any Of These Characters: ( )<>,;:\"`\'{ }/~#$%^&*+=[ ]|? ');
//								theForm.email.style.background = normalBG;
								return false;
							}
							// Phone
//							if (theForm.phone.value == ""){
////								window.location.hash = "contact_info_question";
//								theForm.phone.focus();
////								theForm.phone.style.background = errorBG;
//								alert("Please Enter Your Primary Phone Number");
////								theForm.phone.style.background = normalBG;
//								return false;
//							}
						// Format validation handled by formatPhone() function.
						//	var phone1_regex = /^\([1-9]\d{2}\)\s?\d{3}\-\d{4}$/;  // (xxx)xxx-xxxx
						//	var phone2_regex = /^[1-9]\d{2}\-\d{3}\-\d{4}$/;  // xxx-xxx-xxxx
						//	if (phone1_regex.test(theForm.phone.value) == false && phone2_regex.test(theForm.phone.value) == false){
						//		window.location.hash = "contact_info_question";
						//		theForm.phone.focus();
						//		theForm.phone.style.background = errorBG;
						//		alert('Please Enter a Valid Phone Number as (NNN)NNN-NNNN or NNN-NNN-NNNN');
						//		theForm.phone.style.background = normalBG;
						//		return false;
						//	}
							// All's well - do it.
						//	return false; // Testing
						//	theForm.submit();
						//	alert('Thank You for your interest in Marketocracy.');
							return true;
						}

*/