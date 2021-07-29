/*
TRADE WIZARD - JAVASCRIPT FILE

SUPPORTING FILES:
_______________________________________________________________

AJAX 			- process/ajax/trade-wizard-processes.php
JAVASCRIPT 		- THIS FILE js/trade-wizard.js
PHP JAVASCRIPT	- includes/scripts/trade-wizard.js.php
HTML 			- includes/pages/trade-wizard.php
_______________________________________________________________

*/

var TradeWizard = function () {


    return {
        //main function to initiate the module
        init: function () {
            if (!jQuery().bootstrapWizard) {
                return;
            }

            /*function format(state) {
                if (!state.id) return state.text; // optgroup
                return "<img class='flag' src='images/flags/" + state.id.toLowerCase() + ".png'/>&nbsp;&nbsp;" + state.text;
            }

            $("#country_list").select2({
                placeholder: "Select",
                allowClear: true,
                formatResult: format,
                formatSelection: format,
                escapeMarkup: function (m) {
                    return m;
                }
            });*/

            var form = $('#submit_form');
            var error = $('.alert-danger', form);
            var success = $('.alert-success', form);
			
			
			
			
            form.validate({
                doNotHideMessage: true, //this option enables to show the error/success messages on tab switch.
                errorElement: 'span', //default input error message container
                errorClass: 'help-block', // default input error message class
                focusInvalid: false, // do not focus the last invalid input
                
				
				
				rules: {
					symbols: {
						required: true	
					},
					order_type: {
						required: true	
					}
					
                    /*//account
                    username: {
                        minlength: 5,
                        required: true
                    },
                    password: {
                        minlength: 5,
                        required: true
                    },
                    rpassword: {
                        minlength: 5,
                        required: true,
                        equalTo: "#submit_form_password"
                    },
                    //profile
                    fullname: {
                        required: true
                    },
                    email: {
                        required: true,
                        email: true
                    },
                    phone: {
                        required: true
                    },
                    gender: {
                        required: true
                    },
                    address: {
                        required: true
                    },
                    city: {
                        required: true
                    },
                    country: {
                        required: true
                    },
                    //payment
                    card_name: {
                        required: true
                    },
                    card_number: {
                        minlength: 16,
                        maxlength: 16,
                        required: true
                    },
                    card_cvc: {
                        digits: true,
                        required: true,
                        minlength: 3,
                        maxlength: 4
                    },
                    card_expiry_date: {
                        required: true
                    },
                    'payment[]': {
                        required: true,
                        minlength: 1
                    }*/
                },

                messages: { // custom messages for radio buttons and checkboxes
                    'payment[]': {
                        required: "Please select at least one option",
                        minlength: jQuery.format("Please select at least one option")
                    },
					'order_type': {
						required: "Please select an Order Type."	
					},
					'validate': {
						required: "Please fix the errors above."	
					}
                },

                errorPlacement: function (error, element) { // render error placement for each input type
                    if (element.attr("name") == "order_type") { // for uniform radio buttons, insert the after the given container
                        error.insertAfter("#form_order_type_error");
                    } else if (element.attr("name") == "payment[]") { // for uniform radio buttons, insert the after the given container
                        error.insertAfter("#form_payment_error");
                    } else {
                        error.insertAfter(element); // for other inputs, just perform default behavior
                    }
                },

                invalidHandler: function (event, validator) { //display error alert on form submit   
                    success.hide();
                    error.show();
                    App.scrollTo(error, -200);
                },

                highlight: function (element) { // hightlight error inputs
                    $(element)
                        .closest('.form-group').removeClass('has-success').addClass('has-error'); // set error class to the control group
                },

                unhighlight: function (element) { // revert the change done by hightlight
                    $(element)
                        .closest('.form-group').removeClass('has-error'); // set error class to the control group
                },

                success: function (label) {
                    if (label.attr("for") == "order-type" /*|| label.attr("for") == "payment[]"*/) { // for checkboxes and radio buttons, no need to show OK icon
                        label
                            .closest('.form-group').removeClass('has-error').addClass('has-success');
                        label.remove(); // remove error label here
                    } else { // display success icon for other inputs
                        label
                            .addClass('valid') // mark the current input as valid and display OK icon
                        .closest('.form-group').removeClass('has-error').addClass('has-success'); // set success class to the control group
                    }
                },

                submitHandler: function (form) {
                    success.show();
                    error.hide();
                    //add here some ajax code to submit your form or just call form.submit() if you want to submit the form without ajax
                }

            });

            var displayConfirm = function() {
                $('#tab4 .form-control-static', form).each(function(){
                    var input = $('[name="'+$(this).attr("data-display")+'"]', form);
                    if (input.is(":text") || input.is("textarea")) {
                        $(this).html(input.val());
                    } else if (input.is("select")) {
                        $(this).html(input.find('option:selected').text());
                    } else if (input.is(":radio") && input.is(":checked")) {
                        $(this).html(input.attr("data-title"));
                    } else if ($(this).attr("data-display") == 'payment') {
                        var payment = [];
                        $('[name="payment[]"]').each(function(){
                            payment.push($(this).attr('data-title'));
                        });
                        $(this).html(payment.join("<br>"));
                    }
                });
            }
			
			
            var handleTitle = function(tab, navigation, index) {
				//alert(tab);
                var total = navigation.find('li').length;
                var current = index + 1;
				
				//alert(index);
				$('#change_button').html('<a onclick="previousButton(\''+index+'\');" href="javascript:;" class="btn btn-default button-previous" ><i class="m-icon-swapleft"></i> Back</a> <a onclick="nextButton(\''+index+'\');" href="javascript:;" class="btn btn-info button-next">Continue <i class="m-icon-swapright m-icon-white"></i></a> <a href="javascript:;" onclick="processTrades(\''+index+'\');" class="btn btn-success button-submit">Submit Trade<i class="m-icon-swapright m-icon-white"></i></a>');
				
                // set wizard title
                $('.step-title', $('#trade_wizard')).text('Step ' + (index + 1) + ' of ' + total);
                // set done steps
                jQuery('li', $('#trade_wizard')).removeClass("done");
                var li_list = navigation.find('li');
                for (var i = 0; i < index; i++) {
                    jQuery(li_list[i]).addClass("done");
                }
				
                if (current == 1) {
                    $('#trade_wizard').find('.button-previous').hide();
                } else {
                    $('#trade_wizard').find('.button-previous').show();
					
                }

                if (current >= total) {
                    $('#trade_wizard').find('.button-next').hide();
                    $('#trade_wizard').find('.button-submit').show();
                    displayConfirm();
                } else {
                    $('#trade_wizard').find('.button-next').show();
                    $('#trade_wizard').find('.button-submit').hide();
                }
                App.scrollTo($('.page-title'));
            }

            // default form wizard
            $('#trade_wizard').bootstrapWizard({
                'nextSelector': '.button-next',
                'previousSelector': '.button-previous',
                onTabClick: function (tab, navigation, index, clickedIndex) {
                    success.hide();
                    error.hide();
                    if (form.valid() == false) {
                        return false;
                    }
                    handleTitle(tab, navigation, clickedIndex);
                },
                onNext: function (tab, navigation, index) {
                    success.hide();
                    error.hide();

                    if (form.valid() == false) {
                        return false;
                    }
					
                    handleTitle(tab, navigation, index);
                },
                onPrevious: function (tab, navigation, index) {
                    success.hide();
                    error.hide();

                    handleTitle(tab, navigation, index);
                },
                onTabShow: function (tab, navigation, index) {
                    var total = navigation.find('li').length;
                    var current = index + 1;
                    var $percent = (current / total) * 100;
                    $('#trade_wizard').find('.progress-bar').css({
                        width: $percent + '%'
                    });
					
                }
            });

            $('#trade_wizard').find('.button-previous').hide();
            $('#trade_wizard .button-submit').click(function () {
                alert('Finished! Hope you like it :)');
            }).hide();
        }

    };

}();


