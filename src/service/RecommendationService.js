/*
 * SPDX-FileCopyrightText: 2018 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */
import Axios from '@nextcloud/axios'
import { generateUrl } from '@nextcloud/router'

export const fetchRecommendedFiles = (always) => {
	const url = generateUrl('/ocs/v2.php/apps/recommendations/api/v1/recommendations' + (always ? '/always' : ''),
		{}, { noRewrite: false })

	return Axios.get(url)
		.then(resp => resp.data.ocs.data)
}
