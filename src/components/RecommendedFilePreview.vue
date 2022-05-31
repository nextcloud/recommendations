<!--
  - @copyright 2019 Christoph Wurst <christoph@winzerhof-wurst.at>
  -
  - @author 2019 Christoph Wurst <christoph@winzerhof-wurst.at>
  -
  - @license GNU AGPL version 3 or any later version
  -
  - This program is free software: you can redistribute it and/or modify
  - it under the terms of the GNU Affero General Public License as
  - published by the Free Software Foundation, either version 3 of the
  - License, or (at your option) any later version.
  -
  - This program is distributed in the hope that it will be useful,
  - but WITHOUT ANY WARRANTY; without even the implied warranty of
  - MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  - GNU Affero General Public License for more details.
  -
  - You should have received a copy of the GNU Affero General Public License
  - along with this program.  If not, see <http://www.gnu.org/licenses/>.
  -->

<template>
	<div v-if="loading" class="thumbnail icon-loading" />
	<div v-else
		class="thumbnail"
		:style="{ 'background-image': 'url(' + previewUrl + ')' }" />
</template>

<script>
import { generateUrl } from '@nextcloud/router'
import { VTooltip } from 'v-tooltip'

export default {
	name: 'RecommendedFilePreview',
	directives: {
		tooltip: VTooltip,
	},
	props: {
		id: {
			type: String,
			required: true,
		},
		mimeType: {
			type: String,
			required: true,
		},
		hasPreview: {
			type: Boolean,
			default: false,
		},
		size: {
			type: Number,
			default: 44,
		},
	},
	data() {
		return {
			loading: true,
		}
	},
	computed: {
		previewUrl() {
			// if there is a dedicated preview, grab that
			if (this.hasPreview) {
				return generateUrl('/core/preview?fileId={fileId}&x=250&y=250', {
					fileId: this.id,
				})
			}

			// otherwise just grab the generic mime type icon
			return OC.MimeType.getIconUrl(this.mimeType)
		},
	},
	mounted() {
		const img = new Image()
		img.onload = () => {
			this.loading = false
		}
		img.onerror = err => {
			console.error('could not load recommendation preview', err)
		}
		img.src = this.previewUrl
	},
}
</script>

<style scoped lang="scss">
.thumbnail {
	width: 32px;
	height: 32px;
	background-size: contain;
	flex-shrink: 0;
	border-radius: var(--border-radius);
}
</style>