var TradeTable1 = function () {

    var initTable1 = function() {

        /* Formatting function for row details */
        function fnFormatDetails ( oTable, nTr )
        {
            var aData = oTable.fnGetData( nTr );
            var sOut = '<table>';
            sOut += '<tr><td>Platform(s):</td><td>'+aData[2]+'</td></tr>';
            sOut += '<tr><td>Engine version:</td><td>'+aData[3]+'</td></tr>';
            sOut += '<tr><td>CSS grade:</td><td>'+aData[4]+'</td></tr>';
            sOut += '<tr><td>Others:</td><td>Could provide a link here</td></tr>';
            sOut += '</table>';
             
            return sOut;
        }

        /*
         * Insert a 'details' column to the table
         */
        var nCloneTh = document.createElement( 'th' );
        var nCloneTd = document.createElement( 'td' );
        nCloneTd.innerHTML = '<span class="row-details row-details-close"></span>';
         
        $('#sample_1 thead tr').each( function () {
            this.insertBefore( nCloneTh, this.childNodes[0] );
        } );
         
        $('#sample_1 tbody tr').each( function () {
            this.insertBefore(  nCloneTd.cloneNode( true ), this.childNodes[0] );
        } );
         
        /*
         * Initialize DataTables, with no sorting on the 'details' column
         */
        var oTable = $('#sample_1').dataTable( {
            "aoColumnDefs": [
                {"bSortable": false, "aTargets": [ 0 ] }
            ],
            "aaSorting": [[1, 'asc']],
             "aLengthMenu": [
                [5, 15, 20, -1],
                [5, 15, 20, "All"] // change per page values here
            ],
            // set the initial value
            "iDisplayLength": 10,
        });

        jQuery('#sample_1_wrapper .dataTables_filter input').addClass("form-control input-small"); // modify table search input
        jQuery('#sample_1_wrapper .dataTables_length select').addClass("form-control input-small"); // modify table per page dropdown
        jQuery('#sample_1_wrapper .dataTables_length select').select2(); // initialize select2 dropdown
         
        /* Add event listener for opening and closing details
         * Note that the indicator for showing which row is open is not controlled by DataTables,
         * rather it is done here
         */
        $('#sample_1').on('click', ' tbody td .row-details', function () {
            var nTr = $(this).parents('tr')[0];
            if ( oTable.fnIsOpen(nTr) )
            {
                /* This row is already open - close it */
                $(this).addClass("row-details-close").removeClass("row-details-open");
                oTable.fnClose( nTr );
            }
            else
            {
                /* Open this row */                
                $(this).addClass("row-details-open").removeClass("row-details-close");
                oTable.fnOpen( nTr, fnFormatDetails(oTable, nTr), 'details' );
            }
        });
    }

     var initTable2 = function() {
        var oTable = $('#sample_2').dataTable( {           
            "aoColumnDefs": [
                { "aTargets": [ 0 ] }
            ],
            "aaSorting": [[1, 'asc']],
             "aLengthMenu": [
                [5, 15, 20, -1],
                [5, 15, 20, "All"] // change per page values here
            ],
            // set the initial value
            "iDisplayLength": 10,
        });

        //jQuery('#sample_2_wrapper .dataTables_filter input').addClass("form-control input-small"); // modify table search input
        //jQuery('#sample_2_wrapper .dataTables_length select').addClass("form-control input-small"); // modify table per page dropdown
        //jQuery('#sample_2_wrapper .dataTables_length select').select2(); // initialize select2 dropdown
		jQuery('#sample_2_wrapper .dataTables_filter').css('display', 'none');
		jQuery('#sample_2_wrapper .dataTables_length').css('display', 'none');
		
        $('#sample_2_column_toggler input[type="checkbox"]').change(function(){
            /* Get the DataTables object again - this is not a recreation, just a get of the object */
            var iCol = parseInt($(this).attr("data-column"));
            var bVis = oTable.fnSettings().aoColumns[iCol].bVisible;
            oTable.fnSetColumnVis(iCol, (bVis ? false : true));
        });
    }

    return {

        //main function to initiate the module
        init: function () {
            
            if (!jQuery().dataTable) {
                return;
            }

            initTable1();
            initTable2();
        }

    };

}();