<!--
  - @copyright 2019 Gary Kim <gary@garykim.dev>
  -
  - @author 2019 Gary Kim <gary@garykim.dev>
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
  <div class="section" id="recommendations">
	<h2>{{ t('recommendations', 'Recommendations') }}</h2>
	<p class="settings-hint">{{ t('recommendations', 'Shows recommended files for quick access of files and folders with recent activity') }}</p>
	<input type="checkbox" name="enabled" id="recommendations-enabled" class="checkbox" v-model="enabled" @change="update">
	<label for="recommendations-enabled">{{ t('recommendations', 'Enabled') }}</label>
  </div>
</template>

<script>
import Axios from 'nextcloud-axios';

export default {
	name: 'PersonalSettings',
	data() {
		return {
			enabled: true
		}
	},
	methods: {
		update(e) {
			Axios.put(OC.generateUrl(`apps/recommendations/settings/${e.currentTarget.name}`), {
				value: this[e.currentTarget.name].toString()
			});
		}
	}
}
</script>
