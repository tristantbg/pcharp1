/* jshint esversion: 6 */

import 'whatwg-fetch'
// import 'babel-polyfill'
import InfiniteGrid, { JustifiedLayout } from "@egjs/infinitegrid"
import Headroom from 'headroom.js'
import throttle from 'lodash.throttle'
import debounce from 'lodash.debounce'
import Flickity from 'flickity-hash'
import imagesLoaded from 'imagesloaded'
import lazysizes from 'lazysizes'
import optimumx from 'lazysizes'
require('../../node_modules/lazysizes/plugins/object-fit/ls.object-fit.js')
require('../../node_modules/lazysizes/plugins/unveilhooks/ls.unveilhooks.js')
import Barba from 'barba.js'
import jump from 'jump.js'
require('viewport-units-buggyfill').init();

const easeInOutExpo = (t, b, c, d) => {
  if (t == 0) return b;
  if (t == d) return b + c;
  if ((t /= d / 2) < 1) return c / 2 * Math.pow(2, 10 * (t - 1)) + b;
  return c / 2 * (-Math.pow(2, -10 * --t) + 2) + b;
}

const htmlDecode = input => {
  var doc = new DOMParser().parseFromString(input, "text/html");
  return doc.documentElement.textContent;
}

const getUrlParams = prop => {
  var params = {};
  var search = decodeURIComponent(window.location.href.slice(window.location.href.indexOf('?') + 1));
  var definitions = search.split('&');

  definitions.forEach(function(val, key) {
    var parts = val.split('=', 2);
    params[parts[0]] = parts[1];
  });

  return (prop && prop in params) ? params[prop] : params;
}

const resizeWindow = () => {
  var event = document.createEvent('HTMLEvents');
  event.initEvent('resize', true, false);
  window.dispatchEvent(event);
}

const simulateClick = elem => {
  // Create our event (with options)
  var evt = new MouseEvent('click', {
    bubbles: true,
    cancelable: true,
    view: window
  });
  // If cancelled, don't dispatch our event
  var canceled = !elem.dispatchEvent(evt);
};

const isInViewport = elem => {
  var bounding = elem.getBoundingClientRect();
  return (
    bounding.top >= (window.innerHeight || document.documentElement.clientHeight) / 4 &&
    bounding.bottom <= (window.innerHeight || document.documentElement.clientHeight)
  );
};

