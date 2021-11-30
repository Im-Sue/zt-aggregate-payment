<template>
  <div class="app-container">
    <el-tabs v-model="tabName" @tab-click="switchTab">
      <el-tab-pane label="支付后广告配置" name="ad">
        <el-form v-if="form" ref="adForm" :model="form" :rules="formRules" label-width="120px">
          <div class="form-title">支付宝</div>
          <el-form-item label="支付后" prop="ad_alipay_type">
            <el-radio-group v-model="form['ad_alipay_type']" size="small">
              <el-radio label="" :selected="true">默认</el-radio>
              <el-radio label="image" :selected="true">展示广告图</el-radio>
              <el-radio label="link" :selected="true">自动跳转链接</el-radio>
            </el-radio-group>
          </el-form-item>
          <el-form-item v-if="form['ad_alipay_type'] === 'image'" label="广告标题" prop="ad_alipay_image_title">
            <el-input v-model="form['ad_alipay_image_title']" placeholder="留空则不显示" size="small" />
          </el-form-item>
          <el-form-item v-if="form['ad_alipay_type'] === 'image'" label="广告图" prop="ad_alipay_image">
            <el-input v-model="form['ad_alipay_image']" placeholder="输入图片地址或上传图片" size="small" />
            <el-upload
              class="avatar-uploader"
              action=""
              :data="{type: 'ad_alipay_image'}"
              :http-request="uploadImage"
              :show-file-list="false"
              :multiple="false"
            >
              <img v-if="form['ad_alipay_image']" :src="form['ad_alipay_image']" class="avatar" style="height: 120px; width: auto;">
              <i v-else class="el-icon-plus avatar-uploader-icon" style="width: 237px; height: 120px; line-height:120px;" />
            </el-upload>
          </el-form-item>
          <el-form-item v-if="form['ad_alipay_type'] === 'image'" label="点击跳转" prop="ad_alipay_image_link">
            <el-input v-model="form['ad_alipay_image_link']" placeholder="http://" size="small" />
          </el-form-item>
          <el-form-item v-if="form['ad_alipay_type'] === 'link'" label="链接" prop="ad_alipay_link">
            <el-input v-model="form['ad_alipay_link']" placeholder="http://" size="small" />
          </el-form-item>

          <div class="form-title">微信</div>
          <el-alert
            title="微信服务商参加点金计划后，广告将被微信接管，此处配置失效"
            type="warning"
            :closable="false"
            style="width: 580px; margin: 10px 20px;"
          />
          <el-form-item label="支付后" prop="ad_wxpay_type">
            <el-radio-group v-model="form['ad_wxpay_type']" size="small">
              <el-radio label="" :selected="true">默认</el-radio>
              <el-radio label="image" :selected="true">展示广告图</el-radio>
              <el-radio label="link" :selected="true">自动跳转链接</el-radio>
            </el-radio-group>
          </el-form-item>
          <el-form-item v-if="form['ad_wxpay_type'] === 'image'" label="广告标题" prop="ad_wxpay_image_title">
            <el-input v-model="form['ad_wxpay_image_title']" placeholder="留空则不显示" size="small" />
          </el-form-item>
          <el-form-item v-if="form['ad_wxpay_type'] === 'image'" label="广告图" prop="ad_wxpay_image">
            <el-input v-model="form['ad_wxpay_image']" placeholder="输入图片地址或上传图片" size="small" />
            <el-upload
              class="avatar-uploader"
              action=""
              :data="{type: 'ad_wxpay_image'}"
              :http-request="uploadImage"
              :show-file-list="false"
              :multiple="false"
            >
              <img v-if="form['ad_wxpay_image']" :src="form['ad_wxpay_image']" class="avatar" style="height: 120px; width: auto;">
              <i v-else class="el-icon-plus avatar-uploader-icon" style="width: 237px; height: 120px; line-height:120px;" />
            </el-upload>
          </el-form-item>
          <el-form-item v-if="form['ad_wxpay_type'] === 'image'" label="点击跳转" prop="ad_wxpay_image_link">
            <el-input v-model="form['ad_wxpay_image_link']" placeholder="http://" size="small" />
          </el-form-item>
          <el-form-item v-if="form['ad_wxpay_type'] === 'link'" label="链接" prop="ad_wxpay_link">
            <el-input v-model="form['ad_wxpay_link']" placeholder="http://" size="small" />
          </el-form-item>

          <el-form-item label="">
            <el-button type="primary" icon="el-icon-check" size="small" @click="clickSubmit">提 交</el-button>
          </el-form-item>
        </el-form>
      </el-tab-pane>
      <el-tab-pane label="收款音箱配置" name="speaker">
        <el-form v-if="form" ref="speakerForm" :model="form" :rules="formRules" label-width="120px">
          <div class="form-title">深圳品生</div>
          <el-form-item label="代理账号" prop="pinsheng_username">
            <el-input v-model="form['pinsheng_username']" placeholder="厂商分配，获取token使用" size="small" />
          </el-form-item>
          <el-form-item label="密码" prop="pinsheng_password">
            <el-input v-model="form['pinsheng_password']" placeholder="密码" size="small" />
          </el-form-item>
          <el-form-item label="">
            <el-button type="primary" icon="el-icon-check" size="small" @click="clickSubmit">提 交</el-button>
          </el-form-item>
        </el-form>
      </el-tab-pane>
    </el-tabs>

  </div>
