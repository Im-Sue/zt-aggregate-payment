<template>
  <div style="padding: 0 15px; font-size: 20px;" @click="toggleClick">
    <i class="el-icon-d-arrow-left" v-if="isActive" title="收起菜单" />
    <i class="el-icon-d-arrow-right" v-else title="展开菜单" />
  </div>
</template>

<script>
export default {
  name: 'Hamburger',
  props: {
    isActive: {
      type: Boolean,
      default: false
    }
  },
  methods: {
    toggleClick() {
      this.$emit('toggleClick')
    }
  }
}
</script>

<style scoped>
.hamburger {
  display: inline-block;
  vertical-align: middle;
  width: 20px;
  height: 20px;
}

.hamburger.is-active {
  transform: rotate(180deg);
}
</style>
