// Styles
import '@mdi/font/css/materialdesignicons.css'
import 'vuetify/styles'

// Vuetify
import { createVuetify } from 'vuetify'


const Theme1 = {
  dark: true, // 您可以根据需要设置 true 或 false
  colors: {
    background: "#0d1b2a", // 深蓝色背景
    surface: "#1c2e3f", // 浅一些的蓝色表面
    primary: "#2196f3", // 蓝色主色调
    secondary: "#64b5f6", // 淡一些的蓝色次色调
    error: "#ff5252", // 红色错误提示
    info: "#2196f3", // 蓝色信息提示
    success: "#4caf50", // 绿色成功提示
    warning: "#ff9800", // 橙色警告提示
  },
};



export default createVuetify(

  // https://vuetifyjs.com/en/introduction/why-vuetify/#feature-guides
  {

    theme: {
      defaultTheme: 'Theme1',
      themes: {
        Theme1,
      }
    }
  }
)