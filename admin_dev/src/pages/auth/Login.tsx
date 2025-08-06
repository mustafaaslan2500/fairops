import { useState } from "react";
import { useNavigate } from "react-router-dom";
import { Form, Input, Button, message } from "antd";
import { GoogleOutlined } from "@ant-design/icons";
import { apiService } from "../../services/api";
import { useAuth } from "../../contexts/AuthContext";
import { GoogleAuth } from "../../utils/googleAuth";
import type { LoginRequest } from "../../types/api";
import './css/Login.css';

const Login = () => {
  const [loading, setLoading] = useState(false);
  const [googleLoading, setGoogleLoading] = useState(false);
  const [error, setError] = useState<string>("");
  const navigate = useNavigate();
  const { login } = useAuth();

  const onFinish = async (values: LoginRequest) => {
    setLoading(true);
    setError(""); // Hata mesajını temizle

    try {
      const response = await apiService.login(values);
      
      if (response.status) {
        // Başarılı giriş
        login(response.user_data);
        message.success(response.message || "Giriş başarılı! Yönlendiriliyorsunuz...");
        navigate("/dashboard"); // Dashboard'a yönlendir
      } else {
        // Başarısız giriş - hata mesajını state'e kaydet
        setError(response.message || "Giriş başarısız. Email veya şifre yanlış.");
      }
    } catch (error) {
      console.error("Login error:", error);
      setError("Beklenmeyen bir hata oluştu. Lütfen tekrar deneyin.");
    } finally {
      setLoading(false);
    }
  };

  const handleGoogleLogin = async () => {
    setGoogleLoading(true);
    setError("");

    try {
      console.log("Google giriş işlemi başlatılıyor...");
      
      // Gerçek Google OAuth kullan
      const accessToken = await GoogleAuth.getAccessToken();
      
      // Test için: bu satırı kullanmayın
      // const accessToken = await GoogleAuth.getTestToken();
      
      console.log("Google access token alındı -:", accessToken);
      console.log("🔥 CURL TEST KOMUTU (PowerShell):");
      console.log(`curl.exe -X POST "https://api.fairops.com.tr/admin/google-login" -H "Content-Type: application/json" -H "Cookie: PHPSESSID=899fdb0e16c48fb77f2e0a26e61e0c69" -d '{"access_token": "${accessToken}"}'`);
      console.log("🔥 TOKEN:", accessToken);

      // Token'ın geçerliliğini kontrol et
      const isValidToken = await GoogleAuth.validateToken(accessToken);
      if (!isValidToken) {
        throw new Error("Alınan token geçersiz. Lütfen tekrar deneyin.");
      }

      // API'ye Google token ile giriş isteği gönder
      const response = await apiService.googleLogin({ access_token: accessToken });
      console.log("Google login API response:", response);
      
      if (response.status) {
        // Başarılı giriş
        login(response.user_data);
        message.success(response.message || "Google ile giriş başarılı! Yönlendiriliyorsunuz...");
        navigate("/dashboard");
      } else {
        // Başarısız giriş
        setError(response.message || "Google ile giriş başarısız.");
      }
    } catch (error: any) {
      console.error("Google login error:", error);
      setError(error.message || "Google giriş işlemi başarısız. Lütfen tekrar deneyin.");
    } finally {
      setGoogleLoading(false);
    }
  };

  return (
    <div className="login-container">
      <div className="login-card">
        <div className="logo-section">
          <div className="logo">
            <span className="logo-icon">✦</span>
          </div>
        </div>

        <h1 className="login-title">Giriş Yap</h1>

        <div className="form-container">
          {error && (
            <div className="error-message">
              <span>{error}</span>
            </div>
          )}
          
          <Form name="login" onFinish={onFinish} layout="vertical" onFieldsChange={() => setError("")}>
            <div className="form-group">
              <label className="form-label" htmlFor="email">E-Posta</label>
              <Form.Item
                name="email"
                rules={[
                  { required: true, message: "Please enter a valid email address." },
                  { type: 'email', message: "Please enter a valid email address." }
                ]}
              >
                <Input
                  id="email"
                  className="form-input"
                  placeholder="your@email.com"
                  autoComplete="email"
                  autoFocus
                />
              </Form.Item>
            </div>

            <div className="form-group password-input">
              <label className="form-label" htmlFor="password">Şifre</label>
              <Form.Item
                name="password"
                rules={[
                  { required: true, message: "Password must be at least 6 characters long." },
                  { min: 6, message: "Password must be at least 6 characters long." }
                ]}
              >
                <Input.Password
                  id="password"
                  className="form-input"
                  placeholder="••••••"
                  autoComplete="current-password"
                />
              </Form.Item>
            </div>

            <Form.Item>
              <Button
                type="primary"
                htmlType="submit"
                loading={loading}
                className="signin-button"
                block
              >
                Giriş Yap
              </Button>
            </Form.Item>
          </Form>

          <div className="divider">
            <span>or</span>
          </div>

          <div className="social-buttons">
            <Button 
              className="google-button" 
              icon={<GoogleOutlined />} 
              block
              loading={googleLoading}
              onClick={handleGoogleLogin}
            >
              Google ile Giriş Yap
            </Button>
          </div>

        </div>
      </div>
    </div>
  );
};

export default Login;
