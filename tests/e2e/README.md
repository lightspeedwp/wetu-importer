# E2E Tests for WETU Importer

This directory contains end-to-end tests using Playwright and WordPress e2e test utils.

## Setup

```bash
# Install Playwright browsers
npx playwright install
```

## Running Tests

```bash
# Run all e2e tests
npm run test:e2e

# Run tests in headed mode (see browser)
npm run test:e2e -- --headed

# Run specific test file
npm run test:e2e tests/e2e/import-flow.spec.js
```

## Writing Tests

Use WordPress e2e test utilities:
```javascript
import { test, expect } from '@playwright/test';
import { Admin, Editor } from '@wordpress/e2e-test-utils-playwright';

test('WETU import flow', async ({ page }) => {
  const admin = new Admin({ page });
  await admin.visitAdminPage('admin.php?page=wetu-importer');
  // Test implementation
});
```
