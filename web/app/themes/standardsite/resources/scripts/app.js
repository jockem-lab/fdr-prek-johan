import domReady from '@roots/sage/client/dom-ready';
import { createApp } from 'vue';
import _ from "lodash";
import listings from './vue-components/listings.vue';
import vuevideo from './vue-components/video.vue';
import listingcard from './vue-components/listingcard.vue'
// import 'slick-carousel/slick/slick.min.js'; // removed - not available
import 'leaflet/dist/leaflet.js';
import leafletMarkerIcon from '@src/images/leaflet/marker-icon.png';
import leafletMarkerShadow from '@src/images/leaflet/marker-shadow.png';

/**
 * Application entrypoint
 */
domReady(async () => {
  const $slick = $('.slick:not(.slick_noinit)');
  if ($slick.length) {
    initSlick($slick, { dots: false });
  }

  function initSlick(element, params = {}) {
    let defaultParams = {
      arrows: false,
      dots: true,
      autoplay: true,
      slidesToShow: 1,
      slidesToScroll: 1,
      fade: true,
      cssEase: 'linear',
      speed: 1500,
      infinite: true,
      pauseOnHover: false,
      // slide: '.slide',// doesnt work
      touchThreshold: 20,
      rows: 0, //inline-block fix
    };
    $.each(params, function (index, value) {
      defaultParams[index] = value;
    });
    if (element.length) {
      $(element).on('init', function () {
        $(window).trigger('resize');
      });
      $(element).slick(defaultParams);
    }
  }

  function initMenu() {
    const $burgerNavigationWrapper = $('#burger-navigation-wrapper');
    const $horizontalNavigationWrapper = $('#horizontal-navigation-wrapper');
    if ($horizontalNavigationWrapper.length) {
      const $parents = $horizontalNavigationWrapper.find(
        'li.menu-item-has-children'
      );
      const $submenu = $parents.find('ul.sub-menu');
      $submenu.css('top', $submenu.closest('li').outerHeight());
      $parents.on('mouseenter', function () {
        const $li = $(this);
        let height = $li.find('> ul.sub-menu').outerHeight();
        $li.find('> ul.sub-menu > li').each((index, item) => {
          height += $(item).outerHeight();
        });
        $li.find('> a .chevron').addClass('up');
        $li.find('> ul.sub-menu').css('height', height);
      });
      $parents.on('mouseleave', function () {
        const $li = $(this);
        $li.find('> a .chevron').removeClass('up');
        $li.find('> ul.sub-menu').css('height', 0);
      });
    }
    if ($burgerNavigationWrapper.length) {
      const $trigger = $burgerNavigationWrapper.find(
        '.burger-navigation-trigger'
      );
      const $menu = $burgerNavigationWrapper.find('#navigation');
      $trigger.on('click', () => {
        $trigger.toggleClass('open');
        $menu.toggleClass('visible');
      });
      $(window).on('resize', () => {
        showOrHideBurgerMenu();
        if($trigger.hasClass('open')){
          $trigger.removeClass('open');
          $menu.removeClass('visible');
        }
      })
    }
  }

  function initAccordion() {
    const $accordions = $('.accordion');
    const $accordionsContent = $accordions.find('.accordion-content');
    const $accordionsToggle = $accordions.find('.accordion-toggle');
    $accordionsContent.hide();
    $accordions.removeClass('accordion-pre-init');
    $accordionsToggle.on('click', function () {
      const $accordion = $(this);
      const target = $accordion.data('accordion-target');
      const group = $accordion.parent('.accordion').data('accordion-group');

      if (target) {
        const $content = $accordions.find(
          '[data-accordion-anchor="' + target + '"]'
        );
        // const group = $accordion
        if ($content.length) {
          if (group) {
            closeAccordionGroup(group);
          }
          if ($content.is(':hidden')) {
            $content.slideDown(600, function () {
              //we should scroll here
            });
            $accordion.addClass('open');
            $accordion.find('.chevron').addClass('up');
          } else {
            $accordion.removeClass('open');
            $accordion.find('.chevron').removeClass('up').addClass('down');
            $content.slideUp();
          }
        }
      }
    });
    $accordions.each((index, element) => {
      //Trigger click if accordion should be expanded
      if ($(element).data('expanded') === 1) {
        $(element).find('.accordion-toggle').trigger('click');
      }
    });
  }

  function closeAccordionGroup(group, immediate = false) {
    const $accordionsgroup = $(
      '.accordion[data-accordion-group="' + group + '"]'
    );
    if(immediate) {
      $accordionsgroup.find('.accordion-content').hide();
    }else{
      $accordionsgroup.find('.accordion-content').slideUp();
    }
    $accordionsgroup.find('.accordion-toggle').removeClass('open');
    $accordionsgroup
    .find('.accordion-toggle .chevron')
    .removeClass('up')
    .addClass('down');
  }

  function initVue() {
    const config = {};
    //class for handling vue events
    window.Event = new (class {
      constructor() {
        this.vue = createApp(config);
      }

      fire(event, data = null) {
        this.vue.$emit(event, data);
      }

      listen(event, callback) {
        this.vue.$on(event, callback);
      }
    })();
    const componentElements = document.querySelectorAll('.vue-component');
    if (componentElements.length > 0) {
      componentElements.forEach((item) => {
        const app = createApp(config);
        app.component('listings', listings);
        app.component('vuevideo', vuevideo);
        app.component('listingcard', listingcard);
        app.mount(item);
      });
    }
  }

  function initShowHidden() {
    const $buttons = $('.showhidden');
    if ($buttons.length) {
      $buttons.on('click', function (event) {
        event.preventDefault();
        const container = $(this).data('container');
        if (container) {
          const $container = $(container);
          $container.find('.hidden').removeClass('hidden');
        }
        $(this).remove();
      });
    }
  }

  function initAnchorLinks() {
    const $links = $('a');
    const $accordions = $('.accordion');
    const accordiongroups = [];
      $accordions.each((index, item) => {
        const group = $(item).data('accordion-group');
        if(group && !accordiongroups.includes(group)){
          accordiongroups.push(group);
        }
    })
    $links.on('click', function (e) {
      let href = $(this).attr('href');
      if ((typeof href !== 'undefined' && href.startsWith('#')) || $(this).hasClass('anchor-link')) {
        if (!href) {
          href = $(this).find('a').attr('href');
        }
        if (href) {
          if(accordiongroups.length) {
            //close all accordion groups before scroll
            accordiongroups.forEach((group) => {
              closeAccordionGroup(group, true);
            })
          }
          let height = calculateHeaderHeight();
          let $anchor = $(href);
          if ($anchor.length) {
            if ($anchor.hasClass('accordion')) {
              const $toggle = $anchor.find('.accordion-toggle');
              if ($toggle.length && !$toggle.hasClass('open')) {
                $toggle.trigger('click');
              }
            }
            let top = $anchor.offset().top - 40;
            if (height > 0 && height < top) {
              top -= height;
            }
            $('html,body').animate({ scrollTop: top }, 1600);
            window.history.pushState(null, null, href);
            e.preventDefault();
          }
        }
      }
    });
  }

  function calculateHeaderHeight()
  {
    let height = 0;
    const $header = $('header');
    if ($header.length) {
      height += $('header').outerHeight();
      const $brand = $header.find('.brand');
      if($brand.length) {
        const $img = $brand.find('img');
        if($img.length) {
          //we have brand and image
          const brandHeight = $brand.height();
          const imgHeight = $img.height();
          if(imgHeight > brandHeight){
            //image has an offset
            height += imgHeight - brandHeight;
          }
        }
      }
    }
    return height;
  }

  function initForms() {
    //Exclusive checkboxes
    const $exclusiveCheckboxes = $('input[type="checkbox"][data-exclusive]');
    if ($exclusiveCheckboxes.length) {
      $exclusiveCheckboxes.on('click', function (e) {
        const $checkbox = $(this);
        const checkEvent = $checkbox.is(':checked');
        const $checkboxesInGroup = $(
          'input[type="checkbox"][data-exclusive="' +
            $checkbox.data('exclusive') +
            '"]'
        );
        $checkboxesInGroup.prop('checked', false); //uncheck all in group, this included
        if (checkEvent) {
          $checkbox.prop('checked', true); //check this
        }
      });
    }

    //Showingselect
    const $showingSelects = $('.showings input[name="showing"]');
    const $slotSelects = $('.showings select[name="slot"]');
    $showingSelects.on('change', function () {
      $slotSelects.prop('disabled', true);
      $showingSelects.prop('required', true);
      const isChecked = $(this).is(':checked');
      const showingId = $(this).val();
      if (isChecked) {
        $showingSelects.prop('required', false);
        if (showingId) {
          const $slotSelect = $(
            '.showings select[data-belongsto="showing-' + showingId + '"]'
          );
          if ($slotSelect.length) {
            $slotSelect.prop('disabled', false);
          }
        }
      }
    });
    if ($showingSelects.length === 1) {
      $showingSelects.trigger('click');
    }
  }

  function initOnResize() {
    $(window).on('resize', () => {
      calculateAppFooter();
    });
    $(window).trigger('resize');
  }

  function showOrHideBurgerMenu() {
    const $horizontalNavigationWrapper = $('#horizontal-navigation-wrapper');
    if ($horizontalNavigationWrapper.length) {
      const $header = $('header');
      const $banner = $header.find('.banner');
      const bannerWidth = $banner.width();
      let childrenWidth = 0;
      const $toggle = $(
        '#burger-navigation-wrapper .burger-navigation-trigger'
      );
      let height = null;
      const $links = $horizontalNavigationWrapper.find('a');
      if (!$horizontalNavigationWrapper.hasClass('!hidden')) {
        $horizontalNavigationWrapper.attr(
          'data-show-at-header-width',
          $header.width()
        );
        $banner
          .children()
          .each((index, element) => {
            childrenWidth += $(element).outerWidth();
          });
        if (childrenWidth > 0 && childrenWidth > bannerWidth) {
          //nav is too wide, hide it
          if ($toggle.hasClass('!hidden')) {
            $toggle.removeClass('!hidden');
            $horizontalNavigationWrapper.addClass('!hidden');
          }
        }
        $links.each((index, element) => {
          const currentElementHeight = $(element).height();
          if (currentElementHeight > 0) {
            if (height == null) {
              height = currentElementHeight;
            }
            if (height !== currentElementHeight) {
              //nav is too high, hide it
              if ($toggle.hasClass('!hidden')) {
                $toggle.removeClass('!hidden');
                $horizontalNavigationWrapper.addClass('!hidden');
              }
            }
          }
        });
      } else {
        if (
          $header.width() >
          $horizontalNavigationWrapper.attr('data-show-at-header-width')
        ) {
          $horizontalNavigationWrapper.removeClass('!hidden');
          $toggle.addClass('!hidden');
        }
      }
    }
  }

  function calculateAppFooter() {
    const $app = $('#app');
    const $footer = $('footer');
    if ($app.length && $footer.length) {
      const footerheight = $footer.height();
      if (footerheight) {
        $app.css('padding-bottom', footerheight);
      }
    }
  }

  function initMap() {
    const mapObjects = document.querySelectorAll('.map-object');

    if (mapObjects.length > 0) {
      const markerIcon = L.icon({
        iconUrl: leafletMarkerIcon,
        shadowUrl: leafletMarkerShadow,
        iconSize: [24, 36],
        iconAnchor: [12, 36],
      });
      mapObjects.forEach((item) => {
        let showMap = false;
        const id = item.getAttribute('id');
        if (id) {
          const dataset = item.dataset;
          if (dataset.lat && dataset.lon) {
            showMap = true;
            const zoom = dataset.zoom ?? 13;
            const map = new L.map(id, {
              zoom: zoom,
              scrollWheelZoom: 'center',
              zoomControl: false,
            }).setView([dataset.lat, dataset.lon], zoom);
            L.control
              .zoom({
                position: 'bottomleft',
              })
              .addTo(map);
            L.marker([dataset.lat, dataset.lon], {icon: markerIcon}).addTo(map);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
              attribution:
                '<a href="http://openstreetmap.org">OpenStreetMap</a>',
              maxZoom: 21,
            }).addTo(map);
          }
        }
        if (!showMap) {
          item.remove();
        }
      });
    }
  }

  function initFixedRatio() {
    const fixedRatioObjects = document.querySelectorAll('.fixed-ratio');
    fixedRatioObjects.forEach((item) => {
      const width = item.getAttribute('width');
      const height = item.getAttribute('height');
      if (width && height) {
        const ratio = Math.ceil((height / width) * 100) / 100;
        addEventListener(
          'resize',
          _.debounce(() => {
            const itemHeight = Math.round(item.offsetWidth * ratio);
            item.setAttribute('style', 'height:' + itemHeight + 'px');
          }, 100)
        );
        setTimeout(() => {
          const itemHeight = Math.round(item.offsetWidth * ratio);
          item.setAttribute('style', 'height:' + itemHeight + 'px');
        }, 400);
      }
    });
  }

  initVue();
  initMenu();
  initAccordion();
  initShowHidden();
  initAnchorLinks();
  initForms();
  initMap();
  initFixedRatio();
  initOnResize(); //should be last to trigger resize
});

