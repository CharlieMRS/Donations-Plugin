$(function ($) {

    var var_form_79 = $("#var-form-79").get();
    var var_form_238 = $("#var-form-238").get();
    var $var_form_79 = $("#var-form-79");
    var $var_form_238 = $("#var-form-238");
    var $theCheckbox = $("input#subscribeDonation");
    var i = 0;

    var donation = $('.woocommerce-checkout-review-order-table .woocommerce-Price-amount.amount').first();
    var origVariation = donation.html();
    donation.find('span').remove();

    var variationPriceText = donation.text();
    var variationPrice = parseInt(donation.text());
    var nyp = false;
    if ($.inArray(variationPrice, [35,50,125,250]) === -1) {nyp = true;}

    donation.html(origVariation);

    if ( $theCheckbox.is(':checked') ){

        if (nyp) {
            $('#var-form-79 .select-buttons li:last-child a').addClass( "picked" ).text(variationPriceText);
            $('#var-form-238 .select-buttons li:last-child a').click();
            console.log('nyp is '+ nyp + ' because the value is '+variationPriceText);
        } else {
            $('.select-buttons li:nth-child(n+2) a').each(function() {
                if (parseInt($(this).text()) == parseInt(variationPrice) ) {
                    $(this).click();
                }
            });
            console.log('nyp is '+ nyp + 'but this evaluated anhyway');
        }

        $var_form_238.toggle();
    } else {

        if (nyp) {
            $('#var-form-238 .select-buttons li:last-child a').addClass( "picked" ).text(variationPriceText);
            $('#var-form-79 .select-buttons li:last-child a').click();
            console.log('nyp is '+ nyp + ' because the value is '+variationPriceText);
        } else{
            $('.select-buttons li:nth-child(n+2) a').each(function() {
                //console.log('this val is'+parseInt($(this).text()) + 'and parseint var is '+parseInt(variationPrice));
                if (parseInt($(this).text()) == parseInt(variationPrice) ) {
                    //console.log("we got a matcheroo");
                    $(this).click();
                }
            });
        }

        $var_form_79.toggle();
    }

    $theCheckbox.change(function() {
        $var_form_79.toggle();
        $var_form_238.toggle();
    });

    $("#var-form-79 #nyp").on("focusout", function() {
        if ($(this).val()){
            $('.select-buttons li:last-child a').addClass( "picked" ).text($(this).val());
            $("#var-form-238 #nyp").val($(this).val());
        }
    });
    $("#var-form-238 #nyp").on("focusout", function() {
        if ($(this).val()){
            $('.select-buttons li:last-child a').addClass( "picked" ).text($(this).val());
            $("#var-form-79 #nyp").val($(this).val());
        }
    });
});