# JurisLocator Updates

This repository contains updates to the JurisLocator application that fix the legal document reference popup system.

## Changes

- Updated `ViewLegalTableController.php` to use jsonResponse() for all API responses
- Added new API routes in `routes/web.php` for section content, references, and annotations
- Created `JsonAuthenticate.php` middleware for consistent JSON error responses
- Updated `legal-reference-popups.js` to use the new Laravel API endpoints
- Updated `script.js` to remove all fetch_reference.php calls and use the new Laravel API endpoints

These changes ensure that all reference/content/annotation fetches use new Laravel API routes (not legacy PHP endpoints), always return valid JSON, and handle errors (including authentication) gracefully.
