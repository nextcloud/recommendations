/*
 * SPDX-FileCopyrightText: 2018 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */
import Axios from '@nextcloud/axios'
import { generateOcsUrl } from '@nextcloud/router'

export const fetchRecommendedFiles = (always) => {
	const url = generateOcsUrl('apps/recommendations/api/v1/recommendations' + (always ? '/always' : ''))

	return Axios.get(url)
		.then(resp => resp.data.ocs.data)
}
