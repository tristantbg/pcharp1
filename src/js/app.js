/* jshint esversion: 6 */

import 'babel-polyfill'
import InfiniteGrid, {
  JustifiedLayout
} from "@egjs/infinitegrid"
import Flickity from 'flickity'
import lazysizes from 'lazysizes'
import optimumx from 'lazysizes'
require('../../node_modules/lazysizes/plugins/object-fit/ls.object-fit.js')
require('../../node_modules/lazysizes/plugins/unveilhooks/ls.unveilhooks.js')
import Barba from 'barba.js'

const App = {
  init: () => {
    App.interact.init()
    document.getElementById("loader").style.display = "none"

  },
  intro: () => {
    const introHide = () => {
      TweenMax.to(intro, 0.2, {
        autoAlpha: 0,
        onComplete: () => {
          document.body.classList.remove("with-intro");
        }
      });
    };
    const intro = document.getElementById('intro');
    if (intro && document.body.classList.contains("with-intro")) {
      intro.addEventListener("click", introHide);
    }
  },
  interact: {
    init: () => {
      Grid.init()
      App.interact.embedKirby()
      App.interact.linkTargets()
      Sliders.init()
    },
    linkTargets: () => {
      const links = document.querySelectorAll("a");
      for (var i = 0; i < links.length; i++) {
        const element = links[i];
        if (element.host !== window.location.host) {
          element.setAttribute('target', '_blank');
        } else {
          element.setAttribute('target', '_self');
        }
      }
    },
    embedKirby: () => {
      var pluginEmbedLoadLazyVideo = function() {
        var wrapper = this.parentNode;
        var embed = wrapper.children[0];
        var script = wrapper.querySelector('script');
        embed.src = script ? script.getAttribute('data-src') + '&autoplay=1' : embed.getAttribute('data-src') + '&autoplay=1';
        wrapper.removeChild(this);
      };

      var thumb = document.getElementsByClassName('embed__thumb');

      for (var i = 0; i < thumb.length; i++) {
        thumb[i].addEventListener('click', pluginEmbedLoadLazyVideo, false);
      }
    },

  },
}

const Grid = {
  mediasContainer: null,
  medias: null,

  init: () => {
    Grid.mediasContainer = document.getElementById('medias')
    Grid.medias = document.getElementsByClassName('media')
    if(Grid.mediasContainer) Grid.render()
  },
  render: () => {
    Grid.element = new InfiniteGrid('#medias', {
      itemSelector: 'visible',
      horizontal: false,
    })

    Grid.element.setLayout(JustifiedLayout, {
      minSize: App.isMobile ? 50 : 150,
      maxSize: 200,
      margin: 0
    })
    Grid.element.on('layoutComplete', Grid.show)

    Grid.element.layout()

  },
  show: () => {
    Grid.mediasContainer.style.opacity = 1
  },
  hide: () => {
    Grid.mediasContainer.style.opacity = 0
  },
  random: () => {
    Grid.rebuild(true)
  },
  next: () => {
    const current = document.querySelector('.media.active')
    if (current) {
      const next = nextVisible(current)
      if (next) Grid.selectMedia(next)
    }
  },
  previous: () => {
    const current = document.querySelector('.media.active')
    if (current) {
      const previous = previousVisible(current)
      if (previous) Grid.selectMedia(previous)
    }
  },
  close: () => {
    const current = document.querySelector('.media.active')
    if (current) {
      current.classList.remove('active')
      for (let i = 0; i < Grid.medias.length; i++) {
        Grid.medias[i].style.transform = ''
      }
      Grid.mediasContainer.classList.remove('is-selecting')
      Grid.projectViewer.element.classList.add('hidden')
      Grid.mediasContainer.style.marginBottom = '0px'
      App.topbar.classList.remove('sticky--force-unpinned')
      if (App.isMobile) {
        stopBodyScrolling(false)
        document.body.style.overflow = 'initial'
      }
    }
  }
}

