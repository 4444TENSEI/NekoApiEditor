<!-- eslint-disable vue/no-v-text-v-html-on-component -->

<template>
  <!-- 顶部导航栏 -->
  <v-app-bar class="position-fixed" :elevation="0">
    <!-- 左上角汉堡按钮 -->
    <v-app-bar-nav-icon
      variant="text"
      @click.stop="drawer = !drawer"
    ></v-app-bar-nav-icon>

    <v-spacer></v-spacer>

    <!-- 顶部标题文字 -->
    <!-- <v-app-bar-title>{{ currentMenuItem }}</v-app-bar-title> -->

    <v-btn icon="mdi-arrow-up-bold" @click="scrollToTop"></v-btn>
    <v-btn icon="mdi-arrow-down-bold" @click="scrollToBottom"></v-btn>
    <v-btn color="primary" icon="mdi-check-all" @click="toggleSelect"></v-btn>
    <v-btn color="error" icon="mdi-delete" @click="deleteSelected"></v-btn>

    <!-- 顶栏右上角头像 -->
    <!-- <v-list>
      <v-list-item
        prepend-avatar="https://f.yokaze.top/d/%F0%9F%90%B1InfiniCLOUD-25G/blog_img/usagi.png?sign=T3wf_v3r9yhMKKUFra8Ccfe0zvdqL5OAS8OkfuGYeMA=:0"
      ></v-list-item>
    </v-list> -->
  </v-app-bar>

  <!-- 左侧侧边栏，可设置手机和电脑不同宽度 -->
  <v-navigation-drawer
    class="position-fixed"
    v-model="drawer"
    :location="$vuetify.display.mobile ? 'left' : undefined"
    temporary
    :width="$vuetify.display.mobile ? '290' : '360'"
    opacity-30
  >
    <!-- 侧边栏头像 -->
    <v-list>
      <v-list-item
        prepend-avatar="https://f.yokaze.top/d/%F0%9F%90%B1InfiniCLOUD-25G/blog_img/usagi.png?sign=T3wf_v3r9yhMKKUFra8Ccfe0zvdqL5OAS8OkfuGYeMA=:0"
        subtitle="2479757568@qq.com"
        title="🐱"
        height="100px"
      ></v-list-item>
    </v-list>

    <!-- 分割线 -->
    <v-divider></v-divider>

    <!-- 侧边栏子菜单 -->
    <v-list v-model:opened="open">
      <v-list-group value="Actions">
        <template v-slot:activator="{ props }">
          <v-list-subheader>设置</v-list-subheader>
          <v-list-item
            v-bind="props"
            prepend-icon="mdi-account-circle"
            title="个人中心"
            color="primary"
            rounded="xl"
          ></v-list-item>
        </template>

        <v-list-item
          v-for="(item, i) in account"
          :key="i"
          :value="item"
          color="primary"
          rounded="xl"
          style="padding-left: 8px"
        >
          <template v-slot:prepend>
            <v-icon :icon="item.icon"></v-icon>
          </template>

          <v-list-item-title v-text="item.text"></v-list-item-title>
        </v-list-item>
      </v-list-group>
    </v-list>

    <!-- 边栏功能区 -->
    <v-list>
      <v-list-subheader>功能</v-list-subheader>

      <v-list-item @click="showApiDialog = true">
        <template v-slot:prepend>
          <v-icon :icon="ChangeApi.icon"></v-icon>
        </template>

        <v-list-item-title v-text="ChangeApi.text"></v-list-item-title>
      </v-list-item>

      <!-- 弹窗组件 -->
      <v-dialog v-model="showApiDialog" persistent max-width="600px">
        <v-card>
          <v-card-title>
            <span class="headline">设置API地址</span>
          </v-card-title>
          <v-card-text>
            <v-container>
              <v-row>
                <v-col cols="12">
                  <v-text-field
                    label="API地址*"
                    v-model="newApiUrl"
                    required
                  ></v-text-field>
                </v-col>
              </v-row>
            </v-container>
          </v-card-text>
          <v-card-actions>
            <v-spacer></v-spacer>
            <v-btn color="blue darken-1" text @click="saveApiUrl">保存</v-btn>
            <v-btn color="blue darken-1" text @click="showApiDialog = false"
              >取消</v-btn
            >
          </v-card-actions>
        </v-card>
      </v-dialog>
    </v-list>

    <!-- 切换主题按钮 -->
    <v-btn
      icon
      @click="toggleTheme"
      style="position: absolute; bottom: 2rem; left: 1.8rem"
    >
      <v-icon>{{ icon }}</v-icon>
    </v-btn>
  </v-navigation-drawer>
</template>

<script setup>
import { useTheme } from "vuetify";
// 导入父组件共享函数
const deleteSelected = inject("deleteSelected");
const scrollToTop = inject("scrollToTop");
const scrollToBottom = inject("scrollToBottom");
const toggleSelect = inject("toggleSelect");

const showApiDialog = ref(false);
const newApiUrl = ref("");

const ChangeApi = { text: "设置API地址", icon: "mdi-server" };

const theme = useTheme();
const drawer = ref(false);
const open = ref(["Users"]);
const account = ref([
  { text: "更改头像", icon: "mdi-camera" },
  { text: "修改用户名", icon: "mdi-card-account-details-outline" },
  { text: "修改密码", icon: "mdi-lock" },
  { text: "注销", icon: "mdi-logout-variant" },
]);

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
