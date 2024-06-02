<template>
  <v-app>
    <NekoFrame />

    <v-main>
      <!-- <router-view /> -->

      <div v-if="desserts === ''">暂时还没有数据</div>
      <!-- 数据表格组件 -->
      <v-data-table
        v-if="desserts.length > 0"
        :headers="headers"
        :items="desserts"
        :item-value="getItemValue"
        :show-select="showSelect"
        :search="search"
        :show-expand="false"
        v-model="selectedItems"
        class="mobile-table"
      >
        <!-- 表格项中的操作列 -->
        <template v-slot:[`item.data`]="{ item }">
          <v-btn
            id="Operation-Button"
            icon
            @click="copyData(item.data)"
            size="small"
            color="primary"
          >
            <v-icon>mdi-content-copy</v-icon>
          </v-btn>
          <v-btn
            id="Operation-Button"
            icon
            @click="deleteItem(item.id)"
            size="small"
            color="error"
          >
            <v-icon>mdi-delete</v-icon>
          </v-btn>
        </template>

        <template v-slot:top>
          <v-text-field
            v-model="search"
            label="搜索"
            class="search-1"
            hide-details
            variant="outlined"
            clearable
            append-inner-icon="mdi-magnify"
          ></v-text-field>
        </template>
      </v-data-table>

      <!-- 确认删除的对话框 -->
      <v-dialog v-model="confirmDialog" persistent max-width="290">
        <v-card>
          <v-card-title>
            <h4>确认删除</h4>
          </v-card-title>
          <v-card-text
            >确定要删除选中的{{ selectedItems.length }}条项目吗？</v-card-text
          >
          <v-card-actions>
            <v-spacer></v-spacer>
            <v-btn color="error" text @click="confirmDelete">确定</v-btn>
            <v-btn color="green darken-1" text @click="confirmDialog = false"
              >取消</v-btn
            >
          </v-card-actions>
        </v-card>
      </v-dialog>

      <!-- 提示气泡 -->
      <v-snackbar
        v-model="snackbar"
        :timeout="2000"
        color="primary"
        location="top"
        rounded="pill"
      >
        复制Cookie成功
      </v-snackbar>
      <v-snackbar
        v-model="deleteSnackbar"
        :timeout="2000"
        color="success"
        rounded="pill"
        location="top"
      >
        删除成功喵~
      </v-snackbar>
      <!-- 没有选择项目的提示气泡 -->
      <v-snackbar
        v-model="noSelectionSnackbar"
        :timeout="2000"
        color="info"
        location="top"
        rounded="pill"
      >
        没有任何勾选喵~
      </v-snackbar>
      <!-- API服务链接失败提示 -->
      <v-snackbar
        v-model="connectionFailedSnackbar"
        :timeout="2000"
        color="error"
        location="top"
        rounded="pill"
      >
        连接失败，请检查URL是否正确或服务是否可用。
      </v-snackbar>

      <!-- API服务链接解析错误提示 -->
      <v-snackbar
        v-model="parseErrorSnackbar"
        :timeout="2000"
        color="error"
        location="top"
        rounded="pill"
      >
        解析响应失败，请确保后端返回了正确的JSON格式。
      </v-snackbar>
      <!-- 服务URL表单 -->

      <v-sheet
        v-if="!serverUrl || connecting"
        class="h-100 w-100 mx-auto d-flex flex-column align-center justify-center"
      >
        <v-card
          width="370"
          class="px-6 py-6 d-flex flex-column align-center justify-center"
        >
          <v-form
            v-model="form"
            @submit.prevent="setServerUrl"
            class="w-100 d-flex flex-column align-center justify-center"
          >
            <v-text-field
              v-model="inputServerUrl"
              :readonly="connecting"
              :rules="[required, allowNull, isUrl]"
              label="🐱API链接"
              variant="outlined"
              clearable
              hide-details
              append-inner-icon="mdi-link-variant"
              style="height: 3.8rem; margin-bottom: 1rem"
              class="w-100"
            ></v-text-field>

            <v-btn
              :loading="connecting"
              color="info"
              size="large"
              type="submit"
              variant="elevated"
              block
              rounded="pill"
              style="height: 3.4rem"
              class="w-100"
            >
              🐱连接
            </v-btn>
          </v-form>
        </v-card>
      </v-sheet>
    </v-main>
  </v-app>
</template>

<script setup>
import { ref, onMounted } from "vue";
import { useCookies } from "@vueuse/integrations/useCookies";
import axios from "axios";

const serverUrl = ref("");
const inputServerUrl = ref("");
const connecting = ref(false);
const error = ref(null);

const cookies = useCookies(); // 创建一个 cookies 实例

const form = ref(false);
// 表单格式验证
const allowNull = (value) => null;
const required = (value) => !!value || "This field is required";
const isUrl = (value) => {
  const urlPattern =
    /^(?:https?:\/\/)?(?:localhost|(?:(?:[a-zA-Z0-9-]+\.)*[a-zA-Z0-9-]+\.[a-zA-Z]{2,})(?::\d{2,5})?(?:\/[\S]*)?)$/;
  return urlPattern.test(value);
};

const connectionFailedSnackbar = ref(false);
const parseErrorSnackbar = ref(false);
// 设置 serverUrl后保存到 cookies

