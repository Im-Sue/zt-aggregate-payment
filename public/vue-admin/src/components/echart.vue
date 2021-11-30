<template slot-scope="scope">
  <div>
    <div ref="echart" :style="{ width: width, height: height }" />
  </div>
</template>
<script>
import Echarts from 'echarts'
export default {
  name: 'Echart',
  props: {
    // 表格曲线文字提示
    tips: {
      type: String,
      default: ''
    },
    // 图标标题
    title: {
      type: Object,
      default: null
    },
    // 线条的颜色
    color: {
      type: Array,
      default: () => {
        return ['#f9a026', '#fff100', '#8fc31f', '#e60012']
      }
    },
    // y轴名字
    yname: {
      type: String,
      default: ''
    },
    yAxis: {
      type: Array,
      default: null
    },
    // y轴名字
    series: {
      type: Array,
      default: () => {
        return []
      }
    },
    // x轴
    xAxis: {
      type: Object,
      default: null
    },
    // 宽高
    width: {
      type: String,
      default: '100%'
    },
    height: {
      type: String,
      default: '300px'
    },
    toolbox: {
      type: Object,
      default: null
    },
    tooltip: {
      type: Object,
      default: () => {
        return {
          show: true,
          trigger: 'axis',
          showContent: true,
          backgroundColor: 'rgba(29, 56, 136, 0.7)',
          borderWidth: 0,
          textStyle: {
            color: '#fff'
          }
        }
      }
    },
    legend: {
      type: Object,
      default: null
    },
    textStyle: {
      type: Object,
      default: () => {
        return {
          color: '#444'
        }
      }
    },
    grid: {
      type: Object,
      default: null
    },
    dataZoom: {
      type: Array,
      default: null
    }
  },
  data() {
    return {
      echart: null
    }
  },
  watch: {
    series: function(val) {
      this.draw()
    }
  },
  mounted() {
    this.draw()
  },
  methods: {
    draw() {
      if (!this.echart) {
        this.echart = Echarts.init(this.$refs.echart)
      }
      var option = {
        series: this.series
      }
      if (this.yAxis) {
        option.yAxis = this.yAxis
      } else if (this.yname) {
        option.yAxis = {
          name: this.yname,
          nameTextStyle: {
            color: '#fff'
          },
          splitLine: {
            show: true,
            lineStyle: {
              color: '#eee'
            }
          }
        }
      }
      this.xAxis && (option.xAxis = this.xAxis)
      this.title && (option.title = this.title)
      this.textStyle && (option.textStyle = this.textStyle)
      this.legend && (option.legend = this.legend)
      this.color && (option.color = this.color) // 线条颜色
      this.tooltip && (option.tooltip = this.tooltip)
      this.toolbox && (option.toolbox = this.toolbox) // 图表工具
      this.grid && (option.grid = this.grid) // 边界空白宽度
      this.dataZoom && (option.dataZoom = this.dataZoom)
      this.echart.setOption(option)
    },
    resize() {
      if (this.echart) {
        this.echart.resize()
      }
    }
  }
}
</script>
