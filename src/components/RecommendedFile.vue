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
	<a v-tooltip="tooltip"
		class="recommendation"
		tabindex="0"
		@click.prevent="navigate"
		@keyup.enter.prevent="navigate">
		<div class="thumbnail"
			:style="{ 'background-image': 'url(' + previewUrl + ')' }" />
		<div class="details">
			<div class="file-name">
				<template v-if="extension">
					<span class="name">{{ nameWithoutExtension }}</span><!--
				 --><span v-if="extension"
						  class="extension">.{{ extension }}</span>
				</template>
				<template v-else>
					<span class="name">{{ name }}</span>
				</template>

			</div>
			<div class="reason">
				{{ reason }}
			</div>
		</div>
	</a>
</template>

<script>
import { generateUrl } from '@nextcloud/router'
import { VTooltip } from 'v-tooltip'

export default {
	name: 'RecommendedFile',
	directives: {
		tooltip: VTooltip,
	},
	props: {
		id: {
			type: String,
			required: true,
		},
		extension: {
			type: String,
			required: true,
		},
		mimeType: {
			type: String,
			required: true,
		},
		name: {
			type: String,
			required: true,
		},
		directory: {
			type: String,
			required: true,
		},
		reason: {
			type: String,
			required: true,
		},
		hasPreview: {
			type: Boolean,
			default: false,
		},
	},
	data() {
		return {
			previewUrl: OC.MimeType.getIconUrl(this.mimeType),
		}
	},
	computed: {
		nameWithoutExtension() {
			if (this.name.endsWith(this.extension)) {
				return this.name.substring(0, this.name.length - this.extension.length - 1)
			} else {
				return this.name
			}
		},
		isFileListAvailable() {
			return OCA.Files.App.fileList.changeDirectory && OCA.Files.App.fileList.scrollTo
		},
		path() {
			return (this.directory === '/' ? '' : this.directory) + '/' + this.name
		},
		tooltip() {
			return {
				content: this.path,
				html: false,
				placement: 'bottom',
				delay: { show: 500, hide: 0 },
			}
		},
	},
	mounted() {
		if (this.hasPreview) {
			const previewUrl = generateUrl('/core/preview?fileId={fileId}&x=250&y=250', {
				fileId: this.id,
			})
			const img = new Image()
			img.onload = () => {
				this.previewUrl = previewUrl
			}
			img.onerror = err => {
				console.error('could not load recommendation preview', err)
			}
			img.src = previewUrl
		}
	},
	methods: {
		changeDirectory(directory) {
			// This call does not always return a promise, so we
			// wrap it
			return Promise.resolve(OCA.Files.App.fileList.changeDirectory(directory))
		},
		scrollTo(name) {
			OCA.Files.App.fileList.scrollTo(name)
		},
		navigate() {
			if (OCA.Viewer && OCA.Viewer.mimetypes.indexOf(this.mimeType) !== -1) {
				OCA.Viewer.open({ path: this.path })
				return
			}
			if (this.isFileListAvailable) {
				this.changeDirectory(this.directory)
					.then(() => this.scrollTo(this.name))
					.catch(console.error.bind(this))
			} else {
				window.location = generateUrl('/f/' + this.id)
			}
		},
	},
}
</script>

<style scoped lang="scss">
	.recommendation {
		display: flex;
		align-items: center;
		flex-grow: 1;
		min-width: 250px;
		padding: 5px 0;
		margin-right: 12px;
		border-radius: var(--border-radius);

		&:hover,
		&:focus {
			background: var(--color-background-hover);
		}

		&:focus-visible {
			box-shadow: 0 0 0 2px var(--color-primary-element);
		}
	}

	.thumbnail {
		margin-right: 9px;
		margin-left: 10px;
		width: 32px;
		height: 32px;
		background-size: contain;
		flex-shrink: 0;
		border-radius: var(--border-radius);
	}

	.details {
		.file-name {
			white-space: nowrap;
			margin-bottom: -8px;

			.name {
				display: inline-block;
				max-width: 170px;
				color: var(--color-main-text);
				text-overflow: ellipsis;
				overflow: hidden;
			}

			.extension {
				display: inline;
				color: var(--color-text-maxcontrast);
			}
		}

		.reason {
			white-space: nowrap;
			text-overflow: ellipsis;
			overflow: hidden;
			color: var(--color-text-maxcontrast);
		}
	}

	/* show 2 per line for screen sizes smaller that 1200px */
	@media only screen and (max-width: 1200px) {
		.recommendation {
			flex-basis: 50%;
			max-width: calc(50% - 15px);
		}
	}

	/*  GO FULL WIDTH BELOW 480 PIXELS */
	@media only screen and (max-width: 480px) {
		.recommendation {
			flex-basis: 100%;
			min-width: 100%;
		}
	}
</style>
