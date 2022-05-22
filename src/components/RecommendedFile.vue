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
	<div class="recommendation-container">
		<a v-tooltip="tooltip"
			class="recommendation"
			tabindex="0"
			@click.prevent="navigate"
			@keyup.enter.prevent="navigate">
			<RecommendedFilePreview
				:id="id"
				:has-preview="hasPreview"
				:mime-type="mimeType" />
			<div class="details">
				<div class="file-name">
					<template v-if="extension">
						<span class="name">{{ nameWithoutExtension }}</span>
						<span v-if="extension"
							class="extension">.{{ extension }}</span>
					</template>
					<template v-else>
						<span class="name">{{ name }}</span>
					</template>

				</div>
				<div v-if="reason" class="reason">
					{{ reason }}
				</div>
			</div>
		</a>

		<Actions menu-align="right">
			<ActionLink
				key="openInNewTab"
				icon="icon-external-dark"
				:close-after-click="true"
				target="_blank"
				:href="directFileUrl">
				{{ t('recommendations', 'Open in new tab') }}
			</ActionLink>
			<ActionButton
				key="showInFiles"
				icon="icon-files-dark"
				:close-after-click="true"
				@click.prevent.stop="navigateToFile">
				{{ t('recommendations', 'Show in files') }}
			</ActionButton>
		</Actions>
	</div>
</template>

<script>
import { generateUrl } from '@nextcloud/router'
import { VTooltip } from 'v-tooltip'
import RecommendedFilePreview from './RecommendedFilePreview'
import Actions from '@nextcloud/vue/dist/Components/Actions'
import ActionButton from '@nextcloud/vue/dist/Components/ActionButton'
import ActionLink from '@nextcloud/vue/dist/Components/ActionLink'

export default {
	name: 'RecommendedFile',
	components: { Actions, ActionButton, ActionLink, RecommendedFilePreview },
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
			required: false,
			default: undefined,
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
		directFileUrl() {
			return generateUrl('/f/' + this.id)
		},
		fileUrl() {
			return generateUrl('/apps/files/?fileid=' + this.id)
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
		navigateToFile() {
			if (this.isFileListAvailable) {
				this.changeDirectory(this.directory)
					.then(() => this.scrollTo(this.name))
					.catch(console.error.bind(this))
			} else {
				window.location.href = this.fileUrl
			}
		},
	},
}
</script>

<style scoped lang="scss">
	.recommendation-container {
		display: flex;
		align-items: center;
		min-width: 250px;
		padding: 5px 0 5px 5px;
		border-radius: var(--border-radius);

		&:hover,
		&:focus {
			background: var(--color-background-hover);
		}
	}

	.recommendation {
		// 49 = padding recommendation-container (left 5) + action button (44)
		width: calc(100% - 49px);
		display: flex;
		flex-grow: 1;
		align-items: center;
	}

	.thumbnail {
		margin-right: 8px;
	}

	.details {
		// 40 = thumbnail (32 + 8)
		width: calc(100% - 40px);
		.file-name {
			white-space: nowrap;
			margin-bottom: -8px;

			.name {
				display: inline-block;
				// 40 px = .extension (40)
				max-width: calc(100% - 40px);
				color: var(--color-main-text);
				text-overflow: ellipsis;
				overflow: hidden;
			}

			.extension {
				width: 40px;
				display: inline;
				color: var(--color-text-maxcontrast);
				vertical-align: top;
			}
		}

		.reason {
			white-space: nowrap;
			text-overflow: ellipsis;
			overflow: hidden;
			color: var(--color-text-maxcontrast);
		}
	}
</style>