/**
 * @see {@link https://webpack.js.org/api/hot-module-replacement/}
 */
import.meta.webpackHot?.accept(console.error);

// Objektsida accordion
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.accordion-trigger').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const item = this.closest('.accordion-item');
            const content = item.querySelector('.accordion-content');
            const icon = this.querySelector('.accordion-icon');
            const isOpen = item.classList.contains('open');
            item.classList.toggle('open', !isOpen);
            if (icon) icon.textContent = isOpen ? '+' : '×';
        });
    });
});

// Till salu-filter
document.addEventListener('DOMContentLoaded', function() {
    const filterBtns = document.querySelectorAll('.filter-knapp');
    const objekt = document.querySelectorAll('.objekt-kort-inner[data-status]');
    
    filterBtns.forEach(function(btn) {
        btn.addEventListener('click', function() {
            filterBtns.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            const filter = this.dataset.filter;
            objekt.forEach(function(o) {
                if (filter === 'alla' || o.dataset.status === filter) {
                    o.classList.remove('hidden');
                } else {
                    o.classList.add('hidden');
                }
            });
        });
    });
});

// Objektsida hero-slideshow
document.addEventListener('DOMContentLoaded', function() {
    const slideshow = document.querySelector('.objekt-hero-slideshow');
    if (!slideshow) return;

    const slides = slideshow.querySelectorAll('.objekt-hero-slide');
    const dots = slideshow.querySelectorAll('.objekt-hero-dot');
    if (slides.length <= 1) return;

    let current = 0;
    let timer = null;

    function goTo(n) {
        slides[current].classList.remove('active');
        dots[current]?.classList.remove('active');
        current = (n + slides.length) % slides.length;
        slides[current].classList.add('active');
        dots[current]?.classList.add('active');
    }

    function autoPlay() {
        timer = setInterval(() => goTo(current + 1), 5000);
    }

    function resetTimer() {
        clearInterval(timer);
        autoPlay();
    }

    slideshow.querySelector('.objekt-hero-next')?.addEventListener('click', () => { goTo(current + 1); resetTimer(); });
    slideshow.querySelector('.objekt-hero-prev')?.addEventListener('click', () => { goTo(current - 1); resetTimer(); });
    dots.forEach((dot, i) => dot.addEventListener('click', () => { goTo(i); resetTimer(); }));

    autoPlay();
});

