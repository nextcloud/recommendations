<!--
  - SPDX-FileCopyrightText: 2019 Nextcloud GmbH and Nextcloud contributors
  - SPDX-License-Identifier: AGPL-3.0-or-later
-->

<template>
	<a class="recommendation"
		tabindex="0"
		:aria-describedby="`recommendation-description-${id}`"
		:title="path"
		@click.prevent="navigate"
		@keyup.enter.prevent="navigate">
		<!-- Preview or mime icon -->
		<FolderIcon v-if="isFolder" class="thumbnail" />
		<div v-else class="thumbnail" :style="{ 'background-image': 'url(' + previewUrl + ')' }" />

		<!-- Details -->
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
			<div v-if="description" class="description">
				{{ description }}
			</div>
			<span :id="`recommendation-description-${id}`" class="hidden-visually">{{ t('recommendations', 'Path name {path}', {path: path}) }}</span>
		</div>
	</a>
</template>

<script>
import { computed } from 'vue'
import { translate as t } from '@nextcloud/l10n'
import { generateUrl } from '@nextcloud/router'
import { joinPaths } from '@nextcloud/paths'
import { useFormatDateTime } from '@nextcloud/vue'

import FolderIcon from 'vue-material-design-icons/Folder.vue'

export default {
	name: 'RecommendedFile',

	components: {
		FolderIcon,
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
		timestamp: {
			type: Number,
			required: true,
		},
	},
	setup(props) {
		const { formattedTime } = useFormatDateTime(computed(() => props.timestamp * 1000), {
			ignoreSeconds: true,
		})
		return {
			formattedTime,
		}
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
		path() {
			return (this.directory === '/' ? '' : this.directory) + '/' + this.name
		},
		isFolder() {
			return this.mimeType === 'httpd/unix-directory'
		},
		description() {
			if (this.reason === 'recently-edited') {
				return t('recommendations', 'Last updated {timeAgo}', { timeAgo: this.formattedTime })
			}
			if (this.reason === 'recently-shared') {
				return t('recommendations', 'Shared with you {timeAgo}', { timeAgo: this.formattedTime })
			}
			if (this.reason === 'recently-commented') {
				return t('recommendations', 'Last commented on {timeAgo}', { timeAgo: this.formattedTime })
			}
			return null
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
		t,

		navigate() {
			// If Viewer is enabled and supports this file, open directly
			if (window.OCA?.Viewer && window.OCA.Viewer.mimetypes.indexOf(this.mimeType) !== -1) {
				window.OCA.Viewer.open({ path: this.path })
				return
			}

			// Navigate to the file if the file router is available
			if (window.OCP?.Files?.Router) {
				const dir = this.isFolder ? joinPaths(this.directory, this.name) : this.directory
				const fileid = this.isFolder ? null : this.id
				window.OCP.Files.Router.goToRoute(
					// use default route
					null,
					// recommendations is only enabled on files
					{ view: 'files', fileid },
					{ dir },
				)
				return
			}

			// Fallback to the old way of navigating to the file
			window.location = generateUrl('/f/' + this.id)
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
		padding: 5px;
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
		width: 32px;
		height: 32px;
		background-size: contain;
		flex-shrink: 0;
		border-radius: var(--border-radius);
		display: flex;
		justify-content: center;
		align-items: center;
		// For the folder icon
		:deep(svg) {
			color: var(--color-primary-element);
			width: 100%;
			height: 100%;
		}
	}

	.details {
		min-width: 0;

		.file-name {
			white-space: nowrap;
			min-width: 0;
			text-overflow: ellipsis;
			overflow: hidden;

			.name {
				max-width: 170px;
				color: var(--color-main-text);
			}

			.extension {
				color: var(--color-text-maxcontrast);
			}
		}

		.description {
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
