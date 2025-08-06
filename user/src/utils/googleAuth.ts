// Google OAuth2 Utility

export class GoogleAuth {
  private static CLIENT_ID = "227946643726-81bu8ar3ljp2fg2k3cdi5aeckh7oibqu.apps.googleusercontent.com"; // Google Console'dan aldığınız gerçek Client ID'yi buraya yazın
  private static SCOPE = "openid email profile https://www.googleapis.com/auth/userinfo.profile https://www.googleapis.com/auth/userinfo.email"; // Bu scope'lar Google'dan kullanıcı bilgilerini almak için yeterli
  private static REDIRECT_URI = "https://dev.fairops.com.tr"; // Development environment için proxy URL

  // Simplified Google OAuth flow using implicit grant
  static async getAccessToken(): Promise<string> {
    return new Promise((resolve, reject) => {
      const params = new URLSearchParams({
        client_id: this.CLIENT_ID,
        redirect_uri: this.REDIRECT_URI,
        scope: this.SCOPE,
        response_type: "token",
        include_granted_scopes: "true",
        prompt: "consent", // Her zaman yeni consent iste
        state: Math.random().toString(36).substring(2, 15)
      });

      const authUrl = `https://accounts.google.com/o/oauth2/v2/auth?${params.toString()}`;
      
      console.log("Google Auth URL:", authUrl);
      console.log("Redirect URI:", this.REDIRECT_URI);
      console.log("Client ID:", this.CLIENT_ID);
      
      // Popup window aç
      const popup = window.open(
        authUrl,
        "google-signin",
        "width=500,height=600,scrollbars=yes,resizable=yes"
      );

      if (!popup) {
        reject(new Error("Popup window açılamadı. Lütfen popup engelleyicisini devre dışı bırakın."));
        return;
      }

      // Popup'ın kapanmasını ve URL'ini kontrol et
      const checkPopup = setInterval(() => {
        try {
          if (popup.closed) {
            clearInterval(checkPopup);
            reject(new Error("Google giriş işlemi iptal edildi."));
            return;
          }

          // URL değişikliğini kontrol et
          const popupUrl = popup.location.href;
          console.log("Popup URL:", popupUrl);
          
          if (popupUrl && popupUrl.includes('access_token=')) {
            clearInterval(checkPopup);
            
            // URL'den access token'ı çıkar
            const urlFragment = popupUrl.split('#')[1];
            const urlParams = new URLSearchParams(urlFragment);
            const accessToken = urlParams.get('access_token');
            
            if (accessToken) {
              console.log("Access token alındı:", accessToken);
              popup.close();
              resolve(accessToken);
            } else {
              popup.close();
              reject(new Error("Access token alınamadı."));
            }
          }
        } catch (error) {
          // Cross-origin error, popup henüz aynı domain'de değil
          // Bu normal, devam et
          console.log("Popup henüz Google domain'inde, bekleniyor...");
        }
      }, 1000);

      // 30 saniye timeout
      setTimeout(() => {
        if (!popup.closed) {
          clearInterval(checkPopup);
          popup.close();
          reject(new Error("Google giriş işlemi zaman aşımına uğradı."));
        }
      }, 30000);
    });
  }

  // Token'ın geçerliliğini kontrol et
  static async validateToken(accessToken: string): Promise<boolean> {
    try {
      const response = await fetch(`https://www.googleapis.com/oauth2/v1/tokeninfo?access_token=${accessToken}`);
      const data = await response.json();
      
      if (response.ok && data.audience === this.CLIENT_ID) {
        console.log("Token geçerli:", data);
        return true;
      } else {
        console.log("Token geçersiz:", data);
        return false;
      }
    } catch (error) {
      console.error("Token validation error:", error);
      return false;
    }
  }
}