const App = {
  root: window.location.origin == 'http://localhost:8888' ? '/pierrecharpin/www/' : '/',
  init: () => {
    App.sizeSet()
    App.pageType = document.body.getAttribute('page-type')
    App.siteTitle = document.getElementById('site-title')
    if (App.isMobile) App.siteTitle.classList.add('no-barba')
    Intro.init()
    App.interact.init()
    Panel.menuTargets()
    Panel.init(true)
    Pjax.init()
    Search.init()
    window.addEventListener('resize', debounce(App.sizeSet, 200), false)
    document.getElementById('loader').style.display = "none"

  },
  sizeSet: () => {
    App.width = (window.innerWidth || document.documentElement.clientWidth);
    App.height = (window.innerHeight || document.documentElement.clientHeight);
    if (App.width <= 767)
      App.isMobile = true;
    if (App.isMobile) {
      if (App.width > 767) {
        // location.reload();
        App.isMobile = false;
      }
    }
  },
  closeMenu: () => {
    App.menu.classList.add('sticky--unpinned')
    App.menu.classList.remove('sticky--force-pinned')
    App.siteTitle.classList.remove('active')
  // Search.unselect()
  },
  scrollTo: (element) => {
    if (!App.isScrolling) {
      App.isScrolling = true;
      const max = document.documentElement.offsetHeight - App.height - window.scrollY;
      if (element.getBoundingClientRect().top >= max)
        element = max
      jump(element, {
        duration: 1200,
        easing: easeInOutExpo,
        offset: App.isMobile ? -36 : 0,
        callback: () => App.isScrolling = false
      });
    }
  },
  stickyHeader: () => {
    App.menu = document.getElementById("menu")
    App.headroom = new Headroom(App.menu, {
      offset: 0,
      // tolerance: {
      //   up: 5,
      //   down: 30
      // },
      classes: {
        initial: "sticky",
        pinned: "sticky--pinned",
        unpinned: "sticky--unpinned-disable",
        top: "sticky--top",
        notTop: "sticky--not-top",
        bottom: "sticky--bottom",
        notBottom: "sticky--not-bottom"
      },
      onUnpin: function() {
        // document.body.classList.remove('sticky--menu')
      },
      onPin: function() {
        document.body.classList.add('sticky--menu')
      },
      onTop: function() {},
      onNotTop: function() {
        document.body.classList.remove('sticky--menu')
      }
    })
    App.menu.addEventListener('mouseenter', () => {
      document.body.classList.add('sticky--menu')
    })
    App.menu.addEventListener('mouseleave', () => {
      document.body.classList.remove('sticky--menu')
    })
    setTimeout(function() {
      App.headroom.init()
      App.menu.classList.add('sticky--unpinned')
    }, 1500);
  },
  interact: {
    init: () => {
      Grid.init()
      Lightbox.init()
      App.interact.embedKirby()
      App.interact.linkTargets()
      Panel.init()
    },
    linkTargets: () => {
      const links = document.querySelectorAll("a");
      for (var i = 0; i < links.length; i++) {
        const element = links[i];
        if (element.host !== window.location.host) {
          element.setAttribute('target', '_blank');
          element.setAttribute('rel', 'noopener');
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

const Search = {
  init: () => {
    Search.element = document.getElementById('search-form')
    if (Search.element) {
      // Search.element.addEventListener('click', () => {
      //   Search.element.getElementById('query').focus()
      // })
      Search.element.addEventListener('submit', e => {
        e.preventDefault()
        Barba.Pjax.goTo(e.target.action + '?q=' + e.target[0].value)
        App.closeMenu()
      })
      Search.labels = document.querySelectorAll('label.mobile')
      Search.labels.forEach(l => {
        l.addEventListener('click', e => {
          e.currentTarget.classList.toggle('active')
        })
      })
    }
  },
  reset: () => {
    if (Search.element)
      Search.element.querySelector('input').value = ''
  },
  unselect: () => {
    Search.labels.forEach(l => {
      l.classList.remove('active')
    })
  }
}

const Grid = {
  mediasContainer: null,
  medias: null,
  mediasData: null,
  options: {
    minSize: 150,
    maxSize: 250,
    margin: 0
  },
  init: () => {
    Grid.finished = false
    Grid.mediasContainer = document.getElementById('medias')
    Grid.medias = document.querySelectorAll('#medias > .media')
    if (Grid.mediasContainer) Grid.render()
    Grid.getData()
  },
  getData: () => {
    if (Grid.mediasData) return
    const fetchOptions = {
      method: 'GET',
      mode: 'no-cors',
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json'
      }
    }
    fetch(window.location.origin + App.root + 'api/v1/works', fetchOptions)
      .then(response => {
        response.json().then(function(data) {
          Grid.mediasData = data
        // if (Lightbox.opened && Lightbox.slider.selectedElement) {
        //   Grid.description.showById(Lightbox.slider.selectedElement.dataset.id)
        // }
        })
      })
  },
  render: () => {
    if (!App.isMobile && Grid.medias.length < 3) {
      if (!App.isMobile) Grid.mediasContainer.classList.add('no-fit')
      Grid.show()
      return
    }
    if (Grid.medias.length == 0) Grid.show()
    Grid.description.element = document.getElementById('media-description')
    Grid.timeline.element = document.getElementById('timeline-date')

    Grid.element = new InfiniteGrid('#medias', {
      itemSelector: 'visible',
      horizontal: false,
    })

    if (App.isMobile) {
      Grid.options.minSize = 80
    } else {
      if (App.pageType == 'project') {
        Grid.options.minSize = 250
        Grid.options.maxSize = 400
      } else {
        Grid.options.minSize = 150
        Grid.options.maxSize = 250
      }
    }

    Grid.element.setLayout(JustifiedLayout, Grid.options)
    Grid.element.on('layoutComplete', Grid.show)

    setTimeout(function() {
      Grid.element.layout()
      Grid.interact()
    }, 100);
    
  },
  timeline: {
    element: null,
    check: () => {
      if (Grid.timeline.element) {
        for (var i = 0; i < Grid.medias.length; i++) {
          const element = Grid.medias[i]
          if (isInViewport(element)) {
            if (element.dataset.date != '')
              Grid.timeline.element.innerHTML = '<div class="inner">' + element.dataset.date + '</div>'
            Grid.timeline.element.classList.add('visible')
            window.clearTimeout(Grid.timeline.timeout)
            Grid.timeline.timeout = setTimeout(Grid.timeline.hide, 1000)
            break
          }
        }
      }
    },
    hide: () => {
      if (Grid.timeline.element) Grid.timeline.element.classList.remove('visible')
    }
  },
  interact: () => {
    Grid.medias.forEach(element => {
      element.addEventListener('mouseenter', () => {
        Grid.description.show(element)
      })
      element.addEventListener('mouseleave', () => {
        Grid.description.clear()
      })
    });
    window.addEventListener('scroll', debounce(Grid.timeline.check, 300), false)
  },
  description: {
    show: element => {
      if (Grid.description.element && Grid.mediasData && element.dataset.id && Grid.mediasData[element.dataset.id]) {
        Grid.description.element.innerHTML = htmlDecode(Grid.mediasData[element.dataset.id].formattedText)
      }
    },
    showById: id => {
      if (Grid.description.element && Grid.mediasData && Grid.mediasData[id]) {
        Grid.description.element.innerHTML = htmlDecode(Grid.mediasData[id].formattedText)
      }
    },
    clear: () => {
      if (Grid.description.element)
        Grid.description.element.innerHTML = ''
    }
  },
  show: () => {
    Grid.mediasContainer.style.opacity = 1
    if (App.pageType == 'projects' && Grid.selectedElement) {
      const selectedElement = Grid.mediasContainer.querySelector(Grid.selectedElement)
      if (!Grid.finished && selectedElement) App.scrollTo(selectedElement)
    }
    Grid.finished = true
  },
  hide: () => {
    Grid.mediasContainer.style.opacity = 0
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

const Intro = {
  loops: 0,
  loopsAmount: 1,
  init: () => {
    Intro.element = document.getElementById('intro');
    if (App.isMobile) Intro.destroy()
    if (!App.isMobile && Intro.element) {
      Intro.loopsAmount = parseInt(Intro.element.dataset.loop, 10)
      Intro.interval = parseInt(Intro.element.dataset.interval, 10)
      const images = Intro.element.querySelectorAll('img')
      images.forEach(i => {
        i.setAttribute('src', i.dataset.src)
      })
      const load = imagesLoaded(Intro.element, () => {
        Intro.flickity(Intro.element, {
          cellSelector: '.slide',
          imagesLoaded: false,
          lazyLoad: false,
          cellAlign: 'center',
          setGallerySize: false,
          adaptiveHeight: false,
          wrapAround: false,
          prevNextButtons: false,
          pauseOnHover: false,
          pageDots: false,
          draggable: false
        });
        Intro.element.addEventListener('click', Intro.destroy)
        Intro.slider.on('change', Intro.checkLastCell)
        Intro.timer = setInterval(() => {
          Intro.slider.next(true)
        }, Intro.interval)
      })
    } else {
      App.stickyHeader()
    }
  },
  flickity: (element, options) => {
    Intro.slider = new Flickity(element, options);
  },
  checkLastCell: () => {
    if (Intro.slider.selectedIndex == 0) {
      Intro.loops++
      if (Intro.loops >= Intro.loopsAmount) Intro.destroy()
    }
  },
  destroy: () => {
    if (Intro.element) {
      window.clearInterval(Intro.timer)
      if (Intro.slider) Intro.slider.destroy()
      Intro.element.parentNode.removeChild(Intro.element)
      document.body.classList.remove('with-intro')
      App.stickyHeader()
    }
  }
}

const Panel = {
  isVisible: false,
  init: first => {
    Panel.element = document.getElementById('page-panel')
    if (!Panel.element) return
    if (first) {
      Panel.isVisible = Panel.element.classList.contains('visible')
      if (Panel.isVisible) document.body.classList.add('page-panel')
      document.addEventListener('keydown', e => {
        switch (e.keyCode) {
          case 27:
            Panel.close()
            break;
          default:
            return;
        }
      })
    }
    Panel.interact()
  },
  menuTargets: () => {
    const menuTargets = document.querySelectorAll('[event-target=page]')
    menuTargets.forEach(elem => {
      elem.addEventListener('click', e => {
        e.preventDefault()
        const href = e.currentTarget.href
        fetch(href)
          .then(function(response) {
            return response.text()
          }).then(function(body) {
          const parser = new DOMParser();
          const doc = parser.parseFromString(body, "text/html");
          const texts = doc.querySelector('#page-panel')
          if (texts) {
            history.replaceState(null, doc.title, href)
            document.title = doc.title
            Panel.insertText(texts.innerHTML)
            App.closeMenu()
          }
        })
      })
    })
    App.siteTitle.addEventListener('click', e => {
      if (App.pageType == 'projects') Panel.close()
      if (App.isMobile) {
        e.preventDefault()
        if (App.menu.classList.contains('sticky--force-pinned')) {
          App.menu.classList.remove('sticky--force-pinned')
          App.siteTitle.classList.remove('active')
        } else {
          App.menu.classList.add('sticky--force-pinned')
          App.siteTitle.classList.add('active')
        }
      }
    })
  },
  interact: () => {
    App.interact.linkTargets()

    const langSwitch = Panel.element.querySelector('[event-target=lang-switch]')
    if (langSwitch) langSwitch.addEventListener('click', Panel.toggleLanguage)

    const texts = document.querySelectorAll('[event-target=text]')
    texts.forEach(t => {
      t.addEventListener('click', () => {
        const texts = document.getElementById('project-texts')
        if (texts) Panel.insertText(texts.innerHTML)
      })
    })

    const closePanel = document.querySelectorAll('[event-target=close-panel]')
    closePanel.forEach(c => {
      c.addEventListener('click', Panel.close)
    })
  },
  toggleLanguage: e => {
    const container = e.currentTarget.parentNode
    if (container.getAttribute('lang') == 'en') {
      container.setAttribute('lang', 'fr')
    } else if (container.getAttribute('lang') == 'fr') {
      container.setAttribute('lang', 'en')
    }
  },
  open: () => {
    Panel.element.classList.add('visible')
    document.body.classList.add('page-panel')
    Panel.isVisible = true
  },
  close: () => {
    Panel.element.classList.remove('visible')
    document.body.classList.remove('page-panel')
    Panel.isVisible = false
  },
  insertText: text => {
    if (Panel.element) {
      Panel.element.innerHTML = text
      Panel.element.scroll(0, 0)
      Panel.interact()
      Panel.open()
    }
  }
}

const Lightbox = {
  init: () => {
    Lightbox.opened = document.body.classList.contains('lightbox-on')
    // if(Lightbox.slider) {
    //   Lightbox.slider.destroy()
    //   Lightbox.slider = null
    // }
    Lightbox.element = document.getElementById('lightbox');
    if (Lightbox.element) {
      Lightbox.videos = Lightbox.element.querySelectorAll('video')
      Lightbox.flickity(Lightbox.element, {
        cellSelector: '.slide',
        accessibility: true,
        imagesLoaded: true,
        hash: true,
        lazyLoad: 1,
        cellAlign: 'left',
        setGallerySize: App.isMobile,
        wrapAround: false,
        prevNextButtons: true,
        pageDots: false,
        draggable: true,
        dragThreshold: 40,
        arrowShape: 'M73.9 100l-50-50 50-50 2.2 2.1L28.2 50l47.9 47.9z'
      });
      Lightbox.accessibility()
      if (window.location.hash !== '') {
        // Lightbox.slider.select(window.location.hash)
        Lightbox.open()
      }
      const params = getUrlParams()
      if (params.slide) {
        Lightbox.slider.selectCell('#' + params.slide)
        // window.history.replaceState(null, null, window.location.href.replace(window.location.search, ''));
        Lightbox.open()
      }
      Lightbox.lastCell = Lightbox.slider.selectedIndex
      Lightbox.element.classList.add('loaded')
    }
  },
  flickity: (element, options) => {
    if (Lightbox.slider) Lightbox.slider.destroy()
    Lightbox.slider = new Flickity(element, options);
    if (Lightbox.slider.slides.length < 1) return; // Stop if no slides
    if (Lightbox.slider.slides.length == 1) {
      Lightbox.open()
      Lightbox.slider.on('staticClick', function(event, pointer, cellElement, cellIndex) {
        Lightbox.nextProject()
      })
      Lightbox.slider.on('touchend', function() {
        Lightbox.nextProject()
      })
    }
    Lightbox.prev = Lightbox.element.querySelector('.flickity-button.previous')
    if (Lightbox.prev) {

      Lightbox.prev.removeAttribute('disabled')
      Lightbox.prev.addEventListener('click', () => {
        Lightbox.checkLastCell('backward')
      })
    }

    Lightbox.next = Lightbox.element.querySelector('.flickity-button.next')
    if (Lightbox.next) {
      Lightbox.next.removeAttribute('disabled')
      Lightbox.next.addEventListener('click', () => {
        Lightbox.checkLastCell('forward')
      })
    }
    Lightbox.slider.on('change', function() {
      resizeWindow()
      Lightbox.prev.removeAttribute('disabled')
      Lightbox.next.removeAttribute('disabled')
      if (this.selectedElement) {
        const caption = this.element.parentNode.querySelector('.caption');
        if (caption)
          caption.innerHTML = this.selectedElement.getAttribute('data-caption');
        const selectedId = this.selectedElement.dataset.id
        Grid.description.showById(selectedId)
        Lightbox.videos.forEach(v => {
          v.pause()
          v.currentTime = 0
        })
        const v = this.selectedElement.querySelector('video')
        if (v) v.play()
      }
      const adjCellElems = this.getAdjacentCellElements(1);
      for (let i = 0; i < adjCellElems.length; i++) {
        const adjCellImgs = adjCellElems[i].querySelectorAll('.lazy:not(.lazyloaded):not(.lazyload)')
        for (let j = 0; j < adjCellImgs.length; j++) {
          adjCellImgs[j].classList.add('lazyload')
        }
      }
    })
    Lightbox.slider.on('select', function() {
      if (this.selectedElement) {
        const selectedId = this.selectedElement.dataset.id
        if (Grid.mediasData)
          Grid.selectedElement = Grid.mediasData[selectedId].overview ? '[data-id="' + selectedId + '"]' : '[data-project="' + Grid.mediasData[selectedId].project + '"]'
        if (!App.isMobile) {
          const img = this.selectedElement.querySelector('.content img, .content video')
          const imgWidth = img.offsetWidth
          Lightbox.prev.style.width = imgWidth/2 + 'px'
          Lightbox.prev.style.opacity = 0
          Lightbox.next.style.width = `calc((100% - 15rem) - ${imgWidth/2}px)`
          Lightbox.next.style.opacity = 0
        }
      }
    })
    Lightbox.slider.on('staticClick', function(event, pointer, cellElement, cellIndex) {
      if (!cellElement || event.target.className == 'description' || !Modernizr.touchevents) {
        return;
      } else {
        if (pointer.pageX > App.width / 2) {
          Lightbox.checkLastCell('forward')
          this.next()
        } else {
          Lightbox.checkLastCell('backward')
          this.previous()
        }
      }
    });
    if (Lightbox.opened && Lightbox.slider.selectedElement) {
      const caption = Lightbox.element.querySelector('.caption');
      if (caption)
        caption.innerHTML = Lightbox.slider.selectedElement.getAttribute('data-caption');
      // Grid.description.showById(Lightbox.slider.selectedElement.dataset.id)
      Lightbox.slider.selectedCell.seen = true
    }
    Lightbox.lastCell = null
  },
  checkLastCell: way => {
    if (Lightbox.slider.selectedIndex == 0 && Lightbox.lastCell == 0 && way == 'backward') {
      document.getElementById('container').style.opacity = 0
      Lightbox.previousProject()
    // console.log('prev')
    } else if (Lightbox.slider.selectedIndex == Lightbox.slider.slides.length - 1 && Lightbox.lastCell == Lightbox.slider.slides.length - 1 && way == 'forward') {
      document.getElementById('container').style.opacity = 0
      Lightbox.nextProject()
    // console.log('next')
    }
    Lightbox.lastCell = Lightbox.slider.selectedIndex
    // function seen(element) {
    //   return element.seen === true;
    // }
    // const isLast = Lightbox.slider.cells.every(seen)

  // if (isLast) {
  //   document.getElementById('container').style.opacity = 0
  //   if (Lightbox.way == 'forward') {
  //     Lightbox.nextProject()
  //   } else {
  //     Lightbox.previousProject()
  //   }
  // }
  },
  accessibility: () => {
    document.addEventListener('keydown', e => {
      switch (e.keyCode) {
        // case 37:
        //   if (Lightbox.slider) {
        //     Lightbox.checkLastCell('backward')
        //     Lightbox.slider.previous()
        //   }
        //   break;
        // case 39:
        //   if (Lightbox.slider) {
        //     Lightbox.checkLastCell('forward')
        //     Lightbox.slider.next()
        //   }
        //   break;
        case 27:
          if (Lightbox.slider) {
            Lightbox.close()
          }
          break;
        default:
          return;
      }
    })
    const prevNext = Lightbox.element.getElementsByClassName('flickity-prev-next-button')
    for (var i = 0; i < prevNext.length; i++) {
      const elem = prevNext[i]
      elem.addEventListener('mousemove', event => {
        var svg = elem.querySelector("svg")
        var parentOffset = elem.getBoundingClientRect()
        svg.parentNode.style.opacity = 1
        svg.style.top = event.pageY - parentOffset.top - pageYOffset + "px"
        svg.style.left = event.pageX - parentOffset.left + "px"

      })
    }

    const toggles = document.querySelectorAll('[event-target=lightbox]')
    for (let i = 0; i < toggles.length; i++) {
      toggles[i].addEventListener('click', () => {
        if (Lightbox.opened) {
          Lightbox.close()
        } else {
          Lightbox.open()
          Lightbox.slider.selectCell(toggles[i].getAttribute('href'))
        }
      })
    }
    // const arrowLeft = document.querySelectorAll('[event-target=lightbox-previous]')
    // for (let i = 0; i < arrowLeft.length; i++) {
    //   arrowLeft[i].addEventListener('click', () => {
    //     Lightbox.previous()
    //   })
    // }
    // const arrowRight = document.querySelectorAll('[event-target=lightbox-next]')
    // for (let i = 0; i < arrowRight.length; i++) {
    //   arrowRight[i].addEventListener('click', () => {
    //     Lightbox.next()
    //   })
    // }
    const close = document.querySelectorAll('[event-target=lightbox-close]')
    for (let i = 0; i < close.length; i++) {
      close[i].addEventListener('click', () => {
        Lightbox.close()
      })
    }
  },
  nextProject: () => {
    const next = document.getElementById('next-project-link')
    if (next) {
      document.getElementById('page-content').style.display = 'none'
      simulateClick(next)
    }
  },
  previousProject: () => {
    const previous = document.getElementById('previous-project-link')
    if (previous) {
      document.getElementById('page-content').style.display = 'none'
      simulateClick(previous)
    }
  },
  next: () => {
    Lightbox.slider.next()
  },
  previous: () => {
    Lightbox.slider.previous()
  },
  open: () => {
    document.body.classList.add('lightbox-on')
    Lightbox.slider.element.focus()
    Lightbox.videos.forEach(v => {
      v.pause()
      v.currentTime = 0
    })
    const v = Lightbox.slider.selectedElement.querySelector('video')
    if (v) v.play()
    Lightbox.slider.selectedCell.seen = true
    Lightbox.opened = true
  },
  close: () => {
    document.body.classList.remove('lightbox-on')
    window.location.hash = '/'
    // Grid.description.clear()
    Lightbox.opened = false
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
      Search.reset()
      App.closeMenu()
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
      if (Panel.isVisible) {
        Panel.close()
        setTimeout(function() {
          _this.newContainerLoading.then(_this.startTransition.bind(_this))
        }, 400);
      } else {
        _this.newContainerLoading.then(_this.startTransition.bind(_this))
      }
    },
    startTransition: function() {
      document.body.classList.add('is-loading')

      let _this = this
      const newContent = _this.newContainer.querySelector('#page-content')

      // const currentLink = document.querySelector('a.active')
      // if (currentLink) currentLink.classList.remove('active')
      // if (App.linkClicked) App.linkClicked.classList.add('active')

      App.nextPageType = newContent.getAttribute('page-type')
      if (App.nextPageType == 'projects') document.body.classList.remove('lightbox-on')

      document.body.setAttribute('page-type', App.nextPageType)
      _this.endTransition(_this, newContent)


    },
    endTransition: function(_this, newContent) {
      window.scroll(0, 0)

      _this.finish(_this, newContent)

    },
    finish: function(_this, newContent) {

      _this.done()
      App.pageType = App.nextPageType

      App.sizeSet()
      App.interact.init()
      setTimeout(function() {
        document.body.classList.remove('is-loading')
      }, 150);

      if (window.ga) window.ga('send', 'pageview')
    }


  })
}

document.addEventListener("DOMContentLoaded", App.init);