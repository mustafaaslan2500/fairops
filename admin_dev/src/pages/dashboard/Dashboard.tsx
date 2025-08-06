import { FiHome, FiUsers, FiBarChart, FiSettings, FiPlus, FiTrendingUp } from "react-icons/fi";
import "./css/Dashboard.css";

const Dashboard = () => {
    return (
      <div className="dashboard-container">
        <div className="dashboard-header">
          <h1 className="dashboard-title">kerem Dashboard</h1>
          <p className="dashboard-subtitle">Hoş geldin! İşletmeni yönetmek için gereken tüm araçlar burada.</p>
        </div>
        
        <div className="dashboard-grid">
          <div className="dashboard-card welcome-card">
            <div className="card-header">
              <div className="card-icon">
                <FiHome />
              </div>
              <h3 className="card-title">Hoş Geldin!</h3>
            </div>
            <p className="card-content">
              ERP sisteminize başarılı bir şekilde giriş yaptınız. 
              Tüm iş süreçlerinizi buradan kolayca yönetebilirsiniz.
            </p>
            <div className="dashboard-actions">
              <a href="/users" className="action-btn primary">
                <FiPlus />
                Hızlı Başlangıç
              </a>
              <a href="/settings" className="action-btn">
                <FiSettings />
                Ayarlar
              </a>
            </div>
          </div>
          
          <div className="dashboard-card">
            <div className="card-header">
              <div className="card-icon">
                <FiUsers />
              </div>
              <h3 className="card-title">Kullanıcılar</h3>
            </div>
            <p className="card-content">
              Sistem kullanıcılarını yönetin, yeni kullanıcılar ekleyin ve yetkilendirmeleri düzenleyin.
            </p>
            <div className="stats-grid">
              <div className="stat-card">
                <span className="stat-number">24</span>
                <span className="stat-label">Aktif Kullanıcı</span>
              </div>
              <div className="stat-card">
                <span className="stat-number">12</span>
                <span className="stat-label">Yeni Kayıt</span>
              </div>
            </div>
          </div>
          
          <div className="dashboard-card">
            <div className="card-header">
              <div className="card-icon">
                <FiBarChart />
              </div>
              <h3 className="card-title">Analitik</h3>
            </div>
            <p className="card-content">
              İş performansınızı takip edin, raporlar oluşturun ve veriye dayalı kararlar alın.
            </p>
            <div className="stats-grid">
              <div className="stat-card">
                <span className="stat-number">156</span>
                <span className="stat-label">Toplam Satış</span>
              </div>
              <div className="stat-card">
                <span className="stat-number">89%</span>
                <span className="stat-label">Başarı Oranı</span>
              </div>
            </div>
          </div>
          
          <div className="dashboard-card">
            <div className="card-header">
              <div className="card-icon">
                <FiTrendingUp />
              </div>
              <h3 className="card-title">Performans</h3>
            </div>
            <p className="card-content">
              Bu ay yapılan işlemler ve genel sistem performans metrikleri.
            </p>
            <div className="stats-grid">
              <div className="stat-card">
                <span className="stat-number">₺45.2K</span>
                <span className="stat-label">Gelir</span>
              </div>
              <div className="stat-card">
                <span className="stat-number">+18%</span>
                <span className="stat-label">Büyüme</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    );
  };
  
  export default Dashboard;
  