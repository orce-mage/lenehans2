define([
    "jquery",
    "Magento_Ui/js/modal/modal"
], function ($) {
    'use strict';

    $.widget('mage.amLocator', {
        options: {},
        markers: [],

        _create: function () {
            var self = this;

            $('#location_lat').keyup(function() {
                document.getElementById("location_lat").value = document.getElementById("location_lat").value.replace(",",".");
                self.displayByLatLng();
            });

            self.displayByLatLng();

            $('#amlocator_fill').click(function() {
                self.display();
            });
            
            $('#location_lng').keyup(function() {
                document.getElementById("location_lng").value = document.getElementById("location_lng").value.replace(",",".");
                self.displayByLatLng();
            });
        },

        displayByLatLng: function() {
            document.getElementById("map-canvas").style.display = "block";
            var mapOptions = {zoom: 4},
                marker;

            if (!this.map) {
                this.map = new google.maps.Map(document.getElementById('map-canvas'), mapOptions);
            }
            var lat = $('input[name="lat"]').val();
            var lng = $('input[name="lng"]').val();
            if ($('.marker-uploader-preview').find('.preview-image')) {
                var markerImage = $('.marker-uploader-preview').find('.preview-image').attr('src');
            }
            var myLatlng = new google.maps.LatLng(lat, lng),
            marker = new google.maps.Marker({
                map: this.map,
                position: myLatlng,
                icon: markerImage ? markerImage : ''
            });
            this.deleteMarkers();
            this.markers.push(marker);
            this.map.setCenter(myLatlng);

            return true;
        },

        deleteMarkers: function() {
            for (var i = 0; i < this.markers.length; i++) {
                this.markers[i].setMap();
            }
            this.markers = [];
        },

        display: function() {
            var country = $('select[name="country"]').val(),
                city = $('input[name="city"]').val(),
                zip = $('input[name="zip"]').val(),
                address = $('input[name="address"]').val(),
                geocoder = new google.maps.Geocoder(),
                self = this,
                mapOptions = {zoom: 4};

            address = country +','+ city+','+zip+','+address;
            document.getElementById("map-canvas").style.display = "block";

            if (!this.map) this.map = new google.maps.Map(document.getElementById('map-canvas'), mapOptions);

            geocoder.geocode( { 'address': address}, function(results, status) {
                if (status == google.maps.GeocoderStatus.OK) {
                    if ($('.marker-uploader-preview').find('.preview-image')) {
                        var markerImage = $('.marker-uploader-preview').find('.preview-image').attr('src');
                    }
                    self.map.setCenter(results[0].geometry.location);
                    $('input[name="lat"]').val(results[0].geometry.location.lat()).trigger('change');
                    $('input[name="lng"]').val(results[0].geometry.location.lng()).trigger('change');

                    var marker = new google.maps.Marker({
                        map: self.map,
                        position: results[0].geometry.location,
                        icon: markerImage
                    });

                    self.deleteMarkers();
                    self.markers.push(marker);
                }
            });
        }
    });
    return $.mage.amLocator;
});
