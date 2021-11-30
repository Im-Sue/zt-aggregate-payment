const getters = {
  sidebar: state => state.app.sidebar,
  device: state => state.app.device,
  visitedViews: state => state.tagsView.visitedViews,
  token: state => state.user.token,
  avatar: state => state.user.avatar,
  name: state => state.user.name,
  role: state => state.user.role,
  logo: state => state.user.logo,
  logo_mini: state => state.user.logo_mini,
  nopass: state => state.user.nopass,
  copyright: state => state.user.copyright,
  routes: state => state.permission.routes
}
export default getters
