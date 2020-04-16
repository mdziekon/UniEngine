---
name: Bug report
about: Report a reproducible bug, regression, visual problem or security vulnerability.
title: "[BUG] "
labels: bug, bug:unconfirmed
assignees: mdziekon

---

## Problem description

<!--
  [REQUIRED]
  Please provide a clear and concise description of what the bug is.

  Important note: in cases of detected security vulnerabilities, first consider contacting the authors directly (eg. via email) rather than via Github issues.
-->

## Engine details

<!--
  [REQUIRED]
  Please provide details of your game server's instance.

  Remember: providing your secrets (eg. DB passwords) is never recommended nor required!

  Note: In square brackets, type X where the value matches your scenario.
-->

- Engine version: 
    - [ ] `version X.Y.Z` <!-- (eg. `version 1.0.0`) -->
    - [ ] `commit COMMIT_HASH` <!-- (when using dev version, include the local HEAD commit hash, eg. `commit 854fd9c44f8bb3a476e2b7ce449a06435a42eb03`) -->
- Custom modifications:
    - [ ] Yes
    - [ ] No
- Environment:
    - [ ] local
    - [ ] testing (inaccessible from the outside)
    - [ ] production (accessible from the outside world)
- Configuration:
    - Game speeds
    - Debris settings
    - Any other relevant config entry

## Steps to reproduce

Prerequisites:
- User `X` exists
- User `X` has moon of size `Y`

<!--
  [MOSTLY REQUIRED]
  If applicable, include as many details as possible about any prerequisites necessary to start the reproduction. Such details include engine configuration, initial details of an entity (eg. planet, user, fleet row), or your machine's configuration in cases of performance degradation.
-->

Steps to reproduce the behavior:
1. Go to `...`
2. Click on `....`
3. Scroll down to `....`
4. See error

<!--
  [REQUIRED]
  Your bug will get fixed much faster if we can run your steps right away.
  Issues without reproduction steps, code examples or at least minimum details about the circumstances of the problem may be immediately closed as invalid or impossible to reproduce.
-->

## Expected behavior

<!--
  [REQUIRED]
  Please provide a clear and concise description of what you expected to happen.
-->

## Actual behavior

<!--
  [REQUIRED]
  Please provide a clear and concise description of what actually happened when the provided steps were followed.
-->

## Screenshots

<!--
  [OPTIONAL]
  If applicable, add screenshots to help explain your problem.
-->

## Reproduction environment

<!--
  [OPTIONAL]
  If applicable, provide details related to the problem reproduction environment.
  For example, if the reported bug is related to an element being displayed incorrectly, write down as much information as possible about the used browser.
  If you've noticed that a problem is reproducible on one environment, but not the other, write that down as well.
-->

**Desktop:**
 - OS: <!-- [e.g. Windows 10] -->
 - Browser: <!-- [e.g. Chrome, Firefox] -->
 - Version: <!-- [e.g. 22] -->

**Smartphone:**
 - Device: <!-- [e.g. iPhone6] -->
 - OS: <!-- [e.g. iOS8.1] -->
 - Browser: <!-- [e.g. Stock browser, Safari] -->
 - Version: <!-- [e.g. 22] -->

## Additional context

<!--
  [OPTIONAL]
  Add any other context about the problem here.
-->

## Regression tracking

<!--
  [OPTIONAL]
  If you do happen to know (or suspect) which code fragment or introduced Pull Request causes the issue, let us know about that by providing these details here.
-->

**Caused by PR:**
 - PR number X <!-- [eg. #987654321] -->

**Related code fragment:**

<!--
  Code preview linking template:

  https://github.com/mdziekon/UniEngine/blob/<COMMIT_HASH>/<FILE_RELATIVE_PATH>#L<FRAGMENT_START_LINE>-L<FRAGMENT_END_LINE>
-->
