(function () {
  function ready(fn) {
    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', fn, { once: true });
    } else {
      fn();
    }
  }

  ready(function () {
    var menu = document.getElementById('mobile-menu');
    if (!menu) return;

    // Delegate clicks inside the mobile menu
    menu.addEventListener('click', function (e) {
      // Top-level trigger
      var topTrigger = e.target.closest('.nav-trigger');
      if (topTrigger && menu.contains(topTrigger)) {
        e.preventDefault();
        var ul = topTrigger.parentElement.querySelector('.dropdown-panel');
        if (ul) ul.classList.toggle('hidden');
        return;
      }

      // Nested trigger
      var nestedTrigger = e.target.closest('.nested-trigger');
      if (nestedTrigger && menu.contains(nestedTrigger)) {
        e.preventDefault();
        var ul2 = nestedTrigger.parentElement.querySelector('.nested-panel');
        if (ul2) ul2.classList.toggle('hidden');
        return;
      }
    }, { passive: true });
  });
})();
