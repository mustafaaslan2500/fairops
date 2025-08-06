import { BrowserRouter, Routes, Route, Navigate } from "react-router-dom";
import { useAuth } from "./contexts/AuthContext";

import Dashboard from "./pages/dashboard/Dashboard";
import DashboardLayout from "./layouts/DashboardLayout";
import Login from "./pages/auth/Login";
import ExhibitionList from "./pages/exhibitions/ExhibitionList";
import AddExhibition from "./pages/exhibitions/AddExhibition";
import ParticipantList from "./pages/participants/ParticipantList";
import DecoratorList from "./pages/decorators/DecoratorList";
import { ThemeProvider } from "./contexts/ThemeContext";

import "./assets/global-css/app.css";

function App() {
  const { isAuthenticated, loading } = useAuth();

  // Loading durumunda spinner göster
  if (loading) {
    return (
      <div style={{ 
        display: 'flex', 
        justifyContent: 'center', 
        alignItems: 'center', 
        height: '100vh' 
      }}>
        Loading...
      </div>
    );
  }

  return (
    <ThemeProvider>
      <BrowserRouter>
        <Routes>
          {/* Kimlik doğrulama durumuna göre yönlendirme */}
          {!isAuthenticated ? (
            <>
              <Route path="/login" element={<Login />} />
              <Route path="*" element={<Navigate to="/login" replace />} />
            </>
          ) : (
            <>
              {/* DashboardLayout altında yer alan tüm sayfalar */}
              <Route element={<DashboardLayout />}>
                <Route path="/" element={<Navigate to="/dashboard" replace />} />
                <Route path="/dashboard" element={<Dashboard />} />
                
                {/* Fuar/Firma Yönetimi */}
                <Route path="/exhibitions" element={<ExhibitionList />} />
                <Route path="/exhibitions/add" element={<AddExhibition />} />
                <Route path="/exhibitions/:id" element={<div>Fuar/Firma Detayı</div>} />
                <Route path="/exhibitions/:id/edit" element={<div>Fuar/Firma Düzenle</div>} />
                
                {/* Takip Sistemleri */}
                <Route path="/participants" element={<ParticipantList />} />
                <Route path="/decorators" element={<DecoratorList />} />
                <Route path="/documents" element={<div>Belge Takibi</div>} />
                
                {/* Sistem Yönetimi */}
                <Route path="/users" element={<div>Admin Kullanıcıları</div>} />
                <Route path="/users/add" element={<div>Yeni Admin Ekle</div>} />
                <Route path="/logs" element={<div>İşlem Kayıtları</div>} />
                
                {/* Bildirimler & Raporlar */}
                <Route path="/notifications" element={<div>Bildirim Takibi</div>} />
                <Route path="/reports" element={<div>Raporlama</div>} />
              </Route>
              <Route path="/login" element={<Navigate to="/dashboard" replace />} />
              <Route path="*" element={<Navigate to="/dashboard" replace />} />
            </>
          )}
        </Routes>
      </BrowserRouter>
    </ThemeProvider>
  );
}

export default App;