// Hamburger-meny
document.addEventListener('DOMContentLoaded', function() {
    const toggle = document.getElementById('menu-toggle');
    const close = document.getElementById('menu-close');
    const overlay = document.getElementById('mobile-menu');
    if (!toggle || !overlay) return;

    function openMenu() {
        overlay.classList.add('active');
        toggle.classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function closeMenu() {
        overlay.classList.remove('active');
        toggle.classList.remove('active');
        document.body.style.overflow = '';
    }

    toggle.addEventListener('click', function() {
        overlay.classList.contains('active') ? closeMenu() : openMenu();
    });

    close?.addEventListener('click', closeMenu);

    overlay.addEventListener('click', function(e) {
        if (e.target === overlay) closeMenu();
    });

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeMenu();
    });
});

// Page hero slideshow (kontakt, om oss, till salu)
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.kontakt-hero').forEach(function(hero) {
        const slides = hero.querySelectorAll('.kontakt-hero-slide');
        if (slides.length <= 1) return;
        let current = 0;
        setInterval(function() {
            slides[current].classList.remove('active');
            current = (current + 1) % slides.length;
            slides[current].classList.add('active');
        }, 5000);
    });
});

// Bildgalleri lightbox
function initLightbox() {
  var lb = document.getElementById('lightbox');
  if (!lb) return;

  var lbImg = document.getElementById('lightbox-img');
  var lbCaption = document.getElementById('lightbox-caption');
  var lbCounter = document.getElementById('lightbox-counter');
  var triggers = document.querySelectorAll('.galleri-trigger');
  var images = (typeof allImages !== 'undefined') ? allImages : [];
  var total = images.length || triggers.length;
  var current = 0;

  function openLightbox(index) {
    current = index;
    showImage();
    lb.classList.add('active');
    document.body.style.overflow = 'hidden';
  }

  function closeLightbox() {
    lb.classList.remove('active');
    document.body.style.overflow = '';
  }

  function showImage() {
    lbCounter.textContent = (current + 1) + ' / ' + total;
    if (images.length > 0) {
      var src = images[current];
      lbImg.src = src;
      lbImg.alt = 'Bild ' + (current + 1);
      lbCaption.textContent = 'Bild ' + (current + 1);
    } else {
      var a = triggers[current];
      lbImg.src = a.dataset.highres;
      lbImg.alt = a.dataset.text || '';
      lbCaption.textContent = a.dataset.text || '';
    }
  }

  triggers.forEach(function(a) {
    a.addEventListener('click', function(e) {
      e.preventDefault();
      e.stopPropagation();
      openLightbox(parseInt(this.dataset.index));
    });
  });

  document.getElementById('lightbox-close').addEventListener('click', closeLightbox);
  document.getElementById('lightbox-prev').addEventListener('click', function() {
    current = (current - 1 + total) % total;
    showImage();
  });
  document.getElementById('lightbox-next').addEventListener('click', function() {
    current = (current + 1) % total;
    showImage();
  });

  lb.addEventListener('click', function(e) {
    if (e.target === lb) closeLightbox();
  });

  document.addEventListener('keydown', function(e) {
    if (!lb.classList.contains('active')) return;
    if (e.key === 'Escape') closeLightbox();
    if (e.key === 'ArrowLeft') { current = (current - 1 + total) % total; showImage(); }
    if (e.key === 'ArrowRight') { current = (current + 1) % total; showImage(); }
  });
}

