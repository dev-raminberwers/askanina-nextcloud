# AskAnina for Nextcloud

AskAnina inside Nextcloud: this app shows the AskAnina web app in Nextcloud's own page, with
Nextcloud's left menu and an "AskAnina Today" widget on the Dashboard.

## What this app is

* A thin frame: the AskAnina web app is loaded from its own address (by default
  `https://app.askanina.com/embed.html`) in a frame. This app contains none of AskAnina's code.
* The frame is another origin than your Nextcloud. The two pages only talk by `postMessage`, and
  each checks the other's origin and window. This app never passes your Nextcloud session or a
  password to AskAnina.
* The framed app checks that it really runs in this app: it asks `/apps/askanina/check` on your
  Nextcloud (no cookies) whether this Nextcloud frames exactly its origin.
* Sign in inside the frame with your AskAnina account. To the AskAnina service the frame is a
  device of its own, as a browser is.

## Settings (admins, optional)

The frame's address comes with the app. To point it elsewhere (for example a test address):

    occ config:app:set askanina frame_url --value=https://app.example.com/embed.html

Only `https` addresses are accepted (plain `http` only for `localhost`).

## Licence

This app (the frame, the files in this archive) is open source under the Apache License 2.0
(`LICENSE`).

The AskAnina service and the AskAnina web app it shows are **not** part of this app and are
proprietary: © Ramin Berwers. All rights reserved. Using them is subject to the terms at
https://askanina.com.
