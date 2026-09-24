/**
 * Decimal inputs: only a point (.) is accepted as the decimal separator.
 *
 * Mark an input with data-decimal (non-negative numbers) or
 * data-decimal="signed" (a leading minus is allowed). A comma is blocked,
 * never converted.
 *
 * After a blocked comma the field stays flagged until the value has a point
 * or is cleared, and its form will not submit: typing "0,03" and carrying on
 * would otherwise leave "003" (a 100x error) in the field.
 */
(function () {
  'use strict';

  var HINT = 'Use a point (.) as the decimal separator, e.g. 0.75';

  function hintFor(input) {
    var host = input.closest('.input-group') || input;
    var hint = host.parentNode.querySelector('.decimal-hint');
    if (!hint) {
      hint = document.createElement('div');
      hint.className = 'decimal-hint small text-danger mt-1 d-none';
      hint.setAttribute('role', 'alert');
      hint.textContent = HINT;
      host.insertAdjacentElement('afterend', hint);
    }
    return hint;
  }

  function flag(input) {
    input._decimalComma = true;
    input.classList.add('is-invalid');
    input.setAttribute('aria-invalid', 'true');
    hintFor(input).classList.remove('d-none');
  }

  function unflag(input) {
    input._decimalComma = false;
    input.classList.remove('is-invalid');
    input.removeAttribute('aria-invalid');
    hintFor(input).classList.add('d-none');
  }

  // Digits, one point and (for signed inputs) a leading minus
  function clean(value, signed) {
    var out = '';
    var seenPoint = false;
    for (var i = 0; i < value.length; i++) {
      var ch = value[i];
      if (ch >= '0' && ch <= '9') {
        out += ch;
      } else if (ch === '.' && !seenPoint) {
        out += ch;
        seenPoint = true;
      } else if (ch === '-' && signed && out === '') {
        out += ch;
      }
    }
    return out;
  }

  function isDecimal(el) {
    return el && el.matches && el.matches('input[data-decimal]');
  }

  document.addEventListener('keydown', function (e) {
    if (isDecimal(e.target) && e.key === ',') {
      e.preventDefault();
      flag(e.target);
    }
  });

  document.addEventListener('paste', function (e) {
    if (!isDecimal(e.target)) return;
    var text = (e.clipboardData || window.clipboardData).getData('text');
    if (text.indexOf(',') !== -1) {
      // Dropping the comma from "0,7" would silently give "07"
      e.preventDefault();
      flag(e.target);
    }
  });

  // Anything else that slips through (mobile keyboards, autofill)
  document.addEventListener('input', function (e) {
    var input = e.target;
    if (!isDecimal(input)) return;
    var signed = input.getAttribute('data-decimal') === 'signed';
    if (input.value.indexOf(',') !== -1) {
      // Undo the comma entirely rather than guess where the digits belong
      input.value = input._decimalLast || '';
      flag(input);
      return;
    }
    var cleaned = clean(input.value, signed);
    if (cleaned !== input.value) input.value = cleaned;
    input._decimalLast = cleaned;
    // The flag clears once the user has written the decimal point or started over
    if (input._decimalComma && (cleaned.indexOf('.') !== -1 || cleaned === '')) unflag(input);
  });

  document.addEventListener('focusin', function (e) {
    if (isDecimal(e.target)) e.target._decimalLast = e.target.value;
  });

  // Capture phase: runs before the pages' own submit handlers
  document.addEventListener('submit', function (e) {
    var flagged = Array.prototype.filter.call(
      e.target.querySelectorAll('input[data-decimal]'),
      function (input) { return input._decimalComma; }
    );
    if (!flagged.length) return;
    e.preventDefault();
    e.stopImmediatePropagation();
    flagged[0].focus();
    var names = flagged.map(function (input) {
      var label = input.id && document.querySelector('label[for="' + input.id + '"]');
      return label ? label.textContent.trim() : input.name;
    });
    var text = 'A comma was typed in: ' + names.join(', ') + '. Re-enter the value using a point (.) as the decimal separator.';
    if (window.Swal) {
      Swal.fire({ title: 'Check the decimal values', text: text, icon: 'warning' });
    } else {
      alert(text);
    }
  }, true);
})();
