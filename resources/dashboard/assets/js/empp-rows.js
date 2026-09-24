/**
 * Clickable table rows: <tr data-href="/url"> opens the URL when the row is
 * clicked anywhere. Links, buttons and form fields inside the row keep their
 * own behaviour. Ctrl/Cmd/Shift + click and middle click open a new tab, like
 * a link; Enter opens the row when it has keyboard focus (tabindex="0").
 */
(function () {
  'use strict';

  var INTERACTIVE = 'a, button, input, select, textarea, label, [role="button"]';

  function rowFrom(e) {
    var row = e.target.closest && e.target.closest('tr[data-href]');
    if (!row || e.target.closest(INTERACTIVE)) return null;
    // Leave text selection alone (dragging over a description to copy it)
    var sel = window.getSelection && window.getSelection();
    if (sel && sel.toString().length && row.contains(sel.anchorNode)) return null;
    return row;
  }

  function open(row, newTab) {
    var url = row.getAttribute('data-href');
    if (!url) return;
    if (newTab) {
      window.open(url, '_blank', 'noopener');
    } else {
      window.location.href = url;
    }
  }

  document.addEventListener('click', function (e) {
    var row = rowFrom(e);
    if (row) open(row, e.ctrlKey || e.metaKey || e.shiftKey);
  });

  // Middle button
  document.addEventListener('auxclick', function (e) {
    if (e.button !== 1) return;
    var row = rowFrom(e);
    if (row) {
      e.preventDefault();
      open(row, true);
    }
  });

  document.addEventListener('keydown', function (e) {
    if (e.key !== 'Enter') return;
    var row = e.target.matches && e.target.matches('tr[data-href]') ? e.target : null;
    if (row) open(row, e.ctrlKey || e.metaKey);
  });
})();
