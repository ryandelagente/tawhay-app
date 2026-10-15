$(function () {
    "use strict";
    
    // User Checkbox Functionality
    if ($(".checkbox_user").length == $(".checkbox_user:checked").length) {
        $("#checkbox_userAll").prop("checked", true);
    } else {
        $("#checkbox_userAll").removeAttr("checked");
    }
    $(document).on('change', '#checkbox_userAll', function(){
        let checked = $(this).is(':checked');
        if(checked){
            $(".checkbox_user").each(function(){
                $(this).prop("checked",true);
            });
            $(".checkbox_user_p").prop("checked", true);
        }else{
            $(".checkbox_user").each(function(){
                $(this).prop("checked",false);
            });
            $(".checkbox_user_p").prop("checked", false);
        }
    });
    $(document).on('click', '.checkbox_user', function(){
        if($(".checkbox_user").length == $(".checkbox_user:checked").length) {
            $("#checkbox_userAll").prop("checked", true);

        } else {
            $("#checkbox_userAll").prop("checked", false);
        }
    });
    
    // ZATCA Integration
    if ($('#zatca_enable_checkbox').length > 0) {
        
        // Toggle ZATCA fields based on dropdown
        var toggleZatcaFields = function() {
            var isEnabled = $('#zatca_enable_checkbox').val() == '1';
            
            if (isEnabled) {
                $('#zatca_fields_container').slideDown(300);
                checkConnectButtonVisibility();
                // Show reconnect button if it exists
                if ($('#reconnect_zatca_btn').length > 0) {
                    $('#reconnect_zatca_btn').show();
                }
            } else {
                $('#zatca_fields_container').slideUp(300);
                $('#connect_zatca_btn').hide();
                // Hide reconnect button
                if ($('#reconnect_zatca_btn').length > 0) {
                    $('#reconnect_zatca_btn').hide();
                }
            }
        };
        
        // Check if all ZATCA fields are filled to show Connect button
        var checkConnectButtonVisibility = function() {
            if ($('#zatca_enable_checkbox').val() != '1') {
                $('#connect_zatca_btn').hide();
                return;
            }
            
            // Check if already connected using data attribute
            var zatcaConnected = $('#zatca_enable_checkbox').data('zatca-connected');
            if (zatcaConnected === '1' || zatcaConnected === 1) {
                // Already connected, hide button
                $('#connect_zatca_btn').hide();
                return;
            }
            
            var legalNameEn = $.trim($('#zatca_legal_name_en').val() || '');
            var legalNameAr = $.trim($('#zatca_legal_name_ar').val() || '');
            var vatNumber = $.trim($('#zatca_vat_number').val() || '');
            var crNumber = $.trim($('#zatca_cr_number').val() || '');
            var postalCode = $.trim($('#zatca_postal_code').val() || '');
            var address = $.trim($('#zatca_address').val() || '');
            
            if (legalNameEn && legalNameAr && vatNumber && crNumber && postalCode && address) {
                $('#connect_zatca_btn').show();
            } else {
                $('#connect_zatca_btn').hide();
            }
        };
        
        // On dropdown change
        $(document).on('change', '#zatca_enable_checkbox', function() {
            toggleZatcaFields();
        });
        
        // On field change, check button visibility
        $(document).on('input change', '.zatca-field', function() {
            checkConnectButtonVisibility();
        });
        
        // Initial check on page load
        toggleZatcaFields();
        
    }
    
});