<template>
  <v-app>
    <NekoFrame v-if="serverUrl && serverUrl !== ''" />

    <v-main>
      <!-- 暂时不做多页面，所以注释这个先
    <router-view /> -->

      <div v-if="desserts === ''">暂时还没有数据</div>
      <!-- 数据表格组件 -->
      <v-data-table v-if="desserts.length > 0" :headers="headers" :items="desserts" :item-value="getItemValue"
        :show-select="showSelect" :search="search" :show-expand="false" v-model="selectedItems" class="mobile-table">

        <!-- 表格项中的操作列 -->
        <template v-slot:[`item.actions`]="{ item }">
          <v-btn id="Operation-Button" icon @click="copyData(item[selectedCopyField])" size="small" color="primary">
            <v-icon>mdi-content-copy</v-icon>
          </v-btn>
          <v-btn id="Operation-Button" icon @click="deleteItem(item.id)" size="small" color="error">
            <v-icon>mdi-delete</v-icon>
          </v-btn>
        </template>

        <template v-slot:top>
          <v-text-field v-model="search" label="搜索" class="search-1" hide-details variant="outlined" clearable
            append-inner-icon="mdi-magnify"></v-text-field>
        </template>
      </v-data-table>




      <v-dialog v-model="fieldSelectionDialog" max-width="400">
        <v-card class="pl-6 pr-6 pb-7">

          <v-card-text class="pa-0">

            <v-card-title class="pl-0 pt-6">
              <h4>要展示的字段</h4>
            </v-card-title>
            <v-select clearable variant="outlined" chips hide-details v-model="selectedFields" :items="availableFields"
              label="多选" multiple></v-select>


            <v-card-title class="pl-0 pt-4">
              <h4>设置复制按钮将复制的值</h4>
            </v-card-title>
            <v-select clearable variant="outlined" chips hide-details v-model="selectedCopyField"
              :items="availableFields" label="单选" dense></v-select>
          </v-card-text>

          <v-card-actions class="pl-6 pt-6 pr-0 pb-0">
            <v-btn @click="updateTableFields" rounded="pill" variant="tonal" color="success" size="large">确定</v-btn>

            <v-btn @click="fieldSelectionDialog = false" color="info" rounded="pill" variant="tonal"
              size="large">取消</v-btn>
          </v-card-actions>
        </v-card>
      </v-dialog>




      <!-- 确认删除的对话框 -->
      <v-dialog v-model="confirmDialog" persistent max-width="300">
        <v-card class="pl-5 pr-5 pt-6 pb-5">
          <v-card-title class="pa-0 pb-3">
            <h4>确认删除</h4>
          </v-card-title>
          <v-card-text class="pa-0">确定要删除选中的{{ selectedItems.length }}条项目吗？</v-card-text>
          <v-card-actions class="pl-6 pt-6 pr-0 pb-0">
            <v-spacer></v-spacer>
            <v-btn color="error" text @click="confirmDelete" rounded="pill" variant="tonal" size="large">确定</v-btn>
            <v-btn color="info" text @click="confirmDialog = false" rounded="pill" variant="tonal"
              size="large">取消</v-btn>
          </v-card-actions>
        </v-card>
      </v-dialog>


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

      <!---- 提示气泡 ---->
      <v-snackbar v-model="loginSnackbar" :timeout="2000" color="success" location="top" rounded="pill">
        🐱连接成功喵~
      </v-snackbar>

      <!-- 复制成功气泡 -->
      <v-snackbar v-model="copySnackbar" :timeout="2000" color="primary" location="top" rounded="pill">
        🐱复制{{ selectedCopyField }}成功喵~
      </v-snackbar>


      <v-snackbar v-model="deleteSnackbar" :timeout="2000" color="success" rounded="pill" location="top">
        🐱删除成功喵~
      </v-snackbar>

      <!-- 没有选择项目的提示气泡 -->
      <v-snackbar v-model="noSelectionSnackbar" :timeout="2000" color="info" location="top" rounded="pill">
        🐱没有任何勾选喵~
      </v-snackbar>

      <!-- API服务链接失败提示 -->
      <v-snackbar v-model="connectionFailedSnackbar" :timeout="2000" color="error" location="top" rounded="pill">
        🐱连接失败，请检查API是否可用~
      </v-snackbar>

      <!-- API服务链接解析错误提示 -->
      <v-snackbar v-model="parseErrorSnackbar" :timeout="2000" color="error" location="top" rounded="pill">
        🐱API解析响应失败，请确保返回的是规范的json~
      </v-snackbar>

      <!-- 服务URL表单 -->
      <v-sheet v-if="!serverUrl || connecting"
        class="h-100 w-100 mx-auto d-flex flex-column align-center justify-center">
        <v-card width="370" class="px-6 py-6 d-flex flex-column align-center justify-center">
          <v-form v-model="form" @submit.prevent="setServerUrl"
            class="w-100 d-flex flex-column align-center justify-center">
            <v-text-field v-model="inputServerUrl" :readonly="connecting" :rules="[isUrl]" label="🐱API地址"
              variant="outlined" clearable hide-details append-inner-icon="mdi-link-variant"
              style="height: 3.8rem; margin-bottom: 1rem" class="w-100"></v-text-field>

            <v-btn :loading="connecting" color="info" size="large" type="submit" variant="elevated" block rounded="pill"
              style="height: 3.4rem" class="w-100">
              <h3>开始连接</h3>
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
const desserts = ref([]);
const selectedItems = ref([]);
const availableFields = ref([]);
const selectedFields = ref([]);
const showSelect = ref(false);
const selectedCopyField = ref("");
const search = ref("");

