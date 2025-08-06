import { useState, useEffect } from "react";
import { Link } from "react-router-dom";
import { Table, Input, Space, Tag, Card, Select } from "antd";
import { FiSearch, FiEye, FiTool } from "react-icons/fi";
import { decoratorService } from "../../services/exhibitionApi";
import type { Decorator } from "../../types/exhibition";
import "./css/DecoratorList.css";

const { Search } = Input;
const { Option } = Select;

const DecoratorList = () => {
  const [decorators, setDecorators] = useState<Decorator[]>([]);
  const [loading, setLoading] = useState(false);
  const [searchText, setSearchText] = useState("");
  const [selectedExhibition, setSelectedExhibition] = useState<number | undefined>();
  const [pagination, setPagination] = useState({
    current: 1,
    pageSize: 10,
    total: 0,
  });

  // Dekoratör listesini yükle
  const loadDecorators = async (page = 1, limit = 10, search = "", exhibition_id?: number) => {
    setLoading(true);
    try {
      const response = await decoratorService.getDecorators(page, limit, exhibition_id);
      if (response.status) {
        setDecorators(response.data);
        setPagination({
          current: page,
          pageSize: limit,
          total: response.data.length,
        });
      }
    } catch (error) {
      console.error("Dekoratör listesi yüklenirken hata:", error);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    loadDecorators();
  }, []);

  // Arama işlevi
  const handleSearch = (value: string) => {
    setSearchText(value);
    loadDecorators(1, pagination.pageSize, value, selectedExhibition);
  };

  // Fuar filtresi
  const handleExhibitionFilter = (value: number | undefined) => {
    setSelectedExhibition(value);
    loadDecorators(1, pagination.pageSize, searchText, value);
  };

  // Sayfa değişimi
  const handleTableChange = (page: number, pageSize: number) => {
    loadDecorators(page, pageSize, searchText, selectedExhibition);
  };

  // Durum renk kodlaması
  const getStatusColor = (status: string) => {
    switch (status) {
      case 'assigned': return 'blue';
      case 'working': return 'orange';
      case 'completed': return 'green';
      default: return 'default';
    }
  };

  // Durum metinleri
  const getStatusText = (status: string) => {
    switch (status) {
      case 'assigned': return 'Atandı';
      case 'working': return 'Çalışıyor';
      case 'completed': return 'Tamamlandı';
      default: return status;
    }
  };

  const columns = [
    {
      title: "Dekoratör Firma",
      dataIndex: "company_name",
      key: "company_name",
      render: (text: string) => (
        <div className="decorator-name">
          <FiTool className="decorator-icon" />
          {text}
        </div>
      ),
    },
    {
      title: "Fuar/Firma",
      dataIndex: "exhibition_name",
      key: "exhibition_name",
      render: (text: string, record: Decorator) => (
        <Link 
          to={`/exhibitions/${record.exhibition_id}`} 
          className="exhibition-link"
        >
          {text}
        </Link>
      ),
    },
    {
      title: "Atandığı Katılımcılar",
      dataIndex: "assigned_participants",
      key: "assigned_participants",
      render: (participants: string[]) => (
        <div className="participants-list">
          {participants?.length > 0 ? (
            <div>
              <Tag color="blue">{participants.length} Katılımcı</Tag>
              <div className="participants-preview">
                {participants.slice(0, 2).map((participant, index) => (
                  <span key={index} className="participant-name">
                    {participant}
                  </span>
                ))}
                {participants.length > 2 && (
                  <span className="more-participants">
                    +{participants.length - 2} diğer
                  </span>
                )}
              </div>
            </div>
          ) : (
            <span className="no-participants">Atanmamış</span>
          )}
        </div>
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
      title: "Atanma Tarihi",
      dataIndex: "created_at",
      key: "created_at",
      render: (date: string) => new Date(date).toLocaleDateString("tr-TR"),
    },
    {
      title: "İşlemler",
      key: "actions",
      render: (record: Decorator) => (
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
    <div className="decorator-list-container">
      <Card>
        <div className="page-header">
          <div className="page-title-section">
            <h1 className="page-title">Dekoratör Takibi</h1>
            <p className="page-description">
              Tüm fuar ve firmalardaki dekoratör atamalarını görüntüleyin ve takip edin
            </p>
          </div>
        </div>

        <div className="filter-section">
          <div className="filters-row">
            <Search
              placeholder="Dekoratör firma adı ara..."
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
            dataSource={decorators}
            rowKey="id"
            loading={loading}
            pagination={{
              current: pagination.current,
              pageSize: pagination.pageSize,
              total: pagination.total,
              showSizeChanger: true,
              showQuickJumper: true,
              showTotal: (total, range) =>
                `${range[0]}-${range[1]} / ${total} dekoratör`,
              onChange: handleTableChange,
              onShowSizeChange: handleTableChange,
            }}
            className="decorators-table"
          />
        </div>
      </Card>
    </div>
  );
};

export default DecoratorList;
