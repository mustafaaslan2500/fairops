import { FiHome, FiUsers, FiPlus, FiTrendingUp, FiCalendar, FiUserCheck, FiFileText } from "react-icons/fi";
import "./css/Dashboard.css";

const Dashboard = () => {
    return (
      <div className="dashboard-container">
        <div className="dashboard-header">
          <h1 className="dashboard-title">Fairops Admin Panel</h1>
          <p className="dashboard-subtitle">Hoş geldin! Fuar ve firma yönetim sistemine genel bakış.</p>
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
              Admin paneline başarılı bir şekilde giriş yaptınız. 
              Tüm fuar ve firma süreçlerini buradan kolayca yönetebilirsiniz.
            </p>
            <div className="dashboard-actions">
              <a href="/exhibitions/add" className="action-btn primary">
                <FiPlus />
                Yeni Fuar/Firma
              </a>
              <a href="/exhibitions" className="action-btn">
                <FiCalendar />
                Fuar Listesi
              </a>
            </div>
          </div>
          
          <div className="dashboard-card">
            <div className="card-header">
              <div className="card-icon">
                <FiCalendar />
              </div>
              <h3 className="card-title">Fuar/Firma Yönetimi</h3>
            </div>
            <p className="card-content">
              Sistemdeki tüm fuar ve firmaları yönetin, yeni kayıtlar oluşturun ve mevcut bilgileri güncelleyin.
            </p>
            <div className="stats-grid">
              <div className="stat-card">
                <span className="stat-number">12</span>
                <span className="stat-label">Aktif Fuar</span>
              </div>
              <div className="stat-card">
                <span className="stat-number">156</span>
                <span className="stat-label">Toplam Firma</span>
              </div>
            </div>
            <div className="card-actions">
              <a href="/exhibitions" className="card-link">Listele →</a>
            </div>
          </div>
          
          <div className="dashboard-card">
            <div className="card-header">
              <div className="card-icon">
                <FiUserCheck />
              </div>
              <h3 className="card-title">Katılımcı & Dekoratör</h3>
            </div>
            <p className="card-content">
              Fuar katılımcılarını ve atanmış dekoratörleri takip edin. Tüm süreçleri görüntüleyin.
            </p>
            <div className="stats-grid">
              <div className="stat-card">
                <span className="stat-number">89</span>
                <span className="stat-label">Katılımcı</span>
              </div>
              <div className="stat-card">
                <span className="stat-number">23</span>
                <span className="stat-label">Dekoratör</span>
              </div>
            </div>
            <div className="card-actions">
              <a href="/participants" className="card-link">Katılımcılar →</a>
            </div>
          </div>
          
          <div className="dashboard-card">
            <div className="card-header">
              <div className="card-icon">
                <FiFileText />
              </div>
              <h3 className="card-title">Belge Takibi</h3>
            </div>
            <p className="card-content">
              Firmaların yüklediği belgeleri takip edin. Tasarım, proje ve diğer dökümanları görüntüleyin.
            </p>
            <div className="stats-grid">
              <div className="stat-card">
                <span className="stat-number">342</span>
                <span className="stat-label">Belge</span>
              </div>
              <div className="stat-card">
                <span className="stat-number">78%</span>
                <span className="stat-label">Tamamlanan</span>
              </div>
            </div>
            <div className="card-actions">
              <a href="/documents" className="card-link">Belgeler →</a>
            </div>
          </div>

          <div className="dashboard-card">
            <div className="card-header">
              <div className="card-icon">
                <FiUsers />
              </div>
              <h3 className="card-title">Sistem Yönetimi</h3>
            </div>
            <p className="card-content">
              Admin kullanıcılarını yönetin, sistem loglarını inceleyin ve kullanıcı yetkilerini düzenleyin.
            </p>
            <div className="stats-grid">
              <div className="stat-card">
                <span className="stat-number">8</span>
                <span className="stat-label">Admin</span>
              </div>
              <div className="stat-card">
                <span className="stat-number">124</span>
                <span className="stat-label">Log Kayıt</span>
              </div>
            </div>
            <div className="card-actions">
              <a href="/users" className="card-link">Yönetim →</a>
            </div>
          </div>
          
          <div className="dashboard-card">
            <div className="card-header">
              <div className="card-icon">
                <FiTrendingUp />
              </div>
              <h3 className="card-title">Raporlama</h3>
            </div>
            <p className="card-content">
              Sistem performansını izleyin, raporlar oluşturun ve verileri Excel/PDF formatında dışa aktarın.
            </p>
            <div className="stats-grid">
              <div className="stat-card">
                <span className="stat-number">95%</span>
                <span className="stat-label">Sistem Sağlığı</span>
              </div>
              <div className="stat-card">
                <span className="stat-number">15</span>
                <span className="stat-label">Rapor</span>
              </div>
            </div>
            <div className="card-actions">
              <a href="/reports" className="card-link">Raporlar →</a>
            </div>
          </div>
        </div>
      </div>
    );
};

export default Dashboard;
  