const fieldSelectionDialog = ref(false);
const showLogoutConfirmDialog = ref(false);
const confirmDialog = ref(false);

//弹出气泡
const noSelectionSnackbar = ref(false);
const loginSnackbar = ref(false);
const deleteSnackbar = ref(false);
const copySnackbar = ref(false);
const connectionFailedSnackbar = ref(false);
const parseErrorSnackbar = ref(false);


const isUrl = (value) => {
  const urlPattern =
    /^(?:https?:\/\/)?(?:localhost|(?:(?:[a-zA-Z0-9-]+\.)*[a-zA-Z0-9-]+\.[a-zA-Z]{2,})(?::\d{2,5})?(?:\/[\S]*)?)$/;
  return urlPattern.test(value);
};


const setServerUrl = async () => {
  connecting.value = true;
  error.value = null;
  try {
    const response = await axios.get(inputServerUrl.value);
    if (response.status === 200) {
      serverUrl.value = inputServerUrl.value;
      cookies.set("serverUrl", serverUrl.value);
      // 等待获取数据成功
      await fetchData();
      fieldSelectionDialog.value = true;

      // 设置连接成功提示
      loginSnackbar.value = true;
    } else {
      throw new Error("Invalid response status");
    }
  } catch (err) {
    // 显示连接失败提示
    connectionFailedSnackbar.value = true;
  } finally {
    connecting.value = false;
  }
};


onMounted(() => {
  serverUrl.value = cookies.get("serverUrl") || "";
  inputServerUrl.value = serverUrl.value; // 将读取的值赋给输入框

  // 从cookie中读取选定的字段和复制的字段
  const storedSelectedFields = cookies.get("selectedFields");
  const storedSelectedCopyField = cookies.get("selectedCopyField");

  if (storedSelectedFields) {
    selectedFields.value = storedSelectedFields;
  }

  if (storedSelectedCopyField) {
    selectedCopyField.value = storedSelectedCopyField;
  }

  updateTableFields(); // 更新表格字段
});


const headers = ref([
  { title: '名称', key: 'name', sortable: true, nowrap: true },
  { title: '操作', key: 'actions', sortable: false },
]);


const updateTableFields = () => {
  const selectedHeaders = selectedFields.value.map(field => ({
    title: field,
    key: field,
    sortable: true,
  }));

  selectedHeaders.push({ title: '操作', key: 'actions', sortable: false });

  headers.value = selectedHeaders;

  // 保存选定的字段和复制的字段到cookie
  cookies.set("selectedFields", selectedFields.value);
  cookies.set("selectedCopyField", selectedCopyField.value);

  fieldSelectionDialog.value = false;
};


const fetchData = async () => {
  if (serverUrl.value) {
    try {
      const response = await axios.get(serverUrl.value);
      desserts.value = response.data.sort((a, b) => Number(b.id) - Number(a.id));
      availableFields.value = Object.keys(response.data[0] || {});
    } catch (err) {
      error.value = err;
    }
  } else {
    error.value = new Error("请先设置服务URL。");
  }
};


onMounted(fetchData);


const toggleSelect = () => {
  showSelect.value = !showSelect.value;
  if (!showSelect.value) {
    selectedItems.value = [];
  }
};


const getItemValue = (item) => {
  return item.id;
};


const deleteItem = (id) => {
  selectedItems.value = [id];
  confirmDialog.value = true;
};


const deleteSelected = () => {
  if (selectedItems.value.length === 0) {
    noSelectionSnackbar.value = true;
    return;
  }
  confirmDialog.value = true;
};


const confirmDelete = async () => {
  confirmDialog.value = false;
  try {
    const url = `${serverUrl.value}/`;
    await axios.post(url, { action: "delete", ids: selectedItems.value }, { headers: { "Content-Type": "application/json" } });
    deleteSnackbar.value = true;
    await fetchData();
  } catch (error) {
    console.error("Error deleting selected items: ", error.message);
  }
  selectedItems.value = [];
};


const copyData = (data) => {
  if (selectedCopyField.value) {
    try {
      navigator.clipboard.writeText(data).then(() => {
        // 设置 copySnackbar 为 true 来显示 snackbar
        copySnackbar.value = true;
      }).catch((error) => {
        console.error("复制数据失败: ", error);
        alert("复制数据失败，请检查控制台错误信息。");
      });
    } catch (e) {
      console.error("复制数据失败: ", e);
      alert("复制数据失败，请检查控制台错误信息。");
    }
  } else {
    alert("请先选择要复制的字段。");
  }
};



const scrollToTop = () => {
  window.scrollTo({ top: 0, behavior: "smooth" });
};


const scrollToBottom = () => {
  window.scrollTo({ top: document.body.scrollHeight, behavior: "smooth" });
};




// 导出共享函数
provide("deleteSelected", deleteSelected);
provide("scrollToTop", scrollToTop);
provide("scrollToBottom", scrollToBottom);
provide("toggleSelect", toggleSelect);
provide("fieldSelectionDialog ", fieldSelectionDialog);
provide("isUrl", isUrl);

provide("cookies", cookies);
provide("serverUrl", serverUrl);

</script>







<style scoped>

/* 操作列的两个按钮间距 */
#Operation-Button {
  margin: 4px;
}

.search-1 {
  margin: 0.6rem 1.1rem 0.3rem 1.1rem;
}

/* 隐藏数据表底部默认的分页功能 */
/* .v-data-table-footer {
  display: none;
} */

</style>
