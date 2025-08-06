import "./css/footer.css";

const Footer = () => {
  return (
    <footer className="footer">
      <div className="footer-content">
        <div className="footer-section">
          <h4>Oksid N9</h4>
          <p>Modern ERP çözümü</p>
        </div>
        <div className="footer-section">
          <h4>Hızlı Linkler</h4>
          <ul>
            <li><a href="/">Dashboard</a></li>
            <li><a href="/users">Kullanıcılar</a></li>
            <li><a href="/settings">Ayarlar</a></li>
          </ul>
        </div>
        <div className="footer-section">
          <h4>Destek</h4>
          <ul>
            <li><a href="/help">Yardım</a></li>
            <li><a href="/contact">İletişim</a></li>
            <li><a href="/docs">Dokümantasyon</a></li>
          </ul>
        </div>
        <div className="footer-section">
          <h4>İletişim</h4>
          <p>Email: info@oksid.com</p>
          <p>Tel: +90 555 123 45 67</p>
        </div>
      </div>
      <div className="footer-bottom">
        <p>&copy; 2025 Oksid N9. Tüm hakları saklıdır.</p>
      </div>
    </footer>
  );
};

export default Footer;
