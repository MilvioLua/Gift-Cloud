<!--
  - @copyright Copyright (c) 2019 John Molakvoæ <skjnldsv@protonmail.com>
  -
  - @author John Molakvoæ <skjnldsv@protonmail.com>
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
  - along with this program. If not, see <http://www.gnu.org/licenses/>.
  -
  -->

<template>
	<AppSidebar
		v-if="fileInfo"
		:background="background"
		:active.sync="activeTab"
		:class="{'has-preview': fileInfo.hasPreview}"
		:star-loading="starLoading"
		:starred.sync="fileInfo.isFavourited"
		:subtitle="subtitle"
		:title="fileInfo.name"
		@close="onClose"
		@update:starred="toggleStarred">
		<component v-for="tab in tabs" v-bind="tabComponent(tab)" :key="tab.id" :name="tab.name" :file-info="fileInfo" />
	</AppSidebar>

	<!-- Empty sidebar loading feedback -->
	<AppSidebar 
		v-else-if="file"
		:class="{'icon-loading': !error}"
		key="loading"
		title=""
		@close="onClose">
		<div v-if="error" class="emptycontent">
			<div class="icon-error"></div>
			<h2>{{ error }}</h2>
		</div>
	</AppSidebar>
</template>
<script>
import axios from 'nextcloud-axios'
import AppSidebar from 'nextcloud-vue/dist/Components/AppSidebar'
import FileInfo from '../services/FileInfo'
import LegacyTab from '../components/LegacyTab'

export default {
	name: 'Sidebar',

	components: {
		AppSidebar
	},

	data() {
		return {
			// reactive state
			Sidebar: OCA.Files.Sidebar.state,
			error: null,
			fileInfo: null,
			starLoading: false
		}
	},

	watch: {
		// update the sidebar data
		async file(curr, prev) {
			this.resetData()
			if (curr && curr.trim() !== '') {
				try {
					this.fileInfo = await FileInfo(this.davPath, curr)
				} catch(error) {
					this.error = t('core', 'Error while loading the file data')
					console.error('Error while loading the file data');
				}
			}
		}
	},

	computed: {
		file() {
			return this.Sidebar.file
		},
		davPath() {
			const user = OC.getCurrentUser().uid
			return OC.linkToRemote(`dav/files/${user}${this.file}`)
		},
		tabs() {
			return this.Sidebar.tabs
		},
		activeTab: {
			get: function () {
				return this.Sidebar.activeTab
			},
			set: function(id) {
				OCA.Files.Sidebar.activeTab = id
			}
		},
		subtitle() {
			return `${this.size}, ${this.time}`
		},
		time() {
			return OC.Util.relativeModifiedDate(this.fileInfo.mtime)
		},
		size() {
			return OC.Util.humanFileSize(this.fileInfo.size)
		},
		background() {
			return this.getPreviewIfAny(this.fileInfo)
		}
	},

	methods: {
		onClose() {
			this.resetData()
			OCA.Files.Sidebar.file = ''
		},
		resetData() {
			this.error = null
			this.fileInfo = null
		},
		getPreviewIfAny(fileInfo) {
			if (fileInfo.hasPreview) {
				return OC.generateUrl(`/core/preview?fileId=${fileInfo.id}&x=${screen.width}&y=${screen.height}&a=true`)
			}
			return OCA.Files.App.fileList._getIconUrl(fileInfo)
		},

		tabComponent(tab) {
			if (tab.isLegacyTab) {
				return {
					is: LegacyTab,
					component: tab.component
				}
			}
			return {
				is: tab.component
			}
		},

		/**
		 * Toggle favourite state
		 * 
		 * @param {Boolean} state favourited or not
		 */
		async toggleStarred(state) {
			try {
				this.starLoading = true
				await axios({
					method: 'PROPPATCH',
					url: this.davPath,
					data: `<?xml version="1.0"?>
						<d:propertyupdate xmlns:d="DAV:" xmlns:oc="http://owncloud.org/ns">
						${state ? '<d:set>' : '<d:remove>'}
							<d:prop>
								<oc:favorite>1</oc:favorite>
							</d:prop>
						${state ? '</d:set>' : '</d:remove>'}
						</d:propertyupdate>`
				})
			} catch(error) {
				OC.Notification.showTemporary(t('files', 'Unable to change the favourite state of the file'))
				console.error('Unable to change favourite state', error)
			}
			this.starLoading = false
		}
	}
}
</script>
<style lang="scss" scoped>
#app-sidebar {
	&.has-preview::v-deep .app-sidebar-header__figure {
		background-size: cover;
	}
}
</style>