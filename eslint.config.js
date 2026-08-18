const js = require("@eslint/js");

module.exports = [
  js.configs.recommended,
  {
    files: ["assets/js/**/*.js"],
    languageOptions: {
      ecmaVersion: 2021,
      sourceType: "script",
      globals: {
        window: "readonly",
        document: "readonly",
        jQuery: "readonly",
        gtag: "readonly",
        CustomEvent: "readonly",
        Event: "readonly",
        URLSearchParams: "readonly",
        MutationObserver: "readonly",
        getComputedStyle: "readonly",
        naviCookieConfig: "readonly",
        naviStickyCartI18n: "readonly",
        naviConfig: "readonly",
        console: "readonly",
        setTimeout: "readonly",
        clearTimeout: "readonly",
      },
    },
  },
];
