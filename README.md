<!--
  - SPDX-FileCopyrightText: 2019-2024 Nextcloud GmbH and Nextcloud contributors
  - SPDX-License-Identifier: CC0-1.0
-->
# 🔮 Nextcloud Recommendations

[![REUSE status](https://api.reuse.software/badge/github.com/nextcloud/recommendations)](https://api.reuse.software/info/github.com/nextcloud/recommendations)

The app is in incubation stage, so it’s time for you to [get involved! 👩‍💻](https://github.com/nextcloud/recommendations#development-setup)

## How files are ranked

Each source suggests up to 7 files, and every reason a file is suggested for adds its weight to the file's score. That weight is halved for every week the reason is old. How often the user opened a file adds the frequently opened weight, scaled against their most opened file and halved for every 30 days since its last open. A file needs at least 2 opens to count as frequently opened, and opens are kept for 30 days.

| Reason | App config key | Default |
|---|---|---|
| Recently opened | `weight_recently_opened` | 0.3 |
| Frequently opened | `weight_frequently_opened` | 0.2 |
| Favorite | `weight_favorite` | 0.2 |
| Recently edited | `weight_recently_edited` | 0.1 |
| Recently shared | `weight_recently_shared` | 0.1 |
| Recently commented | `weight_recently_commented` | 0.1 |

Change a weight with `occ config:app:set recommendations weight_favorite --value 0.3 --type float`. A weight of 0 makes a reason count for nothing in the ranking, and negative values count as 0.

## Development setup

1. ☁ Clone the app into the `apps` folder of your Nextcloud: `git clone https://github.com/nextcloud/recommendations.git`
2. 💻 Run `npm i` or `krankerl up` to install the dependencies
3. 🏗 To build the Javascript whenever you make changes, run `npm run dev`
4. ☁ Enable the app through the app management of your Nextcloud or run `krankerl enable`
5. 👍 Partytime! Help fix [some issues](https://github.com/nextcloud/recommendations/issues) and [review pull requests](https://github.com/nextcloud/recommendations/pulls)

Whenever you commit changes to submit a pull request, make sure to build the Javascript using `npm run build` and commit the files `js/*.js` and `js/*.js.map` as well. This needs to be done because the app is shipped with the release as is.
