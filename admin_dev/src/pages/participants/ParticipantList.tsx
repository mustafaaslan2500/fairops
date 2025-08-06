import { useState, useEffect } from "react";
import { Link } from "react-router-dom";
import { Table, Input, Space, Tag, Card, Select } from "antd";
import { FiSearch, FiEye, FiUsers } from "react-icons/fi";
import { participantService } from "../../services/exhibitionApi";
import type { Participant } from "../../types/exhibition";
import "./css/ParticipantList.css";

const { Search } = Input;
const { Option } = Select;

const ParticipantList = () => {
  const [participants, setParticipants] = useState<Participant[]>([]);
  const [loading, setLoading] = useState(false);
  const [searchText, setSearchText] = useState("");
  const [selectedExhibition, setSelectedExhibition] = useState<number | undefined>();
  const [pagination, setPagination] = useState({
    current: 1,
    pageSize: 10,
    total: 0,
  });

  // Katılımcı listesini yükle
  const loadParticipants = async (page = 1, limit = 10, search = "", exhibition_id?: number) => {
    setLoading(true);
    try {
      const response = await participantService.getParticipants(page, limit, exhibition_id);
      if (response.status) {
        setParticipants(response.data);
        setPagination({
          current: page,
          pageSize: limit,
          total: response.data.length,
        });
      }
    } catch (error) {
      console.error("Katılımcı listesi yüklenirken hata:", error);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    loadParticipants();
  }, []);

  // Arama işlevi
  const handleSearch = (value: string) => {
    setSearchText(value);
    loadParticipants(1, pagination.pageSize, value, selectedExhibition);
  };

  // Fuar filtresi
  const handleExhibitionFilter = (value: number | undefined) => {
    setSelectedExhibition(value);
    loadParticipants(1, pagination.pageSize, searchText, value);
  };

  // Sayfa değişimi
  const handleTableChange = (page: number, pageSize: number) => {
    loadParticipants(page, pageSize, searchText, selectedExhibition);
  };

  // Durum renk kodlaması
  const getStatusColor = (status: string) => {
    switch (status) {
      case 'active': return 'green';
      case 'registered': return 'blue';
      case 'incomplete': return 'orange';
      case 'pending': return 'gray';
      default: return 'default';
    }
  };

  // Durum metinleri
  const getStatusText = (status: string) => {
    switch (status) {
      case 'active': return 'Aktif';
      case 'registered': return 'Kayıt Tamamlandı';
      case 'incomplete': return 'Eksik Bilgi';
      case 'pending': return 'Beklemede';
      default: return status;
    }
  };

  const columns = [
    {
      title: "Katılımcı Adı",
      dataIndex: "name",
      key: "name",
      render: (text: string) => (
        <div className="participant-name">
          <FiUsers className="participant-icon" />
          {text}
        </div>
      ),
    },
    {
      title: "E-posta",
      dataIndex: "email",
      key: "email",
      render: (email: string) => (
        <div className="participant-email">{email}</div>
      ),
    },
    {
      title: "Fuar/Firma",
      dataIndex: "exhibition_name",
      key: "exhibition_name",
      render: (text: string, record: Participant) => (
        <Link 
          to={`/exhibitions/${record.exhibition_id}`} 
          className="exhibition-link"
        >
          {text}
        </Link>
      ),
    },
    {
      title: "Durum",
      dataIndex: "status",
      key: "status",
      render: (status: string) => (
        <Tag color={getStatusColor(status)}>
          {getStatusText(status)}
        </Tag>
      ),
    },
    {
      title: "Kayıt Tarihi",
      dataIndex: "created_at",
      key: "created_at",
      render: (date: string) => new Date(date).toLocaleDateString("tr-TR"),
    },
    {
      title: "İşlemler",
      key: "actions",
      render: (record: Participant) => (
        <Space size="small">
          <Link to={`/exhibitions/${record.exhibition_id}`}>
            <button className="action-btn view-btn" title="Firma Detayına Git">
              <FiEye />
            </button>
          </Link>
        </Space>
      ),
    },
  ];

  return (
    <div className="participant-list-container">
      <Card>
        <div className="page-header">
          <div className="page-title-section">
            <h1 className="page-title">Katılımcı Takibi</h1>
            <p className="page-description">
              Tüm fuar ve firmalardaki katılımcıları görüntüleyin ve takip edin
            </p>
          </div>
        </div>

        <div className="filter-section">
          <div className="filters-row">
            <Search
              placeholder="Katılımcı adı veya e-posta ara..."
              allowClear
              enterButton={<FiSearch />}
              size="large"
              onSearch={handleSearch}
              className="search-input"
            />
            <Select
              placeholder="Fuar/Firma Filtrele"
              allowClear
              size="large"
              style={{ width: 250 }}
              onChange={handleExhibitionFilter}
              className="exhibition-filter"
            >
              <Option value={1}>İstanbul Fuar Merkezi</Option>
              <Option value={2}>Ankara İş Fuarı</Option>
              <Option value={3}>İzmir Ticaret Odası</Option>
            </Select>
          </div>
        </div>

        <div className="table-section">
          <Table
            columns={columns}
            dataSource={participants}
            rowKey="id"
            loading={loading}
            pagination={{
              current: pagination.current,
              pageSize: pagination.pageSize,
              total: pagination.total,
              showSizeChanger: true,
              showQuickJumper: true,
              showTotal: (total, range) =>
                `${range[0]}-${range[1]} / ${total} katılımcı`,
              onChange: handleTableChange,
              onShowSizeChange: handleTableChange,
            }}
            className="participants-table"
          />
        </div>
      </Card>
    </div>
  );
};

export default ParticipantList;
