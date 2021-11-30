<template>
  <div class="copyright">
    <span v-if="copyright['footerleft']" class="copyright">{{ copyright['footerleft'] }}</span>
    <span v-else>Powered by <a href="http://www.w7.cc"><b>微擎</b></a> v{{ copyright.ims_version }}  2014-2015 <a href="http://www.w7.cc">www.w7.cc</a></span>
    <span v-if="copyright.icp">备案号：<a href="http://www.miitbeian.gov.cn" target="_blank">{{ copyright.icp }}</a></span>
  </div>
</template>

<script>
import { mapGetters } from 'vuex'

export default {
  name: 'Copyright',
  computed: {
    ...mapGetters([
      'copyright'
    ])
  }
}
</script>

<style scoped>
.copyright {
  color: #666;
  font-size: 12px;
}
.copyright span {
  margin-right: 10px;
}
  .copyright a {
    color: #333;
  }
</style>
