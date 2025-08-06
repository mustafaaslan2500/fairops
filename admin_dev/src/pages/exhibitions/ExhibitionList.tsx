import { useState, useEffect } from "react";
import { Link } from "react-router-dom";
import { Table, Button, Input, Space, Tag, message, Popconfirm, Card } from "antd";
import { FiPlus, FiSearch, FiEdit, FiTrash2, FiEye } from "react-icons/fi";
import { exhibitionService } from "../../services/exhibitionApi";
import type { Exhibition } from "../../types/exhibition";
import "./css/ExhibitionList.css";

const { Search } = Input;

const ExhibitionList = () => {
  const [exhibitions, setExhibitions] = useState<Exhibition[]>([]);
  const [loading, setLoading] = useState(false);
  const [searchText, setSearchText] = useState("");
  const [pagination, setPagination] = useState({
    current: 1,
    pageSize: 10,
    total: 0,
  });

  // Fuar/Firma listesini yükle
  const loadExhibitions = async (page = 1, limit = 10, search = "") => {
    setLoading(true);
    try {
      const response = await exhibitionService.getExhibitions(page, limit, search);
      if (response.status) {
        setExhibitions(response.data);
        setPagination({
          current: response.page || page,
          pageSize: limit,
          total: response.total || response.data.length,
        });
      } else {
        message.error(response.message);
      }
    } catch (error) {
      message.error("Veriler yüklenirken hata oluştu.");
    } finally {
      setLoading(false);
    }
  };

  // Component yüklendiğinde verileri getir
  useEffect(() => {
    loadExhibitions();
  }, []);

  // Arama işlevi
  const handleSearch = (value: string) => {
    setSearchText(value);
    loadExhibitions(1, pagination.pageSize, value);
  };

  // Sayfa değişimi
  const handleTableChange = (page: number, pageSize: number) => {
    loadExhibitions(page, pageSize, searchText);
  };

  // Fuar/Firma silme
  const handleDelete = async (id: number) => {
    try {
      const response = await exhibitionService.deleteExhibition(id);
      if (response.status) {
        message.success(response.message);
        loadExhibitions(pagination.current, pagination.pageSize, searchText);
      } else {
        message.error(response.message);
      }
    } catch (error) {
      message.error("Silme işlemi başarısız.");
    }
  };

  // Tablo sütunları
  const columns = [
    {
      title: "Fuar/Firma Adı",
      dataIndex: "name",
      key: "name",
      render: (text: string, record: Exhibition) => (
        <Link to={`/exhibitions/${record.id}`} className="exhibition-name-link">
          {text}
        </Link>
      ),
    },
    {
      title: "Sorumlu Kişi",
      key: "responsible",
      render: (record: Exhibition) => (
        <div>
          <div className="responsible-name">
            {record.responsible_person_name} {record.responsible_person_surname}
          </div>
          <div className="responsible-email">{record.responsible_person_email}</div>
        </div>
      ),
    },
    {
      title: "Durum",
      dataIndex: "status",
      key: "status",
      render: (status: string) => (
        <Tag color={status === "active" ? "green" : "red"}>
          {status === "active" ? "Aktif" : "Pasif"}
        </Tag>
      ),
    },
    {
      title: "Oluşturulma Tarihi",
      dataIndex: "created_at",
      key: "created_at",
      render: (date: string) => new Date(date).toLocaleDateString("tr-TR"),
    },
    {
      title: "İşlemler",
      key: "actions",
      render: (record: Exhibition) => (
        <Space size="small">
          <Link to={`/exhibitions/${record.id}`}>
            <Button
              type="text"
              size="small"
              icon={<FiEye />}
              title="Detay"
              className="action-btn view-btn"
            />
          </Link>
          <Link to={`/exhibitions/${record.id}/edit`}>
            <Button
              type="text"
              size="small"
              icon={<FiEdit />}
              title="Düzenle"
              className="action-btn edit-btn"
            />
          </Link>
          <Popconfirm
            title="Bu fuar/firmayı silmek istediğinizden emin misiniz?"
            onConfirm={() => handleDelete(record.id)}
            okText="Evet"
            cancelText="İptal"
          >
            <Button
              type="text"
              size="small"
              icon={<FiTrash2 />}
              title="Sil"
              className="action-btn delete-btn"
              danger
            />
          </Popconfirm>
        </Space>
      ),
    },
  ];

  return (
    <div className="exhibition-list-container">
      <Card>
        <div className="page-header">
          <div className="page-title-section">
            <h1 className="page-title">Fuar/Firma Yönetimi</h1>
            <p className="page-description">
              Sistem içindeki tüm fuar ve firmaları yönetin
            </p>
          </div>
          <Link to="/exhibitions/add">
            <Button
              type="primary"
              icon={<FiPlus />}
              size="large"
              className="add-button"
            >
              Yeni Fuar/Firma Ekle
            </Button>
          </Link>
        </div>

        <div className="filter-section">
          <Search
            placeholder="Fuar/Firma adı veya sorumlu kişi ara..."
            allowClear
            enterButton={<FiSearch />}
            size="large"
            onSearch={handleSearch}
            className="search-input"
          />
        </div>

        <div className="table-section">
          <Table
            columns={columns}
            dataSource={exhibitions}
            rowKey="id"
            loading={loading}
            pagination={{
              current: pagination.current,
              pageSize: pagination.pageSize,
              total: pagination.total,
              showSizeChanger: true,
              showQuickJumper: true,
              showTotal: (total, range) =>
                `${range[0]}-${range[1]} / ${total} kayıt`,
              onChange: handleTableChange,
              onShowSizeChange: handleTableChange,
            }}
            className="exhibitions-table"
          />
        </div>
      </Card>
    </div>
  );
};

export default ExhibitionList;
