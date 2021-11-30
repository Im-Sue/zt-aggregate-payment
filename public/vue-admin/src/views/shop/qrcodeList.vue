<template>
  <div>
    <el-drawer
      title="收款码管理"
      :visible.sync="listShow"
      direction="rtl"
      size="1200px"
      :close-on-click-modal="false"
      :before-close="closeList"
    >
      <div style="padding: 0 20px;">
        <div class="dialog-toolbar" style="padding-bottom: 20px;">
          <el-button type="primary" icon="el-icon-plus" size="mini" @click="clickCreate">创建收款码</el-button>
        </div>
        <el-table
          :data="dataList"
          stripe
          size="medium"
        >
          <el-table-column type="index" label="序号" width="60" :index="tableIndex" />
          <el-table-column property="add_time" label="创建时间" width="160" />
          <el-table-column property="qrcode" label="收款码" width="80">
            <template slot-scope="scope">
              <img :src="scope.row.qrcode" style="width:40px; height:40px;" @click="showBigQrcode(scope.row.qrcode)">
            </template>
          </el-table-column>
          <el-table-column property="title" label="收款码名称" />
          <el-table-column property="speaker" label="收款音箱" width="100" />
          <el-table-column label="操作">
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
      </div>
    <!--</el-dialog>-->

    </el-drawer>

    <div v-if="form">
      <el-dialog
        custom-class="my-dialog"
        title="创建收款码"
        :visible="true"
        width="500px"
        :close-on-click-modal="false"
        :append-to-body="true"
        :before-close="closeForm"
      >
        <el-form ref="form" :model="form" :rules="formRules" label-width="120px">
          <el-form-item label="收款码名称" prop="title">
            <el-input v-model="form.title" placeholder="" size="small" style="width: 260px;" />
          </el-form-item>
          <el-form-item label="收款音箱" prop="speaker_status">
            <el-switch
              v-model="form.speaker_status"
              :active-value="1"
              :inactive-value="0"
              @change="switchSpeakerStatus($event)"
            />
          </el-form-item>
          <div v-if="form.speaker_status === 1">
            <el-form-item label="设备品牌" prop="speaker_brand">
              <el-radio-group v-model="form.speaker_brand" size="small">
                <el-radio label="pinsheng" :selected="true">深圳品生</el-radio>
              </el-radio-group>
            </el-form-item>
            <div v-if="form.speaker_brand === 'pinsheng'">
              <el-form-item label="设备id" prop="speaker_devid">
                <el-input v-model="form.speaker_devid" placeholder="音箱设备id" size="small" />
              </el-form-item>
            </div>
          </div>
        </el-form>
        <span slot="footer" class="dialog-footer">
          <el-button type="default" icon="el-icon-close" size="small" @click="closeForm">取 消</el-button>
          <el-button v-if="form.speaker_status === 1" type="warning" icon="el-icon-phone-outline" size="small" @click="speakerTest">测试音箱配置</el-button>
          <el-button type="primary" icon="el-icon-check" size="small" @click="clickSubmit">提 交</el-button>
        </span>
      </el-dialog>
    </div>

    <div v-if="bigQrcodeUrl">
      <el-dialog
        custom-class="my-dialog"
        title="查看收款码"
        :visible="true"
        width="400px"
        :close-on-click-modal="false"
        :before-close="closeBigQrcode"
      >
        <el-row style="text-align: center;">
          <img :src="bigQrcodeUrl" style="width:300px;">
        </el-row>
      </el-dialog>
    </div>
  </div>
</template>

<script>
import {
  getList,
  getInfo,
  saveInfo,
  del,
  speakerTest
} from '@/api/qrcode'

export default {
  props: {
    // 表格曲线文字提示
    isShow: {
      type: Boolean,
      default: false
    },
    // eslint-disable-next-line vue/prop-name-casing
    shop_id: {
      type: Number,
      default: 0
    }
  },
  data() {
    return {
      // 列表
      listShow: false,
      dataList: [],
      dataTotal: 0,
      page: 1,
      pagesize: 10,
      // 查看收款码大图
      bigQrcodeUrl: '',
      form: null,
      formType: null,
      formRules: {
        title: [
          { required: true, message: '此项必填', trigger: 'blur' }
        ],
        speaker_brand: [
          { required: true, message: '此项必填', trigger: 'blur' }
        ],
        speaker_devid: [
          { required: true, message: '此项必填', trigger: 'blur' }
        ]
      }
    }
  },
  computed: {
    formTitle() {
      return this.formType === 'add' ? '创建收款码' : '编辑'
    }
  },
  watch: {
    isShow(val) {
      this.listShow = val
      if (val) {
        this.getList()
      }
    }
  },
  methods: {
    closeList() {
      this.dataList = null
      this.dataTotal = 0
      this.page = 1
      this.$emit('close')
    },
    tableIndex(index) {
      return this.pagesize * (this.page - 1) + index + 1
    },
    getList() {
      getList({
        shop_id: this.shop_id,
        page: this.page,
        pagesize: this.pagesize
      }).then(res => {
        this.dataList = res.data.list
        this.dataTotal = res.data.count
      })
    },
    pageChange(page) {
      this.page = page
      this.getList()
    },
    clickCreate() {
      this.form = {}
      this.formType = 'add'
    },
    clickEdit(id) {
      getInfo({ id: id }).then(res => {
        this.form = res.data
        this.formType = 'edit'
      })
    },
    // 关闭form弹框
    closeForm() {
      this.form = null
      this.formType = ''
    },
    closeBigQrcode() {
      this.bigQrcodeUrl = ''
    },
    // 语音播报开关
    switchSpeakerStatus(value) {
      this.form.speaker_status = value
    },
    showBigQrcode(url) {
      this.bigQrcodeUrl = url + '?r=' + Math.random()
    },
    speakerTest() {
      const postData = {
        speaker_brand: this.form.speaker_brand
      }
      if (this.form.speaker_brand === 'pinsheng') {
        if (!this.form.speaker_devid) {
          this.$message({
            message: '请填写设备id',
            type: 'error',
            duration: 1500
          })
          return
        }
        postData.speaker_devid = this.form.speaker_devid
      }
      speakerTest(postData).then(res => {
        this.$message({
          message: res.message,
          type: 'success',
          duration: 1500
        })
      })
    },
    clickSubmit() {
      this.$refs.form.validate((valid) => {
        if (valid) {
          this.form.shop_id = this.shop_id
          saveInfo(this.form).then(res => {
            this.page = 1
            this.getList()
            this.$message({
              message: res.message,
              type: 'success',
              duration: 1500
            })
            this.closeForm()
          })
        }
      })
    },
    clickDel(id) {
      this.$confirm('删除后不可恢复，确认删除吗?', '提示', {
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
    }

  }
}
</script>
<style scoped>
  .el-input {
    width: 200px;
  }
  .el-select {
    width: 200px;
  }
  .el-switch {
    transform: scale(0.80);
  }
</style>

