$(function ($) {

    $('select#tier').select2OptionPicker();

    $("#subscribeDonationWrapper").prependTo(".woocommerce-variation-add-to-cart");

    $("#var-form-79 #subscribeDonation").change(function () {
        $("#var-form-238 #subscribeDonation").prop("checked", this.checked);
    });
    $("#var-form-238 #subscribeDonation").change(function () {
        $("#var-form-79 #subscribeDonation").prop("checked", this.checked);
    });

    $("#var-form-79 .select-buttons li").on('click', function (e) {
        if (!e.isTrigger) {

            var index = $('#var-form-79 .select-buttons li').index(this) + 1;
            $("#var-form-238 .select-buttons li:nth-child(" + index + ") a").click();

        }

    });

    $("#var-form-238 .select-buttons li").on('click', function (e) {
        if (!e.isTrigger) {
            var index = $('#var-form-238 .select-buttons li').index(this) + 1;
            $("#var-form-79 .select-buttons li:nth-child(" + index + ") a").click();

        }

    });

    //woo was calling it checkout
    var productName = $('.woocommerce-checkout-review-order-table .product-name');
    productName.text('Donation');

    //don't use bind here or infinite loop occurs..but .one doesn't work as needed either
    $(".woocommerce-checkout-review-order-table").one("DOMSubtreeModified", function () {
        productName.text('Donation');
        //console.log(productName);
    });

    $("#customer_details .col-1").removeClass();
    $("#customer_details .col-2").remove();
    $(".product_title.entry-title").replaceWith("<h3>Choose Gift Amount</h3>").css("display", "initial");

    $("#nyp").on("focusout", function () {
        $('.single_add_to_cart_button').show();
        $('.submit-reminder').show();
    });

    $(".select-buttons li").on('click', function (e) {
        if (!e.isTrigger) {
            $('.single_add_to_cart_button').show();
            $('.submit-reminder').show();
        }
    });
    $("#subscribeDonation").on('change', function (e) {
        if (!e.isTrigger) {

            $('.single_add_to_cart_button').show();
            $('.submit-reminder').show();
        }
    });

    function tgSetCookie(cname, cvalue, exdays) {
        var d = new Date();
        d.setTime(d.getTime() + (exdays * 24 * 60 * 60 * 1000));
        var expires = "expires=" + d.toUTCString();
        document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
    }

    function tgGetCookie(cname) {
        var name = cname + "=";
        var decodedCookie = decodeURIComponent(document.cookie);
        var ca = decodedCookie.split(';');
        for (var i = 0; i < ca.length; i++) {
            var c = ca[i];
            while (c.charAt(0) == ' ') {
                c = c.substring(1);
            }
            if (c.indexOf(name) == 0) {
                return c.substring(name.length, c.length);
            }
        }
        return "";
    }

    if (tgGetCookie('tallgrass_dolly') == 1) {
        $('#dyes').prop('checked', true)
        $('select#dolly').val(1)
    }

    $('select#dolly').on('change', function () {
        if (this.value == 1) $('#dyes').prop('checked', true)
        else $('#dno').prop('checked', true)
        tgSetCookie('tallgrass_dolly', this.value, 1)
    });

    $('#dollyRadioWrapper input').change(function () {
        $('select#dolly').val(this.value)
        tgSetCookie('tallgrass_dolly', this.value, 1)

    })
});