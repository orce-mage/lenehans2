define([
    "jquery",
    "domReady!"
], function($){
    "use strict";

    $.widget('magebig.videobackground', {
        options: {
            videoType: 'contain'
        },

        _create: function() {
            self = this;
            self.initial();
        },

        initial: function() {
            var elmId = '#' + this.element[0]['id'],
                self = this,
                timer = false,
                videosrc = $(elmId).attr('data-videosrc'),
                videoPlay;
            if (typeof videosrc !== typeof undefined && videosrc !== false) {
                videoPlay = '<video class="background" loop ' + self.options.autoplay + ' ' + self.options.muted + ' ' + self.options.controls
                    + '><source src="' + videosrc + '" type="video/mp4"></video>';
                $(elmId).append(videoPlay).append('<div class="playvideo mbi mbi-play-circle"></div>');
            }

            var video = $(elmId + ' video'),
                play = video.next('.playvideo');

            if (video[0].paused) {
                play.addClass('mbi-pause-circle').removeClass('mbi-play-circle');
            } else {
                play.addClass('mbi-play-circle').removeClass('mbi-pause-circle');
            }

            $(elmId + ' .playvideo').on('click', function(event) {
                event.preventDefault();
                if (video[0].paused) {
                    video[0].play();
                    play.addClass('mbi-pause-circle').removeClass('mbi-play-circle');
                } else {
                    video[0].pause();
                    play.addClass('mbi-play-circle').removeClass('mbi-pause-circle');
                }
            });

            self.scaleVideo();

            $(window).on('resize', function() {
                if (timer) clearTimeout(timer);
                timer = setTimeout(function(){
                    self.scaleVideo();
                }, 1000);
            });
        },

        scaleVideo: function () {
            var self = this;
            var elmId = '#' + this.element[0]['id'];
            var video = $(elmId + ' video');

            video.bind("loadedmetadata", function () {
                var elmId = '#' + self.element[0]['id'];
                var heightContainer = $(elmId).outerHeight();
                var widthContainer = $(elmId).outerWidth();

                var widthVideo = this.videoWidth;
                var heightVideo = this.videoHeight;

                var ratioVideo = widthVideo/heightVideo;

                if (self.options.videoType == 'contain') {
                    heightVideo = parseInt(heightContainer) + 4;
                    widthVideo = parseInt(heightVideo*ratioVideo);
                    if (widthVideo < widthContainer) {
                        widthVideo = parseInt(widthContainer) + 4;
                        heightVideo = parseInt(widthVideo/ratioVideo);
                    }
                    video.height(heightVideo).width(widthVideo);
                } else if (self.options.videoType == 'fullscreen') {
                    var windowWidth = $(window).width(),
                        windowHeight = $(window).height();

                    $(elmId).width(windowWidth).height(windowHeight);

                    heightVideo = parseInt(heightContainer) + 4;
                    widthVideo = parseInt(heightVideo*ratioVideo);
                    if (widthVideo < widthContainer) {
                        widthVideo = parseInt(widthContainer) + 4;
                        heightVideo = parseInt(widthVideo/ratioVideo);
                    }
                    video.height(heightVideo).width(widthVideo);
                }
            });
        }
    });
    return $.magebig.videobackground;
});