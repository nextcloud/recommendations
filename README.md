<!--
  - SPDX-FileCopyrightText: 2019-2024 Nextcloud GmbH and Nextcloud contributors
  - SPDX-License-Identifier: CC0-1.0
-->
# 🔮 Nextcloud Recommendations

[![REUSE status](https://api.reuse.software/badge/github.com/nextcloud/recommendations)](https://api.reuse.software/info/github.com/nextcloud/recommendations)

The app is in incubation stage, so it’s time for you to [get involved! 👩‍💻](https://github.com/nextcloud/recommendations#development-setup)

## Development setup

1. ☁ Clone the app into the `apps` folder of your Nextcloud: `git clone https://github.com/nextcloud/recommendations.git`
2. 💻 Run `npm i` or `krankerl up` to install the dependencies
3. 🏗 To build the Javascript whenever you make changes, run `npm run dev`
4. ☁ Enable the app through the app management of your Nextcloud or run `krankerl enable`
5. 👍 Partytime! Help fix [some issues](https://github.com/nextcloud/recommendations/issues) and [review pull requests](https://github.com/nextcloud/recommendations/pulls)

Whenever you commit changes to submit a pull request, make sure to build the Javascript using `npm run build` and commit the files `js/*.js` and `js/*.js.map` as well. This needs to be done because the app is shipped with the release as is.
