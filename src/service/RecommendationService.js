/*
 * SPDX-FileCopyrightText: 2018 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */
import Axios from '@nextcloud/axios'
import { generateOcsUrl } from '@nextcloud/router'

/**
 * Fetch the recommended files of the current user
 *
 * @param {boolean} always Return recommendations even if the user disabled them
 */
export function fetchRecommendedFiles(always) {
	const url = generateOcsUrl('apps/recommendations/api/v1/recommendations' + (always ? '/always' : ''))

	return Axios.get(url)
		.then((resp) => resp.data.ocs.data)
}