const setServerUrl = async () => {
  connecting.value = true;
  error.value = null;
  try {
    const response = await axios.get(inputServerUrl.value, {
      headers: {
        Accept: "application/json",
      },
    });
    if (response.status === 200) {
      try {
        // 尝试解析JSON
        const data = response.data;
        if (typeof data === "object" && data !== null) {
          // 如果解析成功，并且数据是对象类型，继续处理数据
          serverUrl.value = inputServerUrl.value;
          cookies.set("serverUrl", serverUrl.value);
          await fetchData();
        } else {
          // 如果数据不是对象类型，显示错误提示
          parseErrorSnackbar.value = true;
        }
      } catch (parseErr) {
        // 如果解析失败，显示错误提示
        parseErrorSnackbar.value = true;
      }
    }
  } catch (err) {
    connectionFailedSnackbar.value = true; // 显示连接失败提示
  } finally {
    connecting.value = false;
  }
};
// 从 cookies 读取 serverUrl
onMounted(() => {
  serverUrl.value = cookies.get("serverUrl") || "";
  inputServerUrl.value = serverUrl.value; // 将读取的值赋给输入框
});

// 用于存储数据的响应式引用
const desserts = ref([]);

// 获取数据并更新表格
const fetchData = async () => {
  if (serverUrl.value) {
    try {
      const response = await axios.get(serverUrl.value);
      desserts.value = response.data; // 假设响应数据直接是表格数据
    } catch (err) {
      error.value = err; // 设置错误信息
    }
  } else {
    error.value = new Error("请先设置服务URL。");
  }
};

// 在组件挂载时获取数据
onMounted(fetchData);

const tryConnect = async () => {
  connecting.value = true; // 开始连接
  try {
    // 尝试连接到后端服务
    const response = await axios.get(inputServerUrl.value);
    if (response.status === 200) {
      serverUrl.value = inputServerUrl.value; // 如果连接成功，更新serverUrl
    }
  } catch (error) {
    console.error("连接失败: ", error);
    alert("连接失败，请检查URL是否正确或服务是否可用。");
  } finally {
    connecting.value = false; // 连接尝试结束
  }
};

// 从祖先组件注入提供的函数
const selectedItems = ref([]);

const headers = ref([
  { title: "时间", key: "settime", width: "20%" },
  { title: "超星", key: "chaoxing_name", width: "20%" },
  { title: "URL", key: "url", width: "20%" },
  { title: "IP", key: "ipaddress", width: "20%" },
  {
    title: "操作",
    key: "data",
    sortable: false,
    align: "center",
    value: "data",
    width: "20%",
  },
]);

// 切换多选框的显示
const toggleSelect = () => {
  showSelect.value = !showSelect.value;
  if (!showSelect.value) {
    selectedItems.value = []; // 关闭多选框时，去除所有勾选
  }
};

// 获取表格项的值
const getItemValue = (item) => {
  return item.id;
};

// 用于存储所有数据的响应式引用
const allDesserts = ref([]);

// 确认删除对话框默认不显示
const confirmDialog = ref(false);

// 删除单条数据
const deleteItem = (id) => {
  selectedItems.value = [id]; // 将选中的项目设置为只有这个id
  confirmDialog.value = true; // 显示确认删除对话框
};

// 多选删除操作，点击后显示弹窗
const noSelectionSnackbar = ref(false);
const deleteSelected = () => {
  if (selectedItems.value.length === 0) {
    noSelectionSnackbar.value = true; // 显示没有选择项目的提示
    return;
  }
  confirmDialog.value = true; // 显示确认删除对话框
};

// 确认删除操作
const confirmDelete = async () => {
  confirmDialog.value = false; // 先关闭弹窗
  try {
    // 打印api地址的值进行检查
    console.log(serverUrl.value);

    // 构建正确的请求URL
    const url = `${serverUrl.value}/`; // 确保这里使用的是字符串

    // 发送请求
    await axios.post(
      url,
      {
        action: "delete",
        ids: selectedItems.value,
      },
      {
        headers: {
          "Content-Type": "application/json",
        },
      }
    );

    // 删除操作成功后的逻辑
    deleteSnackbar.value = true;

    // 重新获取数据
    await fetchData();
  } catch (error) {
    // 错误处理
    console.error("Error deleting selected items: ", error.message);
    // 可以在这里添加用户友好的错误提示
  }
  selectedItems.value = [];
};

// 删除成功后显示提示气泡
const deleteSnackbar = ref(false);

// 复制数据到剪贴板，此处更改要复制的是什么值
const snackbar = ref(false);

const copyData = (data) => {
  try {
    navigator.clipboard
      .writeText(data)
      .then(() => {
        snackbar.value = true;
      })
      .catch((error) => {
        console.error("复制数据失败: ", error);
        alert("复制数据失败，请检查控制台错误信息。");
      });
  } catch (e) {
    console.error("复制数据失败: ", e);
    alert("复制数据失败，请检查控制台错误信息。");
  }
};

const showSelect = ref(false);

// 回顶部去底部按钮
const scrollToTop = () => {
  window.scrollTo({ top: 0, behavior: "smooth" });
};
const scrollToBottom = () => {
  window.scrollTo({ top: document.body.scrollHeight, behavior: "smooth" });
};

// 添加搜索功能
const search = ref("");

// 共享函数
provide("deleteSelected", deleteSelected);
provide("scrollToTop", scrollToTop);
provide("scrollToBottom", scrollToBottom);
provide("toggleSelect", toggleSelect);
</script>

<style scoped>
/* 操作列的两个按钮间距 */
#Operation-Button {
  margin: 4px;
}

.search-1 {
  margin: 0.6rem 1.1rem 0.3rem 1.1rem;
}

.highlight-row {
  filter: brightness(1.2); /* 提高亮度 */
}

/* 隐藏数据表底部默认的分页功能 */
/* .v-data-table-footer {
  display: none;
} */
</style>
