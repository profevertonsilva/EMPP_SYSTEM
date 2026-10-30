/*
 * English validation messages for every form field.
 *
 * Browsers write the native "required" / "invalid email" messages (the submit
 * bubble and the hover tooltip) in the operating system's language, whatever
 * the page's lang. EMPP's interface is English, so each field carries its own
 * English message through setCustomValidity.
 *
 * A message set by a page script (e.g. "The passwords do not match.") is left
 * alone: only messages written here are replaced.
 */
(function () {
  'use strict';

  var SELECTOR = 'input, select, textarea';

  function englishMessage(el) {
    var v = el.validity;
    if (v.valueMissing) {
      if (el.type === 'checkbox') return 'Please tick this box to continue.';
      if (el.tagName === 'SELECT') return 'Please select an option.';
      if (el.type === 'file') return 'Please choose a file.';
      return 'Please fill in this field.';
    }
    if (v.typeMismatch) {
      if (el.type === 'email') return 'Please enter a valid email address.';
      if (el.type === 'url') return 'Please enter a valid URL.';
      return 'Please enter a valid value.';
    }
    if (v.patternMismatch) return el.title || 'Please match the requested format.';
    if (v.tooShort) return 'Please use at least ' + el.minLength + ' characters.';
    if (v.tooLong) return 'Please use no more than ' + el.maxLength + ' characters.';
    if (v.rangeUnderflow) return 'The value must be ' + el.min + ' or more.';
    if (v.rangeOverflow) return 'The value must be ' + el.max + ' or less.';
    if (v.stepMismatch) return 'Please enter a valid value.';
    if (v.badInput) return 'Please enter a number.';
    return '';
  }

  function refresh(el) {
    if (!el.willValidate) return;
    // Someone else's custom message: keep it
    if (el.validity.customError && el.validationMessage !== el._emppMessage) return;
    el.setCustomValidity('');
    var msg = englishMessage(el);
    el._emppMessage = msg;
    if (msg) el.setCustomValidity(msg);
  }

  function refreshAll(root) {
    Array.prototype.forEach.call((root || document).querySelectorAll(SELECTOR), refresh);
  }

  // After page scripts (capture runs first, so wait a tick) so their own
  // messages are already in place when this one checks
  ['input', 'change'].forEach(function (type) {
    document.addEventListener(type, function (e) {
      if (e.target.matches && e.target.matches(SELECTOR)) {
        setTimeout(function () { refresh(e.target); }, 0);
      }
    }, true);
  });
  // Right before the browser shows its bubble
  document.addEventListener('invalid', function (e) { refresh(e.target); }, true);
  // Fields that hover before anyone types, and fields added later
  document.addEventListener('mouseover', function (e) {
    if (e.target.matches && e.target.matches(SELECTOR)) refresh(e.target);
  }, true);

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function () { refreshAll(); });
  } else {
    refreshAll();
  }
})();
