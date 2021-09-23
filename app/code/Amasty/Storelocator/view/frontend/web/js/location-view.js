define([
    'jquery',
    'mage/url',
    'Magento_Ui/js/modal/modal',
    'mage/translate',
    'Amasty_Storelocator/vendor/fancybox/jquery.fancybox.min',
    'Amasty_Storelocator/js/validate-review',
    'Amasty_Base/vendor/slick/slick.min',
    'Magento_Review/js/error-placement',
    'domReady!',
    'mage/loader'
], function ($, urlBuilder) {

    $.widget('mage.amLocationPage', {
        options: {},
        mapSelector: '[data-amlocator-js="location-map"]',
        directionsSelector: '[data-amlocator-js="directions"]',
        originPointSelector: '[data-amlocator-js="origin-point"]',
        destinationPointSelector: '[data-amlocator-js="destination-point"]',
        reviewMessageSelector: '[data-amlocator-js="review-message"]',
        toggleReviewSelector: '[data-amlocator-js="toggle-review"]',
        reviewPopupSelector: '[data-amlocator-js="review-popup"]',
        directionsService: new google.maps.DirectionsService(),
        directionsDisplay: new google.maps.DirectionsRenderer(),
        panoramaSelector: '[data-amlocator-js="locator-panorama"]',
        panoramaService: new google.maps.StreetViewService(),
        reviewFormSelector: '#amlocator-review-form',
        reviewControllerUrl: 'amlocator/location/savereview',

        _create: function () {
            var self = this;

            if ($(window).width() <= 768) {
                $('[data-amlocator-js="route-creator"]').before($(this.mapSelector));
                $(this.directionsSelector).after($('[data-amlocator-js="location-attributes"]'));
            }

            this.initializeMap();
            this.initializeRoute();
            this.initializeGallery();
            this.initReviewSubmit();

            $(self.reviewMessageSelector).each(function () {
                if ($(this)[0].clientHeight == $(this)[0].scrollHeight) {
                    $(this).siblings('.amlocator-footer').find(self.toggleReviewSelector).hide();
                }
            });

            $(self.toggleReviewSelector).on('click', function () {
                var reviewMessage = $(this).parents('[data-amlocator-js="location-review"]').find(self.reviewMessageSelector);

                reviewMessage.toggleClass('-collapsed');
                if (reviewMessage.is('.-collapsed')) {
                    $(this).text($.mage.__('See full review'));
                } else {
                    $(this).text($.mage.__('Collapse'));
                }
            });

            $('[data-amlocator-js="collapse-trigger"]').on('click', function (event) {
                $(this).siblings('[data-amlocator-js="collapse-content"]').slideToggle().toggleClass('-collapsed');
                $(this).find('[data-amlocator-js="collapse-indicator"]').toggleClass('-down');
                event.stopPropagation();
            });

            $('[data-amlocator-js="write-review"]').on('click', function () {
                $(self.reviewPopupSelector).fadeIn();
            });

            $(self.reviewPopupSelector).on('click', function (e) {
                var target = $(e.target);

                if (target.hasClass('amlocator-popup-overlay') || target.hasClass('amlocator-close')) {
                    $(self.reviewPopupSelector).fadeOut();
                }
            });

            $(self.panoramaSelector).on('click', function () {
                self.initPanorama();
            });
        },

        initReviewSubmit: function () {
            var self = this,
                reviewForm = $(this.reviewFormSelector);

            if (reviewForm.length) {
                reviewForm.submit(function (e) {

                    if (reviewForm.valid()) {
                        e.preventDefault();
                        self.sendReview(reviewForm);
                    }
                });
            }
        },

        sendReview: function (form) {
            var self = this,
                formData = form.serializeArray(),
                url = urlBuilder.build(this.reviewControllerUrl);

            $.ajax({
                showLoader: true,
                data: formData,
                url: url,
                method: "POST"
            }).done(function () {
                $(self.reviewPopupSelector).fadeOut();
                form[0].reset();
            });
        },

        initializeMap: function () {
            var self = this,
                mapOptions = {
                    zoom: 9,
                    center: self.options.locationData,
                    mapTypeId: google.maps.MapTypeId.ROADMAP
            },
                map = new google.maps.Map($(this.mapSelector)[0], mapOptions),
                locationMarker = new google.maps.Marker({
                    position: new google.maps.LatLng(self.options.locationData.lat, self.options.locationData.lng),
                    map: map,
                    icon: self.options.locationData.marker_url
                });

                self.directionsDisplay.setMap(map);
        },

        initializeRoute: function () {
            var self = this,
                autocompleteOrigin = new google.maps.places.Autocomplete($(self.originPointSelector)[0]),
                locationLatLng = self.options.locationData.lat.toString() + ', ' + self.options.locationData.lng.toString(),
                swapMode = $('[data-amlocator-js="swap-mode"]');

            google.maps.event.addListener(autocompleteOrigin, 'place_changed', function () {
                self.calcRoute($(self.originPointSelector).val(), locationLatLng, swapMode.is(':checked'));
            });

            $('[data-amlocator-js="travel-mode"], [data-amlocator-js="swap-mode"]').on('change', function () {
                self.calcRoute($(self.originPointSelector).val(), locationLatLng, swapMode.is(':checked'));
            });

            self.directionsDisplay.setPanel($('[data-amlocator-js="directions-panel"]')[0]);
        },

        calcRoute: function (origin, destination, swapMode) {
            var self = this,
                travelMode = $('[data-amlocator-js="travel-mode"]:checked').val(),
                request = {
                    origin: origin,
                    destination: destination,
                    travelMode: google.maps.TravelMode[travelMode]
            };

            if (swapMode) {
                request.origin = destination;
                request.destination = origin;
                $(self.destinationPointSelector).after($(self.originPointSelector));
            } else {
                $(self.destinationPointSelector).before($(self.originPointSelector));
            }

            if (origin) {
                $('body').trigger('processStart');

                self.directionsService.route(request, function (result, status) {
                    if (status == 'OK') {
                        self.directionsDisplay.setDirections(result);
                        $(self.directionsSelector).show();
                    } else {
                        alert($.mage.__('Sorry, Google failed to get directions and answered with status: ') + status);
                    }
                    $('body').trigger('processStop');
                });
            }
        },

        initializeGallery: function () {
            $('[data-amlocator-js="locator-gallery"]').slick({
                infinite: false,
                slidesToShow: 3,
                slidesToScroll: 3
            });
        },

        initPanorama: function () {
            this.panoramaService.getPanorama(
                {location: new google.maps.LatLng(this.options.locationData.lat, this.options.locationData.lng)},
                function (result, status) {
                    if (status === 'OK') {
                        panorama = new google.maps.StreetViewPanorama(
                            $('[data-amlocator-js="location-map"]')[0],
                            {
                                pano: result.location.pano,
                                enableCloseButton: true
                            }
                        );
                    } else {
                        alert($.mage.__('Sorry, there is no available view of the street yet.'));
                    }
                }
            )
        }
    });

    return $.mage.amLocationPage;
});
