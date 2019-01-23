/*
 * @copyright 2018 Christoph Wurst <christoph@winzerhof-wurst.at>
 *
 * @author 2018 Christoph Wurst <christoph@winzerhof-wurst.at>
 *
 * @license GNU AGPL version 3 or any later version
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
 */

import Vue from "vue";

import Nextcloud from "./mixins/Nextcloud";
import Recommendations from "./components/Recommendations";

Vue.mixin(Nextcloud);

OC.Plugins.register('OCA.Files.FileList', {

	attach: fileList => {
		const el = document.createElement('div');
		const controls = document.getElementById('controls');
		controls.parentNode.insertBefore(el, controls.nextSibling);
		el.id = 'files-recommendation-wrapper';

		const View = Vue.extend(Recommendations);
		const vm = new View({
			propsData: {}
		}).$mount(el);

		fileList.$el.on('changeDirectory', data => {
			if (data.dir.toString() === '/') {
				vm.show();
			} else {
				vm.hide();
			}
		});

		if (fileList.getCurrentDirectory() === '/') {
			vm.show();
		}
	}

});
