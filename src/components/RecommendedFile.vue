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
	<div class="col recommendation-columns">
		<a :id="id" class="name"
		   :href="url">
			<div class="thumbnail-wrapper">
				<div class="thumbnail"
					 style="height: 32px; width: 32px; float: left"
					 :style="{ 'background-image': 'url(' + mimeUrl(mimeType) + ')' }">
				</div>
				<div class="nametext">
							<span id="recommendation-content-file-name"
								  class="innernametext">{{ name }}</span><span
						id="recommendation-content-extension"
						class="extension">.{{ extension }}</span>
				</div>
				<div style="clear: right;"></div>
				<div class="nametext">
							<span id="recommendation-transparency-extension"
								  class="extension">{{ reason }}</span>
				</div>
			</div>
		</a>
	</div>
</template>

<script>
	export default {
		name: "RecommendedFile",
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
			reason: {
				type: String,
				required: true,
			},
			url: {
				type: String,
				required: true,
			},
		},
		methods: {
			mimeUrl (mime) {
				return OC.MimeType.getIconUrl(mime);
			},
		}
	}
</script>

<style scoped>
	.recommendation-columns {
		width: 32.26%;
	}

	#recommendation-content-file-name {
		white-space: nowrap;
		text-overflow: ellipsis;
		display: block;
		max-width: 200px;
		float: left;
		overflow: hidden;
	}

	#recommendation-content-extension.extension {
		overflow: hidden;
		float: right;
		display: block;
	}

	#recommendation-transparency-extension {
		white-space: nowrap;
		text-overflow: ellipsis;
		display: block;
		overflow: hidden;
	}

	#recommendation-content .thumbnail {
		margin-right: 10px;
		width: 32px;
		height: 32px;
		background-size: contain;
		flex-shrink: 0;
	}

	#recommendation-content .thumbnail-wrapper {
		max-width: 280px;
		/*white-space: nowrap;*/
		overflow: hidden;
		text-overflow: ellipsis;
	}

	.name {
		display: flex;
	}
</style>
