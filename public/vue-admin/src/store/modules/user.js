import { login, logout, getInfo } from '@/api/user'
import { getToken, setToken, removeToken } from '@/utils/auth'
import { resetRouter } from '@/router'
import store from '@/store'

const getDefaultState = () => {
  return {
    token: getToken(),
    name: '',
    avatar: '',
    logo: '',
    logo_mini: '',
    nopass: 0
  }
}

const state = getDefaultState()

const mutations = {
  RESET_STATE: (state) => {
    Object.assign(state, getDefaultState())
  },
  SET_TOKEN: (state, token) => {
    state.token = token
  },
  SET_NAME: (state, name) => {
    state.name = name
  },
  SET_AVATAR: (state, avatar) => {
    state.avatar = avatar
  },
  SET_LOGO: (state, logo) => {
    state.logo = logo
  },
  SET_LOGO_MINI: (state, logo_mini) => {
    state.logo_mini = logo_mini
  },
  SET_NOPASS: (state, nopass) => {
    state.nopass = nopass
  },
  SET_ROLE: (state, role) => {
    state.role = role
  },
  SET_COPYRIGHT: (state, copyright) => {
    state.copyright = copyright
  }
}

const actions = {
  // user login
  login({ commit }, userInfo) {
    const { username, password } = userInfo
    return new Promise((resolve, reject) => {
      login({ username: username.trim(), password: password }).then(response => {
        if (response.errno === 0) {
          const { data } = response
          commit('SET_TOKEN', data.token)
          setToken(data.token)
        }
        resolve(response)
      }).catch(error => {
        console.log('error', error)
        reject(error)
      })
    })
  },

  // get user info
  getInfo({ commit, state }) {
    return new Promise((resolve, reject) => {
      getInfo().then(response => {
        const { data } = response

        if (!data) {
          return reject('登录过期，请重新登录')
        }

        const { name, avatar, logo, logo_mini, nopass, roles, copyright } = data

        commit('SET_NAME', name)
        commit('SET_AVATAR', avatar)
        commit('SET_LOGO', logo)
        commit('SET_LOGO_MINI', logo_mini)
        commit('SET_NOPASS', nopass)
        commit('SET_ROLE', roles[0])
        commit('SET_COPYRIGHT', copyright)

        resolve(data)
      }).catch(error => {
        reject(error)
      })
    })
  },

  // user logout
  logout({ commit, state }) {
    return new Promise((resolve, reject) => {
      logout().then(() => {
        removeToken() // must remove  token  first
        resetRouter()
        commit('RESET_STATE')
        resolve()
      }).catch(error => {
        reject(error)
      })
    })
  },

  // remove token
  resetToken({ commit }) {
    return new Promise(resolve => {
      removeToken() // must remove  token  first
      commit('RESET_STATE')
      resolve()
    })
  }
}

export default {
  namespaced: true,
  state,
  mutations,
  actions
}

