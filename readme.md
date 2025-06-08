# Personal Rotation

WordPress Plugin for spaced repetition of a user's own posts, for an exercise routine or learning.

## Overview

Logged-in users author their own posts and see only their own posts. Each post can be a chunk of material that
they would like to learn, almost like a flashcard. Each post could also be a detailed description of an exercise
that, once completed, they would like to pull out of the rotation for a few days.

When a user is done reviewing one of their posts, they can use one of several buttons, that this plugin adds to
the top of the post, to indicate how long they would like it to be before seeing that post again.

The skip button leaves the post in the rotation and randomly chooses the next post. The "1 Day" button prevents
that post from being randomly selected until the next day. The "3 Days" and "10 Days" buttons behave similarly.

This plugin makes the most sense when provided with a dedicated WordPress site.

## Internationalization

Translations are available for en-US and es-MX.

```
wp i18n make-pot . languages/personal-rotation.pot
wp i18n make-mo .
```

## Getting Started

This project was developed and tested against PHP v8.2.28 and WordPress v6.8.1. It has no dependencies other
than WordPress itself. Data is persisted as post metadata. Simply install and activate.

## Packaging

The project can be packaged by running the `./build.sh` script.

