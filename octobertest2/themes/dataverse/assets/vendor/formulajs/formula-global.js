// Force Formula.js to export a usable browser global
(function() {
  try {
    if (typeof window.formula === 'undefined') {
      // Try all known names and module exports
      if (typeof window.FormulaJS !== 'undefined') {
        window.formula = window.FormulaJS;
      } else if (typeof window.formulaJS !== 'undefined') {
        window.formula = window.formulaJS;
      } else if (typeof module !== 'undefined' && module.exports) {
        window.formula = module.exports;
      } else if (typeof this !== 'undefined' && this.formula) {
        window.formula = this.formula;
      } else {
        console.warn("Formula.js global not found; creating empty stub.");
        window.formula = { eval: function() { return 0; } };
      }
    }
    console.log("Formula.js global binding:", typeof window.formula);
  } catch (err) {
    console.error("Formula.js global shim failed:", err);
  }
})();
