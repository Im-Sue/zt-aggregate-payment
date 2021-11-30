import defaultSettings from '@/settings'

const { title, fixedHeader, showSettings, sidebarLogo, sidebarLogoImg, sidebarLogoMiniImg } = defaultSettings

const state = {
  title: title,
  showSettings: showSettings,
  fixedHeader: fixedHeader,
  sidebarLogo: sidebarLogo,
  sidebarLogoImg: sidebarLogoImg,
  sidebarLogoMiniImg: sidebarLogoMiniImg
}

const mutations = {
  CHANGE_SETTING: (state, { key, value }) => {
    // eslint-disable-next-line no-prototype-builtins
    if (state.hasOwnProperty(key)) {
      state[key] = value
    }
  }
}

const actions = {
  changeSetting({ commit }, data) {
    commit('CHANGE_SETTING', data)
  }
}

export default {
  namespaced: true,
  state,
  mutations,
  actions
}

