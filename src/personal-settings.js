/**
 * @copyright Copyright (c) 2019 Gary Kim <gary@garykim.dev>
 *
 * @author Gary Kim <gary@garykim.dev>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

import Vue from "vue";
import PersonalSettings from "./components/PersonalSettings";
import Nextcloud from './mixins/Nextcloud';

Vue.mixin(Nextcloud);

const initalStateElement = document.getElementById('recommendations-enabled-initial-state');
var initialState;
if (initalStateElement) {
	initialState = initalStateElement.value === 'true';
}

const View = Vue.extend(PersonalSettings);
new View({
	data: {
		enabled: initialState
	}
}).$mount('#recommendations');
