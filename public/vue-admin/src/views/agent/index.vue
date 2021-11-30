<template>
  <div class="app-container">
    <div class="toolbar">
      <el-button type="primary" icon="el-icon-plus" size="mini" @click="clickCreate()">添加代理商</el-button>
      <el-popover
        v-if="agentLoginUrl"
        placement="bottom-start"
        title="登录地址"
        width="500"
        trigger="click"
        :content="agentLoginUrl">
        <el-button size="mini" slot="reference" style="margin-left: 10px;" icon="el-icon-info">代理商登录</el-button>
      </el-popover>
      <div style="float:right;">
        <el-input
          placeholder="姓名/手机号/备注"
          v-model="search.keyword"
          class="input-with-select"
          size="small"
          clearable
          style="width: 300px;"
        >
          <el-button slot="append" icon="el-icon-search" @click="clickSearch()"></el-button>
        </el-input>
      </div>
    </div>
    <el-table
      v-loading="dataLoading"
      :data="dataList"
      element-loading-text="加载中..."
      stripe
      size="medium"
      header-cell-class-name="bg-gray"
    >
      <el-table-column type="index" label="序号" width="60" :index="tableIndex" />
      <el-table-column prop="title" label="名称" width="200" />
      <el-table-column prop="phone" label="手机号" width="160" />
      <el-table-column prop="shop_num" label="商户数" width="120" align="center" />
      <el-table-column prop="remark" label="备注" />
      <el-table-column prop="add_time" label="创建时间" width="200" />
      <el-table-column label="操作">
        <template slot-scope="scope">
          <el-button-group>
            <el-button type="text" size="mini" icon="el-icon-edit" @click.native.prevent="clickEdit(scope.row.id)">修改</el-button>
            <el-button type="text text-danger" size="mini" icon="el-icon-delete" @click.native.prevent="clickDel(scope.row.id)">删除</el-button>
          </el-button-group>
        </template>
      </el-table-column>
    </el-table>
    <el-pagination
      :current-page="currentPage"
      :page-size="pagesize"
      layout="total, prev, pager, next"
      :total="dataTotal"
      @current-change="currentPageChange"
    />
    <el-dialog custom-class="my-dialog" :title="dialogTitle" :visible.sync="dialogShow" width="500px" :close-on-click-modal="false">
      <el-form ref="ruleForm" :model="form" :rules="rules" label-width="120px">
        <el-form-item label="手机号" prop="phone">
          <el-input v-model="form.phone" placeholder="请输入手机号" size="small" />
        </el-form-item>
        <el-form-item label="密码" prop="password" :rules="form.id ? []:rules.password">
          <el-input v-model="form.password" type="password" :placeholder="form.id ? '不修改请留空':'请输入密码'" size="small" />
        </el-form-item>
        <el-form-item label="名称" prop="title">
          <el-input v-model="form.title" placeholder="请输入姓名" size="small" />
        </el-form-item>
        <el-form-item label="备注" prop="remark">
          <el-input v-model="form.remark" type="textarea" placeholder="请输入备注" size="small" style="width: 300px; max-width: 100%; min-height: 80px;" />
        </el-form-item>
      </el-form>
      <span
        slot="footer"
        class="dialog-footer"
      >
        <el-button type="default" icon="el-icon-close" size="small" @click="dialogShow = false">取 消</el-button>
        <el-button type="primary" icon="el-icon-check" size="small" @click="clickSubmit('ruleForm')">提 交</el-button>
      </span>
    </el-dialog>
  </div>
</template>

<script>
import { getList, getInfo, saveInfo, del, getAgentLoginUrl } from '@/api/agent'
import { Loading } from 'element-ui'

export default {
  data() {
    return {
      form: {},
      search: {},
      dataList: [],
      dataTotal: 0,
      dataLoading: false,
      dialogShow: false,
      dialogTitle: '',
      currentPage: 1,
      pagesize: 10,
      rules: {
        phone: [
          { required: true, message: '此项必填', trigger: 'blur' }
        ],
        password: [
          { required: true, message: '此项必填', trigger: 'blur' }
        ],
        title: [
          { required: true, message: '此项必填', trigger: 'blur' }
        ]
      },
      agentLoginUrl: ''
    }
  },
  created() {
    this.getList()
    this.getAgentLoginUrl()
  },
  methods: {
    showLoading() {
      this.loading = Loading.service({ background: 'rgba(0, 0, 0, 0.5)' })
    },
    hideLoading() {
      if (this.loading) {
        this.loading.close()
        this.loading = null
      }
    },
    getList() {
      getList({
        page: this.currentPage,
        pagesize: this.pagesize,
        keyword: this.search.keyword
      }).then(res => {
        const data = res.data
        this.dataList = data.list
        this.dataTotal = res.data.count
      })
    },
    getAgentLoginUrl() {
      getAgentLoginUrl().then(res => {
        this.agentLoginUrl = res.data
      })
    },
    tableIndex(index) {
      return this.pagesize * (this.currentPage - 1) + index + 1
    },
    currentPageChange(currentPage) {
      this.currentPage = currentPage
      this.getList()
    },
    clickCreate() {
      this.form = { id: '' }
      this.dialogTitle = '添加'
      this.dialogShow = true
    },
    clickEdit(id) {
      this.form = { id: '' }
      this.dialogTitle = '编辑'
      this.showLoading()
      getInfo({ id: id }).then(res => {
        this.form = res.data
        this.hideLoading()
        this.dialogShow = true
      })
    },
    clickSubmit(formName) {
      this.$refs[formName].validate((valid) => {
        if (valid) {
          const postData = {
            id: this.form.id,
            phone: this.form.phone,
            password: this.form.password,
            title: this.form.title,
            remark: this.form.remark
          }
          this.showLoading()
          saveInfo(postData).then(res => {
            this.hideLoading()
            if (res.errno) {
              this.$message({
                message: res.message,
                type: 'error'
              })
            } else {
              this.currentPage = 1
              this.getList()
              this.$message({
                message: res.message,
                type: 'success',
                duration: 1500
              })
              this.dialogShow = false
            }
          })
        } else {
          console.log('网络错误，请重试！')
        }
      })
    },
    clickDel(id) {
      this.$confirm('删除后不可恢复，确认删除吗?', '提示', {
        confirmButtonText: '确定删除',
        cancelButtonText: '取消',
        type: 'warning'
      }).then(() => {
        this.showLoading()
        del({ id: id }).then(res => {
          this.hideLoading()
          if (res.errno) {
            this.$message({
              message: res.message,
              type: 'error'
            })
          } else {
            this.getList()
            this.$message({
              message: res.message,
              type: 'success',
              duration: 1500
            })
          }
        })
      })
    },
    clickSearch() {
      this.currentPage = 1
      this.getList()
    }
  }
}
</script>
<style scoped>
  .el-input{
    width: 200px;
  }
</style>
