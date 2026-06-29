(function () {
  function getEndOfDay() {
    var now = new Date();
    var end = new Date(now.getFullYear(), now.getMonth(), now.getDate(), 23, 59, 59);
    return end;
  }

  function pad(n) {
    return n < 10 ? '0' + n : String(n);
  }

  function tick() {
    var now = new Date();
    var end = getEndOfDay();
    var diff = Math.max(0, Math.floor((end - now) / 1000));

    var hours = Math.floor(diff / 3600);
    var minutes = Math.floor((diff % 3600) / 60);
    var seconds = diff % 60;

    var hEl = document.getElementById('flash-sale-hours');
    var mEl = document.getElementById('flash-sale-minutes');
    var sEl = document.getElementById('flash-sale-seconds');

    if (hEl) hEl.textContent = pad(hours);
    if (mEl) mEl.textContent = pad(minutes);
    if (sEl) sEl.textContent = pad(seconds);
  }

  document.addEventListener('DOMContentLoaded', function () {
    tick();
    setInterval(tick, 1000);
  });
})();
