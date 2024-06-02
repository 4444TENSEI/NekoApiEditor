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
    </v-main>
  </v-app>
</template>

<script setup>
import { ref, onMounted, provide } from "vue";
import axios from "axios";

// 从祖先组件注入提供的函数
const selectedItems = ref([]);

const desserts = ref([]);

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

// 服务器URL
const serverUrl = import.meta.env.VITE_SERVER_URL;

// 获取表格项的值
const getItemValue = (item) => {
  return item.id;
};

// 用于存储所有数据的响应式引用
const allDesserts = ref([]);

// 组件挂载时获取所有数据
onMounted(async () => {
  try {
    const response = await axios.get(serverUrl);
    // 保存所有数据
    allDesserts.value = response.data;
    // 显示所有数据
    desserts.value = response.data;

    // 默认以时间降序排序
    desserts.value.sort((a, b) => {
      const dateA = new Date(a.settime);
      const dateB = new Date(b.settime);
      return dateB - dateA; // 降序
    });
  } catch (error) {
    console.error("Error fetching data: ", error);
  }
});

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
  try {
    allDesserts.value = allDesserts.value.filter(
      (item) => !selectedItems.value.includes(item.id)
    );

    allDesserts.value.sort((a, b) => {
      const dateA = new Date(a.settime);
      const dateB = new Date(b.settime);
      return dateB - dateA; // 降序排序
    });

    desserts.value = allDesserts.value;

    await axios.post(
      `${serverUrl}/delete`,
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

    deleteSnackbar.value = true;
  } catch (error) {
    console.error("Error deleting selected items: ", error.message);
  }
  confirmDialog.value = false;
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
