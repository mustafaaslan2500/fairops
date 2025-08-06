import Sidebar from "../../components/Header";
import { Outlet } from "react-router-dom";
import "./css/DashboardLayout.css";

const DashboardLayout = () => {
  return (
    <div className="dashboard-layout">
      <Sidebar />
      <div className="main-wrapper">
        <main className="main-content">
          <Outlet />
        </main>
       
      </div>
    </div>
  );
};

export default DashboardLayout;
