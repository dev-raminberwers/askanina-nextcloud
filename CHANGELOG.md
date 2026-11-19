# Changelog

All notable changes to the AskAnina Nextcloud app are documented in this file.
The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/), and this project
adheres to [Semantic Versioning](https://semver.org/).

## 0.4.5 - 2026-11-19

### Changed
- Also listed in the App Store categories Dashboard (the AskAnina Today widget) and AI (Anina).

## 0.4.4 - 2026-11-18

### Changed
- App Store screenshots taken in Nextcloud (the first one is the App Store card's picture), with
  demo data only.
- The description links to a 1-minute video of AskAnina inside Nextcloud.

## 0.4.3 - 2026-11-18

### Changed
- The app icon is a vector drawing of Anina (the App Store showed a gear: it ignores pictures inside an SVG).
- The App Store description says what AskAnina does for you.

## 0.4.2 - 2026-11-18

### Fixed
- The App Store frame works when Nextcloud is on a private or local address (Chrome's Local
  Network Access no longer blocks it): the web app no longer fetches the shell's check route;
  the shell's welcome message names the app origin instead.
- A page that frames the AskAnina embed page without being the Nextcloud shell gets only an
  "Open AskAnina" link, not the sign-in.

## 0.4.1 - 2026-11-17

### Changed
- The App Store version is now a thin frame: it shows the AskAnina web app from its own address
  (https://app.askanina.com) inside Nextcloud and holds none of its code. The frame and Nextcloud
  only talk through checked messages; no Nextcloud password or session is passed to AskAnina.
  Admins can point the frame elsewhere with `occ config:app:set askanina frame_url --value=…`.
- This app (the frame) is Apache-2.0; the AskAnina service it shows is proprietary
  (© Ramin Berwers. All rights reserved.).
- Licence: Apache-2.0 (was AGPL-3.0-or-later).
- App Store details: bug reports go to https://askanina.com/?p=support; the source is public at
  https://github.com/dev-raminberwers/askanina-nextcloud.

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