// Kör lightbox-init vid DOMContentLoaded och window load
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', initLightbox);
} else {
  initLightbox();
}
window.addEventListener('load', initLightbox);

// FDR Menu
const fdrMenuBtn = document.getElementById('fdr-menu-btn');
const fdrMenuClose = document.getElementById('fdr-menu-close');
const fdrMenuOverlay = document.getElementById('fdr-menu-overlay');
if (fdrMenuBtn && fdrMenuOverlay) {
  fdrMenuBtn.addEventListener('click', () => fdrMenuOverlay.classList.add('active'));
  fdrMenuClose?.addEventListener('click', () => fdrMenuOverlay.classList.remove('active'));
  fdrMenuOverlay.addEventListener('click', (e) => {
    if (e.target === fdrMenuOverlay) fdrMenuOverlay.classList.remove('active');
  });
}

// FDR Hero karusell
document.querySelectorAll('.fdr-om-hero').forEach(hero => {
  const slides = hero.querySelectorAll('.fdr-hero-slide');
  if (slides.length < 2) return;
  let current = 0;
  setInterval(() => {
    slides[current].classList.remove('active');
    current = (current + 1) % slides.length;
    slides[current].classList.add('active');
  }, 5000);
});

// FDR Startsida hero karusell
document.querySelectorAll('.fdr-hero').forEach(hero => {
  const slides = hero.querySelectorAll('.fdr-hero-slide');
  if (slides.length < 2) return;
  let current = 0;
  setInterval(() => {
    slides[current].classList.remove('active');
    current = (current + 1) % slides.length;
    slides[current].classList.add('active');
  }, 5000);
});


