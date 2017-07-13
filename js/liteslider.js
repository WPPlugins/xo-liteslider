/**
 * jQuery Liteslider v1.3.0
 * Copyright 2015 ishitaka
 */
;
jQuery(function($) {
  $.fn.liteslider = function(options) {
    var defaultOptions = {
      effect: 'slide',
      navigation: true,
      pagination: true,
      slideshow: true,
      slideshowSpeed: 5000,
      animationSpeed: 1000,
      navigationPrev: '&lt;',
      navigationNext: '&gt;',
      start: function(){},
      before: function(){},
      after: function(){}
    };
    var target = $(this);
    var options = $.extend({}, defaultOptions, options);
    var container = target.find('.slides-container');
    var slideCount = target.find('.slides > li').length;
    var slides = target.find('.slides');
    var pagination;
    var slideNo = 1;
    var slideWidth = 0;
    var slidePos = 0;
    var isSlideAction = false;
    var touchStartX;
    var touchMoveX = 0;

    function init() {
      if (options.effect === 'slide') {
        /* 最後のスライドから最初のスライドへアニメーションさせるため、最後に最初のスライドを追加 */
        slides.append(slides.find('> li:first-child').clone(true));
      } else {
        slides.find('> li:not(:first-child)').css({position:"absolute", left:"0", top:"0", opacity:"0"});
      }

      if (options.navigation) {
        target.append('<div class="slide-nav-prev">'+options.navigationPrev+'</div><div class="slide-nav-next">'+options.navigationNext+'</div>');
        $(".slide-nav-prev").click(function() {
          isSlideAction = true;
          if (options.effect === 'slide') {
            slidePrev();
          } else if (options.effect === 'fade') {
            fade(slideNo === 1 ? slideCount : slideNo - 1);
          } else {
            slideFade(slideNo === 1 ? slideCount : slideNo - 1);
          }
        });
        $(".slide-nav-next").click(function() {
          isSlideAction = true;
          if (options.effect === 'slide') {
            slideNext();
          } else if (options.effect === 'fade') {
            fade(slideNo >= slideCount ? 1 : slideNo + 1);
          } else {
            slideFade(slideNo >= slideCount ? 1 : slideNo + 1);
          }
        });
      }

      if (options.pagination) {
        var paginationHtml = '<ol class="slide-pagination">';
        for (var i = 0; i < slideCount; i++) {
          paginationHtml += '<li></li>';
        }
        paginationHtml += '</ol>';
        target.append(paginationHtml);
        pagination = target.find('.slide-pagination li');
        pagination.click(function() {
          isSlideAction = true;
          var index = pagination.index(this);
          if (options.effect === 'slide') {
            slide(index + 1);
          } else if (options.effect === 'fade') {
            fade(index + 1);
          } else {
            slideFade(index + 1);
          }
        });
        pagination.eq(slideNo - 1).addClass('slide-active');
      }

      slides.bind('touchstart', function () {
        touchStartX = event.changedTouches[0].pageX;
      });
      slides.bind('touchmove', function (e) {
        touchEndX = event.changedTouches[0].pageX;
        touchMoveX = Math.round(touchStartX - touchEndX);
      });
      slides.bind('touchend', function (e) {
        if (touchMoveX > 50) {
          isSlideAction = true;
          if (options.effect === 'slide') {
            slideNext();
          } else if (options.effect === 'fade') {
            fade(slideNo >= slideCount ? 1 : slideNo + 1);
          } else {
            slideFade(slideNo >= slideCount ? 1 : slideNo + 1);
          }
        } else if (touchMoveX < -50) {
          isSlideAction = true;
          if (options.effect === 'slide') {
            slidePrev();
          } else if (options.effect === 'fade') {
            fade(slideNo === 1 ? slideCount : slideNo - 1);
          } else {
            slideFade(slideNo === 1 ? slideCount : slideNo - 1);
          }
        };
      });

      resize();
      slides.find('> li').show();

      options.start();
    }

    function resize() {
      slideWidth = container.width();
      slides.find('li').css({"width": slideWidth + "px"});
      if (options.effect === 'slide') {
        slides.width((slideCount + 1) * slideWidth);
        slidePos = (slideNo - 1) * slideWidth;
        container.animate({scrollLeft: slidePos}, 1, "swing");
      } else {
        slides.width(slideWidth);
      }
    }

    function slideNext() {
      options.before();
      slidePos = ((slideNo - 1) * slideWidth);
      container.animate({scrollLeft: slidePos}, 1, "swing", function() {
        slideNo++;
        slidePos = ((slideNo - 1) * slideWidth);
        container.animate({scrollLeft: slidePos}, options.animationSpeed, "swing", function() {
          options.after();
        });
        if (slideNo > slideCount) {
          slideNo = 1;
        }
        if (options.pagination) {
          pagination.removeClass('slide-active');
          pagination.eq(slideNo - 1).addClass('slide-active');
        }
      });
    }

    function slidePrev() {
      options.before();
      if (slideNo === 1) {
        slideNo = slideCount + 1;
      }
      slidePos = ((slideNo - 1) * slideWidth);
      container.animate({scrollLeft: slidePos}, 1, "swing", function() {
        slideNo--;
        slidePos = ((slideNo - 1) * slideWidth);
        container.animate({scrollLeft: slidePos}, options.animationSpeed, "swing", function() {
          options.after();
        });
        if (options.pagination) {
          pagination.removeClass('slide-active');
          pagination.eq(slideNo - 1).addClass('slide-active');
        }
      });
    }

    function slide(no) {
      if (slideNo === no)
        return;
      options.before();
      slidePos = ((slideNo - 1) * slideWidth);
      container.animate({scrollLeft: slidePos}, 1, "swing", function() {
        slideNo = no;
        slidePos = ((slideNo - 1) * slideWidth);
        container.animate({scrollLeft: slidePos}, options.animationSpeed, "swing", function() {
          options.after();
        });
        if (options.pagination) {
          pagination.removeClass('slide-active');
          pagination.eq(slideNo - 1).addClass('slide-active');
        }
      });
    }

    function fade(no) {
      options.before();
      var slide = slides.find('> li');
      $(slide[slideNo - 1]).animate({opacity: '0'}, options.animationSpeed);
      $(slide[no - 1]).animate({opacity: '1'}, options.animationSpeed, function() {
        options.after();
      });
      slideNo = no;
      if (options.pagination) {
        pagination.removeClass('slide-active');
        pagination.eq(slideNo - 1).addClass('slide-active');
      }
    }

    function slideFade(no) {
      options.before();
      var slide = slides.find('> li');
      if (slideNo > no)
        $(slide[slideNo - 1]).animate({left: '64px', opacity: '0'}, options.animationSpeed);
      else
        $(slide[slideNo - 1]).animate({left: '-64px', opacity: '0'}, options.animationSpeed);
      $(slide[no - 1]).animate({left: '0', opacity: '0'}, 1);
      $(slide[no - 1]).animate({left: '0', opacity: '1'}, options.animationSpeed, function() {
        options.after();
      });
      slideNo = no;
      if (options.pagination) {
        pagination.removeClass('slide-active');
        pagination.eq(slideNo - 1).addClass('slide-active');
      }
    }

    if (options.slideshow) {
      setInterval(function() {
        if (!isSlideAction) {
          if (options.effect === 'slide') {
            slideNext();
          } else if (options.effect === 'fade') {
            fade(slideNo >= slideCount ? 1 : slideNo + 1);
          } else {
            slideFade(slideNo >= slideCount ? 1 : slideNo + 1);
          }
        } else {
          isSlideAction = false;
        }
      }, options.animationSpeed + options.slideshowSpeed);
    }

    $(window).resize(function() { resize(); });

    init();
  };
});
