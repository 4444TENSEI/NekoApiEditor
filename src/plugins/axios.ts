// Axios网络通用包装
import axios from 'axios';

const config = {
    baseURL: import.meta.env.VITE_SERVER_URL
}

const http = axios.create(config)

export default http
