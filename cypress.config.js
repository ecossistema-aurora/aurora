const { defineConfig } = require("cypress");
require('dotenv').config();

module.exports = defineConfig({
  viewportWidth: 1920,
  viewportHeight: 1080,
  e2e: {
    supportFile : false,
    baseUrl: process.env.CYPRESS_BASE_URL,
    chromeWebSecurity: false,
    setupNodeEvents(on, config) {
    },
    specPattern: [
      'cypress/regmel/e2e/web/register/*.cy.js',
      'cypress/regmel/e2e/web/user/*.cy.js',
      'cypress/regmel/e2e/web/company/company-proposal.cy.js',
      'cypress/regmel/e2e/web/proposal/admin-proposal-list.cy.js',
      'cypress/regmel/e2e/**/*.cy.js',
    ],
  },
});
