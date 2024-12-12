/**
 * SPDX-FileCopyrightText: 2022 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */
const path = require('path')
const webpackConfig = require('@nextcloud/webpack-vue-config')
const TerserPlugin = require('terser-webpack-plugin')
const WebpackSPDXPlugin = require('./build-js/WebpackSPDXPlugin.js')
const webpackRules = require('@nextcloud/webpack-vue-config/rules')

webpackConfig.entry.dashboard = path.resolve(path.join('src', 'dashboard.js'))

webpackConfig.optimization.minimizer = [new TerserPlugin({
	extractComments: false,
	terserOptions: {
		format: {
			comments: false,
		},
	},
})]

webpackConfig.plugins = [
	...webpackConfig.plugins,
	// Generate reuse license files
	new WebpackSPDXPlugin({
		override: {
			// TODO: Remove if they fixed the license in the package.json
			'@nextcloud/axios': 'GPL-3.0-or-later',
			'@nextcloud/vue': 'AGPL-3.0-or-later',
			'nextcloud-vue-collections': 'AGPL-3.0-or-later',
		}
	}),
]

module.exports = webpackConfig
