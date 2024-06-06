<!-- eslint-disable vue/no-v-text-v-html-on-component -->

<template>
  <!-- 顶部导航栏 -->
  <v-app-bar class="position-fixed" :elevation="0">
    <!-- 左上角汉堡按钮 -->
    <v-app-bar-nav-icon variant="text" @click.stop="drawer = !drawer"></v-app-bar-nav-icon>

    <v-spacer></v-spacer>


    <v-btn icon="mdi-filter" @click="fieldSelectionDialog = true"></v-btn>
    <v-btn icon="mdi-arrow-up-bold" @click="scrollToTop"></v-btn>
    <v-btn icon="mdi-arrow-down-bold" @click="scrollToBottom"></v-btn>
    <v-btn color="primary" icon="mdi-check-all" @click="toggleSelect"></v-btn>
    <v-btn color="error" icon="mdi-delete" @click="deleteSelected"></v-btn>

  </v-app-bar>

  <!-- 左侧侧边栏，可设置手机和电脑不同宽度 -->
  <v-navigation-drawer class="position-fixed" v-model="drawer" :location="$vuetify.display.mobile ? 'left' : undefined"
    temporary :width="$vuetify.display.mobile ? '290' : '360'" opacity-30>
    <v-list>
      <!-- 自定义侧边栏上方头像等 -->
      <v-list-item
        prepend-avatar="https://f.yokaze.top/d/%F0%9F%90%B1InfiniCLOUD-25G/blog_img/usagi.png?sign=T3wf_v3r9yhMKKUFra8Ccfe0zvdqL5OAS8OkfuGYeMA=:0"
        subtitle="xxxxx@qq.com" title="🐱" height="100px"></v-list-item>
    </v-list>

    <!-- 分割线 -->
    <v-divider></v-divider>

    <!-- 侧边栏子菜单 -->
    <v-list v-model:opened="open">
      <v-list-group value="Actions">
        <template v-slot:activator="{ props }">
          <v-list-subheader>账户</v-list-subheader>
          <v-list-item v-bind="props" prepend-icon="mdi-account-circle" title="个人中心" color="info"
            rounded="xl"></v-list-item>
        </template>

        <!-- 更改头像 -->
        <v-list-item @click="changeAvatar" rounded="xl">
          <template v-slot:prepend>
            <v-icon>mdi-camera</v-icon>
          </template>
          <v-list-item-title>更改头像</v-list-item-title>
        </v-list-item>

        <!-- 修改用户名 -->
        <v-list-item @click="changeUsername" rounded="xl">
          <template v-slot:prepend>
            <v-icon>mdi-card-account-details-outline</v-icon>
          </template>
          <v-list-item-title>修改用户名</v-list-item-title>
        </v-list-item>

        <!-- 修改密码 -->
        <v-list-item @click="changePassword" rounded="xl">
          <template v-slot:prepend>
            <v-icon>mdi-lock</v-icon>
          </template>
          <v-list-item-title>修改密码</v-list-item-title>
        </v-list-item>

        <!-- 注销菜单 -->
        <v-list-item @click="showLogoutConfirmDialog = true" rounded="xl">
          <template v-slot:prepend>
            <v-icon>mdi-logout-variant</v-icon>
          </template>
          <v-list-item-title>注销</v-list-item-title>
        </v-list-item>


        <!-- 注销确认弹窗 -->
        <v-dialog v-model="showLogoutConfirmDialog" max-width="340">
          <v-card class="pl-6 pr-6 pt-6 pb-6">
            <v-card-title class="pa-0 pb-3">
              <h3>确认注销</h3>
            </v-card-title>
            <v-card-text class="pa-0">
              <p>🐱：真的要清除连接信息吗？</p>
            </v-card-text>
            <v-card-actions class="pl-6 pt-6 pr-0 pb-0">
              <v-spacer></v-spacer>
              <v-btn color="error" text @click="logout" rounded="pill" variant="tonal" size="large">确定</v-btn>
              <v-btn color="info" text @click="showLogoutConfirmDialog = false" rounded="pill" variant="tonal"
                size="large">取消</v-btn>
            </v-card-actions>
          </v-card>
        </v-dialog>



      </v-list-group>
    </v-list>

    <!-- 边栏功能区 -->
    <v-list>
      <v-list-subheader>功能</v-list-subheader>

      <v-list-item @click="showApiDialog = true" rounded="xl">
        <template v-slot:prepend>
          <v-icon :icon="ChangeApi.icon"></v-icon>
        </template>

        <v-list-item-title v-text="ChangeApi.text"></v-list-item-title>
      </v-list-item>

      <!-- 修改API地址的弹窗 -->
      <v-dialog v-model="showApiDialog" max-width="380px">
        <v-card class="pl-6 pr-6 pt-6 pb-5">
          <v-card-title class="pa-0 pb-3">
            <span class="headline">🐱修改API地址</span>
          </v-card-title>
          <v-card-text class="pa-0">
            <v-container class="pa-0">
              <v-row>
                <v-col cols="12">
                  <v-text-field label="当前API地址" :rules="[isUrl]" v-model="newApiUrl" required
                    hide-details></v-text-field>
                </v-col>
              </v-row>
            </v-container>
          </v-card-text>
          <v-card-actions class="pl-6 pt-6 pr-0 pb-0">
            <v-spacer></v-spacer>
            <v-btn color="success" text @click="saveApiUrl" rounded="pill" variant="tonal" size="large">修改</v-btn>
            <v-btn color="info" text @click="showApiDialog = false" rounded="pill" variant="tonal"
              size="large">取消</v-btn>
          </v-card-actions>
        </v-card>

      </v-dialog>



      <!-- 顶部的提示气泡 -->
      <v-snackbar v-model="apiSnackbar" :timeout="2000" color="success" location="top" rounded="pill">
        API地址已成功更新
      </v-snackbar>
      <v-snackbar v-model="logoutsnackbar" :timeout="2000" color="primary" location="top" rounded="pill">
        注销成功
      </v-snackbar>




    </v-list>

    <!-- 切换主题按钮 -->
    <v-btn icon @click="toggleTheme" style="position: absolute; bottom: 2rem; left: 1.8rem">
      <v-icon>{{ icon }}</v-icon>
    </v-btn>
  </v-navigation-drawer>
