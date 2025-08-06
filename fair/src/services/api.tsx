import axios from "axios";
import type { LoginRequest, LoginResponse, GoogleLoginRequest } from "../types/api";

const instance = axios.create({
  baseURL: import.meta.env.DEV ? "/api" : "https://api.fairops.com.tr", // Development'ta proxy kullan
  headers: {
    "Content-Type": "application/json",
  },
  timeout: 10000, // 10 saniye timeout
  withCredentials: false, // CORS için
});

// Her istek öncesi token eklemek için interceptor
instance.interceptors.request.use((config) => {
  const token = localStorage.getItem("token");
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

// API fonksiyonları
export const apiService = {
  // Login API
  login: async (credentials: LoginRequest): Promise<LoginResponse> => {
    try {
      console.log("API Request:", {
        url: "/admin/login",
        data: credentials,
        baseURL: instance.defaults.baseURL
      });

      const response = await instance.post<LoginResponse>("/admin/login", credentials);
      console.log("API Response:", response.data);
      return response.data;
    } catch (error: any) {
      console.error("Login API Error:", error);
      
      // Network hatası kontrolü
      if (error.code === 'ERR_NETWORK' || error.message === 'Network Error') {
        return {
          status: false,
          message: "Sunucuya bağlanılamıyor. CORS veya SSL problemi olabilir."
        };
      }
      
      // Timeout hatası
      if (error.code === 'ECONNABORTED') {
        return {
          status: false,
          message: "İstek zaman aşımına uğradı. Lütfen tekrar deneyin."
        };
      }
      
      // Hata durumunda API'den gelen response'u döndür
      if (error.response && error.response.data) {
        return error.response.data;
      }
      
      return {
        status: false,
        message: "Bağlantı hatası. Lütfen tekrar deneyin."
      };
    }
  },

  // Google Login API
  googleLogin: async (data: GoogleLoginRequest): Promise<LoginResponse> => {
    try {
      console.log("Google Login API Request:", {
        url: "/admin/google-login",
        data: data,
        baseURL: instance.defaults.baseURL
      });

      // cURL komutunu konsola yazdır
      const curlCommand = `curl --location 'https://api.fairops.com.tr/admin/google-login' \\
--header 'Content-Type: application/json' \\
--data '{
    "access_token": "${data.access_token}"
}'`;
      
      console.log("🔥 cURL Test Komutu:");
      console.log(curlCommand);
      console.log("🔥 Token:", data.access_token);

      const response = await instance.post<LoginResponse>("/admin/google-login", data);
      console.log("Google Login API Response:", response.data);
      return response.data;
    } catch (error: any) {
      console.error("Google Login API Error:", error);
      
      // Network hatası kontrolü
      if (error.code === 'ERR_NETWORK' || error.message === 'Network Error') {
        return {
          status: false,
          message: "Sunucuya bağlanılamıyor. CORS veya SSL problemi olabilir."
        };
      }
      
      // Timeout hatası
      if (error.code === 'ECONNABORTED') {
        return {
          status: false,
          message: "İstek zaman aşımına uğradı. Lütfen tekrar deneyin."
        };
      }
      
      // Özel Google token hataları
      if (error.response && error.response.data) {
        const responseData = error.response.data;
        if (responseData.message && responseData.message.includes("Token süresi dolmuş")) {
          return {
            status: false,
            message: "Google giriş oturumu süresi doldu. Lütfen tekrar giriş yapın."
          };
        }
        return responseData;
      }
      
      return {
        status: false,
        message: "Google giriş hatası. Lütfen tekrar deneyin."
      };
    }
  }
};

export default instance;