const Sliders = {
  flickitys: [],
  init: () => {
    Sliders.elements = document.getElementsByClassName('slider');
    if (Sliders.elements.length > 0) {
      for (var i = 0; i < Sliders.elements.length; i++) {
        Sliders.flickity(Sliders.elements[i], {
          cellSelector: '.slide',
          imagesLoaded: true,
          lazyLoad: 1,
          cellAlign: 'left',
          setGallerySize: App.isMobile,
          adaptiveHeight: App.isMobile,
          wrapAround: true,
          prevNextButtons: true,
          pageDots: false,
          draggable: App.isMobile ? '>1' : false,
          arrowShape: 'M29.7 77.4l4.8-3.7L10 41.8h90v-6.1H10.1L34.5 4.6 29.7.9 0 38.7z'
        });
      }
      Sliders.accessibility()
    }
  },
  flickity: (element, options) => {
    Sliders.slider = new Flickity(element, options);
    Sliders.flickitys.push(Sliders.slider);
    if (Sliders.slider.slides.length < 1) return; // Stop if no slides

    Sliders.slider.on('change', function() {
      if (this.selectedElement) {
        const caption = this.element.parentNode.querySelector('.caption');
        if (caption)
          caption.innerHTML = this.selectedElement.getAttribute('data-caption');
        const number = this.element.parentNode.querySelector('.slide-number');
        if (number)
          number.innerHTML = (this.selectedIndex + 1) + '/' + this.slides.length;
      }
      const adjCellElems = this.getAdjacentCellElements(1);
      for (let i = 0; i < adjCellElems.length; i++) {
        const adjCellImgs = adjCellElems[i].querySelectorAll('.lazy:not(.lazyloaded):not(.lazyload)')
        for (let j = 0; j < adjCellImgs.length; j++) {
          adjCellImgs[j].classList.add('lazyload')
        }
      }
    });
    Sliders.slider.on('staticClick', function(event, pointer, cellElement, cellIndex) {
      if (!cellElement) {
        return;
      } else {
        this.next();
      }
    });
    if (Sliders.slider.selectedElement) {
      const caption = Sliders.slider.element.querySelector('.caption');
      if (caption)
        caption.innerHTML = Sliders.slider.selectedElement.getAttribute('data-caption');
      const number = Sliders.slider.element.parentNode.querySelector('.slide-number');
      if (number)
        number.innerHTML = (Sliders.slider.selectedIndex + 1) + '/' + Sliders.slider.slides.length;
    }
  },
  accessibility: () => {
    const prevNext = document.getElementsByClassName('flickity-prev-next-button')

    for (var i = 0; i < prevNext.length; i++) {
      const elem = prevNext[i]
      elem.addEventListener('mousemove', event => {
        var svg = elem.querySelector("svg");
        var parentOffset = elem.getBoundingClientRect();
        svg.style.top = event.pageY - parentOffset.top - pageYOffset + "px";
        svg.style.left = event.pageX - parentOffset.left + "px";

      })
    }

  }
}

const Pjax = {
  titleTransition: 0.7,
  init: function() {
    Barba.Pjax.getTransition = function() {
      return Pjax.hideShowTransition
    };
    Barba.Dispatcher.on('linkClicked', function(el) {
      App.linkClicked = el
    });
    Barba.Dispatcher.on('newPageReady', function(currentStatus, oldStatus, container) {
      var js = container.querySelector("script");
      if (js != null) {
        eval(js.innerHTML);
        Audio.init()
      }
    });
    Barba.Pjax.Dom.wrapperId = 'main'
    Barba.Pjax.Dom.containerClass = 'pjax'
    Barba.BaseCache.reset()
    // Barba.Pjax.cacheEnabled = false;
    Barba.Pjax.start()
  },
  hideShowTransition: Barba.BaseTransition.extend({
    start: function() {
      let _this = this
      _this.newContainerLoading.then(_this.startTransition.bind(_this))
    },
    startTransition: function() {
      document.body.classList.add('is-loading')
      document.body.classList.remove('player-playing', 'infos-panel', 'about-panel', 'product-opened')
      Amplitude.pause()

      let _this = this
      const newContent = _this.newContainer.querySelector('#page-content')

      // const currentLink = document.querySelector('a.active')
      // if (currentLink) currentLink.classList.remove('active')
      // if (App.linkClicked) App.linkClicked.classList.add('active')

      App.nextPageType = newContent.getAttribute('page-type')

      if (App.pageType == 'ok') {

      } else {
        document.body.setAttribute('page-type', App.nextPageType)
        _this.endTransition(_this, newContent)
      }

    },
    endTransition: function(_this, newContent) {
      window.scroll(0, 0)
      resizeWindow()

      if (App.nextPageType == 'ok') {

      } else {
        _this.finish(_this, newContent)
      }
    },
    finish: function(_this, newContent) {

      _this.done()
      App.pageType = App.nextPageType

      App.sizeSet()
      App.interact.init()
      document.body.classList.remove('is-loading')

      // setTimeout(function() {
      //   TweenMax.set(document.querySelector('#page-content'), {
      //     clearProps: 'transform,opacity'
      //   })
      // }, 500);

      if (window.ga) window.ga('send', 'pageview')
    }


  })
}

document.addEventListener("DOMContentLoaded", App.init);
