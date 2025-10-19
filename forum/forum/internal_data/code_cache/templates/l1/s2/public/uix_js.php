<?php
// FROM HASH: 30136237d9ac4b7ac2500cd050ecdeb3
return array(
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__templater->includeJs(array(
		'src' => 'themehouse/global/20210125.js',
		'min' => 'true',
	));
	$__finalCompiled .= '
';
	if ($__templater->func('property', array('uix_borderRadiusJs', ), false)) {
		$__finalCompiled .= '
	';
		$__templater->includeJs(array(
			'src' => 'themehouse/' . $__templater->func('property', array('uix_jsPath', ), false) . '/indexRadius.js',
			'min' => 'true',
		));
		$__finalCompiled .= '
	';
	} else {
		$__finalCompiled .= '
	';
		$__templater->includeJs(array(
			'src' => 'themehouse/' . $__templater->func('property', array('uix_jsPath', ), false) . '/index.js',
			'min' => 'true',
		));
		$__finalCompiled .= '
';
	}
	$__finalCompiled .= '

' . $__templater->func('uix_js', array(('themehouse/' . $__templater->func('property', array('uix_jsPath', ), false)) . '/defer.js', true, 'defer', ), true) . '
';
	if ($__templater->func('property', array('uix_navigationType', ), false) == 'sidebarNav') {
		$__finalCompiled .= '
	' . $__templater->func('uix_js', array(('themehouse/' . $__templater->func('property', array('uix_jsPath', ), false)) . '/deferSidebarNav.js', true, 'defer', ), true) . '
';
	}
	$__finalCompiled .= '
';
	if ($__templater->func('property', array('uix_fab', ), false) != 'never') {
		$__finalCompiled .= '
	' . $__templater->func('uix_js', array(('themehouse/' . $__templater->func('property', array('uix_jsPath', ), false)) . '/deferFab.js', true, 'defer', ), true) . '
';
	}
	$__finalCompiled .= '
';
	if ($__templater->func('property', array('uix_categoryCollapse', ), false)) {
		$__finalCompiled .= '
	' . $__templater->func('uix_js', array(('themehouse/' . $__templater->func('property', array('uix_jsPath', ), false)) . '/deferNodesCollapse.js', true, 'defer', ), true) . '
';
	}
	$__finalCompiled .= '
';
	if ($__templater->func('property', array('uix_pageWidthToggle', ), false) != 'disabled') {
		$__finalCompiled .= '
	' . $__templater->func('uix_js', array(('themehouse/' . $__templater->func('property', array('uix_jsPath', ), false)) . '/deferWidthToggle.js', true, 'defer', ), true) . '
';
	}
	$__finalCompiled .= '

';
	$__templater->inlineJs('
	// detect android device. Added to fix the dark pixel bug https://github.com/Audentio/xf2theme-issues/issues/1055

	var ua = navigator.userAgent.toLowerCase();
	var isAndroid = ua.indexOf("android") > -1; //&& ua.indexOf("mobile");

	if(isAndroid) {
	document.querySelector("html").classList.add("device--isAndroid");
	}	
', true);
	$__finalCompiled .= '

';
	if ($__templater->func('property', array('uix_clickableThreads', ), false)) {
		$__finalCompiled .= '
	';
		$__templater->inlineJs('
			var threadSelector = document.querySelector(\'.structItem--thread\') !== null;
			
			if (threadSelector) {
				document.querySelector(\'.structItem--thread\').addEventListener(\'click\', (e) => {
					var target = e.target;
					var skip = [\'a\', \'i\', \'input\', \'label\'];
					if (target && skip.indexOf(target.tagName.toLowerCase()) === -1) {
						var href = this.querySelector(\'.structItem-title\').getAttribute(\'uix-href\');
						if (e.metaKey || e.cmdKey) {
							e.preventDefault();
							window.open(href, \'_blank\');
						} else {
							window.location = href;
						}
					}
				});
			}
	', true);
		$__finalCompiled .= '
';
	}
	$__finalCompiled .= '

';
	if ($__templater->func('property', array('uix_sidebarMobileCanvas', ), false)) {
		$__finalCompiled .= '
	';
		$__templater->inlineJs('
		
			var sidebar = document.querySelector(\'.p-body-sidebar\');
			var backdrop = document.querySelector(\'.p-body-sidebar [data-ocm-class="offCanvasMenu-backdrop"]\');
		
			var hasSidebar = document.querySelector(\'.p-body-sidebar\') !== null;
			var hasBackdrop = document.querySelector(\'.p-body-sidebar [data-ocm-class="offCanvasMenu-backdrop"]\') !== null;
			var sidebarTrigger = document.querySelector(\'.uix_sidebarCanvasTrigger\') !== null;
			var sidebarInner = document.querySelector(\'.uix_sidebarCanvasTrigger\') !== null;
			
			if (sidebarTrigger) {
				document.querySelector(\'.uix_sidebarCanvasTrigger\').addEventListener("click", (e) => {
					e.preventDefault();

					sidebar.style.display = "block";;
					window.setTimeout(function() {
						sidebar.classList.add(\'offCanvasMenu\');
						sidebar.classList.add(\'offCanvasMenu--blocks\');
						sidebar.classList.add(\'is-active\');
						sidebar.classList.add(\'is-transitioning\');
						document.querySelector(\'body\').classList.add(\'sideNav--open\');
					}, 50);
		
					if (hasSidebar) {
						window.setTimeout(function() {
							sidebar.classList.remove(\'is-transitioning\');
						}, 250);
					}
		
					if (sidebarInner) {
						document.querySelector(\'.uix_sidebarInner\').classList.add(\'offCanvasMenu-content\');
						backdrop.classList.add(\'offCanvasMenu-backdrop\');
						document.querySelector(\'body\').classList.add(\'is-modalOpen\');
					}
				})
			}
			if (hasBackdrop) {
				backdrop.addEventListener("click", (e) => {
					sidebar.classList.add(\'is-transitioning\');
					sidebar.classList.remove(\'is-active\');

					window.setTimeout(function() {
						sidebar.classList.remove(\'offCanvasMenu\');
						sidebar.classList.remove(\'offCanvasMenu--blocks\');
						sidebar.classList.remove(\'is-transitioning\');
						document.querySelector(\'.uix_sidebarInner\').classList.remove(\'offCanvasMenu-content\');
						backdrop.classList.remove(\'offCanvasMenu-backdrop\');
						document.querySelector(\'body\').classList.remove(\'is-modalOpen\');
						sidebar.style.display="";
					}, 250);
				});
			}
		
	', true);
		$__finalCompiled .= '
';
	}
	$__finalCompiled .= '

';
	$__templater->inlineJs('
	/****** OFF CANVAS ******/

    var panels = {
        navigation: {
            position: 1,
        },
        account: {
            position: 2,
        },
        inbox: {
            position: 3,
        },
        alerts: {
            position: 4,
        },
    };

    var tabsContainer = document.querySelector(".sidePanel__tabs");

    var activeTab = "navigation";

    var activeTabPosition = panels[activeTab].position;

    function generateDirections() {
        var tabPanels = document.querySelectorAll(".sidePanel__tabPanel");
        tabPanels.forEach(function (tabPanel) {
            var tabPosition = tabPanel.getAttribute("data-content");
            var activeTabPosition = panels[activeTab].position;

            if (tabPosition != activeTab) {
                if (panels[tabPosition].position < activeTabPosition) {
                    tabPanel.classList.add("is-left");
                }

                if (panels[tabPosition].position > activeTabPosition) {
                    tabPanel.classList.add("is-right");
                }
            }
        });
    }

    generateDirections();

	if (tabsContainer) {
		var sidePanelTabs = tabsContainer.querySelectorAll(".sidePanel__tab");
	}
	
	if (sidePanelTabs && sidePanelTabs.length > 0) {
		sidePanelTabs.forEach(function (tab) {
			tab.addEventListener("click", function () {
				sidePanelTabs.forEach(function (tab) {
					tab.classList.remove("sidePanel__tab--active");
				});
				this.classList.add("sidePanel__tab--active");

				activeTab = this.getAttribute("data-attr");

				var tabPanels = document.querySelectorAll(".sidePanel__tabPanel");
				tabPanels.forEach(function (tabPanel) {
					tabPanel.classList.remove("is-active");
				});

				var activeTabPanel = document.querySelector(
					\'.sidePanel__tabPanel[data-content="\' + activeTab + \'"]\'
				);
				activeTabPanel.classList.add("is-active");

				var tabPanels = document.querySelectorAll(".sidePanel__tabPanel");
				tabPanels.forEach(function (tabPanel) {
					tabPanel.classList.remove("is-left", "is-right");
				});

				generateDirections();
			});
		});
	}
	', true);
	$__finalCompiled .= '
	';
	$__templater->inlineJs('

	/******** extra info post toggle ***********/
	
    XF.thThreadsUserExtraTrigger = {
        eventNameSpace: \'XFthThreadsUserExtraTrigger\',

        init: function(e) {},

        click: function(e) {
            var target = e.target;
            var parent = target.closest(\'.message-user\');
            var triggerContainer = target.parentElement.closest(\'.thThreads__userExtra--toggle\');
            var container = triggerContainer.previousElementSibling;
            var child = container.querySelector(\'.message-userExtras\');
            var eleHeight = child.offsetHeight;

            if (parent.classList.contains(\'userExtra--expand\')) {
                container.style.height = eleHeight + \'px\';
                parent.classList.toggle(\'userExtra--expand\');
                setTimeout(function() {
                    container.style.height = \'0\';
                    setTimeout(function() {
                        container.style.height = \'\';
                    }, 200);
                }, 17);
            } else {
                container.style.height = eleHeight + \'px\';
                setTimeout(function() {
                    parent.classList.toggle(\'userExtra--expand\');
                    container.style.height = \'\';
                }, 200);
            }
        }
    };

    document.body.addEventListener(\'click\', function(event) {
        var target = event.target;
        if (target.matches(\'.thThreads__userExtra--trigger\')) {
            XF.thThreadsUserExtraTrigger.click(event);
        }
    });
	
	', true);
	$__finalCompiled .= '
	';
	$__compilerTemp1 = '';
	if ($__templater->func('property', array('uix_backstretch', ), false)) {
		$__compilerTemp1 .= '
		if ( ' . $__templater->func('property', array('uix_backstretch', ), false) . ' ) {

		$("' . $__templater->func('property', array('uix_backstretchSelector', ), false) . '").addClass(\'uix__hasBackstretch\');

		$("' . $__templater->func('property', array('uix_backstretchSelector', ), false) . '").backstretch([
			' . $__templater->func('property', array('uix_backstretchImages', ), false) . '
		], {
		duration: ' . $__templater->func('property', array('uix_backstretchDuration', ), false) . ',
		fade: ' . $__templater->func('property', array('uix_backstretchFade', ), false) . '
		});

		$("' . $__templater->func('property', array('uix_backstretchSelector', ), false) . '").css("zIndex","");
		}
		';
	}
	$__templater->inlineJs('

	/******** Backstretch images ***********/

		' . $__compilerTemp1 . '

', true);
	$__finalCompiled .= '
	';
	$__templater->inlineJs('

	// sidenav canvas blur fix

    document.querySelectorAll(\'.p-body-sideNavTrigger .button\').forEach(function (button) {
        button.addEventListener(\'click\', function () {
            document.body.classList.add(\'sideNav--open\');
        });
    });

    document.querySelectorAll("[data-ocm-class=\'offCanvasMenu-backdrop\']").forEach(function (backdrop) {
        backdrop.addEventListener(\'click\', function () {
            document.body.classList.remove(\'sideNav--open\');
        });
    });

    document.addEventListener(\'editor:start\', function (event) {
        if (typeof event !== \'undefined\' && typeof event.target !== \'undefined\') {
            var ele = event.target;
            if (event?.target) {
				var ele = event.target;
				if (!ele.classList==undefined) {
					if (ele.classList.contains(\'js-editor\')) {
						var wrapper = ele.closest(\'.message-editorWrapper\');
						if (wrapper) {
							setTimeout(function () {
								var innerEle = wrapper.querySelector(\'.fr-element\');
								if (innerEle) {
									innerEle.addEventListener(\'focus\', function (e) {
										document.documentElement.classList.add(\'uix_editor--focused\');
									});
									innerEle.addEventListener(\'blur\', function (e) {
										document.documentElement.classList.remove(\'uix_editor--focused\');
									});
								}
							}, 0);
						}
					}
				}
			}
        }
    });
', true);
	$__finalCompiled .= '
	';
	$__templater->inlineJs('
	// off canvas menu closer keyboard shortcut
    document.body.addEventListener(\'keyup\', function (e) {
        switch (e.key) {
            case \'Escape\':
                var offCanvasMenu = document.querySelector(\'.offCanvasMenu.is-active\');
                if (offCanvasMenu) {
                    var backdrop = offCanvasMenu.querySelector(\'.offCanvasMenu-backdrop\');
                    if (backdrop) {
                        backdrop.click();
                    }
                }
                return;
        }
    });
	', true);
	$__finalCompiled .= '

	';
	if ($__templater->func('property', array('uix_parallax', ), false)) {
		$__finalCompiled .= '
		';
		$__templater->inlineJs('
			var parallaxSelector = "' . $__templater->func('property', array('uix_parallaxSelector', ), false) . '"
			var parallaxImage = "' . $__templater->func('base_url', array($__templater->func('property', array('uix_parallaxImage', ), false), ), false) . '"
			var parallaxPosition = "' . $__templater->func('property', array('uix_parallaxPosition', ), false) . '"
			$(parallaxSelector).parallax({imageSrc: parallaxImage, positionY: parallaxPosition});
		', true);
		$__finalCompiled .= '
	';
	}
	$__finalCompiled .= '

	';
	$__templater->inlineJs('
    let uixMegaHovered = false;
    const navEls = document.querySelectorAll(\'.uix-navEl--hasMegaMenu\');
    const pNav = document.querySelector(\'.p-nav\');
    let menu;

    function handleMouseOver() {
        if (uixMegaHovered) {
            menu = this.getAttribute(\'data-nav-id\');
            pNav.classList.add(\'uix_showMegaMenu\');

            document.querySelectorAll(\'.uix_megaMenu__content\').forEach(function (content) {
                content.classList.remove(\'uix_megaMenu__content--active\');
            });

            document
                .querySelector(\'.uix_megaMenu__content--\' + menu)
                .classList.add(\'uix_megaMenu__content--active\');
        }
    }

    function handleMouseEnter() {
        uixMegaHovered = true;
    }

    function handleMouseLeave() {
        pNav.classList.remove(\'uix_showMegaMenu\');
        uixMegaHovered = false;
    }

    navEls.forEach(function (navEl) {
        navEl.addEventListener(\'mouseover\', handleMouseOver);
    });

    pNav.addEventListener(\'mouseenter\', handleMouseEnter);
    pNav.addEventListener(\'mouseleave\', handleMouseLeave);
	', true);
	$__finalCompiled .= '

	';
	if ($__templater->func('property', array('uix_abridgedSignatures', ), false)) {
		$__finalCompiled .= '
		';
		$__compilerTemp2 = '';
		if ($__templater->func('property', array('uix_signatureHoverEnabled', ), false)) {
			$__compilerTemp2 .= '
      var expandableSignatures = document.querySelectorAll(\'.message-signature--expandable\');

      expandableSignatures.forEach(function(signature) {
        signature.addEventListener(\'mouseenter\', function() {
          expand(signature, false);
        });
      });
    ';
		}
		$__templater->inlineJs('
/******** signature collapse toggle ***********/
  setTimeout(function() {
    var maxHeight = ' . $__templater->func('property', array('uix_signatureMaxHeight', ), false) . ';

    var signatures = document.querySelectorAll(\'.message-signature\');

    signatures.forEach(function(signature) {
      var wrapper = signature.querySelector(\'.bbWrapper\');
      if (wrapper) {
        var height = wrapper.clientHeight;
        if (height > maxHeight) {
          signature.classList.add(\'message-signature--expandable\');
        }
      }
    });

    /*** expand function ***/
    function expand(container, canClose) {
      var inner = container.querySelector(\'.bbWrapper\');
      var eleHeight = inner ? inner.clientHeight : 0;
      var isExpanded = container.classList.contains(\'message-signature--expanded\');

      if (isExpanded) {
        if (canClose) {
          container.style.height = eleHeight + \'px\';
          container.classList.remove(\'message-signature--expanded\');
          setTimeout(function() {
            container.style.height = maxHeight + \'px\';
            setTimeout(function() {
              container.style.height = \'\';
            }, 200);
          }, 17);
        }
      } else {
        container.style.height = eleHeight + \'px\';
        setTimeout(function() {
          container.classList.add(\'message-signature--expanded\');
          container.style.height = \'\';
        }, 200);
      }
    }

    var hash = window.location.hash;
    if (hash && hash.indexOf(\'#\') === 0) {
      var replacedHash = hash.replace(\'#\', \'\');
      var ele = document.getElementById(replacedHash);
      if (ele) {
        ele.scrollIntoView();
      }
    }

    /*** handle hover ***/
    ' . $__compilerTemp2 . '

    /*** handle click ***/
    var signatureExpandButtons = document.querySelectorAll(\'.uix_signatureExpand\');

    signatureExpandButtons.forEach(function(button) {
      button.addEventListener(\'click\', function() {
        var container = button.closest(\'.message-signature\');
        expand(container, true);
      });
    });
  }, 0);
		', true);
		$__finalCompiled .= '
	';
	}
	$__finalCompiled .= '

';
	if ($__templater->func('property', array('uix_lazyLoadSupport', ), false)) {
		$__finalCompiled .= '
	';
		$__templater->inlineJs('
		var lazyloadImages;    

		if ("IntersectionObserver" in window) {
		lazyloadImages = document.querySelectorAll(".lazy");
		var imageObserver = new IntersectionObserver(function(entries, observer) {
		entries.forEach(function(entry) {
		if (entry.isIntersecting) {
		var image = entry.target;
		image.src = image.dataset.src;
		image.classList.remove("lazy");
		imageObserver.unobserve(image);
		}
		});
		});

		lazyloadImages.forEach(function(image) {
		imageObserver.observe(image);
		});
		document.addEventListener(\'xf:reinit\', function() {
			document.querySelectorAll(".lazy").forEach(function(image) {
				imageObserver.observe(image);
			});
		});
		} else {  
		var lazyloadThrottleTimeout;
		lazyloadImages = document.querySelectorAll(".lazy");

		function lazyload () {
		if(lazyloadThrottleTimeout) {
		clearTimeout(lazyloadThrottleTimeout);
		}    

		lazyloadThrottleTimeout = setTimeout(function() {
		var scrollTop = window.pageYOffset;
		lazyloadImages.forEach(function(img) {
		if(img.offsetTop < (window.innerHeight + scrollTop)) {
											  img.src = img.dataset.src;
											  img.classList.remove(\'lazy\');
											  }
											  });
											  if(lazyloadImages.length == 0) { 
											  document.removeEventListener("scroll", lazyload);
											  window.removeEventListener("resize", lazyload);
											  window.removeEventListener("orientationChange", lazyload);
											  }
											  }, 20);
											  }

											  document.addEventListener("scroll", lazyload);
											  window.addEventListener("resize", lazyload);
											  window.addEventListener("orientationChange", lazyload);
											  }
											  ', true);
		$__finalCompiled .= '
		';
	}
	$__finalCompiled .= '

	';
	if (($__templater->func('property', array('uix_dropdownHover', ), false) AND ($__templater->func('property', array('uix_navigationType', ), false) == 'default'))) {
		$__finalCompiled .= '

		';
		$__templater->inlineJs('
	var menuActive = false;
	var closeTrigger = \'a[data-xf-click="menu"]\';
	var menuId = \'\';
	var navElements = document.querySelectorAll(\'.p-navEl\');

	var simulateClick = function(elem) {
		// Create our event (with options)
		var evt = new MouseEvent(\'click\', {
			bubbles: true,
			cancelable: true,
			view: window
		});
		// If cancelled, don\'t dispatch our event
		var canceled = !elem.dispatchEvent(evt);
	};

	var opts = {
		timeout: 200
	};

	navElements.forEach((el, i) => {
		hoverintent(el, function() {
			//Hover in
	
			if (!menuActive) {
				simulateClick(this.querySelector(closeTrigger));
			}
	
			menuActive = true;
			menuId = this.querySelector(closeTrigger).getAttribute(\'aria-controls\');
			this.querySelector(closeTrigger).focus();
		}, function() {
			//Hover out
	
			function closeMenu(menuId) {
				document.addEventListener("mousemove", function () {
					if (!$(\'#\' + menuId + \':hover\').length && !$(\'a[aria-controls="\' + menuId + \'"]:hover\').length) {
						simulateClick(this.querySelector(\'a.is-menuOpen[aria-controls="\' + menuId + \'"]\'));
						menuActive = false;
						menuId = \'\';
						document.removeEventListener(\'mousemove\');
					}
				});
			}
			closeMenu.bind(this, menuId)();
		}).options(opts);
	});
		', true);
		$__finalCompiled .= '

	';
	}
	$__finalCompiled .= '

	';
	$__templater->inlineJs('
			setTimeout(function() {
				var doc = document.querySelector(\'html\');
				editor = XF.getEditorInContainer(doc);
				if (!!editor && !!editor.ed) {
					editor.ed.events.on(\'focus\', function() {
						if (document.querySelector(\'.uix_fabBar\')) {
							var fabBar = document.querySelector(\'.uix_fabBar\');
							fabBar.style.display = \'none\';
						}
						
					});
					editor.ed.events.on(\'blur\', function() {
						if (document.querySelector(\'.uix_fabBar\')) {
							var fabBar = document.querySelector(\'.uix_fabBar\');
							fabBar.style.display = \'\';
						}
					});
				}
			}, 100);
	', true);
	$__finalCompiled .= '

	';
	$__templater->inlineJs('
document.addEventListener(\'ajax:complete\', function(e) {
		
	if (typeof e.detail == \'undefined\') {
		return;
		}
    var xhr = e.detail[0];
    var status = e.detail[1];

    var data = xhr.responseJSON;
    if (!data) {
        return;
    }
    if (data.visitor) {
        var totalUnreadBadge = document.querySelector(\'.js-uix_badge--totalUnread\');
        if (totalUnreadBadge) {
            totalUnreadBadge.dataset.badge = data.visitor.total_unread;
        }
    }
});
	', true);
	return $__finalCompiled;
}
);