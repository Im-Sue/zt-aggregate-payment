<template>
  <div class="app-container">
    <div class="toolbar">
      <el-button type="primary" icon="el-icon-plus" size="mini" @click="clickAdd('alipay')">添加支付宝服务商</el-button>
      <el-button type="success" icon="el-icon-plus" size="mini" @click="clickAdd('wxpay')">添加微信服务商</el-button>
      <div style="float:right;">
        <el-input
          v-model="search.keyword"
          placeholder="服务商名称/AppId"
          class="input-with-select"
          size="small"
          clearable
          style="width: 320px;"
        >
          <el-button
            slot="append"
            icon="el-icon-search"
            @click="doSearch()"
          />
        </el-input>
      </div>
    </div>
    <el-table
      :data="dataList"
      stripe
      size="medium"
      header-cell-class-name="bg-gray"
    >
      <el-table-column type="index" label="序号" width="60" :index="tableIndex" />
      <el-table-column prop="title" label="服务商名称" width="300" />
      <el-table-column prop="type" label="类型" width="150">
        <template slot-scope="scope">
          <span v-if="scope.row.type === 'alipay'">支付宝服务商</span>
          <span v-if="scope.row.type === 'wxpay'">微信服务商</span>
        </template>
      </el-table-column>
      <el-table-column prop="appid" label="AppId" width="200" />
      <el-table-column prop="remark" label="备注" />
      <el-table-column label="操作" width="200">
        <template slot-scope="scope">
          <el-button-group>
            <el-button type="text" size="mini" icon="el-icon-edit" @click.native.prevent="clickEdit(scope.row.id)">编辑
            </el-button>
            <el-button type="text text-danger" size="mini" icon="el-icon-delete" @click.native.prevent="clickDel(scope.row.id)">删除</el-button>
          </el-button-group>
        </template>
      </el-table-column>
    </el-table>
    <el-pagination
      :current-page="page"
      :page-size="pagesize"
      layout="total, prev, pager, next"
      :total="dataTotal"
      @current-change="pageChange"
    />
    <div v-if="form">
      <el-dialog
        custom-class="my-dialog"
        :title="formTitle"
        width="660px"
        :visible="true"
        :close-on-click-modal="false"
        :before-close="formClose"
      >
        <el-form ref="form" :model="form" :rules="formRules" label-width="160px">
          <el-form-item label="服务商名称" prop="title">
            <el-input v-model="form.title" placeholder="服务商名称" size="small" />
          </el-form-item>
          <el-form-item label="类型" prop="title">
            <el-radio-group v-model="form.type">
              <el-radio label="alipay">支付宝服务商</el-radio>
              <el-radio label="wxpay">微信服务商</el-radio>
            </el-radio-group>
          </el-form-item>
          <div v-if="form.type === 'alipay'">
            <el-form-item label="AppId" prop="appid">
              <el-input v-model="form.appid" placeholder="第三方平台应用AppId" size="small" />
            </el-form-item>
            <el-form-item label="PID" prop="pid">
              <el-input v-model="form.pid" placeholder="服务商PID" size="small" />
            </el-form-item>
            <el-form-item label="商户私钥" prop="private_key">
              <el-input
                v-model="form.private_key"
                type="textarea"
                placeholder="商户私钥"
                size="small"
                class="textarea"
              />
            </el-form-item>
            <el-form-item label="支付宝公钥" prop="public_key">
              <el-input v-model="form.public_key" type="textarea" placeholder="支付宝公钥" size="small" class="textarea" />
            </el-form-item>
          </div>
          <div v-if="form.type === 'wxpay'">
            <el-form-item label="AppId" prop="appid">
              <el-input v-model="form.appid" placeholder="公众号AppId" size="small" />
            </el-form-item>
            <el-form-item label="AppSecret" prop="appsecret">
              <el-input v-model="form.appsecret" placeholder="公众号AppSecret" size="small" />
            </el-form-item>
            <el-form-item label="商户号" prop="mch_id">
              <el-input v-model="form.mch_id" placeholder="服务商商户号mch_id" size="small" />
            </el-form-item>
            <el-form-item label="Api密钥" prop="key">
              <el-input v-model="form.key" placeholder="Api密钥key" size="small" />
            </el-form-item>
            <el-form-item label="cert证书">
              <el-upload
                action=""
                :data="{type: 'cert'}"
                :before-upload="uploadCheck"
                :http-request="uploadPem"
                :on-success="uploadSuccess"
                :show-file-list="false"
                :multiple="false"
              >
                <el-button size="small" type="primary">选择文件</el-button>
              </el-upload>
              <span v-if="form.apiclient_cert" class="upload_status">已上传</span>
            </el-form-item>
            <el-form-item label="key证书">
              <el-upload
                action=""
                :data="{type: 'key'}"
                :before-upload="uploadCheck"
                :http-request="uploadPem"
                :on-success="uploadSuccess"
                :show-file-list="false"
                :multiple="false"
              >
                <el-button size="small" type="primary">选择文件</el-button>
              </el-upload>
              <span v-if="form.apiclient_key" class="upload_status">已上传</span>
            </el-form-item>
          </div>
          <el-form-item label="备注" prop="remark">
            <el-input
              v-model="form.remark"
              type="textarea"
              placeholder="请输入备注"
              size="small"
              class="textarea"
            />
          </el-form-item>
        </el-form>
        <span slot="footer" class="dialog-footer">
          <el-button type="default" icon="el-icon-close" size="small" @click="formClose">取 消</el-button>
          <el-button type="primary" icon="el-icon-check" size="small" @click="clickSubmit">提 交</el-button>
        </span>
      </el-dialog>
    </div>
  </div>