</template>

<script setup>
import { useTheme } from "vuetify";
import { useRouter } from 'vue-router';
// 导入父组件共享函数
const deleteSelected = inject("deleteSelected");
const scrollToTop = inject("scrollToTop");
const scrollToBottom = inject("scrollToBottom");
const toggleSelect = inject("toggleSelect");
const fieldSelectionDialog = inject("fieldSelectionDialog ");

const isUrl = inject("isUrl");
const cookies = inject("cookies");
const serverUrl = inject("serverUrl");


// 控制弹窗显示的响应式变量
const logoutsnackbar = ref(false); // 控制提示气泡的显示

const apiSnackbar = ref(false); // 控制API地址修改对话框的显示

const showApiDialog = ref(false);

const showLogoutConfirmDialog = ref(false);




watch(showApiDialog, (newValue) => {
  if (newValue) {
    // 当弹窗打开时，设置newApiUrl为当前的serverUrl
    newApiUrl.value = serverUrl.value;
  }
});
// 新的API地址
const newApiUrl = ref('');

// 保存新的API地址
const saveApiUrl = async () => {
  // 更新serverUrl
  serverUrl.value = newApiUrl.value;

  apiSnackbar.value = true;

  // 将新的API地址存储在cookie中
  cookies.set('serverUrl', serverUrl.value);

  // 假设保存成功，显示成功提示气泡并关闭弹窗
  showApiDialog.value = false;
};




const ChangeApi = { text: "设置API地址", icon: "mdi-server" };



const theme = useTheme();
const drawer = ref(false);
const open = ref(["Users"]);



const changeAvatar = () => {
  console.log("更改头像");
  // 这里可以添加更改头像的逻辑
};
const changeUsername = () => {
  console.log("修改用户名");
  // 这里可以添加修改用户名的逻辑
};
const changePassword = () => {
  console.log("修改密码");
  // 这里可以添加修改密码的逻辑
};

const router = useRouter();


const logout = async () => {

  // 清除用户状态
  cookies.remove("serverUrl"); // 假设您的认证令牌存储在名为 "authToken" 的 cookie 中

  // 重新加载页面
  router.go(0);
  await router.go(0); // 确保路由跳转完成后执行以下代码

  logoutsnackbar.value = true; // 显示弹窗
};



// 底部导航按钮
// const value = ref(1);

// 主题名称数组，包含light、dark和Theme1
const themes = ref(["Theme1", "light"]);
// 当前主题索引，设置为2，因为Theme1是数组的第三个元素
const currentThemeIndex = ref(2);
// 默认按钮图标
let icon = ref("mdi-weather-night");
// 切换主题按钮
const toggleTheme = () => {
  currentThemeIndex.value = (currentThemeIndex.value + 1) % themes.value.length;
  theme.global.name.value = themes.value[currentThemeIndex.value];

  // 根据当前主题切换图标
  switch (themes.value[currentThemeIndex.value]) {
    case "Theme1":
      icon.value = "mdi-weather-night";
      break;
    case "light":
      icon.value = "mdi-white-balance-sunny";
      break;
  }
};

// const currentMenuItem = ref("🐱默认顶栏标题");
// watch(
//   () => route.path,
//   (newPath) => {
//     // 根据路由路径设置不同的标题
//     switch (newPath) {
//       case "/":
//         currentMenuItem.value = "🐱标题";
//         break;

//     }
//   },
//   {
//     immediate: true, // 立即执行一次以获取初始值
//   }
// );


</script>
