import { Link, useLocation } from "react-router-dom";
import { 
    FiChevronDown, 
    FiChevronUp, 
    FiHome, 
    FiLogOut, 
    FiUsers, 
    FiMenu, 
    FiX, 
    FiMoon, 
    FiSun,
    FiCalendar,
    FiUserCheck,
    FiFileText,
    FiSettings,
    FiActivity,
    FiBell,
    FiBarChart
} from "react-icons/fi";
import { useState } from "react";
import { useTheme } from "../../contexts/ThemeContext";
import { useAuth } from "../../contexts/AuthContext";
import "./css/main.css";

function Sidebar() {
    const location = useLocation();
    const { theme, toggleTheme } = useTheme();
    const { user, logout } = useAuth();
    const [openDropdown, setOpenDropdown] = useState<string | null>(null);
    const [isMobileMenuOpen, setIsMobileMenuOpen] = useState(false);


    const toggleMobileMenu = () => {
        setIsMobileMenuOpen(!isMobileMenuOpen);
    };

    const menuItems = [
        { path: "/", label: "Dashboard", icon: <FiHome size={20} /> },
        {
            label: "Fuar/Firma Yönetimi",
            icon: <FiCalendar size={20} />,
            children: [
                { path: "/exhibitions", label: "Fuar/Firma Listesi" },
                { path: "/exhibitions/add", label: "Yeni Fuar/Firma Ekle" },
            ],
        },
        {
            label: "Takip Sistemleri",
            icon: <FiUserCheck size={20} />,
            children: [
                { path: "/participants", label: "Katılımcı Listesi" },
                { path: "/decorators", label: "Dekoratör Listesi" },
                { path: "/documents", label: "Belge Takibi" },
            ],
        },
        {
            label: "Sistem Yönetimi",
            icon: <FiSettings size={20} />,
            children: [
                { path: "/users", label: "Admin Kullanıcıları" },
                { path: "/users/add", label: "Yeni Admin Ekle" },
                { path: "/logs", label: "İşlem Kayıtları" },
            ],
        },
        {
            label: "Bildirimler & Raporlar",
            icon: <FiBell size={20} />,
            children: [
                { path: "/notifications", label: "Bildirim Takibi" },
                { path: "/reports", label: "Raporlama" },
            ],
        },
    ];

    const toggleDropdown = (label: string) => {
        if (openDropdown === label) {
            setOpenDropdown(null);
        } else {
            setOpenDropdown(label);
        }
    };

    const handleMainLinkClick = () => {
        setOpenDropdown(null);
    };

    return (
        <>
            <nav className="mobile-navbar">
                <button className="mobile-menu-btn" onClick={toggleMobileMenu}>
                    {isMobileMenuOpen ? <FiX size={24} /> : <FiMenu size={24} />}
                </button>
                <div className="mobile-logo">Fairops</div>
                <div className="mobile-profile">
                    <div className="mobile-avatar">
                        <img src="https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?fm=jpg&q=60&w=3000&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxzZWFyY2h8M3x8dXNlciUyMHByb2ZpbGV8ZW58MHx8MHx8fDA%3D" alt="Avatar" />
                    </div>
                </div>
            </nav>

            <aside className={`sidebar ${isMobileMenuOpen ? 'mobile-open' : ''}`}>
                <div className="sidebar-logo">Fairops</div>
                <ul className="sidebar-menu">
                    {menuItems.map((item) => (
                        <li
                            key={item.label}
                            className={`sidebar-item ${location.pathname === item.path ? "active" : ""
                                }`}
                        >
                            {item.children ? (
                                <>
                                    <button
                                        className="dropdown-toggle"
                                        onClick={() => toggleDropdown(item.label)}
                                    >
                                        {item.icon}
                                        <span>{item.label}</span>
                                        <span className="arrow">
                                            {openDropdown === item.label ? <FiChevronUp size={16} /> : <FiChevronDown size={16} />}
                                        </span>
                                    </button>
                                    <ul
                                        className={`dropdown-menu ${openDropdown === item.label ? "show" : ""
                                            }`}
                                    >
                                        {item.children.map((child) => (
                                            <li
                                                key={child.path}
                                                className={`sidebar-subitem ${location.pathname === child.path ? "active" : ""
                                                    }`}
                                            >
                                                <Link to={child.path} onClick={() => setIsMobileMenuOpen(false)}>{child.label}</Link>
                                            </li>
                                        ))}
                                    </ul>
                                </>
                            ) : (
                                <Link to={item.path} onClick={() => {
                                    handleMainLinkClick();
                                    setIsMobileMenuOpen(false);
                                }}>
                                    {item.icon}
                                    <span>{item.label}</span>
                                </Link>
                            )}
                        </li>
                    ))}
                </ul>
                
                {/* Theme Toggle */}
                <div className="theme-toggle-container">
                    <button
                        className="theme-toggle-btn"
                        onClick={toggleTheme}
                        title={theme === 'light' ? 'Karanlık moda geç' : 'Açık moda geç'}
                    >
                        {theme === 'light' ? <FiMoon size={20} /> : <FiSun size={20} />}
                        <span>{theme === 'light' ? 'Karanlık Mod' : 'Açık Mod'}</span>
                    </button>
                </div>
                
                <div className="sidebar-account-detail">
                    <div className="account-detail">
                        <div className="account-avatar">
                            <img src="https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?fm=jpg&q=60&w=3000&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxzZWFyY2h8M3x8dXNlciUyMHByb2ZpbGV8ZW58MHx8MHx8fDA%3D" alt="Avatar" />
                        </div>
                        <div className="account-info">
                            <span className="account-name">
                                {user ? `${user.name} ${user.surname}` : 'Kullanıcı'}
                            </span>
                        </div>
                    </div>
                    <div className="logOut" onClick={logout}>
                        <FiLogOut size={16} />
                    </div>
                </div>
            </aside>

            {isMobileMenuOpen && <div className="mobile-overlay" onClick={toggleMobileMenu}></div>}
        </>
    );
}

export default Sidebar;