</template>

<script>
import { getList, getInfo, saveInfo, del, uploadPem } from '@/api/server'

export default {
  data() {
    return {
      form: null,
      formType: null,
      search: {},
      // 列表
      dataList: [],
      dataTotal: 0,
      page: 1,
      pagesize: 10,
      //
      formRules: {
        title: [
          { required: true, message: '此项必填', trigger: 'blur' }
        ],
        appid: [
          { required: true, message: '此项必填', trigger: 'blur' }
        ],
        pid: [
          { required: true, message: '此项必填', trigger: 'blur' }
        ],
        private_key: [
          { required: true, message: '此项必填', trigger: 'blur' }
        ],
        public_key: [
          { required: true, message: '此项必填', trigger: 'blur' }
        ],
        appsecret: [
          { required: true, message: '此项必填', trigger: 'blur' }
        ],
        mch_id: [
          { required: true, message: '此项必填', trigger: 'blur' }
        ],
        key: [
          { required: true, message: '此项必填', trigger: 'blur' }
        ],
        apiclient_cert: [
          { required: true, message: '此项必填', trigger: 'blur' }
        ],
        apiclient_key: [
          { required: true, message: '此项必填', trigger: 'blur' }
        ]
      }
    }
  },
  computed: {
    formTitle() {
      return this.formType === 'add' ? '添加服务商' : '编辑'
    }
  },
  mounted() {
    this.getList()
  },
  methods: {
    getList() {
      const param = {
        page: this.page,
        pagesize: this.pagesize,
        keyword: this.search.keyword
      }
      getList(param).then(response => {
        const data = response.data
        this.dataList = data.list
        this.dataTotal = response.data.count
      })
    },
    tableIndex(index) {
      return this.pagesize * (this.page - 1) + index + 1
    },
    pageChange(page) {
      this.page = page
      this.getList()
    },
    clickAdd(type = 'alipay') {
      this.formType = 'add'
      this.form = {
        type: type
      }
    },
    clickEdit(id) {
      getInfo({ id: id }).then(res => {
        this.formType = 'edit'
        this.form = res.data
      })
    },
    formClose() {
      this.form = null
      this.formType = ''
    },
    clickSubmit() {
      this.$refs.form.validate((valid) => {
        if (valid) {
          saveInfo(this.form).then(res => {
            this.getList()
            this.$message({
              message: res.message,
              type: 'success',
              duration: 1500
            })
            this.formClose()
          })
        } else {
          console.log('请按照规则填写表单')
        }
      })
    },
    clickDel(id) {
      this.$confirm('删除后不可恢复，同时使用此服务商配置的商户将不能继续收款，请谨慎操作，确认删除吗?', '提示', {
        confirmButtonText: '确定删除',
        cancelButtonText: '取消',
        type: 'warning'
      }).then(() => {
        del({ id: id }).then(res => {
          this.getList()
          this.$message({
            message: res.message,
            type: 'success',
            duration: 1500
          })
        })
      })
    },
    uploadCheck(field) {
      // 判断类型
      if (field.name.substr(-3) !== 'pem') {
        this.$message({
          showClose: true,
          message: '请上传后缀为.pem的证书文件',
          type: 'warning'
        })
        return false
      }
    },
    uploadPem(item) {
      var form = new FormData()
      form.append('file', item.file)
      if (item.data) {
        form.append('data', JSON.stringify(item.data))
      }
      uploadPem(form).then(res => {
        if (item.data.type === 'cert') {
          this.$set(this.form, 'apiclient_cert', res.data)
        } else if (item.data.type === 'key') {
          this.$set(this.form, 'apiclient_key', res.data)
        }

        this.$message.success('上传成功')
      })
    },
    doSearch() {
      this.page = 1
      this.getList()
    }
  }
}
</script>
<style scoped>
  .el-input {
    width: 240px;
  }
  .el-select {
    width: 240px;
  }
  .el-switch {
    transform: scale(0.80);
  }
  .textarea {
    width: 400px;
    max-width: 100%;
  }
  .upload_status {
    color: #149314;
    font-weight: bold;
  }
</style>
