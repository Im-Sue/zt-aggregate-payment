<template>
  <div class="app-container">
    <div class="toolbar">
      <el-button type="primary" icon="el-icon-plus" size="mini" @click="clickCreate">添加商户</el-button>
      <div style="float:right;">
        <el-input
          v-model="search.keyword"
          placeholder="商户名称/地址/姓名/手机号/备注"
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
      <el-table-column prop="add_time" label="创建时间" width="150" />
      <el-table-column prop="title" label="商户名称" />
      <el-table-column prop="link_name" label="联系人" width="100" />
      <el-table-column prop="remark" label="备注" />
      <!--<el-table-column prop="end_time" label="到期时间" width="100" align="center" />-->
      <!--<el-table-column prop="status" label="状态" width="100" align="center">
        <template slot-scope="scope">
          {{ scope.row.status }}
        </template>
      </el-table-column>-->
      <el-table-column label="操作" width="360">
        <template slot-scope="scope">
          <el-button-group>
            <el-button type="text" size="mini" @click.native.prevent="clickEditPay(scope.row.id)"><svg-icon icon-class="setting" /> 支付配置</el-button>
            <el-button type="text" size="mini" @click.native.prevent="showQrcodeList(scope.row.id)"><svg-icon icon-class="qrcode" /> 聚合收款码</el-button>
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

    <div v-if="shopForm">
      <el-dialog
        custom-class="my-dialog"
        :title="shopFormTitle"
        :visible="true"
        width="500px"
        :close-on-click-modal="false"
        :before-close="closeShopForm"
      >
        <el-form ref="shopForm" :model="shopForm" :rules="shopFormRules" label-width="120px">
          <el-form-item label="商户名称" prop="title">
            <el-input v-model="shopForm.title" placeholder="商户名称" size="small" />
          </el-form-item>
          <el-form-item label="地址" prop="address">
            <el-input v-model="shopForm.address" placeholder="商户名称" size="small" />
          </el-form-item>
          <el-form-item label="联系人" prop="link_name">
            <el-input v-model="shopForm.link_name" placeholder="请输入联系人姓名" size="small" />
          </el-form-item>
          <el-form-item label="联系电话" prop="link_phone">
            <el-input v-model="shopForm.link_phone" placeholder="请输入联系电话" size="small" />
          </el-form-item>
          <el-form-item label="备注" prop="remark">
            <el-input
              v-model="shopForm.remark"
              type="textarea"
              placeholder="请输入备注"
              size="small"
              style="width: 300px; max-width: 100%; min-height: 80px;"
            />
          </el-form-item>
        </el-form>
        <span slot="footer" class="dialog-footer">
          <el-button type="default" icon="el-icon-close" size="small" @click="closeShopForm">取 消</el-button>
          <el-button type="primary" icon="el-icon-check" size="small" @click="clickSubmit('shopForm')">提 交</el-button>
        </span>
      </el-dialog>
    </div>

    <div v-if="payForm">
      <el-dialog
        custom-class="my-dialog"
        title="支付参数配置"
        :visible="true"
        width="660px"
        :close-on-click-modal="false"
        :before-close="closePayForm"
      >
        <el-form ref="payForm" :model="payForm" :rules="payFormRules" label-width="100px">
          <div class="form-title">支付宝</div>
          <el-form-item label="开关" prop="alipay_status">
            <el-switch
              v-model="payForm.alipay_status"
              :active-value="1"
              :inactive-value="0"
              @change="switchAlipayStatus($event)"
            />
          </el-form-item>
          <div v-if="payForm.alipay_status === 1">
            <el-form-item label="支付模式" prop="alipay_type">
              <el-radio-group v-model="payForm.alipay_type">
                <el-radio label="server">服务商模式</el-radio>
                <el-radio label="shop">普通模式</el-radio>
              </el-radio-group>
            </el-form-item>
            <div v-if="payForm.alipay_type === 'server'">
              <el-form-item label="选择服务商" prop="alipay_server_id">
                <el-select v-model="payForm.alipay_server_id" size="small" placeholder="请选择服务商">
                  <el-option
                    v-for="item in alipayServerList"
                    :key="item.id"
                    :label="item.title"
                    :value="item.id"
                  />
                </el-select>
              </el-form-item>
              <el-form-item label="PID" prop="alipay_pid">
                <el-input v-model="payForm.alipay_pid" placeholder="支付宝PID" size="small" />
              </el-form-item>
              <el-form-item label="授权token" prop="alipay_token">
                <el-input v-model="payForm.alipay_token" placeholder="支付宝授权token" size="small" style="width: 340px;" />
              </el-form-item>
            </div>
            <div v-if="payForm.alipay_type === 'shop'">
              <el-form-item label="AppId" prop="alipay_appid">
                <el-input v-model="payForm.alipay_appid" placeholder="支付宝应用AppId" size="small" />
              </el-form-item>
              <el-form-item label="商户私钥" prop="alipay_private_key">
                <el-input
                  v-model="payForm.alipay_private_key"
                  type="textarea"
                  placeholder="商户私钥"
                  size="small"
                  class="textarea"
                />
              </el-form-item>
              <el-form-item label="支付宝公钥" prop="alipay_public_key">
                <el-input v-model="payForm.alipay_public_key" type="textarea" placeholder="支付宝公钥" size="small" class="textarea" />
              </el-form-item>
            </div>
            <el-form-item label="手续费率" prop="alipay_rate">
              <el-input v-model="payForm.alipay_rate" placeholder="费率" size="small" style="width: 100px;" />
              %
            </el-form-item>
          </div>

          <div class="form-title" style="margin-top: 15px;">微信支付</div>
          <el-form-item label="开关" prop="wxpay_status">
            <el-switch
              v-model="payForm.wxpay_status"
              :active-value="1"
              :inactive-value="0"
              @change="switchWxpayStatus($event)"
            />
          </el-form-item>
          <div v-if="payForm.wxpay_status === 1">
            <el-form-item label="支付模式" prop="wxpay_type">
              <el-radio-group v-model="payForm.wxpay_type">
                <el-radio label="server">服务商模式</el-radio>
                <el-radio label="shop">普通模式</el-radio>
              </el-radio-group>
            </el-form-item>
            <div v-if="payForm.wxpay_type === 'server'">
              <el-form-item label="选择服务商" prop="wxpay_server_id">
                <el-select v-model="payForm.wxpay_server_id" size="small" placeholder="请选择服务商">
                  <el-option
                    v-for="item in wxpayServerList"
                    :key="item.id"
                    :label="item.title"
                    :value="item.id"
                  />
                </el-select>
              </el-form-item>
              <el-form-item label="子商户号" prop="wxpay_mch_id">
                <el-input v-model="payForm.wxpay_mch_id" placeholder="商户子商户号" size="small" />
              </el-form-item>
            </div>
            <div v-if="payForm.wxpay_type === 'shop'">
              <el-form-item label="AppId" prop="wxpay_appid">
                <el-input v-model="payForm.wxpay_appid" placeholder="公众号AppId" size="small" />
              </el-form-item>
              <el-form-item label="AppSecret" prop="wxpay_appsecret">
                <el-input v-model="payForm.wxpay_appsecret" placeholder="公众号AppSecret" size="small" />
              </el-form-item>
              <el-form-item label="商户号" prop="wxpay_mch_id">
                <el-input v-model="payForm.wxpay_mch_id" placeholder="商户号mch_id" size="small" />
              </el-form-item>
              <el-form-item label="Api密钥" prop="wxpay_key">
                <el-input v-model="payForm.wxpay_key" placeholder="Api密钥key" size="small" />
              </el-form-item>
              <el-form-item label="cert证书">
                <el-upload
                  action=""
                  :data="{type: 'cert'}"
                  :before-upload="uploadCheck"
                  :http-request="uploadPem"
                  :show-file-list="false"
                  :multiple="false"
                >
                  <el-button size="small" type="primary">选择文件</el-button>
                </el-upload>
                <span v-if="payForm.wxpay_apiclient_cert" class="upload_status">已上传</span>
              </el-form-item>
              <el-form-item label="key证书">
                <el-upload
                  action=""
                  :data="{type: 'key'}"
                  :before-upload="uploadCheck"
                  :http-request="uploadPem"
                  :show-file-list="false"
                  :multiple="false"
                >
                  <el-button size="small" type="primary">选择文件</el-button>
                </el-upload>
                <span v-if="payForm.wxpay_apiclient_key" class="upload_status">已上传</span>
              </el-form-item>
            </div>
            <el-form-item label="手续费率" prop="wxpay_rate">
              <el-input v-model="payForm.wxpay_rate" placeholder="费率" size="small" style="width: 100px;" />
              %
            </el-form-item>
          </div>
        </el-form>
        <span slot="footer" class="dialog-footer">
          <el-button type="default" icon="el-icon-close" size="small" @click="closePayForm">取 消</el-button>
          <el-button type="primary" icon="el-icon-check" size="small" @click="clickSubmit('payForm')">提 交</el-button>
        </span>
      </el-dialog>
    </div>

    <qrcode-list :is-show="qrcodeListShow" :shop_id="shop_id" @close="closeQrcodeList" />

  </div>
