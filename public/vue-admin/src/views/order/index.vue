<template>
  <div class="app-container">
    <div class="toolbar" style="display: flex; justify-content: space-between;">
      <div class="tongji">
        <span><i class="el-icon-s-data text-green" />订单笔数:{{ tongji.orderCount }}</span>
        <span><i class="el-icon-s-data text-danger" />订单金额:{{ tongji.orderAmount }}</span>
      </div>
      <div>
        <el-select
          v-model="form.pay_type"
          size="small"
        >
          <el-option
            v-for="item in payTypes"
            :key="item.name"
            :label="item.title"
            :value="item.name"
          />
        </el-select>
        <el-select
          v-model="form.shop_id"
          clearable
          filterable
          remote
          reserve-keyword
          size="small"
          placeholder="请输入商户名称"
          :remote-method="remoteShop"
          :loading="remoteLoading"
          style="margin-left: 10px;"
        >
          <el-option
            v-for="item in remoteShopList"
            :key="item.id"
            :label="item.title"
            :value="item.id"
          />
        </el-select>
        <el-select
          v-model="form.qrcode_id"
          clearable
          filterable
          remote
          reserve-keyword
          size="small"
          placeholder="收款码名称"
          :remote-method="remoteQrcode"
          :loading="remoteLoading"
          style="margin-left: 10px;"
        >
          <el-option
            v-for="item in remoteQrcodeList"
            :key="item.id"
            :label="item.title"
            :value="item.id"
          />
        </el-select>
        <el-date-picker
          v-model="form.date"
          type="daterange"
          format="yyyy-MM-dd"
          range-separator="至"
          start-placeholder="支付时间-开始"
          end-placeholder="支付时间-结束"
          size="small"
          style="margin-left: 10px;"
        />
        <el-button
          type="primary"
          icon="el-icon-search"
          size="mini"
          style="margin-left: 10px;"
          @click="clickSearch()"
        >
          筛选
        </el-button>
      </div>
    </div>
    <el-table
      :data="dataList"
      element-loading-text="加载中..."
      stripe
      size="medium"
      header-cell-class-name="bg-gray"
    >
      <el-table-column width="52">
        <template slot-scope="scope">
          <span v-if="scope.row.pay_type === 'alipay'" style="font-size: 40px;"><svg-icon icon-class="alipay" style="color:#027aff;" /></span>
          <span v-if="scope.row.pay_type === 'wxpay'" style="font-size: 40px;"><svg-icon icon-class="wxpay" style="color:#04BE02;" /></span>
        </template>
      </el-table-column>
      <el-table-column prop="pay_time" label="渠道 / 支付时间" width="180">
        <template slot-scope="scope">
          <p v-if="scope.row.pay_type === 'alipay'">支付宝</p>
          <p v-if="scope.row.pay_type === 'wxpay'">微信</p>
          <p class="small">{{ scope.row.pay_time }}</p>
        </template>
      </el-table-column>
      <el-table-column prop="pay_type" label="商户单号 / 渠道单号" width="250">
        <template slot-scope="scope">
          <p>{{ scope.row.out_trade_no }}</p>
          <p class="small">{{ scope.row.transaction_id }}</p>
        </template>
      </el-table-column>
      <el-table-column prop="shop_title" label="商户 / 收款码名称" width="180">
        <template slot-scope="scope">
          <p>{{ scope.row.shop_title }}</p>
          <p class="small">{{ scope.row.qrcode_title }}</p>
        </template>
      </el-table-column>
      <el-table-column prop="total_fee" label="订单金额" width="120" align="center" />
      <el-table-column prop="settlement_total_fee" label="客户实付" width="120" align="center" />
      <el-table-column prop="fee" label="手续费" width="120" align="center" />
      <el-table-column label="状态" width="100">
        <p class="text-green">支付成功</p>
      </el-table-column>
      <el-table-column prop="remark" label="客户留言" />
      <!--<el-table-column label="操作" width="100">
        <template slot-scope="scope">
          <el-button type="text text-primary" size="mini" icon="el-icon-delete" @click.native.prevent="clickRefund(scope.row.id)">退款</el-button>
        </template>
      </el-table-column>-->
    </el-table>
    <el-pagination
      :current-page="page"
      :page-size="pagesize"
      layout="total, prev, pager, next"
      :total="dataTotal"
      @current-change="pageChange"
    />
  </div>
</template>

<script>
import { getList, getTongji, getShopList, getQrcodeList, refund } from '@/api/order'

export default {
  data() {
    return {
      form: {
        pay_type: '',
        qrcode_id: '',
        shop_id: '',
        date: []
      },
      tongji: {
        orderCount: 0,
        orderAmount: 0,
        couponCount: 0
      },
      payTypes: [
        { name: '', title: '全部渠道' },
        { name: 'wxpay', title: '微信' },
        { name: 'alipay', title: '支付宝' }
      ],
      dataList: [],
      dataTotal: 0,
      page: 1,
      pagesize: 15,
      remoteLoading: false,
      remoteShopList: [],
      remoteQrcodeList: []
    }
  },
  computed: {
    role() {
      return this.$store.getters.role
    }
  },
  created() {
    var now = new Date()
    const today = now.getFullYear() + '-' + (now.getMonth() + 1) + '-' + now.getDate()
    this.form.date = [today, today]
    this.getList()
  },
  methods: {
    getList() {
      const param = {
        page: this.page,
        pagesize: this.pagesize,
        pay_type: this.form.pay_type,
        qrcode_id: this.form.qrcode_id,
        shop_id: this.form.shop_id,
        date: this.form.date
      }
      getList(param).then(res => {
        const data = res.data
        this.dataList = data.list
        this.dataTotal = res.data.count
      })
      if (this.page === 1) {
        getTongji(param).then(res => {
          this.tongji = res.data
        })
      }
    },
    tableIndex(index) {
      return this.pagesize * (this.page - 1) + index + 1
    },
    pageChange(page) {
      this.page = page
      this.getList()
    },
    remoteShop(query) {
      if (query !== '') {
        this.remoteLoading = true
        getShopList({ keyword: query }).then(res => {
          this.remoteLoading = false
          this.remoteShopList = res.data
        })
      } else {
        this.remoteShopList = []
      }
    },
    remoteQrcode(query) {
      if (query !== '') {
        this.remoteLoading = true
        getQrcodeList({ keyword: query }).then(res => {
          this.remoteLoading = false
          this.remoteQrcodeList = res.data
        })
      } else {
        this.remoteQrcodeList = []
      }
    },
    clickRefund(id) {
      var pwd = window.prompt('输入退款密码', '')
      refund({
        id: id,
        pwd: pwd
      }).then(res => {
        this.getList()
        this.$message({
          message: res.message,
          type: 'success',
          duration: 1500
        })
      })
    },
    clickSearch() {
      this.page = 1
      this.getList()
    }
  }
}
</script>
<style>
  .el-input{
    width: 140px;
  }
</style>
<style scoped>
  .tongji {
    height: 32px;
    line-height: 32px;
    font-size: 14px;
    color: #666;
    min-width: 240px;
  }
  .tongji span {
    margin-right: 25px;
  }
  .tongji span i {
    font-size: 18px;
    margin-right: 5px;
  }
  .el-table p {
    margin: 0;
  }
  .el-table p.small {
    font-size: 12px;
    color: #999;
  }
</style>