// === Listing card carousel: pilar + scroll-snap ===
(function() {
  function initCarousels() {
    document.querySelectorAll('.em-listing-kort[data-image-count]').forEach(function(kort) {
      if (kort.dataset.carouselInit) return;
      kort.dataset.carouselInit = '1';

      var track = kort.querySelector('.em-listing-track');
      if (!track) return;

      var prev = kort.querySelector('.em-listing-pil--prev');
      var next = kort.querySelector('.em-listing-pil--next');

      function scrollByOne(dir) {
        var slide = track.querySelector('.em-listing-slide');
        if (!slide) return;
        var w = slide.getBoundingClientRect().width;
        track.scrollBy({ left: dir * w, behavior: 'smooth' });
      }

      function handlePil(e, dir) {
        e.preventDefault();
        e.stopPropagation();
        scrollByOne(dir);
      }

      if (prev) prev.addEventListener('click', function(e) { handlePil(e, -1); });
      if (next) next.addEventListener('click', function(e) { handlePil(e, 1); });

      // Förhindra att drag på bilden navigerar via <a>
      var dragStart = null;
      track.addEventListener('mousedown', function(e) { dragStart = e.clientX; });
      track.addEventListener('click', function(e) {
        if (dragStart !== null && Math.abs(e.clientX - dragStart) > 5) {
          e.preventDefault();
        }
        dragStart = null;
      }, true);
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initCarousels);
  } else {
    initCarousels();
  }
})();

// Säljtext accordion
document.addEventListener('DOMContentLoaded', function() {
  const wraps = document.querySelectorAll('.em-detalj-saljtext-wrap');
  wraps.forEach(wrap => {
    const text = wrap.querySelector('.em-detalj-saljtext');
    const toggle = wrap.querySelector('.em-detalj-saljtext-toggle');
    if (!text || !toggle) return;
    
    // Kolla om innehållet faktiskt är längre än max-height
    if (text.scrollHeight > text.clientHeight + 5) {
      wrap.classList.add('has-overflow');
    }
    
    toggle.addEventListener('click', function() {
      const expanded = wrap.classList.toggle('is-expanded');
      toggle.textContent = expanded ? 'VISA MINDRE' : 'LÄS MER';
    });
  });
});

// Renoveringar - läs mer toggle
document.addEventListener('DOMContentLoaded', function() {
  document.querySelectorAll('.em-detalj-renoveringar-wrap').forEach(function(wrap) {
    var text = wrap.querySelector('.em-detalj-renoveringar-text');
    var toggle = wrap.querySelector('.em-detalj-renoveringar-toggle');
    if (!text || !toggle) return;
    if (text.scrollHeight > text.clientHeight + 1) {
      wrap.classList.add('has-overflow');
    }
    toggle.addEventListener('click', function() {
      wrap.classList.toggle('is-expanded');
      toggle.textContent = wrap.classList.contains('is-expanded') ? 'VISA MINDRE' : 'LÄS MER';
    });
  });
});

// Galleri-expand: ESC stänger
document.addEventListener('keydown', function(e) {
  if (e.key === 'Escape') {
    var galleri = document.getElementById('em-galleri-expand');
    if (galleri && galleri.classList.contains('em-galleri-expand--open')) {
      if (typeof emCloseGalleri === 'function') emCloseGalleri();
      else {
        galleri.classList.remove('em-galleri-expand--open');
      }
    }
  }
});