</template>

<script>
import { getSetting, setSetting, uploadImage } from '@/api/setting'

export default {
  data() {
    return {
      tabName: 'ad',
      form: null,
      formRules: {
        'ad_alipay_image': [
          { required: true, message: '此项必填', trigger: 'blur' }
        ],
        'ad_alipay_link': [
          { required: true, message: '此项必填', trigger: 'blur' }
        ],
        'ad_wxpay_image': [
          { required: true, message: '此项必填', trigger: 'blur' }
        ],
        'ad_wxpay_link': [
          { required: true, message: '此项必填', trigger: 'blur' }
        ],
        'pinsheng_username': [
          { required: true, message: '此项必填', trigger: 'blur' }
        ],
        'pinsheng_password': [
          { required: true, message: '此项必填', trigger: 'blur' }
        ]
      }
    }
  },
  mounted() {
    this.getSetting()
  },
  methods: {
    getSetting() {
      getSetting({ name: this.tabName }).then(res => {
        this.form = res.data
      })
    },
    switchTab() {
      this.getSetting()
    },
    clickSubmit() {
      var formObj = null
      if (this.tabName === 'ad') {
        formObj = this.$refs.adForm
      } else if (this.tabName === 'speaker') {
        formObj = this.$refs.speakerForm
      }
      if (!formObj) {
        return
      }
      formObj.validate((valid) => {
        if (valid) {
          console.log('this.form', this.form)
          setSetting({
            name: this.tabName,
            data: JSON.stringify(this.form)
          }).then(res => {
            this.getSetting()
            this.$message({
              message: res.message,
              type: 'success',
              duration: 1500
            })
          })
        } else {
          this.$message({
            message: '请填写必填项',
            type: 'error',
            duration: 1500
          })
        }
      })
    },
    uploadImage(item) {
      var form = new FormData()
      form.append('file', item.file)
      if (item.data) {
        form.append('data', JSON.stringify(item.data))
      }
      uploadImage(form).then(res => {
        if (item.data.type === 'ad_alipay_image') {
          this.$set(this.form, 'ad_alipay_image', res.data.path)
        } else if (item.data.type === 'ad_wxpay_image') {
          this.$set(this.form, 'ad_wxpay_image', res.data.path)
        }

        this.$message.success('上传成功')
      })
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
  .form-title {
    width: 600px;
    border-bottom: 1px solid #e2e2e2;
    height: 44px;
    line-height:44px;
    margin: 15px;
    background: #f8f8f8;
    padding-left: 20px;
    font-size: 16px;
    font-weight: 500;
  }
</style>
