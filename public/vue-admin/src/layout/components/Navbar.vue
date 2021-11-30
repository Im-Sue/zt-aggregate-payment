<template>
  <div class="navbar">
    <hamburger :is-active="sidebar.opened" class="hamburger-container" @toggleClick="toggleSideBar" />

    <!--    <breadcrumb class="breadcrumb-container" />-->

    <div class="right-menu">
      <el-dropdown class="avatar-container" trigger="click">
        <div class="avatar-wrapper">
          <img :src="avatar" class="user-avatar">
          <div class="user-name">{{ name }}</div>
          <i class="el-icon-caret-bottom" />
        </div>
        <el-dropdown-menu slot="dropdown" class="user-dropdown">
          <el-dropdown-item v-if="!nopass" @click.native="changePassword">修改密码</el-dropdown-item>
          <el-dropdown-item divided @click.native="logout">
            <span style="display:block;">退出登录</span>
          </el-dropdown-item>
        </el-dropdown-menu>
      </el-dropdown>
    </div>

    <el-dialog
      custom-class="my-dialog"
      title="修改密码"
      :visible.sync="passwordDialogShow"
      width="460px"
      :close-on-click-modal="false"
      :append-to-body="true"
    >
      <el-form ref="speakerForm" :model="passForm" :rules="rules" label-width="100px">
        <el-form-item label="原密码" prop="password_old">
          <el-input v-model="passForm.password_old" type="password" placeholder="请输入原密码" style="width: 250px;" />
        </el-form-item>
        <el-form-item label="新密码" prop="password_new">
          <el-input v-model="passForm.password_new" type="password" placeholder="请输入新密码" style="width: 250px;" />
        </el-form-item>
        <el-form-item label="重复新密码" prop="password_new2">
          <el-input v-model="passForm.password_new2" type="password" placeholder="请重复一遍新密码" style="width: 250px;" />
        </el-form-item>
      </el-form>
      <span
        slot="footer"
        class="dialog-footer"
      >
        <el-button type="default" icon="el-icon-close" size="small" @click="passwordDialogShow = false">取 消</el-button>
        <el-button type="primary" icon="el-icon-check" size="small" @click="clickSubmit">提 交</el-button>
      </span>
    </el-dialog>
  </div>
</template>

<script>
import { mapGetters } from 'vuex'
import Breadcrumb from '@/components/Breadcrumb'
import Hamburger from '@/components/Hamburger'
import { changePassword } from '@/api/user'

export default {
  components: {
    Breadcrumb,
    Hamburger
  },
  computed: {
    ...mapGetters([
      'sidebar',
      'avatar',
      'name',
      'nopass'
    ])
  },
  data() {
    var validateNewPass = (rule, value, callback) => {
      if (typeof (value) === undefined || value === '') {
        callback(new Error('此项必填'))
      }
      if (value.length < 6 || value.length > 18) {
        callback(new Error('密码长度要求6-18位'))
      }
    }
    var validateNewPass2 = (rule, value, callback) => {
      if (typeof (value) === undefined || value === '') {
        callback(new Error('请重复一遍新密码'))
      }
      if (this.passForm.password_new !== value) {
        callback(new Error('两次密码输入不一致'))
      }
    }
    return {
      passwordDialogShow: false,
      passForm: {
        password_old: '',
        password_new: '',
        password_new2: ''
      },
      rules: {
        password_old: [
          { required: true, message: '此项必填', trigger: 'blur' }
        ],
        password_new: [
          { required: true, validator: validateNewPass, trigger: 'blur' }
        ],
        password_new2: [
          { required: true, validator: validateNewPass2, trigger: 'blur' }
        ]
      }
    }
  },
  methods: {
    toggleSideBar() {
      this.$store.dispatch('app/toggleSideBar')
    },
    changePassword() {
      this.passwordDialogShow = true
    },
    clickSubmit() {
      const _this = this
      changePassword(this.passForm).then(res => {
        if (res.errno) {
          this.$message({
            message: res.message,
            type: 'error'
          })
        } else {
          this.$message({
            message: res.message,
            type: 'success',
            duration: 1500,
            onClose() {
              _this.logout()
            }
          })
          this.passForm = {}
          this.passwordDialogShow = false
        }
      })
    },
    async logout() {
      await this.$store.dispatch('user/logout')
      // this.$router.push(`/login`)
      // window.location.href = '/admin/login'
      window.location.href = '/'
    }
  }
}
</script>

<style lang="scss" scoped>
.navbar {
  height: 50px;
  overflow: hidden;
  position: relative;
  background: #fff;
  box-shadow: 0 1px 4px rgba(0,21,41,.08);

  .hamburger-container {
    line-height: 46px;
    height: 100%;
    float: left;
    cursor: pointer;
    transition: background .3s;
    -webkit-tap-highlight-color:transparent;

    &:hover {
      background: rgba(0, 0, 0, .025)
    }
  }

  .breadcrumb-container {
    float: left;
  }

  .right-menu {
    float: right;
    height: 100%;
    line-height: 50px;

    &:focus {
      outline: none;
    }

    .right-menu-item {
      display: inline-block;
      padding: 0 8px;
      height: 100%;
      font-size: 18px;
      color: #5a5e66;
      vertical-align: text-bottom;

      &.hover-effect {
        cursor: pointer;
        transition: background .3s;

        &:hover {
          background: rgba(0, 0, 0, .025)
        }
      }
    }

    .avatar-container {
      margin-right: 30px;

      .avatar-wrapper {
        position: relative;
        display: flex;
        align-items: center;

        .user-avatar {
          cursor: pointer;
          width: 40px;
          height: 40px;
          border-radius: 10px;
        }

        .user-name {
          cursor: pointer;
          margin-left: 10px;
        }

        .el-icon-caret-bottom {
          cursor: pointer;
          font-size: 12px;
          margin-left: 10px;
        }
      }
    }
  }
}
</style>
