# Changelog

All notable changes to the AskAnina Nextcloud app are documented in this file.
The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/), and this project
adheres to [Semantic Versioning](https://semver.org/).

## Unreleased

### Added
- Loading: the AskAnina logo breathes (softly with reduced motion) until the app appears, in the
  app's light or dark colours, instead of an empty area.
- Dashboard widget "AskAnina Today": how many reminders are overdue and due today, as on the Today
  card; a tap opens AskAnina at Today.

## 0.4.0 - 2026-11-10

### Added
- Dashboard widget "AskAnina Today": the next appointments of today with their times and when to
  leave, worked out in your browser with your own keys (the Nextcloud server never sees them).

## 0.3.1 - 2026-10-22

### Security
- The app's page in the frame is served by the app with a strict Content Security Policy: only its
  own script files run, and only Nextcloud may show it in a frame.

## 0.3.0 - 2026-10-06

### Added
- AskAnina inside Nextcloud: today, calendar, trips, reminders and settings, calculated on the
  AskAnina server; sign in with your AskAnina account.
- Navigation in Nextcloud's left pane, in your language; the admin menu for AskAnina admins.
- Follows Nextcloud's light or dark theme.
