/*! @source http://purl.eligrey.com/github/classList.js/blob/master/classList.js */
"document"in self&&("classList"in document.createElement("_")&&(!document.createElementNS||"classList"in document.createElementNS("http://www.w3.org/2000/svg","g"))||!function(t){"use strict";if("Element"in t){var e="classList",n="prototype",i=t.Element[n],s=Object,r=String[n].trim||function(){return this.replace(/^\s+|\s+$/g,"")},o=Array[n].indexOf||function(t){for(var e=0,n=this.length;n>e;e++)if(e in this&&this[e]===t)return e;return-1},a=function(t,e){this.name=t,this.code=DOMException[t],this.message=e},c=function(t,e){if(""===e)throw new a("SYNTAX_ERR","An invalid or illegal string was specified");if(/\s/.test(e))throw new a("INVALID_CHARACTER_ERR","String contains an invalid character");return o.call(t,e)},l=function(t){for(var e=r.call(t.getAttribute("class")||""),n=e?e.split(/\s+/):[],i=0,s=n.length;s>i;i++)this.push(n[i]);this._updateClassName=function(){t.setAttribute("class",""+this)}},u=l[n]=[],h=function(){return new l(this)};if(a[n]=Error[n],u.item=function(t){return this[t]||null},u.contains=function(t){return t+="",-1!==c(this,t)},u.add=function(){var t,e=arguments,n=0,i=e.length,s=!1;do t=e[n]+"",-1===c(this,t)&&(this.push(t),s=!0);while(++n<i);s&&this._updateClassName()},u.remove=function(){var t,e,n=arguments,i=0,s=n.length,r=!1;do for(t=n[i]+"",e=c(this,t);-1!==e;)this.splice(e,1),r=!0,e=c(this,t);while(++i<s);r&&this._updateClassName()},u.toggle=function(t,e){t+="";var n=this.contains(t),i=n?e!==!0&&"remove":e!==!1&&"add";return i&&this[i](t),e===!0||e===!1?e:!n},u.toString=function(){return this.join(" ")},s.defineProperty){var f={get:h,enumerable:!0,configurable:!0};try{s.defineProperty(i,e,f)}catch(g){(void 0===g.number||-2146823252===g.number)&&(f.enumerable=!1,s.defineProperty(i,e,f))}}else s[n].__defineGetter__&&i.__defineGetter__(e,h)}}(self),function(){"use strict";var t=document.createElement("_");if(t.classList.add("c1","c2"),!t.classList.contains("c2")){var e=function(t){var e=DOMTokenList.prototype[t];DOMTokenList.prototype[t]=function(t){var n,i=arguments.length;for(n=0;i>n;n++)t=arguments[n],e.call(this,t)}};e("add"),e("remove")}if(t.classList.toggle("c3",!1),t.classList.contains("c3")){var n=DOMTokenList.prototype.toggle;DOMTokenList.prototype.toggle=function(t,e){return 1 in arguments&&!this.contains(t)==!e?e:n.call(this,t)}}t=null}());

/**
 * Swiper 3.4.1
 * Most modern mobile touch slider and framework with hardware accelerated transitions
 * 
 * http://www.idangero.us/swiper/
 * 
 * Copyright 2016, Vladimir Kharlampidi
 * The iDangero.us
 * http://www.idangero.us/
 * 
 * Licensed under MIT
 * 
 * Released on: December 13, 2016
 */
!function(){"use strict";function e(e){e.fn.swiper=function(a){var s;return e(this).each(function(){var e=new t(this,a);s||(s=e)}),s}}var a,t=function(e,i){function r(e){return Math.floor(e)}function n(){var e=b.params.autoplay,a=b.slides.eq(b.activeIndex);a.attr("data-swiper-autoplay")&&(e=a.attr("data-swiper-autoplay")||b.params.autoplay),b.autoplayTimeoutId=setTimeout(function(){b.params.loop?(b.fixLoop(),b._slideNext(),b.emit("onAutoplay",b)):b.isEnd?i.autoplayStopOnLast?b.stopAutoplay():(b._slideTo(0),b.emit("onAutoplay",b)):(b._slideNext(),b.emit("onAutoplay",b))},e)}function o(e,t){var s=a(e.target);if(!s.is(t))if("string"==typeof t)s=s.parents(t);else if(t.nodeType){var i;return s.parents().each(function(e,a){a===t&&(i=t)}),i?t:void 0}if(0!==s.length)return s[0]}function l(e,a){a=a||{};var t=window.MutationObserver||window.WebkitMutationObserver,s=new t(function(e){e.forEach(function(e){b.onResize(!0),b.emit("onObserverUpdate",b,e)})});s.observe(e,{attributes:"undefined"==typeof a.attributes||a.attributes,childList:"undefined"==typeof a.childList||a.childList,characterData:"undefined"==typeof a.characterData||a.characterData}),b.observers.push(s)}function p(e){e.originalEvent&&(e=e.originalEvent);var a=e.keyCode||e.charCode;if(!b.params.allowSwipeToNext&&(b.isHorizontal()&&39===a||!b.isHorizontal()&&40===a))return!1;if(!b.params.allowSwipeToPrev&&(b.isHorizontal()&&37===a||!b.isHorizontal()&&38===a))return!1;if(!(e.shiftKey||e.altKey||e.ctrlKey||e.metaKey||document.activeElement&&document.activeElement.nodeName&&("input"===document.activeElement.nodeName.toLowerCase()||"textarea"===document.activeElement.nodeName.toLowerCase()))){if(37===a||39===a||38===a||40===a){var t=!1;if(b.container.parents("."+b.params.slideClass).length>0&&0===b.container.parents("."+b.params.slideActiveClass).length)return;var s={left:window.pageXOffset,top:window.pageYOffset},i=window.innerWidth,r=window.innerHeight,n=b.container.offset();b.rtl&&(n.left=n.left-b.container[0].scrollLeft);for(var o=[[n.left,n.top],[n.left+b.width,n.top],[n.left,n.top+b.height],[n.left+b.width,n.top+b.height]],l=0;l<o.length;l++){var p=o[l];p[0]>=s.left&&p[0]<=s.left+i&&p[1]>=s.top&&p[1]<=s.top+r&&(t=!0)}if(!t)return}b.isHorizontal()?(37!==a&&39!==a||(e.preventDefault?e.preventDefault():e.returnValue=!1),(39===a&&!b.rtl||37===a&&b.rtl)&&b.slideNext(),(37===a&&!b.rtl||39===a&&b.rtl)&&b.slidePrev()):(38!==a&&40!==a||(e.preventDefault?e.preventDefault():e.returnValue=!1),40===a&&b.slideNext(),38===a&&b.slidePrev())}}function d(){var e="onwheel",a=e in document;if(!a){var t=document.createElement("div");t.setAttribute(e,"return;"),a="function"==typeof t[e]}return!a&&document.implementation&&document.implementation.hasFeature&&document.implementation.hasFeature("","")!==!0&&(a=document.implementation.hasFeature("Events.wheel","3.0")),a}function u(e){e.originalEvent&&(e=e.originalEvent);var a=0,t=b.rtl?-1:1,s=c(e);if(b.params.mousewheelForceToAxis)if(b.isHorizontal()){if(!(Math.abs(s.pixelX)>Math.abs(s.pixelY)))return;a=s.pixelX*t}else{if(!(Math.abs(s.pixelY)>Math.abs(s.pixelX)))return;a=s.pixelY}else a=Math.abs(s.pixelX)>Math.abs(s.pixelY)?-s.pixelX*t:-s.pixelY;if(0!==a){if(b.params.mousewheelInvert&&(a=-a),b.params.freeMode){var i=b.getWrapperTranslate()+a*b.params.mousewheelSensitivity,r=b.isBeginning,n=b.isEnd;if(i>=b.minTranslate()&&(i=b.minTranslate()),i<=b.maxTranslate()&&(i=b.maxTranslate()),b.setWrapperTransition(0),b.setWrapperTranslate(i),b.updateProgress(),b.updateActiveIndex(),(!r&&b.isBeginning||!n&&b.isEnd)&&b.updateClasses(),b.params.freeModeSticky?(clearTimeout(b.mousewheel.timeout),b.mousewheel.timeout=setTimeout(function(){b.slideReset()},300)):b.params.lazyLoading&&b.lazy&&b.lazy.load(),b.emit("onScroll",b,e),b.params.autoplay&&b.params.autoplayDisableOnInteraction&&b.stopAutoplay(),0===i||i===b.maxTranslate())return}else{if((new window.Date).getTime()-b.mousewheel.lastScrollTime>60)if(a<0)if(b.isEnd&&!b.params.loop||b.animating){if(b.params.mousewheelReleaseOnEdges)return!0}else b.slideNext(),b.emit("onScroll",b,e);else if(b.isBeginning&&!b.params.loop||b.animating){if(b.params.mousewheelReleaseOnEdges)return!0}else b.slidePrev(),b.emit("onScroll",b,e);b.mousewheel.lastScrollTime=(new window.Date).getTime()}return e.preventDefault?e.preventDefault():e.returnValue=!1,!1}}function c(e){var a=10,t=40,s=800,i=0,r=0,n=0,o=0;return"detail"in e&&(r=e.detail),"wheelDelta"in e&&(r=-e.wheelDelta/120),"wheelDeltaY"in e&&(r=-e.wheelDeltaY/120),"wheelDeltaX"in e&&(i=-e.wheelDeltaX/120),"axis"in e&&e.axis===e.HORIZONTAL_AXIS&&(i=r,r=0),n=i*a,o=r*a,"deltaY"in e&&(o=e.deltaY),"deltaX"in e&&(n=e.deltaX),(n||o)&&e.deltaMode&&(1===e.deltaMode?(n*=t,o*=t):(n*=s,o*=s)),n&&!i&&(i=n<1?-1:1),o&&!r&&(r=o<1?-1:1),{spinX:i,spinY:r,pixelX:n,pixelY:o}}function m(e,t){e=a(e);var s,i,r,n=b.rtl?-1:1;s=e.attr("data-swiper-parallax")||"0",i=e.attr("data-swiper-parallax-x"),r=e.attr("data-swiper-parallax-y"),i||r?(i=i||"0",r=r||"0"):b.isHorizontal()?(i=s,r="0"):(r=s,i="0"),i=i.indexOf("%")>=0?parseInt(i,10)*t*n+"%":i*t*n+"px",r=r.indexOf("%")>=0?parseInt(r,10)*t+"%":r*t+"px",e.transform("translate3d("+i+", "+r+",0px)")}function h(e){return 0!==e.indexOf("on")&&(e=e[0]!==e[0].toUpperCase()?"on"+e[0].toUpperCase()+e.substring(1):"on"+e),e}if(!(this instanceof t))return new t(e,i);var g={direction:"horizontal",touchEventsTarget:"container",initialSlide:0,speed:300,autoplay:!1,autoplayDisableOnInteraction:!0,autoplayStopOnLast:!1,iOSEdgeSwipeDetection:!1,iOSEdgeSwipeThreshold:20,freeMode:!1,freeModeMomentum:!0,freeModeMomentumRatio:1,freeModeMomentumBounce:!0,freeModeMomentumBounceRatio:1,freeModeMomentumVelocityRatio:1,freeModeSticky:!1,freeModeMinimumVelocity:.02,autoHeight:!1,setWrapperSize:!1,virtualTranslate:!1,effect:"slide",coverflow:{rotate:50,stretch:0,depth:100,modifier:1,slideShadows:!0},flip:{slideShadows:!0,limitRotation:!0},cube:{slideShadows:!0,shadow:!0,shadowOffset:20,shadowScale:.94},fade:{crossFade:!1},parallax:!1,zoom:!1,zoomMax:3,zoomMin:1,zoomToggle:!0,scrollbar:null,scrollbarHide:!0,scrollbarDraggable:!1,scrollbarSnapOnRelease:!1,keyboardControl:!1,mousewheelControl:!1,mousewheelReleaseOnEdges:!1,mousewheelInvert:!1,mousewheelForceToAxis:!1,mousewheelSensitivity:1,mousewheelEventsTarged:"container",hashnav:!1,hashnavWatchState:!1,history:!1,replaceState:!1,breakpoints:void 0,spaceBetween:0,slidesPerView:1,slidesPerColumn:1,slidesPerColumnFill:"column",slidesPerGroup:1,centeredSlides:!1,slidesOffsetBefore:0,slidesOffsetAfter:0,roundLengths:!1,touchRatio:1,touchAngle:45,simulateTouch:!0,shortSwipes:!0,longSwipes:!0,longSwipesRatio:.5,longSwipesMs:300,followFinger:!0,onlyExternal:!1,threshold:0,touchMoveStopPropagation:!0,touchReleaseOnEdges:!1,uniqueNavElements:!0,pagination:null,paginationElement:"span",paginationClickable:!1,paginationHide:!1,paginationBulletRender:null,paginationProgressRender:null,paginationFractionRender:null,paginationCustomRender:null,paginationType:"bullets",resistance:!0,resistanceRatio:.85,nextButton:null,prevButton:null,watchSlidesProgress:!1,watchSlidesVisibility:!1,grabCursor:!1,preventClicks:!0,preventClicksPropagation:!0,slideToClickedSlide:!1,lazyLoading:!1,lazyLoadingInPrevNext:!1,lazyLoadingInPrevNextAmount:1,lazyLoadingOnTransitionStart:!1,preloadImages:!0,updateOnImagesReady:!0,loop:!1,loopAdditionalSlides:0,loopedSlides:null,control:void 0,controlInverse:!1,controlBy:"slide",normalizeSlideIndex:!0,allowSwipeToPrev:!0,allowSwipeToNext:!0,swipeHandler:null,noSwiping:!0,noSwipingClass:"swiper-no-swiping",passiveListeners:!0,containerModifierClass:"swiper-container-",slideClass:"swiper-slide",slideActiveClass:"swiper-slide-active",slideDuplicateActiveClass:"swiper-slide-duplicate-active",slideVisibleClass:"swiper-slide-visible",slideDuplicateClass:"swiper-slide-duplicate",slideNextClass:"swiper-slide-next",slideDuplicateNextClass:"swiper-slide-duplicate-next",slidePrevClass:"swiper-slide-prev",slideDuplicatePrevClass:"swiper-slide-duplicate-prev",wrapperClass:"swiper-wrapper",bulletClass:"swiper-pagination-bullet",bulletActiveClass:"swiper-pagination-bullet-active",buttonDisabledClass:"swiper-button-disabled",paginationCurrentClass:"swiper-pagination-current",paginationTotalClass:"swiper-pagination-total",paginationHiddenClass:"swiper-pagination-hidden",paginationProgressbarClass:"swiper-pagination-progressbar",paginationClickableClass:"swiper-pagination-clickable",paginationModifierClass:"swiper-pagination-",lazyLoadingClass:"swiper-lazy",lazyStatusLoadingClass:"swiper-lazy-loading",lazyStatusLoadedClass:"swiper-lazy-loaded",lazyPreloaderClass:"swiper-lazy-preloader",notificationClass:"swiper-notification",preloaderClass:"preloader",zoomContainerClass:"swiper-zoom-container",observer:!1,observeParents:!1,a11y:!1,prevSlideMessage:"Previous slide",nextSlideMessage:"Next slide",firstSlideMessage:"This is the first slide",lastSlideMessage:"This is the last slide",paginationBulletMessage:"Go to slide {{index}}",runCallbacksOnInit:!0},f=i&&i.virtualTranslate;i=i||{};var v={};for(var w in i)if("object"!=typeof i[w]||null===i[w]||(i[w].nodeType||i[w]===window||i[w]===document||"undefined"!=typeof s&&i[w]instanceof s||"undefined"!=typeof jQuery&&i[w]instanceof jQuery))v[w]=i[w];else{v[w]={};for(var y in i[w])v[w][y]=i[w][y]}for(var x in g)if("undefined"==typeof i[x])i[x]=g[x];else if("object"==typeof i[x])for(var T in g[x])"undefined"==typeof i[x][T]&&(i[x][T]=g[x][T]);var b=this;if(b.params=i,b.originalParams=v,b.classNames=[],"undefined"!=typeof a&&"undefined"!=typeof s&&(a=s),("undefined"!=typeof a||(a="undefined"==typeof s?window.Dom7||window.Zepto||window.jQuery:s))&&(b.$=a,b.currentBreakpoint=void 0,b.getActiveBreakpoint=function(){if(!b.params.breakpoints)return!1;var e,a=!1,t=[];for(e in b.params.breakpoints)b.params.breakpoints.hasOwnProperty(e)&&t.push(e);t.sort(function(e,a){return parseInt(e,10)>parseInt(a,10)});for(var s=0;s<t.length;s++)e=t[s],e>=window.innerWidth&&!a&&(a=e);return a||"max"},b.setBreakpoint=function(){var e=b.getActiveBreakpoint();if(e&&b.currentBreakpoint!==e){var a=e in b.params.breakpoints?b.params.breakpoints[e]:b.originalParams,t=b.params.loop&&a.slidesPerView!==b.params.slidesPerView;for(var s in a)b.params[s]=a[s];b.currentBreakpoint=e,t&&b.destroyLoop&&b.reLoop(!0)}},b.params.breakpoints&&b.setBreakpoint(),b.container=a(e),0!==b.container.length)){if(b.container.length>1){var S=[];return b.container.each(function(){S.push(new t(this,i))}),S}b.container[0].swiper=b,b.container.data("swiper",b),b.classNames.push(b.params.containerModifierClass+b.params.direction),b.params.freeMode&&b.classNames.push(b.params.containerModifierClass+"free-mode"),b.support.flexbox||(b.classNames.push(b.params.containerModifierClass+"no-flexbox"),b.params.slidesPerColumn=1),b.params.autoHeight&&b.classNames.push(b.params.containerModifierClass+"autoheight"),(b.params.parallax||b.params.watchSlidesVisibility)&&(b.params.watchSlidesProgress=!0),b.params.touchReleaseOnEdges&&(b.params.resistanceRatio=0),["cube","coverflow","flip"].indexOf(b.params.effect)>=0&&(b.support.transforms3d?(b.params.watchSlidesProgress=!0,b.classNames.push(b.params.containerModifierClass+"3d")):b.params.effect="slide"),"slide"!==b.params.effect&&b.classNames.push(b.params.containerModifierClass+b.params.effect),"cube"===b.params.effect&&(b.params.resistanceRatio=0,b.params.slidesPerView=1,b.params.slidesPerColumn=1,b.params.slidesPerGroup=1,b.params.centeredSlides=!1,b.params.spaceBetween=0,b.params.virtualTranslate=!0,b.params.setWrapperSize=!1),"fade"!==b.params.effect&&"flip"!==b.params.effect||(b.params.slidesPerView=1,b.params.slidesPerColumn=1,b.params.slidesPerGroup=1,b.params.watchSlidesProgress=!0,b.params.spaceBetween=0,b.params.setWrapperSize=!1,"undefined"==typeof f&&(b.params.virtualTranslate=!0)),b.params.grabCursor&&b.support.touch&&(b.params.grabCursor=!1),b.wrapper=b.container.children("."+b.params.wrapperClass),b.params.pagination&&(b.paginationContainer=a(b.params.pagination),b.params.uniqueNavElements&&"string"==typeof b.params.pagination&&b.paginationContainer.length>1&&1===b.container.find(b.params.pagination).length&&(b.paginationContainer=b.container.find(b.params.pagination)),"bullets"===b.params.paginationType&&b.params.paginationClickable?b.paginationContainer.addClass(b.params.paginationModifierClass+"clickable"):b.params.paginationClickable=!1,b.paginationContainer.addClass(b.params.paginationModifierClass+b.params.paginationType)),(b.params.nextButton||b.params.prevButton)&&(b.params.nextButton&&(b.nextButton=a(b.params.nextButton),b.params.uniqueNavElements&&"string"==typeof b.params.nextButton&&b.nextButton.length>1&&1===b.container.find(b.params.nextButton).length&&(b.nextButton=b.container.find(b.params.nextButton))),b.params.prevButton&&(b.prevButton=a(b.params.prevButton),b.params.uniqueNavElements&&"string"==typeof b.params.prevButton&&b.prevButton.length>1&&1===b.container.find(b.params.prevButton).length&&(b.prevButton=b.container.find(b.params.prevButton)))),b.isHorizontal=function(){return"horizontal"===b.params.direction},b.rtl=b.isHorizontal()&&("rtl"===b.container[0].dir.toLowerCase()||"rtl"===b.container.css("direction")),b.rtl&&b.classNames.push(b.params.containerModifierClass+"rtl"),b.rtl&&(b.wrongRTL="-webkit-box"===b.wrapper.css("display")),b.params.slidesPerColumn>1&&b.classNames.push(b.params.containerModifierClass+"multirow"),b.device.android&&b.classNames.push(b.params.containerModifierClass+"android"),b.container.addClass(b.classNames.join(" ")),b.translate=0,b.progress=0,b.velocity=0,b.lockSwipeToNext=function(){b.params.allowSwipeToNext=!1,b.params.allowSwipeToPrev===!1&&b.params.grabCursor&&b.unsetGrabCursor()},b.lockSwipeToPrev=function(){b.params.allowSwipeToPrev=!1,b.params.allowSwipeToNext===!1&&b.params.grabCursor&&b.unsetGrabCursor()},b.lockSwipes=function(){b.params.allowSwipeToNext=b.params.allowSwipeToPrev=!1,b.params.grabCursor&&b.unsetGrabCursor()},b.unlockSwipeToNext=function(){b.params.allowSwipeToNext=!0,b.params.allowSwipeToPrev===!0&&b.params.grabCursor&&b.setGrabCursor()},b.unlockSwipeToPrev=function(){b.params.allowSwipeToPrev=!0,b.params.allowSwipeToNext===!0&&b.params.grabCursor&&b.setGrabCursor()},b.unlockSwipes=function(){b.params.allowSwipeToNext=b.params.allowSwipeToPrev=!0,b.params.grabCursor&&b.setGrabCursor()},b.setGrabCursor=function(e){b.container[0].style.cursor="move",b.container[0].style.cursor=e?"-webkit-grabbing":"-webkit-grab",b.container[0].style.cursor=e?"-moz-grabbin":"-moz-grab",b.container[0].style.cursor=e?"grabbing":"grab"},b.unsetGrabCursor=function(){b.container[0].style.cursor=""},b.params.grabCursor&&b.setGrabCursor(),b.imagesToLoad=[],b.imagesLoaded=0,b.loadImage=function(e,a,t,s,i,r){function n(){r&&r()}var o;e.complete&&i?n():a?(o=new window.Image,o.onload=n,o.onerror=n,s&&(o.sizes=s),t&&(o.srcset=t),a&&(o.src=a)):n()},b.preloadImages=function(){function e(){"undefined"!=typeof b&&null!==b&&b&&(void 0!==b.imagesLoaded&&b.imagesLoaded++,b.imagesLoaded===b.imagesToLoad.length&&(b.params.updateOnImagesReady&&b.update(),b.emit("onImagesReady",b)))}b.imagesToLoad=b.container.find("img");for(var a=0;a<b.imagesToLoad.length;a++)b.loadImage(b.imagesToLoad[a],b.imagesToLoad[a].currentSrc||b.imagesToLoad[a].getAttribute("src"),b.imagesToLoad[a].srcset||b.imagesToLoad[a].getAttribute("srcset"),b.imagesToLoad[a].sizes||b.imagesToLoad[a].getAttribute("sizes"),!0,e)},b.autoplayTimeoutId=void 0,b.autoplaying=!1,b.autoplayPaused=!1,b.startAutoplay=function(){return"undefined"==typeof b.autoplayTimeoutId&&(!!b.params.autoplay&&(!b.autoplaying&&(b.autoplaying=!0,b.emit("onAutoplayStart",b),void n())))},b.stopAutoplay=function(e){b.autoplayTimeoutId&&(b.autoplayTimeoutId&&clearTimeout(b.autoplayTimeoutId),b.autoplaying=!1,b.autoplayTimeoutId=void 0,b.emit("onAutoplayStop",b))},b.pauseAutoplay=function(e){b.autoplayPaused||(b.autoplayTimeoutId&&clearTimeout(b.autoplayTimeoutId),b.autoplayPaused=!0,0===e?(b.autoplayPaused=!1,n()):b.wrapper.transitionEnd(function(){b&&(b.autoplayPaused=!1,b.autoplaying?n():b.stopAutoplay())}))},b.minTranslate=function(){return-b.snapGrid[0]},b.maxTranslate=function(){return-b.snapGrid[b.snapGrid.length-1]},b.updateAutoHeight=function(){var e,a=[],t=0;if("auto"!==b.params.slidesPerView&&b.params.slidesPerView>1)for(e=0;e<Math.ceil(b.params.slidesPerView);e++){var s=b.activeIndex+e;if(s>b.slides.length)break;a.push(b.slides.eq(s)[0])}else a.push(b.slides.eq(b.activeIndex)[0]);for(e=0;e<a.length;e++)if("undefined"!=typeof a[e]){var i=a[e].offsetHeight;t=i>t?i:t}t&&b.wrapper.css("height",t+"px")},b.updateContainerSize=function(){var e,a;e="undefined"!=typeof b.params.width?b.params.width:b.container[0].clientWidth,a="undefined"!=typeof b.params.height?b.params.height:b.container[0].clientHeight,0===e&&b.isHorizontal()||0===a&&!b.isHorizontal()||(e=e-parseInt(b.container.css("padding-left"),10)-parseInt(b.container.css("padding-right"),10),a=a-parseInt(b.container.css("padding-top"),10)-parseInt(b.container.css("padding-bottom"),10),b.width=e,b.height=a,b.size=b.isHorizontal()?b.width:b.height)},b.updateSlidesSize=function(){b.slides=b.wrapper.children("."+b.params.slideClass),b.snapGrid=[],b.slidesGrid=[],b.slidesSizesGrid=[];var e,a=b.params.spaceBetween,t=-b.params.slidesOffsetBefore,s=0,i=0;if("undefined"!=typeof b.size){"string"==typeof a&&a.indexOf("%")>=0&&(a=parseFloat(a.replace("%",""))/100*b.size),b.virtualSize=-a,b.rtl?b.slides.css({marginLeft:"",marginTop:""}):b.slides.css({marginRight:"",marginBottom:""});var n;b.params.slidesPerColumn>1&&(n=Math.floor(b.slides.length/b.params.slidesPerColumn)===b.slides.length/b.params.slidesPerColumn?b.slides.length:Math.ceil(b.slides.length/b.params.slidesPerColumn)*b.params.slidesPerColumn,"auto"!==b.params.slidesPerView&&"row"===b.params.slidesPerColumnFill&&(n=Math.max(n,b.params.slidesPerView*b.params.slidesPerColumn)));var o,l=b.params.slidesPerColumn,p=n/l,d=p-(b.params.slidesPerColumn*p-b.slides.length);for(e=0;e<b.slides.length;e++){o=0;var u=b.slides.eq(e);if(b.params.slidesPerColumn>1){var c,m,h;"column"===b.params.slidesPerColumnFill?(m=Math.floor(e/l),h=e-m*l,(m>d||m===d&&h===l-1)&&++h>=l&&(h=0,m++),c=m+h*n/l,u.css({"-webkit-box-ordinal-group":c,"-moz-box-ordinal-group":c,"-ms-flex-order":c,"-webkit-order":c,order:c})):(h=Math.floor(e/p),m=e-h*p),u.css("margin-"+(b.isHorizontal()?"top":"left"),0!==h&&b.params.spaceBetween&&b.params.spaceBetween+"px").attr("data-swiper-column",m).attr("data-swiper-row",h)}"none"!==u.css("display")&&("auto"===b.params.slidesPerView?(o=b.isHorizontal()?u.outerWidth(!0):u.outerHeight(!0),b.params.roundLengths&&(o=r(o))):(o=(b.size-(b.params.slidesPerView-1)*a)/b.params.slidesPerView,b.params.roundLengths&&(o=r(o)),b.isHorizontal()?b.slides[e].style.width=o+"px":b.slides[e].style.height=o+"px"),b.slides[e].swiperSlideSize=o,b.slidesSizesGrid.push(o),b.params.centeredSlides?(t=t+o/2+s/2+a,0===e&&(t=t-b.size/2-a),Math.abs(t)<.001&&(t=0),i%b.params.slidesPerGroup===0&&b.snapGrid.push(t),b.slidesGrid.push(t)):(i%b.params.slidesPerGroup===0&&b.snapGrid.push(t),b.slidesGrid.push(t),t=t+o+a),b.virtualSize+=o+a,s=o,i++)}b.virtualSize=Math.max(b.virtualSize,b.size)+b.params.slidesOffsetAfter;var g;if(b.rtl&&b.wrongRTL&&("slide"===b.params.effect||"coverflow"===b.params.effect)&&b.wrapper.css({width:b.virtualSize+b.params.spaceBetween+"px"}),b.support.flexbox&&!b.params.setWrapperSize||(b.isHorizontal()?b.wrapper.css({width:b.virtualSize+b.params.spaceBetween+"px"}):b.wrapper.css({height:b.virtualSize+b.params.spaceBetween+"px"})),b.params.slidesPerColumn>1&&(b.virtualSize=(o+b.params.spaceBetween)*n,b.virtualSize=Math.ceil(b.virtualSize/b.params.slidesPerColumn)-b.params.spaceBetween,b.isHorizontal()?b.wrapper.css({width:b.virtualSize+b.params.spaceBetween+"px"}):b.wrapper.css({height:b.virtualSize+b.params.spaceBetween+"px"}),b.params.centeredSlides)){for(g=[],e=0;e<b.snapGrid.length;e++)b.snapGrid[e]<b.virtualSize+b.snapGrid[0]&&g.push(b.snapGrid[e]);b.snapGrid=g}if(!b.params.centeredSlides){for(g=[],e=0;e<b.snapGrid.length;e++)b.snapGrid[e]<=b.virtualSize-b.size&&g.push(b.snapGrid[e]);b.snapGrid=g,Math.floor(b.virtualSize-b.size)-Math.floor(b.snapGrid[b.snapGrid.length-1])>1&&b.snapGrid.push(b.virtualSize-b.size)}0===b.snapGrid.length&&(b.snapGrid=[0]),0!==b.params.spaceBetween&&(b.isHorizontal()?b.rtl?b.slides.css({marginLeft:a+"px"}):b.slides.css({marginRight:a+"px"}):b.slides.css({marginBottom:a+"px"})),b.params.watchSlidesProgress&&b.updateSlidesOffset()}},b.updateSlidesOffset=function(){for(var e=0;e<b.slides.length;e++)b.slides[e].swiperSlideOffset=b.isHorizontal()?b.slides[e].offsetLeft:b.slides[e].offsetTop},b.currentSlidesPerView=function(){var e,a,t=1;if(b.params.centeredSlides){var s,i=b.slides[b.activeIndex].swiperSlideSize;for(e=b.activeIndex+1;e<b.slides.length;e++)b.slides[e]&&!s&&(i+=b.slides[e].swiperSlideSize,t++,i>b.size&&(s=!0));for(a=b.activeIndex-1;a>=0;a--)b.slides[a]&&!s&&(i+=b.slides[a].swiperSlideSize,t++,i>b.size&&(s=!0))}else for(e=b.activeIndex+1;e<b.slides.length;e++)b.slidesGrid[e]-b.slidesGrid[b.activeIndex]<b.size&&t++;return t},b.updateSlidesProgress=function(e){if("undefined"==typeof e&&(e=b.translate||0),0!==b.slides.length){"undefined"==typeof b.slides[0].swiperSlideOffset&&b.updateSlidesOffset();var a=-e;b.rtl&&(a=e),b.slides.removeClass(b.params.slideVisibleClass);for(var t=0;t<b.slides.length;t++){var s=b.slides[t],i=(a+(b.params.centeredSlides?b.minTranslate():0)-s.swiperSlideOffset)/(s.swiperSlideSize+b.params.spaceBetween);if(b.params.watchSlidesVisibility){var r=-(a-s.swiperSlideOffset),n=r+b.slidesSizesGrid[t],o=r>=0&&r<b.size||n>0&&n<=b.size||r<=0&&n>=b.size;o&&b.slides.eq(t).addClass(b.params.slideVisibleClass)}s.progress=b.rtl?-i:i}}},b.updateProgress=function(e){"undefined"==typeof e&&(e=b.translate||0);var a=b.maxTranslate()-b.minTranslate(),t=b.isBeginning,s=b.isEnd;0===a?(b.progress=0,b.isBeginning=b.isEnd=!0):(b.progress=(e-b.minTranslate())/a,b.isBeginning=b.progress<=0,b.isEnd=b.progress>=1),b.isBeginning&&!t&&b.emit("onReachBeginning",b),b.isEnd&&!s&&b.emit("onReachEnd",b),b.params.watchSlidesProgress&&b.updateSlidesProgress(e),b.emit("onProgress",b,b.progress)},b.updateActiveIndex=function(){var e,a,t,s=b.rtl?b.translate:-b.translate;for(a=0;a<b.slidesGrid.length;a++)"undefined"!=typeof b.slidesGrid[a+1]?s>=b.slidesGrid[a]&&s<b.slidesGrid[a+1]-(b.slidesGrid[a+1]-b.slidesGrid[a])/2?e=a:s>=b.slidesGrid[a]&&s<b.slidesGrid[a+1]&&(e=a+1):s>=b.slidesGrid[a]&&(e=a);b.params.normalizeSlideIndex&&(e<0||"undefined"==typeof e)&&(e=0),t=Math.floor(e/b.params.slidesPerGroup),t>=b.snapGrid.length&&(t=b.snapGrid.length-1),e!==b.activeIndex&&(b.snapIndex=t,b.previousIndex=b.activeIndex,b.activeIndex=e,b.updateClasses(),b.updateRealIndex())},b.updateRealIndex=function(){b.realIndex=parseInt(b.slides.eq(b.activeIndex).attr("data-swiper-slide-index")||b.activeIndex,10)},b.updateClasses=function(){b.slides.removeClass(b.params.slideActiveClass+" "+b.params.slideNextClass+" "+b.params.slidePrevClass+" "+b.params.slideDuplicateActiveClass+" "+b.params.slideDuplicateNextClass+" "+b.params.slideDuplicatePrevClass);var e=b.slides.eq(b.activeIndex);e.addClass(b.params.slideActiveClass),i.loop&&(e.hasClass(b.params.slideDuplicateClass)?b.wrapper.children("."+b.params.slideClass+":not(."+b.params.slideDuplicateClass+')[data-swiper-slide-index="'+b.realIndex+'"]').addClass(b.params.slideDuplicateActiveClass):b.wrapper.children("."+b.params.slideClass+"."+b.params.slideDuplicateClass+'[data-swiper-slide-index="'+b.realIndex+'"]').addClass(b.params.slideDuplicateActiveClass));var t=e.next("."+b.params.slideClass).addClass(b.params.slideNextClass);b.params.loop&&0===t.length&&(t=b.slides.eq(0),t.addClass(b.params.slideNextClass));var s=e.prev("."+b.params.slideClass).addClass(b.params.slidePrevClass);if(b.params.loop&&0===s.length&&(s=b.slides.eq(-1),s.addClass(b.params.slidePrevClass)),i.loop&&(t.hasClass(b.params.slideDuplicateClass)?b.wrapper.children("."+b.params.slideClass+":not(."+b.params.slideDuplicateClass+')[data-swiper-slide-index="'+t.attr("data-swiper-slide-index")+'"]').addClass(b.params.slideDuplicateNextClass):b.wrapper.children("."+b.params.slideClass+"."+b.params.slideDuplicateClass+'[data-swiper-slide-index="'+t.attr("data-swiper-slide-index")+'"]').addClass(b.params.slideDuplicateNextClass),s.hasClass(b.params.slideDuplicateClass)?b.wrapper.children("."+b.params.slideClass+":not(."+b.params.slideDuplicateClass+')[data-swiper-slide-index="'+s.attr("data-swiper-slide-index")+'"]').addClass(b.params.slideDuplicatePrevClass):b.wrapper.children("."+b.params.slideClass+"."+b.params.slideDuplicateClass+'[data-swiper-slide-index="'+s.attr("data-swiper-slide-index")+'"]').addClass(b.params.slideDuplicatePrevClass)),b.paginationContainer&&b.paginationContainer.length>0){var r,n=b.params.loop?Math.ceil((b.slides.length-2*b.loopedSlides)/b.params.slidesPerGroup):b.snapGrid.length;if(b.params.loop?(r=Math.ceil((b.activeIndex-b.loopedSlides)/b.params.slidesPerGroup),r>b.slides.length-1-2*b.loopedSlides&&(r-=b.slides.length-2*b.loopedSlides),r>n-1&&(r-=n),r<0&&"bullets"!==b.params.paginationType&&(r=n+r)):r="undefined"!=typeof b.snapIndex?b.snapIndex:b.activeIndex||0,"bullets"===b.params.paginationType&&b.bullets&&b.bullets.length>0&&(b.bullets.removeClass(b.params.bulletActiveClass),b.paginationContainer.length>1?b.bullets.each(function(){a(this).index()===r&&a(this).addClass(b.params.bulletActiveClass)}):b.bullets.eq(r).addClass(b.params.bulletActiveClass)),"fraction"===b.params.paginationType&&(b.paginationContainer.find("."+b.params.paginationCurrentClass).text(r+1),b.paginationContainer.find("."+b.params.paginationTotalClass).text(n)),"progress"===b.params.paginationType){var o=(r+1)/n,l=o,p=1;b.isHorizontal()||(p=o,l=1),b.paginationContainer.find("."+b.params.paginationProgressbarClass).transform("translate3d(0,0,0) scaleX("+l+") scaleY("+p+")").transition(b.params.speed)}"custom"===b.params.paginationType&&b.params.paginationCustomRender&&(b.paginationContainer.html(b.params.paginationCustomRender(b,r+1,n)),b.emit("onPaginationRendered",b,b.paginationContainer[0]))}b.params.loop||(b.params.prevButton&&b.prevButton&&b.prevButton.length>0&&(b.isBeginning?(b.prevButton.addClass(b.params.buttonDisabledClass),b.params.a11y&&b.a11y&&b.a11y.disable(b.prevButton)):(b.prevButton.removeClass(b.params.buttonDisabledClass),b.params.a11y&&b.a11y&&b.a11y.enable(b.prevButton))),b.params.nextButton&&b.nextButton&&b.nextButton.length>0&&(b.isEnd?(b.nextButton.addClass(b.params.buttonDisabledClass),b.params.a11y&&b.a11y&&b.a11y.disable(b.nextButton)):(b.nextButton.removeClass(b.params.buttonDisabledClass),b.params.a11y&&b.a11y&&b.a11y.enable(b.nextButton))))},b.updatePagination=function(){if(b.params.pagination&&b.paginationContainer&&b.paginationContainer.length>0){var e="";if("bullets"===b.params.paginationType){for(var a=b.params.loop?Math.ceil((b.slides.length-2*b.loopedSlides)/b.params.slidesPerGroup):b.snapGrid.length,t=0;t<a;t++)e+=b.params.paginationBulletRender?b.params.paginationBulletRender(b,t,b.params.bulletClass):"<"+b.params.paginationElement+' class="'+b.params.bulletClass+'"></'+b.params.paginationElement+">";b.paginationContainer.html(e),b.bullets=b.paginationContainer.find("."+b.params.bulletClass),b.params.paginationClickable&&b.params.a11y&&b.a11y&&b.a11y.initPagination()}"fraction"===b.params.paginationType&&(e=b.params.paginationFractionRender?b.params.paginationFractionRender(b,b.params.paginationCurrentClass,b.params.paginationTotalClass):'<span class="'+b.params.paginationCurrentClass+'"></span> / <span class="'+b.params.paginationTotalClass+'"></span>',b.paginationContainer.html(e)),"progress"===b.params.paginationType&&(e=b.params.paginationProgressRender?b.params.paginationProgressRender(b,b.params.paginationProgressbarClass):'<span class="'+b.params.paginationProgressbarClass+'"></span>',b.paginationContainer.html(e)),"custom"!==b.params.paginationType&&b.emit("onPaginationRendered",b,b.paginationContainer[0])}},b.update=function(e){function a(){b.rtl?-b.translate:b.translate;s=Math.min(Math.max(b.translate,b.maxTranslate()),b.minTranslate()),b.setWrapperTranslate(s),b.updateActiveIndex(),b.updateClasses()}if(b)if(b.updateContainerSize(),b.updateSlidesSize(),b.updateProgress(),b.updatePagination(),b.updateClasses(),b.params.scrollbar&&b.scrollbar&&b.scrollbar.set(),e){var t,s;b.controller&&b.controller.spline&&(b.controller.spline=void 0),b.params.freeMode?(a(),b.params.autoHeight&&b.updateAutoHeight()):(t=("auto"===b.params.slidesPerView||b.params.slidesPerView>1)&&b.isEnd&&!b.params.centeredSlides?b.slideTo(b.slides.length-1,0,!1,!0):b.slideTo(b.activeIndex,0,!1,!0),t||a())}else b.params.autoHeight&&b.updateAutoHeight()},b.onResize=function(e){b.params.breakpoints&&b.setBreakpoint();var a=b.params.allowSwipeToPrev,t=b.params.allowSwipeToNext;b.params.allowSwipeToPrev=b.params.allowSwipeToNext=!0,b.updateContainerSize(),b.updateSlidesSize(),("auto"===b.params.slidesPerView||b.params.freeMode||e)&&b.updatePagination(),b.params.scrollbar&&b.scrollbar&&b.scrollbar.set(),b.controller&&b.controller.spline&&(b.controller.spline=void 0);var s=!1;if(b.params.freeMode){var i=Math.min(Math.max(b.translate,b.maxTranslate()),b.minTranslate());b.setWrapperTranslate(i),b.updateActiveIndex(),b.updateClasses(),b.params.autoHeight&&b.updateAutoHeight()}else b.updateClasses(),s=("auto"===b.params.slidesPerView||b.params.slidesPerView>1)&&b.isEnd&&!b.params.centeredSlides?b.slideTo(b.slides.length-1,0,!1,!0):b.slideTo(b.activeIndex,0,!1,!0);b.params.lazyLoading&&!s&&b.lazy&&b.lazy.load(),b.params.allowSwipeToPrev=a,b.params.allowSwipeToNext=t},b.touchEventsDesktop={start:"mousedown",move:"mousemove",end:"mouseup"},window.navigator.pointerEnabled?b.touchEventsDesktop={start:"pointerdown",move:"pointermove",end:"pointerup"}:window.navigator.msPointerEnabled&&(b.touchEventsDesktop={start:"MSPointerDown",move:"MSPointerMove",end:"MSPointerUp"}),b.touchEvents={start:b.support.touch||!b.params.simulateTouch?"touchstart":b.touchEventsDesktop.start,move:b.support.touch||!b.params.simulateTouch?"touchmove":b.touchEventsDesktop.move,end:b.support.touch||!b.params.simulateTouch?"touchend":b.touchEventsDesktop.end},(window.navigator.pointerEnabled||window.navigator.msPointerEnabled)&&("container"===b.params.touchEventsTarget?b.container:b.wrapper).addClass("swiper-wp8-"+b.params.direction),b.initEvents=function(e){var a=e?"off":"on",t=e?"removeEventListener":"addEventListener",s="container"===b.params.touchEventsTarget?b.container[0]:b.wrapper[0],r=b.support.touch?s:document,n=!!b.params.nested;if(b.browser.ie)s[t](b.touchEvents.start,b.onTouchStart,!1),r[t](b.touchEvents.move,b.onTouchMove,n),r[t](b.touchEvents.end,b.onTouchEnd,!1);else{if(b.support.touch){var o=!("touchstart"!==b.touchEvents.start||!b.support.passiveListener||!b.params.passiveListeners)&&{passive:!0,capture:!1};s[t](b.touchEvents.start,b.onTouchStart,o),s[t](b.touchEvents.move,b.onTouchMove,n),s[t](b.touchEvents.end,b.onTouchEnd,o)}(i.simulateTouch&&!b.device.ios&&!b.device.android||i.simulateTouch&&!b.support.touch&&b.device.ios)&&(s[t]("mousedown",b.onTouchStart,!1),document[t]("mousemove",b.onTouchMove,n),document[t]("mouseup",b.onTouchEnd,!1))}window[t]("resize",b.onResize),b.params.nextButton&&b.nextButton&&b.nextButton.length>0&&(b.nextButton[a]("click",b.onClickNext),b.params.a11y&&b.a11y&&b.nextButton[a]("keydown",b.a11y.onEnterKey)),b.params.prevButton&&b.prevButton&&b.prevButton.length>0&&(b.prevButton[a]("click",b.onClickPrev),b.params.a11y&&b.a11y&&b.prevButton[a]("keydown",b.a11y.onEnterKey)),b.params.pagination&&b.params.paginationClickable&&(b.paginationContainer[a]("click","."+b.params.bulletClass,b.onClickIndex),b.params.a11y&&b.a11y&&b.paginationContainer[a]("keydown","."+b.params.bulletClass,b.a11y.onEnterKey)),(b.params.preventClicks||b.params.preventClicksPropagation)&&s[t]("click",b.preventClicks,!0);
},b.attachEvents=function(){b.initEvents()},b.detachEvents=function(){b.initEvents(!0)},b.allowClick=!0,b.preventClicks=function(e){b.allowClick||(b.params.preventClicks&&e.preventDefault(),b.params.preventClicksPropagation&&b.animating&&(e.stopPropagation(),e.stopImmediatePropagation()))},b.onClickNext=function(e){e.preventDefault(),b.isEnd&&!b.params.loop||b.slideNext()},b.onClickPrev=function(e){e.preventDefault(),b.isBeginning&&!b.params.loop||b.slidePrev()},b.onClickIndex=function(e){e.preventDefault();var t=a(this).index()*b.params.slidesPerGroup;b.params.loop&&(t+=b.loopedSlides),b.slideTo(t)},b.updateClickedSlide=function(e){var t=o(e,"."+b.params.slideClass),s=!1;if(t)for(var i=0;i<b.slides.length;i++)b.slides[i]===t&&(s=!0);if(!t||!s)return b.clickedSlide=void 0,void(b.clickedIndex=void 0);if(b.clickedSlide=t,b.clickedIndex=a(t).index(),b.params.slideToClickedSlide&&void 0!==b.clickedIndex&&b.clickedIndex!==b.activeIndex){var r,n=b.clickedIndex,l="auto"===b.params.slidesPerView?b.currentSlidesPerView():b.params.slidesPerView;if(b.params.loop){if(b.animating)return;r=parseInt(a(b.clickedSlide).attr("data-swiper-slide-index"),10),b.params.centeredSlides?n<b.loopedSlides-l/2||n>b.slides.length-b.loopedSlides+l/2?(b.fixLoop(),n=b.wrapper.children("."+b.params.slideClass+'[data-swiper-slide-index="'+r+'"]:not(.'+b.params.slideDuplicateClass+")").eq(0).index(),setTimeout(function(){b.slideTo(n)},0)):b.slideTo(n):n>b.slides.length-l?(b.fixLoop(),n=b.wrapper.children("."+b.params.slideClass+'[data-swiper-slide-index="'+r+'"]:not(.'+b.params.slideDuplicateClass+")").eq(0).index(),setTimeout(function(){b.slideTo(n)},0)):b.slideTo(n)}else b.slideTo(n)}};var C,z,M,E,P,I,k,L,D,B,H="input, select, textarea, button, video",G=Date.now(),X=[];b.animating=!1,b.touches={startX:0,startY:0,currentX:0,currentY:0,diff:0};var Y,A;b.onTouchStart=function(e){if(e.originalEvent&&(e=e.originalEvent),Y="touchstart"===e.type,Y||!("which"in e)||3!==e.which){if(b.params.noSwiping&&o(e,"."+b.params.noSwipingClass))return void(b.allowClick=!0);if(!b.params.swipeHandler||o(e,b.params.swipeHandler)){var t=b.touches.currentX="touchstart"===e.type?e.targetTouches[0].pageX:e.pageX,s=b.touches.currentY="touchstart"===e.type?e.targetTouches[0].pageY:e.pageY;if(!(b.device.ios&&b.params.iOSEdgeSwipeDetection&&t<=b.params.iOSEdgeSwipeThreshold)){if(C=!0,z=!1,M=!0,P=void 0,A=void 0,b.touches.startX=t,b.touches.startY=s,E=Date.now(),b.allowClick=!0,b.updateContainerSize(),b.swipeDirection=void 0,b.params.threshold>0&&(L=!1),"touchstart"!==e.type){var i=!0;a(e.target).is(H)&&(i=!1),document.activeElement&&a(document.activeElement).is(H)&&document.activeElement.blur(),i&&e.preventDefault()}b.emit("onTouchStart",b,e)}}}},b.onTouchMove=function(e){if(e.originalEvent&&(e=e.originalEvent),!Y||"mousemove"!==e.type){if(e.preventedByNestedSwiper)return b.touches.startX="touchmove"===e.type?e.targetTouches[0].pageX:e.pageX,void(b.touches.startY="touchmove"===e.type?e.targetTouches[0].pageY:e.pageY);if(b.params.onlyExternal)return b.allowClick=!1,void(C&&(b.touches.startX=b.touches.currentX="touchmove"===e.type?e.targetTouches[0].pageX:e.pageX,b.touches.startY=b.touches.currentY="touchmove"===e.type?e.targetTouches[0].pageY:e.pageY,E=Date.now()));if(Y&&b.params.touchReleaseOnEdges&&!b.params.loop)if(b.isHorizontal()){if(b.touches.currentX<b.touches.startX&&b.translate<=b.maxTranslate()||b.touches.currentX>b.touches.startX&&b.translate>=b.minTranslate())return}else if(b.touches.currentY<b.touches.startY&&b.translate<=b.maxTranslate()||b.touches.currentY>b.touches.startY&&b.translate>=b.minTranslate())return;if(Y&&document.activeElement&&e.target===document.activeElement&&a(e.target).is(H))return z=!0,void(b.allowClick=!1);if(M&&b.emit("onTouchMove",b,e),!(e.targetTouches&&e.targetTouches.length>1)){if(b.touches.currentX="touchmove"===e.type?e.targetTouches[0].pageX:e.pageX,b.touches.currentY="touchmove"===e.type?e.targetTouches[0].pageY:e.pageY,"undefined"==typeof P){var t;b.isHorizontal()&&b.touches.currentY===b.touches.startY||!b.isHorizontal()&&b.touches.currentX===b.touches.startX?P=!1:(t=180*Math.atan2(Math.abs(b.touches.currentY-b.touches.startY),Math.abs(b.touches.currentX-b.touches.startX))/Math.PI,P=b.isHorizontal()?t>b.params.touchAngle:90-t>b.params.touchAngle)}if(P&&b.emit("onTouchMoveOpposite",b,e),"undefined"==typeof A&&b.browser.ieTouch&&(b.touches.currentX===b.touches.startX&&b.touches.currentY===b.touches.startY||(A=!0)),C){if(P)return void(C=!1);if(A||!b.browser.ieTouch){b.allowClick=!1,b.emit("onSliderMove",b,e),e.preventDefault(),b.params.touchMoveStopPropagation&&!b.params.nested&&e.stopPropagation(),z||(i.loop&&b.fixLoop(),k=b.getWrapperTranslate(),b.setWrapperTransition(0),b.animating&&b.wrapper.trigger("webkitTransitionEnd transitionend oTransitionEnd MSTransitionEnd msTransitionEnd"),b.params.autoplay&&b.autoplaying&&(b.params.autoplayDisableOnInteraction?b.stopAutoplay():b.pauseAutoplay()),B=!1,!b.params.grabCursor||b.params.allowSwipeToNext!==!0&&b.params.allowSwipeToPrev!==!0||b.setGrabCursor(!0)),z=!0;var s=b.touches.diff=b.isHorizontal()?b.touches.currentX-b.touches.startX:b.touches.currentY-b.touches.startY;s*=b.params.touchRatio,b.rtl&&(s=-s),b.swipeDirection=s>0?"prev":"next",I=s+k;var r=!0;if(s>0&&I>b.minTranslate()?(r=!1,b.params.resistance&&(I=b.minTranslate()-1+Math.pow(-b.minTranslate()+k+s,b.params.resistanceRatio))):s<0&&I<b.maxTranslate()&&(r=!1,b.params.resistance&&(I=b.maxTranslate()+1-Math.pow(b.maxTranslate()-k-s,b.params.resistanceRatio))),r&&(e.preventedByNestedSwiper=!0),!b.params.allowSwipeToNext&&"next"===b.swipeDirection&&I<k&&(I=k),!b.params.allowSwipeToPrev&&"prev"===b.swipeDirection&&I>k&&(I=k),b.params.threshold>0){if(!(Math.abs(s)>b.params.threshold||L))return void(I=k);if(!L)return L=!0,b.touches.startX=b.touches.currentX,b.touches.startY=b.touches.currentY,I=k,void(b.touches.diff=b.isHorizontal()?b.touches.currentX-b.touches.startX:b.touches.currentY-b.touches.startY)}b.params.followFinger&&((b.params.freeMode||b.params.watchSlidesProgress)&&b.updateActiveIndex(),b.params.freeMode&&(0===X.length&&X.push({position:b.touches[b.isHorizontal()?"startX":"startY"],time:E}),X.push({position:b.touches[b.isHorizontal()?"currentX":"currentY"],time:(new window.Date).getTime()})),b.updateProgress(I),b.setWrapperTranslate(I))}}}}},b.onTouchEnd=function(e){if(e.originalEvent&&(e=e.originalEvent),M&&b.emit("onTouchEnd",b,e),M=!1,C){b.params.grabCursor&&z&&C&&(b.params.allowSwipeToNext===!0||b.params.allowSwipeToPrev===!0)&&b.setGrabCursor(!1);var t=Date.now(),s=t-E;if(b.allowClick&&(b.updateClickedSlide(e),b.emit("onTap",b,e),s<300&&t-G>300&&(D&&clearTimeout(D),D=setTimeout(function(){b&&(b.params.paginationHide&&b.paginationContainer.length>0&&!a(e.target).hasClass(b.params.bulletClass)&&b.paginationContainer.toggleClass(b.params.paginationHiddenClass),b.emit("onClick",b,e))},300)),s<300&&t-G<300&&(D&&clearTimeout(D),b.emit("onDoubleTap",b,e))),G=Date.now(),setTimeout(function(){b&&(b.allowClick=!0)},0),!C||!z||!b.swipeDirection||0===b.touches.diff||I===k)return void(C=z=!1);C=z=!1;var i;if(i=b.params.followFinger?b.rtl?b.translate:-b.translate:-I,b.params.freeMode){if(i<-b.minTranslate())return void b.slideTo(b.activeIndex);if(i>-b.maxTranslate())return void(b.slides.length<b.snapGrid.length?b.slideTo(b.snapGrid.length-1):b.slideTo(b.slides.length-1));if(b.params.freeModeMomentum){if(X.length>1){var r=X.pop(),n=X.pop(),o=r.position-n.position,l=r.time-n.time;b.velocity=o/l,b.velocity=b.velocity/2,Math.abs(b.velocity)<b.params.freeModeMinimumVelocity&&(b.velocity=0),(l>150||(new window.Date).getTime()-r.time>300)&&(b.velocity=0)}else b.velocity=0;b.velocity=b.velocity*b.params.freeModeMomentumVelocityRatio,X.length=0;var p=1e3*b.params.freeModeMomentumRatio,d=b.velocity*p,u=b.translate+d;b.rtl&&(u=-u);var c,m=!1,h=20*Math.abs(b.velocity)*b.params.freeModeMomentumBounceRatio;if(u<b.maxTranslate())b.params.freeModeMomentumBounce?(u+b.maxTranslate()<-h&&(u=b.maxTranslate()-h),c=b.maxTranslate(),m=!0,B=!0):u=b.maxTranslate();else if(u>b.minTranslate())b.params.freeModeMomentumBounce?(u-b.minTranslate()>h&&(u=b.minTranslate()+h),c=b.minTranslate(),m=!0,B=!0):u=b.minTranslate();else if(b.params.freeModeSticky){var g,f=0;for(f=0;f<b.snapGrid.length;f+=1)if(b.snapGrid[f]>-u){g=f;break}u=Math.abs(b.snapGrid[g]-u)<Math.abs(b.snapGrid[g-1]-u)||"next"===b.swipeDirection?b.snapGrid[g]:b.snapGrid[g-1],b.rtl||(u=-u)}if(0!==b.velocity)p=b.rtl?Math.abs((-u-b.translate)/b.velocity):Math.abs((u-b.translate)/b.velocity);else if(b.params.freeModeSticky)return void b.slideReset();b.params.freeModeMomentumBounce&&m?(b.updateProgress(c),b.setWrapperTransition(p),b.setWrapperTranslate(u),b.onTransitionStart(),b.animating=!0,b.wrapper.transitionEnd(function(){b&&B&&(b.emit("onMomentumBounce",b),b.setWrapperTransition(b.params.speed),b.setWrapperTranslate(c),b.wrapper.transitionEnd(function(){b&&b.onTransitionEnd()}))})):b.velocity?(b.updateProgress(u),b.setWrapperTransition(p),b.setWrapperTranslate(u),b.onTransitionStart(),b.animating||(b.animating=!0,b.wrapper.transitionEnd(function(){b&&b.onTransitionEnd()}))):b.updateProgress(u),b.updateActiveIndex()}return void((!b.params.freeModeMomentum||s>=b.params.longSwipesMs)&&(b.updateProgress(),b.updateActiveIndex()))}var v,w=0,y=b.slidesSizesGrid[0];for(v=0;v<b.slidesGrid.length;v+=b.params.slidesPerGroup)"undefined"!=typeof b.slidesGrid[v+b.params.slidesPerGroup]?i>=b.slidesGrid[v]&&i<b.slidesGrid[v+b.params.slidesPerGroup]&&(w=v,y=b.slidesGrid[v+b.params.slidesPerGroup]-b.slidesGrid[v]):i>=b.slidesGrid[v]&&(w=v,y=b.slidesGrid[b.slidesGrid.length-1]-b.slidesGrid[b.slidesGrid.length-2]);var x=(i-b.slidesGrid[w])/y;if(s>b.params.longSwipesMs){if(!b.params.longSwipes)return void b.slideTo(b.activeIndex);"next"===b.swipeDirection&&(x>=b.params.longSwipesRatio?b.slideTo(w+b.params.slidesPerGroup):b.slideTo(w)),"prev"===b.swipeDirection&&(x>1-b.params.longSwipesRatio?b.slideTo(w+b.params.slidesPerGroup):b.slideTo(w))}else{if(!b.params.shortSwipes)return void b.slideTo(b.activeIndex);"next"===b.swipeDirection&&b.slideTo(w+b.params.slidesPerGroup),"prev"===b.swipeDirection&&b.slideTo(w)}}},b._slideTo=function(e,a){return b.slideTo(e,a,!0,!0)},b.slideTo=function(e,a,t,s){"undefined"==typeof t&&(t=!0),"undefined"==typeof e&&(e=0),e<0&&(e=0),b.snapIndex=Math.floor(e/b.params.slidesPerGroup),b.snapIndex>=b.snapGrid.length&&(b.snapIndex=b.snapGrid.length-1);var i=-b.snapGrid[b.snapIndex];if(b.params.autoplay&&b.autoplaying&&(s||!b.params.autoplayDisableOnInteraction?b.pauseAutoplay(a):b.stopAutoplay()),b.updateProgress(i),b.params.normalizeSlideIndex)for(var r=0;r<b.slidesGrid.length;r++)-Math.floor(100*i)>=Math.floor(100*b.slidesGrid[r])&&(e=r);return!(!b.params.allowSwipeToNext&&i<b.translate&&i<b.minTranslate())&&(!(!b.params.allowSwipeToPrev&&i>b.translate&&i>b.maxTranslate()&&(b.activeIndex||0)!==e)&&("undefined"==typeof a&&(a=b.params.speed),b.previousIndex=b.activeIndex||0,b.activeIndex=e,b.updateRealIndex(),b.rtl&&-i===b.translate||!b.rtl&&i===b.translate?(b.params.autoHeight&&b.updateAutoHeight(),b.updateClasses(),"slide"!==b.params.effect&&b.setWrapperTranslate(i),!1):(b.updateClasses(),b.onTransitionStart(t),0===a||b.browser.lteIE9?(b.setWrapperTranslate(i),b.setWrapperTransition(0),b.onTransitionEnd(t)):(b.setWrapperTranslate(i),b.setWrapperTransition(a),b.animating||(b.animating=!0,b.wrapper.transitionEnd(function(){b&&b.onTransitionEnd(t)}))),!0)))},b.onTransitionStart=function(e){"undefined"==typeof e&&(e=!0),b.params.autoHeight&&b.updateAutoHeight(),b.lazy&&b.lazy.onTransitionStart(),e&&(b.emit("onTransitionStart",b),b.activeIndex!==b.previousIndex&&(b.emit("onSlideChangeStart",b),b.activeIndex>b.previousIndex?b.emit("onSlideNextStart",b):b.emit("onSlidePrevStart",b)))},b.onTransitionEnd=function(e){b.animating=!1,b.setWrapperTransition(0),"undefined"==typeof e&&(e=!0),b.lazy&&b.lazy.onTransitionEnd(),e&&(b.emit("onTransitionEnd",b),b.activeIndex!==b.previousIndex&&(b.emit("onSlideChangeEnd",b),b.activeIndex>b.previousIndex?b.emit("onSlideNextEnd",b):b.emit("onSlidePrevEnd",b))),b.params.history&&b.history&&b.history.setHistory(b.params.history,b.activeIndex),b.params.hashnav&&b.hashnav&&b.hashnav.setHash()},b.slideNext=function(e,a,t){if(b.params.loop){if(b.animating)return!1;b.fixLoop();b.container[0].clientLeft;return b.slideTo(b.activeIndex+b.params.slidesPerGroup,a,e,t)}return b.slideTo(b.activeIndex+b.params.slidesPerGroup,a,e,t)},b._slideNext=function(e){return b.slideNext(!0,e,!0)},b.slidePrev=function(e,a,t){if(b.params.loop){if(b.animating)return!1;b.fixLoop();b.container[0].clientLeft;return b.slideTo(b.activeIndex-1,a,e,t)}return b.slideTo(b.activeIndex-1,a,e,t)},b._slidePrev=function(e){return b.slidePrev(!0,e,!0)},b.slideReset=function(e,a,t){return b.slideTo(b.activeIndex,a,e)},b.disableTouchControl=function(){return b.params.onlyExternal=!0,!0},b.enableTouchControl=function(){return b.params.onlyExternal=!1,!0},b.setWrapperTransition=function(e,a){b.wrapper.transition(e),"slide"!==b.params.effect&&b.effects[b.params.effect]&&b.effects[b.params.effect].setTransition(e),b.params.parallax&&b.parallax&&b.parallax.setTransition(e),b.params.scrollbar&&b.scrollbar&&b.scrollbar.setTransition(e),b.params.control&&b.controller&&b.controller.setTransition(e,a),b.emit("onSetTransition",b,e)},b.setWrapperTranslate=function(e,a,t){var s=0,i=0,n=0;b.isHorizontal()?s=b.rtl?-e:e:i=e,b.params.roundLengths&&(s=r(s),i=r(i)),b.params.virtualTranslate||(b.support.transforms3d?b.wrapper.transform("translate3d("+s+"px, "+i+"px, "+n+"px)"):b.wrapper.transform("translate("+s+"px, "+i+"px)")),b.translate=b.isHorizontal()?s:i;var o,l=b.maxTranslate()-b.minTranslate();o=0===l?0:(e-b.minTranslate())/l,o!==b.progress&&b.updateProgress(e),a&&b.updateActiveIndex(),"slide"!==b.params.effect&&b.effects[b.params.effect]&&b.effects[b.params.effect].setTranslate(b.translate),b.params.parallax&&b.parallax&&b.parallax.setTranslate(b.translate),b.params.scrollbar&&b.scrollbar&&b.scrollbar.setTranslate(b.translate),b.params.control&&b.controller&&b.controller.setTranslate(b.translate,t),b.emit("onSetTranslate",b,b.translate)},b.getTranslate=function(e,a){var t,s,i,r;return"undefined"==typeof a&&(a="x"),b.params.virtualTranslate?b.rtl?-b.translate:b.translate:(i=window.getComputedStyle(e,null),window.WebKitCSSMatrix?(s=i.transform||i.webkitTransform,s.split(",").length>6&&(s=s.split(", ").map(function(e){return e.replace(",",".")}).join(", ")),r=new window.WebKitCSSMatrix("none"===s?"":s)):(r=i.MozTransform||i.OTransform||i.MsTransform||i.msTransform||i.transform||i.getPropertyValue("transform").replace("translate(","matrix(1, 0, 0, 1,"),t=r.toString().split(",")),"x"===a&&(s=window.WebKitCSSMatrix?r.m41:16===t.length?parseFloat(t[12]):parseFloat(t[4])),"y"===a&&(s=window.WebKitCSSMatrix?r.m42:16===t.length?parseFloat(t[13]):parseFloat(t[5])),b.rtl&&s&&(s=-s),s||0)},b.getWrapperTranslate=function(e){return"undefined"==typeof e&&(e=b.isHorizontal()?"x":"y"),b.getTranslate(b.wrapper[0],e)},b.observers=[],b.initObservers=function(){if(b.params.observeParents)for(var e=b.container.parents(),a=0;a<e.length;a++)l(e[a]);l(b.container[0],{childList:!1}),l(b.wrapper[0],{attributes:!1})},b.disconnectObservers=function(){for(var e=0;e<b.observers.length;e++)b.observers[e].disconnect();b.observers=[]},b.createLoop=function(){b.wrapper.children("."+b.params.slideClass+"."+b.params.slideDuplicateClass).remove();var e=b.wrapper.children("."+b.params.slideClass);"auto"!==b.params.slidesPerView||b.params.loopedSlides||(b.params.loopedSlides=e.length),b.loopedSlides=parseInt(b.params.loopedSlides||b.params.slidesPerView,10),b.loopedSlides=b.loopedSlides+b.params.loopAdditionalSlides,b.loopedSlides>e.length&&(b.loopedSlides=e.length);var t,s=[],i=[];for(e.each(function(t,r){var n=a(this);t<b.loopedSlides&&i.push(r),t<e.length&&t>=e.length-b.loopedSlides&&s.push(r),n.attr("data-swiper-slide-index",t)}),t=0;t<i.length;t++)b.wrapper.append(a(i[t].cloneNode(!0)).addClass(b.params.slideDuplicateClass));for(t=s.length-1;t>=0;t--)b.wrapper.prepend(a(s[t].cloneNode(!0)).addClass(b.params.slideDuplicateClass))},b.destroyLoop=function(){b.wrapper.children("."+b.params.slideClass+"."+b.params.slideDuplicateClass).remove(),b.slides.removeAttr("data-swiper-slide-index")},b.reLoop=function(e){var a=b.activeIndex-b.loopedSlides;b.destroyLoop(),b.createLoop(),b.updateSlidesSize(),e&&b.slideTo(a+b.loopedSlides,0,!1)},b.fixLoop=function(){var e;b.activeIndex<b.loopedSlides?(e=b.slides.length-3*b.loopedSlides+b.activeIndex,e+=b.loopedSlides,b.slideTo(e,0,!1,!0)):("auto"===b.params.slidesPerView&&b.activeIndex>=2*b.loopedSlides||b.activeIndex>b.slides.length-2*b.params.slidesPerView)&&(e=-b.slides.length+b.activeIndex+b.loopedSlides,e+=b.loopedSlides,b.slideTo(e,0,!1,!0))},b.appendSlide=function(e){if(b.params.loop&&b.destroyLoop(),"object"==typeof e&&e.length)for(var a=0;a<e.length;a++)e[a]&&b.wrapper.append(e[a]);else b.wrapper.append(e);b.params.loop&&b.createLoop(),b.params.observer&&b.support.observer||b.update(!0)},b.prependSlide=function(e){b.params.loop&&b.destroyLoop();var a=b.activeIndex+1;if("object"==typeof e&&e.length){for(var t=0;t<e.length;t++)e[t]&&b.wrapper.prepend(e[t]);a=b.activeIndex+e.length}else b.wrapper.prepend(e);b.params.loop&&b.createLoop(),b.params.observer&&b.support.observer||b.update(!0),b.slideTo(a,0,!1)},b.removeSlide=function(e){b.params.loop&&(b.destroyLoop(),b.slides=b.wrapper.children("."+b.params.slideClass));var a,t=b.activeIndex;if("object"==typeof e&&e.length){for(var s=0;s<e.length;s++)a=e[s],b.slides[a]&&b.slides.eq(a).remove(),a<t&&t--;t=Math.max(t,0)}else a=e,b.slides[a]&&b.slides.eq(a).remove(),a<t&&t--,t=Math.max(t,0);b.params.loop&&b.createLoop(),b.params.observer&&b.support.observer||b.update(!0),b.params.loop?b.slideTo(t+b.loopedSlides,0,!1):b.slideTo(t,0,!1)},b.removeAllSlides=function(){for(var e=[],a=0;a<b.slides.length;a++)e.push(a);b.removeSlide(e)},b.effects={fade:{setTranslate:function(){for(var e=0;e<b.slides.length;e++){var a=b.slides.eq(e),t=a[0].swiperSlideOffset,s=-t;b.params.virtualTranslate||(s-=b.translate);var i=0;b.isHorizontal()||(i=s,s=0);var r=b.params.fade.crossFade?Math.max(1-Math.abs(a[0].progress),0):1+Math.min(Math.max(a[0].progress,-1),0);a.css({opacity:r}).transform("translate3d("+s+"px, "+i+"px, 0px)")}},setTransition:function(e){if(b.slides.transition(e),b.params.virtualTranslate&&0!==e){var a=!1;b.slides.transitionEnd(function(){if(!a&&b){a=!0,b.animating=!1;for(var e=["webkitTransitionEnd","transitionend","oTransitionEnd","MSTransitionEnd","msTransitionEnd"],t=0;t<e.length;t++)b.wrapper.trigger(e[t])}})}}},flip:{setTranslate:function(){for(var e=0;e<b.slides.length;e++){var t=b.slides.eq(e),s=t[0].progress;b.params.flip.limitRotation&&(s=Math.max(Math.min(t[0].progress,1),-1));var i=t[0].swiperSlideOffset,r=-180*s,n=r,o=0,l=-i,p=0;if(b.isHorizontal()?b.rtl&&(n=-n):(p=l,l=0,o=-n,n=0),t[0].style.zIndex=-Math.abs(Math.round(s))+b.slides.length,b.params.flip.slideShadows){var d=b.isHorizontal()?t.find(".swiper-slide-shadow-left"):t.find(".swiper-slide-shadow-top"),u=b.isHorizontal()?t.find(".swiper-slide-shadow-right"):t.find(".swiper-slide-shadow-bottom");0===d.length&&(d=a('<div class="swiper-slide-shadow-'+(b.isHorizontal()?"left":"top")+'"></div>'),t.append(d)),0===u.length&&(u=a('<div class="swiper-slide-shadow-'+(b.isHorizontal()?"right":"bottom")+'"></div>'),t.append(u)),d.length&&(d[0].style.opacity=Math.max(-s,0)),u.length&&(u[0].style.opacity=Math.max(s,0))}t.transform("translate3d("+l+"px, "+p+"px, 0px) rotateX("+o+"deg) rotateY("+n+"deg)")}},setTransition:function(e){if(b.slides.transition(e).find(".swiper-slide-shadow-top, .swiper-slide-shadow-right, .swiper-slide-shadow-bottom, .swiper-slide-shadow-left").transition(e),b.params.virtualTranslate&&0!==e){var t=!1;b.slides.eq(b.activeIndex).transitionEnd(function(){if(!t&&b&&a(this).hasClass(b.params.slideActiveClass)){t=!0,b.animating=!1;for(var e=["webkitTransitionEnd","transitionend","oTransitionEnd","MSTransitionEnd","msTransitionEnd"],s=0;s<e.length;s++)b.wrapper.trigger(e[s])}})}}},cube:{setTranslate:function(){var e,t=0;b.params.cube.shadow&&(b.isHorizontal()?(e=b.wrapper.find(".swiper-cube-shadow"),0===e.length&&(e=a('<div class="swiper-cube-shadow"></div>'),b.wrapper.append(e)),e.css({height:b.width+"px"})):(e=b.container.find(".swiper-cube-shadow"),0===e.length&&(e=a('<div class="swiper-cube-shadow"></div>'),b.container.append(e))));for(var s=0;s<b.slides.length;s++){var i=b.slides.eq(s),r=90*s,n=Math.floor(r/360);b.rtl&&(r=-r,n=Math.floor(-r/360));var o=Math.max(Math.min(i[0].progress,1),-1),l=0,p=0,d=0;s%4===0?(l=4*-n*b.size,d=0):(s-1)%4===0?(l=0,d=4*-n*b.size):(s-2)%4===0?(l=b.size+4*n*b.size,d=b.size):(s-3)%4===0&&(l=-b.size,d=3*b.size+4*b.size*n),b.rtl&&(l=-l),b.isHorizontal()||(p=l,l=0);var u="rotateX("+(b.isHorizontal()?0:-r)+"deg) rotateY("+(b.isHorizontal()?r:0)+"deg) translate3d("+l+"px, "+p+"px, "+d+"px)";if(o<=1&&o>-1&&(t=90*s+90*o,b.rtl&&(t=90*-s-90*o)),i.transform(u),b.params.cube.slideShadows){var c=b.isHorizontal()?i.find(".swiper-slide-shadow-left"):i.find(".swiper-slide-shadow-top"),m=b.isHorizontal()?i.find(".swiper-slide-shadow-right"):i.find(".swiper-slide-shadow-bottom");0===c.length&&(c=a('<div class="swiper-slide-shadow-'+(b.isHorizontal()?"left":"top")+'"></div>'),i.append(c)),0===m.length&&(m=a('<div class="swiper-slide-shadow-'+(b.isHorizontal()?"right":"bottom")+'"></div>'),i.append(m)),c.length&&(c[0].style.opacity=Math.max(-o,0)),m.length&&(m[0].style.opacity=Math.max(o,0))}}if(b.wrapper.css({"-webkit-transform-origin":"50% 50% -"+b.size/2+"px","-moz-transform-origin":"50% 50% -"+b.size/2+"px","-ms-transform-origin":"50% 50% -"+b.size/2+"px","transform-origin":"50% 50% -"+b.size/2+"px"}),b.params.cube.shadow)if(b.isHorizontal())e.transform("translate3d(0px, "+(b.width/2+b.params.cube.shadowOffset)+"px, "+-b.width/2+"px) rotateX(90deg) rotateZ(0deg) scale("+b.params.cube.shadowScale+")");else{var h=Math.abs(t)-90*Math.floor(Math.abs(t)/90),g=1.5-(Math.sin(2*h*Math.PI/360)/2+Math.cos(2*h*Math.PI/360)/2),f=b.params.cube.shadowScale,v=b.params.cube.shadowScale/g,w=b.params.cube.shadowOffset;e.transform("scale3d("+f+", 1, "+v+") translate3d(0px, "+(b.height/2+w)+"px, "+-b.height/2/v+"px) rotateX(-90deg)")}var y=b.isSafari||b.isUiWebView?-b.size/2:0;b.wrapper.transform("translate3d(0px,0,"+y+"px) rotateX("+(b.isHorizontal()?0:t)+"deg) rotateY("+(b.isHorizontal()?-t:0)+"deg)")},setTransition:function(e){b.slides.transition(e).find(".swiper-slide-shadow-top, .swiper-slide-shadow-right, .swiper-slide-shadow-bottom, .swiper-slide-shadow-left").transition(e),b.params.cube.shadow&&!b.isHorizontal()&&b.container.find(".swiper-cube-shadow").transition(e)}},coverflow:{setTranslate:function(){for(var e=b.translate,t=b.isHorizontal()?-e+b.width/2:-e+b.height/2,s=b.isHorizontal()?b.params.coverflow.rotate:-b.params.coverflow.rotate,i=b.params.coverflow.depth,r=0,n=b.slides.length;r<n;r++){var o=b.slides.eq(r),l=b.slidesSizesGrid[r],p=o[0].swiperSlideOffset,d=(t-p-l/2)/l*b.params.coverflow.modifier,u=b.isHorizontal()?s*d:0,c=b.isHorizontal()?0:s*d,m=-i*Math.abs(d),h=b.isHorizontal()?0:b.params.coverflow.stretch*d,g=b.isHorizontal()?b.params.coverflow.stretch*d:0;Math.abs(g)<.001&&(g=0),Math.abs(h)<.001&&(h=0),Math.abs(m)<.001&&(m=0),Math.abs(u)<.001&&(u=0),Math.abs(c)<.001&&(c=0);var f="translate3d("+g+"px,"+h+"px,"+m+"px)  rotateX("+c+"deg) rotateY("+u+"deg)";if(o.transform(f),o[0].style.zIndex=-Math.abs(Math.round(d))+1,b.params.coverflow.slideShadows){var v=b.isHorizontal()?o.find(".swiper-slide-shadow-left"):o.find(".swiper-slide-shadow-top"),w=b.isHorizontal()?o.find(".swiper-slide-shadow-right"):o.find(".swiper-slide-shadow-bottom");0===v.length&&(v=a('<div class="swiper-slide-shadow-'+(b.isHorizontal()?"left":"top")+'"></div>'),o.append(v)),0===w.length&&(w=a('<div class="swiper-slide-shadow-'+(b.isHorizontal()?"right":"bottom")+'"></div>'),o.append(w)),v.length&&(v[0].style.opacity=d>0?d:0),w.length&&(w[0].style.opacity=-d>0?-d:0)}}if(b.browser.ie){var y=b.wrapper[0].style;y.perspectiveOrigin=t+"px 50%"}},setTransition:function(e){b.slides.transition(e).find(".swiper-slide-shadow-top, .swiper-slide-shadow-right, .swiper-slide-shadow-bottom, .swiper-slide-shadow-left").transition(e)}}},b.lazy={initialImageLoaded:!1,loadImageInSlide:function(e,t){if("undefined"!=typeof e&&("undefined"==typeof t&&(t=!0),0!==b.slides.length)){var s=b.slides.eq(e),i=s.find("."+b.params.lazyLoadingClass+":not(."+b.params.lazyStatusLoadedClass+"):not(."+b.params.lazyStatusLoadingClass+")");!s.hasClass(b.params.lazyLoadingClass)||s.hasClass(b.params.lazyStatusLoadedClass)||s.hasClass(b.params.lazyStatusLoadingClass)||(i=i.add(s[0])),0!==i.length&&i.each(function(){var e=a(this);e.addClass(b.params.lazyStatusLoadingClass);var i=e.attr("data-background"),r=e.attr("data-src"),n=e.attr("data-srcset"),o=e.attr("data-sizes");b.loadImage(e[0],r||i,n,o,!1,function(){if(i?(e.css("background-image",'url("'+i+'")'),e.removeAttr("data-background")):(n&&(e.attr("srcset",n),e.removeAttr("data-srcset")),o&&(e.attr("sizes",o),e.removeAttr("data-sizes")),r&&(e.attr("src",r),e.removeAttr("data-src"))),e.addClass(b.params.lazyStatusLoadedClass).removeClass(b.params.lazyStatusLoadingClass),s.find("."+b.params.lazyPreloaderClass+", ."+b.params.preloaderClass).remove(),b.params.loop&&t){var a=s.attr("data-swiper-slide-index");if(s.hasClass(b.params.slideDuplicateClass)){var l=b.wrapper.children('[data-swiper-slide-index="'+a+'"]:not(.'+b.params.slideDuplicateClass+")");b.lazy.loadImageInSlide(l.index(),!1)}else{var p=b.wrapper.children("."+b.params.slideDuplicateClass+'[data-swiper-slide-index="'+a+'"]');b.lazy.loadImageInSlide(p.index(),!1)}}b.emit("onLazyImageReady",b,s[0],e[0])}),b.emit("onLazyImageLoad",b,s[0],e[0])})}},load:function(){var e,t=b.params.slidesPerView;if("auto"===t&&(t=0),b.lazy.initialImageLoaded||(b.lazy.initialImageLoaded=!0),b.params.watchSlidesVisibility)b.wrapper.children("."+b.params.slideVisibleClass).each(function(){b.lazy.loadImageInSlide(a(this).index())});else if(t>1)for(e=b.activeIndex;e<b.activeIndex+t;e++)b.slides[e]&&b.lazy.loadImageInSlide(e);else b.lazy.loadImageInSlide(b.activeIndex);if(b.params.lazyLoadingInPrevNext)if(t>1||b.params.lazyLoadingInPrevNextAmount&&b.params.lazyLoadingInPrevNextAmount>1){var s=b.params.lazyLoadingInPrevNextAmount,i=t,r=Math.min(b.activeIndex+i+Math.max(s,i),b.slides.length),n=Math.max(b.activeIndex-Math.max(i,s),0);for(e=b.activeIndex+t;e<r;e++)b.slides[e]&&b.lazy.loadImageInSlide(e);for(e=n;e<b.activeIndex;e++)b.slides[e]&&b.lazy.loadImageInSlide(e)}else{var o=b.wrapper.children("."+b.params.slideNextClass);o.length>0&&b.lazy.loadImageInSlide(o.index());var l=b.wrapper.children("."+b.params.slidePrevClass);l.length>0&&b.lazy.loadImageInSlide(l.index())}},onTransitionStart:function(){b.params.lazyLoading&&(b.params.lazyLoadingOnTransitionStart||!b.params.lazyLoadingOnTransitionStart&&!b.lazy.initialImageLoaded)&&b.lazy.load()},onTransitionEnd:function(){b.params.lazyLoading&&!b.params.lazyLoadingOnTransitionStart&&b.lazy.load()}},b.scrollbar={isTouched:!1,setDragPosition:function(e){var a=b.scrollbar,t=b.isHorizontal()?"touchstart"===e.type||"touchmove"===e.type?e.targetTouches[0].pageX:e.pageX||e.clientX:"touchstart"===e.type||"touchmove"===e.type?e.targetTouches[0].pageY:e.pageY||e.clientY,s=t-a.track.offset()[b.isHorizontal()?"left":"top"]-a.dragSize/2,i=-b.minTranslate()*a.moveDivider,r=-b.maxTranslate()*a.moveDivider;s<i?s=i:s>r&&(s=r),s=-s/a.moveDivider,b.updateProgress(s),b.setWrapperTranslate(s,!0)},dragStart:function(e){var a=b.scrollbar;a.isTouched=!0,e.preventDefault(),e.stopPropagation(),a.setDragPosition(e),clearTimeout(a.dragTimeout),a.track.transition(0),b.params.scrollbarHide&&a.track.css("opacity",1),b.wrapper.transition(100),a.drag.transition(100),b.emit("onScrollbarDragStart",b)},dragMove:function(e){var a=b.scrollbar;a.isTouched&&(e.preventDefault?e.preventDefault():e.returnValue=!1,a.setDragPosition(e),b.wrapper.transition(0),a.track.transition(0),a.drag.transition(0),b.emit("onScrollbarDragMove",b))},dragEnd:function(e){var a=b.scrollbar;a.isTouched&&(a.isTouched=!1,b.params.scrollbarHide&&(clearTimeout(a.dragTimeout),a.dragTimeout=setTimeout(function(){a.track.css("opacity",0),a.track.transition(400)},1e3)),b.emit("onScrollbarDragEnd",b),b.params.scrollbarSnapOnRelease&&b.slideReset())},draggableEvents:function(){return b.params.simulateTouch!==!1||b.support.touch?b.touchEvents:b.touchEventsDesktop}(),enableDraggable:function(){var e=b.scrollbar,t=b.support.touch?e.track:document;a(e.track).on(e.draggableEvents.start,e.dragStart),a(t).on(e.draggableEvents.move,e.dragMove),a(t).on(e.draggableEvents.end,e.dragEnd)},disableDraggable:function(){var e=b.scrollbar,t=b.support.touch?e.track:document;a(e.track).off(e.draggableEvents.start,e.dragStart),a(t).off(e.draggableEvents.move,e.dragMove),a(t).off(e.draggableEvents.end,e.dragEnd)},set:function(){if(b.params.scrollbar){var e=b.scrollbar;e.track=a(b.params.scrollbar),b.params.uniqueNavElements&&"string"==typeof b.params.scrollbar&&e.track.length>1&&1===b.container.find(b.params.scrollbar).length&&(e.track=b.container.find(b.params.scrollbar)),e.drag=e.track.find(".swiper-scrollbar-drag"),0===e.drag.length&&(e.drag=a('<div class="swiper-scrollbar-drag"></div>'),e.track.append(e.drag)),e.drag[0].style.width="",e.drag[0].style.height="",e.trackSize=b.isHorizontal()?e.track[0].offsetWidth:e.track[0].offsetHeight,e.divider=b.size/b.virtualSize,e.moveDivider=e.divider*(e.trackSize/b.size),e.dragSize=e.trackSize*e.divider,b.isHorizontal()?e.drag[0].style.width=e.dragSize+"px":e.drag[0].style.height=e.dragSize+"px",e.divider>=1?e.track[0].style.display="none":e.track[0].style.display="",b.params.scrollbarHide&&(e.track[0].style.opacity=0)}},setTranslate:function(){if(b.params.scrollbar){var e,a=b.scrollbar,t=(b.translate||0,a.dragSize);e=(a.trackSize-a.dragSize)*b.progress,b.rtl&&b.isHorizontal()?(e=-e,e>0?(t=a.dragSize-e,e=0):-e+a.dragSize>a.trackSize&&(t=a.trackSize+e)):e<0?(t=a.dragSize+e,e=0):e+a.dragSize>a.trackSize&&(t=a.trackSize-e),b.isHorizontal()?(b.support.transforms3d?a.drag.transform("translate3d("+e+"px, 0, 0)"):a.drag.transform("translateX("+e+"px)"),a.drag[0].style.width=t+"px"):(b.support.transforms3d?a.drag.transform("translate3d(0px, "+e+"px, 0)"):a.drag.transform("translateY("+e+"px)"),a.drag[0].style.height=t+"px"),b.params.scrollbarHide&&(clearTimeout(a.timeout),a.track[0].style.opacity=1,a.timeout=setTimeout(function(){a.track[0].style.opacity=0,a.track.transition(400)},1e3))}},setTransition:function(e){b.params.scrollbar&&b.scrollbar.drag.transition(e)}},b.controller={LinearSpline:function(e,a){this.x=e,this.y=a,this.lastIndex=e.length-1;var t,s;this.x.length;this.interpolate=function(e){return e?(s=i(this.x,e),t=s-1,(e-this.x[t])*(this.y[s]-this.y[t])/(this.x[s]-this.x[t])+this.y[t]):0};var i=function(){var e,a,t;return function(s,i){for(a=-1,e=s.length;e-a>1;)s[t=e+a>>1]<=i?a=t:e=t;return e}}()},getInterpolateFunction:function(e){b.controller.spline||(b.controller.spline=b.params.loop?new b.controller.LinearSpline(b.slidesGrid,e.slidesGrid):new b.controller.LinearSpline(b.snapGrid,e.snapGrid))},setTranslate:function(e,a){function s(a){e=a.rtl&&"horizontal"===a.params.direction?-b.translate:b.translate,"slide"===b.params.controlBy&&(b.controller.getInterpolateFunction(a),r=-b.controller.spline.interpolate(-e)),r&&"container"!==b.params.controlBy||(i=(a.maxTranslate()-a.minTranslate())/(b.maxTranslate()-b.minTranslate()),r=(e-b.minTranslate())*i+a.minTranslate()),b.params.controlInverse&&(r=a.maxTranslate()-r),a.updateProgress(r),a.setWrapperTranslate(r,!1,b),a.updateActiveIndex()}var i,r,n=b.params.control;if(b.isArray(n))for(var o=0;o<n.length;o++)n[o]!==a&&n[o]instanceof t&&s(n[o]);else n instanceof t&&a!==n&&s(n)},setTransition:function(e,a){function s(a){
a.setWrapperTransition(e,b),0!==e&&(a.onTransitionStart(),a.wrapper.transitionEnd(function(){r&&(a.params.loop&&"slide"===b.params.controlBy&&a.fixLoop(),a.onTransitionEnd())}))}var i,r=b.params.control;if(b.isArray(r))for(i=0;i<r.length;i++)r[i]!==a&&r[i]instanceof t&&s(r[i]);else r instanceof t&&a!==r&&s(r)}},b.hashnav={onHashCange:function(e,a){var t=document.location.hash.replace("#",""),s=b.slides.eq(b.activeIndex).attr("data-hash");t!==s&&b.slideTo(b.wrapper.children("."+b.params.slideClass+'[data-hash="'+t+'"]').index())},attachEvents:function(e){var t=e?"off":"on";a(window)[t]("hashchange",b.hashnav.onHashCange)},setHash:function(){if(b.hashnav.initialized&&b.params.hashnav)if(b.params.replaceState&&window.history&&window.history.replaceState)window.history.replaceState(null,null,"#"+b.slides.eq(b.activeIndex).attr("data-hash")||"");else{var e=b.slides.eq(b.activeIndex),a=e.attr("data-hash")||e.attr("data-history");document.location.hash=a||""}},init:function(){if(b.params.hashnav&&!b.params.history){b.hashnav.initialized=!0;var e=document.location.hash.replace("#","");if(e)for(var a=0,t=0,s=b.slides.length;t<s;t++){var i=b.slides.eq(t),r=i.attr("data-hash")||i.attr("data-history");if(r===e&&!i.hasClass(b.params.slideDuplicateClass)){var n=i.index();b.slideTo(n,a,b.params.runCallbacksOnInit,!0)}}b.params.hashnavWatchState&&b.hashnav.attachEvents()}},destroy:function(){b.params.hashnavWatchState&&b.hashnav.attachEvents(!0)}},b.history={init:function(){if(b.params.history){if(!window.history||!window.history.pushState)return b.params.history=!1,void(b.params.hashnav=!0);b.history.initialized=!0,this.paths=this.getPathValues(),(this.paths.key||this.paths.value)&&(this.scrollToSlide(0,this.paths.value,b.params.runCallbacksOnInit),b.params.replaceState||window.addEventListener("popstate",this.setHistoryPopState))}},setHistoryPopState:function(){b.history.paths=b.history.getPathValues(),b.history.scrollToSlide(b.params.speed,b.history.paths.value,!1)},getPathValues:function(){var e=window.location.pathname.slice(1).split("/"),a=e.length,t=e[a-2],s=e[a-1];return{key:t,value:s}},setHistory:function(e,a){if(b.history.initialized&&b.params.history){var t=b.slides.eq(a),s=this.slugify(t.attr("data-history"));window.location.pathname.includes(e)||(s=e+"/"+s),b.params.replaceState?window.history.replaceState(null,null,s):window.history.pushState(null,null,s)}},slugify:function(e){return e.toString().toLowerCase().replace(/\s+/g,"-").replace(/[^\w\-]+/g,"").replace(/\-\-+/g,"-").replace(/^-+/,"").replace(/-+$/,"")},scrollToSlide:function(e,a,t){if(a)for(var s=0,i=b.slides.length;s<i;s++){var r=b.slides.eq(s),n=this.slugify(r.attr("data-history"));if(n===a&&!r.hasClass(b.params.slideDuplicateClass)){var o=r.index();b.slideTo(o,e,t)}}else b.slideTo(0,e,t)}},b.disableKeyboardControl=function(){b.params.keyboardControl=!1,a(document).off("keydown",p)},b.enableKeyboardControl=function(){b.params.keyboardControl=!0,a(document).on("keydown",p)},b.mousewheel={event:!1,lastScrollTime:(new window.Date).getTime()},b.params.mousewheelControl&&(b.mousewheel.event=navigator.userAgent.indexOf("firefox")>-1?"DOMMouseScroll":d()?"wheel":"mousewheel"),b.disableMousewheelControl=function(){if(!b.mousewheel.event)return!1;var e=b.container;return"container"!==b.params.mousewheelEventsTarged&&(e=a(b.params.mousewheelEventsTarged)),e.off(b.mousewheel.event,u),!0},b.enableMousewheelControl=function(){if(!b.mousewheel.event)return!1;var e=b.container;return"container"!==b.params.mousewheelEventsTarged&&(e=a(b.params.mousewheelEventsTarged)),e.on(b.mousewheel.event,u),!0},b.parallax={setTranslate:function(){b.container.children("[data-swiper-parallax], [data-swiper-parallax-x], [data-swiper-parallax-y]").each(function(){m(this,b.progress)}),b.slides.each(function(){var e=a(this);e.find("[data-swiper-parallax], [data-swiper-parallax-x], [data-swiper-parallax-y]").each(function(){var a=Math.min(Math.max(e[0].progress,-1),1);m(this,a)})})},setTransition:function(e){"undefined"==typeof e&&(e=b.params.speed),b.container.find("[data-swiper-parallax], [data-swiper-parallax-x], [data-swiper-parallax-y]").each(function(){var t=a(this),s=parseInt(t.attr("data-swiper-parallax-duration"),10)||e;0===e&&(s=0),t.transition(s)})}},b.zoom={scale:1,currentScale:1,isScaling:!1,gesture:{slide:void 0,slideWidth:void 0,slideHeight:void 0,image:void 0,imageWrap:void 0,zoomMax:b.params.zoomMax},image:{isTouched:void 0,isMoved:void 0,currentX:void 0,currentY:void 0,minX:void 0,minY:void 0,maxX:void 0,maxY:void 0,width:void 0,height:void 0,startX:void 0,startY:void 0,touchesStart:{},touchesCurrent:{}},velocity:{x:void 0,y:void 0,prevPositionX:void 0,prevPositionY:void 0,prevTime:void 0},getDistanceBetweenTouches:function(e){if(e.targetTouches.length<2)return 1;var a=e.targetTouches[0].pageX,t=e.targetTouches[0].pageY,s=e.targetTouches[1].pageX,i=e.targetTouches[1].pageY,r=Math.sqrt(Math.pow(s-a,2)+Math.pow(i-t,2));return r},onGestureStart:function(e){var t=b.zoom;if(!b.support.gestures){if("touchstart"!==e.type||"touchstart"===e.type&&e.targetTouches.length<2)return;t.gesture.scaleStart=t.getDistanceBetweenTouches(e)}return t.gesture.slide&&t.gesture.slide.length||(t.gesture.slide=a(this),0===t.gesture.slide.length&&(t.gesture.slide=b.slides.eq(b.activeIndex)),t.gesture.image=t.gesture.slide.find("img, svg, canvas"),t.gesture.imageWrap=t.gesture.image.parent("."+b.params.zoomContainerClass),t.gesture.zoomMax=t.gesture.imageWrap.attr("data-swiper-zoom")||b.params.zoomMax,0!==t.gesture.imageWrap.length)?(t.gesture.image.transition(0),void(t.isScaling=!0)):void(t.gesture.image=void 0)},onGestureChange:function(e){var a=b.zoom;if(!b.support.gestures){if("touchmove"!==e.type||"touchmove"===e.type&&e.targetTouches.length<2)return;a.gesture.scaleMove=a.getDistanceBetweenTouches(e)}a.gesture.image&&0!==a.gesture.image.length&&(b.support.gestures?a.scale=e.scale*a.currentScale:a.scale=a.gesture.scaleMove/a.gesture.scaleStart*a.currentScale,a.scale>a.gesture.zoomMax&&(a.scale=a.gesture.zoomMax-1+Math.pow(a.scale-a.gesture.zoomMax+1,.5)),a.scale<b.params.zoomMin&&(a.scale=b.params.zoomMin+1-Math.pow(b.params.zoomMin-a.scale+1,.5)),a.gesture.image.transform("translate3d(0,0,0) scale("+a.scale+")"))},onGestureEnd:function(e){var a=b.zoom;!b.support.gestures&&("touchend"!==e.type||"touchend"===e.type&&e.changedTouches.length<2)||a.gesture.image&&0!==a.gesture.image.length&&(a.scale=Math.max(Math.min(a.scale,a.gesture.zoomMax),b.params.zoomMin),a.gesture.image.transition(b.params.speed).transform("translate3d(0,0,0) scale("+a.scale+")"),a.currentScale=a.scale,a.isScaling=!1,1===a.scale&&(a.gesture.slide=void 0))},onTouchStart:function(e,a){var t=e.zoom;t.gesture.image&&0!==t.gesture.image.length&&(t.image.isTouched||("android"===e.device.os&&a.preventDefault(),t.image.isTouched=!0,t.image.touchesStart.x="touchstart"===a.type?a.targetTouches[0].pageX:a.pageX,t.image.touchesStart.y="touchstart"===a.type?a.targetTouches[0].pageY:a.pageY))},onTouchMove:function(e){var a=b.zoom;if(a.gesture.image&&0!==a.gesture.image.length&&(b.allowClick=!1,a.image.isTouched&&a.gesture.slide)){a.image.isMoved||(a.image.width=a.gesture.image[0].offsetWidth,a.image.height=a.gesture.image[0].offsetHeight,a.image.startX=b.getTranslate(a.gesture.imageWrap[0],"x")||0,a.image.startY=b.getTranslate(a.gesture.imageWrap[0],"y")||0,a.gesture.slideWidth=a.gesture.slide[0].offsetWidth,a.gesture.slideHeight=a.gesture.slide[0].offsetHeight,a.gesture.imageWrap.transition(0),b.rtl&&(a.image.startX=-a.image.startX),b.rtl&&(a.image.startY=-a.image.startY));var t=a.image.width*a.scale,s=a.image.height*a.scale;if(!(t<a.gesture.slideWidth&&s<a.gesture.slideHeight)){if(a.image.minX=Math.min(a.gesture.slideWidth/2-t/2,0),a.image.maxX=-a.image.minX,a.image.minY=Math.min(a.gesture.slideHeight/2-s/2,0),a.image.maxY=-a.image.minY,a.image.touchesCurrent.x="touchmove"===e.type?e.targetTouches[0].pageX:e.pageX,a.image.touchesCurrent.y="touchmove"===e.type?e.targetTouches[0].pageY:e.pageY,!a.image.isMoved&&!a.isScaling){if(b.isHorizontal()&&Math.floor(a.image.minX)===Math.floor(a.image.startX)&&a.image.touchesCurrent.x<a.image.touchesStart.x||Math.floor(a.image.maxX)===Math.floor(a.image.startX)&&a.image.touchesCurrent.x>a.image.touchesStart.x)return void(a.image.isTouched=!1);if(!b.isHorizontal()&&Math.floor(a.image.minY)===Math.floor(a.image.startY)&&a.image.touchesCurrent.y<a.image.touchesStart.y||Math.floor(a.image.maxY)===Math.floor(a.image.startY)&&a.image.touchesCurrent.y>a.image.touchesStart.y)return void(a.image.isTouched=!1)}e.preventDefault(),e.stopPropagation(),a.image.isMoved=!0,a.image.currentX=a.image.touchesCurrent.x-a.image.touchesStart.x+a.image.startX,a.image.currentY=a.image.touchesCurrent.y-a.image.touchesStart.y+a.image.startY,a.image.currentX<a.image.minX&&(a.image.currentX=a.image.minX+1-Math.pow(a.image.minX-a.image.currentX+1,.8)),a.image.currentX>a.image.maxX&&(a.image.currentX=a.image.maxX-1+Math.pow(a.image.currentX-a.image.maxX+1,.8)),a.image.currentY<a.image.minY&&(a.image.currentY=a.image.minY+1-Math.pow(a.image.minY-a.image.currentY+1,.8)),a.image.currentY>a.image.maxY&&(a.image.currentY=a.image.maxY-1+Math.pow(a.image.currentY-a.image.maxY+1,.8)),a.velocity.prevPositionX||(a.velocity.prevPositionX=a.image.touchesCurrent.x),a.velocity.prevPositionY||(a.velocity.prevPositionY=a.image.touchesCurrent.y),a.velocity.prevTime||(a.velocity.prevTime=Date.now()),a.velocity.x=(a.image.touchesCurrent.x-a.velocity.prevPositionX)/(Date.now()-a.velocity.prevTime)/2,a.velocity.y=(a.image.touchesCurrent.y-a.velocity.prevPositionY)/(Date.now()-a.velocity.prevTime)/2,Math.abs(a.image.touchesCurrent.x-a.velocity.prevPositionX)<2&&(a.velocity.x=0),Math.abs(a.image.touchesCurrent.y-a.velocity.prevPositionY)<2&&(a.velocity.y=0),a.velocity.prevPositionX=a.image.touchesCurrent.x,a.velocity.prevPositionY=a.image.touchesCurrent.y,a.velocity.prevTime=Date.now(),a.gesture.imageWrap.transform("translate3d("+a.image.currentX+"px, "+a.image.currentY+"px,0)")}}},onTouchEnd:function(e,a){var t=e.zoom;if(t.gesture.image&&0!==t.gesture.image.length){if(!t.image.isTouched||!t.image.isMoved)return t.image.isTouched=!1,void(t.image.isMoved=!1);t.image.isTouched=!1,t.image.isMoved=!1;var s=300,i=300,r=t.velocity.x*s,n=t.image.currentX+r,o=t.velocity.y*i,l=t.image.currentY+o;0!==t.velocity.x&&(s=Math.abs((n-t.image.currentX)/t.velocity.x)),0!==t.velocity.y&&(i=Math.abs((l-t.image.currentY)/t.velocity.y));var p=Math.max(s,i);t.image.currentX=n,t.image.currentY=l;var d=t.image.width*t.scale,u=t.image.height*t.scale;t.image.minX=Math.min(t.gesture.slideWidth/2-d/2,0),t.image.maxX=-t.image.minX,t.image.minY=Math.min(t.gesture.slideHeight/2-u/2,0),t.image.maxY=-t.image.minY,t.image.currentX=Math.max(Math.min(t.image.currentX,t.image.maxX),t.image.minX),t.image.currentY=Math.max(Math.min(t.image.currentY,t.image.maxY),t.image.minY),t.gesture.imageWrap.transition(p).transform("translate3d("+t.image.currentX+"px, "+t.image.currentY+"px,0)")}},onTransitionEnd:function(e){var a=e.zoom;a.gesture.slide&&e.previousIndex!==e.activeIndex&&(a.gesture.image.transform("translate3d(0,0,0) scale(1)"),a.gesture.imageWrap.transform("translate3d(0,0,0)"),a.gesture.slide=a.gesture.image=a.gesture.imageWrap=void 0,a.scale=a.currentScale=1)},toggleZoom:function(e,t){var s=e.zoom;if(s.gesture.slide||(s.gesture.slide=e.clickedSlide?a(e.clickedSlide):e.slides.eq(e.activeIndex),s.gesture.image=s.gesture.slide.find("img, svg, canvas"),s.gesture.imageWrap=s.gesture.image.parent("."+e.params.zoomContainerClass)),s.gesture.image&&0!==s.gesture.image.length){var i,r,n,o,l,p,d,u,c,m,h,g,f,v,w,y,x,T;"undefined"==typeof s.image.touchesStart.x&&t?(i="touchend"===t.type?t.changedTouches[0].pageX:t.pageX,r="touchend"===t.type?t.changedTouches[0].pageY:t.pageY):(i=s.image.touchesStart.x,r=s.image.touchesStart.y),s.scale&&1!==s.scale?(s.scale=s.currentScale=1,s.gesture.imageWrap.transition(300).transform("translate3d(0,0,0)"),s.gesture.image.transition(300).transform("translate3d(0,0,0) scale(1)"),s.gesture.slide=void 0):(s.scale=s.currentScale=s.gesture.imageWrap.attr("data-swiper-zoom")||e.params.zoomMax,t?(x=s.gesture.slide[0].offsetWidth,T=s.gesture.slide[0].offsetHeight,n=s.gesture.slide.offset().left,o=s.gesture.slide.offset().top,l=n+x/2-i,p=o+T/2-r,c=s.gesture.image[0].offsetWidth,m=s.gesture.image[0].offsetHeight,h=c*s.scale,g=m*s.scale,f=Math.min(x/2-h/2,0),v=Math.min(T/2-g/2,0),w=-f,y=-v,d=l*s.scale,u=p*s.scale,d<f&&(d=f),d>w&&(d=w),u<v&&(u=v),u>y&&(u=y)):(d=0,u=0),s.gesture.imageWrap.transition(300).transform("translate3d("+d+"px, "+u+"px,0)"),s.gesture.image.transition(300).transform("translate3d(0,0,0) scale("+s.scale+")"))}},attachEvents:function(e){var t=e?"off":"on";if(b.params.zoom){var s=(b.slides,!("touchstart"!==b.touchEvents.start||!b.support.passiveListener||!b.params.passiveListeners)&&{passive:!0,capture:!1});b.support.gestures?(b.slides[t]("gesturestart",b.zoom.onGestureStart,s),b.slides[t]("gesturechange",b.zoom.onGestureChange,s),b.slides[t]("gestureend",b.zoom.onGestureEnd,s)):"touchstart"===b.touchEvents.start&&(b.slides[t](b.touchEvents.start,b.zoom.onGestureStart,s),b.slides[t](b.touchEvents.move,b.zoom.onGestureChange,s),b.slides[t](b.touchEvents.end,b.zoom.onGestureEnd,s)),b[t]("touchStart",b.zoom.onTouchStart),b.slides.each(function(e,s){a(s).find("."+b.params.zoomContainerClass).length>0&&a(s)[t](b.touchEvents.move,b.zoom.onTouchMove)}),b[t]("touchEnd",b.zoom.onTouchEnd),b[t]("transitionEnd",b.zoom.onTransitionEnd),b.params.zoomToggle&&b.on("doubleTap",b.zoom.toggleZoom)}},init:function(){b.zoom.attachEvents()},destroy:function(){b.zoom.attachEvents(!0)}},b._plugins=[];for(var O in b.plugins){var N=b.plugins[O](b,b.params[O]);N&&b._plugins.push(N)}return b.callPlugins=function(e){for(var a=0;a<b._plugins.length;a++)e in b._plugins[a]&&b._plugins[a][e](arguments[1],arguments[2],arguments[3],arguments[4],arguments[5])},b.emitterEventListeners={},b.emit=function(e){b.params[e]&&b.params[e](arguments[1],arguments[2],arguments[3],arguments[4],arguments[5]);var a;if(b.emitterEventListeners[e])for(a=0;a<b.emitterEventListeners[e].length;a++)b.emitterEventListeners[e][a](arguments[1],arguments[2],arguments[3],arguments[4],arguments[5]);b.callPlugins&&b.callPlugins(e,arguments[1],arguments[2],arguments[3],arguments[4],arguments[5])},b.on=function(e,a){return e=h(e),b.emitterEventListeners[e]||(b.emitterEventListeners[e]=[]),b.emitterEventListeners[e].push(a),b},b.off=function(e,a){var t;if(e=h(e),"undefined"==typeof a)return b.emitterEventListeners[e]=[],b;if(b.emitterEventListeners[e]&&0!==b.emitterEventListeners[e].length){for(t=0;t<b.emitterEventListeners[e].length;t++)b.emitterEventListeners[e][t]===a&&b.emitterEventListeners[e].splice(t,1);return b}},b.once=function(e,a){e=h(e);var t=function(){a(arguments[0],arguments[1],arguments[2],arguments[3],arguments[4]),b.off(e,t)};return b.on(e,t),b},b.a11y={makeFocusable:function(e){return e.attr("tabIndex","0"),e},addRole:function(e,a){return e.attr("role",a),e},addLabel:function(e,a){return e.attr("aria-label",a),e},disable:function(e){return e.attr("aria-disabled",!0),e},enable:function(e){return e.attr("aria-disabled",!1),e},onEnterKey:function(e){13===e.keyCode&&(a(e.target).is(b.params.nextButton)?(b.onClickNext(e),b.isEnd?b.a11y.notify(b.params.lastSlideMessage):b.a11y.notify(b.params.nextSlideMessage)):a(e.target).is(b.params.prevButton)&&(b.onClickPrev(e),b.isBeginning?b.a11y.notify(b.params.firstSlideMessage):b.a11y.notify(b.params.prevSlideMessage)),a(e.target).is("."+b.params.bulletClass)&&a(e.target)[0].click())},liveRegion:a('<span class="'+b.params.notificationClass+'" aria-live="assertive" aria-atomic="true"></span>'),notify:function(e){var a=b.a11y.liveRegion;0!==a.length&&(a.html(""),a.html(e))},init:function(){b.params.nextButton&&b.nextButton&&b.nextButton.length>0&&(b.a11y.makeFocusable(b.nextButton),b.a11y.addRole(b.nextButton,"button"),b.a11y.addLabel(b.nextButton,b.params.nextSlideMessage)),b.params.prevButton&&b.prevButton&&b.prevButton.length>0&&(b.a11y.makeFocusable(b.prevButton),b.a11y.addRole(b.prevButton,"button"),b.a11y.addLabel(b.prevButton,b.params.prevSlideMessage)),a(b.container).append(b.a11y.liveRegion)},initPagination:function(){b.params.pagination&&b.params.paginationClickable&&b.bullets&&b.bullets.length&&b.bullets.each(function(){var e=a(this);b.a11y.makeFocusable(e),b.a11y.addRole(e,"button"),b.a11y.addLabel(e,b.params.paginationBulletMessage.replace(/{{index}}/,e.index()+1))})},destroy:function(){b.a11y.liveRegion&&b.a11y.liveRegion.length>0&&b.a11y.liveRegion.remove()}},b.init=function(){b.params.loop&&b.createLoop(),b.updateContainerSize(),b.updateSlidesSize(),b.updatePagination(),b.params.scrollbar&&b.scrollbar&&(b.scrollbar.set(),b.params.scrollbarDraggable&&b.scrollbar.enableDraggable()),"slide"!==b.params.effect&&b.effects[b.params.effect]&&(b.params.loop||b.updateProgress(),b.effects[b.params.effect].setTranslate()),b.params.loop?b.slideTo(b.params.initialSlide+b.loopedSlides,0,b.params.runCallbacksOnInit):(b.slideTo(b.params.initialSlide,0,b.params.runCallbacksOnInit),0===b.params.initialSlide&&(b.parallax&&b.params.parallax&&b.parallax.setTranslate(),b.lazy&&b.params.lazyLoading&&(b.lazy.load(),b.lazy.initialImageLoaded=!0))),b.attachEvents(),b.params.observer&&b.support.observer&&b.initObservers(),b.params.preloadImages&&!b.params.lazyLoading&&b.preloadImages(),b.params.zoom&&b.zoom&&b.zoom.init(),b.params.autoplay&&b.startAutoplay(),b.params.keyboardControl&&b.enableKeyboardControl&&b.enableKeyboardControl(),b.params.mousewheelControl&&b.enableMousewheelControl&&b.enableMousewheelControl(),b.params.hashnavReplaceState&&(b.params.replaceState=b.params.hashnavReplaceState),b.params.history&&b.history&&b.history.init(),b.params.hashnav&&b.hashnav&&b.hashnav.init(),b.params.a11y&&b.a11y&&b.a11y.init(),b.emit("onInit",b)},b.cleanupStyles=function(){b.container.removeClass(b.classNames.join(" ")).removeAttr("style"),b.wrapper.removeAttr("style"),b.slides&&b.slides.length&&b.slides.removeClass([b.params.slideVisibleClass,b.params.slideActiveClass,b.params.slideNextClass,b.params.slidePrevClass].join(" ")).removeAttr("style").removeAttr("data-swiper-column").removeAttr("data-swiper-row"),b.paginationContainer&&b.paginationContainer.length&&b.paginationContainer.removeClass(b.params.paginationHiddenClass),b.bullets&&b.bullets.length&&b.bullets.removeClass(b.params.bulletActiveClass),b.params.prevButton&&a(b.params.prevButton).removeClass(b.params.buttonDisabledClass),b.params.nextButton&&a(b.params.nextButton).removeClass(b.params.buttonDisabledClass),b.params.scrollbar&&b.scrollbar&&(b.scrollbar.track&&b.scrollbar.track.length&&b.scrollbar.track.removeAttr("style"),b.scrollbar.drag&&b.scrollbar.drag.length&&b.scrollbar.drag.removeAttr("style"))},b.destroy=function(e,a){b.detachEvents(),b.stopAutoplay(),b.params.scrollbar&&b.scrollbar&&b.params.scrollbarDraggable&&b.scrollbar.disableDraggable(),b.params.loop&&b.destroyLoop(),a&&b.cleanupStyles(),b.disconnectObservers(),b.params.zoom&&b.zoom&&b.zoom.destroy(),b.params.keyboardControl&&b.disableKeyboardControl&&b.disableKeyboardControl(),b.params.mousewheelControl&&b.disableMousewheelControl&&b.disableMousewheelControl(),b.params.a11y&&b.a11y&&b.a11y.destroy(),b.params.history&&!b.params.replaceState&&window.removeEventListener("popstate",b.history.setHistoryPopState),b.params.hashnav&&b.hashnav&&b.hashnav.destroy(),b.emit("onDestroy"),e!==!1&&(b=null)},b.init(),b}};t.prototype={isSafari:function(){var e=window.navigator.userAgent.toLowerCase();return e.indexOf("safari")>=0&&e.indexOf("chrome")<0&&e.indexOf("android")<0}(),isUiWebView:/(iPhone|iPod|iPad).*AppleWebKit(?!.*Safari)/i.test(window.navigator.userAgent),isArray:function(e){return"[object Array]"===Object.prototype.toString.apply(e)},browser:{ie:window.navigator.pointerEnabled||window.navigator.msPointerEnabled,ieTouch:window.navigator.msPointerEnabled&&window.navigator.msMaxTouchPoints>1||window.navigator.pointerEnabled&&window.navigator.maxTouchPoints>1,lteIE9:function(){var e=document.createElement("div");return e.innerHTML="<!--[if lte IE 9]><i></i><![endif]-->",1===e.getElementsByTagName("i").length}()},device:function(){var e=window.navigator.userAgent,a=e.match(/(Android);?[\s\/]+([\d.]+)?/),t=e.match(/(iPad).*OS\s([\d_]+)/),s=e.match(/(iPod)(.*OS\s([\d_]+))?/),i=!t&&e.match(/(iPhone\sOS|iOS)\s([\d_]+)/);return{ios:t||i||s,android:a}}(),support:{touch:window.Modernizr&&Modernizr.touch===!0||function(){return!!("ontouchstart"in window||window.DocumentTouch&&document instanceof DocumentTouch)}(),transforms3d:window.Modernizr&&Modernizr.csstransforms3d===!0||function(){var e=document.createElement("div").style;return"webkitPerspective"in e||"MozPerspective"in e||"OPerspective"in e||"MsPerspective"in e||"perspective"in e}(),flexbox:function(){for(var e=document.createElement("div").style,a="alignItems webkitAlignItems webkitBoxAlign msFlexAlign mozBoxAlign webkitFlexDirection msFlexDirection mozBoxDirection mozBoxOrient webkitBoxDirection webkitBoxOrient".split(" "),t=0;t<a.length;t++)if(a[t]in e)return!0}(),observer:function(){return"MutationObserver"in window||"WebkitMutationObserver"in window}(),passiveListener:function(){var e=!1;try{var a=Object.defineProperty({},"passive",{get:function(){e=!0}});window.addEventListener("testPassiveListener",null,a)}catch(e){}return e}(),gestures:function(){return"ongesturestart"in window}()},plugins:{}};for(var s=(function(){var e=function(e){var a=this,t=0;for(t=0;t<e.length;t++)a[t]=e[t];return a.length=e.length,this},a=function(a,t){var s=[],i=0;if(a&&!t&&a instanceof e)return a;if(a)if("string"==typeof a){var r,n,o=a.trim();if(o.indexOf("<")>=0&&o.indexOf(">")>=0){var l="div";for(0===o.indexOf("<li")&&(l="ul"),0===o.indexOf("<tr")&&(l="tbody"),0!==o.indexOf("<td")&&0!==o.indexOf("<th")||(l="tr"),0===o.indexOf("<tbody")&&(l="table"),0===o.indexOf("<option")&&(l="select"),n=document.createElement(l),n.innerHTML=a,i=0;i<n.childNodes.length;i++)s.push(n.childNodes[i])}else for(r=t||"#"!==a[0]||a.match(/[ .<>:~]/)?(t||document).querySelectorAll(a):[document.getElementById(a.split("#")[1])],i=0;i<r.length;i++)r[i]&&s.push(r[i])}else if(a.nodeType||a===window||a===document)s.push(a);else if(a.length>0&&a[0].nodeType)for(i=0;i<a.length;i++)s.push(a[i]);return new e(s)};return e.prototype={addClass:function(e){if("undefined"==typeof e)return this;for(var a=e.split(" "),t=0;t<a.length;t++)for(var s=0;s<this.length;s++)this[s].classList.add(a[t]);return this},removeClass:function(e){for(var a=e.split(" "),t=0;t<a.length;t++)for(var s=0;s<this.length;s++)this[s].classList.remove(a[t]);return this},hasClass:function(e){return!!this[0]&&this[0].classList.contains(e)},toggleClass:function(e){for(var a=e.split(" "),t=0;t<a.length;t++)for(var s=0;s<this.length;s++)this[s].classList.toggle(a[t]);return this},attr:function(e,a){if(1===arguments.length&&"string"==typeof e)return this[0]?this[0].getAttribute(e):void 0;for(var t=0;t<this.length;t++)if(2===arguments.length)this[t].setAttribute(e,a);else for(var s in e)this[t][s]=e[s],this[t].setAttribute(s,e[s]);return this},removeAttr:function(e){for(var a=0;a<this.length;a++)this[a].removeAttribute(e);return this},data:function(e,a){if("undefined"!=typeof a){for(var t=0;t<this.length;t++){var s=this[t];s.dom7ElementDataStorage||(s.dom7ElementDataStorage={}),s.dom7ElementDataStorage[e]=a}return this}if(this[0]){var i=this[0].getAttribute("data-"+e);return i?i:this[0].dom7ElementDataStorage&&e in this[0].dom7ElementDataStorage?this[0].dom7ElementDataStorage[e]:void 0}},transform:function(e){for(var a=0;a<this.length;a++){var t=this[a].style;t.webkitTransform=t.MsTransform=t.msTransform=t.MozTransform=t.OTransform=t.transform=e}return this},transition:function(e){"string"!=typeof e&&(e+="ms");for(var a=0;a<this.length;a++){var t=this[a].style;t.webkitTransitionDuration=t.MsTransitionDuration=t.msTransitionDuration=t.MozTransitionDuration=t.OTransitionDuration=t.transitionDuration=e}return this},on:function(e,t,s,i){function r(e){var i=e.target;if(a(i).is(t))s.call(i,e);else for(var r=a(i).parents(),n=0;n<r.length;n++)a(r[n]).is(t)&&s.call(r[n],e)}var n,o,l=e.split(" ");for(n=0;n<this.length;n++)if("function"==typeof t||t===!1)for("function"==typeof t&&(s=arguments[1],i=arguments[2]||!1),o=0;o<l.length;o++)this[n].addEventListener(l[o],s,i);else for(o=0;o<l.length;o++)this[n].dom7LiveListeners||(this[n].dom7LiveListeners=[]),this[n].dom7LiveListeners.push({listener:s,liveListener:r}),this[n].addEventListener(l[o],r,i);return this},off:function(e,a,t,s){for(var i=e.split(" "),r=0;r<i.length;r++)for(var n=0;n<this.length;n++)if("function"==typeof a||a===!1)"function"==typeof a&&(t=arguments[1],s=arguments[2]||!1),this[n].removeEventListener(i[r],t,s);else if(this[n].dom7LiveListeners)for(var o=0;o<this[n].dom7LiveListeners.length;o++)this[n].dom7LiveListeners[o].listener===t&&this[n].removeEventListener(i[r],this[n].dom7LiveListeners[o].liveListener,s);return this},once:function(e,a,t,s){function i(n){t(n),r.off(e,a,i,s)}var r=this;"function"==typeof a&&(a=!1,t=arguments[1],s=arguments[2]),r.on(e,a,i,s)},trigger:function(e,a){for(var t=0;t<this.length;t++){var s;try{s=new window.CustomEvent(e,{detail:a,bubbles:!0,cancelable:!0})}catch(t){s=document.createEvent("Event"),s.initEvent(e,!0,!0),s.detail=a}this[t].dispatchEvent(s)}return this},transitionEnd:function(e){function a(r){if(r.target===this)for(e.call(this,r),t=0;t<s.length;t++)i.off(s[t],a)}var t,s=["webkitTransitionEnd","transitionend","oTransitionEnd","MSTransitionEnd","msTransitionEnd"],i=this;if(e)for(t=0;t<s.length;t++)i.on(s[t],a);return this},width:function(){return this[0]===window?window.innerWidth:this.length>0?parseFloat(this.css("width")):null},outerWidth:function(e){return this.length>0?e?this[0].offsetWidth+parseFloat(this.css("margin-right"))+parseFloat(this.css("margin-left")):this[0].offsetWidth:null},height:function(){return this[0]===window?window.innerHeight:this.length>0?parseFloat(this.css("height")):null},outerHeight:function(e){return this.length>0?e?this[0].offsetHeight+parseFloat(this.css("margin-top"))+parseFloat(this.css("margin-bottom")):this[0].offsetHeight:null},offset:function(){if(this.length>0){var e=this[0],a=e.getBoundingClientRect(),t=document.body,s=e.clientTop||t.clientTop||0,i=e.clientLeft||t.clientLeft||0,r=window.pageYOffset||e.scrollTop,n=window.pageXOffset||e.scrollLeft;return{top:a.top+r-s,left:a.left+n-i}}return null},css:function(e,a){var t;if(1===arguments.length){if("string"!=typeof e){for(t=0;t<this.length;t++)for(var s in e)this[t].style[s]=e[s];return this}if(this[0])return window.getComputedStyle(this[0],null).getPropertyValue(e)}if(2===arguments.length&&"string"==typeof e){for(t=0;t<this.length;t++)this[t].style[e]=a;return this}return this},each:function(e){for(var a=0;a<this.length;a++)e.call(this[a],a,this[a]);return this},html:function(e){if("undefined"==typeof e)return this[0]?this[0].innerHTML:void 0;for(var a=0;a<this.length;a++)this[a].innerHTML=e;return this},text:function(e){if("undefined"==typeof e)return this[0]?this[0].textContent.trim():null;for(var a=0;a<this.length;a++)this[a].textContent=e;return this},is:function(t){if(!this[0])return!1;var s,i;if("string"==typeof t){var r=this[0];if(r===document)return t===document;if(r===window)return t===window;if(r.matches)return r.matches(t);if(r.webkitMatchesSelector)return r.webkitMatchesSelector(t);if(r.mozMatchesSelector)return r.mozMatchesSelector(t);if(r.msMatchesSelector)return r.msMatchesSelector(t);for(s=a(t),i=0;i<s.length;i++)if(s[i]===this[0])return!0;return!1}if(t===document)return this[0]===document;if(t===window)return this[0]===window;if(t.nodeType||t instanceof e){for(s=t.nodeType?[t]:t,i=0;i<s.length;i++)if(s[i]===this[0])return!0;return!1}return!1},index:function(){if(this[0]){for(var e=this[0],a=0;null!==(e=e.previousSibling);)1===e.nodeType&&a++;return a}},eq:function(a){if("undefined"==typeof a)return this;var t,s=this.length;return a>s-1?new e([]):a<0?(t=s+a,new e(t<0?[]:[this[t]])):new e([this[a]])},append:function(a){var t,s;for(t=0;t<this.length;t++)if("string"==typeof a){var i=document.createElement("div");for(i.innerHTML=a;i.firstChild;)this[t].appendChild(i.firstChild)}else if(a instanceof e)for(s=0;s<a.length;s++)this[t].appendChild(a[s]);else this[t].appendChild(a);return this},prepend:function(a){var t,s;for(t=0;t<this.length;t++)if("string"==typeof a){var i=document.createElement("div");for(i.innerHTML=a,s=i.childNodes.length-1;s>=0;s--)this[t].insertBefore(i.childNodes[s],this[t].childNodes[0])}else if(a instanceof e)for(s=0;s<a.length;s++)this[t].insertBefore(a[s],this[t].childNodes[0]);else this[t].insertBefore(a,this[t].childNodes[0]);return this},insertBefore:function(e){for(var t=a(e),s=0;s<this.length;s++)if(1===t.length)t[0].parentNode.insertBefore(this[s],t[0]);else if(t.length>1)for(var i=0;i<t.length;i++)t[i].parentNode.insertBefore(this[s].cloneNode(!0),t[i])},insertAfter:function(e){for(var t=a(e),s=0;s<this.length;s++)if(1===t.length)t[0].parentNode.insertBefore(this[s],t[0].nextSibling);else if(t.length>1)for(var i=0;i<t.length;i++)t[i].parentNode.insertBefore(this[s].cloneNode(!0),t[i].nextSibling)},next:function(t){return new e(this.length>0?t?this[0].nextElementSibling&&a(this[0].nextElementSibling).is(t)?[this[0].nextElementSibling]:[]:this[0].nextElementSibling?[this[0].nextElementSibling]:[]:[])},nextAll:function(t){var s=[],i=this[0];if(!i)return new e([]);for(;i.nextElementSibling;){var r=i.nextElementSibling;t?a(r).is(t)&&s.push(r):s.push(r),i=r}return new e(s)},prev:function(t){return new e(this.length>0?t?this[0].previousElementSibling&&a(this[0].previousElementSibling).is(t)?[this[0].previousElementSibling]:[]:this[0].previousElementSibling?[this[0].previousElementSibling]:[]:[])},prevAll:function(t){var s=[],i=this[0];if(!i)return new e([]);for(;i.previousElementSibling;){var r=i.previousElementSibling;t?a(r).is(t)&&s.push(r):s.push(r),i=r}return new e(s)},parent:function(e){for(var t=[],s=0;s<this.length;s++)e?a(this[s].parentNode).is(e)&&t.push(this[s].parentNode):t.push(this[s].parentNode);return a(a.unique(t))},parents:function(e){for(var t=[],s=0;s<this.length;s++)for(var i=this[s].parentNode;i;)e?a(i).is(e)&&t.push(i):t.push(i),i=i.parentNode;return a(a.unique(t))},find:function(a){for(var t=[],s=0;s<this.length;s++)for(var i=this[s].querySelectorAll(a),r=0;r<i.length;r++)t.push(i[r]);return new e(t)},children:function(t){for(var s=[],i=0;i<this.length;i++)for(var r=this[i].childNodes,n=0;n<r.length;n++)t?1===r[n].nodeType&&a(r[n]).is(t)&&s.push(r[n]):1===r[n].nodeType&&s.push(r[n]);return new e(a.unique(s))},remove:function(){for(var e=0;e<this.length;e++)this[e].parentNode&&this[e].parentNode.removeChild(this[e]);return this},add:function(){var e,t,s=this;for(e=0;e<arguments.length;e++){var i=a(arguments[e]);for(t=0;t<i.length;t++)s[s.length]=i[t],s.length++}return s}},a.fn=e.prototype,a.unique=function(e){for(var a=[],t=0;t<e.length;t++)a.indexOf(e[t])===-1&&a.push(e[t]);return a},a}()),i=["jQuery","Zepto","Dom7"],r=0;r<i.length;r++)window[i[r]]&&e(window[i[r]]);var n;n="undefined"==typeof s?window.Dom7||window.Zepto||window.jQuery:s,n&&("transitionEnd"in n.fn||(n.fn.transitionEnd=function(e){function a(r){if(r.target===this)for(e.call(this,r),t=0;t<s.length;t++)i.off(s[t],a)}var t,s=["webkitTransitionEnd","transitionend","oTransitionEnd","MSTransitionEnd","msTransitionEnd"],i=this;if(e)for(t=0;t<s.length;t++)i.on(s[t],a);return this}),"transform"in n.fn||(n.fn.transform=function(e){for(var a=0;a<this.length;a++){var t=this[a].style;t.webkitTransform=t.MsTransform=t.msTransform=t.MozTransform=t.OTransform=t.transform=e}return this}),"transition"in n.fn||(n.fn.transition=function(e){"string"!=typeof e&&(e+="ms");for(var a=0;a<this.length;a++){var t=this[a].style;t.webkitTransitionDuration=t.MsTransitionDuration=t.msTransitionDuration=t.MozTransitionDuration=t.OTransitionDuration=t.transitionDuration=e;
}return this}),"outerWidth"in n.fn||(n.fn.outerWidth=function(e){return this.length>0?e?this[0].offsetWidth+parseFloat(this.css("margin-right"))+parseFloat(this.css("margin-left")):this[0].offsetWidth:null})),window.Swiper=t}(),"undefined"!=typeof module?module.exports=window.Swiper:"function"==typeof define&&define.amd&&define([],function(){"use strict";return window.Swiper});
//# sourceMappingURL=maps/swiper.min.js.map

/** 
 * Name:    Highslide JS
 * Version: 4.1.13 (2011-10-06)
 * Config:  default +events +unobtrusive +imagemap +slideshow +positioning +transitions +viewport +thumbstrip +inline +ajax +iframe +flash
 * Author:  Torstein Hønsi
 * Support: www.highslide.com/support
 * License: www.highslide.com/#license
 */
if(!hs){var hs={lang:{cssDirection:"ltr",loadingText:"Loading...",loadingTitle:"Click to cancel",focusTitle:"Click to bring to front",fullExpandTitle:"Expand to actual size (f)",creditsText:"Powered by <i>Highslide JS</i>",creditsTitle:"Go to the Highslide JS homepage",previousText:"Previous",nextText:"Next",moveText:"Move",closeText:"Close",closeTitle:"Close (esc)",resizeTitle:"Resize",playText:"Play",playTitle:"Play slideshow (spacebar)",pauseText:"Pause",pauseTitle:"Pause slideshow (spacebar)",previousTitle:"Previous (arrow left)",nextTitle:"Next (arrow right)",moveTitle:"Move",fullExpandText:"1:1",number:"Image %1 of %2",restoreTitle:"Click to close image, click and drag to move. Use arrow keys for next and previous."},graphicsDir:"highslide/graphics/",expandCursor:"zoomin.cur",restoreCursor:"zoomout.cur",expandDuration:250,restoreDuration:250,marginLeft:15,marginRight:15,marginTop:15,marginBottom:15,zIndexCounter:1001,loadingOpacity:0.75,allowMultipleInstances:true,numberOfImagesToPreload:5,outlineWhileAnimating:2,outlineStartOffset:3,padToMinWidth:false,fullExpandPosition:"bottom right",fullExpandOpacity:1,showCredits:true,creditsHref:"http://highslide.com/",creditsTarget:"_self",enableKeyListener:true,openerTagNames:["a","area"],transitions:[],transitionDuration:250,dimmingOpacity:0,dimmingDuration:50,allowWidthReduction:false,allowHeightReduction:true,preserveContent:true,objectLoadTime:"before",cacheAjax:true,anchor:"auto",align:"auto",targetX:null,targetY:null,dragByHeading:true,minWidth:200,minHeight:200,allowSizeReduction:true,outlineType:"drop-shadow",skin:{controls:'<div class="highslide-controls"><ul><li class="highslide-previous"><a href="#" title="{hs.lang.previousTitle}"><span>{hs.lang.previousText}</span></a></li><li class="highslide-play"><a href="#" title="{hs.lang.playTitle}"><span>{hs.lang.playText}</span></a></li><li class="highslide-pause"><a href="#" title="{hs.lang.pauseTitle}"><span>{hs.lang.pauseText}</span></a></li><li class="highslide-next"><a href="#" title="{hs.lang.nextTitle}"><span>{hs.lang.nextText}</span></a></li><li class="highslide-move"><a href="#" title="{hs.lang.moveTitle}"><span>{hs.lang.moveText}</span></a></li><li class="highslide-full-expand"><a href="#" title="{hs.lang.fullExpandTitle}"><span>{hs.lang.fullExpandText}</span></a></li><li class="highslide-close"><a href="#" title="{hs.lang.closeTitle}" ><span>{hs.lang.closeText}</span></a></li></ul></div>',contentWrapper:'<div class="highslide-header"><ul><li class="highslide-previous"><a href="#" title="{hs.lang.previousTitle}" onclick="return hs.previous(this)"><span>{hs.lang.previousText}</span></a></li><li class="highslide-next"><a href="#" title="{hs.lang.nextTitle}" onclick="return hs.next(this)"><span>{hs.lang.nextText}</span></a></li><li class="highslide-move"><a href="#" title="{hs.lang.moveTitle}" onclick="return false"><span>{hs.lang.moveText}</span></a></li><li class="highslide-close"><a href="#" title="{hs.lang.closeTitle}" onclick="return hs.close(this)"><span>{hs.lang.closeText}</span></a></li></ul></div><div class="highslide-body"></div><div class="highslide-footer"><div><span class="highslide-resize" title="{hs.lang.resizeTitle}"><span></span></span></div></div>'},preloadTheseImages:[],continuePreloading:true,expanders:[],overrides:["allowSizeReduction","useBox","anchor","align","targetX","targetY","outlineType","outlineWhileAnimating","captionId","captionText","captionEval","captionOverlay","headingId","headingText","headingEval","headingOverlay","creditsPosition","dragByHeading","autoplay","numberPosition","transitions","dimmingOpacity","width","height","contentId","allowWidthReduction","allowHeightReduction","preserveContent","maincontentId","maincontentText","maincontentEval","objectType","cacheAjax","objectWidth","objectHeight","objectLoadTime","swfOptions","wrapperClassName","minWidth","minHeight","maxWidth","maxHeight","pageOrigin","slideshowGroup","easing","easingClose","fadeInOut","src"],overlays:[],idCounter:0,oPos:{x:["leftpanel","left","center","right","rightpanel"],y:["above","top","middle","bottom","below"]},mouse:{},headingOverlay:{},captionOverlay:{},swfOptions:{flashvars:{},params:{},attributes:{}},timers:[],slideshows:[],pendingOutlines:{},sleeping:[],preloadTheseAjax:[],cacheBindings:[],cachedGets:{},clones:{},onReady:[],uaVersion:/Trident\/4\.0/.test(navigator.userAgent)?8:parseFloat((navigator.userAgent.toLowerCase().match(/.+(?:rv|it|ra|ie)[\/: ]([\d.]+)/)||[0,"0"])[1]),ie:(document.all&&!window.opera),safari:/Safari/.test(navigator.userAgent),geckoMac:/Macintosh.+rv:1\.[0-8].+Gecko/.test(navigator.userAgent),$:function(a){if(a){return document.getElementById(a)}},push:function(a,b){a[a.length]=b},createElement:function(a,f,e,d,c){var b=document.createElement(a);if(f){hs.extend(b,f)}if(c){hs.setStyles(b,{padding:0,border:"none",margin:0})}if(e){hs.setStyles(b,e)}if(d){d.appendChild(b)}return b},extend:function(b,c){for(var a in c){b[a]=c[a]}return b},setStyles:function(b,c){for(var a in c){if(hs.ieLt9&&a=="opacity"){if(c[a]>0.99){b.style.removeAttribute("filter")}else{b.style.filter="alpha(opacity="+(c[a]*100)+")"}}else{b.style[a]=c[a]}}},animate:function(f,a,d){var c,g,j;if(typeof d!="object"||d===null){var i=arguments;d={duration:i[2],easing:i[3],complete:i[4]}}if(typeof d.duration!="number"){d.duration=250}d.easing=Math[d.easing]||Math.easeInQuad;d.curAnim=hs.extend({},a);for(var b in a){var h=new hs.fx(f,d,b);c=parseFloat(hs.css(f,b))||0;g=parseFloat(a[b]);j=b!="opacity"?"px":"";h.custom(c,g,j)}},css:function(a,c){if(a.style[c]){return a.style[c]}else{if(document.defaultView){return document.defaultView.getComputedStyle(a,null).getPropertyValue(c)}else{if(c=="opacity"){c="filter"}var b=a.currentStyle[c.replace(/\-(\w)/g,function(e,d){return d.toUpperCase()})];if(c=="filter"){b=b.replace(/alpha\(opacity=([0-9]+)\)/,function(e,d){return d/100})}return b===""?1:b}}},getPageSize:function(){var f=document,b=window,e=f.compatMode&&f.compatMode!="BackCompat"?f.documentElement:f.body,g=hs.ie&&(hs.uaVersion<9||typeof pageXOffset=="undefined");var c=g?e.clientWidth:(f.documentElement.clientWidth||self.innerWidth),a=g?e.clientHeight:self.innerHeight;hs.page={width:c,height:a,scrollLeft:g?e.scrollLeft:pageXOffset,scrollTop:g?e.scrollTop:pageYOffset};return hs.page},getPosition:function(c){if(/area/i.test(c.tagName)){var e=document.getElementsByTagName("img");for(var b=0;b<e.length;b++){var a=e[b].useMap;if(a&&a.replace(/^.*?#/,"")==c.parentNode.name){c=e[b];break}}}var d={x:c.offsetLeft,y:c.offsetTop};while(c.offsetParent){c=c.offsetParent;d.x+=c.offsetLeft;d.y+=c.offsetTop;if(c!=document.body&&c!=document.documentElement){d.x-=c.scrollLeft;d.y-=c.scrollTop}}return d},expand:function(b,h,f,d){if(!b){b=hs.createElement("a",null,{display:"none"},hs.container)}if(typeof b.getParams=="function"){return h}if(d=="html"){for(var c=0;c<hs.sleeping.length;c++){if(hs.sleeping[c]&&hs.sleeping[c].a==b){hs.sleeping[c].awake();hs.sleeping[c]=null;return false}}hs.hasHtmlExpanders=true}try{new hs.Expander(b,h,f,d);return false}catch(g){return true}},htmlExpand:function(b,d,c){return hs.expand(b,d,c,"html")},getSelfRendered:function(){return hs.createElement("div",{className:"highslide-html-content",innerHTML:hs.replaceLang(hs.skin.contentWrapper)})},getElementByClass:function(e,c,d){var b=e.getElementsByTagName(c);for(var a=0;a<b.length;a++){if((new RegExp(d)).test(b[a].className)){return b[a]}}return null},replaceLang:function(c){c=c.replace(/\s/g," ");var b=/{hs\.lang\.([^}]+)\}/g,d=c.match(b),e;if(d){for(var a=0;a<d.length;a++){e=d[a].replace(b,"$1");if(typeof hs.lang[e]!="undefined"){c=c.replace(d[a],hs.lang[e])}}}return c},setClickEvents:function(){var b=document.getElementsByTagName("a");for(var a=0;a<b.length;a++){var c=hs.isUnobtrusiveAnchor(b[a]);if(c&&!b[a].hsHasSetClick){(function(){var d=c;if(hs.fireEvent(hs,"onSetClickEvent",{element:b[a],type:d})){b[a].onclick=(c=="image")?function(){return hs.expand(this)}:function(){return hs.htmlExpand(this,{objectType:d})}}})();b[a].hsHasSetClick=true}}hs.getAnchors()},isUnobtrusiveAnchor:function(a){if(a.rel=="highslide"){return"image"}else{if(a.rel=="highslide-ajax"){return"ajax"}else{if(a.rel=="highslide-iframe"){return"iframe"}else{if(a.rel=="highslide-swf"){return"swf"}}}}},getCacheBinding:function(b){for(var d=0;d<hs.cacheBindings.length;d++){if(hs.cacheBindings[d][0]==b){var e=hs.cacheBindings[d][1];hs.cacheBindings[d][1]=e.cloneNode(1);return e}}return null},preloadAjax:function(f){var b=hs.getAnchors();for(var d=0;d<b.htmls.length;d++){var c=b.htmls[d];if(hs.getParam(c,"objectType")=="ajax"&&hs.getParam(c,"cacheAjax")){hs.push(hs.preloadTheseAjax,c)}}hs.preloadAjaxElement(0)},preloadAjaxElement:function(d){if(!hs.preloadTheseAjax[d]){return}var b=hs.preloadTheseAjax[d];var c=hs.getNode(hs.getParam(b,"contentId"));if(!c){c=hs.getSelfRendered()}var e=new hs.Ajax(b,c,1);e.onError=function(){};e.onLoad=function(){hs.push(hs.cacheBindings,[b,c]);hs.preloadAjaxElement(d+1)};e.run()},focusTopmost:function(){var c=0,b=-1,a=hs.expanders,e,f;for(var d=0;d<a.length;d++){e=a[d];if(e){f=e.wrapper.style.zIndex;if(f&&f>c){c=f;b=d}}}if(b==-1){hs.focusKey=-1}else{a[b].focus()}},getParam:function(b,d){b.getParams=b.onclick;var c=b.getParams?b.getParams():null;b.getParams=null;return(c&&typeof c[d]!="undefined")?c[d]:(typeof hs[d]!="undefined"?hs[d]:null)},getSrc:function(b){var c=hs.getParam(b,"src");if(c){return c}return b.href},getNode:function(e){var c=hs.$(e),d=hs.clones[e],b={};if(!c&&!d){return null}if(!d){d=c.cloneNode(true);d.id="";hs.clones[e]=d;return c}else{return d.cloneNode(true)}},discardElement:function(a){if(a){hs.garbageBin.appendChild(a)}hs.garbageBin.innerHTML=""},dim:function(d){if(!hs.dimmer){a=true;hs.dimmer=hs.createElement("div",{className:"highslide-dimming highslide-viewport-size",owner:"",onclick:function(){if(hs.fireEvent(hs,"onDimmerClick")){hs.close()}}},{visibility:"visible",opacity:0},hs.container,true);if(/(Android|iPad|iPhone|iPod)/.test(navigator.userAgent)){var b=document.body;function c(){hs.setStyles(hs.dimmer,{width:b.scrollWidth+"px",height:b.scrollHeight+"px"})}c();hs.addEventListener(window,"resize",c)}}hs.dimmer.style.display="";var a=hs.dimmer.owner=="";hs.dimmer.owner+="|"+d.key;if(a){if(hs.geckoMac&&hs.dimmingGeckoFix){hs.setStyles(hs.dimmer,{background:"url("+hs.graphicsDir+"geckodimmer.png)",opacity:1})}else{hs.animate(hs.dimmer,{opacity:d.dimmingOpacity},hs.dimmingDuration)}}},undim:function(a){if(!hs.dimmer){return}if(typeof a!="undefined"){hs.dimmer.owner=hs.dimmer.owner.replace("|"+a,"")}if((typeof a!="undefined"&&hs.dimmer.owner!="")||(hs.upcoming&&hs.getParam(hs.upcoming,"dimmingOpacity"))){return}if(hs.geckoMac&&hs.dimmingGeckoFix){hs.dimmer.style.display="none"}else{hs.animate(hs.dimmer,{opacity:0},hs.dimmingDuration,null,function(){hs.dimmer.style.display="none"})}},transit:function(a,d){var b=d||hs.getExpander();d=b;if(hs.upcoming){return false}else{hs.last=b}hs.removeEventListener(document,window.opera?"keypress":"keydown",hs.keyHandler);try{hs.upcoming=a;a.onclick()}catch(c){hs.last=hs.upcoming=null}try{if(!a||d.transitions[1]!="crossfade"){d.close()}}catch(c){}return false},previousOrNext:function(a,c){var b=hs.getExpander(a);if(b){return hs.transit(b.getAdjacentAnchor(c),b)}else{return false}},previous:function(a){return hs.previousOrNext(a,-1)},next:function(a){return hs.previousOrNext(a,1)},keyHandler:function(a){if(!a){a=window.event}if(!a.target){a.target=a.srcElement}if(typeof a.target.form!="undefined"){return true}if(!hs.fireEvent(hs,"onKeyDown",a)){return true}var b=hs.getExpander();var c=null;switch(a.keyCode){case 70:if(b){b.doFullExpand()}return true;case 32:c=2;break;case 34:case 39:case 40:c=1;break;case 8:case 33:case 37:case 38:c=-1;break;case 27:case 13:c=0}if(c!==null){if(c!=2){hs.removeEventListener(document,window.opera?"keypress":"keydown",hs.keyHandler)}if(!hs.enableKeyListener){return true}if(a.preventDefault){a.preventDefault()}else{a.returnValue=false}if(b){if(c==0){b.close()}else{if(c==2){if(b.slideshow){b.slideshow.hitSpace()}}else{if(b.slideshow){b.slideshow.pause()}hs.previousOrNext(b.key,c)}}return false}}return true},registerOverlay:function(a){hs.push(hs.overlays,hs.extend(a,{hsId:"hsId"+hs.idCounter++}))},addSlideshow:function(b){var d=b.slideshowGroup;if(typeof d=="object"){for(var c=0;c<d.length;c++){var e={};for(var a in b){e[a]=b[a]}e.slideshowGroup=d[c];hs.push(hs.slideshows,e)}}else{hs.push(hs.slideshows,b)}},getWrapperKey:function(c,b){var e,d=/^highslide-wrapper-([0-9]+)$/;e=c;while(e.parentNode){if(e.hsKey!==undefined){return e.hsKey}if(e.id&&d.test(e.id)){return e.id.replace(d,"$1")}e=e.parentNode}if(!b){e=c;while(e.parentNode){if(e.tagName&&hs.isHsAnchor(e)){for(var a=0;a<hs.expanders.length;a++){var f=hs.expanders[a];if(f&&f.a==e){return a}}}e=e.parentNode}}return null},getExpander:function(b,a){if(typeof b=="undefined"){return hs.expanders[hs.focusKey]||null}if(typeof b=="number"){return hs.expanders[b]||null}if(typeof b=="string"){b=hs.$(b)}return hs.expanders[hs.getWrapperKey(b,a)]||null},isHsAnchor:function(b){return(b.onclick&&b.onclick.toString().replace(/\s/g," ").match(/hs.(htmlE|e)xpand/))},reOrder:function(){for(var a=0;a<hs.expanders.length;a++){if(hs.expanders[a]&&hs.expanders[a].isExpanded){hs.focusTopmost()}}},fireEvent:function(c,a,b){return c&&c[a]?(c[a](c,b)!==false):true},mouseClickHandler:function(d){if(!d){d=window.event}if(d.button>1){return true}if(!d.target){d.target=d.srcElement}var b=d.target;while(b.parentNode&&!(/highslide-(image|move|html|resize)/.test(b.className))){b=b.parentNode}var f=hs.getExpander(b);if(f&&(f.isClosing||!f.isExpanded)){return true}if(f&&d.type=="mousedown"){if(d.target.form){return true}var a=b.className.match(/highslide-(image|move|resize)/);if(a){hs.dragArgs={exp:f,type:a[1],left:f.x.pos,width:f.x.size,top:f.y.pos,height:f.y.size,clickX:d.clientX,clickY:d.clientY};hs.addEventListener(document,"mousemove",hs.dragHandler);if(d.preventDefault){d.preventDefault()}if(/highslide-(image|html)-blur/.test(f.content.className)){f.focus();hs.hasFocused=true}return false}else{if(/highslide-html/.test(b.className)&&hs.focusKey!=f.key){f.focus();f.doShowHide("hidden")}}}else{if(d.type=="mouseup"){hs.removeEventListener(document,"mousemove",hs.dragHandler);if(hs.dragArgs){if(hs.styleRestoreCursor&&hs.dragArgs.type=="image"){hs.dragArgs.exp.content.style.cursor=hs.styleRestoreCursor}var c=hs.dragArgs.hasDragged;if(!c&&!hs.hasFocused&&!/(move|resize)/.test(hs.dragArgs.type)){if(hs.fireEvent(f,"onImageClick")){f.close()}}else{if(c||(!c&&hs.hasHtmlExpanders)){hs.dragArgs.exp.doShowHide("hidden")}}if(hs.dragArgs.exp.releaseMask){hs.dragArgs.exp.releaseMask.style.display="none"}if(c){hs.fireEvent(hs.dragArgs.exp,"onDrop",hs.dragArgs)}hs.hasFocused=false;hs.dragArgs=null}else{if(/highslide-image-blur/.test(b.className)){b.style.cursor=hs.styleRestoreCursor}}}}return false},dragHandler:function(c){if(!hs.dragArgs){return true}if(!c){c=window.event}var b=hs.dragArgs,d=b.exp;if(d.iframe){if(!d.releaseMask){d.releaseMask=hs.createElement("div",null,{position:"absolute",width:d.x.size+"px",height:d.y.size+"px",left:d.x.cb+"px",top:d.y.cb+"px",zIndex:4,background:(hs.ieLt9?"white":"none"),opacity:0.01},d.wrapper,true)}if(d.releaseMask.style.display=="none"){d.releaseMask.style.display=""}}b.dX=c.clientX-b.clickX;b.dY=c.clientY-b.clickY;var f=Math.sqrt(Math.pow(b.dX,2)+Math.pow(b.dY,2));if(!b.hasDragged){b.hasDragged=(b.type!="image"&&f>0)||(f>(hs.dragSensitivity||5))}if(b.hasDragged&&c.clientX>5&&c.clientY>5){if(!hs.fireEvent(d,"onDrag",b)){return false}if(b.type=="resize"){d.resize(b)}else{d.moveTo(b.left+b.dX,b.top+b.dY);if(b.type=="image"){d.content.style.cursor="move"}}}return false},wrapperMouseHandler:function(c){try{if(!c){c=window.event}var b=/mouseover/i.test(c.type);if(!c.target){c.target=c.srcElement}if(!c.relatedTarget){c.relatedTarget=b?c.fromElement:c.toElement}var d=hs.getExpander(c.target);if(!d.isExpanded){return}if(!d||!c.relatedTarget||hs.getExpander(c.relatedTarget,true)==d||hs.dragArgs){return}hs.fireEvent(d,b?"onMouseOver":"onMouseOut",c);for(var a=0;a<d.overlays.length;a++){(function(){var e=hs.$("hsId"+d.overlays[a]);if(e&&e.hideOnMouseOut){if(b){hs.setStyles(e,{visibility:"visible",display:""})}hs.animate(e,{opacity:b?e.opacity:0},e.dur)}})()}}catch(c){}},addEventListener:function(a,c,b){if(a==document&&c=="ready"){hs.push(hs.onReady,b)}try{a.addEventListener(c,b,false)}catch(d){try{a.detachEvent("on"+c,b);a.attachEvent("on"+c,b)}catch(d){a["on"+c]=b}}},removeEventListener:function(a,c,b){try{a.removeEventListener(c,b,false)}catch(d){try{a.detachEvent("on"+c,b)}catch(d){a["on"+c]=null}}},preloadFullImage:function(b){if(hs.continuePreloading&&hs.preloadTheseImages[b]&&hs.preloadTheseImages[b]!="undefined"){var a=document.createElement("img");a.onload=function(){a=null;hs.preloadFullImage(b+1)};a.src=hs.preloadTheseImages[b]}},preloadImages:function(c){if(c&&typeof c!="object"){hs.numberOfImagesToPreload=c}var a=hs.getAnchors();for(var b=0;b<a.images.length&&b<hs.numberOfImagesToPreload;b++){hs.push(hs.preloadTheseImages,hs.getSrc(a.images[b]))}if(hs.outlineType){new hs.Outline(hs.outlineType,function(){hs.preloadFullImage(0)})}else{hs.preloadFullImage(0)}if(hs.restoreCursor){var d=hs.createElement("img",{src:hs.graphicsDir+hs.restoreCursor})}},init:function(){if(!hs.container){hs.ieLt7=hs.ie&&hs.uaVersion<7;hs.ieLt9=hs.ie&&hs.uaVersion<9;hs.getPageSize();hs.ie6SSL=hs.ieLt7&&location.protocol=="https:";for(var a in hs.langDefaults){if(typeof hs[a]!="undefined"){hs.lang[a]=hs[a]}else{if(typeof hs.lang[a]=="undefined"&&typeof hs.langDefaults[a]!="undefined"){hs.lang[a]=hs.langDefaults[a]}}}hs.container=hs.createElement("div",{className:"highslide-container"},{position:"absolute",left:0,top:0,width:"100%",zIndex:hs.zIndexCounter,direction:"ltr"},document.body,true);hs.loading=hs.createElement("a",{className:"highslide-loading",title:hs.lang.loadingTitle,innerHTML:hs.lang.loadingText,href:"javascript:;"},{position:"absolute",top:"-9999px",opacity:hs.loadingOpacity,zIndex:1},hs.container);hs.garbageBin=hs.createElement("div",null,{display:"none"},hs.container);hs.viewport=hs.createElement("div",{className:"highslide-viewport highslide-viewport-size"},{visibility:(hs.safari&&hs.uaVersion<525)?"visible":"hidden"},hs.container,1);hs.clearing=hs.createElement("div",null,{clear:"both",paddingTop:"1px"},null,true);Math.linearTween=function(f,e,h,g){return h*f/g+e};Math.easeInQuad=function(f,e,h,g){return h*(f/=g)*f+e};Math.easeOutQuad=function(f,e,h,g){return -h*(f/=g)*(f-2)+e};hs.hideSelects=hs.ieLt7;hs.hideIframes=((window.opera&&hs.uaVersion<9)||navigator.vendor=="KDE"||(hs.ieLt7&&hs.uaVersion<5.5));hs.fireEvent(this,"onActivate")}},ready:function(){if(hs.isReady){return}hs.isReady=true;for(var a=0;a<hs.onReady.length;a++){hs.onReady[a]()}},updateAnchors:function(){var a,d,l=[],h=[],k=[],b={},m;for(var e=0;e<hs.openerTagNames.length;e++){d=document.getElementsByTagName(hs.openerTagNames[e]);for(var c=0;c<d.length;c++){a=d[c];m=hs.isHsAnchor(a);if(m){hs.push(l,a);if(m[0]=="hs.expand"){hs.push(h,a)}else{if(m[0]=="hs.htmlExpand"){hs.push(k,a)}}var f=hs.getParam(a,"slideshowGroup")||"none";if(!b[f]){b[f]=[]}hs.push(b[f],a)}}}hs.anchors={all:l,groups:b,images:h,htmls:k};return hs.anchors},getAnchors:function(){return hs.anchors||hs.updateAnchors()},close:function(a){var b=hs.getExpander(a);if(b){b.close()}return false}};hs.fx=function(b,a,c){this.options=a;this.elem=b;this.prop=c;if(!a.orig){a.orig={}}};hs.fx.prototype={update:function(){(hs.fx.step[this.prop]||hs.fx.step._default)(this);if(this.options.step){this.options.step.call(this.elem,this.now,this)}},custom:function(e,d,c){this.startTime=(new Date()).getTime();this.start=e;this.end=d;this.unit=c;this.now=this.start;this.pos=this.state=0;var a=this;function b(f){return a.step(f)}b.elem=this.elem;if(b()&&hs.timers.push(b)==1){hs.timerId=setInterval(function(){var g=hs.timers;for(var f=0;f<g.length;f++){if(!g[f]()){g.splice(f--,1)}}if(!g.length){clearInterval(hs.timerId)}},13)}},step:function(d){var c=(new Date()).getTime();if(d||c>=this.options.duration+this.startTime){this.now=this.end;this.pos=this.state=1;this.update();this.options.curAnim[this.prop]=true;var a=true;for(var b in this.options.curAnim){if(this.options.curAnim[b]!==true){a=false}}if(a){if(this.options.complete){this.options.complete.call(this.elem)}}return false}else{var e=c-this.startTime;this.state=e/this.options.duration;this.pos=this.options.easing(e,0,1,this.options.duration);this.now=this.start+((this.end-this.start)*this.pos);this.update()}return true}};hs.extend(hs.fx,{step:{opacity:function(a){hs.setStyles(a.elem,{opacity:a.now})},_default:function(a){try{if(a.elem.style&&a.elem.style[a.prop]!=null){a.elem.style[a.prop]=a.now+a.unit}else{a.elem[a.prop]=a.now}}catch(b){}}}});hs.Outline=function(g,e){this.onLoad=e;this.outlineType=g;var a=hs.uaVersion,f;this.hasAlphaImageLoader=hs.ie&&hs.uaVersion<7;if(!g){if(e){e()}return}hs.init();this.table=hs.createElement("table",{cellSpacing:0},{visibility:"hidden",position:"absolute",borderCollapse:"collapse",width:0},hs.container,true);var b=hs.createElement("tbody",null,null,this.table,1);this.td=[];for(var c=0;c<=8;c++){if(c%3==0){f=hs.createElement("tr",null,{height:"auto"},b,true)}this.td[c]=hs.createElement("td",null,null,f,true);var d=c!=4?{lineHeight:0,fontSize:0}:{position:"relative"};hs.setStyles(this.td[c],d)}this.td[4].className=g+" highslide-outline";this.preloadGraphic()};hs.Outline.prototype={preloadGraphic:function(){var b=hs.graphicsDir+(hs.outlinesDir||"outlines/")+this.outlineType+".png";var a=hs.safari&&hs.uaVersion<525?hs.container:null;this.graphic=hs.createElement("img",null,{position:"absolute",top:"-9999px"},a,true);var c=this;this.graphic.onload=function(){c.onGraphicLoad()};this.graphic.src=b},onGraphicLoad:function(){var d=this.offset=this.graphic.width/4,f=[[0,0],[0,-4],[-2,0],[0,-8],0,[-2,-8],[0,-2],[0,-6],[-2,-2]],c={height:(2*d)+"px",width:(2*d)+"px"};for(var b=0;b<=8;b++){if(f[b]){if(this.hasAlphaImageLoader){var a=(b==1||b==7)?"100%":this.graphic.width+"px";var e=hs.createElement("div",null,{width:"100%",height:"100%",position:"relative",overflow:"hidden"},this.td[b],true);hs.createElement("div",null,{filter:"progid:DXImageTransform.Microsoft.AlphaImageLoader(sizingMethod=scale, src='"+this.graphic.src+"')",position:"absolute",width:a,height:this.graphic.height+"px",left:(f[b][0]*d)+"px",top:(f[b][1]*d)+"px"},e,true)}else{hs.setStyles(this.td[b],{background:"url("+this.graphic.src+") "+(f[b][0]*d)+"px "+(f[b][1]*d)+"px"})}if(window.opera&&(b==3||b==5)){hs.createElement("div",null,c,this.td[b],true)}hs.setStyles(this.td[b],c)}}this.graphic=null;if(hs.pendingOutlines[this.outlineType]){hs.pendingOutlines[this.outlineType].destroy()}hs.pendingOutlines[this.outlineType]=this;if(this.onLoad){this.onLoad()}},setPosition:function(g,e,c,b,f){var d=this.exp,a=d.wrapper.style,e=e||0,g=g||{x:d.x.pos+e,y:d.y.pos+e,w:d.x.get("wsize")-2*e,h:d.y.get("wsize")-2*e};if(c){this.table.style.visibility=(g.h>=4*this.offset)?"visible":"hidden"}hs.setStyles(this.table,{left:(g.x-this.offset)+"px",top:(g.y-this.offset)+"px",width:(g.w+2*this.offset)+"px"});g.w-=2*this.offset;g.h-=2*this.offset;hs.setStyles(this.td[4],{width:g.w>=0?g.w+"px":0,height:g.h>=0?g.h+"px":0});if(this.hasAlphaImageLoader){this.td[3].style.height=this.td[5].style.height=this.td[4].style.height}},destroy:function(a){if(a){this.table.style.visibility="hidden"}else{hs.discardElement(this.table)}}};hs.Dimension=function(b,a){this.exp=b;this.dim=a;this.ucwh=a=="x"?"Width":"Height";this.wh=this.ucwh.toLowerCase();this.uclt=a=="x"?"Left":"Top";this.lt=this.uclt.toLowerCase();this.ucrb=a=="x"?"Right":"Bottom";this.rb=this.ucrb.toLowerCase();this.p1=this.p2=0};hs.Dimension.prototype={get:function(a){switch(a){case"loadingPos":return this.tpos+this.tb+(this.t-hs.loading["offset"+this.ucwh])/2;case"loadingPosXfade":return this.pos+this.cb+this.p1+(this.size-hs.loading["offset"+this.ucwh])/2;case"wsize":return this.size+2*this.cb+this.p1+this.p2;case"fitsize":return this.clientSize-this.marginMin-this.marginMax;case"maxsize":return this.get("fitsize")-2*this.cb-this.p1-this.p2;case"opos":return this.pos-(this.exp.outline?this.exp.outline.offset:0);case"osize":return this.get("wsize")+(this.exp.outline?2*this.exp.outline.offset:0);case"imgPad":return this.imgSize?Math.round((this.size-this.imgSize)/2):0}},calcBorders:function(){this.cb=(this.exp.content["offset"+this.ucwh]-this.t)/2;this.marginMax=hs["margin"+this.ucrb]},calcThumb:function(){this.t=this.exp.el[this.wh]?parseInt(this.exp.el[this.wh]):this.exp.el["offset"+this.ucwh];this.tpos=this.exp.tpos[this.dim];this.tb=(this.exp.el["offset"+this.ucwh]-this.t)/2;if(this.tpos==0||this.tpos==-1){this.tpos=(hs.page[this.wh]/2)+hs.page["scroll"+this.uclt]}},calcExpanded:function(){var a=this.exp;this.justify="auto";if(a.align=="center"){this.justify="center"}else{if(new RegExp(this.lt).test(a.anchor)){this.justify=null}else{if(new RegExp(this.rb).test(a.anchor)){this.justify="max"}}}this.pos=this.tpos-this.cb+this.tb;if(this.maxHeight&&this.dim=="x"){a.maxWidth=Math.min(a.maxWidth||this.full,a.maxHeight*this.full/a.y.full)}this.size=Math.min(this.full,a["max"+this.ucwh]||this.full);this.minSize=a.allowSizeReduction?Math.min(a["min"+this.ucwh],this.full):this.full;if(a.isImage&&a.useBox){this.size=a[this.wh];this.imgSize=this.full}if(this.dim=="x"&&hs.padToMinWidth){this.minSize=a.minWidth}this.target=a["target"+this.dim.toUpperCase()];this.marginMin=hs["margin"+this.uclt];this.scroll=hs.page["scroll"+this.uclt];this.clientSize=hs.page[this.wh]},setSize:function(a){var f=this.exp;if(f.isImage&&(f.useBox||hs.padToMinWidth)){this.imgSize=a;this.size=Math.max(this.size,this.imgSize);f.content.style[this.lt]=this.get("imgPad")+"px"}else{this.size=a}f.content.style[this.wh]=a+"px";f.wrapper.style[this.wh]=this.get("wsize")+"px";if(f.outline){f.outline.setPosition()}if(f.releaseMask){f.releaseMask.style[this.wh]=a+"px"}if(this.dim=="y"&&f.iDoc&&f.body.style.height!="auto"){try{f.iDoc.body.style.overflow="auto"}catch(b){}}if(f.isHtml){var c=f.scrollerDiv;if(this.sizeDiff===undefined){this.sizeDiff=f.innerContent["offset"+this.ucwh]-c["offset"+this.ucwh]}c.style[this.wh]=(this.size-this.sizeDiff)+"px";if(this.dim=="x"){f.mediumContent.style.width="auto"}if(f.body){f.body.style[this.wh]="auto"}}if(this.dim=="x"&&f.overlayBox){f.sizeOverlayBox(true)}if(this.dim=="x"&&f.slideshow&&f.isImage){if(a==this.full){f.slideshow.disable("full-expand")}else{f.slideshow.enable("full-expand")}}},setPos:function(a){this.pos=a;this.exp.wrapper.style[this.lt]=a+"px";if(this.exp.outline){this.exp.outline.setPosition()}}};hs.Expander=function(k,f,b,l){if(document.readyState&&hs.ie&&!hs.isReady){hs.addEventListener(document,"ready",function(){new hs.Expander(k,f,b,l)});return}this.a=k;this.custom=b;this.contentType=l||"image";this.isHtml=(l=="html");this.isImage=!this.isHtml;hs.continuePreloading=false;this.overlays=[];this.last=hs.last;hs.last=null;hs.init();var m=this.key=hs.expanders.length;for(var g=0;g<hs.overrides.length;g++){var c=hs.overrides[g];this[c]=f&&typeof f[c]!="undefined"?f[c]:hs[c]}if(!this.src){this.src=k.href}var d=(f&&f.thumbnailId)?hs.$(f.thumbnailId):k;d=this.thumb=d.getElementsByTagName("img")[0]||d;this.thumbsUserSetId=d.id||k.id;if(!hs.fireEvent(this,"onInit")){return true}for(var g=0;g<hs.expanders.length;g++){if(hs.expanders[g]&&hs.expanders[g].a==k&&!(this.last&&this.transitions[1]=="crossfade")){hs.expanders[g].focus();return false}}if(!hs.allowSimultaneousLoading){for(var g=0;g<hs.expanders.length;g++){if(hs.expanders[g]&&hs.expanders[g].thumb!=d&&!hs.expanders[g].onLoadStarted){hs.expanders[g].cancelLoading()}}}hs.expanders[m]=this;if(!hs.allowMultipleInstances&&!hs.upcoming){if(hs.expanders[m-1]){hs.expanders[m-1].close()}if(typeof hs.focusKey!="undefined"&&hs.expanders[hs.focusKey]){hs.expanders[hs.focusKey].close()}}this.el=d;this.tpos=this.pageOrigin||hs.getPosition(d);hs.getPageSize();var j=this.x=new hs.Dimension(this,"x");j.calcThumb();var h=this.y=new hs.Dimension(this,"y");h.calcThumb();if(/area/i.test(d.tagName)){this.getImageMapAreaCorrection(d)}this.wrapper=hs.createElement("div",{id:"highslide-wrapper-"+this.key,className:"highslide-wrapper "+this.wrapperClassName},{visibility:"hidden",position:"absolute",zIndex:hs.zIndexCounter+=2},null,true);this.wrapper.onmouseover=this.wrapper.onmouseout=hs.wrapperMouseHandler;if(this.contentType=="image"&&this.outlineWhileAnimating==2){this.outlineWhileAnimating=0}if(!this.outlineType||(this.last&&this.isImage&&this.transitions[1]=="crossfade")){this[this.contentType+"Create"]()}else{if(hs.pendingOutlines[this.outlineType]){this.connectOutline();this[this.contentType+"Create"]()}else{this.showLoading();var e=this;new hs.Outline(this.outlineType,function(){e.connectOutline();e[e.contentType+"Create"]()})}}return true};hs.Expander.prototype={error:function(a){if(hs.debug){alert("Line "+a.lineNumber+": "+a.message)}else{window.location.href=this.src}},connectOutline:function(){var a=this.outline=hs.pendingOutlines[this.outlineType];a.exp=this;a.table.style.zIndex=this.wrapper.style.zIndex-1;hs.pendingOutlines[this.outlineType]=null},showLoading:function(){if(this.onLoadStarted||this.loading){return}this.loading=hs.loading;var c=this;this.loading.onclick=function(){c.cancelLoading()};if(!hs.fireEvent(this,"onShowLoading")){return}var c=this,a=this.x.get("loadingPos")+"px",b=this.y.get("loadingPos")+"px";if(!d&&this.last&&this.transitions[1]=="crossfade"){var d=this.last}if(d){a=d.x.get("loadingPosXfade")+"px";b=d.y.get("loadingPosXfade")+"px";this.loading.style.zIndex=hs.zIndexCounter++}setTimeout(function(){if(c.loading){hs.setStyles(c.loading,{left:a,top:b,zIndex:hs.zIndexCounter++})}},100)},imageCreate:function(){var b=this;var a=document.createElement("img");this.content=a;a.onload=function(){if(hs.expanders[b.key]){b.contentLoaded()}};if(hs.blockRightClick){a.oncontextmenu=function(){return false}}a.className="highslide-image";hs.setStyles(a,{visibility:"hidden",display:"block",position:"absolute",maxWidth:"9999px",zIndex:3});a.title=hs.lang.restoreTitle;if(hs.safari&&hs.uaVersion<525){hs.container.appendChild(a)}if(hs.ie&&hs.flushImgSize){a.src=null}a.src=this.src;this.showLoading()},htmlCreate:function(){if(!hs.fireEvent(this,"onBeforeGetContent")){return}this.content=hs.getCacheBinding(this.a);if(!this.content){this.content=hs.getNode(this.contentId)}if(!this.content){this.content=hs.getSelfRendered()}this.getInline(["maincontent"]);if(this.maincontent){var a=hs.getElementByClass(this.content,"div","highslide-body");if(a){a.appendChild(this.maincontent)}this.maincontent.style.display="block"}hs.fireEvent(this,"onAfterGetContent");var d=this.innerContent=this.content;if(/(swf|iframe)/.test(this.objectType)){this.setObjContainerSize(d)}hs.container.appendChild(this.wrapper);hs.setStyles(this.wrapper,{position:"static",padding:"0 "+hs.marginRight+"px 0 "+hs.marginLeft+"px"});this.content=hs.createElement("div",{className:"highslide-html"},{position:"relative",zIndex:3,height:0,overflow:"hidden"},this.wrapper);this.mediumContent=hs.createElement("div",null,null,this.content,1);this.mediumContent.appendChild(d);hs.setStyles(d,{position:"relative",display:"block",direction:hs.lang.cssDirection||""});if(this.width){d.style.width=this.width+"px"}if(this.height){hs.setStyles(d,{height:this.height+"px",overflow:"hidden"})}if(d.offsetWidth<this.minWidth){d.style.width=this.minWidth+"px"}if(this.objectType=="ajax"&&!hs.getCacheBinding(this.a)){this.showLoading();var c=this;var b=new hs.Ajax(this.a,d);b.src=this.src;b.onLoad=function(){if(hs.expanders[c.key]){c.contentLoaded()}};b.onError=function(){location.href=c.src};b.run()}else{if(this.objectType=="iframe"&&this.objectLoadTime=="before"){this.writeExtendedContent()}else{this.contentLoaded()}}},contentLoaded:function(){try{if(!this.content){return}this.content.onload=null;if(this.onLoadStarted){return}else{this.onLoadStarted=true}var j=this.x,g=this.y;if(this.loading){hs.setStyles(this.loading,{top:"-9999px"});this.loading=null;hs.fireEvent(this,"onHideLoading")}if(this.isImage){j.full=this.content.width;g.full=this.content.height;hs.setStyles(this.content,{width:j.t+"px",height:g.t+"px"});this.wrapper.appendChild(this.content);hs.container.appendChild(this.wrapper)}else{if(this.htmlGetSize){this.htmlGetSize()}}j.calcBorders();g.calcBorders();hs.setStyles(this.wrapper,{left:(j.tpos+j.tb-j.cb)+"px",top:(g.tpos+j.tb-g.cb)+"px"});this.initSlideshow();this.getOverlays();var f=j.full/g.full;j.calcExpanded();this.justify(j);g.calcExpanded();this.justify(g);if(this.isHtml){this.htmlSizeOperations()}if(this.overlayBox){this.sizeOverlayBox(0,1)}if(this.allowSizeReduction){if(this.isImage){this.correctRatio(f)}else{this.fitOverlayBox()}var k=this.slideshow;if(k&&this.last&&k.controls&&k.fixedControls){var h=k.overlayOptions.position||"",a;for(var c in hs.oPos){for(var b=0;b<5;b++){a=this[c];if(h.match(hs.oPos[c][b])){a.pos=this.last[c].pos+(this.last[c].p1-a.p1)+(this.last[c].size-a.size)*[0,0,0.5,1,1][b];if(k.fixedControls=="fit"){if(a.pos+a.size+a.p1+a.p2>a.scroll+a.clientSize-a.marginMax){a.pos=a.scroll+a.clientSize-a.size-a.marginMin-a.marginMax-a.p1-a.p2}if(a.pos<a.scroll+a.marginMin){a.pos=a.scroll+a.marginMin}}}}}}if(this.isImage&&this.x.full>(this.x.imgSize||this.x.size)){this.createFullExpand();if(this.overlays.length==1){this.sizeOverlayBox()}}}this.show()}catch(d){this.error(d)}},setObjContainerSize:function(a,d){var b=hs.getElementByClass(a,"DIV","highslide-body");if(/(iframe|swf)/.test(this.objectType)){if(this.objectWidth){b.style.width=this.objectWidth+"px"}if(this.objectHeight){b.style.height=this.objectHeight+"px"}}},writeExtendedContent:function(){if(this.hasExtendedContent){return}var f=this;this.body=hs.getElementByClass(this.innerContent,"DIV","highslide-body");if(this.objectType=="iframe"){this.showLoading();var g=hs.clearing.cloneNode(1);this.body.appendChild(g);this.newWidth=this.innerContent.offsetWidth;if(!this.objectWidth){this.objectWidth=g.offsetWidth}var c=this.innerContent.offsetHeight-this.body.offsetHeight,d=this.objectHeight||hs.page.height-c-hs.marginTop-hs.marginBottom,e=this.objectLoadTime=="before"?' onload="if (hs.expanders['+this.key+"]) hs.expanders["+this.key+'].contentLoaded()" ':"";this.body.innerHTML+='<iframe name="hs'+(new Date()).getTime()+'" frameborder="0" key="'+this.key+'"  style="width:'+this.objectWidth+"px; height:"+d+'px" '+e+' src="'+this.src+'" ></iframe>';this.ruler=this.body.getElementsByTagName("div")[0];this.iframe=this.body.getElementsByTagName("iframe")[0];if(this.objectLoadTime=="after"){this.correctIframeSize()}}if(this.objectType=="swf"){this.body.id=this.body.id||"hs-flash-id-"+this.key;var b=this.swfOptions;if(!b.params){b.params={}}if(typeof b.params.wmode=="undefined"){b.params.wmode="transparent"}if(swfobject){swfobject.embedSWF(this.src,this.body.id,this.objectWidth,this.objectHeight,b.version||"7",b.expressInstallSwfurl,b.flashvars,b.params,b.attributes)}}this.hasExtendedContent=true},htmlGetSize:function(){if(this.iframe&&!this.objectHeight){this.iframe.style.height=this.body.style.height=this.getIframePageHeight()+"px"}this.innerContent.appendChild(hs.clearing);if(!this.x.full){this.x.full=this.innerContent.offsetWidth}this.y.full=this.innerContent.offsetHeight;this.innerContent.removeChild(hs.clearing);if(hs.ie&&this.newHeight>parseInt(this.innerContent.currentStyle.height)){this.newHeight=parseInt(this.innerContent.currentStyle.height)}hs.setStyles(this.wrapper,{position:"absolute",padding:"0"});hs.setStyles(this.content,{width:this.x.t+"px",height:this.y.t+"px"})},getIframePageHeight:function(){var a;try{var d=this.iDoc=this.iframe.contentDocument||this.iframe.contentWindow.document;var b=d.createElement("div");b.style.clear="both";d.body.appendChild(b);a=b.offsetTop;if(hs.ie){a+=parseInt(d.body.currentStyle.marginTop)+parseInt(d.body.currentStyle.marginBottom)-1}}catch(c){a=300}return a},correctIframeSize:function(){var b=this.innerContent.offsetWidth-this.ruler.offsetWidth;hs.discardElement(this.ruler);if(b<0){b=0}var a=this.innerContent.offsetHeight-this.iframe.offsetHeight;if(this.iDoc&&!this.objectHeight&&!this.height&&this.y.size==this.y.full){try{this.iDoc.body.style.overflow="hidden"}catch(c){}}hs.setStyles(this.iframe,{width:Math.abs(this.x.size-b)+"px",height:Math.abs(this.y.size-a)+"px"});hs.setStyles(this.body,{width:this.iframe.style.width,height:this.iframe.style.height});this.scrollingContent=this.iframe;this.scrollerDiv=this.scrollingContent},htmlSizeOperations:function(){this.setObjContainerSize(this.innerContent);if(this.objectType=="swf"&&this.objectLoadTime=="before"){this.writeExtendedContent()}if(this.x.size<this.x.full&&!this.allowWidthReduction){this.x.size=this.x.full}if(this.y.size<this.y.full&&!this.allowHeightReduction){this.y.size=this.y.full}this.scrollerDiv=this.innerContent;hs.setStyles(this.mediumContent,{position:"relative",width:this.x.size+"px"});hs.setStyles(this.innerContent,{border:"none",width:"auto",height:"auto"});var e=hs.getElementByClass(this.innerContent,"DIV","highslide-body");if(e&&!/(iframe|swf)/.test(this.objectType)){var b=e;e=hs.createElement(b.nodeName,null,{overflow:"hidden"},null,true);b.parentNode.insertBefore(e,b);e.appendChild(hs.clearing);e.appendChild(b);var c=this.innerContent.offsetWidth-e.offsetWidth;var a=this.innerContent.offsetHeight-e.offsetHeight;e.removeChild(hs.clearing);var d=hs.safari||navigator.vendor=="KDE"?1:0;hs.setStyles(e,{width:(this.x.size-c-d)+"px",height:(this.y.size-a)+"px",overflow:"auto",position:"relative"});if(d&&b.offsetHeight>e.offsetHeight){e.style.width=(parseInt(e.style.width)+d)+"px"}this.scrollingContent=e;this.scrollerDiv=this.scrollingContent}if(this.iframe&&this.objectLoadTime=="before"){this.correctIframeSize()}if(!this.scrollingContent&&this.y.size<this.mediumContent.offsetHeight){this.scrollerDiv=this.content}if(this.scrollerDiv==this.content&&!this.allowWidthReduction&&!/(iframe|swf)/.test(this.objectType)){this.x.size+=17}if(this.scrollerDiv&&this.scrollerDiv.offsetHeight>this.scrollerDiv.parentNode.offsetHeight){setTimeout("try { hs.expanders["+this.key+"].scrollerDiv.style.overflow = 'auto'; } catch(e) {}",hs.expandDuration)}},getImageMapAreaCorrection:function(d){var h=d.coords.split(",");for(var b=0;b<h.length;b++){h[b]=parseInt(h[b])}if(d.shape.toLowerCase()=="circle"){this.x.tpos+=h[0]-h[2];this.y.tpos+=h[1]-h[2];this.x.t=this.y.t=2*h[2]}else{var f,e,a=f=h[0],g=e=h[1];for(var b=0;b<h.length;b++){if(b%2==0){a=Math.min(a,h[b]);f=Math.max(f,h[b])}else{g=Math.min(g,h[b]);e=Math.max(e,h[b])}}this.x.tpos+=a;this.x.t=f-a;this.y.tpos+=g;this.y.t=e-g}},justify:function(f,b){var g,h=f.target,e=f==this.x?"x":"y";if(h&&h.match(/ /)){g=h.split(" ");h=g[0]}if(h&&hs.$(h)){f.pos=hs.getPosition(hs.$(h))[e];if(g&&g[1]&&g[1].match(/^[-]?[0-9]+px$/)){f.pos+=parseInt(g[1])}if(f.size<f.minSize){f.size=f.minSize}}else{if(f.justify=="auto"||f.justify=="center"){var d=false;var a=f.exp.allowSizeReduction;if(f.justify=="center"){f.pos=Math.round(f.scroll+(f.clientSize+f.marginMin-f.marginMax-f.get("wsize"))/2)}else{f.pos=Math.round(f.pos-((f.get("wsize")-f.t)/2))}if(f.pos<f.scroll+f.marginMin){f.pos=f.scroll+f.marginMin;d=true}if(!b&&f.size<f.minSize){f.size=f.minSize;a=false}if(f.pos+f.get("wsize")>f.scroll+f.clientSize-f.marginMax){if(!b&&d&&a){f.size=Math.min(f.size,f.get(e=="y"?"fitsize":"maxsize"))}else{if(f.get("wsize")<f.get("fitsize")){f.pos=f.scroll+f.clientSize-f.marginMax-f.get("wsize")}else{f.pos=f.scroll+f.marginMin;if(!b&&a){f.size=f.get(e=="y"?"fitsize":"maxsize")}}}}if(!b&&f.size<f.minSize){f.size=f.minSize;a=false}}else{if(f.justify=="max"){f.pos=Math.floor(f.pos-f.size+f.t)}}}if(f.pos<f.marginMin){var c=f.pos;f.pos=f.marginMin;if(a&&!b){f.size=f.size-(f.pos-c)}}},correctRatio:function(c){var a=this.x,g=this.y,e=false,d=Math.min(a.full,a.size),b=Math.min(g.full,g.size),f=(this.useBox||hs.padToMinWidth);if(d/b>c){d=b*c;if(d<a.minSize){d=a.minSize;b=d/c}e=true}else{if(d/b<c){b=d/c;e=true}}if(hs.padToMinWidth&&a.full<a.minSize){a.imgSize=a.full;g.size=g.imgSize=g.full}else{if(this.useBox){a.imgSize=d;g.imgSize=b}else{a.size=d;g.size=b}}e=this.fitOverlayBox(this.useBox?null:c,e);if(f&&g.size<g.imgSize){g.imgSize=g.size;a.imgSize=g.size*c}if(e||f){a.pos=a.tpos-a.cb+a.tb;a.minSize=a.size;this.justify(a,true);g.pos=g.tpos-g.cb+g.tb;g.minSize=g.size;this.justify(g,true);if(this.overlayBox){this.sizeOverlayBox()}}},fitOverlayBox:function(b,c){var a=this.x,d=this.y;if(this.overlayBox&&(this.isImage||this.allowHeightReduction)){while(d.size>this.minHeight&&a.size>this.minWidth&&d.get("wsize")>d.get("fitsize")){d.size-=10;if(b){a.size=d.size*b}this.sizeOverlayBox(0,1);c=true}}return c},reflow:function(){if(this.scrollerDiv){var a=/iframe/i.test(this.scrollerDiv.tagName)?(this.getIframePageHeight()+1)+"px":"auto";if(this.body){this.body.style.height=a}this.scrollerDiv.style.height=a;this.y.setSize(this.innerContent.offsetHeight)}},show:function(){var a=this.x,b=this.y;this.doShowHide("hidden");hs.fireEvent(this,"onBeforeExpand");if(this.slideshow&&this.slideshow.thumbstrip){this.slideshow.thumbstrip.selectThumb()}this.changeSize(1,{wrapper:{width:a.get("wsize"),height:b.get("wsize"),left:a.pos,top:b.pos},content:{left:a.p1+a.get("imgPad"),top:b.p1+b.get("imgPad"),width:a.imgSize||a.size,height:b.imgSize||b.size}},hs.expandDuration)},changeSize:function(d,i,b){var k=this.transitions,e=d?(this.last?this.last.a:null):hs.upcoming,j=(k[1]&&e&&hs.getParam(e,"transitions")[1]==k[1])?k[1]:k[0];if(this[j]&&j!="expand"){this[j](d,i);return}if(this.outline&&!this.outlineWhileAnimating){if(d){this.outline.setPosition()}else{this.outline.destroy((this.isHtml&&this.preserveContent))}}if(!d){this.destroyOverlays()}var c=this,h=c.x,g=c.y,f=this.easing;if(!d){f=this.easingClose||f}var a=d?function(){if(c.outline){c.outline.table.style.visibility="visible"}setTimeout(function(){c.afterExpand()},50)}:function(){c.afterClose()};if(d){hs.setStyles(this.wrapper,{width:h.t+"px",height:g.t+"px"})}if(d&&this.isHtml){hs.setStyles(this.wrapper,{left:(h.tpos-h.cb+h.tb)+"px",top:(g.tpos-g.cb+g.tb)+"px"})}if(this.fadeInOut){hs.setStyles(this.wrapper,{opacity:d?0:1});hs.extend(i.wrapper,{opacity:d})}hs.animate(this.wrapper,i.wrapper,{duration:b,easing:f,step:function(n,l){if(c.outline&&c.outlineWhileAnimating&&l.prop=="top"){var m=d?l.pos:1-l.pos;var o={w:h.t+(h.get("wsize")-h.t)*m,h:g.t+(g.get("wsize")-g.t)*m,x:h.tpos+(h.pos-h.tpos)*m,y:g.tpos+(g.pos-g.tpos)*m};c.outline.setPosition(o,0,1)}if(c.isHtml){if(l.prop=="left"){c.mediumContent.style.left=(h.pos-n)+"px"}if(l.prop=="top"){c.mediumContent.style.top=(g.pos-n)+"px"}}}});hs.animate(this.content,i.content,b,f,a);if(d){this.wrapper.style.visibility="visible";this.content.style.visibility="visible";if(this.isHtml){this.innerContent.style.visibility="visible"}this.a.className+=" highslide-active-anchor"}},fade:function(f,h){this.outlineWhileAnimating=false;var c=this,j=f?hs.expandDuration:0;if(f){hs.animate(this.wrapper,h.wrapper,0);hs.setStyles(this.wrapper,{opacity:0,visibility:"visible"});hs.animate(this.content,h.content,0);this.content.style.visibility="visible";hs.animate(this.wrapper,{opacity:1},j,null,function(){c.afterExpand()})}if(this.outline){this.outline.table.style.zIndex=this.wrapper.style.zIndex;var b=f||-1,d=this.outline.offset,a=f?3:d,g=f?d:3;for(var e=a;b*e<=b*g;e+=b,j+=25){(function(){var i=f?g-e:a-e;setTimeout(function(){c.outline.setPosition(0,i,1)},j)})()}}if(f){}else{setTimeout(function(){if(c.outline){c.outline.destroy(c.preserveContent)}c.destroyOverlays();hs.animate(c.wrapper,{opacity:0},hs.restoreDuration,null,function(){c.afterClose()})},j)}},crossfade:function(g,m,o){if(!g){return}var f=this,p=this.last,l=this.x,k=this.y,d=p.x,b=p.y,a=this.wrapper,i=this.content,c=this.overlayBox;hs.removeEventListener(document,"mousemove",hs.dragHandler);hs.setStyles(i,{width:(l.imgSize||l.size)+"px",height:(k.imgSize||k.size)+"px"});if(c){c.style.overflow="visible"}this.outline=p.outline;if(this.outline){this.outline.exp=f}p.outline=null;var h=hs.createElement("div",{className:"highslide-"+this.contentType},{position:"absolute",zIndex:4,overflow:"hidden",display:"none"});var j={oldImg:p,newImg:this};for(var e in j){this[e]=j[e].content.cloneNode(1);hs.setStyles(this[e],{position:"absolute",border:0,visibility:"visible"});h.appendChild(this[e])}a.appendChild(h);if(this.isHtml){hs.setStyles(this.mediumContent,{left:0,top:0})}if(c){c.className="";a.appendChild(c)}h.style.display="";p.content.style.display="none";if(hs.safari&&hs.uaVersion<525){this.wrapper.style.visibility="visible"}hs.animate(a,{width:l.size},{duration:hs.transitionDuration,step:function(u,r){var x=r.pos,q=1-x;var w,s={},t=["pos","size","p1","p2"];for(var v in t){w=t[v];s["x"+w]=Math.round(q*d[w]+x*l[w]);s["y"+w]=Math.round(q*b[w]+x*k[w]);s.ximgSize=Math.round(q*(d.imgSize||d.size)+x*(l.imgSize||l.size));s.ximgPad=Math.round(q*d.get("imgPad")+x*l.get("imgPad"));s.yimgSize=Math.round(q*(b.imgSize||b.size)+x*(k.imgSize||k.size));s.yimgPad=Math.round(q*b.get("imgPad")+x*k.get("imgPad"))}if(f.outline){f.outline.setPosition({x:s.xpos,y:s.ypos,w:s.xsize+s.xp1+s.xp2+2*l.cb,h:s.ysize+s.yp1+s.yp2+2*k.cb})}p.wrapper.style.clip="rect("+(s.ypos-b.pos)+"px, "+(s.xsize+s.xp1+s.xp2+s.xpos+2*d.cb-d.pos)+"px, "+(s.ysize+s.yp1+s.yp2+s.ypos+2*b.cb-b.pos)+"px, "+(s.xpos-d.pos)+"px)";hs.setStyles(i,{top:(s.yp1+k.get("imgPad"))+"px",left:(s.xp1+l.get("imgPad"))+"px",marginTop:(k.pos-s.ypos)+"px",marginLeft:(l.pos-s.xpos)+"px"});hs.setStyles(a,{top:s.ypos+"px",left:s.xpos+"px",width:(s.xp1+s.xp2+s.xsize+2*l.cb)+"px",height:(s.yp1+s.yp2+s.ysize+2*k.cb)+"px"});hs.setStyles(h,{width:(s.ximgSize||s.xsize)+"px",height:(s.yimgSize||s.ysize)+"px",left:(s.xp1+s.ximgPad)+"px",top:(s.yp1+s.yimgPad)+"px",visibility:"visible"});hs.setStyles(f.oldImg,{top:(b.pos-s.ypos+b.p1-s.yp1+b.get("imgPad")-s.yimgPad)+"px",left:(d.pos-s.xpos+d.p1-s.xp1+d.get("imgPad")-s.ximgPad)+"px"});hs.setStyles(f.newImg,{opacity:x,top:(k.pos-s.ypos+k.p1-s.yp1+k.get("imgPad")-s.yimgPad)+"px",left:(l.pos-s.xpos+l.p1-s.xp1+l.get("imgPad")-s.ximgPad)+"px"});if(c){hs.setStyles(c,{width:s.xsize+"px",height:s.ysize+"px",left:(s.xp1+l.cb)+"px",top:(s.yp1+k.cb)+"px"})}},complete:function(){a.style.visibility=i.style.visibility="visible";i.style.display="block";hs.discardElement(h);f.afterExpand();p.afterClose();f.last=null}})},reuseOverlay:function(d,c){if(!this.last){return false}for(var b=0;b<this.last.overlays.length;b++){var a=hs.$("hsId"+this.last.overlays[b]);if(a&&a.hsId==d.hsId){this.genOverlayBox();a.reuse=this.key;hs.push(this.overlays,this.last.overlays[b]);return true}}return false},afterExpand:function(){this.isExpanded=true;this.focus();if(this.isHtml&&this.objectLoadTime=="after"){this.writeExtendedContent()}if(this.iframe){try{var g=this,f=this.iframe.contentDocument||this.iframe.contentWindow.document;hs.addEventListener(f,"mousedown",function(){if(hs.focusKey!=g.key){g.focus()}})}catch(d){}if(hs.ie&&typeof this.isClosing!="boolean"){this.iframe.style.width=(this.objectWidth-1)+"px"}}if(this.dimmingOpacity){hs.dim(this)}if(hs.upcoming&&hs.upcoming==this.a){hs.upcoming=null}this.prepareNextOutline();var c=hs.page,b=hs.mouse.x+c.scrollLeft,a=hs.mouse.y+c.scrollTop;this.mouseIsOver=this.x.pos<b&&b<this.x.pos+this.x.get("wsize")&&this.y.pos<a&&a<this.y.pos+this.y.get("wsize");if(this.overlayBox){this.showOverlays()}hs.fireEvent(this,"onAfterExpand")},prepareNextOutline:function(){var a=this.key;var b=this.outlineType;new hs.Outline(b,function(){try{hs.expanders[a].preloadNext()}catch(c){}})},preloadNext:function(){var b=this.getAdjacentAnchor(1);if(b&&b.onclick.toString().match(/hs\.expand/)){var a=hs.createElement("img",{src:hs.getSrc(b)})}},getAdjacentAnchor:function(c){var b=this.getAnchorIndex(),a=hs.anchors.groups[this.slideshowGroup||"none"];if(a&&!a[b+c]&&this.slideshow&&this.slideshow.repeat){if(c==1){return a[0]}else{if(c==-1){return a[a.length-1]}}}return(a&&a[b+c])||null},getAnchorIndex:function(){var a=hs.getAnchors().groups[this.slideshowGroup||"none"];if(a){for(var b=0;b<a.length;b++){if(a[b]==this.a){return b}}}return null},getNumber:function(){if(this[this.numberPosition]){var a=hs.anchors.groups[this.slideshowGroup||"none"];if(a){var b=hs.lang.number.replace("%1",this.getAnchorIndex()+1).replace("%2",a.length);this[this.numberPosition].innerHTML='<div class="highslide-number">'+b+"</div>"+this[this.numberPosition].innerHTML}}},initSlideshow:function(){if(!this.last){for(var c=0;c<hs.slideshows.length;c++){var b=hs.slideshows[c],d=b.slideshowGroup;if(typeof d=="undefined"||d===null||d===this.slideshowGroup){this.slideshow=new hs.Slideshow(this.key,b)}}}else{this.slideshow=this.last.slideshow}var b=this.slideshow;if(!b){return}var a=b.expKey=this.key;b.checkFirstAndLast();b.disable("full-expand");if(b.controls){this.createOverlay(hs.extend(b.overlayOptions||{},{overlayId:b.controls,hsId:"controls",zIndex:5}))}if(b.thumbstrip){b.thumbstrip.add(this)}if(!this.last&&this.autoplay){b.play(true)}if(b.autoplay){b.autoplay=setTimeout(function(){hs.next(a)},(b.interval||500))}},cancelLoading:function(){hs.discardElement(this.wrapper);hs.expanders[this.key]=null;if(hs.upcoming==this.a){hs.upcoming=null}hs.undim(this.key);if(this.loading){hs.loading.style.left="-9999px"}hs.fireEvent(this,"onHideLoading")},writeCredits:function(){if(this.credits){return}this.credits=hs.createElement("a",{href:hs.creditsHref,target:hs.creditsTarget,className:"highslide-credits",innerHTML:hs.lang.creditsText,title:hs.lang.creditsTitle});this.createOverlay({overlayId:this.credits,position:this.creditsPosition||"top left",hsId:"credits"})},getInline:function(types,addOverlay){for(var i=0;i<types.length;i++){var type=types[i],s=null;if(type=="caption"&&!hs.fireEvent(this,"onBeforeGetCaption")){return}else{if(type=="heading"&&!hs.fireEvent(this,"onBeforeGetHeading")){return}}if(!this[type+"Id"]&&this.thumbsUserSetId){this[type+"Id"]=type+"-for-"+this.thumbsUserSetId}if(this[type+"Id"]){this[type]=hs.getNode(this[type+"Id"])}if(!this[type]&&!this[type+"Text"]&&this[type+"Eval"]){try{s=eval(this[type+"Eval"])}catch(e){}}if(!this[type]&&this[type+"Text"]){s=this[type+"Text"]}if(!this[type]&&!s){this[type]=hs.getNode(this.a["_"+type+"Id"]);if(!this[type]){var next=this.a.nextSibling;while(next&&!hs.isHsAnchor(next)){if((new RegExp("highslide-"+type)).test(next.className||null)){if(!next.id){this.a["_"+type+"Id"]=next.id="hsId"+hs.idCounter++}this[type]=hs.getNode(next.id);break}next=next.nextSibling}}}if(!this[type]&&!s&&this.numberPosition==type){s="\n"}if(!this[type]&&s){this[type]=hs.createElement("div",{className:"highslide-"+type,innerHTML:s})}if(addOverlay&&this[type]){var o={position:(type=="heading")?"above":"below"};for(var x in this[type+"Overlay"]){o[x]=this[type+"Overlay"][x]}o.overlayId=this[type];this.createOverlay(o)}}},doShowHide:function(a){if(hs.hideSelects){this.showHideElements("SELECT",a)}if(hs.hideIframes){this.showHideElements("IFRAME",a)}if(hs.geckoMac){this.showHideElements("*",a)}},showHideElements:function(c,b){var e=document.getElementsByTagName(c);var a=c=="*"?"overflow":"visibility";for(var f=0;f<e.length;f++){if(a=="visibility"||(document.defaultView.getComputedStyle(e[f],"").getPropertyValue("overflow")=="auto"||e[f].getAttribute("hidden-by")!=null)){var h=e[f].getAttribute("hidden-by");if(b=="visible"&&h){h=h.replace("["+this.key+"]","");e[f].setAttribute("hidden-by",h);if(!h){e[f].style[a]=e[f].origProp}}else{if(b=="hidden"){var k=hs.getPosition(e[f]);k.w=e[f].offsetWidth;k.h=e[f].offsetHeight;if(!this.dimmingOpacity){var j=(k.x+k.w<this.x.get("opos")||k.x>this.x.get("opos")+this.x.get("osize"));var g=(k.y+k.h<this.y.get("opos")||k.y>this.y.get("opos")+this.y.get("osize"))}var d=hs.getWrapperKey(e[f]);if(!j&&!g&&d!=this.key){if(!h){e[f].setAttribute("hidden-by","["+this.key+"]");e[f].origProp=e[f].style[a];e[f].style[a]="hidden"}else{if(h.indexOf("["+this.key+"]")==-1){e[f].setAttribute("hidden-by",h+"["+this.key+"]")}}}else{if((h=="["+this.key+"]"||hs.focusKey==d)&&d!=this.key){e[f].setAttribute("hidden-by","");e[f].style[a]=e[f].origProp||""}else{if(h&&h.indexOf("["+this.key+"]")>-1){e[f].setAttribute("hidden-by",h.replace("["+this.key+"]",""))}}}}}}}},focus:function(){this.wrapper.style.zIndex=hs.zIndexCounter+=2;for(var a=0;a<hs.expanders.length;a++){if(hs.expanders[a]&&a==hs.focusKey){var b=hs.expanders[a];b.content.className+=" highslide-"+b.contentType+"-blur";if(b.isImage){b.content.style.cursor=hs.ieLt7?"hand":"pointer";b.content.title=hs.lang.focusTitle}hs.fireEvent(b,"onBlur")}}if(this.outline){this.outline.table.style.zIndex=this.wrapper.style.zIndex-1}this.content.className="highslide-"+this.contentType;if(this.isImage){this.content.title=hs.lang.restoreTitle;if(hs.restoreCursor){hs.styleRestoreCursor=window.opera?"pointer":"url("+hs.graphicsDir+hs.restoreCursor+"), pointer";if(hs.ieLt7&&hs.uaVersion<6){hs.styleRestoreCursor="hand"}this.content.style.cursor=hs.styleRestoreCursor}}hs.focusKey=this.key;hs.addEventListener(document,window.opera?"keypress":"keydown",hs.keyHandler);hs.fireEvent(this,"onFocus")},moveTo:function(a,b){this.x.setPos(a);this.y.setPos(b)},resize:function(d){var a,b,c=d.width/d.height;a=Math.max(d.width+d.dX,Math.min(this.minWidth,this.x.full));if(this.isImage&&Math.abs(a-this.x.full)<12){a=this.x.full}b=this.isHtml?d.height+d.dY:a/c;if(b<Math.min(this.minHeight,this.y.full)){b=Math.min(this.minHeight,this.y.full);if(this.isImage){a=b*c}}this.resizeTo(a,b)},resizeTo:function(a,b){this.y.setSize(b);this.x.setSize(a);this.wrapper.style.height=this.y.get("wsize")+"px"},close:function(){if(this.isClosing||!this.isExpanded){return}if(this.transitions[1]=="crossfade"&&hs.upcoming){hs.getExpander(hs.upcoming).cancelLoading();hs.upcoming=null}if(!hs.fireEvent(this,"onBeforeClose")){return}this.isClosing=true;if(this.slideshow&&!hs.upcoming){this.slideshow.pause()}hs.removeEventListener(document,window.opera?"keypress":"keydown",hs.keyHandler);try{if(this.isHtml){this.htmlPrepareClose()}this.content.style.cursor="default";this.changeSize(0,{wrapper:{width:this.x.t,height:this.y.t,left:this.x.tpos-this.x.cb+this.x.tb,top:this.y.tpos-this.y.cb+this.y.tb},content:{left:0,top:0,width:this.x.t,height:this.y.t}},hs.restoreDuration)}catch(a){this.afterClose()}},htmlPrepareClose:function(){if(hs.geckoMac){if(!hs.mask){hs.mask=hs.createElement("div",null,{position:"absolute"},hs.container)}hs.setStyles(hs.mask,{width:this.x.size+"px",height:this.y.size+"px",left:this.x.pos+"px",top:this.y.pos+"px",display:"block"})}if(this.objectType=="swf"){try{hs.$(this.body.id).StopPlay()}catch(a){}}if(this.objectLoadTime=="after"&&!this.preserveContent){this.destroyObject()}if(this.scrollerDiv&&this.scrollerDiv!=this.scrollingContent){this.scrollerDiv.style.overflow="hidden"}},destroyObject:function(){if(hs.ie&&this.iframe){try{this.iframe.contentWindow.document.body.innerHTML=""}catch(a){}}if(this.objectType=="swf"){swfobject.removeSWF(this.body.id)}this.body.innerHTML=""},sleep:function(){if(this.outline){this.outline.table.style.display="none"}this.releaseMask=null;this.wrapper.style.display="none";this.isExpanded=false;hs.push(hs.sleeping,this)},awake:function(){try{hs.expanders[this.key]=this;if(!hs.allowMultipleInstances&&hs.focusKey!=this.key){try{hs.expanders[hs.focusKey].close()}catch(b){}}var d=hs.zIndexCounter++,a={display:"",zIndex:d};hs.setStyles(this.wrapper,a);this.isClosing=false;var c=this.outline||0;if(c){if(!this.outlineWhileAnimating){a.visibility="hidden"}hs.setStyles(c.table,a)}if(this.slideshow){this.initSlideshow()}this.show()}catch(b){}},createOverlay:function(e){var d=e.overlayId,a=(e.relativeTo=="viewport"&&!/panel$/.test(e.position));if(typeof d=="string"){d=hs.getNode(d)}if(e.html){d=hs.createElement("div",{innerHTML:e.html})}if(!d||typeof d=="string"){return}if(!hs.fireEvent(this,"onCreateOverlay",{overlay:d})){return}d.style.display="block";e.hsId=e.hsId||e.overlayId;if(this.transitions[1]=="crossfade"&&this.reuseOverlay(e,d)){return}this.genOverlayBox();var c=e.width&&/^[0-9]+(px|%)$/.test(e.width)?e.width:"auto";if(/^(left|right)panel$/.test(e.position)&&!/^[0-9]+px$/.test(e.width)){c="200px"}var b=hs.createElement("div",{id:"hsId"+hs.idCounter++,hsId:e.hsId},{position:"absolute",visibility:"hidden",width:c,direction:hs.lang.cssDirection||"",opacity:0},a?hs.viewport:this.overlayBox,true);if(a){b.hsKey=this.key}b.appendChild(d);hs.extend(b,{opacity:1,offsetX:0,offsetY:0,dur:(e.fade===0||e.fade===false||(e.fade==2&&hs.ie))?0:250});hs.extend(b,e);if(this.gotOverlays){this.positionOverlay(b);if(!b.hideOnMouseOut||this.mouseIsOver){hs.animate(b,{opacity:b.opacity},b.dur)}}hs.push(this.overlays,hs.idCounter-1)},positionOverlay:function(e){var f=e.position||"middle center",c=(e.relativeTo=="viewport"),b=e.offsetX,a=e.offsetY;if(c){hs.viewport.style.display="block";e.hsKey=this.key;if(e.offsetWidth>e.parentNode.offsetWidth){e.style.width="100%"}}else{if(e.parentNode!=this.overlayBox){this.overlayBox.appendChild(e)}}if(/left$/.test(f)){e.style.left=b+"px"}if(/center$/.test(f)){hs.setStyles(e,{left:"50%",marginLeft:(b-Math.round(e.offsetWidth/2))+"px"})}if(/right$/.test(f)){e.style.right=-b+"px"}if(/^leftpanel$/.test(f)){hs.setStyles(e,{right:"100%",marginRight:this.x.cb+"px",top:-this.y.cb+"px",bottom:-this.y.cb+"px",overflow:"auto"});this.x.p1=e.offsetWidth}else{if(/^rightpanel$/.test(f)){hs.setStyles(e,{left:"100%",marginLeft:this.x.cb+"px",top:-this.y.cb+"px",bottom:-this.y.cb+"px",overflow:"auto"});this.x.p2=e.offsetWidth}}var d=e.parentNode.offsetHeight;e.style.height="auto";if(c&&e.offsetHeight>d){e.style.height=hs.ieLt7?d+"px":"100%"}if(/^top/.test(f)){e.style.top=a+"px"}if(/^middle/.test(f)){hs.setStyles(e,{top:"50%",marginTop:(a-Math.round(e.offsetHeight/2))+"px"})}if(/^bottom/.test(f)){e.style.bottom=-a+"px"}if(/^above$/.test(f)){hs.setStyles(e,{left:(-this.x.p1-this.x.cb)+"px",right:(-this.x.p2-this.x.cb)+"px",bottom:"100%",marginBottom:this.y.cb+"px",width:"auto"});this.y.p1=e.offsetHeight}else{if(/^below$/.test(f)){hs.setStyles(e,{position:"relative",left:(-this.x.p1-this.x.cb)+"px",right:(-this.x.p2-this.x.cb)+"px",top:"100%",marginTop:this.y.cb+"px",width:"auto"});this.y.p2=e.offsetHeight;e.style.position="absolute"}}},getOverlays:function(){this.getInline(["heading","caption"],true);this.getNumber();if(this.caption){hs.fireEvent(this,"onAfterGetCaption")}if(this.heading){hs.fireEvent(this,"onAfterGetHeading")}if(this.heading&&this.dragByHeading){this.heading.className+=" highslide-move"}if(hs.showCredits){this.writeCredits()}for(var a=0;a<hs.overlays.length;a++){var d=hs.overlays[a],e=d.thumbnailId,b=d.slideshowGroup;if((!e&&!b)||(e&&e==this.thumbsUserSetId)||(b&&b===this.slideshowGroup)){if(this.isImage||(this.isHtml&&d.useOnHtml)){this.createOverlay(d)}}}var c=[];for(var a=0;a<this.overlays.length;a++){var d=hs.$("hsId"+this.overlays[a]);if(/panel$/.test(d.position)){this.positionOverlay(d)}else{hs.push(c,d)}}for(var a=0;a<c.length;a++){this.positionOverlay(c[a])}this.gotOverlays=true},genOverlayBox:function(){if(!this.overlayBox){this.overlayBox=hs.createElement("div",{className:this.wrapperClassName},{position:"absolute",width:(this.x.size||(this.useBox?this.width:null)||this.x.full)+"px",height:(this.y.size||this.y.full)+"px",visibility:"hidden",overflow:"hidden",zIndex:hs.ie?4:"auto"},hs.container,true)}},sizeOverlayBox:function(f,d){var c=this.overlayBox,a=this.x,h=this.y;hs.setStyles(c,{width:a.size+"px",height:h.size+"px"});if(f||d){for(var e=0;e<this.overlays.length;e++){var g=hs.$("hsId"+this.overlays[e]);var b=(hs.ieLt7||document.compatMode=="BackCompat");if(g&&/^(above|below)$/.test(g.position)){if(b){g.style.width=(c.offsetWidth+2*a.cb+a.p1+a.p2)+"px"}h[g.position=="above"?"p1":"p2"]=g.offsetHeight}if(g&&b&&/^(left|right)panel$/.test(g.position)){g.style.height=(c.offsetHeight+2*h.cb)+"px"}}}if(f){hs.setStyles(this.content,{top:h.p1+"px"});hs.setStyles(c,{top:(h.p1+h.cb)+"px"})}},showOverlays:function(){var a=this.overlayBox;a.className="";hs.setStyles(a,{top:(this.y.p1+this.y.cb)+"px",left:(this.x.p1+this.x.cb)+"px",overflow:"visible"});if(hs.safari){a.style.visibility="visible"}this.wrapper.appendChild(a);for(var c=0;c<this.overlays.length;c++){var d=hs.$("hsId"+this.overlays[c]);d.style.zIndex=d.zIndex||4;if(!d.hideOnMouseOut||this.mouseIsOver){d.style.visibility="visible";hs.setStyles(d,{visibility:"visible",display:""});hs.animate(d,{opacity:d.opacity},d.dur)}}},destroyOverlays:function(){if(!this.overlays.length){return}if(this.slideshow){var d=this.slideshow.controls;if(d&&hs.getExpander(d)==this){d.parentNode.removeChild(d)}}for(var a=0;a<this.overlays.length;a++){var b=hs.$("hsId"+this.overlays[a]);if(b&&b.parentNode==hs.viewport&&hs.getExpander(b)==this){hs.discardElement(b)}}if(this.isHtml&&this.preserveContent){this.overlayBox.style.top="-9999px";hs.container.appendChild(this.overlayBox)}else{hs.discardElement(this.overlayBox)}},createFullExpand:function(){if(this.slideshow&&this.slideshow.controls){this.slideshow.enable("full-expand");return}this.fullExpandLabel=hs.createElement("a",{href:"javascript:hs.expanders["+this.key+"].doFullExpand();",title:hs.lang.fullExpandTitle,className:"highslide-full-expand"});if(!hs.fireEvent(this,"onCreateFullExpand")){return}this.createOverlay({overlayId:this.fullExpandLabel,position:hs.fullExpandPosition,hideOnMouseOut:true,opacity:hs.fullExpandOpacity})},doFullExpand:function(){try{if(!hs.fireEvent(this,"onDoFullExpand")){return}if(this.fullExpandLabel){hs.discardElement(this.fullExpandLabel)}this.focus();var c=this.x.size,a=this.y.size;this.resizeTo(this.x.full,this.y.full);var b=this.x.pos-(this.x.size-c)/2;if(b<hs.marginLeft){b=hs.marginLeft}var f=this.y.pos-(this.y.size-a)/2;if(f<hs.marginTop){f=hs.marginTop}this.moveTo(b,f);this.doShowHide("hidden")}catch(d){this.error(d)}},afterClose:function(){this.a.className=this.a.className.replace("highslide-active-anchor","");this.doShowHide("visible");if(this.isHtml&&this.preserveContent&&this.transitions[1]!="crossfade"){this.sleep()}else{if(this.outline&&this.outlineWhileAnimating){this.outline.destroy()}hs.discardElement(this.wrapper)}if(hs.mask){hs.mask.style.display="none"}this.destroyOverlays();if(!hs.viewport.childNodes.length){hs.viewport.style.display="none"}if(this.dimmingOpacity){hs.undim(this.key)}hs.fireEvent(this,"onAfterClose");hs.expanders[this.key]=null;hs.reOrder()}};hs.Ajax=function(b,c,d){this.a=b;this.content=c;this.pre=d};hs.Ajax.prototype={run:function(){var d;if(!this.src){this.src=hs.getSrc(this.a)}if(this.src.match("#")){var a=this.src.split("#");this.src=a[0];this.id=a[1]}if(hs.cachedGets[this.src]){this.cachedGet=hs.cachedGets[this.src];if(this.id){this.getElementContent()}else{this.loadHTML()}return}try{d=new XMLHttpRequest()}catch(b){try{d=new ActiveXObject("Msxml2.XMLHTTP")}catch(b){try{d=new ActiveXObject("Microsoft.XMLHTTP")}catch(b){this.onError()}}}var f=this;d.onreadystatechange=function(){if(f.xhr.readyState==4){if(f.id){f.getElementContent()}else{f.loadHTML()}}};var c=this.src;this.xhr=d;if(hs.forceAjaxReload){c=c.replace(/$/,(/\?/.test(c)?"&":"?")+"dummy="+(new Date()).getTime())}d.open("GET",c,true);d.setRequestHeader("X-Requested-With","XMLHttpRequest");d.setRequestHeader("Content-Type","application/x-www-form-urlencoded");d.send(null)},getElementContent:function(){hs.init();var a=window.opera||hs.ie6SSL?{src:"about:blank"}:null;this.iframe=hs.createElement("iframe",a,{position:"absolute",top:"-9999px"},hs.container);this.loadHTML()},loadHTML:function(){var c=this.cachedGet||this.xhr.responseText,b;if(this.pre){hs.cachedGets[this.src]=c}if(!hs.ie||hs.uaVersion>=5.5){c=c.replace(new RegExp("<link[^>]*>","gi"),"").replace(new RegExp("<script[^>]*>.*?<\/script>","gi"),"");if(this.iframe){var f=this.iframe.contentDocument;if(!f&&this.iframe.contentWindow){f=this.iframe.contentWindow.document}if(!f){var g=this;setTimeout(function(){g.loadHTML()},25);return}f.open();f.write(c);f.close();try{c=f.getElementById(this.id).innerHTML}catch(d){try{c=this.iframe.document.getElementById(this.id).innerHTML}catch(d){}}hs.discardElement(this.iframe)}else{b=/(<body[^>]*>|<\/body>)/ig;if(b.test(c)){c=c.split(b)[hs.ieLt9?1:2]}}}hs.getElementByClass(this.content,"DIV","highslide-body").innerHTML=c;this.onLoad();for(var a in this){this[a]=null}}};hs.Slideshow=function(c,b){if(hs.dynamicallyUpdateAnchors!==false){hs.updateAnchors()}this.expKey=c;for(var a in b){this[a]=b[a]}if(this.useControls){this.getControls()}if(this.thumbstrip){this.thumbstrip=hs.Thumbstrip(this)}};hs.Slideshow.prototype={getControls:function(){this.controls=hs.createElement("div",{innerHTML:hs.replaceLang(hs.skin.controls)},null,hs.container);var b=["play","pause","previous","next","move","full-expand","close"];this.btn={};var c=this;for(var a=0;a<b.length;a++){this.btn[b[a]]=hs.getElementByClass(this.controls,"li","highslide-"+b[a]);this.enable(b[a])}this.btn.pause.style.display="none"},checkFirstAndLast:function(){if(this.repeat||!this.controls){return}var c=hs.expanders[this.expKey],b=c.getAnchorIndex(),a=/disabled$/;if(b==0){this.disable("previous")}else{if(a.test(this.btn.previous.getElementsByTagName("a")[0].className)){this.enable("previous")}}if(b+1==hs.anchors.groups[c.slideshowGroup||"none"].length){this.disable("next");this.disable("play")}else{if(a.test(this.btn.next.getElementsByTagName("a")[0].className)){this.enable("next");this.enable("play")}}},enable:function(d){if(!this.btn){return}var c=this,b=this.btn[d].getElementsByTagName("a")[0],e=/disabled$/;b.onclick=function(){c[d]();return false};if(e.test(b.className)){b.className=b.className.replace(e,"")}},disable:function(c){if(!this.btn){return}var b=this.btn[c].getElementsByTagName("a")[0];b.onclick=function(){return false};if(!/disabled$/.test(b.className)){b.className+=" disabled"}},hitSpace:function(){if(this.autoplay){this.pause()}else{this.play()}},play:function(a){if(this.btn){this.btn.play.style.display="none";this.btn.pause.style.display=""}this.autoplay=true;if(!a){hs.next(this.expKey)}},pause:function(){if(this.btn){this.btn.pause.style.display="none";this.btn.play.style.display=""}clearTimeout(this.autoplay);this.autoplay=null},previous:function(){this.pause();hs.previous(this.btn.previous)},next:function(){this.pause();hs.next(this.btn.next)},move:function(){},"full-expand":function(){hs.getExpander().doFullExpand()},close:function(){hs.close(this.btn.close)}};hs.Thumbstrip=function(k){function p(i){hs.extend(f||{},{overlayId:r,hsId:"thumbstrip",className:"highslide-thumbstrip-"+m+"-overlay "+(f.className||"")});if(hs.ieLt7){f.fade=0}i.createOverlay(f);hs.setStyles(r.parentNode,{overflow:"hidden"})}function c(i){d(undefined,Math.round(i*r[h?"offsetWidth":"offsetHeight"]*0.7))}function d(L,M){if(L===undefined){for(var K=0;K<j.length;K++){if(j[K]==hs.expanders[k.expKey].a){L=K;break}}}if(L===undefined){return}var G=r.getElementsByTagName("a"),z=G[L],w=z.parentNode,y=h?"Left":"Top",N=h?"Right":"Bottom",I=h?"Width":"Height",B="offset"+y,H="offset"+I,x=n.parentNode.parentNode[H],F=x-s[H],v=parseInt(s.style[h?"left":"top"])||0,C=v,D=20;if(M!==undefined){C=v-M;if(F>0){F=0}if(C>0){C=0}if(C<F){C=F}}else{for(var K=0;K<G.length;K++){G[K].className=""}z.className="highslide-active-anchor";var J=L>0?G[L-1].parentNode[B]:w[B],A=w[B]+w[H]+(G[L+1]?G[L+1].parentNode[H]:0);if(A>x-v){C=x-A}else{if(J<-v){C=-J}}}var E=w[B]+(w[H]-g[H])/2+C;hs.animate(s,h?{left:C}:{top:C},null,"easeOutQuad");hs.animate(g,h?{left:E}:{top:E},null,"easeOutQuad");l.style.display=C<0?"block":"none";t.style.display=(C>F)?"block":"none"}var j=hs.anchors.groups[hs.expanders[k.expKey].slideshowGroup||"none"],f=k.thumbstrip,m=f.mode||"horizontal",u=(m=="float"),o=u?["div","ul","li","span"]:["table","tbody","tr","td"],h=(m=="horizontal"),r=hs.createElement("div",{className:"highslide-thumbstrip highslide-thumbstrip-"+m,innerHTML:'<div class="highslide-thumbstrip-inner"><'+o[0]+"><"+o[1]+"></"+o[1]+"></"+o[0]+'></div><div class="highslide-scroll-up"><div></div></div><div class="highslide-scroll-down"><div></div></div><div class="highslide-marker"><div></div></div>'},{display:"none"},hs.container),e=r.childNodes,n=e[0],l=e[1],t=e[2],g=e[3],s=n.firstChild,a=r.getElementsByTagName(o[1])[0],b;for(var q=0;q<j.length;q++){if(q==0||!h){b=hs.createElement(o[2],null,null,a)}(function(){var v=j[q],i=hs.createElement(o[3],null,null,b),w=q;hs.createElement("a",{href:v.href,title:v.title,onclick:function(){if(/highslide-active-anchor/.test(this.className)){return false}hs.getExpander(this).focus();return hs.transit(v)},innerHTML:hs.stripItemFormatter?hs.stripItemFormatter(v):v.innerHTML},null,i)})()}if(!u){l.onclick=function(){c(-1)};t.onclick=function(){c(1)};hs.addEventListener(a,document.onmousewheel!==undefined?"mousewheel":"DOMMouseScroll",function(i){var v=0;i=i||window.event;if(i.wheelDelta){v=i.wheelDelta/120;if(hs.opera){v=-v}}else{if(i.detail){v=-i.detail/3}}if(v){c(-v*0.2)}if(i.preventDefault){i.preventDefault()}i.returnValue=false})}return{add:p,selectThumb:d}};hs.langDefaults=hs.lang;var HsExpander=hs.Expander;if(hs.ie&&window==window.top){(function(){try{document.documentElement.doScroll("left")}catch(a){setTimeout(arguments.callee,50);return}hs.ready()})()}hs.addEventListener(document,"DOMContentLoaded",hs.ready);hs.addEventListener(window,"load",hs.ready);hs.addEventListener(document,"ready",function(){if(hs.expandCursor||hs.dimmingOpacity){var d=hs.createElement("style",{type:"text/css"},null,document.getElementsByTagName("HEAD")[0]),c=document.compatMode=="BackCompat";function b(f,g){if(hs.ie&&(hs.uaVersion<9||c)){var e=document.styleSheets[document.styleSheets.length-1];if(typeof(e.addRule)=="object"){e.addRule(f,g)}}else{d.appendChild(document.createTextNode(f+" {"+g+"}"))}}function a(e){return"expression( ( ( ignoreMe = document.documentElement."+e+" ? document.documentElement."+e+" : document.body."+e+" ) ) + 'px' );"}if(hs.expandCursor){b(".highslide img","cursor: url("+hs.graphicsDir+hs.expandCursor+"), pointer !important;")}b(".highslide-viewport-size",hs.ie&&(hs.uaVersion<7||c)?"position: absolute; left:"+a("scrollLeft")+"top:"+a("scrollTop")+"width:"+a("clientWidth")+"height:"+a("clientHeight"):"position: fixed; width: 100%; height: 100%; left: 0; top: 0")}});hs.addEventListener(window,"resize",function(){hs.getPageSize();if(hs.viewport){for(var a=0;a<hs.viewport.childNodes.length;a++){var b=hs.viewport.childNodes[a],c=hs.getExpander(b);c.positionOverlay(b);if(b.hsId=="thumbstrip"){c.slideshow.thumbstrip.selectThumb()}}}});hs.addEventListener(document,"mousemove",function(a){hs.mouse={x:a.clientX,y:a.clientY}});hs.addEventListener(document,"mousedown",hs.mouseClickHandler);hs.addEventListener(document,"mouseup",hs.mouseClickHandler);hs.addEventListener(document,"ready",hs.setClickEvents);hs.addEventListener(window,"load",hs.preloadImages);hs.addEventListener(window,"load",hs.preloadAjax)};
/*
     _ _      _       _
 ___| (_) ___| | __  (_)___
/ __| | |/ __| |/ /  | / __|
\__ \ | | (__|   < _ | \__ \
|___/_|_|\___|_|\_(_)/ |___/
                   |__/

 Version: 1.6.0
  Author: Ken Wheeler
 Website: http://kenwheeler.github.io
    Docs: http://kenwheeler.github.io/slick
    Repo: http://github.com/kenwheeler/slick
  Issues: http://github.com/kenwheeler/slick/issues

 */
/* global window, document, define, jQuery, setInterval, clearInterval */
(function(factory) {
    'use strict';
    if (typeof define === 'function' && define.amd) {
        define(['jquery'], factory);
    } else if (typeof exports !== 'undefined') {
        module.exports = factory(require('jquery'));
    } else {
        factory(jQuery);
    }

}(function($) {
    'use strict';
    var Slick = window.Slick || {};

    Slick = (function() {

        var instanceUid = 0;

        function Slick(element, settings) {

            var _ = this, dataSettings;

            _.defaults = {
                accessibility: true,
                adaptiveHeight: false,
                appendArrows: $(element),
                appendDots: $(element),
                arrows: true,
                asNavFor: null,
                prevArrow: '<button type="button" data-role="none" class="slick-prev" aria-label="Previous" tabindex="0" role="button">Previous</button>',
                nextArrow: '<button type="button" data-role="none" class="slick-next" aria-label="Next" tabindex="0" role="button">Next</button>',
                autoplay: false,
                autoplaySpeed: 3000,
                centerMode: false,
                centerPadding: '50px',
                cssEase: 'ease',
                customPaging: function(slider, i) {
                    return $('<button type="button" data-role="none" role="button" tabindex="0" />').text(i + 1);
                },
                dots: false,
                dotsClass: 'slick-dots',
                draggable: true,
                easing: 'linear',
                edgeFriction: 0.35,
                fade: false,
                focusOnSelect: false,
                infinite: true,
                initialSlide: 0,
                lazyLoad: 'ondemand',
                mobileFirst: false,
                pauseOnHover: true,
                pauseOnFocus: true,
                pauseOnDotsHover: false,
                respondTo: 'window',
                responsive: null,
                rows: 1,
                rtl: false,
                slide: '',
                slidesPerRow: 1,
                slidesToShow: 1,
                slidesToScroll: 1,
                speed: 500,
                swipe: true,
                swipeToSlide: false,
                touchMove: true,
                touchThreshold: 5,
                useCSS: true,
                useTransform: true,
                variableWidth: false,
                vertical: false,
                verticalSwiping: false,
                waitForAnimate: true,
                zIndex: 1000
            };

            _.initials = {
                animating: false,
                dragging: false,
                autoPlayTimer: null,
                currentDirection: 0,
                currentLeft: null,
                currentSlide: 0,
                direction: 1,
                $dots: null,
                listWidth: null,
                listHeight: null,
                loadIndex: 0,
                $nextArrow: null,
                $prevArrow: null,
                slideCount: null,
                slideWidth: null,
                $slideTrack: null,
                $slides: null,
                sliding: false,
                slideOffset: 0,
                swipeLeft: null,
                $list: null,
                touchObject: {},
                transformsEnabled: false,
                unslicked: false
            };

            $.extend(_, _.initials);

            _.activeBreakpoint = null;
            _.animType = null;
            _.animProp = null;
            _.breakpoints = [];
            _.breakpointSettings = [];
            _.cssTransitions = false;
            _.focussed = false;
            _.interrupted = false;
            _.hidden = 'hidden';
            _.paused = true;
            _.positionProp = null;
            _.respondTo = null;
            _.rowCount = 1;
            _.shouldClick = true;
            _.$slider = $(element);
            _.$slidesCache = null;
            _.transformType = null;
            _.transitionType = null;
            _.visibilityChange = 'visibilitychange';
            _.windowWidth = 0;
            _.windowTimer = null;

            dataSettings = $(element).data('slick') || {};

            _.options = $.extend({}, _.defaults, settings, dataSettings);

            _.currentSlide = _.options.initialSlide;

            _.originalSettings = _.options;

            if (typeof document.mozHidden !== 'undefined') {
                _.hidden = 'mozHidden';
                _.visibilityChange = 'mozvisibilitychange';
            } else if (typeof document.webkitHidden !== 'undefined') {
                _.hidden = 'webkitHidden';
                _.visibilityChange = 'webkitvisibilitychange';
            }

            _.autoPlay = $.proxy(_.autoPlay, _);
            _.autoPlayClear = $.proxy(_.autoPlayClear, _);
            _.autoPlayIterator = $.proxy(_.autoPlayIterator, _);
            _.changeSlide = $.proxy(_.changeSlide, _);
            _.clickHandler = $.proxy(_.clickHandler, _);
            _.selectHandler = $.proxy(_.selectHandler, _);
            _.setPosition = $.proxy(_.setPosition, _);
            _.swipeHandler = $.proxy(_.swipeHandler, _);
            _.dragHandler = $.proxy(_.dragHandler, _);
            _.keyHandler = $.proxy(_.keyHandler, _);

            _.instanceUid = instanceUid++;

            // A simple way to check for HTML strings
            // Strict HTML recognition (must start with <)
            // Extracted from jQuery v1.11 source
            _.htmlExpr = /^(?:\s*(<[\w\W]+>)[^>]*)$/;


            _.registerBreakpoints();
            _.init(true);

        }

        return Slick;

    }());

    Slick.prototype.activateADA = function() {
        var _ = this;

        _.$slideTrack.find('.slick-active').attr({
            'aria-hidden': 'false'
        }).find('a, input, button, select').attr({
            'tabindex': '0'
        });

    };

    Slick.prototype.addSlide = Slick.prototype.slickAdd = function(markup, index, addBefore) {

        var _ = this;

        if (typeof(index) === 'boolean') {
            addBefore = index;
            index = null;
        } else if (index < 0 || (index >= _.slideCount)) {
            return false;
        }

        _.unload();

        if (typeof(index) === 'number') {
            if (index === 0 && _.$slides.length === 0) {
                $(markup).appendTo(_.$slideTrack);
            } else if (addBefore) {
                $(markup).insertBefore(_.$slides.eq(index));
            } else {
                $(markup).insertAfter(_.$slides.eq(index));
            }
        } else {
            if (addBefore === true) {
                $(markup).prependTo(_.$slideTrack);
            } else {
                $(markup).appendTo(_.$slideTrack);
            }
        }

        _.$slides = _.$slideTrack.children(this.options.slide);

        _.$slideTrack.children(this.options.slide).detach();

        _.$slideTrack.append(_.$slides);

        _.$slides.each(function(index, element) {
            $(element).attr('data-slick-index', index);
        });

        _.$slidesCache = _.$slides;

        _.reinit();

    };

    Slick.prototype.animateHeight = function() {
        var _ = this;
        if (_.options.slidesToShow === 1 && _.options.adaptiveHeight === true && _.options.vertical === false) {
            var targetHeight = _.$slides.eq(_.currentSlide).outerHeight(true);
            _.$list.animate({
                height: targetHeight
            }, _.options.speed);
        }
    };

    Slick.prototype.animateSlide = function(targetLeft, callback) {

        var animProps = {},
            _ = this;

        _.animateHeight();

        if (_.options.rtl === true && _.options.vertical === false) {
            targetLeft = -targetLeft;
        }
        if (_.transformsEnabled === false) {
            if (_.options.vertical === false) {
                _.$slideTrack.animate({
                    left: targetLeft
                }, _.options.speed, _.options.easing, callback);
            } else {
                _.$slideTrack.animate({
                    top: targetLeft
                }, _.options.speed, _.options.easing, callback);
            }

        } else {

            if (_.cssTransitions === false) {
                if (_.options.rtl === true) {
                    _.currentLeft = -(_.currentLeft);
                }
                $({
                    animStart: _.currentLeft
                }).animate({
                    animStart: targetLeft
                }, {
                    duration: _.options.speed,
                    easing: _.options.easing,
                    step: function(now) {
                        now = Math.ceil(now);
                        if (_.options.vertical === false) {
                            animProps[_.animType] = 'translate(' +
                                now + 'px, 0px)';
                            _.$slideTrack.css(animProps);
                        } else {
                            animProps[_.animType] = 'translate(0px,' +
                                now + 'px)';
                            _.$slideTrack.css(animProps);
                        }
                    },
                    complete: function() {
                        if (callback) {
                            callback.call();
                        }
                    }
                });

            } else {

                _.applyTransition();
                targetLeft = Math.ceil(targetLeft);

                if (_.options.vertical === false) {
                    animProps[_.animType] = 'translate3d(' + targetLeft + 'px, 0px, 0px)';
                } else {
                    animProps[_.animType] = 'translate3d(0px,' + targetLeft + 'px, 0px)';
                }
                _.$slideTrack.css(animProps);

                if (callback) {
                    setTimeout(function() {

                        _.disableTransition();

                        callback.call();
                    }, _.options.speed);
                }

            }

        }

    };

    Slick.prototype.getNavTarget = function() {

        var _ = this,
            asNavFor = _.options.asNavFor;

        if ( asNavFor && asNavFor !== null ) {
            asNavFor = $(asNavFor).not(_.$slider);
        }

        return asNavFor;

    };

    Slick.prototype.asNavFor = function(index) {

        var _ = this,
            asNavFor = _.getNavTarget();

        if ( asNavFor !== null && typeof asNavFor === 'object' ) {
            asNavFor.each(function() {
                var target = $(this).slick('getSlick');
                if(!target.unslicked) {
                    target.slideHandler(index, true);
                }
            });
        }

    };

    Slick.prototype.applyTransition = function(slide) {

        var _ = this,
            transition = {};

        if (_.options.fade === false) {
            transition[_.transitionType] = _.transformType + ' ' + _.options.speed + 'ms ' + _.options.cssEase;
        } else {
            transition[_.transitionType] = 'opacity ' + _.options.speed + 'ms ' + _.options.cssEase;
        }

        if (_.options.fade === false) {
            _.$slideTrack.css(transition);
        } else {
            _.$slides.eq(slide).css(transition);
        }

    };

    Slick.prototype.autoPlay = function() {

        var _ = this;

        _.autoPlayClear();

        if ( _.slideCount > _.options.slidesToShow ) {
            _.autoPlayTimer = setInterval( _.autoPlayIterator, _.options.autoplaySpeed );
        }

    };

    Slick.prototype.autoPlayClear = function() {

        var _ = this;

        if (_.autoPlayTimer) {
            clearInterval(_.autoPlayTimer);
        }

    };

    Slick.prototype.autoPlayIterator = function() {

        var _ = this,
            slideTo = _.currentSlide + _.options.slidesToScroll;

        if ( !_.paused && !_.interrupted && !_.focussed ) {

            if ( _.options.infinite === false ) {

                if ( _.direction === 1 && ( _.currentSlide + 1 ) === ( _.slideCount - 1 )) {
                    _.direction = 0;
                }

                else if ( _.direction === 0 ) {

                    slideTo = _.currentSlide - _.options.slidesToScroll;

                    if ( _.currentSlide - 1 === 0 ) {
                        _.direction = 1;
                    }

                }

            }

            _.slideHandler( slideTo );

        }

    };

    Slick.prototype.buildArrows = function() {

        var _ = this;

        if (_.options.arrows === true ) {

            _.$prevArrow = $(_.options.prevArrow).addClass('slick-arrow');
            _.$nextArrow = $(_.options.nextArrow).addClass('slick-arrow');

            if( _.slideCount > _.options.slidesToShow ) {

                _.$prevArrow.removeClass('slick-hidden').removeAttr('aria-hidden tabindex');
                _.$nextArrow.removeClass('slick-hidden').removeAttr('aria-hidden tabindex');

                if (_.htmlExpr.test(_.options.prevArrow)) {
                    _.$prevArrow.prependTo(_.options.appendArrows);
                }

                if (_.htmlExpr.test(_.options.nextArrow)) {
                    _.$nextArrow.appendTo(_.options.appendArrows);
                }

                if (_.options.infinite !== true) {
                    _.$prevArrow
                        .addClass('slick-disabled')
                        .attr('aria-disabled', 'true');
                }

            } else {

                _.$prevArrow.add( _.$nextArrow )

                    .addClass('slick-hidden')
                    .attr({
                        'aria-disabled': 'true',
                        'tabindex': '-1'
                    });

            }

        }

    };

    Slick.prototype.buildDots = function() {

        var _ = this,
            i, dot;

        if (_.options.dots === true && _.slideCount > _.options.slidesToShow) {

            _.$slider.addClass('slick-dotted');

            dot = $('<ul />').addClass(_.options.dotsClass);

            for (i = 0; i <= _.getDotCount(); i += 1) {
                dot.append($('<li />').append(_.options.customPaging.call(this, _, i)));
            }

            _.$dots = dot.appendTo(_.options.appendDots);

            _.$dots.find('li').first().addClass('slick-active').attr('aria-hidden', 'false');

        }

    };

    Slick.prototype.buildOut = function() {

        var _ = this;

        _.$slides =
            _.$slider
                .children( _.options.slide + ':not(.slick-cloned)')
                .addClass('slick-slide');

        _.slideCount = _.$slides.length;

        _.$slides.each(function(index, element) {
            $(element)
                .attr('data-slick-index', index)
                .data('originalStyling', $(element).attr('style') || '');
        });

        _.$slider.addClass('slick-slider');

        _.$slideTrack = (_.slideCount === 0) ?
            $('<div class="slick-track"/>').appendTo(_.$slider) :
            _.$slides.wrapAll('<div class="slick-track"/>').parent();

        _.$list = _.$slideTrack.wrap(
            '<div aria-live="polite" class="slick-list"/>').parent();
        _.$slideTrack.css('opacity', 0);

        if (_.options.centerMode === true || _.options.swipeToSlide === true) {
            _.options.slidesToScroll = 1;
        }

        $('img[data-lazy]', _.$slider).not('[src]').addClass('slick-loading');

        _.setupInfinite();

        _.buildArrows();

        _.buildDots();

        _.updateDots();


        _.setSlideClasses(typeof _.currentSlide === 'number' ? _.currentSlide : 0);

        if (_.options.draggable === true) {
            _.$list.addClass('draggable');
        }

    };

    Slick.prototype.buildRows = function() {

        var _ = this, a, b, c, newSlides, numOfSlides, originalSlides,slidesPerSection;

        newSlides = document.createDocumentFragment();
        originalSlides = _.$slider.children();

        if(_.options.rows > 1) {

            slidesPerSection = _.options.slidesPerRow * _.options.rows;
            numOfSlides = Math.ceil(
                originalSlides.length / slidesPerSection
            );

            for(a = 0; a < numOfSlides; a++){
                var slide = document.createElement('div');
                for(b = 0; b < _.options.rows; b++) {
                    var row = document.createElement('div');
                    for(c = 0; c < _.options.slidesPerRow; c++) {
                        var target = (a * slidesPerSection + ((b * _.options.slidesPerRow) + c));
                        if (originalSlides.get(target)) {
                            row.appendChild(originalSlides.get(target));
                        }
                    }
                    slide.appendChild(row);
                }
                newSlides.appendChild(slide);
            }

            _.$slider.empty().append(newSlides);
            _.$slider.children().children().children()
                .css({
                    'width':(100 / _.options.slidesPerRow) + '%',
                    'display': 'inline-block'
                });

        }

    };

    Slick.prototype.checkResponsive = function(initial, forceUpdate) {

        var _ = this,
            breakpoint, targetBreakpoint, respondToWidth, triggerBreakpoint = false;
        var sliderWidth = _.$slider.width();
        var windowWidth = window.innerWidth || $(window).width();

        if (_.respondTo === 'window') {
            respondToWidth = windowWidth;
        } else if (_.respondTo === 'slider') {
            respondToWidth = sliderWidth;
        } else if (_.respondTo === 'min') {
            respondToWidth = Math.min(windowWidth, sliderWidth);
        }

        if ( _.options.responsive &&
            _.options.responsive.length &&
            _.options.responsive !== null) {

            targetBreakpoint = null;

            for (breakpoint in _.breakpoints) {
                if (_.breakpoints.hasOwnProperty(breakpoint)) {
                    if (_.originalSettings.mobileFirst === false) {
                        if (respondToWidth < _.breakpoints[breakpoint]) {
                            targetBreakpoint = _.breakpoints[breakpoint];
                        }
                    } else {
                        if (respondToWidth > _.breakpoints[breakpoint]) {
                            targetBreakpoint = _.breakpoints[breakpoint];
                        }
                    }
                }
            }

            if (targetBreakpoint !== null) {
                if (_.activeBreakpoint !== null) {
                    if (targetBreakpoint !== _.activeBreakpoint || forceUpdate) {
                        _.activeBreakpoint =
                            targetBreakpoint;
                        if (_.breakpointSettings[targetBreakpoint] === 'unslick') {
                            _.unslick(targetBreakpoint);
                        } else {
                            _.options = $.extend({}, _.originalSettings,
                                _.breakpointSettings[
                                    targetBreakpoint]);
                            if (initial === true) {
                                _.currentSlide = _.options.initialSlide;
                            }
                            _.refresh(initial);
                        }
                        triggerBreakpoint = targetBreakpoint;
                    }
                } else {
                    _.activeBreakpoint = targetBreakpoint;
                    if (_.breakpointSettings[targetBreakpoint] === 'unslick') {
                        _.unslick(targetBreakpoint);
                    } else {
                        _.options = $.extend({}, _.originalSettings,
                            _.breakpointSettings[
                                targetBreakpoint]);
                        if (initial === true) {
                            _.currentSlide = _.options.initialSlide;
                        }
                        _.refresh(initial);
                    }
                    triggerBreakpoint = targetBreakpoint;
                }
            } else {
                if (_.activeBreakpoint !== null) {
                    _.activeBreakpoint = null;
                    _.options = _.originalSettings;
                    if (initial === true) {
                        _.currentSlide = _.options.initialSlide;
                    }
                    _.refresh(initial);
                    triggerBreakpoint = targetBreakpoint;
                }
            }

            // only trigger breakpoints during an actual break. not on initialize.
            if( !initial && triggerBreakpoint !== false ) {
                _.$slider.trigger('breakpoint', [_, triggerBreakpoint]);
            }
        }

    };

    Slick.prototype.changeSlide = function(event, dontAnimate) {

        var _ = this,
            $target = $(event.currentTarget),
            indexOffset, slideOffset, unevenOffset;

        // If target is a link, prevent default action.
        if($target.is('a')) {
            event.preventDefault();
        }

        // If target is not the <li> element (ie: a child), find the <li>.
        if(!$target.is('li')) {
            $target = $target.closest('li');
        }

        unevenOffset = (_.slideCount % _.options.slidesToScroll !== 0);
        indexOffset = unevenOffset ? 0 : (_.slideCount - _.currentSlide) % _.options.slidesToScroll;

        switch (event.data.message) {

            case 'previous':
                slideOffset = indexOffset === 0 ? _.options.slidesToScroll : _.options.slidesToShow - indexOffset;
                if (_.slideCount > _.options.slidesToShow) {
                    _.slideHandler(_.currentSlide - slideOffset, false, dontAnimate);
                }
                break;

            case 'next':
                slideOffset = indexOffset === 0 ? _.options.slidesToScroll : indexOffset;
                if (_.slideCount > _.options.slidesToShow) {
                    _.slideHandler(_.currentSlide + slideOffset, false, dontAnimate);
                }
                break;

            case 'index':
                var index = event.data.index === 0 ? 0 :
                    event.data.index || $target.index() * _.options.slidesToScroll;

                _.slideHandler(_.checkNavigable(index), false, dontAnimate);
                $target.children().trigger('focus');
                break;

            default:
                return;
        }

    };

    Slick.prototype.checkNavigable = function(index) {

        var _ = this,
            navigables, prevNavigable;

        navigables = _.getNavigableIndexes();
        prevNavigable = 0;
        if (index > navigables[navigables.length - 1]) {
            index = navigables[navigables.length - 1];
        } else {
            for (var n in navigables) {
                if (index < navigables[n]) {
                    index = prevNavigable;
                    break;
                }
                prevNavigable = navigables[n];
            }
        }

        return index;
    };

    Slick.prototype.cleanUpEvents = function() {

        var _ = this;

        if (_.options.dots && _.$dots !== null) {

            $('li', _.$dots)
                .off('click.slick', _.changeSlide)
                .off('mouseenter.slick', $.proxy(_.interrupt, _, true))
                .off('mouseleave.slick', $.proxy(_.interrupt, _, false));

        }

        _.$slider.off('focus.slick blur.slick');

        if (_.options.arrows === true && _.slideCount > _.options.slidesToShow) {
            _.$prevArrow && _.$prevArrow.off('click.slick', _.changeSlide);
            _.$nextArrow && _.$nextArrow.off('click.slick', _.changeSlide);
        }

        _.$list.off('touchstart.slick mousedown.slick', _.swipeHandler);
        _.$list.off('touchmove.slick mousemove.slick', _.swipeHandler);
        _.$list.off('touchend.slick mouseup.slick', _.swipeHandler);
        _.$list.off('touchcancel.slick mouseleave.slick', _.swipeHandler);

        _.$list.off('click.slick', _.clickHandler);

        $(document).off(_.visibilityChange, _.visibility);

        _.cleanUpSlideEvents();

        if (_.options.accessibility === true) {
            _.$list.off('keydown.slick', _.keyHandler);
        }

        if (_.options.focusOnSelect === true) {
            $(_.$slideTrack).children().off('click.slick', _.selectHandler);
        }

        $(window).off('orientationchange.slick.slick-' + _.instanceUid, _.orientationChange);

        $(window).off('resize.slick.slick-' + _.instanceUid, _.resize);

        $('[draggable!=true]', _.$slideTrack).off('dragstart', _.preventDefault);

        $(window).off('load.slick.slick-' + _.instanceUid, _.setPosition);
        $(document).off('ready.slick.slick-' + _.instanceUid, _.setPosition);

    };

    Slick.prototype.cleanUpSlideEvents = function() {

        var _ = this;

        _.$list.off('mouseenter.slick', $.proxy(_.interrupt, _, true));
        _.$list.off('mouseleave.slick', $.proxy(_.interrupt, _, false));

    };

    Slick.prototype.cleanUpRows = function() {

        var _ = this, originalSlides;

        if(_.options.rows > 1) {
            originalSlides = _.$slides.children().children();
            originalSlides.removeAttr('style');
            _.$slider.empty().append(originalSlides);
        }

    };

    Slick.prototype.clickHandler = function(event) {

        var _ = this;

        if (_.shouldClick === false) {
            event.stopImmediatePropagation();
            event.stopPropagation();
            event.preventDefault();
        }

    };

    Slick.prototype.destroy = function(refresh) {

        var _ = this;

        _.autoPlayClear();

        _.touchObject = {};

        _.cleanUpEvents();

        $('.slick-cloned', _.$slider).detach();

        if (_.$dots) {
            _.$dots.remove();
        }


        if ( _.$prevArrow && _.$prevArrow.length ) {

            _.$prevArrow
                .removeClass('slick-disabled slick-arrow slick-hidden')
                .removeAttr('aria-hidden aria-disabled tabindex')
                .css('display','');

            if ( _.htmlExpr.test( _.options.prevArrow )) {
                _.$prevArrow.remove();
            }
        }

        if ( _.$nextArrow && _.$nextArrow.length ) {

            _.$nextArrow
                .removeClass('slick-disabled slick-arrow slick-hidden')
                .removeAttr('aria-hidden aria-disabled tabindex')
                .css('display','');

            if ( _.htmlExpr.test( _.options.nextArrow )) {
                _.$nextArrow.remove();
            }

        }


        if (_.$slides) {

            _.$slides
                .removeClass('slick-slide slick-active slick-center slick-visible slick-current')
                .removeAttr('aria-hidden')
                .removeAttr('data-slick-index')
                .each(function(){
                    $(this).attr('style', $(this).data('originalStyling'));
                });

            _.$slideTrack.children(this.options.slide).detach();

            _.$slideTrack.detach();

            _.$list.detach();

            _.$slider.append(_.$slides);
        }

        _.cleanUpRows();

        _.$slider.removeClass('slick-slider');
        _.$slider.removeClass('slick-initialized');
        _.$slider.removeClass('slick-dotted');

        _.unslicked = true;

        if(!refresh) {
            _.$slider.trigger('destroy', [_]);
        }

    };

    Slick.prototype.disableTransition = function(slide) {

        var _ = this,
            transition = {};

        transition[_.transitionType] = '';

        if (_.options.fade === false) {
            _.$slideTrack.css(transition);
        } else {
            _.$slides.eq(slide).css(transition);
        }

    };

    Slick.prototype.fadeSlide = function(slideIndex, callback) {

        var _ = this;

        if (_.cssTransitions === false) {

            _.$slides.eq(slideIndex).css({
                zIndex: _.options.zIndex
            });

            _.$slides.eq(slideIndex).animate({
                opacity: 1
            }, _.options.speed, _.options.easing, callback);

        } else {

            _.applyTransition(slideIndex);

            _.$slides.eq(slideIndex).css({
                opacity: 1,
                zIndex: _.options.zIndex
            });

            if (callback) {
                setTimeout(function() {

                    _.disableTransition(slideIndex);

                    callback.call();
                }, _.options.speed);
            }

        }

    };

    Slick.prototype.fadeSlideOut = function(slideIndex) {

        var _ = this;

        if (_.cssTransitions === false) {

            _.$slides.eq(slideIndex).animate({
                opacity: 0,
                zIndex: _.options.zIndex - 2
            }, _.options.speed, _.options.easing);

        } else {

            _.applyTransition(slideIndex);

            _.$slides.eq(slideIndex).css({
                opacity: 0,
                zIndex: _.options.zIndex - 2
            });

        }

    };

    Slick.prototype.filterSlides = Slick.prototype.slickFilter = function(filter) {

        var _ = this;

        if (filter !== null) {

            _.$slidesCache = _.$slides;

            _.unload();

            _.$slideTrack.children(this.options.slide).detach();

            _.$slidesCache.filter(filter).appendTo(_.$slideTrack);

            _.reinit();

        }

    };

    Slick.prototype.focusHandler = function() {

        var _ = this;

        _.$slider
            .off('focus.slick blur.slick')
            .on('focus.slick blur.slick',
                '*:not(.slick-arrow)', function(event) {

            event.stopImmediatePropagation();
            var $sf = $(this);

            setTimeout(function() {

                if( _.options.pauseOnFocus ) {
                    _.focussed = $sf.is(':focus');
                    _.autoPlay();
                }

            }, 0);

        });
    };

    Slick.prototype.getCurrent = Slick.prototype.slickCurrentSlide = function() {

        var _ = this;
        return _.currentSlide;

    };

    Slick.prototype.getDotCount = function() {

        var _ = this;

        var breakPoint = 0;
        var counter = 0;
        var pagerQty = 0;

        if (_.options.infinite === true) {
            while (breakPoint < _.slideCount) {
                ++pagerQty;
                breakPoint = counter + _.options.slidesToScroll;
                counter += _.options.slidesToScroll <= _.options.slidesToShow ? _.options.slidesToScroll : _.options.slidesToShow;
            }
        } else if (_.options.centerMode === true) {
            pagerQty = _.slideCount;
        } else if(!_.options.asNavFor) {
            pagerQty = 1 + Math.ceil((_.slideCount - _.options.slidesToShow) / _.options.slidesToScroll);
        }else {
            while (breakPoint < _.slideCount) {
                ++pagerQty;
                breakPoint = counter + _.options.slidesToScroll;
                counter += _.options.slidesToScroll <= _.options.slidesToShow ? _.options.slidesToScroll : _.options.slidesToShow;
            }
        }

        return pagerQty - 1;

    };

    Slick.prototype.getLeft = function(slideIndex) {

        var _ = this,
            targetLeft,
            verticalHeight,
            verticalOffset = 0,
            targetSlide;

        _.slideOffset = 0;
        verticalHeight = _.$slides.first().outerHeight(true);

        if (_.options.infinite === true) {
            if (_.slideCount > _.options.slidesToShow) {
                _.slideOffset = (_.slideWidth * _.options.slidesToShow) * -1;
                verticalOffset = (verticalHeight * _.options.slidesToShow) * -1;
            }
            if (_.slideCount % _.options.slidesToScroll !== 0) {
                if (slideIndex + _.options.slidesToScroll > _.slideCount && _.slideCount > _.options.slidesToShow) {
                    if (slideIndex > _.slideCount) {
                        _.slideOffset = ((_.options.slidesToShow - (slideIndex - _.slideCount)) * _.slideWidth) * -1;
                        verticalOffset = ((_.options.slidesToShow - (slideIndex - _.slideCount)) * verticalHeight) * -1;
                    } else {
                        _.slideOffset = ((_.slideCount % _.options.slidesToScroll) * _.slideWidth) * -1;
                        verticalOffset = ((_.slideCount % _.options.slidesToScroll) * verticalHeight) * -1;
                    }
                }
            }
        } else {
            if (slideIndex + _.options.slidesToShow > _.slideCount) {
                _.slideOffset = ((slideIndex + _.options.slidesToShow) - _.slideCount) * _.slideWidth;
                verticalOffset = ((slideIndex + _.options.slidesToShow) - _.slideCount) * verticalHeight;
            }
        }

        if (_.slideCount <= _.options.slidesToShow) {
            _.slideOffset = 0;
            verticalOffset = 0;
        }

        if (_.options.centerMode === true && _.slideCount <= _.options.slidesToShow) {
            _.slideOffset = ((_.slideWidth * Math.floor(_.options.slidesToShow)) / 2) - ((_.slideWidth * _.slideCount) / 2);
        } else if (_.options.centerMode === true && _.options.infinite === true) {
            _.slideOffset += _.slideWidth * Math.floor(_.options.slidesToShow / 2) - _.slideWidth;
        } else if (_.options.centerMode === true) {
            _.slideOffset = 0;
            _.slideOffset += _.slideWidth * Math.floor(_.options.slidesToShow / 2);
        }

        if (_.options.vertical === false) {
            targetLeft = ((slideIndex * _.slideWidth) * -1) + _.slideOffset;
        } else {
            targetLeft = ((slideIndex * verticalHeight) * -1) + verticalOffset;
        }

        if (_.options.variableWidth === true) {

            if (_.slideCount <= _.options.slidesToShow || _.options.infinite === false) {
                targetSlide = _.$slideTrack.children('.slick-slide').eq(slideIndex);
            } else {
                targetSlide = _.$slideTrack.children('.slick-slide').eq(slideIndex + _.options.slidesToShow);
            }

            if (_.options.rtl === true) {
                if (targetSlide[0]) {
                    targetLeft = (_.$slideTrack.width() - targetSlide[0].offsetLeft - targetSlide.width()) * -1;
                } else {
                    targetLeft =  0;
                }
            } else {
                targetLeft = targetSlide[0] ? targetSlide[0].offsetLeft * -1 : 0;
            }

            if (_.options.centerMode === true) {
                if (_.slideCount <= _.options.slidesToShow || _.options.infinite === false) {
                    targetSlide = _.$slideTrack.children('.slick-slide').eq(slideIndex);
                } else {
                    targetSlide = _.$slideTrack.children('.slick-slide').eq(slideIndex + _.options.slidesToShow + 1);
                }

                if (_.options.rtl === true) {
                    if (targetSlide[0]) {
                        targetLeft = (_.$slideTrack.width() - targetSlide[0].offsetLeft - targetSlide.width()) * -1;
                    } else {
                        targetLeft =  0;
                    }
                } else {
                    targetLeft = targetSlide[0] ? targetSlide[0].offsetLeft * -1 : 0;
                }

                targetLeft += (_.$list.width() - targetSlide.outerWidth()) / 2;
            }
        }

        return targetLeft;

    };

    Slick.prototype.getOption = Slick.prototype.slickGetOption = function(option) {

        var _ = this;

        return _.options[option];

    };

    Slick.prototype.getNavigableIndexes = function() {

        var _ = this,
            breakPoint = 0,
            counter = 0,
            indexes = [],
            max;

        if (_.options.infinite === false) {
            max = _.slideCount;
        } else {
            breakPoint = _.options.slidesToScroll * -1;
            counter = _.options.slidesToScroll * -1;
            max = _.slideCount * 2;
        }

        while (breakPoint < max) {
            indexes.push(breakPoint);
            breakPoint = counter + _.options.slidesToScroll;
            counter += _.options.slidesToScroll <= _.options.slidesToShow ? _.options.slidesToScroll : _.options.slidesToShow;
        }

        return indexes;

    };

    Slick.prototype.getSlick = function() {

        return this;

    };

    Slick.prototype.getSlideCount = function() {

        var _ = this,
            slidesTraversed, swipedSlide, centerOffset;

        centerOffset = _.options.centerMode === true ? _.slideWidth * Math.floor(_.options.slidesToShow / 2) : 0;

        if (_.options.swipeToSlide === true) {
            _.$slideTrack.find('.slick-slide').each(function(index, slide) {
                if (slide.offsetLeft - centerOffset + ($(slide).outerWidth() / 2) > (_.swipeLeft * -1)) {
                    swipedSlide = slide;
                    return false;
                }
            });

            slidesTraversed = Math.abs($(swipedSlide).attr('data-slick-index') - _.currentSlide) || 1;

            return slidesTraversed;

        } else {
            return _.options.slidesToScroll;
        }

    };

    Slick.prototype.goTo = Slick.prototype.slickGoTo = function(slide, dontAnimate) {

        var _ = this;

        _.changeSlide({
            data: {
                message: 'index',
                index: parseInt(slide)
            }
        }, dontAnimate);

    };

    Slick.prototype.init = function(creation) {

        var _ = this;

        if (!$(_.$slider).hasClass('slick-initialized')) {

            $(_.$slider).addClass('slick-initialized');

            _.buildRows();
            _.buildOut();
            _.setProps();
            _.startLoad();
            _.loadSlider();
            _.initializeEvents();
            _.updateArrows();
            _.updateDots();
            _.checkResponsive(true);
            _.focusHandler();

        }

        if (creation) {
            _.$slider.trigger('init', [_]);
        }

        if (_.options.accessibility === true) {
            _.initADA();
        }

        if ( _.options.autoplay ) {

            _.paused = false;
            _.autoPlay();

        }

    };

    Slick.prototype.initADA = function() {
        var _ = this;
        _.$slides.add(_.$slideTrack.find('.slick-cloned')).attr({
            'aria-hidden': 'true',
            'tabindex': '-1'
        }).find('a, input, button, select').attr({
            'tabindex': '-1'
        });

        _.$slideTrack.attr('role', 'listbox');

        _.$slides.not(_.$slideTrack.find('.slick-cloned')).each(function(i) {
            $(this).attr('role', 'option');
            
            //Evenly distribute aria-describedby tags through available dots.
            var describedBySlideId = _.options.centerMode ? i : Math.floor(i / _.options.slidesToShow);
            
            if (_.options.dots === true) {
                $(this).attr('aria-describedby', 'slick-slide' + _.instanceUid + describedBySlideId + '');
            }
        });

        if (_.$dots !== null) {
            _.$dots.attr('role', 'tablist').find('li').each(function(i) {
                $(this).attr({
                    'role': 'presentation',
                    'aria-selected': 'false',
                    'aria-controls': 'navigation' + _.instanceUid + i + '',
                    'id': 'slick-slide' + _.instanceUid + i + ''
                });
            })
                .first().attr('aria-selected', 'true').end()
                .find('button').attr('role', 'button').end()
                .closest('div').attr('role', 'toolbar');
        }
        _.activateADA();

    };

    Slick.prototype.initArrowEvents = function() {

        var _ = this;

        if (_.options.arrows === true && _.slideCount > _.options.slidesToShow) {
            _.$prevArrow
               .off('click.slick')
               .on('click.slick', {
                    message: 'previous'
               }, _.changeSlide);
            _.$nextArrow
               .off('click.slick')
               .on('click.slick', {
                    message: 'next'
               }, _.changeSlide);
        }

    };

    Slick.prototype.initDotEvents = function() {

        var _ = this;

        if (_.options.dots === true && _.slideCount > _.options.slidesToShow) {
            $('li', _.$dots).on('click.slick', {
                message: 'index'
            }, _.changeSlide);
        }

        if ( _.options.dots === true && _.options.pauseOnDotsHover === true ) {

            $('li', _.$dots)
                .on('mouseenter.slick', $.proxy(_.interrupt, _, true))
                .on('mouseleave.slick', $.proxy(_.interrupt, _, false));

        }

    };

    Slick.prototype.initSlideEvents = function() {

        var _ = this;

        if ( _.options.pauseOnHover ) {

            _.$list.on('mouseenter.slick', $.proxy(_.interrupt, _, true));
            _.$list.on('mouseleave.slick', $.proxy(_.interrupt, _, false));

        }

    };

    Slick.prototype.initializeEvents = function() {

        var _ = this;

        _.initArrowEvents();

        _.initDotEvents();
        _.initSlideEvents();

        _.$list.on('touchstart.slick mousedown.slick', {
            action: 'start'
        }, _.swipeHandler);
        _.$list.on('touchmove.slick mousemove.slick', {
            action: 'move'
        }, _.swipeHandler);
        _.$list.on('touchend.slick mouseup.slick', {
            action: 'end'
        }, _.swipeHandler);
        _.$list.on('touchcancel.slick mouseleave.slick', {
            action: 'end'
        }, _.swipeHandler);

        _.$list.on('click.slick', _.clickHandler);

        $(document).on(_.visibilityChange, $.proxy(_.visibility, _));

        if (_.options.accessibility === true) {
            _.$list.on('keydown.slick', _.keyHandler);
        }

        if (_.options.focusOnSelect === true) {
            $(_.$slideTrack).children().on('click.slick', _.selectHandler);
        }

        $(window).on('orientationchange.slick.slick-' + _.instanceUid, $.proxy(_.orientationChange, _));

        $(window).on('resize.slick.slick-' + _.instanceUid, $.proxy(_.resize, _));

        $('[draggable!=true]', _.$slideTrack).on('dragstart', _.preventDefault);

        $(window).on('load.slick.slick-' + _.instanceUid, _.setPosition);
        $(document).on('ready.slick.slick-' + _.instanceUid, _.setPosition);

    };

    Slick.prototype.initUI = function() {

        var _ = this;

        if (_.options.arrows === true && _.slideCount > _.options.slidesToShow) {

            _.$prevArrow.show();
            _.$nextArrow.show();

        }

        if (_.options.dots === true && _.slideCount > _.options.slidesToShow) {

            _.$dots.show();

        }

    };

    Slick.prototype.keyHandler = function(event) {

        var _ = this;
         //Dont slide if the cursor is inside the form fields and arrow keys are pressed
        if(!event.target.tagName.match('TEXTAREA|INPUT|SELECT')) {
            if (event.keyCode === 37 && _.options.accessibility === true) {
                _.changeSlide({
                    data: {
                        message: _.options.rtl === true ? 'next' :  'previous'
                    }
                });
            } else if (event.keyCode === 39 && _.options.accessibility === true) {
                _.changeSlide({
                    data: {
                        message: _.options.rtl === true ? 'previous' : 'next'
                    }
                });
            }
        }

    };

    Slick.prototype.lazyLoad = function() {

        var _ = this,
            loadRange, cloneRange, rangeStart, rangeEnd;

        function loadImages(imagesScope) {

            $('img[data-lazy]', imagesScope).each(function() {

                var image = $(this),
                    imageSource = $(this).attr('data-lazy'),
                    imageToLoad = document.createElement('img');

                imageToLoad.onload = function() {

                    image
                        .animate({ opacity: 0 }, 100, function() {
                            image
                                .attr('src', imageSource)
                                .animate({ opacity: 1 }, 200, function() {
                                    image
                                        .removeAttr('data-lazy')
                                        .removeClass('slick-loading');
                                });
                            _.$slider.trigger('lazyLoaded', [_, image, imageSource]);
                        });

                };

                imageToLoad.onerror = function() {

                    image
                        .removeAttr( 'data-lazy' )
                        .removeClass( 'slick-loading' )
                        .addClass( 'slick-lazyload-error' );

                    _.$slider.trigger('lazyLoadError', [ _, image, imageSource ]);

                };

                imageToLoad.src = imageSource;

            });

        }

        if (_.options.centerMode === true) {
            if (_.options.infinite === true) {
                rangeStart = _.currentSlide + (_.options.slidesToShow / 2 + 1);
                rangeEnd = rangeStart + _.options.slidesToShow + 2;
            } else {
                rangeStart = Math.max(0, _.currentSlide - (_.options.slidesToShow / 2 + 1));
                rangeEnd = 2 + (_.options.slidesToShow / 2 + 1) + _.currentSlide;
            }
        } else {
            rangeStart = _.options.infinite ? _.options.slidesToShow + _.currentSlide : _.currentSlide;
            rangeEnd = Math.ceil(rangeStart + _.options.slidesToShow);
            if (_.options.fade === true) {
                if (rangeStart > 0) rangeStart--;
                if (rangeEnd <= _.slideCount) rangeEnd++;
            }
        }

        loadRange = _.$slider.find('.slick-slide').slice(rangeStart, rangeEnd);
        loadImages(loadRange);

        if (_.slideCount <= _.options.slidesToShow) {
            cloneRange = _.$slider.find('.slick-slide');
            loadImages(cloneRange);
        } else
        if (_.currentSlide >= _.slideCount - _.options.slidesToShow) {
            cloneRange = _.$slider.find('.slick-cloned').slice(0, _.options.slidesToShow);
            loadImages(cloneRange);
        } else if (_.currentSlide === 0) {
            cloneRange = _.$slider.find('.slick-cloned').slice(_.options.slidesToShow * -1);
            loadImages(cloneRange);
        }

    };

    Slick.prototype.loadSlider = function() {

        var _ = this;

        _.setPosition();

        _.$slideTrack.css({
            opacity: 1
        });

        _.$slider.removeClass('slick-loading');

        _.initUI();

        if (_.options.lazyLoad === 'progressive') {
            _.progressiveLazyLoad();
        }

    };

    Slick.prototype.next = Slick.prototype.slickNext = function() {

        var _ = this;

        _.changeSlide({
            data: {
                message: 'next'
            }
        });

    };

    Slick.prototype.orientationChange = function() {

        var _ = this;

        _.checkResponsive();
        _.setPosition();

    };

    Slick.prototype.pause = Slick.prototype.slickPause = function() {

        var _ = this;

        _.autoPlayClear();
        _.paused = true;

    };

    Slick.prototype.play = Slick.prototype.slickPlay = function() {

        var _ = this;

        _.autoPlay();
        _.options.autoplay = true;
        _.paused = false;
        _.focussed = false;
        _.interrupted = false;

    };

    Slick.prototype.postSlide = function(index) {

        var _ = this;

        if( !_.unslicked ) {

            _.$slider.trigger('afterChange', [_, index]);

            _.animating = false;

            _.setPosition();

            _.swipeLeft = null;

            if ( _.options.autoplay ) {
                _.autoPlay();
            }

            if (_.options.accessibility === true) {
                _.initADA();
            }

        }

    };

    Slick.prototype.prev = Slick.prototype.slickPrev = function() {

        var _ = this;

        _.changeSlide({
            data: {
                message: 'previous'
            }
        });

    };

    Slick.prototype.preventDefault = function(event) {

        event.preventDefault();

    };

    Slick.prototype.progressiveLazyLoad = function( tryCount ) {

        tryCount = tryCount || 1;

        var _ = this,
            $imgsToLoad = $( 'img[data-lazy]', _.$slider ),
            image,
            imageSource,
            imageToLoad;

        if ( $imgsToLoad.length ) {

            image = $imgsToLoad.first();
            imageSource = image.attr('data-lazy');
            imageToLoad = document.createElement('img');

            imageToLoad.onload = function() {

                image
                    .attr( 'src', imageSource )
                    .removeAttr('data-lazy')
                    .removeClass('slick-loading');

                if ( _.options.adaptiveHeight === true ) {
                    _.setPosition();
                }

                _.$slider.trigger('lazyLoaded', [ _, image, imageSource ]);
                _.progressiveLazyLoad();

            };

            imageToLoad.onerror = function() {

                if ( tryCount < 3 ) {

                    /**
                     * try to load the image 3 times,
                     * leave a slight delay so we don't get
                     * servers blocking the request.
                     */
                    setTimeout( function() {
                        _.progressiveLazyLoad( tryCount + 1 );
                    }, 500 );

                } else {

                    image
                        .removeAttr( 'data-lazy' )
                        .removeClass( 'slick-loading' )
                        .addClass( 'slick-lazyload-error' );

                    _.$slider.trigger('lazyLoadError', [ _, image, imageSource ]);

                    _.progressiveLazyLoad();

                }

            };

            imageToLoad.src = imageSource;

        } else {

            _.$slider.trigger('allImagesLoaded', [ _ ]);

        }

    };

    Slick.prototype.refresh = function( initializing ) {

        var _ = this, currentSlide, lastVisibleIndex;

        lastVisibleIndex = _.slideCount - _.options.slidesToShow;

        // in non-infinite sliders, we don't want to go past the
        // last visible index.
        if( !_.options.infinite && ( _.currentSlide > lastVisibleIndex )) {
            _.currentSlide = lastVisibleIndex;
        }

        // if less slides than to show, go to start.
        if ( _.slideCount <= _.options.slidesToShow ) {
            _.currentSlide = 0;

        }

        currentSlide = _.currentSlide;

        _.destroy(true);

        $.extend(_, _.initials, { currentSlide: currentSlide });

        _.init();

        if( !initializing ) {

            _.changeSlide({
                data: {
                    message: 'index',
                    index: currentSlide
                }
            }, false);

        }

    };

    Slick.prototype.registerBreakpoints = function() {

        var _ = this, breakpoint, currentBreakpoint, l,
            responsiveSettings = _.options.responsive || null;

        if ( $.type(responsiveSettings) === 'array' && responsiveSettings.length ) {

            _.respondTo = _.options.respondTo || 'window';

            for ( breakpoint in responsiveSettings ) {

                l = _.breakpoints.length-1;
                currentBreakpoint = responsiveSettings[breakpoint].breakpoint;

                if (responsiveSettings.hasOwnProperty(breakpoint)) {

                    // loop through the breakpoints and cut out any existing
                    // ones with the same breakpoint number, we don't want dupes.
                    while( l >= 0 ) {
                        if( _.breakpoints[l] && _.breakpoints[l] === currentBreakpoint ) {
                            _.breakpoints.splice(l,1);
                        }
                        l--;
                    }

                    _.breakpoints.push(currentBreakpoint);
                    _.breakpointSettings[currentBreakpoint] = responsiveSettings[breakpoint].settings;

                }

            }

            _.breakpoints.sort(function(a, b) {
                return ( _.options.mobileFirst ) ? a-b : b-a;
            });

        }

    };

    Slick.prototype.reinit = function() {

        var _ = this;

        _.$slides =
            _.$slideTrack
                .children(_.options.slide)
                .addClass('slick-slide');

        _.slideCount = _.$slides.length;

        if (_.currentSlide >= _.slideCount && _.currentSlide !== 0) {
            _.currentSlide = _.currentSlide - _.options.slidesToScroll;
        }

        if (_.slideCount <= _.options.slidesToShow) {
            _.currentSlide = 0;
        }

        _.registerBreakpoints();

        _.setProps();
        _.setupInfinite();
        _.buildArrows();
        _.updateArrows();
        _.initArrowEvents();
        _.buildDots();
        _.updateDots();
        _.initDotEvents();
        _.cleanUpSlideEvents();
        _.initSlideEvents();

        _.checkResponsive(false, true);

        if (_.options.focusOnSelect === true) {
            $(_.$slideTrack).children().on('click.slick', _.selectHandler);
        }

        _.setSlideClasses(typeof _.currentSlide === 'number' ? _.currentSlide : 0);

        _.setPosition();
        _.focusHandler();

        _.paused = !_.options.autoplay;
        _.autoPlay();

        _.$slider.trigger('reInit', [_]);

    };

    Slick.prototype.resize = function() {

        var _ = this;

        if ($(window).width() !== _.windowWidth) {
            clearTimeout(_.windowDelay);
            _.windowDelay = window.setTimeout(function() {
                _.windowWidth = $(window).width();
                _.checkResponsive();
                if( !_.unslicked ) { _.setPosition(); }
            }, 50);
        }
    };

    Slick.prototype.removeSlide = Slick.prototype.slickRemove = function(index, removeBefore, removeAll) {

        var _ = this;

        if (typeof(index) === 'boolean') {
            removeBefore = index;
            index = removeBefore === true ? 0 : _.slideCount - 1;
        } else {
            index = removeBefore === true ? --index : index;
        }

        if (_.slideCount < 1 || index < 0 || index > _.slideCount - 1) {
            return false;
        }

        _.unload();

        if (removeAll === true) {
            _.$slideTrack.children().remove();
        } else {
            _.$slideTrack.children(this.options.slide).eq(index).remove();
        }

        _.$slides = _.$slideTrack.children(this.options.slide);

        _.$slideTrack.children(this.options.slide).detach();

        _.$slideTrack.append(_.$slides);

        _.$slidesCache = _.$slides;

        _.reinit();

    };

    Slick.prototype.setCSS = function(position) {

        var _ = this,
            positionProps = {},
            x, y;

        if (_.options.rtl === true) {
            position = -position;
        }
        x = _.positionProp == 'left' ? Math.ceil(position) + 'px' : '0px';
        y = _.positionProp == 'top' ? Math.ceil(position) + 'px' : '0px';

        positionProps[_.positionProp] = position;

        if (_.transformsEnabled === false) {
            _.$slideTrack.css(positionProps);
        } else {
            positionProps = {};
            if (_.cssTransitions === false) {
                positionProps[_.animType] = 'translate(' + x + ', ' + y + ')';
                _.$slideTrack.css(positionProps);
            } else {
                positionProps[_.animType] = 'translate3d(' + x + ', ' + y + ', 0px)';
                _.$slideTrack.css(positionProps);
            }
        }

    };

    Slick.prototype.setDimensions = function() {

        var _ = this;

        if (_.options.vertical === false) {
            if (_.options.centerMode === true) {
                _.$list.css({
                    padding: ('0px ' + _.options.centerPadding)
                });
            }
        } else {
            _.$list.height(_.$slides.first().outerHeight(true) * _.options.slidesToShow);
            if (_.options.centerMode === true) {
                _.$list.css({
                    padding: (_.options.centerPadding + ' 0px')
                });
            }
        }

        _.listWidth = _.$list.width();
        _.listHeight = _.$list.height();


        if (_.options.vertical === false && _.options.variableWidth === false) {
            _.slideWidth = Math.ceil(_.listWidth / _.options.slidesToShow);
            _.$slideTrack.width(Math.ceil((_.slideWidth * _.$slideTrack.children('.slick-slide').length)));

        } else if (_.options.variableWidth === true) {
            _.$slideTrack.width(5000 * _.slideCount);
        } else {
            _.slideWidth = Math.ceil(_.listWidth);
            _.$slideTrack.height(Math.ceil((_.$slides.first().outerHeight(true) * _.$slideTrack.children('.slick-slide').length)));
        }

        var offset = _.$slides.first().outerWidth(true) - _.$slides.first().width();
        if (_.options.variableWidth === false) _.$slideTrack.children('.slick-slide').width(_.slideWidth - offset);

    };

    Slick.prototype.setFade = function() {

        var _ = this,
            targetLeft;

        _.$slides.each(function(index, element) {
            targetLeft = (_.slideWidth * index) * -1;
            if (_.options.rtl === true) {
                $(element).css({
                    position: 'relative',
                    right: targetLeft,
                    top: 0,
                    zIndex: _.options.zIndex - 2,
                    opacity: 0
                });
            } else {
                $(element).css({
                    position: 'relative',
                    left: targetLeft,
                    top: 0,
                    zIndex: _.options.zIndex - 2,
                    opacity: 0
                });
            }
        });

        _.$slides.eq(_.currentSlide).css({
            zIndex: _.options.zIndex - 1,
            opacity: 1
        });

    };

    Slick.prototype.setHeight = function() {

        var _ = this;

        if (_.options.slidesToShow === 1 && _.options.adaptiveHeight === true && _.options.vertical === false) {
            var targetHeight = _.$slides.eq(_.currentSlide).outerHeight(true);
            _.$list.css('height', targetHeight);
        }

    };

    Slick.prototype.setOption =
    Slick.prototype.slickSetOption = function() {

        /**
         * accepts arguments in format of:
         *
         *  - for changing a single option's value:
         *     .slick("setOption", option, value, refresh )
         *
         *  - for changing a set of responsive options:
         *     .slick("setOption", 'responsive', [{}, ...], refresh )
         *
         *  - for updating multiple values at once (not responsive)
         *     .slick("setOption", { 'option': value, ... }, refresh )
         */

        var _ = this, l, item, option, value, refresh = false, type;

        if( $.type( arguments[0] ) === 'object' ) {

            option =  arguments[0];
            refresh = arguments[1];
            type = 'multiple';

        } else if ( $.type( arguments[0] ) === 'string' ) {

            option =  arguments[0];
            value = arguments[1];
            refresh = arguments[2];

            if ( arguments[0] === 'responsive' && $.type( arguments[1] ) === 'array' ) {

                type = 'responsive';

            } else if ( typeof arguments[1] !== 'undefined' ) {

                type = 'single';

            }

        }

        if ( type === 'single' ) {

            _.options[option] = value;


        } else if ( type === 'multiple' ) {

            $.each( option , function( opt, val ) {

                _.options[opt] = val;

            });


        } else if ( type === 'responsive' ) {

            for ( item in value ) {

                if( $.type( _.options.responsive ) !== 'array' ) {

                    _.options.responsive = [ value[item] ];

                } else {

                    l = _.options.responsive.length-1;

                    // loop through the responsive object and splice out duplicates.
                    while( l >= 0 ) {

                        if( _.options.responsive[l].breakpoint === value[item].breakpoint ) {

                            _.options.responsive.splice(l,1);

                        }

                        l--;

                    }

                    _.options.responsive.push( value[item] );

                }

            }

        }

        if ( refresh ) {

            _.unload();
            _.reinit();

        }

    };

    Slick.prototype.setPosition = function() {

        var _ = this;

        _.setDimensions();

        _.setHeight();

        if (_.options.fade === false) {
            _.setCSS(_.getLeft(_.currentSlide));
        } else {
            _.setFade();
        }

        _.$slider.trigger('setPosition', [_]);

    };

    Slick.prototype.setProps = function() {

        var _ = this,
            bodyStyle = document.body.style;

        _.positionProp = _.options.vertical === true ? 'top' : 'left';

        if (_.positionProp === 'top') {
            _.$slider.addClass('slick-vertical');
        } else {
            _.$slider.removeClass('slick-vertical');
        }

        if (bodyStyle.WebkitTransition !== undefined ||
            bodyStyle.MozTransition !== undefined ||
            bodyStyle.msTransition !== undefined) {
            if (_.options.useCSS === true) {
                _.cssTransitions = true;
            }
        }

        if ( _.options.fade ) {
            if ( typeof _.options.zIndex === 'number' ) {
                if( _.options.zIndex < 3 ) {
                    _.options.zIndex = 3;
                }
            } else {
                _.options.zIndex = _.defaults.zIndex;
            }
        }

        if (bodyStyle.OTransform !== undefined) {
            _.animType = 'OTransform';
            _.transformType = '-o-transform';
            _.transitionType = 'OTransition';
            if (bodyStyle.perspectiveProperty === undefined && bodyStyle.webkitPerspective === undefined) _.animType = false;
        }
        if (bodyStyle.MozTransform !== undefined) {
            _.animType = 'MozTransform';
            _.transformType = '-moz-transform';
            _.transitionType = 'MozTransition';
            if (bodyStyle.perspectiveProperty === undefined && bodyStyle.MozPerspective === undefined) _.animType = false;
        }
        if (bodyStyle.webkitTransform !== undefined) {
            _.animType = 'webkitTransform';
            _.transformType = '-webkit-transform';
            _.transitionType = 'webkitTransition';
            if (bodyStyle.perspectiveProperty === undefined && bodyStyle.webkitPerspective === undefined) _.animType = false;
        }
        if (bodyStyle.msTransform !== undefined) {
            _.animType = 'msTransform';
            _.transformType = '-ms-transform';
            _.transitionType = 'msTransition';
            if (bodyStyle.msTransform === undefined) _.animType = false;
        }
        if (bodyStyle.transform !== undefined && _.animType !== false) {
            _.animType = 'transform';
            _.transformType = 'transform';
            _.transitionType = 'transition';
        }
        _.transformsEnabled = _.options.useTransform && (_.animType !== null && _.animType !== false);
    };


    Slick.prototype.setSlideClasses = function(index) {

        var _ = this,
            centerOffset, allSlides, indexOffset, remainder;

        allSlides = _.$slider
            .find('.slick-slide')
            .removeClass('slick-active slick-center slick-current')
            .attr('aria-hidden', 'true');

        _.$slides
            .eq(index)
            .addClass('slick-current');

        if (_.options.centerMode === true) {

            centerOffset = Math.floor(_.options.slidesToShow / 2);

            if (_.options.infinite === true) {

                if (index >= centerOffset && index <= (_.slideCount - 1) - centerOffset) {

                    _.$slides
                        .slice(index - centerOffset, index + centerOffset + 1)
                        .addClass('slick-active')
                        .attr('aria-hidden', 'false');

                } else {

                    indexOffset = _.options.slidesToShow + index;
                    allSlides
                        .slice(indexOffset - centerOffset + 1, indexOffset + centerOffset + 2)
                        .addClass('slick-active')
                        .attr('aria-hidden', 'false');

                }

                if (index === 0) {

                    allSlides
                        .eq(allSlides.length - 1 - _.options.slidesToShow)
                        .addClass('slick-center');

                } else if (index === _.slideCount - 1) {

                    allSlides
                        .eq(_.options.slidesToShow)
                        .addClass('slick-center');

                }

            }

            _.$slides
                .eq(index)
                .addClass('slick-center');

        } else {

            if (index >= 0 && index <= (_.slideCount - _.options.slidesToShow)) {

                _.$slides
                    .slice(index, index + _.options.slidesToShow)
                    .addClass('slick-active')
                    .attr('aria-hidden', 'false');

            } else if (allSlides.length <= _.options.slidesToShow) {

                allSlides
                    .addClass('slick-active')
                    .attr('aria-hidden', 'false');

            } else {

                remainder = _.slideCount % _.options.slidesToShow;
                indexOffset = _.options.infinite === true ? _.options.slidesToShow + index : index;

                if (_.options.slidesToShow == _.options.slidesToScroll && (_.slideCount - index) < _.options.slidesToShow) {

                    allSlides
                        .slice(indexOffset - (_.options.slidesToShow - remainder), indexOffset + remainder)
                        .addClass('slick-active')
                        .attr('aria-hidden', 'false');

                } else {

                    allSlides
                        .slice(indexOffset, indexOffset + _.options.slidesToShow)
                        .addClass('slick-active')
                        .attr('aria-hidden', 'false');

                }

            }

        }

        if (_.options.lazyLoad === 'ondemand') {
            _.lazyLoad();
        }

    };

    Slick.prototype.setupInfinite = function() {

        var _ = this,
            i, slideIndex, infiniteCount;

        if (_.options.fade === true) {
            _.options.centerMode = false;
        }

        if (_.options.infinite === true && _.options.fade === false) {

            slideIndex = null;

            if (_.slideCount > _.options.slidesToShow) {

                if (_.options.centerMode === true) {
                    infiniteCount = _.options.slidesToShow + 1;
                } else {
                    infiniteCount = _.options.slidesToShow;
                }

                for (i = _.slideCount; i > (_.slideCount -
                        infiniteCount); i -= 1) {
                    slideIndex = i - 1;
                    $(_.$slides[slideIndex]).clone(true).attr('id', '')
                        .attr('data-slick-index', slideIndex - _.slideCount)
                        .prependTo(_.$slideTrack).addClass('slick-cloned');
                }
                for (i = 0; i < infiniteCount; i += 1) {
                    slideIndex = i;
                    $(_.$slides[slideIndex]).clone(true).attr('id', '')
                        .attr('data-slick-index', slideIndex + _.slideCount)
                        .appendTo(_.$slideTrack).addClass('slick-cloned');
                }
                _.$slideTrack.find('.slick-cloned').find('[id]').each(function() {
                    $(this).attr('id', '');
                });

            }

        }

    };

    Slick.prototype.interrupt = function( toggle ) {

        var _ = this;

        if( !toggle ) {
            _.autoPlay();
        }
        _.interrupted = toggle;

    };

    Slick.prototype.selectHandler = function(event) {

        var _ = this;

        var targetElement =
            $(event.target).is('.slick-slide') ?
                $(event.target) :
                $(event.target).parents('.slick-slide');

        var index = parseInt(targetElement.attr('data-slick-index'));

        if (!index) index = 0;

        if (_.slideCount <= _.options.slidesToShow) {

            _.setSlideClasses(index);
            _.asNavFor(index);
            return;

        }

        _.slideHandler(index);

    };

    Slick.prototype.slideHandler = function(index, sync, dontAnimate) {

        var targetSlide, animSlide, oldSlide, slideLeft, targetLeft = null,
            _ = this, navTarget;

        sync = sync || false;

        if (_.animating === true && _.options.waitForAnimate === true) {
            return;
        }

        if (_.options.fade === true && _.currentSlide === index) {
            return;
        }

        if (_.slideCount <= _.options.slidesToShow) {
            return;
        }

        if (sync === false) {
            _.asNavFor(index);
        }

        targetSlide = index;
        targetLeft = _.getLeft(targetSlide);
        slideLeft = _.getLeft(_.currentSlide);

        _.currentLeft = _.swipeLeft === null ? slideLeft : _.swipeLeft;

        if (_.options.infinite === false && _.options.centerMode === false && (index < 0 || index > _.getDotCount() * _.options.slidesToScroll)) {
            if (_.options.fade === false) {
                targetSlide = _.currentSlide;
                if (dontAnimate !== true) {
                    _.animateSlide(slideLeft, function() {
                        _.postSlide(targetSlide);
                    });
                } else {
                    _.postSlide(targetSlide);
                }
            }
            return;
        } else if (_.options.infinite === false && _.options.centerMode === true && (index < 0 || index > (_.slideCount - _.options.slidesToScroll))) {
            if (_.options.fade === false) {
                targetSlide = _.currentSlide;
                if (dontAnimate !== true) {
                    _.animateSlide(slideLeft, function() {
                        _.postSlide(targetSlide);
                    });
                } else {
                    _.postSlide(targetSlide);
                }
            }
            return;
        }

        if ( _.options.autoplay ) {
            clearInterval(_.autoPlayTimer);
        }

        if (targetSlide < 0) {
            if (_.slideCount % _.options.slidesToScroll !== 0) {
                animSlide = _.slideCount - (_.slideCount % _.options.slidesToScroll);
            } else {
                animSlide = _.slideCount + targetSlide;
            }
        } else if (targetSlide >= _.slideCount) {
            if (_.slideCount % _.options.slidesToScroll !== 0) {
                animSlide = 0;
            } else {
                animSlide = targetSlide - _.slideCount;
            }
        } else {
            animSlide = targetSlide;
        }

        _.animating = true;

        _.$slider.trigger('beforeChange', [_, _.currentSlide, animSlide]);

        oldSlide = _.currentSlide;
        _.currentSlide = animSlide;

        _.setSlideClasses(_.currentSlide);

        if ( _.options.asNavFor ) {

            navTarget = _.getNavTarget();
            navTarget = navTarget.slick('getSlick');

            if ( navTarget.slideCount <= navTarget.options.slidesToShow ) {
                navTarget.setSlideClasses(_.currentSlide);
            }

        }

        _.updateDots();
        _.updateArrows();

        if (_.options.fade === true) {
            if (dontAnimate !== true) {

                _.fadeSlideOut(oldSlide);

                _.fadeSlide(animSlide, function() {
                    _.postSlide(animSlide);
                });

            } else {
                _.postSlide(animSlide);
            }
            _.animateHeight();
            return;
        }

        if (dontAnimate !== true) {
            _.animateSlide(targetLeft, function() {
                _.postSlide(animSlide);
            });
        } else {
            _.postSlide(animSlide);
        }

    };

    Slick.prototype.startLoad = function() {

        var _ = this;

        if (_.options.arrows === true && _.slideCount > _.options.slidesToShow) {

            _.$prevArrow.hide();
            _.$nextArrow.hide();

        }

        if (_.options.dots === true && _.slideCount > _.options.slidesToShow) {

            _.$dots.hide();

        }

        _.$slider.addClass('slick-loading');

    };

    Slick.prototype.swipeDirection = function() {

        var xDist, yDist, r, swipeAngle, _ = this;

        xDist = _.touchObject.startX - _.touchObject.curX;
        yDist = _.touchObject.startY - _.touchObject.curY;
        r = Math.atan2(yDist, xDist);

        swipeAngle = Math.round(r * 180 / Math.PI);
        if (swipeAngle < 0) {
            swipeAngle = 360 - Math.abs(swipeAngle);
        }

        if ((swipeAngle <= 45) && (swipeAngle >= 0)) {
            return (_.options.rtl === false ? 'left' : 'right');
        }
        if ((swipeAngle <= 360) && (swipeAngle >= 315)) {
            return (_.options.rtl === false ? 'left' : 'right');
        }
        if ((swipeAngle >= 135) && (swipeAngle <= 225)) {
            return (_.options.rtl === false ? 'right' : 'left');
        }
        if (_.options.verticalSwiping === true) {
            if ((swipeAngle >= 35) && (swipeAngle <= 135)) {
                return 'down';
            } else {
                return 'up';
            }
        }

        return 'vertical';

    };

    Slick.prototype.swipeEnd = function(event) {

        var _ = this,
            slideCount,
            direction;

        _.dragging = false;
        _.interrupted = false;
        _.shouldClick = ( _.touchObject.swipeLength > 10 ) ? false : true;

        if ( _.touchObject.curX === undefined ) {
            return false;
        }

        if ( _.touchObject.edgeHit === true ) {
            _.$slider.trigger('edge', [_, _.swipeDirection() ]);
        }

        if ( _.touchObject.swipeLength >= _.touchObject.minSwipe ) {

            direction = _.swipeDirection();

            switch ( direction ) {

                case 'left':
                case 'down':

                    slideCount =
                        _.options.swipeToSlide ?
                            _.checkNavigable( _.currentSlide + _.getSlideCount() ) :
                            _.currentSlide + _.getSlideCount();

                    _.currentDirection = 0;

                    break;

                case 'right':
                case 'up':

                    slideCount =
                        _.options.swipeToSlide ?
                            _.checkNavigable( _.currentSlide - _.getSlideCount() ) :
                            _.currentSlide - _.getSlideCount();

                    _.currentDirection = 1;

                    break;

                default:


            }

            if( direction != 'vertical' ) {

                _.slideHandler( slideCount );
                _.touchObject = {};
                _.$slider.trigger('swipe', [_, direction ]);

            }

        } else {

            if ( _.touchObject.startX !== _.touchObject.curX ) {

                _.slideHandler( _.currentSlide );
                _.touchObject = {};

            }

        }

    };

    Slick.prototype.swipeHandler = function(event) {

        var _ = this;

        if ((_.options.swipe === false) || ('ontouchend' in document && _.options.swipe === false)) {
            return;
        } else if (_.options.draggable === false && event.type.indexOf('mouse') !== -1) {
            return;
        }

        _.touchObject.fingerCount = event.originalEvent && event.originalEvent.touches !== undefined ?
            event.originalEvent.touches.length : 1;

        _.touchObject.minSwipe = _.listWidth / _.options
            .touchThreshold;

        if (_.options.verticalSwiping === true) {
            _.touchObject.minSwipe = _.listHeight / _.options
                .touchThreshold;
        }

        switch (event.data.action) {

            case 'start':
                _.swipeStart(event);
                break;

            case 'move':
                _.swipeMove(event);
                break;

            case 'end':
                _.swipeEnd(event);
                break;

        }

    };

    Slick.prototype.swipeMove = function(event) {

        var _ = this,
            edgeWasHit = false,
            curLeft, swipeDirection, swipeLength, positionOffset, touches;

        touches = event.originalEvent !== undefined ? event.originalEvent.touches : null;

        if (!_.dragging || touches && touches.length !== 1) {
            return false;
        }

        curLeft = _.getLeft(_.currentSlide);

        _.touchObject.curX = touches !== undefined ? touches[0].pageX : event.clientX;
        _.touchObject.curY = touches !== undefined ? touches[0].pageY : event.clientY;

        _.touchObject.swipeLength = Math.round(Math.sqrt(
            Math.pow(_.touchObject.curX - _.touchObject.startX, 2)));

        if (_.options.verticalSwiping === true) {
            _.touchObject.swipeLength = Math.round(Math.sqrt(
                Math.pow(_.touchObject.curY - _.touchObject.startY, 2)));
        }

        swipeDirection = _.swipeDirection();

        if (swipeDirection === 'vertical') {
            return;
        }

        if (event.originalEvent !== undefined && _.touchObject.swipeLength > 4) {
            event.preventDefault();
        }

        positionOffset = (_.options.rtl === false ? 1 : -1) * (_.touchObject.curX > _.touchObject.startX ? 1 : -1);
        if (_.options.verticalSwiping === true) {
            positionOffset = _.touchObject.curY > _.touchObject.startY ? 1 : -1;
        }


        swipeLength = _.touchObject.swipeLength;

        _.touchObject.edgeHit = false;

        if (_.options.infinite === false) {
            if ((_.currentSlide === 0 && swipeDirection === 'right') || (_.currentSlide >= _.getDotCount() && swipeDirection === 'left')) {
                swipeLength = _.touchObject.swipeLength * _.options.edgeFriction;
                _.touchObject.edgeHit = true;
            }
        }

        if (_.options.vertical === false) {
            _.swipeLeft = curLeft + swipeLength * positionOffset;
        } else {
            _.swipeLeft = curLeft + (swipeLength * (_.$list.height() / _.listWidth)) * positionOffset;
        }
        if (_.options.verticalSwiping === true) {
            _.swipeLeft = curLeft + swipeLength * positionOffset;
        }

        if (_.options.fade === true || _.options.touchMove === false) {
            return false;
        }

        if (_.animating === true) {
            _.swipeLeft = null;
            return false;
        }

        _.setCSS(_.swipeLeft);

    };

    Slick.prototype.swipeStart = function(event) {

        var _ = this,
            touches;

        _.interrupted = true;

        if (_.touchObject.fingerCount !== 1 || _.slideCount <= _.options.slidesToShow) {
            _.touchObject = {};
            return false;
        }

        if (event.originalEvent !== undefined && event.originalEvent.touches !== undefined) {
            touches = event.originalEvent.touches[0];
        }

        _.touchObject.startX = _.touchObject.curX = touches !== undefined ? touches.pageX : event.clientX;
        _.touchObject.startY = _.touchObject.curY = touches !== undefined ? touches.pageY : event.clientY;

        _.dragging = true;

    };

    Slick.prototype.unfilterSlides = Slick.prototype.slickUnfilter = function() {

        var _ = this;

        if (_.$slidesCache !== null) {

            _.unload();

            _.$slideTrack.children(this.options.slide).detach();

            _.$slidesCache.appendTo(_.$slideTrack);

            _.reinit();

        }

    };

    Slick.prototype.unload = function() {

        var _ = this;

        $('.slick-cloned', _.$slider).remove();

        if (_.$dots) {
            _.$dots.remove();
        }

        if (_.$prevArrow && _.htmlExpr.test(_.options.prevArrow)) {
            _.$prevArrow.remove();
        }

        if (_.$nextArrow && _.htmlExpr.test(_.options.nextArrow)) {
            _.$nextArrow.remove();
        }

        _.$slides
            .removeClass('slick-slide slick-active slick-visible slick-current')
            .attr('aria-hidden', 'true')
            .css('width', '');

    };

    Slick.prototype.unslick = function(fromBreakpoint) {

        var _ = this;
        _.$slider.trigger('unslick', [_, fromBreakpoint]);
        _.destroy();

    };

    Slick.prototype.updateArrows = function() {

        var _ = this,
            centerOffset;

        centerOffset = Math.floor(_.options.slidesToShow / 2);

        if ( _.options.arrows === true &&
            _.slideCount > _.options.slidesToShow &&
            !_.options.infinite ) {

            _.$prevArrow.removeClass('slick-disabled').attr('aria-disabled', 'false');
            _.$nextArrow.removeClass('slick-disabled').attr('aria-disabled', 'false');

            if (_.currentSlide === 0) {

                _.$prevArrow.addClass('slick-disabled').attr('aria-disabled', 'true');
                _.$nextArrow.removeClass('slick-disabled').attr('aria-disabled', 'false');

            } else if (_.currentSlide >= _.slideCount - _.options.slidesToShow && _.options.centerMode === false) {

                _.$nextArrow.addClass('slick-disabled').attr('aria-disabled', 'true');
                _.$prevArrow.removeClass('slick-disabled').attr('aria-disabled', 'false');

            } else if (_.currentSlide >= _.slideCount - 1 && _.options.centerMode === true) {

                _.$nextArrow.addClass('slick-disabled').attr('aria-disabled', 'true');
                _.$prevArrow.removeClass('slick-disabled').attr('aria-disabled', 'false');

            }

        }

    };

    Slick.prototype.updateDots = function() {

        var _ = this;

        if (_.$dots !== null) {

            _.$dots
                .find('li')
                .removeClass('slick-active')
                .attr('aria-hidden', 'true');

            _.$dots
                .find('li')
                .eq(Math.floor(_.currentSlide / _.options.slidesToScroll))
                .addClass('slick-active')
                .attr('aria-hidden', 'false');

        }

    };

    Slick.prototype.visibility = function() {

        var _ = this;

        if ( _.options.autoplay ) {

            if ( document[_.hidden] ) {

                _.interrupted = true;

            } else {

                _.interrupted = false;

            }

        }

    };

    $.fn.slick = function() {
        var _ = this,
            opt = arguments[0],
            args = Array.prototype.slice.call(arguments, 1),
            l = _.length,
            i,
            ret;
        for (i = 0; i < l; i++) {
            if (typeof opt == 'object' || typeof opt == 'undefined')
                _[i].slick = new Slick(_[i], opt);
            else
                ret = _[i].slick[opt].apply(_[i].slick, args);
            if (typeof ret != 'undefined') return ret;
        }
        return _;
    };

}));

/**
 * Event.simulate(@element, eventName[, options]) -> Element
 * 
 * - @element: element to fire event on
 * - eventName: name of event to fire (only MouseEvents and HTMLEvents interfaces are supported)
 * - options: optional object to fine-tune event properties - pointerX, pointerY, ctrlKey, etc.
 *
 *    $('foo').simulate('click'); // => fires "click" event on an element with id=foo
 *
 **/
(function(){
  
  var eventMatchers = {
    'HTMLEvents': /^(?:load|unload|abort|error|select|change|submit|reset|focus|blur|resize|scroll)$/,
    'MouseEvents': /^(?:click|dblclick|mouse(?:down|up|over|move|out))$/
  }
  var defaultOptions = {
    pointerX: 0,
    pointerY: 0,
    button: 0,
    ctrlKey: false,
    altKey: false,
    shiftKey: false,
    metaKey: false,
    bubbles: true,
    cancelable: true
  }
  
  Event.simulate = function(element, eventName) {
    var options = Object.extend(Object.clone(defaultOptions), arguments[2] || { });
    var oEvent, eventType = null;
    
    element = $(element);
    
    for (var name in eventMatchers) {
      if (eventMatchers[name].test(eventName)) { eventType = name; break; }
    }

    if (!eventType)
      throw new SyntaxError('Only HTMLEvents and MouseEvents interfaces are supported');

    if (document.createEvent) {
      oEvent = document.createEvent(eventType);
      if (eventType == 'HTMLEvents') {
        oEvent.initEvent(eventName, options.bubbles, options.cancelable);
      }
      else {
        oEvent.initMouseEvent(eventName, options.bubbles, options.cancelable, document.defaultView, 
          options.button, options.pointerX, options.pointerY, options.pointerX, options.pointerY,
          options.ctrlKey, options.altKey, options.shiftKey, options.metaKey, options.button, element);
      }
      element.dispatchEvent(oEvent);
    }
    else {
      options.clientX = options.pointerX;
      options.clientY = options.pointerY;
      oEvent = Object.extend(document.createEventObject(), options);
      element.fireEvent('on' + eventName, oEvent);
    }
    return element;
  }
  
  Element.addMethods({ simulate: Event.simulate });
})();
// Chosen, a Select Box Enhancer for jQuery and Prototype
// by Patrick Filler for Harvest, http://getharvest.com
//
// Version 1.0.0
// Full source at https://github.com/harvesthq/chosen
// Copyright (c) 2011 Harvest http://getharvest.com

// MIT License, https://github.com/harvesthq/chosen/blob/master/LICENSE.md
// This file is generated by `grunt build`, do not edit it by hand.
;
(function() {
  var AbstractChosen, SelectParser, _ref,
    __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  SelectParser = (function() {
    function SelectParser() {
      this.options_index = 0;
      this.parsed = [];
    }

    SelectParser.prototype.add_node = function(child) {
      if (child.nodeName.toUpperCase() === "OPTGROUP") {
        return this.add_group(child);
      } else {
        return this.add_option(child);
      }
    };

    SelectParser.prototype.add_group = function(group) {
      var group_position, option, _i, _len, _ref, _results;

      group_position = this.parsed.length;
      this.parsed.push({
        array_index: group_position,
        group: true,
        label: this.escapeExpression(group.label),
        children: 0,
        disabled: group.disabled
      });
      _ref = group.childNodes;
      _results = [];
      for (_i = 0, _len = _ref.length; _i < _len; _i++) {
        option = _ref[_i];
        _results.push(this.add_option(option, group_position, group.disabled));
      }
      return _results;
    };

    SelectParser.prototype.add_option = function(option, group_position, group_disabled) {
      if (option.nodeName.toUpperCase() === "OPTION") {
        if (option.text !== "") {
          if (group_position != null) {
            this.parsed[group_position].children += 1;
          }
          this.parsed.push({
            array_index: this.parsed.length,
            options_index: this.options_index,
            value: option.value,
            text: option.text,
            html: option.innerHTML,
            selected: option.selected,
            disabled: group_disabled === true ? group_disabled : option.disabled,
            group_array_index: group_position,
            classes: option.className,
            style: option.style.cssText
          });
        } else {
          this.parsed.push({
            array_index: this.parsed.length,
            options_index: this.options_index,
            empty: true
          });
        }
        return this.options_index += 1;
      }
    };

    SelectParser.prototype.escapeExpression = function(text) {
      var map, unsafe_chars;

      if ((text == null) || text === false) {
        return "";
      }
      if (!/[\&\<\>\"\'\`]/.test(text)) {
        return text;
      }
      map = {
        "<": "&lt;",
        ">": "&gt;",
        '"': "&quot;",
        "'": "&#x27;",
        "`": "&#x60;"
      };
      unsafe_chars = /&(?!\w+;)|[\<\>\"\'\`]/g;
      return text.replace(unsafe_chars, function(chr) {
        return map[chr] || "&amp;";
      });
    };

    return SelectParser;

  })();

  SelectParser.select_to_array = function(select) {
    var child, parser, _i, _len, _ref;

    parser = new SelectParser();
    _ref = select.childNodes;
    for (_i = 0, _len = _ref.length; _i < _len; _i++) {
      child = _ref[_i];
      parser.add_node(child);
    }
    return parser.parsed;
  };

  AbstractChosen = (function() {
    function AbstractChosen(form_field, options) {
      this.form_field = form_field;
      this.options = options != null ? options : {};
      if (!AbstractChosen.browser_is_supported()) {
        return;
      }
      this.is_multiple = this.form_field.multiple;
      this.set_default_text();
      this.set_default_values();
      this.setup();
      this.set_up_html();
      this.register_observers();
    }

    AbstractChosen.prototype.set_default_values = function() {
      var _this = this;

      this.click_test_action = function(evt) {
        return _this.test_active_click(evt);
      };
      this.activate_action = function(evt) {
        return _this.activate_field(evt);
      };
      this.active_field = false;
      this.mouse_on_container = false;
      this.results_showing = false;
      this.result_highlighted = null;
      this.result_single_selected = null;
      this.allow_single_deselect = (this.options.allow_single_deselect != null) && (this.form_field.options[0] != null) && this.form_field.options[0].text === "" ? this.options.allow_single_deselect : false;
      this.disable_search_threshold = this.options.disable_search_threshold || 0;
      this.disable_search = this.options.disable_search || false;
      this.enable_split_word_search = this.options.enable_split_word_search != null ? this.options.enable_split_word_search : true;
      this.group_search = this.options.group_search != null ? this.options.group_search : true;
      this.search_contains = this.options.search_contains || false;
      this.single_backstroke_delete = this.options.single_backstroke_delete != null ? this.options.single_backstroke_delete : true;
      this.max_selected_options = this.options.max_selected_options || Infinity;
      this.inherit_select_classes = this.options.inherit_select_classes || false;
      this.display_selected_options = this.options.display_selected_options != null ? this.options.display_selected_options : true;
      return this.display_disabled_options = this.options.display_disabled_options != null ? this.options.display_disabled_options : true;
    };

    AbstractChosen.prototype.set_default_text = function() {
      if (this.form_field.getAttribute("data-placeholder")) {
        this.default_text = this.form_field.getAttribute("data-placeholder");
      } else if (this.is_multiple) {
        this.default_text = this.options.placeholder_text_multiple || this.options.placeholder_text || AbstractChosen.default_multiple_text;
      } else {
        this.default_text = this.options.placeholder_text_single || this.options.placeholder_text || AbstractChosen.default_single_text;
      }
      return this.results_none_found = this.form_field.getAttribute("data-no_results_text") || this.options.no_results_text || AbstractChosen.default_no_result_text;
    };

    AbstractChosen.prototype.mouse_enter = function() {
      return this.mouse_on_container = true;
    };

    AbstractChosen.prototype.mouse_leave = function() {
      return this.mouse_on_container = false;
    };

    AbstractChosen.prototype.input_focus = function(evt) {
      var _this = this;

      if (this.is_multiple) {
        if (!this.active_field) {
          return setTimeout((function() {
            return _this.container_mousedown();
          }), 50);
        }
      } else {
        if (!this.active_field) {
          return this.activate_field();
        }
      }
    };

    AbstractChosen.prototype.input_blur = function(evt) {
      var _this = this;

      if (!this.mouse_on_container) {
        this.active_field = false;
        return setTimeout((function() {
          return _this.blur_test();
        }), 100);
      }
    };

    AbstractChosen.prototype.results_option_build = function(options) {
      var content, data, _i, _len, _ref;

      content = '';
      _ref = this.results_data;
      for (_i = 0, _len = _ref.length; _i < _len; _i++) {
        data = _ref[_i];
        if (data.group) {
          content += this.result_add_group(data);
        } else {
          content += this.result_add_option(data);
        }
        if (options != null ? options.first : void 0) {
          if (data.selected && this.is_multiple) {
            this.choice_build(data);
          } else if (data.selected && !this.is_multiple) {
            this.single_set_selected_text(data.text);
          }
        }
      }
      return content;
    };

    AbstractChosen.prototype.result_add_option = function(option) {
      var classes, style;

      if (!option.search_match) {
        return '';
      }
      if (!this.include_option_in_results(option)) {
        return '';
      }
      classes = [];
      if (!option.disabled && !(option.selected && this.is_multiple)) {
        classes.push("active-result");
      }
      if (option.disabled && !(option.selected && this.is_multiple)) {
        classes.push("disabled-result");
      }
      if (option.selected) {
        classes.push("result-selected");
      }
      if (option.group_array_index != null) {
        classes.push("group-option");
      }
      if (option.classes !== "") {
        classes.push(option.classes);
      }
      style = option.style.cssText !== "" ? " style=\"" + option.style + "\"" : "";
      return "<li class=\"" + (classes.join(' ')) + "\"" + style + " data-option-array-index=\"" + option.array_index + "\">" + option.search_text + "</li>";
    };

    AbstractChosen.prototype.result_add_group = function(group) {
      if (!(group.search_match || group.group_match)) {
        return '';
      }
      if (!(group.active_options > 0)) {
        return '';
      }
      return "<li class=\"group-result\">" + group.search_text + "</li>";
    };

    AbstractChosen.prototype.results_update_field = function() {
      this.set_default_text();
      if (!this.is_multiple) {
        this.results_reset_cleanup();
      }
      this.result_clear_highlight();
      this.result_single_selected = null;
      this.results_build();
      if (this.results_showing) {
        return this.winnow_results();
      }
    };

    AbstractChosen.prototype.results_toggle = function() {
      if (this.results_showing) {
        return this.results_hide();
      } else {
        return this.results_show();
      }
    };

    AbstractChosen.prototype.results_search = function(evt) {
      if (this.results_showing) {
        return this.winnow_results();
      } else {
        return this.results_show();
      }
    };

    AbstractChosen.prototype.winnow_results = function() {
      var escapedSearchText, option, regex, regexAnchor, results, results_group, searchText, startpos, text, zregex, _i, _len, _ref;

      this.no_results_clear();
      results = 0;
      searchText = this.get_search_text();
      escapedSearchText = searchText.replace(/[-[\]{}()*+?.,\\^$|#\s]/g, "\\$&");
      regexAnchor = this.search_contains ? "" : "^";
      regex = new RegExp(regexAnchor + escapedSearchText, 'i');
      zregex = new RegExp(escapedSearchText, 'i');
      _ref = this.results_data;
      for (_i = 0, _len = _ref.length; _i < _len; _i++) {
        option = _ref[_i];
        option.search_match = false;
        results_group = null;
        if (this.include_option_in_results(option)) {
          if (option.group) {
            option.group_match = false;
            option.active_options = 0;
          }
          if ((option.group_array_index != null) && this.results_data[option.group_array_index]) {
            results_group = this.results_data[option.group_array_index];
            if (results_group.active_options === 0 && results_group.search_match) {
              results += 1;
            }
            results_group.active_options += 1;
          }
          if (!(option.group && !this.group_search)) {
            option.search_text = option.group ? option.label : option.html;
            option.search_match = this.search_string_match(option.search_text, regex);
            if (option.search_match && !option.group) {
              results += 1;
            }
            if (option.search_match) {
              if (searchText.length) {
                startpos = option.search_text.search(zregex);
                text = option.search_text.substr(0, startpos + searchText.length) + '</em>' + option.search_text.substr(startpos + searchText.length);
                option.search_text = text.substr(0, startpos) + '<em>' + text.substr(startpos);
              }
              if (results_group != null) {
                results_group.group_match = true;
              }
            } else if ((option.group_array_index != null) && this.results_data[option.group_array_index].search_match) {
              option.search_match = true;
            }
          }
        }
      }
      this.result_clear_highlight();
      if (results < 1 && searchText.length) {
        this.update_results_content("");
        return this.no_results(searchText);
      } else {
        this.update_results_content(this.results_option_build());
        return this.winnow_results_set_highlight();
      }
    };

    AbstractChosen.prototype.search_string_match = function(search_string, regex) {
      var part, parts, _i, _len;

      if (regex.test(search_string)) {
        return true;
      } else if (this.enable_split_word_search && (search_string.indexOf(" ") >= 0 || search_string.indexOf("[") === 0)) {
        parts = search_string.replace(/\[|\]/g, "").split(" ");
        if (parts.length) {
          for (_i = 0, _len = parts.length; _i < _len; _i++) {
            part = parts[_i];
            if (regex.test(part)) {
              return true;
            }
          }
        }
      }
    };

    AbstractChosen.prototype.choices_count = function() {
      var option, _i, _len, _ref;

      if (this.selected_option_count != null) {
        return this.selected_option_count;
      }
      this.selected_option_count = 0;
      _ref = this.form_field.options;
      for (_i = 0, _len = _ref.length; _i < _len; _i++) {
        option = _ref[_i];
        if (option.selected) {
          this.selected_option_count += 1;
        }
      }
      return this.selected_option_count;
    };

    AbstractChosen.prototype.choices_click = function(evt) {
      evt.preventDefault();
      if (!(this.results_showing || this.is_disabled)) {
        return this.results_show();
      }
    };

    AbstractChosen.prototype.keyup_checker = function(evt) {
      var stroke, _ref;

      stroke = (_ref = evt.which) != null ? _ref : evt.keyCode;
      this.search_field_scale();
      switch (stroke) {
        case 8:
          if (this.is_multiple && this.backstroke_length < 1 && this.choices_count() > 0) {
            return this.keydown_backstroke();
          } else if (!this.pending_backstroke) {
            this.result_clear_highlight();
            return this.results_search();
          }
          break;
        case 13:
          evt.preventDefault();
          if (this.results_showing) {
            return this.result_select(evt);
          }
          break;
        case 27:
          if (this.results_showing) {
            this.results_hide();
          }
          return true;
        case 9:
        case 38:
        case 40:
        case 16:
        case 91:
        case 17:
          break;
        default:
          return this.results_search();
      }
    };

    AbstractChosen.prototype.container_width = function() {
      if (this.options.width != null) {
        return this.options.width;
      } else {
        return "" + this.form_field.offsetWidth + "px";
      }
    };

    AbstractChosen.prototype.include_option_in_results = function(option) {
      if (this.is_multiple && (!this.display_selected_options && option.selected)) {
        return false;
      }
      if (!this.display_disabled_options && option.disabled) {
        return false;
      }
      if (option.empty) {
        return false;
      }
      return true;
    };

    AbstractChosen.browser_is_supported = function() {
      if (window.navigator.appName === "Microsoft Internet Explorer") {
        return document.documentMode >= 8;
      }
      // if (/iP(od|hone)/i.test(window.navigator.userAgent)) {
      //   return false;
      // }
      // if (/Android/i.test(window.navigator.userAgent)) {
      //   if (/Mobile/i.test(window.navigator.userAgent)) {
      //     return false;
      //   }
      // }
      return true;
    };

    AbstractChosen.default_multiple_text = "Select Some Options";

    AbstractChosen.default_single_text = "Select an Option";

    AbstractChosen.default_no_result_text = "No results match";

    return AbstractChosen;

  })();

  this.Chosen = (function(_super) {
    __extends(Chosen, _super);

    function Chosen() {
      _ref = Chosen.__super__.constructor.apply(this, arguments);
      return _ref;
    }

    Chosen.prototype.setup = function() {
      this.current_selectedIndex = this.form_field.selectedIndex;
      return this.is_rtl = this.form_field.hasClassName("chosen-rtl");
    };

    Chosen.prototype.set_default_values = function() {
      Chosen.__super__.set_default_values.call(this);
      this.single_temp = new Template('<a class="chosen-single chosen-default" tabindex="-1"><span>#{default}</span><div><b></b></div></a><div class="chosen-drop"><div class="chosen-search"><input type="text" autocomplete="off" /></div><ul class="chosen-results"></ul></div>');
      this.multi_temp = new Template('<ul class="chosen-choices"><li class="search-field"><input type="text" value="#{default}" class="default" autocomplete="off" style="width:25px;" /></li></ul><div class="chosen-drop"><ul class="chosen-results"></ul></div>');
      return this.no_results_temp = new Template('<li class="no-results">' + this.results_none_found + ' "<span>#{terms}</span>"</li>');
    };

    Chosen.prototype.set_up_html = function() {
      var container_classes, container_props;

      container_classes = ["chosen-container"];
      container_classes.push("chosen-container-" + (this.is_multiple ? "multi" : "single"));
      if (this.inherit_select_classes && this.form_field.className) {
        container_classes.push(this.form_field.className);
      }
      if (this.is_rtl) {
        container_classes.push("chosen-rtl");
      }
      container_props = {
        'class': container_classes.join(' '),
        'style': "width: " + (this.container_width()) + ";",
        'title': this.form_field.title
      };
      if (this.form_field.id.length) {
        container_props.id = this.form_field.id.replace(/[^\w]/g, '_') + "_chosen";
      }
      this.container = this.is_multiple ? new Element('div', container_props).update(this.multi_temp.evaluate({
        "default": this.default_text
      })) : new Element('div', container_props).update(this.single_temp.evaluate({
        "default": this.default_text
      }));
      this.form_field.hide().insert({
        after: this.container
      });
      this.dropdown = this.container.down('div.chosen-drop');
      this.search_field = this.container.down('input');
      this.search_results = this.container.down('ul.chosen-results');
      this.search_field_scale();
      this.search_no_results = this.container.down('li.no-results');
      if (this.is_multiple) {
        this.search_choices = this.container.down('ul.chosen-choices');
        this.search_container = this.container.down('li.search-field');
      } else {
        this.search_container = this.container.down('div.chosen-search');
        this.selected_item = this.container.down('.chosen-single');
      }
      this.results_build();
      this.set_tab_index();
      this.set_label_behavior();
      return this.form_field.fire("chosen:ready", {
        chosen: this
      });
    };

    Chosen.prototype.register_observers = function() {
      var _this = this;

      this.container.observe("mousedown", function(evt) {
        return _this.container_mousedown(evt);
      });
      this.container.observe("mouseup", function(evt) {
        return _this.container_mouseup(evt);
      });
      this.container.observe("mouseenter", function(evt) {
        return _this.mouse_enter(evt);
      });
      this.container.observe("mouseleave", function(evt) {
        return _this.mouse_leave(evt);
      });
      this.search_results.observe("mouseup", function(evt) {
        return _this.search_results_mouseup(evt);
      });
      this.search_results.observe("mouseover", function(evt) {
        return _this.search_results_mouseover(evt);
      });
      this.search_results.observe("mouseout", function(evt) {
        return _this.search_results_mouseout(evt);
      });
      this.search_results.observe("mousewheel", function(evt) {
        return _this.search_results_mousewheel(evt);
      });
      this.search_results.observe("DOMMouseScroll", function(evt) {
        return _this.search_results_mousewheel(evt);
      });
      this.form_field.observe("chosen:updated", function(evt) {
        return _this.results_update_field(evt);
      });
      this.form_field.observe("chosen:activate", function(evt) {
        return _this.activate_field(evt);
      });
      this.form_field.observe("chosen:open", function(evt) {
        return _this.container_mousedown(evt);
      });
      this.search_field.observe("blur", function(evt) {
        return _this.input_blur(evt);
      });
      this.search_field.observe("keyup", function(evt) {
        return _this.keyup_checker(evt);
      });
      this.search_field.observe("keydown", function(evt) {
        return _this.keydown_checker(evt);
      });
      this.search_field.observe("focus", function(evt) {
        return _this.input_focus(evt);
      });
      if (this.is_multiple) {
        return this.search_choices.observe("click", function(evt) {
          return _this.choices_click(evt);
        });
      } else {
        return this.container.observe("click", function(evt) {
          return evt.preventDefault();
        });
      }
    };

    Chosen.prototype.destroy = function() {
      document.stopObserving("click", this.click_test_action);
      this.form_field.stopObserving();
      this.container.stopObserving();
      this.search_results.stopObserving();
      this.search_field.stopObserving();
      if (this.form_field_label != null) {
        this.form_field_label.stopObserving();
      }
      if (this.is_multiple) {
        this.search_choices.stopObserving();
        this.container.select(".search-choice-close").each(function(choice) {
          return choice.stopObserving();
        });
      } else {
        this.selected_item.stopObserving();
      }
      if (this.search_field.tabIndex) {
        this.form_field.tabIndex = this.search_field.tabIndex;
      }
      this.container.remove();
      return this.form_field.show();
    };

    Chosen.prototype.search_field_disabled = function() {
      this.is_disabled = this.form_field.disabled;
      if (this.is_disabled) {
        this.container.addClassName('chosen-disabled');
        this.search_field.disabled = true;
        if (!this.is_multiple) {
          this.selected_item.stopObserving("focus", this.activate_action);
        }
        return this.close_field();
      } else {
        this.container.removeClassName('chosen-disabled');
        this.search_field.disabled = false;
        if (!this.is_multiple) {
          return this.selected_item.observe("focus", this.activate_action);
        }
      }
    };

    Chosen.prototype.container_mousedown = function(evt) {
      if (!this.is_disabled) {
        if (evt && evt.type === "mousedown" && !this.results_showing) {
          evt.stop();
        }
        if (!((evt != null) && evt.target.hasClassName("search-choice-close"))) {
          if (!this.active_field) {
            if (this.is_multiple) {
              this.search_field.clear();
            }
            document.observe("click", this.click_test_action);
            this.results_show();
          } else if (!this.is_multiple && evt && (evt.target === this.selected_item || evt.target.up("a.chosen-single"))) {
            this.results_toggle();
          }
          return this.activate_field();
        }
      }
    };

    Chosen.prototype.container_mouseup = function(evt) {
      if (evt.target.nodeName === "ABBR" && !this.is_disabled) {
        return this.results_reset(evt);
      }
    };

    Chosen.prototype.search_results_mousewheel = function(evt) {
      var delta;

      delta = -evt.wheelDelta || evt.detail;
      if (delta != null) {
        evt.preventDefault();
        if (evt.type === 'DOMMouseScroll') {
          delta = delta * 40;
        }
        return this.search_results.scrollTop = delta + this.search_results.scrollTop;
      }
    };

    Chosen.prototype.blur_test = function(evt) {
      if (!this.active_field && this.container.hasClassName("chosen-container-active")) {
        return this.close_field();
      }
    };

    Chosen.prototype.close_field = function() {
      document.stopObserving("click", this.click_test_action);
      this.active_field = false;
      this.results_hide();
      this.container.removeClassName("chosen-container-active");
      this.clear_backstroke();
      this.show_search_field_default();
      return this.search_field_scale();
    };

    Chosen.prototype.activate_field = function() {
      this.container.addClassName("chosen-container-active");
      this.active_field = true;
      this.search_field.value = this.search_field.value;
      return this.search_field.focus();
    };

    Chosen.prototype.test_active_click = function(evt) {
      if (evt.target.up('.chosen-container') === this.container) {
        return this.active_field = true;
      } else {
        return this.close_field();
      }
    };

    Chosen.prototype.results_build = function() {
      this.parsing = true;
      this.selected_option_count = null;
      this.results_data = SelectParser.select_to_array(this.form_field);
      if (this.is_multiple) {
        this.search_choices.select("li.search-choice").invoke("remove");
      } else if (!this.is_multiple) {
        this.single_set_selected_text();
        if (this.disable_search || this.form_field.options.length <= this.disable_search_threshold) {
          this.search_field.readOnly = true;
          this.container.addClassName("chosen-container-single-nosearch");
        } else {
          this.search_field.readOnly = false;
          this.container.removeClassName("chosen-container-single-nosearch");
        }
      }
      this.update_results_content(this.results_option_build({
        first: true
      }));
      this.search_field_disabled();
      this.show_search_field_default();
      this.search_field_scale();
      return this.parsing = false;
    };

    Chosen.prototype.result_do_highlight = function(el) {
      var high_bottom, high_top, maxHeight, visible_bottom, visible_top;

      this.result_clear_highlight();
      this.result_highlight = el;
      this.result_highlight.addClassName("highlighted");
      maxHeight = parseInt(this.search_results.getStyle('maxHeight'), 10);
      visible_top = this.search_results.scrollTop;
      visible_bottom = maxHeight + visible_top;
      high_top = this.result_highlight.positionedOffset().top;
      high_bottom = high_top + this.result_highlight.getHeight();
      if (high_bottom >= visible_bottom) {
        return this.search_results.scrollTop = (high_bottom - maxHeight) > 0 ? high_bottom - maxHeight : 0;
      } else if (high_top < visible_top) {
        return this.search_results.scrollTop = high_top;
      }
    };

    Chosen.prototype.result_clear_highlight = function() {
      if (this.result_highlight) {
        this.result_highlight.removeClassName('highlighted');
      }
      return this.result_highlight = null;
    };

    Chosen.prototype.results_show = function() {
      if (this.is_multiple && this.max_selected_options <= this.choices_count()) {
        this.form_field.fire("chosen:maxselected", {
          chosen: this
        });
        return false;
      }
      this.container.addClassName("chosen-with-drop");
      this.form_field.fire("chosen:showing_dropdown", {
        chosen: this
      });
      this.results_showing = true;
      this.search_field.focus();
      this.search_field.value = this.search_field.value;
      return this.winnow_results();
    };

    Chosen.prototype.update_results_content = function(content) {
      return this.search_results.update(content);
    };

    Chosen.prototype.results_hide = function() {
      if (this.results_showing) {
        this.result_clear_highlight();
        this.container.removeClassName("chosen-with-drop");
        this.form_field.fire("chosen:hiding_dropdown", {
          chosen: this
        });
      }
      return this.results_showing = false;
    };

    Chosen.prototype.set_tab_index = function(el) {
      var ti;

      if (this.form_field.tabIndex) {
        ti = this.form_field.tabIndex;
        this.form_field.tabIndex = -1;
        return this.search_field.tabIndex = ti;
      }
    };

    Chosen.prototype.set_label_behavior = function() {
      var _this = this;

      this.form_field_label = this.form_field.up("label");
      if (this.form_field_label == null) {
        this.form_field_label = $$("label[for='" + this.form_field.id + "']").first();
      }
      if (this.form_field_label != null) {
        return this.form_field_label.observe("click", function(evt) {
          if (_this.is_multiple) {
            return _this.container_mousedown(evt);
          } else {
            return _this.activate_field();
          }
        });
      }
    };

    Chosen.prototype.show_search_field_default = function() {
      if (this.is_multiple && this.choices_count() < 1 && !this.active_field) {
        this.search_field.value = this.default_text;
        return this.search_field.addClassName("default");
      } else {
        this.search_field.value = "";
        return this.search_field.removeClassName("default");
      }
    };

    Chosen.prototype.search_results_mouseup = function(evt) {
      var target;

      target = evt.target.hasClassName("active-result") ? evt.target : evt.target.up(".active-result");
      if (target) {
        this.result_highlight = target;
        this.result_select(evt);
        return this.search_field.focus();
      }
    };

    Chosen.prototype.search_results_mouseover = function(evt) {
      var target;

      target = evt.target.hasClassName("active-result") ? evt.target : evt.target.up(".active-result");
      if (target) {
        return this.result_do_highlight(target);
      }
    };

    Chosen.prototype.search_results_mouseout = function(evt) {
      if (evt.target.hasClassName('active-result') || evt.target.up('.active-result')) {
        return this.result_clear_highlight();
      }
    };

    Chosen.prototype.choice_build = function(item) {
      var choice, close_link,
        _this = this;

      choice = new Element('li', {
        "class": "search-choice"
      }).update("<span>" + item.html + "</span>");
      if (item.disabled) {
        choice.addClassName('search-choice-disabled');
      } else {
        close_link = new Element('a', {
          href: '#',
          "class": 'search-choice-close',
          rel: item.array_index
        });
        close_link.observe("click", function(evt) {
          return _this.choice_destroy_link_click(evt);
        });
        choice.insert(close_link);
      }
      return this.search_container.insert({
        before: choice
      });
    };

    Chosen.prototype.choice_destroy_link_click = function(evt) {
      evt.preventDefault();
      evt.stopPropagation();
      if (!this.is_disabled) {
        return this.choice_destroy(evt.target);
      }
    };

    Chosen.prototype.choice_destroy = function(link) {
      if (this.result_deselect(link.readAttribute("rel"))) {
        this.show_search_field_default();
        if (this.is_multiple && this.choices_count() > 0 && this.search_field.value.length < 1) {
          this.results_hide();
        }
        link.up('li').remove();
        return this.search_field_scale();
      }
    };

    Chosen.prototype.results_reset = function() {
      this.form_field.options[0].selected = true;
      this.selected_option_count = null;
      this.single_set_selected_text();
      this.show_search_field_default();
      this.results_reset_cleanup();
      if (typeof Event.simulate === 'function') {
        this.form_field.simulate("change");
      }
      if (this.active_field) {
        return this.results_hide();
      }
    };

    Chosen.prototype.results_reset_cleanup = function() {
      var deselect_trigger;

      this.current_selectedIndex = this.form_field.selectedIndex;
      deselect_trigger = this.selected_item.down("abbr");
      if (deselect_trigger) {
        return deselect_trigger.remove();
      }
    };

    Chosen.prototype.result_select = function(evt) {
      var high, item, selected_index;

      if (this.result_highlight) {
        high = this.result_highlight;
        this.result_clear_highlight();
        if (this.is_multiple && this.max_selected_options <= this.choices_count()) {
          this.form_field.fire("chosen:maxselected", {
            chosen: this
          });
          return false;
        }
        if (this.is_multiple) {
          high.removeClassName("active-result");
        } else {
          if (this.result_single_selected) {
            this.result_single_selected.removeClassName("result-selected");
            selected_index = this.result_single_selected.getAttribute('data-option-array-index');
            this.results_data[selected_index].selected = false;
          }
          this.result_single_selected = high;
        }
        high.addClassName("result-selected");
        item = this.results_data[high.getAttribute("data-option-array-index")];
        item.selected = true;
        this.form_field.options[item.options_index].selected = true;
        this.selected_option_count = null;
        if (this.is_multiple) {
          this.choice_build(item);
        } else {
          this.single_set_selected_text(item.text);
        }
        if (!((evt.metaKey || evt.ctrlKey) && this.is_multiple)) {
          this.results_hide();
        }
        this.search_field.value = "";
        if (typeof Event.simulate === 'function' && (this.is_multiple || this.form_field.selectedIndex !== this.current_selectedIndex)) {
          this.form_field.simulate("change");
        }
        this.current_selectedIndex = this.form_field.selectedIndex;
        return this.search_field_scale();
      }
    };

    Chosen.prototype.single_set_selected_text = function(text) {
      if (text == null) {
        text = this.default_text;
      }
      if (text === this.default_text) {
        this.selected_item.addClassName("chosen-default");
      } else {
        this.single_deselect_control_build();
        this.selected_item.removeClassName("chosen-default");
      }
      return this.selected_item.down("span").update(text);
    };

    Chosen.prototype.result_deselect = function(pos) {
      var result_data;

      result_data = this.results_data[pos];
      if (!this.form_field.options[result_data.options_index].disabled) {
        result_data.selected = false;
        this.form_field.options[result_data.options_index].selected = false;
        this.selected_option_count = null;
        this.result_clear_highlight();
        if (this.results_showing) {
          this.winnow_results();
        }
        if (typeof Event.simulate === 'function') {
          this.form_field.simulate("change");
        }
        this.search_field_scale();
        return true;
      } else {
        return false;
      }
    };

    Chosen.prototype.single_deselect_control_build = function() {
      if (!this.allow_single_deselect) {
        return;
      }
      if (!this.selected_item.down("abbr")) {
        this.selected_item.down("span").insert({
          after: "<abbr class=\"search-choice-close\"></abbr>"
        });
      }
      return this.selected_item.addClassName("chosen-single-with-deselect");
    };

    Chosen.prototype.get_search_text = function() {
      if (this.search_field.value === this.default_text) {
        return "";
      } else {
        return this.search_field.value.strip().escapeHTML();
      }
    };

    Chosen.prototype.winnow_results_set_highlight = function() {
      var do_high;

      if (!this.is_multiple) {
        do_high = this.search_results.down(".result-selected.active-result");
      }
      if (do_high == null) {
        do_high = this.search_results.down(".active-result");
      }
      if (do_high != null) {
        return this.result_do_highlight(do_high);
      }
    };

    Chosen.prototype.no_results = function(terms) {
      return this.search_results.insert(this.no_results_temp.evaluate({
        terms: terms
      }));
    };

    Chosen.prototype.no_results_clear = function() {
      var nr, _results;

      nr = null;
      _results = [];
      while (nr = this.search_results.down(".no-results")) {
        _results.push(nr.remove());
      }
      return _results;
    };

    Chosen.prototype.keydown_arrow = function() {
      var next_sib;

      if (this.results_showing && this.result_highlight) {
        next_sib = this.result_highlight.next('.active-result');
        if (next_sib) {
          return this.result_do_highlight(next_sib);
        }
      } else {
        return this.results_show();
      }
    };

    Chosen.prototype.keyup_arrow = function() {
      var actives, prevs, sibs;

      if (!this.results_showing && !this.is_multiple) {
        return this.results_show();
      } else if (this.result_highlight) {
        sibs = this.result_highlight.previousSiblings();
        actives = this.search_results.select("li.active-result");
        prevs = sibs.intersect(actives);
        if (prevs.length) {
          return this.result_do_highlight(prevs.first());
        } else {
          if (this.choices_count() > 0) {
            this.results_hide();
          }
          return this.result_clear_highlight();
        }
      }
    };

    Chosen.prototype.keydown_backstroke = function() {
      var next_available_destroy;

      if (this.pending_backstroke) {
        this.choice_destroy(this.pending_backstroke.down("a"));
        return this.clear_backstroke();
      } else {
        next_available_destroy = this.search_container.siblings().last();
        if (next_available_destroy && next_available_destroy.hasClassName("search-choice") && !next_available_destroy.hasClassName("search-choice-disabled")) {
          this.pending_backstroke = next_available_destroy;
          if (this.pending_backstroke) {
            this.pending_backstroke.addClassName("search-choice-focus");
          }
          if (this.single_backstroke_delete) {
            return this.keydown_backstroke();
          } else {
            return this.pending_backstroke.addClassName("search-choice-focus");
          }
        }
      }
    };

    Chosen.prototype.clear_backstroke = function() {
      if (this.pending_backstroke) {
        this.pending_backstroke.removeClassName("search-choice-focus");
      }
      return this.pending_backstroke = null;
    };

    Chosen.prototype.keydown_checker = function(evt) {
      var stroke, _ref1;

      stroke = (_ref1 = evt.which) != null ? _ref1 : evt.keyCode;
      this.search_field_scale();
      if (stroke !== 8 && this.pending_backstroke) {
        this.clear_backstroke();
      }
      switch (stroke) {
        case 8:
          this.backstroke_length = this.search_field.value.length;
          break;
        case 9:
          if (this.results_showing && !this.is_multiple) {
            this.result_select(evt);
          }
          this.mouse_on_container = false;
          break;
        case 13:
          evt.preventDefault();
          break;
        case 38:
          evt.preventDefault();
          this.keyup_arrow();
          break;
        case 40:
          evt.preventDefault();
          this.keydown_arrow();
          break;
      }
    };

    Chosen.prototype.search_field_scale = function() {
      var div, f_width, h, style, style_block, styles, w, _i, _len;

      if (this.is_multiple) {
        h = 0;
        w = 0;
        style_block = "position:absolute; left: -1000px; top: -1000px; display:none;";
        styles = ['font-size', 'font-style', 'font-weight', 'font-family', 'line-height', 'text-transform', 'letter-spacing'];
        for (_i = 0, _len = styles.length; _i < _len; _i++) {
          style = styles[_i];
          style_block += style + ":" + this.search_field.getStyle(style) + ";";
        }
        div = new Element('div', {
          'style': style_block
        }).update(this.search_field.value.escapeHTML());
        document.body.appendChild(div);
        w = Element.measure(div, 'width') + 25;
        div.remove();
        f_width = this.container.getWidth();
        if (w > f_width - 10) {
          w = f_width - 10;
        }
        return this.search_field.setStyle({
          'width': w + 'px'
        });
      }
    };

    return Chosen;

  })(AbstractChosen);

}).call(this);

var EasyTabs = Class.create();
EasyTabs.prototype = {
    tpl: {
        tab    : '(.+)?',
        href   : '#product_tabs_(.+)?',
        content: 'product_tabs_(.+)?_contents'
    },
    config: {
        tabs     : '.easytabs-anchor',
        scrollSpeed: 0.5,
        scrollOffset: -5
    },

    initialize: function(container, options) {
        Object.extend(this.config, options || {});
        this.container = container;
        this.activeTabs = [];
        this.counters = {}; // Activity counters

        if (this.container.hasAttribute("data-track-hash") && window.location.hash.length > 1) {
            this.activate(this.getTabByHref(window.location.hash), true);
            Event.observe(window, "load", function() {
                this.activate(this.getTabByHref(window.location.hash), true);
            }.bind(this));
        }

        Event.observe(window, "hashchange", function() {
            var href = window.location.hash;
            if (href.length <= 1) {
                var first = this.container.down(this.config.tabs);
                href = first.href || first.readAttribute('data-href');
            } else {
                if (-1 === href.indexOf('#tab_')) {
                    return;
                }
            }
            this.deactivate();
            this.activate(this.getTabByHref(href));
        }.bind(this));

        if (!this.activeTabs.length) {
            var first = this.container.down(this.config.tabs);
            if ('undefined' !== typeof first) {
                this.activate(this.getTabByHref(first.href || first.readAttribute('data-href')));
            }
        }

        this.container.select(this.config.tabs).each(function(el ,i) {
            el.observe('click', this.onclick.bind(this, el));

            var id = $(el).getAttribute('data-href');
            if (!id) {
                return;
            }
            $$(id + '_contents .pages a').each(function(_el){
                if (-1 == _el.href.indexOf("#")
                    && -1 !== _el.href.indexOf(window.location.host)) {

                    _el.href = _el.href + id;
                }
            });
        }.bind(this));
    },

    /**
     * @param {String} tab      Tab to activate
     * @param {Boolean} scroll  lag to indicate that page should be scrolled to the tab
     * @return {String|false}   Activated tab of false if tab wasn't found
     */
    activate: function(tab, scroll, animate) {
        var content = this.container
            .down('#' + this.tpl.content.replace(this.tpl.tab, tab));
        if (!content) {
            return false;
        }

        document.fire('easytabs:beforeActivate', {
            'tab'     : tab,
            'content' : content,
            'easytabs': this
        });

        this._updateCounter(tab);
        content.addClassName('active');
        content.show();

        if (-1 === this.activeTabs.indexOf(tab)) {
            this.activeTabs.push(tab);
        }

        var href = this.tpl.href.replace(this.tpl.tab, tab),
            tabs = this.container.select(
                this.config.tabs + '[href="' + href + '"]',
                this.config.tabs + '[data-href="' + href + '"]'
            );

        tabs.each(function(a) {
            a.addClassName('active');
            var parentLi = a.up('li');
            parentLi && parentLi.addClassName('active');
        });

        if (scroll) {
            var visibleTab = tabs.detect(function(el) {
                return el.getStyle('display') !== 'none';
            });
            if (visibleTab) {
                Effect.ScrollTo(visibleTab, {
                    duration: animate ? this.config.scrollSpeed : 0,
                    offset: this.config.scrollOffset
                });
            }
        }

        document.fire('easytabs:afterActivate', {
            'tab'     : tab,
            'content' : content,
            'easytabs': this
        });

        return tab;
    },

    /**
     * @param {String} tab      Tab to deactivate
     * @return {String|false}   Last deactivated tab or false if tab not found
     */
    deactivate: function(tab) {
        if (!tab) {
            while (this.activeTabs.length) {
                this.deactivate(this.activeTabs[0]);
            }
            return tab;
        }

        var index = this.activeTabs.indexOf(tab);
        if (index > -1) {
            this.activeTabs.splice(index, 1);
        }

        var content = this.container
            .down('#' + this.tpl.content.replace(this.tpl.tab, tab));
        if (!content) {
            return false;
        }

        document.fire('easytabs:beforeDeactivate', {
            'tab'     : tab,
            'content' : content,
            'easytabs': this
        });

        content.removeClassName('active');
        content.hide();

        var href = this.tpl.href.replace(this.tpl.tab, tab),
            tabs = this.container.select(
                this.config.tabs + '[href="' + href + '"]',
                this.config.tabs + '[data-href="' + href + '"]'
            );

        tabs.each(function(a) {
            a.removeClassName('active');
            var parentLi = a.up('li');
            parentLi && parentLi.removeClassName('active');
        });

        document.fire('easytabs:afterDeactivate', {
            'tab'     : tab,
            'content' : content,
            'easytabs': this
        });

        return tab;
    },

    /**
     * @param {Object} el       Element
     * @param {Object} e        Event
     * @param {String} tab      Tab to activate
     * @param {Boolean} scroll  Flag to indicate that page should be scrolled to the tab
     */
    onclick: function(el, e, tab, scroll, animate) {
        var isAccordion = false,
            accordionTrigger = $$('.easytabs-a-accordion').first();
        if (accordionTrigger) {
            // accordion tabs are hidden for desktop
            isAccordion = (accordionTrigger.getStyle('display') !== 'none');
        }

        tab    = tab || this.getTabByHref(el.href || el.readAttribute('data-href'));
        scroll = scroll || el.hasClassName('easytabs-scroll');
        animate = animate || el.hasClassName('easytabs-animate');
        if (isAccordion) {
            if (el.hasClassName('active')) {
                this.deactivate(tab);
            } else {
                this.activate(tab, scroll, animate);
            }
        } else {
            this.deactivate();
            this.activate(tab, scroll, animate);
        }
    },

    /**
     * Retrieve tab name from the url
     *
     * @param {String} href
     */
    getTabByHref: function(href) {
        var tab = href.match(this.tpl.href + '$');
        if (!tab) {
            return false;
        }
        return tab[1];
    },

    /**
     * Update activation counter
     *
     * @param  {String} tab
     * @return void
     */
    _updateCounter: function(tab) {
        if (!this.counters[tab]) {
            this.counters[tab] = 0;
        }
        this.counters[tab]++;
    },

    /**
     * Retreive activation count for specified tab
     *
     * @param  {String} tab
     * @return {Integer}
     */
    getActivationCount: function(tab) {
        if (!this.counters[tab]) {
            this.counters[tab] = 0;
        }
        return this.counters[tab];
    }
};

document.observe('dom:loaded', function(){
    window.easytabs = [];
    $$('.easytabs-wrapper').each(function (container){
        window.easytabs.push(new EasyTabs(container));
    })
});

document.observe('dom:loaded', ('getComputedStyle' in window) ? function() {

    var sliders = $$('.swiper-container');
    if (sliders.length > 0) {
        // initialize sliders
        sliders.each(function(el){
            // get slider config
            var config = JSON.parse(el.readAttribute('data-config'));

            config.onInit = function(swiper){
                // observe click on slides
                swiper.container[0].select('.easyslide-link').each(function(el){
                    el.observe('click', function(e) {
                        if (this.hasClassName('target-self'))
                            return true;
                        e.stop();
                        var options = '';
                        if (this.hasClassName('target-popup'))
                            options = 'width=600,height=400';
                        window.open(this.href, this.up().id, options);
                    });
                });
                // observers to listen mouse hover
                if (swiper.params.stopOnHover) {
                    swiper.container[0].observe('mouseenter', function(){
                        this.swiper.stopAutoplay()
                    });
                    swiper.container[0].observe('mouseleave', function(){
                        this.swiper.startAutoplay()
                    });
                }
                if (swiper.params.lazyLoading) {
                    swiper.container[0].resizeSlider = function(){
                        var layout = new Element.Layout(this.down('.swiper-slide-active'));
                        var padding = layout.get('padding-bottom') + layout.get('padding-top');
                        var w = this.swiper.params.sliderSizeWidth;
                        var h = this.swiper.params.sliderSizeHeight;
                        if (!h || !w) return;
                        var newHeight = this.offsetWidth * (h - padding) / w + padding;
                        this.setStyle({maxHeight: Math.round(newHeight) + 'px'});
                    };
                    swiper.container[0].resizeSlider();
                }
            };

            new Swiper(el, config);
        });
        // observe window resize to resize slider
        window.easyslideResizeTimer = null;
        Event.observe(window, 'resize', function(){
            clearTimeout(window.easyslideResizeTimer);
            window.easyslideResizeTimer = setTimeout(function() {
                $$('.swiper-container').each(function (el){
                    if (typeof el.resizeSlider !== 'undefined')
                        el.resizeSlider();
                });
            }, 250);
        });
    }

} : null);

;(function (){

function getDataset(elem){
    if (typeof elem.dataset !== 'undefined') {
        return elem.dataset;
    }
    // fallback for zombie browsers - IE8
    var dataset = {};
    for (var i = 0; i < elem.attributes.length; i++) {
        if (elem.attributes[i].name.substring(0,5) == 'data-') {
            var key = elem.attributes[i].name.substring(5).camelize();
            dataset[key] = elem.attributes[i].value
        }
    }
    return dataset
}

function valueToType(value) {
    if (value == "true") { return true };
    if (value == "false") { return false };
    if (!isNaN(value)) { return parseFloat(value) };
    if (value.substring(0,1) == '{' && value.substring(value.length-1) == '}'){
        var obj = JSON.parse(value.split('\'').join('"'));
        for(var key in obj){
            obj[key] = valueToType(obj[key])
        }
        return obj
    }
    return value
}

function getUniqueId(prefix){
    var id=Math.floor((1+Math.random())*0x10000).toString(16).substring(1);
    return prefix+id;
}

var LightboxPro = Class.create();
LightboxPro.prototype = {
    initialize: function(){
        this.skipTextTranslation = [
            'cssDirection',
            'creditsText',
            'creditsTitle'
        ];
        this.selector = {
            config: '.lightbox-highslide-config',
            html: '.lightbox-html', // selector for Html Widget
            image: '.lightbox-single-image', // selector for Image Widget
            gallery: '.highslide-gallery', // selector for Gallery Widget
            slideshow: '.highslide-gallery .lightbox-main-image',
            thumbs: '.lightbox-image'
        };
        this.onClick = {
            image: 'return hs.expand('
                    + 'this, lightBox.getCustomConfigs(this.idHs)'
                    + ')',
            html:  'return hs.htmlExpand('
                    + 'this, lightBox.getCustomConfigs(this.idHs)'
                    + ')'
            };
        this.customConfigs = {}
    },

    getInstance: function(instanceId){
        if (typeof instanceId === 'undefined') { instanceId = 0 }
        return $$(this.selector.gallery,
                this.selector.html,
                this.selector.image
            )[instanceId]
    },


    loadCofig: function(){
        var elemWithConfig = $$(this.selector.config)[0];
        if (!elemWithConfig) {
            // HS configuration not found
            ['expandCursor', 'outlineType','restoreCursor'].
                map(function(v){hs[v]=null});
            return false;
        }
        //default HighSlide settings
        hs.allowWidthReduction = true;
        hs.showCredits = false;
        // settings from configuration
        var dataset = getDataset(elemWithConfig);
        for (var key in dataset) {
            hs[key] = valueToType(dataset[key]);
        }
        // prepare close button for popup
        if (hs.closeButtonEnabled) {
            hs.registerOverlay({
                html: '<div class="closebutton" onclick="return hs.close(this)" title="Close"></div>',
                position: 'top right',
                useOnHtml: false,
                fade: 2 // fading the semi-transparent overlay looks bad in IE
            });
        }
        // highslide texts (translations)
        for (var key in hs.lang) {
            if (this.skipTextTranslation.indexOf(key) == -1) {
                hs.lang[key] = Translator.translate(hs.lang[key]);
            }
        }
        return true
    },

    initGallery: function(){
        var self = this;
        $$(this.selector.slideshow).each(function(el){
            el.idHs = getUniqueId('gallery-');
            var galleryParent = el.up(self.selector.gallery);
            if (galleryParent.hasAttribute('data-thumnails')) {
                el.thumbnails = $$(galleryParent.readAttribute('data-thumnails'))
                    .each(function(th){
                        th.idHs = el.idHs;
                    });
            } else {
                el.thumbnails = galleryParent
                    .select(self.selector.thumbs)
                    .each(function(th){
                        th.idHs = el.idHs;
                    });
            }
            var dataset = getDataset(el);
            var slideShow = {},
                customConfigs = {};
            slideShow.slideshowGroup = el.idHs;
            for (var key in dataset) {
                if (key != 'customConfigs') {
                    slideShow[key] = valueToType(dataset[key]);
                } else {
                    customConfigs = valueToType(dataset.customConfigs);
                }
            }
            window.hs.addSlideshow(slideShow);
            customConfigs.slideshowGroup = el.idHs;
            customConfigs.transitions = ['expand', 'crossfade'];
            self.setCustomConfigs(el.idHs, customConfigs);
        });
        return this
    },

    initSingleElementWidgets: function () {
        var self = this;
        $$(this.selector.html, self.selector.image).each(function(el){
            el.elementHs = el.down('a');
            el.elementHs.idHs = getUniqueId('widget-');
            var dataset = getDataset(el.elementHs);
            var customConfigs = {};
            if (dataset.customConfigs) {
                customConfigs = valueToType(dataset.customConfigs);
            }
            customConfigs.slideshowGroup = el.elementHs.idHs;
            self.setCustomConfigs(el.elementHs.idHs, customConfigs);
        });
        return this
    },

    setCustomConfigs: function(key, config){
        this.customConfigs[key] = config
    },

    getCustomConfigs: function(key){
        return this.customConfigs[key]
    },

    registerOnClick: function(){
        var self = this;
        // register clicks for galleries
        $$(self.selector.slideshow).each(function(sshow){
            sshow.setAttribute('onclick', self.onClick.image);
            sshow.thumbnails.each(function(th){
                if ("mainImage" in th) { th.mainImage = false };
                th.stopObserving('click');
                th.removeAttribute('onclick');
                if (th.href == sshow.href) {
                    th.mainImage = sshow;
                }
                if (th.mainImage) {
                    th.observe('click', function(event){
                        event.stop();
                        this.mainImage.click();
                    })
                } else {
                    th.setAttribute('onclick', self.onClick.image)
                }
            });
        });
        // register clicks for single element widgets
        ['image','html'].map(function(v){
            $$(self.selector[v]).each(function(e){
                e.elementHs.setAttribute('onclick', self.onClick[v]);
            });
        });
        return this
    },

    integrateMageConfiguralSwatches: function(){
        // method implements integration LightboxPro and Magento ConfigSwatches
        // !! Theme should work with colorswatches without lightboxpro first
        if ('undefined' === typeof ProductMediaManager) {
            return this
        }
        ProductMediaManager.createZoom = ProductMediaManager.createZoom.wrap(
            function(original, image){
                original(image);
                var img = $j('.main-image img');
                // prevent image size increasing
                if (!img.hasClass('resized') && img.height()) {
                    img.addClass('resized').css({
                        'max-height': img.height()
                    });
                }
                $j('.main-image').attr('href', image.attr('src'));
                var srcset = img.attr('srcset'),
                    newSrc = image.attr('src');
                    img.attr('src', newSrc);
                if (srcset) {
                    if (image.attr('srcset')) {
                        img.attr('srcset', image.attr('srcset'));
                    } else {
                        img.removeAttr('srcset');
                    }
                }
                lightBox.registerOnClick()
            });
        ProductMediaManager.swapImage = ProductMediaManager.swapImage.wrap(
            function(original, targetImage){
                original(targetImage);
                var imageGallery = $j('.highslide-gallery');
                if (!targetImage[0].complete){
                    imageGallery.addClass('loading');
                    imagesLoaded(targetImage, function() {
                        imageGallery.removeClass('loading');
                    });
                }
            });
        return this
    }

}

window.lightBox = new LightboxPro();

})();

document.observe("dom:loaded", function () {
    // LightboxPro initialization
    if (lightBox.loadCofig()) {

        lightBox.initGallery().initSingleElementWidgets().registerOnClick().
            integrateMageConfiguralSwatches();

        // workaround for jumping thumbnails in firefox
        if(Prototype.Browser.Gecko) {
            hs.Expander.prototype.positionOverlay =
                hs.Expander.prototype.positionOverlay.wrap(
                function (callOriginal, overlay) {
                    if (overlay.hsId == 'thumbstrip') {
                        setTimeout(function(callFunction, node){
                            callFunction(node);
                            exp = hs.getExpander(node);
                            exp.slideshow.thumbstrip.selectThumb();
                        }, 13, callOriginal, overlay);
                    } else {
                        callOriginal(overlay);
                    }
                }
            )
        };

    };
});

/**
 * Templates Master http://templates-master.com
 *
 * Modified menu script.
 * - Dropdown autoalignment
 * - Check for link inside el, before operating
 * - Dropdown width calculating
 *
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE_AFL.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Varien
 * @package     js
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

/**
 * @classDescription simple Navigation with replacing old handlers
 * @param {String} id id of ul element with navigation lists
 * @param {Object} settings object with settings
 */
var navPro = function() {

    var main = {
        obj_nav :   $(arguments[0]) || $("nav"),

        settings :  {
            show_delay   : 0,
            hide_delay   : 0,
            _ie6         : /MSIE 6.+Win/.test(navigator.userAgent),
            _ie7         : /MSIE 7.+Win/.test(navigator.userAgent),
            constraint_el: $$('.main')[0],
            dropdown_side: 'right',
            fit_width    : true,
            accordion_width: 768 // @see navigationpro.css @media (max-width: 768px) {...
        },

        fitColumnWidth: function() {
            // find next levels without dropdown wrapper
            var columns = this.obj_nav.select('.nav-li-column > .nav-column-wrapper > .nav-column').reverse();
            columns.each(function(column) {
                var parentLi,       parentUl,       oldLiWidth,
                    newLiWidth,     oldUlWidth,     newUlWidth,
                    dimension,      ulSiblings,     dropdown,
                    oldDropdownWidth, newDropdowWidth;

                do {
                    parentLi = column.up('.nav-li-column');
                    parentUl = column.up('.nav-column');

                    if (!parentLi) {
                        break;
                    }

                    oldLiWidth = parseInt(parentLi.getStyle('width'));
                    newLiWidth = parseInt(column.getStyle('width'));
                    dimension  = column.getStyle('width').replace(newLiWidth, '');

                    ulSiblings = column.siblings();
                    ulSiblings.each(function(ulSibling) {
                        newLiWidth += parseInt(ulSibling.getStyle('width'));
                    });

                    if (oldLiWidth >= newLiWidth) {
                        break;
                    }

                    parentLi.setStyle({
                        width: newLiWidth + dimension
                    });

                    oldUlWidth = parseInt(parentUl.getStyle('width'));
                    newUlWidth = oldUlWidth + (newLiWidth - oldLiWidth);
                    parentUl.setStyle({
                        width: newUlWidth + dimension
                    });

                    dropdown = parentUl.up('.nav-dropdown');
                    oldDropdownWidth = parseInt(dropdown.getStyle('width'));
                    newDropdowWidth = oldDropdownWidth + (newUlWidth - oldUlWidth);
                    dropdown.setStyle({
                        width: newDropdowWidth + dimension
                    });

                    column = parentUl;
                } while (true);
            });
        },

        init :  function(obj, level) {
            obj.lists = obj.childElements();
            obj.lists.each(function(el,ind){
                main.handlNavElement(el);
                if((main.settings._ie6 || main.settings._ie7) && level){
                    main.ieFixZIndex(el, ind, obj.lists.size());
                }
            });
            if(main.settings._ie6 && !level){
                document.execCommand("BackgroundImageCache", false, true);
            }
        },

        handlNavElement: function(list) {
            if (!list) {
                return;
            }

            var self = this;
            var toggler = list.down('.nav-toggler');
            list.hover(
                function() {
                    main.toggleOverClass(list, true);
                    if (!list.hasClassName('nav-style-dropdown')) {
                        return;
                    }
                    if (!self.showOnHover()) {
                        return;
                    }
                    main.fireNavEvent(list, true);
                },
                function() {
                    main.toggleOverClass(list, false);
                    if (!list.hasClassName('nav-style-dropdown')) {
                        return;
                    }
                    if (!self.showOnHover()) {
                        return;
                    }
                    main.fireNavEvent(list, false);
                }
            );
            if (toggler) {
                toggler.observe('click', function(e) {
                    if (!self.showOnClick()) {
                        return;
                    }
                    e.stop();
                    main.fireNavEvent(list);
                });
            }

            // tm modification start
            // our menu rendered in multiple rows,
            // so every row should be initialized
            var row = list.down('ul.nav-ul');
            if (row) {
                main.init(row, true);
                row.siblings().each(function(el) {
                    if (!el.hasClassName('nav-ul')) {
                        return;
                    }
                    main.init(el, true);
                });
            }
            // tm modification end
        },

        /**
         * Retrieve browser window width including scrollbar size.
         *
         * Viewport size is not meet our needs, because css media queries are
         * applied to window width, not to the body width, while accordion_width
         * is used in css file.
         *
         * @return {[type]} [description]
         */
        getWindowWidth: function() {
            return window.innerWidth || document.documentElement.clientWidth || document.body.clientWidth;
        },

        showOnHover: function() {
            if (!main.settings.is_responsive) {
                return true;
            }
            if (this.getWindowWidth() > main.settings.accordion_width) {
                return true;
            }
            return false;
        },

        showOnClick: function() {
            if (!main.settings.is_responsive) {
                return false;
            }
            if (this.getWindowWidth() <= main.settings.accordion_width) {
                return true;
            }
            return false;
        },

        ieFixZIndex : function(el, i, l) {
            if(el.tagName.toString().toLowerCase().indexOf("iframe") == -1){
                el.style.zIndex = l - i;
            } else {
                el.onmouseover = "null";
                el.onmouseout = "null";
            }
        },

        fireNavEvent :  function(el, flag) {
            var dropdown = el.childElements()[2];
            if (!dropdown) {
                return;
            }
            if (typeof flag === 'undefined') {
                flag = !el.hasClassName('opened');
            }
            var toggler = el.down('.nav-toggler');
            if (flag) {
                el.addClassName('opened');
                if (toggler && !toggler.hasClassName('nav-accordion-toggler')) {
                    toggler.addClassName('active');
                }
                main.show(dropdown);
            } else {
                el.removeClassName('opened');
                if (toggler && !toggler.hasClassName('nav-accordion-toggler')) {
                    toggler.removeClassName('active');
                }
                main.hide(dropdown);
            }
        },

        toggleOverClass: function(elm, state) {
            var a = elm.down('a'),
                method = state ? 'addClassName' : 'removeClassName';

            elm[method]('over');
            if (a) {
                a[method]('over');
            }
        },

        show : function (sub_elm) {
            if (sub_elm.hide_time_id) {
                clearTimeout(sub_elm.hide_time_id);
            }
            var self = this;
            sub_elm.show_time_id = setTimeout(function() {
                if (sub_elm.hasClassName("shown-sub")) {
                    return;
                }
                sub_elm.addClassName("shown-sub");

                // tm modification start
                // fix position of the dropdown to make it fully visible
                if (!main.settings.is_responsive ||
                    self.getWindowWidth() > main.settings.accordion_width) {

                    self.setDropdownPosition(sub_elm);
                }
                // tm modification end
            }, main.settings.show_delay);
        },

        hide : function (sub_elm) {
            if (sub_elm.show_time_id) {
                clearTimeout(sub_elm.show_time_id);
            }
            var self = this;
            sub_elm.hide_time_id = setTimeout(function(){
                if (sub_elm.hasClassName("shown-sub")) {
                    sub_elm.removeClassName("shown-sub");
                    sub_elm.setStyle({
                        left: '',
                        right: '',
                        top: ''
                    });
                    if (self.settings.afterHide) {
                        self.settings.afterHide(sub_elm);
                    }
                }
            }, main.settings.hide_delay);
        },

        setDropdownPosition: function(sub_elm)
        {
            var self         = this,
                parentLi     = sub_elm.up('li'),
                top          = 0,
                left         = 0,
                right        = 0,
                dropdownSide = self.settings.dropdown_side;

            if (!parentLi) {
                return;
            }

            if (parentLi.up().hasClassName('navpro-inline')) {
                top = parentLi.getHeight();// - 1;
            } else {
                top   = 10;
                left  = parentLi.getWidth() - 40;
                right = left;
            }
            sub_elm.setStyle({
                left: left + 'px',
                top : top + 'px'
            });
            if ('left' === dropdownSide) {
                sub_elm.setStyle({
                    right: right + 'px',
                    top  : top + 'px',
                    left : 'auto'
                });
            }

            var parentOffset   = parentLi.viewportOffset(),
                elSize         = sub_elm.getDimensions(),
                elOffset       = sub_elm.viewportOffset(),
                constraintSize   = self.settings.constraint_el.getDimensions(),
                constraintOffset = self.settings.constraint_el.viewportOffset(),
                viewportSize   = document.viewport.getDimensions(),
                viewportOffset = document.viewport.getScrollOffsets(),
                elRight        = elOffset.left + elSize.width,
                contraintRight = constraintOffset.left + constraintSize.width,
                limitRight     = Math.min(contraintRight, viewportSize.width),
                limitLeft      = Math.max(constraintOffset.left, viewportOffset.left);

            if ('right' === dropdownSide && elRight > limitRight) {
                left = left - (elRight - limitRight);
                if (left < 30 && !parentLi.up().hasClassName('navpro-inline')) {
                    // try to use alignment to the left
                    var elLeft = parentOffset.left + parentLi.getWidth() - elSize.width - right;
                    if (left < 10 && elLeft > 0) {
                        sub_elm.setStyle({
                            right: right + 'px',
                            left : 'auto'
                        });
                        return;
                    }
                    left = 40; // we should leave the gap to allow to move cursor down to the next li
                } else if (parentOffset.left < Math.abs(left)) {
                    left = -parentOffset.left;
                }
                sub_elm.setStyle({
                    left: left + 'px'
                });
            } else if (('left' === dropdownSide) && (elOffset.left < 0) &&
                ((elSize.width + elOffset.left) < (viewportSize.width - (parentOffset.left + left)))) {

                sub_elm.setStyle({
                    left: left + 'px'
                });
            }

            if (self.settings.afterSetDropdownPosition) {
                top = self.settings.afterSetDropdownPosition(sub_elm);
            }
        }
    };
    if (arguments[1]) {
        main.settings = Object.extend(main.settings, arguments[1]);
    }
    if (main.obj_nav) {
        main.init(main.obj_nav, false);
        if (main.settings.fit_width) {
            main.fitColumnWidth();
        }
    }
};

/**
 * Do not remove or edit this.
 * Modified by Templates-Master,
 * to provide support of multiple navigation levels
 * of one accordion instance.
 *
 * www.templates-master.com
 */
// accordion.js v2.0
//
// Copyright (c) 2007 stickmanlabs
// Author: Kevin P Miller | http://www.stickmanlabs.com
//
// Accordion is freely distributable under the terms of an MIT-style license.
//

var accordion = Class.create();
accordion.prototype = {
    currentAccordion: null,
    duration        : null,
    effects         : [],
    animating       : false,

    initialize: function(container, options)
    {
        this.options = Object.extend({
            resizeSpeed: 8,
            classNames: {
                toggle      : 'nav-accordion-toggler',
                toggleActive: 'nav-accordion-toggler-active'
            },
            defaultSize: {
                height: null,
                width : null
            },
            collapse: true,
            direction: 'vertical',
            onEvent  : 'click'
        }, options || {});

        this.duration = ((11-this.options.resizeSpeed)*0.15);

        var accordions = $$('#'+container+' .'+this.options.classNames.toggle);
        accordions.each(function(accordion) {
            Event.observe(accordion, this.options.onEvent, this.activate.bind(this, accordion), false);
            if (this.options.onEvent == 'click') {
                accordion.onclick = function() {return false;};
            }

            var accordion_options;
            if (this.options.direction == 'horizontal') {
                accordion_options = $H({width: '0px'});
            } else {
                accordion_options = $H({height: '0px'});
            }

            this.currentAccordion = $(accordion.next(0)).setStyle(accordion_options.toJSON());
        }.bind(this));

        this.showActive(container);
    },

    showActive: function(container)
    {
        var self = this;
        $$('#' + container + ' .nav-style-accordion.active > .nav-dropdown').each(function(el) {
            el.previous(0).addClassName(self.options.classNames.toggleActive);
            el.setStyle({ height: 'auto' });
        });
    },

    activate: function(accordion)
    {
        if (this.animating) {
            return false;
        }

        this.effects = [];

        if (this.options.direction == 'horizontal') {
            this.scaleX = true;
            this.scaleY = false;
        } else {
            this.scaleX = false;
            this.scaleY = true;
        }

        this.currentAccordion = $(accordion.next(0));

        if (accordion.hasClassName(this.options.classNames.toggleActive)) {
            this.deactivate();
        } else {
            this._handleAccordion();
        }
    },

    deactivate: function()
    {
        this.currentAccordion.previous(0).removeClassName(this.options.classNames.toggleActive);

        new Effect.Scale(this.currentAccordion, 0, {
            duration: this.duration,
            scaleContent: false,
            scaleX: this.scaleX,
            scaleY: this.scaleY,
            transition: Effect.Transitions.sinoidal,
            queue: {
                position: 'end',
                scope: 'accordionAnimation'
            },
            scaleMode: {
                originalHeight: this.options.defaultSize.height ? this.options.defaultSize.height : this.currentAccordion.scrollHeight,
                originalWidth: this.options.defaultSize.width ? this.options.defaultSize.width : this.currentAccordion.scrollWidth
            },
            afterFinish: function() {
                this.animating = false;
            }.bind(this)
        });
    },

    _handleAccordion: function()
    {
        this.effects.push(
            new Effect.Scale(this.currentAccordion, 100, {
                sync: true,
                scaleFrom: 0,
                scaleContent: false,
                scaleX: this.scaleX,
                scaleY: this.scaleY,
                transition: Effect.Transitions.sinoidal,
                scaleMode: {
                    originalHeight: this.options.defaultSize.height ? this.options.defaultSize.height : this.currentAccordion.scrollHeight,
                    originalWidth: this.options.defaultSize.width ? this.options.defaultSize.width : this.currentAccordion.scrollWidth
                }
            })
        );

        var opened = this._getOpened();
        if (opened && this.options.collapse) {
            opened.previous(0).removeClassName(this.options.classNames.toggleActive);
            this.effects.push(
                new Effect.Scale(opened, 0, {
                    sync: true,
                    scaleContent: false,
                    scaleX: this.scaleX,
                    scaleY: this.scaleY,
                    transition: Effect.Transitions.sinoidal
                })
            );
        }

        this.currentAccordion.previous(0).addClassName(this.options.classNames.toggleActive);
        new Effect.Parallel(this.effects, {
            duration: this.duration,
            queue: {
                position: 'end',
                scope: 'accordionAnimation'
            },
            beforeStart: function() {
                this.animating = true;
            }.bind(this),
            afterFinish: function() {
                this.currentAccordion.setStyle({ height: 'auto' });
                this.animating = false;
            }.bind(this)
        });
    },

    _getOpened: function()
    {
        var up = this.currentAccordion.up(),
            siblings = up.siblings(),
            opened   = false,
            self     = this,
            selector = '> .nav-accordion-toggler';

        if (up.hasClassName('nav-li-column') || up.hasClassName('nav-li-row')) {
            up.up('ul').siblings().each(function(el) {
                siblings = siblings.concat(el.select('> li'));
            });
        }

        siblings.each(function(el) {
            el.select(selector).each(function(toggler) {
                if (toggler.hasClassName(self.options.classNames.toggleActive)) {
                    opened = toggler.next(0);
                    throw $break;
                }
            });
            if (opened) {
                throw $break;
            }
        });

        return opened;
    }
};

// protoHover
// a simple hover implementation for prototype.js
// Sasha Sklar and David Still
(function() {
    // copied from jquery
    var withinElement = function(evt, el) {
        // Check if mouse(over|out) are still within the same parent element
        var parent = evt.relatedTarget;

        // Traverse up the tree
        while (parent && parent != el) {
            try {
                parent = parent.parentNode;
            } catch (error) {
                parent = el;
            }
        }
        // Return true if we actually just moused on to a sub-element
        return parent == el;
    };

    // Extend event with mouseEnter and mouseLeave
    Object.extend(Event, {
        mouseEnter: function(element, f, options) {
            element = $(element);

            // curry the delay into f
            var fc = (options && options.enterDelay)?(function(){window.setTimeout(f, options.enterDelay);}):(f);

            if (Prototype.Browser.IE) {
                element.observe('mouseenter', fc);
            } else {
                element.hovered = false;

                element.observe('mouseover', function(evt) {
                    // conditions to fire the mouseover
                    // mouseover is simple, the only change to default behavior is we don't want hover fireing multiple times on one element
                    if (!element.hovered) {
                        // set hovered to true
                        element.hovered = true;

                        // fire the mouseover function
                        fc(evt);
                    }
                });
            }
        },
        mouseLeave: function(element, f, options) {
            element = $(element);

            // curry the delay into f
            var fc = (options && options.leaveDelay)?(function(){window.setTimeout(f, options.leaveDelay);}):(f);

            if (Prototype.Browser.IE) {
                element.observe('mouseleave', fc);
            } else {
                element.observe('mouseout', function(evt) {
                    // get the element that fired the event
                    // use the old syntax to maintain compatibility w/ prototype 1.5x
                    var target = Event.element(evt);

                    // conditions to fire the mouseout
                    // if we leave the element we're observing
                    if (!withinElement(evt, element)) {
                        // fire the mouseover function
                        fc(evt);

                        // set hovered to false
                        element.hovered = false;
                    }
                });
            }
        }
    });


    // add method to Prototype extended element
    Element.addMethods({
        'hover': function(element, mouseEnterFunc, mouseLeaveFunc, options) {
            options = Object.extend({}, options) || {};
            Event.mouseEnter(element, mouseEnterFunc, options);
            Event.mouseLeave(element, mouseLeaveFunc, options);
        }
    });
})();

document.observe("dom:loaded", function() {
    $$('ul.navpro').each(function (menu){
        var config = JSON.parse(menu.readAttribute('data-config'));
        if (!config) {
            console.warn('Fail to initialize NavPro menu "' + menu.id + '"');
            return;
        }
        config.constraint_el = eval(config.constraint_el);
        navPro(menu.id, config);
        new accordion(menu.id);
    });
});

var ProLabelsTooltip = Class.create();
ProLabelsTooltip.prototype = {
    initialize: function() {
        this.prepareMarkup();
        this.addObservers();
    },

    prepareMarkup: function() {
        this.addStylesToHead();
        var self = this,
            tooltips = $$('.prolabels-content-labels .tooltip-label');
        tooltips.each(function(tooltip){
            labelWidth = 60;
            tooltipMargin = '-' + labelWidth + 'px';
            tooltip.setStyle(self.styles);
            tooltip.setStyle({marginLeft: tooltipMargin});
            tooltip.setStyle({display: ''});
            tooltip.up('a.tt-gplus').removeAttribute('title');
        });
    },

    getConfig: function() {
        var config = false;
        $$('.prolabels-content-labels').each(function(element){
            var attrName = 'data-tooltip-config';
            if (element.hasAttribute(attrName)) {
                config = JSON.parse(element.getAttribute(attrName));
            }
            throw $break;
        });
        return config;
    },

    addObservers: function() {
        var self = this;
        ["quickshopping:previewloaded", "ajaxlayerednavigation:ready",
         "AjaxPro:onSuccess:after"].map(
            function(eventName){
                document.observe(eventName, self.prepareMarkup.bind(self));
            }
        );
    },

    addStylesToHead: function() {
        if ( (typeof this.styles === 'undefined') || !this.styles) {
            this.styles = this.getConfig();
            if (!this.styles) {
                return;
            }
        } else {
            return;
        }
        var tooltipMargin = '',
            labelWidth = '',
            aHoverCss = '.tt-wrapper li a:hover span.tooltip-label{background-color:'+this.styles.background+';}',
            spanAfterCss = '.tt-wrapper li a span.tooltip-label:after{border-top: 9px solid '+this.styles.background+';}',
            tooltipStyle = aHoverCss + spanAfterCss;
        var style = document.createElement('style');
        style.setAttribute('type', 'text/css');
        if (style.styleSheet) {
            style.styleSheet.cssText = tooltipStyle;
        } else {
            style.appendChild(document.createTextNode(tooltipStyle));
        }
        document.getElementsByTagName('head')[0].appendChild(style);
    }
};

document.observe("dom:loaded", function(){
    window.prolabelsTooltip = new ProLabelsTooltip();
});

document.observe("dom:loaded", function() {
    if (typeof jQuery === 'undefined') {
        console.warn('Slick Carousel can not find jQuery');
        return;
    }
    // init slick
    jQuery('div[data-slick]').slick();
    // init slick wrapper
    jQuery.each(jQuery('div[data-slick-wrapper]'), function() {
        var wrapper = jQuery(this);
        var config = wrapper.data('slick-wrapper');
        if (config.el) {
            wrapper.find(config.el).slick(config);
        }
    });
});

var SoldTogether = {};
SoldTogether.Amazon = Class.create();
SoldTogether.Amazon.prototype = {
    initialize: function(options) {
        this.config = Object.extend({
            checkboxes            : '.relatedorderamazon-checkbox',
            checkboxIdPattern     : '#relatedorderamazon-checkbox{{id}}',
            priceSelector         : '#relatedorderamazon-hidden{{id}}',
            priceSelectorInc      : '#relatedorderamazon-hidden-inc{{id}}',
            mainProductPrice      : '.soldtogether-amazon-main',
            mainProductPriceInc   : '.soldtogether-amazon-main-inc',
            totalPriceSelector    : '.amazonstyle-checkboxes .totalprice .price',
            totalPriceSelectorInc : '.amazonstyle-checkboxes .totalprice .price-including-tax .price',
            totalPriceSelectorExc : '.amazonstyle-checkboxes .totalprice .price-excluding-tax .price',
            submitEl              : '.block-soldtogether-order .btn-cart'
        }, options || {});

        if ($$('.product-shop .price-box .price-excluding-tax')[0]
            && $$('.product-shop .price-box .price-including-tax')[0]) {

            $('soldtogether-total-price-inc').show();
        } else {
            $('soldtogether-total-price').show();
        }

        this.checkboxes = $$(this.config.checkboxes);
        this.mainProductPrice = $$(this.config.mainProductPrice).first();
        this.mainProductPriceInc = $$(this.config.mainProductPriceInc).first();

        $$(this.config.submitEl).invoke('observe', 'click', this.submit.bind(this));
        this.checkboxes.invoke('observe', 'click', this.onCheckboxClick.bind(this));
        this.checkboxes.each(function(el) {
            this.syncCheckboxState(el);
        }.bind(this));

        this.addProductPriceChangeListener();
    },

    onCheckboxClick: function(e) {
        this.syncCheckboxState(e.element());
    },

    /**
     * Sync total and images with browser cached state of checkboxes
     */
    syncCheckboxState: function(el) {
        if (el.checked) {
            this.activate(el.value);
        } else {
            this.deactivate(el.value);
        }
    },

    activate: function(id) {
        this.getCheckbox(id).checked = true;
        $('image' + id).appear({
            duration: 0.2
        });
        this.updateTotal();
    },

    deactivate: function(id) {
        this.getCheckbox(id).checked = false;
        $('image' + id).fade({
            duration: 0.2
        });
        this.updateTotal();
    },

    getCheckbox: function(id) {
        return $$(this.config.checkboxIdPattern.replace('{{id}}', id)).first();
    },

    parseLocalizedFloat: function(value) {
        if (typeof optionsPrice !== 'undefined' && optionsPrice.priceFormat) {
            var groupSymbol   = optionsPrice.priceFormat.groupSymbol,
                decimalSymbol = optionsPrice.priceFormat.decimalSymbol;
        } else {
            var groupSymbol   = this.config.priceFormat.groupSymbol,
                decimalSymbol = this.config.priceFormat.decimalSymbol;
        }
        value = value.replace(/\s/g, '');
        value = value.replace(/&nbsp;/g, '');
        value = value.replace(new RegExp('\\' + groupSymbol, 'g'), '');
        value = value.replace(decimalSymbol, '.');
        value = value.match(/\d.+/)[0];
        return parseFloat(value);
    },

    updateTotal: function() {
        var total = parseFloat(this.mainProductPrice.getValue()),
            totalInc = parseFloat(this.mainProductPriceInc.getValue()),
            formattedPrice = '',
            formattedPriceInc = '',
            priceSelector  = this.config.priceSelector,
            priceSelectorInc  = this.config.priceSelectorInc,
            currentPriceSelector = '',
            self = this,
            currentPriceSelectorInc = '';

        this.checkboxes.each(function(el) {
            if (!el.checked) {
                return;
            }
            currentPriceSelector = priceSelector.replace('{{id}}', el.value);
            currentPriceSelectorInc = priceSelectorInc.replace('{{id}}', el.value);
            totalInc += parseFloat($$(currentPriceSelectorInc).first().getValue());

            total += parseFloat($$(currentPriceSelector).first().getValue());
        });

        if (typeof optionsPrice !== 'undefined' && optionsPrice.priceFormat) {
            formattedPrice = optionsPrice.formatPrice(total);
            formattedPriceInc = optionsPrice.formatPrice(totalInc);
        } else {
            formattedPrice = formatCurrency(total, this.config.priceFormat);
            formattedPriceInc = formatCurrency(totalInc, this.config.priceFormat);
        }

        $$(this.config.totalPriceSelectorInc).first().update(formattedPriceInc);
        $$(this.config.totalPriceSelectorExc).first().update(formattedPrice);
        $$(this.config.totalPriceSelector).first().update(formattedPrice);
    },

    submit: function() {
        if (!productAddToCartForm.validator.validate()) {
            return;
        }

        var values = [],
            hiddenProductsField = $('related-products-field');
        this.checkboxes.each(function(el) {
            if (!el.checked) {
                return;
            }
            values.push(el.value);
        });
        values = values.uniq();
        if (hiddenProductsField) {
            // reset related selected checkboxes?
            hiddenProductsField.value = values.join(',');
        }
        productAddToCartForm.submit();
    },

    /**
     * Update main price and amazon total, when options are changes
     * on bundle, configurable and grouped products
     */
    addProductPriceChangeListener: function() {
        if (optionsPrice !== 'undefined' && optionsPrice.priceFormat && optionsPrice.reload) {
            var finalPriceEls = $$('.product-options-bottom .price');
            if (!finalPriceEls.length) {
                finalPriceEls = $$('.product-shop .price-box .price');
            }
            optionsPrice.reload = optionsPrice.reload.wrap(
                function(original) {
                    original();
                    syncPrice();
                }
            );
        } else if ($('super-product-table')) {
            var groupedTable = $('super-product-table'),
                finalPriceEls = groupedTable.select('.price-box .price');

            groupedTable.select('.qty').each(function(el) {
                el.observe('change', function() {
                    syncPrice();
                });
            });
        } else {
            return;
        }

        var self = this,
            syncPrice = function() {
                var total = {
                    'default'  : 0,
                    'including': 0
                };
                finalPriceEls.each(function(priceEl) {
                    var price = self.parseLocalizedFloat(priceEl.innerHTML);
                    if (isNaN(price)) {
                        return;
                    }
                    var id = priceEl.id || priceEl.up().id;
                    if (0 === id.indexOf('price-including-tax-')) {
                        var key = 'including';
                    } else if (0 === id.indexOf('price-excluding-tax-')
                        || 0 === id.indexOf('product-price-')) {

                        var key = 'default';
                    } else {
                        // old-price goes here
                        return;
                    }

                    id = id.match(/-(\d+)/);
                    id = id[1];
                    if ($('super-product-table')) {
                        var qtyName = 'super_group[' + id + ']';
                        var input = $$('input[name="'+qtyName+'"]').first();
                        var qty = input ? parseFloat(input.getValue()) : 0;
                        qty = (qty < 0) ? 1 : qty;
                    } else {
                        qty = 1;
                    }
                    total[key] += (price * qty);
                });
                self.mainProductPrice.setValue(total['default']);
                self.mainProductPriceInc.setValue(total['including']);
                self.updateTotal();
            };
        syncPrice();
    }
};

SoldTogether.Checkboxes = Class.create();
SoldTogether.Checkboxes.prototype = {
    initialize: function(options) {
        this.config = Object.extend({
            container        : '.block-soldtogether-customer',
            checkboxIdPattern: '#relatedcustomer-checkbox{{id}}',
            checkboxes       : '.addtocart-checkbox',
            selectAll        : '.select-all',
            selectAllText    : 'select all',
            unselectAllText  : 'unselect all'
        }, options || {});

        this.container  = $$(this.config.container).first();
        this.checkboxes = this.container.select(this.config.checkboxes);
        this.selectAll  = this.container.select(this.config.selectAll);

        this.selectAll.invoke('observe', 'click', this.toggleAll.bind(this));
        this.checkboxes.invoke('observe', 'click', this.onCheckboxClick.bind(this));
    },

    onCheckboxClick: function(e) {
        this.syncCheckboxState(e.element());
    },

    /**
     * Sync total and images with browser cached state of checkboxes
     */
    syncCheckboxState: function(el) {
        if (el.checked) {
            this.activate(el.value);
        } else {
            this.deactivate(el.value);
        }
    },

    activate: function(id) {
        this.getCheckbox(id).checked = true;
        this.updateSelectAllLabel();
        addRelatedToProduct();
    },

    deactivate: function(id) {
        this.getCheckbox(id).checked = false;
        this.updateSelectAllLabel();
        addRelatedToProduct();
    },

    getCheckbox: function(id) {
        return $$(this.config.checkboxIdPattern.replace('{{id}}', id)).first();
    },

    toggleAll: function() {
        var status = this.hasNotCheckedCheckboxes();
        this.checkboxes.each(function(el) {
            el.checked = status;
            this.syncCheckboxState(el);
        }.bind(this));
    },

    hasNotCheckedCheckboxes: function() {
        var status = false;
        this.checkboxes.each(function(el) {
            if (!el.checked) {
                status = true;
                throw $break;
            }
        });
        return status;
    },

    updateSelectAllLabel: function() {
        var text = this.hasNotCheckedCheckboxes() ?
            this.config.selectAllText : this.config.unselectAllText;

        this.selectAll.each(function(el) {
            el.innerHTML = text;
        });
    }
};

// override addRelatedToProduct because we use the same hidden field
document.observe('dom:loaded', function() {
    addRelatedToProduct = function() {
        var checkboxes = $$('.related-checkbox, .addtocart-checkbox'),
            values = [];

        for (var i = 0; i < checkboxes.length; i++) {
            if (checkboxes[i].checked) {
                values.push(checkboxes[i].value);
            }
        }

        values = values.uniq();
        if ($('related-products-field')) {
            $('related-products-field').value = values.join(',');
        }
    };
    addRelatedToProduct();
});

;(function (exports){
    // testimonials widget list
    var widgetContentSelector = '.block-testimonials .content .content-wrapper',
        config = {},
        itemPrefix = 'testimonial_',
        curTestimonial = 0,
        showMoreActive = false,
        contentHeight,
        changeInterval;

    function showMore(e) {
        e.stop();
        showMoreActive = true;
        this.hide();
        this.up().down('.read-less').show();
        this.up().down('.content-wrapper').setStyle({height: 'auto'});
    }

    function showLess(e) {
        e.stop();
        showMoreActive = false;
        this.hide();
        this.up().down('.read-more').show();
        this.up().down('.content-wrapper').setStyle({height: contentHeight});
    }

    function startChangeTimer() {
        if (!showMoreActive) {
            changeInterval = setInterval(nextTestimonial, config.viewTime);
        }
    }

    function nextTestimonial() {
        if (config.numTestimonials < 2) {
            return;
        }
        if ($(itemPrefix + '0').down('.read-more')) {
            $(itemPrefix + curTestimonial).down('.read-more').stopObserving();
            $(itemPrefix + curTestimonial).down('.read-less').stopObserving();
        }
        Effect.Fade(itemPrefix + curTestimonial, {
            duration: config.animDuration / 1000
        });

        ++curTestimonial;
        if (curTestimonial >= config.numTestimonials) {
            curTestimonial = 0;
        }

        setTimeout(function() {
            Effect.Appear(itemPrefix + curTestimonial, {
                duration: config.animDuration / 1000
            });
            if ($(itemPrefix + '0').down('.read-more')) {
                var elem = $(itemPrefix + curTestimonial);
                elem.down('.read-more').observe('click', showMore);
                elem.down('.read-less').observe('click', showLess);
            }
        }, config.animDuration);
    }

    var WidgetList = Class.create();
    WidgetList.prototype = {
        initialize: function (element) {
            if (!element) {
                return;
            }
            contentHeight = $$(widgetContentSelector)[0].getStyle('height');
            config = JSON.parse(element.readAttribute('data-widget-config'));
            // set min height on testimonial container so it does not jump
            var testimonialContainer = element.down('.testimonial-container');
            if (testimonialContainer) {
                testimonialContainer.setStyle({
                    minHeight: testimonialContainer.getHeight()+'px'
                });
            }
            element.observe('mouseenter', function() {
                if (!showMoreActive) clearInterval(changeInterval);
            });
            element.observe('mouseleave', startChangeTimer);
            var elem = $(itemPrefix + '0');
            elem.down('.read-more').observe('click', showMore);
            elem.down('.read-less').observe('click', showLess);
            startChangeTimer();
        },

        next: function() {
            nextTestimonial();
        }
    }

    // testimonials - post new testimonail form
    var TestimonialForm = Class.create(VarienForm, {

        initialize: function($super, formId, firstFieldFocus){
            $super(formId, firstFieldFocus);
            if (this.form) {
                this.initRatingStars();
            }
        },

        initRatingStars: function(){
            var ratingRadiosSelector = '.testimonialForm .ratings-table label',
                ratingBox = $('testimonial-form-rating-box');
            if (!ratingBox) {
                return;
            }
            $$(ratingRadiosSelector).each(function (el){
               el.setStyle({'display': 'none'});
            });
            // show stars instead of radiobuttons
            ratingBox.setStyle({'display': ''});
            // listen star click on testimonial form
            ratingBox.observe('click', function(event) {
                var xPosInDiv = event.pointerX() - this.cumulativeOffset().left;
                var starWidth = this.getWidth() / 5;
                var n = Math.floor( xPosInDiv / starWidth ) + 1;
                $('rating_' + n).checked = 'checked';
                $('testimonial-form-rating').setStyle({'width' : (n*20) + '%'});
            });
        }

    });

    // testimonails page
    var Testimonials = Class.create();
    Testimonials.prototype = {
        initialize: function(divToUpdate) {
            if (!divToUpdate) {
                return;
            }
            this.url = divToUpdate.readAttribute('data-ajax-url');
            this.div = divToUpdate;
            this.currentPage = 1;
        },

        makeAjaxCall: function(event) {
            event.stop();
            if ($$('.more-button button')[0].hasClassName('disabled')) return;
            $$('.more-button button')[0].addClassName('disabled');
            ++this.currentPage;
            new Ajax.Request(this.url + 'page/' + this.currentPage, {
                onSuccess: function(transport) {
                    var response = transport.responseText.evalJSON();
                    this.div.insert(response.outputHtml);
                    $$('.more-button button')[0].removeClassName('disabled');
                }.bind(this)
            });
        }
    };

    var testimonialObject = {};
    testimonialObject.widgetList = new WidgetList();
    testimonialObject.form = new TestimonialForm();
    testimonialObject.list = new Testimonials();

    exports.testimonial = testimonialObject;

})(this);

document.observe('dom:loaded', function() {
    testimonial.form.initialize('testimonialForm', true);
    testimonial.widgetList.initialize($('testimonialsList'));
    var listContainer = $$('.testimonials-list .testimonials');
    if (!listContainer.length) { return; }
    testimonial.list.initialize(listContainer[0]);
    $('testimonials-view-more-button').observe(
        'click',
        testimonial.list.makeAjaxCall.bind(testimonial.list)
    );
});