</template>

<script>
import {
  getList,
  getInfo,
  saveInfo,
  del,
  getPayInfo,
  savePayInfo,
  getServerList,
  uploadPem
} from '@/api/shop'
import qrcodeList from './qrcodeList'
import SvgIcon from '@/components/SvgIcon'

export default {
  components: { qrcodeList, SvgIcon },
  data() {
    return {
      shopForm: null,
      shopFormType: null,
      search: {},
      // 商户列表
      dataList: [],
      dataTotal: 0,
      page: 1,
      pagesize: 10,
      shopFormRules: {
        title: [
          { required: true, message: '此项必填', trigger: 'blur' },
        ]
      },
      payForm: null,
      payFormRules: {
        alipay_type: [
          { required: true, message: '此项必填', trigger: 'blur' }
        ],
        alipay_server_id: [
          { required: true, message: '此项必填', trigger: 'blur' }
        ],
        alipay_pid: [
          { required: true, message: '此项必填', trigger: 'blur' }
        ],
        alipay_token: [
          { required: true, message: '此项必填', trigger: 'blur' }
        ],
        alipay_rate: [
          { required: true, message: '此项必填', trigger: 'blur' }
        ],
        alipay_appid: [
          { required: true, message: '此项必填', trigger: 'blur' }
        ],
        alipay_private_key: [
          { required: true, message: '此项必填', trigger: 'blur' }
        ],
        alipay_public_key: [
          { required: true, message: '此项必填', trigger: 'blur' }
        ],
        wxpay_type: [
          { required: true, message: '此项必填', trigger: 'blur' }
        ],
        wxpay_server_id: [
          { required: true, message: '此项必填', trigger: 'blur' }
        ],
        wxpay_mch_id: [
          { required: true, message: '此项必填', trigger: 'blur' }
        ],
        wxpay_appid: [
          { required: true, message: '此项必填', trigger: 'blur' }
        ],
        wxpay_appsecret: [
          { required: true, message: '此项必填', trigger: 'blur' }
        ],
        wxpay_key: [
          { required: true, message: '此项必填', trigger: 'blur' }
        ],
        wxpay_rate: [
          { required: true, message: '此项必填', trigger: 'blur' }
        ]
      },
      qrcodeListShow: false,
      shop_id: 0,
      alipayServerList: null,
      wxpayServerList: null
    }
  },
  computed: {
    shopFormTitle() {
      return this.shopFormType === 'add' ? '添加商户' : '编辑'
    }
  },
  created() {
    this.getList()
    this.getServerList()
  },
  methods: {
    getList() {
      getList({
        page: this.page,
        pagesize: this.pagesize,
        keyword: this.search.keyword
      }).then(res => {
        const data = res.data
        this.dataList = data.list
        this.dataTotal = res.data.count
      })
    },
    getServerList() {
      getServerList().then(res => {
        this.alipayServerList = res.data.alipay
        this.wxpayServerList = res.data.wxpay
      })
    },
    tableIndex(index) {
      return this.pagesize * (this.page - 1) + index + 1
    },
    pageChange(page) {
      this.page = page
      this.getList()
    },
    clickCreate() {
      this.shopForm = {}
      this.shopFormType = 'add'
    },
    clickEdit(id) {
      getInfo({ id: id }).then(res => {
        this.shopForm = res.data
        this.shopFormType = 'edit'
      })
    },
    // 支付宝收款开关
    switchAlipayStatus(value) {
      this.payForm.alipay_status = value
    },
    // 微信收款开关
    switchWxpayStatus(value) {
      this.payForm.wxpay_status = value
    },
    // 点击"设置支付参数"
    clickEditPay(id) {
      getPayInfo({ id: id }).then(res => {
        this.payForm = res.data
      })
    },
    // 关闭商户资料弹框
    closeShopForm() {
      this.shopForm = null
      this.shopFormType = ''
    },
    // 关闭支付参数弹框
    closePayForm() {
      this.payForm = null
      this.payFormType = ''
    },
    showQrcodeList(shop_id) {
      this.shop_id = shop_id
      this.qrcodeListShow = true
    },
    closeQrcodeList() {
      this.shop_id = 0
      this.qrcodeListShow = false
    },
    clickSubmit(formName) {
      if (formName === 'shopForm') {
        this.$refs.shopForm.validate((valid) => {
          if (valid) {
            saveInfo(this.shopForm).then(res => {
              this.page = 1
              this.getList()
              this.$message({
                message: res.message,
                type: 'success',
                duration: 1500
              })
              this.closeShopForm()
            })
          }
        })
      }
      if (formName === 'payForm') {
        this.$refs.payForm.validate((valid) => {
          if (valid) {
            savePayInfo(this.payForm).then(res => {
              this.$message({
                message: res.message,
                type: 'success',
                duration: 1500
              })
              this.closePayForm()
            })
          }
        })
      }
    },
    clickDel(id) {
      this.$confirm('此操作将清除商户资料、支付参数、收款码，删除后不可恢复，确认删除吗?', '提示', {
        confirmButtonText: '确定删除',
        cancelButtonText: '取消',
        type: 'warning'
      }).then(() => {
        del({ id: id }).then(res => {
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
          this.$set(this.payForm, 'wxpay_apiclient_cert', res.data)
        } else if (item.data.type === 'key') {
          this.$set(this.payForm, 'wxpay_apiclient_key', res.data)
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
  .form-title {
    line-height: 40px;
    border-top: 1px solid #e6e6e6;
    padding-left: 20px;
    font-size: 18px;
    margin: 10px 0;
    background: #fafafa;
  }
  .upload_status {
    color: #149314;
    font-weight: bold;
  }
</style>
