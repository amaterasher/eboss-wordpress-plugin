function region_dropdown(country_id, target) {
    jQuery.ajax({
        type: "GET",
        dataType: 'json',
        url: myAjax.ajaxurl,
        data: {action: "v3_regions", country_id: country_id},
        success: function (data) {
            jQuery(target).empty();
            jQuery(target).append(jQuery('<option>', {
                value: "",
                text: "--All--"
            }));

            jQuery.each(data, function (key, region) {
                jQuery(target).append(jQuery('<option>', {
                    value: region.id,
                    text: region.name
                }));
            });
        }
    });
}

jQuery(document).ready(function () {

    jQuery('.eboss-v3-form').find('select.eboss-v3-country').on("change",function () {
        var countryId = jQuery(this).val();
        region_dropdown(countryId, 'select.eboss-v3-region');
    });

});